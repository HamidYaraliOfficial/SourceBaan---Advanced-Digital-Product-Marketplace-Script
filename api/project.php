<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $id = (int)($_GET['id'] ?? 0);
    if ($id <= 0) { json_response(['ok' => false, 'error' => 'شناسه نامعتبر'], 400); }

    $subs = JsonDB::read('submissions');
    $project = null;
    foreach ($subs as $p) {
        if ((int)($p['id'] ?? 0) === $id && ($p['status'] ?? 'pending') === 'approved') { $project = $p; break; }
    }
    if (!$project) { json_response(['ok' => false, 'error' => 'پروژه یافت نشد'], 404); }

    // Enrich author
    $author = get_user_by_id((int)($project['authorId'] ?? 0));
    if ($author) {
        unset($author['password_hash']);
        $project['authorProfile'] = [
            'id' => (int)$author['id'],
            'name' => $author['name'],
            'verified' => !empty($author['verified']),
            'username' => $author['username'] ?? '',
            'points' => (int)($author['points'] ?? 0),
            'createdAt' => $author['createdAt'] ?? ''
        ];
    }

    // Comments (latest first)
    $comments = JsonDB::read('comments');
    $projectComments = array_values(array_filter($comments, fn($c) => (int)($c['projectId'] ?? 0) === $id));
    usort($projectComments, fn($a, $b) => strtotime($b['createdAt'] ?? '') - strtotime($a['createdAt'] ?? ''));

    // ensure views is integer
    $project['views'] = (int)($project['views'] ?? 0);
    json_response(['ok' => true, 'project' => $project, 'comments' => $projectComments]);
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}


