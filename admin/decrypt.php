<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

if (session_status() === PHP_SESSION_NONE) {
    @session_start();
}

$user = current_user();
if (!$user || !is_admin()) {
    http_response_code(403);
    header('Content-Type: text/plain; charset=utf-8');
    echo "403 - دسترسی غیرمجاز";
    exit;
}

// Helper: derive the same key used by JsonDB
function __sb_get_key(): string {
    $secret = defined('APP_SECRET') ? (string)APP_SECRET : '';
    if ($secret === '') {
        $secret = hash('sha256', SITE_NAME . '::fallback', true);
    }
    return hash('sha256', $secret, true); // 32 bytes
}

// Paths helper (mirror of JsonDB private logic)
function __sb_paths(string $name): array {
    $primaryDir = rtrim((string)DATA_DIR, "/\\");
    $primary = $primaryDir . DIRECTORY_SEPARATOR . $name . '.json';
    $fallbackDir = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sourcebaan-data';
    $fallback = $fallbackDir . DIRECTORY_SEPARATOR . $name . '.json';
    return ['primary' => $primary, 'fallback' => $fallback];
}

// Helper: decrypt raw ENCv1 payload string to array
function __sb_decrypt_raw(string $raw): array {
    $raw = trim($raw);
    // If payload contains ENCv1 anywhere (copied with prefixes/suffixes), extract from there
    $pos = strpos($raw, 'ENCv1:');
    if ($pos !== false) {
        $raw = substr($raw, $pos);
    }
    if (str_starts_with($raw, 'ENCv1:')) {
        $b64 = substr($raw, 6);
        // Remove any whitespace/newlines pasted from viewers
        $b64 = preg_replace('/\s+/', '', $b64);
        // Allow whitespace by using non-strict decode
        $bin = base64_decode($b64, false);
        if (is_string($bin) && strlen($bin) > 28) {
            $iv = substr($bin, 0, 12);
            $tag = substr($bin, 12, 16);
            $cipher = substr($bin, 28);
            $plain = openssl_decrypt($cipher, 'aes-256-gcm', __sb_get_key(), OPENSSL_RAW_DATA, $iv, $tag, 'db');
            if (is_string($plain) && $plain !== '') {
                $data = json_decode($plain, true);
                return is_array($data) ? $data : [];
            }
        }
        return [];
    }
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

// Pretty JSON helper
function __sb_json($data): string {
    return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
}

// CSV export helper (flatten associative arrays)
function __sb_to_csv(array $rows): string {
    if (empty($rows)) return "";
    $keys = [];
    foreach ($rows as $r) { $keys = array_unique(array_merge($keys, array_keys((array)$r))); }
    $out = fopen('php://temp', 'w+');
    fputcsv($out, $keys);
    foreach ($rows as $r) {
        $line = [];
        foreach ($keys as $k) { $v = $r[$k] ?? ''; $line[] = is_scalar($v) ? (string)$v : json_encode($v, JSON_UNESCAPED_UNICODE); }
        fputcsv($out, $line);
    }
    rewind($out);
    return stream_get_contents($out) ?: '';
}

// Router
$action = $_GET['action'] ?? 'ui';

if ($action === 'dump_users') {
    $format = ($_GET['format'] ?? 'json');
    $includeHash = isset($_GET['include_hash']) && $_GET['include_hash'] === '1';
    $source = 'primary';
    $users = JsonDB::read('users');
    if (empty($users)) {
        // Try explicit fallback file if present and primary seems empty
        $paths = __sb_paths('users');
        if (file_exists($paths['fallback'])) {
            $raw = @file_get_contents($paths['fallback']);
            if (is_string($raw) && $raw !== '') {
                $decoded = __sb_decrypt_raw($raw);
                if (!empty($decoded)) {
                    $users = $decoded;
                    $source = 'fallback';
                }
            }
        }
    }
    // Mask sensitive fields by default
    $safe = array_map(function($u) use ($includeHash) {
        $copy = $u;
        if (!$includeHash) {
            unset($copy['password_hash']);
        }
        return $copy;
    }, $users);

    if ($format === 'csv') {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="users_export.csv"');
        echo __sb_to_csv($safe);
        exit;
    }

    header('Content-Type: application/json; charset=utf-8');
    echo __sb_json(['ok' => true, 'count' => count($safe), 'source' => $source, 'users' => $safe]);
    exit;
}

if ($action === 'decrypt_payload' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    $payload = trim((string)($_POST['payload'] ?? ''));
    $data = __sb_decrypt_raw($payload);
    header('Content-Type: application/json; charset=utf-8');
    if (empty($data)) {
        $error = 'رمزگشایی ناموفق بود. اطمینان حاصل کنید متن دقیقا از "ENCv1:" شروع شده و APP_SECRET تغییر نکرده باشد.';
        echo __sb_json(['ok' => false, 'error' => $error]);
    } else {
        echo __sb_json(['ok' => true, 'data' => $data]);
    }
    exit;
}

if ($action === 'rewrite_plain' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    // Rewrite users as plaintext JSON (respects JSONDB_ENCRYPT=false)
    $users = JsonDB::read('users');
    $ok = true;
    try {
        JsonDB::write('users', is_array($users) ? $users : []);
    } catch (Throwable $e) {
        $ok = false;
    }
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => $ok, 'count' => is_array($users) ? count($users) : 0]);
    exit;
}

// UI
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ابزار رمزگشایی و خروجی کاربران</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="max-w-5xl mx-auto p-6">
        <div class="mb-6 flex items-center justify-between">
            <h1 class="text-2xl font-extrabold text-gray-900">ابزار رمزگشایی دیتابیس (ادمین)</h1>
            <a href="/admin/dashboard.php" class="px-4 py-2 bg-gray-900 text-white rounded-lg">داشبورد</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-2xl shadow p-5">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-users text-indigo-600"></i> خروجی کاربران (users)</h2>
                <p class="text-sm text-gray-500 mb-4">مشاهده سریع کاربران از فایل users.json (به صورت رمزگشایی شده)</p>
                <div class="flex flex-wrap items-center gap-3">
                    <a href="?action=dump_users&format=json" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg">
                        دانلود JSON
                    </a>
                    <a href="?action=dump_users&format=csv" class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg">
                        دانلود CSV
                    </a>
                    <a href="?action=dump_users&format=json&include_hash=1" class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white rounded-lg" title="نمایش password_hash (پیشنهاد نمی‌شود)">
                        JSON با password_hash
                    </a>
                    <form method="post" action="?action=rewrite_plain" onsubmit="return confirm('بازنویسی کاربران به صورت JSON بدون رمزنگاری انجام شود؟');">
                        <button type="submit" class="px-4 py-2 bg-gray-900 hover:bg-black text-white rounded-lg">بازنویسی به JSON ساده</button>
                    </form>
                </div>
                <p class="text-xs text-gray-500 mt-3">نکته امنیتی: نمایش password_hash فقط برای پشتیبان‌گیری/دیباگ. هرگز رمز عبور واقعی قابل بازیابی نیست.</p>
                <details class="mt-3">
                    <summary class="cursor-pointer text-sm text-gray-700">جزئیات مسیر ذخیره‌سازی</summary>
                    <?php $paths = __sb_paths('users'); ?>
                    <div class="mt-2 text-xs text-gray-600">
                        <div>Primary: <code class="break-all"><?php echo htmlspecialchars($paths['primary']); ?></code></div>
                        <div>Fallback: <code class="break-all"><?php echo htmlspecialchars($paths['fallback']); ?></code></div>
                        <div class="mt-1">در صورتی که فایل اصلی خالی باشد، ابزار تلاش می‌کند از مسیر fallback هم بخواند.</div>
                    </div>
                </details>
            </div>

            <div class="bg-white rounded-2xl shadow p-5">
                <h2 class="font-bold text-gray-800 mb-3 flex items-center gap-2"><i class="fas fa-lock-open text-purple-600"></i> رمزگشایی متن ENCv1</h2>
                <p class="text-sm text-gray-500 mb-4">متن داخل فایل‌های دیتابیس که با «ENCv1:» شروع می‌شود را اینجا وارد کنید</p>
                <form method="post" action="?action=decrypt_payload" class="space-y-3">
                    <textarea name="payload" rows="6" class="w-full border rounded-lg p-3 font-mono text-xs" placeholder="ENCv1:..."></textarea>
                    <button type="submit" class="px-4 py-2 bg-gray-900 text-white rounded-lg">رمزگشایی</button>
                </form>
                <details class="mt-4">
                    <summary class="cursor-pointer text-sm text-gray-700">توضیح نحوه کار</summary>
                    <p class="text-sm text-gray-600 mt-2">رمزنگاری با AES-256-GCM انجام شده و کلید از APP_SECRET در <code>includes/config.php</code> مشتق می‌شود. خروجی بعد از رمزگشایی به JSON تبدیل می‌گردد.</p>
                </details>
            </div>
        </div>
    </div>
</body>
</html>


