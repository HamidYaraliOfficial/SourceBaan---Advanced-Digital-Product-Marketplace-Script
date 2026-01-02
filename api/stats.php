<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

try {
    // Get submissions (projects)
    $submissions = JsonDB::read('submissions');
    $approvedProjects = array_filter($submissions, function($submission) {
        return ($submission['status'] ?? '') === 'approved';
    });
    
    // Get users
    $users = JsonDB::read('users');
    
    // Get forum topics for additional activity
    $topics = JsonDB::read('topics');
    
    // Calculate stats
    $totalProjects = count($approvedProjects);
    $totalDevelopers = count($users);
    $totalDownloads = array_sum(array_map(function($project) {
        return $project['downloads'] ?? 0;
    }, $approvedProjects));
    $totalStars = array_sum(array_map(function($project) {
        return $project['stars'] ?? 0;
    }, $approvedProjects));
    
    // Additional stats
    $totalTopics = count($topics);
    $activeUsers = count(array_filter($users, function($user) {
        // Consider users active if they've been active in the last 30 days
        $lastActivity = $user['last_activity'] ?? $user['createdAt'] ?? '';
        if (!$lastActivity) return false;
        
        $lastActivityDate = new DateTime($lastActivity);
        $thirtyDaysAgo = new DateTime('-30 days');
        return $lastActivityDate > $thirtyDaysAgo;
    }));
    
    json_response([
        'success' => true,
        'stats' => [
            'totalProjects' => $totalProjects,
            'totalDevelopers' => $totalDevelopers,
            'totalDownloads' => $totalDownloads,
            'totalStars' => $totalStars,
            'totalTopics' => $totalTopics,
            'activeUsers' => $activeUsers,
            'pendingProjects' => count($submissions) - $totalProjects
        ]
    ]);
    
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
?>
