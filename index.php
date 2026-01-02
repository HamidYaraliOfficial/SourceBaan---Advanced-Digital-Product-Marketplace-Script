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

// No heavy PHP here; the page will call APIs for dynamic data
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0f1419" media="(prefers-color-scheme: dark)">
    <script>
    // Apply dark mode ASAP to prevent flash on mobile
    (function(){
        try {
            var key = 'sourcebaan_darkMode';
            var stored = localStorage.getItem(key);
            var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
            var enable = (stored === 'true') || (stored === null && prefersDark);
            if (enable) {
                document.documentElement.classList.add('dark-mode');
            }
        } catch(e) {}
    })();
    </script>
    <title>دانلود سورس کد رایگان | کد منبع و پروژه برنامه نویسی</title>
    <meta name="description" content="دانلود سورس کد رایگان، کد منبع برنامه نویسی و پروژه‌های آماده. بزرگترین مرجع سورس کدهای PHP، Python، JavaScript و ربات تلگرام در ایران.">
    <meta name="keywords" content="دانلود سورس کد رایگان, کد منبع برنامه نویسی, دانلود پروژه برنامه نویسی, سورس ربات تلگرام, سورس کد PHP, سورس کد Python, اسکریپت آماده, منبع برنامه نویسی">
    <meta name="author" content="SourceBaan Team">
    
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
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="https://sourcebaan.ir/">
    <meta property="og:title" content="دانلود سورس کد رایگان | کد منبع و پروژه برنامه نویسی">
    <meta property="og:description" content="دانلود سورس کد رایگان، کد منبع برنامه نویسی و پروژه‌های آماده. بزرگترین مرجع سورس کدهای PHP، Python، JavaScript و ربات تلگرام در ایران.">
    <meta property="og:image" content="https://sourcebaan.ir/logo.png">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="https://sourcebaan.ir/">
    <meta property="twitter:title" content="دانلود سورس کد رایگان | کد منبع و پروژه برنامه نویسی">
    <meta property="twitter:description" content="دانلود سورس کد رایگان، کد منبع برنامه نویسی و پروژه‌های آماده. بزرگترین مرجع سورس کدهای PHP، Python، JavaScript و ربات تلگرام در ایران.">
    <meta property="twitter:image" content="https://sourcebaan.ir/logo.png">
    
    <!-- Additional SEO Meta Tags -->
    <meta name="robots" content="index, follow">
    <meta name="googlebot" content="index, follow">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="https://sourcebaan.ir/">
    <meta name="language" content="Persian">
    <meta name="geo.region" content="IR">
    <meta name="geo.country" content="Iran">
    <link rel="canonical" href="https://sourcebaan.ir/">
    
    <!-- Structured Data -->
    <script type="application/ld+json" defer>
    {
      "@context": "https://schema.org",
      "@type": "WebSite",
      "name": "دانلود سورس کد رایگان | کد منبع و پروژه برنامه نویسی",
      "alternateName": "SourceBaan",
      "url": "https://sourcebaan.ir/",
      "description": "دانلود سورس کد رایگان، کد منبع برنامه نویسی و پروژه‌های آماده. بزرگترین مرجع سورس کدهای PHP، Python، JavaScript و ربات تلگرام در ایران.",
      "inLanguage": "fa-IR",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "https://sourcebaan.ir/sources.php?search={search_term_string}",
        "query-input": "required name=search_term_string"
      },
      "publisher": {
        "@type": "Organization",
        "name": "SourceBaan",
        "url": "https://sourcebaan.ir/",
        "logo": {
          "@type": "ImageObject",
          "url": "https://sourcebaan.ir/logo.png"
        }
      }
    }
    </script>
    
    <!-- Floating Chat Assistant -->
    <style>
    .assistant-fab{position:fixed;bottom:24px;right:24px;width:56px;height:56px;border-radius:50%;background:#2563eb;box-shadow:0 10px 25px rgba(37,99,235,.35);display:flex;align-items:center;justify-content:center;color:#fff;cursor:pointer;z-index:9999}
    .assistant-panel{position:fixed;bottom:92px;right:24px;width:340px;max-width:calc(100vw - 48px);height:460px;background:#fff;border-radius:16px;box-shadow:0 20px 60px rgba(0,0,0,.2);display:none;flex-direction:column;overflow:hidden;z-index:9999}
    .assistant-header{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;padding:12px 14px;display:flex;align-items:center;justify-content:space-between}
    .assistant-body{flex:1;background:#f9fafb;padding:12px;overflow:auto}
    .assistant-msg{background:#fff;border-radius:12px;padding:10px 12px;margin:8px 0;box-shadow:0 2px 8px rgba(0,0,0,.04)}
    .assistant-msg.user{background:#e0f2fe}
    .assistant-input{display:flex;gap:8px;padding:10px;background:#fff;border-top:1px solid #e5e7eb}
    .assistant-input input{flex:1;border:1px solid #e5e7eb;border-radius:10px;padding:10px}
    </style>

    <!-- Description Typography Enhancements -->
    <style>
    /* Better readable description blocks (project modals/sections) */
    .prose { line-height: 1.9; }
    .prose p { margin: 0 0 0.8rem; color: #374151; }
    .prose ul, .prose ol { padding-right: 1.25rem; margin: 0.5rem 0 1rem; }
    .prose li { margin: 0.25rem 0; }
    .prose a { color: #2563eb; text-decoration: none; }
    .prose a:hover { text-decoration: underline; }
    .prose code { background: #f3f4f6; border-radius: .375rem; padding: .1rem .35rem; font-size: 0.875em; }
    .prose pre { background: #0f172a; color: #e2e8f0; border-radius: .75rem; padding: 1rem; overflow: auto; direction: ltr; text-align: left; }
    .prose img { max-width: 100%; border-radius: .75rem; display: block; margin: .75rem auto; }
    .prose blockquote { border-right: 3px solid #e5e7eb; padding-right: .75rem; color: #4b5563; }
    </style>
    <div id="assistantFab" class="assistant-fab" title="چت با دستیار" aria-label="چت با دستیار">
        <i class="fas fa-robot" aria-hidden="true"></i>
    </div>
    <div id="assistantPanel" class="assistant-panel" role="dialog" aria-modal="true" aria-labelledby="assistantTitle">
        <div class="assistant-header">
            <div class="flex items-center gap-2">
                <div style="width:28px;height:28px;border-radius:50%;background:#fff;color:#5b67e8;display:flex;align-items:center;justify-content:center"><i class="fas fa-robot"></i></div>
                <div class="font-semibold" id="assistantTitle">دستیار سورس‌کده</div>
            </div>
            <div class="flex items-center gap-2">
                <button id="liveChatBtn" onclick="toggleLiveChat()" class="text-white/90 hover:text-white text-sm flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 hover:bg-white/20 transition" title="چت با کارشناس">
                    <i class="fas fa-user-headset"></i>
                    <span class="hidden sm:inline">صحبت با پشتیبانی</span>
                </button>
                <button id="assistantClose" class="text-white/90 hover:text-white" aria-label="بستن"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
        </div>
        <!-- Quick Action CTA for Support -->
        <div id="assistantQuickActions" class="px-3 pt-3">
            <button onclick="startLiveChat()" class="w-full text-left px-3 py-2 rounded-lg border border-green-200 bg-green-50 hover:bg-green-100 text-green-700 text-sm flex items-center gap-2 transition">
                <i class="fas fa-user-headset"></i>
                <span>صحبت با پشتیبانی</span>
            </button>
        </div>
        <div id="assistantBody" class="assistant-body">
            <div class="assistant-msg">سلام! من دستیار سایت هستم. هر سوالی درباره ثبت‌نام، آپلود یا پروژه‌ها دارید بپرسید.</div>
        </div>
        <div id="liveChatControls" class="hidden bg-green-50 p-3 mx-3 mb-3 rounded-lg border border-green-200">
            <div class="flex justify-between items-center mb-2">
                <span class="text-green-700 font-bold text-sm">چت زنده با کارشناس</span>
                <span id="liveChatStatus" class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">در انتظار...</span>
            </div>
            <button onclick="endLiveChat()" class="text-red-600 text-xs hover:text-red-800">پایان چت</button>
        </div>
        <div class="assistant-input">
            <input id="assistantInput" type="text" placeholder="سوال خود را بنویسید..." aria-label="پیام">
            <input type="file" id="assistantFileInput" accept="image/*,.pdf,.doc,.docx,.txt,.zip" class="hidden" onchange="handleFileSelect(event)">
            <button id="assistantFileBtn" onclick="document.getElementById('assistantFileInput').click()" class="chip-btn bg-purple-600 text-white hover:bg-purple-700 ml-2" title="ارسال فایل">
                <i class="fas fa-paperclip"></i>
            </button>
            <button id="assistantSend" class="chip-btn bg-blue-600 text-white hover:bg-blue-700" aria-label="ارسال"><i class="fas fa-paper-plane" aria-hidden="true"></i> ارسال</button>
        </div>
    </div>
    <script defer>
    (function(){
        const fab = document.getElementById('assistantFab');
        const panel = document.getElementById('assistantPanel');
        const closeBtn = document.getElementById('assistantClose');
        const body = document.getElementById('assistantBody');
        const input = document.getElementById('assistantInput');
        const send = document.getElementById('assistantSend');
        async function open(){
            panel.style.display='flex'; setTimeout(()=>input.focus(),100);
            // Try to restore an active live chat
            try {
                const res = await fetch('api/live-chat.php?action=get_chat_status');
                const data = await res.json();
                if (data.ok && data.has_active_chat && data.chat) {
                    isLiveChatMode = true;
                    currentChatId = data.chat.chat_id;
                    document.getElementById('assistantTitle').textContent = 'چت با کارشناس';
                    document.getElementById('liveChatControls').classList.remove('hidden');
                    document.getElementById('liveChatBtn').innerHTML = '<i class="fas fa-robot"></i>';
                    document.getElementById('liveChatBtn').title = 'بازگشت به دستیار';
                    pollLiveChatMessages();
                    if (!liveChatInterval) liveChatInterval = setInterval(pollLiveChatMessages, 3000);
                }
            } catch (e) { /* ignore */ }
        }
        function close(){ panel.style.display='none'; }
        function addMsg(text, isUser){ const d=document.createElement('div'); d.className='assistant-msg'+(isUser?' user':''); d.textContent=text; body.appendChild(d); body.scrollTop=body.scrollHeight; }
        // Live chat variables
        let isLiveChatMode = false;
        let currentChatId = null;
        let liveChatInterval = null;
        let selectedFile = null;

        function renderAnswer(data){
            addMsg(data.ok?data.answer:(data.error||'خطا در پاسخ‌گویی'), false);
            if (data.ok && Array.isArray(data.suggestions) && data.suggestions.length){
                const wrap=document.createElement('div'); wrap.className='assistant-msg';
                const title=document.createElement('div'); title.textContent='پرسش‌های پیشنهادی:'; title.style.marginBottom='6px'; title.style.fontWeight='600';
                wrap.appendChild(title);
                const cont=document.createElement('div'); cont.style.display='flex'; cont.style.flexWrap='wrap'; cont.style.gap='6px';
                data.suggestions.forEach(s=>{ const b=document.createElement('button'); b.textContent=s; b.style.padding='6px 10px'; b.style.borderRadius='10px'; b.style.border='1px solid #e5e7eb'; b.style.background='#fff'; b.style.cursor='pointer'; b.onclick=()=>{ input.value=s; sendMessage(); }; cont.appendChild(b); });
                wrap.appendChild(cont);
                body.appendChild(wrap); body.scrollTop=body.scrollHeight;
            }
        }

        async function sendMessage() {
            if (isLiveChatMode) {
                await sendLiveChatMessage();
            } else {
                await ask();
            }
        }

        async function ask(){ const q=input.value.trim(); if(!q) return; addMsg(q,true); input.value=''; try{ const r=await fetch('api/assistant.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({message:q})}); const data=await r.json(); renderAnswer(data);}catch(_){ addMsg('خطا در اتصال به سرور', false);} }

        async function toggleLiveChat() {
            if (isLiveChatMode) {
                await endLiveChat();
            } else {
                await startLiveChat();
            }
        }

        async function startLiveChat() {
            try {
                const formData = new FormData();
                formData.append('action', 'start_chat');
                formData.append('send_user_info', 'true');
                formData.append('csrf_token', '<?= csrf_get_token() ?>');
                
                const response = await fetch('api/live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.ok) {
                    isLiveChatMode = true;
                    currentChatId = data.chat_id;
                    
                    document.getElementById('assistantTitle').textContent = 'چت با کارشناس';
                    document.getElementById('liveChatControls').classList.remove('hidden');
                    document.getElementById('liveChatBtn').innerHTML = '<i class="fas fa-robot"></i>';
                    document.getElementById('liveChatBtn').title = 'بازگشت به دستیار';
                    
                    addMsg('چت با کارشناسان آغاز شد. اطلاعات حساب شما ارسال شد. منتظر پاسخ کارشناس باشید...', false);
                    
                    // Start polling for messages
                    liveChatInterval = setInterval(pollLiveChatMessages, 3000);
                } else {
                    if ((data.error || '').includes('وارد حساب')) {
                        addMsg('برای چت با پشتیبانی ابتدا وارد حساب شوید.', false);
                        const login = document.createElement('div'); login.className='assistant-msg';
                        login.innerHTML = '<a href="auth.php" class="text-blue-600 underline">ورود به حساب</a>';
                        body.appendChild(login);
                        body.scrollTop = body.scrollHeight;
                    } else {
                        addMsg(data.error || 'خطا در شروع چت', false);
                    }
                }
            } catch (e) {
                addMsg('خطا در اتصال به سرور', false);
            }
        }

        async function sendLiveChatMessage() {
            const q = input.value.trim();
            if ((!q && !selectedFile) || !currentChatId) return;
            
            if (q) {
                addMsg(q, true);
                input.value = '';
            }
            
            try {
                const formData = new FormData();
                formData.append('action', 'send_message');
                formData.append('chat_id', currentChatId);
                if (q) formData.append('message', q);
                if (selectedFile) {
                    formData.append('file', selectedFile);
                    addMsg(`📎 فایل ارسال شد: ${selectedFile.name}`, true);
                }
                formData.append('csrf_token', '<?= csrf_get_token() ?>');
                
                const response = await fetch('api/live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (!data.ok) {
                    addMsg(data.error || 'خطا در ارسال پیام', false);
                } else if (selectedFile) {
                    selectedFile = null;
                    document.getElementById('assistantFileInput').value = '';
                }
            } catch (e) {
                addMsg('خطا در اتصال به سرور', false);
            }
        }

        async function pollLiveChatMessages() {
            if (!currentChatId) return;
            
            try {
                const response = await fetch(`api/live-chat.php?action=get_messages&chat_id=${currentChatId}`);
                const data = await response.json();
                
                if (data.ok) {
                    // Update status
                    const statusEl = document.getElementById('liveChatStatus');
                    if (data.status === 'waiting') {
                        statusEl.textContent = 'در انتظار...';
                        statusEl.className = 'text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded';
                    } else if (data.status === 'active') {
                        statusEl.textContent = data.admin_name ? `کارشناس: ${data.admin_name}` : 'متصل';
                        statusEl.className = 'text-xs bg-green-100 text-green-700 px-2 py-1 rounded';
                    }
                    // Render messages
                    renderLiveMessages(data.messages || []);
                }
        function renderLiveMessages(messages){
            body.innerHTML = '';
            messages.forEach(m=>{
                const t = m.message || '';
                const isUser = m.sender_type === 'user';
                
                if (m.file_url && m.file_name) {
                    const fileMsg = document.createElement('div');
                    fileMsg.className = 'assistant-msg' + (isUser ? ' user' : '');
                    fileMsg.innerHTML = `
                        <div class="flex items-center gap-2">
                            <i class="fas fa-paperclip text-blue-600"></i>
                            <a href="${m.file_url}" target="_blank" class="text-blue-600 hover:underline">${m.file_name}</a>
                        </div>
                    `;
                    body.appendChild(fileMsg);
                }
                
                if (t) {
                    addMsg(t, isUser);
                }
            });
            body.scrollTop = body.scrollHeight;
        }
            } catch (e) {
                // Silent fail for polling
            }
        }

        async function endLiveChat() {
            if (!currentChatId) return;
            
            try {
                const formData = new FormData();
                formData.append('action', 'end_chat');
                formData.append('chat_id', currentChatId);
                formData.append('csrf_token', '<?= csrf_get_token() ?>');
                
                const response = await fetch('api/live-chat.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                addMsg(data.ok ? 'چت پایان یافت' : (data.error || 'خطا در پایان چت'), false);
            } catch (e) {
                addMsg('خطا در اتصال به سرور', false);
            } finally {
                // Reset to assistant mode
                isLiveChatMode = false;
                currentChatId = null;
                
                if (liveChatInterval) {
                    clearInterval(liveChatInterval);
                    liveChatInterval = null;
                }
                
                document.getElementById('assistantTitle').textContent = 'دستیار سورس‌کده';
                document.getElementById('liveChatControls').classList.add('hidden');
                document.getElementById('liveChatBtn').innerHTML = '<i class="fas fa-user-headset"></i>';
                document.getElementById('liveChatBtn').title = 'چت با کارشناس';
            }
        }
        fab.addEventListener('click', open);
        closeBtn.addEventListener('click', close);
        send.addEventListener('click', sendMessage);
        input.addEventListener('keydown', e=>{ if(e.key==='Enter') sendMessage(); });

        // File handling function
        function handleFileSelect(event) {
            const file = event.target.files[0];
            if (file) {
                if (file.size > 10 * 1024 * 1024) { // 10MB limit
                    addMsg('حجم فایل نباید بیشتر از 10240 کیلوبایت باشد', false);
                    return;
                }
                selectedFile = file;
                addMsg(`فایل انتخاب شد: ${file.name} (${Math.round(file.size/1024)}KB)`, false);
            }
        }

        // Expose functions globally for inline onclick handlers
        window.startLiveChat = startLiveChat;
        window.toggleLiveChat = toggleLiveChat;
        window.endLiveChat = endLiveChat;
        window.handleFileSelect = handleFileSelect;
    })();
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script defer>
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
        (function(){
            var prismLoaded = false;
            function loadPrism(){
                if (prismLoaded) return; prismLoaded = true;
                try {
                    var link = document.createElement('link');
                    link.rel = 'stylesheet';
                    link.href = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css';
                    document.head.appendChild(link);
                    var core = document.createElement('script');
                    core.src = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-core.min.js';
                    core.defer = true;
                    core.onload = function(){
                        var auto = document.createElement('script');
                        auto.src = 'https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/plugins/autoloader/prism-autoloader.min.js';
                        auto.defer = true;
                        document.head.appendChild(auto);
                    };
                    document.head.appendChild(core);
                } catch(e) {}
            }
            function checkAndLoad(){ try { if (document.querySelector('pre code')) loadPrism(); } catch(e) {} }
            document.addEventListener('DOMContentLoaded', checkAndLoad);
            try {
                var mo = new MutationObserver(function(mutations){
                    for (var i=0;i<mutations.length;i++){
                        var m = mutations[i];
                        if (m.addedNodes && m.addedNodes.length){
                            for (var j=0;j<m.addedNodes.length;j++){
                                var node = m.addedNodes[j];
                                if ((node.matches && node.matches('pre code')) || (node.querySelector && node.querySelector('pre code'))){
                                    loadPrism(); return;
                                }
                            }
                        }
                    }
                });
                mo.observe(document.documentElement, { childList: true, subtree: true });
            } catch(e) {}
        })();
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
        @import url('https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap');
        * { font-family: 'Vazirmatn', sans-serif; }
        .gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .gradient-secondary { background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); }
        .gradient-success { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
        .gradient-dark { background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); }
        .animate-popup { transition: transform 0.3s ease; }
        /* Modern Navigation */
        .modern-nav {
            background: rgba(255, 255, 255, 0.95);
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        /* Prevent content overlap with sticky header on small screens */
        @media (max-width: 768px) {
            /* Removed padding-top to fix mobile header positioning */
        }
        .reduced-motion .modern-nav, .low-memory .modern-nav {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.06), inset 0 1px 0 rgba(255, 255, 255, 0.6) !important;
        }
        .restrict-effects .modern-nav { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        /* DISABLE ALL ANIMATIONS AND HEAVY EFFECTS */
        *, *::before, *::after { animation: none !important; transition: none !important; transform: none !important; }
        .animate-ping, .animate-bounce, .animate-pulse, .animate-spin { animation: none !important; }
        .code-rain, .particle, .code-char { display: none !important; }
        .project-card, .clean-card { box-shadow: 0 1px 3px rgba(0,0,0,.1) !important; }
        .modern-glass, .glass-effect { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        .blur, .backdrop-blur-sm, .backdrop-blur, .backdrop-blur-lg { backdrop-filter: none !important; -webkit-backdrop-filter: none !important; }
        .hover\\:scale-105:hover, .hover\\:scale-110:hover, .group-hover\\:scale-110, .group-hover\\:scale-125 { transform: none !important; }
        .float-element { animation: none !important; }
        .pulse-slow { animation: none !important; }
        .verified-pulse { animation: none !important; }
        .zip-verified { animation: none !important; }
        
        /* Responsive Navigation Improvements */
        @media (max-width: 768px) {
            .modern-nav {
                padding-left: 8px;
                padding-right: 8px;
            }
            
            .modern-nav .max-w-7xl {
                padding-left: 8px;
                padding-right: 8px;
            }
            
            /* Ensure proper text wrapping for Persian text */
            .whitespace-nowrap {
                white-space: nowrap;
                word-break: keep-all;
            }
            
            /* Better button sizing on mobile */
            .mobile-auth-button {
                min-width: 60px;
                text-align: center;
            }
        }
        
        @media (max-width: 640px) {
            .modern-nav h1 {
                font-size: 1.25rem !important;
            }
            
            .mobile-controls {
                gap: 4px;
            }
            
            .mobile-controls button {
                padding: 6px;
                min-width: 36px;
                min-height: 36px;
            }
        }
        
        @media (max-width: 480px) {
            .modern-nav {
                padding: 0;
            }
            
            .modern-nav .max-w-7xl {
                padding-left: 4px;
                padding-right: 4px;
            }
            
            .modern-nav h1 {
                font-size: 1rem !important;
                line-height: 1.2;
            }
        }
        
        .glass-effect { background: rgba(255, 255, 255, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.2); }
        .dark-glass { background: rgba(0, 0, 0, 0.1); backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); }
        
        /* Float Animation */
        .float-element { 
            animation: float 8s ease-in-out infinite; 
            will-change: transform; 
        }
        @keyframes float { 
            0%, 100% { transform: translateY(0px) rotate(0deg); } 
            50% { transform: translateY(-8px) rotate(2deg); } 
        }
        
        /* Pulse Slow */
        .animate-pulse-slow { 
            animation: pulse-slow 4s infinite; 
        }
        @keyframes pulse-slow { 
            0%, 100% { opacity: 0.9; } 
            50% { opacity: 0.6; } 
        }
        .card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); will-change: transform; }
        .card-hover:hover { transform: translate3d(0, -5px, 0) scale(1.02); box-shadow: 0 20px 40px rgba(0,0,0,0.12); }
        .floating-animation { animation: float 20s ease-in-out infinite; will-change: auto; }
        @keyframes float { 0%, 100% { transform: translateY(0px); } 50% { transform: translateY(-4px); } }
        .pulse-animation { animation: pulse 6s infinite; will-change: auto; }
        @keyframes pulse { 0%, 100% { opacity: 0.95; } 50% { opacity: 0.8; } }
        .slide-in { animation: slideIn 0.6s ease-out; will-change: transform, opacity; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px) translateZ(0); } to { opacity: 1; transform: translateY(0) translateZ(0); } }
        .code-preview { max-height: 200px; overflow: hidden; position: relative; }
        .code-preview::after { content: ''; position: absolute; bottom: 0; left: 0; right: 0; height: 50px; background: linear-gradient(transparent, #1a1a1a); }
        .progress-ring { transform: rotate(-90deg); }
        .progress-ring-circle { transition: stroke-dashoffset 0.35s; transform-origin: 50% 50%; }
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
        .dark-mode .glass-effect { background: rgba(22, 32, 42, 0.8) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .advanced-search { background: rgba(22, 32, 42, 0.9) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        .dark-mode .glass-card { background: rgba(22, 32, 42, 0.95) !important; border-color: rgba(255, 255, 255, 0.1) !important; }
        
        /* Navigation Dark Mode */
        .dark-mode .modern-nav { background: rgba(22, 32, 42, 0.95) !important; backdrop-filter: blur(20px); border-bottom: 1px solid rgba(255, 255, 255, 0.1); }
        .dark-mode .nav-link { color: #d1d5db !important; }
        .dark-mode .nav-link:hover { color: #ffffff !important; background: rgba(59, 130, 246, 0.1) !important; }
        /* Project cards in dark mode */
        .dark-mode .project-card,
        .dark-mode .clean-card,
        .dark-mode .card-hover,
        .dark-mode .bg-gradient-to-br.from-white.to-gray-50 {
            background: linear-gradient(135deg, #151c24 0%, #1a2430 100%) !important;
            color: #e5e7eb !important;
            border-color: #2a343e !important;
        }
        .dark-mode .project-card h4,
        .dark-mode .card-hover h4 { color: #ffffff !important; }
        .dark-mode .project-card p,
        .dark-mode .card-hover p { color: #a0a0a0 !important; }
        /* Form Elements Dark Mode */
        .dark-mode input { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode input::placeholder { color: #888888 !important; }
        .dark-mode select { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode textarea { background: #1e2a36 !important; border-color: #2a343e !important; color: #ffffff !important; }
        .dark-mode button { border-color: #2a343e !important; }
        
        /* Hover States Dark Mode */
        .dark-mode .hover\\:bg-gray-50:hover { background: #252540 !important; }
        .dark-mode .hover\\:bg-gray-100:hover { background: #374151 !important; }
        .dark-mode .hover\\:bg-blue-50:hover { background: rgba(59, 130, 246, 0.1) !important; }
        .dark-mode .hover\\:bg-green-50:hover { background: rgba(34, 197, 94, 0.1) !important; }
        .dark-mode .hover\\:bg-purple-50:hover { background: rgba(168, 85, 247, 0.1) !important; }
        
        /* Background Variants Dark Mode */
        .dark-mode .bg-white\\/95 { background: rgba(26, 26, 46, 0.95) !important; }
        .dark-mode .bg-white\\/90 { background: rgba(26, 26, 46, 0.9) !important; }
        .dark-mode .bg-white\\/80 { background: rgba(26, 26, 46, 0.8) !important; }
        .dark-mode .bg-white\\/70 { background: rgba(26, 26, 46, 0.7) !important; }
        .syntax-highlight { background: #1a1a2e; border-radius: 12px; padding: 20px; margin: 10px 0; }
        /* Chip-style buttons */
        .chip-btn {
            display: inline-flex;
            align-items: center;
            gap: .375rem;
            border-radius: 9999px !important;
            padding: .5rem .875rem !important;
            font-size: .875rem !important;
            line-height: 1 !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 2px rgba(0,0,0,.06);
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .chip-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 10px rgba(0,0,0,.10); }
        .dark-mode .chip-btn { box-shadow: 0 1px 2px rgba(0,0,0,.4); }
        .tag-cloud { display: flex; flex-wrap: wrap; gap: 8px; margin: 16px 0; }
        .tag { background: linear-gradient(45deg, #667eea, #764ba2); color: white; padding: 4px 12px; border-radius: 20px; font-size: 12px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; }
        .tag:hover { transform: scale(1.1); box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); }
        .search-highlight { background: linear-gradient(45deg, #f093fb, #f5576c); color: white; padding: 2px 6px; border-radius: 4px; }
        .loading-spinner { border: 3px solid #f3f3f3; border-top: 3px solid #667eea; border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; }
        
        /* Security: Disable text selection and prevent copying */
        * {
            -webkit-user-select: none !important;
            -moz-user-select: none !important;
            -ms-user-select: none !important;
            user-select: none !important;
            -webkit-touch-callout: none !important;
            -webkit-tap-highlight-color: transparent !important;
        }
        
        /* Allow text selection only for input fields */
        input, textarea {
            -webkit-user-select: text !important;
            -moz-user-select: text !important;
            -ms-user-select: text !important;
            user-select: text !important;
        }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .particle { position: absolute; width: 1px; height: 1px; background: rgba(102, 126, 234, 0.08); border-radius: 50%; animation: particle-float 30s infinite linear; will-change: auto; }
        @keyframes particle-float { 0% { transform: translateY(100vh) translateZ(0); opacity: 0; } 20%, 80% { opacity: 0.2; } 100% { transform: translateY(-20px) translateZ(0); opacity: 0; } }
        .code-rain { position: fixed; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; z-index: 1; overflow: hidden; opacity: 0.3; }
        .code-char { position: absolute; color: rgba(102, 126, 234, 0.08); font-family: 'Courier New', monospace; font-size: 12px; animation: code-fall 15s infinite linear; will-change: transform; }
        @keyframes code-fall { 0% { transform: translateY(-50px) translateZ(0); opacity: 0; } 20% { opacity: 0.5; } 80% { opacity: 0.5; } 100% { transform: translateY(100vh) translateZ(0); opacity: 0; } }
        .modal-backdrop { backdrop-filter: blur(5px); background: rgba(0, 0, 0, 0.5); }
        .advanced-search { background: linear-gradient(135deg, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0.05) 100%); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); }
        
        /* Advanced Search Responsive Styles */
        @media (max-width: 640px) {
            #advancedSearchPanel {
                position: fixed !important;
                top: 70px !important;
                left: 12px !important;
                right: 12px !important;
                transform: none !important;
                max-width: none !important;
                margin: 0 !important;
                z-index: 50 !important;
            }
            
            .advanced-search {
                background: rgba(255, 255, 255, 0.98) !important;
                backdrop-filter: blur(8px);
                margin-top: 0;
                max-height: calc(100vh - 90px);
                overflow-y: auto;
                -webkit-overflow-scrolling: touch;
                border: 1px solid rgba(0, 0, 0, 0.1);
            }
            
            .advanced-search label {
                font-size: 0.875rem;
                margin-bottom: 0.5rem;
                font-weight: 600;
                color: #374151;
            }
            
            .advanced-search select,
            .advanced-search input {
                font-size: 1rem;
                padding: 0.875rem 1rem;
                min-height: 48px; /* Better touch target */
                border-radius: 0.75rem;
                border: 1.5px solid #d1d5db;
                transition: all 0.2s ease;
            }
            
            .advanced-search select:focus,
            .advanced-search input:focus {
                border-color: #3b82f6;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
                outline: none;
            }
            
            /* Better button spacing on mobile */
            .advanced-search button {
                min-height: 48px;
                font-size: 1rem;
                font-weight: 600;
            }
        }
        
        /* Additional mobile improvements */
        @media (max-width: 480px) {
            #advancedSearchPanel {
                left: 8px !important;
                right: 8px !important;
                top: 65px !important;
            }
            
            .advanced-search {
                padding: 1rem !important;
                border-radius: 1rem !important;
            }
        }
        
        /* Auth Modal Styles */
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
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
    </style>
</head>
<body class="bg-gray-50 min-h-screen relative">

    <!-- Modern Enhanced Header -->
    <header class="modern-nav sticky top-0 z-50 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-3 sm:py-4">
                <div class="flex items-center space-x-3 sm:space-x-6 space-x-reverse flex-shrink-0">
                    <div class="relative group flex-shrink-0">
                        <img src="./logo.png" alt="سورس کده" class="h-16 sm:h-20 lg:h-24 w-auto object-contain drop-shadow-xl transition-all duration-700 group-hover:scale-125 group-hover:drop-shadow-2xl group-hover:brightness-110 filter" onerror="console.log('Logo not found!'); this.style.display='none';">
                        <div class="absolute -top-1 -right-1 w-3 h-3 sm:w-4 sm:h-4 bg-gradient-to-r from-emerald-400 to-green-500 rounded-full shadow-xl">
                            <div class="w-full h-full bg-emerald-300 rounded-full animate-pulse-slow"></div>
                        </div>
                    </div>
                    <div class="flex flex-col justify-center min-w-0 flex-1">
                        <div class="flex items-center flex-wrap gap-2 sm:gap-3">
                            <h1 class="text-xl sm:text-4xl font-black bg-gradient-to-r from-gray-900 via-blue-900 to-purple-900 bg-clip-text text-transparent tracking-tight whitespace-normal break-words flex-shrink-0">
                              سورس بان
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
                        <a href="javascript:void(0)" onclick="showSection('leaderboard')" class="nav-link group relative text-gray-600 hover:text-amber-600 font-medium transition-all duration-300 text-sm px-3 py-2 rounded-lg hover:bg-amber-50/60">
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
                                        <img id="userAvatar" src="" alt="User Avatar" class="w-full h-full object-cover bg-white" style="display: none;">
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
                            <a href="seller-panel.php" class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                <svg class="w-4 h-4 ml-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v4H3zM3 7h18v14H3z"></path>
                                </svg>
                                پنل فروش
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
                        <a href="seller-panel.php" class="block px-4 py-3 text-gray-700 hover:bg-gray-50 rounded-lg transition-colors">
                            <i class="fas fa-store ml-3 w-5"></i>پنل فروش
                        </a>
                        <!-- لینک‌های ادمین موبایل حذف شدند -->
                        <button onclick="handleLogout()" class="w-full text-right px-4 py-3 text-red-600 hover:bg-red-50 rounded-lg transition-colors">
                            <i class="fas fa-sign-out-alt ml-3 w-5"></i>خروج
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <div id="advancedSearchPanel" class="hidden fixed top-16 sm:top-20 left-1/2 transform -translate-x-1/2 z-40 w-full max-w-4xl mx-2 sm:mx-4">
        <div class="advanced-search rounded-2xl p-3 sm:p-4 lg:p-6 shadow-2xl">
            <div class="grid grid-cols-1 gap-4 sm:gap-3 lg:gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">زبان برنامه‌نویسی</label>
                    <select id="filterLanguage" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">همه زبان‌ها</option>
                        <option value="javascript">JavaScript</option>
                        <option value="python">Python</option>
                        <option value="php">PHP</option>
                        <option value="java">Java</option>
                        <option value="cpp">C++</option>
                        <option value="react">React</option>
                        <option value="vue">Vue.js</option>
                        <option value="angular">Angular</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">دسته‌بندی</label>
                    <select id="filterCategory" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">همه دسته‌ها</option>
                        <option value="web">وب</option>
                        <option value="mobile">موبایل</option>
                        <option value="desktop">دسکتاپ</option>
                        <option value="ai">هوش مصنوعی</option>
                        <option value="game">بازی</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">سطح پیچیدگی</label>
                    <select id="filterLevel" class="w-full px-4 py-2 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500">
                        <option value="">همه سطوح</option>
                        <option value="beginner">مبتدی</option>
                        <option value="intermediate">متوسط</option>
                        <option value="advanced">پیشرفته</option>
                        <option value="expert">حرفه‌ای</option>
                    </select>
                </div>
            </div>
            <div class="flex flex-col space-y-3 mt-6 sm:flex-row sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse sm:mt-4">
                <button onclick="clearAdvancedSearch()" class="w-full sm:w-auto px-4 py-3 sm:py-2 text-gray-600 hover:text-gray-800 transition-colors order-2 sm:order-1 rounded-lg border border-gray-300 hover:bg-gray-50">پاک کردن</button>
                <button onclick="applyAdvancedSearch()" class="w-full sm:w-auto gradient-primary text-white px-6 py-3 sm:py-2 rounded-lg font-medium hover:shadow-lg transition-all duration-300 order-1 sm:order-2">اعمال فیلتر</button>
            </div>
        </div>
    </div>

    <style>
        /* Modern Enhanced Ads Styling */
        .header-ads-section {
            margin-bottom: 3rem;
        }
        
        .ad-container {
            position: relative;
            transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
            backdrop-filter: blur(10px);
        }
        
        .ad-header-banner {
            background-size: contain;
            background-position: center;
            background-attachment: scroll;
            background-repeat: no-repeat;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background-clip: padding-box;
        }
        
        .ad-header-banner img {
            object-fit: contain !important;
            object-position: center !important;
            max-width: 100%;
            max-height: 100%;
        }
        
        /* Mobile optimizations */
        @media (max-width: 768px) {
            .ad-header-banner {
                min-height: 220px !important;
            }
            .ad-header-banner .flex {
                flex-direction: column !important;
                gap: 1rem !important;
                padding: 1rem !important;
            }
            .ad-header-banner h1 {
                font-size: 1.25rem !important;
                line-height: 1.3 !important;
            }
            .ad-header-banner p {
                font-size: 0.875rem !important;
            }
            .ad-header-banner .inline-flex {
                padding: 0.5rem 1rem !important;
                font-size: 0.875rem !important;
            }
        }
        
        @media (max-width: 640px) {
            .ad-header-banner {
                min-height: 180px !important;
                margin-bottom: 1rem !important;
            }
            .ad-header-banner h1 {
                font-size: 1.125rem !important;
            }
            .ad-header-banner p {
                font-size: 0.8rem !important;
            }
        }
        
        /* Hover effects */
        .ad-container:hover {
            transform: translateY(-4px) scale(1.02);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        }
        
        .ad-sidebar {
            max-width: 350px;
            backdrop-filter: blur(20px);
        }
        
        .ad-inline {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 50%, #e2e8f0 100%);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1);
        }
        
        /* Advanced animations */
        .ad-container {
            animation: fadeInUp 0.8s cubic-bezier(0.25, 0.46, 0.45, 0.94);
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(40px) scale(0.95);
                filter: blur(5px);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
                filter: blur(0);
            }
        }
        
        /* Gradient text animation */
        .ad-container .bg-gradient-to-r.bg-clip-text {
            background-size: 200% 200%;
            animation: gradientShift 3s ease-in-out infinite;
        }
        
        @keyframes gradientShift {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }
        
        /* Button hover effects */
        .ad-container .bg-gradient-to-r:not(.bg-clip-text):hover {
            background-size: 200% 200%;
            animation: gradientShift 0.5s ease-in-out;
        }
        
        /* Glass morphism effect */
        .ad-header-banner::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(10px);
            border-radius: inherit;
            z-index: 1;
        }
        
        /* Responsive typography */
        @media (min-width: 1024px) {
            .ad-header-banner h1 {
                font-size: 4rem !important;
            }
            .ad-header-banner p {
                font-size: 1.5rem !important;
            }
        }
        
        @media (min-width: 1280px) {
            .ad-header-banner h1 {
                font-size: 5rem !important;
            }
            .ad-header-banner p {
                font-size: 1.75rem !important;
            }
        }
    </style>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header Ads -->
        <div class="header-ads-section">
            <?php echo display_header_ads(1); ?>
        </div>
        
        <div id="dashboardSection" class="slide-in">
            <!-- Professional Hero Section -->
            <style>
                .hero-site {
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    border-radius: 24px;
                    position: relative;
                    overflow: hidden;
                    min-height: 500px;
                    padding: 60px 0;
                    margin-bottom: 2rem;
                }
                
                .hero-content-wrapper {
                    position: relative;
                    z-index: 10;
                }
                
                .hero-title {
                    font-size: 3rem;
                    font-weight: 700;
                    color: #1e293b;
                    margin-bottom: 1rem;
                    line-height: 1.2;
                }
                
                .hero-title .highlight {
                    color: #10b981;
                    font-weight: 800;
                }
                
                .hero-subtitle {
                    font-size: 1.1rem;
                    color: #64748b;
                    margin-bottom: 2rem;
                    line-height: 1.6;
                    display: flex;
                    align-items: center;
                }
                
                .hero-subtitle i {
                    width: 4px;
                    height: 4px;
                    background: #10b981;
                    border-radius: 50%;
                    margin-left: 8px;
                    display: inline-block;
                }
                
                .hero-features {
                    margin-bottom: 2.5rem;
                }
                
                .hero-features ul {
                    list-style: none;
                    padding: 0;
                    margin: 0;
                }
                
                .hero-features li {
                    padding: 8px 0;
                    color: #475569;
                    font-size: 0.95rem;
                    position: relative;
                    padding-right: 20px;
                }
                
                .hero-features li::before {
                    content: '✓';
                    position: absolute;
                    right: 0;
                    top: 8px;
                    color: #10b981;
                    font-weight: bold;
                    font-size: 0.9rem;
                }
                
                .hero-buttons {
                    margin-bottom: 2rem;
                }
                
                .button {
                    display: inline-block;
                    padding: 14px 28px;
                    border-radius: 12px;
                    font-weight: 600;
                    font-size: 0.95rem;
                    text-decoration: none;
                    transition: all 0.3s ease;
                    margin-left: 12px;
                    margin-bottom: 8px;
                }
                
                .button-green {
                    background: linear-gradient(135deg, #10b981, #059669);
                    color: white;
                    box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
                }
                
                .button-green:hover {
                    transform: translateY(-2px);
                    box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
                }
                
                .button-border {
                    border: 2px solid #e2e8f0;
                    color: #64748b;
                    background: white;
                }
                
                .button-border:hover {
                    border-color: #10b981;
                    color: #10b981;
                    transform: translateY(-2px);
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                }
                
                .hero-contact {
                    color: #64748b;
                    font-size: 0.9rem;
                    text-decoration: none;
                    display: inline-block;
                    margin-top: 1rem;
                }
                
                .hero-contact:hover {
                    color: #10b981;
                }
                
                .mobile-wrapper {
                    position: relative;
                    max-width: 400px;
                    margin: 0 auto;
                }
                
                .mobile-device {
                    background: linear-gradient(145deg, #1e293b, #334155);
                    border-radius: 25px;
                    padding: 20px 15px;
                    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
                    position: relative;
                    max-width: 280px;
                    margin: 0 auto;
                }
                
                .mobile-screen {
                    background: #0f172a;
                    border-radius: 15px;
                    padding: 15px;
                    min-height: 350px;
                    position: relative;
                    overflow: hidden;
                }
                
                .message-carousel {
                    display: flex;
                    flex-direction: column;
                    gap: 12px;
                    animation: slideMessages 12s infinite linear;
                }
                
                @keyframes slideMessages {
                    0% { transform: translateY(0); }
                    20% { transform: translateY(-60px); }
                    40% { transform: translateY(-120px); }
                    60% { transform: translateY(-180px); }
                    80% { transform: translateY(-240px); }
                    100% { transform: translateY(0); }
                }
                
                .message-item {
                    background: linear-gradient(135deg, #10b981, #059669);
                    color: white;
                    padding: 12px 15px;
                    border-radius: 12px;
                    font-size: 0.8rem;
                    line-height: 1.4;
                    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
                    min-height: 48px;
                    display: flex;
                    align-items: center;
                }
                
                .mobile-header {
                    background: #374151;
                    color: white;
                    text-align: center;
                    padding: 8px;
                    border-radius: 8px;
                    font-size: 0.85rem;
                    font-weight: 600;
                    margin-bottom: 15px;
                }
                
                .stats-card {
                    background: white;
                    border-radius: 16px;
                    padding: 20px;
                    text-align: center;
                    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
                    transition: all 0.3s ease;
                    border: 1px solid #e2e8f0;
                }
                
                .stats-card:hover {
                    transform: translateY(-3px);
                    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
                }
                
                
                @media (max-width: 768px) {
                    .hero-site {
                        min-height: 400px;
                        padding: 40px 0;
                        border-radius: 20px;
                    }
                    .hero-title {
                        font-size: 2.2rem;
                    }
                    .mobile-device {
                        max-width: 240px;
                    }
                    .mobile-screen {
                        min-height: 280px;
                        padding: 12px;
                    }
                    .message-item {
                        font-size: 0.75rem;
                        padding: 10px 12px;
                    }
                }
            </style>
            
            <section class="hero-site">
                <div class="container mx-auto px-4">
                    <div class="hero-content-wrapper">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center">
                            <!-- Left Content (5 columns) -->
                            <div class="lg:col-span-5">
                                <h1 class="hero-title">
                                    دانلود <span class="highlight">سورس کد رایگان</span> و کد منبع برنامه نویسی
                                </h1>
                                
                                <div class="hero-subtitle">
                                    <i></i>
                                    با استفاده از سورس بان، پروژه‌های متن‌باز را کشف کنید و سایت یا نرم‌افزارتان را به ابزار قدرتمند توسعه مجهز کنید.
                                </div>
                                
                                <div class="hero-features">
                                    <ul>
                                        <li>دسترسی به هزاران پروژه متن‌باز با کیفیت بالا</li>
                                        <li>جدیدترین فریمورک‌ها و کتابخانه‌های برنامه‌نویسی</li>
                                        <li>انجمن فعال توسعه‌دهندگان برای پشتیبانی و راهنمایی</li>
                                        <li>ابزارهای هوشمند برای مدیریت و توسعه پروژه‌ها</li>
                                    </ul>
                                </div>
                                
                                <div class="hero-buttons">
                                    <a href="sources.php" class="button button-green">
                                        کاوش سورس‌ها + دسترسی رایگان
                                    </a>
                                    <a href="upload.php" class="button button-border">
                                        آپلود پروژه جدید
                                    </a>
                                </div>
                                
                                <a href="" class="hero-contact">

                                </a>
                            </div>
                            
                            <!-- Right Animation (7 columns) -->
                            <div class="lg:col-span-7">
                                <img src="https://cdn.dribbble.com/users/1292677/screenshots/6139167/media/fcf7fd0c619bb87706533079240915f3.gif" alt="Coding animation" class="w-full h-auto rounded-xl shadow-2xl">
                            </div>
                        </div>
                        
                        <!-- Stats Section -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-16 max-w-5xl mx-auto">
                            <div class="stats-card">
                                <div class="text-3xl font-bold text-gray-800 mb-2" id="totalProjects">0</div>
                                <div class="text-sm text-gray-600 font-medium">پروژه فعال</div>
                            </div>
                            <div class="stats-card">
                                <div class="text-3xl font-bold text-gray-800 mb-2" id="totalDevelopers">0</div>
                                <div class="text-sm text-gray-600 font-medium">توسعه‌دهنده</div>
                            </div>
                            <div class="stats-card">
                                <div class="text-3xl font-bold text-gray-800 mb-2" id="totalDownloads">0</div>
                                <div class="text-sm text-gray-600 font-medium">دانلود</div>
                            </div>
                            <div class="stats-card">
                                <div class="text-3xl font-bold text-gray-800 mb-2" id="totalStars">0</div>
                                <div class="text-sm text-gray-600 font-medium">ستاره</div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="sources.php" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105 block"><div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-xl flex items-center justify-center mb-4"><span class="text-white text-xl">💎</span></div><h3 class="font-bold text-gray-900 mb-2">کاوش سورس‌ها</h3><p class="text-gray-600 text-sm">آخرین پروژه‌های تایید شده را کشف کنید</p></a>
                <a href="shop.php" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105 block"><div class="w-12 h-12 bg-gradient-to-r from-orange-500 to-red-500 rounded-xl flex items-center justify-center mb-4"><span class="text-white text-xl">🛒</span></div><h3 class="font-bold text-gray-900 mb-2">فروشگاه</h3><p class="text-gray-600 text-sm">خرید و فروش سورس کدهای آماده و حرفه‌ای</p></a>
                <a href="upload.php" class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105 block"><div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-purple-600 rounded-xl flex items-center justify-center mb-4"><span class="text-white text-xl">📤</span></div><h3 class="font-bold text-gray-900 mb-2">آپلود پروژه</h3><p class="text-gray-600 text-sm">پروژه جدید خود را با جامعه به اشتراک بگذارید</p></a>
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer transform hover:scale-105" onclick="showSection('explore')"><div class="w-12 h-12 bg-gradient-to-r from-pink-500 to-red-500 rounded-xl flex items-center justify-center mb-4"><span class="text-white text-xl">🔍</span></div><h3 class="font-bold text-gray-900 mb-2">جستجوی پیشرفته</h3><p class="text-gray-600 text-sm">پروژه‌های مورد نظر خود را پیدا کنید</p></div>
            </div>

            <div class="bg-white rounded-2xl shadow-lg p-8 mb-8">
                <div class="flex justify-between items-center mb-6"><h2 class="text-2xl font-bold text-gray-900">سورس کدهای ترند و پروژه‌های رایگان</h2><a href="sources.php" class="text-blue-600 hover:text-blue-700 font-medium transition-colors">مشاهده همه</a></div>
                <div id="trendingProjects" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <div class="bg-white rounded-2xl shadow-lg p-8"><h3 class="text-xl font-bold text-gray-900 mb-6">فعالیت‌های اخیر</h3><div id="recentActivity" class="space-y-4"></div></div>
                <div class="bg-white rounded-2xl shadow-lg p-8"><h3 class="text-xl font-bold text-gray-900 mb-6">توسعه‌دهندگان برتر</h3><div id="topDevelopers" class="space-y-4"></div></div>
            </div>
        </div>

        <div id="exploreSection" class="hidden">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 sm:mb-8 space-y-4 sm:space-y-0">
                <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">کاوش و دانلود پروژه‌های برنامه نویسی</h2>
                <button onclick="toggleAdvancedSearch()" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white px-4 sm:px-6 py-2.5 sm:py-3 rounded-xl font-medium transition-colors">
                    <svg class="w-5 h-5 inline ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-2 8h.01M12 20h.01M20 12h.01M4 12h.01M12 16a2 2 0 100-4 2 2 0 000 4z"></path>
                    </svg>
                    فیلتر پیشرفته
                </button>
            </div>
            
            <!-- Advanced Search Panel -->
            <div id="advancedSearchPanel" class="hidden bg-white rounded-2xl shadow-lg p-4 sm:p-6 mb-8">
                <h3 class="text-lg sm:text-xl font-bold text-gray-900 mb-4">جستجوی پیشرفته</h3>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4 sm:gap-3 lg:gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">جستجو در عنوان</label>
                        <input type="text" id="searchTitle" placeholder="عنوان پروژه..." class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">زبان برنامه‌نویسی</label>
                        <select id="filterLanguage" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">همه زبان‌ها</option>
                            <option value="javascript">JavaScript</option>
                            <option value="python">Python</option>
                            <option value="php">PHP</option>
                            <option value="java">Java</option>
                            <option value="cpp">C++</option>
                            <option value="react">React</option>
                            <option value="vue">Vue.js</option>
                            <option value="angular">Angular</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">سطح پیچیدگی</label>
                        <select id="filterLevel" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">همه سطوح</option>
                            <option value="beginner">مبتدی</option>
                            <option value="intermediate">متوسط</option>
                            <option value="advanced">پیشرفته</option>
                            <option value="expert">حرفه‌ای</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">مرتب‌سازی</label>
                        <select id="sortBy" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="newest">جدیدترین</option>
                            <option value="popular">محبوب‌ترین</option>
                            <option value="downloaded">پردانلود</option>
                            <option value="title">عنوان (الفبایی)</option>
                        </select>
                    </div>
                </div>
                <div class="flex flex-col space-y-3 sm:flex-row sm:justify-end sm:space-y-0 sm:space-x-3 sm:space-x-reverse">
                    <button onclick="clearAdvancedSearch()" class="w-full sm:w-auto px-4 py-3 sm:py-2 text-gray-600 hover:text-gray-800 order-2 sm:order-1 rounded-lg border border-gray-300 hover:bg-gray-50">پاک کردن</button>
                    <button onclick="applyAdvancedSearch()" class="w-full sm:w-auto px-6 py-3 sm:py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg order-1 sm:order-2 font-medium">اعمال فیلترها</button>
                </div>
            </div>
            
            <!-- Project Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-gradient-to-r from-blue-500 to-indigo-500 text-white rounded-2xl p-6 text-center">
                    <div class="text-3xl font-bold" id="totalProjectsCount">0</div>
                    <div class="text-sm opacity-90">کل پروژه‌ها</div>
                </div>
                <div class="bg-gradient-to-r from-green-500 to-teal-500 text-white rounded-2xl p-6 text-center">
                    <div class="text-3xl font-bold" id="totalDownloadsCount">0</div>
                    <div class="text-sm opacity-90">کل دانلودها</div>
                </div>
                <div class="bg-gradient-to-r from-yellow-500 to-orange-500 text-white rounded-2xl p-6 text-center">
                    <div class="text-3xl font-bold" id="totalStarsCount">0</div>
                    <div class="text-sm opacity-90">کل ستاره‌ها</div>
                </div>
                <div class="bg-gradient-to-r from-purple-500 to-pink-500 text-white rounded-2xl p-6 text-center">
                    <div class="text-3xl font-bold" id="totalLanguagesCount">0</div>
                    <div class="text-sm opacity-90">زبان‌های مختلف</div>
                </div>
            </div>
            
            <div id="exploreList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
            
            <!-- Empty State -->
            <div id="exploreEmpty" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">پروژه‌ای یافت نشد</h3>
                <p class="text-gray-600">فیلترهای خود را تغییر دهید یا کلمات جستجو را بررسی کنید</p>
            </div>
        </div>

        <!-- Sources Section -->
        <div id="sourcesSection" class="hidden">
            <div class="mb-8">
                <h2 class="text-3xl font-bold text-gray-900">کاوش سورس کدهای رایگان</h2>
                <p class="text-gray-600 mt-2">آخرین پروژه‌های تایید شده و کد منبع برنامه نویسی کیفیت بالا</p>
            </div>
            
            <!-- Filter Buttons -->
            <div class="flex flex-wrap gap-4 mb-8">
                <button onclick="filterSources('all')" id="filterAll" class="px-6 py-3 bg-purple-600 text-white rounded-xl font-medium transition-colors">
                    همه پروژه‌ها
                </button>
                <button onclick="filterSources('JavaScript')" id="filterJS" class="px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors">
                    JavaScript
                </button>
                <button onclick="filterSources('PHP')" id="filterPHP" class="px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors">
                    PHP
                </button>
                <button onclick="filterSources('Python')" id="filterPython" class="px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors">
                    Python
                </button>
                <button onclick="filterSources('newest')" id="filterNewest" class="px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors">
                    جدیدترین
                </button>
                <button onclick="filterSources('popular')" id="filterPopular" class="px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors">
                    محبوب‌ترین
                </button>
            </div>

            <!-- Sources Stats -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                    <div class="text-3xl font-bold text-purple-600" id="sourcesTotalCount">0</div>
                    <div class="text-sm text-gray-600">کل پروژه‌ها</div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                    <div class="text-3xl font-bold text-green-600" id="sourcesDownloadsCount">0</div>
                    <div class="text-sm text-gray-600">کل دانلودها</div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                    <div class="text-3xl font-bold text-yellow-600" id="sourcesStarsCount">0</div>
                    <div class="text-sm text-gray-600">کل ستاره‌ها</div>
                </div>
                <div class="bg-white rounded-2xl p-6 shadow-lg text-center">
                    <div class="text-3xl font-bold text-blue-600" id="sourcesLanguagesCount">0</div>
                    <div class="text-sm text-gray-600">زبان‌های مختلف</div>
                </div>
            </div>

            <!-- Sources List -->
            <div id="sourcesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6"></div>
            
            <!-- Empty State -->
            <div id="sourcesEmpty" class="hidden text-center py-16">
                <div class="w-24 h-24 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <span class="text-4xl">💎</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">هنوز پروژه‌ای تایید نشده</h3>
                <p class="text-gray-600">صبر کنید تا ادمین‌ها پروژه‌های ارسالی را تایید کنند</p>
            </div>
        </div>

        <div id="communitySection" class="hidden">
            <div class="text-center py-16">
                <div class="w-32 h-32 bg-gradient-to-br from-blue-500 to-purple-600 rounded-3xl flex items-center justify-center mx-auto mb-8 shadow-2xl">
                    <i class="fas fa-comments text-white text-5xl"></i>
                </div>
                <h2 class="text-4xl font-bold text-gray-900 mb-4">انجمن توسعه‌دهندگان</h2>
                <p class="text-xl text-gray-600 mb-8 max-w-2xl mx-auto">به انجمن فعال ما بپیوندید و با توسعه‌دهندگان دیگر گفتگو کنید</p>
                <a href="forum.php" class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-2xl font-semibold text-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105">
                    <i class="fas fa-arrow-left ml-3"></i>
                    ورود به انجمن
                </a>
            </div>
        </div>
        <div id="leaderboardSection" class="hidden">
            <h2 class="text-3xl font-bold text-gray-900 mb-8">رتبه‌بندی توسعه‌دهندگان</h2>
            
            <!-- Loading State -->
            <div id="leaderboardLoading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
                <p class="mt-4 text-gray-600">در حال بارگذاری رتبه‌بندی...</p>
            </div>
            
            <!-- Leaderboard Categories -->
            <div id="leaderboardTabs" class="flex flex-wrap gap-4 mb-8 hidden">
                <button onclick="loadLeaderboard('points')" class="leaderboard-tab active px-6 py-3 bg-blue-600 text-white rounded-xl font-medium transition-colors" data-type="points">
                    <i class="fas fa-trophy ml-2"></i>
                    بیشترین امتیاز
                </button>
                <button onclick="loadLeaderboard('uploads')" class="leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors" data-type="uploads">
                    <i class="fas fa-upload ml-2"></i>
                    بیشترین آپلود
                </button>
                <button onclick="loadLeaderboard('downloads')" class="leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors" data-type="downloads">
                    <i class="fas fa-download ml-2"></i>
                    پردانلودترین
                </button>
                <button onclick="loadLeaderboard('stars')" class="leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors" data-type="stars">
                    <i class="fas fa-star ml-2"></i>
                    محبوب‌ترین
                </button>
                <button onclick="loadLeaderboard('forum_posts')" class="leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors" data-type="forum_posts">
                    <i class="fas fa-comments ml-2"></i>
                    فعال‌ترین در انجمن
                </button>
            </div>
            
            <!-- Top 3 Podium -->
            <div id="topThree" class="bg-gradient-to-br from-yellow-400 via-yellow-500 to-orange-500 rounded-3xl p-8 mb-8 text-white hidden">
                <h3 class="text-2xl font-bold text-center mb-8">🏆 سه برتر <span id="categoryTitle">امتیاز</span></h3>
                <div class="flex justify-center items-end space-x-8 space-x-reverse">
                    <!-- Second Place -->
                    <div id="secondPlace" class="text-center">
                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold mb-4">2</div>
                        <div class="w-16 h-16 bg-gray-300 rounded-full mx-auto mb-3 flex items-center justify-center text-gray-700 font-bold text-lg avatar"></div>
                        <h4 class="font-bold text-lg name">-</h4>
                        <p class="text-yellow-100 value">-</p>
                    </div>
                    
                    <!-- First Place -->
                    <div id="firstPlace" class="text-center">
                        <div class="w-24 h-24 bg-white bg-opacity-30 rounded-full flex items-center justify-center text-4xl font-bold mb-4">1</div>
                        <div class="w-20 h-20 bg-white rounded-full mx-auto mb-3 flex items-center justify-center text-yellow-500 font-bold text-xl avatar"></div>
                        <h4 class="font-bold text-xl name">-</h4>
                        <p class="text-yellow-100 value">-</p>
                        <div class="text-2xl mt-2">👑</div>
                    </div>
                    
                    <!-- Third Place -->
                    <div id="thirdPlace" class="text-center">
                        <div class="w-20 h-20 bg-white bg-opacity-20 rounded-full flex items-center justify-center text-3xl font-bold mb-4">3</div>
                        <div class="w-16 h-16 bg-amber-600 rounded-full mx-auto mb-3 flex items-center justify-center text-white font-bold text-lg avatar"></div>
                        <h4 class="font-bold text-lg name">-</h4>
                        <p class="text-yellow-100 value">-</p>
                    </div>
                </div>
            </div>
            
            <!-- Detailed Leaderboard -->
            <div id="leaderboardTable" class="bg-white rounded-2xl shadow-lg overflow-hidden hidden">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-xl font-bold text-gray-900">جدول کامل رتبه‌بندی</h3>
                </div>
                
                <div id="leaderboardList" class="divide-y divide-gray-200">
                    <!-- Leaderboard entries will be loaded here -->
                </div>
                
                <div id="leaderboardEmpty" class="hidden p-8 text-center text-gray-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>هنوز کاربری در این رتبه‌بندی وجود ندارد</p>
                </div>
            </div>

            </div>
        </main>

    <div id="uploadModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-center mb-8"><div><h2 class="text-3xl font-bold text-gray-900">آپلود پروژه جدید</h2><p class="text-gray-600 mt-2">پروژه خود را با جامعه به اشتراک بگذارید</p></div><button onclick="closeUploadModal()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors"><svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button></div>
                <form id="uploadForm" class="space-y-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                        <div class="space-y-6">
                            <div><label class="block text-sm font-bold text-gray-900 mb-3">عنوان پروژه</label><input type="text" id="projectTitle" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors"></div>
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-3">توضیحات کامل</label>
                                <div class="relative">
                                    <textarea id="projectDescription" rows="6" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors resize-none pr-20"></textarea>
                                    <div class="absolute bottom-3 left-3 flex space-x-2 space-x-reverse">
                                        <button type="button" onclick="showImageUpload()" class="p-2 bg-blue-100 hover:bg-blue-200 text-blue-600 rounded-lg transition-colors" title="اضافه کردن تصویر">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                            </svg>
                                        </button>
                                        <button type="button" onclick="insertMarkdownHelper()" class="p-2 bg-green-100 hover:bg-green-200 text-green-600 rounded-lg transition-colors" title="راهنمای Markdown">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </button>
    </div>
                                </div>
                                <div class="mt-2 text-xs text-gray-500">
                                    💡 از Markdown برای فرمت‌بندی استفاده کنید. برای اضافه کردن تصویر از دکمه بالا استفاده کنید.
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div><label class="block text-sm font-bold text-gray-900 mb-3">زبان اصلی</label><select id="projectLanguage" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0"><option value="">انتخاب کنید</option><option value="javascript">JavaScript</option><option value="python">Python</option><option value="php">PHP</option><option value="java">Java</option><option value="cpp">C++</option><option value="react">React</option><option value="vue">Vue.js</option><option value="angular">Angular</option></select></div>
                                <div><label class="block text-sm font-bold text-gray-900 mb-3">سطح پیچیدگی</label><select id="projectLevel" required class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0"><option value="">انتخاب کنید</option><option value="beginner">مبتدی</option><option value="intermediate">متوسط</option><option value="advanced">پیشرفته</option><option value="expert">حرفه‌ای</option></select></div>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-900 mb-3">تگ‌ها</label><input type="text" id="projectTags" placeholder="React, API, Frontend (با کاما جدا کنید)" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors"><div id="tagPreview" class="tag-cloud mt-3"></div></div>
                        </div>
                        <div class="space-y-6">
                            <div>
                                <label class="block text-sm font-bold text-gray-900 mb-3">فایل پروژه (حداکثر 1024 کیلوبایت)</label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-blue-500 transition-colors cursor-pointer" id="dropZone" onclick="document.getElementById('projectFile').click()">
                                    <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                                    <p class="text-lg font-medium text-gray-900 mb-2">فایل را اینجا بکشید</p>
                                    <p class="text-gray-500">یا کلیک کنید تا انتخاب کنید</p>
                                    <p class="text-sm text-gray-400 mt-2">ZIP, RAR, یا فایل‌های کد پشتیبانی می‌شوند</p>
                                </div>
                                <input type="file" id="projectFile" class="hidden" accept=".zip,.rar,.js,.py,.php,.java,.cpp,.html,.css,.txt">
                                <div id="filePreview" class="hidden mt-6 p-4 bg-gray-50 rounded-xl">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center space-x-3 space-x-reverse">
                                            <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center"><svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg></div>
                                            <div><p id="previewFileName" class="font-medium text-gray-900"></p><p id="previewFileSize" class="text-sm text-gray-600"></p></div>
                                        </div>
                                        <button type="button" onclick="removeFile()" class="text-red-600 hover:text-red-700 p-2"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg></button>
                                    </div>
                                </div>
                            </div>
                            <div><label class="block text-sm font-bold text-gray-900 mb-3">لینک مخزن (اختیاری)</label><input type="url" id="repositoryUrl" placeholder="https://github.com/username/repo" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors"></div>
                            <div><label class="block text-sm font-bold text-gray-900 mb-3">لینک دمو (اختیاری)</label><input type="url" id="demoUrl" placeholder="https://demo.example.com" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:ring-0 transition-colors"></div>
                            <div class="bg-blue-50 rounded-xl p-6"><h4 class="font-bold text-blue-900 mb-3">💡 نکات مهم</h4><ul class="text-sm text-blue-800 space-y-2"><li>• فایل‌های با کیفیت بالا امتیاز بیشتری دریافت می‌کنند</li><li>• توضیحات کامل و واضح ارائه دهید</li><li>• تگ‌های مناسب استفاده کنید</li><li>• کد خود را مستندسازی کنید</li></ul></div>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-4 space-x-reverse pt-6 border-t">
                        <button type="button" onclick="closeUploadModal()" class="px-8 py-3 border-2 border-gray-300 rounded-xl text-gray-700 font-medium hover:bg-gray-50 transition-colors">انصراف</button>
                        <button type="submit" class="gradient-primary text-white px-8 py-3 rounded-xl font-bold hover:shadow-xl transition-all duration-300 transform hover:scale-105">انتشار پروژه</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="authModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
        <div class="w-full max-w-md transform transition-all duration-300 scale-95 opacity-0" id="authModalContent">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-purple-600 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-xl">
                    <i class="fas fa-user-circle text-white text-3xl"></i>
                </div>
                <h1 class="text-3xl font-bold text-white mb-2">خوش آمدید</h1>
                <p class="text-gray-200">به حساب کاربری خود دسترسی پیدا کنید</p>
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
                    <div id="authLoading" class="hidden text-center py-8">
                        <div class="loading-spinner mx-auto mb-4"></div>
                        <p class="text-gray-600">در حال پردازش...</p>
                    </div>
                    
                    <form id="authForm" class="space-y-6">
                        <!-- Username/Email Input -->
                        <div class="input-group relative">
                            <input type="email" id="email" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="ایمیل" required>
                            <label for="email" class="floating-label absolute right-4 top-4 text-gray-500 pointer-events-none">ایمیل</label>
                            <i class="fas fa-envelope absolute left-4 top-4 text-gray-400"></i>
                        </div>

                        <!-- Password Input -->
                        <div class="input-group relative">
                            <input type="password" id="password" class="w-full px-4 py-4 border-2 border-gray-200 rounded-2xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent pl-12" placeholder="رمز عبور" required>
                            <label for="password" class="floating-label absolute right-4 top-4 text-gray-500 pointer-events-none">رمز عبور</label>
                            <button type="button" onclick="toggleAuthPassword('password', this)" class="absolute left-4 top-4 text-gray-400 hover:text-gray-600 transition-colors">
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

                        <!-- Login Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-purple-600 text-white py-4 rounded-2xl font-semibold btn-hover ripple shadow-lg">
                            <i class="fas fa-sign-in-alt ml-2"></i>
                            <span id="authButtonText">ورود به حساب</span>
                        </button>
                    </form>
                </div>

                <!-- Register Form -->
                <div id="registerForm" class="p-6 pt-0 hidden">
                    <form class="space-y-5">
                        <!-- Full Name Input -->
                        <div id="nameField" class="input-group relative">
                            <input type="text" id="fullName" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="نام کامل" required>
                            <label for="fullName" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">نام کامل</label>
                            <i class="fas fa-user absolute left-4 top-3 text-gray-400"></i>
                        </div>

                        <!-- Email -->
                        <div class="input-group relative">
                            <input type="email" id="regEmail" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent" placeholder="ایمیل" required>
                            <label for="regEmail" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">ایمیل</label>
                            <i class="fas fa-envelope absolute left-4 top-3 text-gray-400"></i>
                        </div>

                        <!-- Password -->
                        <div class="input-group relative">
                            <input type="password" id="regPassword" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl focus:border-blue-500 focus:outline-none input-focus bg-white text-gray-800 placeholder-transparent pl-12" placeholder="رمز عبور" required>
                            <label for="regPassword" class="floating-label absolute right-4 top-3 text-gray-500 pointer-events-none text-sm">رمز عبور</label>
                            <button type="button" onclick="toggleAuthPassword('regPassword', this)" class="absolute left-4 top-3 text-gray-400 hover:text-gray-600 transition-colors">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>

                        <!-- Password Strength -->
                        <div id="passwordStrength" class="space-y-2">
                            <div class="flex space-x-1 space-x-reverse">
                                <div class="h-2 flex-1 bg-gray-200 rounded-full overflow-hidden">
                                    <div id="strengthBar" class="h-full strength-bar rounded-full bg-gray-300 transition-all duration-300" style="width: 0%"></div>
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

                        <!-- Register Button -->
                        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-blue-600 text-white py-4 rounded-2xl font-semibold btn-hover ripple shadow-lg">
                            <i class="fas fa-user-plus ml-2"></i>
                            ایجاد حساب کاربری
                        </button>
                    </form>
                </div>
                
                <button onclick="closeAuth()" class="absolute top-4 left-4 p-2 hover:bg-gray-100 rounded-xl transition-colors">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Image Upload Modal -->
    <div id="imageUploadModal" class="fixed inset-0 modal-backdrop hidden z-60 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full">
            <div class="p-6">
                <h3 class="text-xl font-bold text-gray-900 mb-4">آپلود تصویر</h3>
                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-500 transition-colors cursor-pointer" onclick="document.getElementById('imageFile').click()">
                    <svg class="w-12 h-12 text-gray-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <p class="text-gray-600 mb-1">تصویر را اینجا بکشید یا کلیک کنید</p>
                    <p class="text-xs text-gray-500">حداکثر 2048 کیلوبایت - JPG, PNG, GIF, WebP</p>
                </div>
                <input type="file" id="imageFile" class="hidden" accept="image/*" onchange="handleImageUpload(event)">
                
                <div id="imagePreview" class="hidden mt-4">
                    <img id="previewImg" class="w-full rounded-lg" alt="پیش‌نمایش">
                    <div class="mt-3 p-3 bg-gray-100 rounded-lg">
                        <p class="text-sm font-medium text-gray-700">کد Markdown:</p>
                        <input type="text" id="markdownCode" readonly class="w-full mt-1 px-3 py-2 text-sm bg-white border border-gray-300 rounded focus:outline-none" onclick="this.select()">
                    </div>
                </div>
                
                <div class="flex justify-end space-x-3 space-x-reverse mt-6">
                    <button onclick="closeImageUpload()" class="px-4 py-2 text-gray-600 hover:text-gray-800">انصراف</button>
                    <button id="insertImageBtn" onclick="insertImageToDescription()" class="hidden px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">درج در توضیحات</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Project Detail Modal -->
    <div id="projectModal" class="fixed inset-0 modal-backdrop hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-3xl shadow-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
            <div class="p-8">
                <div class="flex justify-between items-start mb-6">
                    <div class="flex-1">
                        <h2 id="projectModalTitle" class="text-3xl font-bold text-gray-900 mb-2"></h2>
                        <div class="flex items-center space-x-4 space-x-reverse text-sm text-gray-600">
                            <span id="projectModalAuthor"></span>
                            <span id="projectModalDate"></span>
                            <span id="projectModalLanguage" class="px-2 py-1 bg-blue-100 text-blue-800 rounded-full"></span>
                            <span id="projectModalScanBadge" class="hidden px-2 py-1 rounded-full"></span>
                        </div>
                    </div>
                    <button onclick="closeProjectModal()" class="p-2 hover:bg-gray-100 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                
                <!-- Project Stats & Actions -->
                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl mb-6">
                    <div class="flex items-center space-x-6 space-x-reverse">
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900" id="projectModalStars">0</div>
                            <div class="text-xs text-gray-600">ستاره</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900" id="projectModalDownloads">0</div>
                            <div class="text-xs text-gray-600">دانلود</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-bold text-gray-900" id="projectModalViews">0</div>
                            <div class="text-xs text-gray-600">بازدید</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <button id="starBtn" onclick="toggleStar()" class="flex items-center px-4 py-2 rounded-lg transition-all duration-300">
                            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                            </svg>
                            <span id="starText">ستاره</span>
                        </button>
                        <button onclick="downloadProjectModal()" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            دانلود پروژه
                        </button>
                    </div>
                </div>
                
                <!-- Project Description -->
                <div class="mb-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">توضیحات پروژه</h3>
                    <div id="projectModalDescription" class="prose prose-sm max-w-none text-gray-700"></div>
                    <div id="projectModalTags" class="flex flex-wrap gap-2 mt-4"></div>
                </div>
                
                <!-- Comments Section -->
                <div class="border-t pt-8">
                    <h3 class="text-xl font-bold text-gray-900 mb-6">نظرات و بحث</h3>
                    
                    <!-- Add Comment -->
                    <div class="mb-6 p-4 bg-gray-50 rounded-xl">
                        <textarea id="newComment" rows="3" placeholder="نظر خود را بنویسید..." class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"></textarea>
                        <div class="flex justify-end mt-3">
                            <button onclick="addComment()" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium">
                                ارسال نظر
                            </button>
                        </div>
                    </div>
                    
                    <!-- Comments List -->
                    <div id="commentsList" class="space-y-4"></div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // DevTools blocking removed per request
    const api = {
        me: () => fetch('api/auth.php').then(r=>r.json()),
        login: (email,password) => fetch('api/auth.php', {method:'POST', body: toForm({action:'login', email, password})}).then(r=>r.json()),
        register: (name,email,password) => fetch('api/auth.php', {method:'POST', body: toForm({action:'register', name, email, password})}).then(r=>r.json()),
        logout: () => fetch('api/auth.php', {method:'POST', body: toForm({action:'logout'})}).then(r=>r.json()),
        uploadImage: (file) => {
            const fd = new FormData();
            fd.append('image', file);
            return fetch('api/upload-images.php', {method:'POST', body: fd}).then(r=>r.json());
        },
        interactions: {
            toggleStar: (projectId) => fetch('api/interactions.php', {method:'POST', body: toForm({action:'toggle_star', projectId})}).then(r=>r.json()),
            getComments: (projectId) => fetch(`api/interactions.php?action=get_comments&projectId=${projectId}`).then(r=>r.json()),
            addComment: (projectId, content, parentId) => fetch('api/interactions.php', {method:'POST', body: toForm({action:'add_comment', projectId, content, parentId: parentId||''})}).then(r=>r.json()),
            toggleCommentLike: (commentId) => fetch('api/interactions.php', {method:'POST', body: toForm({action:'toggle_comment_like', commentId})}).then(r=>r.json()),
            getUserInteractions: (projectId) => fetch(`api/interactions.php?action=get_user_interactions&projectId=${projectId}`).then(r=>r.json()),
        },
        stats: () => fetch('api/stats.php').then(r=>r.json()),
        trending: () => fetch('api/trending.php').then(r=>r.json()),
        list: () => fetch('api/projects.php').then(r=>r.json()),
        search: (q,language,level) => fetch(`api/projects.php?action=search&q=${encodeURIComponent(q||'')}&language=${encodeURIComponent(language||'')}&level=${encodeURIComponent(level||'')}`).then(r=>r.json()),
        upload: (formData) => fetch('api/upload.php', {method:'POST', body: formData}).then(r=>r.json()),
        download: (id) => window.location.href = `api/download.php?id=${id}`
    };

    function toForm(obj){ const fd=new FormData(); Object.entries(obj).forEach(([k,v])=>fd.append(k,v)); return fd; }

    const db = { isDarkMode: localStorage.getItem('sourcebaan_darkMode')==='true' };
    let currentSection = 'dashboard';
    let authMode = 'login';

    document.addEventListener('DOMContentLoaded', async function(){
        // Essential setup first
        if (db.isDarkMode) { document.documentElement.classList.add('dark-mode'); updateThemeIcon(); }
        showSection('dashboard');
        setupEventListeners();
        
        // Async loading without blocking
        requestAnimationFrame(async () => {
            try {
                await refreshUser();
                await updateStats();
                showTrendingProjects();
                showRecentActivity();
                showTopDevelopers();
                checkVerificationCelebration();
                
                // Background tasks
                setTimeout(() => {
                    const userCheck = api.me().then(res => {
                        if (res.ok && res.user) startHeartbeat();
                    });
                    setInterval(showTrendingProjects, 30000);
                }, 1000);
            } catch (e) {
                console.log('Background loading error:', e);
            }
        });
    });

    // Ensure mobile auth/user sections reflect current state on initial load and after dynamic updates
    function syncMobileAuthState() {
        try {
            const userSection = document.getElementById('userSection');
            const authSection = document.getElementById('authSection');
            const mobileUser = document.getElementById('mobileUserSection');
            const mobileAuth = document.getElementById('mobileAuthSection');
            
            let isLoggedIn = false;
            
            // Check if user is logged in by examining desktop sections
            if (userSection && !userSection.classList.contains('hidden')) {
                isLoggedIn = true;
            } else if (authSection && authSection.classList.contains('hidden')) {
                isLoggedIn = true;
            }
            
            // Update mobile sections based on login state
            if (isLoggedIn) {
                if (mobileUser) mobileUser.classList.remove('hidden');
                if (mobileAuth) mobileAuth.classList.add('hidden');
            } else {
                if (mobileUser) mobileUser.classList.add('hidden');
                if (mobileAuth) mobileAuth.classList.remove('hidden');
            }
        } catch(e) { 
            console.warn('mobile auth sync failed', e); 
        }
    }

    function setupEventListeners(){
        const fileInput = document.getElementById('projectFile');
        const dropZone = document.getElementById('dropZone');
        if (fileInput && dropZone) {
            fileInput.addEventListener('change', handleFileSelect, { passive: true });
            dropZone.addEventListener('dragover', handleDragOver, { passive: false });
            dropZone.addEventListener('drop', handleDrop, { passive: false });
        }
        const tagInput = document.getElementById('projectTags');
        if (tagInput) tagInput.addEventListener('input', debounce(updateTagPreview, 300));
        document.getElementById('authForm').addEventListener('submit', handleAuthSubmit);
        document.getElementById('uploadForm').addEventListener('submit', handleUploadSubmit);
        
        // Initialize mobile auth state sync
        document.addEventListener('DOMContentLoaded', syncMobileAuthState);
    }

    function debounce(fn, wait){ let t; return (...a)=>{ clearTimeout(t); t=setTimeout(()=>fn(...a), wait); }; }

    async function refreshUser(){
        const res = await api.me();
        if (res.ok && res.user){ showUserSection(res.user); } else { showLoggedOut(); }
    }

    function showLoggedOut(){
        document.getElementById('authSection').classList.remove('hidden');
        document.getElementById('userSection').classList.add('hidden');
        const menu = document.getElementById('userMenu');
        if (menu) menu.classList.add('hidden');
        
        // Sync mobile sections
        if (typeof syncMobileAuthState === 'function') {
            syncMobileAuthState();
        }
    }

    async function showUserSection(user){
        document.getElementById('authSection').classList.add('hidden');
        document.getElementById('userSection').classList.remove('hidden');
        document.getElementById('userName').textContent = user.name;
        document.getElementById('userLevel').textContent = (user.role==='admin'?'ادمین':'کاربر');
        document.getElementById('userPoints').textContent = user.points || 0;
        
        // Display avatar
        try {
            const avatarRes = await fetch('api/profile.php?action=get_avatar_options');
            const avatarData = await avatarRes.json();
            
            if (avatarData.ok && avatarData.avatars && user.avatar) {
                const avatarUrl = avatarData.avatars[user.avatar];
                if (avatarUrl) {
                    const avatarImg = document.getElementById('userAvatar');
                    const avatarFallback = document.getElementById('userAvatarFallback');
                    
                    avatarImg.src = avatarUrl;
                    avatarImg.style.display = 'block';
                    avatarFallback.style.display = 'none';
                } else {
                    // Fallback to letter avatar
                    document.getElementById('userAvatarFallback').textContent = (user.name||'?').charAt(0).toUpperCase();
                }
            } else {
                // Fallback to letter avatar
                document.getElementById('userAvatarFallback').textContent = (user.name||'?').charAt(0).toUpperCase();
            }
        } catch (error) {
            // Fallback to letter avatar on error
            document.getElementById('userAvatarFallback').textContent = (user.name||'?').charAt(0).toUpperCase();
        }
        
        // نمایش لینک‌های ادمین
        const adminLinks = document.getElementById('adminLinks');
        if (adminLinks) {
            adminLinks.classList.toggle('hidden', user.role !== 'admin');
        }
        
        // Sync mobile sections
        if (typeof syncMobileAuthState === 'function') {
            syncMobileAuthState();
        }
    }

    function showAuth(mode){
        authMode = mode;
        const modal = document.getElementById('authModal');
        const content = document.getElementById('authModalContent');
        
        modal.classList.remove('hidden');
        setTimeout(() => {
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        }, 10);
        
        if (mode==='register'){
            document.getElementById('authTitle').textContent = 'عضویت سریع';
            document.getElementById('authSubtitle').textContent = 'اکانت جدید بسازید';
            document.getElementById('authButtonText').textContent = 'ثبت نام';
            document.getElementById('authToggle').textContent = 'حساب کاربری دارید؟ وارد شوید';
            document.getElementById('nameField').classList.remove('hidden');
        } else {
            document.getElementById('authTitle').textContent = 'ورود سریع';
            document.getElementById('authSubtitle').textContent = 'با اکانت خود وارد شوید';
            document.getElementById('authButtonText').textContent = 'ورود';
            document.getElementById('authToggle').textContent = 'حساب کاربری ندارید؟ ثبت نام کنید';
            document.getElementById('nameField').classList.add('hidden');
        }
    }

    function closeAuth(){ 
        const modal = document.getElementById('authModal');
        const content = document.getElementById('authModalContent');
        
        content.classList.add('scale-95', 'opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('authForm').reset();
            document.getElementById('authLoading').classList.add('hidden');
            document.getElementById('authForm').classList.remove('hidden');
        }, 300);
    }
    
    function toggleAuthMode(){ 
        const newMode = authMode === 'login' ? 'register' : 'login';
        switchAuthTab(newMode);
    }
    
    function switchAuthTab(tab) {
        const loginTab = document.getElementById('loginTab');
        const registerTab = document.getElementById('registerTab');
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');

        authMode = tab;

        if (tab === 'login') {
            loginTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 bg-white text-gray-800 shadow-md';
            registerTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 text-gray-600 hover:text-gray-800';
            
            registerForm.classList.add('hidden');
            loginForm.classList.remove('hidden');
        } else {
            registerTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 bg-white text-gray-800 shadow-md';
            loginTab.className = 'flex-1 py-3 px-6 rounded-xl font-medium transition-all duration-300 text-gray-600 hover:text-gray-800';
            
            loginForm.classList.add('hidden');
            registerForm.classList.remove('hidden');
        }
    }
    
    function toggleAuthPassword(inputId, button) {
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

            input.addEventListener('input', function() {
                if (this.value) {
                    this.parentElement.classList.add('has-value');
                } else {
                    this.parentElement.classList.remove('has-value');
                }
            });

            // Check initial value
            if (input.value) {
                input.parentElement.classList.add('has-value');
            }
        });
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

    async function handleAuthSubmit(e){
        e.preventDefault();
        
        // Show loading
        document.getElementById('authForm').classList.add('hidden');
        document.getElementById('authLoading').classList.remove('hidden');
        
        try {
            const email = document.getElementById('email').value.trim();
            const password = document.getElementById('password').value;
            
            if (authMode==='register'){
                const name = document.getElementById('fullName').value.trim();
                if (!name) throw new Error('نام کامل الزامی است');
                
                const res = await api.register(name, email, password);
                if (res.ok){ 
                    closeAuth(); 
                    showNotification('خوش آمدید!',`${res.user.name} عزیز، اکانت شما ساخته شد!`,'success'); 
                    showUserSection(res.user); 
                }
                else { 
                    throw new Error(res.error || 'خطا در ثبت نام'); 
                }
            } else {
                const res = await api.login(email, password);
                if (res.ok){ 
                    closeAuth(); 
                    showNotification('خوش آمدید', `${res.user.name} عزیز، خوش برگشتید!`, 'success'); 
                    showUserSection(res.user); 
                }
                else { 
                    throw new Error(res.error || 'ایمیل یا رمز عبور اشتباه است'); 
                }
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
            // Hide loading, show form
            document.getElementById('authLoading').classList.add('hidden');
            document.getElementById('authForm').classList.remove('hidden');
        }
    }

    // Upload modal functions removed - now redirects to upload.php

    function handleDragOver(e){ e.preventDefault(); e.currentTarget.classList.add('border-blue-500','bg-blue-50'); }
    function handleDrop(e){ e.preventDefault(); e.currentTarget.classList.remove('border-blue-500','bg-blue-50'); const files=e.dataTransfer.files; if (files.length>0) handleFile(files[0]); }
    function handleFileSelect(e){ if (e.target.files.length>0) handleFile(e.target.files[0]); }

    function handleFile(file){
        const maxSize = 1024*1024;
    if (file.size>maxSize){ showNotification('خطا','حجم فایل نباید بیشتر از 1024 کیلوبایت باشد','error'); return; }
        document.getElementById('previewFileName').textContent = file.name;
        document.getElementById('previewFileSize').textContent = formatFileSize(file.size);
        document.getElementById('filePreview').classList.remove('hidden');
    }
    function removeFile(){ document.getElementById('projectFile').value=''; document.getElementById('filePreview').classList.add('hidden'); }
    function updateTagPreview(){ const tags = document.getElementById('projectTags').value.split(',').map(t=>t.trim()).filter(Boolean); document.getElementById('tagPreview').innerHTML = tags.map(t=>`<span class="tag">${t}</span>`).join(''); }
    function formatFileSize(bytes){
        if (!bytes || bytes === 0) return '0 بایت';
        // Prefer showing sizes in کیلوبایت (KB) for consistency
        const kb = bytes / 1024;
        if (kb < 1) return bytes + ' بایت';
        if (kb < 1024) return parseFloat(kb.toFixed(2)) + ' کیلوبایت';
        // For very large files, still show in مگابایت if >1024KB
        const mb = kb / 1024;
        return parseFloat(mb.toFixed(2)) + ' مگابایت';
    }

    async function handleUploadSubmit(e){
        e.preventDefault();
        
        // Show loading state
        const submitBtn = e.target.querySelector('button[type="submit"]');
        const originalText = submitBtn.textContent;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i>در حال آپلود...';
        
        try {
        const title = document.getElementById('projectTitle').value;
        const description = document.getElementById('projectDescription').value;
        const language = document.getElementById('projectLanguage').value;
        const level = document.getElementById('projectLevel').value;
        const tags = document.getElementById('projectTags').value;
        const repositoryUrl = document.getElementById('repositoryUrl').value;
        const demoUrl = document.getElementById('demoUrl').value;
        const file = document.getElementById('projectFile').files[0];
            
            // Validation
            if (!title.trim()) { throw new Error('عنوان پروژه الزامی است'); }
            if (!description.trim()) { throw new Error('توضیحات پروژه الزامی است'); }
            if (!language) { throw new Error('زبان برنامه‌نویسی الزامی است'); }
            if (!level) { throw new Error('سطح پروژه الزامی است'); }
            if (!file) { throw new Error('لطفاً فایل پروژه را انتخاب کنید'); }
            
            // File size check
            const maxSize = 1024 * 1024; // 1MB
            if (file.size > maxSize) {
                throw new Error('حجم فایل نباید بیشتر از 1024 کیلوبایت باشد');
            }
            
        const fd = new FormData();
            fd.append('title', title.trim());
            fd.append('description', description.trim());
        fd.append('language', language);
        fd.append('level', level);
            fd.append('tags', tags.trim());
            fd.append('repositoryUrl', repositoryUrl.trim());
            fd.append('demoUrl', demoUrl.trim());
        fd.append('file', file);
            
        const res = await api.upload(fd);
            
            if (res.ok) { 
                closeUploadModal(); 
                showNotification('موفقیت','پروژه ارسال شد و پس از تایید منتشر می‌شود. 50 امتیاز دریافت کردید.','success'); 
                await updateStats(); 
                await showTrendingProjects(); 
            } else { 
                throw new Error(res.error || 'آپلود ناموفق بود');
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
        } finally {
            // Reset button
            submitBtn.disabled = false;
            submitBtn.textContent = originalText;
        }
    }

    function showSection(section){ 
        document.querySelectorAll('main [id$="Section"]').forEach(el=>el.classList.add('hidden')); 
        document.getElementById(section+'Section').classList.remove('hidden'); 
        currentSection = section; 
        document.querySelectorAll('.nav-link').forEach(link=>{ link.classList.remove('text-blue-600'); link.classList.add('text-gray-700'); }); 
        
        // Load section data
        if (section === 'sources') {
            loadSources();
        } else if (section === 'explore') {
            api.list().then(res => showExplore(res.projects || []));
        } else if (section === 'leaderboard') {
            loadLeaderboard(currentLeaderboardType);
        }
    }

    async function updateStats(){ 
        try {
            const res = await api.stats(); 
            if (res.success){ 
                const s = res.stats; 
                document.getElementById('totalProjects').textContent = s.totalProjects || 0; 
                document.getElementById('totalDevelopers').textContent = s.totalDevelopers || 0; 
                document.getElementById('totalDownloads').textContent = (s.totalDownloads || 0).toLocaleString('fa-IR'); 
                document.getElementById('totalStars').textContent = s.totalStars || 0; 
            }
        } catch (error) {
            console.error('Error loading stats:', error);
        }
    }

    // Sources Section Functions
    let allSources = [];
    let currentFilter = 'all';

    async function loadSources() {
        try {
            const res = await api.list();
            if (res.ok && res.projects) {
                allSources = res.projects.sort((a, b) => new Date(b.uploadDate || b.createdAt) - new Date(a.uploadDate || a.createdAt));
                displaySources(allSources);
                updateSourcesStats(allSources);
            } else {
                document.getElementById('sourcesEmpty').classList.remove('hidden');
                document.getElementById('sourcesList').innerHTML = '';
            }
        } catch (error) {
            console.error('Error loading sources:', error);
            document.getElementById('sourcesEmpty').classList.remove('hidden');
        }
    }

    function filterSources(filter) {
        currentFilter = filter;
        
        // Update button styles
        document.querySelectorAll('[onclick^="filterSources"]').forEach(btn => {
            btn.className = 'px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors';
        });
        
        const activeBtn = document.getElementById(`filter${filter.charAt(0).toUpperCase() + filter.slice(1)}`) || 
                          document.getElementById('filterAll');
        if (activeBtn) {
            activeBtn.className = 'px-6 py-3 bg-purple-600 text-white rounded-xl font-medium transition-colors';
        }

        let filteredSources = [...allSources];
        
        if (filter === 'newest') {
            filteredSources.sort((a, b) => new Date(b.uploadDate || b.createdAt) - new Date(a.uploadDate || a.createdAt));
        } else if (filter === 'popular') {
            filteredSources.sort((a, b) => (b.stars || 0) - (a.stars || 0));
        } else if (filter !== 'all') {
            filteredSources = filteredSources.filter(p => p.language === filter);
        }
        
        displaySources(filteredSources);
    }

    function displaySources(sources) {
        const container = document.getElementById('sourcesList');
        const emptyState = document.getElementById('sourcesEmpty');
        
        if (!sources || sources.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
            return;
        }
        
        emptyState.classList.add('hidden');
        
        container.innerHTML = sources.map(project => {
            const tags = Array.isArray(project.tags) ? project.tags : (project.tags||'').split(',').map(t=>t.trim()).filter(Boolean);
            const author = project.authorName || project.author || '';
            const dateStr = new Date(project.uploadDate || project.createdAt).toLocaleDateString('fa-IR');
            
            return `
                <div class="bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 cursor-pointer" onclick="showProject(${project.id})">
                    <div class="flex justify-between items-start mb-4">
                        <span class="px-3 py-1 bg-purple-100 text-purple-800 text-sm rounded-full font-medium">${escapeHtml(project.language)}</span>
                        <span class="px-3 py-1 bg-gray-100 text-gray-800 text-sm rounded-full">${escapeHtml(project.level)}</span>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3 line-clamp-2">${escapeHtml(project.title)}</h3>
                    <p class="text-gray-600 text-sm mb-4 line-clamp-3">${escapeHtml(project.description)}</p>
                    <div class="flex flex-wrap gap-2 mb-4">
                        ${tags.slice(0, 3).map(tag => `<span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">${escapeHtml(tag)}</span>`).join('')}
                    </div>
                    <div class="developer-card bg-gradient-to-br from-indigo-50 via-blue-50 to-cyan-50 rounded-2xl p-4 mb-4 border-2 border-blue-200/50 shadow-lg hover:shadow-xl transition-all duration-300">
                        <div class="flex flex-col sm:flex-row items-center sm:items-center sm:justify-between gap-4">
                            <div class="flex items-center space-x-4 space-x-reverse w-full sm:w-auto">
                                <div class="relative flex-shrink-0">
                                    <div class="w-14 h-14 sm:w-16 sm:h-16 bg-gradient-to-br from-blue-500 via-purple-600 to-indigo-700 rounded-2xl shadow-xl flex items-center justify-center transform transition-all duration-300 hover:scale-110 hover:shadow-2xl ring-4 ring-white/50">
                                        <span class="text-white font-black text-lg sm:text-xl">${getInitials(author)}</span>
                                    </div>
                                    ${project.authorProfile && project.authorProfile.verified ? 
                                        '<div class="absolute -top-2 -right-2 w-6 h-6 sm:w-7 sm:h-7 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-xl flex items-center justify-center border-3 border-white verified-pulse ring-2 ring-blue-200"><svg class="w-3 h-3 sm:w-4 sm:h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>' : ''}
                                </div>
                                <div class="flex flex-col flex-1 min-w-0 text-center sm:text-right">
                                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-2 mb-2">
                                        <span class="text-sm font-bold text-gray-600 bg-gradient-to-r from-gray-100 to-gray-200 px-3 py-1.5 rounded-full whitespace-nowrap border border-gray-300">👨‍💻 توسعه‌دهنده</span>
                                        ${project.authorProfile && project.authorProfile.verified ? 
                                            '<span class="text-sm font-bold text-blue-700 bg-gradient-to-r from-blue-100 to-blue-200 px-3 py-1.5 rounded-full flex items-center gap-2 whitespace-nowrap border border-blue-300 shadow-md"><i class="fas fa-certificate text-blue-600"></i>✓ تایید شده</span>' : ''}
                                    </div>
                                    <span class="text-lg font-black text-gray-800 hover:text-blue-600 transition-all duration-300 hover:underline mb-2 block truncate">${escapeHtml(author)}</span>
                                    ${project.authorProfile && project.authorProfile.points ? 
                                        '<div class="flex items-center justify-center sm:justify-start space-x-2 space-x-reverse"><div class="flex items-center gap-2 bg-gradient-to-r from-yellow-50 to-orange-50 px-3 py-1.5 rounded-full border border-yellow-300"><div class="w-3 h-3 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full flex-shrink-0 animate-pulse"></div><span class="text-sm font-bold text-yellow-700">امتیاز: ' + (project.authorProfile.points || 0).toLocaleString('fa-IR') + '</span></div></div>' : ''}
                                </div>
                            </div>
                            <div class="flex-shrink-0 w-full sm:w-auto">
                                <div class="bg-white/90 backdrop-blur-sm rounded-xl px-4 py-3 shadow-md border border-gray-300 text-center">
                                    <div class="flex items-center justify-center text-sm text-gray-600 mb-2">
                                        <i class="fas fa-calendar-alt ml-2 text-blue-600"></i>
                                        <span class="font-bold">تاریخ انتشار</span>
                                    </div>
                                    <span class="text-lg font-black text-gray-800">${dateStr}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                        <div class="flex items-center space-x-4 space-x-reverse">
                            <span class="flex items-center text-gray-600">
                                <i class="fas fa-download text-blue-500 ml-1"></i>
                                ${(project.downloads || 0).toLocaleString('fa-IR')}
                            </span>
                            <span class="flex items-center text-gray-600">
                                <i class="fas fa-star text-yellow-500 ml-1"></i>
                                ${project.stars || 0}
                            </span>
                        </div>
                        <div class="flex space-x-2 space-x-reverse">
                            <button onclick="event.stopPropagation(); toggleStar(${project.id})" class="px-3 py-2 bg-yellow-100 hover:bg-yellow-200 text-yellow-700 rounded-lg text-xs font-medium transition-colors" title="ستاره">
                                <i class="far fa-star ml-1"></i>ستاره
                            </button>
                            <button onclick="event.stopPropagation(); toggleBookmark(${project.id})" class="px-3 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-medium transition-colors" title="علاقه‌مندی">
                                <i class="fas fa-bookmark ml-1"></i>علاقه‌مندی
                            </button>
                            <button onclick="event.stopPropagation(); reportProject(${project.id})" class="px-3 py-2 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg text-xs font-medium transition-colors" title="گزارش">
                                <i class="fas fa-flag ml-1"></i>گزارش
                            </button>
                            <button onclick="event.stopPropagation(); downloadProject(${project.id})" class="px-3 py-2 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg text-xs font-medium transition-colors" title="دانلود">
                                <i class="fas fa-download ml-1"></i>دانلود
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    }

    function updateSourcesStats(sources) {
        const totalSources = sources.length;
        const totalDownloads = sources.reduce((sum, p) => sum + (p.downloads || 0), 0);
        const totalStars = sources.reduce((sum, p) => sum + (p.stars || 0), 0);
        const languages = [...new Set(sources.map(p => p.language).filter(Boolean))];
        
        document.getElementById('sourcesTotalCount').textContent = totalSources;
        document.getElementById('sourcesDownloadsCount').textContent = totalDownloads.toLocaleString('fa-IR');
        document.getElementById('sourcesStarsCount').textContent = totalStars;
        document.getElementById('sourcesLanguagesCount').textContent = languages.length;
    }

    async function showTrendingProjects() { 
        try {
            const res = await api.trending(); 
            const container = document.getElementById('trendingProjects'); 
            if (!container) return; 
            
            container.innerHTML = ''; 
            
            if (!res.success || !res.projects || res.projects.length === 0) {
                container.innerHTML = `
                    <div class="col-span-full text-center py-12">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-chart-line text-gray-400 text-xl"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">هنوز پروژه ترندی وجود ندارد</h3>
                        <p class="text-gray-600">پس از تایید پروژه‌ها، آن‌ها در اینجا نمایش داده می‌شوند</p>
                    </div>
                `;
                return;
            }
            
            const fragment = document.createDocumentFragment(); 
            res.projects.forEach(p => { 
                const card = document.createElement('div'); 
                card.className = 'bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 card-hover'; 
                const langColor = p.language === 'JavaScript' ? 'primary' : (p.language === 'Python' ? 'secondary' : 'success'); 
                const tags = Array.isArray(p.tags) ? p.tags : (p.tags || '').split(',').map(t => t.trim()).filter(Boolean); 
                
                // Status indicators
                const isNew = new Date() - new Date(p.createdAt) < 7 * 24 * 60 * 60 * 1000;
                const isHot = (p.downloads || 0) > 5 || (p.stars || 0) > 3;
                
                let statusBadge = '';
                if (isNew) {
                    statusBadge = '<div class="absolute top-0 left-0 z-0"><span class="gradient-success text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-lg animate-pulse">🆕 جدید</span></div>';
                } else if (isHot) {
                    statusBadge = '<div class="absolute top-0 left-0 z-0"><span class="gradient-secondary text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-lg">🔥 ترند</span></div>';
                }
                
                const authorName = p.author || p.authorName || 'ناشناس';
                const isVerified = p.authorProfile && p.authorProfile.verified;
                const authorPoints = p.authorProfile && p.authorProfile.points ? p.authorProfile.points : 0;
                
                card.innerHTML = `
                    ${statusBadge}
                    <div class="flex justify-between items-start mb-4">
                        <div class="flex-1 ml-3">
                            <a href="project.php?id=${p.id}" class="block hover:text-blue-600 transition-colors">
                                <h4 class="font-black text-gray-900 mb-3 text-xl leading-tight hover:text-blue-600 transition-colors">${escapeHtml(p.title)}</h4>
                            </a>
                            <p class="text-gray-700 text-sm mb-4 leading-relaxed">${escapeHtml((p.description || '').substring(0, 120))}...</p>
                        </div>
                        <div class="flex flex-col gap-2 items-end">
                            <div class="gradient-${langColor} text-white px-3 py-1.5 rounded-xl text-sm font-bold shadow-md">${(p.language || '').toUpperCase()}</div>
                            ${(p.stars || 0) > 2 ? '<div class="bg-gradient-to-r from-yellow-50 to-orange-50 px-2 py-1 rounded-lg border border-yellow-300"><span class="text-xs font-bold text-yellow-700">⭐ محبوب</span></div>' : ''}
                        </div>
                    </div>
                    
                    ${tags.length > 0 ? `<div class="flex flex-wrap gap-2 mb-4">${tags.slice(0, 3).map(t => `<span class="bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 px-2 py-1 rounded-full text-xs font-medium border border-blue-300">#${escapeHtml(t)}</span>`).join('')}</div>` : ''}
                    
                    <!-- Enhanced Stats Cards -->
                    <div class="grid grid-cols-3 gap-2 mb-4">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 p-2 rounded-lg border border-green-200 text-center">
                            <div class="w-6 h-6 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg flex items-center justify-center mx-auto mb-1">
                                <i class="fas fa-download text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-green-700">${p.downloads || 0}</span>
                            <p class="text-xs text-green-600">دانلود</p>
                        </div>
                        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 p-2 rounded-lg border border-yellow-200 text-center">
                            <div class="w-6 h-6 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-lg flex items-center justify-center mx-auto mb-1">
                                <i class="fas fa-star text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-yellow-700">${p.stars || 0}</span>
                            <p class="text-xs text-yellow-600">ستاره</p>
                        </div>
                        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 p-2 rounded-lg border border-blue-200 text-center">
                            <div class="w-6 h-6 bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg flex items-center justify-center mx-auto mb-1">
                                <i class="fas fa-eye text-white text-xs"></i>
                            </div>
                            <span class="text-sm font-bold text-blue-700">${p.views || 0}</span>
                            <p class="text-xs text-blue-600">بازدید</p>
                        </div>
                    </div>
                    
                    <!-- Enhanced Developer Card -->
                    <div class="bg-gradient-to-br from-indigo-50 via-blue-50 to-cyan-50 rounded-xl p-3 mb-4 border-2 border-blue-200/50 shadow-md">
                        <div class="flex items-center gap-3">
                            <div class="relative flex-shrink-0">
                                <div class="w-12 h-12 bg-gradient-to-br from-blue-500 via-purple-600 to-indigo-700 rounded-xl shadow-lg flex items-center justify-center transform transition-all duration-300 hover:scale-110 ring-2 ring-white/50">
                                    <span class="text-white font-black text-sm">${getInitials(authorName)}</span>
                                </div>
                                ${isVerified ? '<div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-r from-blue-500 to-blue-600 rounded-full shadow-lg flex items-center justify-center border-2 border-white verified-pulse"><svg class="w-2.5 h-2.5 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg></div>' : ''}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="text-xs font-bold text-gray-600 bg-gray-100 px-2 py-1 rounded-full">👨‍💻</span>
                                    ${isVerified ? '<span class="text-xs font-bold text-blue-700 bg-blue-100 px-2 py-1 rounded-full">✓</span>' : ''}
                                </div>
                                <a href="profile.php?user=${encodeURIComponent(authorName)}" class="text-sm font-black text-gray-800 hover:text-blue-600 transition-all block truncate">${escapeHtml(authorName)}</a>
                                ${authorPoints > 0 ? `<div class="flex items-center gap-1 mt-1"><div class="w-2 h-2 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-full animate-pulse"></div><span class="text-xs font-bold text-yellow-700">${authorPoints.toLocaleString('fa-IR')}</span></div>` : ''}
                            </div>
                            <div class="text-center">
                                <div class="text-xs text-gray-500 mb-1">
                                    <i class="fas fa-calendar-alt text-blue-600"></i>
                                </div>
                                <span class="text-xs font-bold text-gray-800">${new Date(p.createdAt || Date.now()).toLocaleDateString('fa-IR')}</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button onclick="downloadProject(${p.id})" class="flex-1 gradient-success text-white py-2.5 rounded-xl text-sm font-bold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                            <i class="fas fa-download ml-1"></i>دانلود
                        </button>
                        <a href="project.php?id=${p.id}" class="flex-1 gradient-primary text-white py-2.5 rounded-xl text-sm font-bold hover:shadow-lg transition-all duration-300 transform hover:scale-105 text-center">
                            <i class="fas fa-info-circle ml-1"></i>جزئیات
                        </a>
                    </div>
                `; 
                fragment.appendChild(card); 
            }); 
            container.appendChild(fragment);
        } catch (error) {
            console.error('Error loading trending projects:', error);
        }
    }

    async function downloadProject(id) { 
        // Check if user is logged in
        try {
            const userResponse = await api.me();
            if (!userResponse.ok || !userResponse.user) {
                showLoginPopup();
                return;
            }
        } catch (error) {
            showLoginPopup();
            return;
        }
        
        // User is logged in, proceed with download
        api.download(id); 
    }

    function showRecentActivity(){
        // Optionally you can build a dedicated activity API. For now, show last approved projects and recent users as pseudo-activity.
        api.list().then(res=>{
            if (!(res.ok || res.success)) return; const container=document.getElementById('recentActivity');
            const items = (res.projects||[]).slice(-5).reverse();
            container.innerHTML = items.map(p=>`
                <div class="flex items-center space-x-3 space-x-reverse p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="w-8 h-8 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center">⬆️</div>
                    <div class="flex-1">
                        <p class="text-sm text-gray-900">پروژه "${escapeHtml(p.title)}" منتشر شد</p>
                        <p class="text-xs text-gray-500">${new Date(p.uploadDate).toLocaleDateString('fa-IR')}</p>
                    </div>
                </div>
            `).join('');
        });
    }

    function showTopDevelopers(){
        // Basic leaderboard based on projects counts; for real, create leaderboard API
        Promise.all([api.list(), fetch('api/auth.php').then(r=>r.json())]).then(([res])=>{
            if (!(res.ok || res.success)) return; const byUser = {}; (res.projects||[]).forEach(p=>{ const k=p.author||'ناشناس'; byUser[k]=(byUser[k]||0)+ (p.downloads||0) + (p.stars||0); });
            const arr = Object.entries(byUser).map(([name,score])=>({name,score})).sort((a,b)=>b.score-a.score).slice(0,5);
            const container=document.getElementById('topDevelopers');
            container.innerHTML = arr.map((dev, i)=>`
                <div class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 ${i===0?'bg-yellow-500':i===1?'bg-gray-400':i===2?'bg-yellow-600':'bg-blue-500'} text-white rounded-full flex items-center justify-center font-bold text-sm">${i+1}</div>
                        <div><p class="font-medium text-gray-900 text-sm">${escapeHtml(dev.name)}</p><p class="text-xs text-gray-500">امتیاز ترکیبی</p></div>
                    </div>
                    <div class="text-right"><p class="font-bold text-sm ${i<3?'text-yellow-600':'text-gray-900'}">${dev.score}</p><p class="text-xs text-gray-500">امتیاز</p></div>
                </div>
            `).join('');
        });
    }

    function toggleAdvancedSearch(){ 
        const panel = document.getElementById('advancedSearchPanel');
        const isHidden = panel.classList.contains('hidden');
        
        panel.classList.toggle('hidden'); 
        
        // Better mobile behavior
        if (window.innerWidth <= 640) {
            if (isHidden) {
                // Opening panel
                setTimeout(() => {
                    panel.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start',
                        inline: 'nearest'
                    });
                }, 100);
                
                // Prevent body scroll when panel is open
                document.body.style.overflow = 'hidden';
            } else {
                // Closing panel
                document.body.style.overflow = '';
            }
        }
    }
    function clearAdvancedSearch(){ document.querySelectorAll('#advancedSearchPanel select').forEach(s=>s.value=''); }
    function applyAdvancedSearch(){ 
        const lang=document.getElementById('filterLanguage').value; 
        const level=document.getElementById('filterLevel').value; 
        
        api.search('',lang,level).then(res=>{ 
            showSection('explore'); 
            showExplore(res.projects||[]); 
        }); 
        
        showNotification('جستجو','فیلترهای پیشرفته اعمال شد','info'); 
        
        // Restore body scroll on mobile
        if (window.innerWidth <= 640) {
            document.body.style.overflow = '';
        }
        
        toggleAdvancedSearch(); 
    }

    function showExplore(projects){ 
        const container=document.getElementById('exploreList'); 
        const emptyState = document.getElementById('exploreEmpty');
        
        if (!projects || projects.length === 0) {
            container.innerHTML = '';
            emptyState.classList.remove('hidden');
        } else {
            emptyState.classList.add('hidden');
            container.innerHTML = (projects||[]).map(p=>{
                const tags = Array.isArray(p.tags) ? p.tags : (p.tags||'').split(',').map(t=>t.trim()).filter(Boolean);
                const author = p.authorName || p.author || '';
                const dateStr = new Date(p.uploadDate || p.createdAt).toLocaleDateString('fa-IR');
                const safe = JSON.stringify(p).replace(/"/g, '&quot;');
                return `
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 card-hover cursor-pointer" onclick="showProject(${JSON.stringify(p).replace(/"/g, '&quot;')})">
                    <div class="flex justify-between items-start mb-4"><div class="flex-1"><h4 class="font-bold text-gray-900 mb-2 text-lg">${escapeHtml(p.title)}</h4><p class="text-gray-600 text-sm mb-3">${escapeHtml((p.description||'').substring(0,120))}...</p></div><div class="gradient-success text-white px-3 py-1.5 rounded-xl text-xs font-bold shadow-md">${(p.language||'').toUpperCase()}</div></div>
                    <div class="flex flex-wrap gap-2 mb-4">${tags.slice(0,4).map(t=>`<span class="tag">${escapeHtml(t)}</span>`).join('')}</div>
                    <div class="flex items-center justify-between text-sm text-gray-500 mb-4"><span class="flex items-center">👤 ${escapeHtml(author)}</span><span>📅 ${dateStr}</span></div>
                    <div class="flex items-center justify-between"><div class="flex items-center space-x-4 space-x-reverse text-sm text-gray-500"><span>⭐ ${p.stars||0}</span><span>📥 ${p.downloads||0}</span></div><button onclick="event.stopPropagation(); downloadProject(${p.id})" class="gradient-primary text-white px-4 py-2 rounded-lg text-sm font-medium hover:shadow-lg transition-all duration-300 transform hover:scale-105">دانلود 🚀</button></div>
                </div>`;
            }).join(''); 
        }
        
        // Update stats
        updateExploreStats(projects);
    }
    
    function updateExploreStats(projects) {
        const totalProjects = projects.length;
        const totalDownloads = projects.reduce((sum, p) => sum + (p.downloads || 0), 0);
        const totalStars = projects.reduce((sum, p) => sum + (p.stars || 0), 0);
        const languages = [...new Set(projects.map(p => p.language).filter(Boolean))];
        
        document.getElementById('totalProjectsCount').textContent = totalProjects;
        document.getElementById('totalDownloadsCount').textContent = totalDownloads.toLocaleString('fa-IR');
        document.getElementById('totalStarsCount').textContent = totalStars;
        document.getElementById('totalLanguagesCount').textContent = languages.length;
    }

    function toggleTheme(){ db.isDarkMode = !db.isDarkMode; document.documentElement.classList.toggle('dark-mode'); updateThemeIcon(); localStorage.setItem('sourcebaan_darkMode', db.isDarkMode?'true':'false'); }
    
    function updateThemeIcon(){
        const desktopIcon = document.getElementById('themeIcon');
        const mobileIcon = document.getElementById('mobileThemeIcon');
        
        if (db.isDarkMode) {
            if (desktopIcon) desktopIcon.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path>';
            if (mobileIcon) mobileIcon.className = 'fas fa-sun w-6 h-6 text-gray-600';
        } else {
            if (desktopIcon) desktopIcon.innerHTML='<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path>';
            if (mobileIcon) mobileIcon.className = 'fas fa-moon w-6 h-6 text-gray-600';
        }
    }
    function toggleNotifications(){ showNotification('اعلانات','فعلاً اعلانی ندارید','info'); }
    
    function toggleMobileMenu() {
        const menu = document.getElementById('mobileMenu');
        const icon = document.getElementById('mobileMenuIcon');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        if (isHidden) {
            menu.classList.remove('hidden');
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>';
        } else {
            menu.classList.add('hidden');
            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>';
        }

        // Ensure correct mobile auth/user visibility based on current UI state
        if (typeof syncMobileAuthState === 'function') {
            syncMobileAuthState();
        }
    }
    function showUserMenu(){
        const menu = document.getElementById('userMenu');
        if (!menu) return;
        menu.classList.toggle('hidden');
    }

    async function handleLogout(){
        const res = await api.logout();
        if (res.ok){
            showLoggedOut();
            showSection('dashboard');
            showNotification('خروج','با موفقیت خارج شدید','info');
        } else {
            showNotification('خطا', res.error||'ناموفق', 'error');
        }
    }

    document.addEventListener('click', (e)=>{
        const menu = document.getElementById('userMenu');
        const btn = document.querySelector('button[onclick="showUserMenu()"]');
        if (!menu || !btn) return;
        if (!menu.contains(e.target) && !btn.contains(e.target)){
            menu.classList.add('hidden');
        }
    });

    function showNotification(title, message, type='info'){
        // Create visual notification
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

    // Function to show login popup for download
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
        
        // Animate popup
        setTimeout(() => {
            const animateElement = popup.querySelector('.animate-popup');
            if (animateElement) {
                animateElement.style.transform = 'scale(1)';
            }
        }, 10);
    }

    function closeLoginPopup() {
        const popup = document.querySelector('.fixed.inset-0.bg-black');
        if (popup) {
            const animateElement = popup.querySelector('.animate-popup');
            if (animateElement) {
                animateElement.style.transform = 'scale(0.95)';
            }
            setTimeout(() => popup.remove(), 200);
        }
    }

    function escapeHtml(str){ return (str||'').toString().replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[s])); }

    // Verification celebration functions
    async function checkVerificationCelebration() {
        try {
            const userRes = await api.me();
            if (userRes.ok && userRes.user && userRes.user.verified) {
                // Check if celebration was already shown using localStorage
                const celebrationShown = localStorage.getItem(`verification_celebrated_${userRes.user.id}`);
                if (!celebrationShown && userRes.user.verification_celebrated !== true) {
                    showVerificationCelebration();
                }
            }
        } catch (error) {
            console.log('Error checking verification status:', error);
        }
    }
    
    function showVerificationCelebration() {
        // Create celebration modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 bg-black/50 backdrop-blur-sm z-[9999] flex items-center justify-center';
        modal.innerHTML = `
            <div class="bg-white rounded-3xl p-8 max-w-md mx-4 text-center celebration-modal transform scale-95 opacity-0 transition-all duration-500">
                <div class="celebration-gift-container mb-6 relative">
                    <div class="gift-box w-24 h-24 mx-auto relative cursor-pointer" onclick="openGift(this)">
                        <div class="gift-base bg-gradient-to-br from-purple-500 to-pink-500 w-full h-16 rounded-lg shadow-lg"></div>
                        <div class="gift-ribbon-v absolute top-0 left-1/2 transform -translate-x-1/2 w-4 h-full bg-gradient-to-b from-yellow-400 to-yellow-500"></div>
                        <div class="gift-ribbon-h absolute top-4 left-0 w-full h-4 bg-gradient-to-r from-yellow-400 to-yellow-500"></div>
                        <div class="gift-bow absolute -top-2 left-1/2 transform -translate-x-1/2 w-8 h-6 bg-gradient-to-br from-red-500 to-pink-500 rounded-full"></div>
                        <div class="gift-sparkles absolute inset-0">
                            <div class="sparkle absolute top-1 right-2 w-2 h-2 bg-yellow-300 rounded-full animate-ping"></div>
                            <div class="sparkle absolute bottom-2 left-1 w-1 h-1 bg-white rounded-full animate-pulse"></div>
                            <div class="sparkle absolute top-8 right-1 w-1 h-1 bg-blue-300 rounded-full animate-bounce"></div>
                        </div>
                    </div>
                    <p class="text-gray-600 text-sm mt-3 animate-pulse">🎁 روی کادو کلیک کنید</p>
                </div>
                
                <div class="celebration-content hidden">
                    <div class="verification-badge mb-6">
                        <div class="inline-flex items-center gap-3 bg-gradient-to-r from-blue-500 to-cyan-400 text-white px-6 py-4 rounded-full text-lg font-bold shadow-2xl animate-bounce">
                            <svg class="w-8 h-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                <path d="M20 6L9 17l-5-5"></path>
                            </svg>
                            <span>تایید‌شده</span>
                        </div>
                    </div>
                    
                    <h2 class="text-3xl font-bold text-gray-900 mb-4 animate-pulse">🎉 تبریک!</h2>
                    <p class="text-lg text-gray-700 mb-6 leading-relaxed">
                        شما تیک آبی <span class="text-blue-500 font-bold">تایید‌شده</span> دریافت کردید!<br>
                        <span class="text-sm text-gray-500 mt-2 block">مبارکتون باشه! 🥳</span>
                    </p>
                    
                    <div class="confetti-container absolute inset-0 pointer-events-none overflow-hidden">
                        <div class="confetti absolute w-2 h-2 bg-blue-500 rounded-full animate-bounce" style="left: 20%; animation-delay: 0s;"></div>
                        <div class="confetti absolute w-2 h-2 bg-purple-500 rounded-full animate-bounce" style="left: 80%; animation-delay: 0.2s;"></div>
                        <div class="confetti absolute w-2 h-2 bg-pink-500 rounded-full animate-bounce" style="left: 60%; animation-delay: 0.4s;"></div>
                        <div class="confetti absolute w-2 h-2 bg-yellow-500 rounded-full animate-bounce" style="left: 40%; animation-delay: 0.6s;"></div>
                    </div>
                    
                    <button onclick="closeCelebration()" class="gradient-primary text-white px-8 py-3 rounded-xl font-bold hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                        عالیه! ممنونم 🎉
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Animate modal appearance
        setTimeout(() => {
            modal.querySelector('.celebration-modal').classList.remove('scale-95', 'opacity-0');
            modal.querySelector('.celebration-modal').classList.add('scale-100', 'opacity-100');
        }, 100);
        
        // Play celebration sound (optional)
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCDuBzvLUeywFJHfH8N2QQAoUXrTp66hVFApGn+Dqwm0gCD');
            audio.volume = 0.3;
            audio.play().catch(() => {}); // Ignore if autoplay blocked
        } catch (e) {}
        
        // Show notification
        showNotification('تبریک!', 'شما تیک آبی تایید‌شده دریافت کردید! مبارکتون باشه! 🎉', 'success');
    }
    
    window.openGift = function(giftBox) {
        giftBox.classList.add('animate-pulse');
        setTimeout(() => {
            giftBox.style.transform = 'scale(0)';
            setTimeout(() => {
                giftBox.parentElement.parentElement.querySelector('.celebration-content').classList.remove('hidden');
                giftBox.parentElement.style.display = 'none';
            }, 300);
        }, 500);
    }
    
    window.closeCelebration = async function() {
        try {
            // Get current user ID
            const userRes = await api.me();
            if (userRes.ok && userRes.user) {
                // Mark in localStorage to prevent showing again
                localStorage.setItem(`verification_celebrated_${userRes.user.id}`, 'true');
                
                // Mark celebration as seen on server
                const formData = new FormData();
                formData.append('action', 'mark_verification_celebrated');
                formData.append('csrf_token', '<?php echo htmlspecialchars($csrfToken ?? ''); ?>');
                
                await fetch('api/profile.php', {
                    method: 'POST',
                    body: formData
                });
            }
        } catch (error) {
            console.log('Error marking celebration:', error);
        }
        
        // Close modal with animation
        const modal = document.querySelector('.fixed.inset-0.bg-black\\/50');
        if (modal) {
            modal.querySelector('.celebration-modal').classList.add('scale-95', 'opacity-0');
            setTimeout(() => {
                modal.remove();
            }, 300);
        }
    }

    // Global variables for project modal
    let currentProject = null;
    let currentProjectId = null;
    let userInteractions = { starred: false, likedComments: [] };

    // Image Upload Functions
    function showImageUpload() {
        document.getElementById('imageUploadModal').classList.remove('hidden');
    }

    function closeImageUpload() {
        document.getElementById('imageUploadModal').classList.add('hidden');
        document.getElementById('imageFile').value = '';
        document.getElementById('imagePreview').classList.add('hidden');
        document.getElementById('insertImageBtn').classList.add('hidden');
    }

    async function handleImageUpload(event) {
        const file = event.target.files[0];
        if (!file) return;

        // Validate file
        const maxSize = 2 * 1024 * 1024; // 2MB
        if (file.size > maxSize) {
            showNotification('خطا', 'حجم تصویر نباید بیشتر از 2048 کیلوبایت باشد', 'error');
            return;
        }

        const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            showNotification('خطا', 'فقط فایل‌های JPG، PNG، GIF و WebP مجاز هستند', 'error');
            return;
        }

        try {
            // Show preview
            const reader = new FileReader();
            reader.onload = (e) => {
                document.getElementById('previewImg').src = e.target.result;
                document.getElementById('imagePreview').classList.remove('hidden');
            };
            reader.readAsDataURL(file);

            // Upload image
            const result = await api.uploadImage(file);
            if (result.ok) {
                document.getElementById('markdownCode').value = result.markdown;
                document.getElementById('insertImageBtn').classList.remove('hidden');
                showNotification('موفقیت', 'تصویر با موفقیت آپلود شد', 'success');
            } else {
                throw new Error(result.error || 'خطا در آپلود تصویر');
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
        }
    }

    function insertImageToDescription() {
        const markdown = document.getElementById('markdownCode').value;
        const textarea = document.getElementById('projectDescription');
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        
        textarea.value = textBefore + '\n' + markdown + '\n' + textAfter;
        closeImageUpload();
        textarea.focus();
    }

    function insertMarkdownHelper() {
        const textarea = document.getElementById('projectDescription');
        const helpers = [
            '**متن ضخیم**',
            '*متن مورب*',
            '# عنوان اصلی',
            '## زیرعنوان',
            '- فهرست',
            '[لینک](http://example.com)',
            '`کد`',
            '```\nبلوک کد\n```'
        ];
        
        const helpText = 'راهنمای Markdown:\n\n' + helpers.join('\n');
        const cursorPos = textarea.selectionStart;
        const textBefore = textarea.value.substring(0, cursorPos);
        const textAfter = textarea.value.substring(cursorPos);
        
        textarea.value = textBefore + '\n' + helpText + '\n' + textAfter;
        textarea.focus();
    }

    // Project Modal Functions
    function showProject(project) {
        currentProject = project;
        currentProjectId = project.id;
        
        document.getElementById('projectModalTitle').textContent = project.title;
        document.getElementById('projectModalAuthor').textContent = `توسعه‌دهنده: ${project.authorName || project.author}`;
        document.getElementById('projectModalDate').textContent = new Date(project.createdAt || project.uploadDate).toLocaleDateString('fa-IR');
        document.getElementById('projectModalLanguage').textContent = project.language;
        // Scan badge: treat missing scan as clean per policy
        const scanBadge = document.getElementById('projectModalScanBadge');
        const isClean = !project.scan || project.scan.status === 'clean';
        const summary = project.scan && project.scan.summary ? project.scan.summary : 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.';
        scanBadge.className = 'px-2 py-1 rounded-full ' + (isClean ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700');
        scanBadge.innerHTML = isClean ? '<i class="fas fa-shield-alt ml-1"></i>تایید امنیتی' : '<i class="fas fa-exclamation-triangle ml-1"></i>نیازمند بررسی';
        scanBadge.title = summary;
        scanBadge.classList.remove('hidden');
        document.getElementById('projectModalStars').textContent = project.stars || 0;
        document.getElementById('projectModalDownloads').textContent = project.downloads || 0;
        document.getElementById('projectModalViews').textContent = project.views || 0;
        
        // Convert markdown to HTML (simple implementation)
        const description = (project.description || '').replace(/\n/g, '<br>');
        document.getElementById('projectModalDescription').innerHTML = description;
        
        // Tags
        const tags = Array.isArray(project.tags) ? project.tags : (project.tags || '').split(',').map(t => t.trim()).filter(Boolean);
        document.getElementById('projectModalTags').innerHTML = tags.map(tag => 
            `<span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm">${escapeHtml(tag)}</span>`
        ).join(' ');
        
        document.getElementById('projectModal').classList.remove('hidden');
        
        loadUserInteractions();
        loadComments();
    }

    function closeProjectModal() {
        document.getElementById('projectModal').classList.add('hidden');
        currentProject = null;
        currentProjectId = null;
    }

    async function loadUserInteractions() {
        if (!currentProjectId) return;
        
        try {
            const result = await api.interactions.getUserInteractions(currentProjectId);
            if (result.ok) {
                userInteractions = result;
                updateStarButton();
            }
        } catch (error) {
            console.error('Error loading user interactions:', error);
        }
    }

    function updateStarButton() {
        const starBtn = document.getElementById('starBtn');
        const starText = document.getElementById('starText');
        
        if (userInteractions.starred) {
            starBtn.className = 'flex items-center px-4 py-2 bg-yellow-100 text-yellow-800 rounded-lg transition-all duration-300';
            starText.textContent = 'ستاره‌دار';
        } else {
            starBtn.className = 'flex items-center px-4 py-2 bg-gray-100 text-gray-700 hover:bg-yellow-100 hover:text-yellow-800 rounded-lg transition-all duration-300';
            starText.textContent = 'ستاره';
        }
    }

    async function toggleStar() {
        if (!currentProjectId) return;
        
        try {
            const result = await api.interactions.toggleStar(currentProjectId);
            if (result.ok) {
                userInteractions.starred = result.starred;
                document.getElementById('projectModalStars').textContent = result.stars;
                updateStarButton();
                
                const message = result.starred ? 'ستاره اضافه شد' : 'ستاره برداشته شد';
                showNotification('موفقیت', message, 'success');
            } else {
                throw new Error(result.error || 'خطا در ثبت ستاره');
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
        }
    }

    async function downloadProjectModal() {
        if (currentProject) {
            // Check if user is logged in
            try {
                const userResponse = await api.me();
                if (!userResponse.ok || !userResponse.user) {
                    showLoginPopup();
                    return;
                }
            } catch (error) {
                showLoginPopup();
                return;
            }
            
            // User is logged in, proceed with download
            window.open(`api/download.php?id=${currentProject.id}`, '_blank');
        }
    }

    // Comments Functions
    async function loadComments() {
        if (!currentProjectId) return;
        
        try {
            const result = await api.interactions.getComments(currentProjectId);
            if (result.ok) {
                displayComments(result.comments);
            }
        } catch (error) {
            console.error('Error loading comments:', error);
        }
    }

    function displayComments(comments) {
        const container = document.getElementById('commentsList');
        
        if (comments.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-8">هنوز نظری ثبت نشده است.</p>';
            return;
        }
        
        const html = comments.map(comment => `
            <div class="border border-gray-200 rounded-xl p-4">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center space-x-3 space-x-reverse">
                        <div class="w-8 h-8 bg-blue-500 text-white rounded-full flex items-center justify-center font-bold text-sm">
                            ${escapeHtml(comment.userName.charAt(0).toUpperCase())}
                        </div>
                        <div>
                            <p class="font-medium text-gray-900">${escapeHtml(comment.userName)}</p>
                            <p class="text-xs text-gray-500">${new Date(comment.createdAt).toLocaleDateString('fa-IR')}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2 space-x-reverse">
                        <button onclick="toggleCommentLike(${comment.id})" class="flex items-center space-x-1 space-x-reverse text-gray-600 hover:text-red-600 transition-colors ${userInteractions.likedComments.includes(comment.id) ? 'text-red-600' : ''}">
                            <svg class="w-4 h-4" fill="${userInteractions.likedComments.includes(comment.id) ? 'currentColor' : 'none'}" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>
                            <span class="text-sm">${comment.likes || 0}</span>
                        </button>
                    </div>
                </div>
                <p class="text-gray-700 whitespace-pre-wrap">${escapeHtml(comment.content)}</p>
            </div>
        `).join('');
        
        container.innerHTML = html;
    }

    async function addComment() {
        const content = document.getElementById('newComment').value.trim();
        if (!content) {
            showNotification('خطا', 'لطفاً نظر خود را بنویسید', 'error');
            return;
        }
        
        if (!currentProjectId) return;
        
        try {
            const result = await api.interactions.addComment(currentProjectId, content);
            if (result.ok) {
                document.getElementById('newComment').value = '';
                loadComments(); // Reload comments
                showNotification('موفقیت', 'نظر شما ثبت شد', 'success');
            } else {
                throw new Error(result.error || 'خطا در ثبت نظر');
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
        }
    }

    async function toggleCommentLike(commentId) {
        try {
            const result = await api.interactions.toggleCommentLike(commentId);
            if (result.ok) {
                if (result.liked) {
                    userInteractions.likedComments.push(commentId);
                } else {
                    userInteractions.likedComments = userInteractions.likedComments.filter(id => id !== commentId);
                }
                loadComments(); // Reload to update like counts
            } else {
                throw new Error(result.error || 'خطا در ثبت لایک');
            }
        } catch (error) {
            showNotification('خطا', error.message, 'error');
        }
    }

    // Leaderboard Functions
    function showLeaderboard(type) {
        // Update tab styles
        document.querySelectorAll('.leaderboard-tab').forEach(tab => {
            if (tab.textContent.includes(getLeaderboardTitle(type))) {
                tab.className = 'leaderboard-tab active px-6 py-3 bg-blue-600 text-white rounded-xl font-medium transition-colors';
            } else {
                tab.className = 'leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors';
            }
        });
        
        // For now show static data - this would fetch from API in real implementation
        showNotification('رتبه‌بندی', `نمایش رتبه‌بندی بر اساس ${getLeaderboardTitle(type)}`, 'info');
    }
    
    function getLeaderboardTitle(type) {
        const titles = {
            'points': 'بیشترین امتیاز',
            'projects': 'بیشترین پروژه', 
            'downloads': 'پردانلودترین',
            'stars': 'محبوب‌ترین'
        };
        return titles[type] || 'بیشترین امتیاز';
    }

    // Enhanced search with title filter
    function enhancedSearch() {
        const title = document.getElementById('searchTitle')?.value || '';
        const language = document.getElementById('filterLanguage')?.value || '';
        const level = document.getElementById('filterLevel')?.value || '';
        const sortBy = document.getElementById('sortBy')?.value || 'newest';
        
        api.search(title, language, level).then(res => {
            let projects = res.projects || [];
            
            // Sort projects
            projects = sortProjects(projects, sortBy);
            
            showSection('explore');
            showExplore(projects);
        });
    }
    
    function sortProjects(projects, sortBy) {
        switch(sortBy) {
            case 'popular':
                return projects.sort((a, b) => (b.stars || 0) - (a.stars || 0));
            case 'downloaded':
                return projects.sort((a, b) => (b.downloads || 0) - (a.downloads || 0));
            case 'title':
                return projects.sort((a, b) => (a.title || '').localeCompare(b.title || ''));
            case 'newest':
            default:
                return projects.sort((a, b) => new Date(b.createdAt) - new Date(a.createdAt));
        }
    }
    
    // Update the advanced search function
    function applyAdvancedSearch() {
        enhancedSearch();
        showNotification('جستجو','فیلترهای پیشرفته اعمال شد','info'); 
        toggleAdvancedSearch();
    }

    // Initialize section data when shown
    function showSection(section) {
        document.querySelectorAll('main > div[id$="Section"]').forEach(s => s.classList.add('hidden'));
        document.getElementById(section+'Section').classList.remove('hidden');
        
        // Load section data
        if (section === 'explore') {
            api.list().then(res => showExplore(res.projects || []));
        }
    }

    // Initialize page
    document.addEventListener('DOMContentLoaded', function() {
        setupFloatingLabels();
        
        // Add tab click handlers
        document.getElementById('loginTab').addEventListener('click', () => switchAuthTab('login'));
        document.getElementById('registerTab').addEventListener('click', () => switchAuthTab('register'));
        
        // Add password strength checker
        const regPassword = document.getElementById('regPassword');
        if (regPassword) {
            regPassword.addEventListener('input', checkPasswordStrength);
        }
    });

    // Leaderboard functions
    let currentLeaderboardType = 'points';
    
    async function loadLeaderboard(type = 'points') {
        try {
            currentLeaderboardType = type;
            
            // Show loading
            document.getElementById('leaderboardLoading').classList.remove('hidden');
            document.getElementById('leaderboardTabs').classList.add('hidden');
            document.getElementById('topThree').classList.add('hidden');
            document.getElementById('leaderboardTable').classList.add('hidden');
            
            // Update active tab
            document.querySelectorAll('.leaderboard-tab').forEach(tab => {
                if (tab.dataset.type === type) {
                    tab.className = 'leaderboard-tab active px-6 py-3 bg-blue-600 text-white rounded-xl font-medium transition-colors';
                } else {
                    tab.className = 'leaderboard-tab px-6 py-3 bg-gray-200 text-gray-700 hover:bg-gray-300 rounded-xl font-medium transition-colors';
                }
            });
            
            const response = await fetch(`api/leaderboard.php?action=get&type=${type}&limit=20`);
            const result = await response.json();
            
            if (result.ok) {
                displayLeaderboardData(result.leaderboard, type);
            } else {
                showNotification('خطا در بارگذاری رتبه‌بندی: ' + (result.error || 'خطای نامشخص'), 'error');
            }
        } catch (error) {
            showNotification('خطا در اتصال به سرور', 'error');
        } finally {
            // Hide loading
            document.getElementById('leaderboardLoading').classList.add('hidden');
            document.getElementById('leaderboardTabs').classList.remove('hidden');
        }
    }
    
    function displayLeaderboardData(leaderboard, type) {
        const typeLabels = {
            'points': 'امتیاز',
            'uploads': 'آپلود',
            'downloads': 'دانلود',
            'stars': 'ستاره',
            'forum_posts': 'فعالیت انجمن'
        };
        
        const typeFields = {
            'points': 'points',
            'uploads': 'totalUploads',
            'downloads': 'totalDownloads',
            'stars': 'totalStars',
            'forum_posts': 'forumActivity'
        };
        
        const currentField = typeFields[type];
        const currentLabel = typeLabels[type];
        
        // Update category title
        document.getElementById('categoryTitle').textContent = currentLabel;
        
        if (leaderboard.length === 0) {
            document.getElementById('leaderboardEmpty').classList.remove('hidden');
            return;
        }
        
        // Show and populate top 3
        if (leaderboard.length >= 3) {
            document.getElementById('topThree').classList.remove('hidden');
            
            // First place
            const first = leaderboard[0];
            const firstPlace = document.getElementById('firstPlace');
            firstPlace.querySelector('.avatar').textContent = getInitials(first.name);
            firstPlace.querySelector('.name').textContent = first.name;
            firstPlace.querySelector('.value').textContent = formatValue(first[currentField], type);
            
            // Second place
            const second = leaderboard[1];
            const secondPlace = document.getElementById('secondPlace');
            secondPlace.querySelector('.avatar').textContent = getInitials(second.name);
            secondPlace.querySelector('.name').textContent = second.name;
            secondPlace.querySelector('.value').textContent = formatValue(second[currentField], type);
            
            // Third place
            const third = leaderboard[2];
            const thirdPlace = document.getElementById('thirdPlace');
            thirdPlace.querySelector('.avatar').textContent = getInitials(third.name);
            thirdPlace.querySelector('.name').textContent = third.name;
            thirdPlace.querySelector('.value').textContent = formatValue(third[currentField], type);
        }
        
        // Show and populate table
        document.getElementById('leaderboardTable').classList.remove('hidden');
        const container = document.getElementById('leaderboardList');
        container.innerHTML = '';
        
        leaderboard.forEach((user, index) => {
            const userElement = document.createElement('div');
            userElement.className = 'p-6 hover:bg-gray-50 transition-colors';
            
            const rankBadgeColor = index < 3 ? 'bg-yellow-500' : 'bg-gray-500';
            const joinDate = user.joinDate ? new Date(user.joinDate).toLocaleDateString('fa-IR') : 'نامشخص';
            
            userElement.innerHTML = `
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 ${rankBadgeColor} text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">${index + 1}</div>
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 text-white rounded-full flex items-center justify-center font-bold text-lg mr-4">
                            ${getInitials(user.name)}
                        </div>
                        <div>
                            <h4 class="font-semibold text-gray-900">${escapeHtml(user.name)}</h4>
                            <p class="text-sm text-gray-600">عضو از ${joinDate}</p>
                            ${user.role === 'admin' ? '<span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded-full mt-1">مدیر</span>' : ''}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-2xl font-bold text-gray-900">${formatValue(user[currentField], type)}</div>
                        <div class="text-sm text-gray-600">${currentLabel}</div>
                    </div>
                </div>
                <div class="mt-4 grid grid-cols-4 gap-4 text-sm text-gray-600">
                    <div class="text-center">
                        <div class="font-semibold text-gray-900">${user.projectCount || 0}</div>
                        <div>پروژه</div>
                    </div>
                    <div class="text-center">
                        <div class="font-semibold text-gray-900">${formatNumber(user.totalDownloads || 0)}</div>
                        <div>دانلود</div>
                    </div>
                    <div class="text-center">
                        <div class="font-semibold text-gray-900">${user.totalStars || 0}</div>
                        <div>ستاره</div>
                    </div>
                    <div class="text-center">
                        <div class="font-semibold text-gray-900">${user.forumActivity || 0}</div>
                        <div>انجمن</div>
                    </div>
                </div>
            `;
            
            container.appendChild(userElement);
        });
    }
    
    function formatValue(value, type) {
        if (type === 'downloads') {
            return formatNumber(value);
        }
        return value || 0;
    }
    
    function formatNumber(num) {
        if (num >= 1000000) {
            return Math.floor(num / 1000000) + 'M';
        } else if (num >= 1000) {
            return Math.floor(num / 1000) + 'K';
        }
        return num.toString();
    }
    
    function getInitials(name) {
        return name.split(' ').map(word => word.charAt(0)).join('').substring(0, 2).toUpperCase();
    }
    
    // Load leaderboard when section is shown
    function showSection(section) {
        document.querySelectorAll('main [id$="Section"]').forEach(el => el.classList.add('hidden'));
        document.getElementById(section + 'Section').classList.remove('hidden');
        currentSection = section;
        
        // Reset navigation
        document.querySelectorAll('.nav-link').forEach(link => {
            link.classList.remove('text-blue-600');
            link.classList.add('text-gray-700');
        });
        
        // Load leaderboard data when leaderboard section is shown
        if (section === 'leaderboard') {
            loadLeaderboard(currentLeaderboardType);
        }
    }

    // Heartbeat for online users tracking
    function startHeartbeat() {
        // Send initial heartbeat
        sendHeartbeat();
        
        // Setup interval for heartbeat every 30 seconds
        setInterval(sendHeartbeat, 30000);
    }

    async function sendHeartbeat() {
        if (document.hidden) return;
        try {
            const formData = new FormData();
            formData.append('action', 'heartbeat');
            formData.append('page', 'index');
            
            await fetch('api/online-users.php', {
                method: 'POST',
                body: formData
            });
        } catch (error) {
            // Silent fail for heartbeat
        }
    }

    // Live Footer Stats Update
    function updateLiveStats() {
        // Update online users count
        const onlineElement = document.getElementById('onlineUsers');
        if (onlineElement) {
            const baseOnline = 8;
            const variation = Math.floor(Math.random() * 8) + 1; // 1-8
            onlineElement.textContent = baseOnline + variation;
        }
        
        // Update today views
        const viewsElement = document.getElementById('todayViews');
        if (viewsElement) {
            const baseViews = 250;
            const currentViews = parseInt(viewsElement.textContent) || baseViews;
            const increment = Math.floor(Math.random() * 3) + 1; // 1-3
            viewsElement.textContent = currentViews + increment;
        }
    }

    // Visibility-aware live stats
    let liveStatsInterval = null;
    function startLiveStats(){ if (liveStatsInterval) return; updateLiveStats(); liveStatsInterval = setInterval(updateLiveStats, 15000); }
    function stopLiveStats(){ if (liveStatsInterval) { clearInterval(liveStatsInterval); liveStatsInterval = null; } }
    document.addEventListener('visibilitychange', function(){ if (document.hidden) { stopLiveStats(); } else { startLiveStats(); } });
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(startLiveStats, 2000);
        try {
            var images = document.querySelectorAll('img');
            for (var i=0;i<images.length;i++){
                var img = images[i];
                if (!img.hasAttribute('loading')) { if (i > 4) img.setAttribute('loading','lazy'); }
                if (!img.hasAttribute('decoding')) img.setAttribute('decoding','async');
                if (!img.getAttribute('fetchpriority')) img.setAttribute('fetchpriority', i <= 2 ? 'high' : 'low');
            }
        } catch(e) {}
    });

    </script>

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
