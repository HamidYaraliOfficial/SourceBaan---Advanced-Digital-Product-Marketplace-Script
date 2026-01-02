<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Includes
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/utils.php';
    require_once __DIR__ . '/../includes/content-filter.php';
    
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';

    // Helper: read chat closed setting
    $getChatClosed = function(): bool {
        $settings = JsonDB::read('settings');
        foreach ($settings as $setting) {
            if (($setting['key'] ?? '') === 'chat_closed') {
                return ($setting['value'] ?? 'false') === 'true';
            }
        }
        return false;
    };

    // GET: messages list
    if ($method === 'GET' && $action === '') {
        $messages = JsonDB::read('chat_messages');
        $activeMessages = [];
        foreach ($messages as $message) {
            if (($message['status'] ?? 'active') === 'active') {
                $activeMessages[] = $message;
            }
        }
        // last 20
        $recentMessages = array_slice($activeMessages, -20);
        echo json_encode(['success' => true, 'messages' => $recentMessages], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // GET: status
    if ($action === 'status') {
        echo json_encode([
            'success' => true,
            'closed' => $getChatClosed(),
            'is_admin' => is_admin(),
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // POST actions
    if ($method === 'POST') {
        // Toggle chat open/close (admin only)
        if ($action === 'toggle') {
            if (!is_admin()) {
                echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $settings = JsonDB::read('settings');
            $updated = false;
            foreach ($settings as $i => $setting) {
                if (($setting['key'] ?? '') === 'chat_closed') {
                    $current = ($setting['value'] ?? 'false') === 'true';
                    $settings[$i]['value'] = $current ? 'false' : 'true';
                    $settings[$i]['updated_at'] = date('c');
                    $updated = true;
                    break;
                }
            }
            if (!$updated) {
                $settings[] = ['key' => 'chat_closed', 'value' => 'true', 'updated_at' => date('c')];
            }
            JsonDB::write('settings', $settings);
            $closed = $getChatClosed();
            echo json_encode(['success' => true, 'closed' => $closed, 'message' => $closed ? 'چت بسته شد' : 'چت باز شد'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Delete chat message (admin only)
        if ($action === 'delete') {
            if (!is_admin()) {
                echo json_encode(['success' => false, 'error' => 'دسترسی مجاز نیست'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $messageId = (int)($_POST['messageId'] ?? 0);
            if ($messageId <= 0) {
                echo json_encode(['success' => false, 'error' => 'شناسه پیام نامعتبر است'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            $messages = JsonDB::read('chat_messages');
            $found = false;
            foreach ($messages as $i => $msg) {
                if ((int)($msg['id'] ?? 0) === $messageId) {
                    $messages[$i]['status'] = 'deleted';
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                echo json_encode(['success' => false, 'error' => 'پیام یافت نشد'], JSON_UNESCAPED_UNICODE);
                exit;
            }
            JsonDB::write('chat_messages', $messages);
            echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Send chat message (JSON body)
        $raw = file_get_contents('php://input') ?: '';
        $payload = json_decode($raw, true);
        $message = '';
        if (is_array($payload)) {
            $message = trim((string)($payload['message'] ?? ''));
        }
        
        // Fallback to form field if provided
        if ($message === '') {
            $message = trim((string)($_POST['message'] ?? ''));
        }

        if ($message === '') {
            echo json_encode(['success' => false, 'error' => 'متن پیام الزامی است'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if (!current_user()) {
            echo json_encode(['success' => false, 'error' => 'برای ارسال پیام وارد شوید'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        if ($getChatClosed() && !is_admin()) {
            echo json_encode(['success' => false, 'error' => 'چت بسته است'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        // Content filtering
        $user = current_user();
        $filter = ContentFilter::filterText($message);
        if (!$filter['passed']) {
            ContentFilter::logContentIssue((int)$user['id'], $message, $filter['issues']);
            echo json_encode(['success' => false, 'error' => 'پیام حاوی محتوای نامناسب است'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $newMessage = [
            'id' => JsonDB::nextId(),
            'user_id' => (int)$user['id'],
            'user_name' => (string)$user['name'],
            'message' => (string)$filter['cleanText'],
            'created_at' => date('c'),
            'status' => 'active',
        ];

        JsonDB::upsert('chat_messages', function(array $items) use ($newMessage) {
            $items[] = $newMessage;
            // Keep only last 200 messages to limit file size
            if (count($items) > 200) {
                $items = array_slice($items, -200);
            }
            return $items;
        });

        echo json_encode(['success' => true, 'message' => $newMessage], JSON_UNESCAPED_UNICODE);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>