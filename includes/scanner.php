<?php
// Simple static scanner for uploaded sources (no external deps)

declare(strict_types=1);

require_once __DIR__ . '/config.php';

/**
 * Scan a file (ZIP or single source file) for suspicious patterns.
 * Returns an associative array with keys:
 * - status: 'clean' | 'suspicious' | 'error'
 * - issues: array of ['file' => string, 'pattern' => string, 'line' => int]
 * - summary: string (fa-IR)
 * - scannedFiles: int
 * - skippedFiles: int
 * - encryptedEntries: int
 * - scanAt: ISO8601 string
 */
function scan_source_for_malware(string $path): array
{
    $result = [
        'status' => 'error',
        'issues' => [],
        'summary' => 'خطا در اسکن فایل',
        'scannedFiles' => 0,
        'skippedFiles' => 0,
        'encryptedEntries' => 0,
        'scanAt' => date('c')
    ];

    if (!is_file($path)) {
        $result['summary'] = 'فایل یافت نشد';
        return $result;
    }

    $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

    try {
        if ($ext === 'zip') {
            $local = scan_zip_file($path);
            if (VIRUSTOTAL_ENABLE === true) {
                $vt = virustotal_scan_and_report($path);
                if ($vt['ok']) {
                    $positives = (int)($vt['positives'] ?? 0);
                    $total = (int)($vt['total'] ?? 0);
                    if ($positives > 0) { $local['status'] = 'suspicious'; }
                    $local['summary'] .= ' | VirusTotal: ' . $positives . '/' . $total . ' engines';
                }
            }
            return $local;
        }
        // Fallback: scan single file as text
        $local = scan_text_file($path, basename($path));
        // Optionally augment with VirusTotal final report
        if (VIRUSTOTAL_ENABLE === true) {
            $vt = virustotal_scan_and_report($path);
            if ($vt['ok']) {
                $positives = (int)($vt['positives'] ?? 0);
                $total = (int)($vt['total'] ?? 0);
                if ($positives > 0) { $local['status'] = 'suspicious'; }
                $local['summary'] .= ' | VirusTotal: ' . $positives . '/' . $total . ' engines';
            }
        }
        return $local;
    } catch (Throwable $e) {
        $result['summary'] = 'استثنا در زمان اسکن: ' . $e->getMessage();
        return $result;
    }
}

/**
 * Scan inside a ZIP archive using ZipArchive without extraction to disk.
 */
function scan_zip_file(string $zipPath): array
{
    $issues = [];
    $scanned = 0;
    $skipped = 0;
    $encrypted = 0;
    $maxFiles = 500; // safety limit
    $maxBytesPerFile = 512 * 1024; // 512KB per file

    $zip = new ZipArchive();
    if ($zip->open($zipPath) !== true) {
        return [
            'status' => 'error',
            'issues' => [],
            'summary' => 'عدم توانایی در باز کردن فایل ZIP',
            'scannedFiles' => 0,
            'skippedFiles' => 0,
            'encryptedEntries' => 0,
            'scanAt' => date('c')
        ];
    }

    $total = $zip->numFiles;
    for ($i = 0; $i < $total && $scanned + $skipped < $maxFiles; $i++) {
        $stat = $zip->statIndex($i);
        if (!$stat) { $skipped++; continue; }
        $name = $stat['name'] ?? ''; // can be directories
        if ($name === '' || substr($name, -1) === '/') { continue; }

        // Try to read small prefix to detect executables
        $stream = $zip->getStream($name);
        if (!$stream) {
            // Could be encrypted
            $encrypted++;
            continue;
        }
        $prefix = stream_get_contents($stream, 4096);
        $exeType = detect_executable_type($prefix ?: '');
        if ($exeType !== '') {
            $issues[] = [ 'file' => $name, 'pattern' => 'Executable detected (' . $exeType . ')', 'line' => 1 ];
        }

        // After header check, decide whether to read full content for text scanning
        $lower = strtolower($name);
        $size = (int)($stat['size'] ?? 0);
        $isLikelyBinary = preg_match('/\.(png|jpg|jpeg|gif|webp|ico|bmp|svgz|ttf|otf|woff2?|eot|pdf|exe|dll|so|dylib|bin|obj|class|jar)$/i', $lower) === 1;
        $content = '';
        if (!($isLikelyBinary || $size > $maxBytesPerFile)) {
            $remaining = $maxBytesPerFile - strlen($prefix ?? '');
            $rest = $remaining > 0 ? stream_get_contents($stream, $remaining) : '';
            $content = (string)($prefix . $rest);
        }
        fclose($stream);
        if ($content === false) { $skipped++; continue; }

        if ($content !== '') {
            $fileIssues = detect_suspicious_patterns($content, $name);
            if (!empty($fileIssues)) {
                foreach ($fileIssues as $fi) { $issues[] = $fi; }
            }
        }
        $scanned++;
    }

    $status = empty($issues) && $encrypted === 0 ? 'clean' : (!empty($issues) ? 'suspicious' : 'clean');
    $summary = $status === 'clean'
        ? 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.'
        : 'این سورس دارای الگوهای مشکوک است. لطفاً با احتیاط بررسی شود.';
    if ($encrypted > 0 && $status === 'clean') {
        $summary = 'بخشی از فایل‌ها رمزگذاری شده‌اند. نتیجه نهایی: بدون مورد مشهود.';
    }

    $zip->close();

    return [
        'status' => $status,
        'issues' => $issues,
        'summary' => $summary,
        'scannedFiles' => $scanned,
        'skippedFiles' => $skipped,
        'encryptedEntries' => $encrypted,
        'scanAt' => date('c')
    ];
}

/**
 * Scan a single text file path.
 */
function scan_text_file(string $filePath, string $displayName): array
{
    // Detect executables by magic header even if extension is disguised
    $prefix = @file_get_contents($filePath, false, null, 0, 4096) ?: '';
    $exeType = detect_executable_type($prefix);
    if ($exeType !== '') {
        return [
            'status' => 'suspicious',
            'issues' => [ [ 'file' => $displayName, 'pattern' => 'Executable detected (' . $exeType . ')', 'line' => 1 ] ],
            'summary' => 'فایل اجرایی شناسایی شد: ' . $exeType,
            'scannedFiles' => 1,
            'skippedFiles' => 0,
            'encryptedEntries' => 0,
            'scanAt' => date('c')
        ];
    }

    $content = @file_get_contents($filePath);
    if ($content === false) {
        return [
            'status' => 'error',
            'issues' => [],
            'summary' => 'عدم توانایی در خواندن فایل برای اسکن',
            'scannedFiles' => 0,
            'skippedFiles' => 1,
            'encryptedEntries' => 0,
            'scanAt' => date('c')
        ];
    }
    $issues = detect_suspicious_patterns($content, $displayName);
    return [
        'status' => empty($issues) ? 'clean' : 'suspicious',
        'issues' => $issues,
        'summary' => empty($issues)
            ? 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.'
            : 'این سورس دارای الگوهای مشکوک است. لطفاً با احتیاط بررسی شود.',
        'scannedFiles' => 1,
        'skippedFiles' => 0,
        'encryptedEntries' => 0,
        'scanAt' => date('c')
    ];
}

/**
 * Pattern detector: light-weight heuristics across common languages.
 */
function detect_suspicious_patterns(string $content, string $fileName): array
{
    $issues = [];
    $lowerFile = strtolower($fileName);

    // Only scan text-like files by extension heuristic
    $isText = preg_match('/\.(php|phtml|php\d?|js|ts|py|rb|java|c|cpp|h|cs|go|sh|bat|cmd|ps1|html?|css|json|yml|yaml|md|txt)$/i', $lowerFile) === 1;
    if (!$isText) { return []; }

    $lines = preg_split('/\r\n|\n|\r/', $content);
    $maxLines = 20000; // limit
    if (count($lines) > $maxLines) { $lines = array_slice($lines, 0, $maxLines); }

    $patterns = [
        // PHP risky
        ['/(?:eval|assert|create_function)\s*\(/i', 'PHP: eval/assert/create_function'],
        ['/base64_decode\s*\(/i', 'PHP: base64_decode'],
        ['/(?:shell_exec|system|passthru|popen|proc_open)\s*\(/i', 'PHP: command execution'],
        ['/preg_replace\s*\(.*?\/[eE].*?\)/', 'PHP: preg_replace /e modifier'],
        ['/gzinflate\s*\(.*?base64_decode/i', 'PHP: gzinflate + base64 obfuscation'],
        ['/`[^`]+`/', 'PHP: backtick command execution'],
        ['/chr\s*\(\s*\d+\s*\)(?:\s*\.\s*chr\s*\(\s*\d+\s*\)\s*){4,}/i', 'PHP: chr obfuscation sequence'],
        ['/\$[a-zA-Z_][a-zA-Z0-9_]*\s*=\s*["\'][a-zA-Z_][a-zA-Z0-9_]*["\'];\s*\$[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', 'PHP: variable function call'],

        // JS risky
        ['/\beval\s*\(/i', 'JS: eval'],
        ['/\bFunction\s*\(/', 'JS: Function constructor'],
        ['/atob\s*\(/i', 'JS: atob decode'],
        ['/(XMLHttpRequest|fetch)\s*\(.*http/i', 'JS: network fetch to http'],

        // Python risky
        ['/\b(?:eval|exec)\s*\(/', 'Python: eval/exec'],
        ['/(?:os\.)?system\s*\(|subprocess\./', 'Python: system/subprocess'],
        ['/base64\.b64decode\s*\(/i', 'Python: base64 decode'],
        ['/marshal\.loads|zlib\.decompress\s*\(/i', 'Python: marshal/zlib decode'],
        ['/socket\.(?:socket|create_connection)\s*\(/i', 'Python: socket network'],
        ['/requests\.(?:get|post|put)\s*\(\s*["\']http:\/\//i', 'Python: HTTP request over insecure channel'],

        // Shell/Batch/PowerShell
        ['/\b(curl|wget)\b.*http/i', 'Shell: download via curl/wget'],
        ['/powershell(?:\.exe)?/i', 'Shell: invokes powershell'],
        ['/Invoke-\w+/', 'PowerShell: invoke commands'],
        ['/cmd\.exe\s*\/c/i', 'Batch: cmd.exe /c'],

        // Node.js child process
        ['/require\s*\(\s*["\']child_process["\']\s*\)/', 'Node: child_process require'],
        ['/child_process\.(?:exec|execSync|spawn|spawnSync)\s*\(/', 'Node: child_process execution'],

        // Generic: long base64-like strings
        ['/[A-Za-z0-9+\/]{120,}={0,2}/', 'Generic: long suspicious encoded blob'],
        ['/\b[0-9a-fA-F]{200,}\b/', 'Generic: long hex-encoded blob'],
    ];

    foreach ($lines as $ln => $line) {
        foreach ($patterns as [$regex, $label]) {
            if (preg_match($regex, $line) === 1) {
                $issues[] = [
                    'file' => $fileName,
                    'pattern' => $label,
                    'line' => $ln + 1
                ];
            }
        }
    }

    // Full-content patterns (cross-line chains)
    $fullPatterns = [
        // PHP decode chains
        ['/(?:eval|assert)\s*\(\s*(?:gzuncompress|gzinflate|str_rot13|base64_decode)\s*\(/is', 'PHP: eval on decoded content'],
        ['/base64_decode\s*\(\s*["\']?[A-Za-z0-9+\/]{200,}={0,2}["\']?\s*\)/is', 'PHP: direct base64 payload'],
        ['/gzinflate\s*\(\s*base64_decode\s*\(/is', 'PHP: base64+gzinflate chain'],
        // Python decode chains
        ['/exec\s*\(\s*(?:compile\s*\(|base64\.b64decode\s*\(|marshal\.loads\s*\(|zlib\.decompress\s*\()/is', 'Python: exec on decoded content'],
    ];

    foreach ($fullPatterns as [$regex, $label]) {
        if (preg_match_all($regex, $content, $m, PREG_OFFSET_CAPTURE) && !empty($m[0])) {
            foreach ($m[0] as $match) {
                $offset = (int)$match[1];
                $lineNum = 1 + substr_count(substr($content, 0, $offset), "\n");
                $issues[] = [
                    'file' => $fileName,
                    'pattern' => $label,
                    'line' => $lineNum
                ];
            }
        }
    }

    return $issues;
}

/**
 * Detect common executable formats by magic numbers
 */
function detect_executable_type(string $prefix): string
{
    $bytes = $prefix;
    if ($bytes === '') return '';
    // PE (Windows EXE/DLL) starts with MZ
    if (strncmp($bytes, "MZ", 2) === 0) return 'PE/Windows';
    // ELF (Linux/Unix)
    if (substr($bytes, 0, 4) === "\x7FELF") return 'ELF';
    // Mach-O (macOS) magic numbers
    $machOMagics = [
        "\xFE\xED\xFA\xCE", // 32-bit big-endian
        "\xCE\xFA\xED\xFE", // 32-bit little-endian
        "\xFE\xED\xFA\xCF", // 64-bit big-endian
        "\xCF\xFA\xED\xFE", // 64-bit little-endian
        "\xCA\xFE\xBA\xBE", // Fat binary (also used by Java class, but we also check 'class' extension elsewhere)
    ];
    foreach ($machOMagics as $m) { if (strncmp($bytes, $m, 4) === 0) return 'Mach-O'; }
    return '';
}

/**
 * Submit a file to VirusTotal API (simplified best-effort client)
 * Returns ['ok'=>true,'positives'=>int,'total'=>int] on partial success.
 */
function virustotal_scan_file(string $filePath): array
{
    if (!is_file($filePath)) return ['ok' => false];
    if (!defined('VIRUSTOTAL_API_KEY') || VIRUSTOTAL_API_KEY === '') return ['ok' => false];
    $url = 'https://www.virustotal.com/vtapi/v2/file/scan';
    $boundary = '----vtForm' . bin2hex(random_bytes(6));
    $fileName = basename($filePath);
    $fileData = @file_get_contents($filePath);
    if ($fileData === false) return ['ok' => false];

    // Build multipart form-data body
    $body = '';
    $eol = "\r\n";
    $body .= '--' . $boundary . $eol;
    $body .= 'Content-Disposition: form-data; name="apikey"' . $eol . $eol;
    $body .= VIRUSTOTAL_API_KEY . $eol;
    $body .= '--' . $boundary . $eol;
    $body .= 'Content-Disposition: form-data; name="file"; filename="' . addslashes($fileName) . '"' . $eol;
    $body .= 'Content-Type: application/octet-stream' . $eol . $eol;
    $body .= $fileData . $eol;
    $body .= '--' . $boundary . '--' . $eol;

    $opts = [
        'http' => [
            'method' => 'POST',
            'header' => [
                'Content-Type: multipart/form-data; boundary=' . $boundary,
                'User-Agent: SourceBaan-Scanner'
            ],
            'content' => $body,
            'timeout' => 20
        ]
    ];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);
    if (!is_string($resp) || $resp === '') {
        if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT scan http-empty for ' . $fileName);
        return ['ok' => false];
    }
    $json = json_decode($resp, true);
    if (!is_array($json)) {
        if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT scan json-decode failed: ' . substr($resp,0,200));
        return ['ok' => false];
    }
    if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT scan ok: ' . json_encode($json));
    // VT immediate response returns scan_id; full report requires another endpoint
    // Best-effort: map to ok
    return ['ok' => true, 'positives' => (int)($json['positives'] ?? 0), 'total' => (int)($json['total'] ?? 0)];
}

/**
 * Query VirusTotal for a final report using file hash or scan_id (best-effort).
 */
function virustotal_file_report(string $resource): array
{
    if (!defined('VIRUSTOTAL_API_KEY') || VIRUSTOTAL_API_KEY === '') return ['ok' => false];
    $url = 'https://www.virustotal.com/vtapi/v2/file/report?apikey=' . urlencode(VIRUSTOTAL_API_KEY) . '&resource=' . urlencode($resource);
    $opts = [ 'http' => [ 'method' => 'GET', 'timeout' => 15, 'header' => [ 'User-Agent: SourceBaan-Scanner' ] ] ];
    $ctx = stream_context_create($opts);
    $resp = @file_get_contents($url, false, $ctx);
    if (!is_string($resp) || $resp === '') {
        if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT report http-empty for ' . $resource);
        return ['ok' => false];
    }
    $json = json_decode($resp, true);
    if (!is_array($json)) {
        if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT report json-decode failed: ' . substr($resp,0,200));
        return ['ok' => false];
    }
    // response_code==1 indicates report found
    if ((int)($json['response_code'] ?? 0) !== 1) {
        if (defined('VIRUSTOTAL_LOG') && VIRUSTOTAL_LOG) @error_log('VT report not ready: ' . json_encode($json));
        return ['ok' => false];
    }
    return [
        'ok' => true,
        'positives' => (int)($json['positives'] ?? 0),
        'total' => (int)($json['total'] ?? 0),
    ];
}

/**
 * Submit then poll (with small backoff) for a short period to get a report.
 */
function virustotal_scan_and_report(string $filePath): array
{
    // Try by hash first (faster if already known)
    $hash = @hash_file('sha256', $filePath) ?: '';
    if ($hash !== '') {
        $rep = virustotal_file_report($hash);
        if (!empty($rep['ok'])) return $rep;
    }
    // Submit file
    $scan = virustotal_scan_file($filePath);
    // Try polling a few times (best-effort, keep fast)
    $delays = [1, 2, 3];
    foreach ($delays as $sec) {
        usleep($sec * 200000); // 0.2s, 0.4s, 0.6s: keep fast; VT may need longer in real world
        if ($hash !== '') {
            $rep = virustotal_file_report($hash);
            if (!empty($rep['ok'])) return $rep;
        }
    }
    // If no final report yet, return ok without metrics
    return ['ok' => !empty($scan['ok']), 'positives' => $scan['positives'] ?? 0, 'total' => $scan['total'] ?? 0];
}

?>


