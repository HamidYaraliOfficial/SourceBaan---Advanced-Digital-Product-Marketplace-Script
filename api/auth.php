<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';

// Ensure required helpers are available
if (!function_exists('current_user')) {
    require_once __DIR__ . '/../includes/auth.php';
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? '');
$action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    if ($method === 'GET' && ($action === '' || $action === 'me')) {
        $u = function_exists('current_user') ? current_user() : null;
        if ($u) {
            unset($u['password_hash']);
        }
        json_response(['ok' => true, 'user' => $u]);
    }

    if ($method === 'POST' && $action === 'check_username') {
        ensure_post();
        ensure_csrf_token();
        $username = trim($_POST['username'] ?? '');
        if ($username === '') {
            json_response(['ok' => false, 'error' => 'نام کاربری الزامی است'], 400);
        }
        $existing = find_user_by_username($username);
        json_response(['ok' => true, 'available' => !$existing]);
    }

    if ($method === 'GET' && $action === 'captcha') {
        if (!CAPTCHA_ENABLE) {
            json_response(['ok' => true, 'captcha' => null]);
        }
        $c = captcha_generate();
        json_response(['ok' => true, 'captcha' => $c]);
    }

    if ($method === 'POST' && $action === 'send_verification_code') {
        ensure_post();
        ensure_csrf_token();
        if (is_ip_banned()) { throw new RuntimeException('دسترسی شما مسدود شده است'); }
        // CAPTCHA check to prevent abuse of email sending
        if (CAPTCHA_ENABLE) {
            $captcha_token = (string)($_POST['captcha_token'] ?? '');
            $captcha_value = (string)($_POST['captcha_value'] ?? '');
            if (!captcha_verify($captcha_token, $captcha_value)) {
                throw new RuntimeException('کپچا نامعتبر است');
            }
            captcha_clear();
        }
        $email = trim($_POST['email'] ?? '');
        $username = trim($_POST['username'] ?? '');
        
        if ($email === '') {
            throw new RuntimeException('ایمیل الزامی است');
        }
        
        // Validate email with allowed domains
        if (!validate_email_limited_domains($email)) {
            throw new RuntimeException('ایمیل نامعتبر است یا دامنه مجاز نیست (gmail, icloud, yahoo, hotmail)');
        }
        
        // Check if email already exists
        $existing = find_user_by_email($email);
        if ($existing) {
            throw new RuntimeException('این ایمیل قبلاً ثبت شده است');
        }
        
        // Check if username already exists
        if ($username !== '') {
            $existing = find_user_by_username($username);
            if ($existing) {
                throw new RuntimeException('این نام کاربری قبلاً انتخاب شده است');
            }
        }
        
        // Generate 6-digit verification code
        $verificationCode = str_pad((string)random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        
        // Store verification code
        store_verification_code($email, $verificationCode);
        
        // Attempt to send email only; do not expose code in API or UI
        $sent = send_verification_email($email, $verificationCode);
        $message = $sent ? 'کد تایید ارسال شد' : 'ارسال کد به ایمیل انجام شد';
        json_response(['ok' => true, 'message' => $message]);
    }

    if ($method === 'POST' && $action === 'verify_and_register') {
        ensure_post();
        ensure_csrf_token();
        if (is_ip_banned()) { throw new RuntimeException('دسترسی شما مسدود شده است'); }
        $verificationCode = trim($_POST['verification_code'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $name = sanitize_text_field(trim($_POST['name'] ?? ''), 80);
        $password = (string)($_POST['password'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $province = sanitize_text_field(trim($_POST['province'] ?? ''), 60);
        $city = sanitize_text_field(trim($_POST['city'] ?? ''), 60);
        $languages = $_POST['programming_languages'] ?? [];
        
        if ($verificationCode === '' || $email === '') {
            throw new RuntimeException('کد تایید و ایمیل الزامی است');
        }
        
        // Verify the code
        if (!verify_code($email, $verificationCode)) {
            throw new RuntimeException('کد تایید نادرست یا منقضی شده است');
        }
        
        // Proceed with registration (same validations as before)
        if ($name === '' || $password === '') {
            throw new RuntimeException('نام و رمز عبور الزامی است');
        }
        // Name validation removed - allow any characters
        if (!validate_email_limited_domains($email)) {
            throw new RuntimeException('ایمیل نامعتبر است یا دامنه مجاز نیست (gmail, icloud, yahoo, hotmail)');
        }
        
        // Minimal username requirement: non-empty and unique (format unrestricted)
        if ($username === '') {
            throw new RuntimeException('نام کاربری الزامی است');
        }
        // Keep profanity check for name/email only
        $profanityWords = ['fuck', 'shit', 'damn', 'bitch', 'bastard', 'ass', 'hell'];
        foreach ($profanityWords as $word) {
            if (stripos($email, $word) !== false || stripos($name, $word) !== false) {
                throw new RuntimeException('متن حاوی کلمات نامناسب است و مجاز نیست');
            }
        }
        
        if (!validate_password_basic($password)) {
            throw new RuntimeException('رمز عبور باید حداقل ۸ کاراکتر باشد');
        }
        if ($phone !== '' && !validate_phone_digits($phone)) {
            throw new RuntimeException('شماره تلفن نامعتبر است (فقط اعداد)');
        }
        
        $additional_data = [
            'username' => $username,
            'phone' => $phone,
            'province' => $province,
            'city' => $city,
            'programming_languages' => is_array($languages) ? $languages : explode(',', $languages)
        ];
        
        $u = register_user($name, $email, $password, $additional_data);
        
        // Clean up verification code
        remove_verification_code($email);
        
        unset($u['password_hash']);
        json_response(['ok' => true, 'user' => $u]);
    }

    if ($method === 'POST' && $action === 'register') {
        ensure_post();
        ensure_csrf_token();
        if (is_ip_banned()) { throw new RuntimeException('دسترسی شما مسدود شده است'); }
        // CAPTCHA check
        if (CAPTCHA_ENABLE) {
            $captcha_token = (string)($_POST['captcha_token'] ?? '');
            $captcha_value = (string)($_POST['captcha_value'] ?? '');
            if (!captcha_verify($captcha_token, $captcha_value)) {
                throw new RuntimeException('کپچا نامعتبر است');
            }
            captcha_clear();
        }
        $name = sanitize_text_field(trim($_POST['name'] ?? ''), 80);
        $email = trim($_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $province = sanitize_text_field(trim($_POST['province'] ?? ''), 60);
        $city = sanitize_text_field(trim($_POST['city'] ?? ''), 60);
        $languages = $_POST['programming_languages'] ?? [];
        
        if ($name === '' || $email === '' || $password === '') {
            throw new RuntimeException('نام، ایمیل و رمز عبور الزامی است');
        }

        // Name validation removed - allow any characters
        if (!validate_email_limited_domains($email)) {
            throw new RuntimeException('ایمیل نامعتبر است یا دامنه مجاز نیست (gmail, icloud, yahoo, hotmail)');
        }
        // Minimal username requirement: non-empty (format unrestricted)
        if ($username === '') {
            throw new RuntimeException('نام کاربری الزامی است');
        }
        // Keep profanity check for name/email only
        $profanityWords = ['fuck', 'shit', 'damn', 'bitch', 'bastard', 'ass', 'hell'];
        foreach ($profanityWords as $word) {
            if (stripos($email, $word) !== false || stripos($name, $word) !== false) {
                throw new RuntimeException('متن حاوی کلمات نامناسب است و مجاز نیست');
            }
        }
        
        if (!validate_password_basic($password)) {
            throw new RuntimeException('رمز عبور باید حداقل ۸ کاراکتر باشد');
        }
        if ($phone !== '' && !validate_phone_digits($phone)) {
            throw new RuntimeException('شماره تلفن نامعتبر است (فقط اعداد)');
        }
        
        $additional_data = [
            'username' => $username,
            'phone' => $phone,
            'province' => $province,
            'city' => $city,
            'programming_languages' => is_array($languages) ? $languages : explode(',', $languages)
        ];
        
        $u = register_user($name, $email, $password, $additional_data);
        unset($u['password_hash']);
        json_response(['ok' => true, 'user' => $u]);
    }

    if ($method === 'POST' && $action === 'login') {
        ensure_post();
        ensure_csrf_token();
        if (is_ip_banned()) { throw new RuntimeException('دسترسی شما مسدود شده است'); }
        // CAPTCHA check
        if (CAPTCHA_ENABLE) {
            $captcha_token = (string)($_POST['captcha_token'] ?? '');
            $captcha_value = (string)($_POST['captcha_value'] ?? '');
            if (!captcha_verify($captcha_token, $captcha_value)) {
                throw new RuntimeException('کپچا نامعتبر است');
            }
            captcha_clear();
        }
        $identifier = trim($_POST['identifier'] ?? $_POST['email'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $u = login_user($identifier, $password);
        if (is_user_suspended($u)) { throw new RuntimeException('حساب کاربری شما معلق شده است'); }
        unset($u['password_hash']);
        json_response(['ok' => true, 'user' => $u]);
    }

    if ($method === 'POST' && $action === 'logout') {
        logout_user();
        json_response(['ok' => true]);
    }

    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
