<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/shop-analytics.php';
require_once __DIR__ . '/../includes/utils.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // Public stats endpoint
    if ($method === 'GET' && $action === 'public') {
        $todayStats = ShopAnalytics::getTodayStats();
        
        // Get total products count
        $products = JsonDB::read('shop_products');
        $approvedProducts = array_filter($products, function($product) {
            return ($product['status'] ?? '') === 'approved';
        });
        
        // Get verified sellers count
        $users = JsonDB::read('users');
        $verifiedSellers = array_filter($users, function($user) {
            return ($user['verified'] ?? false) === true;
        });
        
        json_response([
            'success' => true,
            'stats' => [
                'online_users' => $todayStats['online_users'],
                'today_visits' => $todayStats['visits'],
                'today_views' => $todayStats['product_views'],
                'today_searches' => $todayStats['searches'],
                'today_contacts' => $todayStats['contacts'],
                'total_products' => count($approvedProducts),
                'verified_sellers' => count($verifiedSellers),
                'popular_products' => ShopAnalytics::getPopularProducts(3)
            ]
        ]);
        exit;
    }
    
    // Track product view
    if ($method === 'POST' && $action === 'track_view') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $productTitle = (string)($_POST['product_title'] ?? '');
        
        if ($productId > 0) {
            ShopAnalytics::trackProductView($productId, $productTitle);
        }
        
        json_response(['success' => true]);
        exit;
    }
    
    // Track search
    if ($method === 'POST' && $action === 'track_search') {
        $query = trim((string)($_POST['query'] ?? ''));
        $resultsCount = (int)($_POST['results_count'] ?? 0);
        
        if ($query) {
            ShopAnalytics::trackSearch($query, $resultsCount);
        }
        
        json_response(['success' => true]);
        exit;
    }
    
    // Track contact click
    if ($method === 'POST' && $action === 'track_contact') {
        $productId = (int)($_POST['product_id'] ?? 0);
        $contactType = (string)($_POST['contact_type'] ?? 'telegram');
        
        if ($productId > 0) {
            ShopAnalytics::trackContact($productId, $contactType);
        }
        
        json_response(['success' => true]);
        exit;
    }
    
    // Admin-only detailed stats
    if ($method === 'GET' && $action === 'admin') {
        $user = current_user();
        if (!$user || !is_admin()) {
            http_response_code(403);
            json_response(['success' => false, 'error' => 'دسترسی غیرمجاز']);
            exit;
        }
        
        $todayStats = ShopAnalytics::getTodayStats();
        $popularProducts = ShopAnalytics::getPopularProducts(10);
        $searchTrends = ShopAnalytics::getSearchTrends(10);
        $hourlyActivity = ShopAnalytics::getHourlyActivity();
        
        // Get analytics for last 7 days
        $weeklyStats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $analytics = JsonDB::read('shop_analytics');
            $dayData = $analytics[$date] ?? [];
            $weeklyStats[] = [
                'date' => $date,
                'visits' => $dayData['visits'] ?? 0,
                'views' => $dayData['product_views'] ?? 0,
                'searches' => $dayData['searches'] ?? 0,
                'contacts' => $dayData['contacts'] ?? 0
            ];
        }
        
        json_response([
            'success' => true,
            'stats' => [
                'today' => $todayStats,
                'popular_products' => $popularProducts,
                'search_trends' => $searchTrends,
                'hourly_activity' => $hourlyActivity,
                'weekly_stats' => $weeklyStats
            ]
        ]);
        exit;
    }
    
    json_response(['success' => false, 'error' => 'عمل نامعتبر']);
    
} catch (Throwable $e) {
    error_log('Shop Stats API Error: ' . $e->getMessage());
    json_response(['success' => false, 'error' => 'خطای داخلی سرور']);
}
