<?php
declare(strict_types=1);

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/content-filter.php';

require_login();

const IMAGES_DIR = BASE_PATH . 'uploads/images';
const MAX_IMAGE_SIZE = 2097152; // 2MB
const ALLOWED_IMAGE_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

// ایجاد پوشه تصاویر
if (!is_dir(IMAGES_DIR)) {
    @mkdir(IMAGES_DIR, 0775, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['image'])) {
        $file = $_FILES['image'];
        $user = current_user();
        
        // بررسی مسدودسازی کاربر
        if (ContentFilter::isUserSuspended($user['id'])) {
            throw new RuntimeException('حساب شما مسدود شده است');
        }
        
        // بررسی نام فایل
        $imageCheck = ContentFilter::checkImageContent($file['name']);
        if (!$imageCheck['passed']) {
            ContentFilter::logContentIssue($user['id'], 'Image upload: ' . $file['name'], $imageCheck['issues']);
            if ($imageCheck['action'] === 'ban_user') {
                ContentFilter::suspendUser($user['id'], 24, 'آپلود فایل نامناسب');
            }
            throw new RuntimeException('نام فایل نامناسب است: ' . implode(', ', $imageCheck['issues']));
        }
        
        // بررسی خطاهای آپلود
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException('خطا در آپلود فایل');
        }
        
        // بررسی حجم فایل
        if ($file['size'] > MAX_IMAGE_SIZE) {
            throw new RuntimeException('حجم تصویر نباید بیشتر از 2048 کیلوبایت باشد');
        }
        
        // بررسی نوع فایل
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        if (!in_array($mimeType, ALLOWED_IMAGE_TYPES, true)) {
            throw new RuntimeException('فقط فایل‌های تصویری (JPG, PNG, GIF, WebP) مجاز هستند');
        }
        
        // تولید نام فایل یکتا
        $extension = match($mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png', 
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            default => 'jpg'
        };
        
        $fileName = 'img_' . time() . '_' . mt_rand(1000, 9999) . '.' . $extension;
        $filePath = IMAGES_DIR . DIRECTORY_SEPARATOR . $fileName;
        
        // انتقال فایل
        if (!move_uploaded_file($file['tmp_name'], $filePath)) {
            throw new RuntimeException('خطا در ذخیره‌سازی تصویر');
        }
        
        // بهینه‌سازی تصویر
        optimizeImage($filePath, $mimeType);
        
        // ثبت در دیتابیس
        $image = [
            'id' => JsonDB::nextId(),
            'fileName' => $fileName,
            'originalName' => $file['name'],
            'mimeType' => $mimeType,
            'size' => $file['size'],
            'uploaderId' => (int)current_user()['id'],
            'createdAt' => date('c'),
        ];
        
        JsonDB::upsert('images', function($images) use ($image) {
            $images[] = $image;
            // نگه داشتن فقط 1000 تصویر آخر
            if (count($images) > 1000) {
                $removed = array_slice($images, 0, -1000);
                // حذف فایل‌های قدیمی
                foreach ($removed as $img) {
                    @unlink(IMAGES_DIR . DIRECTORY_SEPARATOR . $img['fileName']);
                }
                $images = array_slice($images, -1000);
            }
            return $images;
        });
        
        $imageUrl = 'uploads/images/' . $fileName;
        
        json_response([
            'ok' => true,
            'image' => $image,
            'url' => $imageUrl,
            'markdown' => "![{$file['name']}]($imageUrl)"
        ]);
    }
    
    json_response(['ok' => false, 'error' => 'فایل تصویری ارسال نشده'], 400);
    
} catch (Throwable $e) {
    json_response(['ok' => false, 'error' => $e->getMessage()], 400);
}

function optimizeImage(string $filePath, string $mimeType): void {
    try {
        $maxWidth = 1200;
        $maxHeight = 800;
        $quality = 85;
        
        switch ($mimeType) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($filePath);
                break;
            case 'image/png':
                $image = imagecreatefrompng($filePath);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($filePath);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($filePath);
                break;
            default:
                return;
        }
        
        if (!$image) return;
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // محاسبه ابعاد جدید
        if ($width > $maxWidth || $height > $maxHeight) {
            $ratio = min($maxWidth / $width, $maxHeight / $height);
            $newWidth = (int)($width * $ratio);
            $newHeight = (int)($height * $ratio);
            
            $resized = imagecreatetruecolor($newWidth, $newHeight);
            
            // حفظ شفافیت برای PNG و GIF
            if ($mimeType === 'image/png' || $mimeType === 'image/gif') {
                imagealphablending($resized, false);
                imagesavealpha($resized, true);
                $transparent = imagecolorallocatealpha($resized, 255, 255, 255, 127);
                imagefill($resized, 0, 0, $transparent);
            }
            
            imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
            
            // ذخیره تصویر بهینه‌شده
            switch ($mimeType) {
                case 'image/jpeg':
                    imagejpeg($resized, $filePath, $quality);
                    break;
                case 'image/png':
                    imagepng($resized, $filePath, 9);
                    break;
                case 'image/gif':
                    imagegif($resized, $filePath);
                    break;
                case 'image/webp':
                    imagewebp($resized, $filePath, $quality);
                    break;
            }
            
            imagedestroy($resized);
        }
        
        imagedestroy($image);
    } catch (Throwable $e) {
        // در صورت خطا، فایل اصلی را نگه می‌داریم
        error_log("Image optimization failed: " . $e->getMessage());
    }
}
?>
