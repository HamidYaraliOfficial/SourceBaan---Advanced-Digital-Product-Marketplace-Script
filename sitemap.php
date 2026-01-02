<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/db.php';

// Output XML sitemap for all approved projects and products
header('Content-Type: application/xml; charset=utf-8');

$host = 'https://sourcebaan.ir';

function xml_escape(string $s): string {
    return htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8');
}

function format_lastmod(?string $iso): string {
    if (!$iso) return date('Y-m-d');
    $t = strtotime($iso);
    return $t ? date('Y-m-d', $t) : date('Y-m-d');
}

$urls = [];

// Static important pages
$urls[] = [ 'loc' => $host . '/', 'changefreq' => 'daily', 'priority' => '1.0', 'lastmod' => date('Y-m-d') ];
$urls[] = [ 'loc' => $host . '/sources.php', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => date('Y-m-d') ];
$urls[] = [ 'loc' => $host . '/shop.php', 'changefreq' => 'daily', 'priority' => '0.9', 'lastmod' => date('Y-m-d') ];

// Language filter pages (common languages)
$languages = ['PHP','Python','JavaScript','Java','C%23','C%2B%2B','Go','Rust','TypeScript'];
foreach ($languages as $lang) {
    $urls[] = [
        'loc' => $host . '/sources.php?filter=' . $lang,
        'changefreq' => 'daily',
        'priority' => '0.7',
        'lastmod' => date('Y-m-d')
    ];
}

// Shop categories (align with product categories used)
$shopCategories = ['php','python','javascript','react','vue','laravel','wordpress','mobile','desktop','telegram_bot','website','other'];
foreach ($shopCategories as $cat) {
    $urls[] = [
        'loc' => $host . '/shop.php?category=' . $cat,
        'changefreq' => 'daily',
        'priority' => '0.7',
        'lastmod' => date('Y-m-d')
    ];
}

// Projects (submissions approved)
$projects = JsonDB::read('submissions');
foreach ($projects as $p) {
    if (($p['status'] ?? 'pending') !== 'approved') continue;
    $id = (int)($p['id'] ?? 0);
    if ($id <= 0) continue;
    $title = (string)($p['title'] ?? '');
    $path = build_project_pretty_path($id, $title);
    $last = (string)($p['updatedAt'] ?? ($p['createdAt'] ?? ''));
    $urls[] = [
        'loc' => $host . $path,
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => format_lastmod($last)
    ];
}

// Products (shop_products approved)
$products = JsonDB::read('shop_products');
foreach ($products as $prod) {
    if (($prod['status'] ?? 'pending') !== 'approved') continue;
    $id = (int)($prod['id'] ?? 0);
    if ($id <= 0) continue;
    $title = (string)($prod['title'] ?? '');
    $path = build_product_pretty_path($id, $title);
    $last = (string)($prod['updated_at'] ?? ($prod['created_at'] ?? ''));
    $urls[] = [
        'loc' => $host . $path,
        'changefreq' => 'weekly',
        'priority' => '0.8',
        'lastmod' => format_lastmod($last)
    ];
}

// Persian keyword search pages (comprehensive high-intent queries)
$faKeywords = [
    // Instagram related
    'اینستاگرام دانلودر',
    'دانلودر اینستاگرام',
    'ربات اینستاگرام',
    'سورس اینستاگرام',
    'دانلود عکس اینستاگرام',
    'دانلود ویدیو اینستاگرام',
    'اینستاگرام بات',
    'Instagram downloader',
    'اینستا دانلودر',
    
    // Telegram bots
    'دانلود ربات تلگرام',
    'سورس ربات تلگرام',
    'ربات تلگرام',
    'تلگرام بات',
    'telegram bot',
    'ربات تلگرام رایگان',
    'سورس بات تلگرام',
    'ساخت ربات تلگرام',
    'آموزش ربات تلگرام',
    
    // PHP projects
    'دانلود سورس PHP',
    'پروژه PHP',
    'سورس کد PHP',
    'اسکریپت PHP',
    'برنامه PHP',
    'PHP source code',
    'کد آماده PHP',
    'پروژه آماده PHP',
    
    // Python projects  
    'دانلود سورس پایتون',
    'پروژه پایتون',
    'سورس کد پایتون',
    'اسکریپت پایتون',
    'برنامه پایتون',
    'Python source code',
    'کد آماده پایتون',
    'پروژه آماده پایتون',
    
    // JavaScript projects
    'دانلود سورس جاوااسکریپت',
    'پروژه جاوااسکریپت',
    'سورس کد جاوااسکریپت',
    'اسکریپت جاوااسکریپت',
    'JavaScript source code',
    'کد آماده جاوااسکریپت',
    'پروژه آماده جاوااسکریپت',
    
    // React & Vue
    'پروژه React',
    'سورس React',
    'React source code',
    'پروژه Vue',
    'سورس Vue',
    'Vue source code',
    'React آماده',
    'Vue آماده',
    
    // WordPress
    'دانلود سورس وردپرس',
    'قالب وردپرس',
    'افزونه وردپرس',
    'WordPress theme',
    'WordPress plugin',
    'سورس وردپرس',
    'پوسته وردپرس',
    
    // E-commerce
    'قالب فروشگاهی',
    'اسکریپت فروشگاهی',
    'سایت فروشگاهی',
    'فروشگاه آنلاین',
    'e-commerce script',
    'فروشگاه اینترنتی',
    'سورس فروشگاه',
    'سبد خرید',
    
    // General programming
    'دانلود اسکریپت رایگان',
    'دانلود سورس رایگان',
    'سورس کد رایگان',
    'پروژه برنامه نویسی',
    'source code download',
    'free source code',
    'open source project',
    'کد آماده',
    'پروژه آماده',
    
    // Laravel
    'پروژه Laravel',
    'سورس Laravel',
    'Laravel source code',
    'اسکریپت Laravel',
    'Laravel آماده',
    
    // Mobile Development
    'اپلیکیشن موبایل',
    'سورس اندروید',
    'Android source code',
    'اپ اندروید',
    'mobile app source',
    'سورس اپلیکیشن',
    
    // Web Development
    'طراحی وب سایت',
    'سورس وب سایت',
    'website source code',
    'قالب HTML',
    'CSS template',
    'وب سایت آماده',
    
    // Backend Development  
    'API سورس',
    'REST API',
    'backend source',
    'سرور سایت',
    'database script',
    'پایگاه داده',
    
    // CMS & Frameworks
    'سیستم مدیریت محتوا',
    'CMS script',
    'فریمورک PHP',
    'PHP framework',
    'Django project',
    'Node.js source',
    
    // Automation & Bots
    'ربات خودکار',
    'automation script',
    'web scraping',
    'data mining',
    'crawler source',
    'ربات جمع آوری',
    
    // Games & Entertainment
    'بازی برنامه نویسی',
    'game source code',
    'سورس بازی',
    'پروژه بازی',
    'HTML5 game',
    
    // AI & Machine Learning
    'هوش مصنوعی',
    'machine learning',
    'AI source code',
    'یادگیری ماشین',
    'الگوریتم هوش مصنوعی'
];
foreach ($faKeywords as $kw) {
    $urls[] = [
        'loc' => $host . '/sources.php?search=' . rawurlencode($kw),
        'changefreq' => 'daily',
        'priority' => '0.6',
        'lastmod' => date('Y-m-d')
    ];
}

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
<?php foreach ($urls as $u): ?>
  <url>
    <loc><?= xml_escape($u['loc']) ?></loc>
    <lastmod><?= xml_escape($u['lastmod']) ?></lastmod>
    <changefreq><?= xml_escape($u['changefreq']) ?></changefreq>
    <priority><?= xml_escape($u['priority']) ?></priority>
  </url>
<?php endforeach; ?>
</urlset>


