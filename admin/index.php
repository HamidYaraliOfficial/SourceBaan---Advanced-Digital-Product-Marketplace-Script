<?php
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/utils.php';

$currentUser = current_user();
$csrf_token = csrf_get_token();
if (!$currentUser || !is_admin()) {
    // Redirect to main page instead of showing 403
    header('Location: ../index.php');
    exit;
}
?><!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1"/>
<title>پنل ادمین - SourceBaan</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
* { font-family: 'Vazirmatn', sans-serif; }
.gradient-primary { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.card-hover { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
.card-hover:hover { transform: translateY(-2px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
</style>
</head>
<body class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen" data-csrf="<?= htmlspecialchars($csrf_token) ?>">
<div class="max-w-7xl mx-auto p-6">
  <!-- Header -->
  <div class="flex items-center justify-between mb-8">
    <div class="flex items-center space-x-4 space-x-reverse">
      <a href="../index.php" class="p-3 bg-white rounded-xl shadow-md hover:shadow-lg transition-all duration-300 card-hover">
        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m0 7h18"></path>
        </svg>
      </a>
      <div>
        <h1 class="text-3xl font-bold bg-gradient-to-r from-red-600 to-pink-600 bg-clip-text text-transparent">پنل ادمین</h1>
        <p class="text-gray-600">مدیریت درخواست‌های ارسالی</p>
      </div>
    </div>
    <div class="flex items-center space-x-3 space-x-reverse">
      <a href="live-chats.php" class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition-colors text-sm flex items-center gap-2">
        <i class="fas fa-comments"></i>
        چت‌های زنده
      </a>
      <div class="bg-white rounded-2xl px-4 py-2 shadow-lg flex items-center space-x-3 space-x-reverse">
        <div class="text-right">
          <p class="font-semibold text-gray-900 text-sm"><?= htmlspecialchars($currentUser['name']) ?></p>
          <p class="text-xs text-red-600">ادمین</p>
        </div>
        <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-pink-500 rounded-full flex items-center justify-center text-white font-bold text-sm">
          <?= strtoupper(substr($currentUser['name'], 0, 1)) ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Stats Cards -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
      <div class="w-12 h-12 bg-gradient-to-r from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="text-2xl font-bold text-gray-900" id="pendingCount">0</div>
      <div class="text-sm text-gray-600">در انتظار تایید</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
      <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-teal-500 rounded-xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="text-2xl font-bold text-gray-900" id="approvedCount">0</div>
      <div class="text-sm text-gray-600">تایید شده امروز</div>
    </div>
    <div class="bg-white rounded-2xl p-6 shadow-lg card-hover text-center">
      <div class="w-12 h-12 bg-gradient-to-r from-red-500 to-pink-500 rounded-xl flex items-center justify-center mx-auto mb-3">
        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <div class="text-2xl font-bold text-gray-900" id="rejectedCount">0</div>
      <div class="text-sm text-gray-600">رد شده امروز</div>
    </div>
  </div>

  <!-- Pending Submissions -->
  <div class="bg-white rounded-3xl shadow-xl p-8">
    <h2 class="text-2xl font-bold text-gray-900 mb-6">درخواست‌های در انتظار تایید</h2>
    <div id="list" class="space-y-4"></div>
  </div>
</div>
<script>
async function fetchPending(){
  try {
    const res = await fetch('../api/admin.php?action=list');
    const data = await res.json();
    
    if (!data.ok){ 
      document.getElementById('list').innerHTML = `
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-red-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">خطا در بارگذاری</h3>
          <p class="text-gray-600">${data.error||'خطا در دریافت داده‌ها'}</p>
        </div>`;
      return; 
    }
    
    const items = data.pending || [];
    document.getElementById('pendingCount').textContent = items.length;
    
    if (items.length === 0) {
      document.getElementById('list').innerHTML = `
        <div class="text-center py-12">
          <div class="w-16 h-16 bg-green-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">همه درخواست‌ها بررسی شده</h3>
          <p class="text-gray-600">درخواست جدیدی برای تایید وجود ندارد</p>
        </div>`;
    } else {
      document.getElementById('list').innerHTML = items.map(card).join('');
    }
    
    // Update stats
    await updateStats();
  } catch (err) {
    console.error('Error fetching pending:', err);
    document.getElementById('list').innerHTML = `
      <div class="p-4 bg-red-100 text-red-700 rounded-xl">
        خطا در اتصال به سرور. لطفاً دوباره تلاش کنید.
      </div>`;
  }
}

async function updateStats() {
  try {
    // Get all submissions for stats
    const allSubmissions = await fetch('../api/admin.php?action=all').then(r => r.json()).catch(() => ({ok: false}));
    
    if (allSubmissions.ok) {
      const today = new Date().toDateString();
      const todayApproved = (allSubmissions.submissions || []).filter(s => 
        s.status === 'approved' && new Date(s.updatedAt || s.createdAt).toDateString() === today
      ).length;
      
      const todayRejected = (allSubmissions.submissions || []).filter(s => 
        s.status === 'rejected' && new Date(s.updatedAt || s.createdAt).toDateString() === today
      ).length;
      
      document.getElementById('approvedCount').textContent = todayApproved;
      document.getElementById('rejectedCount').textContent = todayRejected;
    }
  } catch (err) {
    console.error('Error updating stats:', err);
  }
}

function card(s){
  return `<div class="p-6 bg-gray-50 rounded-2xl border-l-4 border-blue-500 hover:shadow-lg transition-all duration-300">
    <div class="flex justify-between items-start">
      <div class="flex-1">
        <div class="flex items-center justify-between mb-3">
          <h3 class="font-bold text-xl text-gray-900">${escapeHtml(s.title)}</h3>
          <span class="px-3 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-medium">در انتظار</span>
        </div>
        <p class="text-gray-600 mb-4 leading-relaxed">${escapeHtml((s.description||'').substring(0,200))}${(s.description||'').length > 200 ? '...' : ''}</p>
        
        <div class="flex flex-wrap gap-2 mb-4">
          ${(s.tags || []).slice(0, 4).map(tag => `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded-lg text-xs font-medium">${escapeHtml(tag)}</span>`).join('')}
        </div>
        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm text-gray-500 mb-4">
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
            </svg>
            ${escapeHtml(s.author)}
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 110-2h4z"></path>
            </svg>
            ${((s.fileSize/1024)).toFixed(2)} کیلوبایت
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
            </svg>
            ${s.language || 'نامشخص'}
          </div>
          <div class="flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            ${new Date(s.createdAt).toLocaleDateString('fa-IR')}
          </div>
        </div>
      </div>
      
      <div class="flex flex-col space-y-3 mr-6">
        <div class="grid grid-cols-2 gap-2 mb-3">
          <button onclick="previewFile(${s.id})" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-all duration-300 text-sm">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
            </svg>
            بررسی
          </button>
          <button onclick="downloadForReview(${s.id})" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg font-medium transition-all duration-300 text-sm">
            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
            </svg>
            دانلود
          </button>
        </div>
        <div class="text-xs ${( !s.scan || s.scan.status==='clean') ? 'text-green-700 bg-green-50' : 'text-yellow-700 bg-yellow-50'} rounded-lg px-2 py-1 text-center" title="${(s.scan && s.scan.summary) ? escapeHtml(s.scan.summary) : 'این سورس توسط SourceBaan بررسی شد و مشکلی یافت نشد.'}">
          ${(!s.scan || s.scan.status==='clean') ? '✅ اسکن شده توسط SourceBaan: امن' : '⚠️ نیازمند بررسی امنیتی'}
        </div>
        <button onclick="approve(${s.id})" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-green-200">
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
          </svg>
          تایید
        </button>
        <button onclick="reject(${s.id})" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl font-medium transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-red-200">
          <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          رد
        </button>
      </div>
    </div>
  </div>`;
}

function escapeHtml(str){ 
  return (str||'').toString().replace(/[&<>"']/g, s=>({"&":"&amp;","<":"&lt;",">":"&gt;","\"":"&quot;","'":"&#039;"}[s])); 
}

async function approve(id){ 
  if (!confirm('آیا از تایید این پروژه اطمینان دارید؟')) return;
  
  const fd=new FormData(); 
  fd.append('action','approve'); 
  fd.append('id', String(id)); 
  try { fd.append('csrf_token', document.body.getAttribute('data-csrf') || ''); } catch(_){}
  
  try {
    const res=await fetch('../api/admin.php', {method:'POST', body: fd}); 
    const data=await res.json(); 
    
    if(data.ok){ 
      await fetchPending();
      showNotification('تایید شد', 'پروژه با موفقیت تایید و منتشر شد', 'success');
    } else { 
      showNotification('خطا', data.error||'خطا در تایید پروژه', 'error');
    }
  } catch (err) {
    showNotification('خطا', 'خطا در اتصال به سرور', 'error');
  }
}

async function reject(id){ 
  const reason = prompt('علت رد (اختیاری):',''); 
  if (reason === null) return; // User cancelled
  
  const fd=new FormData(); 
  fd.append('action','reject'); 
  fd.append('id', String(id)); 
  if (reason) fd.append('reason', reason); 
  try { fd.append('csrf_token', document.body.getAttribute('data-csrf') || ''); } catch(_){}
  
  try {
    const res=await fetch('../api/admin.php', {method:'POST', body: fd}); 
    const data=await res.json(); 
    
    if(data.ok){ 
      await fetchPending();
      showNotification('رد شد', 'پروژه رد شد', 'info');
    } else { 
      showNotification('خطا', data.error||'خطا در رد پروژه', 'error');
    }
  } catch (err) {
    showNotification('خطا', 'خطا در اتصال به سرور', 'error');
  }
}

function showNotification(title, message, type = 'info') {
  const colors = {
    success: 'bg-green-500',
    error: 'bg-red-500', 
    info: 'bg-blue-500'
  };
  
  const notification = document.createElement('div');
  notification.className = `fixed top-4 right-4 ${colors[type]} text-white px-6 py-4 rounded-xl shadow-2xl z-50 transform translate-x-full transition-transform duration-300`;
  notification.innerHTML = `
    <div class="flex items-center">
      <div class="mr-3">
        <div class="font-bold">${title}</div>
        <div class="text-sm opacity-90">${message}</div>
      </div>
    </div>
  `;
  
  document.body.appendChild(notification);
  
  setTimeout(() => notification.classList.remove('translate-x-full'), 100);
  setTimeout(() => {
    notification.classList.add('translate-x-full');
    setTimeout(() => notification.remove(), 300);
  }, 3000);
}

// File Preview Functions
function downloadForReview(submissionId) {
  window.open(`../api/preview-file.php?action=download&submissionId=${submissionId}`, '_blank');
}

async function previewFile(submissionId) {
  try {
    const response = await fetch(`../api/preview-file.php?action=analyze&submissionId=${submissionId}`);
    const result = await response.json();
    
    if (result.ok) {
      showFileAnalysis(result.analysis, result.submission);
    } else {
      throw new Error(result.error || 'خطا در تجزیه فایل');
    }
  } catch (error) {
    showNotification('خطا', error.message, 'error');
  }
}

function showFileAnalysis(analysis, submission) {
  const modal = document.createElement('div');
  modal.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
  modal.onclick = (e) => e.target === modal && modal.remove();
  
  const securityColor = analysis.security.safe ? 'text-green-600' : 'text-red-600';
  const securityIcon = analysis.security.safe ? '✅' : '⚠️';
  
  modal.innerHTML = `
    <div class="bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-y-auto">
      <div class="p-6 border-b border-gray-200">
        <div class="flex justify-between items-center">
          <h2 class="text-2xl font-bold text-gray-900">تجزیه و تحلیل فایل</h2>
          <button onclick="this.closest('.fixed').remove()" class="p-2 hover:bg-gray-100 rounded-lg">
            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
          </button>
        </div>
      </div>
      
      <div class="p-6">
        <!-- File Info -->
        <div class="bg-gray-50 rounded-xl p-4 mb-6">
          <h3 class="font-bold text-gray-900 mb-3">اطلاعات کلی</h3>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div><span class="text-gray-600">نام فایل:</span><br><strong>${escapeHtml(analysis.fileName)}</strong></div>
            <div><span class="text-gray-600">حجم:</span><br><strong>${(analysis.size/1024).toFixed(2)} KB</strong></div>
            <div><span class="text-gray-600">نوع:</span><br><strong>${analysis.mimeType}</strong></div>
            <div><span class="text-gray-600">پسوند:</span><br><strong>.${analysis.extension}</strong></div>
          </div>
        </div>
        
        <!-- Security Analysis -->
        <div class="mb-6">
          <h3 class="font-bold text-gray-900 mb-3">بررسی امنیتی</h3>
          <div class="p-4 rounded-xl ${analysis.security.safe ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200'}">
            <div class="flex items-center mb-2">
              <span class="text-2xl ml-2">${securityIcon}</span>
              <span class="font-medium ${securityColor}">${analysis.security.safe ? 'فایل امن است' : 'مشکلات امنیتی شناسایی شد'}</span>
            </div>
            ${analysis.security.issues.length > 0 ? `
              <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                ${analysis.security.issues.map(issue => `<li>${escapeHtml(issue)}</li>`).join('')}
              </ul>
            ` : ''}
          </div>
        </div>
        
        <!-- Archive Contents -->
        ${analysis.isArchive && analysis.contents.length > 0 ? `
          <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">محتویات آرشیو</h3>
            <div class="max-h-48 overflow-y-auto border border-gray-200 rounded-lg">
              <div class="divide-y divide-gray-200">
                ${analysis.contents.map(file => `
                  <div class="p-3 text-sm">
                    <div class="flex items-center justify-between">
                      <span class="flex items-center">
                        ${file.isDir ? '📁' : '📄'}
                        <span class="mr-2">${escapeHtml(file.name)}</span>
                      </span>
                      ${!file.isDir ? `<span class="text-gray-500">${(file.size/1024).toFixed(1)} KB</span>` : ''}
                    </div>
                  </div>
                `).join('')}
              </div>
            </div>
          </div>
        ` : ''}
        
        <!-- Code Analysis -->
        ${analysis.codeAnalysis && analysis.codeAnalysis.lines > 0 ? `
          <div class="mb-6">
            <h3 class="font-bold text-gray-900 mb-3">تجزیه کد</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
              <div class="text-center p-3 bg-blue-50 rounded-lg">
                <div class="text-2xl font-bold text-blue-600">${analysis.codeAnalysis.lines}</div>
                <div class="text-sm text-blue-600">خط کد</div>
              </div>
              <div class="text-center p-3 bg-green-50 rounded-lg">
                <div class="text-2xl font-bold text-green-600">${analysis.codeAnalysis.functions?.length || 0}</div>
                <div class="text-sm text-green-600">تابع</div>
              </div>
              <div class="text-center p-3 bg-purple-50 rounded-lg">
                <div class="text-2xl font-bold text-purple-600">${analysis.codeAnalysis.classes?.length || 0}</div>
                <div class="text-sm text-purple-600">کلاس</div>
              </div>
            </div>
            
            ${analysis.codeAnalysis.preview ? `
              <div class="mb-4">
                <h4 class="font-medium text-gray-900 mb-2">پیش‌نمایش کد:</h4>
                <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-xs overflow-x-auto max-h-48">${escapeHtml(analysis.codeAnalysis.preview)}</pre>
              </div>
            ` : ''}
            
            ${analysis.codeAnalysis.functions?.length > 0 ? `
              <div class="mb-4">
                <h4 class="font-medium text-gray-900 mb-2">توابع شناسایی شده:</h4>
                <div class="flex flex-wrap gap-2">
                  ${analysis.codeAnalysis.functions.map(func => `<span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-sm">${escapeHtml(func)}</span>`).join('')}
                </div>
              </div>
            ` : ''}
          </div>
        ` : ''}
        
        <!-- Project Details -->
        <div class="bg-blue-50 rounded-xl p-4">
          <h3 class="font-bold text-gray-900 mb-3">جزئیات پروژه</h3>
          <div class="space-y-2 text-sm">
            <div><span class="text-gray-600">عنوان:</span> <strong>${escapeHtml(submission.title)}</strong></div>
            <div><span class="text-gray-600">توسعه‌دهنده:</span> <strong>${escapeHtml(submission.author)}</strong></div>
            <div><span class="text-gray-600">زبان:</span> <strong>${escapeHtml(submission.language)}</strong></div>
            <div><span class="text-gray-600">توضیحات:</span></div>
            <div class="p-3 bg-white rounded border border-blue-200 max-h-32 overflow-y-auto">
              ${escapeHtml(submission.description).replace(/\\n/g, '<br>')}
            </div>
          </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex justify-end space-x-3 space-x-reverse mt-6 pt-4 border-t border-gray-200">
          <button onclick="downloadForReview(${submission.id})" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
            دانلود فایل
          </button>
          <button onclick="approve(${submission.id}); this.closest('.fixed').remove()" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
            تایید پروژه
          </button>
          <button onclick="reject(${submission.id}); this.closest('.fixed').remove()" class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg">
            رد پروژه
          </button>
          <button onclick="this.closest('.fixed').remove()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg">
            بستن
          </button>
        </div>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
}

function rescanAll(){
  fetch('../api/admin.php?action=rescan_missing').then(r=>r.json()).then(d=>{
    if (d.ok){ showNotification('اسکن انجام شد', `${d.updated} مورد به‌روزرسانی شد`, 'success'); fetchPending(); }
    else { showNotification('خطا', d.error||'اسکن ناموفق بود', 'error'); }
  }).catch(()=>showNotification('خطا','اتصال ناموفق','error'));
}

// Initialize
fetchPending();
setInterval(fetchPending, 30000); // Auto refresh every 30 seconds
</script>
</body>
</html>
