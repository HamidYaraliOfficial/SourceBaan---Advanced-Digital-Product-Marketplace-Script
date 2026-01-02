<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';

$currentUser = current_user();
if (!$currentUser || !is_admin()) {
    header('Location: ../index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>پنل مدیریت پیشرفته - SourceBaan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { font-family: 'Vazirmatn', sans-serif; }
.gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.gradient-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
.gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
.nav-tab { transition: all 0.3s ease; }
.nav-tab.active { border-bottom: 3px solid #667eea; color: #667eea; }
.modal-backdrop { backdrop-filter: blur(5px); background: rgba(0, 0, 0, 0.5); }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">

<!-- Header -->
<header class="bg-white shadow-lg sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4 space-x-reverse">
                <a href="../index.php" class="p-2 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"></path>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">پنل مدیریت</h1>
                    <p class="text-gray-600 text-sm">مدیریت کامل سایت SourceBaan</p>
                </div>
            </div>
            
            <!-- Navigation Tabs -->
            <nav class="flex space-x-8 space-x-reverse">
                <button onclick="showTab('dashboard')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium active">داشبورد</button>
                <button onclick="showTab('users')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">کاربران</button>
                <button onclick="showTab('verification')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">تیک ابی</button>
                <button onclick="showTab('shop')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">فروشگاه</button>
                <button onclick="showTab('analytics')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">آنالیتیکس</button>
                <button onclick="showTab('notifications')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">اعلانات</button>
                <button onclick="showTab('settings')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">تنظیمات</button>
                <button onclick="showTab('reports')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">گزارش‌ها</button>
                <button onclick="showTab('ads')" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">تبلیغات</button>
                <a href="index.php" class="nav-tab px-4 py-2 text-gray-600 hover:text-blue-600 font-medium">تایید پروژه‌ها</a>
            </nav>
            
            <div class="flex items-center space-x-3 space-x-reverse">
                <div class="text-right">
                    <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($currentUser['name']) ?></p>
                    <p class="text-xs text-red-600">ادمین اصلی</p>
                </div>
                <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold">
                    <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto p-6">
    <!-- Dashboard Tab -->
    <div id="dashboardTab" class="tab-content">
        <!-- Advanced Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
                <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalUsers">0</div>
                <div class="text-sm text-gray-600">کل کاربران</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
                <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalProjects">0</div>
                <div class="text-sm text-gray-600">پروژه‌های تایید شده</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
                <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="pendingSubmissions">0</div>
                <div class="text-sm text-gray-600">در انتظار تایید</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
                <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 0v1m-2 0V6a2 2 0 00-2 0v1m2 0V9.5m0 0v3m0-3h4.5m0 0a2.25 2.25 0 110 4.5M12 12.75h4.5a2.25 2.25 0 000-4.5M12 12.75V9.5"></path>
                    </svg>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalDownloads">0</div>
                <div class="text-sm text-gray-600">کل دانلودها</div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            <!-- Top Languages Chart -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">محبوب‌ترین زبان‌ها</h3>
                <div id="topLanguages" class="space-y-4"></div>
            </div>

            <!-- Active Users -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">کاربران فعال</h3>
                <div id="activeUsers" class="space-y-4"></div>
            </div>
        </div>

        <!-- Recent Activities -->
        <div class="bg-white rounded-3xl shadow-xl p-8 mt-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">فعالیت‌های اخیر</h3>
            <div id="recentActivities" class="space-y-3"></div>
        </div>
    </div>

    <!-- Users Tab -->
    <div id="usersTab" class="tab-content hidden">
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">مدیریت کاربران</h3>
                <div class="flex space-x-3 space-x-reverse">
                    <input type="text" id="userSearch" placeholder="جستجوی کاربر..." class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
            <div id="usersList" class="space-y-4"></div>
        </div>
    </div>

    <!-- Verification Tab -->
    <div id="verificationTab" class="tab-content hidden">
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">مدیریت درخواست‌های تیک ابی</h3>
                <button onclick="loadVerificationRequests()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-refresh ml-2"></i>بروزرسانی
                </button>
            </div>
            
            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="pendingVerifications">0</div>
                    <div class="text-sm opacity-90">در انتظار بررسی</div>
                </div>
                <div class="bg-gradient-to-r from-green-400 to-teal-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="approvedVerifications">0</div>
                    <div class="text-sm opacity-90">تایید شده</div>
                </div>
                <div class="bg-gradient-to-r from-red-400 to-pink-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="rejectedVerifications">0</div>
                    <div class="text-sm opacity-90">رد شده</div>
                </div>
            </div>
            
            <!-- Filter Buttons -->
            <div class="flex space-x-3 space-x-reverse mb-6">
                <button onclick="filterVerifications('all')" class="verification-filter-btn filter-active px-4 py-2 rounded-lg font-medium transition-colors">همه</button>
                <button onclick="filterVerifications('pending')" class="verification-filter-btn px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors">در انتظار</button>
                <button onclick="filterVerifications('approved')" class="verification-filter-btn px-4 py-2 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors">تایید شده</button>
                <button onclick="filterVerifications('rejected')" class="verification-filter-btn px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors">رد شده</button>
            </div>
            
            <div id="verificationRequests" class="space-y-4">
                <div class="text-center py-8 text-gray-500">در حال بارگذاری...</div>
            </div>
        </div>
    </div>

    <!-- Shop Management Tab -->
    <div id="shopTab" class="tab-content hidden">
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-2xl font-bold text-gray-900">مدیریت فروشگاه</h3>
                <button onclick="loadShopProducts()" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-refresh ml-2"></i>بروزرسانی
                </button>
            </div>
            
            <!-- Shop Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-400 to-indigo-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="totalProducts">0</div>
                    <div class="text-sm opacity-90">کل محصولات</div>
                </div>
                <div class="bg-gradient-to-r from-yellow-400 to-orange-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="pendingProducts">0</div>
                    <div class="text-sm opacity-90">در انتظار تایید</div>
                </div>
                <div class="bg-gradient-to-r from-green-400 to-teal-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="approvedProducts">0</div>
                    <div class="text-sm opacity-90">تایید شده</div>
                </div>
                <div class="bg-gradient-to-r from-purple-400 to-pink-500 rounded-2xl p-6 text-white text-center">
                    <div class="text-2xl font-bold" id="totalProductViews">0</div>
                    <div class="text-sm opacity-90">کل بازدید</div>
                </div>
            </div>
            
            <!-- Product Filter Buttons -->
            <div class="flex space-x-3 space-x-reverse mb-6">
                <button onclick="filterProducts('all')" class="product-filter-btn filter-active px-4 py-2 rounded-lg font-medium transition-colors">همه</button>
                <button onclick="filterProducts('pending')" class="product-filter-btn px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg hover:bg-yellow-200 transition-colors">در انتظار</button>
                <button onclick="filterProducts('approved')" class="product-filter-btn px-4 py-2 bg-green-100 text-green-800 rounded-lg hover:bg-green-200 transition-colors">تایید شده</button>
                <button onclick="filterProducts('rejected')" class="product-filter-btn px-4 py-2 bg-red-100 text-red-800 rounded-lg hover:bg-red-200 transition-colors">رد شده</button>
            </div>
            
            <div id="shopProducts" class="space-y-4">
                <div class="text-center py-8 text-gray-500">در حال بارگذاری...</div>
            </div>
        </div>
    </div>
    
    <!-- Analytics Tab -->
    <div id="analyticsTab" class="tab-content hidden">
        <!-- Shop Analytics Dashboard -->
        <div class="bg-white rounded-2xl p-6 shadow-lg mb-8">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-10 h-10 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center">
                    <i class="fas fa-chart-bar text-white"></i>
                </div>
                <h2 class="text-xl font-bold text-gray-900">آنالیتیکس فروشگاه</h2>
            </div>
            
            <!-- Real-time Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm">کاربران آنلاین</p>
                            <p class="text-2xl font-bold" id="analyticsOnlineUsers">-</p>
                        </div>
                        <i class="fas fa-users text-3xl text-blue-200"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm">بازدید امروز</p>
                            <p class="text-2xl font-bold" id="analyticsTodayVisits">-</p>
                        </div>
                        <i class="fas fa-eye text-3xl text-green-200"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm">جستجو امروز</p>
                            <p class="text-2xl font-bold" id="analyticsTodaySearches">-</p>
                        </div>
                        <i class="fas fa-search text-3xl text-purple-200"></i>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm">تماس امروز</p>
                            <p class="text-2xl font-bold" id="analyticsTodayContacts">-</p>
                        </div>
                        <i class="fas fa-phone text-3xl text-orange-200"></i>
                    </div>
                </div>
            </div>
            
            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <!-- Hourly Activity Chart -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4">فعالیت ساعتی امروز</h3>
                    <div class="h-64 flex items-end justify-center space-x-1" id="hourlyChart">
                        <!-- Chart bars will be generated here -->
                    </div>
                </div>
                
                <!-- Weekly Stats Chart -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4">آمار هفته گذشته</h3>
                    <div class="h-64 flex items-end justify-center space-x-1" id="weeklyChart">
                        <!-- Chart bars will be generated here -->
                    </div>
                </div>
            </div>
            
            <!-- Popular Products -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4">محصولات محبوب امروز</h3>
                    <div id="popularProducts" class="space-y-3">
                        <!-- Popular products will be loaded here -->
                    </div>
                </div>
                
                <!-- Search Trends -->
                <div class="bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold mb-4">پرجستجوترین عبارات</h3>
                    <div id="searchTrends" class="space-y-3">
                        <!-- Search trends will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Notifications Tab -->
    <div id="notificationsTab" class="tab-content hidden">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Send Broadcast -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">ارسال اعلان عمومی</h3>
                <form id="broadcastForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان</label>
                        <input type="text" id="broadcastTitle" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">پیام</label>
                        <textarea id="broadcastMessage" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع</label>
                        <select id="broadcastType" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="info">اطلاعات</option>
                            <option value="success">موفقیت</option>
                            <option value="warning">هشدار</option>
                            <option value="error">خطا</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full gradient-primary text-white py-3 rounded-xl font-bold hover:shadow-lg transition-all duration-300">
                        ارسال به همه کاربران
                    </button>
                </form>
            </div>

            <!-- Send Individual -->
            <div class="bg-white rounded-3xl shadow-xl p-8">
                <h3 class="text-2xl font-bold text-gray-900 mb-6">ارسال اعلان شخصی</h3>
                <form id="individualForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">انتخاب کاربر</label>
                        <select id="targetUser" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">انتخاب کنید...</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان</label>
                        <input type="text" id="individualTitle" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">پیام</label>
                        <textarea id="individualMessage" rows="4" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع</label>
                        <select id="individualType" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="info">اطلاعات</option>
                            <option value="success">موفقیت</option>
                            <option value="warning">هشدار</option>
                            <option value="error">خطا</option>
                        </select>
                    </div>
                    <button type="submit" class="w-full gradient-secondary text-white py-3 rounded-xl font-bold hover:shadow-lg transition-all duration-300">
                        ارسال اعلان
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Reports Tab -->
    <div id="reportsTab" class="tab-content hidden">
        <!-- Stats Overview -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-br from-red-500 to-red-600 rounded-3xl p-6 text-white shadow-xl transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-red-100 text-sm font-medium">در انتظار بررسی</p>
                        <p class="text-3xl font-bold" id="pendingReportsCount">0</p>
                        <p class="text-red-100 text-xs mt-1">نیاز به اقدام</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-3xl p-6 text-white shadow-xl transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">حل شده</p>
                        <p class="text-3xl font-bold" id="resolvedReportsCount">0</p>
                        <p class="text-green-100 text-xs mt-1">رسیدگی شده</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-gray-500 to-gray-600 rounded-3xl p-6 text-white shadow-xl transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-gray-100 text-sm font-medium">رد شده</p>
                        <p class="text-3xl font-bold" id="dismissedReportsCount">0</p>
                        <p class="text-gray-100 text-xs mt-1">غیرموجه</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-3xl p-6 text-white shadow-xl transform hover:scale-105 transition-all duration-300">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">کل گزارش‌ها</p>
                        <p class="text-3xl font-bold" id="totalReportsCount">0</p>
                        <p class="text-purple-100 text-xs mt-1">همه موارد</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-xl p-8">
            <!-- Header with Advanced Controls -->
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between mb-8 space-y-4 lg:space-y-0">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">مدیریت گزارش‌های کاربران</h3>
                    <p class="text-gray-600">بررسی، پیگیری و پاسخ به گزارش‌های ارسالی کاربران</p>
                </div>
                
                <!-- Action Buttons -->
                <div class="flex items-center space-x-3 space-x-reverse">
                    <button onclick="sendBulkEmail()" class="gradient-primary text-white px-6 py-3 rounded-2xl font-bold hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        ارسال ایمیل گروهی
                    </button>
                    <button onclick="exportReports()" class="bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded-2xl font-bold hover:shadow-lg transition-all duration-300">
                        <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        خروجی Excel
                    </button>
                </div>
            </div>

            <!-- Enhanced Filters -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">فیلتر وضعیت</label>
                    <select id="reportStatusFilter" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-gradient-to-r from-blue-50 to-blue-100">
                        <option value="all">همه وضعیت‌ها</option>
                        <option value="pending">در انتظار بررسی</option>
                        <option value="resolved">حل شده</option>
                        <option value="dismissed">رد شده</option>
                    </select>
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">نوع گزارش</label>
                    <select id="reportTypeFilter" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-purple-500 focus:border-transparent bg-gradient-to-r from-purple-50 to-purple-100">
                        <option value="all">همه انواع</option>
                        <option value="spam">اسپم</option>
                        <option value="inappropriate">نامناسب</option>
                        <option value="copyright">نقض کپی رایت</option>
                        <option value="malware">بدافزار</option>
                        <option value="other">سایر</option>
                    </select>
            </div>

                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">جستجو</label>
                    <input type="text" id="reportSearchInput" placeholder="جستجو در عنوان، توضیحات یا کاربر..." class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-green-500 focus:border-transparent bg-gradient-to-r from-green-50 to-green-100">
                </div>
                
                <div class="space-y-2">
                    <label class="text-sm font-medium text-gray-700">مرتب‌سازی</label>
                    <select id="reportSortOrder" class="w-full px-4 py-3 border border-gray-300 rounded-2xl focus:ring-2 focus:ring-orange-500 focus:border-transparent bg-gradient-to-r from-orange-50 to-orange-100">
                        <option value="newest">جدیدترین</option>
                        <option value="oldest">قدیمی‌ترین</option>
                        <option value="priority">اولویت‌دار</option>
                    </select>
                </div>
            </div>

            <!-- Bulk Actions -->
            <div id="bulkActionsBar" class="hidden bg-gradient-to-r from-indigo-50 to-purple-50 border border-indigo-200 rounded-2xl p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <span class="text-indigo-800 font-medium" id="selectedCount">0 گزارش انتخاب شده</span>
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <button onclick="bulkAction('resolve')" class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm transition-colors">
                            حل کردن همه
                        </button>
                        <button onclick="bulkAction('dismiss')" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm transition-colors">
                            رد کردن همه
                        </button>
                        <button onclick="bulkAction('email')" class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm transition-colors">
                            ایمیل گروهی
                        </button>
                        <button onclick="clearSelection()" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm transition-colors">
                            لغو انتخاب
                        </button>
                    </div>
                </div>
            </div>

            <!-- Reports Grid -->
            <div id="reportsContainer" class="space-y-6"></div>
            
            <!-- Pagination -->
            <div id="reportsPagination" class="flex items-center justify-center mt-8 space-x-2 space-x-reverse"></div>
        </div>
    </div>

    <!-- Ads Management Tab -->
    <div id="adsTab" class="tab-content hidden">
        <!-- Header Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-3xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-blue-100 text-sm font-medium">کل تبلیغات</p>
                        <p class="text-3xl font-bold" id="totalAdsCount">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-bullhorn text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-3xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-green-100 text-sm font-medium">کل بازدیدها</p>
                        <p class="text-3xl font-bold" id="totalViewsCount">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-eye text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-3xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-purple-100 text-sm font-medium">کل کلیک‌ها</p>
                        <p class="text-3xl font-bold" id="totalClicksCount">0</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-mouse-pointer text-2xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-3xl p-6 text-white shadow-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-orange-100 text-sm font-medium">نرخ کلیک میانگین</p>
                        <p class="text-3xl font-bold" id="avgClickRateCount">0%</p>
                    </div>
                    <div class="bg-white/20 p-3 rounded-full">
                        <i class="fas fa-chart-line text-2xl"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h3 class="text-3xl font-bold text-gray-900 mb-2">مدیریت تبلیغات</h3>
                    <p class="text-gray-600">مدیریت و پیگیری عملکرد تبلیغات سایت</p>
                </div>
                <button onclick="showCreateAdModal()" class="gradient-primary text-white px-8 py-4 rounded-2xl font-bold hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-plus ml-2"></i>تبلیغ جدید
                </button>
            </div>

            <div id="adsList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
        </div>
    </div>

    <!-- Settings Tab -->
    <div id="settingsTab" class="tab-content hidden">
        <div class="bg-white rounded-3xl shadow-xl p-8">
            <h3 class="text-2xl font-bold text-gray-900 mb-6">تنظیمات سایت</h3>
            <form id="settingsForm" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نام سایت</label>
                    <input type="text" id="siteName" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات سایت</label>
                    <input type="text" id="siteDescription" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">حداکثر حجم آپلود (بایت)</label>
                    <input type="number" id="maxUploadSize" min="102400" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">پسوندهای مجاز</label>
                    <input type="text" id="allowedExtensions" placeholder="zip,rar,js,py,php" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">امتیاز آپلود</label>
                    <input type="number" id="pointsPerUpload" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">امتیاز دانلود</label>
                    <input type="number" id="pointsPerDownload" min="0" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <input type="checkbox" id="registrationEnabled" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="registrationEnabled" class="text-sm font-medium text-gray-700">ثبت نام فعال</label>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <input type="checkbox" id="requireApproval" class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                    <label for="requireApproval" class="text-sm font-medium text-gray-700">نیاز به تایید پروژه‌ها</label>
                </div>
                <div class="md:col-span-2">
                    <button type="submit" class="gradient-success text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg transition-all duration-300">
                        ذخیره تنظیمات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modals -->
<div id="userModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full">
        <div class="p-8">
            <h3 class="text-xl font-bold text-gray-900 mb-6" id="userModalTitle">مدیریت کاربر</h3>
            <div id="userModalContent"></div>
        </div>
    </div>
</div>

<!-- Ad Modal -->
<div id="adModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900" id="adModalTitle">تبلیغ جدید</h3>
                <button onclick="closeAdModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <form id="adForm" class="space-y-6">
                <input type="hidden" id="adId">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان تبلیغ</label>
                        <input type="text" id="adTitle" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع تبلیغ</label>
                        <select id="adType" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="banner">بنر</option>
                            <option value="sidebar">نوار کناری</option>
                            <option value="inline">درون محتوا</option>
                            <option value="popup">پاپ آپ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">اولویت (0-100)</label>
                        <input type="number" id="adPriority" min="0" max="100" value="50" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">محتوای تبلیغ</label>
                        <textarea id="adContent" rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">لینک تبلیغ</label>
                        <input type="url" id="adLink" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تصویر تبلیغ (URL)</label>
                        <input type="url" id="adImage" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ شروع (اختیاری)</label>
                        <input type="date" id="adStartDate" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">تاریخ پایان (اختیاری)</label>
                        <input type="date" id="adEndDate" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">موقعیت‌های نمایش</label>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="home_header" class="ml-2">
                                هدر صفحه اصلی
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="home_sidebar" class="ml-2">
                                نوار کناری صفحه اصلی
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="sources_top" class="ml-2">
                                بالای کاوش سورس
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="sources_bottom" class="ml-2">
                                پایین کاوش سورس
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="project_page" class="ml-2">
                                صفحه پروژه
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="profile_page" class="ml-2">
                                صفحه پروفایل
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="forum_top" class="ml-2">
                                بالای انجمن
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" name="locations" value="forum_sidebar" class="ml-2">
                                نوار کناری انجمن
                            </label>
                        </div>
                    </div>
                </div>
                <div class="flex space-x-4 space-x-reverse">
                    <button type="submit" class="gradient-primary text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg transition-all duration-300">
                        ذخیره تبلیغ
                    </button>
                    <button type="button" onclick="closeAdModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div id="emailModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-screen overflow-y-auto">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">ارسال ایمیل</h3>
                <button onclick="closeEmailModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <form id="emailForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">نوع ایمیل</label>
                        <select id="emailTemplate" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="report_resolved">گزارش حل شد</option>
                            <option value="report_dismissed">گزارش رد شد</option>
                            <option value="project_updated">پروژه به‌روز شد</option>
                            <option value="custom">پیام سفارشی</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">از طرف</label>
                        <input type="email" id="emailFrom" value="admin@sourcebaan.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">به</label>
                        <input type="email" id="emailTo" placeholder="user@example.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">موضوع</label>
                        <input type="text" id="emailSubject" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">متن ایمیل</label>
                        <div class="border border-gray-300 rounded-xl overflow-hidden">
                            <div class="bg-gray-50 px-4 py-2 border-b border-gray-200 flex items-center space-x-2 space-x-reverse">
                                <button type="button" onclick="formatEmail('bold')" class="p-1 hover:bg-gray-200 rounded">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 4h8a4 4 0 0 1 0 8H6v4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2Z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 12h9a4 4 0 0 1 0 8H6"></path>
                                    </svg>
                                </button>
                                <span class="text-sm text-gray-600">HTML پشتیبانی می‌شود</span>
                            </div>
                            <textarea id="emailBody" rows="8" class="w-full px-4 py-3 border-0 focus:ring-0 resize-none" placeholder="متن ایمیل خود را وارد کنید..."></textarea>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <button type="submit" class="gradient-primary text-white px-8 py-3 rounded-2xl font-bold hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                        <svg class="w-4 h-4 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        ارسال ایمیل
                    </button>
                    <button type="button" id="previewEmailBtn" class="bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded-xl font-medium transition-colors">
                        پیش‌نمایش
                    </button>
                    <button type="button" onclick="closeEmailModal()" class="bg-gray-200 text-gray-700 px-6 py-3 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                        انصراف
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Report Details Modal -->
<div id="reportDetailsModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-screen overflow-y-auto">
        <div class="p-8">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">جزئیات گزارش</h3>
                <button onclick="closeReportDetailsModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div id="reportDetailsContent" class="space-y-6">
                <!-- Content will be dynamically loaded -->
            </div>
        </div>
    </div>
</div>

<script>
let currentTab = 'dashboard';
let allUsers = [];

// Tab Management
function showTab(tabName) {
    document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
    document.querySelectorAll('.nav-tab').forEach(btn => btn.classList.remove('active'));
    
    document.getElementById(tabName + 'Tab').classList.remove('hidden');
    event.target.classList.add('active');
    
    currentTab = tabName;
    
    if (tabName === 'dashboard') loadDashboard();
    else if (tabName === 'users') loadUsers();
    else if (tabName === 'verification') loadVerificationRequests();
    else if (tabName === 'shop') loadShopProducts();
    else if (tabName === 'analytics') loadAnalytics();
    else if (tabName === 'notifications') loadNotifications();
    else if (tabName === 'settings') loadSettings();
    else if (tabName === 'reports') loadReports();
    else if (tabName === 'ads') loadAds();
}

// Dashboard Functions
async function loadDashboard() {
    try {
        const res = await fetch('../api/admin-advanced.php?action=advanced_stats');
        const data = await res.json();
        
        if (data.ok) {
            const stats = data.stats;
            
            document.getElementById('totalUsers').textContent = stats.totalUsers;
            document.getElementById('totalProjects').textContent = stats.totalProjects;
            document.getElementById('pendingSubmissions').textContent = stats.pendingSubmissions;
            document.getElementById('totalDownloads').textContent = stats.totalDownloads.toLocaleString('fa-IR');
            
            // Top Languages
            const languagesHtml = Object.entries(stats.topLanguages).map(([lang, count]) => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <span class="font-medium">${lang}</span>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <span class="text-sm text-gray-600">${count} پروژه</span>
                        <div class="w-20 h-2 bg-gray-200 rounded-full">
                            <div class="h-full bg-blue-500 rounded-full" style="width: ${(count / Math.max(...Object.values(stats.topLanguages))) * 100}%"></div>
                        </div>
                    </div>
                </div>
            `).join('');
            document.getElementById('topLanguages').innerHTML = languagesHtml;
            
            // Active Users
            const usersHtml = stats.activeUsers.map((user, index) => `
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 ${index === 0 ? 'bg-yellow-500' : index === 1 ? 'bg-gray-400' : 'bg-yellow-600'} text-white rounded-full flex items-center justify-center font-bold text-sm">
                            ${index + 1}
                        </div>
                        <span class="font-medium">${escapeHtml(user.name)}</span>
                    </div>
                    <span class="text-sm text-gray-600">${user.projectCount} پروژه</span>
                </div>
            `).join('');
            document.getElementById('activeUsers').innerHTML = usersHtml;
            
            // Recent Activities
            const activitiesHtml = stats.recentActivities.map(activity => `
                <div class="flex items-center space-x-3 space-x-reverse p-3 hover:bg-gray-50 rounded-lg">
                    <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                    <span class="flex-1 text-sm">${escapeHtml(activity.description)}</span>
                    <span class="text-xs text-gray-500">${new Date(activity.timestamp).toLocaleDateString('fa-IR')}</span>
                </div>
            `).join('');
            document.getElementById('recentActivities').innerHTML = activitiesHtml;
        }
    } catch (err) {
        console.error('Error loading dashboard:', err);
    }
}

// Users Functions
async function loadUsers() {
    try {
        const res = await fetch('../api/admin-advanced.php?action=users');
        const data = await res.json();
        
        if (data.ok) {
            allUsers = data.users;
            displayUsers(allUsers);
            
            // Populate user select for notifications
            const userSelect = document.getElementById('targetUser');
            userSelect.innerHTML = '<option value="">انتخاب کنید...</option>' + 
                allUsers.map(user => `<option value="${user.id}">${escapeHtml(user.name)} (${escapeHtml(user.email)})</option>`).join('');
        }
    } catch (err) {
        console.error('Error loading users:', err);
    }
}

function displayUsers(users) {
    const html = users.map(user => `
        <div class="flex items-center justify-between p-6 bg-gray-50 rounded-2xl">
            <div class="flex items-center space-x-4 space-x-reverse">
                <div class="w-12 h-12 ${user.role === 'admin' ? 'bg-red-500' : 'bg-blue-500'} text-white rounded-xl flex items-center justify-center font-bold">
                    ${escapeHtml(user.name.charAt(0).toUpperCase())}
                </div>
                <div>
                    <h4 class="font-bold text-gray-900">${escapeHtml(user.name)} ${user.verified ? "<span title='تایید شده' class='inline-block align-middle text-blue-500 ml-2'><svg class='w-4 h-4' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2'><path d='M20 6L9 17l-5-5'></path></svg></span>" : ''}</h4>
                    <p class="text-sm text-gray-600">${escapeHtml(user.email)}</p>
                    <div class="flex items-center space-x-4 space-x-reverse text-xs text-gray-500 mt-1">
                        <span>امتیاز: ${user.points}</span>
                        <span>پروژه: ${user.projectCount}</span>
                        <span>دانلود: ${user.totalDownloads}</span>
                        ${user.lastIp ? `<span>IP: ${escapeHtml(user.lastIp)}</span>` : ''}
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-3 space-x-reverse">
                <span class="px-3 py-1 ${user.role === 'admin' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800'} rounded-full text-xs font-medium">
                    ${user.role === 'admin' ? 'ادمین' : 'کاربر'}
                </span>
                <button onclick="manageUser(${user.id})" class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-lg text-sm transition-colors">
                    مدیریت
                </button>
            </div>
        </div>
    `).join('');
    document.getElementById('usersList').innerHTML = html;
}

function manageUser(userId) {
    const user = allUsers.find(u => u.id === userId);
    if (!user) return;
    
    document.getElementById('userModalTitle').textContent = `مدیریت ${user.name}`;
    document.getElementById('userModalContent').innerHTML = `
        <div class="space-y-4">
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <span>امتیاز فعلی:</span>
                <span class="font-bold">${user.points}</span>
            </div>
            
            <div class="flex space-x-2 space-x-reverse">
                <button onclick="adjustPoints(${userId}, 50, 'پاداش ادمین')" class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg">+50</button>
                <button onclick="adjustPoints(${userId}, 100, 'پاداش ویژه')" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded-lg">+100</button>
                <button onclick="adjustPoints(${userId}, -50, 'جریمه')" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-lg">-50</button>
            </div>
            
            <div>
                <input type="number" id="customPoints" placeholder="مقدار دلخواه..." class="w-full px-4 py-2 border border-gray-300 rounded-lg">
                <button onclick="adjustCustomPoints(${userId})" class="w-full mt-2 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">اعمال امتیاز</button>
            </div>
            
            <hr class="my-4">
            
            <div class="flex space-x-2 space-x-reverse">
                ${user.role === 'admin' ? 
                    `<button onclick="changeRole(${userId}, 'user')" class="flex-1 bg-orange-500 hover:bg-orange-600 text-white py-2 px-4 rounded-lg">تنزل به کاربر</button>` :
                    `<button onclick="changeRole(${userId}, 'admin')" class="flex-1 bg-purple-500 hover:bg-purple-600 text-white py-2 px-4 rounded-lg">ارتقا به ادمین</button>`
                }
                <button onclick="sendNotificationToUser(${userId})" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg">ارسال پیام</button>
            </div>

            <div class="flex space-x-2 space-x-reverse">
                <button id="verifyBtn_${userId}" onclick="toggleVerified(${userId})" class="flex-1 ${user.verified ? 'bg-blue-600 hover:bg-blue-700' : 'bg-gray-200 hover:bg-gray-300'} text-white py-2 px-4 rounded-lg">${user.verified ? 'لغو تیک آبی' : 'تایید (تیک آبی)'}</button>
                <button onclick="banUser(${userId})" class="flex-1 bg-red-600 hover:bg-red-700 text-white py-2 px-4 rounded-lg">بن کاربر (IP)</button>
            </div>
            
            <button onclick="closeUserModal()" class="w-full mt-4 bg-gray-500 hover:bg-gray-600 text-white py-2 px-4 rounded-lg">بستن</button>
        </div>
    `;
    
    document.getElementById('userModal').classList.remove('hidden');
}

async function adjustPoints(userId, points, reason) {
    try {
        const fd = new FormData();
        fd.append('action', 'adjust_points');
        fd.append('userId', userId);
        fd.append('points', points);
        fd.append('reason', reason);
        
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            closeUserModal();
            loadUsers();
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

function adjustCustomPoints(userId) {
    const points = parseInt(document.getElementById('customPoints').value);
    if (isNaN(points)) {
        showNotification('خطا', 'مقدار امتیاز نامعتبر است', 'error');
        return;
    }
    
    const reason = prompt('علت تغییر امتیاز:');
    if (reason !== null) {
        adjustPoints(userId, points, reason);
    }
}

async function changeRole(userId, newRole) {
    if (!confirm(`آیا از تغییر نقش این کاربر به ${newRole === 'admin' ? 'ادمین' : 'کاربر'} اطمینان دارید؟`)) return;
    
    try {
        const fd = new FormData();
        fd.append('action', 'change_role');
        fd.append('userId', userId);
        fd.append('role', newRole);
        
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            closeUserModal();
            loadUsers();
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function banUser(userId) {
    if (!confirm('آیا از بن آی‌پی آخر این کاربر اطمینان دارید؟')) return;
    try {
        const fd = new FormData();
        fd.append('action', 'ban_user');
        fd.append('userId', userId);
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        if (data.ok) {
            showNotification('موفقیت', 'کاربر بن شد (IP)', 'success');
            closeUserModal();
        } else {
            showNotification('خطا', data.error || 'خطا در بن کاربر', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

function closeUserModal() {
    document.getElementById('userModal').classList.add('hidden');
}

async function toggleVerified(userId) {
    if (!confirm('آیا از تغییر وضعیت تیک آبی این کاربر اطمینان دارید؟')) return;
    try {
        const fd = new FormData();
        fd.append('action', 'toggle_verified');
        fd.append('userId', userId);
        const res = await fetch('../api/admin-advanced.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            // Refresh list and modal
            await loadUsers();
            // update modal button text/color if open
            const btn = document.getElementById('verifyBtn_' + userId);
            if (btn) {
                // find updated user
                const u = allUsers.find(x => x.id === userId);
                if (u) {
                    btn.textContent = u.verified ? 'لغو تیک آبی' : 'تایید (تیک آبی)';
                    btn.className = 'flex-1 ' + (u.verified ? 'bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg' : 'bg-gray-200 hover:bg-gray-300 text-gray-700 py-2 px-4 rounded-lg');
                }
            }
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق بود', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Notifications Functions
function loadNotifications() {
    document.getElementById('broadcastForm').addEventListener('submit', handleBroadcast);
    document.getElementById('individualForm').addEventListener('submit', handleIndividual);
}

async function handleBroadcast(e) {
    e.preventDefault();
    
    const fd = new FormData();
    fd.append('action', 'broadcast_notification');
    fd.append('title', document.getElementById('broadcastTitle').value);
    fd.append('message', document.getElementById('broadcastMessage').value);
    fd.append('type', document.getElementById('broadcastType').value);
    
    try {
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            document.getElementById('broadcastForm').reset();
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function handleIndividual(e) {
    e.preventDefault();
    
    const fd = new FormData();
    fd.append('action', 'send_notification');
    fd.append('userId', document.getElementById('targetUser').value);
    fd.append('title', document.getElementById('individualTitle').value);
    fd.append('message', document.getElementById('individualMessage').value);
    fd.append('type', document.getElementById('individualType').value);
    
    try {
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            document.getElementById('individualForm').reset();
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Settings Functions
async function loadSettings() {
    try {
        const res = await fetch('../api/admin-advanced.php?action=get_settings');
        const data = await res.json();
        
        if (data.ok) {
            const settings = data.settings;
            
            document.getElementById('siteName').value = settings.siteName;
            document.getElementById('siteDescription').value = settings.siteDescription;
            document.getElementById('maxUploadSize').value = settings.maxUploadSize;
            document.getElementById('allowedExtensions').value = settings.allowedExtensions;
            document.getElementById('pointsPerUpload').value = settings.pointsPerUpload;
            document.getElementById('pointsPerDownload').value = settings.pointsPerDownload;
            document.getElementById('registrationEnabled').checked = settings.registrationEnabled;
            document.getElementById('requireApproval').checked = settings.requireApproval;
            
            document.getElementById('settingsForm').addEventListener('submit', handleSettings);
        }
    } catch (err) {
        console.error('Error loading settings:', err);
    }
}

async function handleSettings(e) {
    e.preventDefault();
    
    const fd = new FormData();
    fd.append('action', 'save_settings');
    fd.append('siteName', document.getElementById('siteName').value);
    fd.append('siteDescription', document.getElementById('siteDescription').value);
    fd.append('maxUploadSize', document.getElementById('maxUploadSize').value);
    fd.append('allowedExtensions', document.getElementById('allowedExtensions').value);
    fd.append('pointsPerUpload', document.getElementById('pointsPerUpload').value);
    fd.append('pointsPerDownload', document.getElementById('pointsPerDownload').value);
    fd.append('registrationEnabled', document.getElementById('registrationEnabled').checked);
    fd.append('requireApproval', document.getElementById('requireApproval').checked);
    
    try {
        const res = await fetch('../api/admin-advanced.php', {method: 'POST', body: fd});
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Utility Functions
function showNotification(title, message, type = 'info') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500', 
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };
    
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl z-50 transform translate-x-full transition-transform duration-300`;
    notification.innerHTML = `
        <div class="flex items-center">
            <div class="mr-3">
                <div class="font-bold">${title}</div>
                <div class="text-sm opacity-90">${message}</div>
            </div>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => notification.classList.remove('translate-x-full'), 100);
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => notification.remove(), 300);
    }, 4000);
}

function escapeHtml(str) {
    return (str||'').toString().replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[s]));
}

// Search Users
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('userSearch').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        const filtered = allUsers.filter(user => 
            user.name.toLowerCase().includes(query) || 
            user.email.toLowerCase().includes(query)
        );
        displayUsers(filtered);
    });
    
    loadDashboard();
});

// expose CSRF token for admin JS actions
<?php $csrfToken = csrf_get_token(); ?>
// inject token into the running script context
window.csrfToken = '<?= $csrfToken ?>';

// Enhanced Reports Management System
let currentReports = [];
let selectedReports = [];
let reportFilters = {
    status: 'all',
    type: 'all',
    search: '',
    sort: 'newest'
};

// Load Reports with enhanced filtering
async function loadReports() {
    try {
        showLoadingState();
        
        const params = new URLSearchParams({
            action: 'get',
            status: reportFilters.status,
            type: reportFilters.type,
            search: reportFilters.search,
            sort: reportFilters.sort
        });
        
        const res = await fetch(`../api/reports.php?${params}`);
        const data = await res.json();
        
        if (data.ok) {
            currentReports = data.reports || [];
            updateReportStats(data.counts || {});
            renderReports(currentReports);
            renderPagination(data.pagination || {});
        } else {
            showNotification('خطا', data.error || 'خطا در بارگذاری گزارش‌ها', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    } finally {
        hideLoadingState();
    }
}

function showLoadingState() {
    document.getElementById('reportsContainer').innerHTML = `
        <div class="flex items-center justify-center py-12">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
            <span class="mr-3 text-gray-600">در حال بارگذاری...</span>
        </div>
    `;
}

function hideLoadingState() {
    // Loading state will be replaced by actual content
}

function updateReportStats(counts) {
    // Animate counters
    animateCounterElement('pendingReportsCount', counts.pending || 0);
    animateCounterElement('resolvedReportsCount', counts.resolved || 0);
    animateCounterElement('dismissedReportsCount', counts.dismissed || 0);
    animateCounterElement('totalReportsCount', counts.total || 0);
}

function animateCounterElement(elementId, targetValue) {
    const element = document.getElementById(elementId);
    if (!element) return;
    
    const startValue = 0;
    const duration = 1500;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
        
        element.textContent = currentValue.toLocaleString('fa-IR');
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        }
    }
    
    requestAnimationFrame(updateCounter);
}

function renderReports(reports) {
    const container = document.getElementById('reportsContainer');
    
    if (!reports || reports.length === 0) {
        container.innerHTML = `
            <div class="text-center py-16">
                <svg class="mx-auto h-16 w-16 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <h3 class="text-lg font-medium text-gray-900 mb-2">گزارشی یافت نشد</h3>
                <p class="text-gray-500">هیچ گزارشی با فیلترهای انتخاب شده یافت نشد.</p>
            </div>
        `;
        return;
    }

    const html = reports.map(report => createReportCard(report)).join('');
    container.innerHTML = html;
}

function createReportCard(report) {
    const statusColors = {
        'pending': 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'resolved': 'bg-green-100 text-green-800 border-green-200',
        'dismissed': 'bg-gray-100 text-gray-800 border-gray-200'
    };
    
    const reasonLabels = {
        'spam': 'اسپم',
        'inappropriate': 'نامناسب',
        'copyright': 'نقض کپی رایت',
        'malware': 'بدافزار',
        'other': 'سایر'
    };
    
    const priorityLevel = getPriorityLevel(report);
    const priorityColor = priorityLevel === 'high' ? 'text-red-600' : priorityLevel === 'medium' ? 'text-yellow-600' : 'text-green-600';
    
    return `
        <div class="bg-gradient-to-r from-white to-gray-50 rounded-3xl shadow-lg border border-gray-100 p-6 hover:shadow-xl transition-all duration-300 transform hover:scale-[1.02]">
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <input type="checkbox" class="report-checkbox w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                           data-report-id="${report.id}" onchange="toggleReportSelection(${report.id})">
            <div class="flex-1">
                        <div class="flex items-center space-x-3 space-x-reverse mb-2">
                            <h4 class="text-xl font-bold text-gray-900">${escapeHtml(report.projectTitle || 'بدون عنوان')}</h4>
                            <span class="px-3 py-1 rounded-full text-xs font-medium border ${statusColors[report.status] || statusColors.pending}">
                                ${getStatusLabel(report.status)}
                            </span>
                            ${priorityLevel === 'high' ? '<span class="text-red-500 text-lg">🔥</span>' : ''}
                    </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600 mb-3">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                <span>گزارش‌کننده: <strong>${escapeHtml(report.userInfo?.username || 'ناشناس')}</strong></span>
                </div>
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>دلیل: <strong>${reasonLabels[report.reason] || report.reason}</strong></span>
            </div>
            </div>
                        
                        ${report.description ? `
                            <div class="bg-gray-50 rounded-xl p-4 mb-4">
                                <p class="text-gray-700 leading-relaxed">${escapeHtml(report.description)}</p>
        </div>
                        ` : ''}
                        
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>تاریخ: ${new Date(report.createdAt).toLocaleDateString('fa-IR', { 
                                year: 'numeric', month: 'long', day: 'numeric', 
                                hour: '2-digit', minute: '2-digit' 
                            })}</span>
                            <span class="${priorityColor} font-medium">اولویت ${getPriorityLabel(priorityLevel)}</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex flex-wrap items-center gap-2">
                <button onclick="viewReportDetails(${report.id})" 
                        class="px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm font-medium transition-colors flex items-center space-x-2 space-x-reverse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                    </svg>
                    <span>مشاهده جزئیات</span>
                </button>
                
                <button onclick="previewProject(${report.projectId})" 
                        class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-xl text-sm font-medium transition-colors flex items-center space-x-2 space-x-reverse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    <span>پیش‌نمایش</span>
                </button>
                
                <button onclick="sendEmailToReporter(${report.id})" 
                        class="px-4 py-2 bg-green-500 hover:bg-green-600 text-white rounded-xl text-sm font-medium transition-colors flex items-center space-x-2 space-x-reverse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    <span>ایمیل</span>
                </button>
                
                ${report.status === 'pending' ? `
                    <button onclick="resolveReport(${report.id}, 'resolve')" 
                            class="px-4 py-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl text-sm font-medium transition-colors">
                        حل شد
                    </button>
                    <button onclick="resolveReport(${report.id}, 'dismiss')" 
                            class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition-colors">
                        رد کردن
                    </button>
                ` : ''}
                
                <button onclick="deleteProjectFromReport(${report.projectId})" 
                        class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium transition-colors flex items-center space-x-1 space-x-reverse">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    <span>حذف</span>
                </button>
                
                <button onclick="openEditReport(${report.id}, ${JSON.stringify(''+(report.description||''))}, ${JSON.stringify(''+(report.adminNote||''))})" 
                        class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white rounded-xl text-sm font-medium transition-colors">
                    ویرایش گزارش
                </button>
            </div>
        </div>
    `;
}

function getPriorityLevel(report) {
    // Determine priority based on reason and status
    if (report.reason === 'malware' || report.reason === 'copyright') return 'high';
    if (report.reason === 'inappropriate' || report.reason === 'spam') return 'medium';
    return 'low';
}

function getPriorityLabel(level) {
    const labels = {
        'high': 'بالا',
        'medium': 'متوسط',
        'low': 'پایین'
    };
    return labels[level] || 'پایین';
}

function getStatusLabel(status) {
    const labels = {
        'pending': 'در انتظار',
        'resolved': 'حل شده',
        'dismissed': 'رد شده'
    };
    return labels[status] || status;
}

// Selection Management
function toggleReportSelection(reportId) {
    const index = selectedReports.indexOf(reportId);
    if (index > -1) {
        selectedReports.splice(index, 1);
    } else {
        selectedReports.push(reportId);
    }
    updateBulkActionsBar();
}

function updateBulkActionsBar() {
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (selectedReports.length > 0) {
        bulkBar.classList.remove('hidden');
        selectedCount.textContent = `${selectedReports.length} گزارش انتخاب شده`;
    } else {
        bulkBar.classList.add('hidden');
    }
}

function clearSelection() {
    selectedReports = [];
    document.querySelectorAll('.report-checkbox').forEach(cb => cb.checked = false);
    updateBulkActionsBar();
}

async function previewProject(projectId) {
    try {
        const res = await fetch('../api/reports.php?action=preview&projectId=' + encodeURIComponent(projectId));
        const data = await res.json();
        if (!data.ok) return showNotification('خطا', data.error || 'خطا در پیش‌نمایش', 'error');

        const p = data.project || {};
        const prev = data.preview || {};
        let content = `<h3 class="text-lg font-bold mb-3">${escapeHtml(p.title || '')}</h3>`;
        content += `<div class="text-sm text-gray-600 mb-2">${escapeHtml(p.description || '')}</div>`;

        if (prev.type === 'text') {
            content += `<pre class="whitespace-pre-wrap bg-gray-100 p-3 rounded-lg text-xs overflow-auto" style="max-height:300px">${escapeHtml(prev.content || '')}</pre>`;
        } else if (prev.type === 'archive') {
            content += `<div class="text-sm">فهرست فایل‌ها در آرشیو:`;
            content += `<ul class="text-sm mt-2">` + (prev.contents || []).map(i=>`<li>${escapeHtml(i.name)} (${i.size} بایت)</li>`).join('') + `</ul></div>`;
        } else if (prev.type === 'binary') {
            content += `<div class="text-sm text-gray-700">${escapeHtml(prev.message || '')}</div>`;
        } else {
            content += `<div class="text-sm text-gray-500">${escapeHtml(prev.message || 'پیش‌نمایش موجود نیست')}</div>`;
        }

        // Add sender info if available
        if (p.authorProfile) {
            const ap = p.authorProfile;
            let senderHtml = `<div class="mt-4 p-3 bg-gray-50 rounded-md">`;
            senderHtml += `<div class="font-medium">اطلاعات فرستنده</div>`;
            senderHtml += `<div class="text-sm text-gray-700 mt-2">نام: ${escapeHtml(ap.username || '')}</div>`;
            if (ap.email) senderHtml += `<div class="text-sm text-gray-700">ایمیل: ${escapeHtml(ap.email)}</div>`;
            if (ap.telegram) senderHtml += `<div class="text-sm mt-1">تلگرام: <a href="${escapeHtml(ap.telegram)}" target="_blank" class="text-blue-600 hover:underline">${escapeHtml(ap.telegram.replace(/^https?:\/\//i,''))}</a></div>`;
            senderHtml += `</div>`;
            content += senderHtml;
        }

        document.getElementById('userModalTitle').textContent = 'پیش‌نمایش پروژه';
        document.getElementById('userModalContent').innerHTML = content + `<div class="mt-4"><button onclick="closeUserModal()" class="w-full bg-gray-500 text-white py-2 rounded-lg">بستن</button></div>`;
        document.getElementById('userModal').classList.remove('hidden');

    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function openEditProjectModal(projectId) {
    try {
        // Fetch project details from projects JSON via preview call
        const res = await fetch('../api/reports.php?action=preview&projectId=' + encodeURIComponent(projectId));
        const data = await res.json();
        if (!data.ok) return showNotification('خطا', data.error || 'خطا', 'error');

        const p = data.project || {};
        const content = `
            <div class="space-y-3">
                <label class="text-sm font-medium">عنوان</label>
                <input id="editTitle" class="w-full px-3 py-2 border rounded-lg" value="${escapeHtml(p.title||'')}" />
                <label class="text-sm font-medium">توضیحات</label>
                <textarea id="editDescription" class="w-full px-3 py-2 border rounded-lg" rows="4">${escapeHtml(p.description||'')}</textarea>
                <label class="text-sm font-medium">تگ‌ها (با کاما جدا کنید)</label>
                <input id="editTags" class="w-full px-3 py-2 border rounded-lg" value="${escapeHtml((p.tags||[]).join && p.tags.join(',')||'')}" />
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="submitProjectEdit(${projectId})" class="flex-1 bg-green-500 text-white py-2 rounded-lg">ذخیره</button>
                    <button onclick="closeUserModal()" class="flex-1 bg-gray-500 text-white py-2 rounded-lg">انصراف</button>
                </div>
            </div>
        `;

        document.getElementById('userModalTitle').textContent = 'ویرایش پروژه';
        document.getElementById('userModalContent').innerHTML = content;
        document.getElementById('userModal').classList.remove('hidden');

    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function submitProjectEdit(projectId) {
    try {
        const fd = new FormData();
        fd.append('action', 'edit_project');
        fd.append('projectId', projectId);
        fd.append('title', document.getElementById('editTitle').value);
        fd.append('description', document.getElementById('editDescription').value);
        fd.append('tags', document.getElementById('editTags').value);
        // CSRF token appended if available on the page
        const token = window.csrfToken || '';
        if (token) fd.append('csrf_token', token);

        const res = await fetch('../api/reports.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
            showNotification('موفقیت', data.message || 'ذخیره شد', 'success');
            closeUserModal();
            loadReports();
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function deleteProjectFromReport(projectId) {
    if (!confirm('آیا از حذف این پروژه و فایل مربوطه اطمینان دارید؟ این عمل قابل بازگشت نیست.')) return;
    try {
        const note = prompt('ذکر علت حذف (اختیاری):', 'حذف توسط مدیر');
        const fd = new FormData();
        fd.append('action', 'delete_project');
        fd.append('projectId', projectId);
        fd.append('adminNote', note || 'حذف توسط مدیر');
        const token = window.csrfToken || '';
        if (token) fd.append('csrf_token', token);

        const res = await fetch('../api/reports.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
            showNotification('موفقیت', data.message || 'حذف شد', 'success');
            loadReports();
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Ads Management Functions
async function loadAds() {
    try {
        const res = await fetch('../api/ads.php?action=get');
        const data = await res.json();
        if (data.ok) {
            const ads = data.ads || [];
            renderAds(ads);
            updateAdsStats(ads);
        } else {
            showNotification('خطا', data.error || 'خطا در بارگذاری تبلیغات', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

function updateAdsStats(ads) {
    const totalAds = ads.length;
    const totalViews = ads.reduce((sum, ad) => sum + (ad.views || 0), 0);
    const totalClicks = ads.reduce((sum, ad) => sum + (ad.clicks || 0), 0);
    const avgClickRate = totalViews > 0 ? ((totalClicks / totalViews) * 100).toFixed(1) : 0;
    
    // Update stats display
    document.getElementById('totalAdsCount').textContent = totalAds.toLocaleString('fa-IR');
    document.getElementById('totalViewsCount').textContent = totalViews.toLocaleString('fa-IR');
    document.getElementById('totalClicksCount').textContent = totalClicks.toLocaleString('fa-IR');
    document.getElementById('avgClickRateCount').textContent = avgClickRate + '%';
    
    // Animate counters
    animateCounter('totalAdsCount', totalAds);
    animateCounter('totalViewsCount', totalViews);
    animateCounter('totalClicksCount', totalClicks);
}

function animateCounter(elementId, targetValue) {
    const element = document.getElementById(elementId);
    const startValue = 0;
    const duration = 1000;
    const startTime = performance.now();
    
    function updateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const currentValue = Math.floor(startValue + (targetValue - startValue) * progress);
        
        element.textContent = currentValue.toLocaleString('fa-IR');
        
        if (progress < 1) {
            requestAnimationFrame(updateCounter);
        } else {
            element.textContent = targetValue.toLocaleString('fa-IR');
        }
    }
    
    requestAnimationFrame(updateCounter);
}

function renderAds(ads) {
    if (!ads || ads.length === 0) {
        document.getElementById('adsList').innerHTML = '<div class="p-6 bg-gray-50 rounded-lg text-center text-gray-600">تبلیغی وجود ندارد</div>';
        return;
    }

    const html = ads.map(ad => {
        const clickRate = ad.views > 0 ? ((ad.clicks / ad.views) * 100).toFixed(1) : 0;
        const statusColor = ad.active ? 'bg-green-500' : 'bg-gray-400';
        const typeColor = {
            'header_banner': 'bg-purple-500',
            'sidebar': 'bg-blue-500', 
            'inline': 'bg-green-500',
            'banner': 'bg-orange-500'
        }[ad.type] || 'bg-gray-500';
        
        return `
        <div class="bg-white rounded-3xl shadow-xl border border-gray-100 overflow-hidden hover:shadow-2xl transition-all duration-500 transform hover:scale-[1.02]">
            <!-- Header with image preview -->
            <div class="relative">
                ${ad.image ? `
                    <div class="h-32 bg-gradient-to-r from-gray-100 to-gray-200 overflow-hidden">
                        <img src="${escapeHtml(ad.image)}" alt="${escapeHtml(ad.title)}" class="w-full h-full object-cover opacity-80 hover:opacity-100 transition-opacity">
                    </div>
                    <div class="absolute top-3 right-3 flex space-x-2 space-x-reverse">
                        <span class="px-3 py-1 ${statusColor} text-white rounded-full text-xs font-bold shadow-lg">
                            ${ad.active ? 'فعال' : 'غیرفعال'}
                        </span>
                        <span class="px-3 py-1 ${typeColor} text-white rounded-full text-xs font-bold shadow-lg">
                            ${getAdTypeLabel(ad.type)}
                        </span>
                    </div>
                ` : `
                    <div class="h-20 bg-gradient-to-r from-purple-500 via-blue-500 to-indigo-500 relative">
                        <div class="absolute inset-0 bg-black/20"></div>
                        <div class="absolute top-3 right-3 flex space-x-2 space-x-reverse">
                            <span class="px-3 py-1 ${statusColor} text-white rounded-full text-xs font-bold shadow-lg">
                                ${ad.active ? 'فعال' : 'غیرفعال'}
                            </span>
                            <span class="px-3 py-1 ${typeColor} text-white rounded-full text-xs font-bold shadow-lg">
                                ${getAdTypeLabel(ad.type)}
                            </span>
                    </div>
                </div>
                `}
            </div>
            
            <!-- Content -->
            <div class="p-6">
                <div class="mb-4">
                    <h4 class="text-xl font-bold text-gray-900 mb-2 leading-tight">${escapeHtml(ad.title)}</h4>
                    <p class="text-gray-600 text-sm leading-relaxed">${escapeHtml(ad.content || '').substring(0, 120)}${(ad.content || '').length > 120 ? '...' : ''}</p>
                </div>
                
                <!-- Enhanced Stats -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gradient-to-r from-blue-50 to-blue-100 p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold text-blue-600">${(ad.views || 0).toLocaleString('fa-IR')}</div>
                        <div class="text-xs text-blue-700 font-medium">بازدید</div>
                        <div class="w-full bg-blue-200 rounded-full h-1 mt-2">
                            <div class="bg-blue-500 h-1 rounded-full transition-all duration-500" style="width: ${Math.min((ad.views / 1000) * 100, 100)}%"></div>
                        </div>
                    </div>
                    
                    <div class="bg-gradient-to-r from-green-50 to-green-100 p-4 rounded-2xl text-center">
                        <div class="text-2xl font-bold text-green-600">${(ad.clicks || 0).toLocaleString('fa-IR')}</div>
                        <div class="text-xs text-green-700 font-medium">کلیک</div>
                        <div class="w-full bg-green-200 rounded-full h-1 mt-2">
                            <div class="bg-green-500 h-1 rounded-full transition-all duration-500" style="width: ${Math.min((ad.clicks / 100) * 100, 100)}%"></div>
                        </div>
                    </div>
                </div>
                
                <!-- Performance metrics -->
                <div class="grid grid-cols-3 gap-3 mb-6">
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <div class="text-lg font-bold ${clickRate > 5 ? 'text-green-600' : clickRate > 2 ? 'text-yellow-600' : 'text-red-600'}">${clickRate}%</div>
                        <div class="text-xs text-gray-600">نرخ کلیک</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <div class="text-lg font-bold text-purple-600">${ad.priority || 0}</div>
                        <div class="text-xs text-gray-600">اولویت</div>
                    </div>
                    <div class="text-center p-3 bg-gray-50 rounded-xl">
                        <div class="text-lg font-bold text-indigo-600">${(ad.locations || []).length}</div>
                        <div class="text-xs text-gray-600">موقعیت</div>
                    </div>
                </div>
                
                <!-- Action buttons -->
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="editAd(${ad.id})" class="flex-1 px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-xl text-sm font-medium transition-colors duration-300">
                        <i class="fas fa-edit ml-1"></i>ویرایش
                    </button>
                    <button onclick="toggleAd(${ad.id})" class="flex-1 px-4 py-2 ${ad.active ? 'bg-yellow-500 hover:bg-yellow-600' : 'bg-green-500 hover:bg-green-600'} text-white rounded-xl text-sm font-medium transition-colors duration-300">
                        <i class="fas fa-${ad.active ? 'pause' : 'play'} ml-1"></i>${ad.active ? 'توقف' : 'فعال'}
                    </button>
                    <button onclick="deleteAd(${ad.id})" class="px-4 py-2 bg-red-500 hover:bg-red-600 text-white rounded-xl text-sm font-medium transition-colors duration-300">
                        <i class="fas fa-trash ml-1"></i>حذف
                    </button>
                </div>
            </div>
        </div>
        `;
    }).join('');

    document.getElementById('adsList').innerHTML = html;
}

function getAdTypeLabel(type) {
    const types = {
        'banner': 'بنر عادی',
        'header_banner': 'بنر سربرگ',
        'sidebar': 'نوار کناری',
        'inline': 'درون محتوا',
        'popup': 'پاپ آپ'
    };
    return types[type] || type;
}

function showCreateAdModal() {
    document.getElementById('adModalTitle').textContent = 'تبلیغ جدید';
    document.getElementById('adForm').reset();
    document.getElementById('adId').value = '';
    document.getElementById('adModal').classList.remove('hidden');
}

function closeAdModal() {
    document.getElementById('adModal').classList.add('hidden');
}

async function editAd(adId) {
    try {
        const res = await fetch('../api/ads.php?action=get');
        const data = await res.json();
        if (data.ok) {
            const ad = data.ads.find(a => a.id === adId);
            if (ad) {
                document.getElementById('adModalTitle').textContent = 'ویرایش تبلیغ';
                document.getElementById('adId').value = ad.id;
                document.getElementById('adTitle').value = ad.title || '';
                document.getElementById('adContent').value = ad.content || '';
                document.getElementById('adType').value = ad.type || 'banner';
                document.getElementById('adPriority').value = ad.priority || 50;
                document.getElementById('adLink').value = ad.link || '';
                document.getElementById('adImage').value = ad.image || '';
                document.getElementById('adStartDate').value = ad.start_date || '';
                document.getElementById('adEndDate').value = ad.end_date || '';
                
                // Set locations
                document.querySelectorAll('input[name="locations"]').forEach(cb => {
                    cb.checked = (ad.locations || []).includes(cb.value);
                });
                
                document.getElementById('adModal').classList.remove('hidden');
            }
        }
    } catch (err) {
        showNotification('خطا', 'خطا در بارگذاری اطلاعات تبلیغ', 'error');
    }
}

async function toggleAd(adId) {
    try {
        const formData = new FormData();
        formData.append('id', adId);
        formData.append('csrf_token', window.csrfToken);

        const res = await fetch('../api/ads.php?action=toggle', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            loadAds();
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

async function deleteAd(adId) {
    if (!confirm('آیا از حذف این تبلیغ مطمئن هستید؟')) return;
    
    try {
        const formData = new FormData();
        formData.append('id', adId);
        formData.append('csrf_token', window.csrfToken);

        const res = await fetch('../api/ads.php?action=delete', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            loadAds();
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Ad Form Submit
document.getElementById('adForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const adId = document.getElementById('adId').value;
    const action = adId ? 'update' : 'create';
    
    const formData = new FormData();
    if (adId) formData.append('id', adId);
    formData.append('title', document.getElementById('adTitle').value);
    formData.append('content', document.getElementById('adContent').value);
    formData.append('type', document.getElementById('adType').value);
    formData.append('priority', document.getElementById('adPriority').value);
    formData.append('link', document.getElementById('adLink').value);
    formData.append('image', document.getElementById('adImage').value);
    formData.append('start_date', document.getElementById('adStartDate').value);
    formData.append('end_date', document.getElementById('adEndDate').value);
    formData.append('csrf_token', window.csrfToken);
    
    // Get selected locations
    const locations = [];
    document.querySelectorAll('input[name="locations"]:checked').forEach(cb => {
        locations.push(cb.value);
    });
    locations.forEach(loc => formData.append('locations[]', loc));
    
    try {
        const res = await fetch(`../api/ads.php?action=${action}`, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            closeAdModal();
            loadAds();
        } else {
            showNotification('خطا', data.error || 'عملیات ناموفق', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
});

// Email Integration with Resend API
async function sendEmailToReporter(reportId) {
    const report = currentReports.find(r => r.id === reportId);
    if (!report || !report.userInfo?.email) {
        showNotification('خطا', 'ایمیل کاربر موجود نیست', 'error');
        return;
    }
    
    // Pre-fill email form
    document.getElementById('emailTo').value = report.userInfo.email;
    document.getElementById('emailSubject').value = `بررسی گزارش شما - ${report.projectTitle}`;
    document.getElementById('emailTemplate').value = report.status === 'resolved' ? 'report_resolved' : 'custom';
    
    // Load template content
    await loadEmailTemplate(document.getElementById('emailTemplate').value, report);
    
    // Store current report ID for context
    document.getElementById('emailForm').setAttribute('data-report-id', reportId);
    
    document.getElementById('emailModal').classList.remove('hidden');
}

async function loadEmailTemplate(templateType, report) {
    const templates = {
        'report_resolved': {
            subject: `گزارش شما بررسی شد - ${report?.projectTitle || 'پروژه'}`,
            body: `
                <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
                    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #059669; font-size: 28px; margin: 0;">✅ گزارش شما بررسی شد</h1>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            سلام ${escapeHtml(report?.userInfo?.username || 'کاربر گرامی')},
                        </p>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            گزارش شما در خصوص پروژه "<strong>${escapeHtml(report?.projectTitle || '')}</strong>" بررسی و پیگیری شد.
                        </p>
                        
                        <div style="background: #dcfce7; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #059669;">
                            <p style="margin: 0; color: #059669; font-weight: bold;">✅ وضعیت: حل شده</p>
                            <p style="margin: 10px 0 0 0; color: #374151;">اقدامات لازم انجام شده و مشکل برطرف شده است.</p>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            از همکاری شما در بهبود کیفیت محتوای سایت متشکریم.
                        </p>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 14px;">
                                تیم پشتیبانی SourceBaan<br>
                                <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            `
        },
        'report_dismissed': {
            subject: `بررسی گزارش شما - ${report?.projectTitle || 'پروژه'}`,
            body: `
                <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
                    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #dc2626; font-size: 28px; margin: 0;">📋 بررسی گزارش شما</h1>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            سلام ${escapeHtml(report?.userInfo?.username || 'کاربر گرامی')},
                        </p>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            گزارش شما در خصوص پروژه "<strong>${escapeHtml(report?.projectTitle || '')}</strong>" بررسی شد.
                        </p>
                        
                        <div style="background: #fef3c7; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #f59e0b;">
                            <p style="margin: 0; color: #d97706; font-weight: bold;">ℹ️ وضعیت: رد شده</p>
                            <p style="margin: 10px 0 0 0; color: #374151;">پس از بررسی، محتوای گزارش شده در حال حاضر مشکلی ندارد.</p>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            در صورت وجود سوال یا نیاز به توضیح بیشتر، می‌توانید با تیم پشتیبانی تماس بگیرید.
                        </p>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 14px;">
                                تیم پشتیبانی SourceBaan<br>
                                <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            `
        },
        'project_updated': {
            subject: `پروژه شما به‌روز شد - ${report?.projectTitle || 'پروژه'}`,
            body: `
                <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
                    <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
                        <div style="text-align: center; margin-bottom: 30px;">
                            <h1 style="color: #3b82f6; font-size: 28px; margin: 0;">🔄 پروژه شما به‌روز شد</h1>
                        </div>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            سلام ${escapeHtml(report?.userInfo?.username || 'کاربر گرامی')},
                        </p>
                        
                        <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                            پروژه "<strong>${escapeHtml(report?.projectTitle || '')}</strong>" توسط تیم ما بهبود یافته است.
                        </p>
                        
                        <div style="background: #dbeafe; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #3b82f6;">
                            <p style="margin: 0; color: #1d4ed8; font-weight: bold;">🔄 وضعیت: به‌روز شد</p>
                            <p style="margin: 10px 0 0 0; color: #374151;">اطلاعات و محتوای پروژه بهبود یافته است.</p>
                        </div>
                        
                        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                            <p style="color: #6b7280; font-size: 14px;">
                                تیم پشتیبانی SourceBaan<br>
                                <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                            </p>
                        </div>
                    </div>
                </div>
            `
        }
    };
    
    const template = templates[templateType];
    if (template) {
        document.getElementById('emailSubject').value = template.subject;
        document.getElementById('emailBody').value = template.body;
    }
}

// Handle email form submission
async function handleEmailSubmit(e) {
    e.preventDefault();
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div> در حال ارسال...';
        
        const formData = new FormData();
        formData.append('action', 'send_email');
        formData.append('from', document.getElementById('emailFrom').value);
        formData.append('to', document.getElementById('emailTo').value);
        formData.append('subject', document.getElementById('emailSubject').value);
        formData.append('body', document.getElementById('emailBody').value);
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../api/admin-email.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification('موفقیت', 'ایمیل با موفقیت ارسال شد', 'success');
            closeEmailModal();
        } else {
            showNotification('خطا', data.error || 'خطا در ارسال ایمیل', 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function closeEmailModal() {
    document.getElementById('emailModal').classList.add('hidden');
    document.getElementById('emailForm').reset();
}

// Report Details Modal
async function viewReportDetails(reportId) {
    const report = currentReports.find(r => r.id === reportId);
    if (!report) return;
    
    const content = `
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-6">
                    <h4 class="font-bold text-blue-900 mb-4">اطلاعات گزارش</h4>
                    <div class="space-y-3">
                        <div><span class="font-medium text-blue-800">شناسه:</span> <span class="text-blue-600">#${report.id}</span></div>
                        <div><span class="font-medium text-blue-800">وضعیت:</span> <span class="text-blue-600">${getStatusLabel(report.status)}</span></div>
                        <div><span class="font-medium text-blue-800">دلیل:</span> <span class="text-blue-600">${report.reason}</span></div>
                        <div><span class="font-medium text-blue-800">تاریخ ایجاد:</span> <span class="text-blue-600">${new Date(report.createdAt).toLocaleDateString('fa-IR')}</span></div>
                    </div>
                </div>
                
                <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-2xl p-6">
                    <h4 class="font-bold text-green-900 mb-4">اطلاعات کاربر</h4>
                    <div class="space-y-3">
                        <div><span class="font-medium text-green-800">نام کاربری:</span> <span class="text-green-600">${escapeHtml(report.userInfo?.username || 'ناشناس')}</span></div>
                        <div><span class="font-medium text-green-800">ایمیل:</span> <span class="text-green-600">${escapeHtml(report.userInfo?.email || 'ندارد')}</span></div>
                    </div>
                </div>
            </div>
            
            <div class="bg-gray-50 rounded-2xl p-6">
                <h4 class="font-bold text-gray-900 mb-4">توضیحات گزارش</h4>
                <p class="text-gray-700 leading-relaxed">${escapeHtml(report.description || 'توضیحی ارائه نشده است.')}</p>
            </div>
            
            <div class="bg-purple-50 rounded-2xl p-6">
                <h4 class="font-bold text-purple-900 mb-4">اطلاعات پروژه</h4>
                <div class="space-y-2">
                    <div><span class="font-medium text-purple-800">عنوان:</span> <span class="text-purple-600">${escapeHtml(report.projectTitle)}</span></div>
                    <div><span class="font-medium text-purple-800">شناسه پروژه:</span> <span class="text-purple-600">#${report.projectId}</span></div>
                </div>
                <div class="mt-4 flex space-x-3 space-x-reverse">
                    <button onclick="previewProject(${report.projectId}); closeReportDetailsModal();" 
                            class="px-4 py-2 bg-purple-500 hover:bg-purple-600 text-white rounded-xl text-sm transition-colors">
                        پیش‌نمایش پروژه
                    </button>
                    <button onclick="openEditProjectModal(${report.projectId}); closeReportDetailsModal();" 
                            class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-xl text-sm transition-colors">
                        ویرایش پروژه
                    </button>
                </div>
            </div>
            
            ${report.adminNote ? `
                <div class="bg-orange-50 rounded-2xl p-6">
                    <h4 class="font-bold text-orange-900 mb-4">یادداشت مدیر</h4>
                    <p class="text-orange-700 leading-relaxed">${escapeHtml(report.adminNote)}</p>
                </div>
            ` : ''}
            
            <div class="flex space-x-3 space-x-reverse">
                ${report.status === 'pending' ? `
                    <button onclick="resolveReport(${report.id}, 'resolve'); closeReportDetailsModal();" 
                            class="px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-xl font-medium transition-colors">
                        ✅ حل کردن گزارش
                    </button>
                    <button onclick="resolveReport(${report.id}, 'dismiss'); closeReportDetailsModal();" 
                            class="px-6 py-3 bg-gray-500 hover:bg-gray-600 text-white rounded-xl font-medium transition-colors">
                        ❌ رد کردن گزارش
                    </button>
                ` : ''}
                <button onclick="sendEmailToReporter(${report.id}); closeReportDetailsModal();" 
                        class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-xl font-medium transition-colors">
                    ✉️ ارسال ایمیل
                </button>
                <button onclick="closeReportDetailsModal()" 
                        class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl font-medium transition-colors">
                    بستن
                </button>
            </div>
        </div>
    `;
    
    document.getElementById('reportDetailsContent').innerHTML = content;
    document.getElementById('reportDetailsModal').classList.remove('hidden');
}

function closeReportDetailsModal() {
    document.getElementById('reportDetailsModal').classList.add('hidden');
}

// Report Actions
async function resolveReport(reportId, action) {
    const note = prompt('یادداشت (اختیاری):');
    
    try {
        const formData = new FormData();
        formData.append('action', 'resolve');
        formData.append('reportId', reportId);
        formData.append('reportAction', action);
        formData.append('adminNote', note || '');
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../api/reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            loadReports(); // Refresh reports
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Filtering and Search
function applyFilters() {
    reportFilters.status = document.getElementById('reportStatusFilter').value;
    reportFilters.type = document.getElementById('reportTypeFilter').value;
    reportFilters.search = document.getElementById('reportSearchInput').value;
    reportFilters.sort = document.getElementById('reportSortOrder').value;
    
    loadReports();
}

// Bulk Actions
async function bulkAction(action) {
    if (selectedReports.length === 0) {
        showNotification('خطا', 'لطفا حداقل یک گزارش را انتخاب کنید', 'error');
        return;
    }
    
    if (!confirm(`آیا از اعمال این عملیات روی ${selectedReports.length} گزارش اطمینان دارید؟`)) {
        return;
    }
    
    const note = action !== 'email' ? prompt('یادداشت (اختیاری):') : null;
    
    try {
        const formData = new FormData();
        formData.append('action', 'bulk_action');
        formData.append('bulk_action', action);
        formData.append('report_ids', JSON.stringify(selectedReports));
        formData.append('admin_note', note || '');
        formData.append('csrf_token', window.csrfToken);
        
        const response = await fetch('../api/reports.php', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            showNotification('موفقیت', data.message, 'success');
            clearSelection();
            loadReports();
        } else {
            showNotification('خطا', data.error, 'error');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در اتصال به سرور', 'error');
    }
}

// Edit report (description/adminNote)
function openEditReport(reportId, currentDescription, currentAdminNote) {
    const newDesc = prompt('ویرایش توضیحات گزارش:', currentDescription || '');
    if (newDesc === null) return;
    const newNote = prompt('ویرایش یادداشت مدیر:', currentAdminNote || '');
    if (newNote === null) return;
    saveEditedReport(reportId, newDesc, newNote);
}

async function saveEditedReport(reportId, description, adminNote) {
    try {
        const fd = new FormData();
        fd.append('action', 'edit_report');
        fd.append('reportId', reportId);
        fd.append('description', description);
        fd.append('adminNote', adminNote);
        fd.append('csrf_token', window.csrfToken);
        const res = await fetch('../api/reports.php', { method: 'POST', body: fd });
        const data = await res.json();
        if (data.ok) {
            showNotification('موفقیت', 'گزارش به‌روز شد', 'success');
            loadReports();
        } else {
            showNotification('خطا', data.error || 'ذخیره تغییرات گزارش ناموفق بود', 'error');
        }
    } catch (_) {
        showNotification('خطا', 'ارتباط با سرور ناموفق بود', 'error');
    }
}

// Event Listeners Setup
document.addEventListener('DOMContentLoaded', function() {
    // Email form submission
    const emailForm = document.getElementById('emailForm');
    if (emailForm) {
        emailForm.addEventListener('submit', handleEmailSubmit);
    }
    
    // Template change handler
    const templateSelect = document.getElementById('emailTemplate');
    if (templateSelect) {
        templateSelect.addEventListener('change', async function() {
            const reportId = emailForm.getAttribute('data-report-id');
            const report = currentReports.find(r => r.id == reportId);
            await loadEmailTemplate(this.value, report);
        });
    }
    
    // Filter event handlers
    const statusFilter = document.getElementById('reportStatusFilter');
    const typeFilter = document.getElementById('reportTypeFilter');
    const searchInput = document.getElementById('reportSearchInput');
    const sortSelect = document.getElementById('reportSortOrder');
    
    if (statusFilter) statusFilter.addEventListener('change', applyFilters);
    if (typeFilter) typeFilter.addEventListener('change', applyFilters);
    if (sortSelect) sortSelect.addEventListener('change', applyFilters);
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(applyFilters, 500);
        });
    }
});

// Bulk Email
function sendBulkEmail() {
    document.getElementById('emailModal').classList.remove('hidden');
    document.getElementById('emailSubject').value = 'اطلاعیه مهم';
    document.getElementById('emailTemplate').value = 'custom';
    document.getElementById('emailBody').value = '';
}

// Export functionality
async function exportReports() {
    try {
        showNotification('اطلاع', 'در حال تولید فایل اکسل...', 'info');
        
        const response = await fetch('../api/reports.php?action=export', {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${window.csrfToken}`
            }
        });
        
        if (response.ok) {
            const blob = await response.blob();
            const url = window.URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `reports_${new Date().toISOString().split('T')[0]}.xlsx`;
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            window.URL.revokeObjectURL(url);
            
            showNotification('موفقیت', 'فایل اکسل آماده دانلود است', 'success');
        } else {
            throw new Error('Export failed');
        }
    } catch (err) {
        showNotification('خطا', 'خطا در تولید فایل اکسل', 'error');
    }
}

// ======================
// VERIFICATION MANAGEMENT
// ======================

let allVerificationRequests = [];
let filteredVerificationRequests = [];

async function loadVerificationRequests() {
    try {
        const response = await fetch('../api/verification.php?action=list_requests');
        const data = await response.json();
        
        if (data.ok) {
            allVerificationRequests = data.requests;
            filteredVerificationRequests = [...allVerificationRequests];
            displayVerificationRequests();
            updateVerificationStats();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        document.getElementById('verificationRequests').innerHTML = 
            '<div class="text-center py-8 text-red-500">خطا در بارگذاری: ' + error.message + '</div>';
    }
}

function displayVerificationRequests() {
    const container = document.getElementById('verificationRequests');
    
    if (filteredVerificationRequests.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">درخواستی یافت نشد</div>';
        return;
    }
    
    container.innerHTML = filteredVerificationRequests.map(req => `
        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h4 class="font-semibold text-lg text-gray-900">${req.name}</h4>
                        <span class="px-3 py-1 text-sm font-medium rounded-full ${getVerificationStatusColor(req.status)}">${getVerificationStatusText(req.status)}</span>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <span class="text-sm text-gray-500">کاربر:</span>
                            <p class="font-medium">${req.user?.name || 'نامشخص'} (${req.user?.email || 'ایمیل نامشخص'})</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">تلگرام:</span>
                            <p class="font-medium">${req.telegram_id}</p>
                        </div>
                        ${req.phone ? `
                        <div>
                            <span class="text-sm text-gray-500">تلفن:</span>
                            <p class="font-medium">${req.phone}</p>
                        </div>
                        ` : ''}
                        <div>
                            <span class="text-sm text-gray-500">تاریخ درخواست:</span>
                            <p class="font-medium">${new Date(req.created_at).toLocaleDateString('fa-IR')}</p>
                        </div>
                    </div>
                    
                    <!-- User Activity Stats -->
                    ${req.user_activity ? `
                    <div class="bg-gray-50 rounded-xl p-4 mb-4">
                        <h5 class="font-semibold text-gray-900 mb-3">فعالیت کاربر</h5>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-xl font-bold text-blue-600">${req.user_activity.total_projects}</div>
                                <div class="text-xs text-gray-500">کل پروژه‌ها</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-green-600">${req.user_activity.approved_projects}</div>
                                <div class="text-xs text-gray-500">تایید شده</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-red-600">${req.user_activity.rejected_projects}</div>
                                <div class="text-xs text-gray-500">رد شده</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl font-bold text-purple-600">${req.user_activity.total_points}</div>
                                <div class="text-xs text-gray-500">امتیاز</div>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div class="text-center">
                                <div class="text-lg font-bold text-yellow-600">${req.user_activity.total_stars}</div>
                                <div class="text-xs text-gray-500">کل ستاره‌ها</div>
                            </div>
                            <div class="text-center">
                                <div class="text-lg font-bold text-indigo-600">${req.user_activity.total_downloads}</div>
                                <div class="text-xs text-gray-500">کل دانلودها</div>
                            </div>
                        </div>
                        
                        ${req.user_activity.projects_list && req.user_activity.projects_list.length > 0 ? `
                        <div>
                            <h6 class="font-medium text-gray-800 mb-2">آخرین پروژه‌ها:</h6>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                ${req.user_activity.projects_list.slice(0, 5).map(project => `
                                    <div class="flex items-center justify-between p-2 bg-white rounded border">
                                        <div class="flex-1">
                                            <span class="text-sm font-medium">${project.title}</span>
                                            <span class="text-xs text-gray-500 mr-2">(${project.language})</span>
                                        </div>
                                        <div class="flex items-center space-x-2 space-x-reverse">
                                            <span class="px-2 py-1 text-xs rounded ${getProjectStatusBadge(project.status)}">${getProjectStatusText(project.status)}</span>
                                            <span class="text-xs text-gray-500">⭐ ${project.stars}</span>
                                            <span class="text-xs text-gray-500">📥 ${project.downloads}</span>
                                        </div>
                                    </div>
                                `).join('')}
                                ${req.user_activity.projects_list.length > 5 ? `<p class="text-xs text-gray-500 text-center">... و ${req.user_activity.projects_list.length - 5} پروژه دیگر</p>` : ''}
                            </div>
                        </div>
                        ` : '<p class="text-sm text-gray-500">هیچ پروژه‌ای ندارد</p>'}
                    </div>
                    ` : ''}
                    
                    <div class="mb-4">
                        <span class="text-sm text-gray-500">دلیل درخواست:</span>
                        <p class="mt-1 text-gray-700">${req.reason}</p>
                    </div>
                    
                    ${req.admin_notes ? `
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                        <span class="text-sm text-gray-500">یادداشت ادمین:</span>
                        <p class="mt-1 text-gray-700">${req.admin_notes}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
            
            ${req.status === 'pending' ? `
            <div class="flex space-x-3 space-x-reverse mt-4 pt-4 border-t border-gray-200">
                <button onclick="processVerificationRequest(${req.id}, 'approve')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i class="fas fa-check ml-1"></i>تایید
                </button>
                <button onclick="processVerificationRequest(${req.id}, 'reject')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                    <i class="fas fa-times ml-1"></i>رد
                </button>
            </div>
            ` : ''}
        </div>
    `).join('');
}

function filterVerifications(status) {
    // Update filter buttons
    document.querySelectorAll('.verification-filter-btn').forEach(btn => {
        btn.classList.remove('filter-active');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    event.target.classList.add('filter-active');
    event.target.classList.remove('bg-gray-100', 'text-gray-700');
    
    // Filter requests
    if (status === 'all') {
        filteredVerificationRequests = [...allVerificationRequests];
    } else {
        filteredVerificationRequests = allVerificationRequests.filter(req => req.status === status);
    }
    
    displayVerificationRequests();
}

async function processVerificationRequest(requestId, decision) {
    const adminNotes = prompt(decision === 'approve' ? 'یادداشت برای تایید (اختیاری):' : 'دلیل رد درخواست:');
    
    if (decision === 'reject' && !adminNotes) {
        alert('لطفاً دلیل رد درخواست را وارد کنید');
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('request_id', requestId);
        formData.append('decision', decision);
        formData.append('admin_notes', adminNotes || '');
        
        const response = await fetch('../api/verification.php?action=process_request', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert(data.message);
            loadVerificationRequests();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
}

function updateVerificationStats() {
    const pending = allVerificationRequests.filter(req => req.status === 'pending').length;
    const approved = allVerificationRequests.filter(req => req.status === 'approved').length;
    const rejected = allVerificationRequests.filter(req => req.status === 'rejected').length;
    
    document.getElementById('pendingVerifications').textContent = pending;
    document.getElementById('approvedVerifications').textContent = approved;
    document.getElementById('rejectedVerifications').textContent = rejected;
}

function getVerificationStatusColor(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
}

function getVerificationStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید شده';
        case 'rejected': return 'رد شده';
        default: return 'در انتظار بررسی';
    }
}

// ======================
// SHOP MANAGEMENT
// ======================

let allShopProducts = [];
let filteredShopProducts = [];

async function loadShopProducts() {
    try {
        const response = await fetch('../api/shop.php?action=admin_list_products');
        const data = await response.json();
        
        if (data.ok) {
            allShopProducts = data.products;
            filteredShopProducts = [...allShopProducts];
            displayShopProducts();
            updateShopStats();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        document.getElementById('shopProducts').innerHTML = 
            '<div class="text-center py-8 text-red-500">خطا در بارگذاری: ' + error.message + '</div>';
    }
}

function displayShopProducts() {
    const container = document.getElementById('shopProducts');
    
    if (filteredShopProducts.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">محصولی یافت نشد</div>';
        return;
    }
    
    container.innerHTML = filteredShopProducts.map(product => `
        <div class="border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h4 class="font-semibold text-lg text-gray-900">${product.title}</h4>
                        <span class="px-3 py-1 text-sm font-medium rounded-full ${getProductStatusColor(product.status)}">${getProductStatusText(product.status)}</span>
                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">${getCategoryName(product.category)}</span>
                        ${product.banner_path ? `<span class="px-2 py-1 text-xs rounded-full ${getBannerStatusColor(product.banner_status || 'pending')}">بنر: ${getBannerStatusText(product.banner_status || 'pending')}</span>` : ''}
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <span class="text-sm text-gray-500">فروشنده:</span>
                            <p class="font-medium flex items-center gap-1">
                                ${product.seller?.name || 'نامشخص'}
                                ${product.seller?.verified ? '<i class="fas fa-check-circle text-blue-500"></i>' : ''}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">قیمت:</span>
                            <p class="font-medium text-purple-600">${formatPrice(product.price)} تومان</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">بازدید:</span>
                            <p class="font-medium">${product.views || 0}</p>
                        </div>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-sm text-gray-500">توضیحات:</span>
                        <p class="mt-1 text-gray-700">${product.description.substring(0, 200)}${product.description.length > 200 ? '...' : ''}</p>
                    </div>
                    
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span>تاریخ ایجاد: ${new Date(product.created_at).toLocaleDateString('fa-IR')}</span>
                        ${product.demo_url ? `<a href="${product.demo_url}" target="_blank" class="text-blue-600 hover:text-blue-800"><i class="fas fa-external-link-alt ml-1"></i>دمو</a>` : ''}
                        <span>تلگرام: ${product.telegram_id}</span>
                    </div>
                </div>
                
                ${product.banner_path ? `<img src="../${product.banner_path}" class="w-24 h-24 object-cover rounded-lg mr-4">` : ''}
            </div>
            
            <div class="flex space-x-3 space-x-reverse mt-4 pt-4 border-t border-gray-200">
                ${product.status === 'pending' ? `
                    <button onclick="approveProduct(${product.id}, 'approve')" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        <i class="fas fa-check ml-1"></i>تایید محصول
                    </button>
                    <button onclick="approveProduct(${product.id}, 'reject')" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i class="fas fa-times ml-1"></i>رد محصول
                    </button>
                ` : ''}
                
                ${(product.banner_path && (product.banner_status === 'pending' || product.banner_status === 'rejected')) ? `
                    <button onclick="approveBanner(${product.id}, 'approve')" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                        <i class="fas fa-image ml-1"></i>تایید بنر
                    </button>
                    <button onclick="approveBanner(${product.id}, 'reject')" class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition-colors">
                        <i class="fas fa-image ml-1"></i>رد بنر
                    </button>
                ` : ''}
                
                <button onclick="deleteProduct(${product.id})" class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                    <i class="fas fa-trash ml-1"></i>حذف
                </button>
            </div>
        </div>
    `).join('');
}

function filterProducts(status) {
    // Update filter buttons
    document.querySelectorAll('.product-filter-btn').forEach(btn => {
        btn.classList.remove('filter-active');
        btn.classList.add('bg-gray-100', 'text-gray-700');
    });
    
    event.target.classList.add('filter-active');
    event.target.classList.remove('bg-gray-100', 'text-gray-700');
    
    // Filter products
    if (status === 'all') {
        filteredShopProducts = [...allShopProducts];
    } else {
        filteredShopProducts = allShopProducts.filter(product => product.status === status);
    }
    
    displayShopProducts();
}

async function approveProduct(productId, decision) {
    if (!confirm(`آیا مطمئن هستید که می‌خواهید این محصول را ${decision === 'approve' ? 'تایید' : 'رد'} کنید؟`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('decision', decision);
        
        const response = await fetch('../api/shop.php?action=admin_approve_product', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert(data.message);
            loadShopProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
}

async function approveBanner(productId, decision) {
    if (!confirm(`آیا مطمئن هستید که می‌خواهید بنر این محصول را ${decision === 'approve' ? 'تایید' : 'رد'} کنید؟`)) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('decision', decision);
        
        const response = await fetch('../api/shop.php?action=admin_approve_banner', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert(data.message);
            loadShopProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
}

async function deleteProduct(productId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        
        const response = await fetch('../api/shop.php?action=admin_delete_product', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert(data.message);
            loadShopProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
}

function updateShopStats() {
    const total = allShopProducts.length;
    const pending = allShopProducts.filter(p => p.status === 'pending').length;
    const approved = allShopProducts.filter(p => p.status === 'approved').length;
    const totalViews = allShopProducts.reduce((sum, p) => sum + (p.views || 0), 0);
    
    document.getElementById('totalProducts').textContent = total;
    document.getElementById('pendingProducts').textContent = pending;
    document.getElementById('approvedProducts').textContent = approved;
    document.getElementById('totalProductViews').textContent = totalViews;
}

function getProductStatusColor(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
}

function getProductStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید شده';
        case 'rejected': return 'رد شده';
        default: return 'در انتظار تایید';
    }
}

function getCategoryName(category) {
    const categories = {
        'php': 'PHP',
        'python': 'Python',
        'javascript': 'JavaScript',
        'react': 'React',
        'vue': 'Vue.js',
        'laravel': 'Laravel',
        'wordpress': 'WordPress',
        'mobile': 'موبایل',
        'desktop': 'دسکتاپ',
        'other': 'سایر'
    };
    return categories[category] || category;
}

function formatPrice(price) {
    return price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

// Helper functions for project status in verification requests
function getProjectStatusBadge(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getProjectStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید';
        case 'rejected': return 'رد';
        case 'pending': return 'انتظار';
        default: return 'نامشخص';
    }
}

// Helper functions for banner status
function getBannerStatusColor(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function getBannerStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید شده';
        case 'rejected': return 'رد شده';
        case 'pending': return 'در انتظار';
        default: return 'نامشخص';
    }
}

// ======================
// ANALYTICS MANAGEMENT
// ======================

let analyticsData = {};

async function loadAnalytics() {
    try {
        const response = await fetch('../api/shop-stats.php?action=admin');
        const data = await response.json();
        
        if (data.success) {
            analyticsData = data.stats;
            displayAnalytics();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        console.error('خطا در بارگذاری آنالیتیکس:', error);
    }
}

function displayAnalytics() {
    // Update main stats
    document.getElementById('analyticsOnlineUsers').textContent = analyticsData.today.online_users || 0;
    document.getElementById('analyticsTodayVisits').textContent = analyticsData.today.visits || 0;
    document.getElementById('analyticsTodaySearches').textContent = analyticsData.today.searches || 0;
    document.getElementById('analyticsTodayContacts').textContent = analyticsData.today.contacts || 0;
    
    // Display hourly chart
    displayHourlyChart();
    
    // Display weekly chart
    displayWeeklyChart();
    
    // Display popular products
    displayPopularProducts();
    
    // Display search trends
    displaySearchTrends();
}

function displayHourlyChart() {
    const chartContainer = document.getElementById('hourlyChart');
    if (!analyticsData.hourly_activity) return;
    
    const maxValue = Math.max(...analyticsData.hourly_activity, 1);
    
    chartContainer.innerHTML = analyticsData.hourly_activity.map((value, hour) => {
        const height = (value / maxValue) * 100;
        return `
            <div class="flex flex-col items-center">
                <div class="w-4 bg-blue-500 rounded-t hover:bg-blue-600 transition-colors" 
                     style="height: ${height * 2}px; min-height: 4px;"
                     title="${hour}:00 - ${value} فعالیت"></div>
                <span class="text-xs text-gray-500 mt-1">${hour}</span>
            </div>
        `;
    }).join('');
}

function displayWeeklyChart() {
    const chartContainer = document.getElementById('weeklyChart');
    if (!analyticsData.weekly_stats) return;
    
    const maxValue = Math.max(...analyticsData.weekly_stats.map(d => d.visits), 1);
    
    chartContainer.innerHTML = analyticsData.weekly_stats.map((dayData) => {
        const height = (dayData.visits / maxValue) * 100;
        const date = new Date(dayData.date);
        const dayName = date.toLocaleDateString('fa-IR', { weekday: 'short' });
        
        return `
            <div class="flex flex-col items-center">
                <div class="w-8 bg-green-500 rounded-t hover:bg-green-600 transition-colors" 
                     style="height: ${height * 2}px; min-height: 4px;"
                     title="${dayData.date}: ${dayData.visits} بازدید"></div>
                <span class="text-xs text-gray-500 mt-1">${dayName}</span>
            </div>
        `;
    }).join('');
}

function displayPopularProducts() {
    const container = document.getElementById('popularProducts');
    if (!analyticsData.popular_products || Object.keys(analyticsData.popular_products).length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">داده‌ای موجود نیست</p>';
        return;
    }
    
    container.innerHTML = Object.entries(analyticsData.popular_products).map(([productId, views], index) => `
        <div class="flex items-center justify-between p-3 bg-white rounded-lg">
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center text-sm font-bold">
                    ${index + 1}
                </span>
                <span class="text-sm text-gray-600">محصول #${productId}</span>
            </div>
            <span class="text-sm font-semibold text-gray-900">${views} بازدید</span>
        </div>
    `).join('');
}

function displaySearchTrends() {
    const container = document.getElementById('searchTrends');
    if (!analyticsData.search_trends || Object.keys(analyticsData.search_trends).length === 0) {
        container.innerHTML = '<p class="text-gray-500 text-center py-4">داده‌ای موجود نیست</p>';
        return;
    }
    
    container.innerHTML = Object.entries(analyticsData.search_trends).map(([query, count], index) => `
        <div class="flex items-center justify-between p-3 bg-white rounded-lg">
            <div class="flex items-center gap-3">
                <span class="w-6 h-6 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center text-sm font-bold">
                    ${index + 1}
                </span>
                <span class="text-sm text-gray-900">${query}</span>
            </div>
            <span class="text-sm font-semibold text-gray-600">${count} جستجو</span>
        </div>
    `).join('');
}

</script>
</body>
</html>
