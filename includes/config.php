<?php
// Core configuration for SourceBaan

declare(strict_types=1);

// Site
const SITE_NAME = 'SourceBaan';

// Paths
const BASE_PATH = __DIR__ . '/../';
const DATA_DIR = BASE_PATH . 'data';
const UPLOADS_DIR = BASE_PATH . 'uploads';
const PENDING_DIR = UPLOADS_DIR . '/pending';
const APPROVED_DIR = UPLOADS_DIR . '/approved';
const IMAGES_DIR = UPLOADS_DIR . '/images';

// Upload constraints
const MAX_UPLOAD_BYTES = 1048576; // 1MB
const ALLOWED_EXTENSIONS = 'zip,rar,js,py,php,java,cpp,html,css,txt';

// Default admin (will be created if not present)
const DEFAULT_ADMIN_EMAIL = 'admin@sourcebaan.local';
const DEFAULT_ADMIN_PASSWORD = 'SourceBaan@123';
const DEFAULT_ADMIN_NAME = 'Admin';

// Development mode (set to false in production)
const DEVELOPMENT_MODE = false;

// Storage mode: true => encrypt with ENCv1 (AES-256-GCM), false => plaintext JSON (pretty)
if (!defined('JSONDB_ENCRYPT')) {
    define('JSONDB_ENCRYPT', false);
}

// SMTP email settings (cPanel/Exim on same server)
// If this app runs on your cPanel host, using localhost:25 usually sends via Exim without auth.
const SMTP_ENABLE = true;
const SMTP_HOST = 'localhost';
const SMTP_PORT = 25; // no encryption
const SMTP_SECURE = '';
const SMTP_USERNAME = '';
const SMTP_PASSWORD = '';
const SMTP_FROM_EMAIL = 'noreply@sourcebaan.ir';
const SMTP_FROM_NAME = 'SourceBaan';

// If email cannot be sent, show verification code in UI (small sites)
const ALLOW_EMAIL_FALLBACK_SHOW_CODE = true;

// Google OAuth settings (get from Google Cloud Console)
const GOOGLE_OAUTH_ENABLE = false;
const GOOGLE_CLIENT_ID = '1234567890-abcdefghijklmnopqrstuvwxyz.apps.googleusercontent.com'; // Replace with your Google Client ID
const GOOGLE_CLIENT_SECRET = 'GOCSPX-abcdefghijklmnopqrstuvwxyz'; // Replace with your Google Client Secret
const GOOGLE_REDIRECT_URI = 'https://sourcebaan.ir/auth-google.php'; // OAuth callback URL

// CAPTCHA
const CAPTCHA_ENABLE = true; // set false to disable
const CAPTCHA_TYPE = 'math'; // future: 'image'

// VirusTotal integration
// You can override via environment variable APP_VT_API_KEY
if (!defined('VIRUSTOTAL_API_KEY')) {
    $vtEnv = getenv('APP_VT_API_KEY');
    // Default to provided key if env not set
    define('VIRUSTOTAL_API_KEY', is_string($vtEnv) && $vtEnv !== '' ? $vtEnv : '03f75def0a1f9feb5b284d76bfa93a77ae01b2c0bb7acb6103d60e464defed63');
}
if (!defined('VIRUSTOTAL_ENABLE')) {
    define('VIRUSTOTAL_ENABLE', true);
}
if (!defined('VIRUSTOTAL_LOG')) {
    define('VIRUSTOTAL_LOG', true);
}

// Application secret for signing (load from env/file or generate once)
if (!defined('APP_SECRET')) {
    $envSecret = getenv('APP_SECRET') ?: '';
    if (is_string($envSecret) && $envSecret !== '') {
        define('APP_SECRET', $envSecret);
    } else {
        $secretFile = DATA_DIR . '/app_secret.txt';
        $secret = '';
        if (is_file($secretFile)) {
            $secret = trim((string)@file_get_contents($secretFile));
        }
        if ($secret === '') {
            try { $secret = bin2hex(random_bytes(32)); } catch (Throwable $e) { $secret = sha1(uniqid('', true)); }
            @file_put_contents($secretFile, $secret);
        }
        define('APP_SECRET', $secret);
    }
}

// Geo-block config
const GEO_BLOCK_ENABLE = false; // set true to enable country allowlist
const GEO_ALLOWED_COUNTRY = 'IR'; // ISO country code to allow

// Basic app-level rate limiting (anti-DDoS best-effort)
const RATE_LIMIT_ENABLE = false; // set false to disable
const RATE_LIMIT_WINDOW_SECONDS = 60; // sliding window per IP
const RATE_LIMIT_MAX_REQUESTS = 120; // allowed requests per IP per window

// Harden session cookies (set BEFORE session_start)
if (session_status() !== PHP_SESSION_ACTIVE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    $cookieParams = session_get_cookie_params();
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookieParams['path'] ?? '/',
        'domain' => $cookieParams['domain'] ?? '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// Canonical host + HTTPS redirect (SEO)
// Enforce https://sourcebaan.ir for all public requests
if (!DEVELOPMENT_MODE) {
    $currentHost = (string)($_SERVER['HTTP_HOST'] ?? '');
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
    // Canonical host for SEO and HTTPS enforcement.
    // You can override this by setting an environment variable `APP_HOST` on your server (recommended),
    // or edit the default value below to match your domain.
    $canonicalHost = getenv('APP_HOST') ?: 'bildingbottelegram.sbs';
    if ($currentHost !== '' && ($currentHost !== $canonicalHost || !$isHttps)) {
        $requestUri = (string)($_SERVER['REQUEST_URI'] ?? '/');
        // Avoid redirect loops in CLI or internal scripts
        if (php_sapi_name() !== 'cli') {
            // Do NOT redirect API requests to avoid fetch JSON parse failures on client
            $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
            $isApi = (stripos($scriptName, '/api/') !== false) || (stripos($requestUri, '/api/') !== false);
            if (!$isApi) {
                header('Location: https://' . $canonicalHost . $requestUri, true, 301);
                exit;
            }
        }
    }
}

// Global security headers (best-effort; do not override per-page policies)
@header('X-Frame-Options: DENY');
@header('X-Content-Type-Options: nosniff');
@header('Referrer-Policy: strict-origin-when-cross-origin');
@header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
// HSTS (only when HTTPS and not in dev)
if (!DEVELOPMENT_MODE && (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')) {
    @header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}
// Basic CSP allowing required CDNs used by the app
$__isHttpsNow = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (($_SERVER['SERVER_PORT'] ?? '') === '443');
$__csp = "default-src 'self' data: blob: https:; "
       . "img-src 'self' data: https:; "
       . "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com https://cdn.jsdelivr.net https://static.cloudflareinsights.com https://*.cloudflareinsights.com; "
       . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdnjs.cloudflare.com; "
       . "font-src 'self' data: https://fonts.gstatic.com https://cdnjs.cloudflare.com; "
       . "connect-src 'self' https: https://static.cloudflareinsights.com https://*.cloudflareinsights.com; "
       . "frame-ancestors 'none'";
if ($__isHttpsNow) { $__csp .= '; upgrade-insecure-requests'; }
@header('Content-Security-Policy: ' . $__csp);
// Keep X-Frame-Options for defense-in-depth (frame-ancestors CSP also set)

// Add X-Robots-Tag for non-public areas
try {
    $scriptName = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    $isApiOrAdmin = (stripos($scriptName, '/api/') !== false) || (stripos($scriptName, '/admin/') !== false);
    if ($isApiOrAdmin) {
        @header('X-Robots-Tag: noindex, nofollow');
    }
} catch (Throwable $e) { /* ignore */ }
// Geo-block (optional)
if (defined('GEO_BLOCK_ENABLE') && GEO_BLOCK_ENABLE === true) {
    require_once __DIR__ . '/geoblock.php';
    geo_block_if_not_iran();
}

// Lightweight per-IP rate limiter (best-effort; use a CDN/WAF for robust protection)
if (RATE_LIMIT_ENABLE && !DEVELOPMENT_MODE) {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $script = (string)($_SERVER['SCRIPT_NAME'] ?? '');
    // Apply limiter mainly to POST requests and API endpoints to reduce overhead on public pages
    $applyLimiter = ($method === 'POST') || (strpos($script, '/api/') !== false);
    if ($applyLimiter) {
        $keys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ];
        $ip = '0.0.0.0';
        foreach ($keys as $k) {
            $v = $_SERVER[$k] ?? '';
            if ($v) { $try = trim(explode(',', $v)[0]); if (filter_var($try, FILTER_VALIDATE_IP)) { $ip = $try; break; } }
        }
        $now = time();
        $bucket = [ 't' => $now, 'c' => 0 ];
        // Prefer APCu (fast, memory-based)
        if (function_exists('apcu_fetch') && function_exists('apcu_store')) {
            $key = 'rl:' . $ip;
            $entry = apcu_fetch($key);
            if (!is_array($entry)) { $entry = $bucket; }
            if (($now - (int)($entry['t'] ?? 0)) >= RATE_LIMIT_WINDOW_SECONDS) {
                $entry = [ 't' => $now, 'c' => 1 ];
            } else {
                $entry['c'] = (int)($entry['c'] ?? 0) + 1;
            }
            apcu_store($key, $entry, RATE_LIMIT_WINDOW_SECONDS * 2);
            if ((int)$entry['c'] > RATE_LIMIT_MAX_REQUESTS) {
                http_response_code(429);
                header('Retry-After: ' . RATE_LIMIT_WINDOW_SECONDS);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'تعداد درخواست‌های شما زیاد است. لطفاً بعداً تلاش کنید.';
                exit;
            }
        } else {
            // File-based fallback (only triggers on POST/API)
            $rateFile = DATA_DIR . '/rate_limits.json';
            $map = [];
            $raw = @file_get_contents($rateFile);
            if ($raw) { $tmp = json_decode($raw, true); if (is_array($tmp)) { $map = $tmp; } }
            $entry = $map[$ip] ?? $bucket;
            if (!is_array($entry)) { $entry = $bucket; }
            if (($now - (int)($entry['t'] ?? 0)) >= RATE_LIMIT_WINDOW_SECONDS) {
                $entry = [ 't' => $now, 'c' => 1 ];
            } else {
                $entry['c'] = (int)($entry['c'] ?? 0) + 1;
            }
            $map[$ip] = $entry;
            foreach ($map as $k => $v) { if (($now - (int)($v['t'] ?? 0)) > (RATE_LIMIT_WINDOW_SECONDS * 2)) { unset($map[$k]); } }
            // Write once every few hits to reduce I/O
            if (((int)$entry['c'] % 3) === 1) {
                @file_put_contents($rateFile, json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }
            if ((int)$entry['c'] > RATE_LIMIT_MAX_REQUESTS) {
                http_response_code(429);
                header('Retry-After: ' . RATE_LIMIT_WINDOW_SECONDS);
                header('Content-Type: text/plain; charset=utf-8');
                echo 'تعداد درخواست‌های شما زیاد است. لطفاً بعداً تلاش کنید.';
                exit;
            }
        }
    }
}

// Skip directory creation in production to avoid timeout
// Ensure directories exist
foreach ([DATA_DIR, UPLOADS_DIR, PENDING_DIR, APPROVED_DIR, IMAGES_DIR] as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0775, true);
    }
}

// Skip .htaccess generation to avoid timeout
// Ensure protection files even in production (best-effort)
// Basic security .htaccess for uploads (may be overridden by hosting)
$uploadsHtaccess = UPLOADS_DIR . '/.htaccess';
if (!file_exists($uploadsHtaccess)) {
    @file_put_contents($uploadsHtaccess, "Options -Indexes\nphp_flag engine off\nRemoveHandler .php .phtml .php3 .php4 .php5 .php7 .php8\n");
}

// Protect data directory from direct access
$dataHtaccess = DATA_DIR . '/.htaccess';
if (!file_exists($dataHtaccess)) {
    @file_put_contents($dataHtaccess, "Deny from all\n");
}


