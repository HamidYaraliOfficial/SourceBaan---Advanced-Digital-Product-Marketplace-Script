<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';

header('Content-Type: application/json; charset=utf-8');

$user = current_user();
$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

try {
    if ($method === 'GET' && $action === 'list_products') {
        // List all approved products
        $products = JsonDB::read('shop_products');
        $users = JsonDB::read('users');
        
        // Filter only approved products
        $products = array_filter($products, function($p) {
            return ($p['status'] ?? 'pending') === 'approved';
        });

        // Add seller info to products
        foreach ($products as &$product) {
            foreach ($users as $u) {
                if ((int)$u['id'] === (int)$product['seller_id']) {
                    $product['seller'] = [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'username' => $u['username'] ?? '',
                        'verified' => $u['verified'] ?? false
                    ];
                    break;
                }
            }
        }

        // Sort by creation date (newest first)
        usort($products, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        echo json_encode(['ok' => true, 'products' => array_values($products)]);
        exit;
    }

    if ($method === 'GET' && $action === 'get_product') {
        $product_id = (int)($_GET['id'] ?? 0);
        if (!$product_id) {
            throw new RuntimeException('شناسه محصول نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $users = JsonDB::read('users');
        $product = null;

        foreach ($products as $p) {
            if ((int)$p['id'] === $product_id && ($p['status'] ?? 'pending') === 'approved') {
                $product = $p;
                break;
            }
        }

        if (!$product) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'error' => 'محصول یافت نشد']);
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

        echo json_encode(['ok' => true, 'product' => $product]);
        exit;
    }

    // User must be logged in for other actions
    if (!$user) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'error' => 'نیاز به ورود دارید']);
        exit;
    }

    if ($method === 'GET' && $action === 'my_products') {
        // Get user's products
        $products = JsonDB::read('shop_products');
        $userProducts = array_filter($products, function($p) use ($user) {
            return (int)$p['seller_id'] === (int)$user['id'];
        });

        // Sort by creation date (newest first)
        usort($userProducts, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        echo json_encode(['ok' => true, 'products' => array_values($userProducts)]);
        exit;
    }

    if ($method === 'POST' && $action === 'add_product') {
        // Check if user is verified
        if (($user['verified'] ?? false) !== true) {
            throw new RuntimeException('برای فروش محصول باید تایید شده (تیک ابی) باشید');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = trim($_POST['price'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $telegram_id = trim($_POST['telegram_id'] ?? '');
        $demo_url = trim($_POST['demo_url'] ?? '');
        $features = trim($_POST['features'] ?? '');
        $requirements = trim($_POST['requirements'] ?? '');

        if (empty($title) || empty($description) || empty($price) || empty($category) || empty($telegram_id)) {
            throw new RuntimeException('لطفاً تمام فیلدهای اجباری را پر کنید');
        }

        // Handle banner upload
        $banner_path = '';
        $banner_status = 'pending'; // Banner needs admin approval
        if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $banner_path = handle_banner_upload($_FILES['banner'], $user['id']);
        }

        $product = [
            'id' => JsonDB::nextId(),
            'seller_id' => (int)$user['id'],
            'title' => $title,
            'description' => $description,
            'price' => $price,
            'category' => $category,
            'telegram_id' => $telegram_id,
            'demo_url' => $demo_url,
            'features' => $features,
            'requirements' => $requirements,
            'banner_path' => $banner_path,
            'banner_status' => $banner_status, // pending, approved, rejected
            'status' => 'pending', // pending, approved, rejected
            'views' => 0,
            'created_at' => date('c'),
            'updated_at' => date('c'),
            'approved_at' => null,
            'approved_by' => null,
            'banner_approved_at' => null,
            'banner_approved_by' => null
        ];

        $products = JsonDB::read('shop_products');
        $products[] = $product;
        JsonDB::write('shop_products', $products);

        echo json_encode(['ok' => true, 'message' => 'محصول شما ارسال شد و در انتظار تایید ادمین است', 'product_id' => $product['id']]);
        exit;
    }

    if ($method === 'POST' && $action === 'update_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (!$product_id) {
            throw new RuntimeException('شناسه محصول نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $productFound = false;

        foreach ($products as &$product) {
            if ((int)$product['id'] === $product_id && (int)$product['seller_id'] === (int)$user['id']) {
                $product['title'] = trim($_POST['title'] ?? $product['title']);
                $product['description'] = trim($_POST['description'] ?? $product['description']);
                $product['price'] = trim($_POST['price'] ?? $product['price']);
                $product['category'] = trim($_POST['category'] ?? $product['category']);
                $product['telegram_id'] = trim($_POST['telegram_id'] ?? $product['telegram_id']);
                $product['demo_url'] = trim($_POST['demo_url'] ?? $product['demo_url']);
                $product['features'] = trim($_POST['features'] ?? $product['features']);
                $product['requirements'] = trim($_POST['requirements'] ?? $product['requirements']);
                $product['updated_at'] = date('c');

                // Handle banner upload if new file provided
                if (isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
                    $product['banner_path'] = handle_banner_upload($_FILES['banner'], $user['id']);
                    $product['banner_status'] = 'pending'; // New banner needs admin approval
                    $product['banner_approved_at'] = null;
                    $product['banner_approved_by'] = null;
                }

                $productFound = true;
                break;
            }
        }

        if (!$productFound) {
            throw new RuntimeException('محصول یافت نشد یا دسترسی ندارید');
        }

        JsonDB::write('shop_products', $products);
        echo json_encode(['ok' => true, 'message' => 'محصول با موفقیت به‌روزرسانی شد']);
        exit;
    }

    if ($method === 'POST' && $action === 'delete_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        if (!$product_id) {
            throw new RuntimeException('شناسه محصول نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $initialCount = count($products);
        
        $products = array_filter($products, function($p) use ($product_id, $user) {
            return !((int)$p['id'] === $product_id && (int)$p['seller_id'] === (int)$user['id']);
        });

        if (count($products) === $initialCount) {
            throw new RuntimeException('محصول یافت نشد یا دسترسی ندارید');
        }

        JsonDB::write('shop_products', array_values($products));
        echo json_encode(['ok' => true, 'message' => 'محصول حذف شد']);
        exit;
    }

    // Admin actions
    if (!is_admin()) {
        http_response_code(403);
        echo json_encode(['ok' => false, 'error' => 'دسترسی ندارید']);
        exit;
    }

    if ($method === 'GET' && $action === 'admin_list_products') {
        // List all products for admin
        $products = JsonDB::read('shop_products');
        $users = JsonDB::read('users');
        
        // Add seller info to products
        foreach ($products as &$product) {
            foreach ($users as $u) {
                if ((int)$u['id'] === (int)$product['seller_id']) {
                    $product['seller'] = [
                        'id' => $u['id'],
                        'name' => $u['name'],
                        'username' => $u['username'] ?? '',
                        'verified' => $u['verified'] ?? false
                    ];
                    break;
                }
            }
        }

        // Sort by creation date (newest first)
        usort($products, function($a, $b) {
            return strcmp($b['created_at'], $a['created_at']);
        });

        echo json_encode(['ok' => true, 'products' => $products]);
        exit;
    }

    if ($method === 'POST' && $action === 'admin_approve_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $decision = $_POST['decision'] ?? ''; // 'approve' or 'reject'

        if (!$product_id || !in_array($decision, ['approve', 'reject'])) {
            throw new RuntimeException('اطلاعات ارسالی نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $productFound = false;

        foreach ($products as &$product) {
            if ((int)$product['id'] === $product_id) {
                $product['status'] = $decision === 'approve' ? 'approved' : 'rejected';
                if ($decision === 'approve') {
                    $product['approved_at'] = date('c');
                    $product['approved_by'] = (int)$user['id'];
                }
                $productFound = true;
                break;
            }
        }

        if (!$productFound) {
            throw new RuntimeException('محصول یافت نشد');
        }

        JsonDB::write('shop_products', $products);
        echo json_encode([
            'ok' => true, 
            'message' => $decision === 'approve' ? 'محصول تایید شد' : 'محصول رد شد'
        ]);
        exit;
    }

    if ($method === 'POST' && $action === 'admin_approve_banner') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        $decision = $_POST['decision'] ?? ''; // 'approve' or 'reject'

        if (!$product_id || !in_array($decision, ['approve', 'reject'])) {
            throw new RuntimeException('اطلاعات ارسالی نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $productFound = false;

        foreach ($products as &$product) {
            if ((int)$product['id'] === $product_id) {
                $product['banner_status'] = $decision === 'approve' ? 'approved' : 'rejected';
                if ($decision === 'approve') {
                    $product['banner_approved_at'] = date('c');
                    $product['banner_approved_by'] = (int)$user['id'];
                }
                $productFound = true;
                break;
            }
        }

        if (!$productFound) {
            throw new RuntimeException('محصول یافت نشد');
        }

        JsonDB::write('shop_products', $products);
        echo json_encode([
            'ok' => true, 
            'message' => $decision === 'approve' ? 'بنر تایید شد' : 'بنر رد شد'
        ]);
        exit;
    }

    if ($method === 'POST' && $action === 'admin_delete_product') {
        $product_id = (int)($_POST['product_id'] ?? 0);
        
        if (!$product_id) {
            throw new RuntimeException('شناسه محصول نامعتبر است');
        }

        $products = JsonDB::read('shop_products');
        $initialCount = count($products);
        
        $products = array_filter($products, function($p) use ($product_id) {
            return (int)$p['id'] !== $product_id;
        });

        if (count($products) === $initialCount) {
            throw new RuntimeException('محصول یافت نشد');
        }

        JsonDB::write('shop_products', array_values($products));
        echo json_encode(['ok' => true, 'message' => 'محصول حذف شد']);
        exit;
    }

    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'عملیات نامعتبر']);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}

function handle_banner_upload($file, $user_id) {
    $upload_dir = '../uploads/shop_banners/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }

    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new RuntimeException('فرمت تصویر پشتیبانی نمی‌شود');
    }

    if ($file['size'] > 5 * 1024 * 1024) { // 5MB max
        throw new RuntimeException('حجم تصویر نباید بیشتر از 5 مگابایت باشد');
    }

    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'banner_' . $user_id . '_' . time() . '.' . $extension;
    $filepath = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new RuntimeException('خطا در آپلود تصویر');
    }

    return 'uploads/shop_banners/' . $filename;
}
