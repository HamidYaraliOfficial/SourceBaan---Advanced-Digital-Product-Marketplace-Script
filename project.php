<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/db.php';

// Support both old ID format and new slug format
$id = 0;
$slug = $_GET['slug'] ?? '';

if (!empty($slug)) {
    // Handle pretty URL slug
    $id = parse_project_slug($slug);
} else {
    // Handle old-style ID parameter
    $id = (int)($_GET['id'] ?? 0);
}

if ($id <= 0) {
    header('Location: sources.php');
    exit;
}

$projects = JsonDB::read('submissions');
$project = null;
foreach ($projects as $p) { 
    if ((int)($p['id'] ?? 0) === $id && ($p['status'] ?? 'pending') === 'approved') { 
        $project = $p; 
        break; 
    } 
}

if (!$project) {
    header('HTTP/1.1 404 Not Found');
}

// Increment views (once per session) and persist
if ($project) {
    if (!isset($_SESSION)) session_start();
    $viewed = $_SESSION['viewed_projects'] ?? [];
    if (!in_array($project['id'], $viewed, true)) {
        // update in-memory and on-disk
        foreach ($projects as $idx => $p) {
            if ((int)($p['id'] ?? 0) === (int)$project['id']) {
                $projects[$idx]['views'] = (int)($projects[$idx]['views'] ?? 0) + 1;
                $projects[$idx]['updatedAt'] = date('c');
                $project['views'] = $projects[$idx]['views'];
                break;
            }
        }
        JsonDB::write('submissions', $projects);
        $viewed[] = $project['id'];
        $_SESSION['viewed_projects'] = $viewed;
    } else {
        // ensure $project has views value
        $project['views'] = (int)($project['views'] ?? 0);
    }
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <?php
    if (session_status() === PHP_SESSION_NONE) { @session_start(); }
    $csrfToken = csrf_get_token();
    ?>
    <meta name="csrf-token" content="<?php echo htmlspecialchars($csrfToken); ?>">
    <?php if ($project): ?>
    <title><?php echo htmlspecialchars($project['title']) . ' - دانلود رایگان | سورس بان - SourceBan'; ?></title>
    <meta name="description" content="دانلود رایگان پروژه <?php echo htmlspecialchars($project['title']); ?> - <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 155)); ?> | سورس کد <?php echo htmlspecialchars($project['language'] ?? ''); ?> | سورس بان">
    <meta name="keywords" content="<?php echo implode(', ', [
        'دانلود ' . htmlspecialchars($project['title']),
        'سورس ' . htmlspecialchars($project['language'] ?? 'کد'),
        'پروژه ' . htmlspecialchars($project['language'] ?? ''),
        'اسکریپت ' . htmlspecialchars($project['language'] ?? ''),
        htmlspecialchars($project['title']),
        'سورس کد رایگان',
        'پروژه آماده',
        'دانلود رایگان',
        'open source',
        'sourcebaan',
        'سورس بان'
    ]); ?>">
    <link rel="canonical" href="https://sourcebaan.ir<?php echo build_project_pretty_path($id, $project['title']); ?>">
    
    <!-- Open Graph Tags -->
    <meta property="og:title" content="<?php echo htmlspecialchars($project['title']) . ' - دانلود رایگان | سورس بان'; ?>">
    <meta property="og:description" content="دانلود رایگان پروژه <?php echo htmlspecialchars($project['title']); ?> - <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 155)); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sourcebaan.ir<?php echo build_project_pretty_path($id, $project['title']); ?>">
    <meta property="og:site_name" content="سورس بان - SourceBan">
    <meta property="og:locale" content="fa_IR">
    <?php 
    $previewImages = [];
    if (!empty($project['previewImages'])) {
        $previewImages = is_array($project['previewImages']) ? $project['previewImages'] : json_decode($project['previewImages'], true) ?? [];
    }
    if (!empty($previewImages)):
    ?>
    <meta property="og:image" content="<?php echo htmlspecialchars($previewImages[0]); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <?php endif; ?>
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($project['title']) . ' - دانلود رایگان | سورس بان'; ?>">
    <meta name="twitter:description" content="دانلود رایگان پروژه <?php echo htmlspecialchars($project['title']); ?> - <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 155)); ?>">
    <?php if (!empty($previewImages)): ?>
    <meta name="twitter:image" content="<?php echo htmlspecialchars($previewImages[0]); ?>">
    <?php endif; ?>
    
    <!-- JSON-LD Structured Data -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareSourceCode",
        "name": "<?php echo htmlspecialchars($project['title']); ?>",
        "description": "<?php echo htmlspecialchars($project['description'] ?? ''); ?>",
        "programmingLanguage": "<?php echo htmlspecialchars($project['language'] ?? ''); ?>",
        "runtimePlatform": "<?php echo htmlspecialchars($project['language'] ?? ''); ?>",
        "codeRepository": "https://sourcebaan.ir<?php echo build_project_pretty_path($id, $project['title']); ?>",
        "downloadUrl": "https://sourcebaan.ir/api/download.php?id=<?php echo (int)$id; ?>",
        "author": {
            "@type": "Person",
            "name": "<?php echo htmlspecialchars($project['author'] ?? 'ناشناس'); ?>"
        },
        "dateCreated": "<?php echo htmlspecialchars($project['createdAt'] ?? date('c')); ?>",
        "dateModified": "<?php echo htmlspecialchars($project['updatedAt'] ?? $project['createdAt'] ?? date('c')); ?>",
        "fileSize": "<?php echo (int)($project['fileSize'] ?? 0); ?>",
        "version": "<?php echo htmlspecialchars($project['version'] ?? '1.0.0'); ?>",
        "license": "Open Source",
        "url": "https://sourcebaan.ir<?php echo build_project_pretty_path($id, $project['title']); ?>",
        "publisher": {
            "@type": "Organization",
            "name": "سورس بان - SourceBan",
            "url": "https://sourcebaan.ir",
            "logo": {
                "@type": "ImageObject",
                "url": "https://sourcebaan.ir/logo.png"
            }
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "<?php echo min(5, max(1, ((int)($project['stars'] ?? 0) / 10) + 4)); ?>",
            "reviewCount": "<?php echo max(1, (int)($project['views'] ?? 1) / 100); ?>"
        },
        "interactionStatistic": [
            {
                "@type": "InteractionCounter",
                "interactionType": "https://schema.org/DownloadAction",
                "userInteractionCount": "<?php echo (int)($project['downloads'] ?? 0); ?>"
            },
            {
                "@type": "InteractionCounter", 
                "interactionType": "https://schema.org/ViewAction",
                "userInteractionCount": "<?php echo (int)($project['views'] ?? 0); ?>"
            }
        ]
        <?php if (!empty($previewImages)): ?>
        ,
        "image": [
            <?php foreach ($previewImages as $index => $image): ?>
            "<?php echo htmlspecialchars($image); ?>"<?php echo $index < count($previewImages) - 1 ? ',' : ''; ?>
            <?php endforeach; ?>
        ]
        <?php endif; ?>
    }
    </script>
    
    <!-- Additional SEO Meta Tags -->
    <meta name="author" content="<?php echo htmlspecialchars($project['author'] ?? 'سورس بان'); ?>">
    <meta name="generator" content="SourceBan - سورس بان">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <link rel="alternate" hreflang="fa" href="https://sourcebaan.ir<?php echo build_project_pretty_path($id, $project['title']); ?>">
    
    <?php else: ?>
    <title>پروژه یافت نشد | سورس بان - SourceBan</title>
    <meta name="description" content="پروژه مورد نظر یافت نشد. مجموعه کاملی از پروژه‌های متن باز و سورس کدهای رایگان را در سورس بان مشاهده کنید.">
    <meta name="robots" content="noindex, nofollow">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- TailwindCSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        'vazir': ['Vazirmatn', 'system-ui', 'sans-serif']
                    },
                    colors: {
                        primary: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            200: '#bfdbfe',
                            300: '#93c5fd',
                            400: '#60a5fa',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            800: '#1e40af',
                            900: '#1e3a8a',
                        },
                        success: {
                            50: '#f0fdf4',
                            100: '#dcfce7',
                            200: '#bbf7d0',
                            300: '#86efac',
                            400: '#4ade80',
                            500: '#22c55e',
                            600: '#16a34a',
                            700: '#15803d',
                            800: '#166534',
                            900: '#14532d',
                        }
                    },
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'bounce-soft': 'bounceSoft 1s infinite',
                        'pulse-glow': 'pulseGlow 2s infinite',
                        'float': 'float 3s ease-in-out infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': { opacity: '0' },
                            '100%': { opacity: '1' }
                        },
                        slideUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' }
                        },
                        bounceSoft: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-5px)' }
                        },
                        pulseGlow: {
                            '0%, 100%': { boxShadow: '0 0 20px rgba(59, 130, 246, 0.3)' },
                            '50%': { boxShadow: '0 0 30px rgba(59, 130, 246, 0.6)' }
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0px)' },
                            '50%': { transform: 'translateY(-10px)' }
                        }
                    }
                }
            }
        }
    </script>
    
    <style>
        * {
            font-family: 'Vazirmatn', system-ui, sans-serif;
        }

        /* Optimized CSS Variables */
        :root {
            --gradient-primary: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            --gradient-success: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);
            --gradient-card: linear-gradient(145deg, #ffffff 0%, #f8fafc 100%);
            --shadow-card: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            --border-radius: 16px;
        }

        /* Performance optimized animations */
        .animate-on-scroll {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .animate-on-scroll.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Modern glass effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: var(--shadow-lg);
        }

        /* Modern button styles */
        .btn-modern {
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            transform: translateZ(0);
        }

        .btn-modern::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn-modern:hover::before {
            left: 100%;
        }

        /* Responsive images */
        .responsive-img {
            max-width: 100%;
            height: auto;
            border-radius: var(--border-radius);
            transition: transform 0.3s ease;
        }

        .responsive-img:hover {
            transform: scale(1.02);
        }

        /* Sticky sidebar optimization */
        .sticky-sidebar {
            position: sticky;
            top: 6rem;
            align-self: flex-start;
        }

        .sticky-sidebar > div {
            margin-bottom: 1.5rem;
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .container-mobile {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            
            .text-responsive {
                font-size: clamp(0.875rem, 2.5vw, 1.125rem);
            }
            
            .card-mobile {
                margin-bottom: 1rem;
                border-radius: 12px;
            }

            /* Remove sticky on mobile for better UX */
            .sticky-sidebar {
                position: static;
            }

            .sticky-sidebar > div {
                position: static !important;
                top: auto !important;
            }
        }

        /* Disable animations for users who prefer reduced motion */
        @media (prefers-reduced-motion: reduce) {
            *, *::before, *::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
            }
        }

        /* Tab styles */
        .tab-button {
            transition: all 0.3s ease;
        }

        .tab-button.active {
            background: var(--gradient-primary);
            color: white;
            transform: translateY(-2px);
            box-shadow: var(--shadow-lg);
        }

        /* Loading states */
        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-gray-50 via-white to-blue-50 font-vazir">
    <!-- Navigation -->
    <nav class="sticky top-0 z-50 bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <a href="index.php" class="flex items-center space-x-3 space-x-reverse group">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300 animate-float">
                        <i class="fas fa-code text-white text-lg"></i>
                    </div>
                    <div>
                        <span class="text-xl font-bold text-gray-900">سورس کده</span>
                        <p class="text-xs text-gray-600">مرجع پروژه‌های اوپن سورس</p>
                    </div>
                </a>
                
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="sources.php" class="btn-modern bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-2.5 rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                        <i class="fas fa-search ml-2"></i>
                        کاوش پروژه‌ها
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <?php if (!$project): ?>
        <!-- 404 Page -->
        <div class="min-h-screen flex items-center justify-center px-4">
            <div class="text-center animate-fade-in">
                <div class="w-32 h-32 mx-auto bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center mb-8 animate-bounce-soft">
                    <i class="fas fa-search text-white text-4xl"></i>
                </div>
                <h1 class="text-4xl font-bold text-gray-900 mb-4">پروژه یافت نشد</h1>
                <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">ممکن است این پروژه حذف یا هنوز تایید نشده باشد.</p>
                <a href="sources.php" class="btn-modern bg-gradient-to-r from-blue-600 to-purple-600 text-white px-8 py-3 rounded-xl font-bold text-lg inline-flex items-center hover:shadow-lg transition-all duration-300">
                    <i class="fas fa-arrow-right ml-2"></i>
                    بازگشت به فهرست پروژه‌ها
                </a>
            </div>
        </div>
    <?php else: ?>

    <!-- Hero Section -->
    <section class="relative bg-gradient-to-r from-blue-600 via-purple-600 to-blue-800 text-white py-16 overflow-hidden">
        <!-- Background decorations -->
        <div class="absolute inset-0 opacity-10">
            <div class="absolute top-10 left-10 w-32 h-32 bg-white rounded-full animate-float"></div>
            <div class="absolute bottom-10 right-10 w-24 h-24 bg-white rounded-full animate-float" style="animation-delay: -1.5s;"></div>
            <div class="absolute top-1/2 left-1/3 w-16 h-16 bg-white rounded-full animate-float" style="animation-delay: -3s;"></div>
        </div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative">
            <div class="text-center animate-slide-up">
                <!-- Badge -->
                <div class="inline-flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2 mb-6">
                    <i class="fas fa-fire text-yellow-300 ml-2 animate-bounce-soft"></i>
                    <span class="text-sm font-semibold">پروژه محبوب</span>
                </div>
                
                <!-- Title -->
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-black mb-6 leading-tight">
                    <?php echo htmlspecialchars($project['title']); ?>
                </h1>
                
                <!-- Description -->
                <p class="text-xl md:text-2xl text-blue-100 max-w-4xl mx-auto leading-relaxed mb-8">
                    <?php echo htmlspecialchars(substr($project['description'] ?? '', 0, 200)) . (strlen($project['description'] ?? '') > 200 ? '...' : ''); ?>
                </p>
                
                <!-- Stats -->
                <div class="flex flex-wrap justify-center gap-4 text-sm md:text-base">
                    <div class="flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-code ml-2 text-blue-200"></i>
                        <span><?php echo htmlspecialchars($project['language'] ?? ''); ?></span>
                    </div>
                    <div class="flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-star ml-2 text-yellow-300"></i>
                        <span><?php echo (int)($project['stars'] ?? 0); ?> ستاره</span>
                    </div>
                    <div class="flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-download ml-2 text-green-300"></i>
                        <span><?php echo (int)($project['downloads'] ?? 0); ?> دانلود</span>
                    </div>
                    <div class="flex items-center bg-white/20 backdrop-blur-sm rounded-full px-4 py-2">
                        <i class="fas fa-eye ml-2 text-purple-200"></i>
                        <span><?php echo (int)($project['views'] ?? 0); ?> مشاهده</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
            
            <!-- Main Content Column -->
            <div class="lg:col-span-3 space-y-8">
                
                <!-- Breadcrumb -->
                <nav class="flex items-center space-x-2 space-x-reverse text-sm text-gray-600 animate-on-scroll">
                    <a href="index.php" class="hover:text-blue-600 transition-colors">خانه</a>
                    <i class="fas fa-chevron-left text-xs"></i>
                    <a href="sources.php" class="hover:text-blue-600 transition-colors">پروژه‌ها</a>
                    <i class="fas fa-chevron-left text-xs"></i>
                    <span class="text-gray-900 font-medium"><?php echo htmlspecialchars($project['title']); ?></span>
                </nav>

                <!-- Project Image -->
                <?php 
                $previewImages = [];
                if (!empty($project['previewImages'])) {
                    $previewImages = is_array($project['previewImages']) ? $project['previewImages'] : json_decode($project['previewImages'], true) ?? [];
                }
                if (!empty($previewImages)): 
                ?>
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <img src="<?php echo htmlspecialchars($previewImages[0]); ?>" 
                         alt="پیش‌نمایش پروژه" 
                         class="responsive-img w-full h-64 md:h-96 object-cover cursor-pointer"
                         onclick="openImageModal('<?php echo htmlspecialchars($previewImages[0]); ?>', 0)"
                         loading="lazy">
                </div>
                <?php else: ?>
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <div class="w-full h-64 md:h-96 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center">
                        <div class="text-center text-gray-500">
                            <i class="fas fa-code text-6xl mb-4 text-blue-500"></i>
                            <p class="text-xl font-bold"><?php echo strtoupper(htmlspecialchars($project['language'] ?? 'CODE')); ?></p>
                            <p class="text-sm">پروژه اوپن سورس</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Tabs -->
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <div class="flex flex-wrap gap-2 mb-6 border-b border-gray-200 pb-4">
                        <button class="tab-button active px-6 py-3 rounded-xl font-semibold transition-all duration-300" data-tab="details">
                            <i class="fas fa-info-circle ml-2"></i>
                            جزئیات پروژه
                        </button>
                        <button class="tab-button px-6 py-3 rounded-xl font-semibold text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300" data-tab="setup">
                            <i class="fas fa-cog ml-2"></i>
                            راهنمای نصب
                        </button>
                        <button class="tab-button px-6 py-3 rounded-xl font-semibold text-gray-600 hover:text-blue-600 hover:bg-blue-50 transition-all duration-300" data-tab="comments">
                            <i class="fas fa-comments ml-2"></i>
                            نظرات کاربران
                        </button>
                    </div>

                    <!-- Tab Content -->
                    <div id="tab-details" class="tab-content">
                        <div class="prose max-w-none">
                            <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-star text-yellow-500 ml-2"></i>
                                درباره پروژه
                            </h2>
                            <div class="text-gray-700 text-lg leading-relaxed mb-6">
                                <?php echo nl2br(htmlspecialchars($project['description'] ?? '')); ?>
                            </div>
                            
                            <h3 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                                <i class="fas fa-cogs text-blue-500 ml-2"></i>
                                مشخصات فنی
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="text-sm text-gray-600 mb-1">زبان برنامه‌نویسی</div>
                                    <div class="font-bold text-gray-900"><?php echo htmlspecialchars($project['language'] ?? ''); ?></div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="text-sm text-gray-600 mb-1">حجم فایل</div>
                                    <div class="font-bold text-gray-900"><?php echo number_format((($project['fileSize'] ?? 0) / 1024), 2); ?> کیلوبایت</div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="text-sm text-gray-600 mb-1">سطح پروژه</div>
                                    <div class="font-bold text-gray-900"><?php echo htmlspecialchars($project['level'] ?? 'متوسط'); ?></div>
                                </div>
                                <div class="bg-gray-50 rounded-xl p-4">
                                    <div class="text-sm text-gray-600 mb-1">نسخه</div>
                                    <div class="font-bold text-gray-900"><?php echo htmlspecialchars($project['version'] ?? '1.0.0'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="tab-setup" class="tab-content hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-rocket text-green-500 ml-2"></i>
                            راهنمای نصب و راه‌اندازی
                        </h2>
                        <div class="bg-gray-50 rounded-xl p-6 text-gray-700 leading-relaxed whitespace-pre-wrap">
                            <?php echo htmlspecialchars($project['setupGuide'] ?? 'راهنمای نصب ارائه نشده است.'); ?>
                        </div>
                    </div>

                    <div id="tab-comments" class="tab-content hidden">
                        <h2 class="text-2xl font-bold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-users text-purple-500 ml-2"></i>
                            نظرات کاربران
                        </h2>
                        <div id="comments" class="space-y-4 mb-6">
                            <!-- Comments will be loaded here -->
                        </div>
                        
                        <?php if (current_user()): ?>
                        <form id="commentForm" class="bg-gray-50 rounded-xl p-6">
                            <textarea id="commentInput" rows="4" class="w-full border border-gray-300 rounded-xl p-4 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors resize-none" placeholder="نظر خود را بنویسید..."></textarea>
                            <div class="mt-4 text-left">
                                <button type="submit" class="btn-modern bg-gradient-to-r from-blue-600 to-purple-600 text-white px-6 py-3 rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                                    <i class="fas fa-paper-plane ml-2"></i>
                                    ارسال نظر
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="text-center py-8 bg-gray-50 rounded-xl">
                            <i class="fas fa-user-lock text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-600">برای ثبت نظر <a href="auth.php" class="text-blue-600 font-semibold hover:underline">وارد شوید</a></p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Preview Images Gallery -->
                <?php if (!empty($previewImages) && count($previewImages) > 1): ?>
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                        <i class="fas fa-images text-purple-500 ml-2"></i>
                        گالری تصاویر
                    </h2>
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <?php foreach ($previewImages as $index => $image): ?>
                        <div class="relative group cursor-pointer" onclick="openImageModal('<?php echo htmlspecialchars($image); ?>', <?php echo $index; ?>)">
                            <img src="<?php echo htmlspecialchars($image); ?>" 
                                 alt="پیش‌نمایش <?php echo $index + 1; ?>" 
                                 class="w-full h-32 md:h-40 object-cover rounded-xl transition-transform duration-300 group-hover:scale-105"
                                 loading="lazy">
                            <div class="absolute inset-0 bg-black bg-opacity-0 group-hover:bg-opacity-30 rounded-xl transition-all duration-300 flex items-center justify-center">
                                <i class="fas fa-search-plus text-white text-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"></i>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Password Section -->
                <?php if (!empty($project['password'])): ?>
                <div class="glass-card rounded-2xl p-6 animate-on-scroll border-l-4 border-red-500">
                    <h2 class="text-xl font-bold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-lock text-red-500 ml-2"></i>
                        رمز عبور فایل
                    </h2>
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <p class="text-red-700 mb-3">برای استخراج فایل ZIP از رمز عبور زیر استفاده کنید:</p>
                        <div class="flex items-center space-x-3 space-x-reverse">
                            <code id="passwordText" class="bg-white px-4 py-2 rounded-lg border border-red-200 font-mono text-lg font-bold text-red-800 select-all flex-1">
                                <?php echo htmlspecialchars($project['password']); ?>
                            </code>
                            <button onclick="copyPassword()" class="btn-modern bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-semibold transition-all duration-300">
                                <i class="fas fa-copy ml-1"></i>کپی
                            </button>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

            </div>

            <!-- Sidebar -->
            <div class="lg:col-span-1 sticky-sidebar">
                
                <!-- Download Card -->
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <div class="text-center mb-6">
                        <div class="text-3xl font-black text-green-600 mb-2">رایگان</div>
                        <div class="text-gray-600">دانلود فوری</div>
                    </div>
                    
                    <div class="space-y-3 mb-6">
                        <button onclick="downloadProject(<?php echo (int)$project['id']; ?>)" class="btn-modern w-full bg-gradient-to-r from-green-600 to-emerald-600 text-white py-4 rounded-xl font-bold text-lg hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-download ml-2"></i>
                            دانلود پروژه
                        </button>
                        
                        <button onclick="viewCodeOnline(<?php echo (int)$project['id']; ?>)" class="btn-modern w-full bg-gradient-to-r from-blue-600 to-purple-600 text-white py-3 rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                            <i class="fas fa-code ml-2"></i>
                            مشاهده کد آنلاین
                        </button>
                        
                        <button onclick="shareProject()" class="btn-modern w-full border-2 border-gray-300 text-gray-700 py-3 rounded-xl font-semibold hover:border-blue-500 hover:text-blue-600 transition-all duration-300">
                            <i class="fas fa-share-alt ml-2"></i>
                            اشتراک‌گذاری
                        </button>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="grid grid-cols-2 gap-3">
                        <button class="btn-modern border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:border-red-500 hover:text-red-600 transition-all duration-300">
                            <i class="fas fa-heart ml-1"></i>
                            علاقه‌مندی
                        </button>
                        <button class="btn-modern border border-gray-300 text-gray-700 py-2 rounded-lg font-medium hover:border-yellow-500 hover:text-yellow-600 transition-all duration-300">
                            <i class="fas fa-bookmark ml-1"></i>
                            نشان‌کردن
                        </button>
                    </div>
                </div>

                <!-- Stats Card -->
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">آمار پروژه</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-download text-blue-500 ml-2"></i>
                                <span class="text-gray-700">دانلود</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo number_format((int)($project['downloads'] ?? 0)); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-eye text-green-500 ml-2"></i>
                                <span class="text-gray-700">مشاهده</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo number_format((int)($project['views'] ?? 0)); ?></span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <i class="fas fa-star text-yellow-500 ml-2"></i>
                                <span class="text-gray-700">ستاره</span>
                            </div>
                            <span class="font-bold text-gray-900"><?php echo number_format((int)($project['stars'] ?? 0)); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Author Card -->
                <div class="glass-card rounded-2xl p-6 animate-on-scroll">
                    <h3 class="text-lg font-bold text-gray-900 mb-4">توسعه‌دهنده</h3>
                    <div class="flex items-center mb-4">
                        <div class="w-12 h-12 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-xl ml-3">
                            <?php echo strtoupper(substr(($project['author'] ?? 'ن'), 0, 1)); ?>
                        </div>
                        <div class="flex-1">
                            <div class="font-semibold text-gray-900">
                                <?php echo htmlspecialchars($project['author'] ?? 'ناشناس'); ?>
                            </div>
                            <div class="text-sm text-gray-600">توسعه‌دهنده</div>
                        </div>
                    </div>
                    <a href="profile.php?user=<?php echo urlencode($project['author'] ?? ''); ?>" class="btn-modern w-full border-2 border-blue-200 text-blue-600 py-2 rounded-xl font-semibold hover:bg-blue-50 transition-all duration-300">
                        <i class="fas fa-user ml-2"></i>
                        مشاهده پروفایل
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- Image Modal -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 hidden items-center justify-center z-50" onclick="closeImageModal()">
        <div class="relative max-w-4xl max-h-screen p-4" onclick="event.stopPropagation()">
            <button onclick="closeImageModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 text-2xl">
                <i class="fas fa-times"></i>
            </button>
            <img id="modalImage" src="" alt="" class="max-w-full max-h-full object-contain rounded-lg">
        </div>
    </div>

    <?php endif; ?>

    <!-- Scripts -->
    <script>
        // Tab functionality
        document.addEventListener('DOMContentLoaded', function() {
            // CSRF token from meta (fallback to server if missing)
            const csrfMeta = document.querySelector('meta[name="csrf-token"]');
            const CSRF = csrfMeta ? csrfMeta.getAttribute('content') : '';
            const tabButtons = document.querySelectorAll('.tab-button');
            const tabContents = document.querySelectorAll('.tab-content');

            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabName = button.getAttribute('data-tab');
                    
                    // Remove active class from all buttons
                    tabButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Hide all tab contents
                    tabContents.forEach(content => content.classList.add('hidden'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    // Show selected tab content
                    document.getElementById('tab-' + tabName).classList.remove('hidden');
                });
            });

            // Scroll animations
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.animate-on-scroll').forEach(el => {
                observer.observe(el);
            });

            // Load comments
            loadComments();
        });

        // Project functions
        async function downloadProject(projectId) {
            <?php if (!current_user()): ?>
            showLoginPopup();
            return;
            <?php endif; ?>
            
            try {
                const fd = new FormData();
                fd.append('action', 'track_download');
                fd.append('projectId', projectId);
                if (CSRF) fd.append('csrf_token', CSRF);
                await fetch('api/interactions.php', {method: 'POST', body: fd});
            } catch(_) {}
            
            try {
                const res = await fetch('api/assistant.php?action=get_signed_download&id=' + projectId);
                const data = await res.json();
                if (data && data.ok && data.url) {
                    const link = document.createElement('a');
                    link.href = data.url;
                    link.style.display = 'none';
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                    return;
                }
            } catch(_) {}
            
            const link = document.createElement('a');
            link.href = 'api/download.php?id=' + projectId;
            link.style.display = 'none';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function viewCodeOnline(projectId) {
            const viewerUrl = `code-viewer.php?id=${projectId}`;
            window.open(viewerUrl, '_blank', 'width=1200,height=800,scrollbars=yes,resizable=yes');
        }

        async function shareProject() {
            const shareData = {
                title: document.title,
                text: '<?php echo addslashes($project ? ($project['title'] ?? '') : 'پروژه سورس کده'); ?>',
                url: window.location.href
            };
            
            try {
                if (navigator.share) {
                    await navigator.share(shareData);
                } else {
                    await navigator.clipboard.writeText(window.location.href);
                    showNotification('کپی شد', 'لینک پروژه در کلیپ‌بورد کپی شد', 'success');
                }
            } catch (_) {
                // ignore cancel
            }
        }

        function showLoginPopup() {
            const popup = document.createElement('div');
            popup.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50';
            popup.innerHTML = `
                <div class="bg-white rounded-2xl p-8 max-w-md mx-4 shadow-2xl animate-slide-up">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-user-lock text-white text-2xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-3">ورود به حساب کاربری</h3>
                        <p class="text-gray-600 mb-6">برای دانلود پروژه ابتدا باید وارد حساب کاربری خود شوید</p>
                        <div class="flex gap-3">
                            <a href="auth.php" class="flex-1 bg-gradient-to-r from-blue-500 to-purple-600 text-white py-3 px-4 rounded-xl font-semibold hover:shadow-lg transition-all duration-300">
                                <i class="fas fa-sign-in-alt ml-2"></i>
                                ورود / ثبت‌نام
                            </a>
                            <button onclick="closeLoginPopup()" class="px-4 py-3 bg-gray-100 text-gray-700 rounded-xl font-semibold hover:bg-gray-200 transition-colors">
                                بستن
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            popup.onclick = function(e) {
                if (e.target === popup) closeLoginPopup();
            };
            
            document.body.appendChild(popup);
        }

        function closeLoginPopup() {
            const popup = document.querySelector('.fixed.inset-0.bg-black');
            if (popup) popup.remove();
        }

        // Image modal functions
        function openImageModal(imageSrc, index) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modalImage.alt = `پیش‌نمایش ${index + 1}`;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Comments functionality
        async function loadComments() {
            try {
                const url = new URL('api/interactions.php', window.location.origin);
                url.searchParams.set('action', 'get_comments');
                url.searchParams.set('projectId', '<?php echo (int)$id; ?>');
                const res = await fetch(url.toString(), { method: 'GET', credentials: 'same-origin' });
                const data = await res.json();
                const container = document.getElementById('comments');
                
                if (!data.ok || !data.comments || data.comments.length === 0) {
                    container.innerHTML = '<div class="text-center py-8 text-gray-500">هنوز نظری ثبت نشده است.</div>';
                    return;
                }
                
                container.innerHTML = data.comments.map(c => `
                    <div class="bg-white rounded-xl p-4 border border-gray-200 hover:shadow-md transition-shadow duration-300">
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="w-10 h-10 bg-gradient-to-r from-blue-600 to-purple-600 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                ${escapeHtml((c.userName || 'کاربر').charAt(0).toUpperCase())}
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-gray-900">${escapeHtml(c.userName || 'کاربر')}</div>
                                    <div class="text-xs text-gray-500">${new Date(c.createdAt).toLocaleDateString('fa-IR')}</div>
                                </div>
                                <div class="text-gray-700 leading-relaxed">${escapeHtml(c.content)}</div>
                            </div>
                        </div>
                    </div>
                `).join('');
            } catch (e) {
                document.getElementById('comments').innerHTML = '<div class="text-center py-8 text-red-500">خطا در بارگذاری نظرات</div>';
            }
        }

        <?php if (current_user()): ?>
        document.getElementById('commentForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const content = document.getElementById('commentInput').value.trim();
            if (!content) return;
            
            const fd = new FormData();
            fd.append('action', 'add_comment');
            fd.append('projectId', '<?php echo (int)$id; ?>');
            fd.append('content', content);
            if (CSRF) fd.append('csrf_token', CSRF);
            
            try {
                const res = await fetch('api/interactions.php', {method: 'POST', body: fd});
                const data = await res.json();
                if (data.ok) {
                    document.getElementById('commentInput').value = '';
                    loadComments();
                    showNotification('ثبت شد', 'نظر شما با موفقیت ثبت شد', 'success');
                }
            } catch(e) {
                showNotification('خطا', 'خطا در ثبت نظر', 'error');
            }
        });
        <?php endif; ?>

        // Copy password function
        function copyPassword() {
            const passwordText = document.getElementById('passwordText');
            const text = passwordText.textContent;
            
            navigator.clipboard.writeText(text).then(() => {
                showNotification('کپی شد!', 'رمز عبور با موفقیت کپی شد', 'success');
            }).catch(() => {
                // Fallback for older browsers
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                document.execCommand('copy');
                document.body.removeChild(textArea);
                showNotification('کپی شد!', 'رمز عبور با موفقیت کپی شد', 'success');
            });
        }

        // Utility functions
        function escapeHtml(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return text.replace(/[&<>"']/g, function(m) { return map[m]; });
        }

        function showNotification(title, message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-full max-w-sm`;
            
            const colors = {
                error: 'bg-red-500 text-white',
                success: 'bg-green-500 text-white',
                info: 'bg-blue-500 text-white'
            };
            
            const icons = {
                error: 'fas fa-exclamation-triangle',
                success: 'fas fa-check-circle',
                info: 'fas fa-info-circle'
            };
            
            notification.className += ' ' + colors[type];
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="${icons[type]} ml-2"></i>
                    <div>
                        <div class="font-bold">${title}</div>
                        <div class="text-sm opacity-90">${message}</div>
                    </div>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.remove('translate-x-full');
            }, 100);
            
            setTimeout(() => {
                notification.classList.add('translate-x-full');
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 3000);
        }

        // Keyboard support
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeImageModal();
                closeLoginPopup();
            }
        });
    </script>
</body>
</html>