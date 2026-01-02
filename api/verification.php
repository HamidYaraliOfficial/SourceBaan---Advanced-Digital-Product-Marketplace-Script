<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
if (!$user) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'error' => 'نیاز به ورود دارید']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'POST' && $action === 'request_verification') {
        // Request verification (blue checkmark)
        $name = trim($_POST['name'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        $telegram_id = trim($_POST['telegram_id'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        
        if (empty($name) || empty($reason) || empty($telegram_id)) {
            throw new RuntimeException('لطفاً تمام فیلدهای اجباری را پر کنید');
        }

        // Check if user already has verification
        if (($user['verified'] ?? false) === true) {
            throw new RuntimeException('شما قبلاً تایید شده‌اید');
        }

        // Check if user already has pending request
        $requests = JsonDB::read('verification_requests');
        foreach ($requests as $req) {
            if ((int)$req['user_id'] === (int)$user['id'] && $req['status'] === 'pending') {
                throw new RuntimeException('شما قبلاً درخواست تایید ارسال کرده‌اید و در انتظار بررسی است');
            }
        }

        // Get user activity data
        $projects = JsonDB::read('projects');
        $userProjects = array_filter($projects, function($p) use ($user) {
            return (int)($p['authorId'] ?? 0) === (int)$user['id'];
        });

        $totalProjects = count($userProjects);
        $approvedProjects = count(array_filter($userProjects, function($p) {
            return ($p['status'] ?? '') === 'approved';
        }));
        $rejectedProjects = count(array_filter($userProjects, function($p) {
            return ($p['status'] ?? '') === 'rejected';
        }));
        $pendingProjects = count(array_filter($userProjects, function($p) {
            return ($p['status'] ?? '') === 'pending';
        }));
        
        // Calculate total stars and downloads
        $totalStars = array_sum(array_map(function($p) {
            return (int)($p['stars'] ?? 0);
        }, $userProjects));
        
        $totalDownloads = array_sum(array_map(function($p) {
            return (int)($p['downloads'] ?? 0);
        }, $userProjects));

        $request = [
            'id' => JsonDB::nextId(),
            'user_id' => (int)$user['id'],
            'name' => $name,
            'reason' => $reason,
            'telegram_id' => $telegram_id,
            'phone' => $phone,
            'status' => 'pending', // pending, approved, rejected
            'created_at' => date('c'),
            'processed_at' => null,
            'processed_by' => null,
            'admin_notes' => '',
            // User activity data
            'user_activity' => [
                'total_projects' => $totalProjects,
                'approved_projects' => $approvedProjects,
                'rejected_projects' => $rejectedProjects,
                'pending_projects' => $pendingProjects,
                'total_points' => (int)($user['points'] ?? 0),
                'total_stars' => $totalStars,
                'total_downloads' => $totalDownloads,
                'projects_list' => array_map(function($p) {
                    return [
                        'id' => $p['id'] ?? '',
                        'title' => $p['title'] ?? '',
                        'language' => $p['language'] ?? '',
                        'status' => $p['status'] ?? '',
                        'stars' => (int)($p['stars'] ?? 0),
                        'downloads' => (int)($p['downloads'] ?? 0),
                        'uploadDate' => $p['uploadDate'] ?? $p['createdAt'] ?? ''
                    ];
                }, $userProjects)
            ]
        ];

        $requests[] = $request;
        JsonDB::write('verification_requests', $requests);

        echo json_encode(['ok' => true, 'message' => 'درخواست تایید شما با موفقیت ارسال شد']);
        exit;
    }

    if ($method === 'GET' && $action === 'my_request') {
        // Get user's verification request status
        $requests = JsonDB::read('verification_requests');
        $userRequest = null;
        
        foreach ($requests as $req) {
            if ((int)$req['user_id'] === (int)$user['id']) {
                $userRequest = $req;
                break;
            }
        }

        echo json_encode([
            'ok' => true,
            'has_request' => $userRequest !== null,
            'request' => $userRequest,
            'is_verified' => ($user['verified'] ?? false) === true
        ]);
        exit;
    }

    // Admin actions
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'دسترسی ندارید']);
        exit;
    }

    if ($method === 'GET' && $action === 'list_requests') {
        // List all verification requests for admin
        $requests = JsonDB::read('verification_requests');
        $users = JsonDB::read('users');
        
        // Add user info to requests
        foreach ($requests as &$req) {
            foreach ($users as $u) {
                if ((int)$u['id'] === (int)$req['user_id']) {
                    $req['user'] = [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'email' => $u['email'],
                        'username' => $u['username'] ?? '',
                        'verified' => $u['verified'] ?? false
                    ];
                    break;
                }
            }
        }

        // Sort by creation date (newest first)
        usort($requests, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        echo json_encode(['ok' => true, 'requests' => $requests]);
        exit;
    }

    if ($method === 'POST' && $action === 'process_request') {
        // Approve or reject verification request
        $request_id = (int)($_POST['request_id'] ?? 0);
        $decision = $_POST['decision'] ?? ''; // 'approve' or 'reject'
        $admin_notes = trim($_POST['admin_notes'] ?? '');

        if (!$request_id || !in_array($decision, ['approve', 'reject'])) {
            throw new RuntimeException('اطلاعات ارسالی نامعتبر است');
        }

        $requests = JsonDB::read('verification_requests');
        $requestFound = false;
        
        foreach ($requests as &$req) {
            if ((int)$req['id'] === $request_id) {
                $req['status'] = $decision === 'approve' ? 'approved' : 'rejected';
                $req['processed_at'] = date('c');
                $req['processed_by'] = (int)$user['id'];
                $req['admin_notes'] = $admin_notes;
                $requestFound = true;

                // If approved, update user verification status
                if ($decision === 'approve') {
                    $users = JsonDB::read('users');
                    foreach ($users as &$u) {
                        if ((int)$u['id'] === (int)$req['user_id']) {
                            $u['verified'] = true;
                            $u['verified_at'] = date('c');
                            $u['verified_by'] = (int)$user['id'];
                            break;
                        }
                    }
                    JsonDB::write('users', $users);
                }
                break;
            }
        }

        if (!$requestFound) {
            throw new RuntimeException('درخواست مورد نظر یافت نشد');
        }

        JsonDB::write('verification_requests', $requests);

        echo json_encode([
            'ok' => true, 
            'message' => $decision === 'approve' ? 'درخواست تایید شد' : 'درخواست رد شد'
        ]);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'عملیات نامعتبر']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
