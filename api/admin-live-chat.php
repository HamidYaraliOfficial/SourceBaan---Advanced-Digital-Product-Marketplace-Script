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
    
    // Only admins can use this API
    $user = current_user();
    if (!$user || ($user['role'] ?? '') !== 'admin') {
        echo json_encode(['ok' => false, 'error' => 'دسترسی مجاز نیست']);
        exit;
    }

    switch ($action) {
        case 'get_messages':
            handle_get_messages_admin($user);
            break;
        case 'join_chat':
            handle_join_chat($user);
            break;
            
        case 'send_admin_message':
            handle_send_admin_message($user);
            break;
            
        case 'end_chat_admin':
            handle_end_chat_admin($user);
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
    }

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}

function handle_get_messages_admin(array $user): void {
    $chatId = trim($_GET['chat_id'] ?? '');
    if ($chatId === '') { echo json_encode(['ok' => false, 'error' => 'شناسه چت مشخص نشده']); return; }
    $chats = JsonDB::read('live_chats');
    if (!isset($chats[$chatId])) { echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']); return; }
    $chat = $chats[$chatId];
    echo json_encode([
        'ok' => true,
        'messages' => $chat['messages'] ?? [],
        'status' => $chat['status'] ?? 'ended',
        'username' => $chat['username'] ?? 'کاربر',
        'admin_id' => $chat['admin_id'] ?? null,
        'admin_name' => $chat['admin_name'] ?? null,
    ]);
}

function handle_join_chat(array $user): void {
    ensure_csrf_token();

    $chatId = trim($_POST['chat_id'] ?? '');
    $adminId = (int)($_POST['admin_id'] ?? 0);
    $adminName = trim($_POST['admin_name'] ?? '');
    
    if (empty($chatId) || $adminId !== (int)$user['id']) {
        echo json_encode(['ok' => false, 'error' => 'اطلاعات نامعتبر است']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    
    if (!isset($chats[$chatId])) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    if (($chats[$chatId]['status'] ?? '') === 'ended') {
        echo json_encode(['ok' => false, 'error' => 'این چت پایان یافته است']);
        return;
    }
    
    // Check if another admin is already assigned
    if (isset($chats[$chatId]['admin_id']) && (int)$chats[$chatId]['admin_id'] !== $adminId) {
        echo json_encode(['ok' => false, 'error' => 'کارشناس دیگری به این چت پیوسته است']);
        return;
    }
    
    // Assign admin to chat
    $chats[$chatId]['admin_id'] = $adminId;
    $chats[$chatId]['admin_name'] = $adminName;
    $chats[$chatId]['status'] = 'active';
    $chats[$chatId]['updated_at'] = time();
    
    // Add system message
    $systemMessage = [
        'id' => 'msg_' . time() . '_admin_join',
        'sender_type' => 'system',
        'sender_id' => 0,
        'message' => "کارشناس {$adminName} به چت پیوست",
        'timestamp' => time()
    ];
    
    $chats[$chatId]['messages'][] = $systemMessage;
    JsonDB::write('live_chats', $chats);
    
    echo json_encode(['ok' => true, 'message' => 'با موفقیت به چت پیوستید']);
}

function handle_send_admin_message(array $user): void {
    ensure_csrf_token();

    $chatId = trim($_POST['chat_id'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $adminId = (int)($_POST['admin_id'] ?? 0);
    $adminName = trim($_POST['admin_name'] ?? '');
    
    if (empty($chatId) || empty($message) || $adminId !== (int)$user['id']) {
        echo json_encode(['ok' => false, 'error' => 'اطلاعات ناقص است']);
        return;
    }
    
    if (mb_strlen($message) > 1000) {
        echo json_encode(['ok' => false, 'error' => 'پیام خیلی طولانی است']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    
    if (!isset($chats[$chatId])) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    if (($chats[$chatId]['status'] ?? '') === 'ended') {
        echo json_encode(['ok' => false, 'error' => 'این چت پایان یافته است']);
        return;
    }
    
    // Verify admin is assigned to this chat
    if ((int)($chats[$chatId]['admin_id'] ?? 0) !== $adminId) {
        echo json_encode(['ok' => false, 'error' => 'شما مجاز به پاسخ در این چت نیستید']);
        return;
    }
    
    // Add admin message
    $adminMessage = [
        'id' => 'msg_' . time() . '_' . rand(1000, 9999),
        'sender_type' => 'admin',
        'sender_id' => $adminId,
        'sender_name' => $adminName,
        'message' => $message,
        'timestamp' => time()
    ];
    
    $chats[$chatId]['messages'][] = $adminMessage;
    $chats[$chatId]['updated_at'] = time();
    
    JsonDB::write('live_chats', $chats);
    
    echo json_encode(['ok' => true, 'message' => 'پیام ارسال شد']);
}

function handle_end_chat_admin(array $user): void {
    ensure_csrf_token();

    $chatId = trim($_POST['chat_id'] ?? '');
    $adminId = (int)($_POST['admin_id'] ?? 0);
    
    if (empty($chatId) || $adminId !== (int)$user['id']) {
        echo json_encode(['ok' => false, 'error' => 'اطلاعات نامعتبر است']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    
    if (!isset($chats[$chatId])) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    if (($chats[$chatId]['status'] ?? '') === 'ended') {
        echo json_encode(['ok' => false, 'error' => 'این چت قبلاً پایان یافته است']);
        return;
    }
    
    $chats[$chatId]['status'] = 'ended';
    $chats[$chatId]['ended_at'] = time();
    $chats[$chatId]['ended_by'] = 'admin';
    $chats[$chatId]['ended_by_admin_id'] = $adminId;
    
    // Add system message
    $systemMessage = [
        'id' => 'msg_' . time(),
        'sender_type' => 'system',
        'sender_id' => 0,
        'message' => 'چت توسط کارشناس پایان یافت',
        'timestamp' => time()
    ];
    
    $chats[$chatId]['messages'][] = $systemMessage;
    JsonDB::write('live_chats', $chats);
    
    echo json_encode(['ok' => true, 'message' => 'چت پایان یافت']);
}

?>
