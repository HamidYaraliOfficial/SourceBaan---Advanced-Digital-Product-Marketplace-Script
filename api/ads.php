<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');

// Remove server signature
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
    header_remove('Server');
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    switch ($action) {
        case 'get':
            handle_get_ads();
            break;
            
        case 'create':
            require_login();
            if (!is_admin()) {
                throw new RuntimeException('دسترسی غیرمجاز');
            }
            handle_create_ad();
            break;
            
        case 'update':
            require_login();
            if (!is_admin()) {
                throw new RuntimeException('دسترسی غیرمجاز');
            }
            handle_update_ad();
            break;
            
        case 'delete':
            require_login();
            if (!is_admin()) {
                throw new RuntimeException('دسترسی غیرمجاز');
            }
            handle_delete_ad();
            break;
            
        case 'toggle':
            require_login();
            if (!is_admin()) {
                throw new RuntimeException('دسترسی غیرمجاز');
            }
            handle_toggle_ad();
            break;
            
        case 'track_view':
            handle_track_view();
            break;
            
        case 'track_click':
            handle_track_click();
            break;
            
        default:
            throw new RuntimeException('عمل نامعتبر است');
    }

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}

function handle_get_ads(): void {
    $location = $_GET['location'] ?? 'all';
    $active_only = isset($_GET['active_only']) && $_GET['active_only'] === 'true';
    
    $ads = JsonDB::read('ads') ?? [];
    
    // Filter by location
    if ($location !== 'all') {
        $ads = array_filter($ads, fn($ad) => in_array($location, $ad['locations'] ?? []));
    }
    
    // Filter active only
    if ($active_only) {
        $ads = array_filter($ads, fn($ad) => ($ad['active'] ?? false) === true);
    }
    
    // Sort by priority (higher first)
    usort($ads, fn($a, $b) => ($b['priority'] ?? 0) - ($a['priority'] ?? 0));
    
    echo json_encode(['ok' => true, 'ads' => array_values($ads)], JSON_UNESCAPED_UNICODE);
}

function handle_create_ad(): void {
    ensure_csrf_token();
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $type = $_POST['type'] ?? 'banner'; // banner, popup, sidebar, inline
    $locations = $_POST['locations'] ?? [];
    $link = trim($_POST['link'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $priority = max(0, min(100, (int)($_POST['priority'] ?? 50)));
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    if (empty($title)) {
        throw new RuntimeException('عنوان تبلیغ الزامی است');
    }
    
    if (empty($content) && empty($image)) {
        throw new RuntimeException('محتوا یا تصویر تبلیغ الزامی است');
    }
    
    if (!is_array($locations) || empty($locations)) {
        throw new RuntimeException('حداقل یک موقعیت نمایش انتخاب کنید');
    }
    
    $ads = JsonDB::read('ads') ?? [];
    
    $ad = [
        'id' => JsonDB::nextId(),
        'title' => $title,
        'content' => $content,
        'type' => $type,
        'locations' => $locations,
        'link' => $link,
        'image' => $image,
        'priority' => $priority,
        'active' => true,
        'start_date' => $start_date,
        'end_date' => $end_date,
        'views' => 0,
        'clicks' => 0,
        'created_at' => date('c'),
        'updated_at' => date('c')
    ];
    
    $ads[] = $ad;
    JsonDB::write('ads', $ads);
    
    echo json_encode(['ok' => true, 'message' => 'تبلیغ با موفقیت ایجاد شد', 'ad' => $ad], JSON_UNESCAPED_UNICODE);
}

function handle_update_ad(): void {
    ensure_csrf_token();
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('شناسه تبلیغ نامعتبر است');
    }
    
    $ads = JsonDB::read('ads') ?? [];
    $adIndex = -1;
    
    foreach ($ads as $i => $ad) {
        if ((int)($ad['id'] ?? 0) === $id) {
            $adIndex = $i;
            break;
        }
    }
    
    if ($adIndex === -1) {
        throw new RuntimeException('تبلیغ یافت نشد');
    }
    
    $title = trim($_POST['title'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $type = $_POST['type'] ?? 'banner';
    $locations = $_POST['locations'] ?? [];
    $link = trim($_POST['link'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $priority = max(0, min(100, (int)($_POST['priority'] ?? 50)));
    $start_date = $_POST['start_date'] ?? null;
    $end_date = $_POST['end_date'] ?? null;
    
    if (empty($title)) {
        throw new RuntimeException('عنوان تبلیغ الزامی است');
    }
    
    if (!is_array($locations) || empty($locations)) {
        throw new RuntimeException('حداقل یک موقعیت نمایش انتخاب کنید');
    }
    
    $ads[$adIndex]['title'] = $title;
    $ads[$adIndex]['content'] = $content;
    $ads[$adIndex]['type'] = $type;
    $ads[$adIndex]['locations'] = $locations;
    $ads[$adIndex]['link'] = $link;
    $ads[$adIndex]['image'] = $image;
    $ads[$adIndex]['priority'] = $priority;
    $ads[$adIndex]['start_date'] = $start_date;
    $ads[$adIndex]['end_date'] = $end_date;
    $ads[$adIndex]['updated_at'] = date('c');
    
    JsonDB::write('ads', $ads);
    
    echo json_encode(['ok' => true, 'message' => 'تبلیغ با موفقیت به‌روزرسانی شد'], JSON_UNESCAPED_UNICODE);
}

function handle_delete_ad(): void {
    ensure_csrf_token();
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('شناسه تبلیغ نامعتبر است');
    }
    
    $ads = JsonDB::read('ads') ?? [];
    $newAds = [];
    
    foreach ($ads as $ad) {
        if ((int)($ad['id'] ?? 0) !== $id) {
            $newAds[] = $ad;
        }
    }
    
    JsonDB::write('ads', $newAds);
    
    echo json_encode(['ok' => true, 'message' => 'تبلیغ با موفقیت حذف شد'], JSON_UNESCAPED_UNICODE);
}

function handle_track_view(): void {
    $adId = (int)($_GET['ad_id'] ?? $_POST['ad_id'] ?? 0);
    if ($adId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه تبلیغ نامعتبر است'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $ads = JsonDB::read('ads') ?? [];
    
    foreach ($ads as &$ad) {
        if ((int)($ad['id'] ?? 0) === $adId) {
            $ad['views'] = ($ad['views'] ?? 0) + 1;
            $ad['updated_at'] = date('c');
            break;
        }
    }
    
    JsonDB::write('ads', $ads);
    
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
}

function handle_track_click(): void {
    $adId = (int)($_GET['ad_id'] ?? $_POST['ad_id'] ?? 0);
    if ($adId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه تبلیغ نامعتبر است'], JSON_UNESCAPED_UNICODE);
        return;
    }
    
    $ads = JsonDB::read('ads') ?? [];
    
    foreach ($ads as &$ad) {
        if ((int)($ad['id'] ?? 0) === $adId) {
            $ad['clicks'] = ($ad['clicks'] ?? 0) + 1;
            $ad['updated_at'] = date('c');
            break;
        }
    }
    
    JsonDB::write('ads', $ads);
    
    echo json_encode(['ok' => true], JSON_UNESCAPED_UNICODE);
}

function handle_toggle_ad(): void {
    ensure_csrf_token();
    
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) {
        throw new RuntimeException('شناسه تبلیغ نامعتبر است');
    }
    
    $ads = JsonDB::read('ads') ?? [];
    
    foreach ($ads as &$ad) {
        if ((int)($ad['id'] ?? 0) === $id) {
            $ad['active'] = !($ad['active'] ?? false);
            $ad['updated_at'] = date('c');
            break;
        }
    }
    
    JsonDB::write('ads', $ads);
    
    echo json_encode(['ok' => true, 'message' => 'وضعیت تبلیغ تغییر کرد'], JSON_UNESCAPED_UNICODE);
}
