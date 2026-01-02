<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/scanner.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // Only admin users allowed
    $u = current_user();
    if (!$u || strtolower((string)($u['role'] ?? 'user')) !== 'admin') {
        throw new RuntimeException('دسترسی غیرمجاز');
    }

    // Rescan all submissions missing scan info (or refresh all)
    if ($action === 'rescan_missing') {
        $subs = JsonDB::read('submissions');
        $updated = 0;
        foreach ($subs as $i => $s) {
            $fileName = (string)($s['fileName'] ?? '');
            if ($fileName === '') continue;
            $status = (string)($s['status'] ?? 'pending');
            $dir = $status === 'approved' ? APPROVED_DIR : PENDING_DIR;
            $path = rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            if (!is_file($path)) continue;

            // Only rescan if missing or requested to refresh all
            $scan = scan_source_for_malware($path);
            $subs[$i]['scan'] = [
                'status' => (string)($scan['status'] ?? 'error'),
                'summary' => (string)($scan['summary'] ?? ''),
                'issuesCount' => (int)count($scan['issues'] ?? []),
                'scannedFiles' => (int)($scan['scannedFiles'] ?? 0),
                'skippedFiles' => (int)($scan['skippedFiles'] ?? 0),
                'encryptedEntries' => (int)($scan['encryptedEntries'] ?? 0),
                'scanAt' => (string)($scan['scanAt'] ?? date('c')),
            ];
            $updated++;
        }
        JsonDB::write('submissions', $subs);
        json_response(['ok' => true, 'updated' => $updated]);
    }

    if ($action === 'ban_ip') {
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $ip = trim($_POST['ip'] ?? '');
        $reason = trim($_POST['reason'] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new RuntimeException('IP نامعتبر است');
        }
        ban_ip($ip, $reason);
        json_response(['ok' => true]);
    }

    if ($action === 'unban_ip') {
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $ip = trim($_POST['ip'] ?? '');
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new RuntimeException('IP نامعتبر است');
        }
        unban_ip($ip);
        json_response(['ok' => true]);
    }

    if ($action === 'suspend_user') {
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $userId = (int)($_POST['userId'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        suspend_user($userId, $reason);
        // Optionally reduce user visibility/points here in future
        json_response(['ok' => true]);
    }

    if ($action === 'unsuspend_user') {
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $userId = (int)($_POST['userId'] ?? 0);
        if ($userId <= 0) {
            throw new RuntimeException('شناسه کاربر نامعتبر است');
        }
        unsuspend_user($userId);
        json_response(['ok' => true]);
    }

    if ($action === 'list_bans') {
        $bans = JsonDB::read('banned_ips');
        $susp = JsonDB::read('user_suspensions');
        json_response(['ok' => true, 'banned_ips' => $bans, 'suspensions' => $susp]);
    }

    // Pending submissions list (GET)
    if ($action === 'list' && (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET')) {
        $subs = JsonDB::read('submissions');
        $pending = array_values(array_filter($subs, fn($s) => ($s['status'] ?? 'pending') === 'pending'));
        json_response(['ok' => true, 'pending' => $pending]);
    }

    // All submissions (GET)
    if ($action === 'all' && (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'GET')) {
        $subs = JsonDB::read('submissions');
        json_response(['ok' => true, 'submissions' => $subs]);
    }

    // Approve submission (POST)
    if ($action === 'approve' && (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) throw new RuntimeException('شناسه نامعتبر');

        $subs = JsonDB::read('submissions');
        $idx = -1;
        foreach ($subs as $i => $s) {
            if ((int)($s['id'] ?? 0) === $id) { $idx = $i; break; }
        }
        if ($idx < 0) throw new RuntimeException('یافت نشد');

        // پیش از تایید: اجرای اسکن (اختیاری - بدون مسدودسازی)
        $fileName = $subs[$idx]['fileName'] ?? '';
        if ($fileName !== '') {
            $src = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            if (is_file($src)) {
                $scan = scan_source_for_malware($src);
                // ذخیره نتیجه واقعی اسکن (بدون جلوگیری از تایید)
                $subs[$idx]['scan'] = [
                    'status' => (string)($scan['status'] ?? 'error'),
                    'summary' => (string)($scan['summary'] ?? ''),
                    'issuesCount' => (int)count($scan['issues'] ?? []),
                    'scannedFiles' => (int)($scan['scannedFiles'] ?? 0),
                    'skippedFiles' => (int)($scan['skippedFiles'] ?? 0),
                    'encryptedEntries' => (int)($scan['encryptedEntries'] ?? 0),
                    'scanAt' => (string)($scan['scanAt'] ?? date('c')),
                ];
            }
        }

        // Move file from pending to approved (best effort)
        if ($fileName !== '') {
            $src = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            $dst = rtrim(APPROVED_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            @rename($src, $dst);
        }

        // Update submission
        $subs[$idx]['status'] = 'approved';
        $subs[$idx]['downloads'] = (int)($subs[$idx]['downloads'] ?? 0);
        $subs[$idx]['stars'] = (int)($subs[$idx]['stars'] ?? 0);
        $subs[$idx]['views'] = (int)($subs[$idx]['views'] ?? 0);
        $subs[$idx]['updatedAt'] = date('c');
        JsonDB::write('submissions', $subs);

        // Add/update in projects collection for analytics
        $projects = JsonDB::read('projects');
        $found = false;
        foreach ($projects as $pi => $p) {
            if ((int)($p['id'] ?? 0) === $id) { $projects[$pi] = $subs[$idx]; $found = true; break; }
        }
        if (!$found) { $projects[] = $subs[$idx]; }
        JsonDB::write('projects', $projects);

        // Award points to author on approval
        $settings = JsonDB::read('settings');
        $pointsPerUpload = (int)($settings['pointsPerUpload'] ?? 50);
        $authorId = (int)($subs[$idx]['authorId'] ?? 0);
        if ($authorId > 0) {
            $user = get_user_by_id($authorId);
            if ($user) {
                $user['points'] = max(0, (int)($user['points'] ?? 0) + $pointsPerUpload);
                save_user($user);
                
                // Notify followers about the new project
                $projectTitle = $subs[$idx]['title'] ?? 'پروژه جدید';
                $authorName = $user['name'] ?? $user['username'] ?? 'کاربر';
                $projectLink = 'sources.php?id=' . $id;
                
                notify_followers(
                    $authorId,
                    'پروژه جدید منتشر شد! 🚀',
                    $authorName . ' پروژه جدید "' . $projectTitle . '" را منتشر کرد.',
                    $projectLink,
                    'success'
                );
            }
        }

        json_response(['ok' => true, 'project' => $subs[$idx]]);
    }

    // Reject submission (POST)
    if ($action === 'reject' && (strtoupper($_SERVER['REQUEST_METHOD'] ?? '') === 'POST')) {
        ensure_post();
        $id = (int)($_POST['id'] ?? 0);
        $reason = trim($_POST['reason'] ?? '');
        if ($id <= 0) throw new RuntimeException('شناسه نامعتبر');

        $subs = JsonDB::read('submissions');
        $idx = -1;
        foreach ($subs as $i => $s) {
            if ((int)($s['id'] ?? 0) === $id) { $idx = $i; break; }
        }
        if ($idx < 0) throw new RuntimeException('یافت نشد');

        // Remove file from pending (best effort)
        $fileName = $subs[$idx]['fileName'] ?? '';
        if ($fileName !== '') {
            $src = rtrim(PENDING_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
            @unlink($src);
        }
        $subs[$idx]['status'] = 'rejected';
        $subs[$idx]['reason'] = $reason;
        $subs[$idx]['updatedAt'] = date('c');
        JsonDB::write('submissions', $subs);
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
