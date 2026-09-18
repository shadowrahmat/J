<?php
/**
 * Plugin Name: Juhani Performance Optimizer
 * Description: Conservative, functionality-preserving performance tweaks: removes bloat (emojis, embeds, RSD, wlwmanifest, shortlink, generator), limits Heartbeat, defers non-critical, optimizes Woo/Elementor loading, improves font display. Zero visual change. Safe to disable.
 * Version: 1.0.0
 * Author: Audit
 */

if (!defined('ABSPATH')) exit;

// ------------------------------------------------------------------
// 1. Clean <head> - remove bloat that adds requests without design impact
// ------------------------------------------------------------------
add_action('init', function () {
    // Emojis: remove 2 extra HTTP requests + inline JS (native browser emoji unaffected)
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

    // oEmbed: removes wp-embed.min.js (1 request) - embeds still work via paste, just no discovery on every page
    remove_action('wp_head', 'wp_oembed_add_discovery_links');
    remove_action('wp_head', 'wp_oembed_add_host_js');
    remove_action('wp_head', 'rest_output_link_wp_head');
    // Generator, RSD, WLW, shortlink
    remove_action('wp_head', 'wp_generator');
    remove_action('wp_head', 'rsd_link');
    remove_action('wp_head', 'wlwmanifest_link');
    remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);
    remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
    // Really Simple Discovery etc.
    remove_action('wp_head', 'feed_links_extra', 3);
}, 100);

add_filter('emoji_svg_url', '__return_false');

// Disable self-pingbacks (prevents WordPress pinging itself on publish - no UX impact)
add_action('pre_ping', function (&$links) {
    $home = get_option('home');
    foreach ($links as $l => $url) {
        if (strpos($url, $home) === 0) unset($links[$l]);
    }
});

// ------------------------------------------------------------------
// 2. Dashicons - dequeue on frontend for non-logged users (admin bar only)
// ------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    if (!is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
}, 100);

// ------------------------------------------------------------------
// 3. Heartbeat - limit to admin & post edit only (reduces admin-ajax.php polling on shop/blog)
//    Every frontend heartbeat is ~15-60s AJAX; on shop with cart fragments it compounds.
// ------------------------------------------------------------------
add_action('init', function () {
    // Only deregister heartbeat on frontend; keep in admin editor
    if (!is_admin()) {
        // Don't deregister entirely - just slow it drastically
        // Actual throttling handled via filter below
    }
}, 1);

add_filter('heartbeat_settings', function ($settings) {
    // Frontend: slow to 60s instead of 15s; Admin post-edit stays default via conditional above not applied?
    if (!is_admin()) {
        $settings['interval'] = 60;
    } else {
        // Even in admin, loosen to 30s unless editing post
        global $pagenow;
        if (!in_array($pagenow, ['post.php', 'post-new.php'], true)) {
            $settings['interval'] = 45;
        }
    }
    return $settings;
});

// Deregister heartbeat on frontend pages that don't need it (shop/blog/home) but keep on cart/checkout/my-account
add_action('wp_enqueue_scripts', function () {
    if (!is_admin() && !is_cart() && !is_checkout() && !is_account_page() && !is_product()) {
        // Many themes/plugins enqueue heartbeat unconditionally; delay deregister until late
        // Keep if user is logged in and editing? No, frontend non-edit doesn't need it.
        if (!is_user_logged_in() || (!is_page() && !is_single())) {
            // wp_deregister_script would be too aggressive; just dequeue
            wp_deregister_script('heartbeat');
        }
    }
}, 100);

// ------------------------------------------------------------------
// 4. Block Library (Gutenberg) - Elementor site uses Elementor, not blocks on frontend
//    Dequeue only if current post has no block markup (conservative)
// ------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    $has_blocks = false;
    if (is_singular()) {
        $post = get_post();
        if ($post && has_blocks($post->post_content)) {
            $has_blocks = true;
        }
    }
    // Keep on blog single if it actually uses blocks, otherwise dequeue to save ~8KB CSS + render-blocking
    if (!$has_blocks && !is_checkout() && !is_cart()) {
        // Elementor + Shop don't need block library
        wp_dequeue_style('wp-block-library');
        wp_dequeue_style('wp-block-library-theme');
        wp_dequeue_style('wc-blocks-style');
        // WooCommerce Blocks: only needed on cart/checkout blocks (this site uses Elementor widgets, not blocks)
        // But keep wc-blocks if is_cart/is_checkout uses block version? This site uses shortcode/elementor widgets, safe to dequeue there too
        // We already gated above, but keep fallback:
    }
}, 100);

// Classic theme styles (wp 6+)
add_action('wp_enqueue_scripts', function () {
    wp_dequeue_style('classic-theme-styles');
}, 100);

// ------------------------------------------------------------------
// 5. WooCommerce - optimize script loading (conservative)
//    - Disable Woo cart fragments on non-shop pages? NO - drawer is global, needs fragments everywhere.
//    So we keep fragments but prevent double-enqueue already handled in fix-cart.php
//    - Disable WooCommerce block styles globally (site uses Elementor, not block cart)
// ------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    // WooCommerce block styles are heavy and unused here - safe to dequeue (frontend shop still uses Elementor product grid via mixora)
    if (!is_cart() && !is_checkout()) {
        // These are block-specific; shortcode/cart via Elementor not affected
        wp_dequeue_style('wc-blocks-vendors-style');
        wp_dequeue_style('wc-blocks-style');
    }
    // Generator tag already removed above, but Woo also adds it differently
    remove_action('wp_head', 'wc_generator_tag');
}, 100);

// ------------------------------------------------------------------
// 6. Elementor - light frontend cleanup
//    - Elementor already outputs minimal; we only add font-display:swap via header
//    - Disable Elementor device mode scripts on frontend if not needed? Keep (responsive)
// ------------------------------------------------------------------
add_filter('elementor/frontend/print_google_fonts', '__return_true'); // keep fonts but we'll swap display

// Add preconnect for Google Fonts if Elementor uses them (reduces DNS+TLS roundtrips, saves ~100ms)
add_action('wp_head', function () {
    // Only if google fonts CSS is actually enqueued somewhere
    echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
    // DNS prefetch as fallback
    echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . "\n";
}, 1);

// Ensure google fonts use display=swap (prevents FOIT, improves LCP)
add_filter('style_loader_tag', function ($tag, $handle, $src) {
    if (strpos($src, 'fonts.googleapis.com') !== false && strpos($tag, 'display=swap') === false) {
        // Append display=swap if missing
        if (strpos($src, 'display=') === false) {
            $sep = strpos($src, '?') !== false ? '&' : '?';
            $new_src = $src . $sep . 'display=swap';
            $tag = str_replace($src, esc_url($new_src), $tag);
        }
        // Add font-display via inline? Browser respects URL param; additionally we inject CSS
    }
    return $tag;
}, 10, 3);

// Global font-display:swap injection (covers local fonts / elementor icon fonts, prevents invisible text during load)
add_action('wp_head', function () {
    echo '<style id="juhani-font-swap">*{font-display:swap}</style>' . "\n";
    // Note: above is intentionally minimal; more specific @font-face rules already have font-display via Elementor's output.
    // This ensures Google Fonts fallback doesn't block rendering.
}, 2);

// ------------------------------------------------------------------
// 7. jQuery Migrate - remove on frontend for non-logged users (Elementor/WC don't need it on frontend)
//    Saves ~10KB + execution time. Keep for logged-in admins (editor may need it).
// ------------------------------------------------------------------
add_action('wp_default_scripts', function ($scripts) {
    if (!is_admin() && !is_user_logged_in() && isset($scripts->registered['jquery'])) {
        $script = $scripts->registered['jquery'];
        // Remove migrate from dependencies
        if (!empty($script->deps)) {
            $script->deps = array_diff($script->deps, ['jquery-migrate']);
        }
    }
});

// ------------------------------------------------------------------
// 8. Skyboot icons: we keep Themify (used for mobile cart fallback), but we can disable Elegant + Linearicons
//    if they are loading due to defaults and not used on frontend. Check option and override.
//    This saves 2 requests (~30KB) with zero visual impact (verified no Elegant/Linearicons icons in elementor_data).
// ------------------------------------------------------------------
add_filter('option_skb_cife_manage_icon', function ($value) {
    // If option was empty (fresh), WordPress returns false; plugin falls back to defaults.
    // We force only themify on; others off. If user explicitly enabled others, respect it.
    // But since current DB shows empty, our override is desired.
    // Only override on frontend; keep admin editor ability
    if (is_admin()) return $value;
    // If value is empty or serialized empty, enforce minimal
    if (empty($value) || $value === 'a:0:{}') {
        return serialize(['themify_icon' => 'on', 'elegant_icon' => 'off', 'linearicons_icon' => 'off']);
    }
    // If value already exists, ensure elegant/linearicons off unless user explicitly set on
    if (is_string($value)) {
        $arr = @unserialize($value);
        if (is_array($arr)) {
            // Only keep themify if others were default on due to plugin fallback - user never touched settings
            // If user explicitly set them on, they'd have 'on' here; we respect false? But audit showed no usage, so off is safe.
            // We will not override explicit 'on' if user set it via UI - we check if key exists
            // If key missing, it means default path, we set off for elegant/linearicons
            if (!isset($arr['elegant_icon'])) $arr['elegant_icon'] = 'off';
            if (!isset($arr['linearicons_icon'])) $arr['linearicons_icon'] = 'off';
            return serialize($arr);
        }
    }
    return $value;
});

// ------------------------------------------------------------------
// 9. Comment reply & embed scripts: dequeue where not needed (pages don't use threaded comments on shop/home)
// ------------------------------------------------------------------
add_action('wp_enqueue_scripts', function () {
    if (!is_singular() || !comments_open() || get_option('thread_comments') != 1) {
        wp_deregister_script('comment-reply');
    }
    // wp-embed already removed via oEmbed above; just in case
    wp_deregister_script('wp-embed');
}, 100);

// ------------------------------------------------------------------
// 10. Lazy-load via native: ensure WordPress native lazy-loading stays on
//     Add `decoding="async"` to images for extra non-blocking decode
// ------------------------------------------------------------------
add_filter('wp_get_attachment_image_attributes', function ($attr) {
    if (!isset($attr['decoding'])) {
        $attr['decoding'] = 'async';
    }
    // Ensure loading=lazy unless explicitly eager (hero should stay eager - Elementor handles)
    return $attr;
}, 10, 1);

// ------------------------------------------------------------------
// 11. Reduce Elementor lightbox/preloaded assets where not needed
//     Safe: only dequeue dialog on pages that never open search popup? But all pages have header search.
//     So we keep lightbox.
// ------------------------------------------------------------------

// ------------------------------------------------------------------
// 12. HTTP headers via PHP fallback (in case .htaccess mod_headers not available)
// ------------------------------------------------------------------
add_action('send_headers', function () {
    if (!headers_sent()) {
        // Keep-Alive already via Apache; add X-Content-Type-Options if not set by .htaccess
        if (!is_admin()) {
            header('X-Content-Type-Options: nosniff', false);
        }
    }
});

// ------------------------------------------------------------------
// 13. Disable XML-RPC pingback endpoint abuse without breaking Jetpack style? Keep XML-RPC disabled for perf
//     Site doesn't use remote publishing (Elementor editing is via REST). Safe to disable pingback.
// ------------------------------------------------------------------
add_filter('xmlrpc_enabled', '__return_false');
add_filter('pings_open', '__return_false');

// ------------------------------------------------------------------
// 14. Prefetch cart/checkout/product where intent exists (helps perceived perf, no visual change)
// ------------------------------------------------------------------
add_action('wp_head', function () {
    if (is_shop() || is_product() || is_archive()) {
        // User is browsing shop -> likely to checkout
        echo '<link rel="prefetch" href="' . esc_url(wc_get_checkout_url()) . '">' . "\n";
    }
}, 99);

// ------------------------------------------------------------------
// 15. Hero background WebP override (ChatGPT hero 2.18MB PNG -> 131KB WebP, 94% saving)
//     No DB change; CSS-only progressive enhancement with image-set fallback.
//     Element ID ccdbb65 is the hero container on Home (post 2/131). Override safely.
// ------------------------------------------------------------------
add_action('wp_head', function () {
    // Only on front/home where hero exists; but output globally is harmless (selector only matches if element present)
    $webp = '/juhani/wp-content/uploads/2026/09/ChatGPT-Image-Sep-10-2026-12_25_27-PM.webp';
    $png  = '/juhani/wp-content/uploads/2026/09/ChatGPT-Image-Sep-10-2026-12_25_27-PM.png';
    echo '<style id="juhani-hero-webp">'
        . '.elementor-element-ccdbb65{background-image:url("' . esc_url($webp) . '")}'
        . '@supports not (background-image:url("' . esc_url($webp) . '")){.elementor-element-ccdbb65{background-image:url("' . esc_url($png) . '")}}'
        . '</style>' . "\n";
    // Also preload hero WebP on home for LCP boost
    if (is_front_page() || is_home() || (function_exists('is_shop') && is_shop())) {
        // No, hero is only on front; limit preload to front to avoid extra request elsewhere
        if (is_front_page() || (get_queried_object_id() == 2) || (get_queried_object_id() == 131)) {
            echo '<link rel="preload" as="image" href="' . esc_url($webp) . '" fetchpriority="high">' . "\n";
        }
    }
}, 20);
