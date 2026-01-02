<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/db.php';

$id = (int)($_GET['id'] ?? 0);
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
    exit('پروژه یافت نشد');
}

// Get file path
$fileName = $project['fileName'] ?? '';
$filePath = __DIR__ . '/uploads/approved/' . $fileName;
if (!file_exists($filePath)) {
    $filePath = __DIR__ . '/uploads/pending/' . $fileName;
}

if (!file_exists($filePath)) {
    header('HTTP/1.1 404 Not Found');
    exit('فایل پروژه یافت نشد');
}

// Extract and read ZIP contents
$files = [];
$error = '';

try {
    $zip = new ZipArchive();
    $result = $zip->open($filePath);
    
    if ($result === TRUE) {
        $allowedExtensions = ['php', 'js', 'html', 'css', 'py', 'java', 'cpp', 'c', 'h', 'cs', 'rb', 'go', 'rs', 'swift', 'kt', 'ts', 'tsx', 'jsx', 'vue', 'scss', 'sass', 'less', 'sql', 'xml', 'json', 'md', 'txt', 'yml', 'yaml', 'ini', 'env'];
        $maxFileSize = 1024 * 1024; // 1MB max per file for preview
        $maxFiles = 50; // Max 50 files to prevent overload
        
        for ($i = 0; $i < $zip->numFiles && count($files) < $maxFiles; $i++) {
            $stat = $zip->statIndex($i);
            $filename = $stat['name'];
            
            // Skip directories and hidden files
            if (substr($filename, -1) === '/' || strpos(basename($filename), '.') === 0) {
                continue;
            }
            
            // Check file extension
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            if (!in_array($extension, $allowedExtensions)) {
                continue;
            }
            
            // Check file size
            if ($stat['size'] > $maxFileSize) {
                $files[] = [
                    'name' => $filename,
                    'size' => $stat['size'],
                    'content' => '',
                    'error' => 'فایل بزرگ‌تر از 1024 کیلوبایت است',
                    'extension' => $extension
                ];
                continue;
            }
            
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                // Check if content is binary
                if (mb_check_encoding($content, 'UTF-8') || mb_check_encoding($content, 'ASCII')) {
                    $files[] = [
                        'name' => $filename,
                        'size' => $stat['size'],
                        'content' => $content,
                        'extension' => $extension,
                        'error' => ''
                    ];
                } else {
                    $files[] = [
                        'name' => $filename,
                        'size' => $stat['size'],
                        'content' => '',
                        'error' => 'فایل باینری است و قابل نمایش نیست',
                        'extension' => $extension
                    ];
                }
            }
        }
        
        $zip->close();
    } else {
        $error = 'خطا در باز کردن فایل ZIP';
    }
} catch (Exception $e) {
    $error = 'خطا در استخراج فایل: ' . $e->getMessage();
}

function getLanguageFromExtension($ext) {
    $map = [
        'php' => 'php',
        'js' => 'javascript',
        'jsx' => 'javascript',
        'ts' => 'typescript',
        'tsx' => 'typescript',
        'html' => 'html',
        'css' => 'css',
        'scss' => 'scss',
        'sass' => 'sass',
        'py' => 'python',
        'java' => 'java',
        'cpp' => 'cpp',
        'c' => 'c',
        'cs' => 'csharp',
        'rb' => 'ruby',
        'go' => 'go',
        'rs' => 'rust',
        'swift' => 'swift',
        'kt' => 'kotlin',
        'sql' => 'sql',
        'json' => 'json',
        'xml' => 'xml',
        'md' => 'markdown',
        'yml' => 'yaml',
        'yaml' => 'yaml'
    ];
    return $map[$ext] ?? 'text';
}
?>

<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>مشاهده کد - <?php echo htmlspecialchars($project['title']); ?> | سورس کده</title>
    <meta name="description" content="مشاهده کد آنلاین پروژه <?php echo htmlspecialchars($project['title']); ?>">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <!-- Syntax Highlighting -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/line-numbers/prism-line-numbers.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@300;400;500;600&display=swap');
        
        * { font-family: 'Vazirmatn', sans-serif; }
        
        .gradient-primary { 
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); 
        }
        
        .file-tree {
            max-height: calc(100vh - 200px);
            overflow-y: auto;
        }
        
        .file-item {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .file-item:hover {
            background-color: #f3f4f6;
        }
        
        .file-item.active {
            background-color: #dbeafe;
            border-right: 3px solid #3b82f6;
        }
        
        .code-container {
            max-height: calc(100vh - 200px);
            overflow: auto;
            font-family: 'JetBrains Mono', 'Courier New', monospace;
        }
        
        pre[class*="language-"] {
            margin: 0;
            border-radius: 0;
            background: #2d3748 !important;
        }
        
        .line-numbers .line-numbers-rows {
            border-right: 1px solid #4a5568;
            padding-right: 0.5em;
            margin-right: 0.5em;
        }
        
        .file-extension-icon {
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 8px;
            text-transform: uppercase;
        }
        
        .ext-php { background: #8892bf; color: white; }
        .ext-js, .ext-jsx { background: #f7df1e; color: black; }
        .ext-ts, .ext-tsx { background: #3178c6; color: white; }
        .ext-html { background: #e34f26; color: white; }
        .ext-css { background: #1572b6; color: white; }
        .ext-py { background: #3776ab; color: white; }
        .ext-java { background: #ed8b00; color: white; }
        .ext-cpp, .ext-c { background: #00599c; color: white; }
        .ext-cs { background: #239120; color: white; }
        .ext-json { background: #000; color: white; }
        .ext-md { background: #083fa1; color: white; }
        .ext-default { background: #6b7280; color: white; }
        
        .toolbar {
            background: #1a202c;
            color: white;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #2d3748;
        }
        
        .search-box {
            background: #2d3748;
            border: 1px solid #4a5568;
            color: white;
            padding: 0.5rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }
        
        .search-box:focus {
            outline: none;
            border-color: #3b82f6;
        }
        
        .file-stats {
            background: #f8fafc;
            border-radius: 0.5rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .file-tree {
                max-height: 300px;
            }
            .code-container {
                max-height: 400px;
            }
        }
    </style>
</head>
<body class="bg-gray-100">
    <!-- Header -->
    <div class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center">
                    <i class="fas fa-code text-blue-600 text-xl ml-2"></i>
                    <h1 class="text-lg font-bold text-gray-900">مشاهده کد آنلاین</h1>
                    <span class="text-gray-500 text-sm mr-4">
                        <?php echo htmlspecialchars($project['title']); ?>
                    </span>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <button onclick="window.close()" class="text-gray-600 hover:text-gray-900 px-3 py-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <i class="fas fa-times ml-1"></i>
                        بستن
                    </button>
                    <a href="project.php?id=<?php echo $id; ?>" target="_blank" class="gradient-primary text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all text-sm font-medium">
                        <i class="fas fa-external-link-alt ml-1"></i>
                        مشاهده پروژه
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
                <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                <h2 class="text-xl font-bold text-red-800 mb-2">خطا در بارگذاری کد</h2>
                <p class="text-red-700"><?php echo htmlspecialchars($error); ?></p>
            </div>
        </div>
    <?php elseif (empty($files)): ?>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-6 text-center">
                <i class="fas fa-folder-open text-yellow-500 text-3xl mb-4"></i>
                <h2 class="text-xl font-bold text-yellow-800 mb-2">فایل کدی یافت نشد</h2>
                <p class="text-yellow-700">این پروژه شامل فایل‌های کد قابل نمایش نیست</p>
            </div>
        </div>
    <?php else: ?>
        <!-- File Stats -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="file-stats">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4 space-x-reverse">
                        <div class="flex items-center">
                            <i class="fas fa-file-code text-blue-500 ml-2"></i>
                            <span class="font-semibold"><?php echo count($files); ?> فایل</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-hdd text-green-500 ml-2"></i>
                            <span class="text-sm text-gray-600">
                                <?php echo number_format(array_sum(array_column($files, 'size')) / 1024, 1); ?> کیلوبایت
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center">
                        <input type="text" id="searchFiles" placeholder="جستجو در فایل‌ها..." class="search-box w-64">
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-8">
            <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
                <!-- File Tree -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="bg-gray-50 px-4 py-3 border-b">
                            <h3 class="font-semibold text-gray-900 flex items-center">
                                <i class="fas fa-folder-tree text-blue-500 ml-2"></i>
                                فایل‌ها
                            </h3>
                        </div>
                        <div class="file-tree p-2">
                            <?php foreach ($files as $index => $file): ?>
                            <div class="file-item p-3 rounded-lg mb-1 flex items-center" 
                                 data-index="<?php echo $index; ?>" 
                                 data-filename="<?php echo htmlspecialchars($file['name']); ?>"
                                 onclick="showFile(<?php echo $index; ?>)">
                                <div class="file-extension-icon ext-<?php echo $file['extension']; ?>">
                                    <?php echo strtoupper(substr($file['extension'], 0, 2)); ?>
                                </div>
                                <div class="flex-1">
                                    <div class="font-medium text-sm text-gray-900 truncate">
                                        <?php echo htmlspecialchars(basename($file['name'])); ?>
                                    </div>
                                    <div class="text-xs text-gray-500">
                                        <?php 
                                        if ($file['error']) {
                                            echo '<span class="text-red-500">' . htmlspecialchars($file['error']) . '</span>';
                                        } else {
                                            echo number_format($file['size'] / 1024, 1) . ' کیلوبایت';
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Code Viewer -->
                <div class="lg:col-span-3">
                    <div class="bg-white rounded-xl shadow overflow-hidden">
                        <div class="toolbar">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <i class="fas fa-file-code ml-2"></i>
                                    <span id="currentFileName" class="font-medium">فایل را انتخاب کنید</span>
                                </div>
                                <div class="flex items-center space-x-2 space-x-reverse">
                                    <button onclick="copyCode()" class="text-gray-300 hover:text-white px-3 py-1 rounded transition-colors text-sm">
                                        <i class="fas fa-copy ml-1"></i>کپی
                                    </button>
                                    <button onclick="downloadCurrentFile()" class="text-gray-300 hover:text-white px-3 py-1 rounded transition-colors text-sm">
                                        <i class="fas fa-download ml-1"></i>دانلود
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="code-container" id="codeContainer">
                            <div class="p-8 text-center text-gray-500">
                                <i class="fas fa-mouse-pointer text-4xl mb-4"></i>
                                <p class="text-lg">فایل مورد نظر را از فهرست سمت راست انتخاب کنید</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <script>
        const files = <?php echo json_encode($files); ?>;
        let currentFileIndex = -1;

        function showFile(index) {
            if (index < 0 || index >= files.length) return;
            
            const file = files[index];
            currentFileIndex = index;
            
            // Update active file in tree
            document.querySelectorAll('.file-item').forEach(item => item.classList.remove('active'));
            document.querySelector(`.file-item[data-index="${index}"]`).classList.add('active');
            
            // Update file name in toolbar
            document.getElementById('currentFileName').textContent = file.name;
            
            const container = document.getElementById('codeContainer');
            
            if (file.error) {
                container.innerHTML = `
                    <div class="p-8 text-center text-red-500">
                        <i class="fas fa-exclamation-triangle text-4xl mb-4"></i>
                        <p class="text-lg">${file.error}</p>
                    </div>
                `;
                return;
            }
            
            if (!file.content) {
                container.innerHTML = `
                    <div class="p-8 text-center text-gray-500">
                        <i class="fas fa-file-times text-4xl mb-4"></i>
                        <p class="text-lg">محتوای فایل در دسترس نیست</p>
                    </div>
                `;
                return;
            }
            
            const language = getLanguageFromExtension(file.extension);
            const escapedContent = escapeHtml(file.content);
            
            container.innerHTML = `
                <pre class="line-numbers"><code class="language-${language}">${escapedContent}</code></pre>
            `;
            
            // Re-highlight syntax
            Prism.highlightAll();
        }

        function getLanguageFromExtension(ext) {
            const map = {
                'php': 'php',
                'js': 'javascript',
                'jsx': 'javascript',
                'ts': 'typescript',
                'tsx': 'typescript',
                'html': 'html',
                'css': 'css',
                'scss': 'scss',
                'sass': 'sass',
                'py': 'python',
                'java': 'java',
                'cpp': 'cpp',
                'c': 'c',
                'cs': 'csharp',
                'rb': 'ruby',
                'go': 'go',
                'rs': 'rust',
                'swift': 'swift',
                'kt': 'kotlin',
                'sql': 'sql',
                'json': 'json',
                'xml': 'xml',
                'md': 'markdown',
                'yml': 'yaml',
                'yaml': 'yaml'
            };
            return map[ext] || 'text';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        function copyCode() {
            if (currentFileIndex < 0) return;
            
            const code = files[currentFileIndex].content;
            navigator.clipboard.writeText(code).then(() => {
                showNotification('کپی شد!', 'کد فایل با موفقیت کپی شد', 'success');
            }).catch(() => {
                showNotification('خطا', 'خطا در کپی کردن کد', 'error');
            });
        }

        function downloadCurrentFile() {
            if (currentFileIndex < 0) return;
            
            const file = files[currentFileIndex];
            const blob = new Blob([file.content], { type: 'text/plain' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = file.name.split('/').pop();
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }

        // Search functionality
        document.getElementById('searchFiles').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase();
            const fileItems = document.querySelectorAll('.file-item');
            
            fileItems.forEach(item => {
                const filename = item.dataset.filename.toLowerCase();
                if (filename.includes(query)) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });

        // Auto-select first file
        if (files.length > 0) {
            showFile(0);
        }

        // Notification function
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

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.ctrlKey || e.metaKey) {
                switch(e.key) {
                    case 'c':
                        if (currentFileIndex >= 0) {
                            e.preventDefault();
                            copyCode();
                        }
                        break;
                    case 'f':
                        e.preventDefault();
                        document.getElementById('searchFiles').focus();
                        break;
                }
            }
        });
    </script>
</body>
</html>
