<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$currentUser = current_user();
if (!$currentUser) {
    header('Location: auth.php');
    exit;
}

// Check if user is verified
$isVerified = ($currentUser['verified'] ?? false) === true;
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>پنل فروش - SourceBaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .btn-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .btn-primary:hover { background: linear-gradient(135deg, #5a67d8 0%, #6b46c1 100%); }
        .tab-active { border-bottom: 3px solid #667eea; color: #667eea; }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">

<!-- Premium Seller Header -->
<header class="bg-gradient-to-r from-slate-900 via-indigo-900 to-purple-900 shadow-2xl sticky top-0 z-50 backdrop-blur-lg border-b border-indigo-500/20">
    <div class="max-w-7xl mx-auto px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Brand Section -->
            <div class="flex items-center gap-4">
                <a href="index.php" class="group relative p-3 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl hover:from-indigo-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-home text-white text-lg group-hover:scale-110 transition-transform"></i>
                    <div class="absolute inset-0 bg-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
                </a>
                <div>
                    <h1 class="text-3xl font-black bg-gradient-to-r from-white via-indigo-200 to-purple-200 bg-clip-text text-transparent">
                        💼 پنل فروش حرفه‌ای
                    </h1>
                    <p class="text-indigo-200 text-sm mt-1">سیستم مدیریت پیشرفته محصولات</p>
                </div>
            </div>
            
            <!-- User Profile & Actions -->
            <div class="flex items-center gap-4">
                <!-- Stats Mini Card -->
                <div class="hidden md:flex items-center gap-3 bg-white/10 backdrop-blur-md rounded-xl px-4 py-2 border border-white/20">
                    <div class="text-center">
                        <div class="text-white font-bold text-lg" id="totalProducts">0</div>
                        <div class="text-indigo-200 text-xs">محصولات</div>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div class="text-center">
                        <div class="text-white font-bold text-lg" id="totalSales">0</div>
                        <div class="text-indigo-200 text-xs">فروش</div>
                    </div>
                </div>
                
                <!-- Navigation -->
                <a href="shop.php" class="group relative px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                    <div class="flex items-center gap-2">
                        <div class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center">
                            <i class="fas fa-store text-sm"></i>
                        </div>
                        <span>فروشگاه</span>
                    </div>
                    <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </a>
                
                <!-- User Profile -->
                <div class="flex items-center gap-3 px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/20">
                    <div class="text-right">
                        <p class="font-semibold text-white text-sm flex items-center gap-2">
                            <?= htmlspecialchars($currentUser['name']) ?>
                            <?php if ($isVerified): ?>
                                <div class="relative group">
                                    <i class="fas fa-check-circle text-blue-400 cursor-help" title="فروشنده تایید شده"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-black text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        فروشنده تایید شده
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="relative group">
                                    <i class="fas fa-clock text-amber-400 cursor-help" title="در انتظار تایید"></i>
                                    <div class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 px-3 py-1 bg-black text-white text-xs rounded-lg opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                                        در انتظار تایید
                                    </div>
                                </div>
                            <?php endif; ?>
                        </p>
                        <p class="text-indigo-200 text-xs">
                            <?= $isVerified ? '✅ فروشنده تایید شده' : '⏳ در انتظار تایید' ?>
                        </p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-400 to-purple-400 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                        <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto p-6">
    <?php if (!$isVerified): ?>
    <!-- Premium Verification Notice -->
    <div class="relative overflow-hidden mb-8">
        <!-- Animated Background -->
        <div class="absolute inset-0 bg-gradient-to-br from-amber-500 via-orange-500 to-red-500 rounded-3xl">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%23FBB6CE" fill-opacity="0.1"%3E%3Ccircle cx="30" cy="30" r="2"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')] opacity-20"></div>
            <div class="absolute top-0 right-0 w-64 h-64 bg-yellow-400/20 rounded-full blur-3xl animate-pulse delay-500"></div>
            <div class="absolute bottom-0 left-0 w-96 h-96 bg-orange-400/20 rounded-full blur-3xl animate-pulse"></div>
        </div>
        
        <div class="relative z-10 p-8 lg:p-12">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                <!-- Content -->
                <div class="space-y-6">
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-white/20 backdrop-blur-sm rounded-2xl flex items-center justify-center">
                                <i class="fas fa-user-check text-white text-xl"></i>
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-black text-white">
                                درخواست تایید
                            </h2>
                        </div>
                        <p class="text-xl text-white/90 leading-relaxed">
                            برای شروع فروش، ابتدا باید تیک ابی (تایید فروشنده) دریافت کنید
                        </p>
                    </div>
                    
                    <!-- Features -->
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                            <span class="text-white/90">بررسی سوابق و کیفیت محصولات شما</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                            <span class="text-white/90">امکان فروش محصولات با کیفیت بالا</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 bg-white rounded-full"></div>
                            <span class="text-white/90">دسترسی به پنل فروش حرفه‌ای</span>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex flex-col sm:flex-row gap-4">
                        <button onclick="openVerificationModal()" class="group relative px-8 py-4 bg-white text-orange-600 font-bold rounded-2xl hover:bg-gray-50 transition-all duration-300 shadow-lg hover:shadow-xl">
                            <div class="flex items-center justify-center gap-3">
                                <i class="fas fa-paper-plane text-orange-600"></i>
                                <span>درخواست تایید</span>
                            </div>
                        </button>
                        
                        <button onclick="toggleGuide()" class="group relative px-6 py-4 bg-white/20 backdrop-blur-sm rounded-2xl border border-white/30 hover:bg-white/30 transition-all duration-300">
                            <div class="flex items-center justify-center gap-3">
                                <i class="fas fa-info-circle text-white"></i>
                                <span class="text-white font-semibold">راهنمای تایید</span>
                            </div>
                        </button>
                    </div>
                </div>
                
                <!-- Visual Element -->
                <div class="hidden lg:flex justify-center items-center">
                    <div class="relative">
                        <div class="w-80 h-80 bg-white/10 backdrop-blur-sm rounded-3xl border border-white/20 flex items-center justify-center">
                            <div class="text-center space-y-6">
                                <div class="w-32 h-32 bg-gradient-to-br from-white/20 to-white/10 rounded-3xl flex items-center justify-center mx-auto shadow-xl backdrop-blur-sm">
                                    <i class="fas fa-certificate text-white text-4xl"></i>
                                </div>
                                <div class="space-y-2">
                                    <h3 class="text-white font-bold text-2xl">تایید فروشنده</h3>
                                    <p class="text-white/80 text-sm">احراز هویت و کیفیت</p>
                                </div>
                                <div class="flex justify-center gap-2">
                                    <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse"></div>
                                    <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse delay-300"></div>
                                    <div class="w-3 h-3 bg-white/60 rounded-full animate-pulse delay-700"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Floating Elements -->
                        <div class="absolute -top-6 -right-6 w-20 h-20 bg-gradient-to-r from-yellow-400 to-orange-400 rounded-3xl flex items-center justify-center shadow-2xl animate-bounce">
                            <i class="fas fa-star text-white text-xl"></i>
                        </div>
                        <div class="absolute -bottom-6 -left-6 w-16 h-16 bg-gradient-to-r from-emerald-400 to-teal-400 rounded-2xl flex items-center justify-center shadow-2xl animate-bounce delay-500">
                            <i class="fas fa-check text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Seller Guide -->
    <div id="sellerGuide" class="bg-white rounded-2xl shadow-lg p-6 mb-8 hidden">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-graduation-cap text-blue-500 ml-2"></i>
            راهنمای فروشندگان
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Steps -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">مراحل فروش:</h4>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold ml-3 mt-0.5">1</span>
                        <div>
                            <p class="font-medium text-gray-800">درخواست تایید</p>
                            <p class="text-sm text-gray-600">ابتدا درخواست تیک ابی ارسال کنید</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold ml-3 mt-0.5">2</span>
                        <div>
                            <p class="font-medium text-gray-800">افزودن محصول</p>
                            <p class="text-sm text-gray-600">پس از تایید، محصولات خود را اضافه کنید</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold ml-3 mt-0.5">3</span>
                        <div>
                            <p class="font-medium text-gray-800">بررسی توسط ادمین</p>
                            <p class="text-sm text-gray-600">محصول و بنر توسط تیم بررسی می‌شود</p>
                        </div>
                    </div>
                    <div class="flex items-start">
                        <span class="bg-blue-500 text-white w-6 h-6 rounded-full flex items-center justify-center text-sm font-bold ml-3 mt-0.5">4</span>
                        <div>
                            <p class="font-medium text-gray-800">انتشار و فروش</p>
                            <p class="text-sm text-gray-600">محصول در فروشگاه نمایش داده می‌شود</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Tips -->
            <div>
                <h4 class="font-semibold text-gray-800 mb-3">نکات مهم:</h4>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        عنوان محصول باید واضح و جذاب باشد
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        توضیحات کاملی از قابلیت‌ها ارائه دهید
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        بنر با کیفیت و مناسب آپلود کنید
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        قیمت منطقی و رقابتی تعین کنید
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        آیدی تلگرام صحیح وارد کنید
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check text-green-500 ml-2 mt-1"></i>
                        در صورت امکان لینک دمو ارائه دهید
                    </li>
                </ul>
            </div>
        </div>
        
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
            <h5 class="font-semibold text-blue-800 mb-2">درباره تایید:</h5>
            <p class="text-sm text-blue-700">
                تیم ما فعالیت‌های شما شامل تعداد پروژه‌های آپلود شده، امتیاز کسب شده، و کیفیت سورس‌هایتان را بررسی می‌کند. 
                کاربرانی که سابقه مثبت و محتوای باکیفیت دارند، زودتر تایید می‌شوند.
            </p>
        </div>
        
        <div class="mt-4 text-center">
            <button onclick="toggleGuide()" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                بستن راهنما
            </button>
        </div>
    </div>
    <?php else: ?>
    <!-- Verified User Guide Button -->
    <div class="text-center mb-6">
        <button onclick="toggleGuide()" class="px-6 py-3 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-xl transition-colors">
            <i class="fas fa-info-circle ml-2"></i>راهنمای فروش
        </button>
    </div>
    
    <!-- Seller Guide for Verified Users -->
    <div id="sellerGuide" class="bg-white rounded-2xl shadow-lg p-6 mb-8 hidden">
        <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
            <i class="fas fa-star text-green-500 ml-2"></i>
            راهنمای فروش موفق
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-plus text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">محصول کیفی اضافه کنید</h4>
                <p class="text-sm text-gray-600">عنوان جذاب، توضیحات کامل و بنر مناسب</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-clock text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">منتظر تایید باشید</h4>
                <p class="text-sm text-gray-600">محصول و بنر توسط تیم بررسی می‌شود</p>
            </div>
            
            <div class="text-center">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-money-bill-wave text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-800 mb-2">فروش و درآمد</h4>
                <p class="text-sm text-gray-600">خریداران از طریق تلگرام تماس می‌گیرند</p>
            </div>
        </div>
        
        <div class="mt-4 text-center">
            <button onclick="toggleGuide()" class="px-6 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                بستن راهنما
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-2xl shadow-lg mb-8">
        <div class="border-b border-gray-200">
            <nav class="flex space-x-8 space-x-reverse px-6">
                <button onclick="showTab('products')" class="py-4 text-gray-600 hover:text-blue-600 font-medium tab-active" id="productsTab">
                    <i class="fas fa-box ml-2"></i>محصولات من
                </button>
                <?php if ($isVerified): ?>
                <button onclick="showTab('add')" class="py-4 text-gray-600 hover:text-blue-600 font-medium" id="addTab">
                    <i class="fas fa-plus ml-2"></i>افزودن محصول
                </button>
                <?php endif; ?>
                <button onclick="showTab('stats')" class="py-4 text-gray-600 hover:text-blue-600 font-medium" id="statsTab">
                    <i class="fas fa-chart-line ml-2"></i>آمار
                </button>
            </nav>
        </div>
    </div>

    <!-- Products Tab -->
    <div id="productsTabContent" class="tab-content">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-gray-900">محصولات من</h3>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="loadMyProducts()" class="px-4 py-2 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition-colors">
                        <i class="fas fa-refresh ml-1"></i>بروزرسانی
                    </button>
                </div>
            </div>
            <div id="myProductsList" class="space-y-4">
                <div class="text-center py-8 text-gray-500">در حال بارگذاری...</div>
            </div>
        </div>
    </div>

    <!-- Add Product Tab -->
    <?php if ($isVerified): ?>
    <div id="addTabContent" class="tab-content hidden">
        <div class="bg-white rounded-2xl shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-900 mb-6">افزودن محصول جدید</h3>
            <form id="addProductForm" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان محصول *</label>
                        <input type="text" name="title" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">قیمت (تومان) *</label>
                        <input type="text" name="price" required placeholder="مثال: 100,000" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">دسته‌بندی *</label>
                        <select name="category" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">انتخاب دسته‌بندی</option>
                            <option value="php">PHP</option>
                            <option value="python">Python</option>
                            <option value="javascript">JavaScript</option>
                            <option value="react">React</option>
                            <option value="vue">Vue.js</option>
                            <option value="laravel">Laravel</option>
                            <option value="wordpress">WordPress</option>
                            <option value="mobile">موبایل</option>
                            <option value="telegram_bot">ربات تلگرام</option>
                            <option value="website">سایت</option>
                            <option value="desktop">دسکتاپ</option>
                            <option value="other">سایر</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">آیدی تلگرام *</label>
                        <input type="text" name="telegram_id" required placeholder="@username" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">بنر محصول</label>
                    <input type="file" name="banner" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <p class="text-sm text-gray-500 mt-1">حداکثر 5 مگابایت - فرمت‌های مجاز: JPG, PNG, GIF, WebP</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">لینک دمو (اختیاری)</label>
                    <input type="url" name="demo_url" placeholder="https://demo.example.com" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات محصول *</label>
                    <textarea name="description" required rows="4" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="توضیح کاملی از محصول ارائه دهید..."></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">ویژگی‌های محصول (اختیاری)</label>
                    <textarea name="features" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="• ریسپانسیو&#10;• پنل ادمین&#10;• دیتابیس MySQL"></textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نیازمندی‌ها (اختیاری)</label>
                    <textarea name="requirements" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="• PHP 7.4 یا بالاتر&#10;• MySQL 5.7&#10;• Apache/Nginx"></textarea>
                </div>

                <button type="submit" class="w-full btn-primary text-white px-6 py-4 rounded-xl font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-plus ml-2"></i>افزودن محصول
                </button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Tab -->
    <div id="statsTabContent" class="tab-content hidden">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-box text-blue-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalMyProducts">0</div>
                <div class="text-sm text-gray-600">کل محصولات</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-check-circle text-green-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="approvedProducts">0</div>
                <div class="text-sm text-gray-600">تایید شده</div>
            </div>
            
            <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-eye text-yellow-600"></i>
                </div>
                <div class="text-2xl font-bold text-gray-900" id="totalViews">0</div>
                <div class="text-sm text-gray-600">کل بازدید</div>
            </div>
        </div>
    </div>
</div>

<!-- Verification Request Modal -->
<div id="verificationModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">درخواست تایید (تیک ابی)</h3>
            <button onclick="closeVerificationModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="verificationForm" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نام کامل *</label>
                <input type="text" name="name" required value="<?= htmlspecialchars($currentUser['name']) ?>" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">آیدی تلگرام *</label>
                <input type="text" name="telegram_id" required placeholder="@username" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">شماره تماس</label>
                <input type="text" name="phone" placeholder="09123456789" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">دلیل درخواست تایید *</label>
                <textarea name="reason" required rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="چرا می‌خواهید تایید شوید؟"></textarea>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white px-6 py-3 rounded-xl font-semibold">
                ارسال درخواست
            </button>
        </form>
    </div>
</div>

<!-- Edit Product Modal -->
<div id="editProductModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-bold text-gray-900">ویرایش محصول</h3>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <form id="editProductForm" class="space-y-4">
            <input type="hidden" name="product_id" id="editProductId">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">عنوان محصول *</label>
                    <input type="text" name="title" id="editTitle" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">قیمت (تومان) *</label>
                    <input type="text" name="price" id="editPrice" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">دسته‌بندی *</label>
                    <select name="category" id="editCategory" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="php">PHP</option>
                        <option value="python">Python</option>
                        <option value="javascript">JavaScript</option>
                        <option value="react">React</option>
                        <option value="vue">Vue.js</option>
                        <option value="laravel">Laravel</option>
                        <option value="wordpress">WordPress</option>
                        <option value="mobile">موبایل</option>
                        <option value="telegram_bot">ربات تلگرام</option>
                        <option value="website">سایت</option>
                        <option value="desktop">دسکتاپ</option>
                        <option value="other">سایر</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">آیدی تلگرام *</label>
                    <input type="text" name="telegram_id" id="editTelegramId" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">بنر جدید</label>
                <input type="file" name="banner" accept="image/*" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">لینک دمو</label>
                <input type="url" name="demo_url" id="editDemoUrl" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات محصول *</label>
                <textarea name="description" id="editDescription" required rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ویژگی‌ها</label>
                <textarea name="features" id="editFeatures" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">نیازمندی‌ها</label>
                <textarea name="requirements" id="editRequirements" rows="3" class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
            </div>
            
            <button type="submit" class="w-full btn-primary text-white px-6 py-3 rounded-xl font-semibold">
                بروزرسانی محصول
            </button>
        </form>
    </div>
</div>

<script>
// Tab management
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('nav button').forEach(tab => {
        tab.classList.remove('tab-active');
    });
    
    // Show selected tab content
    document.getElementById(tabName + 'TabContent').classList.remove('hidden');
    
    // Add active class to selected tab
    document.getElementById(tabName + 'Tab').classList.add('tab-active');
    
    // Load data based on tab
    if (tabName === 'products') {
        loadMyProducts();
    } else if (tabName === 'stats') {
        loadStats();
    }
}

// Load user's products
async function loadMyProducts() {
    try {
        const response = await fetch('api/shop.php?action=my_products');
        const data = await response.json();
        
        if (data.ok) {
            displayMyProducts(data.products);
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        document.getElementById('myProductsList').innerHTML = 
            '<div class="text-center py-8 text-red-500">خطا در بارگذاری: ' + error.message + '</div>';
    }
}

function displayMyProducts(products) {
    const container = document.getElementById('myProductsList');
    
    if (products.length === 0) {
        container.innerHTML = '<div class="text-center py-8 text-gray-500">هنوز محصولی نداشته‌اید</div>';
        return;
    }
    
    container.innerHTML = products.map(product => `
        <div class="border border-gray-200 rounded-xl p-4 hover:shadow-lg transition-shadow">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <h4 class="font-semibold text-gray-900">${product.title}</h4>
                        <span class="px-2 py-1 text-xs font-medium rounded-full ${getStatusColor(product.status)}">${getStatusText(product.status)}</span>
                    </div>
                    <p class="text-gray-600 text-sm mb-2">${product.description.substring(0, 100)}...</p>
                    <div class="flex items-center gap-4 text-sm text-gray-500">
                        <span><i class="fas fa-money-bill-wave ml-1"></i>${product.price} تومان</span>
                        <span><i class="fas fa-tag ml-1"></i>${product.category}</span>
                        <span><i class="fas fa-eye ml-1"></i>${product.views || 0} بازدید</span>
                        ${product.banner_path ? `<span class="px-2 py-1 text-xs rounded-full ${getBannerStatusColor(product.banner_status || 'pending')}">بنر: ${getBannerStatusText(product.banner_status || 'pending')}</span>` : ''}
                    </div>
                </div>
                ${product.banner_path ? `<img src="${product.banner_path}" class="w-20 h-20 object-cover rounded-lg mr-4">` : ''}
            </div>
            <div class="flex items-center justify-between mt-4 pt-4 border-t border-gray-100">
                <span class="text-xs text-gray-500">ایجاد شده: ${new Date(product.created_at).toLocaleDateString('fa-IR')}</span>
                <div class="flex space-x-2 space-x-reverse">
                    <button onclick="editProduct(${product.id})" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-lg text-sm hover:bg-blue-200 transition-colors">
                        <i class="fas fa-edit ml-1"></i>ویرایش
                    </button>
                    <button onclick="deleteProduct(${product.id})" class="px-3 py-1 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition-colors">
                        <i class="fas fa-trash ml-1"></i>حذف
                    </button>
                </div>
            </div>
        </div>
    `).join('');
}

function getStatusColor(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
}

function getStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید شده';
        case 'rejected': return 'رد شده';
        default: return 'در انتظار تایید';
    }
}

function getBannerStatusColor(status) {
    switch(status) {
        case 'approved': return 'bg-green-100 text-green-800';
        case 'rejected': return 'bg-red-100 text-red-800';
        default: return 'bg-yellow-100 text-yellow-800';
    }
}

function getBannerStatusText(status) {
    switch(status) {
        case 'approved': return 'تایید شده';
        case 'rejected': return 'رد شده';
        default: return 'در انتظار';
    }
}

// Load stats
async function loadStats() {
    try {
        const response = await fetch('api/shop.php?action=my_products');
        const data = await response.json();
        
        if (data.ok) {
            const total = data.products.length;
            const approved = data.products.filter(p => p.status === 'approved').length;
            const totalViews = data.products.reduce((sum, p) => sum + (p.views || 0), 0);
            
            document.getElementById('totalMyProducts').textContent = total;
            document.getElementById('approvedProducts').textContent = approved;
            document.getElementById('totalViews').textContent = totalViews;
        }
    } catch (error) {
        console.error('خطا در بارگذاری آمار:', error);
    }
}

// Verification request
function openVerificationModal() {
    document.getElementById('verificationModal').classList.remove('hidden');
    document.getElementById('verificationModal').classList.add('flex');
}

function closeVerificationModal() {
    document.getElementById('verificationModal').classList.add('hidden');
    document.getElementById('verificationModal').classList.remove('flex');
}

document.getElementById('verificationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/verification.php?action=request_verification', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert('درخواست تایید شما با موفقیت ارسال شد');
            closeVerificationModal();
            location.reload();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
});

// Add product
<?php if ($isVerified): ?>
document.getElementById('addProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/shop.php?action=add_product', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert('محصول با موفقیت اضافه شد');
            this.reset();
            showTab('products');
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
});
<?php endif; ?>

// Edit product
function editProduct(productId) {
    // Load product data and show edit modal
    fetch('api/shop.php?action=my_products')
        .then(response => response.json())
        .then(data => {
            if (data.ok) {
                const product = data.products.find(p => p.id == productId);
                if (product) {
                    document.getElementById('editProductId').value = product.id;
                    document.getElementById('editTitle').value = product.title;
                    document.getElementById('editPrice').value = product.price;
                    document.getElementById('editCategory').value = product.category;
                    document.getElementById('editTelegramId').value = product.telegram_id;
                    document.getElementById('editDemoUrl').value = product.demo_url || '';
                    document.getElementById('editDescription').value = product.description;
                    document.getElementById('editFeatures').value = product.features || '';
                    document.getElementById('editRequirements').value = product.requirements || '';
                    
                    document.getElementById('editProductModal').classList.remove('hidden');
                    document.getElementById('editProductModal').classList.add('flex');
                }
            }
        });
}

function closeEditModal() {
    document.getElementById('editProductModal').classList.add('hidden');
    document.getElementById('editProductModal').classList.remove('flex');
}

document.getElementById('editProductForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    try {
        const response = await fetch('api/shop.php?action=update_product', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert('محصول با موفقیت بروزرسانی شد');
            closeEditModal();
            loadMyProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
});

// Delete product
async function deleteProduct(productId) {
    if (!confirm('آیا مطمئن هستید که می‌خواهید این محصول را حذف کنید؟')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('product_id', productId);
        
        const response = await fetch('api/shop.php?action=delete_product', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.ok) {
            alert('محصول حذف شد');
            loadMyProducts();
        } else {
            throw new Error(data.error);
        }
    } catch (error) {
        alert('خطا: ' + error.message);
    }
}

// Toggle guide section
function toggleGuide() {
    const guideSection = document.getElementById('sellerGuide');
    if (guideSection.classList.contains('hidden')) {
        guideSection.classList.remove('hidden');
        guideSection.scrollIntoView({ behavior: 'smooth' });
    } else {
        guideSection.classList.add('hidden');
    }
}

// Update header stats
function updateHeaderStats() {
    // Count products and simulate sales
    const totalProducts = document.getElementById('totalProducts');
    const totalSales = document.getElementById('totalSales');
    
    if (totalProducts && <?= $isVerified ? 'true' : 'false' ?>) {
        fetch('/api/shop.php?action=get_my_products')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    animateNumber(totalProducts, data.products.length);
                    // Simulate sales data (you can replace with real data)
                    animateNumber(totalSales, Math.floor(data.products.length * 2.5));
                }
            })
            .catch(() => {
                totalProducts.textContent = '0';
                totalSales.textContent = '0';
            });
    }
}

function animateNumber(element, target) {
    let current = 0;
    const increment = Math.max(1, Math.floor(target / 30));
    
    const timer = setInterval(() => {
        if (current < target) {
            current += increment;
            if (current > target) current = target;
            element.textContent = current;
        } else {
            clearInterval(timer);
        }
    }, 50);
}

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    loadMyProducts();
    
    // Update header stats after a brief delay
    setTimeout(updateHeaderStats, 500);
});
</script>

</body>
</html>
