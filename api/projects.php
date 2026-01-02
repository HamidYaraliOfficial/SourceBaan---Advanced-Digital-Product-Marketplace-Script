<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Includes
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    
    $action = $_GET['action'] ?? '';
    
    // Source of truth: approved submissions are our public projects
    $all = JsonDB::read('submissions');
    $projects = array_values(array_filter($all, function($p) {
        return ($p['status'] ?? 'pending') === 'approved';
    }));

    // Enrich with lastUpdatedAt (max of updatedAt, file mtime, createdAt)
    foreach ($projects as $i => $p) {
        $createdAt = (string)($p['createdAt'] ?? '');
        $updatedAt = (string)($p['updatedAt'] ?? '');
        $fileName = (string)($p['fileName'] ?? '');
        $createdTs = $createdAt !== '' ? strtotime($createdAt) : 0;
        $updatedTs = $updatedAt !== '' ? strtotime($updatedAt) : 0;
        $fileTs = 0;
        if ($fileName !== '') {
            $path = rtrim(APPROVED_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($path)) {
                $fileTs = (int)@filemtime($path);
            }
        }
        $lastTs = max($createdTs, $updatedTs, $fileTs);
        $projects[$i]['lastUpdatedAt'] = $lastTs > 0 ? date('c', $lastTs) : ($updatedAt ?: $createdAt);
    }

    // Enrich with author profile (id, name, username, points, verified)
    require_once __DIR__ . '/../includes/auth.php';
    foreach ($projects as $i => $p) {
        $authorId = (int)($p['authorId'] ?? 0);
        if ($authorId > 0) {
            $author = get_user_by_id($authorId);
            if ($author) {
                unset($author['password_hash']);
                $projects[$i]['authorProfile'] = [
                    'id' => (int)$author['id'],
                    'name' => $author['name'],
                    'username' => $author['username'] ?? '',
                    'points' => (int)($author['points'] ?? 0),
                    'verified' => !empty($author['verified']),
                    'telegram' => $author['telegram'] ?? ''
                ];
            }
        }
    }
    
    // Stats
    if ($action === 'stats') {
        $totalProjects = count($projects);
        $totalDownloads = 0;
        $totalStars = 0;
        $languages = [];
        
        foreach ($projects as $project) {
            $totalDownloads += (int)($project['downloads'] ?? 0);
            $totalStars += (int)($project['stars'] ?? 0);
            $lang = $project['language'] ?? 'سایر';
            if (!in_array($lang, $languages, true)) {
                $languages[] = $lang;
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'totalProjects' => $totalProjects,
                'totalDownloads' => $totalDownloads,
                'totalStars' => $totalStars,
                'totalLanguages' => count($languages)
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Trending projects
    if ($action === 'trending') {
        $trending = $projects;
        usort($trending, function($a, $b) {
            $scoreA = (int)($a['stars'] ?? 0) + (int)($a['downloads'] ?? 0);
            $scoreB = (int)($b['stars'] ?? 0) + (int)($b['downloads'] ?? 0);
            return $scoreB - $scoreA;
        });
        $trending = array_slice($trending, 0, 12);
        echo json_encode(['success' => true, 'projects' => $trending], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Search projects with pagination
    if ($action === 'search') {
        $query = trim($_GET['q'] ?? '');
        $language = trim($_GET['language'] ?? '');
        $level = trim($_GET['level'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
        $sort = $_GET['sort'] ?? 'newest'; // newest, popular, downloads, stars
        
        $filtered = [];
        foreach ($projects as $project) {
            $match = true;
            
            if ($query !== '') {
                $searchText = strtolower(($project['title'] ?? '') . ' ' . ($project['description'] ?? ''));
                $queryLower = strtolower($query);
                if (strpos($searchText, $queryLower) === false) {
                    $match = false;
                }
            }
            
            if ($match && $language !== '' && ($project['language'] ?? '') !== $language) {
                $match = false;
            }
            
            if ($match && $level !== '' && ($project['level'] ?? '') !== $level) {
                $match = false;
            }
            
            if ($match) {
                $filtered[] = $project;
            }
        }
        
        // Sort results
        switch ($sort) {
            case 'popular':
                usort($filtered, fn($a, $b) => (int)($b['views'] ?? 0) - (int)($a['views'] ?? 0));
                break;
            case 'downloads':
                usort($filtered, fn($a, $b) => (int)($b['downloads'] ?? 0) - (int)($a['downloads'] ?? 0));
                break;
            case 'stars':
                usort($filtered, fn($a, $b) => (int)($b['stars'] ?? 0) - (int)($a['stars'] ?? 0));
                break;
            case 'newest':
            default:
                usort($filtered, fn($a, $b) => strtotime($b['lastUpdatedAt'] ?? '0') - strtotime($a['lastUpdatedAt'] ?? '0'));
                break;
        }
        
        // Paginate
        $total = count($filtered);
        $pages = ceil($total / $limit);
        $offset = ($page - 1) * $limit;
        $paginatedProjects = array_slice($filtered, $offset, $limit);
        
        echo json_encode([
            'success' => true, 
            'projects' => $paginatedProjects,
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => $total,
                'pages' => $pages
            ],
            'filters' => [
                'query' => $query,
                'language' => $language,
                'level' => $level,
                'sort' => $sort
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Default: return all approved projects with pagination
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 50)));
    $sort = $_GET['sort'] ?? 'newest';
    
    // Sort projects
    $sortedProjects = $projects;
    switch ($sort) {
        case 'popular':
            usort($sortedProjects, fn($a, $b) => (int)($b['views'] ?? 0) - (int)($a['views'] ?? 0));
            break;
        case 'downloads':
            usort($sortedProjects, fn($a, $b) => (int)($b['downloads'] ?? 0) - (int)($a['downloads'] ?? 0));
            break;
        case 'stars':
            usort($sortedProjects, fn($a, $b) => (int)($b['stars'] ?? 0) - (int)($a['stars'] ?? 0));
            break;
        case 'newest':
        default:
            usort($sortedProjects, fn($a, $b) => strtotime($b['lastUpdatedAt'] ?? '0') - strtotime($a['lastUpdatedAt'] ?? '0'));
            break;
    }
    
    // Paginate
    $total = count($sortedProjects);
    $pages = ceil($total / $limit);
    $offset = ($page - 1) * $limit;
    $paginatedProjects = array_slice($sortedProjects, $offset, $limit);
    
    echo json_encode([
        'success' => true, 
        'projects' => $paginatedProjects,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $pages
        ],
        'sort' => $sort
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>