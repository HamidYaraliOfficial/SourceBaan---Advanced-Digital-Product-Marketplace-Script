<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Basic includes
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    
    $users = JsonDB::read('users');
    $onlineUsers = [];
    
    // Users active in last 10 minutes
    $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
    
    foreach ($users as $user) {
        $lastActivity = $user['last_activity'] ?? $user['createdAt'] ?? '';
        if ($lastActivity && $lastActivity > $tenMinutesAgo) {
            $onlineUsers[] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $user['role'] ?? 'user'
            ];
        }
    }
    
    echo json_encode(['success' => true, 'users' => $onlineUsers], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>