<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

// Check if user is admin
$user = current_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    header('Location: ../auth.php');
    exit;
}

$chats = JsonDB::read('live_chats');
$activeChats = array_filter($chats, fn($chat) => in_array($chat['status'] ?? '', ['waiting', 'active']));
$endedChats = array_filter($chats, fn($chat) => ($chat['status'] ?? '') === 'ended');

// Sort by most recent first
uasort($activeChats, fn($a, $b) => ($b['updated_at'] ?? 0) - ($a['updated_at'] ?? 0));
uasort($endedChats, fn($a, $b) => ($b['updated_at'] ?? 0) - ($a['updated_at'] ?? 0));
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مدیریت چت‌های زنده - پنل ادمین</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            width: 6px;
        }
        .custom-scrollbar::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 3px;
        }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        
        /* Chat message styles */
        .chat-message { 
            max-width: 75%; 
            word-wrap: break-word; 
            animation: fadeInUp 0.3s ease-out;
        }
        .user-message { 
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white;
            margin-left: auto; 
        }
        .admin-message { 
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            margin-right: auto; 
        }
        .system-message { 
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            text-align: center; 
            font-size: 0.875rem; 
            margin: 0 auto;
            max-width: 90%;
        }
        
        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        
        .pulse-animation {
            animation: pulse 2s infinite;
        }
        
        /* Glass morphism effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Chat status indicators */
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        
        /* Gradient backgrounds */
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-success {
            background: linear-gradient(135deg, #84fab0 0%, #8fd3f4 100%);
        }
        
        .gradient-warning {
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-100 via-blue-50 to-indigo-100 min-h-screen">
    <div class="min-h-screen">
        <!-- Enhanced Header -->
        <header class="glass-card shadow-lg border-b sticky top-0 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center py-4">
                    <div class="flex items-center gap-4">
                        <a href="index.php" class="text-blue-600 hover:text-blue-800 transition-colors duration-200 flex items-center gap-2">
                            <i class="fas fa-arrow-right"></i> 
                            <span class="hidden sm:inline">بازگشت به پنل</span>
                        </a>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white">
                                <i class="fas fa-comments"></i>
                            </div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent">
                                مدیریت چت‌های زنده
                            </h1>
                        </div>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden sm:flex items-center gap-4 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="status-indicator bg-green-500"></div>
                                <span class="text-gray-700">فعال: <?= count($activeChats) ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                <span class="text-gray-700">پایان یافته: <?= count($endedChats) ?></span>
                            </div>
                        </div>
                        <button onclick="refreshPage()" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                            <i class="fas fa-sync-alt"></i>
                            <span class="hidden sm:inline ml-2">به‌روزرسانی</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                
                <!-- Enhanced Active Chats List -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="glass-card rounded-xl shadow-lg">
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-3">
                                <div class="status-indicator bg-green-500"></div>
                                <span>چت‌های فعال</span>
                                <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-sm font-medium"><?= count($activeChats) ?></span>
                            </h2>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-96 overflow-y-auto custom-scrollbar">
                            <?php if (empty($activeChats)): ?>
                                <div class="p-8 text-center text-gray-500">
                                    <div class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-comments text-2xl text-gray-400"></i>
                                    </div>
                                    <p class="font-medium">هیچ چت فعالی وجود ندارد</p>
                                    <p class="text-sm mt-1">زمانی که کاربران چت جدید شروع کنند، اینجا نمایش داده می‌شود</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($activeChats as $chatId => $chat): ?>
                                    <div class="p-4 hover:bg-blue-50 cursor-pointer transition-all duration-200 border-r-4 border-transparent hover:border-blue-400" onclick="openChat('<?= htmlspecialchars($chatId) ?>')">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-gradient-to-r from-blue-400 to-blue-600 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                                    <?= mb_substr($chat['username'] ?? 'ک', 0, 1) ?>
                                                </div>
                                                <div class="font-medium text-gray-900"><?= htmlspecialchars($chat['username'] ?? 'کاربر') ?></div>
                                            </div>
                                            <span class="text-xs px-3 py-1 rounded-full font-medium <?= ($chat['status'] ?? '') === 'waiting' ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700' ?>">
                                                <?= ($chat['status'] ?? '') === 'waiting' ? 'در انتظار' : 'فعال' ?>
                                            </span>
                                        </div>
                                        <div class="text-sm text-gray-600 flex items-center gap-2">
                                            <i class="fas fa-clock text-xs"></i>
                                            آخرین فعالیت: <?= date('H:i', (int)($chat['updated_at'] ?? 0)) ?>
                                        </div>
                                        <?php 
                                        $messages = $chat['messages'] ?? [];
                                        if (!empty($messages)) {
                                            $tempMessages = $messages;
                                            $lastMessage = end($tempMessages);
                                            if ($lastMessage): 
                                        ?>
                                            <div class="text-xs text-gray-500 mt-2 p-2 bg-gray-50 rounded-lg">
                                                <i class="fas fa-quote-left text-xs mr-1"></i>
                                                <?= htmlspecialchars(mb_substr($lastMessage['message'] ?? '', 0, 60)) ?><?= mb_strlen($lastMessage['message'] ?? '') > 60 ? '...' : '' ?>
                                            </div>
                                        <?php endif; } ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Enhanced Recent Ended Chats -->
                    <div class="glass-card rounded-xl shadow-lg">
                        <div class="p-6 border-b border-gray-100">
                            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-3">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                <span>چت‌های اخیر</span>
                                <span class="bg-gray-100 text-gray-700 px-2 py-1 rounded-full text-sm font-medium"><?= min(count($endedChats), 5) ?></span>
                            </h2>
                        </div>
                        <div class="divide-y divide-gray-100 max-h-64 overflow-y-auto custom-scrollbar">
                            <?php 
                            $recentEnded = array_slice($endedChats, 0, 5, true);
                            if (empty($recentEnded)): 
                            ?>
                                <div class="p-6 text-center text-gray-500">
                                    <i class="fas fa-history text-2xl mb-2"></i>
                                    <p class="text-sm">هیچ چت پایان یافته‌ای وجود ندارد</p>
                                </div>
                            <?php else: ?>
                                <?php foreach ($recentEnded as $chatId => $chat): ?>
                                    <div class="p-4 hover:bg-gray-50 cursor-pointer transition-all duration-200" onclick="openChat('<?= htmlspecialchars($chatId) ?>')">
                                        <div class="flex items-center justify-between mb-2">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 bg-gray-400 rounded-full flex items-center justify-center text-white text-sm font-semibold">
                                                    <?= mb_substr($chat['username'] ?? 'ک', 0, 1) ?>
                                                </div>
                                                <div class="font-medium text-gray-600"><?= htmlspecialchars($chat['username'] ?? 'کاربر') ?></div>
                                            </div>
                                            <span class="text-xs px-2 py-1 bg-gray-100 text-gray-600 rounded-full">پایان یافته</span>
                                        </div>
                                        <div class="text-sm text-gray-500 flex items-center gap-2">
                                            <i class="fas fa-calendar text-xs"></i>
                                            <?= date('Y/m/d H:i', (int)($chat['ended_at'] ?? ($chat['updated_at'] ?? 0))) ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Enhanced Chat Window -->
                <div class="lg:col-span-2">
                    <div id="chatWindow" class="glass-card rounded-xl shadow-lg hidden">
                        <div class="flex flex-col h-[600px]">
                            <!-- Enhanced Chat Header -->
                            <div id="chatHeader" class="p-6 border-b border-gray-100 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-t-xl">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-semibold text-lg">
                                            <span id="chatUserAvatar">ک</span>
                                        </div>
                                        <div>
                                            <h3 id="chatUserName" class="font-semibold text-gray-900 text-lg"></h3>
                                            <p id="chatInfo" class="text-sm text-gray-600 flex items-center gap-2">
                                                <i class="fas fa-circle text-xs text-green-500"></i>
                                                <span></span>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button id="joinChatBtn" onclick="joinChat()" class="bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg hover:from-green-600 hover:to-green-700 text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                            <i class="fas fa-user-plus"></i> پیوستن به چت
                                        </button>
                                        <button id="endChatBtn" onclick="endChatAsAdmin()" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-4 py-2 rounded-lg hover:from-red-600 hover:to-red-700 text-sm transition-all duration-200 shadow-md hover:shadow-lg">
                                            <i class="fas fa-times"></i> پایان چت
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Enhanced Messages -->
                            <div id="chatMessages" class="flex-1 p-6 overflow-y-auto space-y-4 custom-scrollbar bg-gradient-to-b from-gray-50 to-white">
                                <!-- Messages will be loaded here -->
                            </div>

                            <!-- Enhanced Admin Reply -->
                            <div id="adminReply" class="p-6 border-t border-gray-100 bg-gradient-to-r from-gray-50 to-blue-50 rounded-b-xl hidden">
                                <div class="flex gap-3">
                                    <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-green-600 rounded-full flex items-center justify-center text-white">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="flex-1">
                                        <input type="text" id="adminMessage" placeholder="پاسخ خود را بنویسید..." 
                                               class="w-full px-4 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 shadow-sm"
                                               onkeypress="if(event.key==='Enter') sendAdminMessage()">
                                    </div>
                                    <button onclick="sendAdminMessage()" class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-blue-600 hover:to-blue-700 transition-all duration-200 shadow-md hover:shadow-lg">
                                        <i class="fas fa-paper-plane"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Enhanced Welcome Message -->
                    <div id="welcomeMessage" class="glass-card rounded-xl shadow-lg p-12 text-center">
                        <div class="w-24 h-24 mx-auto mb-6 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center">
                            <i class="fas fa-comments text-4xl text-white"></i>
                        </div>
                        <h3 class="text-2xl font-bold bg-gradient-to-r from-gray-900 to-gray-600 bg-clip-text text-transparent mb-4">
                            مدیریت چت‌های زنده
                        </h3>
                        <p class="text-gray-600 text-lg mb-6">برای شروع، یکی از چت‌های فعال را انتخاب کنید</p>
                        <div class="flex justify-center gap-8 text-sm text-gray-500">
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-green-500 rounded-full pulse-animation"></div>
                                <span><?= count($activeChats) ?> چت فعال</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                <span><?= count($endedChats) ?> چت پایان یافته</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script data-csrf="<?= csrf_get_token() ?>">
        let currentChatId = null;
        let currentAdminId = <?= json_encode((int)$user['id']) ?>;
        let currentAdminName = <?= json_encode($user['username'] ?? 'ادمین') ?>;
        let chatRefreshInterval = null;

        function refreshPage() {
            location.reload();
        }

        function openChat(chatId) {
            currentChatId = chatId;
            document.getElementById('welcomeMessage').classList.add('hidden');
            document.getElementById('chatWindow').classList.remove('hidden');
            
            loadChatMessages();
            
            // Start auto-refresh for this chat
            if (chatRefreshInterval) clearInterval(chatRefreshInterval);
            chatRefreshInterval = setInterval(loadChatMessages, 3000);
        }

        async function loadChatMessages() {
            if (!currentChatId) return;
            
            try {
                const response = await fetch(`../api/admin-live-chat.php?action=get_messages&chat_id=${currentChatId}`);
                const data = await response.json();
                
                if (data.ok) {
                    displayMessages(data.messages);
                    updateChatHeader(data);
                }
            } catch (e) {
                console.error('Error loading messages:', e);
            }
        }

        function displayMessages(messages) {
            const container = document.getElementById('chatMessages');
            container.innerHTML = '';
            
            messages.forEach(msg => {
                const div = document.createElement('div');
                div.className = 'p-4 rounded-xl chat-message shadow-sm';
                
                if (msg.sender_type === 'system') {
                    div.className += ' system-message';
                    const isUserInfo = msg.is_user_info;
                    div.innerHTML = `
                        <div class="flex items-center justify-center gap-2 ${isUserInfo ? 'text-left' : ''}">
                            <i class="fas fa-${isUserInfo ? 'user-circle' : 'info-circle'}"></i>
                            <${isUserInfo ? 'pre' : 'span'} class="${isUserInfo ? 'whitespace-pre-wrap text-sm' : ''}">${msg.message}</${isUserInfo ? 'pre' : 'span'}>
                        </div>
                    `;
                } else if (msg.sender_type === 'user') {
                    div.className += ' user-message';
                    let fileSection = '';
                    if (msg.file_url && msg.file_name) {
                        fileSection = `
                            <div class="mt-2 p-2 bg-white/20 rounded-lg">
                                <a href="../${msg.file_url}" target="_blank" class="text-blue-100 hover:text-white flex items-center gap-2">
                                    <i class="fas fa-paperclip"></i>
                                    <span>${msg.file_name}</span>
                                    <i class="fas fa-external-link-alt text-xs"></i>
                                </a>
                            </div>`;
                    }
                    div.innerHTML = `
                        <div class="font-medium text-blue-100 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-user"></i>
                            ${msg.sender_name || 'کاربر'}
                        </div>
                        <div class="text-white">${msg.message}</div>
                        ${fileSection}
                        <div class="text-xs text-blue-100 mt-2 opacity-75">${formatTime(msg.timestamp)}</div>
                    `;
                } else if (msg.sender_type === 'admin') {
                    div.className += ' admin-message';
                    div.innerHTML = `
                        <div class="font-medium text-green-100 text-sm mb-2 flex items-center gap-2">
                            <i class="fas fa-user-tie"></i>
                            ${msg.sender_name || 'کارشناس'}
                        </div>
                        <div class="text-white">${msg.message}</div>
                        <div class="text-xs text-green-100 mt-2 opacity-75">${formatTime(msg.timestamp)}</div>
                    `;
                }
                
                container.appendChild(div);
            });
            
            container.scrollTop = container.scrollHeight;
        }

        function updateChatHeader(data) {
            document.getElementById('chatUserName').textContent = data.username || 'کاربر';
            document.getElementById('chatUserAvatar').textContent = (data.username || 'کاربر').charAt(0);
            document.getElementById('chatInfo').innerHTML = `<i class="fas fa-circle text-xs ${getStatusColor(data.status)}"></i><span>${getStatusText(data.status)}</span>`;
            
            const joinBtn = document.getElementById('joinChatBtn');
            const endBtn = document.getElementById('endChatBtn');
            const replySection = document.getElementById('adminReply');
            
            if (data.status === 'ended') {
                joinBtn.classList.add('hidden');
                endBtn.classList.add('hidden');
                replySection.classList.add('hidden');
            } else {
                endBtn.classList.remove('hidden');
                if (data.admin_id && data.admin_id == currentAdminId) {
                    joinBtn.classList.add('hidden');
                    replySection.classList.remove('hidden');
                } else if (data.admin_id) {
                    joinBtn.innerHTML = `<i class="fas fa-user-check"></i> کارشناس ${data.admin_name} در حال پاسخ است`;
                    joinBtn.disabled = true;
                    joinBtn.className = joinBtn.className.replace('from-green-500 to-green-600 hover:from-green-600 hover:to-green-700', 'from-gray-400 to-gray-500');
                    replySection.classList.add('hidden');
                } else {
                    joinBtn.classList.remove('hidden');
                    joinBtn.disabled = false;
                    joinBtn.className = joinBtn.className.replace('from-gray-400 to-gray-500', 'from-green-500 to-green-600 hover:from-green-600 hover:to-green-700');
                    joinBtn.innerHTML = '<i class="fas fa-user-plus"></i> پیوستن به چت';
                    replySection.classList.add('hidden');
                }
            }
        }

        async function joinChat() {
            if (!currentChatId) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'join_chat');
                formData.append('chat_id', currentChatId);
                formData.append('admin_id', currentAdminId);
                formData.append('admin_name', currentAdminName);
                formData.append('csrf_token', document.querySelector('script[data-csrf]').dataset.csrf);
                
                const response = await fetch('../api/admin-live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.ok) {
                    loadChatMessages(); // Refresh to show admin joined
                } else {
                    alert(data.error || 'خطا در پیوستن به چت');
                }
            } catch (e) {
                alert('خطا در اتصال به سرور');
            }
        }

        async function sendAdminMessage() {
            const messageInput = document.getElementById('adminMessage');
            const message = messageInput.value.trim();
            
            if (!message || !currentChatId) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'send_admin_message');
                formData.append('chat_id', currentChatId);
                formData.append('message', message);
                formData.append('admin_id', currentAdminId);
                formData.append('admin_name', currentAdminName);
                formData.append('csrf_token', document.querySelector('script[data-csrf]').dataset.csrf);
                
                const response = await fetch('../api/admin-live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.ok) {
                    messageInput.value = '';
                    loadChatMessages(); // Refresh to show new message
                } else {
                    alert(data.error || 'خطا در ارسال پیام');
                }
            } catch (e) {
                alert('خطا در اتصال به سرور');
            }
        }

        async function endChatAsAdmin() {
            if (!currentChatId) return;
            
            if (!confirm('آیا مطمئن هستید که می‌خواهید این چت را پایان دهید؟')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'end_chat_admin');
                formData.append('chat_id', currentChatId);
                formData.append('admin_id', currentAdminId);
                formData.append('csrf_token', document.querySelector('script[data-csrf]').dataset.csrf);
                
                const response = await fetch('../api/admin-live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.ok) {
                    loadChatMessages(); // Refresh to show chat ended
                    setTimeout(refreshPage, 2000); // Refresh page to update chat lists
                } else {
                    alert(data.error || 'خطا در پایان چت');
                }
            } catch (e) {
                alert('خطا در اتصال به سرور');
            }
        }

        function getStatusText(status) {
            switch (status) {
                case 'waiting': return 'در انتظار کارشناس';
                case 'active': return 'در حال گفتگو';
                case 'ended': return 'پایان یافته';
                default: return 'نامشخص';
            }
        }

        function getStatusColor(status) {
            switch (status) {
                case 'waiting': return 'text-yellow-500';
                case 'active': return 'text-green-500';
                case 'ended': return 'text-gray-500';
                default: return 'text-gray-500';
            }
        }

        function formatTime(timestamp) {
            const date = new Date(timestamp * 1000);
            return date.toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
        }
    </script>
</body>
</html>