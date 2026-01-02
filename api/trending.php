<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/utils.php';

try {
    // Get approved submissions
    $submissions = JsonDB::read('submissions');
    $approvedProjects = array_filter($submissions, function($submission) {
        return ($submission['status'] ?? '') === 'approved';
    });
    
    // Convert to array values to reindex
    $approvedProjects = array_values($approvedProjects);
    
    // Calculate trending score for each project
    $projectsWithScore = array_map(function($project) {
        $downloads = $project['downloads'] ?? 0;
        $stars = $project['stars'] ?? 0;
        $views = $project['views'] ?? 0;
        
        // Calculate days since creation
        $createdAt = new DateTime($project['createdAt'] ?? 'now');
        $now = new DateTime();
        $daysSinceCreation = max(1, $now->diff($createdAt)->days);
        
        // Trending score (higher is better, newer projects get boost)
        $trendingScore = ($downloads * 3 + $stars * 5 + $views * 1) / $daysSinceCreation;
        
        $project['trending_score'] = $trendingScore;
        return $project;
    }, $approvedProjects);
    
    // Sort by trending score (highest first)
    usort($projectsWithScore, function($a, $b) {
        return $b['trending_score'] <=> $a['trending_score'];
    });
    
    // Get top 6 trending projects
    $trendingProjects = array_slice($projectsWithScore, 0, 6);
    
    // Remove trending_score from output
    $trendingProjects = array_map(function($project) {
        unset($project['trending_score']);
        return $project;
    }, $trendingProjects);
    
    json_response([
        'success' => true,
        'projects' => $trendingProjects
    ]);
    
} catch (Throwable $e) {
    json_response([
        'success' => false,
        'error' => $e->getMessage()
    ], 500);
}
?>
