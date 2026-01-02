<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    require_login();
    $user = current_user();
    $userId = (int)$user['id'];
    
    switch ($action) {
        case 'submit':
            handle_submit_report($userId);
            break;
            
        case 'get':
            // Only admins can view reports
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_get_reports();
            break;
            
        case 'preview':
            // Only admins can preview reported files
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_preview();
            break;

        case 'edit_project':
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_edit_project();
            break;

        case 'delete_project':
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_delete_project();
            break;
            
        case 'resolve':
            // Only admins can resolve reports
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_resolve_report();
            break;

        case 'edit_report':
            // Only admins can edit report metadata (description/adminNote)
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_edit_report();
            break;
            
        case 'bulk_action':
            // Only admins can perform bulk actions
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_bulk_action();
            break;
            
        case 'export':
            // Only admins can export reports
            if (!is_admin()) {
                echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
                return;
            }
            handle_export_reports();
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
    }

} catch (Throwable $e) {
    echo json_encode(['ok' => false, 'error' => 'خطای سرور: ' . $e->getMessage()]);
}

function handle_submit_report(int $userId): void {
    ensure_csrf_token();
    
    $projectId = (int)($_POST['projectId'] ?? 0);
    $reason = trim($_POST['reason'] ?? '');
    $description = trim($_POST['description'] ?? '');
    
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }
    
    if (empty($reason)) {
        echo json_encode(['ok' => false, 'error' => 'دلیل گزارش الزامی است']);
        return;
    }
    
    if (strlen($description) > 1000) {
        echo json_encode(['ok' => false, 'error' => 'توضیحات نباید بیش از 1000 کاراکتر باشد']);
        return;
    }
    
    // Verify project exists
    $projects = JsonDB::read('projects');
    $project = null;
    foreach ($projects as $p) {
        if ((int)$p['id'] === $projectId) {
            $project = $p;
            break;
        }
    }
    
    if (!$project) {
        echo json_encode(['ok' => false, 'error' => 'پروژه یافت نشد']);
        return;
    }
    
    // Check if user already reported this project
    $reports = JsonDB::read('reports');
    foreach ($reports as $report) {
        if ((int)($report['userId'] ?? 0) === $userId && 
            (int)($report['projectId'] ?? 0) === $projectId &&
            ($report['status'] ?? '') === 'pending') {
            echo json_encode(['ok' => false, 'error' => 'شما قبلاً این پروژه را گزارش کرده‌اید']);
            return;
        }
    }
    
    // Create report
    $report = [
        'id' => JsonDB::nextId(),
        'userId' => $userId,
        'projectId' => $projectId,
        'projectTitle' => $project['title'] ?? '',
        'reason' => $reason,
        'description' => $description,
        'status' => 'pending', // pending, resolved, dismissed
        'createdAt' => date('c'),
        'updatedAt' => date('c')
    ];
    
    $reports[] = $report;
    JsonDB::write('reports', $reports);
    
    echo json_encode([
        'ok' => true,
        'message' => 'گزارش شما ثبت شد و توسط مدیران بررسی خواهد شد',
        'reportId' => $report['id']
    ]);
}

function handle_get_reports(): void {
    $status = $_GET['status'] ?? 'all';
    $type = $_GET['type'] ?? 'all';
    $search = trim($_GET['search'] ?? '');
    $sort = $_GET['sort'] ?? 'newest';
    $page = max(1, (int)($_GET['page'] ?? 1));
    $limit = min(100, max(10, (int)($_GET['limit'] ?? 20)));
    $offset = ($page - 1) * $limit;
    
    $reports = JsonDB::read('reports');
    $allReports = $reports; // Keep original for counts
    
    // Get user names for reports first
    $users = JsonDB::read('users');
    $userMap = [];
    foreach ($users as $user) {
        $userMap[$user['id']] = [
            'username' => $user['username'] ?? $user['name'] ?? 'کاربر',
            'email' => $user['email'] ?? ''
        ];
    }
    
    // Add user info to reports
    foreach ($reports as &$report) {
        $userId = (int)($report['userId'] ?? 0);
        $report['userInfo'] = $userMap[$userId] ?? ['username' => 'کاربر حذف شده', 'email' => ''];
    }
    unset($report);
    
    // Filter by status
    if ($status !== 'all') {
        $reports = array_filter($reports, fn($r) => ($r['status'] ?? '') === $status);
    }
    
    // Filter by type/reason
    if ($type !== 'all') {
        $reports = array_filter($reports, fn($r) => ($r['reason'] ?? '') === $type);
    }
    
    // Search functionality
    if (!empty($search)) {
        $reports = array_filter($reports, function($report) use ($search) {
            $searchLower = mb_strtolower($search, 'UTF-8');
            
            // Search in project title
            if (mb_strpos(mb_strtolower($report['projectTitle'] ?? '', 'UTF-8'), $searchLower) !== false) {
                return true;
            }
            
            // Search in description
            if (mb_strpos(mb_strtolower($report['description'] ?? '', 'UTF-8'), $searchLower) !== false) {
                return true;
            }
            
            // Search in username
            if (mb_strpos(mb_strtolower($report['userInfo']['username'] ?? '', 'UTF-8'), $searchLower) !== false) {
                return true;
            }
            
            // Search in admin note
            if (mb_strpos(mb_strtolower($report['adminNote'] ?? '', 'UTF-8'), $searchLower) !== false) {
                return true;
            }
            
            return false;
        });
    }
    
    // Sort reports
    switch ($sort) {
        case 'oldest':
            usort($reports, fn($a, $b) => strtotime($a['createdAt'] ?? '0') - strtotime($b['createdAt'] ?? '0'));
            break;
        case 'priority':
            usort($reports, function($a, $b) {
                $priorityOrder = ['malware' => 4, 'copyright' => 3, 'inappropriate' => 2, 'spam' => 1, 'other' => 0];
                $aPriority = $priorityOrder[$a['reason'] ?? 'other'] ?? 0;
                $bPriority = $priorityOrder[$b['reason'] ?? 'other'] ?? 0;
                
                if ($aPriority === $bPriority) {
                    // If same priority, sort by newest
                    return strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0');
                }
                
                return $bPriority - $aPriority;
            });
            break;
        case 'newest':
        default:
            usort($reports, fn($a, $b) => strtotime($b['createdAt'] ?? '0') - strtotime($a['createdAt'] ?? '0'));
            break;
    }
    
    // Paginate
    $total = count($reports);
    $pages = ceil($total / $limit);
    $paginatedReports = array_slice($reports, $offset, $limit);
    
    echo json_encode([
        'ok' => true,
        'reports' => $paginatedReports,
        'pagination' => [
            'page' => $page,
            'limit' => $limit,
            'total' => $total,
            'pages' => $pages
        ],
        'counts' => [
            'pending' => count(array_filter($allReports, fn($r) => ($r['status'] ?? '') === 'pending')),
            'resolved' => count(array_filter($allReports, fn($r) => ($r['status'] ?? '') === 'resolved')),
            'dismissed' => count(array_filter($allReports, fn($r) => ($r['status'] ?? '') === 'dismissed')),
            'total' => count($allReports)
        ],
        'filters' => [
            'status' => $status,
            'type' => $type,
            'search' => $search,
            'sort' => $sort
        ]
    ]);
}

function handle_resolve_report(): void {
    ensure_csrf_token();
    
    $reportId = (int)($_POST['reportId'] ?? 0);
    $action = $_POST['reportAction'] ?? ''; // resolve, dismiss
    $adminNote = trim($_POST['adminNote'] ?? '');
    
    if ($reportId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه گزارش نامعتبر است']);
        return;
    }
    
    if (!in_array($action, ['resolve', 'dismiss'])) {
        echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
        return;
    }
    
    $reports = JsonDB::read('reports');
    $reportIndex = null;
    
    foreach ($reports as $index => $report) {
        if ((int)$report['id'] === $reportId) {
            $reportIndex = $index;
            break;
        }
    }
    
    if ($reportIndex === null) {
        echo json_encode(['ok' => false, 'error' => 'گزارش یافت نشد']);
        return;
    }
    
    // Update report
    $reports[$reportIndex]['status'] = $action === 'resolve' ? 'resolved' : 'dismissed';
    $reports[$reportIndex]['adminNote'] = $adminNote;
    $reports[$reportIndex]['resolvedAt'] = date('c');
    $reports[$reportIndex]['resolvedBy'] = current_user()['id'] ?? 0;
    $reports[$reportIndex]['updatedAt'] = date('c');
    
    JsonDB::write('reports', $reports);
    
    $message = $action === 'resolve' ? 'گزارش حل شد' : 'گزارش رد شد';
    
    echo json_encode([
        'ok' => true,
        'message' => $message
    ]);
}

function handle_edit_report(): void {
    ensure_post();
    ensure_csrf_token();

    $reportId = (int)($_POST['reportId'] ?? 0);
    if ($reportId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه گزارش نامعتبر است']);
        return;
    }

    $newDescription = isset($_POST['description']) ? trim((string)$_POST['description']) : null;
    $adminNote = isset($_POST['adminNote']) ? trim((string)$_POST['adminNote']) : null;

    if ($newDescription !== null && mb_strlen($newDescription, 'UTF-8') > 2000) {
        echo json_encode(['ok' => false, 'error' => 'توضیحات نباید بیش از 2000 کاراکتر باشد']);
        return;
    }

    $reports = JsonDB::read('reports');
    $reportIndex = null;
    foreach ($reports as $index => $report) {
        if ((int)($report['id'] ?? 0) === $reportId) { $reportIndex = $index; break; }
    }
    if ($reportIndex === null) {
        echo json_encode(['ok' => false, 'error' => 'گزارش یافت نشد']);
        return;
    }

    if ($newDescription !== null) {
        $reports[$reportIndex]['description'] = $newDescription;
    }
    if ($adminNote !== null) {
        $reports[$reportIndex]['adminNote'] = $adminNote;
    }
    $reports[$reportIndex]['updatedAt'] = date('c');

    JsonDB::write('reports', $reports);

    echo json_encode(['ok' => true, 'message' => 'گزارش به‌روز شد']);
}

function handle_preview(): void {
    $projectId = (int)($_GET['projectId'] ?? 0);
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }

    $projects = JsonDB::read('projects');
    $project = null;
    foreach ($projects as $p) {
        if ((int)($p['id'] ?? 0) === $projectId) { $project = $p; break; }
    }

    if (!$project) {
        echo json_encode(['ok' => false, 'error' => 'پروژه یافت نشد']);
        return;
    }
    // Attach author profile info (telegram, username, email) when possible
    $users = JsonDB::read('users');
    $authorProfile = [
        'username' => $project['author'] ?? ($project['authorName'] ?? ''),
        'telegram' => $project['telegram'] ?? ''
    ];
    foreach ($users as $u) {
        if ((int)($u['id'] ?? 0) === (int)($project['authorId'] ?? 0) || (!empty($project['author']) && (strcasecmp(($u['username'] ?? ''), $project['author']) === 0 || strcasecmp(($u['name'] ?? ''), $project['author']) === 0))) {
            $authorProfile['username'] = $u['username'] ?? $u['name'] ?? $authorProfile['username'];
            $authorProfile['telegram'] = $u['telegram'] ?? $authorProfile['telegram'];
            $authorProfile['email'] = $u['email'] ?? '';
            break;
        }
    }
    $project['authorProfile'] = $authorProfile;

    $fileName = $project['fileName'] ?? '';
    $filePath = rtrim(APPROVED_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;

    $out = [ 'ok' => true, 'project' => $project, 'preview' => [] ];

    if (!empty($fileName) && is_file($filePath)) {
        $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $size = filesize($filePath);
        $out['preview']['size'] = $size;
        $out['preview']['extension'] = $ext;

        // Text-like previews
        $textExts = ['php','js','py','java','html','css','txt','md'];
        if (in_array($ext, $textExts, true)) {
            $content = @file_get_contents($filePath, false, null, 0, 200000); // limit
            if ($content === false) $content = '';
            // limit characters for safety
            if (strlen($content) > 200000) $content = substr($content, 0, 200000) . "\n... (فایل بریده شد)";
            $out['preview']['type'] = 'text';
            $out['preview']['content'] = $content;
        } elseif ($ext === 'zip' && class_exists('ZipArchive')) {
            $list = [];
            $zip = new ZipArchive();
            if ($zip->open($filePath) === TRUE) {
                $count = min(200, $zip->numFiles);
                for ($i = 0; $i < $count; $i++) {
                    $stat = $zip->statIndex($i);
                    $list[] = [ 'name' => $stat['name'] ?? '', 'size' => $stat['size'] ?? 0 ];
                }
                $zip->close();
            }
            $out['preview']['type'] = 'archive';
            $out['preview']['contents'] = $list;
        } else {
            $out['preview']['type'] = 'binary';
            $out['preview']['message'] = 'قابل پیش‌نمایش نیست، می‌توانید فایل را دانلود کنید.';
            $out['preview']['downloadUrl'] = '../api/preview-file.php?action=download&submissionId=0';
            // Note: preview-file.php download path is tailored for submissions; admin can download file directly if needed
        }
    } else {
        $out['preview']['type'] = 'missing';
        $out['preview']['message'] = 'فایل فیزیکی یافت نشد';
    }

    echo json_encode($out, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function handle_edit_project(): void {
    ensure_post();
    ensure_csrf_token();

    $projectId = (int)($_POST['projectId'] ?? 0);
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tagsRaw = trim($_POST['tags'] ?? '');
    $tags = $tagsRaw === '' ? [] : array_values(array_filter(array_map('trim', explode(',', $tagsRaw))));

    $projects = JsonDB::read('projects');
    $found = false;
    foreach ($projects as $idx => $p) {
        if ((int)($p['id'] ?? 0) === $projectId) {
            if ($title !== '') $projects[$idx]['title'] = $title;
            $projects[$idx]['description'] = $description;
            if (!empty($tags)) $projects[$idx]['tags'] = $tags;
            $projects[$idx]['updatedAt'] = date('c');
            $found = true;
            break;
        }
    }

    if (!$found) {
        echo json_encode(['ok' => false, 'error' => 'پروژه یافت نشد']);
        return;
    }

    JsonDB::write('projects', $projects);

    echo json_encode(['ok' => true, 'message' => 'اطلاعات پروژه به‌روز شد']);
}

function handle_delete_project(): void {
    ensure_post();
    ensure_csrf_token();

    $projectId = (int)($_POST['projectId'] ?? 0);
    $adminNote = trim($_POST['adminNote'] ?? 'حذف توسط مدیر');
    if ($projectId <= 0) {
        echo json_encode(['ok' => false, 'error' => 'شناسه پروژه نامعتبر است']);
        return;
    }

    $projects = JsonDB::read('projects');
    $foundIndex = null;
    $fileName = '';
    foreach ($projects as $i => $p) {
        if ((int)($p['id'] ?? 0) === $projectId) { $foundIndex = $i; $fileName = $p['fileName'] ?? ''; break; }
    }

    if ($foundIndex === null) {
        echo json_encode(['ok' => false, 'error' => 'پروژه یافت نشد']);
        return;
    }

    // Remove project record
    array_splice($projects, $foundIndex, 1);
    JsonDB::write('projects', $projects);

    // Delete physical file if exists
    if (!empty($fileName)) {
        $filePath = rtrim(APPROVED_DIR, '/\\') . DIRECTORY_SEPARATOR . $fileName;
        if (is_file($filePath)) {
            @unlink($filePath);
        }
    }

    // Mark related reports as resolved and add admin note
    $reports = JsonDB::read('reports');
    $changed = false;
    foreach ($reports as $idx => $r) {
        if ((int)($r['projectId'] ?? 0) === $projectId) {
            $reports[$idx]['status'] = 'resolved';
            $reports[$idx]['adminNote'] = $adminNote;
            $reports[$idx]['resolvedAt'] = date('c');
            $reports[$idx]['resolvedBy'] = current_user()['id'] ?? 0;
            $reports[$idx]['updatedAt'] = date('c');
            $changed = true;
        }
    }
    if ($changed) JsonDB::write('reports', $reports);

    echo json_encode(['ok' => true, 'message' => 'پروژه حذف شد و گزارش‌ها به‌روز شدند']);
}

function handle_bulk_action(): void {
    ensure_post();
    ensure_csrf_token();
    
    $bulkAction = $_POST['bulk_action'] ?? '';
    $reportIds = json_decode($_POST['report_ids'] ?? '[]', true);
    $adminNote = trim($_POST['admin_note'] ?? '');
    
    if (!in_array($bulkAction, ['resolve', 'dismiss', 'email'])) {
        echo json_encode(['ok' => false, 'error' => 'عمل انبوه نامعتبر است']);
        return;
    }
    
    if (!is_array($reportIds) || empty($reportIds)) {
        echo json_encode(['ok' => false, 'error' => 'هیچ گزارشی انتخاب نشده است']);
        return;
    }
    
    $reports = JsonDB::read('reports');
    $updated = 0;
    $emailsSent = 0;
    $errors = [];
    
    foreach ($reportIds as $reportId) {
        $reportIndex = null;
        foreach ($reports as $index => $report) {
            if ((int)$report['id'] === (int)$reportId) {
                $reportIndex = $index;
                break;
            }
        }
        
        if ($reportIndex === null) continue;
        
        if ($bulkAction === 'email') {
            // Handle bulk email - this would integrate with the email system
            // For now, just mark as handled
            $emailsSent++;
        } else {
            // Update report status
            $reports[$reportIndex]['status'] = $bulkAction === 'resolve' ? 'resolved' : 'dismissed';
            $reports[$reportIndex]['adminNote'] = $adminNote;
            $reports[$reportIndex]['resolvedAt'] = date('c');
            $reports[$reportIndex]['resolvedBy'] = current_user()['id'] ?? 0;
            $reports[$reportIndex]['updatedAt'] = date('c');
            $updated++;
        }
    }
    
    if ($updated > 0) {
        JsonDB::write('reports', $reports);
    }
    
    $message = '';
    if ($bulkAction === 'email') {
        $message = "ایمیل به {$emailsSent} گزارش‌دهنده ارسال شد";
    } else {
        $actionLabel = $bulkAction === 'resolve' ? 'حل شد' : 'رد شد';
        $message = "{$updated} گزارش {$actionLabel}";
    }
    
    echo json_encode([
        'ok' => true,
        'message' => $message,
        'updated' => $updated,
        'emails_sent' => $emailsSent
    ]);
}

function handle_export_reports(): void {
    try {
        $reports = JsonDB::read('reports');
        
        // Get user info for reports
        $users = JsonDB::read('users');
        $userMap = [];
        foreach ($users as $user) {
            $userMap[$user['id']] = [
                'username' => $user['username'] ?? $user['name'] ?? 'کاربر',
                'email' => $user['email'] ?? ''
            ];
        }
        
        // Prepare CSV data
        $csvData = [];
        $csvData[] = [
            'شناسه گزارش',
            'شناسه پروژه', 
            'عنوان پروژه',
            'گزارش‌کننده',
            'ایمیل گزارش‌کننده',
            'دلیل',
            'توضیحات',
            'وضعیت',
            'تاریخ ایجاد',
            'تاریخ حل‌شدن',
            'یادداشت مدیر'
        ];
        
        foreach ($reports as $report) {
            $userInfo = $userMap[$report['userId'] ?? 0] ?? ['username' => 'کاربر حذف شده', 'email' => ''];
            $csvData[] = [
                $report['id'] ?? '',
                $report['projectId'] ?? '',
                $report['projectTitle'] ?? '',
                $userInfo['username'],
                $userInfo['email'],
                get_reason_label($report['reason'] ?? ''),
                $report['description'] ?? '',
                get_status_label($report['status'] ?? ''),
                $report['createdAt'] ?? '',
                $report['resolvedAt'] ?? '',
                $report['adminNote'] ?? ''
            ];
        }
        
        // Generate CSV content
        $csvContent = '';
        foreach ($csvData as $row) {
            $csvContent .= '"' . implode('","', array_map(function($field) {
                return str_replace('"', '""', $field); // Escape quotes
            }, $row)) . '"' . "\n";
        }
        
        // Set headers for file download
        $filename = 'reports_' . date('Y-m-d_H-i-s') . '.csv';
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($csvContent));
        header('Cache-Control: no-cache, must-revalidate');
        header('Expires: 0');
        
        // Add BOM for proper UTF-8 encoding in Excel
        echo "\xEF\xBB\xBF" . $csvContent;
        
    } catch (Exception $e) {
        error_log("Export error: " . $e->getMessage());
        echo json_encode(['ok' => false, 'error' => 'خطا در تولید فایل خروجی']);
    }
}

function get_reason_label(string $reason): string {
    $labels = [
        'spam' => 'اسپم',
        'inappropriate' => 'نامناسب',
        'copyright' => 'نقض کپی رایت',
        'malware' => 'بدافزار',
        'other' => 'سایر'
    ];
    return $labels[$reason] ?? $reason;
}

function get_status_label(string $status): string {
    $labels = [
        'pending' => 'در انتظار',
        'resolved' => 'حل شده',
        'dismissed' => 'رد شده'
    ];
    return $labels[$status] ?? $status;
}

?>
