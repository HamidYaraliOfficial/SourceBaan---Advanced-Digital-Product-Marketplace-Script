<?php
/**
 * SEO Helper Functions for SourceBan
 * Enhanced SEO functionality and utilities
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/utils.php';

// Generate breadcrumb schema
function generate_breadcrumb_schema(array $breadcrumbs): string
{
    if (empty($breadcrumbs)) {
        return '';
    }
    
    $items = [];
    foreach ($breadcrumbs as $index => $breadcrumb) {
        $items[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => $breadcrumb['name'],
            'item' => $breadcrumb['url']
        ];
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => $items
    ];
    
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Generate FAQ schema for projects
function generate_faq_schema(): string
{
    $faqs = [
        [
            'question' => 'سورس کدهای سایت رایگان هستند؟',
            'answer' => 'بله، تمامی سورس کدهای موجود در سورس بان کاملاً رایگان هستند و می‌توانید آنها را بدون پرداخت هزینه دانلود کنید.'
        ],
        [
            'question' => 'چگونه می‌توانم پروژه خود را در سایت قرار دهم؟',
            'answer' => 'برای آپلود پروژه، کافی است وارد حساب کاربری خود شوید و از قسمت آپلود پروژه استفاده کنید. پروژه شما پس از بررسی منتشر خواهد شد.'
        ],
        [
            'question' => 'آیا نیاز به ثبت نام برای دانلود دارم؟',
            'answer' => 'بله، برای دانلود پروژه‌ها نیاز به ثبت نام و ورود به سایت دارید. این کار برای امنیت بیشتر و جلوگیری از دانلود خودکار انجام می‌شود.'
        ],
        [
            'question' => 'چه زبان‌های برنامه‌نویسی پشتیبانی می‌شوند؟',
            'answer' => 'سایت ما از انواع زبان‌های برنامه‌نویسی مانند PHP، Python، JavaScript، Java، C#، Go و بسیاری زبان‌های دیگر پشتیبانی می‌کند.'
        ]
    ];
    
    $faqItems = [];
    foreach ($faqs as $faq) {
        $faqItems[] = [
            '@type' => 'Question',
            'name' => $faq['question'],
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => $faq['answer']
            ]
        ];
    }
    
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => $faqItems
    ];
    
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Generate organization schema
function generate_organization_schema(): string
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => 'سورس بان - SourceBan',
        'alternateName' => 'SourceBan',
        'url' => 'https://sourcebaan.ir',
        'logo' => 'https://sourcebaan.ir/logo.png',
        'description' => 'بهترین مرجع دانلود رایگان سورس کدهای متن باز در ایران. دانلود پروژه‌های PHP، Python، JavaScript و سایر زبان‌های برنامه‌نویسی.',
        'foundingDate' => '2023',
        'sameAs' => [
            'https://telegram.me/sourcebaan',
            'https://instagram.com/sourcebaan'
        ],
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'contactType' => 'customer service',
            'availableLanguage' => ['Persian', 'English']
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Iran'
        ],
        'knowsAbout' => [
            'برنامه نویسی',
            'سورس کد',
            'PHP',
            'Python',
            'JavaScript',
            'پروژه های متن باز',
            'طراحی وب سایت',
            'ربات تلگرام'
        ]
    ];
    
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Generate article schema for projects
function generate_project_article_schema(array $project): string
{
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => 'TechArticle',
        'headline' => $project['title'],
        'description' => $project['description'] ?? '',
        'author' => [
            '@type' => 'Person',
            'name' => $project['author'] ?? 'ناشناس'
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => 'سورس بان - SourceBan',
            'logo' => [
                '@type' => 'ImageObject',
                'url' => 'https://sourcebaan.ir/logo.png'
            ]
        ],
        'datePublished' => $project['createdAt'] ?? date('c'),
        'dateModified' => $project['updatedAt'] ?? $project['createdAt'] ?? date('c'),
        'programmingLanguage' => $project['language'] ?? '',
        'genre' => 'software development',
        'keywords' => implode(', ', [
            $project['title'],
            $project['language'] ?? '',
            'سورس کد',
            'پروژه',
            'دانلود رایگان',
            'open source'
        ]),
        'url' => 'https://sourcebaan.ir' . build_project_pretty_path((int)$project['id'], $project['title']),
        'mainEntityOfPage' => [
            '@type' => 'WebPage',
            '@id' => 'https://sourcebaan.ir' . build_project_pretty_path((int)$project['id'], $project['title'])
        ]
    ];
    
    // Add images if available
    if (!empty($project['previewImages'])) {
        $images = is_array($project['previewImages']) ? $project['previewImages'] : json_decode($project['previewImages'], true) ?? [];
        if (!empty($images)) {
            $schema['image'] = $images;
        }
    }
    
    return json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

// Generate related projects suggestions based on language and keywords
function get_related_projects(array $currentProject, int $limit = 6): array
{
    require_once __DIR__ . '/db.php';
    
    $projects = JsonDB::read('submissions');
    $related = [];
    $currentId = (int)$currentProject['id'];
    $currentLanguage = strtolower($currentProject['language'] ?? '');
    $currentTitle = strtolower($currentProject['title'] ?? '');
    
    foreach ($projects as $project) {
        if ((int)$project['id'] === $currentId || ($project['status'] ?? 'pending') !== 'approved') {
            continue;
        }
        
        $score = 0;
        $projectLanguage = strtolower($project['language'] ?? '');
        $projectTitle = strtolower($project['title'] ?? '');
        
        // Same language gets high score
        if ($projectLanguage === $currentLanguage) {
            $score += 10;
        }
        
        // Similar keywords in title
        $currentWords = explode(' ', $currentTitle);
        $projectWords = explode(' ', $projectTitle);
        
        foreach ($currentWords as $word) {
            if (strlen($word) > 3 && strpos($projectTitle, $word) !== false) {
                $score += 2;
            }
        }
        
        if ($score > 0) {
            $related[] = [
                'project' => $project,
                'score' => $score
            ];
        }
    }
    
    // Sort by score and limit
    usort($related, function($a, $b) {
        return $b['score'] - $a['score'];
    });
    
    return array_slice(array_column($related, 'project'), 0, $limit);
}

// Generate meta description based on project data
function generate_meta_description(array $project, int $maxLength = 155): string
{
    $description = $project['description'] ?? '';
    $title = $project['title'] ?? '';
    $language = $project['language'] ?? '';
    
    if (empty($description)) {
        $description = "دانلود رایگان پروژه {$title}";
        if ($language) {
            $description .= " - سورس کد {$language}";
        }
        $description .= " | سورس بان";
    } else {
        $description = "دانلود رایگان {$title} - " . $description;
        if (strlen($description) > $maxLength - 15) {
            $description = substr($description, 0, $maxLength - 15);
            $description = rtrim($description, ' .,!?') . '...';
        }
        $description .= " | سورس بان";
    }
    
    return strlen($description) > $maxLength ? substr($description, 0, $maxLength - 3) . '...' : $description;
}

// Generate keywords based on project data
function generate_keywords(array $project): array
{
    $keywords = [];
    
    // Add project title variations
    $title = $project['title'] ?? '';
    if ($title) {
        $keywords[] = $title;
        $keywords[] = "دانلود {$title}";
        $keywords[] = "سورس {$title}";
    }
    
    // Add language keywords
    $language = $project['language'] ?? '';
    if ($language) {
        $keywords[] = "پروژه {$language}";
        $keywords[] = "سورس {$language}";
        $keywords[] = "اسکریپت {$language}";
        $keywords[] = "کد {$language}";
    }
    
    // Add general keywords
    $keywords = array_merge($keywords, [
        'سورس کد رایگان',
        'پروژه آماده',
        'دانلود رایگان',
        'open source',
        'source code',
        'برنامه نویسی',
        'programming',
        'sourcebaan',
        'سورس بان'
    ]);
    
    return array_unique(array_filter($keywords));
}

// Check if current page should be indexed
function should_index_page(): bool
{
    // Don't index error pages
    if (http_response_code() >= 400) {
        return false;
    }
    
    // Don't index private/admin pages
    $currentPath = $_SERVER['REQUEST_URI'] ?? '';
    $noIndexPaths = [
        '/admin',
        '/account.php',
        '/edit-project.php',
        '/api/',
        '/data/'
    ];
    
    foreach ($noIndexPaths as $path) {
        if (strpos($currentPath, $path) !== false) {
            return false;
        }
    }
    
    return true;
}

// Generate robots meta tag
function generate_robots_meta(): string
{
    if (!should_index_page()) {
        return 'noindex, nofollow';
    }
    
    return 'index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1';
}
