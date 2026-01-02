<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/activity.php';

// require_login(); // Remove this for track_download to work for guests

$action = $_GET['action'] ?? $_POST['action'] ?? '';
if (is_ip_banned()) {
    json_response(['ok' => false, 'error' => 'دسترسی شما مسدود شده است'], 403);
}
$user = current_user();

try {
    // ستاره دادن/برداشتن به پروژه
    if ($action === 'toggle_star') {
        require_login(); // Login required for stars
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $projectId = (int)($_POST['projectId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        
        $projects = JsonDB::read('submissions');
        $project = null;
        $projectIndex = -1;
        
        foreach ($projects as $i => $p) {
            if ((int)$p['id'] === $projectId) {
                $project = $p;
                $projectIndex = $i;
                break;
            }
        }
        
        if (!$project) throw new RuntimeException('پروژه یافت نشد');
        
        // بررسی ستاره‌های قبلی کاربر
        $stars = JsonDB::read('stars');
        $userStarIndex = -1;
        
        foreach ($stars as $i => $star) {
            if ((int)$star['projectId'] === $projectId && (int)$star['userId'] === (int)$user['id']) {
                $userStarIndex = $i;
                break;
            }
        }
        
        if ($userStarIndex >= 0) {
            // برداشتن ستاره
            array_splice($stars, $userStarIndex, 1);
            $projects[$projectIndex]['stars'] = max(0, ($project['stars'] ?? 0) - 1);
            $starred = false;
        } else {
            // اضافه کردن ستاره
            $stars[] = [
                'id' => JsonDB::nextId(),
                'projectId' => $projectId,
                'userId' => (int)$user['id'],
                'createdAt' => date('c'),
            ];
            $projects[$projectIndex]['stars'] = ($project['stars'] ?? 0) + 1;
            $starred = true;
            
            // اعطای امتیاز به صاحب پروژه
            if ((int)$project['authorId'] !== (int)$user['id']) {
                $users = JsonDB::read('users');
                foreach ($users as $i => $u) {
                    if ((int)$u['id'] === (int)$project['authorId']) {
                        $users[$i]['points'] = ($u['points'] ?? 0) + 5;
                        break;
                    }
                }
                JsonDB::write('users', $users);
            }
        }
        
        JsonDB::write('stars', $stars);
        JsonDB::write('submissions', $projects);
        
        json_response([
            'ok' => true, 
            'starred' => $starred, 
            'stars' => $projects[$projectIndex]['stars']
        ]);
    }
    
    // ثبت دانلود پروژه
    if ($action === 'track_download') {
        // Guests allowed, but must be POST + same-origin + CSRF
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $projectId = (int)($_POST['projectId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        
        $projects = JsonDB::read('submissions');
        $projectIndex = -1;
        
        foreach ($projects as $i => $p) {
            if ((int)$p['id'] === $projectId && ($p['status'] ?? 'pending') === 'approved') {
                $projectIndex = $i;
                break;
            }
        }
        
        if ($projectIndex >= 0) {
            $projects[$projectIndex]['downloads'] = ($projects[$projectIndex]['downloads'] ?? 0) + 1;
            JsonDB::write('submissions', $projects);
            
            json_response([
                'ok' => true, 
                'downloads' => $projects[$projectIndex]['downloads']
            ]);
        } else {
            throw new RuntimeException('پروژه یافت نشد');
        }
    }

    // ثبت بازدید پروژه
    if ($action === 'track_view') {
        // Allow public view tracking but enforce POST to prevent GET abuse
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $projectId = (int)($_POST['projectId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        
        $projects = JsonDB::read('submissions');
        $projectIndex = -1;
        
        foreach ($projects as $i => $p) {
            if ((int)$p['id'] === $projectId && ($p['status'] ?? 'pending') === 'approved') {
                $projectIndex = $i;
                break;
            }
        }
        
        if ($projectIndex >= 0) {
            $projects[$projectIndex]['views'] = ($projects[$projectIndex]['views'] ?? 0) + 1;
            JsonDB::write('submissions', $projects);
            
            json_response([
                'ok' => true, 
                'views' => $projects[$projectIndex]['views']
            ]);
        } else {
            throw new RuntimeException('پروژه یافت نشد');
        }
    }
    
    // دریافت نظرات پروژه
    if ($action === 'get_comments') {
        // No login required to view comments
        $projectId = (int)($_GET['projectId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        
        $comments = JsonDB::read('comments');
        $projectComments = array_filter($comments, fn($c) => (int)($c['projectId'] ?? 0) === $projectId);
        
        // مرتب‌سازی بر اساس تاریخ
        usort($projectComments, fn($a, $b) => strtotime($b['createdAt']) - strtotime($a['createdAt']));
        
        json_response(['ok' => true, 'comments' => array_values($projectComments)]);
    }
    
    // اضافه کردن نظر
    if ($action === 'add_comment') {
        require_login(); // Login required to add comments
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $projectId = (int)($_POST['projectId'] ?? 0);
        $content = trim($_POST['content'] ?? '');
        $parentId = (int)($_POST['parentId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        if (empty($content)) throw new RuntimeException('متن نظر الزامی است');
        if (strlen($content) < 5) throw new RuntimeException('نظر باید حداقل 5 کاراکتر باشد');
        
        $comment = [
            'id' => JsonDB::nextId(),
            'projectId' => $projectId,
            'userId' => (int)$user['id'],
            'userName' => $user['name'],
            'content' => $content,
            'parentId' => $parentId > 0 ? $parentId : null,
            'likes' => 0,
            'createdAt' => date('c'),
        ];
        
        JsonDB::upsert('comments', function($comments) use ($comment) {
            $comments[] = $comment;
            return $comments;
        });
        
        // اعطای امتیاز برای نظردهی
        $users = JsonDB::read('users');
        foreach ($users as $i => $u) {
            if ((int)$u['id'] === (int)$user['id']) {
                $users[$i]['points'] = ($u['points'] ?? 0) + 2;
                break;
            }
        }
        JsonDB::write('users', $users);
        
        add_activity("{$user['name']} نظری ثبت کرد", 'comment');
        
        json_response(['ok' => true, 'comment' => $comment]);
    }
    
    // لایک کردن نظر
    if ($action === 'toggle_comment_like') {
        require_login(); // Login required to like comments
        ensure_post();
        ensure_same_origin();
        ensure_csrf_token();
        $commentId = (int)($_POST['commentId'] ?? 0);
        
        if ($commentId <= 0) throw new RuntimeException('شناسه نظر نامعتبر است');
        
        $commentLikes = JsonDB::read('comment_likes');
        $userLikeIndex = -1;
        
        foreach ($commentLikes as $i => $like) {
            if ((int)$like['commentId'] === $commentId && (int)$like['userId'] === (int)$user['id']) {
                $userLikeIndex = $i;
                break;
            }
        }
        
        if ($userLikeIndex >= 0) {
            // برداشتن لایک
            array_splice($commentLikes, $userLikeIndex, 1);
            $liked = false;
        } else {
            // اضافه کردن لایک
            $commentLikes[] = [
                'id' => JsonDB::nextId(),
                'commentId' => $commentId,
                'userId' => (int)$user['id'],
                'createdAt' => date('c'),
            ];
            $liked = true;
        }
        
        JsonDB::write('comment_likes', $commentLikes);
        
        // به‌روزرسانی تعداد لایک‌ها در نظر
        $comments = JsonDB::read('comments');
        foreach ($comments as $i => $comment) {
            if ((int)$comment['id'] === $commentId) {
                $totalLikes = count(array_filter($commentLikes, fn($l) => (int)$l['commentId'] === $commentId));
                $comments[$i]['likes'] = $totalLikes;
                break;
            }
        }
        JsonDB::write('comments', $comments);
        
        json_response(['ok' => true, 'liked' => $liked]);
    }
    
    // دریافت وضعیت ستاره و لایک‌های کاربر
    if ($action === 'get_user_interactions') {
        $projectId = (int)($_GET['projectId'] ?? 0);
        
        if ($projectId <= 0) throw new RuntimeException('شناسه پروژه نامعتبر است');
        
        $stars = JsonDB::read('stars');
        $commentLikes = JsonDB::read('comment_likes');
        
        $userStar = array_filter($stars, fn($s) => 
            (int)$s['projectId'] === $projectId && (int)$s['userId'] === (int)$user['id']
        );
        
        $userCommentLikes = array_filter($commentLikes, fn($l) => 
            (int)$l['userId'] === (int)$user['id']
        );
        
        json_response([
            'ok' => true,
            'starred' => !empty($userStar),
            'likedComments' => array_column($userCommentLikes, 'commentId')
        ]);
    }
    
    json_response(['ok' => false, 'error' => 'درخواست نامعتبر'], 400);
    
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}
?>
