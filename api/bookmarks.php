<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    require_login();
    $user = current_user();
    $userId = (int)$user['id'];
    
    switch ($action) {
        case 'toggle':
            handle_toggle_bookmark($userId);
            break;
            
        case 'get':
            handle_get_bookmarks($userId);
            break;
            
        case 'check':
            handle_check_bookmark($userId);
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
    }

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}

function handle_toggle_bookmark(int $userId): void {
    ensure_csrf_token();
    
    $projectId = (int)($_POST['projectId'] ?? 0);
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }
    
    // Verify project exists
    $projects = JsonDB::read('projects');
    $project = null;
    foreach ($projects as $p) {
        if ((int)$p['id'] === $projectId) {
            $project = $p;
            break;
        }
    }
    
    if (!$project) {
        echo json_encode(['ok' => false, 'error' => 'پروژه یافت نشد']);
        return;
    }
    
    // Get user bookmarks
    $bookmarks = JsonDB::read('bookmarks');
    $userBookmarks = [];
    $bookmarkIndex = null;
    
    foreach ($bookmarks as $index => $bookmark) {
        if ((int)($bookmark['userId'] ?? 0) === $userId) {
            $userBookmarks = $bookmark['projects'] ?? [];
            $bookmarkIndex = $index;
            break;
        }
    }
    
    // Check if project is already bookmarked
    $isBookmarked = in_array($projectId, $userBookmarks);
    
    if ($isBookmarked) {
        // Remove bookmark
        $userBookmarks = array_filter($userBookmarks, fn($id) => $id !== $projectId);
        $userBookmarks = array_values($userBookmarks);
        $message = 'پروژه از علاقه‌مندی‌ها حذف شد';
    } else {
        // Add bookmark
        $userBookmarks[] = $projectId;
        $message = 'پروژه به علاقه‌مندی‌ها اضافه شد';
    }
    
    // Update bookmarks
    if ($bookmarkIndex !== null) {
        $bookmarks[$bookmarkIndex]['projects'] = $userBookmarks;
        $bookmarks[$bookmarkIndex]['updatedAt'] = date('c');
    } else {
        $bookmarks[] = [
            'userId' => $userId,
            'projects' => $userBookmarks,
            'createdAt' => date('c'),
            'updatedAt' => date('c')
        ];
    }
    
    JsonDB::write('bookmarks', $bookmarks);
    
    echo json_encode([
        'ok' => true,
        'bookmarked' => !$isBookmarked,
        'message' => $message,
        'totalBookmarks' => count($userBookmarks)
    ]);
}

function handle_get_bookmarks(int $userId): void {
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(50, max(10, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    // Get user bookmarks
    $bookmarks = JsonDB::read('bookmarks');
    $userBookmarks = [];
    
    foreach ($bookmarks as $bookmark) {
        if ((int)($bookmark['userId'] ?? 0) === $userId) {
            $userBookmarks = $bookmark['projects'] ?? [];
            break;
        }
    }
    
    if (empty($userBookmarks)) {
        echo json_encode([
            'ok' => true,
            'bookmarks' => [],
            'pagination' => [
                'page' => $page,
                'limit' => $limit,
                'total' => 0,
                'pages' => 0
            ]
        ]);
        return;
    }
    
    // Get projects data
    $projects = JsonDB::read('projects');
    $bookmarkedProjects = [];
    
    foreach ($userBookmarks as $projectId) {
        foreach ($projects as $project) {
            if ((int)$project['id'] === $projectId) {
                $bookmarkedProjects[] = $project;
                break;
            }
        }
    }
    
    // Sort by bookmark date (most recent first)
    usort($bookmarkedProjects, function($a, $b) {
        return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
    });
    
    // Paginate
    $total = count($bookmarkedProjects);
    $pages = ceil($total / $limit);
    $paginatedProjects = array_slice($bookmarkedProjects, $offset, $limit);
    
    echo json_encode([
        'ok' => true,
        'bookmarks' => $paginatedProjects,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $pages
        ]
    ]);
}

function handle_check_bookmark(int $userId): void {
    $projectId = (int)($_GET['projectId'] ?? 0);
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }
    
    // Get user bookmarks
    $bookmarks = JsonDB::read('bookmarks');
    $userBookmarks = [];
    
    foreach ($bookmarks as $bookmark) {
        if ((int)($bookmark['userId'] ?? 0) === $userId) {
            $userBookmarks = $bookmark['projects'] ?? [];
            break;
        }
    }
    
    $isBookmarked = in_array($projectId, $userBookmarks);
    
    echo json_encode([
        'ok' => true,
        'bookmarked' => $isBookmarked,
        'totalBookmarks' => count($userBookmarks)
    ]);
}
?>
