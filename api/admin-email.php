<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

// Resend API Configuration
if (!defined('RESEND_API_KEY')) {
    define('RESEND_API_KEY', 're_eRiJNkhn_LqNkj2sH8v9iSuUT8frHG7jE');
}
if (!defined('RESEND_API_URL')) {
    define('RESEND_API_URL', 'https://api.resend.com');
}

try {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
    $action = $_POST['action'] ?? '';
    
    // Authentication check
    require_login();
    if (!is_admin()) {
        echo json_encode(['ok' => false, 'error' => 'دسترسی غیرمجاز']);
        exit;
    }
    
    switch ($action) {
        case 'send_email':
            handle_send_email();
            break;
            
        case 'send_bulk_email':
            handle_send_bulk_email();
            break;
            
        case 'test_connection':
            handle_test_connection();
            break;
            
        default:
            echo json_encode(['ok' => false, 'error' => 'عمل نامعتبر است']);
    }

} catch (Throwable $e) {
    error_log("Email API Error: " . $e->getMessage());
    echo json_encode(['ok' => false, 'error' => 'خطای سرور داخلی']);
}

function handle_send_email(): void {
    ensure_csrf_token();
    
    $from = trim($_POST['from'] ?? 'admin@sourcebaan.com');
    $to = trim($_POST['to'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $body = $_POST['body'] ?? '';
    
    // Validation
    if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['ok' => false, 'error' => 'آدرس ایمیل گیرنده نامعتبر است']);
        return;
    }
    
    if (empty($subject)) {
        echo json_encode(['ok' => false, 'error' => 'موضوع ایمیل الزامی است']);
        return;
    }
    
    if (empty($body)) {
        echo json_encode(['ok' => false, 'error' => 'متن ایمیل الزامی است']);
        return;
    }
    
    // Validate sender email
    if (!filter_var($from, FILTER_VALIDATE_EMAIL)) {
        $from = 'admin@sourcebaan.com';
    }
    
    // Send email via Resend
    $result = send_email_via_resend($from, $to, $subject, $body);
    
    if ($result['success']) {
        // Log successful email
        log_email_activity($to, $subject, 'sent', $result['email_id'] ?? '');
        
        echo json_encode([
            'ok' => true,
            'message' => 'ایمیل با موفقیت ارسال شد',
            'email_id' => $result['email_id'] ?? ''
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'error' => $result['error'] ?? 'خطا در ارسال ایمیل'
        ]);
    }
}

function handle_send_bulk_email(): void {
    ensure_csrf_token();
    
    $from = trim($_POST['from'] ?? 'admin@sourcebaan.com');
    $recipients = json_decode($_POST['recipients'] ?? '[]', true);
    $subject = trim($_POST['subject'] ?? '');
    $body = $_POST['body'] ?? '';
    
    if (!is_array($recipients) || empty($recipients)) {
        echo json_encode(['ok' => false, 'error' => 'لیست گیرندگان خالی است']);
        return;
    }
    
    if (empty($subject) || empty($body)) {
        echo json_encode(['ok' => false, 'error' => 'موضوع و متن ایمیل الزامی است']);
        return;
    }
    
    $results = [];
    $successful = 0;
    $failed = 0;
    
    // Send emails in batches to avoid overwhelming the API
    $batches = array_chunk($recipients, 10); // Process 10 emails at a time
    
    foreach ($batches as $batch) {
        $batchEmails = [];
        
        foreach ($batch as $recipient) {
            if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                $batchEmails[] = [
                    'from' => $from,
                    'to' => [$recipient],
                    'subject' => $subject,
                    'html' => $body
                ];
            }
        }
        
        if (!empty($batchEmails)) {
            $batchResult = send_batch_email_via_resend($batchEmails);
            
            if ($batchResult['success']) {
                $successful += count($batchEmails);
                // Log successful batch
                foreach ($batch as $recipient) {
                    if (filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
                        log_email_activity($recipient, $subject, 'sent', 'batch_' . time());
                    }
                }
            } else {
                $failed += count($batchEmails);
            }
            
            $results[] = $batchResult;
        }
        
        // Small delay between batches to be respectful to the API
        usleep(250000); // 250ms delay
    }
    
    echo json_encode([
        'ok' => true,
        'message' => "ارسال کامل شد. موفق: {$successful}، ناموفق: {$failed}",
        'successful' => $successful,
        'failed' => $failed,
        'details' => $results
    ]);
}

function handle_test_connection(): void {
    $testResult = test_resend_connection();
    
    if ($testResult['success']) {
        echo json_encode([
            'ok' => true,
            'message' => 'اتصال به سرویس ایمیل موفقیت‌آمیز است'
        ]);
    } else {
        echo json_encode([
            'ok' => false,
            'error' => $testResult['error'] ?? 'خطا در اتصال به سرویس ایمیل'
        ]);
    }
}

function send_email_via_resend(string $from, string $to, string $subject, string $body): array {
    $data = [
        'from' => $from,
        'to' => [$to],
        'subject' => $subject,
        'html' => $body
    ];
    
    return make_resend_request('/emails', 'POST', $data);
}

function send_batch_email_via_resend(array $emails): array {
    return make_resend_request('/emails/batch', 'POST', $emails);
}

function test_resend_connection(): array {
    // Simple API key validation by making a domains request
    return make_resend_request('/domains', 'GET');
}

function make_resend_request(string $endpoint, string $method = 'POST', array $data = null): array {
    $url = rtrim(RESEND_API_URL, '/') . $endpoint;
    
    $headers = [
        'Authorization: Bearer ' . RESEND_API_KEY,
        'Content-Type: application/json',
        'User-Agent: SourceBaan-Admin/1.0'
    ];
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_FOLLOWLOCATION => false
    ]);
    
    if ($data !== null && in_array($method, ['POST', 'PUT', 'PATCH'])) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    }
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);
    
    if ($error) {
        error_log("CURL Error: " . $error);
        return [
            'success' => false,
            'error' => 'خطا در اتصال به سرویس ایمیل'
        ];
    }
    
    $decodedResponse = json_decode($response, true);
    
    if ($httpCode >= 200 && $httpCode < 300) {
        return [
            'success' => true,
            'data' => $decodedResponse,
            'email_id' => $decodedResponse['id'] ?? null
        ];
    } else {
        $errorMessage = 'خطا در ارسال ایمیل';
        
        if ($decodedResponse && isset($decodedResponse['message'])) {
            $errorMessage = $decodedResponse['message'];
        } elseif ($decodedResponse && isset($decodedResponse['error'])) {
            $errorMessage = $decodedResponse['error'];
        }
        
        error_log("Resend API Error (HTTP {$httpCode}): " . $response);
        
        return [
            'success' => false,
            'error' => $errorMessage,
            'http_code' => $httpCode,
            'response' => $decodedResponse
        ];
    }
}

function log_email_activity(string $to, string $subject, string $status, string $emailId = ''): void {
    $currentUser = current_user();
    $logEntry = [
        'id' => time() . '_' . mt_rand(1000, 9999),
        'admin_id' => $currentUser['id'] ?? 0,
        'admin_name' => $currentUser['name'] ?? 'Unknown',
        'to' => $to,
        'subject' => $subject,
        'status' => $status,
        'email_id' => $emailId,
        'timestamp' => date('c'),
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ];
    
    try {
        $logs = JsonDB::read('email_logs') ?: [];
        $logs[] = $logEntry;
        
        // Keep only last 1000 email logs to prevent file from getting too large
        if (count($logs) > 1000) {
            $logs = array_slice($logs, -1000);
        }
        
        JsonDB::write('email_logs', $logs);
    } catch (Exception $e) {
        error_log("Failed to log email activity: " . $e->getMessage());
    }
}

function get_email_template(string $templateType, array $variables = []): array {
    $templates = [
        'welcome' => [
            'subject' => 'خوش آمدید به ' . ($variables['site_name'] ?? 'SourceBaan'),
            'body' => generate_welcome_template($variables)
        ],
        'report_resolved' => [
            'subject' => 'گزارش شما بررسی شد - ' . ($variables['project_title'] ?? 'پروژه'),
            'body' => generate_report_resolved_template($variables)
        ],
        'report_dismissed' => [
            'subject' => 'بررسی گزارش شما - ' . ($variables['project_title'] ?? 'پروژه'),
            'body' => generate_report_dismissed_template($variables)
        ],
        'project_updated' => [
            'subject' => 'پروژه شما به‌روز شد - ' . ($variables['project_title'] ?? 'پروژه'),
            'body' => generate_project_updated_template($variables)
        ]
    ];
    
    return $templates[$templateType] ?? ['subject' => '', 'body' => ''];
}

function generate_welcome_template(array $vars): string {
    $siteName = htmlspecialchars($vars['site_name'] ?? 'SourceBaan');
    $userName = htmlspecialchars($vars['user_name'] ?? 'کاربر گرامی');
    
    return <<<HTML
    <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #3b82f6; font-size: 28px; margin: 0;">🎉 خوش آمدید به {$siteName}</h1>
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                سلام {$userName}،
            </p>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                خوشحالیم که به جامعه {$siteName} پیوستید! اکنون می‌توانید از تمامی امکانات سایت استفاده کنید.
            </p>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px;">
                    تیم {$siteName}<br>
                    <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                </p>
            </div>
        </div>
    </div>
HTML;
}

function generate_report_resolved_template(array $vars): string {
    $projectTitle = htmlspecialchars($vars['project_title'] ?? 'پروژه');
    $userName = htmlspecialchars($vars['user_name'] ?? 'کاربر گرامی');
    $adminNote = htmlspecialchars($vars['admin_note'] ?? '');
    
    $adminNoteHtml = '';
    if ($adminNote) {
        $adminNoteHtml = '<p style="margin: 10px 0 0 0; color: #374151; font-style: italic;">توضیح مدیر: ' . $adminNote . '</p>';
    }
    
    return <<<HTML
    <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #059669; font-size: 28px; margin: 0;">✅ گزارش شما بررسی شد</h1>
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                سلام {$userName}،
            </p>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                گزارش شما در خصوص پروژه "<strong>{$projectTitle}</strong>" بررسی و پیگیری شد.
            </p>
            
            <div style="background: #dcfce7; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #059669;">
                <p style="margin: 0; color: #059669; font-weight: bold;">✅ وضعیت: حل شده</p>
                <p style="margin: 10px 0 0 0; color: #374151;">اقدامات لازم انجام شده و مشکل برطرف شده است.</p>
                {$adminNoteHtml}
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                از همکاری شما در بهبود کیفیت محتوای سایت متشکریم.
            </p>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px;">
                    تیم پشتیبانی SourceBaan<br>
                    <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                </p>
            </div>
        </div>
    </div>
HTML;
}

function generate_report_dismissed_template(array $vars): string {
    $projectTitle = htmlspecialchars($vars['project_title'] ?? 'پروژه');
    $userName = htmlspecialchars($vars['user_name'] ?? 'کاربر گرامی');
    $adminNote = htmlspecialchars($vars['admin_note'] ?? '');
    
    $adminNoteHtml = '';
    if ($adminNote) {
        $adminNoteHtml = '<p style="margin: 10px 0 0 0; color: #374151; font-style: italic;">توضیح مدیر: ' . $adminNote . '</p>';
    }
    
    return <<<HTML
    <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #dc2626; font-size: 28px; margin: 0;">📋 بررسی گزارش شما</h1>
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                سلام {$userName}،
            </p>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                گزارش شما در خصوص پروژه "<strong>{$projectTitle}</strong>" بررسی شد.
            </p>
            
            <div style="background: #fef3c7; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #f59e0b;">
                <p style="margin: 0; color: #d97706; font-weight: bold;">ℹ️ وضعیت: رد شده</p>
                <p style="margin: 10px 0 0 0; color: #374151;">پس از بررسی، محتوای گزارش شده در حال حاضر مشکلی ندارد.</p>
                {$adminNoteHtml}
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                در صورت وجود سوال یا نیاز به توضیح بیشتر، می‌توانید با تیم پشتیبانی تماس بگیرید.
            </p>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px;">
                    تیم پشتیبانی SourceBaan<br>
                    <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                </p>
            </div>
        </div>
    </div>
HTML;
}

function generate_project_updated_template(array $vars): string {
    $projectTitle = htmlspecialchars($vars['project_title'] ?? 'پروژه');
    $userName = htmlspecialchars($vars['user_name'] ?? 'کاربر گرامی');
    $updateDetails = htmlspecialchars($vars['update_details'] ?? 'اطلاعات و محتوای پروژه بهبود یافته است.');
    
    return <<<HTML
    <div style="font-family: 'Vazirmatn', Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; background: #f8f9fa;">
        <div style="background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1);">
            <div style="text-align: center; margin-bottom: 30px;">
                <h1 style="color: #3b82f6; font-size: 28px; margin: 0;">🔄 پروژه شما به‌روز شد</h1>
            </div>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                سلام {$userName}،
            </p>
            
            <p style="font-size: 16px; line-height: 1.8; color: #374151; margin-bottom: 20px;">
                پروژه "<strong>{$projectTitle}</strong>" توسط تیم ما بهبود یافته است.
            </p>
            
            <div style="background: #dbeafe; padding: 20px; border-radius: 10px; margin: 20px 0; border-left: 4px solid #3b82f6;">
                <p style="margin: 0; color: #1d4ed8; font-weight: bold;">🔄 وضعیت: به‌روز شد</p>
                <p style="margin: 10px 0 0 0; color: #374151;">{$updateDetails}</p>
            </div>
            
            <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                <p style="color: #6b7280; font-size: 14px;">
                    تیم پشتیبانی SourceBaan<br>
                    <a href="mailto:admin@sourcebaan.com" style="color: #3b82f6;">admin@sourcebaan.com</a>
                </p>
            </div>
        </div>
    </div>
HTML;
}

?>
