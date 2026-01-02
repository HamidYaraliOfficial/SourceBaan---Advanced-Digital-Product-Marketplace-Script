<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';

// Check if Google OAuth is enabled
if (!defined('GOOGLE_OAUTH_ENABLE') || !GOOGLE_OAUTH_ENABLE) {
    header('Location: /auth.php?error=' . urlencode('Google OAuth is disabled'));
    exit;
}

try {
    // Get authorization code from Google
    $code = $_GET['code'] ?? '';
    $state = $_GET['state'] ?? '';
    $error = $_GET['error'] ?? '';

    if ($error) {
        throw new Exception('Google OAuth error: ' . $error);
    }

    if (!$code) {
        throw new Exception('No authorization code received from Google');
    }

    // Exchange code for access token
    $tokenData = exchangeCodeForToken($code);
    
    if (!$tokenData || !isset($tokenData['access_token'])) {
        throw new Exception('Failed to get access token from Google');
    }

    // Get user info from Google
    $userInfo = getUserInfoFromGoogle($tokenData['access_token']);
    
    if (!$userInfo || !isset($userInfo['email'])) {
        throw new Exception('Failed to get user info from Google');
    }

    // Check if user exists
    $users = JsonDB::read('users');
    $existingUser = null;

    foreach ($users as $user) {
        if ($user['email'] === $userInfo['email']) {
            $existingUser = $user;
            break;
        }
    }

    if ($existingUser) {
        // User exists, log them in
        $_SESSION['user_id'] = $existingUser['id'];
        $_SESSION['username'] = $existingUser['username'];
        $_SESSION['role'] = $existingUser['role'];
        
        // Update last login
        $existingUser['lastLogin'] = date('Y-m-d\TH:i:s\Z');
        
        // Update user in database
        JsonDB::upsert('users', function($users) use ($existingUser) {
            for ($i = 0; $i < count($users); $i++) {
                if ($users[$i]['id'] === $existingUser['id']) {
                    $users[$i] = $existingUser;
                    break;
                }
            }
            return $users;
        });
        
        header('Location: /index.php?success=' . urlencode('ورود موفقیت‌آمیز بود'));
        exit;
    } else {
        // New user, create account
        $username = generateUsernameFromEmail($userInfo['email']);
        $name = $userInfo['name'] ?? $username;
        
        $newUser = [
            'id' => uniqid('user_', true),
            'username' => $username,
            'email' => $userInfo['email'],
            'name' => $name,
            'password' => '', // No password for OAuth users
            'role' => 'user',
            'status' => 'active', // Auto-activate OAuth users
            'emailVerified' => true, // Google accounts are verified
            'profileImage' => $userInfo['picture'] ?? '',
            'googleId' => $userInfo['id'] ?? '',
            'authProvider' => 'google',
            'createdAt' => date('Y-m-d\TH:i:s\Z'),
            'lastLogin' => date('Y-m-d\TH:i:s\Z')
        ];

        // Add new user to database
        JsonDB::upsert('users', function($users) use ($newUser) {
            $users[] = $newUser;
            return $users;
        });
        
        // Log them in
        $_SESSION['user_id'] = $newUser['id'];
        $_SESSION['username'] = $newUser['username'];
        $_SESSION['role'] = $newUser['role'];
        
        header('Location: /index.php?success=' . urlencode('حساب کاربری شما با موفقیت ایجاد شد'));
        exit;
    }

} catch (Exception $e) {
    error_log('Google OAuth Error: ' . $e->getMessage());
    header('Location: /auth.php?error=' . urlencode('خطا در ورود با گوگل: ' . $e->getMessage()));
    exit;
}

function exchangeCodeForToken($code) {
    $postData = [
        'code' => $code,
        'client_id' => GOOGLE_CLIENT_ID,
        'client_secret' => GOOGLE_CLIENT_SECRET,
        'redirect_uri' => GOOGLE_REDIRECT_URI,
        'grant_type' => 'authorization_code'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://oauth2.googleapis.com/token');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded'
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    return json_decode($response, true);
}

function getUserInfoFromGoogle($accessToken) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v2/userinfo');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $accessToken
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200) {
        return null;
    }

    return json_decode($response, true);
}

function generateUsernameFromEmail($email) {
    $username = explode('@', $email)[0];
    $username = preg_replace('/[^a-zA-Z0-9_]/', '', $username);
    
    // Check if username exists
    $users = JsonDB::read('users');
    $counter = 1;
    $originalUsername = $username;
    
    while (true) {
        $exists = false;
        foreach ($users as $user) {
            if ($user['username'] === $username) {
                $exists = true;
                break;
            }
        }
        
        if (!$exists) {
            break;
        }
        
        $username = $originalUsername . $counter;
        $counter++;
    }
    
    return $username;
}
?>
