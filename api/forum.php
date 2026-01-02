<?php
header('Content-Type: application/json; charset=utf-8');

try {
    // Basic includes
    require_once __DIR__ . '/../includes/config.php';
    require_once __DIR__ . '/../includes/db.php';
    
    $action = $_GET['action'] ?? '';
    
    // Get categories
    if ($action === 'categories') {
        $categories = JsonDB::read('categories');
        $topics = JsonDB::read('topics');
        
        foreach ($categories as $i => $category) {
            $topicCount = 0;
            foreach ($topics as $topic) {
                if ((int)($topic['categoryId'] ?? 0) === (int)$category['id'] && 
                    ($topic['status'] ?? 'active') === 'active') {
                    $topicCount++;
                }
            }
            $categories[$i]['topic_count'] = $topicCount;
        }
        
        echo json_encode(['success' => true, 'categories' => $categories], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get topics by category
    if ($action === 'topics') {
        $categoryId = (int)($_GET['category_id'] ?? $_GET['categoryId'] ?? 0);
        
        if ($categoryId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه دسته‌بندی نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $categories = JsonDB::read('categories');
        $category = null;
        foreach ($categories as $cat) {
            if ((int)$cat['id'] === $categoryId) {
                $category = $cat;
                break;
            }
        }
        
        if (!$category) {
            echo json_encode(['success' => false, 'error' => 'دسته‌بندی یافت نشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $topics = JsonDB::read('topics');
        $users = JsonDB::read('users');
        
        $filteredTopics = [];
        foreach ($topics as $topic) {
            if ((int)($topic['categoryId'] ?? 0) === $categoryId && 
                ($topic['status'] ?? 'active') === 'active') {
                
                // Add author name
                $authorName = 'ناشناس';
                foreach ($users as $user) {
                    if ((int)$user['id'] === (int)($topic['userId'] ?? 0)) {
                        $authorName = $user['name'];
                        break;
                    }
                }
                $topic['author_name'] = $authorName;
                $filteredTopics[] = $topic;
            }
        }
        
        // Sort by newest first
        usort($filteredTopics, function($a, $b) {
            return strtotime($b['createdAt'] ?? '') - strtotime($a['createdAt'] ?? '');
        });
        
        echo json_encode([
            'success' => true,
            'topics' => $filteredTopics,
            'category' => $category,
            'total_pages' => 1
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get topic details
    if ($action === 'get_topic') {
        $topicId = (int)($_GET['topic_id'] ?? 0);
        
        if ($topicId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه موضوع نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $topics = JsonDB::read('topics');
        $users = JsonDB::read('users');
        $topic = null;
        
        foreach ($topics as $t) {
            if ((int)$t['id'] === $topicId && ($t['status'] ?? 'active') !== 'deleted') {
                $topic = $t;
                break;
            }
        }
        
        if (!$topic) {
            echo json_encode(['success' => false, 'error' => 'موضوع یافت نشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Add author name
        $authorName = 'ناشناس';
        foreach ($users as $user) {
            if ((int)$user['id'] === (int)($topic['userId'] ?? 0)) {
                $authorName = $user['name'];
                break;
            }
        }
        $topic['author_name'] = $authorName;
        
        echo json_encode(['success' => true, 'topic' => $topic], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get forum stats
    if ($action === 'stats') {
        $topics = JsonDB::read('topics');
        $posts = JsonDB::read('posts');
        $users = JsonDB::read('users');
        
        $activeTopics = 0;
        $activePosts = 0;
        
        foreach ($topics as $topic) {
            if (($topic['status'] ?? 'active') === 'active') {
                $activeTopics++;
            }
        }
        
        foreach ($posts as $post) {
            if (($post['status'] ?? 'active') === 'active') {
                $activePosts++;
            }
        }
        
        $onlineCount = 0;
        $tenMinutesAgo = date('Y-m-d H:i:s', strtotime('-10 minutes'));
        foreach ($users as $user) {
            $lastActivity = $user['last_activity'] ?? $user['createdAt'] ?? '';
            if ($lastActivity && $lastActivity > $tenMinutesAgo) {
                $onlineCount++;
            }
        }
        
        echo json_encode([
            'success' => true,
            'stats' => [
                'topics' => $activeTopics,
                'posts' => $activePosts,
                'users' => count($users),
                'online' => $onlineCount
            ]
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get posts for a topic
    if ($action === 'posts') {
        $topicId = (int)($_GET['topic_id'] ?? 0);
        $page = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 20;
        
        if ($topicId <= 0) {
            echo json_encode(['success' => false, 'error' => 'شناسه موضوع نامعتبر است'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        $topics = JsonDB::read('topics');
        $posts = JsonDB::read('posts');
        $users = JsonDB::read('users');
        
        // Find the topic
        $topic = null;
        foreach ($topics as $t) {
            if ((int)$t['id'] === $topicId && ($t['status'] ?? 'active') !== 'deleted') {
                $topic = $t;
                break;
            }
        }
        
        if (!$topic) {
            echo json_encode(['success' => false, 'error' => 'موضوع یافت نشد'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        
        // Get posts for this topic
        $topicPosts = [];
        foreach ($posts as $post) {
            if ((int)($post['topicId'] ?? 0) === $topicId && ($post['status'] ?? 'active') === 'active') {
                // Add author info including verification status
                $authorName = 'ناشناس';
                $authorRole = 'user';
                $authorVerified = false;
                
                foreach ($users as $user) {
                    if ((int)$user['id'] === (int)($post['userId'] ?? 0)) {
                        $authorName = $user['name'] ?? $user['username'] ?? 'ناشناس';
                        $authorRole = $user['role'] ?? 'user';
                        $authorVerified = !empty($user['verified']);
                        break;
                    }
                }
                
                $post['author_name'] = $authorName;
                $post['author_role'] = $authorRole;
                $post['author_verified'] = $authorVerified;
                $post['created_at'] = $post['createdAt'] ?? '';
                
                $topicPosts[] = $post;
            }
        }
        
        // Sort by creation date
        usort($topicPosts, function($a, $b) {
            return strtotime($a['createdAt'] ?? '') - strtotime($b['createdAt'] ?? '');
        });
        
        // Pagination
        $total = count($topicPosts);
        $totalPages = ceil($total / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedPosts = array_slice($topicPosts, $offset, $perPage);
        
        // Add category info to topic
        $categories = JsonDB::read('categories');
        foreach ($categories as $cat) {
            if ((int)$cat['id'] === (int)($topic['categoryId'] ?? 0)) {
                $topic['category_name'] = $cat['name'];
                $topic['category_id'] = $cat['id'];
                break;
            }
        }
        
        echo json_encode([
            'success' => true,
            'posts' => $paginatedPosts,
            'topic' => $topic,
            'total_pages' => $totalPages,
            'current_page' => $page
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    // Get recent activity
    if ($action === 'recent_activity') {
        $topics = JsonDB::read('topics');
        $users = JsonDB::read('users');
        
        $activities = [];
        $recentTopics = array_slice($topics, -5);
        
        foreach ($recentTopics as $topic) {
            if (($topic['status'] ?? 'active') === 'active') {
                $authorName = 'ناشناس';
                foreach ($users as $user) {
                    if ((int)$user['id'] === (int)($topic['userId'] ?? 0)) {
                        $authorName = $user['name'];
                        break;
                    }
                }
                
                $activities[] = [
                    'type' => 'topic',
                    'description' => $authorName . ' موضوع جدید ایجاد کرد: ' . $topic['title'],
                    'created_at' => $topic['createdAt'] ?? date('c')
                ];
            }
        }
        
        echo json_encode(['success' => true, 'activities' => $activities], JSON_UNESCAPED_UNICODE);
        exit;
    }
    
    echo json_encode(['success' => false, 'error' => 'درخواست نامعتبر'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>