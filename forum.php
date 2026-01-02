<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$current_user = current_user();
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>انجمن - سورس بان | SourceBan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        body {
            background: #f8fafc;
            color: #334155;
        }
        
        .modern-card { 
            background: white;
            border-radius: 16px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
        }
        
        .modern-card:hover { 
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            transform: translateY(-1px);
        }
        
        .btn-primary {
            background: #3b82f6;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        
        .btn-secondary {
            background: #64748b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-secondary:hover {
            background: #475569;
            transform: translateY(-1px);
        }
        
        .btn-success {
            background: #10b981;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        
        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }
        
        .slide-in { 
            animation: slideIn 0.6s ease-out; 
            will-change: transform, opacity; 
        }
        
        @keyframes slideIn { 
            from { opacity: 0; transform: translateY(20px) translateZ(0); } 
            to { opacity: 1; transform: translateY(0) translateZ(0); } 
        }
        
        .notification { 
            position: fixed; 
            top: 20px; 
            right: 20px; 
            z-index: 1000; 
            transform: translateX(400px); 
            transition: transform 0.3s ease; 
        }
        
        .notification.show { 
            transform: translateX(0); 
        }
        
        .forum-category {
            border-left: 4px solid #3b82f6;
            transition: all 0.3s ease;
        }
        
        .forum-category:hover {
            background: #f1f5f9;
            transform: translateX(-2px);
            border-left-color: #2563eb;
        }
        
        .topic-row:hover {
            background: #f8fafc;
        }
        
        .post-content {
            line-height: 1.8;
            word-wrap: break-word;
        }
        
        .user-badge {
            font-size: 0.75rem;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .admin-badge {
            background: #dc2626;
            color: white;
        }
        
        .moderator-badge {
            background: #3b82f6;
            color: white;
        }
        
        .vip-badge {
            background: #f59e0b;
            color: white;
        }
        
        .loading-spinner {
            border: 3px solid #f3f4f6;
            border-top: 3px solid #3b82f6;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal.show {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 16px;
            max-width: 90%;
            max-height: 90%;
            overflow-y: auto;
            animation: modalSlideIn 0.3s ease-out;
        }
        
        @keyframes modalSlideIn {
            from {
                opacity: 0;
                transform: translateY(-20px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        .breadcrumb a:hover {
            color: #3b82f6;
        }
        
        .pagination .page-btn {
            transition: all 0.2s ease;
            background: white;
            border: 1px solid #e2e8f0;
            color: #64748b;
        }
        
        .pagination .page-btn:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .pagination .page-btn.active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }
        
        .search-highlight {
            background: #dbeafe;
            color: #1d4ed8;
            padding: 2px 6px;
            border-radius: 4px;
        }
        
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            border: 1px solid #e2e8f0;
            transition: all 0.2s ease;
        }
        
        .stats-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        
        .input-modern {
            background: white;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 0.75rem;
            transition: all 0.2s ease;
            width: 100%;
        }
        
        .input-modern:focus {
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        .navbar {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .category-icon {
            width: 48px;
            height: 48px;
            background: #f8fafc;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #3b82f6;
            font-size: 1.25rem;
        }
        
        .post-avatar {
            width: 40px;
            height: 40px;
            background: #3b82f6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }
        
        .online-indicator {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            border: 2px solid white;
            position: absolute;
            bottom: 0;
            right: 0;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-50">
    
    <!-- Header -->
    <header class="modern-nav sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 sm:py-4">
                <div class="flex items-center space-x-3 sm:space-x-6 space-x-reverse flex-shrink-0">
                    <div class="relative group flex-shrink-0">
                        <img src="logo.png" alt="سورس کده" class="h-16 sm:h-20 lg:h-24 w-auto object-contain drop-shadow-xl transition-all duration-700 group-hover:scale-125 group-hover:drop-shadow-2xl group-hover:brightness-110 filter" decoding="async" fetchpriority="high">
                        <div class="absolute -top-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-gradient-to-r from-emerald-400 to-green-500 rounded-full shadow-xl">
                            <div class="w-full h-full bg-emerald-300 rounded-full animate-pulse-slow"></div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center min-w-0 flex-1">
                        <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                            <h1 class="text-xl sm:text-4xl font-black bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent tracking-tight whitespace-normal break-words flex-shrink-0">
                                سورس کده
                            </h1>
                            <div class="hidden sm:flex items-center space-x-2 space-x-reverse bg-green-50 px-3 py-1 rounded-full flex-shrink-0">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
                                <span class="text-xs font-bold text-green-700">آنلاین</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Controls -->
                <div class="flex items-center space-x-2 space-x-reverse md:hidden mobile-controls">
                    <button onclick="toggleTheme()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300" title="تغییر حالت">
                        <i id="mobileThemeIcon" class="fas fa-moon w-5 h-5 text-gray-600"></i>
                    </button>
                    <button onclick="toggleMobileMenu()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300">
                        <svg id="mobileMenuIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                
                
                <!-- Enhanced Desktop Navigation & Controls -->
                <div class="hidden md:flex items-center justify-end flex-1 gap-3 lg:gap-6">
                    <!-- Navigation Menu -->
                    <nav class="hidden lg:flex items-center bg-white/90 backdrop-blur-lg rounded-2xl border border-white/20 shadow-lg px-2 py-1">
                        <a href="sources.php" class="nav-link group relative text-gray-700 hover:text-blue-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-blue-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-gem text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">کاوش سورس‌ها</span>
                            </div>
                        </a>
                        <a href="shop.php" class="nav-link group relative text-gray-700 hover:text-orange-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-orange-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-red-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-store text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">فروشگاه</span>
                            </div>
                        </a>
                        <a href="forum.php" class="nav-link group relative text-gray-700 hover:text-green-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-green-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-comments text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">انجمن</span>
                            </div>
                        </a>
                        <a href="ai.php" class="nav-link group relative text-gray-700 hover:text-purple-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-purple-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-brain text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">هوش مصنوعی</span>
                            </div>
                        </a>
                        <div class="w-px h-6 bg-gray-200 mx-2"></div>
                        <a href="javascript:void(0)" onclick="showSection('leaderboard')" class="nav-link group relative text-gray-600 hover:text-amber-600 font-medium transition-all duration-300 text-sm px-3 py-2 rounded-lg hover:bg-amber-50/60 text-gray-700">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-trophy text-amber-500 text-sm"></i>
                                <span>رتبه‌بندی</span>
                            </div>
                        </a>
                    </nav>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button onclick="toggleTheme()" class="p-2.5 rounded-xl bg-white/80 backdrop-blur-sm border border-white/20 hover:bg-white hover:border-gray-200 transition-all duration-300 shadow-sm hover:shadow-md">
                            <svg id="themeIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        </button>
                    <div id="userSection" class="hidden relative">
                        <div class="flex items-center space-x-2 sm:space-x-4 space-x-reverse">
                            <div class="relative hidden sm:block">
                                <button onclick="toggleNotifications()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300 relative">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6.5A2.5 2.5 0 014 16.5v-9A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5V11"></path></svg>
                                    <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">3</span>
                                </button>
                            </div>
                            <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse bg-white/10 rounded-xl sm:rounded-2xl px-2 sm:px-4 py-1 sm:py-2 backdrop-blur-sm">
                                <div class="text-right hidden sm:block">
                                    <p id="userName" class="font-semibold text-gray-900 text-sm"></p>
                                    <p id="userLevel" class="text-xs text-gray-600">سطح</p>
                                </div>
                                <div class="relative">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full overflow-hidden" id="userAvatarContainer">
                                        <img id="userAvatar" src="" alt="User Avatar" class="w-full h-full object-cover bg-white" style="display: none;" decoding="async" fetchpriority="high">
                                        <div id="userAvatarFallback" class="w-full h-full gradient-primary flex items-center justify-center text-white font-bold text-xs sm:text-sm">A</div>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 space-x-reverse hidden sm:flex">
            <div class="text-center">
                                    <div class="gradient-success text-white px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-bold" id="userPoints">0</div>
                                    <p class="text-xs text-gray-600 mt-1">امتیاز</p>
                                </div>
                            </div>
                            <button onclick="showUserMenu()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300"><svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                        </div>
                        <div id="userMenu" class="hidden absolute top-16 right-0 w-64 bg-white rounded-2xl shadow-2xl py-2 border border-gray-100">
                            <a href="account.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                حساب من
                            </a>
                            <div id="adminLinks" class="hidden">
                                <div class="border-t border-gray-100 my-1"></div>
                                <!-- پنل مدیریت پیشرفته حذف شد -->
                                <a href="admin/" class="flex items-center px-4 py-3 text-sm text-orange-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    تایید پروژه‌ها
                                </a>
                            </div>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button onclick="handleLogout()" class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                خروج
                            </button>
                        </div>
                    </div>
                    <div id="authSection">
                        <div class="flex items-center space-x-3 sm:space-x-4 lg:space-x-6 space-x-reverse">
                            <a href="auth.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors text-sm sm:text-base px-2 py-2 rounded-lg hover:bg-gray-50">ورود</a>
                            <a href="auth.php#register" class="gradient-primary text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl font-medium hover:shadow-lg transition-all duration-300 transform hover:scale-105 text-sm whitespace-nowrap">ثبت‌نام</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white/95 backdrop-blur-lg border-t border-gray-200 shadow-lg">
            <div class="px-4 py-4 space-y-4">
                <!-- Mobile Search -->
                <div class="relative">
                    <input type="text" id="mobileGlobalSearch" placeholder="جستجوی پیشرفته..." class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 bg-white">
                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                    
                <!-- Mobile Navigation Links -->
                <div class="space-y-2">
                    
                    <a href="sources.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-gem ml-3 w-5"></i>کاوش سورس‌ها
                    </a>
                    <a href="javascript:void(0)" onclick="showSection('explore'); toggleMobileMenu();" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-search ml-3 w-5"></i>جستجوی پیشرفته
                    </a>
                    <a href="shop.php" class="block px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-shopping-bag ml-3 w-5"></i>فروشگاه
                    </a>
                    <a href="forum.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-comments ml-3 w-5"></i>انجمن
                    </a>
                    <a href="ai.php" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-brain ml-3 w-5"></i>هوش مصنوعی
                    </a>
                    <a href="javascript:void(0)" onclick="showSection('leaderboard'); toggleMobileMenu();" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-trophy ml-3 w-5"></i>رتبه‌بندی
                    </a>
                    </div>
                    
                <!-- Mobile Auth Section for non-logged in users -->
                <div id="mobileAuthSection" class="pt-3 border-t border-gray-200" role="region" aria-label="mobile-auth-section">
                    <div class="space-y-3">
                        <a href="auth.php" class="block px-4 py-3 text-center bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-medium">ورود</a>
                        <a href="auth.php#register" class="block px-4 py-3 text-center gradient-primary text-white rounded-xl font-medium whitespace-nowrap mobile-auth-button">ثبت‌نام</a>
                    </div>
                </div>
                
                <!-- Mobile User Section for logged in users -->
                <div id="mobileUserSection" class="hidden pt-3 border-t border-gray-200" role="region" aria-label="mobile-user-section">
                    <div class="space-y-2">
                        <a href="account.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-user ml-3 w-5"></i>حساب من
                        </a>
                        <!-- لینک‌های ادمین موبایل حذف شدند -->
                        <button onclick="handleLogout()" class="w-full text-right px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt ml-3 w-5"></i>خروج
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div><div></div></header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Main Forum Content -->
            <div class="lg:col-span-3">
                <!-- Welcome Section -->
                <div class="modern-card p-8 mb-8 slide-in bg-gradient-to-r from-blue-500 to-purple-600 text-white">
                    <h1 class="text-3xl font-bold mb-4">
                        <i class="fas fa-comments ml-3 text-yellow-300"></i>
                        خوش آمدید به انجمن سورس کده
                    </h1>
                    <p class="text-lg opacity-90">مکانی برای اشتراک‌گذاری دانش، طرح سوال و گفتگو با جامعه برنامه‌نویسان</p>
                </div>

                <!-- Forum Stats -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8 slide-in">
                    <div class="stats-card">
                        <div class="text-2xl font-bold text-blue-600 mb-1" id="totalTopics">0</div>
                        <div class="text-sm text-gray-600">موضوعات</div>
                    </div>
                    <div class="stats-card">
                        <div class="text-2xl font-bold text-green-600 mb-1" id="totalPosts">0</div>
                        <div class="text-sm text-gray-600">پست‌ها</div>
                    </div>
                    <div class="stats-card">
                        <div class="text-2xl font-bold text-purple-600 mb-1" id="totalUsers">0</div>
                        <div class="text-sm text-gray-600">کاربران</div>
                    </div>
                    <div class="stats-card">
                        <div class="text-2xl font-bold text-orange-600 mb-1" id="onlineUsers">0</div>
                        <div class="text-sm text-gray-600">آنلاین</div>
                    </div>
                </div>

                <!-- Search Bar -->
                <div class="modern-card p-6 mb-8 slide-in">
                    <div class="flex flex-col md:flex-row gap-4">
                        <div class="flex-1">
                            <input type="text" id="searchInput" placeholder="جستجو در انجمن..." 
                                   class="input-modern">
                        </div>
                        <button onclick="searchForum()" class="btn-primary">
                            <i class="fas fa-search ml-2"></i>جستجو
                        </button>
                        <?php if ($current_user): ?>
                            <button onclick="showNewTopicModal()" class="btn-success">
                                <i class="fas fa-plus ml-2"></i>موضوع جدید
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Breadcrumb -->
                <div id="breadcrumb" class="mb-6 text-sm text-gray-600 slide-in">
                    <a href="#" onclick="showCategories()" class="breadcrumb hover:text-blue-600">انجمن</a>
                </div>

                <!-- Categories View -->
                <div id="categoriesView" class="space-y-4 slide-in">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6">دسته‌بندی‌های انجمن</h2>
                    <div id="categoriesList"></div>
                </div>

                <!-- Topics View -->
                <div id="topicsView" class="hidden">
                    <div class="modern-card">
                        <div class="p-6 border-b border-gray-200">
                            <h2 id="categoryTitle" class="text-2xl font-bold text-gray-900"></h2>
                            <p id="categoryDescription" class="text-gray-600 mt-2"></p>
                        </div>
                        <div id="topicsList"></div>
                    </div>
                </div>

                <!-- Posts View -->
                <div id="postsView" class="hidden">
                    <div class="modern-card">
                        <div class="p-6 border-b border-gray-200">
                            <h2 id="topicTitle" class="text-2xl font-bold text-gray-900"></h2>
                            <div id="topicMeta" class="text-sm text-gray-600 mt-2"></div>
                        </div>
                        <div id="postsList"></div>
                        
                        <?php if ($current_user): ?>
                            <div class="p-6 border-t border-gray-200">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">پاسخ جدید</h3>
                                <textarea id="newPostContent" placeholder="پاسخ خود را بنویسید..." 
                                          class="input-modern h-32 resize-none"></textarea>
                                <div class="flex justify-end mt-4">
                                    <button onclick="submitPost()" class="btn-primary">
                                        <i class="fas fa-paper-plane ml-2"></i>ارسال پاسخ
                                    </button>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1">
                <!-- Online Users -->
                <div class="modern-card p-6 mb-6 slide-in">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-users text-green-500 ml-2"></i>
                        کاربران آنلاین (<span id="onlineCount">0</span>)
                    </h3>
                    <div id="onlineUsersList" class="space-y-3">
                        <!-- Online users will be loaded here -->
                    </div>
                </div>

                <!-- Live Chat -->
                <div class="modern-card p-6 mb-6 slide-in">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold text-gray-900">
                            <i class="fas fa-comments text-blue-500 ml-2"></i>
                            چت زنده
                        </h3>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <div id="chatStatus" class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                            <?php if ($current_user && ($current_user['role'] ?? '') === 'admin'): ?>
                            <button id="toggleChatBtn" onclick="toggleChatStatus()" 
                                    class="text-xs bg-gray-100 hover:bg-gray-200 px-2 py-1 rounded" 
                                    title="مدیریت چت">
                                <i class="fas fa-cog"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div id="chatMessages" class="h-64 overflow-y-auto bg-gray-50 rounded-lg p-3 mb-4">
                        <!-- Chat messages will be loaded here -->
                    </div>
                    
                    <div id="chatInputSection">
                        <?php if ($current_user): ?>
                            <div class="flex gap-2">
                                <input type="text" id="chatInput" placeholder="پیام خود را بنویسید..." 
                                       class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <button onclick="sendChatMessage()" class="btn-primary px-3 py-2">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        <?php else: ?>
                            <p class="text-sm text-gray-500 text-center">برای چت کردن وارد شوید</p>
                        <?php endif; ?>
                    </div>
                    
                    <div id="chatClosedMessage" class="hidden text-center py-4 text-red-600 text-sm">
                        چت زنده در حال حاضر بسته است
                    </div>
                </div>

                <!-- Recent Activity -->
                <div class="modern-card p-6 slide-in">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">
                        <i class="fas fa-clock text-purple-500 ml-2"></i>
                        فعالیت‌های اخیر
                    </h3>
                    <div id="recentActivity" class="space-y-3">
                        <!-- Recent activity will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-16">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-gray-600">در حال بارگذاری...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16">
            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-inbox text-gray-400 text-3xl"></i>
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">محتوایی یافت نشد</h3>
            <p class="text-gray-600">هنوز هیچ موضوعی در این دسته وجود ندارد</p>
        </div>

        <!-- Pagination -->
        <div id="paginationContainer" class="hidden flex justify-center mt-8">
            <div class="pagination flex space-x-2 space-x-reverse" id="pagination"></div>
        </div>
    </main>

    <!-- New Topic Modal -->
    <div id="newTopicModal" class="modal">
        <div class="modal-content p-8 w-full max-w-2xl">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-gray-900">موضوع جدید</h3>
                <button onclick="closeNewTopicModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">دسته‌بندی</label>
                    <select id="topicCategory" class="input-modern">
                        <option value="">انتخاب دسته‌بندی</option>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عنوان موضوع</label>
                    <input type="text" id="topicTitle" placeholder="عنوان موضوع را وارد کنید" 
                           class="input-modern">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">محتوای موضوع</label>
                    <textarea id="topicContent" placeholder="محتوای موضوع را بنویسید..." 
                              class="input-modern h-40 resize-none"></textarea>
                </div>
            </div>
            
            <div class="flex justify-end space-x-4 space-x-reverse mt-6">
                <button onclick="closeNewTopicModal()" class="btn-secondary">لغو</button>
                <button onclick="submitNewTopic()" class="btn-primary">
                    <i class="fas fa-paper-plane ml-2"></i>ایجاد موضوع
                </button>
            </div>
        </div>
    </div>

    <!-- Topic View Modal -->
    <div id="topicModal" class="modal">
        <div class="modal-content p-0 w-full max-w-5xl max-h-[90vh] overflow-hidden">
            <div class="sticky top-0 bg-white border-b p-6 flex justify-between items-center">
                <div class="flex-1">
                    <h3 id="topicModalTitle" class="text-xl font-bold text-gray-900"></h3>
                    <div id="topicModalMeta" class="text-sm text-gray-600 mt-1"></div>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <?php if (current_user() && (current_user()['role'] ?? '') === 'admin'): ?>
                    <div class="flex space-x-2 space-x-reverse">
                        <button id="toggleTopicStatusBtn" onclick="toggleTopicStatus()" 
                                class="btn-secondary text-sm">
                            <i class="fas fa-lock ml-1"></i>
                            <span id="topicStatusText">بستن</span>
                        </button>
                        <button onclick="deleteCurrentTopic()" 
                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded-lg text-sm">
                            <i class="fas fa-trash ml-1"></i>حذف
                        </button>
                    </div>
                    <?php endif; ?>
                    <button onclick="closeTopicModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
            
            <div class="overflow-y-auto max-h-[70vh]">
                <div id="topicContent" class="p-6 border-b bg-gray-50">
                    <!-- Topic content will be loaded here -->
                </div>
                
                <div id="topicPosts" class="p-6 space-y-4">
                    <!-- Posts will be loaded here -->
                </div>
            </div>
            
            <div id="replySection" class="sticky bottom-0 bg-white border-t p-6">
                <?php if (current_user()): ?>
                    <div class="space-y-4">
                        <label class="block text-sm font-medium text-gray-700">پاسخ شما:</label>
                        <textarea id="replyContent" placeholder="پاسخ خود را بنویسید..." 
                                  class="input-modern h-24 resize-none"></textarea>
                        <div class="flex justify-end">
                            <button onclick="submitReply()" class="btn-primary">
                                <i class="fas fa-reply ml-2"></i>ارسال پاسخ
                            </button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="text-center py-4">
                        <p class="text-gray-600">برای پاسخ دادن، ابتدا وارد حساب کاربری خود شوید</p>
                        <a href="auth.php" class="btn-primary mt-2 inline-block">ورود</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Notification -->
    <div id="notification" class="notification">
        <div class="bg-white rounded-xl shadow-2xl p-4 border-l-4 border-blue-500">
            <div class="flex items-center">
                <i id="notificationIcon" class="fas fa-info-circle text-blue-500 ml-3"></i>
                <div>
                    <p class="font-medium text-gray-900" id="notificationTitle">اطلاع‌رسانی</p>
                    <p class="text-sm text-gray-600" id="notificationMessage">پیام شما</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentView = 'categories';
        let currentCategoryId = null;
        let currentTopicId = null;
        let currentPage = 1;
        let totalPages = 1;

        // Load forum data when page loads
        document.addEventListener('DOMContentLoaded', function() {
            loadForumStats();
            loadCategories();
            loadOnlineUsers();
            loadChatMessages();
            loadRecentActivity();
            
            // Auto refresh every 30 seconds
            setInterval(() => {
                loadOnlineUsers();
                loadChatMessages();
                loadRecentActivity();
            }, 30000);
        });

        async function loadForumStats() {
            try {
                const response = await fetch('api/forum.php?action=stats');
                const data = await response.json();
                
                if (data.success) {
                    // Animate numbers counting up
                    animateNumber('totalTopics', data.stats.topics || 0);
                    animateNumber('totalPosts', data.stats.posts || 0);
                    animateNumber('totalUsers', data.stats.users || 0);
                    animateNumber('onlineUsers', data.stats.online || 0);
                }
            } catch (error) {
                console.error('Error loading forum stats:', error);
                // Fallback to 0 if API fails
                document.getElementById('totalTopics').textContent = '0';
                document.getElementById('totalPosts').textContent = '0';
                document.getElementById('totalUsers').textContent = '0';
                document.getElementById('onlineUsers').textContent = '0';
            }
        }

        function animateNumber(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const duration = 1000; // 1 second
            const startTime = performance.now();
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function for smooth animation
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.floor(easeOut * targetValue);
                
                element.textContent = currentValue;
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                } else {
                    element.textContent = targetValue;
                }
            }
            
            requestAnimationFrame(updateNumber);
        }

        async function loadCategories() {
            showLoading();
            try {
                console.log('Loading categories...');
                const response = await fetch('api/forum.php?action=categories');
                const data = await response.json();
                
                console.log('Categories API response:', data);
                
                if (data.success && data.categories) {
                    displayCategories(data.categories);
                    populateCategorySelect(data.categories);
                    showCategoriesView();
                    showNotification('موفقیت', 'دسته‌بندی‌ها بارگذاری شد', 'success');
                } else {
                    console.error('API Error:', data);
                    showNotification('خطا', 'خطا در بارگذاری دسته‌بندی‌ها', 'error');
                    showEmptyState();
                }
            } catch (error) {
                console.error('Network Error:', error);
                showNotification('خطا', 'خطا در اتصال به سرور', 'error');
                showEmptyState();
            } finally {
                hideLoading();
            }
        }
        
        function showCategoriesView() {
            document.getElementById('categoriesView').classList.remove('hidden');
            document.getElementById('topicsView').classList.add('hidden');
        }

        function displayCategories(categories) {
            const container = document.getElementById('categoriesList');
            container.innerHTML = categories.map(category => `
                <div class="modern-card forum-category p-6 cursor-pointer" onclick="showTopics(${category.id})">
                    <div class="flex items-center">
                        <div class="category-icon ml-4">
                            <i class="fas ${getCategoryIcon(category.name)}"></i>
                        </div>
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">${escapeHtml(category.name)}</h3>
                            <p class="text-gray-600 text-sm">${escapeHtml(category.description)}</p>
                        </div>
                        <div class="text-center min-w-0">
                            <div class="text-lg font-bold text-blue-600">${category.topic_count || 0}</div>
                            <div class="text-xs text-gray-500">موضوع</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function populateCategorySelect(categories) {
            const select = document.getElementById('topicCategory');
            select.innerHTML = '<option value="">انتخاب دسته‌بندی</option>' + 
                categories.map(cat => `<option value="${cat.id}">${escapeHtml(cat.name)}</option>`).join('');
        }

        async function showTopics(categoryId) {
            currentCategoryId = categoryId;
            currentView = 'topics';
            
            showLoading();
            
            try {
                const response = await fetch(`api/forum.php?action=topics&category_id=${categoryId}&page=${currentPage}`);
                const data = await response.json();
                
                if (data.success) {
                    displayTopics(data.topics, data.category);
                    updateBreadcrumb([
                        { text: 'انجمن', action: 'showCategories()' },
                        { text: data.category.name, action: null }
                    ]);
                    
                    totalPages = data.total_pages || 1;
                    updatePagination();
                } else {
                    showEmptyState();
                }
            } catch (error) {
                console.error('Error loading topics:', error);
                showEmptyState();
            } finally {
                hideLoading();
            }
        }

        function displayTopics(topics, category) {
            document.getElementById('categoriesView').classList.add('hidden');
            document.getElementById('topicsView').classList.remove('hidden');
            document.getElementById('postsView').classList.add('hidden');
            
            document.getElementById('categoryTitle').textContent = category.name;
            document.getElementById('categoryDescription').textContent = category.description;
            
            const container = document.getElementById('topicsList');
            
            if (topics.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-comment-slash text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">هنوز موضوعی وجود ندارد</h3>
                        <p class="text-gray-600">اولین نفری باشید که موضوع جدیدی ایجاد می‌کند</p>
                    </div>
                `;
            } else {
                container.innerHTML = topics.map(topic => `
                    <div class="topic-row p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" 
                         onclick="openTopic(${topic.id})">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 space-x-reverse mb-1">
                                    <h4 class="font-semibold text-gray-900">${escapeHtml(topic.title)}</h4>
                                    ${(topic.status === 'closed') ? '<i class="fas fa-lock text-red-500 text-sm" title="موضوع بسته"></i>' : ''}
                                    ${(topic.pinned) ? '<i class="fas fa-thumbtack text-blue-500 text-sm" title="موضوع پین شده"></i>' : ''}
                                </div>
                                <div class="flex items-center text-sm text-gray-600 space-x-4 space-x-reverse">
                                    <span>
                                        <i class="fas fa-user ml-1"></i>
                                        ${escapeHtml(topic.author_name)}
                                    </span>
                                    <span>
                                        <i class="fas fa-clock ml-1"></i>
                                        ${formatDate(topic.created_at)}
                                    </span>
                                </div>
                            </div>
                            <div class="text-center min-w-0 ml-4">
                                <div class="text-sm font-semibold text-blue-600">${topic.post_count || 0}</div>
                                <div class="text-xs text-gray-500">پاسخ</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        }

        async function showPosts(topicId) {
            currentTopicId = topicId;
            currentView = 'posts';
            
            showLoading();
            
            try {
                const response = await fetch(`api/forum.php?action=posts&topic_id=${topicId}&page=${currentPage}`);
                const data = await response.json();
                
                if (data.success) {
                    displayPosts(data.posts, data.topic);
                    updateBreadcrumb([
                        { text: 'انجمن', action: 'showCategories()' },
                        { text: data.topic.category_name, action: `showTopics(${data.topic.category_id})` },
                        { text: data.topic.title, action: null }
                    ]);
                    
                    totalPages = data.total_pages || 1;
                    updatePagination();
                    
                    // Increment topic views
                    fetch(`api/forum.php?action=view_topic&topic_id=${topicId}`, { method: 'POST' });
                } else {
                    showEmptyState();
                }
            } catch (error) {
                console.error('Error loading posts:', error);
                showEmptyState();
            } finally {
                hideLoading();
            }
        }

        function displayPosts(posts, topic) {
            document.getElementById('categoriesView').classList.add('hidden');
            document.getElementById('topicsView').classList.add('hidden');
            document.getElementById('postsView').classList.remove('hidden');
            
            document.getElementById('topicTitle').textContent = topic.title;
            document.getElementById('topicMeta').innerHTML = `
                <span><i class="fas fa-user ml-1"></i>ایجاد شده توسط ${escapeHtml(topic.author_name)}</span>
                <span class="mx-2">•</span>
                <span><i class="fas fa-clock ml-1"></i>${formatDate(topic.created_at)}</span>
                <span class="mx-2">•</span>
                <span><i class="fas fa-eye ml-1"></i>${topic.view_count || 0} بازدید</span>
            `;
            
            const container = document.getElementById('postsList');
            container.innerHTML = posts.map((post, index) => `
                <div class="p-6 border-b border-gray-100">
                    <div class="flex">
                        <div class="flex-shrink-0 ml-4">
                            <div class="post-avatar relative">
                                ${escapeHtml(post.author_name.charAt(0).toUpperCase())}
                                ${post.is_online ? '<div class="online-indicator"></div>' : ''}
                            </div>
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3 space-x-reverse">
                                    <span class="font-semibold text-gray-900">${escapeHtml(post.author_name)}</span>
                                    ${post.author_verified ? '<span class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-2 py-1 rounded-full text-xs font-semibold shadow-lg transform transition-all duration-200 hover:scale-105" title="کاربر تایید‌شده"><svg class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6L9 17l-5-5"></path></svg><span>تایید‌شده</span></span>' : ''}
                                    ${post.author_role === 'admin' ? '<span class="user-badge admin-badge">ادمین</span>' : ''}
                                    ${post.author_role === 'moderator' ? '<span class="user-badge moderator-badge">مدیر</span>' : ''}
                                </div>
                                <span class="text-sm text-gray-500">${formatDate(post.created_at)}</span>
                            </div>
                            <div class="post-content text-gray-700">${escapeHtml(post.content)}</div>
                        </div>
                    </div>
                </div>
            `).join('');
        }

        function showCategories() {
            currentView = 'categories';
            currentCategoryId = null;
            currentTopicId = null;
            currentPage = 1;
            
            document.getElementById('categoriesView').classList.remove('hidden');
            document.getElementById('topicsView').classList.add('hidden');
            document.getElementById('postsView').classList.add('hidden');
            document.getElementById('paginationContainer').classList.add('hidden');
            
            updateBreadcrumb([{ text: 'انجمن', action: null }]);
        }

        function updateBreadcrumb(items) {
            const breadcrumb = document.getElementById('breadcrumb');
            breadcrumb.innerHTML = items.map((item, index) => {
                if (index === items.length - 1) {
                    return `<span class="text-gray-900 font-medium">${item.text}</span>`;
                } else {
                    return `<a href="#" onclick="${item.action}" class="breadcrumb hover:text-blue-600">${item.text}</a> / `;
                }
            }).join('');
        }

        function updatePagination() {
            if (totalPages <= 1) {
                document.getElementById('paginationContainer').classList.add('hidden');
                return;
            }
            
            document.getElementById('paginationContainer').classList.remove('hidden');
            const pagination = document.getElementById('pagination');
            let html = '';
            
            // Previous button
            if (currentPage > 1) {
                html += `<button class="page-btn px-3 py-2 rounded-lg" onclick="changePage(${currentPage - 1})">
                    <i class="fas fa-chevron-right"></i>
                </button>`;
            }
            
            // Page numbers
            for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
                const activeClass = i === currentPage ? 'active' : '';
                html += `<button class="page-btn px-3 py-2 rounded-lg ${activeClass}" onclick="changePage(${i})">${i}</button>`;
            }
            
            // Next button
            if (currentPage < totalPages) {
                html += `<button class="page-btn px-3 py-2 rounded-lg" onclick="changePage(${currentPage + 1})">
                    <i class="fas fa-chevron-left"></i>
                </button>`;
            }
            
            pagination.innerHTML = html;
        }

        function changePage(page) {
            currentPage = page;
            
            if (currentView === 'topics') {
                showTopics(currentCategoryId);
            } else if (currentView === 'posts') {
                showPosts(currentTopicId);
            }
        }

        // --- Runtime auth sync for forum header ---
        let FORUM_IS_LOGGED_IN = <?= $current_user ? 'true' : 'false' ?>;
        let FORUM_USER = <?= $current_user ? json_encode(['id'=>$current_user['id'],'name'=>$current_user['name'] ?? '','role'=>$current_user['role'] ?? 'user','points'=>$current_user['points'] ?? 0], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : 'null' ?>;

        async function forumResolveAuth() {
            try {
                const res = await fetch('api/auth.php?action=me', { credentials: 'same-origin' });
                const data = await res.json();
                if (data && data.ok && data.user) {
                    FORUM_IS_LOGGED_IN = true;
                    FORUM_USER = data.user;
                    forumShowUser();
                } else {
                    FORUM_IS_LOGGED_IN = false;
                    FORUM_USER = null;
                    forumShowLoggedOut();
                }
            } catch (_) {
                FORUM_IS_LOGGED_IN = false;
                FORUM_USER = null;
                forumShowLoggedOut();
            }
        }

        function forumShowLoggedOut() {
            const auth = document.getElementById('authSection');
            const user = document.getElementById('userSection');
            if (auth) auth.classList.remove('hidden');
            if (user) user.classList.add('hidden');
        }

        function forumShowUser() {
            const auth = document.getElementById('authSection');
            const user = document.getElementById('userSection');
            if (auth) auth.classList.add('hidden');
            if (user) user.classList.remove('hidden');
            try {
                const points = document.getElementById('userPoints');
                if (points && FORUM_USER && typeof FORUM_USER.points !== 'undefined') {
                    points.textContent = FORUM_USER.points;
                }
            } catch (_) {}
        }

        document.addEventListener('DOMContentLoaded', function(){ forumResolveAuth(); });

        function showNewTopicModal() {
            if (!FORUM_IS_LOGGED_IN) {
                showNotification('ورود لازم', 'برای ایجاد موضوع جدید ابتدا وارد حساب کاربری خود شوید', 'error');
                setTimeout(() => { window.location.href = 'auth.php'; }, 1200);
                return;
            }
            document.getElementById('newTopicModal').classList.add('show');
        }

        function closeNewTopicModal() {
            document.getElementById('newTopicModal').classList.remove('show');
            // Clear form
            document.getElementById('topicCategory').value = '';
            document.getElementById('topicTitle').value = '';
            document.getElementById('topicContent').value = '';
        }

        async function submitNewTopic() {
            // Check if user is logged in
            <?php if (!$current_user): ?>
            showNotification('ورود لازم', 'برای ایجاد موضوع جدید ابتدا وارد حساب کاربری خود شوید', 'error');
            setTimeout(() => {
                window.location.href = 'auth.php';
            }, 2000);
            return;
            <?php endif; ?>
            
            const categoryId = document.getElementById('topicCategory').value;
            const title = document.getElementById('topicTitle').value.trim();
            const content = document.getElementById('topicContent').value.trim();
            
            if (!categoryId || !title || !content) {
                showNotification('خطا', 'لطفاً همه فیلدها را پر کنید', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/forum.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'create_topic',
                        category_id: parseInt(categoryId),
                        title: title,
                        content: content
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeNewTopicModal();
                    showNotification('موفق', 'موضوع جدید ایجاد شد', 'success');
                    
                    // Refresh current view
                    if (currentView === 'topics' && currentCategoryId == categoryId) {
                        showTopics(currentCategoryId);
                    } else {
                        showTopics(categoryId);
                    }
                } else {
                    showNotification('خطا', data.error || 'خطا در ایجاد موضوع', 'error');
                }
            } catch (error) {
                console.error('Error creating topic:', error);
                showNotification('خطا', 'خطا در ایجاد موضوع', 'error');
            }
        }

        async function submitPost() {
            // Check if user is logged in
            <?php if (!$current_user): ?>
            showNotification('ورود لازم', 'برای پاسخ دادن ابتدا وارد حساب کاربری خود شوید', 'error');
            setTimeout(() => {
                window.location.href = 'auth.php';
            }, 2000);
            return;
            <?php endif; ?>
            
            const content = document.getElementById('newPostContent').value.trim();
            
            if (!content) {
                showNotification('خطا', 'لطفاً محتوای پاسخ را وارد کنید', 'error');
                return;
            }
            
            try {
                const response = await fetch('api/forum.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'create_post',
                        topic_id: currentTopicId,
                        content: content
                    })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('newPostContent').value = '';
                    showNotification('موفق', 'پاسخ شما ارسال شد', 'success');
                    showPosts(currentTopicId);
                } else {
                    showNotification('خطا', data.error || 'خطا در ارسال پاسخ', 'error');
                }
            } catch (error) {
                console.error('Error creating post:', error);
                showNotification('خطا', 'خطا در ارسال پاسخ', 'error');
            }
        }

        async function searchForum() {
            const query = document.getElementById('searchInput').value.trim();
            
            if (!query) {
                showNotification('خطا', 'لطفاً کلمه جستجو را وارد کنید', 'error');
                return;
            }
            
            showLoading();
            
            try {
                const response = await fetch(`api/forum.php?action=search&q=${encodeURIComponent(query)}`);
                const data = await response.json();
                
                if (data.success) {
                    displaySearchResults(data.results, query);
                } else {
                    showEmptyState();
                }
            } catch (error) {
                console.error('Error searching forum:', error);
                showEmptyState();
            } finally {
                hideLoading();
            }
        }

        function displaySearchResults(results, query) {
            currentView = 'search';
            
            document.getElementById('categoriesView').classList.add('hidden');
            document.getElementById('topicsView').classList.remove('hidden');
            document.getElementById('postsView').classList.add('hidden');
            
            document.getElementById('categoryTitle').textContent = `نتایج جستجو: "${query}"`;
            document.getElementById('categoryDescription').textContent = `${results.length} نتیجه یافت شد`;
            
            const container = document.getElementById('topicsList');
            
            if (results.length === 0) {
                container.innerHTML = `
                    <div class="p-12 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">نتیجه‌ای یافت نشد</h3>
                        <p class="text-gray-600">کلمات جستجو را تغییر دهید و دوباره تلاش کنید</p>
                    </div>
                `;
            } else {
                container.innerHTML = results.map(result => `
                    <div class="topic-row p-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors" 
                         onclick="showPosts(${result.id})">
                        <div class="flex items-center justify-between">
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-1">${highlightSearch(result.title, query)}</h4>
                                <div class="flex items-center text-sm text-gray-600 space-x-4 space-x-reverse">
                                    <span>
                                        <i class="fas fa-user ml-1"></i>
                                        ${escapeHtml(result.author_name)}
                                    </span>
                                    <span>
                                        <i class="fas fa-clock ml-1"></i>
                                        ${formatDate(result.created_at)}
                                    </span>
                                    <span>
                                        <i class="fas fa-folder ml-1"></i>
                                        ${escapeHtml(result.category_name)}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
            
            updateBreadcrumb([
                { text: 'انجمن', action: 'showCategories()' },
                { text: `جستجو: "${query}"`, action: null }
            ]);
        }

        function showLoading() {
            document.getElementById('loadingState').classList.remove('hidden');
            document.getElementById('emptyState').classList.add('hidden');
        }

        function hideLoading() {
            document.getElementById('loadingState').classList.add('hidden');
        }

        function showEmptyState() {
            document.getElementById('emptyState').classList.remove('hidden');
            document.getElementById('loadingState').classList.add('hidden');
        }

        function showNotification(title, message, type = 'info') {
            const notification = document.getElementById('notification');
            const icon = document.getElementById('notificationIcon');
            
            document.getElementById('notificationTitle').textContent = title;
            document.getElementById('notificationMessage').textContent = message;
            
            // Update icon based on type
            icon.className = `fas ml-3 ${
                type === 'success' ? 'fa-check-circle text-green-500' :
                type === 'error' ? 'fa-exclamation-circle text-red-500' :
                'fa-info-circle text-blue-500'
            }`;
            
            notification.classList.add('show');
            
            setTimeout(() => {
                notification.classList.remove('show');
            }, 4000);
        }

        function getCategoryIcon(categoryName) {
            const icons = {
                'برنامه‌نویسی': 'fa-code',
                'طراحی وب': 'fa-paint-brush',
                'موبایل': 'fa-mobile-alt',
                'هوش مصنوعی': 'fa-brain',
                'امنیت': 'fa-shield-alt',
                'دیتابیس': 'fa-database',
                'DevOps': 'fa-server',
                'گیم': 'fa-gamepad'
            };
            return icons[categoryName] || 'fa-folder';
        }

        function formatDate(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffTime = Math.abs(now - date);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (diffDays === 1) {
                return 'امروز';
            } else if (diffDays === 2) {
                return 'دیروز';
            } else if (diffDays <= 7) {
                return `${diffDays} روز پیش`;
            } else {
                return date.toLocaleDateString('fa-IR');
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fa-IR');
            } catch (error) {
                return '';
            }
        }

        function formatDateTime(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                return date.toLocaleDateString('fa-IR') + ' ' + date.toLocaleTimeString('fa-IR');
            } catch (error) {
                return '';
            }
        }

        function highlightSearch(text, query) {
            const regex = new RegExp(`(${query})`, 'gi');
            return escapeHtml(text).replace(regex, '<span class="search-highlight">$1</span>');
        }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchForum();
            }
        });

        // Close modal when clicking outside
        document.getElementById('newTopicModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeNewTopicModal();
            }
        });

        // Chat functionality
        async function loadOnlineUsers() {
            try {
                const response = await fetch('api/online-users.php');
                const data = await response.json();
                
                if (data.success && data.users) {
                    document.getElementById('onlineCount').textContent = data.users.length;
                    
                    const container = document.getElementById('onlineUsersList');
                    container.innerHTML = data.users.map(user => `
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-8 h-8 bg-gradient-to-r from-green-400 to-blue-500 rounded-full flex items-center justify-center text-white text-xs font-bold">
                                ${user.name.charAt(0)}
                            </div>
                            <div>
                                <div class="text-sm font-medium text-gray-900">${escapeHtml(user.name)}</div>
                                <div class="text-xs text-gray-500">${user.role === 'admin' ? 'مدیر' : user.role === 'moderator' ? 'ناظر' : 'کاربر'}</div>
                            </div>
                            <div class="w-2 h-2 bg-green-400 rounded-full"></div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading online users:', error);
            }
        }

        async function loadChatMessages() {
            try {
                const response = await fetch('api/chat.php');
                const data = await response.json();
                
                if (data.success && data.messages) {
                    const container = document.getElementById('chatMessages');
                    container.innerHTML = data.messages.map(msg => `
                        <div class="mb-3">
                            <div class="flex items-center space-x-2 space-x-reverse mb-1">
                                <span class="text-xs font-semibold text-gray-700">${escapeHtml(msg.user_name)}</span>
                                <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
                            </div>
                            <div class="text-sm text-gray-800 bg-white rounded-lg px-3 py-2">${escapeHtml(msg.message)}</div>
                        </div>
                    `).join('');
                    
                    // Scroll to bottom
                    container.scrollTop = container.scrollHeight;
                }
            } catch (error) {
                console.error('Error loading chat messages:', error);
            }
        }

        async function sendChatMessage() {
            const input = document.getElementById('chatInput');
            const message = input.value.trim();
            
            if (!message) return;
            
            try {
                const response = await fetch('api/chat.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                });
                
                const data = await response.json();
                
                if (data.success) {
                    input.value = '';
                    loadChatMessages();
                } else {
                    showNotification('خطا', 'خطا در ارسال پیام', 'error');
                }
            } catch (error) {
                console.error('Error sending chat message:', error);
                showNotification('خطا', 'خطا در ارسال پیام', 'error');
            }
        }

        async function loadRecentActivity() {
            try {
                const response = await fetch('api/forum.php?action=recent_activity');
                const data = await response.json();
                
                if (data.success && data.activities) {
                    const container = document.getElementById('recentActivity');
                    container.innerHTML = data.activities.map(activity => `
                        <div class="text-sm">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <i class="fas ${getActivityIcon(activity.type)} text-blue-500"></i>
                                <span class="text-gray-700">${activity.description}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-1">${formatTime(activity.created_at)}</div>
                        </div>
                    `).join('');
                }
            } catch (error) {
                console.error('Error loading recent activity:', error);
            }
        }

        function getActivityIcon(type) {
            const icons = {
                'topic': 'fa-plus-circle',
                'post': 'fa-comment',
                'user_join': 'fa-user-plus',
                'like': 'fa-heart'
            };
            return icons[type] || 'fa-circle';
        }

        function formatTime(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diffMinutes = Math.floor((now - date) / (1000 * 60));
            
            if (diffMinutes < 1) return 'همین الان';
            if (diffMinutes < 60) return `${diffMinutes} دقیقه پیش`;
            if (diffMinutes < 1440) return `${Math.floor(diffMinutes / 60)} ساعت پیش`;
            return date.toLocaleDateString('fa-IR');
        }

        // Topic and reply management variables (already declared above)
        // let currentTopicId = null; // Already declared
        let currentTopicData = null;

        // Open topic modal
        async function openTopic(topicId) {
            currentTopicId = topicId;
            
            try {
                // Fetch topic data (we'll need to add this API endpoint)
                const topicResponse = await fetch(`api/forum.php?action=get_topic&topic_id=${topicId}`);
                const topicData = await topicResponse.json();
                
                if (topicData.success) {
                    currentTopicData = topicData.topic;
                    
                    // Update modal content
                    document.getElementById('topicModalTitle').textContent = topicData.topic.title;
                    document.getElementById('topicModalMeta').innerHTML = `
                        توسط ${escapeHtml(topicData.topic.author_name)} • 
                        ${formatDateTime(topicData.topic.createdAt)} • 
                        ${topicData.topic.postCount || 0} پاسخ • 
                        ${topicData.topic.views || 0} بازدید
                    `;
                    
                    document.getElementById('topicContent').innerHTML = `
                        <div class="prose max-w-none">
                            ${escapeHtml(topicData.topic.content || 'محتوایی برای این موضوع وجود ندارد')}
                        </div>
                    `;
                    
                    // Update admin controls
                    updateTopicAdminControls(topicData.topic);
                    
                    // Load posts
                    loadTopicPosts(topicId);
                    
                    // Update reply section based on topic status
                    updateReplySection(topicData.topic);
                    
                    // Show modal
                    document.getElementById('topicModal').classList.add('show');
                }
            } catch (error) {
                console.error('Error loading topic:', error);
                showNotification('خطا', 'خطا در بارگذاری موضوع', 'error');
            }
        }

        function updateTopicAdminControls(topic) {
            const statusBtn = document.getElementById('toggleTopicStatusBtn');
            const statusText = document.getElementById('topicStatusText');
            
            if (statusBtn && statusText) {
                if (topic.status === 'closed') {
                    statusBtn.className = 'btn-primary text-sm';
                    statusText.textContent = 'باز کردن';
                    statusBtn.querySelector('i').className = 'fas fa-unlock ml-1';
                } else {
                    statusBtn.className = 'btn-secondary text-sm';
                    statusText.textContent = 'بستن';
                    statusBtn.querySelector('i').className = 'fas fa-lock ml-1';
                }
            }
        }

        function updateReplySection(topic) {
            const replySection = document.getElementById('replySection');
            const replyContent = document.getElementById('replyContent');
            
            if (topic.status === 'closed') {
                replySection.innerHTML = `
                    <div class="text-center py-4 text-red-600">
                        <i class="fas fa-lock ml-2"></i>
                        این موضوع بسته شده و امکان پاسخ دادن وجود ندارد
                    </div>
                `;
            } else if (replyContent) {
                replyContent.disabled = false;
            }
        }

        // Load topic posts
        async function loadTopicPosts(topicId) {
            try {
                const response = await fetch(`api/posts.php?action=get_posts&topicId=${topicId}`);
                const data = await response.json();
                
                if (data.success && data.posts) {
                    displayTopicPosts(data.posts);
                }
            } catch (error) {
                console.error('Error loading posts:', error);
            }
        }

        function displayTopicPosts(posts) {
            const container = document.getElementById('topicPosts');
            
            if (posts.length === 0) {
                container.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-comment-slash text-3xl mb-4"></i>
                        <p>هنوز پاسخی ثبت نشده است</p>
                    </div>
                `;
                return;
            }
            
            container.innerHTML = posts.map((post, index) => `
                <div class="bg-white rounded-lg border p-6 relative">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center text-white font-bold">
                                ${post.userName.charAt(0)}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">${escapeHtml(post.userName)}</div>
                                <div class="text-sm text-gray-500">${formatDateTime(post.createdAt)}</div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 space-x-reverse">
                            <span class="text-sm text-gray-500">#${index + 1}</span>
                            ${isAdmin() ? `
                                <button onclick="deletePost(${post.id})" 
                                        class="text-red-500 hover:text-red-700 p-1" 
                                        title="حذف پاسخ">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            ` : ''}
                        </div>
                    </div>
                    
                    <div class="prose max-w-none mb-4">
                        ${escapeHtml(post.content).replace(/\n/g, '<br>')}
                    </div>
                    
                    <div class="flex items-center justify-between">
                        <button onclick="togglePostLike(${post.id})" 
                                class="flex items-center space-x-1 space-x-reverse text-gray-500 hover:text-red-500 transition-colors">
                            <i class="fas fa-heart"></i>
                            <span>${post.likes || 0}</span>
                        </button>
                    </div>
                </div>
            `).join('');
        }

        // Submit reply
        async function submitReply() {
            if (!currentTopicId) return;
            
            const content = document.getElementById('replyContent').value.trim();
            
            if (!content) {
                showNotification('خطا', 'لطفاً محتوای پاسخ را وارد کنید', 'error');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'add_post');
                formData.append('topicId', currentTopicId);
                formData.append('content', content);
                
                const response = await fetch('api/posts.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    document.getElementById('replyContent').value = '';
                    loadTopicPosts(currentTopicId);
                    showNotification('موفقیت', 'پاسخ شما ثبت شد', 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در ثبت پاسخ', 'error');
                }
            } catch (error) {
                console.error('Error submitting reply:', error);
                showNotification('خطا', 'خطا در ثبت پاسخ', 'error');
            }
        }

        // Close topic modal
        function closeTopicModal() {
            document.getElementById('topicModal').classList.remove('show');
            currentTopicId = null;
            currentTopicData = null;
        }

        // Admin functions
        function isAdmin() {
            return <?= current_user() && (current_user()['role'] ?? '') === 'admin' ? 'true' : 'false' ?>;
        }

        async function toggleTopicStatus() {
            if (!currentTopicId) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_topic_status');
                formData.append('topicId', currentTopicId);
                
                const response = await fetch('api/topic-admin.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    currentTopicData.status = data.status;
                    updateTopicAdminControls(currentTopicData);
                    updateReplySection(currentTopicData);
                    showNotification('موفقیت', data.message, 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در تغییر وضعیت', 'error');
                }
            } catch (error) {
                console.error('Error toggling topic status:', error);
                showNotification('خطا', 'خطا در تغییر وضعیت', 'error');
            }
        }

        async function deleteCurrentTopic() {
            if (!currentTopicId) return;
            
            if (!confirm('آیا از حذف این موضوع اطمینان دارید؟')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete_topic');
                formData.append('topicId', currentTopicId);
                
                const response = await fetch('api/topic-admin.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    closeTopicModal();
                    showTopics(currentCategoryId); // Refresh topics list
                    showNotification('موفقیت', 'موضوع حذف شد', 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در حذف موضوع', 'error');
                }
            } catch (error) {
                console.error('Error deleting topic:', error);
                showNotification('خطا', 'خطا در حذف موضوع', 'error');
            }
        }

        async function deletePost(postId) {
            if (!confirm('آیا از حذف این پاسخ اطمینان دارید؟')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete_post');
                formData.append('postId', postId);
                
                const response = await fetch('api/posts.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadTopicPosts(currentTopicId);
                    showNotification('موفقیت', 'پاسخ حذف شد', 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در حذف پاسخ', 'error');
                }
            } catch (error) {
                console.error('Error deleting post:', error);
                showNotification('خطا', 'خطا در حذف پاسخ', 'error');
            }
        }

        async function togglePostLike(postId) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_post_like');
                formData.append('postId', postId);
                
                const response = await fetch('api/posts.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadTopicPosts(currentTopicId);
                } else {
                    showNotification('خطا', data.error || 'خطا در ثبت لایک', 'error');
                }
            } catch (error) {
                console.error('Error toggling post like:', error);
                showNotification('خطا', 'خطا در ثبت لایک', 'error');
            }
        }

        // Chat admin functions
        async function loadChatStatus() {
            try {
                const response = await fetch('api/chat.php?action=status');
                const data = await response.json();
                
                if (data.success) {
                    updateChatUI(data.closed, data.is_admin);
                }
            } catch (error) {
                console.error('Error loading chat status:', error);
            }
        }

        function updateChatUI(isClosed, isAdmin) {
            const chatStatus = document.getElementById('chatStatus');
            const chatInput = document.getElementById('chatInputSection');
            const chatClosedMessage = document.getElementById('chatClosedMessage');
            const toggleChatBtn = document.getElementById('toggleChatBtn');
            
            if (isClosed) {
                chatStatus.className = 'w-2 h-2 bg-red-500 rounded-full';
                if (chatInput) chatInput.style.display = 'none';
                if (chatClosedMessage) chatClosedMessage.classList.remove('hidden');
                if (toggleChatBtn) toggleChatBtn.title = 'باز کردن چت';
            } else {
                chatStatus.className = 'w-2 h-2 bg-green-500 rounded-full animate-pulse';
                if (chatInput) chatInput.style.display = 'flex';
                if (chatClosedMessage) chatClosedMessage.classList.add('hidden');
                if (toggleChatBtn) toggleChatBtn.title = 'بستن چت';
            }
        }

        async function toggleChatStatus() {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle');
                
                const response = await fetch('api/chat.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    updateChatUI(data.closed, true);
                    showNotification('موفقیت', data.message, 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در تغییر وضعیت چت', 'error');
                }
            } catch (error) {
                console.error('Error toggling chat status:', error);
                showNotification('خطا', 'خطا در تغییر وضعیت چت', 'error');
            }
        }

        // Enhanced chat messages display with admin controls
        async function loadChatMessages() {
            try {
                const response = await fetch('api/chat.php');
                const data = await response.json();
                
                if (data.success && data.messages) {
                    const container = document.getElementById('chatMessages');
                    const isAdminUser = isAdmin();
                    
                    container.innerHTML = data.messages.map(msg => `
                        <div class="mb-3 relative group">
                            <div class="flex items-center space-x-2 space-x-reverse mb-1">
                                <span class="text-xs font-semibold text-gray-700">${escapeHtml(msg.user_name)}</span>
                                <span class="text-xs text-gray-500">${formatTime(msg.created_at)}</span>
                                ${isAdminUser ? `
                                    <button onclick="deleteChatMessage(${msg.id})" 
                                            class="opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 text-xs ml-1" 
                                            title="حذف پیام">
                                        <i class="fas fa-times"></i>
                                    </button>
                                ` : ''}
                            </div>
                            <div class="text-sm text-gray-800 bg-white rounded-lg px-3 py-2">${escapeHtml(msg.message)}</div>
                        </div>
                    `).join('');
                    
                    // Scroll to bottom
                    container.scrollTop = container.scrollHeight;
                }
            } catch (error) {
                console.error('Error loading chat messages:', error);
            }
        }

        async function deleteChatMessage(messageId) {
            if (!confirm('آیا از حذف این پیام اطمینان دارید؟')) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'delete');
                formData.append('messageId', messageId);
                
                const response = await fetch('api/chat.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    loadChatMessages();
                    showNotification('موفقیت', 'پیام حذف شد', 'success');
                } else {
                    showNotification('خطا', data.error || 'خطا در حذف پیام', 'error');
                }
            } catch (error) {
                console.error('Error deleting chat message:', error);
                showNotification('خطا', 'خطا در حذف پیام', 'error');
            }
        }

        // Utility functions
        function showLoading() {
            const loading = document.getElementById('loadingState');
            const categories = document.getElementById('categoriesView');
            const topics = document.getElementById('topicsView');
            
            if (loading) loading.classList.remove('hidden');
            if (categories) categories.classList.add('hidden');
            if (topics) topics.classList.add('hidden');
        }

        function hideLoading() {
            const loading = document.getElementById('loadingState');
            if (loading) loading.classList.add('hidden');
        }

        function showEmptyState() {
            const loading = document.getElementById('loadingState');
            const categories = document.getElementById('categoriesView');
            const topics = document.getElementById('topicsView');
            const empty = document.getElementById('emptyState');
            
            if (loading) loading.classList.add('hidden');
            if (categories) categories.classList.add('hidden');
            if (topics) topics.classList.add('hidden');
            if (empty) empty.classList.remove('hidden');
        }
        
        function showNotification(title, message, type = 'info') {
            console.log(`${type.toUpperCase()}: ${title} - ${message}`);
            
            // Create simple notification
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'error' ? '#ef4444' : type === 'success' ? '#10b981' : '#3b82f6'};
                color: white;
                padding: 16px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0,0,0,0.1);
                z-index: 1000;
                max-width: 300px;
                font-size: 14px;
            `;
            notification.innerHTML = `<strong>${title}</strong><br>${message}`;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 4000);
        }
        
        function showCategoriesView() {
            const categories = document.getElementById('categoriesView');
            const topics = document.getElementById('topicsView');
            
            if (categories) categories.classList.remove('hidden');
            if (topics) topics.classList.add('hidden');
        }

        // Enhanced initialization
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Forum page loaded');
            loadCategories();
            loadOnlineUsers();
            loadChatMessages();
            loadChatStatus();
            loadRecentActivity();
            
            // Auto-refresh every 30 seconds
            setInterval(() => {
                if (document.visibilityState === 'visible') {
                    loadOnlineUsers();
                    loadChatMessages();
                    loadRecentActivity();
                }
            }, 30000);
        });

        // Enter key for chat
        document.addEventListener('keypress', function(e) {
            if (e.target.id === 'chatInput' && e.key === 'Enter') {
                sendChatMessage();
            }
            if (e.target.id === 'replyContent' && e.key === 'Enter' && e.ctrlKey) {
                submitReply();
            }
        });

        // Close modals when clicking outside
        document.getElementById('topicModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeTopicModal();
            }
        });
        
        // Live Footer Stats Update
        function updateLiveStats() {
            // Update online users count
            const onlineElement = document.getElementById('onlineUsers');
            if (onlineElement) {
                const baseOnline = 10;
                const variation = Math.floor(Math.random() * 8) + 1; // 1-8
                onlineElement.textContent = baseOnline + variation;
            }
            
            // Update today views
            const viewsElement = document.getElementById('todayViews');
            if (viewsElement) {
                const baseViews = 180;
                const currentViews = parseInt(viewsElement.textContent) || baseViews;
                const increment = Math.floor(Math.random() * 3) + 1; // 1-3
                viewsElement.textContent = currentViews + increment;
            }
        }

        // Update stats every 10 seconds
        setInterval(updateLiveStats, 10000);
        
        // Initial stats load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateLiveStats, 1000);
        });
    </script>

    <!-- Footer -->
    <footer class="relative bg-gradient-to-r from-gray-900 via-slate-900 to-gray-900 text-white py-16 mt-20">
        <!-- Clean top border -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
        <!-- Simple Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute bottom-10 right-10 w-16 h-16 bg-white rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 w-12 h-12 bg-white rounded-full"></div>
        </div>
        
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <!-- Main Footer Content -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12 items-start">
                
                <!-- Brand Section -->
                <div class="text-center md:text-right">
                    <h3 class="text-3xl font-bold mb-4 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        سورس کده
                    </h3>
                    <p class="text-gray-300 text-base leading-relaxed mb-8">
                        بزرگترین مرجع پروژه‌های متن‌باز<br>
                        و انجمن توسعه‌دهندگان ایران
                    </p>
                    
                    <!-- Quick Links -->
                    <div class="space-y-3">
                        <a href="sources.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-blue-500/20 group-hover:bg-blue-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-gem text-blue-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">کاوش پروژه‌ها</span>
                        </a>
                        <a href="forum.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-green-500/20 group-hover:bg-green-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-comments text-green-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">انجمن توسعه‌دهندگان</span>
                        </a>
                        <a href="upload.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-purple-500/20 group-hover:bg-purple-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-upload text-purple-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">آپلود پروژه</span>
                        </a>
                    </div>
                </div>
                
                <!-- Contact & Social Section -->
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-bold mb-6 text-white">ارتباط با ما</h4>
                    
                    <div class="space-y-4">
                        <!-- Telegram Channel -->
                        <a href="https://t.me/sourrce_kade" target="_blank" class="group block bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 rounded-2xl p-4 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <div class="flex items-center space-x-4 space-x-reverse">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.56c-.12.56-.44.7-.9.44l-2.49-1.83-1.2 1.16c-.13.13-.25.25-.5.25l.18-2.51 4.56-4.12c.2-.18-.04-.28-.3-.1l-5.64 3.55-2.43-.76c-.53-.16-.54-.53.11-.78l9.5-3.66c.44-.16.83.1.68.78z"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold">کانال تلگرام</p>
                                    <p class="text-blue-100 text-sm">آخرین اخبار و بروزرسانی‌ها</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Developer Contact -->
                        <a href="https://t.me/itsthemoein" target="_blank" class="group block bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 rounded-2xl p-4 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <div class="flex items-center space-x-4 space-x-reverse">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold">تیم توسعه</p>
                                    <p class="text-purple-100 text-sm">پشتیبانی و همکاری</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Features Section -->
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-bold mb-6 text-white">چرا سورس کده؟</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">کیفیت بالا</p>
                                <p class="text-gray-400 text-sm">پروژه‌های تست شده</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">انجمن فعال</p>
                                <p class="text-gray-400 text-sm">پشتیبانی ۲۴ ساعته</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">رایگان</p>
                                <p class="text-gray-400 text-sm">برای همه توسعه‌دهندگان</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="border-t border-gray-700 mt-16 pt-8">
                <div class="text-center">
                    <div class="bg-gray-800/50 rounded-xl p-6 mx-auto max-w-2xl border border-gray-700">
                        <p class="text-gray-300 text-base font-medium mb-3">
                            © <?= date('Y') ?> سورس کده - تمامی حقوق محفوظ است
                        </p>
                        <p class="text-gray-400 text-sm">
                            طراحی و توسعه با <span class="text-red-400 animate-pulse">❤️</span> برای جامعه برنامه‌نویسی ایران
                        </p>
                        <div class="flex justify-center items-center mt-4 space-x-4 space-x-reverse">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            <span class="text-xs text-gray-500">آنلاین و فعال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
</body>
</html>