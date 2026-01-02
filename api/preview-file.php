<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/scanner.php';

if (!is_admin()) {
    json_response(['ok' => false, 'error' => 'فقط ادمین‌ها اجازه دسترسی دارند'], 403);
}

$action = $_GET['action'] ?? '';
$fileId = $_GET['fileId'] ?? '';
$submissionId = (int)($_GET['submissionId'] ?? 0);

try {
    if ($action === 'download' && $submissionId > 0) {
        // دانلود فایل pending برای بررسی ادمین
        $submissions = JsonDB::read('submissions');
        $submission = null;
        
        foreach ($submissions as $s) {
            if ((int)$s['id'] === $submissionId && ($s['status'] ?? 'pending') === 'pending') {
                $submission = $s;
                break;
            }
        }
        
        if (!$submission) {
            http_response_code(404);
            echo 'فایل یافت نشد';
            exit;
        }
        
        $filePath = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $submission['fileName'];
        
        if (!is_file($filePath)) {
            http_response_code(404);
            echo 'فایل فیزیکی یافت نشد';
            exit;
        }
        
        $filename = $submission['fileName'];
        $size = filesize($filePath);
        
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="admin_preview_' . $filename . '"');
        header('Content-Length: ' . $size);
        header('Cache-Control: no-cache');
        readfile($filePath);
        exit;
    }
    
    if ($action === 'analyze' && $submissionId > 0) {
        // تجزیه و تحلیل فایل
        $submissions = JsonDB::read('submissions');
        $submission = null;
        
        foreach ($submissions as $s) {
            if ((int)$s['id'] === $submissionId) {
                $submission = $s;
                break;
            }
        }
        
        if (!$submission) {
            throw new RuntimeException('فایل یافت نشد');
        }
        
        $filePath = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $submission['fileName'];
        
        if (!is_file($filePath)) {
            throw new RuntimeException('فایل فیزیکی یافت نشد');
        }
        
        $analysis = analyzeFile($filePath, $submission['fileName']);
        
        json_response([
            'ok' => true,
            'analysis' => $analysis,
            'submission' => $submission
        ]);
    }
    
    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
    
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}

function analyzeFile(string $filePath, string $fileName): array {
    $analysis = [
        'fileName' => $fileName,
        'size' => filesize($filePath),
        'mimeType' => get_mime_type($filePath),
        'extension' => strtolower(pathinfo($fileName, PATHINFO_EXTENSION)),
        'isArchive' => false,
        'contents' => [],
        'codeAnalysis' => [],
        'security' => ['safe' => true, 'issues' => []],
    ];
    
    $ext = $analysis['extension'];
    
    // بررسی فایل‌های فشرده
    if (in_array($ext, ['zip', 'rar'], true)) {
        $analysis['isArchive'] = true;
        $analysis['contents'] = analyzeArchive($filePath, $ext);
    }
    
    // بررسی فایل‌های کد
    if (in_array($ext, ['php', 'js', 'py', 'java', 'cpp', 'html', 'css'], true)) {
        $analysis['codeAnalysis'] = analyzeCode($filePath, $ext);
    }
    
    // بررسی امنیتی با اسکنر داخلی (عمقی برای ZIP)
    $scan = scan_source_for_malware($filePath);
    $safe = ($scan['status'] ?? 'error') === 'clean';
    $issuesOut = [];
    foreach (($scan['issues'] ?? []) as $issue) {
        $f = (string)($issue['file'] ?? $fileName);
        $p = (string)($issue['pattern'] ?? 'مشکوک');
        $ln = (int)($issue['line'] ?? 0);
        $issuesOut[] = $ln > 0 ? ("{$f} :: {$p} در خط {$ln}") : ("{$f} :: {$p}");
    }
    $analysis['security'] = [ 'safe' => $safe, 'issues' => $issuesOut ];
    
    return $analysis;
}

function analyzeArchive(string $filePath, string $type): array {
    $contents = [];
    
    try {
        if ($type === 'zip' && class_exists('ZipArchive')) {
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                for ($i = 0; $i < min($zip->numFiles, 50); $i++) { // محدود به 50 فایل
                    $stat = $zip->statIndex($i);
                    $contents[] = [
                        'name' => $stat['name'],
                        'size' => $stat['size'],
                        'isDir' => substr($stat['name'], -1) === '/',
                    ];
                }
                $zip->close();
            }
        }
    } catch (Throwable $e) {
        $contents[] = ['error' => 'خطا در خواندن آرشیو: ' . $e->getMessage()];
    }
    
    return $contents;
}

function analyzeCode(string $filePath, string $ext): array {
    $analysis = [
        'lines' => 0,
        'functions' => [],
        'classes' => [],
        'includes' => [],
        'preview' => '',
    ];
    
    try {
        $content = file_get_contents($filePath);
        if ($content === false) return $analysis;
        
        // محدود کردن حجم برای جلوگیری از مشکل حافظه
        if (strlen($content) > 100000) { // 100KB
            $content = substr($content, 0, 100000) . "\n... (فایل بریده شده)";
        }
        
        $lines = explode("\n", $content);
        $analysis['lines'] = count($lines);
        $analysis['preview'] = implode("\n", array_slice($lines, 0, 20)); // 20 خط اول
        
        // تجزیه بر اساس زبان
        switch ($ext) {
            case 'php':
                $analysis = array_merge($analysis, analyzePhp($content));
                break;
            case 'js':
                $analysis = array_merge($analysis, analyzeJavaScript($content));
                break;
            case 'py':
                $analysis = array_merge($analysis, analyzePython($content));
                break;
        }
        
    } catch (Throwable $e) {
        $analysis['error'] = 'خطا در تجزیه کد: ' . $e->getMessage();
    }
    
    return $analysis;
}

function analyzePhp(string $content): array {
    $analysis = ['functions' => [], 'classes' => [], 'includes' => []];
    
    // جستجوی توابع
    preg_match_all('/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/i', $content, $matches);
    $analysis['functions'] = array_unique($matches[1] ?? []);
    
    // جستجوی کلاس‌ها
    preg_match_all('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $matches);
    $analysis['classes'] = array_unique($matches[1] ?? []);
    
    // جستجوی include/require
    preg_match_all('/(include|require)(_once)?\s*[\(\s][\'"]([^\'"]+)[\'"]/i', $content, $matches);
    $analysis['includes'] = array_unique($matches[3] ?? []);
    
    return $analysis;
}

function analyzeJavaScript(string $content): array {
    $analysis = ['functions' => [], 'classes' => [], 'imports' => []];
    
    // جستجوی توابع
    preg_match_all('/function\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*\(/i', $content, $matches);
    $functions1 = $matches[1] ?? [];
    
    preg_match_all('/const\s+([a-zA-Z_$][a-zA-Z0-9_$]*)\s*=\s*[\(\s]*function/i', $content, $matches);
    $functions2 = $matches[1] ?? [];
    
    $analysis['functions'] = array_unique(array_merge($functions1, $functions2));
    
    // جستجوی کلاس‌ها
    preg_match_all('/class\s+([a-zA-Z_$][a-zA-Z0-9_$]*)/i', $content, $matches);
    $analysis['classes'] = array_unique($matches[1] ?? []);
    
    return $analysis;
}

function analyzePython(string $content): array {
    $analysis = ['functions' => [], 'classes' => [], 'imports' => []];
    
    // جستجوی توابع
    preg_match_all('/def\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\(/i', $content, $matches);
    $analysis['functions'] = array_unique($matches[1] ?? []);
    
    // جستجوی کلاس‌ها
    preg_match_all('/class\s+([a-zA-Z_][a-zA-Z0-9_]*)/i', $content, $matches);
    $analysis['classes'] = array_unique($matches[1] ?? []);
    
    // جستجوی import
    preg_match_all('/(?:import|from)\s+([a-zA-Z_][a-zA-Z0-9_\.]*)/i', $content, $matches);
    $analysis['imports'] = array_unique($matches[1] ?? []);
    
    return $analysis;
}

function securityCheck(string $filePath, string $fileName): array {
    $security = ['safe' => true, 'issues' => []];
    
    try {
        $content = file_get_contents($filePath);
        if ($content === false) return $security;
        
        // محدود کردن حجم برای بررسی امنیت
        if (strlen($content) > 200000) {
            $content = substr($content, 0, 200000);
        }
        
        // الگوهای مشکوک
        $suspiciousPatterns = [
            '/eval\s*\(/i' => 'استفاده از eval()',
            '/exec\s*\(/i' => 'اجرای دستورات سیستم',
            '/system\s*\(/i' => 'اجرای دستورات سیستم', 
            '/shell_exec\s*\(/i' => 'اجرای shell',
            '/file_get_contents\s*\(\s*[\'"]https?:/i' => 'درخواست HTTP خارجی',
            '/curl_exec\s*\(/i' => 'درخواست cURL',
            '/\$_(?:GET|POST|REQUEST|COOKIE)\s*\[\s*[\'"][^\'"]*[\'\"]\s*\]\s*\)/i' => 'دسترسی مستقیم به ورودی کاربر',
            '/(?:password|pass|pwd)\s*=\s*[\'"][^\'"]+[\'"]/i' => 'رمز عبور صریح در کد',
            '/(?:api_key|secret|token)\s*=\s*[\'"][^\'"]+[\'"]/i' => 'کلید API صریح',
        ];
        
        foreach ($suspiciousPatterns as $pattern => $description) {
            if (preg_match($pattern, $content)) {
                $security['safe'] = false;
                $security['issues'][] = $description;
            }
        }
        
        // بررسی پسوند مشکوک
        $suspiciousExtensions = ['exe', 'bat', 'cmd', 'scr', 'pif', 'com'];
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        if (in_array($ext, $suspiciousExtensions, true)) {
            $security['safe'] = false;
            $security['issues'][] = 'پسوند فایل مشکوک: ' . $ext;
        }
        
    } catch (Throwable $e) {
        $security['issues'][] = 'خطا در بررسی امنیتی: ' . $e->getMessage();
    }
    
    return $security;
}
?>
