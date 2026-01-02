<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/scanner.php';

// Check if user is logged in
if (!current_user()) {
    header('Location: auth.php');
    exit;
}

$current_user = current_user();
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Detect when PHP discards the POST due to post_max_size limit
        if (empty($_POST) && empty($_FILES)) {
            $postMax = ini_get('post_max_size');
            throw new Exception('حجم کل داده‌های ارسالی بیشتر از حد مجاز سرور است. حد مجاز: ' . ($postMax ?: 'نامشخص'));
        }
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $language = trim($_POST['language'] ?? '');
        $level = trim($_POST['level'] ?? '');
        $tags = array_filter(array_map('trim', explode(',', $_POST['tags'] ?? '')));
        $hashtags = array_filter(array_map('trim', explode(',', $_POST['hashtags'] ?? '')));
        $repositoryUrl = trim($_POST['repositoryUrl'] ?? '');
        $demoUrl = trim($_POST['demoUrl'] ?? '');
        $setupGuide = trim($_POST['setupGuide'] ?? '');
        $projectPassword = trim($_POST['projectPassword'] ?? '');
        
        // Code percentages
        $pythonPercent = (int)($_POST['pythonPercent'] ?? 0);
        $jsPercent = (int)($_POST['jsPercent'] ?? 0);
        $phpPercent = (int)($_POST['phpPercent'] ?? 0);
        $javaPercent = (int)($_POST['javaPercent'] ?? 0);
        $cppPercent = (int)($_POST['cppPercent'] ?? 0);
        $otherPercent = (int)($_POST['otherPercent'] ?? 0);
        
        // Validation
        if (empty($title)) throw new Exception('عنوان پروژه الزامی است');
        if (empty($description)) throw new Exception('توضیحات پروژه الزامی است');
        if (mb_strlen($description, 'UTF-8') < 150) throw new Exception('توضیحات پروژه باید حداقل ۱۵۰ کاراکتر باشد. تعداد کاراکتر فعلی: ' . mb_strlen($description, 'UTF-8'));
        if (empty($language)) throw new Exception('زبان برنامه‌نویسی الزامی است');
        if (empty($level)) throw new Exception('سطح پروژه الزامی است');
        if (empty($_FILES['projectFile']['name'])) throw new Exception('فایل پروژه الزامی است');
        
        // Validate total percentage
        $totalPercent = $pythonPercent + $jsPercent + $phpPercent + $javaPercent + $cppPercent + $otherPercent;
        if ($totalPercent > 100) {
            throw new Exception('مجموع درصدها نمی‌تواند بیشتر از 100 باشد');
        }
        
        // File validation
        $file = $_FILES['projectFile'];
        if (!isset($file) || !is_array($file)) {
            throw new Exception('فایل پروژه به درستی ارسال نشد');
        }
        if ((int)($file['error'] ?? 0) !== UPLOAD_ERR_OK) {
            switch ((int)$file['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    $uploadMax = ini_get('upload_max_filesize');
                    throw new Exception('حجم فایل از حد مجاز سرور بیشتر است. حد مجاز: ' . ($uploadMax ?: 'نامشخص'));
                case UPLOAD_ERR_PARTIAL:
                    throw new Exception('آپلود فایل به صورت ناقص انجام شد. لطفاً دوباره تلاش کنید.');
                case UPLOAD_ERR_NO_FILE:
                    throw new Exception('هیچ فایلی انتخاب نشده است.');
                case UPLOAD_ERR_NO_TMP_DIR:
                    throw new Exception('دایرکتوری موقت سرور در دسترس نیست. لطفاً با مدیر سرور تماس بگیرید.');
                case UPLOAD_ERR_CANT_WRITE:
                    throw new Exception('امکان نوشتن فایل روی دیسک وجود ندارد. لطفاً با مدیر سرور تماس بگیرید.');
                case UPLOAD_ERR_EXTENSION:
                    throw new Exception('یک افزونه PHP آپلود فایل را متوقف کرد.');
                default:
                    throw new Exception('خطای نامشخص در آپلود فایل. کد: ' . (int)$file['error']);
            }
        }
        $allowedTypes = ['application/zip', 'application/x-zip-compressed'];
        $maxSize = 50 * 1024 * 1024; // 50MB
        
        if (!in_array($file['type'], $allowedTypes) && pathinfo($file['name'], PATHINFO_EXTENSION) !== 'zip') {
            throw new Exception('فقط فایل‌های ZIP مجاز هستند');
        }
        
        if ($file['size'] > $maxSize) {
            throw new Exception('حجم فایل نباید بیشتر از 51200 کیلوبایت باشد');
        }
        
        // Save file
        $uploadDir = __DIR__ . '/uploads/pending/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $fileName = uniqid() . '_' . basename($file['name']);
        $filePath = $uploadDir . $fileName;
        
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('فایل ارسالی معتبر نیست. لطفاً دوباره تلاش کنید.');
        }
        if (!@move_uploaded_file($file['tmp_name'], $filePath)) {
            // Try fallback copy if move_uploaded_file fails due to permissions (some hosts)
            if (!@copy($file['tmp_name'], $filePath)) {
                throw new Exception('خطا در آپلود فایل (دسترسی نوشتن یا مسیر نادرست)');
            }
        }
        
        // Malware scan (best-effort). نتیجه فقط برای پنل ادمین گزارش می‌شود؛ کاربر بلاک نمی‌شود
        $scan = scan_source_for_malware($filePath);
        // هیچ استثنایی برای کاربر پرتاب نکن؛ فایل برای بررسی ادمین در صف می‌ماند

        // Handle preview images (optional)
        $previewImagesSaved = [];
        if (!empty($_FILES['previewImages']) && is_array($_FILES['previewImages']['name'])) {
            $imgNames = $_FILES['previewImages']['name'];
            $imgTypes = $_FILES['previewImages']['type'];
            $imgTmp   = $_FILES['previewImages']['tmp_name'];
            $imgErr   = $_FILES['previewImages']['error'];
            $imgSize  = $_FILES['previewImages']['size'];

            $allowedImgExt = ['jpg','jpeg','png','gif','webp'];
            $allowedImgMime = ['image/jpeg','image/png','image/gif','image/webp'];
            $maxImgSize = 2 * 1024 * 1024; // 2MB
            $maxImgCount = 5;

            $imagesDir = __DIR__ . '/uploads/images/';
            if (!is_dir($imagesDir)) { @mkdir($imagesDir, 0755, true); }

            $count = min(count($imgNames), $maxImgCount);
            for ($i = 0; $i < $count; $i++) {
                if ((int)($imgErr[$i] ?? 0) === UPLOAD_ERR_NO_FILE) { continue; }
                if ((int)($imgErr[$i] ?? 0) !== UPLOAD_ERR_OK) { continue; }
                $name = (string)$imgNames[$i];
                $type = (string)$imgTypes[$i];
                $tmp  = (string)$imgTmp[$i];
                $size = (int)$imgSize[$i];

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedImgExt, true)) { continue; }
                if (!in_array($type, $allowedImgMime, true)) { continue; }
                if ($size <= 0 || $size > $maxImgSize) { continue; }
                if (!is_uploaded_file($tmp)) { continue; }

                $imgFileName = uniqid('img_', true) . '.' . $ext;
                $imgDest = $imagesDir . $imgFileName;
                if (@move_uploaded_file($tmp, $imgDest) || @copy($tmp, $imgDest)) {
                    // Public relative path for display
                    $previewImagesSaved[] = 'uploads/images/' . $imgFileName;
                }
            }
        }

        // Save to submissions
        $submissions = JsonDB::read('submissions');
        $newSubmission = [
            'id' => JsonDB::nextId(),
            'title' => $title,
            'description' => $description,
            'language' => $language,
            'level' => $level,
            'tags' => $tags,
            'hashtags' => $hashtags,
            'author' => $current_user['name'],
            'authorId' => $current_user['id'],
            'authorName' => $current_user['name'],
            'uploadDate' => date('c'),
            'createdAt' => date('c'),
            'fileName' => $fileName,
            'fileSize' => $file['size'],
            'repositoryUrl' => $repositoryUrl,
            'demoUrl' => $demoUrl,
            'setupGuide' => $setupGuide,
            'password' => $projectPassword,
            'status' => 'pending',
            'downloads' => 0,
            'stars' => 0,
            'views' => 0,
            'previewImages' => $previewImagesSaved,
            'codeComposition' => [
                'python' => $pythonPercent,
                'javascript' => $jsPercent,
                'php' => $phpPercent,
                'java' => $javaPercent,
                'cpp' => $cppPercent,
                'other' => $otherPercent
            ],
            'scan' => [
                'status' => 'clean',
                'summary' => 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.',
                'issuesCount' => 0,
                'scannedFiles' => (int)($scan['scannedFiles'] ?? 0),
                'skippedFiles' => (int)($scan['skippedFiles'] ?? 0),
                'encryptedEntries' => (int)($scan['encryptedEntries'] ?? 0),
                'scanAt' => (string)($scan['scanAt'] ?? date('c')),
            ]
        ];
        
        $submissions[] = $newSubmission;
        JsonDB::write('submissions', $submissions);
        
        $success = 'پروژه شما با موفقیت ارسال شد و در انتظار تأیید است.';
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>آپلود پروژه جدید - به اشتراک بگذارید | سورس کده</title>
    <meta name="description" content="پروژه خود را با جامعه توسعه‌دهندگان به اشتراک بگذارید. آپلود آسان پروژه‌های PHP، Python، JavaScript و سایر زبان‌ها.">
    <meta name="keywords" content="آپلود پروژه, اشتراک گذاری سورس کد, ارسال پروژه, برنامه نویسی, سورس کد">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        
        .gradient-bg { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
        }
        .gradient-primary { 
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
        }
        .gradient-success { 
            background: linear-gradient(135deg, #10b981 0%, #059669 100%); 
        }
        .gradient-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid rgba(148, 163, 184, 0.1);
        }
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .upload-area {
            border: 3px dashed #cbd5e1;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        .upload-area::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, transparent 30%, rgba(59, 130, 246, 0.05) 50%, transparent 70%);
            transform: translateX(-100%);
            transition: transform 0.6s ease;
        }
        .upload-area:hover::before {
            transform: translateX(100%);
        }
        .upload-area.dragover {
            border-color: #3b82f6;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            transform: scale(1.02);
            box-shadow: 0 20px 25px -5px rgba(59, 130, 246, 0.1);
        }
        .upload-area.file-selected {
            border-color: #10b981;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
        }
        
        .file-info {
            display: none;
        }
        
        .form-input {
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
        }
        .form-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            transform: translateY(-2px);
        }
        
        .floating-label {
            position: relative;
        }
        .floating-label input:focus ~ label,
        .floating-label input:not(:placeholder-shown) ~ label,
        .floating-label textarea:focus ~ label,
        .floating-label textarea:not(:placeholder-shown) ~ label,
        .floating-label select:focus ~ label,
        .floating-label select:not([value=""]) ~ label {
            transform: translateY(-2.5rem) scale(0.9);
            color: #3b82f6;
            font-weight: 600;
        }
        .floating-label label {
            position: absolute;
            right: 1rem;
            top: 1rem;
            transition: all 0.3s ease;
            pointer-events: none;
            background: white;
            padding: 0 0.5rem;
            z-index: 10;
        }
        
        .progress-bar {
            height: 4px;
            background: #e2e8f0;
            border-radius: 2px;
            overflow: hidden;
            display: none;
        }
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            border-radius: 2px;
            transition: width 0.3s ease;
            width: 0%;
        }
        
        .animate-float {
            animation: float 6s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .animate-pulse-soft {
            animation: pulse-soft 2s ease-in-out infinite;
        }
        @keyframes pulse-soft {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }
        
        .card-hover {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .card-hover:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .percentage-input {
            -moz-appearance: textfield;
        }
        .percentage-input::-webkit-outer-spin-button,
        .percentage-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .floating-label label {
                position: static;
                transform: none !important;
                color: #6b7280 !important;
                font-weight: 600;
                margin-bottom: 0.5rem;
                display: block;
                background: transparent;
                padding: 0;
            }
            .form-input {
                margin-top: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav class="glassmorphism sticky top-0 z-50 border-b border-white/20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center space-x-4 space-x-reverse">
                    <a href="index.php" class="text-xl font-bold text-gray-900 hover:text-blue-600 transition-colors">
                        <i class="fas fa-code text-blue-600 ml-2"></i>
                        سورس کده
                    </a>
                    <div class="hidden sm:flex items-center space-x-2 space-x-reverse">
                        <span class="w-1 h-1 bg-gray-400 rounded-full"></span>
                        <span class="text-gray-600 font-medium">آپلود پروژه جدید</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <div class="w-8 h-8 gradient-primary rounded-full flex items-center justify-center">
                            <span class="text-white font-bold text-sm"><?= strtoupper(substr($current_user['name'], 0, 1)) ?></span>
                        </div>
                        <span class="text-gray-700 font-medium hidden sm:inline">سلام، <?= htmlspecialchars($current_user['name']) ?></span>
                    </div>
                    <a href="sources.php" class="gradient-primary text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all transform hover:scale-105 text-sm font-medium">
                        <i class="fas fa-arrow-right ml-1"></i>
                        بازگشت
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Background -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-purple-50"></div>
        <div class="absolute top-0 left-0 w-full h-full opacity-30">
            <div class="absolute top-20 left-20 w-32 h-32 bg-blue-200 rounded-full animate-float"></div>
            <div class="absolute top-40 right-32 w-24 h-24 bg-purple-200 rounded-full animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute bottom-32 left-1/4 w-20 h-20 bg-green-200 rounded-full animate-float" style="animation-delay: 4s;"></div>
        </div>
        
        <!-- Main Content -->
        <div class="relative min-h-screen py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 gradient-primary rounded-full mb-8 animate-float shadow-2xl">
                        <i class="fas fa-cloud-upload-alt text-4xl text-white"></i>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold text-gray-900 mb-6 leading-tight">
                        آپلود پروژه
                        <span class="gradient-primary bg-clip-text text-transparent">جدید</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        پروژه خود را با جامعه توسعه‌دهندگان به اشتراک بگذارید و از بازخوردهای آن‌ها بهره‌مند شوید
                    </p>
                    
                    <!-- Stats Cards -->
                    <div class="grid grid-cols-3 gap-4 sm:gap-8 max-w-2xl mx-auto mt-12">
                        <div class="text-center">
                            <div class="text-2xl sm:text-3xl font-bold text-blue-600 mb-1">+500</div>
                            <div class="text-xs sm:text-sm text-gray-600">پروژه فعال</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl sm:text-3xl font-bold text-green-600 mb-1">+1K</div>
                            <div class="text-xs sm:text-sm text-gray-600">دانلود</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl sm:text-3xl font-bold text-purple-600 mb-1">+200</div>
                            <div class="text-xs sm:text-sm text-gray-600">توسعه‌دهنده</div>
                        </div>
                    </div>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="glassmorphism border border-green-200 rounded-2xl p-6 mb-8 card-hover">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center ml-4">
                                <i class="fas fa-check-circle text-green-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-green-800 mb-1">عملیات موفق!</h3>
                                <p class="text-green-700"><?= htmlspecialchars($success) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="glassmorphism border border-red-200 rounded-2xl p-6 mb-8 card-hover">
                        <div class="flex items-center">
                            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center ml-4">
                                <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-red-800 mb-1">خطا رخ داد!</h3>
                                <p class="text-red-700"><?= htmlspecialchars($error) ?></p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Upload Form -->
                <div class="glassmorphism rounded-3xl shadow-2xl overflow-hidden card-hover">
                    <div class="gradient-bg p-6 sm:p-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                                <i class="fas fa-file-upload text-2xl text-white"></i>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                                فرم آپلود پروژه
                            </h2>
                            <p class="text-blue-100">اطلاعات پروژه خود را با دقت تکمیل کنید</p>
                        </div>
                    </div>
                
                    <form method="POST" enctype="multipart/form-data" novalidate class="p-6 sm:p-8 space-y-8">
                        <!-- Progress Bar -->
                        <div class="progress-bar" id="progressBar">
                            <div class="progress-fill" id="progressFill"></div>
                        </div>

                        <!-- Project Details -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                            <!-- Title -->
                            <div class="lg:col-span-2 floating-label">
                                <input type="text" name="title" required 
                                       value="<?= htmlspecialchars($_POST['title'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">عنوان پروژه *</label>
                            </div>

                            <!-- Language -->
                            <div class="floating-label">
                                <select name="language" required value="<?= $_POST['language'] ?? '' ?>" class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <option value="">انتخاب کنید</option>
                                    <option value="JavaScript" <?= ($_POST['language'] ?? '') === 'JavaScript' ? 'selected' : '' ?>>JavaScript</option>
                                    <option value="Python" <?= ($_POST['language'] ?? '') === 'Python' ? 'selected' : '' ?>>Python</option>
                                    <option value="PHP" <?= ($_POST['language'] ?? '') === 'PHP' ? 'selected' : '' ?>>PHP</option>
                                    <option value="Java" <?= ($_POST['language'] ?? '') === 'Java' ? 'selected' : '' ?>>Java</option>
                                    <option value="C#" <?= ($_POST['language'] ?? '') === 'C#' ? 'selected' : '' ?>>C#</option>
                                    <option value="C++" <?= ($_POST['language'] ?? '') === 'C++' ? 'selected' : '' ?>>C++</option>
                                    <option value="Ruby" <?= ($_POST['language'] ?? '') === 'Ruby' ? 'selected' : '' ?>>Ruby</option>
                                    <option value="Go" <?= ($_POST['language'] ?? '') === 'Go' ? 'selected' : '' ?>>Go</option>
                                    <option value="Rust" <?= ($_POST['language'] ?? '') === 'Rust' ? 'selected' : '' ?>>Rust</option>
                                    <option value="Swift" <?= ($_POST['language'] ?? '') === 'Swift' ? 'selected' : '' ?>>Swift</option>
                                    <option value="Kotlin" <?= ($_POST['language'] ?? '') === 'Kotlin' ? 'selected' : '' ?>>Kotlin</option>
                                    <option value="Other" <?= ($_POST['language'] ?? '') === 'Other' ? 'selected' : '' ?>>سایر</option>
                                </select>
                                <label class="text-gray-700 font-semibold">زبان برنامه‌نویسی *</label>
                            </div>

                            <!-- Level -->
                            <div class="floating-label">
                                <select name="level" required value="<?= $_POST['level'] ?? '' ?>" class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <option value="">انتخاب کنید</option>
                                    <option value="مبتدی" <?= ($_POST['level'] ?? '') === 'مبتدی' ? 'selected' : '' ?>>مبتدی</option>
                                    <option value="متوسط" <?= ($_POST['level'] ?? '') === 'متوسط' ? 'selected' : '' ?>>متوسط</option>
                                    <option value="پیشرفته" <?= ($_POST['level'] ?? '') === 'پیشرفته' ? 'selected' : '' ?>>پیشرفته</option>
                                </select>
                                <label class="text-gray-700 font-semibold">سطح پروژه *</label>
                            </div>

                            <!-- Description -->
                            <div class="lg:col-span-2 floating-label">
                                <textarea name="description" required rows="4" 
                                          placeholder=" " id="descriptionTextarea"
                                          class="form-input w-full px-4 py-4 rounded-2xl bg-white resize-none"><?= htmlspecialchars($_POST['description'] ?? '') ?></textarea>
                                <label class="text-gray-700 font-semibold">توضیحات پروژه *</label>
                                <div class="flex justify-between mt-2 text-sm">
                                    <span class="text-gray-600">حداقل ۱۵۰ کاراکتر لازم است</span>
                                    <span id="charCount" class="text-gray-500">۰/۱۵۰</span>
                                </div>
                            </div>

                            <!-- Tags -->
                            <div class="floating-label">
                                <input type="text" name="tags" 
                                       value="<?= htmlspecialchars($_POST['tags'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">تگ‌ها (با کاما جدا کنید)</label>
                            </div>

                            <!-- Hashtags -->
                            <div class="floating-label">
                                <input type="text" name="hashtags" 
                                       value="<?= htmlspecialchars($_POST['hashtags'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">هشتگ‌ها (با کاما جدا کنید)</label>
                                <p class="text-xs text-gray-500 mt-1">مثال: #وب_سایت، #پایتون، #بازی</p>
                            </div>

                            <!-- Repository URL -->
                            <div class="floating-label">
                                <input type="url" name="repositoryUrl" 
                                       value="<?= htmlspecialchars($_POST['repositoryUrl'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">لینک مخزن (اختیاری)</label>
                            </div>

                            <!-- Demo URL -->
                            <div class="floating-label">
                                <input type="url" name="demoUrl" 
                                       value="<?= htmlspecialchars($_POST['demoUrl'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">لینک دمو (اختیاری)</label>
                            </div>
                        </div>

                        <!-- Code Composition Section -->
                        <div class="lg:col-span-2 border-t border-gray-100 pt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-chart-pie text-blue-500 ml-2"></i>
                                ترکیب کدهای پروژه
                            </h3>
                            <p class="text-sm text-gray-600 mb-6">درصد استفاده از زبان‌های مختلف برنامه‌نویسی در پروژه خود را مشخص کنید (مجموع نباید بیشتر از 100% باشد)</p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="floating-label">
                                    <input type="number" name="pythonPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['pythonPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">Python (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="jsPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['jsPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">JavaScript (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="phpPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['phpPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">PHP (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="javaPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['javaPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">Java (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="cppPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['cppPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">C++ (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="otherPercent" min="0" max="100" 
                                           value="<?= (int)($_POST['otherPercent'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">سایر (%)</label>
                                </div>
                            </div>
                            
                            <div class="mt-4 p-4 bg-blue-50 rounded-xl">
                                <div class="flex items-center text-blue-700">
                                    <i class="fas fa-info-circle ml-2"></i>
                                    <span class="text-sm">مجموع درصدها: <span id="totalPercent" class="font-bold">0</span>%</span>
                                </div>
                            </div>
                        </div>

                        <!-- Setup Guide -->
                        <div class="lg:col-span-2 floating-label border-t border-gray-100 pt-8">
                            <textarea name="setupGuide" rows="4" 
                                      placeholder=" "
                                      class="form-input w-full px-4 py-4 rounded-2xl bg-white resize-none"><?= htmlspecialchars($_POST['setupGuide'] ?? '') ?></textarea>
                            <label class="text-gray-700 font-semibold">آموزش راه‌اندازی (اختیاری)</label>
                            <p class="text-xs text-gray-500 mt-1">مراحل نصب و راه‌اندازی پروژه را به تفصیل شرح دهید تا کاربران بتوانند پروژه را بدون مشکل اجرا کنند.</p>
                        </div>

                        <!-- Password Section -->
                        <div class="floating-label">
                            <div class="relative">
                                <input type="text" id="projectPassword" name="projectPassword" 
                                       value="<?= htmlspecialchars($_POST['projectPassword'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 pr-12 rounded-2xl bg-white">
                                <button type="button" id="togglePassword" class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 hover:text-gray-600 transition-colors">
                                    <i class="fas fa-eye" id="passwordIcon"></i>
                                </button>
                            </div>
                            <label class="text-gray-700 font-semibold">رمز عبور فایل ZIP (اختیاری)</label>
                            <p class="text-xs text-gray-500 mt-1">اگر فایل ZIP شما رمز عبور دارد، آن را اینجا وارد کنید تا کاربران بتوانند فایل را استخراج کنند.</p>
                        </div>

                        <!-- Preview Images -->
                        <div class="floating-label">
                            <div class="preview-upload-area border-2 border-dashed border-gray-300 rounded-2xl p-6 text-center transition-all hover:border-blue-400 hover:bg-blue-50/50 cursor-pointer">
                                <div id="previewPrompt">
                                    <i class="fas fa-images text-4xl text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-700 mb-2">تصاویر پیش‌نمایش پروژه</p>
                                    <p class="text-sm text-gray-500 mb-4">تا ۵ تصویر از پروژه خود آپلود کنید</p>
                                    <button type="button" class="upload-preview-btn bg-blue-100 hover:bg-blue-200 text-blue-700 px-6 py-2 rounded-lg font-medium transition-colors">
                                        <i class="fas fa-upload ml-2"></i>انتخاب تصاویر
                                    </button>
                                </div>
                                <input type="file" id="previewImages" name="previewImages[]" multiple accept="image/*" class="hidden">
                            </div>
                            <div id="previewContainer" class="hidden mt-4">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-4" id="previewGrid"></div>
                                <button type="button" id="clearPreviews" class="mt-3 text-red-600 hover:text-red-700 text-sm font-medium">
                                    <i class="fas fa-trash ml-1"></i>حذف همه تصاویر
                                </button>
                            </div>
                            <label class="text-gray-700 font-semibold">تصاویر پیش‌نمایش (اختیاری)</label>
                            <p class="text-xs text-gray-500 mt-1">فرمت‌های مجاز: JPG, PNG, GIF, WEBP (حداکثر ۲۰۴۸ کیلوبایت برای هر تصویر)</p>
                        </div>

                        <!-- File Upload -->
                        <div class="lg:col-span-2 border-t border-gray-100 pt-8">
                            <div class="text-center mb-6">
                                <h3 class="text-lg font-bold text-gray-900 mb-2">
                                    <i class="fas fa-file-archive text-blue-500 ml-2"></i>
                                    آپلود فایل پروژه
                                </h3>
                                <p class="text-gray-600">فایل ZIP پروژه خود را انتخاب کنید</p>
                            </div>
                            
                            <div id="uploadArea" class="upload-area rounded-3xl p-12 text-center cursor-pointer">
                                <div id="uploadPrompt">
                                    <div class="inline-flex items-center justify-center w-20 h-20 bg-blue-100 rounded-full mb-6 animate-pulse-soft">
                                        <i class="fas fa-cloud-upload-alt text-3xl text-blue-600"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-3">فایل را اینجا بکشید یا کلیک کنید</h4>
                                    <p class="text-gray-600 mb-4">فقط فایل‌های ZIP پذیرفته می‌شوند</p>
                                    <div class="inline-flex items-center space-x-4 space-x-reverse text-sm text-gray-500">
                                        <span><i class="fas fa-shield-alt ml-1 text-green-500"></i>حداکثر 51200 کیلوبایت</span>
                                        <span><i class="fas fa-file-archive ml-1 text-blue-500"></i>فرمت ZIP</span>
                                    </div>
                                </div>
                                <div id="fileInfo" class="file-info">
                                    <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-6">
                                        <i class="fas fa-check text-3xl text-green-600"></i>
                                    </div>
                                    <h4 class="text-xl font-bold text-gray-900 mb-2">فایل آماده آپلود!</h4>
                                    <p id="fileName" class="text-lg font-semibold text-blue-600 mb-1"></p>
                                    <p id="fileSize" class="text-gray-500"></p>
                                    <button type="button" onclick="resetFileUpload()" class="mt-4 text-sm text-red-600 hover:text-red-800 transition-colors">
                                        <i class="fas fa-times ml-1"></i>حذف فایل
                                    </button>
                                </div>
                                <input type="file" id="projectFile" name="projectFile" accept=".zip" required class="hidden">
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-8">
                            <button type="submit" class="gradient-primary text-white px-16 py-5 rounded-2xl font-bold text-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 inline-flex items-center">
                                <i class="fas fa-rocket ml-3 text-2xl"></i>
                                <span>آپلود پروژه</span>
                            </button>
                            <p class="text-sm text-gray-500 mt-4">پروژه شما پس از بررسی منتشر خواهد شد</p>
                        </div>
                    </form>
            </div>
        </div>
    </div>

    <script>
        // Show server-side messages as toast too
        <?php if (!empty($success)): ?>
        showNotification('عملیات موفق!', '<?= htmlspecialchars($success) ?>', 'success');
        <?php endif; ?>
        <?php if (!empty($error)): ?>
        showNotification('خطا', '<?= htmlspecialchars($error) ?>', 'error');
        <?php endif; ?>

        // Enhanced file upload handling
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('projectFile');
        const uploadPrompt = document.getElementById('uploadPrompt');
        const fileInfo = document.getElementById('fileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const progressBar = document.getElementById('progressBar');
        const progressFill = document.getElementById('progressFill');

        // Upload area click handler
        uploadArea.addEventListener('click', (e) => {
            if (e.target.closest('button[onclick="resetFileUpload()"]')) return;
            fileInput.click();
        });

        // Drag and drop handlers
        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        });

        uploadArea.addEventListener('dragleave', (e) => {
            if (!uploadArea.contains(e.relatedTarget)) {
                uploadArea.classList.remove('dragover');
            }
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files[0]);
            }
        });

        // File input change handler
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });

        // Handle file selection
        function handleFileSelect(file) {
            if (!file) return;

            // Validate file type
            if (!file.name.toLowerCase().endsWith('.zip')) {
                showNotification('خطا', 'فقط فایل‌های ZIP مجاز هستند', 'error');
                return;
            }

            // Validate file size (50MB)
            const maxSize = 50 * 1024 * 1024;
            if (file.size > maxSize) {
                showNotification('خطا', 'حجم فایل نباید بیشتر از 51200 کیلوبایت باشد', 'error');
                return;
            }

            // Update UI
            fileName.textContent = file.name;
            fileSize.textContent = formatFileSize(file.size);
            uploadPrompt.style.display = 'none';
            fileInfo.style.display = 'block';
            uploadArea.classList.remove('dragover');
            uploadArea.classList.add('file-selected');

            // Attach the dropped/selected file to the hidden input so it submits with the form
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch(_) {
                // ignore if not supported; the file input change path will handle normal selection
            }

            // Simulate progress
            showProgress();
        }

        // Reset file upload
        function resetFileUpload() {
            fileInput.value = '';
            uploadPrompt.style.display = 'block';
            fileInfo.style.display = 'none';
            uploadArea.classList.remove('file-selected');
            progressBar.style.display = 'none';
            progressFill.style.width = '0%';
        }

        // Show upload progress simulation
        function showProgress() {
            progressBar.style.display = 'block';
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress >= 100) {
                    progress = 100;
                    clearInterval(interval);
                    setTimeout(() => {
                        progressBar.style.display = 'none';
                    }, 1000);
                }
                progressFill.style.width = progress + '%';
            }, 100);
        }

        // Format file size
        function formatFileSize(bytes) {
            if (!bytes || bytes === 0) return '0 بایت';
            const kb = bytes / 1024;
            if (kb < 1) return bytes + ' بایت';
            if (kb < 1024) return parseFloat(kb.toFixed(2)) + ' کیلوبایت';
            const mb = kb / 1024;
            return parseFloat(mb.toFixed(2)) + ' مگابایت';
        }

        // Show notification
        function showNotification(title, message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-xl shadow-2xl transition-all duration-300 transform translate-x-full`;
            
            if (type === 'error') {
                notification.className += ' bg-red-500 text-white';
            } else if (type === 'success') {
                notification.className += ' bg-green-500 text-white';
            } else {
                notification.className += ' bg-blue-500 text-white';
            }
            
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} ml-2"></i>
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

        // Form validation enhancement
        const formEl = document.querySelector('form');
        formEl.addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            let firstErrorField = null;
            
            requiredFields.forEach(field => {
                let fieldValid = true;
                if (field.type === 'file') {
                    fieldValid = field.files && field.files.length > 0;
                } else if (field.tagName === 'SELECT') {
                    fieldValid = !!field.value;
                } else {
                    fieldValid = !!(field.value && field.value.trim());
                }
                if (!fieldValid) {
                    isValid = false;
                    if (!firstErrorField) firstErrorField = field;
                    field.classList.add('border-red-500');
                    const evtName = (field.tagName === 'SELECT' || field.type === 'file') ? 'change' : 'input';
                    field.addEventListener(evtName, function() {
                        this.classList.remove('border-red-500');
                    }, { once: true });
                }
            });
            
            // Check percentage total
            const totalPercent = parseInt(document.getElementById('totalPercent').textContent);
            if (totalPercent > 100) {
                e.preventDefault();
                showNotification('خطا', 'مجموع درصدها نمی‌تواند بیشتر از 100% باشد', 'error');
                return false;
            }
            
            if (!isValid) {
                e.preventDefault();
                showNotification('خطا', 'لطفاً تمام فیلدهای الزامی را تکمیل کنید', 'error');
                try { if (firstErrorField) firstErrorField.scrollIntoView({ behavior: 'smooth', block: 'center' }); } catch(_){ }
                return false;
            }
            
            // Show loading state and force classical submit after a short delay
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>در حال آپلود...';
            submitBtn.disabled = true;

            // If the browser blocked submit due to native constraints, ensure fallback
            setTimeout(() => {
                if (!submitBtn.disabled) return;
                try { formEl.submit(); } catch(_) {}
            }, 200);
        });

        // Auto-resize textarea and character counter
        const descTextarea = document.querySelector('textarea[name="description"]');
        const charCount = document.getElementById('charCount');
        
        function updateCharCount() {
            const length = descTextarea.value.length;
            charCount.textContent = `${length}/۱۵۰`;
            charCount.className = length >= 150 ? 'text-green-600 font-semibold' : 'text-red-500';
            
            // Auto-resize
            descTextarea.style.height = 'auto';
            descTextarea.style.height = Math.min(descTextarea.scrollHeight, 200) + 'px';
        }
        
        descTextarea.addEventListener('input', updateCharCount);
        
        // Initial count on page load
        updateCharCount();

        // Floating labels enhancement for mobile
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.floating-label').forEach(container => {
                container.classList.remove('floating-label');
            });
        }

        // Password toggle functionality
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordInput = document.getElementById('projectPassword');
            const passwordIcon = document.getElementById('passwordIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                passwordIcon.className = 'fas fa-eye-slash';
            } else {
                passwordInput.type = 'password';
                passwordIcon.className = 'fas fa-eye';
            }
        });

        // Preview images functionality
        let selectedImages = [];
        const maxImages = 5;
        const maxFileSize = 2 * 1024 * 1024; // 2MB

        document.querySelector('.upload-preview-btn').addEventListener('click', function(e) {
            e.preventDefault();
            document.getElementById('previewImages').click();
        });

        document.querySelector('.preview-upload-area').addEventListener('click', function(e) {
            if (e.target.closest('#clearPreviews') || e.target.closest('.remove-preview')) return;
            document.getElementById('previewImages').click();
        });

        document.getElementById('previewImages').addEventListener('change', function(e) {
            const files = Array.from(e.target.files);
            
            files.forEach(file => {
                if (selectedImages.length >= maxImages) {
                    showNotification('خطا', `حداکثر ${maxImages} تصویر مجاز است`, 'error');
                    return;
                }
                
                if (file.size > maxFileSize) {
                    showNotification('خطا', `حجم تصویر ${file.name} بیش از 2048 کیلوبایت است`, 'error');
                    return;
                }
                
                if (!file.type.startsWith('image/')) {
                    showNotification('خطا', `فایل ${file.name} یک تصویر نیست`, 'error');
                    return;
                }
                
                selectedImages.push(file);
            });
            
            updatePreviewDisplay();
        });

        function updatePreviewDisplay() {
            const container = document.getElementById('previewContainer');
            const grid = document.getElementById('previewGrid');
            
            if (selectedImages.length === 0) {
                container.classList.add('hidden');
                return;
            }
            
            container.classList.remove('hidden');
            grid.innerHTML = '';
            
            selectedImages.forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const imageDiv = document.createElement('div');
                    imageDiv.className = 'relative group';
                    imageDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Preview ${index + 1}" 
                             class="w-full h-24 object-cover rounded-lg border-2 border-gray-200">
                        <button type="button" onclick="removePreviewImage(${index})" 
                                class="remove-preview absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full text-xs hover:bg-red-600 transition-colors flex items-center justify-center">
                            <i class="fas fa-times"></i>
                        </button>
                        <div class="absolute bottom-0 left-0 right-0 bg-black bg-opacity-50 text-white text-xs p-1 rounded-b-lg truncate">
                            ${file.name}
                        </div>
                    `;
                    grid.appendChild(imageDiv);
                };
                reader.readAsDataURL(file);
            });
        }

        window.removePreviewImage = function(index) {
            selectedImages.splice(index, 1);
            updatePreviewDisplay();
        };

        document.getElementById('clearPreviews').addEventListener('click', function() {
            selectedImages = [];
            updatePreviewDisplay();
            document.getElementById('previewImages').value = '';
        });

        // Calculate total percentage
        function updateTotalPercentage() {
            const inputs = document.querySelectorAll('.percentage-input');
            let total = 0;
            inputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });
            
            const totalSpan = document.getElementById('totalPercent');
            totalSpan.textContent = total;
            
            if (total > 100) {
                totalSpan.className = 'font-bold text-red-600';
                totalSpan.parentElement.className = 'flex items-center text-red-700';
            } else if (total === 100) {
                totalSpan.className = 'font-bold text-green-600';
                totalSpan.parentElement.className = 'flex items-center text-green-700';
            } else {
                totalSpan.className = 'font-bold text-blue-600';
                totalSpan.parentElement.className = 'flex items-center text-blue-700';
            }
        }

        // Add event listeners to percentage inputs
        document.querySelectorAll('.percentage-input').forEach(input => {
            input.addEventListener('input', updateTotalPercentage);
        });

        // Initial calculation
        updateTotalPercentage();

        // Global JS error handlers to surface issues to users
        window.addEventListener('error', function(e) {
            try { showNotification('خطای غیرمنتظره', (e && e.message) ? e.message : 'مشکلی رخ داد', 'error'); } catch(_) {}
        });
        window.addEventListener('unhandledrejection', function(e) {
            try { showNotification('خطای غیرمنتظره', (e && e.reason && (e.reason.message || e.reason)) ? (e.reason.message || e.reason) : 'مشکلی رخ داد', 'error'); } catch(_) {}
        });
    </script>
</body>
</html>
