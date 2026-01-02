<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Remove server signature
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
    header_remove('Server');
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // Update profile info (name, bio, socials, location)
    if ($method === 'POST' && $action === 'update_profile') {
        require_login();
        ensure_csrf_token();
        $me = current_user();
        if (!$me) { throw new RuntimeException('نیاز به ورود دارید'); }

        $name = sanitize_text_field(trim((string)($_POST['name'] ?? '')), 80);
        $bio = sanitize_multiline((string)($_POST['bio'] ?? ''), 500);
        $website = trim((string)($_POST['website'] ?? ''));
        $github = trim((string)($_POST['github'] ?? ''));
        $twitter = trim((string)($_POST['twitter'] ?? ''));
        $telegram = trim((string)($_POST['telegram'] ?? ''));
        $province = sanitize_text_field(trim((string)($_POST['province'] ?? '')), 60);
        $city = sanitize_text_field(trim((string)($_POST['city'] ?? '')), 60);

        if ($name === '') { throw new RuntimeException('نام نمی‌تواند خالی باشد'); }
        // Name validation removed - allow any characters
        if (mb_strlen($bio) > 500) { throw new RuntimeException('بیوگرافی حداکثر 500 کاراکتر'); }

        // Normalize URLs
        $normalizeUrl = function(string $url): string {
            $url = trim($url);
            if ($url === '') return '';
            if (!preg_match('~^https?://~i', $url)) { $url = 'https://' . $url; }
            return $url;
        };
        $website = $normalizeUrl($website);
        if ($github !== '' && strpos($github, 'github.com') === false) { $github = 'https://github.com/' . ltrim($github, '@/'); }
        if ($twitter !== '' && strpos($twitter, 'twitter.com') === false && strpos($twitter, 'x.com') === false) { $twitter = 'https://twitter.com/' . ltrim($twitter, '@/'); }
        if ($telegram !== '' && strpos($telegram, 't.me') === false) { $telegram = 'https://t.me/' . ltrim($telegram, '@/'); }

        // Save
        $me['name'] = $name;
        $me['bio'] = $bio;
        $me['website'] = $website;
        $me['github'] = $github;
        $me['twitter'] = $twitter;
        $me['telegram'] = $telegram;
        $me['province'] = $province;
        $me['city'] = $city;
        save_user($me);

        echo json_encode(['ok' => true, 'user' => [
            'name' => $me['name'],
            'bio' => $me['bio'] ?? '',
            'website' => $me['website'] ?? '',
            'github' => $me['github'] ?? '',
            'twitter' => $me['twitter'] ?? '',
            'telegram' => $me['telegram'] ?? '',
            'province' => $me['province'] ?? '',
            'city' => $me['city'] ?? '',
        ]], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Set only telegram (one-field fast update) - used by one-time prompt
    if ($method === 'POST' && $action === 'set_telegram') {
        require_login();
        ensure_csrf_token();
        $me = current_user();
        if (!$me) { throw new RuntimeException('نیاز به ورود دارید'); }

        $telegram = trim((string)($_POST['telegram'] ?? ''));
        if ($telegram === '') { json_response(['ok' => false, 'error' => 'شناسه تلگرام خالی است']); }
        // Normalize: accept @username, username, or full t.me URL
        if (preg_match('~^(https?://)?(t\.me/)?@?([A-Za-z0-9_]{3,50})$~i', $telegram, $m)) {
            $handle = $m[3];
            $me['telegram'] = 'https://t.me/' . $handle;
        } else {
            // Fallback: store as-is
            $me['telegram'] = $telegram;
        }
        save_user($me);
        json_response(['ok' => true, 'telegram' => $me['telegram']]);
        exit;
    }

    // Upload avatar
    if ($method === 'POST' && $action === 'upload_avatar') {
        require_login();
        ensure_csrf_token();
        $me = current_user();
        if (!$me) { throw new RuntimeException('نیاز به ورود دارید'); }

        if (!isset($_FILES['avatar']) || !is_array($_FILES['avatar'])) {
            throw new RuntimeException('فایل آواتار ارسال نشده است');
        }
        $file = $_FILES['avatar'];
        if ((int)$file['error'] !== UPLOAD_ERR_OK) { throw new RuntimeException('آپلود آواتار ناموفق'); }
        if ((int)$file['size'] > 2 * 1024 * 1024) { throw new RuntimeException('حداکثر حجم آواتار 2MB است'); }

        $tmpPath = (string)$file['tmp_name'];
        $mime = get_mime_type($tmpPath);
        $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
        if (!isset($allowed[$mime])) { throw new RuntimeException('فرمت تصویر معتبر نیست'); }
        $ext = $allowed[$mime];

        $avatarDir = rtrim(IMAGES_DIR, '/\\') . DIRECTORY_SEPARATOR . 'avatars';
        if (!is_dir($avatarDir)) { @mkdir($avatarDir, 0775, true); }
        $fileName = 'u' . (int)$me['id'] . '_' . time() . '.' . $ext;
        $dest = $avatarDir . DIRECTORY_SEPARATOR . $fileName;
        if (!@move_uploaded_file($tmpPath, $dest)) { throw new RuntimeException('انتقال فایل ناموفق بود'); }

        // Store relative path for web
        $webPath = 'uploads/images/avatars/' . $fileName;
        $me['avatar'] = $webPath;
        save_user($me);

        echo json_encode(['ok' => true, 'avatar' => $webPath], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Follow/Unfollow user
    if ($method === 'POST' && $action === 'follow') {
        require_login();
        ensure_csrf_token();
        $me = current_user();
        if (!$me) { throw new RuntimeException('نیاز به ورود دارید'); }

        $targetUserId = (int)($_POST['user_id'] ?? 0);
        if ($targetUserId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }

        $targetUser = get_user_by_id($targetUserId);
        if (!$targetUser) {
            throw new RuntimeException('کاربر مورد نظر یافت نشد');
        }

        $success = follow_user((int)$me['id'], $targetUserId);
        if (!$success) {
            throw new RuntimeException('در حال حاضر این کاربر را دنبال می‌کنید');
        }

        echo json_encode(['ok' => true, 'message' => 'کاربر با موفقیت دنبال شد'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if ($method === 'POST' && $action === 'unfollow') {
        require_login();
        ensure_csrf_token();
        $me = current_user();
        if (!$me) { throw new RuntimeException('نیاز به ورود دارید'); }

        $targetUserId = (int)($_POST['user_id'] ?? 0);
        if ($targetUserId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }

        $targetUser = get_user_by_id($targetUserId);
        if (!$targetUser) {
            throw new RuntimeException('کاربر مورد نظر یافت نشد');
        }

        $success = unfollow_user((int)$me['id'], $targetUserId);
        if (!$success) {
            throw new RuntimeException('این کاربر را دنبال نمی‌کنید');
        }

        echo json_encode(['ok' => true, 'message' => 'دنبال کردن لغو شد'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Search users (for finding profiles with Persian names)
    if ($method === 'GET' && $action === 'search') {
        $query = trim($_GET['q'] ?? '');
        $limit = min(20, max(1, (int)($_GET['limit'] ?? 10)));
        
        if (mb_strlen($query, 'UTF-8') < 2) {
            echo json_encode(['ok' => false, 'error' => 'جستجو باید حداقل 2 کاراکتر باشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $users = JsonDB::read('users') ?? [];
        $results = [];
        
        foreach ($users as $user) {
            $username = trim((string)($user['username'] ?? ''));
            $name = trim((string)($user['name'] ?? ''));
            $email = trim((string)($user['email'] ?? ''));
            
            // Multi-field search with Persian support
            $searchFields = [$username, $name];
            $matched = false;
            
            foreach ($searchFields as $field) {
                if (
                    (mb_strpos(mb_strtolower($field, 'UTF-8'), mb_strtolower($query, 'UTF-8')) !== false) ||
                    (stripos($field, $query) !== false)
                ) {
                    $matched = true;
                    break;
                }
            }
            
            if ($matched) {
                $userResult = [
                    'id' => (int)$user['id'],
                    'username' => $username,
                    'name' => $name,
                    'avatar' => $user['avatar'] ?? null,
                    'verified' => !empty($user['verified']),
                    'role' => $user['role'] ?? 'user'
                ];
                $results[] = $userResult;
                
                if (count($results) >= $limit) {
                    break;
                }
            }
        }
        
        echo json_encode(['ok' => true, 'users' => $results], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $username = $_GET['user'] ?? '';
    
    if (empty($username)) {
        http_response_code(400);
        echo json_encode(['error' => 'نام کاربری مشخص نشده است']);
        exit;
    }
    
    // Get user data
    $users = JsonDB::read('users') ?? [];
    $user = null;
    foreach ($users as $u) {
        if ($u['username'] === $username) {
            $user = $u;
            break;
        }
    }
    
    if (!$user) {
        http_response_code(404);
        echo json_encode(['error' => 'کاربر یافت نشد']);
        exit;
    }
    
    // Get user's projects
    $submissions = JsonDB::read('submissions') ?? [];
    $userProjects = array_filter($submissions, function($project) use ($username) {
        return ($project['author'] ?? '') === $username && ($project['status'] ?? '') === 'approved';
    });
    
    // Sort by newest first
    usort($userProjects, function($a, $b) {
        return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
    });
    
    // Calculate user stats
    $totalProjects = count($userProjects);
    $totalDownloads = array_sum(array_column($userProjects, 'downloads'));
    $totalStars = array_sum(array_column($userProjects, 'stars'));
    
    // Calculate total file size in MB and KB
    $totalSizeMB = 0;
    $totalSizeKB = 0;
    foreach ($userProjects as $project) {
        $fileSize = $project['file_size'] ?? 0;
        $totalSizeMB += ($fileSize / (1024 * 1024)); // Convert bytes to MB
        $totalSizeKB += ($fileSize / 1024); // Convert bytes to KB
    }
    
    // Get language stats
    $languageStats = [];
    foreach ($userProjects as $project) {
        $lang = $project['language'] ?? 'نامشخص';
        $languageStats[$lang] = ($languageStats[$lang] ?? 0) + 1;
    }
    arsort($languageStats);
    
    // Prepare projects data with limited info for API
    $projectsData = array_map(function($project) {
        return [
            'id' => $project['id'] ?? '',
            'title' => $project['title'] ?? '',
            'description' => substr($project['description'] ?? '', 0, 150),
            'language' => $project['language'] ?? 'نامشخص',
            'downloads' => (int)($project['downloads'] ?? 0),
            'stars' => (int)($project['stars'] ?? 0),
            'views' => (int)($project['views'] ?? 0),
            'fileSize' => (int)($project['file_size'] ?? 0),
            'fileSizeMB' => round(($project['file_size'] ?? 0) / (1024 * 1024), 2),
            'fileSizeKB' => round(($project['file_size'] ?? 0) / 1024, 2),
            'extension' => pathinfo($project['fileName'] ?? '', PATHINFO_EXTENSION),
            'createdAt' => $project['created_at'] ?? '',
            'createdAtFormatted' => date('Y/m/d', strtotime($project['created_at'] ?? 'now'))
        ];
    }, $userProjects);
    
    // Prepare response data
    $response = [
        'success' => true,
        'user' => [
            'username' => $user['username'] ?? '',
            'email' => isset($user['email']) ? substr($user['email'], 0, 3) . '***' . substr($user['email'], -10) : '', // Partial email for privacy
            'joinDate' => $user['created_at'] ?? '',
            'joinDateFormatted' => date('Y/m/d', strtotime($user['created_at'] ?? 'now'))
        ],
        'stats' => [
            'totalProjects' => $totalProjects,
            'totalDownloads' => $totalDownloads,
            'totalStars' => $totalStars,
            'totalSizeMB' => round($totalSizeMB, 2),
            'totalSizeKB' => round($totalSizeKB, 2)
        ],
        'languages' => $languageStats,
        'projects' => $projectsData,
        'projectsCount' => count($projectsData)
    ];
    
    echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'خطای سرور',
        'message' => 'مشکلی در پردازش درخواست رخ داده است'
    ], JSON_UNESCAPED_UNICODE);
}

// Handle verification celebration mark
if ($method === 'POST' && $action === 'mark_verification_celebrated') {
    require_login();
    ensure_csrf_token();
    $me = current_user();
    if (!$me) { 
        echo json_encode(['ok' => false, 'error' => 'نیاز به ورود دارید'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $me['verification_celebrated'] = true;
    save_user($me);

    echo json_encode(['ok' => true, 'message' => 'تبریک نشان داده شد'], JSON_UNESCAPED_UNICODE);
    exit;
}

// Get avatar options
if ($method === 'GET' && $action === 'get_avatar_options') {
    $avatarOptions = get_avatar_options();
    echo json_encode(['ok' => true, 'avatars' => $avatarOptions], JSON_UNESCAPED_UNICODE);
    exit;
}

// Update user avatar
if ($method === 'POST' && $action === 'update_avatar') {
    require_login();
    ensure_csrf_token();
    $me = current_user();
    if (!$me) { 
        echo json_encode(['ok' => false, 'error' => 'نیاز به ورود دارید'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $avatarKey = trim($_POST['avatar'] ?? '');
    if (empty($avatarKey)) {
        echo json_encode(['ok' => false, 'error' => 'آواتار انتخاب نشده'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $success = update_user_avatar((int)$me['id'], $avatarKey);
    if ($success) {
        echo json_encode(['ok' => true, 'message' => 'آواتار بروزرسانی شد'], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['ok' => false, 'error' => 'خطا در بروزرسانی آواتار'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}
?>
