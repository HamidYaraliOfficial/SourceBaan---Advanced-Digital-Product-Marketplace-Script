<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/activity.php';
require_once __DIR__ . '/../includes/scanner.php';

const SUBMISSIONS_COLLECTION = 'submissions';

ensure_post();
require_login();
ensure_same_origin();
ensure_csrf_token();

try {
    $user = current_user();

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $language = trim($_POST['language'] ?? '');
    $level = trim($_POST['level'] ?? '');
    $tags = trim($_POST['tags'] ?? '');
    $repositoryUrl = trim($_POST['repositoryUrl'] ?? '');
    $demoUrl = trim($_POST['demoUrl'] ?? '');

    if ($title === '' || $description === '' || $language === '' || $level === '') {
        throw new RuntimeException('تمام فیلدهای ضروری را پر کنید');
    }

    // Enforce minimum description length for SEO/quality (at least 150 characters)
    $descLen = function_exists('mb_strlen') ? mb_strlen($description, 'UTF-8') : strlen($description);
    if ($descLen < 150) {
        throw new RuntimeException('طول توضیحات پروژه حداقل باید 150 کاراکتر باشد. لطفاً توضیح کامل‌تری بنویسید.');
    }

    if (empty($_FILES['file']) || (int)$_FILES['file']['size'] <= 0) {
        throw new RuntimeException('فایل الزامی است');
    }

    if ((int)$_FILES['file']['size'] > MAX_UPLOAD_BYTES) {
    throw new RuntimeException('حجم فایل نباید بیشتر از 1024 کیلوبایت باشد');
    }

    $originalName = (string)$_FILES['file']['name'];
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($ext, allowed_extensions(), true)) {
        throw new RuntimeException('پسوند فایل مجاز نیست');
    }

    $safeBase = sanitize_filename(pathinfo($originalName, PATHINFO_FILENAME));
    $finalName = $safeBase . '-' . JsonDB::nextId() . '.' . $ext;

    $target = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $finalName;
    if (!@move_uploaded_file($_FILES['file']['tmp_name'], $target)) {
        throw new RuntimeException('آپلود فایل ناموفق بود');
    }

    // Malware scan (mandatory before storing submission)
    $scan = scan_source_for_malware($target);
    if (($scan['status'] ?? 'error') !== 'clean') {
        @unlink($target);
        throw new RuntimeException('فایل شما دارای الگوهای مشکوک است یا قابل بررسی نیست. لطفاً محتوای ZIP را بررسی کرده و دوباره تلاش کنید.');
    }

    $submission = [
        'id' => JsonDB::nextId(),
        'title' => $title,
        'description' => $description,
        'language' => $language,
        'level' => $level,
        'tags' => array_values(array_filter(array_map('trim', explode(',', $tags)), fn($t) => $t !== '')),
        'repositoryUrl' => $repositoryUrl,
        'demoUrl' => $demoUrl,
        'fileName' => $finalName,
        'fileSize' => (int)$_FILES['file']['size'],
        'authorId' => (int)$user['id'],
        'author' => (string)$user['name'],
        'status' => 'pending',
        'createdAt' => date('c'),
        // Initialize updatedAt so clients can always show a last-updated value
        'updatedAt' => date('c'),
        'scan' => [
            'status' => 'clean',
            'summary' => 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.',
            'issuesCount' => 0,
            'scannedFiles' => (int)($scan['scannedFiles'] ?? 0),
            'skippedFiles' => (int)($scan['skippedFiles'] ?? 0),
            'encryptedEntries' => (int)($scan['encryptedEntries'] ?? 0),
            'scanAt' => (string)($scan['scanAt'] ?? date('c')),
        ],
    ];

    JsonDB::upsert(SUBMISSIONS_COLLECTION, function(array $items) use ($submission) {
        $items[] = $submission;
        return $items;
    });

    add_activity($user['name'] . ' پروژه "' . $title . '" را ارسال کرد', 'project-upload');

    // Points will be awarded upon approval, not at submission time

    json_response(['ok' => true, 'submission' => $submission]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
