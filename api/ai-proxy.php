<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';

// Security headers
@header('X-Frame-Options: DENY');
@header('X-Content-Type-Options: nosniff');
@header('Referrer-Policy: strict-origin-when-cross-origin');
@header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
@header('Content-Type: application/json; charset=UTF-8');
@header('Cache-Control: no-cache, no-store, must-revalidate');
@header('Pragma: no-cache');
@header('Expires: 0');

// Only POST allowed
if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    http_response_code(405);
    echo json_encode([ 'success' => false, 'error' => 'Method Not Allowed' ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Basic JSON body parsing
$raw = file_get_contents('php://input');
$payload = json_decode($raw, true);
$model = (string)($payload['model'] ?? '');
$text = (string)($payload['text'] ?? '');

if ($model === '' || $text === '') {
    http_response_code(400);
    echo json_encode([ 'success' => false, 'error' => 'پارامترهای نامعتبر' ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Per-IP 10s throttle (align with upstream API)
$ipKeys = [ 'HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'REMOTE_ADDR' ];
$clientIp = '0.0.0.0';
foreach ($ipKeys as $k) {
    $v = $_SERVER[$k] ?? '';
    if ($v) { $try = trim(explode(',', $v)[0]); if (filter_var($try, FILTER_VALIDATE_IP)) { $clientIp = $try; break; } }
}

$rateFile = DATA_DIR . '/ai_rate_limits.json';
$now = time();
$window = 10; // seconds
$map = [];
$rawMap = @file_get_contents($rateFile);
if ($rawMap) { $tmp = json_decode($rawMap, true); if (is_array($tmp)) { $map = $tmp; } }
$entry = $map[$clientIp] ?? [ 't' => 0 ];
if (($now - (int)($entry['t'] ?? 0)) < $window) {
    $remaining = $window - ($now - (int)($entry['t'] ?? 0));
    http_response_code(429);
    echo json_encode([ 'success' => false, 'error' => 'لطفاً ' . $remaining . ' ثانیه صبر کنید' ], JSON_UNESCAPED_UNICODE);
    exit;
}

// Prepare upstream request
try {
    if ($model === 'copilot') {
        // MajidAPI Copilot
        $token = 'ykd3nrsa6krirdl:NMuIFTe05YU0ur044mCs';
        $url = 'https://api.majidapi.ir/ai/copilot?q=' . rawurlencode($text) . '&token=' . rawurlencode($token);
        $resp = ai_proxy_fetch($url);
    } elseif ($model === 'gpt4') {
        // fast-creat GPT4
        $apiKey = '7135477742:xpbZ0YO92loHaRu@Api_ManagerRoBot';
        $url = 'https://api.fast-creat.ir/gpt/gpt4?apikey=' . rawurlencode($apiKey) . '&text=' . rawurlencode($text);
        $resp = ai_proxy_fetch($url);
    } else {
        http_response_code(400);
        echo json_encode([ 'success' => false, 'error' => 'مدل نامعتبر' ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    if (!$resp['ok']) {
        http_response_code($resp['status'] ?: 502);
        echo json_encode([ 'success' => false, 'error' => $resp['error'] ?: ('HTTP ' . ($resp['status'] ?: 502)) ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Extract only the text result from upstream response
    $rawBody = (string)$resp['body'];
    $finalText = '';
    $decoded = json_decode($rawBody, true);
    if (is_array($decoded)) {
        // fast-creat style: { ok, status, result: string|object }
        if (array_key_exists('result', $decoded)) {
            $result = $decoded['result'];
            if (is_string($result)) {
                $finalText = $result;
            } elseif (is_array($result)) {
                // { title?, text: string|array }
                if (array_key_exists('text', $result)) {
                    if (is_string($result['text'])) {
                        $finalText = $result['text'];
                    } elseif (is_array($result['text'])) {
                        $finalText = implode("\n\n", array_map('strval', $result['text']));
                    }
                }
            }
        }
        // majidapi style: { status: 200, result: "..." }
        if ($finalText === '' && array_key_exists('text', $decoded)) {
            if (is_string($decoded['text'])) { $finalText = $decoded['text']; }
            elseif (is_array($decoded['text'])) { $finalText = implode("\n\n", array_map('strval', $decoded['text'])); }
        }
        if ($finalText === '' && array_key_exists('message', $decoded) && is_string($decoded['message'])) {
            $finalText = $decoded['message'];
        }
        if ($finalText === '' && array_key_exists('status', $decoded) && array_key_exists('result', $decoded) && is_string($decoded['result'])) {
            $finalText = $decoded['result'];
        }
        if ($finalText === '') {
            // fallback: stringify cleaned JSON
            $finalText = trim(strip_tags($rawBody));
        }
    } else {
        // Non-JSON body; return as-is (text)
        $finalText = trim($rawBody);
    }

    // Update rate time on success only
    $map[$clientIp] = [ 't' => $now ];
    // Best-effort write; ignore failures
    @file_put_contents($rateFile, json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

    echo json_encode([ 'success' => true, 'data' => $finalText ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([ 'success' => false, 'error' => 'خطای داخلی سرور' ], JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Perform a simple GET request to upstream API.
 * Returns: [ ok(bool), status(int), body(string), error(string) ]
 */
function ai_proxy_fetch(string $url): array
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_CONNECTTIMEOUT => 8,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Accept: */*',
            'User-Agent: SourceBaan-AI-Proxy/1.0'
        ],
        CURLOPT_ENCODING => '' // allow gzip/deflate
    ]);
    $body = curl_exec($ch);
    $err = curl_error($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    curl_close($ch);

    if ($err) {
        return [ 'ok' => false, 'status' => 0, 'body' => '', 'error' => $err ];
    }
    if ($status < 200 || $status >= 300) {
        return [ 'ok' => false, 'status' => $status, 'body' => (string)$body, 'error' => '' ];
    }
    return [ 'ok' => true, 'status' => $status, 'body' => (string)$body, 'error' => '' ];
}


