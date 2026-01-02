<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/utils.php';
require_once __DIR__ . '/includes/db.php';

// Check if user is logged in
if (!current_user()) {
    header('Location: auth.php');
    exit;
}

$current_user = current_user();
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: sources.php');
    exit;
}

// Get project
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

// Check if user is the author; allow verified bots to view the public page instead of 403
if ((int)($project['authorId'] ?? 0) !== (int)$current_user['id']) {
    if (function_exists('is_search_engine_bot') && is_search_engine_bot()) {
        header('Location: project.php?id=' . (int)$id, true, 302);
        exit;
    }
    header('HTTP/1.1 403 Forbidden');
    exit('شما اجازه ویرایش این پروژه را ندارید');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
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
        if (empty($language)) throw new Exception('زبان برنامه‌نویسی الزامی است');
        if (empty($level)) throw new Exception('سطح پروژه الزامی است');
        
        // Validate total percentage
        $totalPercent = $pythonPercent + $jsPercent + $phpPercent + $javaPercent + $cppPercent + $otherPercent;
        if ($totalPercent > 100) {
            throw new Exception('مجموع درصدها نمی‌تواند بیشتر از 100 باشد');
        }
        
        // Update project
        $submissions = JsonDB::read('submissions');
        for ($i = 0; $i < count($submissions); $i++) {
            if ((int)($submissions[$i]['id'] ?? 0) === $id) {
                $submissions[$i]['title'] = $title;
                $submissions[$i]['description'] = $description;
                $submissions[$i]['language'] = $language;
                $submissions[$i]['level'] = $level;
                $submissions[$i]['tags'] = $tags;
                $submissions[$i]['hashtags'] = $hashtags;
                $submissions[$i]['repositoryUrl'] = $repositoryUrl;
                $submissions[$i]['demoUrl'] = $demoUrl;
                $submissions[$i]['setupGuide'] = $setupGuide;
                $submissions[$i]['password'] = $projectPassword;
                $submissions[$i]['lastUpdatedAt'] = date('c');
                $submissions[$i]['codeComposition'] = [
                    'python' => $pythonPercent,
                    'javascript' => $jsPercent,
                    'php' => $phpPercent,
                    'java' => $javaPercent,
                    'cpp' => $cppPercent,
                    'other' => $otherPercent
                ];
                break;
            }
        }
        
        JsonDB::write('submissions', $submissions);
        $success = 'پروژه با موفقیت بروزرسانی شد!';
        $project = $submissions[$i]; // Update local project data
        
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
    <title>ویرایش پروژه - <?php echo htmlspecialchars($project['title']); ?> | سورس کده</title>
    <meta name="description" content="ویرایش پروژه <?php echo htmlspecialchars($project['title']); ?> در سورس کده">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
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
        
        .glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
                        <span class="text-gray-600 font-medium">ویرایش پروژه</span>
                    </div>
                </div>
                <div class="flex items-center space-x-3 space-x-reverse">
                    <a href="project.php?id=<?php echo $id; ?>" class="gradient-primary text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all transform hover:scale-105 text-sm font-medium">
                        <i class="fas fa-arrow-right ml-1"></i>
                        بازگشت به پروژه
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-50 via-white to-purple-50"></div>
        
        <div class="relative min-h-screen py-12">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="text-center mb-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 gradient-primary rounded-full mb-8 shadow-2xl">
                        <i class="fas fa-edit text-4xl text-white"></i>
                    </div>
                    <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 mb-6 leading-tight">
                        ویرایش پروژه
                    </h1>
                    <p class="text-lg sm:text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed">
                        اطلاعات پروژه خود را بروزرسانی کنید
                    </p>
                </div>

                <!-- Success/Error Messages -->
                <?php if ($success): ?>
                    <div class="glassmorphism border border-green-200 rounded-2xl p-6 mb-8">
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
                    <div class="glassmorphism border border-red-200 rounded-2xl p-6 mb-8">
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

                <!-- Edit Form -->
                <div class="glassmorphism rounded-3xl shadow-2xl overflow-hidden">
                    <div class="gradient-bg p-6 sm:p-8">
                        <div class="text-center">
                            <div class="inline-flex items-center justify-center w-16 h-16 bg-white/20 rounded-full mb-4">
                                <i class="fas fa-file-edit text-2xl text-white"></i>
                            </div>
                            <h2 class="text-2xl sm:text-3xl font-bold text-white mb-2">
                                ویرایش اطلاعات پروژه
                            </h2>
                            <p class="text-blue-100">تغییرات مورد نظر خود را اعمال کنید</p>
                        </div>
                    </div>
                
                    <form method="POST" class="p-6 sm:p-8 space-y-8">
                        <!-- Project Details -->
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8">
                            <!-- Title -->
                            <div class="lg:col-span-2 floating-label">
                                <input type="text" name="title" required 
                                       value="<?= htmlspecialchars($project['title'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">عنوان پروژه *</label>
                            </div>

                            <!-- Language -->
                            <div class="floating-label">
                                <select name="language" required class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <option value="">انتخاب کنید</option>
                                    <option value="JavaScript" <?= ($project['language'] ?? '') === 'JavaScript' ? 'selected' : '' ?>>JavaScript</option>
                                    <option value="Python" <?= ($project['language'] ?? '') === 'Python' ? 'selected' : '' ?>>Python</option>
                                    <option value="PHP" <?= ($project['language'] ?? '') === 'PHP' ? 'selected' : '' ?>>PHP</option>
                                    <option value="Java" <?= ($project['language'] ?? '') === 'Java' ? 'selected' : '' ?>>Java</option>
                                    <option value="C#" <?= ($project['language'] ?? '') === 'C#' ? 'selected' : '' ?>>C#</option>
                                    <option value="C++" <?= ($project['language'] ?? '') === 'C++' ? 'selected' : '' ?>>C++</option>
                                    <option value="Ruby" <?= ($project['language'] ?? '') === 'Ruby' ? 'selected' : '' ?>>Ruby</option>
                                    <option value="Go" <?= ($project['language'] ?? '') === 'Go' ? 'selected' : '' ?>>Go</option>
                                    <option value="Rust" <?= ($project['language'] ?? '') === 'Rust' ? 'selected' : '' ?>>Rust</option>
                                    <option value="Swift" <?= ($project['language'] ?? '') === 'Swift' ? 'selected' : '' ?>>Swift</option>
                                    <option value="Kotlin" <?= ($project['language'] ?? '') === 'Kotlin' ? 'selected' : '' ?>>Kotlin</option>
                                    <option value="Other" <?= ($project['language'] ?? '') === 'Other' ? 'selected' : '' ?>>سایر</option>
                                </select>
                                <label class="text-gray-700 font-semibold">زبان برنامه‌نویسی *</label>
                            </div>

                            <!-- Level -->
                            <div class="floating-label">
                                <select name="level" required class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <option value="">انتخاب کنید</option>
                                    <option value="مبتدی" <?= ($project['level'] ?? '') === 'مبتدی' ? 'selected' : '' ?>>مبتدی</option>
                                    <option value="متوسط" <?= ($project['level'] ?? '') === 'متوسط' ? 'selected' : '' ?>>متوسط</option>
                                    <option value="پیشرفته" <?= ($project['level'] ?? '') === 'پیشرفته' ? 'selected' : '' ?>>پیشرفته</option>
                                </select>
                                <label class="text-gray-700 font-semibold">سطح پروژه *</label>
                            </div>

                            <!-- Description -->
                            <div class="lg:col-span-2 floating-label">
                                <textarea name="description" required rows="4" 
                                          placeholder=" "
                                          class="form-input w-full px-4 py-4 rounded-2xl bg-white resize-none"><?= htmlspecialchars($project['description'] ?? '') ?></textarea>
                                <label class="text-gray-700 font-semibold">توضیحات پروژه *</label>
                            </div>

                            <!-- Tags -->
                            <div class="floating-label">
                                <input type="text" name="tags" 
                                       value="<?= htmlspecialchars(implode(', ', $project['tags'] ?? [])) ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">تگ‌ها (با کاما جدا کنید)</label>
                            </div>

                            <!-- Hashtags -->
                            <div class="floating-label">
                                <input type="text" name="hashtags" 
                                       value="<?= htmlspecialchars(implode(', ', $project['hashtags'] ?? [])) ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">هشتگ‌ها (با کاما جدا کنید)</label>
                                <p class="text-xs text-gray-500 mt-1">مثال: #وب_سایت، #پایتون، #بازی</p>
                            </div>

                            <!-- Repository URL -->
                            <div class="floating-label">
                                <input type="url" name="repositoryUrl" 
                                       value="<?= htmlspecialchars($project['repositoryUrl'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">لینک مخزن (اختیاری)</label>
                            </div>

                            <!-- Demo URL -->
                            <div class="floating-label">
                                <input type="url" name="demoUrl" 
                                       value="<?= htmlspecialchars($project['demoUrl'] ?? '') ?>"
                                       placeholder=" "
                                       class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                                <label class="text-gray-700 font-semibold">لینک دمو (اختیاری)</label>
                            </div>
                        </div>

                        <!-- Code Composition Section -->
                        <div class="border-t border-gray-100 pt-8">
                            <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center">
                                <i class="fas fa-chart-pie text-blue-500 ml-2"></i>
                                ترکیب کدهای پروژه
                            </h3>
                            <p class="text-sm text-gray-600 mb-6">درصد استفاده از زبان‌های مختلف برنامه‌نویسی در پروژه خود را مشخص کنید (مجموع نباید بیشتر از 100% باشد)</p>
                            
                            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                                <div class="floating-label">
                                    <input type="number" name="pythonPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['python'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">Python (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="jsPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['javascript'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">JavaScript (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="phpPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['php'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">PHP (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="javaPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['java'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">Java (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="cppPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['cpp'] ?? 0) ?>"
                                           placeholder=" " class="form-input percentage-input w-full px-4 py-4 rounded-2xl bg-white">
                                    <label class="text-gray-700 font-semibold">C++ (%)</label>
                                </div>
                                
                                <div class="floating-label">
                                    <input type="number" name="otherPercent" min="0" max="100" 
                                           value="<?= (int)($project['codeComposition']['other'] ?? 0) ?>"
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
                        <div class="floating-label border-t border-gray-100 pt-8">
                            <textarea name="setupGuide" rows="4" 
                                      placeholder=" "
                                      class="form-input w-full px-4 py-4 rounded-2xl bg-white resize-none"><?= htmlspecialchars($project['setupGuide'] ?? '') ?></textarea>
                            <label class="text-gray-700 font-semibold">آموزش راه‌اندازی (اختیاری)</label>
                        </div>

                        <!-- Password Section -->
                        <div class="floating-label">
                            <input type="text" name="projectPassword" 
                                   value="<?= htmlspecialchars($project['password'] ?? '') ?>"
                                   placeholder=" "
                                   class="form-input w-full px-4 py-4 rounded-2xl bg-white">
                            <label class="text-gray-700 font-semibold">رمز عبور فایل ZIP (اختیاری)</label>
                        </div>

                        <!-- Submit Button -->
                        <div class="text-center pt-8">
                            <button type="submit" class="gradient-primary text-white px-16 py-5 rounded-2xl font-bold text-xl hover:shadow-2xl transform hover:scale-105 transition-all duration-300 inline-flex items-center">
                                <i class="fas fa-save ml-3 text-2xl"></i>
                                <span>ذخیره تغییرات</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
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

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const totalPercent = parseInt(document.getElementById('totalPercent').textContent);
            if (totalPercent > 100) {
                e.preventDefault();
                alert('مجموع درصدها نمی‌تواند بیشتر از 100% باشد');
                return false;
            }
            
            // Show loading state
            const submitBtn = this.querySelector('button[type="submit"]');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>در حال ذخیره...';
            submitBtn.disabled = true;
        });

        // Floating labels enhancement for mobile
        if (window.innerWidth <= 768) {
            document.querySelectorAll('.floating-label').forEach(container => {
                container.classList.remove('floating-label');
            });
        }
    </script>
</body>
</html>
