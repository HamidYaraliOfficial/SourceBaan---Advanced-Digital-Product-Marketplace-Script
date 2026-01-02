<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Prefer a human-friendly display name: username > name > email local-part > fallback
function user_display_name(array $user): string {
	$username = trim((string)($user['username'] ?? ''));
	if ($username !== '') return $username;
	$name = trim((string)($user['name'] ?? ''));
	if ($name !== '') return $name;
	$email = (string)($user['email'] ?? '');
	if ($email !== '') return explode('@', $email)[0];
	return 'کاربر';
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    // Only logged-in users can use live chat
    $user = current_user();
    if (!$user) {
        echo json_encode(['ok' => false, 'error' => 'برای چت با کارشناسان ابتدا وارد حساب کاربری خود شوید']);
        exit;
    }

    switch ($action) {
        case 'start_chat':
            handle_start_chat($user);
            break;
            
        case 'send_message':
            handle_send_message($user);
            break;
            
        case 'get_messages':
            handle_get_messages($user);
            break;
            
        case 'end_chat':
            handle_end_chat($user);
            break;
            
        case 'get_chat_status':
            handle_get_chat_status($user);
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
    }

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}

function handle_start_chat(array $user): void {
    ensure_csrf_token();

    $chats = JsonDB::read('live_chats');
    $userId = (int)$user['id'];
    
    // Check if user already has an active chat
    $existingChat = array_filter($chats, function($chat) use ($userId) {
        return (int)($chat['user_id'] ?? 0) === $userId && ($chat['status'] ?? '') === 'active';
    });
    
    if (!empty($existingChat)) {
        $chatId = array_keys($existingChat)[0];
        echo json_encode(['ok' => true, 'chat_id' => $chatId, 'message' => 'چت فعال شما ادامه یافت']);
        return;
    }
    
    // Create new chat
    $chatId = 'chat_' . time() . '_' . $userId;
    $newChat = [
        'id' => $chatId,
        'user_id' => $userId,
        'username' => user_display_name($user),
        'status' => 'waiting', // waiting, active, ended
        'admin_id' => null,
        'created_at' => time(),
        'updated_at' => time(),
        'messages' => []
    ];
    
    $chats[$chatId] = $newChat;
    JsonDB::write('live_chats', $chats);
    
    // Add initial system message
    $systemMessage = [
        'id' => 'msg_' . time(),
        'sender_type' => 'system',
        'sender_id' => 0,
        'message' => 'چت شما با کارشناسان آغاز شد. لطفاً تا پاسخ کارشناس صبر کنید...',
        'timestamp' => time()
    ];
    
    $chats[$chatId]['messages'][] = $systemMessage;
    
    // Auto-send user info if requested
    if (!empty($_POST['send_user_info'])) {
        $projects = JsonDB::read('projects');
        // In our schema, projects authored by a user use authorId
        $userProjects = array_filter($projects, fn($p) => (int)($p['authorId'] ?? 0) === $userId);
        $totalDownloads = array_sum(array_map(fn($p) => (int)($p['downloads'] ?? 0), $userProjects));
        $totalStars = array_sum(array_map(fn($p) => (int)($p['stars'] ?? 0), $userProjects));
        
        $userInfo = "📋 اطلاعات کاربر:\n";
        $userInfo .= "👤 نام کاربری: " . user_display_name($user) . "\n";
        $userInfo .= "📧 ایمیل: " . ($user['email'] ?? 'نامشخص') . "\n";
        $userInfo .= "📊 تعداد پروژه‌ها: " . count($userProjects) . "\n";
        $userInfo .= "⬇️ کل دانلودها: " . $totalDownloads . "\n";
        $userInfo .= "⭐ کل ستاره‌ها: " . $totalStars . "\n";
        // createdAt in users is ISO 8601 per includes/auth.php (register_user/seed_default_admin)
        $createdRaw = (string)($user['createdAt'] ?? ($user['created_at'] ?? ''));
        $createdTs = $createdRaw !== '' ? strtotime($createdRaw) : time();
        $userInfo .= "📅 عضویت: " . date('Y/m/d H:i', $createdTs);
        
        $userInfoMessage = [
            'id' => 'msg_' . (time() + 1),
            'sender_type' => 'system',
            'sender_id' => 0,
            'message' => $userInfo,
            'timestamp' => time() + 1,
            'is_user_info' => true
        ];
        
        $chats[$chatId]['messages'][] = $userInfoMessage;
    }
    
    JsonDB::write('live_chats', $chats);
    
    echo json_encode([
        'ok' => true, 
        'chat_id' => $chatId, 
        'message' => 'چت با کارشناسان آغاز شد',
        'status' => 'waiting'
    ]);
}

function handle_send_message(array $user): void {
    ensure_csrf_token();

    $chatId = trim($_POST['chat_id'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $hasFile = !empty($_FILES['file']['name']);
    
    if (empty($chatId) || (empty($message) && !$hasFile)) {
        echo json_encode(['ok' => false, 'error' => 'پیام یا فایل الزامی است']);
        return;
    }
    
    if (mb_strlen($message) > 1000) {
        echo json_encode(['ok' => false, 'error' => 'پیام خیلی طولانی است']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    $userId = (int)$user['id'];
    
    if (!isset($chats[$chatId]) || (int)($chats[$chatId]['user_id'] ?? 0) !== $userId) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    if (($chats[$chatId]['status'] ?? '') === 'ended') {
        echo json_encode(['ok' => false, 'error' => 'این چت پایان یافته است']);
        return;
    }
    
    $fileUrl = null;
    $fileName = null;
    
    // Handle file upload
    if ($hasFile) {
        $uploadDir = __DIR__ . '/../uploads/chat_files/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $file = $_FILES['file'];
        $fileName = $file['name'];
        $fileSize = $file['size'];
        $tmpName = $file['tmp_name'];
        
        // Validate file
        if ($fileSize > 10 * 1024 * 1024) { // 10MB limit
            echo json_encode(['ok' => false, 'error' => 'حجم فایل نباید بیشتر از 10240 کیلوبایت باشد']);
            return;
        }
        
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'txt', 'zip', 'rar'];
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (!in_array($fileExt, $allowedExts)) {
            echo json_encode(['ok' => false, 'error' => 'نوع فایل مجاز نیست']);
            return;
        }
        
        // Generate unique filename
        $uniqueName = time() . '_' . $userId . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
        $filePath = $uploadDir . $uniqueName;
        
        if (move_uploaded_file($tmpName, $filePath)) {
            $fileUrl = 'uploads/chat_files/' . $uniqueName;
        } else {
            echo json_encode(['ok' => false, 'error' => 'خطا در آپلود فایل']);
            return;
        }
    }
    
    // Add user message
    $newMessage = [
        'id' => 'msg_' . time() . '_' . rand(1000, 9999),
        'sender_type' => 'user',
        'sender_id' => $userId,
        'sender_name' => user_display_name($user),
        'message' => $message ?: "📎 فایل ارسال شد: {$fileName}",
        'timestamp' => time(),
        'file_url' => $fileUrl,
        'file_name' => $fileName
    ];
    
    $chats[$chatId]['messages'][] = $newMessage;
    $chats[$chatId]['updated_at'] = time();
    
    // If chat was waiting, mark as active
    if ($chats[$chatId]['status'] === 'waiting') {
        $chats[$chatId]['status'] = 'active';
    }
    
    JsonDB::write('live_chats', $chats);
    
    echo json_encode(['ok' => true, 'message' => 'پیام ارسال شد']);
}

function handle_get_messages(array $user): void {
    $chatId = trim($_GET['chat_id'] ?? '');
    
    if (empty($chatId)) {
        echo json_encode(['ok' => false, 'error' => 'شناسه چت مشخص نشده']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    $userId = (int)$user['id'];
    
    if (!isset($chats[$chatId]) || (int)($chats[$chatId]['user_id'] ?? 0) !== $userId) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    $chat = $chats[$chatId];
    $messages = $chat['messages'] ?? [];
    
    echo json_encode([
        'ok' => true, 
        'messages' => $messages,
        'status' => $chat['status'] ?? 'ended',
        'admin_name' => $chat['admin_name'] ?? null,
        'admin_id' => $chat['admin_id'] ?? null,
        'username' => $chat['username'] ?? null
    ]);
}

function handle_end_chat(array $user): void {
    ensure_csrf_token();

    $chatId = trim($_POST['chat_id'] ?? '');
    
    if (empty($chatId)) {
        echo json_encode(['ok' => false, 'error' => 'شناسه چت مشخص نشده']);
        return;
    }
    
    $chats = JsonDB::read('live_chats');
    $userId = (int)$user['id'];
    
    if (!isset($chats[$chatId]) || (int)($chats[$chatId]['user_id'] ?? 0) !== $userId) {
        echo json_encode(['ok' => false, 'error' => 'چت یافت نشد']);
        return;
    }
    
    $chats[$chatId]['status'] = 'ended';
    $chats[$chatId]['ended_at'] = time();
    $chats[$chatId]['ended_by'] = 'user';
    
    // Add system message
    $systemMessage = [
        'id' => 'msg_' . time(),
        'sender_type' => 'system',
        'sender_id' => 0,
        'message' => 'چت توسط کاربر پایان یافت',
        'timestamp' => time()
    ];
    
    $chats[$chatId]['messages'][] = $systemMessage;
    JsonDB::write('live_chats', $chats);
    
    echo json_encode(['ok' => true, 'message' => 'چت پایان یافت']);
}

function handle_get_chat_status(array $user): void {
    $chats = JsonDB::read('live_chats');
    $userId = (int)$user['id'];
    
    // Find user's active chat
    $activeChat = null;
    foreach ($chats as $chatId => $chat) {
        if ((int)($chat['user_id'] ?? 0) === $userId && in_array($chat['status'] ?? '', ['waiting', 'active'])) {
            $activeChat = [
                'chat_id' => $chatId,
                'status' => $chat['status'],
                'admin_name' => $chat['admin_name'] ?? null,
                'created_at' => $chat['created_at'] ?? 0
            ];
            break;
        }
    }
    
    echo json_encode([
        'ok' => true, 
        'has_active_chat' => $activeChat !== null,
        'chat' => $activeChat
    ]);
}

?>
