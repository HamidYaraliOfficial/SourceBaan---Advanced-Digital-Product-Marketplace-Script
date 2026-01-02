<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ads.php';

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src \'self\' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src \'self\' data: https://*; connect-src \'self\';');

// Anti-caching headers for security
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Remove server signature
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
    header_remove('Server');
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f1419" media="(prefers-color-scheme: dark)">
    <script>
    (function(){
        try {
            var key = 'sourcebaan_darkMode';
            var stored = localStorage.getItem(key);
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var enable = (stored === 'true') || (stored === null && prefersDark);
            if (enable) { document.documentElement.classList.add('dark-mode'); }
        } catch(e) {}
    })();
    </script>
    <?php
    // Dynamic title and meta based on search/filter parameters
    $search = $_GET['search'] ?? '';
    $filter = $_GET['filter'] ?? '';
    $category = $_GET['category'] ?? '';
    
    $pageTitle = 'کاوش سورس‌ها';
    $pageDescription = 'مرور و دانلود بهترین سورس کدهای متن باز. پروژه‌های PHP، Python، JavaScript، Java و سایر زبان‌های برنامه‌نویسی رایگان.';
    $keywords = ['کاوش سورس کد', 'پروژه PHP', 'پروژه Python', 'پروژه JavaScript', 'دانلود پروژه', 'برنامه نویسی', 'سورس کد رایگان', 'open source', 'sourcebaan', 'سورس بان'];
    
    if (!empty($search)) {
        $pageTitle = 'جستجو: ' . htmlspecialchars($search) . ' | سورس بان';
        $pageDescription = 'نتایج جستجو برای "' . htmlspecialchars($search) . '" - دانلود رایگان سورس کدها و پروژه‌های متن باز | سورس بان';
        $keywords[] = htmlspecialchars($search);
        $keywords[] = 'دانلود ' . htmlspecialchars($search);
        $keywords[] = 'سورس ' . htmlspecialchars($search);
    }
    
    if (!empty($filter)) {
        $pageTitle = 'پروژه‌های ' . htmlspecialchars($filter) . ' | سورس بان';
        $pageDescription = 'دانلود رایگان پروژه‌های ' . htmlspecialchars($filter) . ' - بهترین سورس کدهای ' . htmlspecialchars($filter) . ' | سورس بان';
        $keywords[] = 'پروژه ' . htmlspecialchars($filter);
        $keywords[] = 'سورس ' . htmlspecialchars($filter);
        $keywords[] = 'اسکریپت ' . htmlspecialchars($filter);
    }
    
    if (!empty($category)) {
        $pageTitle = 'دسته‌بندی ' . htmlspecialchars($category) . ' | سورس بان';
        $pageDescription = 'پروژه‌های دسته‌بندی ' . htmlspecialchars($category) . ' - دانلود رایگان سورس کدها | سورس بان';
        $keywords[] = htmlspecialchars($category);
    }
    ?>
    
    <title><?php echo $pageTitle; ?></title>
    <meta name="description" content="<?php echo $pageDescription; ?>">
    <meta name="keywords" content="<?php echo implode(', ', array_unique($keywords)); ?>">
    <link rel="canonical" href="https://sourcebaan.ir/sources.php<?php echo (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''); ?>">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo $pageTitle; ?>">
    <meta property="og:description" content="<?php echo $pageDescription; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sourcebaan.ir/sources.php<?php echo (!empty($_SERVER['QUERY_STRING']) ? '?' . htmlspecialchars($_SERVER['QUERY_STRING']) : ''); ?>">
    <meta property="og:site_name" content="سورس بان - SourceBan">
    <meta property="og:locale" content="fa_IR">
    <meta property="og:image" content="https://sourcebaan.ir/logo.png">
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="<?php echo $pageTitle; ?>">
    <meta name="twitter:description" content="<?php echo $pageDescription; ?>">
    <meta name="twitter:image" content="https://sourcebaan.ir/logo.png">
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "WebSite",
        "name": "سورس بان - SourceBan",
        "alternateName": "SourceBan",
        "url": "https://sourcebaan.ir",
        "description": "بهترین مرجع دانلود رایگان سورس کدهای متن باز در ایران",
        "publisher": {
            "@type": "Organization",
            "name": "سورس بان - SourceBan",
            "url": "https://sourcebaan.ir",
            "logo": {
                "@type": "ImageObject",
                "url": "https://sourcebaan.ir/logo.png"
            }
        },
        "potentialAction": {
            "@type": "SearchAction",
            "target": "https://sourcebaan.ir/sources.php?search={search_term_string}",
            "query-input": "required name=search_term_string"
        },
        "mainEntity": {
            "@type": "ItemList",
            "name": "پروژه‌های سورس باز",
            "description": "مجموعه کاملی از پروژه‌های متن باز و سورس کدهای رایگان"
        }
    }
    </script>
    
    <!-- Additional SEO Meta Tags -->
    <meta name="author" content="سورس بان - SourceBan">
    <meta name="generator" content="SourceBan - سورس بان">
    <link rel="alternate" hreflang="fa" href="https://sourcebaan.ir/sources.php">
    
    <!-- Mobile App Tags -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="سورس بان"><?php
    
    // Pagination meta tags for better SEO
    if (isset($_GET['page']) && (int)$_GET['page'] > 1) {
        $currentPage = (int)$_GET['page'];
        $baseUrl = 'https://sourcebaan.ir/sources.php';
        $queryParams = $_GET;
        
        // Previous page
        if ($currentPage > 1) {
            $queryParams['page'] = $currentPage - 1;
            if ($currentPage - 1 == 1) {
                unset($queryParams['page']); // Remove page=1 from URL
            }
            $prevUrl = $baseUrl . (!empty($queryParams) ? '?' . http_build_query($queryParams) : '');
            echo "\n    <link rel=\"prev\" href=\"" . htmlspecialchars($prevUrl) . "\">";
        }
        
        // Next page (we'll assume there might be a next page)
        $queryParams['page'] = $currentPage + 1;
        $nextUrl = $baseUrl . '?' . http_build_query($queryParams);
        echo "\n    <link rel=\"next\" href=\"" . htmlspecialchars($nextUrl) . "\">";
    }
    ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="icon" type="image/png" sizes="32x32" href="logo.png">
    <link rel="shortcut icon" type="image/png" href="logo.png">
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com"/>
    <link rel="preconnect" href="https://cdn.tailwindcss.com"/>
    <link rel="apple-touch-icon" href="logo.png">
    <link rel="apple-touch-icon" sizes="180x180" href="logo.png">
    
    <?php
    session_start();
    require_once __DIR__ . '/includes/utils.php';
    $csrfToken = csrf_get_token();
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    
    <script src="https://cdn.tailwindcss.com" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" media="all">

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        /* Modern Navigation */
        .modern-nav {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 100%);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 
                0 8px 40px rgba(0, 0, 0, 0.06),
                0 4px 20px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
        /* Keep nav labels on one line to avoid broken words */
        .nav-link { white-space: nowrap; }
        nav .flex { gap: 0.5rem; }
        .modern-nav .flex { gap: 0.25rem; }
        
        /* Enhanced Navigation Layout */
        .modern-nav .max-w-7xl { position: relative; }
        .modern-nav .flex.justify-between { align-items: center; min-height: 5rem; }
        
        /* Responsive Navigation */
        @media (max-width: 768px) {
            .modern-nav .flex.justify-between { min-height: 4rem; }
            .nav-link { padding: 0.5rem 0.75rem; font-size: 0.875rem; }
        }
        
        @media (min-width: 1024px) {
            .modern-nav .space-x-6 > * + * { margin-right: 1.5rem; }
        }
        
        /* Enhanced Gradients */
        .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-secondary { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
        .gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        
        /* Modern Cards */
        .clean-card { 
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 
                0 10px 30px rgba(0, 0, 0, 0.08),
                0 4px 16px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
        }
        
        /* Glass Effects */
        .modern-glass { 
            background: rgba(255, 255, 255, 0.95); 
            backdrop-filter: blur(25px); 
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(243, 244, 246, 0.8);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        }
        
        /* Hover Effects */
        .card-hover { 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            will-change: transform; 
        }
        .card-hover:hover { 
            transform: translate3d(0, -8px, 0) scale(1.03); 
            box-shadow: 
                0 25px 60px rgba(0,0,0,0.15),
                0 10px 30px rgba(0,0,0,0.08); 
        }
        
        /* Animations */
        .float-element { 
            animation: float 8s ease-in-out infinite; 
            will-change: transform; 
        }
        @keyframes float { 
            0%, 100% { transform: translateY(0px) rotate(0deg); } 
            50% { transform: translateY(-8px) rotate(2deg); } 
        }
        
        .pulse-slow { 
            animation: pulse-slow 4s infinite; 
            will-change: opacity; 
        }
        @keyframes pulse-slow { 
            0%, 100% { opacity: 0.9; } 
            50% { opacity: 0.6; } 
        }
        
        .slide-in { 
            animation: slideIn 0.8s ease-out; 
            will-change: transform, opacity; 
        }
        @keyframes slideIn { 
            from { 
                opacity: 0; 
                transform: translateY(30px) scale(0.95) translateZ(0); 
            } 
            to { 
                opacity: 1; 
                transform: translateY(0) scale(1) translateZ(0); 
            } 
        }
        /* Old notification styles removed - using dynamic system */
        .language-tag {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        .language-tag::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        .language-tag:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 12px 35px rgba(59, 130, 246, 0.4);
        }
        .language-tag:hover::before {
            left: 100%;
        }
        .project-card {
            background: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            border-radius: 24px;
            box-shadow: 
                0 10px 40px rgba(0, 0, 0, 0.08),
                0 4px 16px rgba(0, 0, 0, 0.04),
                inset 0 1px 0 rgba(255, 255, 255, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 320px;
            position: relative;
            /* max-height removed to prevent truncation of variable-height content */
        }
        .project-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.3), transparent);
        }
        .project-card:hover {
            transform: translateY(-6px) scale(1.02);
            box-shadow: 
                0 25px 60px rgba(0, 0, 0, 0.12),
                0 12px 30px rgba(0, 0, 0, 0.08),
                0 0 0 1px rgba(59, 130, 246, 0.1);
            border-color: rgba(59, 130, 246, 0.2);
        }
        
        /* Grid layout improvements */
        .projects-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            align-items: stretch;
        }
        
        @media (max-width: 640px) {
            .projects-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }
        /* Clamp long descriptions */
        .card-desc {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.5;
            height: 3em; /* Fixed height for 2 lines */
            color: #374151;
        }
        
        /* Clamp long titles to prevent layout issues */
        .card-title {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.3;
            max-height: 2.6em; /* Fixed height for 2 lines */
            word-wrap: break-word;
            word-break: break-word;
            hyphens: auto;
        }
        
        /* Ensure consistent spacing in card headers */
        .card-header-content {
            min-height: 120px; /* Consistent height for card headers */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        /* Improved layout for project cards to avoid icon overlap */
        .project-card .card-header {
            position: relative;
            z-index: 1;
        }
        
        .project-card .absolute {
            z-index: 2; /* Status indicators above content */
        }
        
        .project-card .language-tag {
            position: relative;
            z-index: 1;
            margin-top: auto; /* Push to bottom of flex container */
        }
        
        /* Improved positioning for mobile */
        @media (max-width: 640px) {
            .project-card .absolute.top-4.left-4 {
                top: 0.75rem;
                left: 0.75rem;
                z-index: 3;
            }
            
            /* Prevent overlap with language tags */
            .project-card .flex.flex-col.items-end {
                min-height: 60px;
                justify-content: space-between;
            }
        }
        
        /* Better spacing for tags to avoid overlap */
        .project-tags {
            margin-bottom: 0.75rem;
            z-index: 1;
            position: relative;
        }
        
        /* Ensure proper spacing from bottom elements */
        .card-stats {
            position: relative;
            z-index: 1;
            margin-top: auto;
        }

        /* Status badge positioning to avoid overlap */
        .status-badge { z-index: 20; }
        @media (max-width: 640px) {
            .status-badge { top: 0.5rem !important; left: 0.5rem !important; }
        }
        /* Performance helpers */
        .lazy-section { content-visibility: auto; contain-intrinsic-size: 800px 600px; }
        .defer-animate { opacity: 0; transform: translateY(8px); transition: opacity .35s ease, transform .35s ease; }
        .defer-animate.animate { opacity: 1; transform: translateY(0); }
        
        /* Advanced performance optimizations */
        .reduced-motion * { animation: none !important; transition: opacity .1s !important; }
        .pause-animations * { animation-play-state: paused !important; }
        .low-memory .project-card::before { display: none !important; }
        .low-memory .float-element { animation: none !important; }
        
        /* Virtual scrolling helper */
        .virtual-item { contain: layout style paint; }
        .skeleton-loader { 
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
        }
        @keyframes skeleton-loading { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }
        .card-header {
            flex: 1;
            padding: 1.5rem 1.5rem 1rem;
        }
        .card-footer {
            padding: 0 1.5rem 1.5rem;
            margin-top: auto;
        }
        .card-stats {
            padding: 0 1.5rem;
            margin-bottom: 1rem;
        }
        
        /* Mobile optimizations */
        @media (max-width: 640px) {
            .card-header {
                padding: 1rem 1rem 0.5rem;
            }
            .card-footer {
                padding: 0 1rem 1rem;
            }
            .card-stats {
                padding: 0 1rem;
                margin-bottom: 0.75rem;
            }
            .project-card {
                border-radius: 16px;
                min-height: 400px;
            }
            .card-desc {
                font-size: 0.875rem;
                height: auto;
                -webkit-line-clamp: 3;
            }
            .language-tag {
                padding: 0.25rem 0.75rem;
                font-size: 0.75rem;
            }
            
            /* Status indicators mobile */
            .project-card .absolute.top-4.left-4 {
                top: 0.5rem;
                left: 0.5rem;
            }
            
            /* Stats grid mobile */
            .card-stats .grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 0.75rem;
            }
            
            /* Header mobile */
            .card-header .flex.justify-between {
                flex-direction: column;
                gap: 1rem;
            }
            
            .card-header .flex.flex-col.items-end {
                flex-direction: row;
                justify-content: space-between;
                width: 100%;
            }
            
            /* Developer section mobile styles */
            .developer-card {
                padding: 0.75rem;
            }
            .developer-card .flex {
                flex-direction: column;
                align-items: center;
                text-align: center;
                gap: 0.75rem;
            }
            .developer-card .w-12 {
                width: 2.5rem;
                height: 2.5rem;
            }
            .developer-card .text-sm {
                font-size: 0.75rem;
            }
        }
        .loading-spinner { border: 3px solid #f3f3f3; border-top: 3px solid #3b82f6; border-radius: 50%; width: 30px; height: 30px; }
        
        /* Dark Mode Styles */
        .dark-mode { background: #0f1419 !important; color: #ffffff !important; }
        .dark-mode body { background: #0f1419 !important; color: #ffffff !important; }
        .dark-mode .bg-white { background: #16202a !important; color: #ffffff !important; }
        .dark-mode .bg-gray-50 { background: #0f1419 !important; color: #ffffff !important; }
        .dark-mode .bg-gray-100 { background: #1e2a36 !important; color: #ffffff !important; }
        .dark-mode .bg-gray-200 { background: #2a343e !important; color: #ffffff !important; }
        .dark-mode .text-gray-900 { color: #ffffff !important; }
        .dark-mode .text-gray-800 { color: #e5e7eb !important; }
        .dark-mode .text-gray-700 { color: #d0d0d0 !important; }
        .dark-mode .text-gray-600 { color: #a0a0a0 !important; }
        .dark-mode .text-gray-500 { color: #888888 !important; }
        .dark-mode .text-gray-400 { color: #6b7280 !important; }
        .dark-mode .border-gray-200 { border-color: #2a343e !important; }
        .dark-mode .border-gray-100 { border-color: #2a343e !important; }
        .dark-mode .border-gray-300 { border-color: #374151 !important; }
        
        /* Navigation Dark Mode */
        .dark-mode .modern-nav { background: rgba(22, 32, 42, 0.95) !important; backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .dark-mode .nav-link { color: #d1d5db !important; }
        .dark-mode .nav-link:hover { color: #ffffff !important; background: rgba(59, 130, 246, 0.1) !important; }
        
        /* Cards and Components */
        .dark-mode .project-card { background: rgba(22, 32, 42, 0.95) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .clean-card { background: rgba(22, 32, 42, 0.95) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .modern-glass { background: rgba(22, 32, 42, 0.8) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .stats-card { background: rgba(26, 26, 46, 0.95) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .card-hover { background: rgba(22, 32, 42, 0.95) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        
        /* Form Elements */
        .dark-mode input { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode input::placeholder { color: #888888 !important; }
        .dark-mode select { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode textarea { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode button { border-color: #2a343e !important; }
        
        /* Hover States */
        .dark-mode .hover\\:bg-gray-50:hover { background: #252540 !important; }
        .dark-mode .hover\\:bg-gray-100:hover { background: #374151 !important; }
        .dark-mode .hover\\:bg-blue-50:hover { background: rgba(59, 130, 246, 0.1) !important; }
        .dark-mode .hover\\:bg-green-50:hover { background: rgba(34, 197, 94, 0.1) !important; }
        .dark-mode .hover\\:bg-purple-50:hover { background: rgba(168, 85, 247, 0.1) !important; }
        
        /* Backgrounds */
        .dark-mode .bg-white\\/95 { background: rgba(26, 26, 46, 0.95) !important; }
        .dark-mode .bg-white\\/90 { background: rgba(26, 26, 46, 0.9) !important; }
        .dark-mode .bg-white\\/80 { background: rgba(26, 26, 46, 0.8) !important; }
        .dark-mode .bg-white\\/70 { background: rgba(26, 26, 46, 0.7) !important; }
        
        /* Dark mode for light gradient and white cards */
        .dark-mode .bg-white,
        .dark-mode .bg-gradient-to-br.from-white.to-gray-50,
        .dark-mode .card-hover,
        .dark-mode .project-card,
        .dark-mode .clean-card {
            background: linear-gradient(135deg, #151c24 0%, #1a2430 100%) !important;
            color: #e5e7eb !important;
            border-color: #2a343e !important;
        }
        .dark-mode h1, .dark-mode h2, .dark-mode h3, .dark-mode h4 { color: #ffffff !important; }
        .dark-mode p, .dark-mode .text-gray-700, .dark-mode .text-gray-800, .dark-mode .text-gray-900 { color: #a0a0a0 !important; }
        
        .stats-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .stats-card:hover {
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            border-color: #e2e8f0;
        }
        
        /* Glass effect */
        .glass-effect {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        /* Gradient styles */
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .gradient-success {
            background: linear-gradient(135deg, #4ade80 0%, #22c55e 100%);
        }
        
        .gradient-secondary {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        /* Enhanced Blue verification tick styles with glow effect */
        .blue-tick {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: 2px solid #60a5fa;
            box-shadow: 
                0 0 20px rgba(37, 99, 235, 0.5),
                0 4px 15px rgba(37, 99, 235, 0.3),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            position: relative;
            overflow: hidden;
        }
        
        .blue-tick::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            transition: transform 0.6s ease;
        }
        
        .blue-tick:hover::before {
            transform: rotate(45deg) translate(100%, 100%);
        }
        
        .blue-tick:hover {
            transform: scale(1.05);
            box-shadow: 
                0 0 30px rgba(37, 99, 235, 0.7),
                0 8px 25px rgba(37, 99, 235, 0.4),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
        }
        
        /* Enhanced pulse animation for verified badge */
        @keyframes pulse-blue {
            0%, 100% { opacity: 1; transform: scale(1); box-shadow: 0 0 15px rgba(37, 99, 235, 0.4); }
            50% { opacity: 0.9; transform: scale(1.03); box-shadow: 0 0 25px rgba(37, 99, 235, 0.6); }
        }
        
        .verified-pulse {
            animation: pulse-blue 3s infinite;
        }
        
        /* Zip file icon enhancement */
        .zip-verified {
            background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);
            border: 2px solid #3b82f6;
            box-shadow: 
                0 0 15px rgba(37, 99, 235, 0.4),
                0 2px 10px rgba(37, 99, 235, 0.2);
            animation: zip-glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes zip-glow {
            0% { box-shadow: 0 0 15px rgba(37, 99, 235, 0.4), 0 2px 10px rgba(37, 99, 235, 0.2); }
            100% { box-shadow: 0 0 25px rgba(37, 99, 235, 0.6), 0 4px 15px rgba(37, 99, 235, 0.3); }
        }
        
        /* Better text selection control */
        .no-select {
            -webkit-user-select: none;
            -moz-user-select: none;
            -ms-user-select: none;
            user-select: none;
        }
        
        /* Allow text selection for content */
        .project-card, .project-card * {
            -webkit-user-select: text;
            -moz-user-select: text;
            -ms-user-select: text;
            user-select: text;
        }
        
        /* Input fields text selection */
        input, textarea, select {
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            user-select: text !important;
        }
        
        /* Hide scrollbars to prevent manipulation */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        .dark-mode .bg-white\/90 { background: rgba(26, 26, 46, 0.9) !important; }
        /* Dark mode for light gradient and white cards */
        .dark-mode .bg-white,
        .dark-mode .bg-gradient-to-br.from-white.to-gray-50,
        .dark-mode .card-hover,
        .dark-mode .project-card,
        .dark-mode .clean-card {
            background: linear-gradient(135deg, #151c24 0%, #1a2430 100%) !important;
            color: #e5e7eb !important;
            border-color: #2a343e !important;
        }
        .dark-mode h1, .dark-mode h2, .dark-mode h3, .dark-mode h4 { color: #ffffff !important; }
        .dark-mode p, .dark-mode .text-gray-700, .dark-mode .text-gray-800, .dark-mode .text-gray-900 { color: #a0a0a0 !important; }
    </style>
    <script>
        // Restrict heavy visual effects on lower-spec devices or by query (?eff=low)
        (function(){
            try {
                var dm = navigator.deviceMemory || 0;
                var hc = navigator.hardwareConcurrency || 0;
                var pr = window.devicePixelRatio || 1;
                var ua = navigator.userAgent || '';
                var shouldRestrict = false;
                if (dm && dm <= 6) shouldRestrict = true;
                if (hc && hc <= 6) shouldRestrict = true;
                if (/Mobi|Android|iPhone|iPad/i.test(ua)) shouldRestrict = true;
                if (pr >= 2 && dm && dm <= 8) shouldRestrict = true;
                if (location.search.indexOf('eff=low') !== -1) shouldRestrict = true;
                if (shouldRestrict) document.documentElement.classList.add('restrict-effects');
            } catch(e) {}
        })();
    </script>
    <style>
        /* DISABLE ALL ANIMATIONS AND HEAVY EFFECTS */
        *, *::before, *::after { animation: none !important; transition: none !important; transform: none !important; }
        .animate-ping, .animate-bounce, .animate-pulse, .animate-spin { animation: none !important; }
        .code-rain, .particle, .code-char { display: none !important; }
        .project-card, .clean-card, .stats-card { box-shadow: 0 1px 3px rgba(0,0,0,.1) !important; }
        .modern-glass, .glass-effect { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        .blur, .backdrop-blur-sm, .backdrop-blur, .backdrop-blur-lg { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        .hover\\:scale-105:hover, .hover\\:scale-110:hover, .group-hover\\:scale-110, .group-hover\\:scale-125 { transform: none !important; }
        .float-element { animation: none !important; }
        .pulse-slow { animation: none !important; }
        .verified-pulse { animation: none !important; }
        .zip-verified { animation: none !important; }
    </style>
    <script>
        (function(){
            try {
                var root = document.documentElement;
                var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (reduced) { root.classList.add('reduced-motion','pause-animations'); }
                var dm = (navigator && navigator.deviceMemory) ? navigator.deviceMemory : 0;
                if (!dm && window.performance && performance.memory && performance.memory.jsHeapSizeLimit) {
                    dm = performance.memory.jsHeapSizeLimit / (1024*1024*1024);
                }
                if (dm && dm <= 4) { root.classList.add('low-memory'); }
            } catch(e) {}
        })();
        document.addEventListener('DOMContentLoaded', function(){
            try {
                var images = document.querySelectorAll('img');
                for (var i=0;i<images.length;i++){
                    var img = images[i];
                    if (!img.hasAttribute('loading')) { if (i > 4) img.setAttribute('loading','lazy'); }
                    if (!img.hasAttribute('decoding')) img.setAttribute('decoding','async');
                    if (!img.getAttribute('fetchpriority')) img.setAttribute('fetchpriority', i <= 2 ? 'high' : 'low');
                }
                // Ensure dark-mode applied on root for mobile before UI interactions
                if (localStorage.getItem('sourcebaan_darkMode') === 'true') {
                    document.documentElement.classList.add('dark-mode');
                }
            } catch(e) {}
        });
    </script>
    
    <!-- Minimal Security Script -->
    <script>
        // Prevent right-click context menu on images only
        document.addEventListener('contextmenu', function(e){ 
            if (e.target.tagName === 'IMG') {
                e.preventDefault(); 
            }
        });
    </script>
</head>
<body class="bg-gray-50 min-h-screen text-gray-900 font-vazir">
    <!-- Modern Navigation Header -->
    <header class="modern-nav sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 sm:py-4">
                <div class="flex items-center space-x-3 sm:space-x-6 space-x-reverse flex-shrink-0">
                    <div class="relative group flex-shrink-0">
                        <img src="logo.png" alt="سورس کده" class="h-16 sm:h-20 lg:h-24 w-auto object-contain drop-shadow-xl transition-all duration-700 group-hover:scale-125 group-hover:drop-shadow-2xl group-hover:brightness-110 filter" decoding="async" fetchpriority="high">
                        <div class="absolute -top-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-gradient-to-r from-emerald-400 to-green-500 rounded-full shadow-xl">
                            <div class="w-full h-full bg-emerald-300 rounded-full animate-pulse-slow"></div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center min-w-0 flex-1">
                        <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                            <h1 class="text-xl sm:text-4xl font-black bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent tracking-tight whitespace-normal break-words flex-shrink-0">
                                سورس کده
                            </h1>
                            <div class="hidden sm:flex items-center space-x-2 space-x-reverse bg-green-50 px-3 py-1 rounded-full flex-shrink-0">
                                <div class="w-2 h-2 bg-green-500 rounded-full animate-ping"></div>
                                <span class="text-xs font-bold text-green-700">آنلاین</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Mobile Controls -->
                <div class="flex items-center space-x-2 space-x-reverse md:hidden mobile-controls">
                    <button onclick="toggleTheme()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300" title="تغییر حالت">
                        <i id="mobileThemeIcon" class="fas fa-moon w-5 h-5 text-gray-600"></i>
                    </button>
                    <button onclick="toggleMobileMenu()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300">
                        <svg id="mobileMenuIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
                
                
                <!-- Enhanced Desktop Navigation & Controls -->
                <div class="hidden md:flex items-center justify-end flex-1 gap-3 lg:gap-6">
                    <!-- Navigation Menu -->
                    <nav class="hidden lg:flex items-center bg-white/90 backdrop-blur-lg rounded-2xl border border-white/20 shadow-lg px-2 py-1">
                        <a href="sources.php" class="nav-link group relative text-gray-700 hover:text-blue-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-blue-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-gem text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">کاوش سورس‌ها</span>
                            </div>
                        </a>
                        <a href="shop.php" class="nav-link group relative text-gray-700 hover:text-orange-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-orange-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-orange-400 to-red-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-store text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">فروشگاه</span>
                            </div>
                        </a>
                        <a href="forum.php" class="nav-link group relative text-gray-700 hover:text-green-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-green-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-green-400 to-emerald-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-comments text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">انجمن</span>
                            </div>
                        </a>
                        <a href="ai.php" class="nav-link group relative text-gray-700 hover:text-purple-600 font-semibold transition-all duration-300 text-sm px-4 py-2.5 rounded-xl hover:bg-purple-50/80">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 bg-gradient-to-br from-purple-400 to-pink-500 rounded-lg flex items-center justify-center shadow-sm group-hover:shadow-md transition-all">
                                    <i class="fas fa-brain text-white text-sm"></i>
                                </div>
                                <span class="whitespace-nowrap">هوش مصنوعی</span>
                            </div>
                        </a>
                        <div class="w-px h-6 bg-gray-200 mx-2"></div>
                        <a href="javascript:void(0)" onclick="showSection('leaderboard')" class="nav-link group relative text-gray-600 hover:text-amber-600 font-medium transition-all duration-300 text-sm px-3 py-2 rounded-lg hover:bg-amber-50/60 text-gray-700">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-trophy text-amber-500 text-sm"></i>
                                <span>رتبه‌بندی</span>
                            </div>
                        </a>
                    </nav>
                    
                    <!-- Action Buttons -->
                    <div class="flex items-center gap-2">
                        <button onclick="toggleTheme()" class="p-2.5 rounded-xl bg-white/80 backdrop-blur-sm border border-white/20 hover:bg-white hover:border-gray-200 transition-all duration-300 shadow-sm hover:shadow-md">
                            <svg id="themeIcon" class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        </button>
                    <div id="userSection" class="hidden relative">
                        <div class="flex items-center space-x-2 sm:space-x-4 space-x-reverse">
                            <div class="relative hidden sm:block">
                                <button onclick="toggleNotifications()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300 relative">
                                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5v-5zM11 19H6.5A2.5 2.5 0 014 16.5v-9A2.5 2.5 0 016.5 5h11A2.5 2.5 0 0120 7.5V11"></path></svg>
                                    <span id="notificationBadge" class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center hidden">3</span>
                                </button>
                            </div>
                            <div class="flex items-center space-x-2 sm:space-x-3 space-x-reverse bg-white/10 rounded-xl sm:rounded-2xl px-2 sm:px-4 py-1 sm:py-2 backdrop-blur-sm">
                                <div class="text-right hidden sm:block">
                                    <p id="userName" class="font-semibold text-gray-900 text-sm"></p>
                                    <p id="userLevel" class="text-xs text-gray-600">سطح</p>
                                </div>
                                <div class="relative">
                                    <div class="w-8 h-8 sm:w-10 sm:h-10 rounded-full overflow-hidden" id="userAvatarContainer">
                                        <img id="userAvatar" src="" alt="User Avatar" class="w-full h-full object-cover bg-white" style="display: none;" decoding="async" fetchpriority="high">
                                        <div id="userAvatarFallback" class="w-full h-full gradient-primary flex items-center justify-center text-white font-bold text-xs sm:text-sm">A</div>
                                    </div>
                                    <div class="absolute -bottom-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-green-500 rounded-full border-2 border-white"></div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 space-x-reverse hidden sm:flex">
            <div class="text-center">
                                    <div class="gradient-success text-white px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm font-bold" id="userPoints">0</div>
                                    <p class="text-xs text-gray-600 mt-1">امتیاز</p>
                                </div>
                            </div>
                            <button onclick="showUserMenu()" class="p-2 rounded-lg glass-effect hover:bg-white/20 transition-all duration-300"><svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg></button>
                        </div>
                        <div id="userMenu" class="hidden absolute top-16 right-0 w-64 bg-white rounded-2xl shadow-2xl py-2 border border-gray-100">
                            <a href="account.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                حساب من
                            </a>
                            <div id="adminLinks" class="hidden">
                                <div class="border-t border-gray-100 my-1"></div>
                                <!-- پنل مدیریت پیشرفته حذف شد -->
                                <a href="admin/" class="flex items-center px-4 py-3 text-sm text-orange-700 hover:bg-orange-50 transition-colors">
                                    <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    تایید پروژه‌ها
                                </a>
                            </div>
                            <div class="border-t border-gray-100 my-1"></div>
                            <button onclick="handleLogout()" class="w-full flex items-center px-4 py-3 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                خروج
                            </button>
                        </div>
                    </div>
                    <div id="authSection">
                        <div class="flex items-center space-x-3 sm:space-x-4 lg:space-x-6 space-x-reverse">
                            <a href="auth.php" class="text-gray-700 hover:text-blue-600 font-medium transition-colors text-sm sm:text-base px-2 py-2 rounded-lg hover:bg-gray-50">ورود</a>
                            <a href="auth.php#register" class="gradient-primary text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg sm:rounded-xl font-medium hover:shadow-lg transition-all duration-300 transform hover:scale-105 text-sm whitespace-nowrap">ثبت‌نام</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Mobile Menu -->
        <div id="mobileMenu" class="hidden md:hidden bg-white/95 backdrop-blur-lg border-t border-gray-200 shadow-lg">
            <div class="px-4 py-4 space-y-4">
                <!-- Mobile Search -->
                <div class="relative">
                    <input type="text" id="mobileGlobalSearch" placeholder="جستجوی پیشرفته..." class="w-full px-4 py-3 pr-12 rounded-xl border border-gray-200 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300 bg-white">
                    <div class="absolute right-4 top-1/2 transform -translate-y-1/2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                </div>
                    
                <!-- Mobile Navigation Links -->
                <div class="space-y-2">
                    
                    <a href="sources.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-gem ml-3 w-5"></i>کاوش سورس‌ها
                    </a>
                    <a href="javascript:void(0)" onclick="showSection('explore'); toggleMobileMenu();" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-search ml-3 w-5"></i>جستجوی پیشرفته
                    </a>
                    <a href="shop.php" class="block px-4 py-3 text-gray-700 hover:bg-orange-50 hover:text-orange-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-shopping-bag ml-3 w-5"></i>فروشگاه
                    </a>
                    <a href="forum.php" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-comments ml-3 w-5"></i>انجمن
                    </a>
                    <a href="ai.php" class="block px-4 py-3 text-gray-700 hover:bg-purple-50 hover:text-purple-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-brain ml-3 w-5"></i>هوش مصنوعی
                    </a>
                    <a href="javascript:void(0)" onclick="showSection('leaderboard'); toggleMobileMenu();" class="block px-4 py-3 text-gray-700 hover:bg-blue-50 hover:text-blue-600 rounded-lg transition-colors font-medium">
                        <i class="fas fa-trophy ml-3 w-5"></i>رتبه‌بندی
                    </a>
                    </div>
                    
                <!-- Mobile Auth Section for non-logged in users -->
                <div id="mobileAuthSection" class="pt-3 border-t border-gray-200" role="region" aria-label="mobile-auth-section">
                    <div class="space-y-3">
                        <a href="auth.php" class="block px-4 py-3 text-center bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors font-medium">ورود</a>
                        <a href="auth.php#register" class="block px-4 py-3 text-center gradient-primary text-white rounded-xl font-medium whitespace-nowrap mobile-auth-button">ثبت‌نام</a>
                    </div>
                </div>
                
                <!-- Mobile User Section for logged in users -->
                <div id="mobileUserSection" class="hidden pt-3 border-t border-gray-200" role="region" aria-label="mobile-user-section">
                    <div class="space-y-2">
                        <a href="account.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-user ml-3 w-5"></i>حساب من
                        </a>
                        <!-- لینک‌های ادمین موبایل حذف شدند -->
                        <button onclick="handleLogout()" class="w-full text-right px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt ml-3 w-5"></i>خروج
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div><div></div></header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 lazy-section">
        <!-- Hero Section -->
        <div class="text-center mb-8 sm:mb-12">
            <!-- Top Banner Ads -->
            <?php echo display_ads('sources_top', 1); ?>
            
            <div class="bg-white rounded-2xl sm:rounded-3xl p-6 sm:p-12 mb-6 sm:mb-8 shadow-sm border border-gray-100">
                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900 mb-3 sm:mb-4 slide-in">
                    <i class="fas fa-gem ml-2 sm:ml-3 text-blue-500 floating-animation"></i>
                    کاوش سورس‌ها
                </h1>
                <p class="text-base sm:text-lg lg:text-xl text-gray-600 mb-6 sm:mb-8 max-w-2xl mx-auto slide-in px-4 sm:px-0">
                    آخرین پروژه‌های تایید شده، کیفیت بالا و آماده استفاده
                </p>
                
                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto relative slide-in px-4 sm:px-0">
                    <input type="text" id="searchInput" placeholder="جستجو در پروژه‌ها..." 
                           class="w-full px-4 sm:px-6 py-3 sm:py-4 pr-10 sm:pr-12 rounded-xl sm:rounded-2xl border border-gray-200 bg-gray-50 focus:ring-4 focus:ring-blue-500/20 focus:outline-none focus:border-blue-500 text-gray-900 transition-all text-sm sm:text-base">
                    <button onclick="searchProjects()" class="absolute right-6 sm:right-4 top-1/2 transform -translate-y-1/2 text-blue-600 hover:text-blue-800 transition-colors">
                        <i class="fas fa-search text-lg sm:text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 sm:gap-6 mb-8 slide-in">
                <div class="stats-card">
                    <div class="mb-3">
                        <i class="fas fa-folder-open text-blue-500 text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-blue-600 mb-2" id="totalProjects">0</div>
                    <div class="text-sm text-gray-600 font-medium">کل پروژه‌ها</div>
                </div>
                <div class="stats-card">
                    <div class="mb-3">
                        <i class="fas fa-download text-green-500 text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-green-600 mb-2" id="totalDownloads">0</div>
                    <div class="text-sm text-gray-600 font-medium">کل دانلودها</div>
                </div>
                <div class="stats-card">
                    <div class="mb-3">
                        <i class="fas fa-star text-yellow-500 text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-yellow-600 mb-2" id="totalStars">0</div>
                    <div class="text-sm text-gray-600 font-medium">کل ستاره‌ها</div>
                </div>
                <div class="stats-card">
                    <div class="mb-3">
                        <i class="fas fa-laptop-code text-purple-500 text-2xl"></i>
                    </div>
                    <div class="text-3xl font-bold text-purple-600 mb-2" id="totalLanguages">0</div>
                    <div class="text-sm text-gray-600 font-medium">زبان‌های مختلف</div>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-white rounded-2xl sm:rounded-3xl p-4 sm:p-8 mb-6 sm:mb-8 slide-in shadow-sm border border-gray-100">
            <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4 sm:mb-6">
                <i class="fas fa-filter ml-2 text-blue-500"></i>
                فیلتر و مرتب‌سازی
            </h3>
            
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 sm:gap-8">
                <!-- Language Filters -->
                <div>
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">فیلتر بر اساس زبان</h4>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <button onclick="filterProjects('all')" id="filterAll" 
                                class="filter-btn px-4 py-2 bg-blue-500 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm">
                            همه
                        </button>
                        <button onclick="filterProjects('JavaScript')" id="filterJS" 
                                class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            JavaScript
                        </button>
                        <button onclick="filterProjects('PHP')" id="filterPHP" 
                                class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            PHP
                        </button>
                        <button onclick="filterProjects('Python')" id="filterPython" 
                                class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            Python
                        </button>
                        <button onclick="filterProjects('Java')" id="filterJava" 
                                class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            Java
                        </button>
                        <button onclick="filterProjects('C++')" id="filterCpp" 
                                class="filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            C++
                        </button>
                    </div>
                </div>

                <!-- Sort Options -->
                <div>
                    <h4 class="text-base sm:text-lg font-semibold text-gray-900 mb-3 sm:mb-4">مرتب‌سازی</h4>
                    <div class="flex flex-wrap gap-2 sm:gap-3">
                        <button onclick="sortProjects('newest')" id="sortNewest" 
                                class="sort-btn px-4 py-2 bg-blue-500 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm">
                            <i class="fas fa-clock ml-2"></i>جدیدترین
                        </button>
                        <button onclick="sortProjects('popular')" id="sortPopular" 
                                class="sort-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            <i class="fas fa-fire ml-2"></i>محبوب‌ترین
                        </button>
                        <button onclick="sortProjects('downloads')" id="sortDownloads" 
                                class="sort-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            <i class="fas fa-download ml-2"></i>پردانلودترین
                        </button>
                        <button onclick="sortProjects('stars')" id="sortStars" 
                                class="sort-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200">
                            <i class="fas fa-star ml-2"></i>پرستاره‌ترین
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        <div id="projectsGrid" class="projects-grid">
            <!-- Projects will be loaded here -->
        </div>
        
        <!-- Pagination -->
        <div id="paginationContainer" class="hidden flex justify-center mt-8">
            <nav class="flex space-x-2 space-x-reverse bg-white rounded-2xl shadow-sm border border-gray-200 p-2">
                <button id="prevPage" onclick="goToPage(currentPage - 1)" class="px-4 py-2 rounded-xl text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    <i class="fas fa-chevron-right ml-1"></i>قبلی
                </button>
                <div id="pageNumbers" class="flex space-x-1 space-x-reverse">
                    <!-- Page numbers will be inserted here -->
                </div>
                <button id="nextPage" onclick="goToPage(currentPage + 1)" class="px-4 py-2 rounded-xl text-gray-600 hover:bg-blue-50 hover:text-blue-600 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                    بعدی<i class="fas fa-chevron-left mr-1"></i>
                </button>
            </nav>
        </div>

        <!-- Project Details Modal -->
        <div id="projectDetailsModal" class="fixed inset-0 bg-black/60 hidden z-50">
            <div class="max-w-3xl mx-auto mt-16 bg-white rounded-2xl shadow-xl p-6 relative">
                <button class="absolute top-3 left-3 text-gray-400 hover:text-gray-600" onclick="closeProjectDetails()"><i class="fas fa-times"></i></button>
                <div id="projectDetailsBody"></div>
            </div>
        </div>

        <!-- Loading State -->
        <div id="loadingState" class="text-center py-16">
            <div class="loading-spinner mx-auto mb-4"></div>
            <p class="text-gray-600 text-lg">در حال بارگذاری پروژه‌ها...</p>
        </div>

        <!-- Empty State -->
        <div id="emptyState" class="hidden text-center py-16">
            <div class="w-32 h-32 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-8">
                <i class="fas fa-search text-gray-400 text-5xl"></i>
            </div>
            <h3 class="text-2xl font-bold text-gray-900 mb-4">پروژه‌ای یافت نشد</h3>
            <p class="text-gray-600 text-lg">فیلترهای خود را تغییر دهید یا کلمات جستجو را بررسی کنید</p>
        </div>
    </main>

    <!-- Floating Action Button -->
    <button onclick="scrollToTop()" class="fixed bottom-8 right-8 z-50 bg-blue-500 text-white p-4 rounded-full shadow-2xl hover:shadow-3xl hover:bg-blue-600 transition-all duration-300 transform hover:scale-110">
        <i class="fas fa-arrow-up text-xl"></i>
    </button>

    <!-- Old notification removed - using dynamic notifications now -->

    <script>
        let currentFilter = 'all';
        let currentSort = 'newest';
        let allProjects = [];
        let currentPage = 1;
        let totalPages = 1;
        let currentPagination = null;

        // Runtime auth state (resolved at runtime, not server-side snapshot)
        let isLoggedIn = false;
        let currentUserData = null;

        async function resolveAuthState() {
            try {
                const res = await fetch('api/auth.php?action=me', { credentials: 'same-origin' });
                const data = await res.json();
                if (data && data.ok && data.user) {
                    isLoggedIn = true;
                    currentUserData = data.user;
                    showUserSection(data.user);
                } else {
                    isLoggedIn = false;
                    currentUserData = null;
                    showLoggedOut();
                }
            } catch (_) {
                isLoggedIn = false;
                currentUserData = null;
                showLoggedOut();
            }
        }

        function showLoggedOut() {
            const authSection = document.getElementById('authSection');
            const userSection = document.getElementById('userSection');
            if (authSection) authSection.classList.remove('hidden');
            if (userSection) userSection.classList.add('hidden');
            syncMobileAuthState();
        }

        async function showUserSection(user) {
            const authSection = document.getElementById('authSection');
            const userSection = document.getElementById('userSection');
            if (authSection) authSection.classList.add('hidden');
            if (userSection) userSection.classList.remove('hidden');
            const userName = document.getElementById('userName');
            const userLevel = document.getElementById('userLevel');
            const userPoints = document.getElementById('userPoints');
            if (userName) userName.textContent = user.name || '';
            if (userLevel) userLevel.textContent = (user.role === 'admin' ? 'ادمین' : 'کاربر');
            if (userPoints) userPoints.textContent = user.points || 0;
            try {
                const avatarRes = await fetch('api/profile.php?action=get_avatar_options');
                const avatarData = await avatarRes.json();
                if (avatarData.ok && avatarData.avatars && user.avatar) {
                    const avatarUrl = avatarData.avatars[user.avatar];
                    const img = document.getElementById('userAvatar');
                    const fallback = document.getElementById('userAvatarFallback');
                    if (img && avatarUrl) { img.src = avatarUrl; img.style.display = 'block'; }
                    if (fallback) fallback.style.display = 'none';
                }
            } catch (_) {}
            syncMobileAuthState();
        }

        function syncMobileAuthState() {
            try {
                const userSection = document.getElementById('userSection');
                const authSection = document.getElementById('authSection');
                const mobileUser = document.getElementById('mobileUserSection');
                const mobileAuth = document.getElementById('mobileAuthSection');
                let loggedIn = false;
                if (userSection && !userSection.classList.contains('hidden')) {
                    loggedIn = true;
                } else if (authSection && authSection.classList.contains('hidden')) {
                    loggedIn = true;
                }
                if (loggedIn) {
                    if (mobileUser) mobileUser.classList.remove('hidden');
                    if (mobileAuth) mobileAuth.classList.add('hidden');
                } else {
                    if (mobileUser) mobileUser.classList.add('hidden');
                    if (mobileAuth) mobileAuth.classList.remove('hidden');
                }
            } catch (_) {}
        }

        // Utility functions
        function showNotification(title, message, type = 'info') {
            // Create visual notification (modern style like index.php)
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 max-w-sm p-4 rounded-xl shadow-xl transform translate-x-full transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' :
                type === 'error' ? 'bg-red-500 text-white' :
                type === 'warning' ? 'bg-yellow-500 text-white' :
                'bg-blue-500 text-white'
            }`;
            
            notification.innerHTML = `
                <div class="flex items-start space-x-2 space-x-reverse">
                    <div class="flex-shrink-0">
                        ${type === 'success' ? '<i class="fas fa-check-circle"></i>' :
                          type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' :
                          type === 'warning' ? '<i class="fas fa-exclamation-triangle"></i>' :
                          '<i class="fas fa-info-circle"></i>'}
                    </div>
                    <div class="flex-1">
                        <div class="font-bold text-sm">${title}</div>
                        <div class="text-xs opacity-90 mt-1">${message}</div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="flex-shrink-0 text-white/80 hover:text-white">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            // Auto hide after 2 seconds
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 300);
            }, 2000);
            
            // Also log to console
            console.log(`${type.toUpperCase()}: ${title} - ${message}`);
        }

        // Simple login popup (reuses site's styles)
        function showLoginPopup() {
            const popup = document.createElement('div');
            popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            popup.innerHTML = `
                <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl transform scale-95 animate-popup">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-lock text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">ورود به حساب کاربری</h3>
                        <p class="text-gray-600 mb-6">برای دانلود پروژه ابتدا باید وارد حساب کاربری شوید</p>
                        <div class="flex gap-3">
                            <a href="auth.php" class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-4 rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                                <i class="fas fa-sign-in-alt ml-2"></i>
                                ورود / ثبت‌نام
                            </a>
                            <button onclick="(function(el){ el.closest('.fixed.inset-0').remove(); })(this)" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                                بستن
                            </button>
                        </div>
                    </div>
                </div>
            `;
            popup.onclick = function(e){ if (e.target === popup) popup.remove(); };
            document.body.appendChild(popup);
            setTimeout(()=>{
                const el = popup.querySelector('.animate-popup');
                if (el) el.style.transform = 'scale(1)';
            }, 10);
        }

        // Reconcile header auth UI on first load and after logout/login
        document.addEventListener('DOMContentLoaded', function(){
            // Resolve auth state early to prevent flicker
            resolveAuthState();
        });

        // Load projects on page load with performance monitoring
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Sources page loaded');
            
            // Performance monitoring and adaptive loading
            const startTime = performance.now();
            
            // Check device capabilities
            const isLowEndDevice = navigator.hardwareConcurrency && navigator.hardwareConcurrency <= 2;
            const hasSlowConnection = navigator.connection && navigator.connection.saveData;
            
            // Fast loading - start immediately
            loadProjects(1);
            
            // Monitor performance
            requestAnimationFrame(() => {
                const loadTime = performance.now() - startTime;
                if (loadTime > 3000) {
                    console.warn('Slow page load detected:', loadTime + 'ms');
                    // Enable performance mode
                    document.body.classList.add('pause-animations');
                }
            });
            
            // Pause animations when tab is not visible
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    document.body.classList.add('pause-animations');
                } else {
                    document.body.classList.remove('pause-animations');
                }
            });
        });

        async function loadProjects(page = 1, filters = {}) {
            try {
                console.log('Loading projects...');
                
                // Build query string
                const params = new URLSearchParams({
                    page: page,
                    limit: 50,
                    sort: currentSort,
                    ...filters
                });
                
                const response = await fetch(`api/projects.php?${params}`);
                const data = await response.json();
                
                console.log('Projects API response:', data);
                
                if (data.success && data.projects) {
                    allProjects = data.projects;
                    currentPage = page;
                    currentPagination = data.pagination || null;
                    totalPages = currentPagination ? currentPagination.pages : 1;
                    
                    updateStats();
                    displayProjects(allProjects);
                    updatePagination();
                    
                    // Show project count notification briefly
                    if (page === 1 && data.projects.length > 0) {
                        setTimeout(() => {
                            showNotification('موفق', `${currentPagination ? currentPagination.total : data.projects.length} پروژه لود شد`, 'success');
                        }, 100);
                    }
                } else {
                    console.error('API Error:', data);
                    showNotification('خطا', 'خطا در بارگذاری پروژه‌ها', 'error');
                    showEmptyState();
                }
            } catch (error) {
                console.error('Network Error:', error);
                showNotification('خطا', 'خطا در اتصال به سرور', 'error');
                showEmptyState();
            } finally {
                document.getElementById('loadingState').style.display = 'none';
            }
        }

        function updateStats() {
            // Use pagination data for more accurate stats if available
            if (currentPagination) {
                animateNumber('totalProjects', currentPagination.total);
                // For downloads/stars/languages we'll use current page data since we don't have totals
                const totalDownloads = allProjects.reduce((sum, p) => sum + (p.downloads || 0), 0);
                const totalStars = allProjects.reduce((sum, p) => sum + (p.stars || 0), 0);
                const languages = [...new Set(allProjects.map(p => p.language).filter(lang => lang))];
                
                animateNumber('totalDownloads', totalDownloads);
                animateNumber('totalStars', totalStars);
                animateNumber('totalLanguages', languages.length);
            } else {
                // Fallback to current projects
                const totalProjects = allProjects.length;
                const totalDownloads = allProjects.reduce((sum, p) => sum + (p.downloads || 0), 0);
                const totalStars = allProjects.reduce((sum, p) => sum + (p.stars || 0), 0);
                const languages = [...new Set(allProjects.map(p => p.language).filter(lang => lang))];
                
                animateNumber('totalProjects', totalProjects);
                animateNumber('totalDownloads', totalDownloads);
                animateNumber('totalStars', totalStars);
                animateNumber('totalLanguages', languages.length);
            }
        }
        
        function updatePagination() {
            const container = document.getElementById('paginationContainer');
            const pageNumbers = document.getElementById('pageNumbers');
            const prevButton = document.getElementById('prevPage');
            const nextButton = document.getElementById('nextPage');
            
            if (!currentPagination || currentPagination.pages <= 1) {
                container.classList.add('hidden');
                return;
            }
            
            container.classList.remove('hidden');
            
            // Update prev/next buttons
            prevButton.disabled = currentPage <= 1;
            nextButton.disabled = currentPage >= totalPages;
            
            // Generate page numbers
            let pagesHtml = '';
            const maxPagesToShow = 7;
            let startPage = Math.max(1, currentPage - Math.floor(maxPagesToShow / 2));
            let endPage = Math.min(totalPages, startPage + maxPagesToShow - 1);
            
            // Adjust start if we're near the end
            if (endPage - startPage + 1 < maxPagesToShow) {
                startPage = Math.max(1, endPage - maxPagesToShow + 1);
            }
            
            // First page
            if (startPage > 1) {
                pagesHtml += `<button onclick="goToPage(1)" class="px-3 py-2 rounded-lg text-sm font-medium ${currentPage === 1 ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100'}">1</button>`;
                if (startPage > 2) {
                    pagesHtml += '<span class="px-2 py-2 text-gray-400">...</span>';
                }
            }
            
            // Page numbers
            for (let i = startPage; i <= endPage; i++) {
                pagesHtml += `<button onclick="goToPage(${i})" class="px-3 py-2 rounded-lg text-sm font-medium ${currentPage === i ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100'}">${i}</button>`;
            }
            
            // Last page
            if (endPage < totalPages) {
                if (endPage < totalPages - 1) {
                    pagesHtml += '<span class="px-2 py-2 text-gray-400">...</span>';
                }
                pagesHtml += `<button onclick="goToPage(${totalPages})" class="px-3 py-2 rounded-lg text-sm font-medium ${currentPage === totalPages ? 'bg-blue-500 text-white' : 'text-gray-600 hover:bg-gray-100'}">${totalPages}</button>`;
            }
            
            pageNumbers.innerHTML = pagesHtml;
        }
        
        function goToPage(page) {
            if (page < 1 || page > totalPages || page === currentPage) return;
            
            // Show loading
            document.getElementById('loadingState').style.display = 'block';
            document.getElementById('projectsGrid').style.display = 'none';
            
            // Load new page
            loadProjects(page);
            
            // Scroll to top
            scrollToTop();
        }

        function animateNumber(elementId, targetValue) {
            const element = document.getElementById(elementId);
            const startValue = 0;
            const duration = 1000; // 1 second
            const startTime = performance.now();
            
            function updateNumber(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                
                // Easing function for smooth animation
                const easeOut = 1 - Math.pow(1 - progress, 3);
                const currentValue = Math.floor(easeOut * targetValue);
                
                element.textContent = currentValue;
                
                if (progress < 1) {
                    requestAnimationFrame(updateNumber);
                } else {
                    element.textContent = targetValue;
                }
            }
            
            requestAnimationFrame(updateNumber);
        }

        function displayProjects(projects) {
            const grid = document.getElementById('projectsGrid');
            
            if (projects.length === 0) {
                grid.style.display = 'none';
                showEmptyState();
                return;
            }

            grid.style.display = 'grid';
            document.getElementById('emptyState').classList.add('hidden');
            
            let html = '';
            
            for (let i = 0; i < projects.length; i++) {
                const project = projects[i];
                
                const starButtonHtml = isLoggedIn 
                    ? '<button onclick="toggleStar(' + project.id + ')" class="gradient-secondary w-full py-2.5 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105"><i class="fas fa-star ml-1"></i>ستاره</button>'
                    : '<button onclick="showLoginRequired()" class="bg-gray-300 text-gray-600 w-full py-2.5 rounded-xl font-medium cursor-not-allowed"><i class="fas fa-star ml-1"></i>ستاره</button>';
                
                html += '<div class="project-card slide-in defer-animate relative group overflow-hidden">';
                
                // Status indicator
                const isNew = new Date() - new Date(project.createdAt) < 7 * 24 * 60 * 60 * 1000; // Less than 7 days
                const isHot = (project.downloads || 0) > 10 || (project.stars || 0) > 5;
                
                if (isNew) {
                    html += '<div class="absolute top-0 left-0 z-0"><span class="gradient-success text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-lg animate-pulse">🆕 جدید</span></div>';
                } else if (isHot) {
                    html += '<div class="absolute top-0 left-0 z-0"><span class="gradient-secondary text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-lg">🔥 ترند</span></div>';
                }
                
                // Header section with improved design
                html +=   '<div class="card-header relative">';
                html +=     '<div class="flex justify-between items-start mb-4">';
                html +=       '<div class="flex-1 ml-3">';
                html +=         '<a href="project.php?id=' + project.id + '" class="block group-hover:text-blue-600 transition-colors">';
                html +=           '<h3 class="text-xl font-black text-gray-900 mb-3 leading-tight group-hover:text-blue-600 transition-colors">' + escapeHtml(project.title) + '</h3>';
                html +=         '</a>';
                
                // Add project level and category badges
                if (project.level || project.category) {
                    html +=       '<div class="flex flex-wrap gap-2 mb-3">';
                    if (project.level) {
                        const levelColors = {
                            'مبتدی': 'from-green-500 to-green-600',
                            'متوسط': 'from-yellow-500 to-orange-500', 
                            'پیشرفته': 'from-red-500 to-red-600'
                        };
                        html +=     '<span class="gradient-primary text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-md">' + escapeHtml(project.level || '') + '</span>';
                    }
                    if (project.category) {
                        html +=     '<span class="gradient-secondary text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-md">' + escapeHtml(project.category || '') + '</span>';
                    }
                    html +=       '</div>';
                }
                
                html +=       '</div>';
                html +=       '<div class="flex flex-col items-end gap-2">';
                html +=         '<span class="language-tag flex-shrink-0 text-sm font-bold shadow-md">' + escapeHtml(project.language) + '</span>';
                
                // Quality score
                const qualityScore = Math.min(100, (project.stars || 0) * 10 + (project.downloads || 0) * 5 + (project.views || 0));
                if (qualityScore > 20) {
                    html +=       '<div class="flex items-center gap-1 bg-gradient-to-r from-blue-50 to-indigo-50 px-2 py-1 rounded-lg border border-blue-200">';
                    html +=         '<i class="fas fa-award text-blue-600 text-xs"></i>';
                    html +=         '<span class="text-xs font-bold text-blue-700">کیفیت: ' + Math.round(qualityScore) + '%</span>';
                    html +=       '</div>';
                }
                
                html +=       '</div>';
                html +=     '</div>';
                
                html +=     '<p class="card-desc text-gray-700 text-sm mb-4 leading-relaxed">' + escapeHtml(project.description) + '</p>';
                
                // Add tags if available
                const tags = Array.isArray(project.tags) ? project.tags : (project.tags || '').split(',').map(t => t.trim()).filter(Boolean);
                if (tags.length > 0) {
                    html +=   '<div class="flex flex-wrap gap-2 mb-4">';
                    tags.slice(0, 4).forEach(tag => {
                        html += '<span class="bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 px-3 py-1 rounded-full text-xs font-medium border border-blue-300 hover:from-blue-200 hover:to-blue-300 transition-all">#' + escapeHtml(tag) + '</span>';
                    });
                    if (tags.length > 4) {
                        html += '<span class="text-xs text-gray-500 bg-gray-100 px-2 py-1 rounded-full">+' + (tags.length - 4) + ' بیشتر</span>';
                    }
                    html +=   '</div>';
                }
                
                html +=   '</div>';
                
                // Enhanced Stats section
                html +=   '<div class="card-stats">';
                html +=     '<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-4">';
                
                // Downloads card
                html +=       '<div class="bg-gradient-to-br from-green-50 to-emerald-50 p-3 rounded-xl border border-green-200 hover:shadow-md transition-all">';
                html +=         '<div class="flex items-center justify-between">';
                html +=           '<div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center">';
                html +=             '<i class="fas fa-download text-white text-sm"></i>';
                html +=           '</div>';
                html +=           '<span class="text-lg font-bold text-green-700">' + (project.downloads || 0) + '</span>';
                html +=         '</div>';
                html +=         '<p class="text-xs text-green-600 mt-1 font-medium">دانلود</p>';
                html +=       '</div>';
                
                // Stars card
                html +=       '<div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-3 rounded-xl border border-yellow-200 hover:shadow-md transition-all">';
                html +=         '<div class="flex items-center justify-between">';
                html +=           '<div class="w-8 h-8 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center">';
                html +=             '<i class="fas fa-star text-white text-sm"></i>';
                html +=           '</div>';
                html +=           '<span class="text-lg font-bold text-yellow-700">' + (project.stars || 0) + '</span>';
                html +=         '</div>';
                html +=         '<p class="text-xs text-yellow-600 mt-1 font-medium">ستاره</p>';
                html +=       '</div>';
                
                // Views card
                html +=       '<div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-3 rounded-xl border border-blue-200 hover:shadow-md transition-all">';
                html +=         '<div class="flex items-center justify-between">';
                html +=           '<div class="w-8 h-8 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center">';
                html +=             '<i class="fas fa-eye text-white text-sm"></i>';
                html +=           '</div>';
                html +=           '<span class="text-lg font-bold text-blue-700">' + (project.views || 0) + '</span>';
                html +=         '</div>';
                html +=         '<p class="text-xs text-blue-600 mt-1 font-medium">بازدید</p>';
                html +=       '</div>';
                
                // File size card
                html +=       '<div class="bg-gradient-to-br from-purple-50 to-violet-50 p-3 rounded-xl border border-purple-200 hover:shadow-md transition-all">';
                html +=         '<div class="flex items-center justify-between">';
                html +=           '<div class="w-8 h-8 bg-gradient-to-r from-purple-500 to-violet-600 rounded-lg flex items-center justify-center">';
                html +=             '<i class="fas fa-hdd text-white text-sm"></i>';
                html +=           '</div>';
                html +=           '<span class="text-xs font-bold text-purple-700">' + formatFileSize(project.fileSize || 0) + '</span>';
                html +=         '</div>';
                html +=         '<p class="text-xs text-purple-600 mt-1 font-medium">حجم</p>';
                html +=       '</div>';
                
                html +=     '</div>';
                
                // Last update info
                html +=     '<div class="bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg p-3 border border-gray-300">';
                html +=       '<div class="flex items-center justify-between">';
                html +=         '<div class="flex items-center gap-2">';
                html +=           '<i class="fas fa-clock text-gray-600"></i>';
                html +=           '<span class="text-sm font-medium text-gray-700">آخرین بروزرسانی</span>';
                html +=         '</div>';
                html +=         '<span class="text-sm font-bold text-gray-800">' + formatDate(project.lastUpdatedAt || project.updatedAt || project.createdAt) + '</span>';
                html +=       '</div>';
                html +=     '</div>';
                
                html +=   '</div>';
                
                // Footer section with buttons
                html +=   '<div class="card-footer">';
                html +=     '<div class="space-y-2 mb-3">';
                html +=       '<button onclick="downloadProject(' + project.id + ')" class="gradient-success w-full py-2.5 text-white rounded-xl font-bold transition-all duration-300 transform hover:scale-105 hover:shadow-lg">';
                html +=         '<i class="fas fa-download ml-2"></i>دانلود پروژه</button>';
                html +=       '<a href="project.php?id=' + project.id + '" class="gradient-primary w-full py-2.5 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:shadow-lg text-center inline-block">';
                html +=         '<i class="fas fa-info-circle ml-2"></i>توضیحات</a>';
                html +=       starButtonHtml;
                
                // Add bookmark and report buttons - always visible (login required if not logged in)
                html +=     '<div class="flex space-x-2 space-x-reverse">';
                if (isLoggedIn) {
                    html +=   '<button onclick="toggleBookmark(' + project.id + ')" class="flex-1 bg-yellow-500 hover:bg-yellow-600 text-white py-2 rounded-lg font-medium transition-colors">';
                    html +=     '<i class="fas fa-bookmark ml-1"></i>علاقه‌مندی</button>';
                    html +=   '<button onclick="reportProject(' + project.id + ')" class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded-lg font-medium transition-colors">';
                    html +=     '<i class="fas fa-flag ml-1"></i>گزارش</button>';
                } else {
                    html +=   '<button onclick="showLoginRequired()" class="flex-1 bg-yellow-400/70 text-white py-2 rounded-lg font-medium transition-colors">';
                    html +=     '<i class="fas fa-bookmark ml-1"></i>علاقه‌مندی</button>';
                    html +=   '<button onclick="showLoginRequired()" class="flex-1 bg-red-400/70 text-white py-2 rounded-lg font-medium transition-colors">';
                    html +=     '<i class="fas fa-flag ml-1"></i>گزارش</button>';
                }
                html +=     '</div>';
                
                html +=     '</div>';
                
                // Developer Information Card - Always visible
                const authorName = project.author || project.authorName || 'ناشناس';
                const isVerified = project.authorProfile && project.authorProfile.verified;
                const authorPoints = project.authorProfile && project.authorProfile.points ? project.authorProfile.points : 0;
                
                html +=     '<div class="developer-card bg-gradient-to-br from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl p-4 mt-4 border-2 border-blue-200/50 shadow-lg hover:shadow-xl transition-all duration-300">';
                html +=       '<div class="flex flex-col sm:flex-row items-center sm:items-center sm:justify-between gap-4">';
                html +=         '<div class="flex items-center space-x-4 space-x-reverse w-full sm:w-auto">';
                html +=           '<div class="relative flex-shrink-0">';
                html +=             '<div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 via-purple-600 to-indigo-700 rounded-2xl shadow-xl flex items-center justify-center transform transition-all duration-300 hover:scale-110 hover:shadow-2xl ring-4 ring-white/50">';
                html +=               '<span class="text-white font-black text-lg sm:text-xl">' + getInitials(authorName) + '</span>';
                html +=             '</div>';
                
                // Always show verified badge if user is verified
                if (isVerified) {
                    html +=           '<div class="absolute -top-2 -right-2 w-6 h-6 sm:w-7 sm:h-7 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-xl flex items-center justify-center border-3 border-white verified-pulse ring-2 ring-blue-200">';
                    html +=             '<svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                    html +=           '</div>';
                }
                
                html +=           '</div>';
                html +=           '<div class="flex flex-col flex-1 min-w-0 text-center sm:text-right">';
                html +=             '<div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">';
                html +=               '<span class="text-sm font-bold text-gray-600 bg-gradient-to-r from-gray-100 to-gray-200 px-3 py-1.5 rounded-full whitespace-nowrap border border-gray-300">👨‍💻 توسعه‌دهنده</span>';
                
                if (isVerified) {
                    html +=             '<span class="text-sm font-bold text-blue-700 bg-gradient-to-r from-blue-100 to-blue-200 px-3 py-1.5 rounded-full flex items-center gap-2 whitespace-nowrap border border-blue-300 shadow-md">';
                    html +=               '<i class="fas fa-certificate text-blue-600"></i>✓ تایید شده';
                    html +=             '</span>';
                }
                
                html +=             '</div>';
                html +=             '<a href="profile.php?user=' + encodeURIComponent(authorName) + '" class="text-lg font-black text-gray-800 hover:text-blue-600 transition-all duration-300 hover:underline mb-2 block truncate">';
                html +=               escapeHtml(authorName);
                html +=             '</a>';
                
                // Always show points if available
                if (authorPoints > 0) {
                    html +=           '<div class="flex items-center justify-center sm:justify-start space-x-2 space-x-reverse">';
                    html +=             '<div class="flex items-center gap-2 bg-gradient-to-r from-yellow-50 to-orange-50 px-3 py-1.5 rounded-full border border-yellow-300">';
                    html +=               '<div class="w-3 h-3 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex-shrink-0 animate-pulse"></div>';
                    html +=               '<span class="text-sm font-bold text-yellow-700">امتیاز: ' + authorPoints.toLocaleString('fa-IR') + '</span>';
                    html +=             '</div>';
                    html +=           '</div>';
                }
                
                html +=           '</div>';
                html +=         '</div>';
                html +=         '<div class="flex-shrink-0 w-full sm:w-auto">';
                html +=           '<div class="bg-white/90 backdrop-blur-sm rounded-xl px-4 py-3 shadow-md border border-gray-300 text-center">';
                html +=             '<div class="flex items-center justify-center text-sm text-gray-600 mb-2">';
                html +=               '<i class="fas fa-calendar-alt ml-2 text-blue-600"></i>';
                html +=               '<span class="font-bold">تاریخ انتشار</span>';
                html +=             '</div>';
                html +=             '<span class="text-lg font-black text-gray-800">' + formatDate(project.createdAt) + '</span>';
                html +=           '</div>';
                html +=         '</div>';
                html +=       '</div>';
                html +=     '</div>';
                html +=   '</div>';
                
                html += '</div>';
            }
            
            grid.innerHTML = html;

            // Advanced performance optimizations
            requestAnimationFrame(() => {
                try {
                    const cards = grid.querySelectorAll('.defer-animate');
                    
                    // Performance monitoring
                    const isLowMemory = navigator.deviceMemory && navigator.deviceMemory < 4;
                    const isSlowConnection = navigator.connection && (navigator.connection.effectiveType === 'slow-2g' || navigator.connection.effectiveType === '2g');
                    
                    if (isLowMemory) {
                        document.body.classList.add('low-memory');
                    }
                    
                    // Reduce motion for accessibility
                    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                        document.body.classList.add('reduced-motion');
                    }
                    
                    // Intersection Observer with enhanced options
                    const observerOptions = {
                        rootMargin: isSlowConnection ? '50px' : '0px 0px -10% 0px',
                        threshold: isSlowConnection ? 0.05 : 0.1
                    };
                    
                    const io = new IntersectionObserver((entries, obs) => {
                        entries.forEach(entry => {
                            if (entry.isIntersecting) {
                                // Stagger animations for better performance
                                const delay = Array.from(cards).indexOf(entry.target) * 50;
                                setTimeout(() => {
                                    entry.target.classList.add('animate');
                                }, delay);
                                obs.unobserve(entry.target);
                            }
                        });
                    }, observerOptions);
                    
                    cards.forEach(el => io.observe(el));
                    
                    // Cleanup observer on page unload
                    window.addEventListener('beforeunload', () => {
                        io.disconnect();
                    }, { once: true });
                    
                } catch (e) {
                    console.warn('Performance optimizations failed:', e);
                }
            });
        }

        function filterProjects(filter) {
            currentFilter = filter;
            updateFilterButtons();
            applyFiltersAndSort();
        }

        function sortProjects(sort) {
            currentSort = sort;
            updateSortButtons();
            applyFiltersAndSort();
        }

        function applyFiltersAndSort() {
            let filteredProjects = [...allProjects];

            // Apply filter
            if (currentFilter !== 'all') {
                filteredProjects = filteredProjects.filter(project => 
                    project.language === currentFilter
                );
            }

            // Apply search
            const searchTerm = document.getElementById('searchInput').value.toLowerCase();
            if (searchTerm) {
                filteredProjects = filteredProjects.filter(project => 
                    project.title.toLowerCase().includes(searchTerm) ||
                    project.description.toLowerCase().includes(searchTerm) ||
                    project.language.toLowerCase().includes(searchTerm) ||
                    (project.author && project.author.toLowerCase().includes(searchTerm))
                );
            }

            // Apply sort
            switch (currentSort) {
                case 'newest':
                    filteredProjects.sort((a, b) => new Date(b.lastUpdatedAt || b.updatedAt || b.createdAt) - new Date(a.lastUpdatedAt || a.updatedAt || a.createdAt));
                    break;
                case 'popular':
                    filteredProjects.sort((a, b) => (b.views || 0) - (a.views || 0));
                    break;
                case 'downloads':
                    filteredProjects.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
                    break;
                case 'stars':
                    filteredProjects.sort((a, b) => (b.stars || 0) - (a.stars || 0));
                    break;
            }

            displayProjects(filteredProjects);
        }

        function updateFilterButtons() {
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.className = 'filter-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200';
            });
            
            // Map filter names to button IDs
            const filterMap = {
                'all': 'filterAll',
                'JavaScript': 'filterJS',
                'PHP': 'filterPHP',
                'Python': 'filterPython',
                'Java': 'filterJava',
                'C++': 'filterCpp'
            };
            
            const activeFilter = document.getElementById(filterMap[currentFilter] || 'filterAll');
            if (activeFilter) {
                activeFilter.className = 'filter-btn px-4 py-2 bg-blue-500 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm';
            }
        }

        function updateSortButtons() {
            document.querySelectorAll('.sort-btn').forEach(btn => {
                btn.className = 'sort-btn px-4 py-2 bg-gray-100 text-gray-700 rounded-xl font-medium transition-all duration-300 transform hover:scale-105 hover:bg-gray-200';
            });
            
            const activeSort = document.getElementById(`sort${currentSort.charAt(0).toUpperCase() + currentSort.slice(1)}`);
            if (activeSort) {
                activeSort.className = 'sort-btn px-4 py-2 bg-blue-500 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-sm';
            }
        }

        function searchProjects() {
            applyFiltersAndSort();
        }

        async function downloadProject(projectId) {
            // Require login before download
            if (!isLoggedIn) {
                showLoginPopup();
                return;
            }
            
            // Double check authentication with server
            try {
                const authCheck = await fetch('api/auth.php?action=me');
                const authData = await authCheck.json();
                if (!authData.ok || !authData.user) {
                    showLoginPopup();
                    return;
                }
            } catch (error) {
                showLoginPopup();
                return;
            }
            try {
                // First track the download
                const formData = new FormData();
                formData.append('action', 'track_download');
                formData.append('projectId', projectId);
                
                const trackResponse = await fetch('api/interactions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const trackData = await trackResponse.json();
                
                // Create download link and trigger download
                const downloadUrl = `api/download.php?id=${projectId}`;
                
                // Create a hidden link element and click it
                const link = document.createElement('a');
                link.href = downloadUrl;
                link.style.display = 'none';
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
                
                showNotification('دانلود شروع شد', 'فایل در حال دانلود است...', 'success');
                
                // Update projects to show new download count
                setTimeout(() => {
                    loadProjects();
                }, 1000);
                
            } catch (error) {
                console.error('Error downloading project:', error);
                
                // If tracking fails, still try to download the file
                try {
                    const downloadUrl = `api/download.php?id=${projectId}`;
                    const link = document.createElement('a');
                    link.href = downloadUrl;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    
                    showNotification('دانلود شروع شد', 'فایل در حال دانلود است...', 'success');
                } catch (downloadError) {
                    showNotification('خطا', 'خطا در دانلود فایل', 'error');
                }
            }
        }

        async function toggleStar(projectId) {
            try {
                const formData = new FormData();
                formData.append('action', 'toggle_star');
                formData.append('projectId', projectId);
                
                const response = await fetch('api/interactions.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                if (data.ok) {
                    const message = data.starred ? 'ستاره اضافه شد' : 'ستاره برداشته شد';
                    showNotification('ستاره', message, 'success');
                    loadProjects(); // Refresh to show updated star count
                } else {
                    showNotification('خطا', data.error || 'خطا در اضافه کردن ستاره', 'error');
                }
            } catch (error) {
                console.error('Error starring project:', error);
                showNotification('خطا', 'خطا در اضافه کردن ستاره', 'error');
            }
        }

        function showLoginRequired() {
            showNotification('ورود لازم', 'برای ستاره دادن ابتدا وارد حساب کاربری خود شوید', 'error');
        }



        function showEmptyState() {
            document.getElementById('projectsGrid').style.display = 'none';
            document.getElementById('emptyState').classList.remove('hidden');
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function formatDate(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '';
                return date.toLocaleDateString('fa-IR', { year: 'numeric', month: '2-digit', day: '2-digit' });
            } catch (error) {
                return '';
            }
        }

        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 بایت';
            const kb = bytes / 1024;
            if (kb < 1) return bytes + ' بایت';
            if (kb < 1024) return parseFloat(kb.toFixed(2)) + ' کیلوبایت';
            const mb = kb / 1024;
            return parseFloat(mb.toFixed(2)) + ' مگابایت';
        }

        function formatDateTime(dateString) {
            if (!dateString) return '';
            try {
                const date = new Date(dateString);
                if (isNaN(date.getTime())) return '';
                return date.toLocaleString('fa-IR', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit' });
            } catch (error) {
                return '';
            }
        }

        function getInitials(name) {
            if (!name) return '؟';
            return name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase();
        }

        function showEmptyState() {
            const grid = document.getElementById('projectsGrid');
            const emptyState = document.getElementById('emptyState');
            
            if (grid) grid.style.display = 'none';
            if (emptyState) emptyState.classList.remove('hidden');
        }

        function scrollToTop() {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            const icon = document.getElementById('mobileMenuIcon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
            } else {
                menu.classList.add('hidden');
                icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
            }
        }

        // Remove duplicate function - using modern version above

        // Project Details Modal logic
        function openProjectDetails(projectId) {
            const project = allProjects.find(p => (p.id||0) === projectId);
            if (!project) return;
            const modal = document.getElementById('projectDetailsModal');
            const body = document.getElementById('projectDetailsBody');
            try {
                const fd = new FormData(); fd.append('action','track_view'); fd.append('projectId', projectId);
                fetch('api/interactions.php', { method: 'POST', body: fd }).then(async (r)=>{ try { const j=await r.json(); if (j && j.ok) { project.views = j.views; const v=document.getElementById('detailsViews'); if (v) v.innerHTML = '<i class="fas fa-eye ml-1 text-blue-500"></i>'+j.views; } } catch(_){}});
            } catch(_){}
            // Build sender info HTML
            let senderInfoHtml = '';
            if (project.author) {
                senderInfoHtml = '<div class="bg-gray-50 rounded-xl p-4">\
                    <h4 class="font-semibold text-gray-900 mb-3"><i class="fas fa-user ml-2 text-blue-500"></i>اطلاعات فرستنده</h4>\
                    <div class="flex items-center">\
                        <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center ml-3">\
                            <span class="text-white font-bold text-sm">' + getInitials(project.author) + '</span>\
                        </div>\
                        <div class="flex-1">\
                            <div class="font-medium text-gray-900">\
                                <a href="profile.php?user=' + encodeURIComponent(project.author) + '" class="hover:text-blue-600 transition-colors">' + escapeHtml(project.author) + '</a>';
                
                if (project.authorProfile && project.authorProfile.verified) {
                    senderInfoHtml += '<span title="کاربر تایید‌شده - تیک آبی رسمی سورس بان" class="blue-tick inline-flex items-center gap-2 text-white px-4 py-2 rounded-full text-sm font-bold ml-3 verified-pulse transition-all duration-300">\
                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor" stroke="none"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>\
                        <span>✓ تایید شده رسمی</span>\
                    </span>';
                }
                
                senderInfoHtml += '</div>\
                            <div class="text-sm text-gray-500 mt-1">امتیاز: ' + ((project.authorProfile && project.authorProfile.points) || 0) + ' امتیاز</div>\
                        </div>\
                    </div>\
                </div>';
            }

            const html = '\
                <div class="space-y-5">\
                    <div class="flex justify-between items-start">\
                        <div>\
                            <h3 class="text-2xl font-bold text-gray-900">'+escapeHtml(project.title||'')+'</h3>\
                            <p class="text-sm text-gray-500 mt-2">ساخته شده: '+formatDate(project.createdAt)+' • بروزرسانی: '+formatDateTime(project.lastUpdatedAt || project.updatedAt || project.createdAt)+' • '+escapeHtml(project.language||'')+'</p>\
                        </div>\
                        <span class="language-tag ml-3">'+escapeHtml(project.language||'')+'</span>\
                    </div>\
                    <div class="border-l-4 border-blue-500 pl-4 bg-blue-50 rounded-r-lg p-4">\
                        <p class="text-gray-700 leading-7 whitespace-pre-wrap">'+escapeHtml(project.description||'')+'</p>\
                    </div>\
                    <div class="flex items-center space-x-6 space-x-reverse text-sm text-gray-600 bg-gray-50 rounded-xl p-4">\
                        <span class="flex items-center"><i class="fas fa-download ml-2 text-green-600"></i><span class="font-semibold">'+(project.downloads||0)+'</span> دانلود</span>\
                        <span class="flex items-center"><i class="fas fa-star ml-2 text-yellow-500"></i><span class="font-semibold">'+(project.stars||0)+'</span> ستاره</span>\
                        <span class="flex items-center" id="detailsViews"><i class="fas fa-eye ml-2 text-blue-500"></i><span class="font-semibold">'+(project.views||0)+'</span> بازدید</span>\
                    </div>\
                    ' + senderInfoHtml + '\
                    <div class="flex justify-end space-x-3 space-x-reverse pt-4 border-t border-gray-200">\
                        <a href="api/download.php?id='+project.id+'" class="gradient-success px-6 py-3 text-white rounded-xl font-bold hover:shadow-lg transition-all duration-300 transform hover:scale-105" onclick="event.preventDefault(); downloadProject('+project.id+');">\
                            <i class="fas fa-download ml-2"></i>دانلود پروژه\
                        </a>\
                    </div>\
                </div>';
            body.innerHTML = html;
            modal.classList.remove('hidden');
        }
        function closeProjectDetails(){ document.getElementById('projectDetailsModal').classList.add('hidden'); }

        // Search on Enter key
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                searchProjects();
            }
        });

        // Auto search on input
        document.getElementById('searchInput').addEventListener('input', function() {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                searchProjects();
            }, 500);
        });
        
        // Live Footer Stats Update
        function updateLiveStats() {
            // Update online users count
            const onlineElement = document.getElementById('onlineUsers');
            if (onlineElement) {
                const baseOnline = 5;
                const variation = Math.floor(Math.random() * 6) + 1; // 1-6
                onlineElement.textContent = baseOnline + variation;
            }
            
            // Update today views
            const viewsElement = document.getElementById('todayViews');
            if (viewsElement) {
                const baseViews = 120;
                const currentViews = parseInt(viewsElement.textContent) || baseViews;
                const increment = Math.floor(Math.random() * 2) + 1; // 1-2
                viewsElement.textContent = currentViews + increment;
            }
        }

        // Update stats every 12 seconds
        setInterval(updateLiveStats, 12000);
        
        // Initial stats load
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(updateLiveStats, 1500);
            // Initialize dark mode
            if (localStorage.getItem('sourcebaan_darkMode') === 'true') {
                document.documentElement.classList.add('dark-mode');
                updateThemeIcon();
            }
        });
        
        // Dark Mode Functions
        function toggleTheme() {
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            document.documentElement.classList.toggle('dark-mode');
            localStorage.setItem('sourcebaan_darkMode', isDarkMode ? 'false' : 'true');
            updateThemeIcon();
        }
        
        function updateThemeIcon() {
            const isDarkMode = document.documentElement.classList.contains('dark-mode');
            const themeIcon = document.getElementById('themeIcon');
            const mobileThemeIcon = document.getElementById('mobileThemeIcon');
            
            if (isDarkMode) {
                themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
                mobileThemeIcon.className = 'fas fa-sun ml-3 w-5';
            } else {
                themeIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
                mobileThemeIcon.className = 'fas fa-moon ml-3 w-5';
            }
        }
        
        // Bookmarks Functions
        function showBookmarks() {
            // Create bookmarks modal
            const modal = document.createElement('div');
            modal.id = 'bookmarksModal';
            modal.className = 'fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white dark-mode:bg-gray-800 rounded-2xl max-w-4xl w-full max-h-[80vh] overflow-hidden">
                    <div class="flex items-center justify-between p-6 border-b">
                        <h2 class="text-xl font-bold text-gray-900">علاقه‌مندی‌های من</h2>
                        <button onclick="closeBookmarks()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <div id="bookmarksContent" class="p-6 overflow-y-auto max-h-96">
                        <div class="loading-spinner mx-auto"></div>
                    </div>
                    <div id="bookmarksPagination" class="px-6 py-4 border-t bg-gray-50"></div>
                </div>
            `;
            document.body.appendChild(modal);
            loadBookmarks();
        }
        
        function closeBookmarks() {
            const modal = document.getElementById('bookmarksModal');
            if (modal) modal.remove();
        }
        
        async function loadBookmarks(page = 1) {
            try {
                const response = await fetch(`api/bookmarks.php?action=get&page=${page}&limit=12`);
                const data = await response.json();
                const content = document.getElementById('bookmarksContent');
                const pagination = document.getElementById('bookmarksPagination');
                
                if (data.ok && data.bookmarks.length > 0) {
                    content.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            ${data.bookmarks.map(project => `
                                <div class="project-card p-4">
                                    <h3 class="font-bold text-gray-900 mb-2">${escapeHtml(project.title || '')}</h3>
                                    <p class="text-gray-600 text-sm mb-3">${escapeHtml((project.description || '').substring(0, 100))}...</p>
                                    <div class="flex justify-between items-center">
                                        <span class="language-tag">${escapeHtml(project.language || '')}</span>
                                        <div class="space-x-2 space-x-reverse">
                                            <a href="project.php?id=${project.id}" class="text-blue-600 hover:text-blue-800">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button onclick="toggleBookmark(${project.id})" class="text-red-600 hover:text-red-800">
                                                <i class="fas fa-bookmark"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    `;
                    
                    // Add pagination
                    if (data.pagination.pages > 1) {
                        pagination.innerHTML = `
                            <div class="flex justify-center space-x-2 space-x-reverse">
                                ${Array.from({length: data.pagination.pages}, (_, i) => i + 1).map(p => `
                                    <button onclick="loadBookmarks(${p})" class="px-3 py-2 rounded ${p === data.pagination.page ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700'}">${p}</button>
                                `).join('')}
                            </div>
                        `;
                    }
                } else {
                    content.innerHTML = '<div class="text-center py-8"><i class="fas fa-bookmark text-4xl text-gray-300 mb-4"></i><p class="text-gray-500">هنوز پروژه‌ای به علاقه‌مندی‌ها اضافه نکرده‌اید</p></div>';
                    pagination.innerHTML = '';
                }
            } catch (error) {
                document.getElementById('bookmarksContent').innerHTML = '<div class="text-center py-8 text-red-600">خطا در بارگذاری علاقه‌مندی‌ها</div>';
            }
        }
        
        async function toggleBookmark(projectId) {
            if (!isLoggedIn) {
                showNotification('ورود لازم', 'برای ذخیره علاقه‌مندی‌ها ابتدا وارد شوید', 'error');
                return;
            }
            
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const formData = new FormData();
                formData.append('action', 'toggle');
                formData.append('projectId', projectId);
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('api/bookmarks.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.ok) {
                    showNotification('علاقه‌مندی', data.message, 'success');
                    // Update bookmark icon if in bookmarks modal
                    if (document.getElementById('bookmarksModal')) {
                        loadBookmarks();
                    }
                } else {
                    showNotification('خطا', data.error || 'خطا در ذخیره علاقه‌مندی', 'error');
                }
            } catch (error) {
                showNotification('خطا', 'خطا در اتصال به سرور', 'error');
            }
        }
        
        // Report Project Function
        function reportProject(projectId) {
            if (!isLoggedIn) {
                showNotification('ورود لازم', 'برای گزارش پروژه ابتدا وارد شوید', 'error');
                return;
            }
            
            const modal = document.createElement('div');
            modal.id = 'reportModal';
            modal.className = 'fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4';
            modal.innerHTML = `
                <div class="bg-white rounded-2xl max-w-md w-full p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-gray-900">گزارش پروژه</h2>
                        <button onclick="closeReportModal()" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="reportForm" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">دلیل گزارش</label>
                            <select name="reason" required class="w-full border border-gray-300 rounded-lg px-3 py-2">
                                <option value="">انتخاب کنید</option>
                                <option value="spam">اسپم</option>
                                <option value="inappropriate">محتوای نامناسب</option>
                                <option value="copyright">نقض حق نشر</option>
                                <option value="malware">بدافزار</option>
                                <option value="other">سایر</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">توضیحات (اختیاری)</label>
                            <textarea name="description" rows="3" class="w-full border border-gray-300 rounded-lg px-3 py-2" placeholder="توضیحات تکمیلی..."></textarea>
                        </div>
                        <div class="flex justify-end space-x-3 space-x-reverse">
                            <button type="button" onclick="closeReportModal()" class="px-4 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50">انصراف</button>
                            <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">ارسال گزارش</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('reportForm').addEventListener('submit', async (e) => {
                e.preventDefault();
                await submitReport(projectId, e.target);
            });
        }
        
        function closeReportModal() {
            const modal = document.getElementById('reportModal');
            if (modal) modal.remove();
        }
        
        async function submitReport(projectId, form) {
            try {
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const formData = new FormData(form);
                formData.append('action', 'submit');
                formData.append('projectId', projectId);
                formData.append('csrf_token', csrfToken);
                
                const response = await fetch('api/reports.php', { method: 'POST', body: formData });
                const data = await response.json();
                
                if (data.ok) {
                    showNotification('گزارش ارسال شد', data.message, 'success');
                    closeReportModal();
                } else {
                    showNotification('خطا', data.error || 'خطا در ارسال گزارش', 'error');
                }
            } catch (error) {
                showNotification('خطا', 'خطا در اتصال به سرور', 'error');
            }
        }
    </script>
    
    <!-- Bottom Ads -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-8">
        <?php echo display_ads('sources_bottom', 2); ?>
    </div>

    <!-- Footer -->
    <footer class="relative bg-gradient-to-r from-gray-900 via-slate-900 to-gray-900 text-white py-16 mt-20">
        <!-- Clean top border -->
        <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500"></div>
        <!-- Simple Background Pattern -->
        <div class="absolute inset-0 opacity-5">
            <div class="absolute top-10 left-10 w-20 h-20 bg-white rounded-full"></div>
            <div class="absolute bottom-10 right-10 w-16 h-16 bg-white rounded-full"></div>
            <div class="absolute top-1/2 left-1/3 w-12 h-12 bg-white rounded-full"></div>
        </div>
        
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pt-8">
            <!-- Main Footer Content -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-12 items-start">
                
                <!-- Brand Section -->
                <div class="text-center md:text-right">
                    <h3 class="text-3xl font-bold mb-4 bg-gradient-to-r from-blue-400 to-purple-400 bg-clip-text text-transparent">
                        سورس کده
                    </h3>
                    <p class="text-gray-300 text-base leading-relaxed mb-8">
                        بزرگترین مرجع پروژه‌های متن‌باز<br>
                        و انجمن توسعه‌دهندگان ایران
                    </p>
                    
                    <!-- Quick Links -->
                    <div class="space-y-3">
                        <a href="sources.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-blue-500/20 group-hover:bg-blue-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-gem text-blue-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">کاوش پروژه‌ها</span>
                        </a>
                        <a href="forum.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-green-500/20 group-hover:bg-green-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-comments text-green-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">انجمن توسعه‌دهندگان</span>
                        </a>
                        <a href="upload.php" class="flex items-center justify-center md:justify-end space-x-3 space-x-reverse text-gray-300 hover:text-white transition-colors group">
                            <div class="w-8 h-8 bg-purple-500/20 group-hover:bg-purple-500/30 rounded-lg flex items-center justify-center transition-colors">
                                <i class="fas fa-upload text-purple-400 text-sm"></i>
                            </div>
                            <span class="text-sm font-medium">آپلود پروژه</span>
                        </a>
                    </div>
                </div>
                
                <!-- Contact & Social Section -->
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-bold mb-6 text-white">ارتباط با ما</h4>
                    
                    <div class="space-y-4">
                        <!-- Telegram Channel -->
                        <a href="https://t.me/sourrce_kade" target="_blank" class="group block bg-gradient-to-r from-blue-500 to-cyan-500 hover:from-blue-600 hover:to-cyan-600 rounded-2xl p-4 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <div class="flex items-center space-x-4 space-x-reverse">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.568 8.16l-1.61 7.56c-.12.56-.44.7-.9.44l-2.49-1.83-1.2 1.16c-.13.13-.25.25-.5.25l.18-2.51 4.56-4.12c.2-.18-.04-.28-.3-.1l-5.64 3.55-2.43-.76c-.53-.16-.54-.53.11-.78l9.5-3.66c.44-.16.83.1.68.78z"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold">کانال تلگرام</p>
                                    <p class="text-blue-100 text-sm">آخرین اخبار و بروزرسانی‌ها</p>
                                </div>
                            </div>
                        </a>
                        
                        <!-- Developer Contact -->
                        <a href="https://t.me/itsthemoein" target="_blank" class="group block bg-gradient-to-r from-purple-500 to-pink-500 hover:from-purple-600 hover:to-pink-600 rounded-2xl p-4 transform hover:scale-105 transition-all duration-300 shadow-xl hover:shadow-2xl">
                            <div class="flex items-center space-x-4 space-x-reverse">
                                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div class="text-right">
                                    <p class="text-white font-bold">تیم توسعه</p>
                                    <p class="text-purple-100 text-sm">پشتیبانی و همکاری</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>
                
                <!-- Features Section -->
                <div class="text-center md:text-left">
                    <h4 class="text-xl font-bold mb-6 text-white">چرا سورس کده؟</h4>
                    <div class="space-y-4">
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-emerald-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">کیفیت بالا</p>
                                <p class="text-gray-400 text-sm">پروژه‌های تست شده</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">انجمن فعال</p>
                                <p class="text-gray-400 text-sm">پشتیبانی ۲۴ ساعته</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-center md:justify-start space-x-3 space-x-reverse bg-white/5 rounded-xl p-3 hover:bg-white/10 transition-all duration-300">
                            <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-xl flex items-center justify-center shadow-lg">
                                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">رایگان</p>
                                <p class="text-gray-400 text-sm">برای همه توسعه‌دهندگان</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Bottom Section -->
            <div class="border-t border-gray-700 mt-16 pt-8">
                <div class="text-center">
                    <div class="bg-gray-800/50 rounded-xl p-6 mx-auto max-w-2xl border border-gray-700">
                        <p class="text-gray-300 text-base font-medium mb-3">
                            © <?= date('Y') ?> سورس کده - تمامی حقوق محفوظ است
                        </p>
                        <p class="text-gray-400 text-sm">
                            طراحی و توسعه با <span class="text-red-400 animate-pulse">❤️</span> برای جامعه برنامه‌نویسی ایران
                        </p>
                        <div class="flex justify-center items-center mt-4 space-x-4 space-x-reverse">
                            <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                            <span class="text-xs text-gray-500">آنلاین و فعال</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <?php echo get_ad_tracking_script(); ?>
</body>
</html>