<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/utils.php';

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

$current_user = current_user();
$isOwner = $current_user && isset($current_user['username']) && isset($_GET['user']) && ($current_user['username'] === trim($_GET['user']));
$csrfToken = csrf_get_token();

// Get username from URL parameter
$username = isset($_GET['user']) ? trim($_GET['user']) : null;
if (!$username) {
    header('HTTP/1.1 404 Not Found');
    include '404.php';
    exit;
}

// Get user data - Enhanced search to support Persian names
$users = JsonDB::read('users') ?? [];
$user = null;

// Support lookups by username, name, or email with better Unicode support
foreach ($users as $u) {
    $userUsername = trim((string)($u['username'] ?? ''));
    $userName = trim((string)($u['name'] ?? ''));
    $userEmail = trim((string)($u['email'] ?? ''));
    
    // Normalize search term for better matching
    $searchTerm = trim($username);
    
    // Check exact matches first (case-insensitive for English, exact for Persian)
    if (
        // Username exact match
        (mb_strtolower($userUsername, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8')) ||
        // Name exact match
        (mb_strtolower($userName, 'UTF-8') === mb_strtolower($searchTerm, 'UTF-8')) ||
        // Email exact match
        (strcasecmp($userEmail, $searchTerm) === 0) ||
        // Fallback to old comparison for compatibility
        (strcasecmp($userUsername, $searchTerm) === 0) ||
        (strcasecmp($userName, $searchTerm) === 0)
    ) {
        $user = $u;
        break;
    }
    
    // Also check if the search term is contained within the name (partial search for Persian names)
    if (
        (!empty($searchTerm) && mb_strlen($searchTerm, 'UTF-8') >= 3) &&
        (
            (mb_strpos(mb_strtolower($userName, 'UTF-8'), mb_strtolower($searchTerm, 'UTF-8')) !== false) ||
            (mb_strpos(mb_strtolower($userUsername, 'UTF-8'), mb_strtolower($searchTerm, 'UTF-8')) !== false)
        )
    ) {
        $user = $u;
        break;
    }
}

if (!$user) {
    // If a custom 404 page exists, include it; otherwise render a minimal friendly 404 to avoid blank output
    header('HTTP/1.1 404 Not Found');
    $fallback404 = __DIR__ . '/404.php';
    if (is_readable($fallback404)) {
        include $fallback404;
    } else {
        echo "<!doctype html><html lang=\"fa\"><head><meta charset=\"utf-8\"><title>صفحه یافت نشد</title></head><body style=\"font-family:Vazirmatn, sans-serif;padding:40px;direction:rtl;\"><h1>404 - کاربر یافت نشد</h1><p>کاربری با مشخصات مورد نظر پیدا نشد.</p><p><a href=\"index.php\">بازگشت به صفحه اصلی</a></p></body></html>";
    }
    exit;
}

// Ensure we have a username field for templates that expect it
$user['username'] = $user['username'] ?? $user['name'] ?? explode('@', ($user['email'] ?? ''))[0] ?? 'کاربر';

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

// Get follow stats and status
$followersCount = get_followers_count((int)$user['id']);
$followingCount = get_following_count((int)$user['id']);
$isFollowing = $current_user ? is_following((int)$current_user['id'], (int)$user['id']) : false;

// Calculate total file size in MB
$totalSizeMB = 0;
foreach ($userProjects as $project) {
    $fileSize = $project['file_size'] ?? 0;
    $totalSizeMB += ($fileSize / (1024 * 1024));
}

// Get language stats
$languageStats = [];
foreach ($userProjects as $project) {
    $lang = $project['language'] ?? 'نامشخص';
    $languageStats[$lang] = ($languageStats[$lang] ?? 0) + 1;
}
arsort($languageStats);

function getLanguageBadgeColor($language) {
    $colors = [
        'PHP' => 'from-purple-500 to-purple-600',
        'Python' => 'from-blue-500 to-blue-600',
        'JavaScript' => 'from-yellow-500 to-yellow-600',
        'Java' => 'from-red-500 to-red-600',
        'C++' => 'from-indigo-500 to-indigo-600',
        'C#' => 'from-green-500 to-green-600',
        'Go' => 'from-cyan-500 to-cyan-600',
        'Rust' => 'from-orange-500 to-orange-600',
        'TypeScript' => 'from-blue-600 to-blue-700',
        'HTML/CSS' => 'from-pink-500 to-pink-600'
    ];
    return $colors[$language] ?? 'from-gray-500 to-gray-600';
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پروفایل <?php echo htmlspecialchars($user['username']); ?> - سورس بان | SourceBan</title>
    <meta name="description" content="مشاهده پروفایل و پروژه‌های <?php echo htmlspecialchars($user['username']); ?> در سورس بان. مجموعه کامل پروژه‌های اپلود شده توسط این کاربر.">
    
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        .gradient-bg { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glassmorphism { 
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .card-hover { 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover { 
            transform: translateY(-8px);
            box-shadow: 0 25px 50px rgba(0,0,0,0.15);
        }
        .floating-animation {
            animation: floating 6s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .slide-in-up {
            animation: slideInUp 0.8s ease-out;
        }
        @keyframes slideInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .stagger-1 { animation-delay: 0.1s; }
        .stagger-2 { animation-delay: 0.2s; }
        .stagger-3 { animation-delay: 0.3s; }
        .stagger-4 { animation-delay: 0.4s; }
        .text-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .tab-active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        .pulse-glow {
            animation: pulse-glow 2s ease-in-out infinite alternate;
        }
        @keyframes pulse-glow {
            from { box-shadow: 0 0 20px rgba(102, 126, 234, 0.4); }
            to { box-shadow: 0 0 40px rgba(102, 126, 234, 0.8); }
        }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 3px; }
        
        /* Follow button styles */
        .follow-btn.not-following {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        .follow-btn.not-following:hover {
            background: linear-gradient(135deg, #059669, #047857);
        }
        .follow-btn.following {
            background: rgba(255, 255, 255, 0.9);
            color: #6b7280;
            border: 2px solid #e5e7eb;
        }
        .follow-btn.following:hover {
            background: #fef2f2;
            color: #dc2626;
            border-color: #fca5a5;
        }
        .follow-btn.following:hover .follow-text::after {
            content: ' لغو';
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-indigo-50 via-purple-50 to-pink-50 relative overflow-x-hidden">
    <!-- Animated Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute top-20 right-20 w-72 h-72 bg-gradient-to-br from-blue-400/20 to-purple-600/20 rounded-full floating-animation"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-gradient-to-tr from-pink-400/20 to-yellow-500/20 rounded-full floating-animation" style="animation-delay: -3s;"></div>
        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-gradient-to-r from-green-400/20 to-blue-500/20 rounded-full floating-animation" style="animation-delay: -1.5s;"></div>
    </div>

    <!-- Navigation -->
    <nav class="relative z-50 bg-white/90 backdrop-blur-md border-b border-gray-200/50 sticky top-0 px-4 py-3 slide-in-up shadow-lg">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-4 space-x-reverse">
                <a href="index.php" class="group p-3 bg-gray-100 rounded-xl hover:bg-blue-600 hover:text-white transition-all duration-300 shadow-md">
                    <i class="fas fa-arrow-right text-lg text-gray-700 group-hover:text-white"></i>
                </a>
                <div>
                    <h1 class="text-gray-800 text-xl font-bold">پروفایل کاربری</h1>
                    <p class="text-gray-600 text-sm">مشاهده اطلاعات و پروژه‌ها</p>
                </div>
            </div>
            
            <?php if ($current_user): ?>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <span class="text-sm hidden sm:block text-gray-700 bg-gray-100 px-3 py-2 rounded-xl">خوش آمدید، <span class="font-semibold text-blue-600"><?php echo htmlspecialchars($current_user['username']); ?></span></span>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center text-white font-bold shadow-lg">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
            <?php else: ?>
                <a href="auth.php" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl transition-all duration-300 shadow-lg">
                    <i class="fas fa-sign-in-alt ml-2"></i>
                    ورود
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="relative z-10 gradient-bg py-16 slide-in-up stagger-1">
        <div class="max-w-6xl mx-auto px-4">
            <div class="text-center text-white">
                <!-- User Avatar -->
                <div class="relative w-32 h-32 rounded-full mx-auto mb-6 overflow-hidden border-4 border-white/30 shadow-2xl pulse-glow">
                    <?php $avatarUrl = get_user_avatar($user); ?>
                    <?php if ($avatarUrl): ?>
                        <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="avatar" class="w-full h-full object-cover bg-white">
                    <?php else: ?>
                        <div class="w-full h-full bg-gradient-to-br from-white/20 to-white/10 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-white/90"></i>
                        </div>
                    <?php endif; ?>
                    <?php if ($isOwner): ?>
                    <button onclick="openAvatarModal()" class="absolute bottom-2 right-2 bg-white/90 text-gray-700 px-3 py-1.5 rounded-xl text-xs cursor-pointer hover:bg-white transition-all duration-300 shadow-lg">
                        <i class="fas fa-edit ml-1"></i> تغییر آواتار
                    </button>
                    <?php endif; ?>
                </div>
                
                <!-- User Info -->
                <h1 class="text-4xl sm:text-5xl font-bold mb-4">
                    <?php echo htmlspecialchars($user['username']); ?>
                    <?php if (!empty($user['verified'])): ?>
                        <span title="تایید‌شده" class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg ml-3 transform transition-all duration-200 hover:scale-105">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            <span>تایید‌شده</span>
                        </span>
                    <?php endif; ?>
                </h1>
                <p class="text-xl opacity-90 mb-2"><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></p>
                <p class="text-lg opacity-75 mb-4">عضو از <?php echo date('Y/m/d', strtotime($user['created_at'] ?? ($user['createdAt'] ?? 'now'))); ?></p>
                
                <!-- Follow Stats -->
                <div class="flex justify-center gap-6 mb-6">
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white"><?php echo $followersCount; ?></div>
                        <div class="text-sm opacity-75">دنبال‌کننده</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold text-white"><?php echo $followingCount; ?></div>
                        <div class="text-sm opacity-75">دنبال‌شده</div>
                    </div>
                </div>
                
                <?php if ($isOwner): ?>
                <div class="flex flex-wrap justify-center gap-3">
                    <button id="openEditProfile" class="glassmorphism px-6 py-3 rounded-xl hover:bg-white/90 text-gray-800 font-medium transition-all duration-300 card-hover">
                        <i class="fas fa-user-edit ml-2"></i> ویرایش پروفایل
                    </button>
                    <a href="account.php" class="glassmorphism px-6 py-3 rounded-xl hover:bg-white/90 text-gray-800 font-medium transition-all duration-300 card-hover">
                        <i class="fas fa-cog ml-2"></i> حساب من
                    </a>
                </div>
                <?php elseif ($current_user): ?>
                <div class="flex justify-center">
                    <button id="followBtn" 
                            data-user-id="<?php echo (int)$user['id']; ?>"
                            data-is-following="<?php echo $isFollowing ? 'true' : 'false'; ?>"
                            class="follow-btn <?php echo $isFollowing ? 'following' : 'not-following'; ?> glassmorphism px-8 py-3 rounded-xl font-medium transition-all duration-300 card-hover">
                        <i class="fas <?php echo $isFollowing ? 'fa-user-check' : 'fa-user-plus'; ?> ml-2"></i>
                        <span class="follow-text"><?php echo $isFollowing ? 'دنبال می‌کنید' : 'دنبال کردن'; ?></span>
                    </button>
                </div>
                <?php else: ?>
                <div class="flex justify-center">
                    <a href="auth.php" class="glassmorphism px-8 py-3 rounded-xl hover:bg-white/90 text-gray-800 font-medium transition-all duration-300 card-hover">
                        <i class="fas fa-sign-in-alt ml-2"></i> ورود برای دنبال کردن
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 -mt-8 mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="glassmorphism rounded-2xl p-4 sm:p-6 text-center card-hover slide-in-up stagger-1">
                <div class="text-3xl font-bold text-blue-600 mb-2" id="projectCount"><?php echo $totalProjects; ?></div>
                <div class="text-gray-600 font-medium">پروژه منتشر شده</div>
            </div>
            
            <div class="glassmorphism rounded-2xl p-4 sm:p-6 text-center card-hover slide-in-up stagger-2">
                <div class="text-3xl font-bold text-green-600 mb-2" id="downloadCount"><?php echo $totalDownloads; ?></div>
                <div class="text-gray-600 font-medium">دانلود</div>
            </div>
            
            <div class="glassmorphism rounded-2xl p-4 sm:p-6 text-center card-hover slide-in-up stagger-3">
                <div class="text-3xl font-bold text-yellow-600 mb-2" id="starCount"><?php echo $totalStars; ?></div>
                <div class="text-gray-600 font-medium">ستاره</div>
            </div>
            
            <div class="glassmorphism rounded-2xl p-4 sm:p-6 text-center card-hover slide-in-up stagger-4">
                <div class="text-3xl font-bold text-purple-600 mb-2"><?php echo number_format((($totalSizeMB * 1024)), 1); ?></div>
                    <div class="text-gray-600 font-medium">کیلوبایت کد</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 mb-8 slide-in-up stagger-2">
        <div class="glassmorphism rounded-2xl p-2 flex flex-wrap gap-2">
            <button data-tab="overview" class="tab-btn px-4 py-3 rounded-xl font-medium transition-all duration-300 tab-active">
                <i class="fas fa-chart-line ml-2"></i>نمای کلی
            </button>
            <button data-tab="about" class="tab-btn px-4 py-3 rounded-xl font-medium transition-all duration-300 text-gray-700 hover:bg-white/50">
                <i class="fas fa-user ml-2"></i>درباره
            </button>
            <button data-tab="activity" class="tab-btn px-4 py-3 rounded-xl font-medium transition-all duration-300 text-gray-700 hover:bg-white/50">
                <i class="fas fa-clock ml-2"></i>فعالیت
            </button>
            <button data-tab="achievements" class="tab-btn px-4 py-3 rounded-xl font-medium transition-all duration-300 text-gray-700 hover:bg-white/50">
                <i class="fas fa-trophy ml-2"></i>دستاوردها
            </button>
        </div>
    </div>

    <!-- Content Section -->
    <div class="relative z-10 max-w-6xl mx-auto px-4 pb-8">
        <div class="grid lg:grid-cols-4 gap-8">
            <!-- Sidebar -->
            <div class="lg:col-span-1 space-y-6">
                <!-- Language Stats -->
                <div class="glassmorphism rounded-2xl p-6 card-hover slide-in-up stagger-3">
                    <h3 class="text-lg font-bold mb-4 flex items-center text-gray-800">
                        <i class="fas fa-code text-blue-500 ml-2"></i>
                        زبان‌های برنامه‌نویسی
                    </h3>
                    
                    <?php if (!empty($languageStats)): ?>
                        <div class="space-y-3">
                            <?php foreach ($languageStats as $lang => $count): ?>
                                <div class="flex items-center justify-between">
                                    <span class="bg-gradient-to-r <?php echo getLanguageBadgeColor($lang); ?> text-white px-3 py-2 rounded-xl text-sm font-medium shadow-md">
                                        <?php echo htmlspecialchars($lang); ?>
                                    </span>
                                    <span class="text-gray-600 font-bold text-lg"><?php echo $count; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-8">
                            <i class="fas fa-code text-4xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500">هنوز پروژه‌ای منتشر نشده</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Main Area -->
            <div class="lg:col-span-3">
                <!-- About Tab -->
                <div id="tab-about" class="hidden">
                    <div class="glassmorphism rounded-2xl p-6 sm:p-8 card-hover mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 mb-6 flex items-center">
                            <i class="fas fa-user text-blue-500 ml-3"></i>
                            درباره
                        </h2>
                        <div class="space-y-4 text-gray-700">
                            <div class="bg-white/50 rounded-xl p-4">
                                <span class="font-semibold text-gray-800">نام:</span> 
                                <span class="mr-2"><?php echo htmlspecialchars($user['name'] ?? $user['username']); ?></span>
                            </div>
                            <?php if (!empty($user['bio'] ?? '')): ?>
                            <div class="bg-white/50 rounded-xl p-4">
                                <span class="font-semibold text-gray-800">بیوگرافی:</span>
                                <p class="mt-2 leading-relaxed"><?php echo nl2br(htmlspecialchars($user['bio'])); ?></p>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['website'] ?? '') || !empty($user['github'] ?? '') || !empty($user['twitter'] ?? '') || !empty($user['telegram'] ?? '')): ?>
                            <div class="bg-white/50 rounded-xl p-4">
                                <span class="font-semibold text-gray-800 block mb-3">لینک‌ها:</span>
                                <div class="flex flex-wrap gap-3">
                                    <?php if (!empty($user['website'] ?? '')): ?>
                                    <a href="<?php echo htmlspecialchars($user['website']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:shadow-lg transition-all duration-300">
                                        <i class="fas fa-globe ml-2"></i> وبسایت
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($user['github'] ?? '')): ?>
                                    <a href="<?php echo htmlspecialchars($user['github']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-gray-700 to-gray-800 text-white rounded-xl hover:shadow-lg transition-all duration-300">
                                        <i class="fab fa-github ml-2"></i> گیت‌هاب
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($user['twitter'] ?? '')): ?>
                                    <a href="<?php echo htmlspecialchars($user['twitter']); ?>" target="_blank" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-400 to-blue-500 text-white rounded-xl hover:shadow-lg transition-all duration-300">
                                        <i class="fab fa-twitter ml-2"></i> توییتر
                                    </a>
                                    <?php endif; ?>
                                    <?php if (!empty($user['telegram'] ?? '')): ?>
                                    <?php 
                                    $telegramUrl = trim((string)$user['telegram']);
                                    // Normalize common inputs: @username | t.me/username | http(s)://t.me/username
                                    if ($telegramUrl !== '') {
                                        // Remove whitespace
                                        $telegramUrl = preg_replace('/\s+/', '', $telegramUrl);
                                        // Strip leading @
                                        if (strpos($telegramUrl, '@') === 0) { $telegramUrl = substr($telegramUrl, 1); }
                                        // Replace possible site-prepended path like sourcebaan.ir/t.me/username
                                        $telegramUrl = preg_replace('#^[^/]*t\.me/#i', 't.me/', $telegramUrl);
                                        // If full URL provided but not t.me, keep it
                                        if (!preg_match('#^https?://#i', $telegramUrl)) {
                                            // If starts with t.me/ or /t.me/ keep path after t.me/
                                            if (stripos($telegramUrl, 't.me/') === 0) {
                                                $telegramUrl = 'https://' . $telegramUrl;
                                            } else {
                                                $telegramUrl = 'https://t.me/' . ltrim($telegramUrl, '/');
                                            }
                                        }
                                    }
                                    ?>
                                    <a href="<?php echo htmlspecialchars($telegramUrl); ?>" target="_blank" rel="noopener" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-xl hover:shadow-lg transition-all duration-300">
                                        <i class="fab fa-telegram ml-2"></i> تلگرام
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($user['province'] ?? '') || !empty($user['city'] ?? '')): ?>
                            <div class="bg-white/50 rounded-xl p-4">
                                <span class="font-semibold text-gray-800">محل سکونت:</span> 
                                <span class="mr-2"><?php echo htmlspecialchars(trim(($user['province'] ?? '') . ' ' . ($user['city'] ?? ''))); ?></span>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Activity Tab -->
                <div id="tab-activity" class="hidden">
                    <div class="glassmorphism rounded-2xl p-6 sm:p-8 card-hover text-center">
                        <i class="fas fa-chart-line text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">فعالیت اخیر</h2>
                        <p class="text-gray-600">این بخش به‌زودی اضافه می‌شود...</p>
                    </div>
                </div>

                <!-- Achievements Tab -->
                <div id="tab-achievements" class="hidden">
                    <div class="glassmorphism rounded-2xl p-6 sm:p-8 card-hover text-center">
                        <i class="fas fa-trophy text-6xl text-gray-300 mb-4"></i>
                        <h2 class="text-2xl font-bold text-gray-800 mb-4">دستاوردها</h2>
                        <p class="text-gray-600">این بخش به‌زودی اضافه می‌شود...</p>
                    </div>
                </div>

                <!-- Overview/Projects (default) -->
                <div id="tab-overview">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-folder text-blue-500 ml-3"></i>
                            پروژه‌های منتشر شده
                        </h2>
                        <span class="glassmorphism px-4 py-2 rounded-xl text-gray-600 font-medium"><?php echo $totalProjects; ?> پروژه</span>
                </div>

                <?php if (!empty($userProjects)): ?>
                    <div class="grid md:grid-cols-2 gap-6">
                        <?php foreach ($userProjects as $project): ?>
                                <div class="glassmorphism rounded-2xl p-6 card-hover group slide-in-up">
                                <!-- Project Header -->
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                            <h3 class="text-lg font-bold text-gray-800 mb-2 group-hover:text-blue-600 transition-colors">
                                                <a href="project.php?id=<?php echo $project['id']; ?>" class="flex items-center">
                                                    <i class="fas fa-folder-open ml-2"></i>
                                                <?php echo htmlspecialchars($project['title']); ?>
                                            </a>
                                        </h3>
                                        <div class="flex items-center space-x-2 space-x-reverse">
                                                <span class="bg-gradient-to-r <?php echo getLanguageBadgeColor($project['language'] ?? 'نامشخص'); ?> text-white px-3 py-1 rounded-xl text-xs font-medium">
                                                <?php echo htmlspecialchars($project['language'] ?? 'نامشخص'); ?>
                                            </span>
                                                <span class="text-gray-500 text-sm flex items-center">
                                                    <i class="fas fa-calendar ml-1"></i>
                                                <?php echo date('Y/m/d', strtotime($project['created_at'] ?? 'now')); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Project Description -->
                                    <p class="text-gray-600 mb-4 leading-relaxed">
                                    <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 120)) . (strlen($project['description'] ?? '') > 120 ? '...' : ''); ?>
                                </p>

                                <!-- Project Stats -->
                                    <div class="grid grid-cols-3 gap-4 mb-4">
                                        <div class="bg-white/50 rounded-xl p-3 text-center">
                                            <div class="text-sm font-bold text-green-600 flex items-center justify-center">
                                                <i class="fas fa-download ml-1"></i>
                                                <?php echo $project['downloads'] ?? 0; ?>
                                    </div>
                                        <div class="text-xs text-gray-500">دانلود</div>
                                    </div>
                                        <div class="bg-white/50 rounded-xl p-3 text-center">
                                            <div class="text-sm font-bold text-yellow-600 flex items-center justify-center">
                                                <i class="fas fa-star ml-1"></i>
                                                <?php echo $project['stars'] ?? 0; ?>
                                            </div>
                                        <div class="text-xs text-gray-500">ستاره</div>
                                        </div>
                                        <div class="bg-white/50 rounded-xl p-3 text-center">
                                            <div class="text-sm font-bold text-blue-600 flex items-center justify-center">
                                                <i class="fas fa-eye ml-1"></i>
                                                <?php echo $project['views'] ?? 0; ?>
                                            </div>
                                            <div class="text-xs text-gray-500">بازدید</div>
                                        </div>
                                </div>

                                <!-- Action Button -->
                                <a href="project.php?id=<?php echo $project['id']; ?>" 
                                       class="block w-full bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white text-center py-3 rounded-xl font-medium transition-all duration-300 transform hover:scale-105">
                                        <i class="fas fa-eye ml-2"></i>
                                    مشاهده پروژه
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                        <div class="glassmorphism rounded-2xl p-8 sm:p-16 text-center">
                            <i class="fas fa-folder-open text-8xl text-gray-300 mb-6"></i>
                            <h3 class="text-2xl font-bold text-gray-500 mb-4">هنوز پروژه‌ای منتشر نشده</h3>
                            <p class="text-gray-400 text-lg">این کاربر هنوز پروژه‌ای در انجمن منتشر نکرده است.</p>
                    </div>
                <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Back to Top Button -->
    <button id="backToTop" class="fixed bottom-8 left-8 glassmorphism w-12 h-12 rounded-full transition-all duration-300 opacity-0 pointer-events-none card-hover z-50">
        <i class="fas fa-arrow-up text-gray-600"></i>
    </button>

    <?php if ($isOwner): ?>
    <!-- Edit Profile Modal -->
    <div id="editProfileModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm hidden items-center justify-center z-50 p-4">
        <div class="glassmorphism rounded-3xl shadow-2xl w-full max-w-2xl p-6 sm:p-8 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-edit text-blue-500 ml-3"></i>
                    ویرایش پروفایل
                </h3>
                <button id="closeEditProfile" class="p-2 hover:bg-white/50 rounded-xl transition-all duration-300">
                    <i class="fas fa-times text-gray-600"></i>
                </button>
            </div>
            <form id="editProfileForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام</label>
                    <input name="name" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['name'] ?? $user['username']); ?>" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">بیوگرافی</label>
                    <textarea name="bio" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" rows="4" maxlength="500" placeholder="درباره خودتان بنویسید..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">وبسایت</label>
                        <input name="website" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['website'] ?? ''); ?>" placeholder="https://example.com">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">GitHub</label>
                        <input name="github" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['github'] ?? ''); ?>" placeholder="username">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Twitter/X</label>
                        <input name="twitter" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['twitter'] ?? ''); ?>" placeholder="@username">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Telegram</label>
                        <input name="telegram" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['telegram'] ?? ''); ?>" placeholder="@username">
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">استان</label>
                        <input name="province" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['province'] ?? ''); ?>">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">شهر</label>
                        <input name="city" class="w-full border border-gray-200 rounded-xl px-4 py-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>">
                    </div>
                </div>
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-gray-200">
                    <button type="button" id="cancelEditProfile" class="px-6 py-3 rounded-xl bg-gray-200 text-gray-800 hover:bg-gray-300 transition-all duration-300">انصراف</button>
                    <button type="submit" class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-purple-600 text-white hover:from-blue-600 hover:to-purple-700 transition-all duration-300 shadow-lg">
                        <i class="fas fa-save ml-2"></i>
                        ذخیره تغییرات
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Avatar Selection Modal -->
    <div id="avatarModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-hidden">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-bold text-gray-900">انتخاب آواتار</h3>
                    <button onclick="closeAvatarModal()" class="p-2 hover:bg-gray-100 rounded-lg transition-colors">
                        <i class="fas fa-times text-gray-500"></i>
                    </button>
                </div>
            </div>
            
            <div class="p-6 max-h-[60vh] overflow-y-auto">
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-4" id="avatarOptions">
                    <!-- Avatar options will be loaded here -->
                </div>
            </div>
            
            <div class="p-6 border-t border-gray-200 flex justify-end space-x-3 space-x-reverse">
                <button onclick="closeAvatarModal()" class="px-4 py-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors">
                    انصراف
                </button>
                <button onclick="saveAvatar()" id="saveAvatarBtn" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                    ذخیره
                </button>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        // Counter animation
        function animateCounter(id, target) {
            const element = document.getElementById(id);
            if (!element) return;
            
            let current = 0;
            const increment = target / 50;
            const timer = setInterval(() => {
                current += increment;
                if (current >= target) {
                    element.textContent = target.toLocaleString('fa-IR');
                    clearInterval(timer);
                } else {
                    element.textContent = Math.floor(current).toLocaleString('fa-IR');
                }
            }, 30);
        }

        // Animate counters on load
        window.addEventListener('load', () => {
            animateCounter('projectCount', <?php echo $totalProjects; ?>);
            animateCounter('downloadCount', <?php echo $totalDownloads; ?>);
            animateCounter('starCount', <?php echo $totalStars; ?>);
        });

        // Back to top functionality
        const backToTopBtn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.pageYOffset > 300) {
                backToTopBtn.classList.remove('opacity-0', 'pointer-events-none');
            } else {
                backToTopBtn.classList.add('opacity-0', 'pointer-events-none');
            }
        });
        backToTopBtn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        // Tabs functionality
        const tabButtons = document.querySelectorAll('.tab-btn');
        const showTab = (name) => {
            // Hide all tabs
            ['overview', 'about', 'activity', 'achievements'].forEach(id => {
                const el = document.getElementById('tab-' + id);
                if (el) el.classList.add('hidden');
            });
            
            // Remove active class from all buttons
            tabButtons.forEach(b => {
                b.classList.remove('tab-active');
                b.classList.add('text-gray-700', 'hover:bg-white/50');
            });
            
            // Show selected tab
            const btn = document.querySelector(`[data-tab="${name}"]`);
            if (btn) {
                btn.classList.add('tab-active');
                btn.classList.remove('text-gray-700', 'hover:bg-white/50');
            }
            
            const el = document.getElementById('tab-' + name);
            if (el) el.classList.remove('hidden');
        };

        tabButtons.forEach(btn => btn.addEventListener('click', () => {
            const tab = btn.getAttribute('data-tab');
            showTab(tab);
        }));

        <?php if ($isOwner): ?>
        // Edit Profile Modal
        const modal = document.getElementById('editProfileModal');
        const openBtn = document.getElementById('openEditProfile');
        const closeBtn = document.getElementById('closeEditProfile');
        const cancelBtn = document.getElementById('cancelEditProfile');
        const form = document.getElementById('editProfileForm');
        const csrf = '<?php echo htmlspecialchars($csrfToken); ?>';
        
        function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); }
        function closeModal() { modal.classList.add('hidden'); modal.classList.remove('flex'); }
        
        openBtn?.addEventListener('click', openModal);
        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>در حال ذخیره...';
            submitBtn.disabled = true;
            
            const fd = new FormData(form);
            fd.append('action', 'update_profile');
            
            try {
                const res = await fetch('api/profile.php', { method: 'POST', body: fd });
                const data = await res.json();
                if (data.ok) { 
                    location.reload(); 
                    return; 
                }
                alert(data.error || 'خطا در بروزرسانی پروفایل');
            } catch(err) { 
                alert('خطا در اتصال به سرور'); 
            } finally {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }
        });

        // Avatar selection
        let selectedAvatar = null;
        
        async function openAvatarModal() {
            const modal = document.getElementById('avatarModal');
            const optionsContainer = document.getElementById('avatarOptions');
            
            try {
                const res = await fetch('api/profile.php?action=get_avatar_options');
                const data = await res.json();
                
                if (data.ok && data.avatars) {
                    optionsContainer.innerHTML = '';
                    Object.entries(data.avatars).forEach(([key, url]) => {
                        const avatarDiv = document.createElement('div');
                        avatarDiv.className = 'relative cursor-pointer group';
                        avatarDiv.innerHTML = `
                            <div class="w-20 h-20 rounded-xl overflow-hidden border-2 border-gray-200 hover:border-blue-500 transition-all duration-300 transform group-hover:scale-105">
                                <img src="${url}" alt="Avatar ${key}" class="w-full h-full object-cover bg-white">
                            </div>
                            <div class="absolute inset-0 bg-blue-500/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <i class="fas fa-check text-white text-lg"></i>
                            </div>
                        `;
                        
                        avatarDiv.addEventListener('click', () => {
                            // Remove previous selection
                            document.querySelectorAll('.avatar-selected').forEach(el => {
                                el.classList.remove('avatar-selected');
                                el.querySelector('.border-2').classList.remove('border-blue-500');
                                el.querySelector('.border-2').classList.add('border-gray-200');
                            });
                            
                            // Add selection to clicked avatar
                            avatarDiv.classList.add('avatar-selected');
                            avatarDiv.querySelector('.border-2').classList.remove('border-gray-200');
                            avatarDiv.querySelector('.border-2').classList.add('border-blue-500');
                            selectedAvatar = key;
                        });
                        
                        optionsContainer.appendChild(avatarDiv);
                    });
                }
            } catch (error) {
                console.error('Error loading avatars:', error);
            }
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeAvatarModal() {
            const modal = document.getElementById('avatarModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            selectedAvatar = null;
        }
        
        async function saveAvatar() {
            if (!selectedAvatar) {
                alert('لطفاً آواتاری انتخاب کنید');
                return;
            }
            
            const saveBtn = document.getElementById('saveAvatarBtn');
            const originalText = saveBtn.textContent;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>در حال ذخیره...';
            
            try {
                const fd = new FormData();
                fd.append('action', 'update_avatar');
                fd.append('csrf_token', csrf);
                fd.append('avatar', selectedAvatar);
                
                const res = await fetch('api/profile.php', { method: 'POST', body: fd });
                const data = await res.json();
                
                if (data.ok) {
                    location.reload();
                } else {
                    alert(data.error || 'خطا در بروزرسانی آواتار');
                }
            } catch (error) {
                alert('خطا در اتصال به سرور');
            } finally {
                saveBtn.disabled = false;
                saveBtn.textContent = originalText;
            }
        }
        
        // Make functions global for onclick handlers
        window.openAvatarModal = openAvatarModal;
        window.closeAvatarModal = closeAvatarModal;
        window.saveAvatar = saveAvatar;
        <?php endif; ?>

        // Follow functionality (outside owner check)
        const followBtn = document.getElementById('followBtn');
        if (followBtn) {
            followBtn.addEventListener('click', async function() {
                const userId = this.dataset.userId;
                const isFollowing = this.dataset.isFollowing === 'true';
                const action = isFollowing ? 'unfollow' : 'follow';
                
                this.disabled = true;
                this.style.opacity = '0.7';
                
                try {
                    const formData = new FormData();
                    formData.append('action', action);
                    formData.append('user_id', userId);
                    formData.append('csrf_token', '<?php echo htmlspecialchars($csrfToken); ?>');
                    
                    const response = await fetch('api/profile.php', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.ok) {
                        // Update button state
                        const newIsFollowing = !isFollowing;
                        this.dataset.isFollowing = newIsFollowing.toString();
                        
                        const icon = this.querySelector('i');
                        const text = this.querySelector('.follow-text');
                        
                        if (newIsFollowing) {
                            this.className = this.className.replace('not-following', 'following');
                            icon.className = 'fas fa-user-check ml-2';
                            text.textContent = 'دنبال می‌کنید';
                        } else {
                            this.className = this.className.replace('following', 'not-following');
                            icon.className = 'fas fa-user-plus ml-2';
                            text.textContent = 'دنبال کردن';
                        }
                        
                        // Update follower count (simple increment/decrement)
                        const followerCount = document.querySelector('.text-center .text-2xl.font-bold.text-white');
                        if (followerCount) {
                            const currentCount = parseInt(followerCount.textContent);
                            followerCount.textContent = newIsFollowing ? currentCount + 1 : currentCount - 1;
                        }
                        
                        // Show success message
                        showNotification(result.message, 'success');
                    } else {
                        throw new Error(result.error || 'خطا در انجام عملیات');
                    }
                } catch (error) {
                    showNotification(error.message, 'error');
                } finally {
                    this.disabled = false;
                    this.style.opacity = '1';
                }
            });
        }

        function showToast(message, type) {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.toast');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast fixed top-4 right-4 px-6 py-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} ml-3"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }
        
        // Notification function for follow functionality
        function showNotification(message, type = 'info') {
            // Create notification container if doesn't exist
            let container = document.getElementById('notificationContainer');
            if (!container) {
                container = document.createElement('div');
                container.id = 'notificationContainer';
                container.className = 'fixed top-4 right-4 z-50 space-y-2';
                document.body.appendChild(container);
            }
            
            // Create notification
            const notification = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
            notification.className = `${bgColor} text-white px-6 py-3 rounded-lg shadow-lg transform translate-x-full transition-transform duration-300`;
            notification.textContent = message;
            
            container.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto hide
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }
    </script>
</body>
</html>