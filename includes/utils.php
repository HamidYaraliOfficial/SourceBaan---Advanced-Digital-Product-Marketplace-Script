<?php
// Utility helpers for SourceBaan

declare(strict_types=1);

require_once __DIR__ . '/config.php';

// --- Output escaping helper ---
if (!function_exists('e')) {
    function e(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

// --- Generic sanitizers (server-side XSS mitigation) ---
function sanitize_text_field(string $value, int $maxLength = 500): string
{
    $value = trim(strip_tags($value));
    // Collapse whitespace
    $value = preg_replace('/\s+/u', ' ', $value);
    if (!is_string($value)) { $value = ''; }
    if ($maxLength > 0 && mb_strlen($value, 'UTF-8') > $maxLength) {
        $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return $value;
}

function sanitize_multiline(string $value, int $maxLength = 5000): string
{
    // Allow newlines, strip tags
    $value = str_replace(["\r\n", "\r"], "\n", (string)strip_tags($value));
    // Normalize excessive blank lines
    $value = preg_replace("/\n{3,}/", "\n\n", $value);
    $value = trim($value);
    if ($maxLength > 0 && mb_strlen($value, 'UTF-8') > $maxLength) {
        $value = mb_substr($value, 0, $maxLength, 'UTF-8');
    }
    return $value;
}

// --- Input validators (server-side) ---
function validate_name_fa_en(string $name): bool
{
    // Persian/Arabic letters + English lowercase + spaces
    return (bool)preg_match('/^[a-z\u0600-\u06FF\s]+$/u', $name);
}

function validate_username_letters_only(string $username, int $min = 3, int $max = 30): bool
{
    if (!preg_match('/^[a-zA-Z]+$/', $username)) return false;
    $len = strlen($username);
    return $len >= $min && $len <= $max;
}

function validate_username_frontend_compatible(string $username, int $min = 3, int $max = 30): bool
{
    // Compatible with frontend validation: English letters, numbers, hyphens
    // Must start with letter, cannot end with hyphen, no consecutive hyphens
    if (!preg_match('/^[a-zA-Z0-9\-]+$/', $username)) return false;
    
    $len = strlen($username);
    if ($len < $min || $len > $max) return false;
    
    // Must start with a letter
    if (!preg_match('/^[a-zA-Z]/', $username)) return false;
    
    // Cannot end with hyphen
    if (str_ends_with($username, '-')) return false;
    
    // Cannot have consecutive hyphens
    if (str_contains($username, '--')) return false;
    
    return true;
}

function validate_username_safe(string $username, int $min = 3, int $max = 30): bool
{
    // More permissive: letters, numbers, hyphen; must start with a letter; no trailing or double hyphens
    if (!preg_match('/^[A-Za-z][A-Za-z0-9-]*$/', $username)) return false;
    if (str_ends_with($username, '-')) return false;
    if (strpos($username, '--') !== false) return false;
    $len = strlen($username);
    return $len >= $min && $len <= $max;
}

function validate_email_general(string $email): bool
{
    return (bool)filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validate_email_limited_domains(string $email, array $allowedDomains = ['gmail.com','icloud.com','yahoo.com','hotmail.com']): bool
{
    if (!validate_email_general($email)) return false;
    $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
    return in_array($domain, $allowedDomains, true);
}

function validate_phone_digits(string $phone, int $minLen = 8, int $maxLen = 15): bool
{
    if (!preg_match('/^[0-9]+$/', $phone)) return false;
    $len = strlen($phone);
    return $len >= $minLen && $len <= $maxLen;
}

function validate_password_basic(string $password, int $minLen = 8): bool
{
    return is_string($password) && strlen($password) >= $minLen;
}

function json_response($data, int $status = 200): void
{
    // Clear any accidental output (notices, HTML, etc.) so client receives pure JSON
    if (ob_get_length() !== false && ob_get_length() > 0) {
        @ob_clean();
    }
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function sanitize_filename(string $name): string
{
    $name = preg_replace('/[^A-Za-z0-9_\-.\s\p{Arabic}]/u', '_', $name);
    $name = trim($name);
    if ($name === '') {
        $name = 'file';
    }
    return $name;
}

function allowed_extensions(): array
{
    $list = array_map('trim', explode(',', ALLOWED_EXTENSIONS));
    $list = array_filter($list, fn($e) => $e !== '');
    return array_map('strtolower', $list);
}

function ensure_post(): void
{
    if (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 405);
    }
}

// Same-origin CSRF guard: blocks requests not coming from our origin (for form posts)
function ensure_same_origin(): void
{
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) return; // disable in dev for testing speed
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $referer = $_SERVER['HTTP_REFERER'] ?? '';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    if ($host === '') return; // cannot validate
    $expected = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $expected .= $host;
    // Allow if Origin matches, else fall back to Referer startsWith
    $ok = ($origin !== '' && stripos($origin, $expected) === 0) || ($referer !== '' && stripos($referer, $expected) === 0);
    if (!$ok) {
        json_response(['ok' => false, 'error' => 'CSRF: منبع درخواست معتبر نیست'], 403);
    }
}

// CSRF Token helpers
function csrf_get_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token']) || $_SESSION['csrf_token'] === '') {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return (string)$_SESSION['csrf_token'];
}

function ensure_csrf_token(): void
{
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) return;
    // Accept token from multiple sources to be robust with different clients
    $token = (string)($_POST['csrf_token'] ?? ($_POST['_token'] ?? ''));
    if ($token === '') {
        // Try custom header
        $hdr = (string)($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
        if ($hdr !== '') { $token = $hdr; }
    }
    if ($token === '') {
        // As a last resort, allow token via GET for XHRs that append it
        $get = (string)($_GET['csrf_token'] ?? '');
        if ($get !== '') { $token = $get; }
    }
    // Ensure we have an expected token; generate if missing for current session
    $expected = isset($_SESSION['csrf_token']) && is_string($_SESSION['csrf_token'])
        ? (string)$_SESSION['csrf_token']
        : csrf_get_token();
    if ($token === '' || $expected === '' || !hash_equals($expected, $token)) {
        json_response(['ok' => false, 'error' => 'CSRF: توکن نامعتبر است'], 403);
    }
}

// CAPTCHA helpers
function captcha_generate(): array
{
    if (!defined('CAPTCHA_ENABLE') || !CAPTCHA_ENABLE) {
        return ['question' => '', 'token' => ''];
    }
    // Prevent answer reuse across forms; always regenerate
    unset($_SESSION['captcha']);
    $a = random_int(2, 9);
    $b = random_int(1, 9);
    $op = ['+', '-'][random_int(0, 1)];
    if ($op === '-' && $b > $a) {
        // avoid negative results so user need not type signs
        [$a, $b] = [$b, $a];
    }
    $answer = $op === '+' ? ($a + $b) : ($a - $b);
    $token = bin2hex(random_bytes(8));
    $_SESSION['captcha'] = [
        'token' => $token,
        'answer' => (string)$answer,
        'expires_at' => time() + 300
    ];
    return ['question' => "نتیجه: {$a} {$op} {$b} = ?", 'token' => $token];
}

function captcha_verify(string $token, string $value): bool
{
    if (!defined('CAPTCHA_ENABLE') || !CAPTCHA_ENABLE) {
        return true;
    }
    $c = $_SESSION['captcha'] ?? null;
    if (!$c) return false;
    if (!hash_equals($c['token'] ?? '', $token)) return false;
    if (time() > ($c['expires_at'] ?? 0)) return false;
    // Normalize input: allow optional +/-, ignore spaces; compare numerically
    $rawInput = preg_replace('/\s+/', '', (string)$value);
    // Persian/Arabic digits normalization (optional)
    $persian = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
    $arabic =  ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    $latin  =  ['0','1','2','3','4','5','6','7','8','9'];
    $rawInput = str_replace($persian, $latin, $rawInput);
    $rawInput = str_replace($arabic, $latin, $rawInput);
    $expected = (int)($c['answer'] ?? 0);
    $ok = preg_match('/^[+-]?\d+$/', $rawInput) === 1 && (int)$rawInput === $expected;
    if ($ok) {
        // One-time use
        unset($_SESSION['captcha']);
    }
    return $ok;
}

function captcha_clear(): void
{
    unset($_SESSION['captcha']);
}

// Ban helpers
function get_client_ip(): string
{
    $keys = [
        'HTTP_CF_CONNECTING_IP', // Cloudflare
        'HTTP_X_FORWARDED_FOR',
        'HTTP_X_REAL_IP',
        'REMOTE_ADDR',
    ];
    foreach ($keys as $k) {
        $v = $_SERVER[$k] ?? '';
        if ($v) {
            // take first if list
            $ip = trim(explode(',', $v)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) return $ip;
        }
    }
    return '0.0.0.0';
}

function is_ip_banned(string $ip = ''): bool
{
    require_once __DIR__ . '/db.php';
    $ip = $ip ?: get_client_ip();
    $bans = JsonDB::read('banned_ips');
    foreach ($bans as $b) {
        if (($b['ip'] ?? '') === $ip) return true;
    }
    return false;
}

function ban_ip(string $ip, string $reason = ''): void
{
    require_once __DIR__ . '/db.php';
    $bans = JsonDB::read('banned_ips');
    // prevent duplicates
    foreach ($bans as $b) { if (($b['ip'] ?? '') === $ip) return; }
    $bans[] = [
        'id' => JsonDB::nextId(),
        'ip' => $ip,
        'reason' => $reason,
        'createdAt' => date('c')
    ];
    JsonDB::write('banned_ips', $bans);
}

function unban_ip(string $ip): void
{
    require_once __DIR__ . '/db.php';
    $bans = JsonDB::read('banned_ips');
    $bans = array_values(array_filter($bans, fn($b) => ($b['ip'] ?? '') !== $ip));
    JsonDB::write('banned_ips', $bans);
}

function suspend_user(int $userId, string $reason = ''): void
{
    require_once __DIR__ . '/db.php';
    $s = JsonDB::read('user_suspensions');
    foreach ($s as $row) { if ((int)($row['userId'] ?? 0) === $userId) return; }
    $s[] = [ 'userId' => $userId, 'reason' => $reason, 'createdAt' => date('c') ];
    JsonDB::write('user_suspensions', $s);
}

function unsuspend_user(int $userId): void
{
    require_once __DIR__ . '/db.php';
    $s = JsonDB::read('user_suspensions');
    $s = array_values(array_filter($s, fn($row) => (int)($row['userId'] ?? 0) !== $userId));
    JsonDB::write('user_suspensions', $s);
}

function is_user_suspended(?array $user): bool
{
    if (!$user) return false;
    require_once __DIR__ . '/db.php';
    $s = JsonDB::read('user_suspensions');
    foreach ($s as $row) { if ((int)($row['userId'] ?? 0) === (int)$user['id']) return true; }
    return false;
}
// Email verification functions
function store_verification_code(string $email, string $code): void
{
    require_once __DIR__ . '/db.php';
    
    $verificationCodes = JsonDB::read('verification_codes');
    
    // Remove any existing codes for this email
    $verificationCodes = array_filter($verificationCodes, function($item) use ($email) {
        return $item['email'] !== $email;
    });
    
    // Add new code with expiration (5 minutes)
    $verificationCodes[] = [
        'email' => $email,
        'code' => $code,
        'created_at' => time(),
        'expires_at' => time() + (5 * 60) // 5 minutes
    ];
    
    JsonDB::write('verification_codes', array_values($verificationCodes));
}

function verify_code(string $email, string $code): bool
{
    require_once __DIR__ . '/db.php';
    
    $verificationCodes = JsonDB::read('verification_codes');
    $currentTime = time();
    
    foreach ($verificationCodes as $item) {
        if ($item['email'] === $email && $item['code'] === $code) {
            // Check if code is not expired
            if ($currentTime <= $item['expires_at']) {
                return true;
            }
        }
    }
    
    return false;
}

function remove_verification_code(string $email): void
{
    require_once __DIR__ . '/db.php';
    
    $verificationCodes = JsonDB::read('verification_codes');
    
    // Remove codes for this email
    $verificationCodes = array_filter($verificationCodes, function($item) use ($email) {
        return $item['email'] !== $email;
    });
    
    JsonDB::write('verification_codes', array_values($verificationCodes));
}

function clean_expired_codes(): void
{
    require_once __DIR__ . '/db.php';
    
    $verificationCodes = JsonDB::read('verification_codes');
    $currentTime = time();
    
    // Remove expired codes
    $verificationCodes = array_filter($verificationCodes, function($item) use ($currentTime) {
        return $currentTime <= $item['expires_at'];
    });
    
    JsonDB::write('verification_codes', array_values($verificationCodes));
}

function send_verification_email(string $email, string $code): bool
{
    // Always fetch the latest code for this email from data/verification_codes.json as requested
    try {
        require_once __DIR__ . '/db.php';
        $verificationCodes = JsonDB::read('verification_codes');
        $latest = null;
        foreach ($verificationCodes as $item) {
            if (($item['email'] ?? '') === $email) {
                if ($latest === null || (int)($item['created_at'] ?? 0) > (int)($latest['created_at'] ?? 0)) {
                    $latest = $item;
                }
            }
        }
        if ($latest && time() <= (int)($latest['expires_at'] ?? 0)) {
            $code = (string)($latest['code'] ?? $code);
        }
    } catch (Throwable $e) {
        // ignore and use provided $code
    }

    $subject = "کد تایید ثبت نام در SourceBaan";
    $message = "
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: 'Tahoma', Arial, sans-serif; direction: rtl; text-align: right; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: white; padding: 30px; border-radius: 0 0 10px 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .code { font-size: 32px; font-weight: bold; color: #667eea; text-align: center; letter-spacing: 5px; margin: 20px 0; padding: 15px; background: #f1f3f4; border-radius: 8px; }
            .footer { text-align: center; margin-top: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🚀 SourceBaan</h1>
                <p>خوش آمدید به جامعه برنامه‌نویسان</p>
            </div>
            <div class='content'>
                <h2>تایید ایمیل شما</h2>
                <p>سلام و خوش آمدید!</p>
                <p>برای تکمیل فرآیند ثبت نام، لطفاً کد زیر را در صفحه ثبت نام وارد کنید:</p>
                
                <div class='code'>{$code}</div>
                
                <p><strong>توجه:</strong> این کد تا 5 دقیقه معتبر است.</p>
                <p>اگر شما درخواست ثبت نام نداده‌اید، این ایمیل را نادیده بگیرید.</p>
                
                <hr style='margin: 30px 0; border: none; border-top: 1px solid #eee;'>
                <p>با تشکر،<br>تیم SourceBaan</p>
            </div>
            <div class='footer'>
                <p>این ایمیل به صورت خودکار ارسال شده است. لطفاً پاسخ ندهید.</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $fromEmail = defined('SMTP_FROM_EMAIL') && SMTP_FROM_EMAIL ? SMTP_FROM_EMAIL : ('noreply@' . ($_SERVER['SERVER_NAME'] ?? 'sourcebaan.local'));
    $fromName  = defined('SMTP_FROM_NAME') && SMTP_FROM_NAME ? SMTP_FROM_NAME : (defined('SITE_NAME') ? SITE_NAME : 'SourceBaan');
    $replyTo   = $fromEmail;
    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $fromName . ' <' . $fromEmail . '>',
        'Reply-To: ' . $replyTo,
        'X-Mailer: PHP/' . phpversion()
    ];
    
    // For development/testing, you might want to log emails instead of sending them
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) {
        error_log("VERIFICATION EMAIL: To: {$email}, Code: {$code}");
        return true;
    }

    // Try Resend API first (as requested)
    try {
        if (!defined('RESEND_API_KEY')) {
            define('RESEND_API_KEY', 're_eRiJNkhn_LqNkj2sH8v9iSuUT8frHG7jE');
        }
        if (!defined('RESEND_API_URL')) {
            define('RESEND_API_URL', 'https://api.resend.com');
        }
        $payload = [
            'from' => $fromName . ' <' . $fromEmail . '>',
            'to' => [$email],
            'subject' => $subject,
            'html' => $message
        ];
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => rtrim(RESEND_API_URL, '/') . '/emails',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_CONNECTTIMEOUT => 8,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . RESEND_API_KEY,
                'Content-Type: application/json'
            ],
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);
        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);
        if ($err === '' && $http >= 200 && $http < 300) {
            return true;
        }
        // If Resend failed, continue to SMTP/mail fallback
    } catch (Throwable $e) {
        // ignore and fallback
    }

    // Try SMTP if enabled
    if (defined('SMTP_ENABLE') && SMTP_ENABLE) {
        // Minimal SMTP client using fsockopen (no external deps)
        $result = smtp_send_html($email, $subject, $message, $fromEmail, $fromName, $headers);
        if ($result) return true;
        // Fallback to PHP mail if SMTP failed
    }

    // Try PHP mail with envelope sender to improve deliverability on cPanel
    $additionalParams = '';
    if (filter_var($fromEmail, FILTER_VALIDATE_EMAIL)) {
        $additionalParams = '-f ' . escapeshellarg($fromEmail);
    }
    $sent = @mail($email, encode_mime_header($subject), $message, implode("\r\n", $headers), $additionalParams);
    if (!$sent) {
        // Log to outbox for troubleshooting
        require_once __DIR__ . '/db.php';
        $existing = JsonDB::read('mail_outbox');
        $existing[] = [
            'id' => JsonDB::nextId(),
            'to' => $email,
            'subject' => $subject,
            'body' => $message,
            'headers' => $headers,
            'sent' => false,
            'error' => 'mail() returned false',
            'created_at' => time()
        ];
        JsonDB::write('mail_outbox', $existing);
    }
    return $sent;
}

/**
 * Very small SMTP sender (AUTH LOGIN + SSL/TLS) without external libraries.
 * For production, consider PHPMailer. This is enough for basic sending.
 */
function smtp_send_html(
    string $to,
    string $subject,
    string $html,
    string $fromEmail,
    string $fromName,
    array $headers
): bool {
    $host = SMTP_HOST;
    $port = SMTP_PORT;
    $secure = strtolower(SMTP_SECURE);
    $username = SMTP_USERNAME;
    $password = SMTP_PASSWORD;

    $contextOptions = [];
    if ($secure === 'ssl') {
        $host = 'ssl://' . $host;
    }

    $fp = @fsockopen($host, $port, $errno, $errstr, 15);
    if (!$fp) {
        return false;
    }
    $read = function() use ($fp) { return fgets($fp, 515); };
    $write = function($cmd) use ($fp) { fwrite($fp, $cmd . "\r\n"); };

    $greeting = $read();
    if (strpos($greeting, '220') !== 0) { fclose($fp); return false; }

    $write('EHLO sourcebaan.local');
    $ehlo = '';
    while ($line = $read()) { $ehlo .= $line; if (strpos($line, '250 ') === 0) break; }

    if ($secure === 'tls') {
        $write('STARTTLS');
        $resp = $read();
        if (strpos($resp, '220') !== 0) { fclose($fp); return false; }
        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) { fclose($fp); return false; }
        $write('EHLO sourcebaan.local');
        while ($line = $read()) { if (strpos($line, '250 ') === 0) break; }
    }

    // AUTH LOGIN
    if ($username !== '') {
        $write('AUTH LOGIN');
        if (strpos($read(), '334') !== 0) { fclose($fp); return false; }
        $write(base64_encode($username));
        if (strpos($read(), '334') !== 0) { fclose($fp); return false; }
        $write(base64_encode($password));
        if (strpos($read(), '235') !== 0) { fclose($fp); return false; }
    }

    $fromHeader = sprintf('From: %s <%s>', $fromName, $fromEmail);
    $headersOut = array_merge($headers, [$fromHeader]);
    $boundaryHeaders = implode("\r\n", $headersOut);

    $write('MAIL FROM: <' . $fromEmail . '>');
    if (strpos($read(), '250') !== 0) { fclose($fp); return false; }
    $write('RCPT TO: <' . $to . '>');
    if (strpos($read(), '250') !== 0) { fclose($fp); return false; }
    $write('DATA');
    if (strpos($read(), '354') !== 0) { fclose($fp); return false; }

    $data  = 'Subject: ' . encode_mime_header($subject) . "\r\n";
    $data .= 'To: <' . $to . '>' . "\r\n";
    $data .= $boundaryHeaders . "\r\n\r\n";
    $data .= $html . "\r\n.\r\n";
    $write($data);

    if (strpos($read(), '250') !== 0) { fclose($fp); return false; }
    $write('QUIT');
    fclose($fp);
    return true;
}

function encode_mime_header(string $text): string {
    // Encode UTF-8 subject
    return '=?UTF-8?B?' . base64_encode($text) . '?=';
}

// Robust MIME type detection with graceful fallbacks
function get_mime_type(string $filePath): string
{
    // Prefer mime_content_type if available
    if (function_exists('mime_content_type')) {
        $type = @mime_content_type($filePath);
        if (is_string($type) && $type !== '') { return $type; }
    }
    // Try finfo if available
    if (function_exists('finfo_open')) {
        $fi = @finfo_open(FILEINFO_MIME_TYPE);
        if ($fi) {
            $type = @finfo_file($fi, $filePath);
            @finfo_close($fi);
            if (is_string($type) && $type !== '') { return $type; }
        }
    }
    // Fallback by extension
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
    $map = [
        'zip' => 'application/zip',
        'rar' => 'application/x-rar-compressed',
        '7z'  => 'application/x-7z-compressed',
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'json'=> 'application/json',
        'xml' => 'application/xml',
        'html'=> 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js'  => 'application/javascript',
        'mjs' => 'application/javascript',
        'php' => 'text/x-php',
        'py'  => 'text/x-python',
        'java'=> 'text/x-java-source',
        'cpp' => 'text/x-c++src',
        'c'   => 'text/x-c',
        'jpg' => 'image/jpeg',
        'jpeg'=> 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp'=> 'image/webp',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
    ];
    return $map[$ext] ?? 'application/octet-stream';
}

// Signed URL helpers
function sign_url_params(array $params, int $ttlSeconds = 600): array
{
    // Add expiry
    $params['exp'] = (string)(time() + max(60, $ttlSeconds));
    // Sort params for deterministic signature
    ksort($params);
    $base = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    $sig = hash_hmac('sha256', $base, APP_SECRET);
    $params['sig'] = $sig;
    return $params;
}

function verify_signed_params(array $params): bool
{
    $sig = (string)($params['sig'] ?? '');
    $exp = (int)($params['exp'] ?? 0);
    if ($sig === '' || $exp <= 0 || time() > $exp) {
        return false;
    }
    $copy = $params;
    unset($copy['sig']);
    ksort($copy);
    $base = http_build_query($copy, '', '&', PHP_QUERY_RFC3986);
    $expected = hash_hmac('sha256', $base, APP_SECRET);
    return hash_equals($expected, $sig);
}

function build_signed_url(string $path, array $params, int $ttlSeconds = 600): string
{
    $signed = sign_url_params($params, $ttlSeconds);
    $query = http_build_query($signed, '', '&', PHP_QUERY_RFC3986);
    return $path . (strpos($path, '?') === false ? '?' : '&') . $query;
}

// SEO-friendly URL helpers
function create_slug(string $text): string
{
    // Convert to lowercase
    $text = mb_strtolower($text, 'UTF-8');
    
    // Replace Persian/Arabic numbers with English
    $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
    $englishNumbers = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
    $text = str_replace($persianNumbers, $englishNumbers, $text);
    
    // Replace Arabic numbers with English
    $arabicNumbers = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    $text = str_replace($arabicNumbers, $englishNumbers, $text);
    
    // Replace common words for better SEO
    $replacements = [
        'دانلود' => 'download',
        'رایگان' => 'free',
        'سورس' => 'source',
        'کد' => 'code',
        'پروژه' => 'project',
        'اسکریپت' => 'script',
        'برنامه' => 'app',
        'وب سایت' => 'website',
        'ربات' => 'bot',
        'تلگرام' => 'telegram',
        'اینستاگرام' => 'instagram'
    ];
    
    foreach ($replacements as $persian => $english) {
        $text = str_replace($persian, $english, $text);
    }
    
    // Remove unwanted characters, keep Persian/Arabic letters, English letters, numbers, and spaces
    $text = preg_replace('/[^\p{L}\p{N}\s\-_]/u', ' ', $text);
    
    // Replace multiple spaces/underscores with single hyphen
    $text = preg_replace('/[\s_]+/', '-', $text);
    
    // Remove multiple consecutive hyphens
    $text = preg_replace('/-+/', '-', $text);
    
    // Trim hyphens from start and end
    $text = trim($text, '-');
    
    // Limit length
    if (mb_strlen($text, 'UTF-8') > 100) {
        $text = mb_substr($text, 0, 100, 'UTF-8');
        $text = rtrim($text, '-');
    }
    
    return $text ?: 'project';
}

function build_project_pretty_path(int $id, string $title): string
{
    $slug = create_slug($title);
    return "/project/{$id}-{$slug}";
}

function build_product_pretty_path(int $id, string $title): string
{
    $slug = create_slug($title);
    return "/فروشگاه-{$id}-{$slug}";
}

function parse_project_slug(string $slug): ?int
{
    // Extract ID from slug like "123-project-name"
    if (preg_match('/^(\d+)-/', $slug, $matches)) {
        return (int)$matches[1];
    }
    return null;
}

function parse_product_slug(string $slug): ?int
{
    // Extract ID from slug like "فروشگاه-123-product-name"
    if (preg_match('/^(?:فروشگاه-)?(\d+)-/', $slug, $matches)) {
        return (int)$matches[1];
    }
    return null;
}