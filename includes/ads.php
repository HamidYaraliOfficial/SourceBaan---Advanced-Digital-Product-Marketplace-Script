<?php
require_once __DIR__ . '/db.php';

function get_ads_for_location($location) {
    $ads = JsonDB::read('ads') ?? [];
    $activeAds = [];
    
    foreach ($ads as $ad) {
        if (!($ad['active'] ?? false)) continue;
        
        // Check if ad is in specified location
        if (!in_array($location, $ad['locations'] ?? [])) continue;
        
        // Check date range
        $now = time();
        if (!empty($ad['start_date']) && strtotime($ad['start_date']) > $now) continue;
        if (!empty($ad['end_date']) && strtotime($ad['end_date']) < $now) continue;
        
        $activeAds[] = $ad;
    }
    
    // Sort by priority (higher first)
    usort($activeAds, fn($a, $b) => ($b['priority'] ?? 0) - ($a['priority'] ?? 0));
    
    return $activeAds;
}

function render_ad($ad) {
    if (!$ad) return '';
    
    $hasLink = !empty($ad['link']);
    $hasImage = !empty($ad['image']);
    $content = htmlspecialchars($ad['content'] ?? '');
    $title = htmlspecialchars($ad['title'] ?? '');
    $type = $ad['type'] ?? 'banner';
    
    // Different styles based on ad type
    $containerClasses = '';
    $imageClasses = '';
    
    switch ($type) {
        case 'header_banner':
            $containerClasses = 'ad-container ad-header-banner relative overflow-hidden rounded-2xl shadow-xl mb-8 min-h-[280px] md:min-h-[320px] lg:min-h-[360px] bg-gradient-to-br from-slate-900 via-purple-900 to-indigo-900 border border-white/10';
            $imageClasses = 'absolute inset-0 w-full h-auto object-contain object-center opacity-90';
            break;
        case 'sidebar':
            $containerClasses = 'ad-container ad-sidebar p-6 rounded-3xl bg-white shadow-2xl hover:shadow-3xl transition-all duration-700 mb-6 border border-gray-100 hover:border-purple-300 backdrop-blur-sm';
            $imageClasses = 'w-full h-auto object-cover rounded-2xl mb-5 shadow-xl transition-transform duration-500 hover:scale-105';
            break;
        case 'inline':
            $containerClasses = 'ad-container ad-inline p-8 rounded-2xl bg-gradient-to-br from-white via-blue-50/80 to-purple-50/80 border border-blue-200 mb-8 hover:shadow-xl transition-all duration-500 backdrop-blur-lg';
            $imageClasses = 'w-full h-auto object-cover rounded-xl mb-6 shadow-lg transition-transform duration-300 hover:scale-102';
            break;
        default: // banner
            $containerClasses = 'ad-container ad-banner p-6 rounded-2xl bg-gradient-to-br from-white via-indigo-50/80 to-purple-50/80 border border-indigo-200 mb-6 hover:shadow-xl transition-all duration-500 backdrop-blur-lg';
            $imageClasses = 'w-full h-auto object-cover rounded-xl mb-4 shadow-lg transition-transform duration-300 hover:scale-102';
    }
    
    $html = '<div class="' . $containerClasses . ' dark:text-black" data-ad-id="' . ($ad['id'] ?? '') . '">';
    
    if ($hasLink) {
        $html .= '<a href="' . htmlspecialchars($ad['link']) . '" target="_blank" rel="noopener" class="block hover:opacity-90 transition-opacity group" onclick="trackAdClick(' . ($ad['id'] ?? 0) . ')">';
    }
    
    // Header banner with side-by-side layout for better image display
    if ($type === 'header_banner') {
        // Compact container with better proportions
        $html .= '<div class="flex flex-col md:flex-row items-center min-h-full p-4 md:p-6 lg:p-8 gap-6">';
        
        // Image section - کوچکتر و متعادل‌تر
        if ($hasImage) {
            $html .= '<div class="w-full md:w-2/5 flex items-center justify-center">';
            $html .= '<div class="relative w-full max-w-xs md:max-w-sm">';
            $html .= '<img src="' . htmlspecialchars($ad['image']) . '" alt="' . $title . '" class="w-full h-auto object-contain rounded-xl shadow-xl border-2 border-white/20">';
            $html .= '<div class="absolute -inset-2 bg-gradient-to-r from-purple-600/10 via-pink-600/10 to-blue-600/10 rounded-2xl blur-lg"></div>';
            $html .= '</div>';
            $html .= '</div>';
        }
        
        // Content section - بیشتر جا
        $html .= '<div class="w-full md:w-3/5 text-center md:text-right space-y-4">';
        
        if (!empty($title)) {
            $html .= '<div class="space-y-2">';
            $html .= '<h1 class="text-xl md:text-2xl lg:text-3xl xl:text-4xl font-bold text-white drop-shadow-lg leading-tight">';
            $html .= '<span class="bg-gradient-to-r from-yellow-400 via-pink-400 to-purple-400 bg-clip-text text-transparent">' . $title . '</span>';
            $html .= '</h1>';
            $html .= '<div class="w-16 h-0.5 bg-gradient-to-r from-yellow-400 via-pink-400 to-purple-400 mx-auto md:mx-0 rounded-full"></div>';
            $html .= '</div>';
        }
        
        if (!empty($content)) {
            $html .= '<div class="max-w-xl mx-auto md:mx-0">';
            $html .= '<p class="text-sm md:text-base lg:text-lg text-white/90 leading-relaxed drop-shadow-md font-medium">' . $content . '</p>';
            $html .= '</div>';
        }
        
        // دکمه کوچکتر و بهتر
        if ($hasLink) {
            $html .= '<div class="space-y-2">';
            $html .= '<div class="flex justify-center md:justify-start">';
            $html .= '<span class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-purple-600 via-pink-600 to-red-600 text-white text-sm md:text-base font-bold rounded-full shadow-xl hover:shadow-purple-500/30 hover:scale-105 transition-all duration-300 cursor-pointer border border-white/20">';
            $html .= '<i class="fas fa-rocket ml-2 text-base"></i>';
            $html .= 'شروع کنید الآن';
            $html .= '</span>';
            $html .= '</div>';
            $html .= '<p class="text-white/70 text-xs font-medium">✨ فقط یک کلیک تا شروع!</p>';
            $html .= '</div>';
        }
        
        $html .= '</div>'; // End content section
        $html .= '</div>'; // End main container
    } else {
        // Modern layout for other types
        if ($type === 'sidebar') {
            // Sidebar ads - compact and elegant
            if ($hasImage) {
                $html .= '<div class="relative overflow-hidden rounded-xl mb-4 group">';
                $html .= '<img src="' . htmlspecialchars($ad['image']) . '" alt="' . $title . '" class="' . $imageClasses . ' group-hover:scale-110 transition-transform duration-500">';
                $html .= '<div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';
                $html .= '</div>';
            }
            
            if (!empty($title)) {
                $html .= '<h3 class="text-xl font-bold text-gray-900 mb-3 leading-tight hover:text-purple-600 transition-colors">' . $title . '</h3>';
            }
            
            if (!empty($content)) {
                $html .= '<p class="text-gray-700 text-sm leading-relaxed mb-4">' . $content . '</p>';
            }
    
    if ($hasLink) {
                $html .= '<div class="mt-4">';
                $html .= '<span class="inline-flex items-center text-purple-600 font-semibold text-sm hover:text-purple-800 transition-colors">';
                $html .= 'مشاهده بیشتر <i class="fas fa-arrow-left mr-2"></i>';
                $html .= '</span>';
                $html .= '</div>';
            }
            
        } else {
             // بنر عادی و inline - کامپکت و زیبا
    if ($hasImage) {
                 $html .= '<div class="relative overflow-hidden rounded-xl mb-4 group">';
                 $html .= '<img src="' . htmlspecialchars($ad['image']) . '" alt="' . $title . '" class="' . $imageClasses . ' group-hover:scale-105 transition-transform duration-300">';
                 $html .= '<div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>';
                 $html .= '</div>';
    }
    
    if (!empty($title)) {
                 $html .= '<h2 class="text-xl md:text-2xl font-bold text-gray-900 mb-3 leading-tight">';
                 $html .= '<span class="bg-gradient-to-r from-purple-600 to-pink-600 bg-clip-text text-transparent hover:from-purple-700 hover:to-pink-700 transition-colors">' . $title . '</span>';
                 $html .= '</h2>';
    }
    
    if (!empty($content)) {
                 $html .= '<p class="text-gray-700 text-base leading-relaxed mb-4">' . $content . '</p>';
             }
             
             if ($hasLink) {
                 $html .= '<div class="flex justify-center md:justify-start">';
                 $html .= '<span class="inline-flex items-center px-5 py-2.5 bg-gradient-to-r from-purple-600 to-pink-600 text-white font-semibold rounded-full shadow-md hover:shadow-lg hover:scale-105 transition-all duration-300 cursor-pointer text-sm">';
                 $html .= 'اطلاعات بیشتر <i class="fas fa-external-link-alt mr-2"></i>';
                 $html .= '</span>';
                 $html .= '</div>';
             }
        }
    }
    
    if ($hasLink) {
        $html .= '</a>';
    }
    
    // Add "تبلیغات" label for transparency
    $html .= '<div class="absolute top-2 left-2 bg-gray-800/80 text-white text-xs px-2 py-1 rounded-full">تبلیغات</div>';
    
    $html .= '</div>';
    
    return $html;
}

function track_ad_view($adId) {
    if ($adId <= 0) return;
    
    try {
        $ads = JsonDB::read('ads') ?? [];
        foreach ($ads as &$ad) {
            if ((int)($ad['id'] ?? 0) === (int)$adId) {
                $ad['views'] = ($ad['views'] ?? 0) + 1;
                break;
            }
        }
        JsonDB::write('ads', $ads);
    } catch (Exception $e) {
        // Silently fail if tracking fails
        error_log('Ad tracking failed: ' . $e->getMessage());
    }
}

function display_ads($location, $limit = 3) {
    $ads = get_ads_for_location($location);
    if (empty($ads)) return '';
    
    $html = '';
    $displayed = 0;
    
    foreach ($ads as $ad) {
        if ($displayed >= $limit) break;
        $html .= render_ad($ad);
        $displayed++;
    }
    
    return $html;
}

function display_sidebar_ads($limit = 2) {
    return display_ads('sidebar', $limit);
}

function display_inline_ads($limit = 1) {
    return display_ads('inline', $limit);
}

function display_header_ads($limit = 1) {
    return display_ads('home_header', $limit);
}

// JavaScript for tracking clicks
function get_ad_tracking_script() {
    return '
    <script>
    document.addEventListener("contextmenu", function (event) { event.preventDefault(); });
    function trackAdClick(adId) {
        if (!adId) return;
        fetch("api/ads.php?action=track_click&ad_id=" + adId, {method: "POST"})
            .catch(e => console.log("Ad tracking failed:", e));
    }
    
    // Track ad views on page load
    document.addEventListener("DOMContentLoaded", function() {
        const ads = document.querySelectorAll(".ad-container[data-ad-id]");
        ads.forEach(ad => {
            const adId = ad.dataset.adId;
            if (adId && adId !== "0") {
                fetch("api/ads.php?action=track_view&ad_id=" + adId, {method: "POST"})
                    .catch(e => console.log("Ad view tracking failed:", e));
            }
        });
    });
    </script>';
}
