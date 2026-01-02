<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/content-filter.php';

// Only admins can access this
require_login();
if (!is_admin()) {
    json_response(['ok' => false, 'error' => 'دسترسی مجاز نیست'], 403);
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // Get reports
    if ($method === 'GET' && $action === 'reports') {
        $reportsFile = __DIR__ . '/../data/reports.json';
        $reports = [];
        
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
        }
        
        // Add details to reports
        foreach ($reports as $i => $report) {
            $reporter = get_user_by_id($report['reporterId']);
            $reports[$i]['reporter'] = $reporter ? [
                'id' => $reporter['id'],
                'name' => $reporter['name'],
                'username' => $reporter['username'] ?? ''
            ] : null;
            
            // Get reported content
            if ($report['type'] === 'topic') {
                $topic = get_topic_by_id($report['itemId']);
                $reports[$i]['content'] = $topic ? [
                    'title' => $topic['title'],
                    'content' => mb_substr($topic['content'], 0, 200) . '...'
                ] : null;
            } elseif ($report['type'] === 'post') {
                $posts = JsonDB::read(POSTS_COLLECTION);
                $post = null;
                foreach ($posts as $p) {
                    if ((int)$p['id'] === $report['itemId']) {
                        $post = $p;
                        break;
                    }
                }
                $reports[$i]['content'] = $post ? [
                    'content' => mb_substr($post['content'], 0, 200) . '...'
                ] : null;
            }
        }
        
        json_response(['ok' => true, 'reports' => array_reverse($reports)]);
    }

    // Get suspended users
    if ($method === 'GET' && $action === 'suspensions') {
        $suspensionsFile = __DIR__ . '/../data/user_suspensions.json';
        $suspensions = [];
        
        if (file_exists($suspensionsFile)) {
            $suspensions = json_decode(file_get_contents($suspensionsFile), true) ?? [];
        }
        
        $activeSuspensions = [];
        foreach ($suspensions as $userId => $suspension) {
            if ($suspension['until'] > time()) {
                $user = get_user_by_id((int)$userId);
                $activeSuspensions[] = [
                    'userId' => $userId,
                    'user' => $user ? [
                        'id' => $user['id'],
                        'name' => $user['name'],
                        'username' => $user['username'] ?? ''
                    ] : null,
                    'reason' => $suspension['reason'],
                    'until' => $suspension['until'],
                    'untilFormatted' => date('Y-m-d H:i:s', $suspension['until']),
                    'createdAt' => $suspension['created_at']
                ];
            }
        }
        
        json_response(['ok' => true, 'suspensions' => $activeSuspensions]);
    }

    // Get content violations log
    if ($method === 'GET' && $action === 'violations') {
        $logFile = __DIR__ . '/../data/content_violations.log';
        $violations = [];
        
        if (file_exists($logFile)) {
            $lines = file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $violations = array_map('json_decode', array_slice($lines, -50)); // Last 50 violations
            $violations = array_filter($violations);
            $violations = array_reverse($violations);
        }
        
        json_response(['ok' => true, 'violations' => $violations]);
    }

    // Handle report action
    if ($method === 'POST' && $action === 'handle_report') {
        $reportId = (int)($_POST['reportId'] ?? 0);
        $reportAction = $_POST['reportAction'] ?? ''; // 'dismiss', 'delete_content', 'suspend_user'
        $suspendHours = (int)($_POST['suspendHours'] ?? 24);
        
        if ($reportId <= 0) {
            throw new RuntimeException('شناسه گزارش نامعتبر است');
        }
        
        $reportsFile = __DIR__ . '/../data/reports.json';
        $reports = [];
        
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
        }
        
        // Find report
        $reportIndex = -1;
        $report = null;
        foreach ($reports as $i => $r) {
            if ((int)$r['id'] === $reportId) {
                $reportIndex = $i;
                $report = $r;
                break;
            }
        }
        
        if (!$report) {
            throw new RuntimeException('گزارش یافت نشد');
        }
        
        switch ($reportAction) {
            case 'dismiss':
                $reports[$reportIndex]['status'] = 'dismissed';
                $reports[$reportIndex]['handledAt'] = date('c');
                $reports[$reportIndex]['handledBy'] = current_user()['id'];
                break;
                
            case 'delete_content':
                // Mark content as deleted
                if ($report['type'] === 'topic') {
                    JsonDB::upsert(TOPICS_COLLECTION, function($topics) use ($report) {
                        foreach ($topics as $i => $topic) {
                            if ((int)$topic['id'] === $report['itemId']) {
                                $topics[$i]['status'] = 'deleted';
                                break;
                            }
                        }
                        return $topics;
                    });
                } elseif ($report['type'] === 'post') {
                    JsonDB::upsert(POSTS_COLLECTION, function($posts) use ($report) {
                        foreach ($posts as $i => $post) {
                            if ((int)$post['id'] === $report['itemId']) {
                                $posts[$i]['status'] = 'deleted';
                                break;
                            }
                        }
                        return $posts;
                    });
                }
                
                $reports[$reportIndex]['status'] = 'resolved';
                $reports[$reportIndex]['action'] = 'content_deleted';
                $reports[$reportIndex]['handledAt'] = date('c');
                $reports[$reportIndex]['handledBy'] = current_user()['id'];
                break;
                
            case 'suspend_user':
                // Get user ID from content
                $targetUserId = null;
                if ($report['type'] === 'topic') {
                    $topic = get_topic_by_id($report['itemId']);
                    $targetUserId = $topic ? $topic['userId'] : null;
                } elseif ($report['type'] === 'post') {
                    $posts = JsonDB::read(POSTS_COLLECTION);
                    foreach ($posts as $post) {
                        if ((int)$post['id'] === $report['itemId']) {
                            $targetUserId = $post['userId'];
                            break;
                        }
                    }
                }
                
                if ($targetUserId) {
                    ContentFilter::suspendUser($targetUserId, $suspendHours, "گزارش توسط مدیر: {$report['reason']}");
                }
                
                $reports[$reportIndex]['status'] = 'resolved';
                $reports[$reportIndex]['action'] = 'user_suspended';
                $reports[$reportIndex]['suspendHours'] = $suspendHours;
                $reports[$reportIndex]['handledAt'] = date('c');
                $reports[$reportIndex]['handledBy'] = current_user()['id'];
                break;
                
            default:
                throw new RuntimeException('عملیات نامعتبر است');
        }
        
        file_put_contents($reportsFile, json_encode($reports, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        
        json_response(['ok' => true, 'message' => 'گزارش پردازش شد']);
    }

    // Suspend user manually
    if ($method === 'POST' && $action === 'suspend_user') {
        $userId = (int)($_POST['userId'] ?? 0);
        $hours = (int)($_POST['hours'] ?? 24);
        $reason = trim($_POST['reason'] ?? '');
        
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        
        if (empty($reason)) {
            throw new RuntimeException('دلیل مسدودسازی الزامی است');
        }
        
        $user = get_user_by_id($userId);
        if (!$user) {
            throw new RuntimeException('کاربر یافت نشد');
        }
        
        if (($user['role'] ?? 'user') === 'admin') {
            throw new RuntimeException('نمی‌توان مدیر را مسدود کرد');
        }
        
        ContentFilter::suspendUser($userId, $hours, $reason);
        
        json_response(['ok' => true, 'message' => 'کاربر مسدود شد']);
    }

    // Remove suspension
    if ($method === 'POST' && $action === 'remove_suspension') {
        $userId = (int)($_POST['userId'] ?? 0);
        
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        
        $suspensionsFile = __DIR__ . '/../data/user_suspensions.json';
        $suspensions = [];
        
        if (file_exists($suspensionsFile)) {
            $suspensions = json_decode(file_get_contents($suspensionsFile), true) ?? [];
        }
        
        if (isset($suspensions[$userId])) {
            unset($suspensions[$userId]);
            file_put_contents($suspensionsFile, json_encode($suspensions, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
        
        json_response(['ok' => true, 'message' => 'مسدودسازی لغو شد']);
    }

    // Get forum statistics for admin
    if ($method === 'GET' && $action === 'admin_stats') {
        $categories = get_forum_categories();
        $topics = JsonDB::read(TOPICS_COLLECTION);
        $posts = JsonDB::read(POSTS_COLLECTION);
        $users = JsonDB::read(USERS_COLLECTION);
        
        // Content violations
        $logFile = __DIR__ . '/../data/content_violations.log';
        $violationsCount = 0;
        if (file_exists($logFile)) {
            $violationsCount = count(file($logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES));
        }
        
        // Reports
        $reportsFile = __DIR__ . '/../data/reports.json';
        $reportsCount = 0;
        $pendingReports = 0;
        if (file_exists($reportsFile)) {
            $reports = json_decode(file_get_contents($reportsFile), true) ?? [];
            $reportsCount = count($reports);
            $pendingReports = count(array_filter($reports, fn($r) => $r['status'] === 'pending'));
        }
        
        // Suspensions
        $suspensionsFile = __DIR__ . '/../data/user_suspensions.json';
        $activeSuspensions = 0;
        if (file_exists($suspensionsFile)) {
            $suspensions = json_decode(file_get_contents($suspensionsFile), true) ?? [];
            foreach ($suspensions as $suspension) {
                if ($suspension['until'] > time()) {
                    $activeSuspensions++;
                }
            }
        }
        
        $stats = [
            'totalCategories' => count($categories),
            'totalTopics' => count($topics),
            'totalPosts' => count($posts),
            'totalUsers' => count($users),
            'activeTopics' => count(array_filter($topics, fn($t) => ($t['status'] ?? 'active') === 'active')),
            'activePosts' => count(array_filter($posts, fn($p) => ($p['status'] ?? 'active') === 'active')),
            'deletedTopics' => count(array_filter($topics, fn($t) => ($t['status'] ?? 'active') === 'deleted')),
            'deletedPosts' => count(array_filter($posts, fn($p) => ($p['status'] ?? 'active') === 'deleted')),
            'totalViolations' => $violationsCount,
            'totalReports' => $reportsCount,
            'pendingReports' => $pendingReports,
            'activeSuspensions' => $activeSuspensions
        ];
        
        json_response(['ok' => true, 'stats' => $stats]);
    }

    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);

} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
