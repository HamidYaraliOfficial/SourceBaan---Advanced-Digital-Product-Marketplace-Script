<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';

if (!current_user()) { header('Location: index.php'); exit; }
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<?php $csrfToken = csrf_get_token(); ?>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>حساب من - سورس کده</title>
<meta name="description" content="مدیریت حساب کاربری، پروژه‌ها و آمار شخصی در سورس کده">
<meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    
<!-- Favicon -->
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link rel="preconnect" href="https://cdnjs.cloudflare.com"/>
<link rel="preconnect" href="https://cdn.tailwindcss.com"/>

<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<script>
    tailwind.config = {
        theme: {
            extend: {
                fontFamily: {
                    'vazir': ['Vazirmatn', 'sans-serif']
                },
                animation: {
                    'float': 'float 8s ease-in-out infinite',
                    'glow': 'glow 2s ease-in-out infinite alternate',
                    'pulse-slow': 'pulse 3s ease-in-out infinite',
                    'shimmer': 'shimmer 2s linear infinite',
                    'bounce-slow': 'bounce-slow 3s ease-in-out infinite'
                },
                keyframes: {
                    float: {
                        '0%, 100%': { transform: 'translateY(0px) rotate(0deg)' },
                        '50%': { transform: 'translateY(-12px) rotate(3deg)' }
                    },
                    glow: {
                        '0%': { boxShadow: '0 0 20px rgba(102, 126, 234, 0.3)' },
                        '100%': { boxShadow: '0 0 40px rgba(102, 126, 234, 0.6)' }
                    },
                    shimmer: {
                        '0%': { backgroundPosition: '-200% 0' },
                        '100%': { backgroundPosition: '200% 0' }
                    },
                    'bounce-slow': {
                        '0%, 100%': { transform: 'translateY(0)' },
                        '50%': { transform: 'translateY(-8px)' }
                    }
                }
            }
        }
    }
</script>
<style>
* { font-family: 'Vazirmatn', sans-serif; }

/* Modern Navigation */
.modern-nav {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border-bottom: 1px solid rgba(0, 0, 0, 0.06);
    box-shadow: 
        0 8px 40px rgba(0, 0, 0, 0.06),
        0 4px 20px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.8);
}

/* Enhanced Gradients */
.gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.gradient-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.gradient-warning { background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%); }
.gradient-danger { background: linear-gradient(135deg, #ff8a80 0%, #ff5722 100%); }

/* Enhanced Glass Morphism */
.glassmorphism { 
    background: linear-gradient(145deg, rgba(255, 255, 255, 0.9) 0%, rgba(255, 255, 255, 0.8) 100%);
    backdrop-filter: blur(25px);
    -webkit-backdrop-filter: blur(25px);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 
        0 15px 40px rgba(0, 0, 0, 0.08),
        0 8px 20px rgba(0, 0, 0, 0.04),
        inset 0 1px 0 rgba(255, 255, 255, 0.7);
}

/* Modern Card Hover */
.card-hover { 
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
    position: relative;
    overflow: hidden;
}
.card-hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 2px;
    background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.4), transparent);
    opacity: 0;
    transition: opacity 0.3s ease;
}
.card-hover:hover::before {
    opacity: 1;
}
.card-hover:hover { 
    transform: translateY(-12px) scale(1.03);
    box-shadow: 
        0 30px 60px rgba(0,0,0,0.12),
        0 15px 30px rgba(0,0,0,0.08),
        0 0 0 1px rgba(102, 126, 234, 0.1);
}

/* Float Animation */
.float-element {
    animation: float 8s ease-in-out infinite;
}
@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-15px) rotate(2deg); }
}

/* Enhanced Glow Effect */
.pulse-glow {
    animation: pulse-glow 3s ease-in-out infinite alternate;
    position: relative;
}
@keyframes pulse-glow {
    0% { 
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
        transform: scale(1);
    }
    100% { 
        box-shadow: 0 0 40px rgba(102, 126, 234, 0.6), 0 0 60px rgba(102, 126, 234, 0.3);
        transform: scale(1.05);
    }
}

/* Slide Up Animation */
.slide-in-up {
    animation: slideInUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
}
@keyframes slideInUp {
    0% {
        opacity: 0;
        transform: translateY(40px) scale(0.95);
    }
    100% {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Stagger Animations */
.stagger-1 { animation-delay: 0.1s; }
.stagger-2 { animation-delay: 0.2s; }
.stagger-3 { animation-delay: 0.3s; }
.stagger-4 { animation-delay: 0.4s; }
.stagger-5 { animation-delay: 0.5s; }
.stagger-6 { animation-delay: 0.6s; }

/* Text Gradient */
.text-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    background-clip: text;
    -webkit-text-fill-color: transparent;
    font-weight: 900;
}

/* Enhanced Skeletons */
.skeleton { 
    background: linear-gradient(90deg, rgba(0,0,0,0.04), rgba(0,0,0,0.08), rgba(0,0,0,0.04)); 
    background-size: 200% 100%; 
    animation: skeleton 1.5s ease-in-out infinite; 
}
@keyframes skeleton { 
    0% { background-position: 200% 0; } 
    100% { background-position: -200% 0; } 
}

/* Advanced performance optimizations */
.virtual-scroll { height: 400px; overflow-y: auto; contain: strict; }
.performance-mode * { animation: none !important; transition: opacity .1s !important; }
.gpu-accelerated { transform: translateZ(0); will-change: transform; }
.memory-efficient { contain: layout style paint; }
.defer-animate { opacity: 0; transform: translateY(12px); transition: opacity .4s ease, transform .4s ease; }
.defer-animate.animate { opacity: 1; transform: translateY(0); }

/* Lazy image loading */
.lazy-img { opacity: 0; transition: opacity .3s; }
.lazy-img.loaded { opacity: 1; }

/* Modern Buttons */
.btn-modern {
    position: relative;
    overflow: hidden;
    transition: all 0.3s ease;
    border-radius: 16px;
    font-weight: 600;
    letter-spacing: 0.025em;
}
.btn-modern::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}
.btn-modern:hover::before {
    left: 100%;
}
.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.15);
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .card-hover, .float-element, .pulse-glow, .slide-in-up { 
        animation: none !important; 
        transition: none !important; 
    }
}

/* Responsive improvements */
@media (max-width: 768px) {
    .glassmorphism {
        backdrop-filter: blur(15px);
        -webkit-backdrop-filter: blur(15px);
    }
    .card-hover:hover {
        transform: translateY(-6px) scale(1.02);
    }
}
</style>
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 relative overflow-x-hidden font-vazir">
  <!-- Enhanced Animated Background Elements -->
  <div class="fixed inset-0 overflow-hidden pointer-events-none">
    <div class="absolute top-20 right-20 w-72 h-72 bg-gradient-to-br from-blue-400/15 to-purple-600/15 rounded-full float-element"></div>
    <div class="absolute bottom-20 left-20 w-96 h-96 bg-gradient-to-tr from-pink-400/15 to-yellow-500/15 rounded-full float-element" style="animation-delay: -3s;"></div>
    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-gradient-to-r from-green-400/15 to-blue-500/15 rounded-full float-element" style="animation-delay: -1.5s;"></div>
  </div>

  <!-- Modern Navigation Header -->
  <nav class="modern-nav sticky top-0 z-50 transition-all duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center h-20">
        <div class="flex items-center space-x-4 space-x-reverse">
          <a href="index.php" class="group flex items-center space-x-3 space-x-reverse">
            <div class="w-12 h-12 rounded-3xl bg-gradient-to-br from-purple-600 via-blue-600 to-indigo-700 shadow-2xl border-2 border-white/40 overflow-hidden relative transform transition-all duration-500 group-hover:scale-110 group-hover:shadow-3xl float-element flex items-center justify-center">
              <i class="fas fa-code text-white text-lg transition-all duration-500 group-hover:rotate-12 group-hover:scale-125"></i>
              <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-r from-emerald-400 to-green-500 rounded-full shadow-xl">
                <div class="w-full h-full bg-emerald-300 rounded-full animate-pulse-slow"></div>
              </div>
              <div class="absolute inset-0 bg-gradient-to-t from-white/10 via-transparent to-white/5 rounded-3xl"></div>
            </div>
            <div>
              <h1 class="text-xl font-black bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent">
                سورس کده
              </h1>
              <p class="text-xs text-gray-600 font-medium">پنل کاربری</p>
            </div>
          </a>
        </div>
        
        <div class="flex items-center space-x-3 space-x-reverse">
          <button class="btn-modern glassmorphism px-4 py-2 rounded-2xl text-gray-700 transition-all duration-300">
            <i class="fas fa-cog ml-2"></i>
            <span class="hidden sm:inline">تنظیمات</span>
          </button>
        </div>
      </div>
    </div>
  </nav>

  <main class="relative z-10 max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 lazy-section">
    <!-- Enhanced Header Section -->
    <div class="glassmorphism rounded-3xl p-6 sm:p-8 mb-8 slide-in-up relative overflow-hidden">
      <div class="absolute top-0 right-0 w-40 h-40 bg-gradient-to-bl from-blue-500/10 to-transparent rounded-full -translate-y-20 translate-x-20"></div>
      <div class="absolute bottom-0 left-0 w-32 h-32 bg-gradient-to-tr from-purple-500/10 to-transparent rounded-full translate-y-16 -translate-x-16"></div>
      
      <div class="relative z-10 flex flex-col sm:flex-row items-start sm:items-center justify-between">
        <div class="flex items-center space-x-4 sm:space-x-6 space-x-reverse mb-4 sm:mb-0">
          <div class="w-16 h-16 sm:w-20 sm:h-20 gradient-primary rounded-3xl flex items-center justify-center text-white font-black text-2xl sm:text-3xl pulse-glow shadow-2xl" id="headerAvatar">
            M
          </div>
          <div>
            <h1 class="text-3xl sm:text-4xl font-black text-gradient mb-2">حساب من</h1>
            <p class="text-gray-600 text-sm sm:text-base flex items-center">
              <i class="fas fa-user-cog ml-2 text-blue-500"></i>
              مدیریت حساب کاربری و پروژه‌ها
            </p>
          </div>
        </div>
        
        <div class="flex items-center space-x-3 space-x-reverse">
          <div class="text-center">
            <div class="text-2xl font-black text-blue-600" id="userPointsDisplay">0</div>
            <div class="text-xs text-gray-500 font-medium">امتیاز کل</div>
          </div>
        </div>
      </div>
    </div>

    <!-- User Profile Card -->
    <div id="userCard" class="glassmorphism rounded-3xl p-6 sm:p-8 mb-8 card-hover overflow-hidden relative slide-in-up stagger-1">
      <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-500/10 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
      <div class="absolute bottom-0 left-0 w-40 h-40 bg-gradient-to-tr from-purple-500/10 to-transparent rounded-full translate-y-20 -translate-x-20"></div>
      <div class="relative z-10" id="profileContent">
        <!-- Skeleton while loading -->
        <div class="flex items-center gap-4">
          <div class="w-16 h-16 rounded-3xl skeleton"></div>
          <div class="flex-1">
            <div class="h-5 w-40 rounded-md skeleton mb-2"></div>
            <div class="h-4 w-64 rounded-md skeleton"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Enhanced Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4 sm:gap-6 mb-8">
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-1 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-purple-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 gradient-primary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-folder text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="projectCount">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">پروژه منتشر شده</div>
        </div>
      </div>
      
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-2 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-pink-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 gradient-secondary rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-clock text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="submissionCount">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">در انتظار تایید</div>
        </div>
      </div>
      
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-3 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/5 to-blue-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 gradient-success rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-download text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="totalDownloads">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">مجموع دانلود</div>
        </div>
      </div>
      
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-4 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/5 to-orange-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-star text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="totalStars">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">مجموع ستاره</div>
        </div>
      </div>
      
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-5 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-indigo-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-users text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="followersCount">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">دنبال‌کننده</div>
        </div>
      </div>
      
      <div class="glassmorphism rounded-3xl p-4 sm:p-6 card-hover text-center slide-in-up stagger-6 relative overflow-hidden group">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-600/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
        <div class="relative z-10">
          <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-r from-emerald-500 to-teal-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl group-hover:scale-110 transition-transform duration-300">
            <i class="fas fa-user-friends text-white text-lg sm:text-xl"></i>
          </div>
          <div class="text-2xl sm:text-3xl font-black text-gray-900 mb-1" id="followingCount">0</div>
          <div class="text-xs sm:text-sm text-gray-600 font-medium">دنبال‌شده</div>
        </div>
      </div>
    </div>

    <!-- Enhanced Main Content -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 lg:gap-8">
      <!-- My Projects -->
      <div class="glassmorphism rounded-3xl p-6 sm:p-8 card-hover slide-in-up stagger-2 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-blue-500/5 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
        
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3 space-x-reverse">
              <div class="w-12 h-12 gradient-primary rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-folder text-white text-lg"></i>
              </div>
              <div>
                <h2 class="text-xl sm:text-2xl font-black text-gray-900">پروژه‌های من</h2>
                <p class="text-sm text-gray-600">پروژه‌های منتشر شده</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg" id="projectBadge">0</div>
          </div>
          <div id="myProjects" class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar"></div>
        </div>
      </div>

      <!-- My Submissions -->
      <div class="glassmorphism rounded-3xl p-6 sm:p-8 card-hover slide-in-up stagger-3 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-gradient-to-bl from-purple-500/5 to-transparent rounded-full -translate-y-16 translate-x-16"></div>
        
        <div class="relative z-10">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center space-x-3 space-x-reverse">
              <div class="w-12 h-12 gradient-secondary rounded-2xl flex items-center justify-center shadow-lg">
                <i class="fas fa-clock text-white text-lg"></i>
              </div>
              <div>
                <h2 class="text-xl sm:text-2xl font-black text-gray-900">ارسال‌های من</h2>
                <p class="text-sm text-gray-600">پروژه‌های در انتظار تایید</p>
              </div>
            </div>
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white px-4 py-2 rounded-full text-sm font-bold shadow-lg" id="submissionBadge">0</div>
          </div>
          <div id="mySubmissions" class="space-y-4 max-h-96 overflow-y-auto custom-scrollbar"></div>
        </div>
      </div>
    </div>
    
    <!-- Edit Profile Section -->
    <div id="edit" class="mt-8 glassmorphism rounded-3xl p-6 sm:p-8 slide-in-up stagger-5">
      <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
        <i class="fas fa-user-edit text-blue-500 ml-3"></i>
        ویرایش پروفایل
      </h3>
      <form id="profileForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">نام</label>
          <input id="pfName" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="نام شما">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">وب‌سایت</label>
          <input id="pfWebsite" type="url" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="https://example.com">
        </div>
        <div class="md:col-span-2">
          <label class="block text-sm font-medium text-gray-700 mb-2">بیوگرافی</label>
          <textarea id="pfBio" rows="3" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="درباره شما..."></textarea>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">گیت‌هاب</label>
          <input id="pfGithub" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="username یا https://github.com/username">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">توئیتر</label>
          <input id="pfTwitter" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="username یا https://twitter.com/username">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">تلگرام</label>
          <input id="pfTelegram" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="@handle یا https://t.me/handle">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">استان</label>
          <input id="pfProvince" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="استان">
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-2">شهر</label>
          <input id="pfCity" type="text" class="w-full rounded-xl border-gray-200 focus:ring-2 focus:ring-blue-500" placeholder="شهر">
        </div>
        <div class="md:col-span-2 text-left">
          <button class="btn-modern gradient-primary text-white px-6 py-3 rounded-2xl font-bold" type="submit">
            ذخیره تغییرات
          </button>
        </div>
      </form>
    </div>
    
    <!-- Quick Actions Section -->
    <div class="mt-8 glassmorphism rounded-3xl p-6 sm:p-8 slide-in-up stagger-4">
      <h3 class="text-xl font-black text-gray-900 mb-6 flex items-center">
        <i class="fas fa-bolt text-yellow-500 ml-3"></i>
        اقدامات سریع
      </h3>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <button id="qaNewProject" class="btn-modern gradient-primary text-white p-4 rounded-2xl text-center hover:scale-105 transition-all duration-300">
          <i class="fas fa-plus text-xl mb-2"></i>
          <div class="text-sm font-bold">پروژه جدید</div>
        </button>
        <button id="qaStats" class="btn-modern gradient-success text-white p-4 rounded-2xl text-center hover:scale-105 transition-all duration-300">
          <i class="fas fa-chart-line text-xl mb-2"></i>
          <div class="text-sm font-bold">آمار پیشرفته</div>
        </button>
        <button id="qaEditProfile" class="btn-modern gradient-secondary text-white p-4 rounded-2xl text-center hover:scale-105 transition-all duration-300">
          <i class="fas fa-user-edit text-xl mb-2"></i>
          <div class="text-sm font-bold">ویرایش پروفایل</div>
        </button>
        <button id="qaExport" class="btn-modern gradient-warning text-white p-4 rounded-2xl text-center hover:scale-105 transition-all duration-300">
          <i class="fas fa-download text-xl mb-2"></i>
          <div class="text-sm font-bold">دانلود داده‌ها</div>
        </button>
      </div>
    </div>
  </main>

<style>
.custom-scrollbar::-webkit-scrollbar { width: 6px; }
.custom-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #667eea, #764ba2); border-radius: 3px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: linear-gradient(135deg, #5a67d8, #6b46c1); }
</style>

<script>
fetch('api/me.php').then(r=>r.json()).then(({ok,user,myProjects,mySubmissions,followersCount,followingCount})=>{
    if (!ok) { window.location.href = 'index.php'; return; }
    
    // Update header avatar
    const avatar = (user.name||'?').charAt(0).toUpperCase();
    document.getElementById('headerAvatar').textContent = avatar;
    
    // Update points display
    document.getElementById('userPointsDisplay').textContent = (user.points || 0).toLocaleString('fa-IR');
    
    // Calculate totals
    const totalDownloads = myProjects.reduce((sum, p) => sum + (p.downloads||0), 0);
    const totalStars = myProjects.reduce((sum, p) => sum + (p.stars||0), 0);
    
    // Update profile card with enhanced design
    document.getElementById('profileContent').innerHTML = `
      <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between space-y-4 sm:space-y-0">
        <div class="flex items-center space-x-4 sm:space-x-6 space-x-reverse">
          <div class="relative">
            <div class="w-16 h-16 sm:w-20 sm:h-20 gradient-primary rounded-3xl flex items-center justify-center text-white font-bold text-xl sm:text-2xl shadow-2xl">
              ${escapeHtml(avatar)}
            </div>
            <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-green-500 border-2 border-white rounded-full"></div>
          </div>
          <div>
            <h3 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">${escapeHtml(user.name)}</h3>
            <p class="text-gray-600 mb-3 flex items-center">
              <i class="fas fa-envelope text-blue-500 ml-2"></i>
              ${escapeHtml(user.email)}
            </p>
            <div class="flex flex-wrap items-center gap-2">
              <span class="px-3 py-1 ${(user.role==='admin')?'bg-gradient-to-r from-red-500 to-pink-600 text-white':'bg-gradient-to-r from-blue-500 to-purple-600 text-white'} rounded-full text-sm font-medium shadow-md">
                <i class="fas ${(user.role==='admin')?'fa-crown':'fa-user'} ml-1"></i>
                ${(user.role==='admin')?'ادمین':'کاربر'}
              </span>
              <span class="px-3 py-1 bg-gradient-to-r from-green-500 to-teal-600 text-white rounded-full text-sm font-medium shadow-md">
                <i class="fas fa-coins ml-1"></i>
                ${user.points||0} امتیاز
              </span>
            </div>
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-500 mb-1">عضو از</div>
          <div class="text-lg font-bold text-gray-800">${new Date(user.createdAt||Date.now()).toLocaleDateString('fa-IR')}</div>
          <div class="text-xs text-gray-500 mt-1">
            <i class="fas fa-calendar-alt ml-1"></i>
            ${Math.floor((Date.now() - new Date(user.createdAt||Date.now()).getTime()) / (1000*60*60*24))} روز
          </div>
        </div>
      </div>`;
    
    // Update stats with animation
    animateCounter('projectCount', myProjects.length);
    animateCounter('submissionCount', mySubmissions.filter(s => s.status === 'pending').length);
    animateCounter('totalDownloads', totalDownloads);
    animateCounter('totalStars', totalStars);
    animateCounter('followersCount', followersCount || 0);
    animateCounter('followingCount', followingCount || 0);
    
    document.getElementById('projectBadge').textContent = myProjects.length;
    document.getElementById('submissionBadge').textContent = mySubmissions.length;
    
    // Update projects and submissions
    const proj = myProjects.map(p=>projectCard(p)).join('')||emptyState('پروژه‌ای ندارید', 'پروژه اول خود را آپلود کنید!', 'fa-folder-plus');
    const subs = mySubmissions.map(s=>submissionCard(s)).join('')||emptyState('ارسالی ندارید', 'هنوز پروژه‌ای ارسال نکرده‌اید', 'fa-upload');
    document.getElementById('myProjects').innerHTML = proj;
    document.getElementById('mySubmissions').innerHTML = subs;
}).catch(()=>{ window.location.href='index.php'; });

// Quick Actions Handlers
document.addEventListener('DOMContentLoaded', ()=>{
  const btnNew = document.getElementById('qaNewProject');
  const btnStats = document.getElementById('qaStats');
  const btnEdit = document.getElementById('qaEditProfile');
  const btnExport = document.getElementById('qaExport');

  if (btnNew) btnNew.addEventListener('click', ()=>{
    window.location.href = 'upload.php';
  });

  if (btnEdit) btnEdit.addEventListener('click', ()=>{
    window.location.href = 'account.php#edit';
    const el = document.getElementById('edit');
    if (el) el.scrollIntoView({behavior:'smooth', block:'start'});
  });

  if (btnStats) btnStats.addEventListener('click', ()=>{
    openStatsModal();
  });

  if (btnExport) btnExport.addEventListener('click', async ()=>{
    try {
      const res = await fetch('api/me.php');
      const data = await res.json();
      const blob = new Blob([JSON.stringify(data, null, 2)], {type: 'application/json'});
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = 'sourcekade-account-export.json';
      document.body.appendChild(link);
      link.click();
      document.body.removeChild(link);
    } catch (_) {}
  });
});

function openStatsModal(){
  const existing = document.getElementById('statsModal');
  if (existing) existing.remove();
  const modal = document.createElement('div');
  modal.id = 'statsModal';
  modal.className = 'fixed inset-0 z-50 flex items-center justify-center p-4';
  modal.innerHTML = `
    <div class="absolute inset-0 bg-black/50" onclick="document.getElementById('statsModal').remove()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden">
      <div class="px-6 py-4 border-b flex items-center justify-between">
        <h3 class="text-lg font-bold text-gray-900">آمار پیشرفته</h3>
        <button class="text-gray-500 hover:text-gray-700" onclick="document.getElementById('statsModal').remove()"><i class="fas fa-times"></i></button>
      </div>
      <div class="p-6 grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
        <div class="glassmorphism p-4 rounded-xl">
          <div class="text-gray-500 mb-1">پروژه‌ها</div>
          <div class="text-2xl font-black text-gray-900" id="statProjects">-</div>
        </div>
        <div class="glassmorphism p-4 rounded-xl">
          <div class="text-gray-500 mb-1">دانلودها</div>
          <div class="text-2xl font-black text-gray-900" id="statDownloads">-</div>
        </div>
        <div class="glassmorphism p-4 rounded-xl">
          <div class="text-gray-500 mb-1">ستاره‌ها</div>
          <div class="text-2xl font-black text-gray-900" id="statStars">-</div>
        </div>
        <div class="glassmorphism p-4 rounded-xl">
          <div class="text-gray-500 mb-1">دنبال‌کننده</div>
          <div class="text-2xl font-black text-gray-900" id="statFollowers">-</div>
        </div>
      </div>
      <div class="px-6 py-4 border-t text-left">
        <button class="btn-modern gradient-primary text-white px-5 py-2 rounded-xl" onclick="document.getElementById('statsModal').remove()">باشه</button>
      </div>
    </div>
  `;
  document.body.appendChild(modal);
  // fill data
  try{
    fetch('api/me.php').then(r=>r.json()).then(({ok,myProjects,followersCount})=>{
      if (!ok) return;
      const downloads = (myProjects||[]).reduce((s,p)=> s + (p.downloads||0), 0);
      const stars = (myProjects||[]).reduce((s,p)=> s + (p.stars||0), 0);
      document.getElementById('statProjects').textContent = (myProjects||[]).length.toLocaleString('fa-IR');
      document.getElementById('statDownloads').textContent = downloads.toLocaleString('fa-IR');
      document.getElementById('statStars').textContent = stars.toLocaleString('fa-IR');
      document.getElementById('statFollowers').textContent = (followersCount||0).toLocaleString('fa-IR');
    });
  }catch(_){ }
}

// Enhanced profile form handler with performance optimizations
document.addEventListener('DOMContentLoaded', ()=>{
  // Performance monitoring
  const startTime = performance.now();
  let isLowEndDevice = false;
  
  // Device capability detection
  if (navigator.deviceMemory && navigator.deviceMemory < 4) {
    isLowEndDevice = true;
    document.body.classList.add('performance-mode');
  }
  
  // Connection-aware optimizations
  if (navigator.connection) {
    const connection = navigator.connection;
    if (connection.saveData || connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g') {
      document.body.classList.add('performance-mode');
      isLowEndDevice = true;
    }
  }
  
  // Intersection Observer for deferred animations
  const deferredElements = document.querySelectorAll('.glassmorphism, .card-hover');
  if (deferredElements.length && !isLowEndDevice) {
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('defer-animate', 'animate');
          observer.unobserve(entry.target);
        }
      });
    }, { rootMargin: '50px', threshold: 0.1 });
    
    deferredElements.forEach(el => observer.observe(el));
  }
  
  // Performance monitoring
  requestAnimationFrame(() => {
    const loadTime = performance.now() - startTime;
    if (loadTime > 2000) {
      console.warn('Slow page load detected:', loadTime + 'ms');
      document.body.classList.add('performance-mode');
    }
  });
  
  // Pause animations when tab is not visible
  document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
      document.body.classList.add('performance-mode');
    } else if (!isLowEndDevice) {
      document.body.classList.remove('performance-mode');
    }
  });
  try{
    fetch('api/me.php', {headers: {'Cache-Control':'no-cache'}}).then(r=>r.json()).then(({ok,user})=>{
      if (!ok || !user) return;
      document.getElementById('pfName').value = user.name || '';
      document.getElementById('pfBio').value = user.bio || '';
      document.getElementById('pfWebsite').value = user.website || '';
      document.getElementById('pfGithub').value = (user.github||'').replace(/^https?:\/\//,'');
      document.getElementById('pfTwitter').value = (user.twitter||'').replace(/^https?:\/\//,'');
      document.getElementById('pfTelegram').value = (user.telegram||'').replace(/^https?:\/\//,'');
      document.getElementById('pfProvince').value = user.province || '';
      document.getElementById('pfCity').value = user.city || '';
    });
  }catch(_){ }

  // Optimized form submission with throttling
  const form = document.getElementById('profileForm');
  if (form) {
    let isSubmitting = false;
    
    form.addEventListener('submit', async (e)=>{
      e.preventDefault();
      
      if (isSubmitting) return;
      isSubmitting = true;
      
      const fd = new FormData();
      fd.append('action','update_profile');
      const csrf = document.querySelector('meta[name="csrf-token"]');
      if (csrf) fd.append('csrf_token', csrf.getAttribute('content'));
      
      // Batch form data collection
      const formFields = ['pfName', 'pfBio', 'pfWebsite', 'pfGithub', 'pfTwitter', 'pfTelegram', 'pfProvince', 'pfCity'];
      const fieldMap = {pfName: 'name', pfBio: 'bio', pfWebsite: 'website', pfGithub: 'github', pfTwitter: 'twitter', pfTelegram: 'telegram', pfProvince: 'province', pfCity: 'city'};
      
      formFields.forEach(fieldId => {
        const el = document.getElementById(fieldId);
        if (el) fd.append(fieldMap[fieldId], el.value.trim());
      });
      
      try{
        const res = await fetch('api/profile.php', {
          method:'POST', 
          body: fd, 
          headers: {'X-Requested-With':'XMLHttpRequest'}
        });
        const data = await res.json();
        
        if (data.ok){
          showToast('تغییرات ذخیره شد');
          // Refresh CSRF token after successful write
          try{ 
            const t = await fetch('api/me.php', {headers: {'Cache-Control':'no-cache'}}).then(r=>r.json()); 
            if (t && t.csrf) { 
              const m=document.querySelector('meta[name="csrf-token"]'); 
              if (m) m.setAttribute('content', t.csrf); 
            } 
          }catch(_){}
        } else {
          showToast(data.error||'خطا در ذخیره تغییرات', 'error');
        }
      }catch(_){ 
        showToast('خطای شبکه', 'error'); 
      } finally {
        isSubmitting = false;
      }
    });
  }
});

function showToast(msg, type='success'){
  const toast = document.createElement('div');
  toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 z-50 px-4 py-2 rounded-xl shadow-lg text-white';
  toast.style.background = type==='success' ? '#10b981' : '#ef4444';
  toast.textContent = msg;
  document.body.appendChild(toast);
  setTimeout(()=>{ toast.remove(); }, 2500);
}
function animateCounter(id, target) {
    const element = document.getElementById(id);
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
    }, 20);
}

function emptyState(title, desc, icon = 'fa-file'){ 
    return `<div class="text-center py-16">
        <div class="relative mb-6">
            <div class="w-20 h-20 bg-gradient-to-br from-gray-100 to-gray-200 rounded-3xl flex items-center justify-center mx-auto shadow-lg">
                <i class="fas ${icon} text-3xl text-gray-400"></i>
            </div>
            <div class="absolute -top-2 -right-2 w-6 h-6 bg-blue-100 rounded-full animate-pulse"></div>
            <div class="absolute -bottom-2 -left-2 w-4 h-4 bg-purple-100 rounded-full animate-pulse" style="animation-delay: 0.5s"></div>
        </div>
        <h3 class="text-xl font-black text-gray-900 mb-3">${title}</h3>
        <p class="text-gray-600 text-sm leading-relaxed max-w-sm mx-auto">${desc}</p>
        <div class="mt-6">
            <button class="btn-modern gradient-primary text-white px-6 py-3 rounded-2xl font-bold text-sm hover:scale-105 transition-all duration-300">
                <i class="fas fa-plus ml-2"></i>
                شروع کنید
            </button>
        </div>
    </div>`; 
}

function projectCard(p){ 
    const statusIcon = '<i class="fas fa-check-circle text-green-500 text-lg"></i>';
    return `<div class="group glassmorphism p-5 rounded-3xl border border-white/30 card-hover cursor-pointer relative overflow-hidden">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl from-green-500/10 to-transparent rounded-full -translate-y-10 translate-x-10"></div>
        
        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        ${statusIcon}
                        <h4 class="font-black text-lg text-gray-900 group-hover:text-blue-600 transition-colors">${escapeHtml(p.title)}</h4>
                    </div>
                    <p class="text-gray-600 text-sm leading-relaxed">${escapeHtml((p.description||'').substring(0,120))}${(p.description||'').length > 120 ? '...' : ''}</p>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200/50">
                <div class="flex items-center space-x-4 space-x-reverse text-sm">
                    <span class="flex items-center text-green-600 font-bold bg-green-50 px-3 py-1 rounded-full">
                        <i class="fas fa-download ml-2"></i> ${(p.downloads||0).toLocaleString('fa-IR')}
                    </span>
                    <span class="flex items-center text-yellow-600 font-bold bg-yellow-50 px-3 py-1 rounded-full">
                        <i class="fas fa-star ml-2"></i> ${(p.stars||0).toLocaleString('fa-IR')}
                    </span>
                    <span class="flex items-center text-blue-600 font-bold bg-blue-50 px-3 py-1 rounded-full">
                        <i class="fas fa-eye ml-2"></i> ${(p.views||0).toLocaleString('fa-IR')}
                    </span>
                </div>
                <div class="text-xs text-gray-500 bg-gray-100 px-3 py-2 rounded-full font-medium">
                    ${new Date(p.uploadDate).toLocaleDateString('fa-IR')}
                </div>
            </div>
        </div>
    </div>`; 
}

function submissionCard(s){ 
    const statusInfo = {
        'pending': { 
            color: 'bg-gradient-to-r from-yellow-500 to-orange-500', 
            icon: 'fa-clock', 
            text: 'در انتظار تایید',
            bg: 'from-yellow-500/10 to-orange-500/10'
        },
        'approved': { 
            color: 'bg-gradient-to-r from-green-500 to-emerald-500', 
            icon: 'fa-check-circle', 
            text: 'تایید شده',
            bg: 'from-green-500/10 to-emerald-500/10'
        }, 
        'rejected': { 
            color: 'bg-gradient-to-r from-red-500 to-pink-500', 
            icon: 'fa-times-circle', 
            text: 'رد شده',
            bg: 'from-red-500/10 to-pink-500/10'
        }
    };
    const info = statusInfo[s.status||'pending'];
    
    return `<div class="group glassmorphism p-5 rounded-3xl border border-white/30 card-hover relative overflow-hidden">
        <div class="absolute top-0 right-0 w-20 h-20 bg-gradient-to-bl ${info.bg} rounded-full -translate-y-10 translate-x-10"></div>
        
        <div class="relative z-10">
            <div class="flex items-start justify-between mb-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <span class="px-3 py-1.5 ${info.color} text-white rounded-full text-xs font-bold shadow-lg flex items-center">
                            <i class="fas ${info.icon} ml-2"></i>
                            ${info.text}
                        </span>
                    </div>
                    <h4 class="font-black text-lg text-gray-900 mb-3">${escapeHtml(s.title)}</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">${escapeHtml((s.description||'').substring(0,120))}${(s.description||'').length > 120 ? '...' : ''}</p>
                </div>
            </div>
            
            <div class="flex items-center justify-between pt-4 border-t border-gray-200/50">
                <div class="text-xs text-gray-600 flex items-center bg-gray-50 px-3 py-2 rounded-full font-medium">
                    <i class="fas fa-calendar-alt ml-2"></i>
                    ارسال شده در ${new Date(s.createdAt).toLocaleDateString('fa-IR')}
                </div>
                ${s.reason ? `<div class="text-xs text-red-600 bg-red-50 px-3 py-2 rounded-full max-w-xs truncate font-medium" title="${escapeHtml(s.reason)}">
                    <i class="fas fa-exclamation-triangle ml-1"></i>
                    ${escapeHtml(s.reason)}
                </div>` : ''}
            </div>
        </div>
    </div>`; 
}

function escapeHtml(str){ 
    return (str||'').toString().replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[s])); 
}
</script>
</body>
</html>