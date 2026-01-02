<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

// Redirect if already logged in
$current_user = current_user();
if ($current_user) {
    header('Location: index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود و ثبت نام - سورس کده انجمن</title>
    <meta name="description" content="ورود به حساب کاربری یا ثبت نام در سورس کده. دسترسی به هزاران پروژه متن باز و اشتراک گذاری پروژه‌های خود.">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700&display=swap');
        * { 
            font-family: 'Vazirmatn', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .input-focus {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .input-focus:focus {
            transform: translateY(-1px);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        .btn-hover {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .btn-hover:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(0, 0, 0, 0.2);
        }
        
        .slide-in {
            animation: slideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        .slide-out {
            animation: slideOut 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes slideOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-30px);
            }
        }
        
        .floating-label {
            transition: all 0.2s ease;
        }
        
        .input-group:focus-within .floating-label {
            transform: translateY(-24px) scale(0.85);
            color: #667eea;
        }
        
        .input-group.has-value .floating-label {
            transform: translateY(-24px) scale(0.85);
            color: #6b7280;
        }
        
        .strength-bar {
            transition: all 0.3s ease;
        }
        
        .ripple {
            position: relative;
            overflow: hidden;
        }
        
        .ripple::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .ripple:active::before {
            width: 300px;
            height: 300px;
        }
        
        .checkbox-custom {
            appearance: none;
            width: 20px;
            height: 20px;
            border: 2px solid #d1d5db;
            border-radius: 4px;
            background: white;
            position: relative;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .checkbox-custom:checked {
            background: #667eea;
            border-color: #667eea;
        }
        
        .checkbox-custom:checked::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 12px;
            font-weight: bold;
        }
        
        .select-custom {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: left 12px center;
            background-repeat: no-repeat;
            background-size: 16px 12px;
        }
        
        .language-tag {
            transition: all 0.2s ease;
        }
        
        .language-tag:hover {
            transform: translateY(-1px);
        }
        
        .language-tag.selected {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .loading {
            pointer-events: none;
            opacity: 0.7;
        }
        
        .loading .btn-text {
            display: none;
        }
        
        .loading .btn-loading {
            display: inline-block;
        }
        
        .btn-loading {
            display: none;
        }
        
        /* Registration Steps */
        .register-step { 
            transition: all 0.4s ease-in-out; 
        }
        .register-step.hidden { 
            display: none; 
        }
        .register-step.fade-out { 
            opacity: 0; 
            transform: translateX(-20px); 
        }
        .register-step.fade-in { 
            opacity: 1; 
            transform: translateX(0); 
        }
        
        /* Verification code input styling */
        #verificationCode {
            letter-spacing: 0.5em;
            font-weight: 600;
        }
        
        /* Timer styling */
        #resendTimer {
            color: #6b7280;
        }
        
        /* Dark mode adjustments */
        .dark-mode .bg-blue-50 { 
            background: rgba(59, 130, 246, 0.1) !important; 
        }
        .dark-mode #verificationEmail { 
            color: #60a5fa !important; 
        }
        .dark-mode .text-gray-700 { 
            color: #d1d5db !important; 
        }
        .dark-mode .bg-gray-200 { 
            background: #374151 !important; 
        }
        .dark-mode .bg-gray-200:hover { 
            background: #4b5563 !important; 
        }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 flex items-center justify-center p-4">
    
    <!-- Main Container -->
    <div class="w-full max-w-md">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                <i class="fas fa-user-circle text-white text-3xl"></i>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">خوش آمدید</h1>
            <p class="text-gray-600">به حساب کاربری خود دسترسی پیدا کنید</p>
        </div>

        <!-- Auth Container -->
        <div class="glass-card rounded-3xl shadow-2xl overflow-hidden">
            <!-- Tab Buttons -->
            <div class="flex bg-gray-50 p-2 m-6 rounded-2xl">
                <button id="loginTab" class="flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 bg-white text-gray-800 shadow-md">
                    <i class="fas fa-sign-in-alt ml-2"></i>
                    ورود
                </button>
                <button id="registerTab" class="flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 text-gray-600 hover:text-gray-800">
                    <i class="fas fa-user-plus ml-2"></i>
                    ثبت نام
                </button>
            </div>

            <!-- Login Form -->
            <div id="loginForm" class="p-6 pt-0 slide-in">
                <form id="loginFormElement" class="space-y-6">
                    <!-- Username/Email Input -->
                    <div class="input-group relative">
                        <input type="text" id="loginUsername" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="نام کاربری یا ایمیل" required>
                        <label for="loginUsername" class="floating-label absolute right-4 top-4 text-gray-500 pointer-events-none">نام کاربری یا ایمیل</label>
                        <i class="fas fa-user absolute left-4 top-4 text-gray-400"></i>
                    </div>

                    <!-- Password Input -->
                    <div class="input-group relative">
                        <input type="password" id="loginPassword" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent pl-12" placeholder="رمز عبور" required>
                        <label for="loginPassword" class="floating-label absolute right-4 top-4 text-gray-500 pointer-events-none">رمز عبور</label>
                        <button type="button" onclick="togglePassword('loginPassword', this)" class="absolute left-4 top-4 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Remember & Forgot -->
                    <div class="flex items-center justify-between">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" class="checkbox-custom ml-3">
                            <span class="text-gray-600 text-sm">مرا به خاطر بسپار</span>
                        </label>
                        <a href="#" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors">فراموشی رمز عبور؟</a>
                    </div>

                    <!-- CAPTCHA -->
                    <div id="loginCaptcha" class="input-group relative hidden">
                        <input type="text" id="loginCaptchaAnswer" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="پاسخ کپچا">
                        <label id="loginCaptchaLabel" for="loginCaptchaAnswer" class="floating-label absolute right-4 top-4 text-gray-500 pointer-events-none">کپچا</label>
                        <input type="hidden" id="loginCaptchaToken" value="">
                        <button type="button" id="refreshLoginCaptcha" class="absolute left-4 top-4 text-gray-400 hover:text-gray-600 transition-colors" title="تازه‌سازی کپچا">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-2xl font-semibold btn-hover ripple shadow-lg">
                        <span class="btn-text">
                            <i class="fas fa-sign-in-alt ml-2"></i>
                            ورود به حساب
                        </span>
                        <span class="btn-loading">
                            <i class="fas fa-spinner fa-spin ml-2"></i>
                            در حال ورود...
                        </span>
                    </button>
                </form>

                <!-- Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-200"></div>
                    <span class="px-4 text-sm text-gray-500">یا</span>
                    <div class="flex-1 border-t border-gray-200"></div>
                </div>

                <!-- Google Login Button -->
                <?php if (defined('GOOGLE_OAUTH_ENABLE') && GOOGLE_OAUTH_ENABLE): ?>
                <button id="googleLoginBtn" type="button" class="w-full bg-white border-2 border-gray-200 text-gray-700 py-4 rounded-2xl font-semibold btn-hover flex items-center justify-center hover:bg-gray-50 transition-all duration-300">
                    <svg class="w-5 h-5 ml-3" viewBox="0 0 24 24">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    ورود با گوگل
                </button>
                <?php endif; ?>
            </div>

            <!-- Register Form -->
            <div id="registerForm" class="p-6 pt-0 hidden">
                <!-- Step 1: Registration Details -->
                <div id="registerStep1" class="register-step">
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">ثبت نام - مرحله اول</h3>
                        <p class="text-gray-600 text-sm">اطلاعات حساب کاربری خود را وارد کنید</p>
                        <div class="flex justify-center mt-4">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">1</div>
                                <div class="w-16 h-1 bg-gray-300"></div>
                                <div class="w-8 h-8 bg-gray-300 text-gray-600 rounded-full flex items-center justify-center text-sm font-bold">2</div>
                            </div>
                        </div>
                    </div>
                    <form id="registerFormElement" class="space-y-5">
                    <!-- Personal Info -->
                    <div class="input-group relative">
                        <input type="text" id="firstName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="نام و نام خانوادگی" required>
                        <label for="firstName" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">نام و نام خانوادگی</label>
                    </div>
                    <!-- Hidden last name field (combined with first name) -->
                    <input type="hidden" id="lastName" value="">

                    <!-- Username -->
                    <div class="input-group relative">
                        <input type="text" id="regUsername" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="نام کاربری" required>
                        <label for="regUsername" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">نام کاربری</label>
                        <i class="fas fa-user absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <div id="usernameCheck" class="absolute left-10 top-1/2 transform -translate-y-1/2 hidden z-10">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div id="usernameError" class="absolute left-10 top-1/2 transform -translate-y-1/2 hidden z-10">
                            <i class="fas fa-times-circle text-red-500"></i>
                        </div>
                    </div>

                    <!-- Email -->
                    <div class="input-group relative">
                        <input type="email" id="regEmail" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="ایمیل" required>
                        <label for="regEmail" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">ایمیل</label>
                        <i class="fas fa-envelope absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        <div id="emailCheck" class="absolute left-10 top-1/2 transform -translate-y-1/2 hidden z-10">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                        <div id="emailError" class="absolute left-10 top-1/2 transform -translate-y-1/2 hidden z-10">
                            <i class="fas fa-times-circle text-red-500"></i>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="input-group relative">
                        <input type="tel" id="regPhone" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="شماره تلفن">
                        <label for="regPhone" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">شماره تلفن</label>
                        <i class="fas fa-phone absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>

                    <!-- Location -->
                    <div class="grid grid-cols-2 gap-4">
                        <div class="input-group relative">
                            <select id="regProvince" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 select-custom appearance-none">
                                <option value="">انتخاب استان</option>
                            </select>
                            <label class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm transform -translate-y-6 scale-85">استان</label>
                        </div>
                        <div class="input-group relative">
                            <select id="regCity" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 select-custom appearance-none" disabled>
                                <option value="">انتخاب شهر</option>
                            </select>
                            <label class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm transform -translate-y-6 scale-85">شهر</label>
                        </div>
                    </div>

                    <!-- Programming Languages -->
                    <div>
                        <label class="block text-gray-700 font-medium mb-3 text-sm">زبان‌های برنامه‌نویسی مورد علاقه</label>
                        <div class="grid grid-cols-3 gap-2" id="languagesGrid">
                            <!-- Languages will be populated here -->
                        </div>
                        <p class="text-xs text-gray-500 mt-2">حداقل یک زبان انتخاب کنید</p>
                    </div>

                    <!-- Password -->
                    <div class="input-group relative">
                        <input type="password" id="regPassword" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent pl-12" placeholder="رمز عبور" required>
                        <label for="regPassword" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">رمز عبور</label>
                        <button type="button" onclick="togglePassword('regPassword', this)" class="absolute left-4 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                    </div>

                    <!-- Password Strength -->
                    <div id="passwordStrength" class="space-y-2">
                        <div class="flex space-x-1 space-x-reverse">
                            <div class="h-2 flex-1 bg-gray-200 rounded-full overflow-hidden">
                                <div id="strengthBar" class="h-full strength-bar rounded-full"></div>
                            </div>
                        </div>
                        <div class="flex justify-between text-xs">
                            <span id="strengthText" class="text-gray-500">قدرت رمز عبور</span>
                            <div class="flex space-x-4 space-x-reverse">
                                <span id="req1" class="text-gray-400">8+ کاراکتر</span>
                                <span id="req2" class="text-gray-400">حروف بزرگ</span>
                                <span id="req3" class="text-gray-400">اعداد</span>
                            </div>
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="input-group relative">
                        <input type="password" id="confirmPassword" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent pl-12" placeholder="تکرار رمز عبور" required>
                        <label for="confirmPassword" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">تکرار رمز عبور</label>
                        <button type="button" onclick="togglePassword('confirmPassword', this)" class="absolute left-4 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                            <i class="fas fa-eye"></i>
                        </button>
                        <div id="confirmCheck" class="absolute left-10 top-3 hidden">
                            <i class="fas fa-check-circle text-green-500"></i>
                        </div>
                    </div>

                    <!-- CAPTCHA -->
                    <div id="registerCaptcha" class="input-group relative hidden">
                        <input type="text" id="registerCaptchaAnswer" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="پاسخ کپچا">
                        <label id="registerCaptchaLabel" for="registerCaptchaAnswer" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">کپچا</label>
                        <input type="hidden" id="registerCaptchaToken" value="">
                        <button type="button" id="refreshRegisterCaptcha" class="absolute left-4 top-3 text-gray-400 hover:text-gray-600 transition-colors" title="تازه‌سازی کپچا">
                            <i class="fas fa-sync"></i>
                        </button>
                    </div>

                    <!-- Terms -->
                    <label class="flex items-start cursor-pointer">
                        <input type="checkbox" id="termsCheckbox" class="checkbox-custom ml-3 mt-1" required>
                        <span class="text-gray-600 text-sm leading-relaxed">
                            با <a href="#" class="text-blue-600 hover:underline">قوانین و مقررات</a> و 
                            <a href="#" class="text-blue-600 hover:underline">حریم خصوصی</a> موافقم
                        </span>
                    </label>

                    <!-- Next Step Button -->
                    <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-blue-600 text-white py-4 rounded-2xl font-semibold btn-hover ripple shadow-lg">
                        <span class="btn-text">
                            <i class="fas fa-paper-plane ml-2"></i>
                            ارسال کد تایید ایمیل
                        </span>
                        <span class="btn-loading">
                            <i class="fas fa-spinner fa-spin ml-2"></i>
                            در حال ارسال کد...
                        </span>
                    </button>
                    </form>
                </div>

                <!-- Step 2: Email Verification -->
                <div id="registerStep2" class="register-step hidden">
                    <div class="text-center mb-6">
                        <h3 class="text-xl font-bold text-gray-900 mb-2">ثبت نام - مرحله دوم</h3>
                        <p class="text-gray-600 text-sm">کد تایید ارسال شده به ایمیل خود را وارد کنید</p>
                        <div class="flex justify-center mt-4">
                            <div class="flex items-center space-x-2 space-x-reverse">
                                <div class="w-8 h-8 bg-green-500 text-white rounded-full flex items-center justify-center text-sm">
                                    <i class="fas fa-check text-xs"></i>
                                </div>
                                <div class="w-16 h-1 bg-green-500"></div>
                                <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center text-sm font-bold">2</div>
                            </div>
                        </div>
                    </div>

                    <form id="verificationFormElement" class="space-y-6">
                        <!-- Email Display -->
                        <div class="text-center bg-blue-50 p-4 rounded-xl">
                            <p class="text-gray-700 text-sm mb-2">کد تایید به این ایمیل ارسال شد:</p>
                            <p id="verificationEmail" class="font-bold text-blue-600"></p>
                            <p class="text-gray-600 text-xs mt-2">اگر ایمیل را نمی‌بینید، پوشه اسپم/هرزنامه یا Promotions را بررسی کنید.</p>
                        </div>

                        

                        <!-- Verification Code Input -->
                        <div class="input-group relative">
                            <input type="text" id="verificationCode" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent text-center text-2xl tracking-widest" placeholder="کد تایید 6 رقمی" maxlength="6" required>
                            <label for="verificationCode" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">کد تایید 6 رقمی</label>
                            <i class="fas fa-key absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                        </div>

                        <!-- Resend Code -->
                        <div class="text-center">
                            <p class="text-gray-600 text-sm mb-2">کد را دریافت نکردید؟</p>
                            <button type="button" id="resendCodeBtn" class="text-blue-600 hover:text-blue-800 font-medium transition-colors disabled:text-gray-400" disabled>
                                <span id="resendText">ارسال مجدد کد</span>
                                <span id="resendTimer" class="hidden">ارسال مجدد در <span id="timerCount">60</span> ثانیه</span>
                            </button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex space-x-3 space-x-reverse">
                            <button type="button" id="backToStep1" class="flex-1 bg-gray-200 text-gray-700 py-3 rounded-xl font-medium hover:bg-gray-300 transition-colors">
                                <i class="fas fa-arrow-right ml-2"></i>
                                بازگشت
                            </button>
                            <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-blue-600 text-white py-3 rounded-xl font-semibold btn-hover ripple shadow-lg">
                                <span class="btn-text">
                                    <i class="fas fa-user-check ml-2"></i>
                                    تایید و ثبت نام
                                </span>
                                <span class="btn-loading">
                                    <i class="fas fa-spinner fa-spin ml-2"></i>
                                    در حال تایید...
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-gray-500 text-sm">
                © 2025 سورس بان - تمامی حقوق محفوظ است
            </p>
            <div class="mt-4">
                <a href="index.php" class="text-blue-600 hover:text-blue-700 text-sm font-medium transition-colors">
                    <i class="fas fa-arrow-right ml-1"></i>
                    بازگشت به صفحه اصلی
                </a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("contextmenu", function (event) { event.preventDefault(); });
        // Iranian provinces and cities data
        const iranData = {
            'تهران': ['تهران', 'کرج', 'ورامین', 'شهریار', 'اسلامشهر', 'رباط کریم', 'بهارستان', 'ملارد', 'پاکدشت', 'دماوند'],
            'اصفهان': ['اصفهان', 'کاشان', 'نجف آباد', 'خمینی شهر', 'شاهین شهر', 'فولادشهر', 'زرین شهر', 'سمیرم', 'گلپایگان', 'نطنز'],
            'فارس': ['شیراز', 'مرودشت', 'کازرون', 'جهرم', 'فسا', 'داراب', 'لار', 'آباده', 'استهبان', 'نی ریز'],
            'خراسان رضوی': ['مشهد', 'نیشابور', 'سبزوار', 'تربت حیدریه', 'کاشمر', 'گناباد', 'تایباد', 'قوچان', 'چناران', 'درگز'],
            'آذربایجان شرقی': ['تبریز', 'مراغه', 'میانه', 'مرند', 'اهر', 'بناب', 'سراب', 'شبستر', 'آذرشهر', 'ملکان'],
            'خوزستان': ['اهواز', 'آبادان', 'خرمشهر', 'دزفول', 'اندیمشک', 'شوشتر', 'بهبهان', 'مسجد سلیمان', 'ایذه', 'شوش'],
            'کرمان': ['کرمان', 'رفسنجان', 'جیرفت', 'سیرجان', 'بم', 'زرند', 'کهنوج', 'بردسیر', 'راور', 'انار'],
            'گیلان': ['رشت', 'انزلی', 'لاهیجان', 'لنگرود', 'آستارا', 'تالش', 'رودبار', 'فومن', 'صومعه سرا', 'ماسال'],
            'مازندران': ['ساری', 'بابل', 'آمل', 'قائمشهر', 'گرگان', 'بابلسر', 'نوشهر', 'چالوس', 'تنکابن', 'رامسر'],
            'کردستان': ['سنندج', 'سقز', 'مریوان', 'بانه', 'قروه', 'کامیاران', 'بیجار', 'دیواندره', 'دهگلان', 'سروآباد'],
            'همدان': ['همدان', 'ملایر', 'نهاوند', 'تویسرکان', 'اسدآباد', 'بهار', 'کبودرآهنگ', 'رزن', 'فامنین', 'لالجین'],
            'یزد': ['یزد', 'اردکان', 'میبد', 'بافق', 'ابرکوه', 'تفت', 'مهریز', 'اشکذر', 'خاتم', 'زارچ'],
            'لرستان': ['خرم آباد', 'بروجرد', 'دورود', 'کوهدشت', 'الیگودرز', 'پل دختر', 'ازنا', 'نورآباد', 'دلفان', 'چگنی'],
            'کرمانشاه': ['کرمانشاه', 'اسلام آباد غرب', 'کنگاور', 'هرسین', 'پاوه', 'جوانرود', 'قصر شیرین', 'سنقر', 'صحنه', 'گیلان غرب'],
            'البرز': ['کرج', 'نظرآباد', 'ساوجبلاغ', 'طالقان', 'اشتهارد', 'هشتگرد', 'فردیس', 'محمدشهر', 'مهرشهر', 'گلشهر'],
            'قم': ['قم', 'جعفریه', 'کهک', 'دستجرد', 'سلفچگان', 'قنوات', 'جمکران', 'آبگرم', 'خلجستان', 'قمرود'],
            'زنجان': ['زنجان', 'ابهر', 'خدابنده', 'خرمدره', 'طارم', 'ماهنشان', 'سلطانیه', 'ایجرود', 'قیدار', 'هیدج']
        };

        const programmingLanguages = [
            { name: 'JavaScript', icon: '🟨' },
            { name: 'Python', icon: '🐍' },
            { name: 'Java', icon: '☕' },
            { name: 'C#', icon: '🔷' },
            { name: 'PHP', icon: '🐘' },
            { name: 'C++', icon: '⚡' },
            { name: 'Go', icon: '🐹' },
            { name: 'Rust', icon: '🦀' },
            { name: 'Swift', icon: '🍎' },
            { name: 'Kotlin', icon: '🎯' },
            { name: 'TypeScript', icon: '📘' },
            { name: 'Ruby', icon: '💎' },
            { name: 'Dart', icon: '🎯' },
            { name: 'HTML/CSS', icon: '🌐' },
            { name: 'React', icon: '⚛️' }
        ];

        // Initialize page
        document.addEventListener('DOMContentLoaded', function() {
            initializePage();
            setupEventListeners();
            
            // Load CAPTCHA for active tab only (regenerate for each load)
            if (window.location.hash === '#register') {
                switchTab('register');
                loadCaptcha('register');
            } else {
                switchTab('login');
                loadCaptcha('login');
            }
        });

        function initializePage() {
            loadProvinces();
            loadLanguages();
            setupFloatingLabels();
        }

        function setupEventListeners() {
            // Tab switching
            document.getElementById('loginTab').addEventListener('click', () => { switchTab('login'); loadCaptcha('login'); });
            document.getElementById('registerTab').addEventListener('click', () => { switchTab('register'); loadCaptcha('register'); });

            // Form submissions
            document.getElementById('loginFormElement').addEventListener('submit', handleLogin);
            document.getElementById('registerFormElement').addEventListener('submit', handleSendVerificationCode);
            document.getElementById('verificationFormElement').addEventListener('submit', handleVerification);

            // Registration step navigation
            document.getElementById('backToStep1').addEventListener('click', goBackToStep1);
            document.getElementById('resendCodeBtn').addEventListener('click', resendVerificationCode);

            // CAPTCHA refresh buttons
            document.getElementById('refreshLoginCaptcha')?.addEventListener('click', () => loadCaptcha('login'));
            document.getElementById('refreshRegisterCaptcha')?.addEventListener('click', () => loadCaptcha('register'));

            // Real-time validation
            document.getElementById('regUsername').addEventListener('input', debounce(checkUsername, 500));
            document.getElementById('regUsername').addEventListener('input', debounce(validateUsernameInput, 300));
            document.getElementById('regEmail').addEventListener('input', debounce(validateEmailInput, 300));
            document.getElementById('regPassword').addEventListener('input', checkPasswordStrength);
            document.getElementById('confirmPassword').addEventListener('input', checkPasswordMatch);
            document.getElementById('regProvince').addEventListener('change', loadCities);
            
            // Verification code input formatting
            document.getElementById('verificationCode').addEventListener('input', formatVerificationCode);

            // Input focus handlers for floating labels
            setupInputHandlers();
        }

        function setupFloatingLabels() {
            const inputs = document.querySelectorAll('.input-group input, .input-group select');
            inputs.forEach(input => {
                input.addEventListener('focus', () => {
                    input.parentElement.classList.add('focused');
                });
                
                input.addEventListener('blur', () => {
                    if (!input.value) {
                        input.parentElement.classList.remove('has-value');
                    } else {
                        input.parentElement.classList.add('has-value');
                    }
                    input.parentElement.classList.remove('focused');
                });

                // Check initial value
                if (input.value) {
                    input.parentElement.classList.add('has-value');
                }
            });
        }

        function setupInputHandlers() {
            const inputs = document.querySelectorAll('input, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    if (this.value) {
                        this.parentElement.classList.add('has-value');
                    } else {
                        this.parentElement.classList.remove('has-value');
                    }
                });
            });
        }

        function switchTab(tab) {
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');

            if (tab === 'login') {
                loginTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 bg-white text-gray-800 shadow-md';
                registerTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 text-gray-600 hover:text-gray-800';
                
                registerForm.classList.add('slide-out');
                setTimeout(() => {
                    registerForm.classList.add('hidden');
                    registerForm.classList.remove('slide-out');
                    loginForm.classList.remove('hidden');
                    loginForm.classList.add('slide-in');
                }, 300);
            } else {
                registerTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 bg-white text-gray-800 shadow-md';
                loginTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 text-gray-600 hover:text-gray-800';
                
                loginForm.classList.add('slide-out');
                setTimeout(() => {
                    loginForm.classList.add('hidden');
                    loginForm.classList.remove('slide-out');
                    registerForm.classList.remove('hidden');
                    registerForm.classList.add('slide-in');
                }, 300);
            }
        }

        function loadProvinces() {
            const select = document.getElementById('regProvince');
            Object.keys(iranData).forEach(province => {
                const option = document.createElement('option');
                option.value = province;
                option.textContent = province;
                select.appendChild(option);
            });
        }

        function loadCities() {
            const provinceSelect = document.getElementById('regProvince');
            const citySelect = document.getElementById('regCity');
            const selectedProvince = provinceSelect.value;

            citySelect.innerHTML = '<option value="">انتخاب شهر</option>';
            
            if (selectedProvince && iranData[selectedProvince]) {
                citySelect.disabled = false;
                iranData[selectedProvince].forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
                citySelect.parentElement.classList.add('has-value');
            } else {
                citySelect.disabled = true;
                citySelect.parentElement.classList.remove('has-value');
            }
        }

        function loadLanguages() {
            const grid = document.getElementById('languagesGrid');
            programmingLanguages.forEach(lang => {
                const tag = document.createElement('div');
                tag.className = 'language-tag cursor-pointer p-2 rounded-lg border-2 border-gray-200 text-center text-xs font-medium hover:border-blue-300 transition-all';
                tag.innerHTML = `
                    <div class="text-lg mb-1">${lang.icon}</div>
                    <div>${lang.name}</div>
                `;
                tag.addEventListener('click', () => toggleLanguage(tag, lang.name));
                grid.appendChild(tag);
            });
        }

        function toggleLanguage(element, langName) {
            element.classList.toggle('selected');
            if (element.classList.contains('selected')) {
                element.classList.remove('border-gray-200');
                element.classList.add('border-blue-500');
            } else {
                element.classList.add('border-gray-200');
                element.classList.remove('border-blue-500');
            }
        }

        async function checkUsername() {
            const username = document.getElementById('regUsername').value.trim();
            const usernameField = document.getElementById('regUsername');
            const usernameCheck = document.getElementById('usernameCheck');
            const usernameError = document.getElementById('usernameError');
            
            if (username.length < 1) {
                usernameCheck.classList.add('hidden');
                usernameError.classList.add('hidden');
                return;
            }

            // First validate format locally
            try {
                validateUsername(username);
            } catch (error) {
                usernameField.style.borderColor = '#ef4444';
                usernameCheck.classList.add('hidden');
                usernameError.classList.remove('hidden');
                usernameField.title = error.message;
                return;
            }

            try {
                const formData = new FormData();
                formData.append('action', 'check_username');
                formData.append('username', username);
                formData.append('csrf_token', getCsrfToken());

                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': getCsrfToken() },
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();
                
                if (result.ok) {
                    if (result.available) {
                        usernameField.style.borderColor = '#10b981';
                        usernameCheck.classList.remove('hidden');
                        usernameError.classList.add('hidden');
                        usernameField.title = 'نام کاربری در دسترس است';
                    } else {
                        usernameField.style.borderColor = '#ef4444';
                        usernameCheck.classList.add('hidden');
                        usernameError.classList.remove('hidden');
                        usernameField.title = 'این نام کاربری قبلاً ثبت شده است';
                    }
                } else {
                    usernameCheck.classList.add('hidden');
                    usernameError.classList.add('hidden');
                }
            } catch (error) {
                console.error('Error checking username:', error);
                usernameCheck.classList.add('hidden');
                usernameError.classList.add('hidden');
            }
        }

        function checkPasswordStrength() {
            const password = document.getElementById('regPassword').value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            const req1 = document.getElementById('req1');
            const req2 = document.getElementById('req2');
            const req3 = document.getElementById('req3');

            let score = 0;
            const checks = {
                length: password.length >= 8,
                uppercase: /[A-Z]/.test(password),
                number: /[0-9]/.test(password)
            };

            // Update requirements
            req1.className = checks.length ? 'text-green-500' : 'text-gray-400';
            req2.className = checks.uppercase ? 'text-green-500' : 'text-gray-400';
            req3.className = checks.number ? 'text-green-500' : 'text-gray-400';

            score = Object.values(checks).filter(Boolean).length;

            // Update strength bar
            const colors = ['bg-red-400', 'bg-yellow-400', 'bg-green-400'];
            const texts = ['ضعیف', 'متوسط', 'قوی'];
            const textColors = ['text-red-500', 'text-yellow-500', 'text-green-500'];

            strengthBar.className = `h-full strength-bar rounded-full ${colors[score - 1] || 'bg-gray-300'}`;
            strengthBar.style.width = `${(score / 3) * 100}%`;
            
            strengthText.className = `${textColors[score - 1] || 'text-gray-500'}`;
            strengthText.textContent = score > 0 ? texts[score - 1] : 'قدرت رمز عبور';
        }

        function checkPasswordMatch() {
            const password = document.getElementById('regPassword').value;
            const confirmPassword = document.getElementById('confirmPassword').value;
            const check = document.getElementById('confirmCheck');
            
            if (confirmPassword === '') {
                check.classList.add('hidden');
                return;
            }

            if (password === confirmPassword) {
                check.innerHTML = '<i class="fas fa-check-circle text-green-500"></i>';
                check.classList.remove('hidden');
            } else {
                check.innerHTML = '<i class="fas fa-times-circle text-red-500"></i>';
                check.classList.remove('hidden');
            }
        }

        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        function getCsrfToken() {
            try { return '<?php echo htmlspecialchars(csrf_get_token()); ?>'; } catch(e) { return ''; }
        }

        async function handleLogin(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Add loading state
            submitBtn.classList.add('loading');
            
            try {
                const identifier = document.getElementById('loginUsername').value.trim();
                const password = document.getElementById('loginPassword').value;
                
                if (!identifier || !password) {
                    throw new Error('لطفاً تمام فیلدها را پر کنید');
                }

                const formData = new FormData();
                formData.append('action', 'login');
                formData.append('identifier', identifier);
                formData.append('password', password);
                formData.append('csrf_token', getCsrfToken());
                // attach captcha if exists
                const lct = document.getElementById('loginCaptchaToken');
                const lca = document.getElementById('loginCaptchaAnswer');
                if (lct && lca && lct.value) {
                    formData.append('captcha_token', lct.value);
                    formData.append('captcha_value', lca.value.trim());
                }

                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': getCsrfToken() },
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();
                
                if (result.ok) {
                    showSuccess('ورود موفقیت‌آمیز بود!');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    throw new Error(result.error || 'خطا در ورود');
                }
            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.classList.remove('loading');
            }
        }

        // Validation functions
        function validateUsername(username) {
            if (!username || username.trim().length === 0) {
                throw new Error('نام کاربری الزامی است');
            }
            return true;
        }
        
        function validateEmail(email) {
            // Only @gmail.com addresses
            if (!email.endsWith('@gmail.com')) {
                throw new Error('فقط آدرس‌های ایمیل @gmail.com مجاز هستند');
            }
            return true;
        }
        
        function checkProfanity(text) {
            const profanityWords = ['fuck', 'shit', 'damn', 'bitch', 'bastard', 'ass', 'hell'];
            const lowerText = text.toLowerCase();
            
            for (let word of profanityWords) {
                if (lowerText.includes(word)) {
                    throw new Error('متن حاوی کلمات نامناسب است و مجاز نیست');
                }
            }
            return true;
        }
        
        function validateUsernameInput() {
            const username = document.getElementById('regUsername').value.trim();
            const usernameField = document.getElementById('regUsername');
            const usernameCheck = document.getElementById('usernameCheck');
            const usernameError = document.getElementById('usernameError');
            
            if (username.length === 0) {
                usernameField.style.borderColor = '#d1d5db';
                usernameCheck.classList.add('hidden');
                usernameError.classList.add('hidden');
                usernameField.title = '';
                return;
            }
            
            try {
                validateUsername(username);
                usernameField.style.borderColor = '#10b981'; // Green
                usernameCheck.classList.remove('hidden');
                usernameError.classList.add('hidden');
                usernameField.title = 'نام کاربری معتبر است';
            } catch (error) {
                usernameField.style.borderColor = '#ef4444'; // Red
                usernameCheck.classList.add('hidden');
                usernameError.classList.remove('hidden');
                usernameField.title = error.message;
            }
        }
        
        function validateEmailInput() {
            const email = document.getElementById('regEmail').value.trim();
            const emailField = document.getElementById('regEmail');
            const emailCheck = document.getElementById('emailCheck');
            const emailError = document.getElementById('emailError');
            
            if (email.length === 0) {
                emailField.style.borderColor = '#d1d5db';
                emailCheck.classList.add('hidden');
                emailError.classList.add('hidden');
                emailField.title = '';
                return;
            }
            
            try {
                validateEmail(email);
                checkProfanity(email);
                emailField.style.borderColor = '#10b981'; // Green
                emailCheck.classList.remove('hidden');
                emailError.classList.add('hidden');
                emailField.title = 'ایمیل معتبر است';
            } catch (error) {
                emailField.style.borderColor = '#ef4444'; // Red
                emailCheck.classList.add('hidden');
                emailError.classList.remove('hidden');
                emailField.title = error.message;
            }
        }

        // Store registration data temporarily
        let registrationData = {};

        async function handleSendVerificationCode(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            // Add loading state
            submitBtn.classList.add('loading');
            
            try {
                const firstName = document.getElementById('firstName').value.trim();
                const lastName = ''; // Hidden field now
                const username = document.getElementById('regUsername').value.trim();
                const email = document.getElementById('regEmail').value.trim();
                const phone = document.getElementById('regPhone').value.trim();
                const province = document.getElementById('regProvince').value;
                const city = document.getElementById('regCity').value;
                
                // Validate username (minimal)
                validateUsername(username);
                
                // Validate email
                validateEmail(email);
                checkProfanity(email);
                
                // Check profanity in name
                checkProfanity(firstName);
                
                // Validate form
                const selectedLanguages = document.querySelectorAll('.language-tag.selected');
                if (selectedLanguages.length === 0) {
                    throw new Error('لطفاً حداقل یک زبان برنامه‌نویسی انتخاب کنید');
                }

                const password = document.getElementById('regPassword').value;
                const confirmPassword = document.getElementById('confirmPassword').value;
                
                if (password !== confirmPassword) {
                    throw new Error('رمز عبور و تکرار آن یکسان نیستند');
                }
                
                const languages = Array.from(selectedLanguages).map(el => 
                    el.querySelector('div:last-child').textContent
                );

                // Store registration data temporarily
                registrationData = {
                    name: `${firstName} ${lastName}`,
                    username: username,
                    email: email,
                    phone: phone,
                    province: province,
                    city: city,
                    password: password,
                    programming_languages: languages
                };

                const formData = new FormData();
                formData.append('action', 'send_verification_code');
                formData.append('email', email);
                formData.append('username', username);
                formData.append('csrf_token', getCsrfToken());
                // attach captcha
                const rct = document.getElementById('registerCaptchaToken');
                const rca = document.getElementById('registerCaptchaAnswer');
                if (rct && rca && rct.value) {
                    formData.append('captcha_token', rct.value);
                    formData.append('captcha_value', rca.value.trim());
                }

                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': getCsrfToken() },
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();
                
                if (result.ok) {
                    // Move to step 2
                    showStep2(email);
                    showSuccess('کد تایید به ایمیل شما ارسال شد. لطفاً اسپم/هرزنامه را هم بررسی کنید.');
                } else {
                    throw new Error(result.error || 'خطا در ارسال کد تایید');
                }
            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.classList.remove('loading');
            }
        }

        function showSuccess(message) {
            showToast(message, 'success');
        }

        function showError(message) {
            showToast(message, 'error');
        }

        function showToast(message, type) {
            // Remove existing toasts
            const existingToasts = document.querySelectorAll('.toast');
            existingToasts.forEach(toast => toast.remove());

            const toast = document.createElement('div');
            toast.className = `toast fixed top-4 right-4 px-6 py-4 rounded-xl shadow-lg z-50 transform translate-x-full transition-transform duration-300 ${
                type === 'success' ? 'bg-green-500' : 'bg-red-500'
            } text-white`;
            
            toast.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'} ml-3"></i>
                    <span>${message}</span>
                </div>
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => toast.classList.remove('translate-x-full'), 100);
            setTimeout(() => {
                toast.classList.add('translate-x-full');
                setTimeout(() => toast.remove(), 300);
            }, 4000);
        }

        // Step transition functions
        async function loadCaptcha(form) {
            try {
                const res = await fetch('api/auth.php?action=captcha&t=' + Date.now(), { cache: 'no-store' });
                const data = await res.json();
                if (!data.ok || !data.captcha) {
                    // Hide captcha if disabled
                    if (form === 'login') document.getElementById('loginCaptcha').classList.add('hidden');
                    if (form === 'register') document.getElementById('registerCaptcha').classList.add('hidden');
                    return;
                }
                if (form === 'login') {
                    document.getElementById('loginCaptcha').classList.remove('hidden');
                    document.getElementById('loginCaptchaLabel').textContent = data.captcha.question;
                    document.getElementById('loginCaptchaToken').value = data.captcha.token;
                    document.getElementById('loginCaptchaAnswer').value = '';
                } else if (form === 'register') {
                    document.getElementById('registerCaptcha').classList.remove('hidden');
                    document.getElementById('registerCaptchaLabel').textContent = data.captcha.question;
                    document.getElementById('registerCaptchaToken').value = data.captcha.token;
                    document.getElementById('registerCaptchaAnswer').value = '';
                }
            } catch (e) {
                // On failure, keep CAPTCHA hidden
            }
        }
        function showStep2(email) {
            const step1 = document.getElementById('registerStep1');
            const step2 = document.getElementById('registerStep2');
            const emailDisplay = document.getElementById('verificationEmail');
            
            emailDisplay.textContent = email;
            
            step1.classList.add('fade-out');
            setTimeout(() => {
                step1.classList.add('hidden');
                step2.classList.remove('hidden');
                step2.classList.add('fade-in');
                startResendTimer();
            }, 300);
        }

        function goBackToStep1() {
            const step1 = document.getElementById('registerStep1');
            const step2 = document.getElementById('registerStep2');
            
            step2.classList.add('fade-out');
            setTimeout(() => {
                step2.classList.add('hidden');
                step1.classList.remove('hidden');
                step1.classList.add('fade-in');
                // Clear verification code
                document.getElementById('verificationCode').value = '';
            }, 300);
        }

        // Verification code formatting
        function formatVerificationCode(e) {
            let value = e.target.value.replace(/[^0-9]/g, '');
            if (value.length > 6) value = value.slice(0, 6);
            e.target.value = value;
            
            // Add visual feedback based on length
            const field = e.target;
            if (value.length === 6) {
                field.style.borderColor = '#10b981'; // Green
                field.style.backgroundColor = '#f0fdf4'; // Light green background
            } else if (value.length > 0) {
                field.style.borderColor = '#f59e0b'; // Orange  
                field.style.backgroundColor = '#fffbeb'; // Light orange background
            } else {
                field.style.borderColor = '#d1d5db'; // Gray
                field.style.backgroundColor = '#ffffff'; // White background
            }
        }

        // Timer for resend code
        let resendTimer = null;
        function startResendTimer() {
            const resendBtn = document.getElementById('resendCodeBtn');
            const resendText = document.getElementById('resendText');
            const resendTimerSpan = document.getElementById('resendTimer');
            const timerCount = document.getElementById('timerCount');
            
            let timeLeft = 60;
            resendBtn.disabled = true;
            resendText.classList.add('hidden');
            resendTimerSpan.classList.remove('hidden');
            
            resendTimer = setInterval(() => {
                timeLeft--;
                timerCount.textContent = timeLeft;
                
                if (timeLeft <= 0) {
                    clearInterval(resendTimer);
                    resendBtn.disabled = false;
                    resendText.classList.remove('hidden');
                    resendTimerSpan.classList.add('hidden');
                }
            }, 1000);
        }

        // Resend verification code
        async function resendVerificationCode() {
            try {
                const formData = new FormData();
                formData.append('action', 'send_verification_code');
                formData.append('email', registrationData.email);
                formData.append('username', registrationData.username);
                formData.append('csrf_token', getCsrfToken());
                // attach captcha again if visible
                const rct = document.getElementById('registerCaptchaToken');
                const rca = document.getElementById('registerCaptchaAnswer');
                if (rct && rca && rct.value) {
                    formData.append('captcha_token', rct.value);
                    formData.append('captcha_value', rca.value.trim());
                }

                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': getCsrfToken() },
                    credentials: 'same-origin',
                    body: formData
                });

                const result = await response.json();
                
                if (result.ok) {
                    showSuccess('کد تایید مجدداً ارسال شد. اگر ندیدید، اسپم/هرزنامه را چک کنید.');
                    startResendTimer();
                    // Refresh captcha for next action to avoid stale token
                    loadCaptcha('register');
                } else {
                    throw new Error(result.error || 'خطا در ارسال مجدد کد');
                }
            } catch (error) {
                showError(error.message);
            }
        }

        // Handle verification and final registration
        async function handleVerification(e) {
            e.preventDefault();
            const form = e.target;
            const submitBtn = form.querySelector('button[type="submit"]');
            
            submitBtn.classList.add('loading');
            
            try {
                const verificationCode = document.getElementById('verificationCode').value.trim();
                
                if (verificationCode.length !== 6) {
                    throw new Error('کد تایید باید 6 رقم باشد');
                }

                const formData = new FormData();
                formData.append('action', 'verify_and_register');
                formData.append('verification_code', verificationCode);
                formData.append('email', registrationData.email);
                formData.append('csrf_token', getCsrfToken());
                
                // Add all registration data
                Object.keys(registrationData).forEach(key => {
                    if (key === 'programming_languages') {
                        registrationData[key].forEach(lang => {
                            formData.append('programming_languages[]', lang);
                        });
                    } else {
                        formData.append(key, registrationData[key]);
                    }
                });

                const response = await fetch('api/auth.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();
                
                if (result.ok) {
                    showSuccess('ثبت نام با موفقیت تکمیل شد!');
                    setTimeout(() => {
                        window.location.href = 'index.php';
                    }, 1500);
                } else {
                    throw new Error(result.error || 'خطا در تایید کد');
                }
            } catch (error) {
                showError(error.message);
            } finally {
                submitBtn.classList.remove('loading');
            }
        }

        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Google OAuth
        <?php if (defined('GOOGLE_OAUTH_ENABLE') && GOOGLE_OAUTH_ENABLE): ?>
        document.getElementById('googleLoginBtn')?.addEventListener('click', function() {
            const clientId = '<?php echo GOOGLE_CLIENT_ID; ?>';
            const redirectUri = '<?php echo GOOGLE_REDIRECT_URI; ?>';
            const scope = 'email profile';
            const state = 'google_oauth_' + Math.random().toString(36).substr(2, 9);
            
            const googleAuthUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' +
                'client_id=' + encodeURIComponent(clientId) +
                '&redirect_uri=' + encodeURIComponent(redirectUri) +
                '&scope=' + encodeURIComponent(scope) +
                '&response_type=code' +
                '&state=' + encodeURIComponent(state) +
                '&access_type=offline' +
                '&prompt=select_account';
            
            window.location.href = googleAuthUrl;
        });
        <?php endif; ?>
    </script>
</body>
</html>
