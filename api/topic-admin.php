<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/activity.php';

require_login();

$action = $_GET['action'] ?? $_POST['action'] ?? '';
$user = current_user();

// فقط ادمین‌ها اجازه دسترسی دارند
if (($user['role'] ?? '') !== 'admin') {
    json_response(['success' => false, 'error' => 'شما اجازه دسترسی ندارید'], 403);
}

try {
    // بستن/باز کردن موضوع
    if ($action === 'toggle_topic_status') {
        ensure_post();
        
        $topicId = (int)($_POST['topicId'] ?? 0);
        
        if ($topicId <= 0) {
            throw new RuntimeException('شناسه موضوع نامعتبر است');
        }
        
        $topics = JsonDB::read('topics');
        $topicIndex = -1;
        
        foreach ($topics as $i => $topic) {
            if ((int)$topic['id'] === $topicId) {
                $topicIndex = $i;
                break;
            }
        }
        
        if ($topicIndex === -1) {
            throw new RuntimeException('موضوع یافت نشد');
        }
        
        $currentStatus = $topics[$topicIndex]['status'] ?? 'open';
        $newStatus = $currentStatus === 'open' ? 'closed' : 'open';
        
        $topics[$topicIndex]['status'] = $newStatus;
        $topics[$topicIndex]['updatedAt'] = date('c');
        
        JsonDB::write('topics', $topics);
        
        $statusText = $newStatus === 'closed' ? 'بسته' : 'باز';
        add_activity("ادمین موضوع \"{$topics[$topicIndex]['title']}\" را $statusText کرد", 'admin');
        
        json_response([
            'success' => true,
            'status' => $newStatus,
            'message' => "موضوع $statusText شد"
        ]);
    }
    
    // حذف موضوع
    if ($action === 'delete_topic') {
        ensure_post();
        
        $topicId = (int)($_POST['topicId'] ?? 0);
        
        if ($topicId <= 0) {
            throw new RuntimeException('شناسه موضوع نامعتبر است');
        }
        
        $topics = JsonDB::read('topics');
        $topicIndex = -1;
        
        foreach ($topics as $i => $topic) {
            if ((int)$topic['id'] === $topicId) {
                $topicIndex = $i;
                break;
            }
        }
        
        if ($topicIndex === -1) {
            throw new RuntimeException('موضوع یافت نشد');
        }
        
        // تغییر وضعیت به deleted
        $topics[$topicIndex]['status'] = 'deleted';
        $topics[$topicIndex]['updatedAt'] = date('c');
        
        JsonDB::write('topics', $topics);
        
        // حذف پست‌های مربوطه
        $posts = JsonDB::read('posts');
        foreach ($posts as $i => $post) {
            if ((int)($post['topicId'] ?? 0) === $topicId) {
                $posts[$i]['status'] = 'deleted';
            }
        }
        JsonDB::write('posts', $posts);
        
        add_activity("ادمین موضوع \"{$topics[$topicIndex]['title']}\" را حذف کرد", 'admin');
        
        json_response(['success' => true, 'message' => 'موضوع حذف شد']);
    }
    
    // پین کردن موضوع
    if ($action === 'toggle_topic_pin') {
        ensure_post();
        
        $topicId = (int)($_POST['topicId'] ?? 0);
        
        if ($topicId <= 0) {
            throw new RuntimeException('شناسه موضوع نامعتبر است');
        }
        
        $topics = JsonDB::read('topics');
        $topicIndex = -1;
        
        foreach ($topics as $i => $topic) {
            if ((int)$topic['id'] === $topicId) {
                $topicIndex = $i;
                break;
            }
        }
        
        if ($topicIndex === -1) {
            throw new RuntimeException('موضوع یافت نشد');
        }
        
        $isPinned = ($topics[$topicIndex]['pinned'] ?? false);
        $topics[$topicIndex]['pinned'] = !$isPinned;
        $topics[$topicIndex]['updatedAt'] = date('c');
        
        JsonDB::write('topics', $topics);
        
        $actionText = !$isPinned ? 'پین' : 'آنپین';
        add_activity("ادمین موضوع \"{$topics[$topicIndex]['title']}\" را $actionText کرد", 'admin');
        
        json_response([
            'success' => true,
            'pinned' => !$isPinned,
            'message' => "موضوع {$actionText} شد"
        ]);
    }
    
    json_response(['success' => false, 'error' => 'عملیات نامعتبر'], 400);
    
} catch (Throwable $e) {
    json_response(['success' => false, 'error' => $e->getMessage()], 400);
}
?>
