<?php
// Content filtering system for Sourcekade Anjoman
declare(strict_types=1);

class ContentFilter {
    
    // Persian profanity words list
    private static array $profanityWords = [
        // Common Persian swear words (استعلام شده)
        'احمق', 'خر', 'گاو', 'الاغ', 'عوضی', 'کثیف', 'لاشی', 'شرف', 'گش', 'جنده',
        'کیر', 'کون', 'کص', 'کوص', 'جاکش', 'ملعون', 'کونی', 'پفیوز', 'بیناموس',
        'حرامزاده', 'رذل', 'پدر سگ', 'مادر قهبه', 'نامرد', 'بی غیرت', 'لجن',
        'زبالهای', 'خایه', 'دک', 'آلت', 'سک', 'زن بابا', 'هرزه', 'فاحشه',
        'قحبه', 'ولی', 'کبری', 'جیره خور', 'ضایع', 'لوس', 'پدر کشته',
        'مادر کشته', 'سمج', 'یابو', 'بت پرست', 'منحوس', 'نحس', 'نجس',
        'پلید', 'لعنتی', 'زشت', 'دیوث', 'آشغال', 'لاشه', 'مرتیکه',
        'کچل', 'لاغر', 'چاق', 'بدقیافه', 'مرده شور', 'گاوصندوق', 'کله‌خر',
        
        // Alternative spellings and variations
        'ک.ر', 'ک*ر', 'ک-ر', 'کیــر', 'کییر', 'کک', 'ک ص', 'کــص',
        'جـنده', 'ج.نده', 'جن ده', 'جــنده', 'جاکــش', 'ج.کش',
        'کونــی', 'کو نی', 'ک.نی', 'کـــونی', 'ع.ضی', 'عو ضی',
        'احـمق', 'ا.مق', 'احـــمق', 'حرام زاده', 'حرام‌زاده',
        
        // English profanity that might be used
        'fuck', 'shit', 'bitch', 'damn', 'hell', 'ass', 'bastard',
        'fucking', 'fucked', 'motherfucker', 'asshole', 'dickhead',
        
        // Mixed language
        'فاک', 'شیت', 'دمن', 'باستارد', 'بیچ'
    ];
    
    // Suspicious patterns
    private static array $suspiciousPatterns = [
        '/\b\d{11}\b/', // Phone numbers
        '/https?:\/\/[^\s]+/', // URLs (might be spam)
        '/[\u0600-\u06FF\s]*[aeiou]{4,}[\u0600-\u06FF\s]*/', // Suspicious character repetition
        '/(.)\1{4,}/', // Character repetition (aaaaaaa)
        '/^\s*[A-Z\s!]{20,}\s*$/', // ALL CAPS
    ];
    
    // Image content check keywords
    private static array $inappropriateImageKeywords = [
        'nude', 'naked', 'porn', 'sex', 'xxx', 'nsfw', 'adult',
        'explicit', 'erotic', 'sexual', 'breast', 'penis', 'vagina'
    ];
    
    public static function filterText(string $text): array {
        $issues = [];
        $cleanText = $text;
        $severity = 0;
        
        // Skip filtering for very short common greetings
        $commonWords = ['سلام', 'خوبی', 'چطوری', 'ممنون', 'متشکرم', 'بای', 'خداحافظ', 'سپاس', 'درود', 'چه خبر', 'خوش آمدید'];
        $trimmedText = trim($text);
        if (in_array($trimmedText, $commonWords) || mb_strlen($trimmedText) <= 3) {
            return [
                'passed' => true,
                'issues' => [],
                'cleanText' => $cleanText,
                'severity' => 0,
                'action' => 'approve'
            ];
        }
        
        // Check for profanity (only block severe cases)
        $profanityResult = self::checkProfanity($text);
        if ($profanityResult['found']) {
            $issues[] = 'کلمات نامناسب شناسایی شد';
            $cleanText = $profanityResult['cleanText'];
            $severity = max($severity, $profanityResult['severity']);
        }
        
        // Check suspicious patterns (only for serious violations)
        $patternResult = self::checkSuspiciousPatterns($text);
        if ($patternResult['found'] && $patternResult['severity'] >= 2) {
            $issues = array_merge($issues, $patternResult['issues']);
            $severity = max($severity, $patternResult['severity']);
        }
        
        // Skip text quality checks for short messages (under 10 characters)
        if (mb_strlen(trim($text)) > 10) {
            $qualityResult = self::checkTextQuality($text);
            if ($qualityResult['found'] && $qualityResult['severity'] >= 2) {
                $issues = array_merge($issues, $qualityResult['issues']);
                $severity = max($severity, $qualityResult['severity']);
            }
        }
        
        return [
            'passed' => $severity < 2, // Only block severe violations (severity 2+)
            'issues' => $issues,
            'cleanText' => $cleanText,
            'severity' => $severity, // 0: OK, 1: Warning, 2: Block, 3: Ban
            'action' => self::getAction($severity)
        ];
    }
    
    private static function checkProfanity(string $text): array {
        $foundWords = [];
        $cleanText = $text;
        $severity = 0;
        
        // Normalize text for better matching
        $normalizedText = self::normalizeText($text);
        
        foreach (self::$profanityWords as $word) {
            $pattern = '/' . preg_quote($word, '/') . '/ui';
            
            if (preg_match($pattern, $normalizedText)) {
                $foundWords[] = $word;
                // Replace with stars
                $replacement = str_repeat('*', mb_strlen($word));
                $cleanText = preg_replace($pattern, $replacement, $cleanText);
                $severity = 2; // Block content
            }
        }
        
        return [
            'found' => !empty($foundWords),
            'words' => $foundWords,
            'cleanText' => $cleanText,
            'severity' => $severity
        ];
    }
    
    private static function checkSuspiciousPatterns(string $text): array {
        $issues = [];
        $severity = 0;
        
        foreach (self::$suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $text)) {
                $issues[] = 'الگوی مشکوک شناسایی شد';
                $severity = max($severity, 1); // Warning
            }
        }
        
        return [
            'found' => !empty($issues),
            'issues' => $issues,
            'severity' => $severity
        ];
    }
    
    private static function checkTextQuality(string $text): array {
        $issues = [];
        $severity = 0;
        
        // Too short
        if (mb_strlen(trim($text)) < 5) {
            $issues[] = 'متن خیلی کوتاه است';
            $severity = max($severity, 1);
        }
        
        // Too long
        if (mb_strlen($text) > 5000) {
            $issues[] = 'متن خیلی طولانی است';
            $severity = max($severity, 1);
        }
        
        // Too many line breaks
        if (substr_count($text, "\n") > 20) {
            $issues[] = 'خطوط زیاد شناسایی شد';
            $severity = max($severity, 1);
        }
        
        // Mostly non-Persian characters
        $persianChars = preg_match_all('/[\x{0600}-\x{06FF}]/u', $text);
        $totalChars = mb_strlen(preg_replace('/\s/', '', $text));
        
        if ($totalChars > 10 && $persianChars / $totalChars < 0.3) {
            $issues[] = 'لطفاً از زبان فارسی استفاده کنید';
            $severity = max($severity, 1);
        }
        
        return [
            'found' => !empty($issues),
            'issues' => $issues,
            'severity' => $severity
        ];
    }
    
    public static function checkImageContent(string $filename, string $content = ''): array {
        $issues = [];
        $severity = 0;
        
        // Check filename for inappropriate keywords
        $lowerFilename = strtolower($filename);
        foreach (self::$inappropriateImageKeywords as $keyword) {
            if (strpos($lowerFilename, $keyword) !== false) {
                $issues[] = 'نام فایل نامناسب است';
                $severity = 3; // Ban level
                break;
            }
        }
        
        // Basic file extension check
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        
        if (!in_array($extension, $allowedExtensions)) {
            $issues[] = 'فرمت تصویر مجاز نیست';
            $severity = max($severity, 2);
        }
        
        return [
            'passed' => empty($issues),
            'issues' => $issues,
            'severity' => $severity,
            'action' => self::getAction($severity)
        ];
    }
    
    private static function normalizeText(string $text): string {
        // Convert similar characters to standard forms
        $replacements = [
            'ي' => 'ی',
            'ك' => 'ک',
            'ة' => 'ه',
            '٠' => '0',
            '١' => '1',
            '٢' => '2',
            '٣' => '3',
            '٤' => '4',
            '٥' => '5',
            '٦' => '6',
            '٧' => '7',
            '٨' => '8',
            '٩' => '9'
        ];
        
        $normalized = str_replace(array_keys($replacements), array_values($replacements), $text);
        
        // Remove diacritics
        $normalized = preg_replace('/[\x{064B}-\x{065F}\x{0670}\x{06D6}-\x{06ED}]/u', '', $normalized);
        
        return $normalized;
    }
    
    private static function getAction(int $severity): string {
        switch ($severity) {
            case 0:
                return 'approve';
            case 1:
                return 'warning';
            case 2:
                return 'block';
            case 3:
                return 'ban_user';
            default:
                return 'review';
        }
    }
    
    public static function logContentIssue(int $userId, string $content, array $issues): void {
        $logEntry = [
            'timestamp' => date('c'),
            'userId' => $userId,
            'content' => substr($content, 0, 200) . '...',
            'issues' => $issues,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ];
        
        $logFile = __DIR__ . '/../data/content_violations.log';
        file_put_contents($logFile, json_encode($logEntry) . "\n", FILE_APPEND | LOCK_EX);
    }
    
    public static function isUserSuspended(int $userId): bool {
        $suspensionsFile = __DIR__ . '/../data/user_suspensions.json';
        
        if (!file_exists($suspensionsFile)) {
            return false;
        }
        
        $suspensions = json_decode(file_get_contents($suspensionsFile), true) ?? [];
        
        if (isset($suspensions[$userId])) {
            $suspension = $suspensions[$userId];
            if ($suspension['until'] > time()) {
                return true;
            } else {
                // Suspension expired, remove it
                unset($suspensions[$userId]);
                file_put_contents($suspensionsFile, json_encode($suspensions));
            }
        }
        
        return false;
    }
    
    public static function suspendUser(int $userId, int $hours, string $reason): void {
        $suspensionsFile = __DIR__ . '/../data/user_suspensions.json';
        $suspensions = [];
        
        if (file_exists($suspensionsFile)) {
            $suspensions = json_decode(file_get_contents($suspensionsFile), true) ?? [];
        }
        
        $suspensions[$userId] = [
            'until' => time() + ($hours * 3600),
            'reason' => $reason,
            'created_at' => date('c')
        ];
        
        file_put_contents($suspensionsFile, json_encode($suspensions));
    }
}
