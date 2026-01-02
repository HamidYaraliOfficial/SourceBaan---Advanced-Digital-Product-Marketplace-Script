<?php
// Authentication helpers for SourceBaan

declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/utils.php';

const USERS_COLLECTION = 'users';
const FOLLOWS_COLLECTION = 'follows';

function seed_default_admin(): void
{
    $users = JsonDB::read(USERS_COLLECTION);
    $hasAdmin = false;
    $adminExists = false;
    
    // Check if admin already exists
    foreach ($users as $u) {
        if (($u['role'] ?? 'user') === 'admin') {
            $hasAdmin = true;
        }
        if (strcasecmp($u['email'], DEFAULT_ADMIN_EMAIL) === 0) {
            $adminExists = true;
        }
    }
    
    // Create admin if doesn't exist
    if (!$hasAdmin || !$adminExists) {
        $admin = [
            'id' => JsonDB::nextId(),
            'name' => DEFAULT_ADMIN_NAME,
            'email' => DEFAULT_ADMIN_EMAIL,
            'password_hash' => password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT),
            'role' => 'admin',
            'points' => 1000,
            'createdAt' => date('c'),
        ];
        
        // Remove existing admin email if exists but with wrong role
        $users = array_filter($users, function($u) {
            return strcasecmp($u['email'], DEFAULT_ADMIN_EMAIL) !== 0;
        });
        
        $users[] = $admin;
        JsonDB::write(USERS_COLLECTION, $users);
        
        // Log admin creation for debugging
        error_log("SourceBaan: Default admin created with email " . DEFAULT_ADMIN_EMAIL);
    }
}

seed_default_admin();

function find_user_by_email(string $email): ?array
{
    $users = JsonDB::read(USERS_COLLECTION);
    foreach ($users as $u) {
        if (strcasecmp($u['email'], $email) === 0) {
            return $u;
        }
    }
    return null;
}

function find_user_by_username(string $username): ?array
{
    $users = JsonDB::read(USERS_COLLECTION);
    foreach ($users as $u) {
        $userUsername = trim((string)($u['username'] ?? ''));
        $searchTerm = trim($username);
        
        // Enhanced matching for both English and Persian usernames
        if (
            strcasecmp($userUsername, $searchTerm) === 0 ||
            mb_strtolower($userUsername, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8')
        ) {
            return $u;
        }
    }
    return null;
}

function find_user_by_email_or_username(string $identifier): ?array
{
    $users = JsonDB::read(USERS_COLLECTION);
    foreach ($users as $u) {
        $userEmail = trim((string)($u['email'] ?? ''));
        $userUsername = trim((string)($u['username'] ?? ''));
        $userName = trim((string)($u['name'] ?? ''));
        $searchTerm = trim($identifier);
        
        // Enhanced matching for email, username, and name (for Persian support)
        if (
            strcasecmp($userEmail, $searchTerm) === 0 ||
            strcasecmp($userUsername, $searchTerm) === 0 ||
            mb_strtolower($userUsername, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8') ||
            mb_strtolower($userName, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8')
        ) {
            return $u;
        }
    }
    return null;
}

function get_user_by_id(int $id): ?array
{
    $users = JsonDB::read(USERS_COLLECTION);
    foreach ($users as $u) {
        if ((int)$u['id'] === $id) return $u;
    }
    return null;
}

function save_user(array $user): void
{
    JsonDB::upsert(USERS_COLLECTION, function(array $users) use ($user) {
        $updated = false;
        foreach ($users as $i => $u) {
            if ((int)$u['id'] === (int)$user['id']) {
                $users[$i] = $user;
                $updated = true;
                break;
            }
        }
        if (!$updated) {
            $users[] = $user;
        }
        return $users;
    });
}

function register_user(string $name, string $email, string $password, array $additional_data = []): array
{
    $existing = find_user_by_email($email);
    if ($existing) {
        throw new RuntimeException('این ایمیل قبلاً ثبت شده است');
    }
    
    // Check if username exists (if provided)
    if (!empty($additional_data['username'])) {
        $existing_username = find_user_by_username($additional_data['username']);
        if ($existing_username) {
            throw new RuntimeException('این نام کاربری قبلاً ثبت شده است');
        }
    }
    
    $user = [
        'id' => JsonDB::nextId(),
        'name' => $name,
        'email' => $email,
        'username' => $additional_data['username'] ?? '',
        'phone' => $additional_data['phone'] ?? '',
        'province' => $additional_data['province'] ?? '',
        'city' => $additional_data['city'] ?? '',
        'programming_languages' => $additional_data['programming_languages'] ?? [],
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'user',
        'points' => 0,
        'createdAt' => date('c'),
    ];
    save_user($user);
    $_SESSION['user_id'] = (int)$user['id'];
    return $user;
}

function login_user(string $identifier, string $password): array
{
    $user = find_user_by_email_or_username($identifier);
    if (!$user || !password_verify($password, $user['password_hash'])) {
        throw new RuntimeException('نام کاربری/ایمیل یا رمز عبور اشتباه است');
    }
    $_SESSION['user_id'] = (int)$user['id'];
    // Update last login IP/time
    try {
        $user['lastIp'] = get_client_ip();
        $user['lastLoginAt'] = date('c');
        save_user($user);
    } catch (Throwable $e) {
        // ignore
    }
    return $user;
}

function logout_user(): void
{
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params['path'], $params['domain'], $params['secure'], $params['httponly']
        );
    }
    session_destroy();
}



function current_user(): ?array
{
    if (!empty($_SESSION['user_id'])) {
        return get_user_by_id((int)$_SESSION['user_id']);
    }
    return null;
}

function require_login(): void
{
    if (!current_user()) {
        http_response_code(401);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'error' => 'نیاز به ورود دارید']);
        exit;
    }
}

function is_admin(): bool
{
    $u = current_user();
    return $u && (($u['role'] ?? 'user') === 'admin');
}

/**
 * Detect verified search engine bots (Google, Bing) for safe read-only bypasses on public pages.
 * Uses geoblock's reverse/forward DNS verification when available; otherwise falls back to UA hints.
 */
function is_search_engine_bot(): bool
{
    // Prefer robust verification if available
    try {
        if (!function_exists('is_verified_bot')) {
            @require_once __DIR__ . '/geoblock.php';
        }
        if (function_exists('is_verified_bot')) {
            return is_verified_bot();
        }
    } catch (Throwable $e) {
        // ignore
    }

    // Fallback: lightweight UA-based detection (less secure; best-effort)
    $ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($ua === '') return false;
    return (stripos($ua, 'Googlebot') !== false)
        || (stripos($ua, 'Google-InspectionTool') !== false)
        || (stripos($ua, 'AdsBot-Google') !== false)
        || (stripos($ua, 'bingbot') !== false)
        || (stripos($ua, 'BingPreview') !== false);
}

// Forum helper functions
const CATEGORIES_COLLECTION = 'categories';
const TOPICS_COLLECTION = 'topics';
const POSTS_COLLECTION = 'posts';

function get_forum_categories(): array
{
    return JsonDB::read(CATEGORIES_COLLECTION);
}

function get_category_by_id(int $id): ?array
{
    $categories = JsonDB::read(CATEGORIES_COLLECTION);
    foreach ($categories as $category) {
        if ((int)$category['id'] === $id) {
            return $category;
        }
    }
    return null;
}

// --- One-time Telegram capture prompt ---
// Important: don't emit HTML when this file is included by API endpoints (they expect JSON)
try {
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $requestUri = $_SERVER['REQUEST_URI'] ?? '';
    $isApi = (stripos($scriptName, '/api/') !== false) || (stripos($requestUri, '/api/') !== false) || (php_sapi_name() === 'cli');
    if (!$isApi) {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $cu = null;
        if (!empty($_SESSION['user_id'])) {
            $cu = get_user_by_id((int)$_SESSION['user_id']);
        }
        if ($cu && empty(trim((string)($cu['telegram'] ?? '')))) {
            $sessionKey = 'telegram_prompt_shown_' . ((int)$cu['id']);
            if (empty($_SESSION[$sessionKey])) {
                // Mark as shown for this session so prompt doesn't reappear repeatedly
                $_SESSION[$sessionKey] = true;
                $csrf = htmlspecialchars(csrf_get_token());
                // Output a small modal and JS; it will be injected wherever this file is included in HTML pages
                echo "\n<!-- Telegram prompt modal (one-time) -->\n";
                echo "<style>#tgPromptModal{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:99999;align-items:center;justify-content:center}#tgPromptModal .box{width:96%;max-width:520px;background:#fff;border-radius:14px;padding:20px;box-shadow:0 20px 60px rgba(0,0,0,.25);direction:rtl;font-family:Vazirmatn, sans-serif}#tgPromptModal .actions{display:flex;gap:10px;justify-content:flex-start;margin-top:16px}#tgPromptModal input{width:100%;padding:10px;border:1px solid #e5e7eb;border-radius:8px}</style>\n";
                echo "<div id=\"tgPromptModal\">";
                echo "<div class=\"box\">";
                echo "<h3 style=\"margin:0 0 8px;font-size:18px;color:#111;font-weight:700\">لطفاً شناسه تلگرام خود را وارد کنید</h3>";
                echo "<p style=\"margin:0 0 12px;color:#444;font-size:14px\">برای دریافت اعلان‌ها و ارتباط بهتر، یک‌بار شناسه تلگرام خود را ثبت کنید. بعد از ثبت دیگر این پیغام نمایش داده نخواهد شد.</p>";
                echo "<input id=\"tgHandleInput\" placeholder=\"مثال: @username یا username\" />";
                echo "<div class=\"actions\">";
                echo "<button id=\"tgSaveBtn\" style=\"background:#06b6d4;color:#fff;padding:10px 14px;border-radius:8px;border:none;cursor:pointer\">ارسال و ذخیره</button>";
                echo "<button id=\"tgSkipBtn\" style=\"background:#f3f4f6;color:#111;padding:10px 14px;border-radius:8px;border:none;cursor:pointer\">بعداً</button>";
                echo "</div></div></div>\n";
                // JS to handle submit
                $script = "<script>(function(){try{document.addEventListener('DOMContentLoaded',function(){var m=document.getElementById('tgPromptModal');if(!m) return;setTimeout(function(){m.style.display='flex';},500);var save=document.getElementById('tgSaveBtn');var skip=document.getElementById('tgSkipBtn');var inp=document.getElementById('tgHandleInput');save.addEventListener('click',async function(){var v=(inp.value||'').trim(); if(v===''){alert('لطفاً شناسه تلگرام را وارد کنید'); inp.focus(); return;} if(v.startsWith('@')) v=v.substring(1);var fd=new FormData();fd.append('action','set_telegram');fd.append('telegram',v);fd.append('csrf_token','" . $csrf . "');try{var res=await fetch('api/profile.php',{method:'POST',body:fd});var text=await res.text();var j=null;try{j=JSON.parse(text);}catch(e){ /* not JSON */ } if(j && j.ok){ m.style.display='none'; location.reload(); } else { var msg = (j && j.error) ? j.error : (text ? text : 'خطا در ثبت شناسه'); alert(msg); } }catch(e){ try{console.error(e); alert('خطا در ارتباط با سرور: ' + (e.message||e)); }catch(_){} } }); skip.addEventListener('click',function(){ m.style.display='none'; });});}catch(e){console.error(e);} })();</script>";
                echo $script;
            }
        }
    }
} catch (Throwable $e) {
    // Non-fatal: if something fails here we don't want to break the app
}

function get_topics_by_category(int $categoryId, int $page = 1, int $limit = 20): array
{
    $topics = JsonDB::read(TOPICS_COLLECTION);
    $filtered = array_filter($topics, function($topic) use ($categoryId) {
        return (int)($topic['categoryId'] ?? 0) === $categoryId && ($topic['status'] ?? 'active') === 'active';
    });
    
    // Sort by last activity (pinned first, then by last post time)
    usort($filtered, function($a, $b) {
        if (($a['pinned'] ?? false) !== ($b['pinned'] ?? false)) {
            return ($b['pinned'] ?? false) - ($a['pinned'] ?? false);
        }
        return strtotime($b['lastPostAt'] ?? $b['createdAt'] ?? '2024-01-01') - strtotime($a['lastPostAt'] ?? $a['createdAt'] ?? '2024-01-01');
    });
    
    $offset = ($page - 1) * $limit;
    return array_slice($filtered, $offset, $limit);
}

function get_topic_by_id(int $id): ?array
{
    $topics = JsonDB::read(TOPICS_COLLECTION);
    foreach ($topics as $topic) {
        if ((int)$topic['id'] === $id) {
            return $topic;
        }
    }
    return null;
}

function get_posts_by_topic(int $topicId, int $page = 1, int $limit = 10): array
{
    $posts = JsonDB::read(POSTS_COLLECTION);
    $filtered = array_filter($posts, function($post) use ($topicId) {
        return (int)$post['topicId'] === $topicId && ($post['status'] ?? 'active') === 'active';
    });
    
    // Sort by creation time
    usort($filtered, function($a, $b) {
        return strtotime($a['createdAt']) - strtotime($b['createdAt']);
    });
    
    $offset = ($page - 1) * $limit;
    return array_slice($filtered, $offset, $limit);
}

function create_topic(int $userId, int $categoryId, string $title, string $content): array
{
    require_once __DIR__ . '/content-filter.php';
    
    // Check if user is suspended
    if (ContentFilter::isUserSuspended($userId)) {
        throw new RuntimeException('حساب شما مسدود شده است');
    }
    
    // Filter content
    $titleFilter = ContentFilter::filterText($title);
    $contentFilter = ContentFilter::filterText($content);
    
    if (!$titleFilter['passed']) {
        ContentFilter::logContentIssue($userId, $title, $titleFilter['issues']);
        throw new RuntimeException('عنوان حاوی محتوای نامناسب است: ' . implode(', ', $titleFilter['issues']));
    }
    
    if (!$contentFilter['passed']) {
        ContentFilter::logContentIssue($userId, $content, $contentFilter['issues']);
        if ($contentFilter['action'] === 'ban_user') {
            ContentFilter::suspendUser($userId, 24, 'محتوای نامناسب');
        }
        throw new RuntimeException('محتوا حاوی مطالب نامناسب است: ' . implode(', ', $contentFilter['issues']));
    }
    
    $topic = [
        'id' => JsonDB::nextId(),
        'categoryId' => $categoryId,
        'userId' => $userId,
        'title' => $titleFilter['cleanText'],
        'content' => $contentFilter['cleanText'],
        'status' => 'active',
        'pinned' => false,
        'locked' => false,
        'views' => 0,
        'postCount' => 1,
        'lastPostAt' => date('c'),
        'lastPostUserId' => $userId,
        'createdAt' => date('c'),
    ];
    
    // Save topic
    JsonDB::upsert(TOPICS_COLLECTION, function(array $topics) use ($topic) {
        $topics[] = $topic;
        return $topics;
    });
    
    // Create the first post
    $post = [
        'id' => JsonDB::nextId(),
        'topicId' => $topic['id'],
        'userId' => $userId,
        'content' => $contentFilter['cleanText'],
        'status' => 'active',
        'editedAt' => null,
        'editedBy' => null,
        'createdAt' => date('c'),
    ];
    
    JsonDB::upsert(POSTS_COLLECTION, function(array $posts) use ($post) {
        $posts[] = $post;
        return $posts;
    });
    
    return $topic;
}

function create_post(int $userId, int $topicId, string $content): array
{
    require_once __DIR__ . '/content-filter.php';
    
    // Check if user is suspended
    if (ContentFilter::isUserSuspended($userId)) {
        throw new RuntimeException('حساب شما مسدود شده است');
    }
    
    // Check if topic exists and is not locked
    $topic = get_topic_by_id($topicId);
    if (!$topic) {
        throw new RuntimeException('موضوع یافت نشد');
    }
    
    if ($topic['locked'] ?? false) {
        throw new RuntimeException('این موضوع قفل شده است');
    }
    
    // Filter content
    $contentFilter = ContentFilter::filterText($content);
    
    if (!$contentFilter['passed']) {
        ContentFilter::logContentIssue($userId, $content, $contentFilter['issues']);
        if ($contentFilter['action'] === 'ban_user') {
            ContentFilter::suspendUser($userId, 24, 'محتوای نامناسب');
        }
        throw new RuntimeException('محتوا حاوی مطالب نامناسب است: ' . implode(', ', $contentFilter['issues']));
    }
    
    $post = [
        'id' => JsonDB::nextId(),
        'topicId' => $topicId,
        'userId' => $userId,
        'content' => $contentFilter['cleanText'],
        'status' => 'active',
        'editedAt' => null,
        'editedBy' => null,
        'createdAt' => date('c'),
    ];
    
    // Save post
    JsonDB::upsert(POSTS_COLLECTION, function(array $posts) use ($post) {
        $posts[] = $post;
        return $posts;
    });
    
    // Update topic last post info and post count
    JsonDB::upsert(TOPICS_COLLECTION, function(array $topics) use ($topicId, $userId) {
        foreach ($topics as $i => $topic) {
            if ((int)$topic['id'] === $topicId) {
                $topics[$i]['lastPostAt'] = date('c');
                $topics[$i]['lastPostUserId'] = $userId;
                $topics[$i]['postCount'] = ($topic['postCount'] ?? 1) + 1;
                break;
            }
        }
        return $topics;
    });
    
    return $post;
}

function increment_topic_views(int $topicId): void
{
    JsonDB::upsert(TOPICS_COLLECTION, function(array $topics) use ($topicId) {
        foreach ($topics as $i => $topic) {
            if ((int)$topic['id'] === $topicId) {
                $topics[$i]['views'] = ($topic['views'] ?? 0) + 1;
                break;
            }
        }
        return $topics;
    });
}

// Follow system functions
function follow_user(int $followerId, int $followingId): bool
{
    if ($followerId === $followingId) {
        return false; // Can't follow yourself
    }
    
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    
    // Check if already following
    foreach ($follows as $follow) {
        if ((int)$follow['follower_id'] === $followerId && (int)$follow['following_id'] === $followingId) {
            return false; // Already following
        }
    }
    
    $follow = [
        'id' => JsonDB::nextId(),
        'follower_id' => $followerId,
        'following_id' => $followingId,
        'created_at' => date('c')
    ];
    
    $follows[] = $follow;
    JsonDB::write(FOLLOWS_COLLECTION, $follows);
    
    return true;
}

function unfollow_user(int $followerId, int $followingId): bool
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    $updated = false;
    
    $follows = array_filter($follows, function($follow) use ($followerId, $followingId, &$updated) {
        if ((int)$follow['follower_id'] === $followerId && (int)$follow['following_id'] === $followingId) {
            $updated = true;
            return false;
        }
        return true;
    });
    
    if ($updated) {
        JsonDB::write(FOLLOWS_COLLECTION, array_values($follows));
    }
    
    return $updated;
}

function is_following(int $followerId, int $followingId): bool
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    
    foreach ($follows as $follow) {
        if ((int)$follow['follower_id'] === $followerId && (int)$follow['following_id'] === $followingId) {
            return true;
        }
    }
    
    return false;
}

function get_followers_count(int $userId): int
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    $count = 0;
    
    foreach ($follows as $follow) {
        if ((int)$follow['following_id'] === $userId) {
            $count++;
        }
    }
    
    return $count;
}

function get_following_count(int $userId): int
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    $count = 0;
    
    foreach ($follows as $follow) {
        if ((int)$follow['follower_id'] === $userId) {
            $count++;
        }
    }
    
    return $count;
}

function get_followers(int $userId, int $limit = 50): array
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    $users = JsonDB::read(USERS_COLLECTION);
    $followers = [];
    
    foreach ($follows as $follow) {
        if ((int)$follow['following_id'] === $userId) {
            foreach ($users as $user) {
                if ((int)$user['id'] === (int)$follow['follower_id']) {
                    $userCopy = $user;
                    unset($userCopy['password_hash']);
                    $followers[] = $userCopy;
                    break;
                }
            }
        }
    }
    
    return array_slice($followers, 0, $limit);
}

function get_following(int $userId, int $limit = 50): array
{
    $follows = JsonDB::read(FOLLOWS_COLLECTION);
    $users = JsonDB::read(USERS_COLLECTION);
    $following = [];
    
    foreach ($follows as $follow) {
        if ((int)$follow['follower_id'] === $userId) {
            foreach ($users as $user) {
                if ((int)$user['id'] === (int)$follow['following_id']) {
                    $userCopy = $user;
                    unset($userCopy['password_hash']);
                    $following[] = $userCopy;
                    break;
                }
            }
        }
    }
    
    return array_slice($following, 0, $limit);
}

// Verification system functions
function verify_user(int $userId, string $verifiedBy = 'admin'): bool
{
    $user = get_user_by_id($userId);
    if (!$user) {
        return false;
    }
    
    $user['verified'] = true;
    $user['verified_at'] = date('c');
    $user['verified_by'] = $verifiedBy;
    $user['verification_celebrated'] = false; // New field to track if celebration was shown
    
    save_user($user);
    return true;
}

function unverify_user(int $userId): bool
{
    $user = get_user_by_id($userId);
    if (!$user) {
        return false;
    }
    
    unset($user['verified']);
    unset($user['verified_at']);
    unset($user['verified_by']);
    
    save_user($user);
    return true;
}

function is_user_verified(int $userId): bool
{
    $user = get_user_by_id($userId);
    return $user && !empty($user['verified']);
}

function get_verification_badge(array $user): string
{
    if (empty($user['verified'])) {
        return '';
    }
    
    $verifiedAt = $user['verified_at'] ?? '';
    $verifiedBy = $user['verified_by'] ?? 'admin';
    $title = 'کاربر تایید‌شده';
    
    if ($verifiedAt) {
        $title .= ' - تایید شده در ' . date('Y/m/d', strtotime($verifiedAt));
    }
    
    return '<span title="' . htmlspecialchars($title) . '" class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-2 py-1 rounded-full text-xs font-semibold shadow-lg transform transition-all duration-200 hover:scale-105">
                <svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20 6L9 17l-5-5"></path>
                </svg>
                <span>تایید‌شده</span>
            </span>';
}

// Notification system for followers
function notify_followers($userId, $title, $message, $link = '', $type = 'info')
{
    $followers = get_followers($userId);
    if (empty($followers)) {
        return;
    }
    
    $notifications = JsonDB::read('notifications') ?? [];
    $notificationId = time() . rand(1000, 9999);
    
    foreach ($followers as $follower) {
        $notification = [
            'id' => $notificationId . '_' . $follower['id'],
            'user_id' => (int)$follower['id'],
            'title' => $title,
            'message' => $message,
            'link' => $link,
            'type' => $type,
            'read' => false,
            'created_at' => date('c')
        ];
        
        $notifications[] = $notification;
    }
    
    JsonDB::write('notifications', $notifications);
}

// Avatar system functions
function get_avatar_options()
{
    return [
        'avatar1' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=felix&backgroundColor=b6e3f4,c0aede,d1d4f9',
        'avatar2' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=aneka&backgroundColor=ffd5dc,ffdfbf,c0aede',
        'avatar3' => 'https://api.dicebear.com/7.x/avataaars/svg?seed=bob&backgroundColor=d1d4f9,ffd5dc,b6e3f4',
        'avatar4' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=sarah&backgroundColor=b6e3f4,c0aede,d1d4f9',
        'avatar5' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=max&backgroundColor=ffd5dc,ffdfbf,c0aede',
        'avatar6' => 'https://api.dicebear.com/7.x/adventurer/svg?seed=lily&backgroundColor=d1d4f9,ffd5dc,b6e3f4',
        'avatar7' => 'https://api.dicebear.com/7.x/personas/svg?seed=john&backgroundColor=b6e3f4,c0aede,d1d4f9',
        'avatar8' => 'https://api.dicebear.com/7.x/personas/svg?seed=mary&backgroundColor=ffd5dc,ffdfbf,c0aede',
        'avatar9' => 'https://api.dicebear.com/7.x/personas/svg?seed=alex&backgroundColor=d1d4f9,ffd5dc,b6e3f4',
        'avatar10' => 'https://api.dicebear.com/7.x/miniavs/svg?seed=david&backgroundColor=b6e3f4,c0aede,d1d4f9',
        'avatar11' => 'https://api.dicebear.com/7.x/miniavs/svg?seed=emma&backgroundColor=ffd5dc,ffdfbf,c0aede',
        'avatar12' => 'https://api.dicebear.com/7.x/miniavs/svg?seed=mike&backgroundColor=d1d4f9,ffd5dc,b6e3f4',
    ];
}

function get_user_avatar($user)
{
    if (!$user) return '';
    
    $selectedAvatar = $user['avatar'] ?? 'avatar1';
    $avatarOptions = get_avatar_options();
    
    return $avatarOptions[$selectedAvatar] ?? $avatarOptions['avatar1'];
}

function update_user_avatar($userId, $avatarKey)
{
    $user = get_user_by_id($userId);
    if (!$user) {
        return false;
    }
    
    $avatarOptions = get_avatar_options();
    if (!isset($avatarOptions[$avatarKey])) {
        return false;
    }
    
    $user['avatar'] = $avatarKey;
    save_user($user);
    return true;
}
