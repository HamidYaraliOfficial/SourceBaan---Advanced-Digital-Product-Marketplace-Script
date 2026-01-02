<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/ads.php';
require_once __DIR__ . '/includes/utils.php';

// Security Headers
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Content-Security-Policy: default-src \'self\'; script-src \'self\' \'unsafe-inline\' https://cdn.tailwindcss.com https://cdnjs.cloudflare.com; style-src \'self\' \'unsafe-inline\' https://fonts.googleapis.com https://cdnjs.cloudflare.com; font-src \'self\' https://fonts.gstatic.com https://cdnjs.cloudflare.com; img-src \'self\' data: https://*; connect-src \'self\' https://api.majidapi.ir https://api.fast-creat.ir;');

// Anti-caching headers for security
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Remove server signature
if (function_exists('header_remove')) {
    header_remove('X-Powered-By');
    header_remove('Server');
}

// Get user session
$currentUser = current_user();
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>دستیار هوش مصنوعی GPT-4 و Copilot - چت آنلاین رایگان | سورس بان</title>
    <meta name="description" content="چت رایگان با هوش مصنوعی GPT-4 و GitHub Copilot. کمک در برنامه‌نویسی، کدنویسی، دیباگ و حل مسائل فنی. دسترسی آسان و بدون محدودیت.">
    <meta name="keywords" content="هوش مصنوعی رایگان, چت GPT-4, GitHub Copilot, AI برنامه‌نویسی, دستیار کدنویسی, چت بات هوشمند, کمک برنامه‌نویسی آنلاین, سورس بان">
    <meta name="author" content="تیم سورس بان">
    <link rel="canonical" href="https://sourcebaan.ir/ai.php">
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="language" content="fa-IR">
    <meta name="distribution" content="global">
    <meta name="rating" content="general">
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
    <link rel="icon" type="image/png" sizes="32x32" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    <link rel="preconnect" href="https://fonts.googleapis.com"/>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com"/>
    <link rel="preconnect" href="https://cdn.tailwindcss.com"/>
    <link rel="preconnect" href="https://api.majidapi.ir"/>
    <link rel="preconnect" href="https://api.fast-creat.ir"/>
    <link rel="apple-touch-icon" href="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj4KICA8ZGVmcz4KICAgIDxsaW5lYXJHcmFkaWVudCBpZD0iZ3JhZCIgeDE9IjAlIiB5MT0iMCUiIHgyPSIxMDAlIiB5Mj0iMTAwJSI+CiAgICAgIDxzdG9wIG9mZnNldD0iMCUiIHN0eWxlPSJzdG9wLWNvbG9yOiMzYjgyZjY7c3RvcC1vcGFjaXR5OjEiIC8+CiAgICAgIDxzdG9wIG9mZnNldD0iMTAwJSIgc3R5bGU9InN0b3AtY29sb3I6IzFkNGVkODtzdG9wLW9wYWNpdHk6MSIgLz4KICAgIDwvbGluZWFyR3JhZGllbnQ+CiAgPC9kZWZzPgogIDxyZWN0IHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgcng9IjYiIGZpbGw9InVybCgjZ3JhZCkiLz4KICA8dGV4dCB4PSIxNiIgeT0iMjIiIGZvbnQtZmFtaWx5PSJBcmlhbCwgc2Fucy1zZXJpZiIgZm9udC1zaXplPSIxOCIgZm9udC13ZWlnaHQ9ImJvbGQiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IndoaXRlIj5TPC90ZXh0PgogIDxjaXJjbGUgY3g9IjgiIGN5PSI4IiByPSIyIiBmaWxsPSJ3aGl0ZSIgb3BhY2l0eT0iMC44Ii8+CiAgPGNpcmNsZSBjeD0iMjQiIGN5PSI4IiByPSIxLjUiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjYiLz4KICA8Y2lyY2xlIGN4PSI4IiBjeT0iMjQiIHI9IjEiIGZpbGw9IndoaXRlIiBvcGFjaXR5PSIwLjQiLz4KPC9zdmc+">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="سورس بان">
    <meta property="og:url" content="https://sourcebaan.ir/ai.php">
    <meta property="og:title" content="دستیار هوش مصنوعی GPT-4 و Copilot - چت رایگان">
    <meta property="og:description" content="چت رایگان با GPT-4 و GitHub Copilot. کمک در برنامه‌نویسی، کدنویسی و حل مسائل فنی. دسترسی آسان بدون محدودیت.">
    <meta property="og:image" content="https://sourcebaan.ir/favicon.svg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="دستیار هوش مصنوعی سورس بان">
    <meta property="og:locale" content="fa_IR">
    
    <!-- Twitter -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:site" content="@SourceBaan">
    <meta name="twitter:creator" content="@SourceBaan">
    <meta name="twitter:url" content="https://sourcebaan.ir/ai.php">
    <meta name="twitter:title" content="دستیار هوش مصنوعی GPT-4 و Copilot - چت رایگان">
    <meta name="twitter:description" content="چت رایگان با GPT-4 و GitHub Copilot. کمک در برنامه‌نویسی و کدنویسی. دسترسی آسان و سریع.">
    <meta name="twitter:image" content="https://sourcebaan.ir/favicon.svg">
    <meta name="twitter:image:alt" content="دستیار هوش مصنوعی سورس بان">

    <!-- Structured Data -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": ["WebApplication", "SoftwareApplication"],
      "name": "دستیار هوش مصنوعی سورس بان",
      "alternateName": "SourceBaan AI Assistant",
      "url": "https://sourcebaan.ir/ai.php",
      "description": "چت رایگان با هوش مصنوعی GPT-4 و GitHub Copilot. دستیار هوشمند برای کدنویسی، برنامه‌نویسی و حل مسائل فنی",
      "applicationCategory": ["DeveloperApplication", "UtilityApplication"],
      "applicationSubCategory": "AI Assistant",
      "operatingSystem": "Web",
      "browserRequirements": "Requires JavaScript",
      "softwareVersion": "1.0",
      "releaseNotes": "پشتیبانی از GPT-4 و GitHub Copilot",
      "featureList": [
        "چت با GPT-4",
        "دستیار برنامه‌نویسی Copilot", 
        "کمک در کدنویسی",
        "پاسخ به سوالات فنی",
        "رابط کاربری فارسی"
      ],
      "offers": {
        "@type": "Offer",
        "price": "0",
        "priceCurrency": "IRR",
        "availability": "https://schema.org/InStock"
      },
      "inLanguage": "fa-IR",
      "accessibilityAPI": ["ARIA"],
      "accessibilityControl": ["fullKeyboardControl", "fullMouseControl", "fullTouchControl"],
      "accessibilityFeature": ["readingOrder", "structuralNavigation"],
      "accessibilityHazard": "none",
      "publisher": {
        "@type": "Organization",
        "name": "SourceBaan",
        "alternateName": "سورس بان",
        "url": "https://sourcebaan.ir/",
        "description": "بزرگترین مرجع سورس کدهای متن باز ایران",
        "sameAs": [
          "https://t.me/SourceBaan",
          "https://github.com/SourceBaan"
        ]
      },
      "author": {
        "@type": "Organization", 
        "name": "تیم سورس بان"
      },
      "mainEntity": {
        "@type": "FAQPage",
        "mainEntity": [
          {
            "@type": "Question",
            "name": "آیا استفاده از این هوش مصنوعی رایگان است؟",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "بله، استفاده از دستیار هوش مصنوعی سورس بان کاملاً رایگان است."
            }
          },
          {
            "@type": "Question", 
            "name": "چه نوع سوالاتی می‌توانم بپرسم؟",
            "acceptedAnswer": {
              "@type": "Answer",
              "text": "می‌توانید سوالات مربوط به برنامه‌نویسی، کدنویسی، دیباگ و مسائل فنی بپرسید."
            }
          }
        ]
      }
    }
    </script>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        /* Modern gradient backgrounds */
        .gradient-ai { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        /* Modern White Glassmorphism Design */
        .modern-glassmorphism {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.18);
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.37),
                0 2px 16px rgba(31, 38, 135, 0.2),
                inset 0 1px 1px rgba(255, 255, 255, 0.9);
        }
        
        .modern-glassmorphism::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, 
                rgba(255, 255, 255, 0.1) 0%,
                transparent 50%,
                rgba(255, 255, 255, 0.05) 100%);
            border-radius: inherit;
            pointer-events: none;
        }
        .gradient-gpt { 
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 50%, #ff4757 100%);
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }
        .gradient-copilot { 
            background: linear-gradient(135deg, #4facfe 0%, #00c6fb 50%, #0084ff 100%);
            box-shadow: 0 10px 30px rgba(79, 172, 254, 0.3);
        }
        
        /* Optimized navigation - removed heavy backdrop-filter */
        .modern-nav {
            background: rgba(255, 255, 255, 0.98);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
        }
        
        /* Enhanced chat interface */
        .chat-container {
            height: 65vh;
            min-height: 500px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            position: relative;
            overflow: hidden;
        }
        
        /* Simplified chat container background for better performance */
        .chat-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(248, 250, 252, 0.8) 0%, rgba(241, 245, 249, 0.9) 100%);
            pointer-events: none;
        }
        
        .chat-message {
            animation: slideInMessage 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }
        
        @keyframes slideInMessage {
            from {
                opacity: 0;
                transform: translateY(30px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Enhanced AI selector cards */
        .ai-selector {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }
        
        .ai-selector::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }
        
        .ai-selector:hover::before {
            opacity: 1;
        }
        
        .ai-selector.active {
            transform: translateY(-12px) scale(1.03);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.2);
        }
        
        .ai-selector.active .bg-white {
            border: 3px solid;
            border-image: linear-gradient(45deg, #667eea, #764ba2, #f093fb, #f5576c) 1;
        }
        
        .ai-selector:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }
        
        .ai-selector:hover .bg-white {
            border-color: rgba(102, 126, 234, 0.3);
        }
        
        /* Optimized message bubbles - removed heavy backdrop-filter */
        .user-message {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.2);
            border-radius: 20px 20px 4px 20px;
            position: relative;
        }
        
        .ai-message {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 20px 20px 20px 4px;
            position: relative;
        }
        
        .copy-button {
            position: absolute;
            top: 8px;
            left: 8px;
            background: rgba(255, 255, 255, 0.95);
            border: none;
            border-radius: 6px;
            padding: 4px 8px;
            font-size: 12px;
            color: #6b7280;
            cursor: pointer;
            opacity: 0;
            transition: all 0.3s ease;
        }
        
        .ai-message:hover .copy-button {
            opacity: 1;
        }
        
        .copy-button:hover {
            background: rgba(255, 255, 255, 1);
            color: #374151;
            transform: scale(1.05);
        }
        
        /* Enhanced typing indicator */
        .typing-indicator {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 12px 16px;
        }
        
        .typing-indicator div {
            width: 10px;
            height: 10px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            animation: typing 1.8s infinite ease-in-out;
        }
        
        .typing-indicator div:nth-child(1) { animation-delay: -0.32s; }
        .typing-indicator div:nth-child(2) { animation-delay: -0.16s; }
        .typing-indicator div:nth-child(3) { animation-delay: 0s; }
        
        @keyframes typing {
            0%, 60%, 100% { 
                transform: translateY(0) scale(1); 
                opacity: 0.5;
            }
            30% { 
                transform: translateY(-10px) scale(1.2); 
                opacity: 1;
            }
        }
        
        /* Enhanced code syntax highlighting */
        .message-content pre {
            background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
            color: #f9fafb;
            padding: 1.5rem;
            border-radius: 12px;
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
            position: relative;
        }
        
        .message-content pre::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 30px;
            background: linear-gradient(90deg, #ef4444, #f97316, #eab308, #22c55e, #06b6d4, #3b82f6, #8b5cf6);
            border-radius: 12px 12px 0 0;
            opacity: 0.8;
        }
        
        .message-content code {
            background: rgba(99, 102, 241, 0.1);
            color: #6366f1;
            padding: 0.3rem 0.6rem;
            border-radius: 6px;
            font-size: 0.875rem;
            border: 1px solid rgba(99, 102, 241, 0.2);
        }
        
        .message-content pre code {
            background: transparent;
            color: inherit;
            padding: 0;
            border: none;
        }
        
        /* Enhanced rate limiting notice */
        .rate-limit-notice {
            background: linear-gradient(135deg, #fef3c7, #fde68a, #f59e0b);
            border: 2px solid #f59e0b;
            box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0%, 100% { 
                box-shadow: 0 4px 20px rgba(245, 158, 11, 0.3);
            }
            50% { 
                box-shadow: 0 6px 30px rgba(245, 158, 11, 0.5);
            }
        }
        
        /* Optimized input styling - removed backdrop-filter */
        .message-input {
            background: rgba(255, 255, 255, 0.98);
            border: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .message-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: rgba(255, 255, 255, 1);
        }
        
        /* Send button enhancement */
        .send-button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .send-button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .send-button:hover::before {
            left: 100%;
        }
        
        .send-button:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        /* Smooth scroll */
        .chat-messages {
            scroll-behavior: smooth;
        }
        
        /* Message appear animation */
        .message-appear {
            animation: messageAppear 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        
        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(20px) scale(0.95);
                filter: blur(2px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }
        
        /* Custom scrollbar */
        .chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        
        .chat-messages::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 3px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        
        .chat-messages::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(135deg, #5a67d8, #6b46c1);
        }
    </style>
</head>
<body class="bg-gray-50">
    
    <!-- Modern Enhanced Header -->
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
    <main class="container mx-auto px-4 py-8 max-w-6xl">
        <!-- Hero Section - Enhanced -->
        <div class="relative mb-12 overflow-hidden">
            <!-- Simplified Background - removed heavy blur animations -->
            <div class="absolute inset-0 -z-10 opacity-30">
                <div class="absolute top-10 left-10 w-40 h-40 bg-purple-200 rounded-full"></div>
                <div class="absolute top-10 right-10 w-40 h-40 bg-yellow-200 rounded-full"></div>
                <div class="absolute -bottom-8 left-20 w-40 h-40 bg-pink-200 rounded-full"></div>
            </div>
            
            <div class="relative text-center">
                <!-- Modern Glassmorphism Hero Card -->
                <div class="modern-glassmorphism text-gray-800 rounded-3xl p-8 mb-8 relative overflow-hidden shadow-2xl backdrop-blur-xl border border-white/10">
                    <!-- Subtle Background Pattern -->
                    <div class="absolute inset-0 opacity-10">
                        <div class="absolute top-8 right-8 w-24 h-24 bg-gradient-to-br from-cyan-400 to-blue-500 rounded-full blur-xl"></div>
                        <div class="absolute bottom-8 left-8 w-20 h-20 bg-gradient-to-tr from-blue-400 to-teal-500 rounded-full blur-xl"></div>
                        <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 bg-gradient-to-br from-indigo-400 to-cyan-500 rounded-full blur-2xl opacity-30"></div>
                    </div>
                    
                    <!-- Floating particles -->
                    <div class="absolute top-20 left-20 w-2 h-2 bg-blue-400/60 rounded-full animate-ping"></div>
                    <div class="absolute top-32 right-24 w-1 h-1 bg-purple-400/50 rounded-full animate-pulse"></div>
                    <div class="absolute bottom-24 right-20 w-3 h-3 bg-indigo-400/40 rounded-full animate-bounce"></div>
                    
                    <div class="relative z-10">
                        <!-- Refined AI Icon -->
                        <div class="flex justify-center mb-6">
                            <div class="relative group">
                                <!-- Main Icon Container -->
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center shadow-xl transform group-hover:scale-105 transition-all duration-300 border border-gray-200">
                                    <i class="fas fa-brain text-4xl text-white"></i>
                                </div>
                                
                                <!-- Subtle floating dots -->
                                <div class="absolute -top-2 -right-2 w-3 h-3 bg-blue-500 rounded-full shadow-lg"></div>
                                <div class="absolute -bottom-1 -left-1 w-2 h-2 bg-purple-500 rounded-full shadow-lg"></div>
                                <div class="absolute top-1/3 -right-3 w-1 h-1 bg-indigo-500 rounded-full shadow-lg"></div>
                            </div>
                        </div>
                        
                        <!-- Modern Typography -->
                        <h1 class="text-2xl md:text-4xl lg:text-5xl font-bold mb-6 bg-gradient-to-r from-gray-900 via-blue-800 to-purple-800 bg-clip-text text-transparent leading-tight text-center">
                            دستیار هوش مصنوعی
                        </h1>
                        
                        <!-- Refined Description -->
                        <div class="space-y-4 mb-8">
                            <div class="max-w-3xl mx-auto text-center">
                                <p class="text-base md:text-lg lg:text-xl font-medium leading-relaxed mb-3 text-gray-700">
                                    با <span class="font-bold text-blue-600 px-2 py-1 bg-blue-50 rounded-lg border border-blue-200">GPT-4</span> و <span class="font-bold text-purple-600 px-2 py-1 bg-purple-50 rounded-lg border border-purple-200">GitHub Copilot</span> چت کنید
                                </p>
                                <p class="text-sm md:text-base text-gray-600 max-w-2xl mx-auto leading-relaxed">
                                    از قدرت هوش مصنوعی برای حل مسائل برنامه‌نویسی، کدنویسی و یادگیری استفاده کنید
                                </p>
                            </div>
                        </div>
                        
                        <!-- Modern Feature badges -->
                        <div class="flex flex-wrap justify-center gap-3 mb-8">
                            <div class="group px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm font-medium transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:shadow-md">
                                <span class="text-lg mr-2">🚀</span>
                                <span class="text-gray-700">رایگان و نامحدود</span>
                            </div>
                            <div class="group px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm font-medium transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:shadow-md">
                                <span class="text-lg mr-2">⚡</span>
                                <span class="text-gray-700">پاسخ فوری</span>
                            </div>
                            <div class="group px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm font-medium transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:shadow-md">
                                <span class="text-lg mr-2">🧠</span>
                                <span class="text-gray-700">هوش پیشرفته</span>
                            </div>
                            <div class="group px-4 py-2 bg-gray-50 hover:bg-gray-100 rounded-xl text-sm font-medium transition-all duration-300 border border-gray-200 hover:border-gray-300 hover:shadow-md">
                                <span class="text-lg mr-2">🔒</span>
                                <span class="text-gray-700">کاملاً امن</span>
                            </div>
                        </div>
                        
                        <!-- Modern Call to Action -->
                        <div class="text-center">
                            <div class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 text-white rounded-xl font-medium transition-all duration-300 cursor-pointer shadow-lg hover:shadow-xl group">
                                <i class="fas fa-arrow-down ml-2 text-base group-hover:animate-bounce"></i>
                                <span>شروع گفتگو</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Enhanced Rate Limiting Notice -->
                <div class="rate-limit-notice rounded-2xl p-6 max-w-3xl mx-auto shadow-lg border border-orange-200 relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-r from-orange-50 to-yellow-50 opacity-50"></div>
                    <div class="relative z-10">
                        <div class="flex items-center justify-center mb-2">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center ml-3">
                                <i class="fas fa-clock text-orange-600 text-lg"></i>
                            </div>
                            <div class="text-right">
                                <h3 class="font-bold text-orange-800 text-lg">محدودیت زمانی</h3>
                                <p class="text-orange-700 text-sm">برای حفظ کیفیت سرویس</p>
                            </div>
                        </div>
                        <div class="bg-white bg-opacity-60 rounded-lg p-3 text-center">
                            <span class="text-orange-800 font-bold text-lg">⏱️ هر ۱۰ ثانیه یک درخواست</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- AI Selection - Enhanced -->
        <div class="grid md:grid-cols-2 gap-8 mb-12">
            <!-- Copilot Card -->
            <div id="copilot-card" class="ai-selector cursor-pointer group" onclick="selectAI('copilot')">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-5">
                        <div class="absolute top-4 right-4 text-6xl">💻</div>
                        <div class="absolute bottom-4 left-4 text-4xl">⚡</div>
                    </div>
                    
                    <!-- Header with Icon -->
                    <div class="gradient-copilot text-white p-6 rounded-2xl mb-6 relative overflow-hidden shadow-lg">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white bg-opacity-10 rounded-full -mr-12 -mt-12"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center ml-3">
                                        <i class="fas fa-code text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black">GitHub Copilot</h3>
                                        <p class="text-blue-100 text-sm font-medium">Powered by OpenAI</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 bg-white bg-opacity-20 rounded-full text-xs font-bold">AI</span>
                                </div>
                            </div>
                            <p class="text-blue-100 text-base leading-relaxed">
                                دستیار هوشمند برنامه‌نویسی که کدهای شما را بهبود می‌دهد
                            </p>
                        </div>
                    </div>
                    
                    <!-- Features -->
                    <div class="space-y-4 mb-6">
                        <div class="flex items-center p-3 bg-blue-50 rounded-xl group-hover:bg-blue-100 transition-colors">
                            <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-code text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">مخصوص کدنویسی و برنامه‌نویسی</span>
                        </div>
                        <div class="flex items-center p-3 bg-green-50 rounded-xl group-hover:bg-green-100 transition-colors">
                            <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-bolt text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">پاسخ‌های سریع و دقیق</span>
                        </div>
                        <div class="flex items-center p-3 bg-purple-50 rounded-xl group-hover:bg-purple-100 transition-colors">
                            <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-layer-group text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">پشتیبانی از زبان‌های مختلف</span>
                        </div>
                    </div>
                    
                    <!-- CTA -->
                    <div class="text-center pt-4 border-t border-gray-100">
                        <span class="text-blue-600 font-bold group-hover:text-blue-700 transition-colors">
                            کلیک کنید برای انتخاب ← 
                        </span>
                    </div>
                </div>
            </div>

            <!-- GPT-4 Card -->
            <div id="gpt4-card" class="ai-selector cursor-pointer group" onclick="selectAI('gpt4')">
                <div class="bg-white rounded-3xl p-8 shadow-xl border border-gray-100 relative overflow-hidden">
                    <!-- Background Pattern -->
                    <div class="absolute inset-0 opacity-5">
                        <div class="absolute top-4 right-4 text-6xl">🧠</div>
                        <div class="absolute bottom-4 left-4 text-4xl">✨</div>
                    </div>
                    
                    <!-- Header with Icon -->
                    <div class="gradient-gpt text-white p-6 rounded-2xl mb-6 relative overflow-hidden shadow-lg">
                        <div class="absolute top-0 right-0 w-24 h-24 bg-white bg-opacity-10 rounded-full -mr-12 -mt-12"></div>
                        <div class="relative z-10">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="w-12 h-12 bg-white bg-opacity-20 rounded-xl flex items-center justify-center ml-3">
                                        <i class="fas fa-brain text-xl text-white"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-2xl font-black">GPT-4</h3>
                                        <p class="text-red-100 text-sm font-medium">Latest OpenAI Model</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="inline-block px-3 py-1 bg-white bg-opacity-20 rounded-full text-xs font-bold">PRO</span>
                                </div>
                            </div>
                            <p class="text-red-100 text-base leading-relaxed">
                                مدل پیشرفته هوش مصنوعی برای تحلیل و خلاقیت
                            </p>
                        </div>
                    </div>
                    
                    <!-- Features -->
                    <div class="space-y-4 mb-6">
                        <div class="flex items-center p-3 bg-red-50 rounded-xl group-hover:bg-red-100 transition-colors">
                            <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-comments text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">پاسخ به سوالات عمومی</span>
                        </div>
                        <div class="flex items-center p-3 bg-orange-50 rounded-xl group-hover:bg-orange-100 transition-colors">
                            <div class="w-8 h-8 bg-orange-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-chart-line text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">تحلیل و توضیح مفصل</span>
                        </div>
                        <div class="flex items-center p-3 bg-pink-50 rounded-xl group-hover:bg-pink-100 transition-colors">
                            <div class="w-8 h-8 bg-pink-500 rounded-lg flex items-center justify-center ml-3">
                                <i class="fas fa-lightbulb text-white text-sm"></i>
                            </div>
                            <span class="font-medium text-gray-800">خلاقیت و نوآوری بالا</span>
                        </div>
                    </div>
                    
                    <!-- CTA -->
                    <div class="text-center pt-4 border-t border-gray-100">
                        <span class="text-red-600 font-bold group-hover:text-red-700 transition-colors">
                            کلیک کنید برای انتخاب ← 
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chat Interface -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Chat Header -->
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div id="ai-avatar" class="w-10 h-10 rounded-full gradient-ai flex items-center justify-center text-white font-bold">
                            AI
                        </div>
                        <div class="mr-3">
                            <h3 id="ai-name" class="font-bold text-gray-900">یک مدل هوش مصنوعی انتخاب کنید</h3>
                            <p id="ai-status" class="text-sm text-gray-500">آماده برای گفتگو</p>
                        </div>
                    </div>
                    <button onclick="clearChat()" class="text-gray-500 hover:text-red-500 transition-colors">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                </div>
            </div>

            <!-- Chat Messages -->
            <div id="chat-messages" class="chat-container chat-messages overflow-y-auto px-6 py-4 space-y-4 relative">
                <div class="text-center py-12">
                    <div class="inline-block p-4 bg-white/50 backdrop-blur-sm rounded-full mb-4">
                        <i class="fas fa-robot text-4xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">به دستیار هوش مصنوعی خوش آمدید!</h3>
                    <p class="text-gray-500 max-w-md mx-auto">ابتدا یکی از مدل‌های هوش مصنوعی را انتخاب کنید تا گفتگو شروع شود</p>
                    <div class="mt-6 flex flex-wrap justify-center gap-2">
                        <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm">برنامه‌نویسی</span>
                        <span class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm">کدنویسی</span>
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">دیباگ</span>
                        <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm">آموزش</span>
                    </div>
                </div>
            </div>

            <!-- Chat Input -->
            <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-t border-gray-200">
                <div class="flex space-x-4 space-x-reverse items-end">
                    <div class="flex-1">
                        <textarea id="message-input" 
                                  placeholder="سوال خود را بپرسید... 💭" 
                                  class="message-input w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent resize-none"
                                  rows="3"
                                  disabled></textarea>
                        <div class="mt-2 flex justify-between items-center text-xs text-gray-500">
                            <span>Enter یا Ctrl+Enter برای ارسال</span>
                            <span id="char-counter" class="text-gray-400">0/2000</span>
                        </div>
                    </div>
                    <div class="flex flex-col space-y-2">
                        <button id="send-button" 
                                onclick="sendMessage()" 
                                class="send-button px-6 py-3 text-white rounded-xl disabled:bg-gray-400 disabled:cursor-not-allowed flex items-center justify-center"
                                disabled
                                title="ارسال پیام">
                            <i class="fas fa-paper-plane"></i>
                        </button>
                        <button onclick="clearChat()" 
                                class="px-6 py-3 bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition-all duration-300"
                                title="پاک کردن چت">
                            <i class="fas fa-broom"></i>
                        </button>
                        <button id="voice-button" 
                                onclick="toggleSpeech()" 
                                class="px-6 py-3 bg-gradient-to-r from-green-500 to-green-600 text-white rounded-xl hover:from-green-600 hover:to-green-700 transition-all duration-300"
                                title="تنظیمات صدا"
                                disabled>
                            <i class="fas fa-volume-up"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Enhanced Usage Tips -->
        <div class="mt-16 mb-12">
            <div class="text-center mb-8">
                <h2 class="text-3xl font-bold text-gray-900 mb-4">راهنمای استفاده</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">با این نکات، حداکثر استفاده را از دستیار هوش مصنوعی ببرید</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                <div class="group bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-blue-50 rounded-full -mr-16 -mt-16 group-hover:bg-blue-100 transition-colors duration-500"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                            <i class="fas fa-code text-white text-2xl"></i>
                        </div>
                        <h3 class="font-black text-gray-900 mb-4 text-xl">نکات کدنویسی</h3>
                        <p class="text-gray-600 leading-relaxed">از <span class="font-bold text-blue-600">GitHub Copilot</span> برای دریافت کمک در نوشتن، بهبود و debug کردن کدهای خود استفاده کنید.</p>
                        <div class="mt-4 flex items-center text-blue-600 font-medium text-sm">
                            <i class="fas fa-arrow-left ml-2"></i>
                            <span>برای کدنویسی بهتر</span>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-red-50 rounded-full -mr-16 -mt-16 group-hover:bg-red-100 transition-colors duration-500"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-pink-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                            <i class="fas fa-brain text-white text-2xl"></i>
                        </div>
                        <h3 class="font-black text-gray-900 mb-4 text-xl">سوالات عمومی</h3>
                        <p class="text-gray-600 leading-relaxed">از <span class="font-bold text-red-600">GPT-4</span> برای پاسخ به سوالات عمومی، تحلیل و توضیحات مفصل استفاده کنید.</p>
                        <div class="mt-4 flex items-center text-red-600 font-medium text-sm">
                            <i class="fas fa-arrow-left ml-2"></i>
                            <span>برای دانش عمومی</span>
                        </div>
                    </div>
                </div>
                
                <div class="group bg-white rounded-2xl p-8 shadow-xl hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-green-50 rounded-full -mr-16 -mt-16 group-hover:bg-green-100 transition-colors duration-500"></div>
                    <div class="relative z-10">
                        <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-500 shadow-lg">
                            <i class="fas fa-rocket text-white text-2xl"></i>
                        </div>
                        <h3 class="font-black text-gray-900 mb-4 text-xl">سرعت بالا</h3>
                        <p class="text-gray-600 leading-relaxed">معمولاً پاسخ‌ها در کمتر از <span class="font-bold text-green-600">۱۰ ثانیه</span> آماده می‌شوند. سرویس بهینه و پایدار.</p>
                        <div class="mt-4 flex items-center text-green-600 font-medium text-sm">
                            <i class="fas fa-arrow-left ml-2"></i>
                            <span>پاسخ فوری</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

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

    <script>
        let selectedAI = null;
        let isWaiting = false;
        let lastRequestTime = 0;
        let speechEnabled = false;
        let messageCount = 0;

        // Mobile menu toggle
        function toggleMobileMenu() {
            const menu = document.getElementById('mobileMenu');
            menu.classList.toggle('hidden');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            initializeChat();
            setupEventListeners();
            setupCharacterCounter();
        });

        function initializeChat() {
            // Play subtle welcome sound
            playSound('welcome');
            
            // Add some interactive sparkle effects
            createSparkleEffect();
        }

        function setupEventListeners() {
            const messageInput = document.getElementById('message-input');
            const voiceButton = document.getElementById('voice-button');
            
            messageInput.addEventListener('keydown', handleKeyDown);
            messageInput.addEventListener('input', updateCharCounter);
            
            // Auto-resize textarea
            messageInput.addEventListener('input', autoResize);
        }

        function setupCharacterCounter() {
            const messageInput = document.getElementById('message-input');
            const charCounter = document.getElementById('char-counter');
            
            messageInput.addEventListener('input', function() {
                const length = this.value.length;
                charCounter.textContent = `${length}/2000`;
                
                if (length > 1800) {
                    charCounter.classList.add('text-red-500');
                    charCounter.classList.remove('text-gray-400');
                } else if (length > 1500) {
                    charCounter.classList.add('text-yellow-500');
                    charCounter.classList.remove('text-gray-400', 'text-red-500');
                } else {
                    charCounter.classList.add('text-gray-400');
                    charCounter.classList.remove('text-red-500', 'text-yellow-500');
                }
            });
        }

        function handleKeyDown(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            } else if (e.key === 'Enter' && e.ctrlKey) {
                e.preventDefault();
                sendMessage();
            }
        }

        function autoResize() {
            const messageInput = document.getElementById('message-input');
            messageInput.style.height = 'auto';
            messageInput.style.height = Math.min(messageInput.scrollHeight, 120) + 'px';
        }

        function updateCharCounter() {
            const messageInput = document.getElementById('message-input');
            const charCounter = document.getElementById('char-counter');
            const length = messageInput.value.length;
            charCounter.textContent = `${length}/2000`;
        }

        // AI Selection
        function selectAI(aiType) {
            // Remove active state from all cards
            document.querySelectorAll('.ai-selector').forEach(card => {
                card.classList.remove('active', 'border-blue-300', 'border-pink-300');
                card.classList.add('border-transparent');
            });
            
            selectedAI = aiType;
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');
            const aiAvatar = document.getElementById('ai-avatar');
            const aiName = document.getElementById('ai-name');
            const aiStatus = document.getElementById('ai-status');
            
            if (aiType === 'copilot') {
                const card = document.getElementById('copilot-card');
                card.classList.add('active', 'border-blue-300');
                card.classList.remove('border-transparent');
                
                aiAvatar.className = 'w-10 h-10 rounded-full gradient-copilot flex items-center justify-center text-white font-bold';
                aiAvatar.innerHTML = '<i class="fas fa-code"></i>';
                aiName.textContent = 'Copilot AI';
                aiStatus.textContent = 'دستیار برنامه‌نویسی';
            } else if (aiType === 'gpt4') {
                const card = document.getElementById('gpt4-card');
                card.classList.add('active', 'border-pink-300');
                card.classList.remove('border-transparent');
                
                aiAvatar.className = 'w-10 h-10 rounded-full gradient-gpt flex items-center justify-center text-white font-bold';
                aiAvatar.innerHTML = '<i class="fas fa-brain"></i>';
                aiName.textContent = 'GPT-4';
                aiStatus.textContent = 'مدل پیشرفته OpenAI';
            }
            
            messageInput.disabled = false;
            sendButton.disabled = false;
            messageInput.focus();
            
            // Clear welcome message
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.innerHTML = '';
        }

        // Send Message
        async function sendMessage() {
            if (!selectedAI || isWaiting) return;
            
            const messageInput = document.getElementById('message-input');
            const message = messageInput.value.trim();
            if (!message) return;
            
            // Rate limiting check
            const now = Date.now();
            const timeSinceLastRequest = now - lastRequestTime;
            if (timeSinceLastRequest < 10000) { // 10 seconds
                const remainingTime = Math.ceil((10000 - timeSinceLastRequest) / 1000);
                showSystemMessage(`لطفاً ${remainingTime} ثانیه صبر کنید`, 'warning');
                return;
            }
            
            // Add user message
            addMessage(message, 'user');
            messageInput.value = '';
            
            // Show typing indicator
            addTypingIndicator();
            isWaiting = true;
            lastRequestTime = now;
            
            try {
                let response;
                if (selectedAI === 'copilot') {
                    response = await fetchCopilotResponse(message);
                } else if (selectedAI === 'gpt4') {
                    response = await fetchGPT4Response(message);
                }
                
                removeTypingIndicator();
                
                if (response.success) {
                    addMessage(response.data, 'ai');
                } else {
                    addMessage(`خطا: ${response.error}`, 'error');
                }
            } catch (error) {
                removeTypingIndicator();
                addMessage(`خطا در ارتباط با سرور: ${error.message}`, 'error');
            }
            
            isWaiting = false;
        }

        // Fetch Copilot Response (via server proxy)
        async function fetchCopilotResponse(message) {
            try {
                const response = await fetch('api/ai-proxy.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ model: 'copilot', text: message })
                });
                const json = await response.json().catch(() => null);
                if (!response.ok || !json || json.success !== true) {
                    const err = (json && json.error) ? json.error : `HTTP ${response.status}`;
                    throw new Error(err);
                }
                return { success: true, data: json.data };
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        // Fetch GPT4 Response (via server proxy)
        async function fetchGPT4Response(message) {
            try {
                const response = await fetch('api/ai-proxy.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ model: 'gpt4', text: message })
                });
                const json = await response.json().catch(() => null);
                if (!response.ok || !json || json.success !== true) {
                    const err = (json && json.error) ? json.error : `HTTP ${response.status}`;
                    throw new Error(err);
                }
                return { success: true, data: json.data };
            } catch (error) {
                return { success: false, error: error.message };
            }
        }

        // Add message to chat with enhanced styling
        function addMessage(content, type) {
            messageCount++;
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message message-appear';
            
            if (type === 'user') {
                playSound('send');
                messageDiv.innerHTML = `
                    <div class="flex justify-end">
                        <div class="user-message max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl text-white px-5 py-4">
                            <div class="message-content font-medium">${escapeHtml(content)}</div>
                            <div class="text-xs opacity-75 mt-2 text-left">${getCurrentTime()}</div>
                        </div>
                    </div>
                `;
            } else if (type === 'ai') {
                playSound('receive');
                const aiName = selectedAI === 'copilot' ? 'GitHub Copilot' : 'GPT-4';
                const gradientClass = selectedAI === 'copilot' ? 'gradient-copilot' : 'gradient-gpt';
                const messageId = 'msg-' + Date.now();
                
                messageDiv.innerHTML = `
                    <div class="flex justify-start">
                        <div class="flex items-start space-x-3 space-x-reverse">
                            <div class="w-10 h-10 rounded-full ${gradientClass} flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-lg">
                                ${selectedAI === 'copilot' ? '<i class="fas fa-code"></i>' : '<i class="fas fa-brain"></i>'}
                            </div>
                            <div class="ai-message max-w-xs md:max-w-md lg:max-w-lg xl:max-w-xl px-5 py-4 relative">
                                <button class="copy-button" onclick="copyMessage('${messageId}')" title="کپی متن">
                                    <i class="fas fa-copy"></i>
                                </button>
                                <div class="text-xs text-gray-500 mb-2 font-medium flex items-center">
                                    <span class="flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        ${aiName}
                                    </span>
                                </div>
                                <div class="message-content" id="${messageId}">${formatAIResponse(content)}</div>
                                <div class="text-xs text-gray-400 mt-3 flex items-center justify-between">
                                    <span>${getCurrentTime()}</span>
                                    <button onclick="readAloud('${messageId}')" class="text-gray-400 hover:text-blue-500 transition-colors" title="خواندن متن">
                                        <i class="fas fa-volume-up text-xs"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            } else if (type === 'error') {
                playSound('error');
                messageDiv.innerHTML = `
                    <div class="flex justify-center">
                        <div class="bg-red-50 border-2 border-red-200 text-red-700 rounded-xl px-5 py-4 max-w-md shadow-lg">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-exclamation-triangle text-red-500 ml-3"></i>
                                </div>
                                <div>
                                    <div class="font-medium">خطا در پردازش</div>
                                    <div class="text-sm mt-1 message-content">${escapeHtml(content)}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            }
            
            chatMessages.appendChild(messageDiv);
            smoothScrollToBottom();
            
            // Add notification if page is not visible
            if (document.hidden && type === 'ai') {
                showNotification('پاسخ جدید', content.substring(0, 100) + '...');
            }
        }

        // Copy message content
        function copyMessage(messageId) {
            const messageElement = document.getElementById(messageId);
            const text = messageElement.textContent || messageElement.innerText;
            
            navigator.clipboard.writeText(text).then(() => {
                showToast('متن کپی شد!', 'success');
                playSound('copy');
            }).catch(err => {
                console.error('خطا در کپی کردن: ', err);
                showToast('خطا در کپی کردن', 'error');
            });
        }

        // Read aloud functionality
        function readAloud(messageId) {
            const messageElement = document.getElementById(messageId);
            const text = messageElement.textContent || messageElement.innerText;
            
            if ('speechSynthesis' in window) {
                speechSynthesis.cancel();
                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'fa-IR';
                utterance.rate = 0.8;
                utterance.pitch = 1;
                speechSynthesis.speak(utterance);
                showToast('در حال خواندن...', 'info');
            } else {
                showToast('مرورگر شما از این قابلیت پشتیبانی نمی‌کند', 'error');
            }
        }

        // Get current time
        function getCurrentTime() {
            return new Date().toLocaleTimeString('fa-IR', { hour: '2-digit', minute: '2-digit' });
        }

        // Show system message
        function showSystemMessage(content, type = 'info') {
            const chatMessages = document.getElementById('chat-messages');
            const messageDiv = document.createElement('div');
            messageDiv.className = 'chat-message system-message';
            
            let bgClass, textClass, icon;
            if (type === 'warning') {
                bgClass = 'bg-yellow-100 border-yellow-300';
                textClass = 'text-yellow-700';
                icon = 'fas fa-clock';
            } else {
                bgClass = 'bg-blue-100 border-blue-300';
                textClass = 'text-blue-700';
                icon = 'fas fa-info-circle';
            }
            
            messageDiv.innerHTML = `
                <div class="flex justify-center">
                    <div class="${bgClass} border ${textClass} rounded-xl px-4 py-3 max-w-md">
                        <div class="flex items-center">
                            <i class="${icon} ml-2"></i>
                            <div class="message-content">${escapeHtml(content)}</div>
                        </div>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(messageDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            
            // Remove system message after 3 seconds
            setTimeout(() => {
                if (messageDiv.parentNode) {
                    messageDiv.parentNode.removeChild(messageDiv);
                }
            }, 3000);
        }

        // Add typing indicator
        function addTypingIndicator() {
            const chatMessages = document.getElementById('chat-messages');
            const typingDiv = document.createElement('div');
            typingDiv.className = 'chat-message typing-message';
            typingDiv.id = 'typing-indicator';
            
            const aiName = selectedAI === 'copilot' ? 'Copilot' : 'GPT-4';
            const gradientClass = selectedAI === 'copilot' ? 'gradient-copilot' : 'gradient-gpt';
            
            typingDiv.innerHTML = `
                <div class="flex justify-start">
                    <div class="flex items-start space-x-2 space-x-reverse">
                        <div class="w-8 h-8 rounded-full ${gradientClass} flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                            ${selectedAI === 'copilot' ? '<i class="fas fa-code text-xs"></i>' : '<i class="fas fa-brain text-xs"></i>'}
                        </div>
                        <div class="bg-gray-100 rounded-2xl rounded-bl-md px-4 py-3">
                            <div class="text-xs text-gray-500 mb-1 font-medium">${aiName}</div>
                            <div class="typing-indicator">
                                <div></div>
                                <div></div>
                                <div></div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            chatMessages.appendChild(typingDiv);
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }

        // Remove typing indicator
        function removeTypingIndicator() {
            const typingIndicator = document.getElementById('typing-indicator');
            if (typingIndicator) {
                typingIndicator.remove();
            }
        }

        // Format AI response
        function formatAIResponse(content) {
            // Simple formatting for code blocks and basic markup
            let formatted = escapeHtml(content);
            
            // Format code blocks (```code```)
            formatted = formatted.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
            
            // Format inline code (`code`)
            formatted = formatted.replace(/`([^`]+)`/g, '<code>$1</code>');
            
            // Format line breaks
            formatted = formatted.replace(/\n/g, '<br>');
            
            return formatted;
        }

        // Escape HTML
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

        // Clear chat with animation
        function clearChat() {
            const chatMessages = document.getElementById('chat-messages');
            
            // Fade out existing messages
            const existingMessages = chatMessages.querySelectorAll('.chat-message');
            existingMessages.forEach((msg, index) => {
                setTimeout(() => {
                    msg.style.animation = 'slideOutMessage 0.3s ease-out forwards';
                }, index * 50);
            });
            
            // Clear and show welcome message
            setTimeout(() => {
                chatMessages.innerHTML = `
                    <div class="text-center py-12 animate-fade-in">
                        <div class="inline-block p-4 bg-white/50 backdrop-blur-sm rounded-full mb-4">
                            <i class="fas fa-broom text-4xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-700 mb-2">چت پاک شد! ✨</h3>
                        <p class="text-gray-500 max-w-md mx-auto">می‌توانید گفتگوی جدید را شروع کنید</p>
                    </div>
                `;
                playSound('clear');
                messageCount = 0;
            }, existingMessages.length * 50 + 200);
        }

        // Enhanced helper functions
        function smoothScrollToBottom() {
            const chatMessages = document.getElementById('chat-messages');
            chatMessages.scrollTo({
                top: chatMessages.scrollHeight,
                behavior: 'smooth'
            });
        }

        // Simplified sound function - removed heavy Web Audio API
        let audioContext = null;
        function playSound(type) {
            if (!speechEnabled) return;
            
            // Simple beep using HTML5 audio for better performance
            try {
                const beep = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8+KTQAY=');
                beep.volume = 0.1;
                beep.play().catch(() => {}); // Ignore errors silently
            } catch(e) {
                // Fallback - do nothing if audio fails
            }
        }

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-50 px-4 py-2 rounded-lg text-white font-medium shadow-lg transform transition-all duration-300 translate-x-full opacity-0`;
            
            const colors = {
                'success': 'bg-green-500',
                'error': 'bg-red-500',
                'info': 'bg-blue-500',
                'warning': 'bg-yellow-500'
            };
            
            toast.classList.add(colors[type] || colors.info);
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            // Animate in
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
            }, 100);
            
            // Animate out and remove
            setTimeout(() => {
                toast.classList.add('translate-x-full', 'opacity-0');
                setTimeout(() => document.body.removeChild(toast), 300);
            }, 3000);
        }

        function showNotification(title, body) {
            if ('Notification' in window && Notification.permission === 'granted') {
                new Notification(title, {
                    body: body,
                    icon: 'favicon.svg',
                    badge: 'favicon.svg'
                });
            } else if ('Notification' in window && Notification.permission !== 'denied') {
                Notification.requestPermission().then(permission => {
                    if (permission === 'granted') {
                        new Notification(title, {
                            body: body,
                            icon: 'favicon.svg',
                            badge: 'favicon.svg'
                        });
                    }
                });
            }
        }

        // Removed heavy sparkle effect for better performance
        function createSparkleEffect() {
            // Disabled for performance optimization
            return;
        }

        function toggleSpeech() {
            speechEnabled = !speechEnabled;
            const voiceButton = document.getElementById('voice-button');
            const icon = voiceButton.querySelector('i');
            
            if (speechEnabled) {
                icon.className = 'fas fa-volume-up';
                voiceButton.title = 'غیرفعال کردن صدا';
                showToast('صدا فعال شد', 'success');
                playSound('welcome');
            } else {
                icon.className = 'fas fa-volume-mute';
                voiceButton.title = 'فعال کردن صدا';
                showToast('صدا غیرفعال شد', 'info');
            }
        }

        // Add CSS animations
        const additionalCSS = `
            @keyframes slideOutMessage {
                to { opacity: 0; transform: translateX(100px); }
            }
            @keyframes sparkle {
                0% { opacity: 0; transform: scale(0) rotate(0deg); }
                50% { opacity: 1; transform: scale(1) rotate(180deg); }
                100% { opacity: 0; transform: scale(0) rotate(360deg); }
            }
            @keyframes animate-fade-in {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .animate-fade-in { animation: animate-fade-in 0.6s ease-out; }
            .animation-delay-1000 { animation-delay: 1s; }
            .animation-delay-2000 { animation-delay: 2s; }
            .animation-delay-4000 { animation-delay: 4s; }
        `;
        
        const style = document.createElement('style');
        style.textContent = additionalCSS;
        document.head.appendChild(style);
    </script>
</body>
</html>
