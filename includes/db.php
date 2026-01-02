<?php
// JSON storage helper for SourceBaan

declare(strict_types=1);

require_once __DIR__ . '/config.php';

final class JsonDB
{
    // --- Encryption helpers (AES-256-GCM) ---
    private static function getKey(): string
    {
        $secret = defined('APP_SECRET') ? (string)APP_SECRET : '';
        if ($secret === '') {
            // derive a stable key from site name if secret missing (unlikely)
            $secret = hash('sha256', SITE_NAME . '::fallback', true);
        }
        // Normalize to 32 bytes
        return hash('sha256', $secret, true);
    }

    private static function encrypt(array $data): string
    {
        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if ($json === false) $json = '[]';
        // If encryption disabled, write pretty plaintext JSON
        if (defined('JSONDB_ENCRYPT') && JSONDB_ENCRYPT === false) {
            $pretty = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
            return is_string($pretty) ? $pretty : $json;
        }
        $key = self::getKey();
        $iv = random_bytes(12);
        $tag = '';
        $cipher = openssl_encrypt($json, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag, 'db');
        if ($cipher === false) {
            // On failure, fall back to plaintext (avoid data loss)
            return $json;
        }
        $payload = base64_encode($iv . $tag . $cipher);
        return 'ENCv1:' . $payload;
    }

    private static function decrypt(string $raw): array
    {
        // Detect marker
        if (str_starts_with($raw, 'ENCv1:')) {
            $b64 = substr($raw, 6);
            $bin = base64_decode($b64, true);
            if (is_string($bin) && strlen($bin) > 28) {
                $iv = substr($bin, 0, 12);
                $tag = substr($bin, 12, 16);
                $cipher = substr($bin, 28);
                $plain = openssl_decrypt($cipher, 'aes-256-gcm', self::getKey(), OPENSSL_RAW_DATA, $iv, $tag, 'db');
                if (is_string($plain) && $plain !== '') {
                    $data = json_decode($plain, true);
                    return is_array($data) ? $data : [];
                }
            }
            // If decrypt failed, treat as empty to avoid leaking ciphertext
            return [];
        }
        // Plain JSON fallback for legacy files
        $data = json_decode($raw, true);
        return is_array($data) ? $data : [];
    }

    private static function backupsDir(): string
    {
        $dir = rtrim(DATA_DIR, '/\\') . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
        return $dir;
    }

    private static function createBackup(string $name, string $payload): void
    {
        try {
            $dir = self::backupsDir();
            $date = date('Y-m-d');
            $file = $dir . DIRECTORY_SEPARATOR . $name . '_' . $date . '.enc';
            if (!file_exists($file)) {
                @file_put_contents($file, $payload);
            }
            // Retention: keep last 14 days per file
            $pattern = $dir . DIRECTORY_SEPARATOR . $name . '_*.enc';
            $list = glob($pattern) ?: [];
            if (count($list) > 14) {
                // Sort by mtime asc and delete oldest extras
                usort($list, function($a,$b){ return filemtime($a) <=> filemtime($b); });
                $toDelete = array_slice($list, 0, count($list) - 14);
                foreach ($toDelete as $old) { @unlink($old); }
            }
        } catch (\Throwable $e) { /* ignore */ }
    }

    private static function readLatestBackup(string $name): array
    {
        try {
            $dir = self::backupsDir();
            $pattern = $dir . DIRECTORY_SEPARATOR . $name . '_*.enc';
            $list = glob($pattern) ?: [];
            if (empty($list)) return [];
            usort($list, function($a,$b){ return filemtime($b) <=> filemtime($a); });
            $raw = @file_get_contents($list[0]);
            if (!is_string($raw) || $raw === '') return [];
            return self::decrypt($raw);
        } catch (\Throwable $e) {
            return [];
        }
    }
    private static function primaryDir(): string
    {
        return rtrim(DATA_DIR, '/\\');
    }

    private static function fallbackDir(): string
    {
        $fallback = rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'sourcebaan-data';
        if (!is_dir($fallback)) {
            @mkdir($fallback, 0775, true);
        }
        return $fallback;
    }

    private static function chooseWriteDir(): string
    {
        $primary = self::primaryDir();
        if (!is_dir($primary)) {
            @mkdir($primary, 0775, true);
        }
        if (is_dir($primary) && is_writable($primary)) {
            return $primary;
        }
        return self::fallbackDir();
    }

    private static function filePath(string $name): string
    {
        // Default path (for reads we prefer primary)
        return self::primaryDir() . DIRECTORY_SEPARATOR . $name . '.json';
    }

    public static function read(string $name): array
    {
        $primaryPath = self::filePath($name);
        $fallbackPath = self::fallbackDir() . DIRECTORY_SEPARATOR . $name . '.json';

        $pathToUse = null;
        if (file_exists($primaryPath)) {
            $pathToUse = $primaryPath;
        } elseif (file_exists($fallbackPath)) {
            $pathToUse = $fallbackPath;
        }

        if ($pathToUse === null) {
            // Create empty file where we can write
            $writeDir = self::chooseWriteDir();
            @file_put_contents($writeDir . DIRECTORY_SEPARATOR . $name . '.json', '[]');
            return [];
        }

        $json = @file_get_contents($pathToUse);
        if ($json === false || $json === '') {
            return [];
        }
        $data = self::decrypt($json);
        if (empty($data) && is_string($json) && str_starts_with($json, 'ENCv1:')) {
            // Attempt recovery from latest backup if primary decrypt failed
            $backup = self::readLatestBackup($name);
            if (!empty($backup)) {
                return $backup;
            }
        }
        return $data;
    }

    public static function write(string $name, array $data): void
    {
        // Prefer writing to the primary DATA_DIR if possible (so changes appear in repo),
        // otherwise fall back to the writable directory chosen by chooseWriteDir().
        $primary = self::primaryDir();
        $primaryPath = $primary . DIRECTORY_SEPARATOR . $name . '.json';
        if (is_dir($primary) && is_writable($primary)) {
            $dir = $primary;
        } else {
            $dir = self::chooseWriteDir();
        }
        $path = $dir . DIRECTORY_SEPARATOR . $name . '.json';
        if (!is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }
        $tmp = $path . '.tmp';
        $json = self::encrypt($data);
        // Try creating/opening temp file in a robust way (some environments may not support 'c')
        $fp = @fopen($tmp, 'c');
        if ($fp === false) {
            $fp = @fopen($tmp, 'w');
        }
        if ($fp === false) {
            // Last resort: write directly to target path
            $ok = @file_put_contents($path, $json);
            if ($ok === false) {
                throw new RuntimeException('Failed to open temp file for ' . $name . ' at ' . $tmp);
            }
            return;
        }
        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Failed to lock temp file for ' . $name);
            }
            ftruncate($fp, 0);
            fwrite($fp, $json);
            fflush($fp);
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
        $renamed = @rename($tmp, $path);
        if (!$renamed) {
            // Fallback if rename fails (e.g., permissions on Windows)
            @file_put_contents($path, $json);
            @unlink($tmp);
        }
        // Create daily backup (encrypted payload)
        self::createBackup($name, $json);
    }

    public static function upsert(string $name, callable $mutator): array
    {
        $data = self::read($name);
        $mutated = $mutator($data);
        if (!is_array($mutated)) {
            throw new RuntimeException('Mutator must return array for ' . $name);
        }
        self::write($name, $mutated);
        return $mutated;
    }

    public static function nextId(): int
    {
        // millisecond-based ID
        return (int) floor(microtime(true) * 1000);
    }
}
