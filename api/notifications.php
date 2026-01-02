<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$action = $_GET['action'] ?? '';
$user = current_user();

try {
    if ($action === 'get') {
        $notifications = JsonDB::read('notifications');
        
        // فیلتر کردن نوتیفیکیشن‌های مربوط به کاربر
        $userNotifications = array_filter($notifications, function($notif) use ($user) {
            return isset($notif['broadcast']) || (isset($notif['userId']) && (int)$notif['userId'] === (int)$user['id']);
        });
        
        // مرتب کردن بر اساس تاریخ
        usort($userNotifications, function($a, $b) {
            return strtotime($b['createdAt']) - strtotime($a['createdAt']);
        });
        
        // فقط 20 نوتیفیکیشن آخر
        $userNotifications = array_slice($userNotifications, 0, 20);
        
        json_response(['ok' => true, 'notifications' => $userNotifications]);
    }
    
    if ($action === 'mark_read') {
        ensure_post();
        $notifId = (int)($_POST['id'] ?? 0);
        
        if ($notifId > 0) {
            $notifications = JsonDB::read('notifications');
            
            foreach ($notifications as $i => $notif) {
                if ((int)$notif['id'] === $notifId && 
                    isset($notif['userId']) && 
                    (int)$notif['userId'] === (int)$user['id']) {
                    $notifications[$i]['read'] = true;
                    break;
                }
            }
            
            JsonDB::write('notifications', $notifications);
        }
        
        json_response(['ok' => true]);
    }
    
    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
    
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
?>
