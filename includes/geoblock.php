<?php
// Geo-block helper: allow only requests from Iran (IR)

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Allow verified search engine crawlers to bypass geo restrictions.
 * Verifies by reverse DNS and forward DNS to prevent UA spoofing.
 */
function is_verified_bot(): bool
{
    $userAgent = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
    if ($userAgent === '') {
        return false;
    }

    $ip = (string)($_SERVER['REMOTE_ADDR'] ?? '');
    if ($ip === '' || !filter_var($ip, FILTER_VALIDATE_IP)) {
        return false;
    }

    $botDomains = [];
    if (stripos($userAgent, 'Googlebot') !== false || stripos($userAgent, 'AdsBot-Google') !== false || stripos($userAgent, 'Google-InspectionTool') !== false) {
        $botDomains = ['.googlebot.com', '.google.com'];
    } elseif (stripos($userAgent, 'bingbot') !== false || stripos($userAgent, 'BingPreview') !== false) {
        $botDomains = ['.search.msn.com'];
    } else {
        return false;
    }

    // Reverse lookup
    $host = @gethostbyaddr($ip);
    if (!is_string($host) || $host === '' || $host === $ip) {
        return false;
    }

    // Ensure PTR ends with an allowed domain
    $hostLower = strtolower($host);
    $endsWithAllowed = false;
    foreach ($botDomains as $suffix) {
        if (str_ends_with($hostLower, $suffix)) { $endsWithAllowed = true; break; }
    }
    if (!$endsWithAllowed) {
        return false;
    }

    // Forward resolve the hostname and ensure the original IP is one of the A/AAAA records
    $resolvedIps = [];
    $records = @dns_get_record($host, DNS_A + DNS_AAAA);
    if (is_array($records)) {
        foreach ($records as $rec) {
            if (!empty($rec['ip']) && filter_var($rec['ip'], FILTER_VALIDATE_IP)) {
                $resolvedIps[] = $rec['ip'];
            }
            if (!empty($rec['ipv6']) && filter_var($rec['ipv6'], FILTER_VALIDATE_IP)) {
                $resolvedIps[] = $rec['ipv6'];
            }
        }
    }
    if (empty($resolvedIps)) {
        // Fallback to gethostbyname if dns_get_record fails for A only
        $aOnly = @gethostbyname($host);
        if ($aOnly && $aOnly !== $host) { $resolvedIps[] = $aOnly; }
    }

    return in_array($ip, $resolvedIps, true);
}

function geo_block_if_not_iran(): void
{
    // Always allow verified search engine crawlers
    if (is_verified_bot()) return;
    if (defined('DEVELOPMENT_MODE') && DEVELOPMENT_MODE) return; // skip in dev
    if (!defined('GEO_BLOCK_ENABLE') || GEO_BLOCK_ENABLE !== true) return;

    // Allow localhost/internal
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    if ($remote === '127.0.0.1' || $remote === '::1') return;

    // If behind Cloudflare, use their country header
    $cfCountry = $_SERVER['HTTP_CF_IPCOUNTRY'] ?? '';
    if ($cfCountry !== '') {
        if (strtoupper($cfCountry) === (GEO_ALLOWED_COUNTRY ?? 'IR')) return;
        deny_geo();
    }

    // Resolve client IP (best-effort)
    $ip = '0.0.0.0';
    foreach (['HTTP_CF_CONNECTING_IP','HTTP_X_FORWARDED_FOR','HTTP_X_REAL_IP','REMOTE_ADDR'] as $k) {
        $v = $_SERVER[$k] ?? '';
        if ($v) { $try = trim(explode(',', $v)[0]); if (filter_var($try, FILTER_VALIDATE_IP)) { $ip = $try; break; } }
    }
    if ($ip === '0.0.0.0') return; // cannot determine; allow

    $country = geo_lookup_country_cached($ip);
    if ($country === null) return; // on lookup failure, be permissive
    if (strtoupper($country) === (GEO_ALLOWED_COUNTRY ?? 'IR')) return;
    deny_geo();
}

function geo_lookup_country_cached(string $ip): ?string
{
    $ttl = 86400; // 1 day
    $key = 'geo:' . $ip;

    // APCu preferred
    if (function_exists('apcu_fetch') && function_exists('apcu_store')) {
        $ok = false;
        $val = apcu_fetch($key, $ok);
        if ($ok && is_string($val)) { return $val; }
        $country = geo_lookup_country_http($ip);
        if (is_string($country)) apcu_store($key, $country, $ttl);
        return $country;
    }

    // File cache fallback
    $file = rtrim(DATA_DIR, '/\\') . DIRECTORY_SEPARATOR . 'geo_cache.json';
    $now = time();
    $map = [];
    $raw = @file_get_contents($file);
    if ($raw) { $tmp = json_decode($raw, true); if (is_array($tmp)) { $map = $tmp; } }
    $entry = $map[$ip] ?? null;
    if (is_array($entry) && (int)($entry['exp'] ?? 0) > $now && is_string($entry['cc'] ?? null)) {
        return $entry['cc'];
    }
    $cc = geo_lookup_country_http($ip);
    if (is_string($cc)) {
        $map[$ip] = [ 'cc' => $cc, 'exp' => $now + $ttl ];
        // best-effort eviction
        $i = 0; foreach ($map as $k => $v) { if ((int)($v['exp'] ?? 0) <= $now) { unset($map[$k]); if (++$i > 50) break; } }
        @file_put_contents($file, json_encode($map, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }
    return $cc;
}

function geo_lookup_country_http(string $ip): ?string
{
    // Use ip-api.com (free) minimal fields. Timeout small to avoid latency.
    $url = 'http://ip-api.com/json/' . urlencode($ip) . '?fields=status,countryCode';
    $ctx = stream_context_create([
        'http' => [ 'timeout' => 1.0 ]
    ]);
    $res = @file_get_contents($url, false, $ctx);
    if (!$res) return null;
    $j = json_decode($res, true);
    if (!is_array($j)) return null;
    if (($j['status'] ?? '') !== 'success') return null;
    $cc = (string)($j['countryCode'] ?? '');
    return $cc !== '' ? strtoupper($cc) : null;
}

function deny_geo(): void
{
    http_response_code(403);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="fa"><head><meta charset="utf-8"><title>دسترسی محدود</title></head><body style="font-family:tahoma,arial;direction:rtl;text-align:center;padding:40px"><h2>دسترسی محدود</h2><p>این سرویس فقط برای کاربران داخل ایران در دسترس است.</p></body></html>';
    exit;
}

?>


