<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/config.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    http_response_code(400);
    echo 'Bad request';
    exit;
}

// Require authenticated user for downloads (disable unsigned/signed public access)
require_once __DIR__ . '/../includes/auth.php';
$user = current_user();
if (!$user) {
    http_response_code(403);
    echo 'Forbidden';
    exit;
}

// Work with approved submissions as public projects
$submissions = JsonDB::read('submissions');
$project = null;
foreach ($submissions as $i => $p) {
    if ((int)($p['id'] ?? 0) === $id && ($p['status'] ?? 'pending') === 'approved') {
        $project = $p;
        $submissions[$i]['downloads'] = (int)($p['downloads'] ?? 0) + 1;
        break;
    }
}
if (!$project) {
    http_response_code(404);
    echo 'Not found';
    exit;
}
JsonDB::write('submissions', $submissions);

$path = rtrim(APPROVED_DIR, '/\\') . DIRECTORY_SEPARATOR . $project['fileName'];
if (!is_file($path)) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

$filename = basename($path);
$size = (int)filesize($path);
header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . $size);
header('Cache-Control: no-cache');
readfile($path);
exit;
