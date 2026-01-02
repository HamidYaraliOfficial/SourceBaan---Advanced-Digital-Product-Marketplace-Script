<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/shop-analytics.php';

$currentUser = current_user();
$productId = (int)($_GET['id'] ?? 0);

if (!$productId) {
    header('Location: shop.php');
    exit;
}

// Get product details
$products = JsonDB::read('shop_products');
$users = JsonDB::read('users');
$product = null;

foreach ($products as $p) {
    if ((int)$p['id'] === $productId && ($p['status'] ?? 'pending') === 'approved') {
        $product = $p;
        break;
    }
}

if (!$product) {
    header('Location: shop.php');
    exit;
}

// Add seller info
foreach ($users as $u) {
    if ((int)$u['id'] === (int)$product['seller_id']) {
        $product['seller'] = [
            'id' => $u['id'],
            'name' => $u['name'],
            'username' => $u['username'] ?? '',
            'verified' => $u['verified'] ?? false,
            'telegram_id' => $u['telegram_id'] ?? ''
        ];
        break;
    }
}

// Increment views count
foreach ($products as &$p) {
    if ((int)$p['id'] === $productId) {
        $p['views'] = ($p['views'] ?? 0) + 1;
        $product['views'] = $p['views'];
        break;
    }
}
JsonDB::write('shop_products', $products);

// Track product view
ShopAnalytics::trackProductView($productId, $product['title']);

function getCategoryName($category) {
    $categories = [
        'php' => 'PHP',
        'python' => 'Python',
        'javascript' => 'JavaScript',
        'react' => 'React',
        'vue' => 'Vue.js',
        'laravel' => 'Laravel',
        'wordpress' => 'WordPress',
        'mobile' => 'موبایل',
        'desktop' => 'دسکتاپ',
        'telegram_bot' => 'ربات تلگرام',
        'website' => 'سایت',
        'other' => 'سایر'
    ];
    return $categories[$category] ?? $category;
}

function formatPrice($price) {
    return number_format($price);
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['title']) ?> - فروشگاه SourceBaan</title>
    <meta name="description" content="<?= htmlspecialchars(substr($product['description'], 0, 160)) ?>">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Vazirmatn', sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .card-hover:hover { transform: translateY(-2px); box-shadow: 0 15px 35px rgba(0,0,0,0.1); }
        .hero-gradient { background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%); }
        .glass-effect { background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.18); }
        .text-shadow { text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .image-zoom { transition: transform 0.3s ease; }
        .image-zoom:hover { transform: scale(1.05); }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen">

<!-- Premium Header -->
<header class="bg-gradient-to-r from-slate-900 via-purple-900 to-slate-900 shadow-2xl sticky top-0 z-50 backdrop-blur-lg border-b border-purple-500/20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4">
        <div class="flex items-center justify-between">
            <!-- Brand Section -->
            <div class="flex items-center gap-3 sm:gap-4">
                <a href="shop.php" class="group relative p-2 sm:p-3 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl hover:from-purple-600 hover:to-pink-600 transition-all duration-300 shadow-lg hover:shadow-xl">
                    <i class="fas fa-arrow-right text-white text-sm sm:text-lg group-hover:scale-110 transition-transform"></i>
                    <div class="absolute inset-0 bg-white/20 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity blur-sm"></div>
                </a>
                <div>
                    <h1 class="text-lg sm:text-2xl lg:text-3xl font-black bg-gradient-to-r from-white via-purple-200 to-pink-200 bg-clip-text text-transparent">
                        🛒 فروشگاه سورس کد
                    </h1>
                    <p class="text-purple-200 text-xs sm:text-sm mt-1 hidden sm:block">جزئیات محصول</p>
                </div>
            </div>
            
            <!-- Action Buttons -->
            <div class="flex items-center gap-2 sm:gap-3">
                <?php if ($currentUser): ?>
                    <a href="seller-panel.php" class="flex group relative px-3 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-emerald-500 to-teal-500 text-white font-semibold rounded-xl hover:from-emerald-600 hover:to-teal-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <div class="flex items-center gap-2">
                            <div class="w-4 h-4 sm:w-6 sm:h-6 bg-white/20 rounded-lg flex items-center justify-center">
                                <i class="fas fa-store text-xs sm:text-sm"></i>
                            </div>
                            <span class="text-sm sm:text-base">پنل فروش</span>
                        </div>
                        <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </a>
                    <div class="flex items-center gap-2 sm:gap-3 px-2 sm:px-4 py-2 bg-white/10 backdrop-blur-md rounded-xl border border-white/20">
                        <div class="text-right hidden sm:block">
                            <p class="font-semibold text-white text-xs sm:text-sm flex items-center gap-2">
                                <?= htmlspecialchars($currentUser['name']) ?>
                                <?php if (($currentUser['verified'] ?? false) === true): ?>
                                    <i class="fas fa-check-circle text-blue-400" title="تایید شده"></i>
                                <?php endif; ?>
                            </p>
                            <p class="text-purple-200 text-xs">خوش آمدید</p>
                        </div>
                        <div class="w-8 h-8 sm:w-10 sm:h-10 bg-gradient-to-r from-purple-400 to-pink-400 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                            <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="auth.php" class="group relative px-3 sm:px-6 py-2 sm:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-semibold rounded-xl hover:from-blue-600 hover:to-purple-600 transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-sign-in-alt text-xs sm:text-sm"></i>
                            <span class="text-xs sm:text-sm">ورود</span>
                        </div>
                        <div class="absolute inset-0 bg-white/10 rounded-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-6 sm:py-8">
    <!-- Breadcrumb -->
    <nav class="flex items-center space-x-2 space-x-reverse text-sm text-gray-600 mb-6 sm:mb-8">
        <a href="shop.php" class="hover:text-purple-600 transition-colors">فروشگاه</a>
        <i class="fas fa-chevron-left text-xs"></i>
        <a href="shop.php?category=<?= urlencode($product['category']) ?>" class="hover:text-purple-600 transition-colors"><?= getCategoryName($product['category']) ?></a>
        <i class="fas fa-chevron-left text-xs"></i>
        <span class="text-gray-900 font-medium"><?= htmlspecialchars($product['title']) ?></span>
    </nav>

    <!-- Product Detail Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12 mb-8 sm:mb-12">
        <!-- Product Images & Demo -->
        <div class="space-y-6">
            <!-- Main Image -->
            <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-blue-100 to-purple-100 aspect-video shadow-2xl">
                <?php if ($product['banner_path'] && ($product['banner_status'] ?? '') === 'approved'): ?>
                    <img src="<?= htmlspecialchars($product['banner_path']) ?>" 
                         class="w-full h-full object-cover image-zoom" 
                         alt="<?= htmlspecialchars($product['title']) ?>">
                <?php else: ?>
                    <div class="w-full h-full flex items-center justify-center">
                        <div class="text-center space-y-4">
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-r from-purple-400 to-pink-400 rounded-2xl flex items-center justify-center mx-auto shadow-xl">
                                <i class="fas fa-code text-white text-2xl sm:text-3xl"></i>
                            </div>
                            <p class="text-gray-600 font-medium"><?= htmlspecialchars($product['title']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Category Badge -->
                <div class="absolute top-4 right-4">
                    <span class="px-3 py-1 bg-white/90 backdrop-blur-sm text-purple-800 text-sm font-semibold rounded-full shadow-lg">
                        <?= getCategoryName($product['category']) ?>
                    </span>
                </div>
                
                <!-- Views Badge -->
                <div class="absolute bottom-4 left-4">
                    <span class="px-3 py-1 bg-black/70 text-white text-sm rounded-full backdrop-blur-sm">
                        <i class="fas fa-eye ml-1"></i><?= $product['views'] ?? 0 ?> بازدید
                    </span>
                </div>
            </div>

            <!-- Demo Button -->
            <?php if (!empty($product['demo_url'])): ?>
                <a href="<?= htmlspecialchars($product['demo_url']) ?>" target="_blank" 
                   class="block w-full text-center px-6 py-4 bg-gradient-to-r from-blue-500 to-indigo-600 text-white font-semibold rounded-2xl hover:from-blue-600 hover:to-indigo-700 transition-all duration-300 shadow-lg hover:shadow-xl transform hover:scale-[1.02]">
                    <i class="fas fa-external-link-alt ml-2"></i>مشاهده دمو زنده
                </a>
            <?php endif; ?>
        </div>

        <!-- Product Info -->
        <div class="space-y-6 sm:space-y-8">
            <!-- Header Info -->
            <div>
                <h1 class="text-2xl sm:text-3xl lg:text-4xl font-black text-gray-900 mb-4 leading-tight">
                    <?= htmlspecialchars($product['title']) ?>
                </h1>
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                    <div class="text-3xl sm:text-4xl font-black bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent">
                        <?= formatPrice($product['price']) ?> تومان
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="px-3 py-1 bg-green-100 text-green-800 text-sm font-medium rounded-full">
                            <i class="fas fa-check-circle ml-1"></i>موجود
                        </span>
                    </div>
                </div>
            </div>

            <!-- Seller Info -->
            <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">فروشنده</h3>
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 bg-gradient-to-r from-purple-400 to-pink-400 rounded-2xl flex items-center justify-center text-white font-bold text-lg sm:text-xl shadow-lg">
                        <?= strtoupper(substr($product['seller']['name'], 0, 1)) ?>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-1">
                            <h4 class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($product['seller']['name']) ?></h4>
                            <?php if ($product['seller']['verified']): ?>
                                <div class="flex items-center gap-1 px-2 py-1 bg-blue-100 text-blue-800 rounded-full text-xs font-medium">
                                    <i class="fas fa-check-circle"></i>
                                    <span>تایید شده</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <p class="text-gray-600 text-sm">فروشنده حرفه‌ای</p>
                    </div>
                </div>
            </div>

            <!-- Purchase Button -->
            <div class="space-y-4">
                <a href="https://t.me/<?= htmlspecialchars(str_replace('@', '', $product['telegram_id'])) ?>" 
                   target="_blank" 
                   onclick="trackContactClick(<?= $product['id'] ?>, 'telegram')"
                   class="block w-full text-center px-6 py-4 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-bold rounded-2xl hover:from-purple-700 hover:to-pink-700 transition-all duration-300 shadow-xl hover:shadow-2xl transform hover:scale-[1.02] text-lg">
                    <i class="fab fa-telegram ml-2 text-xl"></i>خرید از طریق تلگرام
                </a>
                
                <div class="flex items-center justify-center gap-4 text-sm text-gray-600">
                    <div class="flex items-center gap-1">
                        <i class="fas fa-shield-check text-green-500"></i>
                        <span>پرداخت امن</span>
                    </div>
                    <div class="w-px h-4 bg-gray-300"></div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-headset text-blue-500"></i>
                        <span>پشتیبانی 24/7</span>
                    </div>
                    <div class="w-px h-4 bg-gray-300"></div>
                    <div class="flex items-center gap-1">
                        <i class="fas fa-download text-purple-500"></i>
                        <span>دانلود فوری</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Product Details Tabs -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">
        <!-- Tab Navigation -->
        <div class="border-b border-gray-200 bg-gray-50">
            <nav class="flex overflow-x-auto">
                <button onclick="showTab('description')" class="tab-btn flex-shrink-0 px-6 py-4 text-gray-600 hover:text-purple-600 font-medium border-b-2 border-transparent hover:border-purple-300 transition-all tab-active" data-tab="description">
                    <i class="fas fa-align-left ml-2"></i>توضیحات
                </button>
                <?php if (!empty($product['features'])): ?>
                <button onclick="showTab('features')" class="tab-btn flex-shrink-0 px-6 py-4 text-gray-600 hover:text-purple-600 font-medium border-b-2 border-transparent hover:border-purple-300 transition-all" data-tab="features">
                    <i class="fas fa-star ml-2"></i>ویژگی‌ها
                </button>
                <?php endif; ?>
                <?php if (!empty($product['requirements'])): ?>
                <button onclick="showTab('requirements')" class="tab-btn flex-shrink-0 px-6 py-4 text-gray-600 hover:text-purple-600 font-medium border-b-2 border-transparent hover:border-purple-300 transition-all" data-tab="requirements">
                    <i class="fas fa-cog ml-2"></i>نیازمندی‌ها
                </button>
                <?php endif; ?>
            </nav>
        </div>

        <!-- Tab Content -->
        <div class="p-6 sm:p-8">
            <!-- Description Tab -->
            <div id="description-content" class="tab-content">
                <h3 class="text-xl font-bold text-gray-900 mb-4">توضیحات کامل محصول</h3>
                <div class="prose prose-lg max-w-none text-gray-700 leading-relaxed">
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                </div>
            </div>

            <!-- Features Tab -->
            <?php if (!empty($product['features'])): ?>
            <div id="features-content" class="tab-content hidden">
                <h3 class="text-xl font-bold text-gray-900 mb-4">ویژگی‌های محصول</h3>
                <div class="bg-green-50 rounded-xl p-6 border-l-4 border-green-400">
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                        <?= htmlspecialchars($product['features']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Requirements Tab -->
            <?php if (!empty($product['requirements'])): ?>
            <div id="requirements-content" class="tab-content hidden">
                <h3 class="text-xl font-bold text-gray-900 mb-4">نیازمندی‌های سیستم</h3>
                <div class="bg-yellow-50 rounded-xl p-6 border-l-4 border-yellow-400">
                    <div class="text-gray-700 leading-relaxed whitespace-pre-line">
                        <?= htmlspecialchars($product['requirements']) ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back to Shop -->
    <div class="text-center mt-8 sm:mt-12">
        <a href="shop.php" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-colors">
            <i class="fas fa-arrow-right"></i>
            بازگشت به فروشگاه
        </a>
    </div>
</div>

<script>
// Tab functionality
function showTab(tabName) {
    // Hide all tab contents
    document.querySelectorAll('.tab-content').forEach(content => {
        content.classList.add('hidden');
    });
    
    // Remove active class from all tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('tab-active', 'border-purple-600', 'text-purple-600');
        btn.classList.add('border-transparent', 'text-gray-600');
    });
    
    // Show selected tab content
    const selectedContent = document.getElementById(tabName + '-content');
    if (selectedContent) {
        selectedContent.classList.remove('hidden');
    }
    
    // Add active class to selected tab
    const selectedTab = document.querySelector(`[data-tab="${tabName}"]`);
    if (selectedTab) {
        selectedTab.classList.add('tab-active', 'border-purple-600', 'text-purple-600');
        selectedTab.classList.remove('border-transparent', 'text-gray-600');
    }
}

// Track contact click
function trackContactClick(productId, contactType = 'telegram') {
    if (productId) {
        fetch('/api/shop-stats.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=track_contact&product_id=${productId}&contact_type=${contactType}`
        }).catch(error => console.error('خطا در ثبت تماس:', error));
    }
}

// Initialize first tab
document.addEventListener('DOMContentLoaded', function() {
    showTab('description');
});
</script>

</body>
</html>
