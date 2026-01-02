<?php
// Shop Analytics and Logging System
declare(strict_types=1);

require_once __DIR__ . '/db.php';

const SHOP_ANALYTICS_COLLECTION = 'shop_analytics';
const SHOP_LOGS_COLLECTION = 'shop_logs';

class ShopAnalytics {
    
    /**
     * Log shop activity
     */
    public static function logActivity(string $action, array $data = []): void {
        $entry = [
            'id' => JsonDB::nextId(),
            'action' => $action,
            'data' => $data,
            'ip' => self::getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'user_id' => self::getCurrentUserId(),
            'timestamp' => date('c'),
            'date' => date('Y-m-d'),
            'hour' => (int)date('H')
        ];
        
        JsonDB::upsert(SHOP_LOGS_COLLECTION, function(array $logs) use ($entry) {
            array_unshift($logs, $entry);
            // Keep last 10000 logs
            if (count($logs) > 10000) {
                $logs = array_slice($logs, 0, 10000);
            }
            return $logs;
        });
    }
    
    /**
     * Track shop visit
     */
    public static function trackVisit(): void {
        self::logActivity('shop_visit', [
            'referrer' => $_SERVER['HTTP_REFERER'] ?? '',
            'page' => $_SERVER['REQUEST_URI'] ?? ''
        ]);
        
        // Update daily statistics
        self::updateDailyStats('visits', 1);
    }
    
    /**
     * Track product view
     */
    public static function trackProductView(int $productId, string $productTitle = ''): void {
        self::logActivity('product_view', [
            'product_id' => $productId,
            'product_title' => $productTitle
        ]);
        
        self::updateDailyStats('product_views', 1);
    }
    
    /**
     * Track search query
     */
    public static function trackSearch(string $query, int $resultsCount = 0): void {
        self::logActivity('shop_search', [
            'query' => $query,
            'results_count' => $resultsCount
        ]);
        
        self::updateDailyStats('searches', 1);
    }
    
    /**
     * Track contact click (Telegram/Contact)
     */
    public static function trackContact(int $productId, string $contactType = 'telegram'): void {
        self::logActivity('contact_click', [
            'product_id' => $productId,
            'contact_type' => $contactType
        ]);
        
        self::updateDailyStats('contacts', 1);
    }
    
    /**
     * Get real-time online users count
     */
    public static function getOnlineUsersCount(): int {
        $users = JsonDB::read('users');
        $fiveMinutesAgo = date('c', strtotime('-5 minutes'));
        $onlineCount = 0;
        
        foreach ($users as $user) {
            $lastActivity = $user['lastLoginAt'] ?? $user['last_activity'] ?? '';
            if ($lastActivity && $lastActivity > $fiveMinutesAgo) {
                $onlineCount++;
            }
        }
        
        return max(1, $onlineCount); // At least 1 (current user)
    }
    
    /**
     * Get today's stats
     */
    public static function getTodayStats(): array {
        $today = date('Y-m-d');
        $analytics = JsonDB::read(SHOP_ANALYTICS_COLLECTION);
        $todayData = $analytics[$today] ?? [];
        
        return [
            'visits' => $todayData['visits'] ?? 0,
            'product_views' => $todayData['product_views'] ?? 0,
            'searches' => $todayData['searches'] ?? 0,
            'contacts' => $todayData['contacts'] ?? 0,
            'online_users' => self::getOnlineUsersCount()
        ];
    }
    
    /**
     * Get popular products (most viewed today)
     */
    public static function getPopularProducts(int $limit = 5): array {
        $logs = JsonDB::read(SHOP_LOGS_COLLECTION);
        $today = date('Y-m-d');
        $productViews = [];
        
        foreach ($logs as $log) {
            if ($log['action'] === 'product_view' && 
                substr($log['timestamp'], 0, 10) === $today) {
                $productId = $log['data']['product_id'] ?? 0;
                if ($productId) {
                    $productViews[$productId] = ($productViews[$productId] ?? 0) + 1;
                }
            }
        }
        
        // Sort by views
        arsort($productViews);
        return array_slice($productViews, 0, $limit, true);
    }
    
    /**
     * Get search trends (popular queries)
     */
    public static function getSearchTrends(int $limit = 10): array {
        $logs = JsonDB::read(SHOP_LOGS_COLLECTION);
        $today = date('Y-m-d');
        $searches = [];
        
        foreach ($logs as $log) {
            if ($log['action'] === 'shop_search' && 
                substr($log['timestamp'], 0, 10) === $today) {
                $query = trim($log['data']['query'] ?? '');
                if ($query && strlen($query) > 2) {
                    $searches[$query] = ($searches[$query] ?? 0) + 1;
                }
            }
        }
        
        // Sort by frequency
        arsort($searches);
        return array_slice($searches, 0, $limit, true);
    }
    
    /**
     * Get hourly activity for today
     */
    public static function getHourlyActivity(): array {
        $logs = JsonDB::read(SHOP_LOGS_COLLECTION);
        $today = date('Y-m-d');
        $hourlyData = array_fill(0, 24, 0);
        
        foreach ($logs as $log) {
            if (substr($log['timestamp'], 0, 10) === $today) {
                $hour = $log['hour'] ?? 0;
                if ($hour >= 0 && $hour < 24) {
                    $hourlyData[$hour]++;
                }
            }
        }
        
        return $hourlyData;
    }
    
    /**
     * Update daily statistics
     */
    private static function updateDailyStats(string $metric, int $increment = 1): void {
        $today = date('Y-m-d');
        
        JsonDB::upsert(SHOP_ANALYTICS_COLLECTION, function(array $analytics) use ($today, $metric, $increment) {
            if (!isset($analytics[$today])) {
                $analytics[$today] = [];
            }
            $analytics[$today][$metric] = ($analytics[$today][$metric] ?? 0) + $increment;
            
            // Clean old data (keep last 30 days)
            $thirtyDaysAgo = date('Y-m-d', strtotime('-30 days'));
            foreach ($analytics as $date => $data) {
                if ($date < $thirtyDaysAgo) {
                    unset($analytics[$date]);
                }
            }
            
            return $analytics;
        });
    }
    
    /**
     * Get client IP address
     */
    private static function getClientIP(): string {
        $keys = ['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR'];
        foreach ($keys as $key) {
            $ip = $_SERVER[$key] ?? '';
            if ($ip) {
                $ip = trim(explode(',', $ip)[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
    
    /**
     * Get current user ID
     */
    private static function getCurrentUserId(): ?int {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }
}
