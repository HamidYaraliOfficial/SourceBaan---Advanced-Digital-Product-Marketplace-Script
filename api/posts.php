<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Includes
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    require_once __DIR__ . '/../includes/auth.php';
    require_once __DIR__ . '/../includes/utils.php';
    require_once __DIR__ . '/../includes/content-filter.php';
    
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    
    // Get posts for a topic
    if ($action === 'get_posts') {
        $topicId = (int)($_GET['topicId'] ?? 0);
        
        if ($topicId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه موضوع نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $posts = JsonDB::read('posts');
        $topicPosts = [];
        
        foreach ($posts as $post) {
            if ((int)($post['topicId'] ?? 0) === $topicId && 
                ($post['status'] ?? 'active') === 'active') {
                $topicPosts[] = $post;
            }
        }
        
        // Sort by creation date
        usort($topicPosts, function($a, $b) {
            return strtotime($a['createdAt'] ?? '') - strtotime($b['createdAt'] ?? '');
        });
        
        echo json_encode(['success' => true, 'posts' => $topicPosts], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Add a post (reply)
    if ($action === 'add_post') {
        ensure_post();
        if (!current_user()) {
            echo json_encode(['success' => false, 'error' => 'برای ارسال پاسخ وارد شوید'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $user = current_user();
        $topicId = (int)($_POST['topicId'] ?? 0);
        $content = trim((string)($_POST['content'] ?? ''));
        
        if ($topicId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه موضوع نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if ($content === '') {
            echo json_encode(['success' => false, 'error' => 'محتوای پاسخ الزامی است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Topic must exist and not be closed/deleted
        $topics = JsonDB::read('topics');
        $topicIndex = -1;
        foreach ($topics as $i => $t) {
            if ((int)($t['id'] ?? 0) === $topicId && ($t['status'] ?? 'active') !== 'deleted') {
                $topicIndex = $i;
                break;
            }
        }
        if ($topicIndex === -1) {
            echo json_encode(['success' => false, 'error' => 'موضوع یافت نشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        if (($topics[$topicIndex]['status'] ?? 'open') === 'closed') {
            echo json_encode(['success' => false, 'error' => 'این موضوع بسته شده است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Filter content
        $filter = ContentFilter::filterText($content);
        if (!$filter['passed']) {
            ContentFilter::logContentIssue((int)$user['id'], $content, $filter['issues']);
            echo json_encode(['success' => false, 'error' => 'متن پاسخ حاوی محتوای نامناسب است'], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $post = [
            'id' => JsonDB::nextId(),
            'topicId' => $topicId,
            'userId' => (int)$user['id'],
            'userName' => (string)$user['name'],
            'content' => (string)$filter['cleanText'],
            'createdAt' => date('c'),
            'updatedAt' => date('c'),
            'likes' => 0,
            'status' => 'active',
        ];

        JsonDB::upsert('posts', function(array $items) use ($post) {
            $items[] = $post;
            return $items;
        });
        
        // Update topic stats
        $topics[$topicIndex]['postCount'] = (int)($topics[$topicIndex]['postCount'] ?? 0) + 1;
        $topics[$topicIndex]['lastPostAt'] = date('c');
        $topics[$topicIndex]['lastPostUserId'] = (int)$user['id'];
        JsonDB::write('topics', $topics);
        
        echo json_encode(['success' => true, 'post' => $post], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Delete a post (admin only)
    if ($action === 'delete_post') {
        ensure_post();
        if (!is_admin()) {
            echo json_encode(['success' => false, 'error' => 'اجازه دسترسی ندارید'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $postId = (int)($_POST['postId'] ?? 0);
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه پاسخ نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $posts = JsonDB::read('posts');
        $found = false;
        foreach ($posts as $i => $post) {
            if ((int)($post['id'] ?? 0) === $postId) {
                $posts[$i]['status'] = 'deleted';
                $found = true;
                break;
            }
        }
        if (!$found) {
            echo json_encode(['success' => false, 'error' => 'پاسخ یافت نشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        JsonDB::write('posts', $posts);
        echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Toggle like on a post
    if ($action === 'toggle_post_like') {
        ensure_post();
        if (!current_user()) {
            echo json_encode(['success' => false, 'error' => 'برای لایک وارد شوید'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $user = current_user();
        $postId = (int)($_POST['postId'] ?? 0);
        if ($postId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه پاسخ نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        $likes = JsonDB::read('post_likes');
        $likeIndex = -1;
        foreach ($likes as $i => $like) {
            if ((int)($like['postId'] ?? 0) === $postId && (int)($like['userId'] ?? 0) === (int)$user['id']) {
                $likeIndex = $i;
                break;
            }
        }
        $liked = false;
        if ($likeIndex >= 0) {
            // remove like
            array_splice($likes, $likeIndex, 1);
            $liked = false;
        } else {
            // add like
            $likes[] = [
                'id' => JsonDB::nextId(),
                'postId' => $postId,
                'userId' => (int)$user['id'],
                'createdAt' => date('c'),
            ];
            $liked = true;
        }
        JsonDB::write('post_likes', $likes);
        
        // Update like count on post
        $posts = JsonDB::read('posts');
        foreach ($posts as $i => $post) {
            if ((int)($post['id'] ?? 0) === $postId) {
                $posts[$i]['likes'] = count(array_filter($likes, function($l) use ($postId) {
                    return (int)($l['postId'] ?? 0) === $postId;
                }));
                break;
            }
        }
        JsonDB::write('posts', $posts);
        
        echo json_encode(['success' => true, 'liked' => $liked], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>