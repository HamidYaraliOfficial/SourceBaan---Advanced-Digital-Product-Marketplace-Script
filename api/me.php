<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';

require_login();

$user = current_user();
$projects = JsonDB::read('projects');
$submissions = JsonDB::read('submissions');

$myProjects = array_values(array_filter($projects, fn($p) => (int)($p['authorId'] ?? 0) === (int)$user['id']));
$mySubmissions = array_values(array_filter($submissions, fn($s) => (int)($s['authorId'] ?? 0) === (int)$user['id']));

// Get follow stats
$followersCount = get_followers_count((int)$user['id']);
$followingCount = get_following_count((int)$user['id']);

unset($user['password_hash']);

json_response([
    'ok' => true,
    'user' => $user,
    'myProjects' => $myProjects,
    'mySubmissions' => $mySubmissions,
    'followersCount' => $followersCount,
    'followingCount' => $followingCount,
]);
