<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/activity.php';

if (!is_admin()) {
    json_response(['ok' => false, 'error' => 'اجازه دسترسی ندارید'], 403);
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // مدیریت کاربران
    if ($action === 'users') {
        $users = JsonDB::read('users');
        $enrichedUsers = array_map(function($user) {
            $projects = JsonDB::read('projects');
            $userProjects = array_filter($projects, fn($p) => (int)($p['authorId'] ?? 0) === (int)$user['id']);
            
            unset($user['password_hash']);
            $user['projectCount'] = count($userProjects);
            $user['totalDownloads'] = array_sum(array_column($userProjects, 'downloads'));
            $user['totalStars'] = array_sum(array_column($userProjects, 'stars'));
            $user['lastIp'] = $user['lastIp'] ?? '';
            
            return $user;
        }, $users);
        
        json_response(['ok' => true, 'users' => $enrichedUsers]);
    }
    
    // اضافه کردن/کم کردن امتیاز کاربر
    if ($action === 'adjust_points') {
        ensure_post();
        $userId = (int)($_POST['userId'] ?? 0);
        $points = (int)($_POST['points'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        
        if ($userId <= 0) throw new RuntimeException('شناسه کاربر نامعتبر است');
        
        $users = JsonDB::read('users');
        $userFound = false;
        
        foreach ($users as $i => $user) {
            if ((int)$user['id'] === $userId) {
                $oldPoints = (int)($user['points'] ?? 0);
                $users[$i]['points'] = max(0, $oldPoints + $points);
                $userFound = true;
                
                // ثبت فعالیت
                $action_type = $points > 0 ? 'اعطای امتیاز' : 'کسر امتیاز';
                add_activity("$action_type به {$user['name']}: " . abs($points) . " امتیاز" . ($reason ? " - $reason" : ""), 'points');
                
                break;
            }
        }
        
        if (!$userFound) throw new RuntimeException('کاربر یافت نشد');
        
        JsonDB::write('users', $users);
        json_response(['ok' => true, 'message' => 'امتیاز با موفقیت تغییر یافت']);
    }
    
    // ارتقا/تنزل نقش کاربر
    if ($action === 'change_role') {
        ensure_post();
        $userId = (int)($_POST['userId'] ?? 0);
        $newRole = trim($_POST['role'] ?? '');
        
        if ($userId <= 0) throw new RuntimeException('شناسه کاربر نامعتبر است');
        if (!in_array($newRole, ['user', 'admin'], true)) throw new RuntimeException('نقش نامعتبر است');
        
        $users = JsonDB::read('users');
        $userFound = false;
        
        foreach ($users as $i => $user) {
            if ((int)$user['id'] === $userId) {
                $oldRole = $user['role'] ?? 'user';
                $users[$i]['role'] = $newRole;
                $userFound = true;
                
                add_activity("تغییر نقش {$user['name']} از $oldRole به $newRole", 'role_change');
                break;
            }
        }
        
        if (!$userFound) throw new RuntimeException('کاربر یافت نشد');
        
        JsonDB::write('users', $users);
        json_response(['ok' => true, 'message' => 'نقش کاربر با موفقیت تغییر یافت']);
    }

    // Ban user (ban by last IP)
    if ($action === 'ban_user') {
        ensure_post();
        $userId = (int)($_POST['userId'] ?? 0);
        if ($userId <= 0) throw new RuntimeException('شناسه کاربر نامعتبر است');
        $user = get_user_by_id($userId);
        if (!$user) throw new RuntimeException('کاربر یافت نشد');
        $ip = $user['lastIp'] ?? '';
        if (!$ip || !filter_var($ip, FILTER_VALIDATE_IP)) throw new RuntimeException('آی‌پی کاربر نامعتبر/ناموجود است');
        ban_ip($ip, 'Banned by admin for userId=' . $userId);
        json_response(['ok' => true, 'message' => 'کاربر و آی‌پی او مسدود شد']);
    }

    // Toggle verified badge for a user
    if ($action === 'toggle_verified') {
        ensure_post();
        $userId = (int)($_POST['userId'] ?? 0);
        if ($userId <= 0) throw new RuntimeException('شناسه کاربر نامعتبر است');

        $users = JsonDB::read('users');
        $found = false;
        foreach ($users as $i => $user) {
            if ((int)($user['id'] ?? 0) === $userId) {
                $current = (bool)($user['verified'] ?? false);
                $users[$i]['verified'] = !$current;
                $found = true;
                add_activity((!$current ? 'تایید' : 'لغو تایید') . " حساب {$user['name']}", 'user_verified');
                break;
            }
        }

        if (!$found) throw new RuntimeException('کاربر یافت نشد');
        JsonDB::write('users', $users);
        json_response(['ok' => true, 'message' => 'وضعیت تایید کاربر بروز شد']);
    }
    
    // ارسال نوتیفیکیشن به همه کاربران
    if ($action === 'broadcast_notification') {
        ensure_post();
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = trim($_POST['type'] ?? 'info');
        
        if (!$title || !$message) throw new RuntimeException('عنوان و پیام الزامی است');
        if (!in_array($type, ['info', 'success', 'warning', 'error'], true)) $type = 'info';
        
        $notification = [
            'id' => JsonDB::nextId(),
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'createdAt' => date('c'),
            'broadcast' => true,
        ];
        
        JsonDB::upsert('notifications', function($notifications) use ($notification) {
            $notifications[] = $notification;
            // نگه داشتن فقط 100 نوتیفیکیشن آخر
            if (count($notifications) > 100) {
                $notifications = array_slice($notifications, -100);
            }
            return $notifications;
        });
        
        add_activity("نوتیفیکیشن عمومی ارسال شد: $title", 'broadcast');
        json_response(['ok' => true, 'message' => 'نوتیفیکیشن به همه کاربران ارسال شد']);
    }
    
    // ارسال نوتیفیکیشن به کاربر خاص
    if ($action === 'send_notification') {
        ensure_post();
        $userId = (int)($_POST['userId'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = trim($_POST['type'] ?? 'info');
        
        if ($userId <= 0) throw new RuntimeException('شناسه کاربر نامعتبر است');
        if (!$title || !$message) throw new RuntimeException('عنوان و پیام الزامی است');
        
        $user = get_user_by_id($userId);
        if (!$user) throw new RuntimeException('کاربر یافت نشد');
        
        $notification = [
            'id' => JsonDB::nextId(),
            'userId' => $userId,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'createdAt' => date('c'),
            'read' => false,
        ];
        
        JsonDB::upsert('notifications', function($notifications) use ($notification) {
            $notifications[] = $notification;
            return $notifications;
        });
        
        add_activity("نوتیفیکیشن به {$user['name']} ارسال شد: $title", 'notification');
        json_response(['ok' => true, 'message' => 'نوتیفیکیشن ارسال شد']);
    }
    
    // دریافت تنظیمات سایت
    if ($action === 'get_settings') {
        $settings = JsonDB::read('settings');
        
        // تنظیمات پیش‌فرض
        $defaultSettings = [
            'siteName' => 'SourceBaan',
            'siteDescription' => 'انجمن پیشرفته سورس کد',
            'maxUploadSize' => 1048576,
            'allowedExtensions' => 'zip,rar,js,py,php,java,cpp,html,css,txt',
            'registrationEnabled' => true,
            'requireApproval' => true,
            'pointsPerUpload' => 50,
            'pointsPerDownload' => 2,
            'maintenanceMode' => false,
        ];
        
        $settings = array_merge($defaultSettings, $settings);
        json_response(['ok' => true, 'settings' => $settings]);
    }
    
    // ذخیره تنظیمات سایت
    if ($action === 'save_settings') {
        ensure_post();
        
        $settings = [
            'siteName' => trim($_POST['siteName'] ?? 'SourceBaan'),
            'siteDescription' => trim($_POST['siteDescription'] ?? 'انجمن پیشرفته سورس کد'),
            'maxUploadSize' => max(102400, (int)($_POST['maxUploadSize'] ?? 1048576)), // حداقل 100KB
            'allowedExtensions' => trim($_POST['allowedExtensions'] ?? 'zip,rar,js,py,php,java,cpp,html,css,txt'),
            'registrationEnabled' => ($_POST['registrationEnabled'] ?? 'true') === 'true',
            'requireApproval' => ($_POST['requireApproval'] ?? 'true') === 'true',
            'pointsPerUpload' => max(0, (int)($_POST['pointsPerUpload'] ?? 50)),
            'pointsPerDownload' => max(0, (int)($_POST['pointsPerDownload'] ?? 2)),
            'maintenanceMode' => ($_POST['maintenanceMode'] ?? 'false') === 'true',
            'updatedAt' => date('c'),
        ];
        
        JsonDB::write('settings', $settings);
        add_activity('تنظیمات سایت به‌روزرسانی شد', 'settings');
        
        json_response(['ok' => true, 'message' => 'تنظیمات با موفقیت ذخیره شد']);
    }
    
    // آمار پیشرفته
    if ($action === 'advanced_stats') {
        $users = JsonDB::read('users');
        $projects = JsonDB::read('projects');
        $submissions = JsonDB::read('submissions');
        $activities = JsonDB::read('activity');
        
        $stats = [
            'totalUsers' => count($users),
            'adminUsers' => count(array_filter($users, fn($u) => ($u['role'] ?? 'user') === 'admin')),
            'totalProjects' => count($projects),
            'pendingSubmissions' => count(array_filter($submissions, fn($s) => ($s['status'] ?? 'pending') === 'pending')),
            'totalDownloads' => array_sum(array_column($projects, 'downloads')),
            'totalStars' => array_sum(array_column($projects, 'stars')),
            'recentActivities' => array_slice($activities, 0, 10),
        ];
        
        // آمار زبان‌های برنامه‌نویسی
        $languages = [];
        foreach ($projects as $project) {
            $lang = $project['language'] ?? 'نامشخص';
            $languages[$lang] = ($languages[$lang] ?? 0) + 1;
        }
        arsort($languages);
        $stats['topLanguages'] = array_slice($languages, 0, 5, true);
        
        // کاربران فعال (با بیشترین پروژه)
        $userProjects = [];
        foreach ($projects as $project) {
            $authorId = $project['authorId'] ?? 0;
            $userProjects[$authorId] = ($userProjects[$authorId] ?? 0) + 1;
        }
        arsort($userProjects);
        
        $activeUsers = [];
        foreach (array_slice($userProjects, 0, 5, true) as $userId => $count) {
            $user = get_user_by_id((int)$userId);
            if ($user) {
                unset($user['password_hash']);
                $user['projectCount'] = $count;
                $activeUsers[] = $user;
            }
        }
        $stats['activeUsers'] = $activeUsers;
        
        json_response(['ok' => true, 'stats' => $stats]);
    }
    
    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
    
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
?>
