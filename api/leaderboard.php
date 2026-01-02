<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Aggregate per-user metrics in one pass to avoid O(U*N) filters
function aggregate_user_metrics(array $projects, array $topics, array $posts, array $submissions): array
{
    $projectCountByUser = [];
    $downloadsByUser = [];
    $starsByUser = [];
    $topicCountByUser = [];
    $postCountByUser = [];
    $submissionCountByUser = [];

    foreach ($projects as $p) {
        $ownerId = (int)($p['authorId'] ?? ($p['userId'] ?? 0));
        if ($ownerId <= 0) continue;
        $projectCountByUser[$ownerId] = ($projectCountByUser[$ownerId] ?? 0) + 1;
        $downloadsByUser[$ownerId] = ($downloadsByUser[$ownerId] ?? 0) + (int)($p['downloads'] ?? 0);
        $starsByUser[$ownerId] = ($starsByUser[$ownerId] ?? 0) + (int)($p['stars'] ?? 0);
    }

    foreach ($topics as $t) {
        if (($t['status'] ?? 'active') !== 'active') continue;
        $uid = (int)($t['userId'] ?? 0);
        if ($uid <= 0) continue;
        $topicCountByUser[$uid] = ($topicCountByUser[$uid] ?? 0) + 1;
    }

    foreach ($posts as $p) {
        if (($p['status'] ?? 'active') !== 'active') continue;
        $uid = (int)($p['userId'] ?? 0);
        if ($uid <= 0) continue;
        $postCountByUser[$uid] = ($postCountByUser[$uid] ?? 0) + 1;
    }

    foreach ($submissions as $s) {
        $ownerId = (int)($s['authorId'] ?? ($s['userId'] ?? 0));
        if ($ownerId <= 0) continue;
        $submissionCountByUser[$ownerId] = ($submissionCountByUser[$ownerId] ?? 0) + 1;
    }

    return [
        'projectCount' => $projectCountByUser,
        'downloads' => $downloadsByUser,
        'stars' => $starsByUser,
        'topicCount' => $topicCountByUser,
        'postCount' => $postCountByUser,
        'submissionCount' => $submissionCountByUser,
    ];
}

try {
    // Get leaderboard data
    if ($method === 'GET' && $action === 'get') {
        $type = $_GET['type'] ?? 'points'; // points, forum_posts, uploads, downloads
        $limit = min((int)($_GET['limit'] ?? 20), 50);
        
        $users = JsonDB::read(USERS_COLLECTION);
        $projects = JsonDB::read('projects');
        $topics = JsonDB::read(TOPICS_COLLECTION);
        $posts = JsonDB::read(POSTS_COLLECTION);
        $submissions = JsonDB::read('submissions');
        
        // Pre-aggregate per-user counts
        $agg = aggregate_user_metrics($projects, $topics, $posts, $submissions);
        
        // Calculate user statistics
        $userStats = [];
        foreach ($users as $user) {
            $userId = (int)$user['id'];
            $projectCount = (int)($agg['projectCount'][$userId] ?? 0);
            $topicCount = (int)($agg['topicCount'][$userId] ?? 0);
            $postCount = (int)($agg['postCount'][$userId] ?? 0);
            $submissionCount = (int)($agg['submissionCount'][$userId] ?? 0);
            $totalDownloads = (int)($agg['downloads'][$userId] ?? 0);
            $totalStars = (int)($agg['stars'][$userId] ?? 0);
            
            $userStats[] = [
                'id' => $userId,
                'name' => $user['name'],
                'username' => $user['username'] ?? '',
                'email' => $user['email'],
                'role' => $user['role'] ?? 'user',
                'points' => (int)($user['points'] ?? 0),
                'joinDate' => $user['createdAt'] ?? '',
                'projectCount' => $projectCount,
                'topicCount' => $topicCount,
                'postCount' => $postCount,
                'submissionCount' => $submissionCount,
                'totalDownloads' => $totalDownloads,
                'totalStars' => $totalStars,
                'forumActivity' => $topicCount + $postCount,
                'totalUploads' => $projectCount + $submissionCount
            ];
        }
        
        // Sort based on type
        switch ($type) {
            case 'points':
                usort($userStats, fn($a, $b) => $b['points'] - $a['points']);
                break;
            case 'forum_posts':
                usort($userStats, fn($a, $b) => $b['forumActivity'] - $a['forumActivity']);
                break;
            case 'uploads':
                usort($userStats, fn($a, $b) => $b['totalUploads'] - $a['totalUploads']);
                break;
            case 'downloads':
                usort($userStats, fn($a, $b) => $b['totalDownloads'] - $a['totalDownloads']);
                break;
            case 'stars':
                usort($userStats, fn($a, $b) => $b['totalStars'] - $a['totalStars']);
                break;
            default:
                usort($userStats, fn($a, $b) => $b['points'] - $a['points']);
        }
        
        // Take top users
        $topUsers = array_slice($userStats, 0, $limit);
        
        // Add rank
        foreach ($topUsers as $i => $user) {
            $topUsers[$i]['rank'] = $i + 1;
        }
        
        json_response([
            'ok' => true,
            'leaderboard' => $topUsers,
            'type' => $type,
            'total' => count($userStats)
        ]);
    }

    // Get user rank
    if ($method === 'GET' && $action === 'user_rank') {
        $userId = (int)($_GET['userId'] ?? 0);
        $type = $_GET['type'] ?? 'points';
        
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        
        $users = JsonDB::read(USERS_COLLECTION);
        $projects = JsonDB::read('projects');
        $topics = JsonDB::read(TOPICS_COLLECTION);
        $posts = JsonDB::read(POSTS_COLLECTION);
        $submissions = JsonDB::read('submissions');

        // Pre-aggregate per-user counts
        $agg = aggregate_user_metrics($projects, $topics, $posts, $submissions);

        // Build value map for requested metric
        $values = [];
        foreach ($users as $user) {
            $uid = (int)$user['id'];
            $points = (int)($user['points'] ?? 0);
            $projectCount = (int)($agg['projectCount'][$uid] ?? 0);
            $submissionCount = (int)($agg['submissionCount'][$uid] ?? 0);
            $topicCount = (int)($agg['topicCount'][$uid] ?? 0);
            $postCount = (int)($agg['postCount'][$uid] ?? 0);
            $downloads = (int)($agg['downloads'][$uid] ?? 0);
            $stars = (int)($agg['stars'][$uid] ?? 0);

            switch ($type) {
                case 'forum_posts':
                    $values[$uid] = $topicCount + $postCount;
                    break;
                case 'uploads':
                    $values[$uid] = $projectCount + $submissionCount;
                    break;
                case 'downloads':
                    $values[$uid] = $downloads;
                    break;
                case 'stars':
                    $values[$uid] = $stars;
                    break;
                case 'points':
                default:
                    $values[$uid] = $points;
                    break;
            }
        }

        arsort($values);
        $ranks = array_flip(array_keys($values));

        $userRank = isset($ranks[$userId]) ? $ranks[$userId] + 1 : null;
        $userValue = $values[$userId] ?? 0;
        
        json_response([
            'ok' => true,
            'rank' => $userRank,
            'value' => $userValue,
            'total' => count($userStats),
            'type' => $type
        ]);
    }

    // Get overall statistics
    if ($method === 'GET' && $action === 'stats') {
        $users = JsonDB::read(USERS_COLLECTION);
        $projects = JsonDB::read('projects');
        $topics = JsonDB::read(TOPICS_COLLECTION);
        $posts = JsonDB::read(POSTS_COLLECTION);
        $submissions = JsonDB::read('submissions');
        
        // Calculate this month's stats
        $thisMonth = date('Y-m');
        
        $monthlyTopics = array_filter($topics, function($t) use ($thisMonth) {
            return ($t['status'] ?? 'active') === 'active' && 
                   strpos($t['createdAt'] ?? '', $thisMonth) === 0;
        });
        
        $monthlyPosts = array_filter($posts, function($p) use ($thisMonth) {
            return ($p['status'] ?? 'active') === 'active' && 
                   strpos($p['createdAt'] ?? '', $thisMonth) === 0;
        });
        
        $monthlyProjects = array_filter($projects, function($p) use ($thisMonth) {
            return strpos($p['createdAt'] ?? '', $thisMonth) === 0;
        });
        
        // Today's stats
        $today = date('Y-m-d');
        
        $todayTopics = array_filter($topics, function($t) use ($today) {
            return ($t['status'] ?? 'active') === 'active' && 
                   strpos($t['createdAt'] ?? '', $today) === 0;
        });
        
        $todayPosts = array_filter($posts, function($p) use ($today) {
            return ($p['status'] ?? 'active') === 'active' && 
                   strpos($p['createdAt'] ?? '', $today) === 0;
        });
        
        $stats = [
            'totalUsers' => count($users),
            'totalProjects' => count($projects),
            'totalTopics' => count(array_filter($topics, fn($t) => ($t['status'] ?? 'active') === 'active')),
            'totalPosts' => count(array_filter($posts, fn($p) => ($p['status'] ?? 'active') === 'active')),
            'totalSubmissions' => count($submissions),
            'totalDownloads' => array_sum(array_map(fn($p) => $p['downloads'] ?? 0, $projects)),
            'totalStars' => array_sum(array_map(fn($p) => $p['stars'] ?? 0, $projects)),
            'monthlyTopics' => count($monthlyTopics),
            'monthlyPosts' => count($monthlyPosts),
            'monthlyProjects' => count($monthlyProjects),
            'todayTopics' => count($todayTopics),
            'todayPosts' => count($todayPosts)
        ];
        
        json_response(['ok' => true, 'stats' => $stats]);
    }

    // Get achievement data for a user
    if ($method === 'GET' && $action === 'achievements') {
        $userId = (int)($_GET['userId'] ?? 0);
        
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        
        $user = get_user_by_id($userId);
        if (!$user) {
            throw new RuntimeException('کاربر یافت نشد');
        }
        
        $projects = JsonDB::read('projects');
        $topics = JsonDB::read(TOPICS_COLLECTION);
        $posts = JsonDB::read(POSTS_COLLECTION);
        $submissions = JsonDB::read('submissions');
        
        $userProjects = array_filter($projects, fn($p) => (int)($p['authorId'] ?? ($p['userId'] ?? 0)) === $userId);
        $userTopics = array_filter($topics, fn($t) => (int)$t['userId'] === $userId && ($t['status'] ?? 'active') === 'active');
        $userPosts = array_filter($posts, fn($p) => (int)$p['userId'] === $userId && ($p['status'] ?? 'active') === 'active');
        $userSubmissions = array_filter($submissions, fn($s) => (int)$s['userId'] === $userId);
        
        $totalDownloads = array_sum(array_map(fn($p) => $p['downloads'] ?? 0, $userProjects));
        $totalStars = array_sum(array_map(fn($p) => $p['stars'] ?? 0, $userProjects));
        
        // Calculate achievements
        $achievements = [];
        
        // First project
        if (count($userProjects) >= 1) {
            $achievements[] = ['name' => 'اولین پروژه', 'icon' => '🎯', 'description' => 'اولین پروژه را آپلود کرد'];
        }
        
        // Active contributor
        if (count($userProjects) >= 5) {
            $achievements[] = ['name' => 'مشارکت‌کننده فعال', 'icon' => '🔥', 'description' => '5 پروژه آپلود کرد'];
        }
        
        // Forum starter
        if (count($userTopics) >= 1) {
            $achievements[] = ['name' => 'شروع‌کننده بحث', 'icon' => '💬', 'description' => 'اولین موضوع را ایجاد کرد'];
        }
        
        // Active discusser
        if (count($userPosts) >= 10) {
            $achievements[] = ['name' => 'بحث‌کننده فعال', 'icon' => '🗣️', 'description' => '10 پست در انجمن ارسال کرد'];
        }
        
        // Popular creator
        if ($totalStars >= 50) {
            $achievements[] = ['name' => 'خالق محبوب', 'icon' => '⭐', 'description' => '50 ستاره دریافت کرد'];
        }
        
        // High downloader
        if ($totalDownloads >= 100) {
            $achievements[] = ['name' => 'پردانلود', 'icon' => '📥', 'description' => 'پروژه‌هایش 100 بار دانلود شد'];
        }
        
        json_response([
            'ok' => true,
            'achievements' => $achievements,
            'stats' => [
                'projectCount' => count($userProjects),
                'topicCount' => count($userTopics),
                'postCount' => count($userPosts),
                'submissionCount' => count($userSubmissions),
                'totalDownloads' => $totalDownloads,
                'totalStars' => $totalStars,
                'points' => (int)($user['points'] ?? 0)
            ]
        ]);
    }

    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);

} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
