<?php
/**
 * Plugin Name: Fix Juhani Cart Toggle
 * Description: Ensures Elementor Menu Cart (side-cart) works correctly inside fixed header and on mobile. Moves cart container to body to avoid transform containing block bug, adds fallback link to cart page.
 */

if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts', function() {
    // Ensure wc cart fragments is enqueued
    if (function_exists('WC')) {
        wp_enqueue_script('wc-cart-fragments');
    }
}, 20);

// AJAX: Update mini-cart quantity from drawer stepper
add_action('wp_ajax_juhani_update_cart_qty', 'juhani_update_cart_qty');
add_action('wp_ajax_nopriv_juhani_update_cart_qty', 'juhani_update_cart_qty');
function juhani_update_cart_qty() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['nonce'])), 'juhani_cart_nonce')) {
        wp_send_json_error(['message' => 'Invalid nonce'], 403);
    }
    if (!function_exists('WC') || !WC()->cart) {
        wp_send_json_error(['message' => 'Cart not available'], 400);
    }
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field(wp_unslash($_POST['cart_item_key'])) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    if (!$cart_item_key) {
        wp_send_json_error(['message' => 'Missing cart item key'], 400);
    }
    if ($quantity < 0) $quantity = 0;
    // Validate stock / max
    $cart = WC()->cart->get_cart();
    if (!isset($cart[$cart_item_key])) {
        wp_send_json_error(['message' => 'Item not found'], 404);
    }
    if ($quantity === 0) {
        WC()->cart->remove_cart_item($cart_item_key);
    } else {
        // Check product max quantity if set
        $cart_item = $cart[$cart_item_key];
        $_product = $cart_item['data'] ?? null;
        if ($_product && method_exists($_product, 'get_max_purchase_quantity')) {
            $max = $_product->get_max_purchase_quantity();
            if ($max > 0 && $quantity > $max) $quantity = $max;
        }
        WC()->cart->set_quantity($cart_item_key, $quantity, true);
    }
    WC()->cart->calculate_totals();
    // Return refreshed fragments so drawer updates instantly
    if (function_exists('WC')) {
        WC()->cart->maybe_set_cart_cookies();
    }
    wp_send_json_success([
        'quantity' => $quantity,
        'cart_hash' => WC()->cart->get_cart_hash(),
        'fragments' => apply_filters('woocommerce_add_to_cart_fragments', []),
    ]);
}

add_action('wp_head', function() {
    ?>
    <style id="juhani-cart-fix">
    /* Ensure cart container is always on top and visible */
    body .elementor-menu-cart__container {
        z-index: 99999 !important;
    }
    body .elementor-menu-cart__main {
        z-index: 99999 !important;
    }
    /* Fix for header transform bug - ensure cart container escapes header */
    .elementor-location-header .elementor-menu-cart__container {
        position: fixed !important;
    }
    /* Mobile & Tablet: ensure shopping cart in header is hidden as requested */
    @media (max-width: 1024px) {
        .elementor-element-4358431,
        .elementor-element-8d170ad,
        .elementor-location-header .elementor-widget-woocommerce-menu-cart {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }
    }
    /* Lock body scrolling when side drawer is open - separate for cart and menu */
    body.juhani-drawer-open,
    body.juhani-cart-open {
        overflow: hidden !important;
        touch-action: none !important;
    }
    /* Ensure cart container hidden/shown correctly via aria-hidden for all viewports - fixes desktop stuck open */
    .elementor-menu-cart__container[aria-hidden="true"] {
        visibility: hidden !important;
        opacity: 0 !important;
        pointer-events: none !important;
    }
    .elementor-menu-cart__container[aria-hidden="false"] {
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
    }
    /* Ensure cart drawer at body level is visible when cart open - global z-index above header/footer */
    body.juhani-cart-open .elementor-menu-cart__container {
        display: flex !important;
        visibility: visible !important;
        opacity: 1 !important;
        pointer-events: auto !important;
        z-index: 999998 !important;
    }
    body.juhani-cart-open .elementor-menu-cart__main {
        transform: translateX(0) !important;
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 999999 !important;
    }
    /* Ensure cart drawer appears above header/footer when open - header must not overlap drawer */
    body.juhani-cart-open .elementor-location-header,
    body.juhani-cart-open .elementor-71 .elementor-element.elementor-element-1d9f82b,
    body.juhani-cart-open [data-id="1d9f82b"] {
        z-index: 10 !important;
    }
    body.juhani-cart-open .elementor-element-34832f4 {
        z-index: 999998 !important;
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
    }
    body.juhani-cart-open .elementor-element-34832f4 .elementor-element-3949973 {
        visibility: hidden !important;
        pointer-events: none !important;
    }
    body.juhani-cart-open .elementor-element-34832f4 .elementor-menu-cart__container {
        z-index: 999998 !important;
    }
    body.juhani-cart-open .elementor-element-34832f4 .elementor-menu-cart__main {
        z-index: 999999 !important;
    }
    /* Ensure close button is visible - fix cross icon */
    body .juhani-mini-cart-close {
        visibility: visible !important;
        opacity: 1 !important;
        z-index: 10 !important;
        position: relative !important;
        display: inline-flex !important;
    }
    body .juhani-mini-cart-close::before,
    body .juhani-mini-cart-close::after {
        visibility: visible !important;
        opacity: 1 !important;
    }

    /* Ensure Header Container has stable high z-index */
    .elementor-location-header,
    [data-id="1d9f82b"],
    .elementor-71 .elementor-element.elementor-element-1d9f82b {
        z-index: 99999 !important;
    }

    /* Independent Fullscreen Backdrop Overlay (Behind header 99999 & drawer 999995 to prevent drawer blur) */
    .juhani-drawer-overlay {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        right: 0 !important;
        bottom: 0 !important;
        width: 100vw !important;
        height: 100vh !important;
        height: 100dvh !important;
        background: rgba(3, 10, 20, 0.65) !important;
        backdrop-filter: blur(4px) !important;
        -webkit-backdrop-filter: blur(4px) !important;
        z-index: 99900 !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
        transition: opacity 0.35s ease, visibility 0.35s ease !important;
        cursor: pointer !important;
    }
    body.juhani-drawer-open .juhani-drawer-overlay {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
    }

    /* ====================================================
       MOBILE FLOATING FOOTER BAR
       ==================================================== */
    @media (max-width: 767px) {
        body {
            padding-bottom: 65px !important;
        }
        .elementor-element-34832f4 {
            position: fixed !important;
            bottom: 0 !important;
            top: auto !important;
            left: 0 !important;
            right: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            background: #ffffff !important;
            box-shadow: 0 -2px 12px rgba(0, 0, 0, 0.08) !important;
            z-index: 99990 !important;
            border-top: 1px solid #eaeaea !important;
            transform: none !important;
        }
        .elementor-element-34832f4 .e-con-inner {
            padding: 0 !important;
            max-width: 100% !important;
            width: 100% !important;
        }
        .elementor-element-3949973 {
            display: flex !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            justify-content: space-around !important;
            align-items: center !important;
            width: 100% !important;
            padding: 8px 0 6px 0 !important;
            gap: 0 !important;
        }
        .elementor-element-3949973 > .e-con {
            flex: 1 1 0 !important;
            max-width: 20% !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0 !important;
            margin: 0 !important;
            cursor: pointer !important;
        }
        .elementor-element-3949973 .elementor-widget-icon-box {
            width: 100% !important;
            margin: 0 !important;
        }
        .elementor-element-3949973 .elementor-icon-box-wrapper {
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            gap: 3px !important;
        }
        .elementor-element-3949973 .elementor-icon-box-icon {
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 26px !important;
        }
        .elementor-element-3949973 .elementor-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #10395E !important;
            fill: #10395E !important;
            font-size: 24px !important;
            line-height: 1 !important;
        }
        .elementor-element-3949973 .elementor-icon svg {
            width: 24px !important;
            height: 24px !important;
            fill: #10395E !important;
        }
        .elementor-element-3949973 .elementor-icon i {
            font-size: 24px !important;
            line-height: 1 !important;
            color: #10395E !important;
        }
        .elementor-element-3949973 .elementor-icon-box-title,
        .elementor-element-3949973 .elementor-icon-box-title a {
            margin: 0 !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 19.2px !important;
            color: #10395E !important;
            text-transform: capitalize !important;
            letter-spacing: normal !important;
            text-decoration: none !important;
        }
        .elementor-element-3949973 .elementor-icon-box-description {
            display: none !important;
        }

        /* Cart Widget in Mobile Bottom Bar — 1:1 match with icon-box (Home/Search/Shop) */
        .elementor-element-77a58cb {
            flex: 1 1 0 !important;
            max-width: 20% !important;
            min-width: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            padding: 0 !important;
            margin: 0 !important;
            cursor: pointer !important;
            height: 48.1875px !important;
            gap: 3px !important;
        }
        .elementor-element-77a58cb .elementor-widget-woocommerce-menu-cart {
            width: 100% !important;
            height: 26px !important;
            min-height: 26px !important;
            max-height: 26px !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .elementor-element-77a58cb .elementor-widget-container,
        .elementor-element-77a58cb .elementor-menu-cart__wrapper,
        .elementor-element-77a58cb .elementor-menu-cart__toggle_wrapper,
        .elementor-element-77a58cb .elementor-menu-cart__toggle {
            width: 100% !important;
            height: 26px !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
        .elementor-element-77a58cb #elementor-menu-cart__toggle_button,
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button,
        .elementor-element-77a58cb .elementor-button {
            background: transparent !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            box-shadow: none !important;
            width: 100% !important;
            height: 26px !important;
            min-height: 26px !important;
            line-height: 1 !important;
            text-decoration: none !important;
            cursor: pointer !important;
            position: relative !important;
        }
        .elementor-element-77a58cb .elementor-button-text,
        .elementor-element-77a58cb .elementor-screen-only {
            display: none !important;
        }
        .elementor-element-77a58cb .elementor-button-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 26px !important;
            width: 24px !important;
            min-width: 24px !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
            line-height: 1 !important;
        }
        /* Cart icon — 1:1 match with Home/Search/Shop (Themify line style, 24px) */
        .elementor-element-77a58cb .elementor-button-icon i,
        .elementor-element-77a58cb .elementor-button-icon svg,
        .elementor-element-77a58cb .elementor-button-icon i[class*="ti-"],
        .elementor-element-77a58cb .elementor-button-icon i.eicon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 24px !important;
            height: 24px !important;
            font-size: 24px !important;
            line-height: 1 !important;
            color: #10395E !important;
            fill: #10395E !important;
            stroke: none !important;
            margin: 0 !important;
            padding: 0 !important;
            position: relative !important;
        }
        .elementor-element-77a58cb .elementor-button-icon i[class*="ti-"] {
            font-family: "themify" !important;
            font-weight: 400 !important;
            speak: none !important;
            font-style: normal !important;
        }
        .elementor-element-77a58cb .elementor-button-icon svg,
        .elementor-element-77a58cb .elementor-button-icon svg.juhani-cart-svg {
            display: none !important;
        }
        .elementor-element-77a58cb .elementor-button-icon.juhani-no-font svg.juhani-cart-svg {
            display: block !important;
            stroke: #10395E !important;
            fill: none !important;
            width: 24px !important;
            height: 24px !important;
        }
        .elementor-element-77a58cb .elementor-button-icon.juhani-no-font i {
            display: none !important;
        }
        .elementor-element-77a58cb .elementor-button-icon-qty {
            position: absolute !important;
            top: -4px !important;
            right: -8px !important;
            left: auto !important;
            background: #1AA7EC !important;
            color: #ffffff !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            min-width: 16px !important;
            height: 16px !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 3px !important;
            line-height: 1 !important;
            z-index: 2 !important;
            border: 1px solid #ffffff !important;
            box-sizing: border-box !important;
        }

        /* The user's Heading Widget styling: exact match with Home/Search/Shop titles */
        .elementor-element-77a58cb .elementor-widget-heading {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            height: 19.1875px !important;
            min-height: 19.1875px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            cursor: pointer !important;
        }
        .elementor-element-77a58cb .elementor-widget-heading .elementor-heading-title,
        .elementor-element-77a58cb .elementor-heading-title {
            margin: 0 !important;
            padding: 0 !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 16px !important;
            font-weight: 400 !important;
            line-height: 19.2px !important;
            color: #10395E !important;
            text-transform: capitalize !important;
            letter-spacing: normal !important;
            text-align: center !important;
            width: 100% !important;
            cursor: pointer !important;
            transition: color 0.16s ease !important;
        }

        /* ====================================================
           FOOTER CART TOGGLE — HOVER ALIGNED WITH DESKTOP CART
           Mirrors desktop cart/drawer hover: same accent
           #4CA2D9 / #10395E, same transitions (0.16s ease)
           — ensures footer-triggered drawer toggle feels
           identical to desktop header cart icon hover
           ==================================================== */
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button {
            transition: opacity 0.16s ease, transform 0.16s ease !important;
        }
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button:hover {
            opacity: 0.92 !important;
        }
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button:hover .elementor-button-icon svg {
            fill: #4CA2D9 !important;
            transition: fill 0.16s ease !important;
        }
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button:hover .elementor-screen-only {
            color: #4CA2D9 !important;
            transition: color 0.16s ease !important;
        }
        .elementor-element-77a58cb .elementor-menu-cart__toggle .elementor-button:hover .elementor-button-icon-qty {
            background: #10395E !important;
            transition: background-color 0.16s ease !important;
        }
        /* Footer Bar Icon-Box hover — unified with desktop nav drawer hover (#4CA2D9 on #f2f9fd) */
        .elementor-element-3949973 > .e-con:hover .elementor-icon,
        .elementor-element-3949973 > .e-con:hover .elementor-icon svg,
        .elementor-element-3949973 > .e-con:hover .elementor-icon i {
            color: #4CA2D9 !important;
            fill: #4CA2D9 !important;
            transition: color 0.16s ease, fill 0.16s ease !important;
        }
        .elementor-element-3949973 > .e-con:hover .elementor-icon-box-title,
        .elementor-element-3949973 > .e-con:hover .elementor-icon-box-title a {
            color: #4CA2D9 !important;
            transition: color 0.16s ease !important;
        }
    }

    /* ====================================================
       PREMIUM WOOCOMMERCE MINI-CART DRAWER
       ==================================================== */
    body .elementor-menu-cart__container,
    body .elementor-menu-cart__main,
    body .elementor-menu-cart__main * {
        box-sizing: border-box !important;
        border-radius: 0 !important;
    }

    body .elementor-menu-cart__container {
        background: rgba(2, 8, 18, 0.54) !important;
        backdrop-filter: blur(5px) !important;
        -webkit-backdrop-filter: blur(5px) !important;
    }

    body .elementor-menu-cart__main {
        width: 430px !important;
        max-width: min(430px, 100vw) !important;
        height: 100vh !important;
        height: 100dvh !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #102033 !important;
        border-left: 1px solid #e5edf5 !important;
        box-shadow: -16px 0 44px rgba(7, 21, 38, 0.14) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
    }

    body .elementor-menu-cart__header {
        display: none !important;
    }

    body .juhani-mini-cart-header {
        min-height: 52px !important;
        padding: 12px 18px 10px 18px !important;
        border-bottom: 1px solid #e4ebf2 !important;
        background: #ffffff !important;
        display: flex !important;
        align-items: center !important;
        justify-content: space-between !important;
        flex: 0 0 auto !important;
    }

    body .elementor-menu-cart__main > .elementor-menu-cart__close-button,
    body .elementor-menu-cart__main > .elementor-menu-cart__close-button-custom {
        display: none !important;
    }

    body .elementor-menu-cart__header .elementor-menu-cart__heading,
    body .juhani-mini-cart-title {
        color: #06395f !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        line-height: 1.2 !important;
        letter-spacing: 0 !important;
        text-transform: uppercase !important;
        margin: 0 !important;
    }

    body .juhani-mini-cart-count {
        color: #42576e !important;
        font-size: 15px !important;
        font-weight: 600 !important;
    }

    body .elementor-menu-cart__close-button,
    body .elementor-menu-cart__close-button-custom,
    body .juhani-mini-cart-close {
        position: relative !important;
        width: 36px !important;
        height: 36px !important;
        min-width: 36px !important;
        border: 1px solid #bfcdda !important;
        background: #ffffff !important;
        color: #0b2d4a !important;
        font-size: 0 !important;
        border-radius: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease !important;
        padding: 0 !important;
        cursor: pointer !important;
    }

    body .elementor-menu-cart__close-button::before,
    body .elementor-menu-cart__close-button::after,
    body .elementor-menu-cart__close-button-custom::before,
    body .elementor-menu-cart__close-button-custom::after,
    body .juhani-mini-cart-close::before,
    body .juhani-mini-cart-close::after {
        background: #0b2d4a !important;
        color: #0b2d4a !important;
        width: 16px !important;
        height: 2px !important;
        opacity: 1 !important;
        content: "" !important;
        position: absolute !important;
        left: 9px !important;
        top: 16px !important;
    }

    body .elementor-menu-cart__close-button::before,
    body .elementor-menu-cart__close-button-custom::before,
    body .juhani-mini-cart-close::before {
        transform: rotate(45deg) !important;
    }

    body .elementor-menu-cart__close-button::after,
    body .elementor-menu-cart__close-button-custom::after,
    body .juhani-mini-cart-close::after {
        transform: rotate(-45deg) !important;
    }

    body .elementor-menu-cart__close-button svg,
    body .elementor-menu-cart__close-button-custom svg {
        display: none !important;
        width: 20px !important;
        height: 20px !important;
        fill: none !important;
        stroke: #0b2d4a !important;
        stroke-width: 2.4 !important;
        opacity: 1 !important;
    }

    body .elementor-menu-cart__close-button:hover,
    body .elementor-menu-cart__close-button-custom:hover,
    body .juhani-mini-cart-close:hover {
        background: #f2f7fb !important;
        border-color: #c8d7e4 !important;
        color: #003f6d !important;
    }

    body .elementor-menu-cart__products {
        flex: 1 1 auto !important;
        min-height: 0 !important;
        padding: 8px 18px 4px 18px !important;
        overflow-y: auto !important;
        overflow-x: hidden !important;
        -webkit-overflow-scrolling: touch !important;
        background: #ffffff !important;
    }

    body .elementor-menu-cart__product,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item,
    body .widget_shopping_cart_content .woocommerce-mini-cart-item {
        position: relative !important;
        display: grid !important;
        grid-template-columns: 96px minmax(0, 1fr) !important;
        column-gap: 12px !important;
        row-gap: 6px !important;
        padding: 14px !important;
        margin: 0 0 16px !important;
        border: none !important;
        border-width: 0 !important;
        background: #ffffff !important;
        box-shadow: none !important;
    }

    body .elementor-menu-cart__product:first-child,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item:first-child {
        padding-top: 14px !important;
    }

    body .elementor-menu-cart__product:last-child,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item:last-child {
        margin-bottom: 0 !important;
    }

    body .elementor-menu-cart__product-image,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item > a:not(.remove):has(img) {
        grid-column: 1 !important;
        grid-row: 1 / span 2 !important;
        width: 96px !important;
        min-width: 96px !important;
        align-self: start !important;
    }

    body .elementor-menu-cart__product-image img,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item img {
        width: 96px !important;
        height: 96px !important;
        object-fit: cover !important;
        border-radius: 0 !important;
        border: 0 !important;
        background: #ffffff !important;
        display: block !important;
    }

    body .elementor-menu-cart__product-name,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item > a:not(.remove) {
        grid-column: 2 !important;
        grid-row: 1 !important;
        min-width: 0 !important;
        padding-right: 36px !important;
        padding-left: 0 !important;
        margin: 0 !important;
        align-self: start !important;
        justify-self: start !important;
        text-align: left !important;
        color: #0f2238 !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 15px !important;
        font-weight: 700 !important;
        line-height: 1.3 !important;
        letter-spacing: 0 !important;
        text-decoration: none !important;
        overflow-wrap: normal !important;
    }

    body .elementor-menu-cart__product-price,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item .quantity {
        grid-column: 2 !important;
        grid-row: 2 !important;
        color: #00447c !important;
        font-size: 17px !important;
        font-weight: 800 !important;
        line-height: 1.25 !important;
        margin: 0 !important;
    }

    body .elementor-menu-cart__product-remove,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove {
        position: absolute !important;
        top: 14px !important;
        right: 20px !important;
        width: 32px !important;
        height: 32px !important;
        min-width: 32px !important;
        border: 1px solid #e0e8f0 !important;
        background: #ffffff !important;
        color: #0b2d4a !important;
        font-size: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-size: 20px !important;
        line-height: 1 !important;
        text-decoration: none !important;
        transition: background-color 0.18s ease, color 0.18s ease, border-color 0.18s ease !important;
    }

    body .elementor-menu-cart__product-remove::before,
    body .elementor-menu-cart__product-remove::after {
        background: #0b2d4a !important;
        opacity: 1 !important;
        width: 15px !important;
        height: 2px !important;
        content: "" !important;
        position: absolute !important;
        left: 8px !important;
        top: 14px !important;
    }

    body .elementor-menu-cart__product-remove::before {
        transform: rotate(45deg) !important;
    }

    body .elementor-menu-cart__product-remove::after {
        transform: rotate(-45deg) !important;
    }

    body .elementor-menu-cart__product-remove svg,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove svg {
        display: none !important;
        width: 16px !important;
        height: 16px !important;
        stroke: #0b2d4a !important;
        opacity: 1 !important;
    }

    body .elementor-menu-cart__product-remove:hover,
    body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove:hover {
        background: #f7fafc !important;
        border-color: #cfdce8 !important;
        color: #003f6d !important;
    }

    body .elementor-menu-cart__product .variation,
    body .woocommerce-mini-cart .variation {
        grid-column: 1 / -1 !important;
        grid-row: 3 !important;
        display: grid !important;
        grid-template-columns: minmax(110px, 0.85fr) minmax(0, 1.15fr) !important;
        gap: 0 18px !important;
        width: 100% !important;
        margin: 0 !important;
        padding: 12px 16px !important;
        background: #f3f6f9 !important;
        border: 1px solid #e5ebf2 !important;
        color: #25364a !important;
    }

    body .elementor-menu-cart__product .variation dt,
    body .elementor-menu-cart__product .variation dd,
    body .woocommerce-mini-cart .variation dt,
    body .woocommerce-mini-cart .variation dd {
        float: none !important;
        margin: 0 !important;
        padding: 0 !important;
        min-width: 0 !important;
        min-height: 30px !important;
        line-height: 1.35 !important;
        word-break: normal !important;
        overflow-wrap: normal !important;
        white-space: normal !important;
    }

    body .elementor-menu-cart__product .variation dt,
    body .woocommerce-mini-cart .variation dt {
        color: #344b63 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-transform: none !important;
        display: flex !important;
        align-items: center !important;
        border-bottom: 1px solid #dce4ec !important;
    }

    body .elementor-menu-cart__product .variation dd,
    body .woocommerce-mini-cart .variation dd {
        color: #243a52 !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        text-align: left !important;
        display: flex !important;
        align-items: center !important;
        border-bottom: 1px solid #dce4ec !important;
        white-space: nowrap !important;
        overflow: hidden !important;
        text-overflow: ellipsis !important;
    }

    body .elementor-menu-cart__product .variation dd p,
    body .woocommerce-mini-cart .variation dd p {
        margin: 0 !important;
    }

    body .elementor-menu-cart__product .variation dt:nth-last-of-type(1),
    body .elementor-menu-cart__product .variation dd:last-child,
    body .woocommerce-mini-cart .variation dt:nth-last-of-type(1),
    body .woocommerce-mini-cart .variation dd:last-child {
        border-bottom: 0 !important;
    }

    body .juhani-mini-cart-qty {
        grid-column: 2 !important;
        grid-row: 2 !important;
        display: inline-flex !important;
        width: 124px !important;
        max-width: 100% !important;
        align-items: center !important;
        border: 1px solid #c9d7e4 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        box-shadow: 0 1px 0 rgba(8, 25, 45, 0.04) !important;
        align-self: start !important;
        justify-self: start !important;
        margin: 0 !important;
        margin-left: 0 !important;
        padding-left: 0 !important;
    }

    body .juhani-mini-cart-qty button,
    body .juhani-mini-cart-qty span {
        height: 38px !important;
        width: 38px !important;
        min-width: 38px !important;
        max-width: 38px !important;
        border: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: transparent !important;
        color: #0f2238 !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
        padding: 0 !important;
        border-right: 1px solid #dce6ef !important;
    }

    body .juhani-mini-cart-qty button {
        color: #003f6d !important;
        background: #f7fafc !important;
        cursor: pointer !important;
        transition: background-color 0.16s ease, color 0.16s ease !important;
        font-size: 0 !important;
        position: relative !important;
    }

    body .juhani-mini-cart-qty button::before {
        color: #003f6d !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        line-height: 1 !important;
    }

    body .juhani-mini-cart-qty button:first-child::before,
    body .juhani-mini-cart-qty .juhani-mini-cart-minus::before {
        content: "-" !important;
    }

    body .juhani-mini-cart-qty button:last-child::before,
    body .juhani-mini-cart-qty .juhani-mini-cart-plus::before {
        content: "+" !important;
    }

    body .juhani-mini-cart-qty button:hover {
        background: #eaf3fa !important;
        color: #002f52 !important;
    }

    body .juhani-mini-cart-qty span {
        width: 48px !important;
        min-width: 48px !important;
        max-width: 48px !important;
        background: #ffffff !important;
        color: #071d33 !important;
    }

    body .juhani-mini-cart-qty button:last-child {
        border-right: 0 !important;
        border-left: 1px solid #dce6ef !important;
    }

    body .juhani-mini-cart-qty-source {
        display: none !important;
    }

    body .juhani-mini-cart-line-total,
    body .elementor-menu-cart__product-subtotal {
        display: none !important;
    }

    body .elementor-menu-cart__subtotal {
        flex: 0 0 auto !important;
        margin: 0 !important;
        padding: 15px 18px 6px !important;
        border-top: 0 !important;
        border-bottom: 0 !important;
        background: #ffffff !important;
        color: #0f2238 !important;
        display: flex !important;
        align-items: flex-start !important;
        justify-content: space-between !important;
        gap: 14px !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 18px !important;
        font-weight: 700 !important;
    }

    body .elementor-menu-cart__main .woocommerce-mini-cart__total,
    body .elementor-menu-cart__main p.total {
        display: none !important;
    }

    body .elementor-menu-cart__subtotal strong {
        color: #0f2238 !important;
        font-size: 18px !important;
        font-weight: 800 !important;
        text-transform: none !important;
        letter-spacing: 0 !important;
    }

    body .elementor-menu-cart__subtotal .amount {
        color: #0f2238 !important;
        font-size: 20px !important;
        font-weight: 800 !important;
    }

    body .juhani-mini-cart-tax-note {
        display: none !important;
    }

    body .elementor-menu-cart__footer-buttons {
        position: static !important;
        z-index: 2 !important;
        flex: 0 0 auto !important;
        display: grid !important;
        grid-template-columns: 1fr !important;
        gap: 10px !important;
        padding: 16px 18px 18px !important;
        background: #ffffff !important;
        border-top: 0 !important;
        box-shadow: none !important;
    }

    body .elementor-menu-cart__footer-buttons a,
    body .elementor-menu-cart__footer-buttons .elementor-button {
        min-height: 48px !important;
        width: 100% !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        padding: 12px 16px !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 15px !important;
        font-weight: 800 !important;
        letter-spacing: 0 !important;
        text-transform: uppercase !important;
        text-decoration: none !important;
    }

    body .elementor-menu-cart__footer-buttons a:first-child,
    body .elementor-menu-cart__footer-buttons .elementor-button--view-cart {
        background: #ffffff !important;
        border: 2px solid #003b68 !important;
        color: #0b2d4a !important;
    }

    body .elementor-menu-cart__footer-buttons a:last-child,
    body .elementor-menu-cart__footer-buttons .elementor-button--checkout {
        background: #003f6d !important;
        border: 2px solid #003f6d !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    body .elementor-menu-cart__footer-buttons a:hover,
    body .elementor-menu-cart__footer-buttons .elementor-button:hover {
        filter: brightness(0.96) !important;
    }

    body .woocommerce-mini-cart__empty-message,
    body .elementor-menu-cart__empty-message {
        padding: 28px 22px !important;
        color: #607084 !important;
        font-size: 14px !important;
        text-align: center !important;
    }

    /* Desktop Header Cart Toggle — hover aligned with footer toggle (same 0.16s ease, opacity) */
    @media (min-width: 768px) {
        body .elementor-element-8d170ad .elementor-menu-cart__toggle .elementor-button,
        body .elementor-location-header .elementor-menu-cart__toggle .elementor-button {
            transition: opacity 0.16s ease !important;
        }
        body .elementor-element-8d170ad .elementor-menu-cart__toggle .elementor-button:hover,
        body .elementor-location-header .elementor-menu-cart__toggle .elementor-button:hover {
            opacity: 0.92 !important;
        }
    }

    /* ====================================================
       MOBILE CART DRAWER — RESPONSIVE VERSION OF DESKTOP
       ==================================================== */
    @media (max-width: 767px) {
        /* Backdrop Overlay (Left portion visible, blur effect) */
        body .elementor-menu-cart__container {
            position: fixed !important;
            inset: 0 !important;
            width: 100vw !important;
            height: 100vh !important;
            height: 100dvh !important;
            background: rgba(4, 18, 32, 0.55) !important;
            backdrop-filter: blur(3px) !important;
            -webkit-backdrop-filter: blur(3px) !important;
            z-index: 999998 !important;
            display: flex !important;
            justify-content: flex-end !important;
            align-items: stretch !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: opacity 0.32s ease, visibility 0.32s ease !important;
        }
        body.juhani-cart-open .elementor-menu-cart__container,
        body .elementor-widget-woocommerce-menu-cart.elementor-menu-cart--shown .elementor-menu-cart__container {
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
        }

        /* 1. Mobile Drawer Width & Position: 88vw max 420px, right-aligned */
        body .elementor-menu-cart__main {
            position: fixed !important;
            top: 0 !important;
            right: 0 !important;
            bottom: 0 !important;
            left: auto !important;
            width: 88vw !important;
            max-width: 420px !important;
            height: 100vh !important;
            height: 100dvh !important;
            max-height: 100dvh !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            background: #ffffff !important;
            border-left: none !important;
            box-shadow: -8px 0 28px rgba(7, 21, 38, 0.16) !important;
            z-index: 999999 !important;
            box-sizing: border-box !important;
            padding: 0 !important;
            transform: translateX(100%) !important;
            transition: transform 0.34s cubic-bezier(0.16, 1, 0.3, 1) !important;
        }
        body.juhani-cart-open .elementor-menu-cart__main,
        body .elementor-widget-woocommerce-menu-cart.elementor-menu-cart--shown .elementor-menu-cart__main {
            transform: translateX(0) !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        @media (max-width: 380px) {
            body .elementor-menu-cart__main {
                width: 90vw !important;
                max-width: 90vw !important;
            }
        }

        /* 2. Header & Close Button */
        body .juhani-mini-cart-header {
            flex: 0 0 auto !important;
            min-height: 56px !important;
            padding: 14px 14px 12px 14px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            border-bottom: 1px solid #e4ebf2 !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
        }
        body .juhani-mini-cart-title {
            font-family: "Poppins", sans-serif !important;
            font-size: 17px !important;
            font-weight: 800 !important;
            color: #06395f !important;
            text-transform: uppercase !important;
            margin: 0 !important;
            letter-spacing: 0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
        }
        body .juhani-mini-cart-count {
            font-size: 15px !important;
            font-weight: 600 !important;
            color: #42576e !important;
        }
        body .juhani-mini-cart-close {
            width: 38px !important;
            height: 38px !important;
            min-width: 38px !important;
            min-height: 38px !important;
            border: 1px solid #d7e0e8 !important;
            background: #ffffff !important;
            color: #061d33 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            cursor: pointer !important;
            border-radius: 0 !important;
            flex-shrink: 0 !important;
            transition: background-color 0.18s ease, border-color 0.18s ease !important;
        }
        body .juhani-mini-cart-close:hover {
            background: #f2f7fb !important;
            border-color: #c8d7e4 !important;
        }
        body .juhani-mini-cart-close svg {
            display: block !important;
            width: 18px !important;
            height: 18px !important;
            stroke: #061d33 !important;
            pointer-events: none !important;
        }
        body .juhani-mini-cart-close::before,
        body .juhani-mini-cart-close::after {
            display: none !important;
            content: none !important;
        }

        /* Essential Flex Column Container for Cart Content */
        body .elementor-menu-cart__main .widget_shopping_cart_content {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            height: 100% !important;
            max-height: 100% !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }

        /* 3. Product Remove X Button */
        body .elementor-menu-cart__product-remove,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove {
            position: absolute !important;
            top: 10px !important;
            right: 10px !important;
            width: 34px !important;
            height: 34px !important;
            min-width: 34px !important;
            min-height: 34px !important;
            border: 1px solid #e0e8f0 !important;
            background: #ffffff !important;
            color: #0b2d4a !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
            font-size: 0 !important;
            cursor: pointer !important;
            z-index: 5 !important;
            transition: background-color 0.18s ease, border-color 0.18s ease !important;
        }
        body .elementor-menu-cart__product-remove:hover,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove:hover {
            background: #f7fafc !important;
            border-color: #cfdce8 !important;
        }
        body .elementor-menu-cart__product-remove a {
            position: absolute !important;
            top: 0 !important;
            left: 0 !important;
            width: 100% !important;
            height: 100% !important;
            z-index: 2 !important;
            display: block !important;
            font-size: 0 !important;
            line-height: 0 !important;
            color: transparent !important;
            text-decoration: none !important;
            cursor: pointer !important;
        }
        body .elementor-menu-cart__product-remove a:not(:first-child) {
            display: none !important;
        }
        body .elementor-menu-cart__product-remove svg,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove svg {
            display: none !important;
        }
        body .elementor-menu-cart__product-remove::before,
        body .elementor-menu-cart__product-remove::after,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove::before,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove::after {
            content: "" !important;
            position: absolute !important;
            top: 50% !important;
            left: 50% !important;
            width: 14px !important;
            height: 2px !important;
            background: #0b2d4a !important;
            opacity: 1 !important;
            display: block !important;
            pointer-events: none !important;
            border-radius: 1px !important;
            z-index: 1 !important;
        }
        body .elementor-menu-cart__product-remove::before,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove::before {
            transform: translate(-50%, -50%) rotate(45deg) !important;
        }
        body .elementor-menu-cart__product-remove::after,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item a.remove::after {
            transform: translate(-50%, -50%) rotate(-45deg) !important;
        }

        /* 4. Product Container & Top Row */
        body .elementor-menu-cart__product,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item,
        body .widget_shopping_cart_content .woocommerce-mini-cart-item {
            display: grid !important;
            grid-template-columns: 96px minmax(0, 1fr) !important;
            grid-template-rows: auto auto auto auto !important;
            column-gap: 12px !important;
            row-gap: 0 !important;
            padding: 12px 12px 14px 12px !important;
            margin: 0 0 12px !important;
            border: 1px solid #e5edf5 !important;
            border-radius: 0 !important;
            background: #ffffff !important;
            box-shadow: none !important;
            position: relative !important;
            align-items: start !important;
            width: 100% !important;
            box-sizing: border-box !important;
        }
        body .elementor-menu-cart__product:last-child,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item:last-child {
            margin-bottom: 0 !important;
        }
        body .elementor-menu-cart__product-image,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item > a:not(.remove):has(img) {
            grid-column: 1 !important;
            grid-row: 1 / span 2 !important;
            width: 96px !important;
            min-width: 96px !important;
            max-width: 96px !important;
            height: 96px !important;
            aspect-ratio: 1 / 1 !important;
            align-self: start !important;
            flex-shrink: 0 !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        body .elementor-menu-cart__product-image img,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item img {
            width: 96px !important;
            height: 96px !important;
            object-fit: cover !important;
            aspect-ratio: 1 / 1 !important;
            border-radius: 0 !important;
            border: 0 !important;
            display: block !important;
        }
        body .elementor-menu-cart__product-name,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item > a:not(.remove) {
            grid-column: 2 !important;
            grid-row: 1 !important;
            min-width: 0 !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            line-height: 1.35 !important;
            color: #0b2d4a !important;
            padding-right: 40px !important;
            padding-left: 0 !important;
            margin: 0 0 8px 0 !important;
            align-self: start !important;
            text-align: left !important;
            text-decoration: none !important;
            word-break: break-word !important;
        }

        /* 5. Quantity Control */
        body .juhani-mini-cart-qty {
            grid-column: 2 !important;
            grid-row: 2 !important;
            display: inline-flex !important;
            align-items: stretch !important;
            height: 40px !important;
            width: auto !important;
            margin: 0 !important;
            align-self: start !important;
            justify-self: start !important;
            border: 1px solid #d7e0e8 !important;
            background: #ffffff !important;
            overflow: hidden !important;
            box-sizing: border-box !important;
        }
        body .juhani-mini-cart-qty button {
            width: 40px !important;
            height: 38px !important;
            min-width: 40px !important;
            display: flex !important;
            justify-content: center !important;
            align-items: center !important;
            font-family: inherit !important;
            font-size: 19px !important;
            font-weight: 700 !important;
            color: #064b7e !important;
            background: #ffffff !important;
            border: none !important;
            border-right: 1px solid #d7e0e8 !important;
            line-height: 1 !important;
            cursor: pointer !important;
            padding: 0 !important;
            margin: 0 !important;
            transition: background-color 0.16s ease !important;
        }
        body .juhani-mini-cart-qty button:last-child {
            border-right: none !important;
            border-left: 1px solid #d7e0e8 !important;
        }
        body .juhani-mini-cart-qty button:hover {
            background: #eaf3fa !important;
            color: #002f52 !important;
        }
        body .juhani-mini-cart-qty button::before,
        body .juhani-mini-cart-qty button::after,
        body .juhani-mini-cart-qty .juhani-mini-cart-minus::before,
        body .juhani-mini-cart-qty .juhani-mini-cart-minus::after,
        body .juhani-mini-cart-qty .juhani-mini-cart-plus::before,
        body .juhani-mini-cart-qty .juhani-mini-cart-plus::after,
        body .juhani-mini-cart-qty button:first-child::before,
        body .juhani-mini-cart-qty button:first-child::after,
        body .juhani-mini-cart-qty button:last-child::before,
        body .juhani-mini-cart-qty button:last-child::after {
            content: none !important;
            display: none !important;
        }
        body .juhani-mini-cart-qty span {
            width: 46px !important;
            height: 38px !important;
            min-width: 46px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            text-align: center !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 15px !important;
            font-weight: 700 !important;
            color: #061d33 !important;
            background: #ffffff !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        /* 6. Attribute Box */
        body .elementor-menu-cart__product .variation,
        body .woocommerce-mini-cart .variation {
            grid-column: 1 / -1 !important;
            grid-row: 3 !important;
            display: grid !important;
            grid-template-columns: minmax(110px, 40%) 1fr !important;
            gap: 10px !important;
            width: 100% !important;
            max-width: 100% !important;
            box-sizing: border-box !important;
            margin: 12px 0 0 0 !important;
            padding: 12px 14px !important;
            background: #f4f7fa !important;
            border: 1px solid #e5ebf2 !important;
        }
        body .elementor-menu-cart__product .variation dt,
        body .woocommerce-mini-cart .variation dt {
            color: #344b63 !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 13px !important;
            font-weight: 500 !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            line-height: 1.4 !important;
            padding: 5px 0 !important;
            border-bottom: 1px solid #e6edf3 !important;
            display: flex !important;
            align-items: center !important;
            margin: 0 !important;
        }
        body .elementor-menu-cart__product .variation dd,
        body .woocommerce-mini-cart .variation dd {
            color: #0f2238 !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 13px !important;
            font-weight: 600 !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
            line-height: 1.4 !important;
            padding: 5px 0 !important;
            border-bottom: 1px solid #e6edf3 !important;
            display: flex !important;
            align-items: center !important;
            margin: 0 !important;
        }
        body .elementor-menu-cart__product .variation dd p,
        body .woocommerce-mini-cart .variation dd p {
            margin: 0 !important;
        }
        body .elementor-menu-cart__product .variation dt:nth-last-of-type(1),
        body .woocommerce-mini-cart .variation dt:nth-last-of-type(1),
        body .elementor-menu-cart__product .variation dd:last-child,
        body .woocommerce-mini-cart .variation dd:last-child {
            border-bottom: none !important;
        }

        /* 7. Price Below Attributes: Single clean price */
        body .juhani-mini-cart-qty-source,
        body .elementor-menu-cart__product-price,
        body .woocommerce-mini-cart .woocommerce-mini-cart-item .quantity,
        body .juhani-mini-cart-line-total,
        body .elementor-menu-cart__product-subtotal {
            display: none !important;
        }
        body .juhani-mobile-price {
            display: block !important;
            grid-column: 1 / -1 !important;
            grid-row: 4 !important;
            width: 100% !important;
            margin-top: 10px !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 17px !important;
            font-weight: 700 !important;
            line-height: 1.3 !important;
            color: #064b7e !important;
            text-align: left !important;
            padding: 0 !important;
        }
        body .juhani-mobile-price .amount {
            font-size: 17px !important;
            font-weight: 700 !important;
            color: #064b7e !important;
        }

        /* 8. Cart Body Spacing */
        body .elementor-menu-cart__products,
        body .widget_shopping_cart_content .woocommerce-mini-cart {
            flex: 1 1 auto !important;
            min-height: 0 !important;
            max-height: none !important;
            height: auto !important;
            overflow-y: auto !important;
            overflow-x: hidden !important;
            padding: 12px 12px !important;
            -webkit-overflow-scrolling: touch !important;
            background: #ffffff !important;
        }

        /* 9. Subtotal + Buttons */
        body .elementor-menu-cart__subtotal {
            flex: 0 0 auto !important;
            margin: 0 !important;
            padding: 12px 12px 8px 12px !important;
            background: #ffffff !important;
            border-top: 1px solid #eef1f5 !important;
            border-bottom: none !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
            font-family: "Poppins", sans-serif !important;
        }
        body .elementor-menu-cart__subtotal strong {
            font-size: 16px !important;
            font-weight: 700 !important;
            color: #0f2238 !important;
            margin: 0 !important;
        }
        body .elementor-menu-cart__subtotal .amount {
            font-size: 18px !important;
            font-weight: 800 !important;
            color: #0f2238 !important;
        }
        body .elementor-menu-cart__footer-buttons {
            flex: 0 0 auto !important;
            width: 100% !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 10px 12px calc(24px + env(safe-area-inset-bottom, 0px)) 12px !important;
            background: #ffffff !important;
            gap: 12px !important;
            border-top: none !important;
            box-shadow: none !important;
            display: flex !important;
            flex-direction: column !important;
            box-sizing: border-box !important;
            overflow: visible !important;
        }
        body .elementor-menu-cart__products,
        body .elementor-menu-cart__product {
            max-width: 100% !important;
            box-sizing: border-box !important;
            overflow-x: hidden !important;
        }
        body .elementor-menu-cart__footer-buttons a,
        body .elementor-menu-cart__footer-buttons .elementor-button,
        body .woocommerce-mini-cart__buttons a {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            min-height: 48px !important;
            height: 48px !important;
            max-height: 48px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 14px !important;
            font-weight: 800 !important;
            letter-spacing: 0.3px !important;
            text-transform: uppercase !important;
            border-radius: 0 !important;
            padding: 0 16px !important;
            margin: 0 !important;
            box-sizing: border-box !important;
            text-decoration: none !important;
            overflow: hidden !important;
            transition: filter 0.18s ease, background-color 0.18s ease !important;
        }
        body .elementor-menu-cart__footer-buttons a:hover,
        body .elementor-menu-cart__footer-buttons .elementor-button:hover,
        body .woocommerce-mini-cart__buttons a:hover {
            filter: brightness(0.96) !important;
        }
        body .elementor-menu-cart__footer-buttons a:first-child,
        body .elementor-menu-cart__footer-buttons .elementor-button--view-cart,
        body .woocommerce-mini-cart__buttons a:first-child {
            background: #ffffff !important;
            border: 2px solid #003b68 !important;
            color: #0b2d4a !important;
        }
        body .elementor-menu-cart__footer-buttons a:first-child *,
        body .elementor-menu-cart__footer-buttons .elementor-button--view-cart * {
            color: #0b2d4a !important;
        }
        body .elementor-menu-cart__footer-buttons a:last-child,
        body .elementor-menu-cart__footer-buttons .elementor-button--checkout,
        body .woocommerce-mini-cart__buttons a:last-child {
            background: #003f6d !important;
            border: 2px solid #003f6d !important;
            color: #ffffff !important;
        }
        body .elementor-menu-cart__footer-buttons a:last-child *,
        body .elementor-menu-cart__footer-buttons .elementor-button--checkout * {
            color: #ffffff !important;
        }
        body .elementor-menu-cart__main {
            gap: 0 !important;
        }
    }

    /* Kill any pseudo backdrop on toggle button to eliminate stacking context conflicts / jitter */
    .elementor-menu-toggle::before,
    .elementor-menu-toggle::after,
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle::before,
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle::after {
        display: none !important;
        content: none !important;
        pointer-events: none !important;
        opacity: 0 !important;
    }

    /* ====================================================
       MODERN MENU TOGGLE BUTTON (NO CROSS ICON, MODERN BURGER)
       ==================================================== */
    @media (max-width: 1024px) {
        /* Strict zero border radius */
        .elementor-menu-toggle,
        .elementor-nav-menu--dropdown,
        .elementor-nav-menu--dropdown ul,
        .elementor-nav-menu--dropdown li,
        .elementor-nav-menu--dropdown a,
        .juhani-drawer-header,
        .juhani-drawer-close,
        .juhani-drawer-footer {
            border-radius: 0px !important;
        }

        /* Modern Hamburger Toggle Button */
        .elementor-menu-toggle,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle {
            margin: 0 !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            background-color: #4CA2D9 !important;
            color: #FFFFFF !important;
            width: 42px !important;
            height: 42px !important;
            min-width: 42px !important;
            min-height: 42px !important;
            padding: 0 !important;
            border: none !important;
            border-radius: 0px !important;
            cursor: pointer !important;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.15) !important;
            transition: background-color 0.25s ease, transform 0.2s ease !important;
            line-height: 1 !important;
            z-index: 999999 !important;
            position: relative !important;
            overflow: hidden !important;
        }

        .elementor-menu-toggle:hover,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle:hover {
            background-color: #10395E !important;
            transform: scale(1.03) !important;
        }

        .elementor-menu-toggle.elementor-active,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle.elementor-active {
            background-color: #10395E !important;
            box-shadow: 0 4px 14px rgba(16, 57, 94, 0.35) !important;
        }

        /* Hide header toggle button when drawer is open so it doesn't float over the drawer */
        body.juhani-drawer-open .elementor-menu-toggle,
        body.juhani-drawer-open .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle {
            opacity: 0 !important;
            pointer-events: none !important;
            visibility: hidden !important;
            transition: opacity 0.2s ease, visibility 0.2s ease !important;
        }

        /* Clean fade for floating header bar and logo when drawer is open so left overlay is a clean backdrop */
        body.juhani-drawer-open .elementor-71 .elementor-element.elementor-element-1d9f82b,
        body.juhani-drawer-open [data-id="1d9f82b"] {
            background: transparent !important;
            box-shadow: none !important;
            pointer-events: none !important;
        }
        body.juhani-drawer-open .elementor-71 .elementor-element.elementor-element-1d9f82b::before,
        body.juhani-drawer-open .elementor-71 .elementor-element.elementor-element-1d9f82b::after,
        body.juhani-drawer-open [data-id="1d9f82b"]::before,
        body.juhani-drawer-open [data-id="1d9f82b"]::after {
            opacity: 0 !important;
            visibility: hidden !important;
        }
        body.juhani-drawer-open .elementor-71 .elementor-element.elementor-element-d1d4298,
        body.juhani-drawer-open [data-id="d1d4298"] {
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            transition: opacity 0.2s ease, visibility 0.2s ease !important;
        }

        /* HIDE CROSS / CLOSE SVG COMPLETELY FROM THE TOGGLE BUTTON */
        .elementor-menu-toggle__icon--close,
        .elementor-menu-toggle svg.elementor-menu-toggle__icon--close,
        .elementor-menu-toggle svg.e-eicon-close,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle svg {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            opacity: 0 !important;
            pointer-events: none !important;
        }

        /* Hide old linear/font-icon once modern burger is injected */
        .elementor-menu-toggle.has-modern-burger .lnr,
        .elementor-menu-toggle.has-modern-burger .elementor-menu-toggle__icon--open {
            display: none !important;
        }

        /* Modern 3-bar Hamburger Icon inside Toggle */
        .juhani-modern-hamburger {
            width: 20px !important;
            height: 14px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            align-items: center !important;
            pointer-events: none !important;
        }
        .juhani-modern-hamburger span {
            display: block !important;
            height: 2px !important;
            background-color: #ffffff !important;
            border-radius: 0px !important;
            transition: all 0.22s ease !important;
        }
        .juhani-modern-hamburger span:nth-child(1) { width: 100% !important; }
        .juhani-modern-hamburger span:nth-child(2) { width: 75% !important; align-self: flex-start !important; }
        .juhani-modern-hamburger span:nth-child(3) { width: 100% !important; }

        .elementor-menu-toggle:hover .juhani-modern-hamburger span:nth-child(2) {
            width: 100% !important;
        }

        /* ====================================================
           OFF-CANVAS DRAWER CONTAINER (WHITE BG, RIGHT SIDE)
           ==================================================== */
        .elementor-widget-nav-menu .elementor-nav-menu--dropdown.elementor-nav-menu__container,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown.elementor-nav-menu__container {
            position: fixed !important;
            top: 0 !important;
            bottom: 0 !important;
            right: 0 !important;
            left: auto !important;
            width: 310px !important;
            max-width: 85vw !important;
            height: 100vh !important;
            height: 100dvh !important;
            max-height: 100vh !important;
            margin: 0 !important;
            padding: 0 !important;
            box-sizing: border-box !important;
            background: #ffffff !important;
            box-shadow: -10px 0 35px rgba(0, 0, 0, 0.15) !important;
            border-left: 1px solid #eef1f5 !important;
            border-top: none !important;
            border-right: none !important;
            border-bottom: none !important;
            border-radius: 0px !important;
            display: flex !important;
            flex-direction: column !important;
            overflow-x: hidden !important;
            overflow-y: auto !important;
            -webkit-overflow-scrolling: touch !important;
            z-index: 999995 !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            filter: none !important;
            pointer-events: auto !important;
            animation: none !important;
            transition: transform 0.38s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease, visibility 0.38s ease !important;
        }

        /* Closed State: Slid out to the right */
        .elementor-widget-nav-menu .elementor-menu-toggle:not(.elementor-active) + .elementor-nav-menu__container,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle:not(.elementor-active) + .elementor-nav-menu__container {
            transform: translate3d(100%, 0, 0) !important;
            opacity: 0 !important;
            visibility: hidden !important;
            pointer-events: none !important;
            max-height: 100vh !important;
        }

        /* Open State: Slid in smoothly */
        .elementor-widget-nav-menu .elementor-menu-toggle.elementor-active + .elementor-nav-menu__container,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-menu-toggle.elementor-active + .elementor-nav-menu__container {
            transform: translate3d(0, 0, 0) !important;
            opacity: 1 !important;
            visibility: visible !important;
            pointer-events: auto !important;
            max-height: 100vh !important;
            animation: none !important;
        }

        /* ====================================================
           DRAWER HEADER (LOGO + CLOSE BUTTON)
           ==================================================== */
        .juhani-drawer-header {
            display: flex !important;
            align-items: center !important;
            justify-content: space-between !important;
            padding: 18px 20px !important;
            border-bottom: 1px solid #eef1f5 !important;
            background: #ffffff !important;
            width: 100% !important;
            box-sizing: border-box !important;
            flex-shrink: 0 !important;
        }
        .juhani-drawer-logo-wrap {
            display: inline-flex !important;
            align-items: center !important;
            text-decoration: none !important;
            max-width: 200px !important;
        }
        .juhani-drawer-logo {
            max-height: 42px !important;
            max-width: 185px !important;
            width: auto !important;
            height: auto !important;
            display: block !important;
            object-fit: contain !important;
        }
        .juhani-drawer-close {
            background: #f1f5f9 !important;
            border: none !important;
            border-radius: 0px !important;
            width: 38px !important;
            height: 38px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #334155 !important;
            cursor: pointer !important;
            transition: background-color 0.2s ease, color 0.2s ease !important;
            padding: 0 !important;
            flex-shrink: 0 !important;
        }
        .juhani-drawer-close:hover {
            background-color: #4CA2D9 !important;
            color: #ffffff !important;
        }

        /* ====================================================
           DRAWER MENU ITEMS (ZERO JITTER / KAPAKAPI FIX)
           ==================================================== */
        .elementor-nav-menu--dropdown ul.elementor-nav-menu,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown ul.elementor-nav-menu {
            width: 100% !important;
            margin: 0 !important;
            padding: 10px 0 16px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 0px !important;
            background: #ffffff !important;
            box-sizing: border-box !important;
            flex-grow: 1 !important;
        }

        .elementor-nav-menu--dropdown li,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown li {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: transparent !important;
            border-radius: 0px !important;
            animation: none !important; /* Disables cascade transform that causes hover jitter */
        }

        /* Clean Stable Menu Links (Anti-Jitter / Kapakapi Fix) */
        .elementor-nav-menu--dropdown a,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a {
            color: #1e293b !important;
            font-family: "Poppins", sans-serif !important;
            font-size: 14.5px !important;
            font-weight: 600 !important;
            letter-spacing: 0.4px !important;
            text-transform: uppercase !important;
            padding: 14px 22px !important;
            border-radius: 0px !important;
            border-bottom: 1px solid #f2f4f7 !important;
            border-left: 3px solid transparent !important;
            border-top: none !important;
            border-right: none !important;
            display: flex !important;
            align-items: center !important;
            justify-content: flex-start !important;
            transition: background-color 0.16s ease, color 0.16s ease, border-left-color 0.16s ease !important;
            background: transparent !important;
            box-sizing: border-box !important;
            transform: none !important;
            pointer-events: auto !important;
            cursor: pointer !important;
        }

        /* Disable glitchy Elementor pointer lines */
        .elementor-nav-menu--dropdown a::before,
        .elementor-nav-menu--dropdown a::after,
        .elementor-nav-menu--dropdown li::before,
        .elementor-nav-menu--dropdown li::after,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a::before,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a::after {
            display: none !important;
            content: none !important;
            pointer-events: none !important;
        }

        .elementor-nav-menu--dropdown li:last-child a,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown li:last-child a {
            border-bottom: none !important;
        }

        /* Stable Hover and Active States - EXACT SAME PADDING (14px 22px), ZERO JITTER */
        .elementor-nav-menu--dropdown a:hover,
        .elementor-nav-menu--dropdown a:focus,
        .elementor-nav-menu--dropdown a.elementor-item-active,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a:hover,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a:focus,
        .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--dropdown a.elementor-item-active {
            color: #4CA2D9 !important;
            background-color: #f2f9fd !important;
            border-left: 3px solid #4CA2D9 !important;
            padding: 14px 22px !important;
            transform: none !important;
        }

        /* Drawer Bottom / Footer */
        .juhani-drawer-footer {
            padding: 16px 22px !important;
            border-top: 1px solid #eef1f5 !important;
            background: #fafbfc !important;
            margin-top: auto !important;
            box-sizing: border-box !important;
            width: 100% !important;
            flex-shrink: 0 !important;
        }
        .juhani-drawer-tagline {
            font-family: "Poppins", sans-serif !important;
            font-size: 12px !important;
            color: #8c98a4 !important;
            font-weight: 500 !important;
            letter-spacing: 0.3px !important;
            display: block !important;
        }
    }

    /* Hide "Product has been added to your cart" message banner on Checkout page */
    .woocommerce-checkout .woocommerce-message,
    .woocommerce-checkout .woocommerce-notices-wrapper .woocommerce-message,
    body.woocommerce-checkout .woocommerce-message,
    form.checkout .woocommerce-message,
    .elementor-widget-woocommerce-checkout-page .woocommerce-message {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
        overflow: hidden !important;
    }
    </style>
    <?php
}, 100);

add_action('wp_footer', function() {
    $cart_url = function_exists('wc_get_cart_url') ? wc_get_cart_url() : '/cart/';
    $checkout_url = function_exists('wc_get_checkout_url') ? wc_get_checkout_url() : '/checkout/';
    ?>
    <script id="juhani-cart-fix-js">
    (function() {
        function initJuhaniCartFix() {
            const $ = window.jQuery;
            if (!$) {
                console.warn('jQuery not found for cart fix');
                return;
            }

            // Wait for elementor frontend
            const tryInit = () => {
                const cartWidgets = document.querySelectorAll('.elementor-widget-woocommerce-menu-cart');
                const toggleBtns = document.querySelectorAll('#elementor-menu-cart__toggle_button, .elementor-menu-cart__toggle_button, .elementor-menu-cart__toggle');

                if (!cartWidgets.length || !toggleBtns.length) {
                    setTimeout(tryInit, 400);
                    return;
                }

                console.log('[Juhani Cart Fix] Found cart widgets (' + cartWidgets.length + '), initializing multi-instance fixes');

                const closeSvg = '<svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                const removeSvg = '<svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>';
                const cartSvg = '<svg class="juhani-cart-svg" viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="#10395E" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1.5"></circle><circle cx="20" cy="21" r="1.5"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';

                // Centralized close for cart drawer - single source, does NOT touch mobile menu state
                function closeJuhaniCartDrawer() {
                    document.querySelectorAll('.elementor-widget-woocommerce-menu-cart.elementor-menu-cart--shown').forEach(w => w.classList.remove('elementor-menu-cart--shown'));
                    document.querySelectorAll('.elementor-menu-cart__container').forEach(c => {
                        c.setAttribute('aria-hidden', 'true');
                        c.style.opacity = '';
                        c.style.visibility = '';
                        c.style.pointerEvents = '';
                        c.style.backdropFilter = '';
                    });
                    document.querySelectorAll('.elementor-menu-cart__main').forEach(m => {
                        m.setAttribute('aria-hidden', 'true');
                        m.style.transform = '';
                        m.style.opacity = '';
                        m.style.visibility = '';
                    });
                    document.querySelectorAll('#elementor-menu-cart__toggle_button, .elementor-menu-cart__toggle_button, .elementor-menu-cart__toggle').forEach(t => t.setAttribute('aria-expanded', 'false'));
                    document.body.style.overflow = '';
                    document.body.style.pointerEvents = '';
                    document.body.classList.remove('juhani-cart-open');
                    document.body.style.removeProperty('overflow');
                    document.body.style.removeProperty('pointer-events');
                }
                window.closeJuhaniCartDrawer = closeJuhaniCartDrawer;

                // Enhance all mini-cart drawers across DOM (both desktop header & mobile footer widgets)
                const enhanceMiniCartDrawer = () => {
                    const drawers = document.querySelectorAll('.elementor-menu-cart__main');
                    if (!drawers.length) return;

                    drawers.forEach(drawer => {
                        // 1. Inject or update custom header with title, count and clean close button
                        let customHeader = drawer.querySelector('.juhani-mini-cart-header');
                        if (!customHeader) {
                            customHeader = document.createElement('div');
                            customHeader.className = 'juhani-mini-cart-header';
                            customHeader.innerHTML = '<span class="juhani-mini-cart-title">YOUR CART</span><button type="button" class="juhani-mini-cart-close" aria-label="Close cart">' + closeSvg + '</button>';
                            drawer.insertBefore(customHeader, drawer.firstChild);
                        } else {
                            const closeBtn = customHeader.querySelector('.juhani-mini-cart-close');
                            if (closeBtn && !closeBtn.querySelector('svg')) {
                                closeBtn.innerHTML = closeSvg;
                            }
                        }

                        // Calculate items count
                        const items = drawer.querySelectorAll('.elementor-menu-cart__product, .woocommerce-mini-cart-item');
                        const itemCount = items.length;

                        const title = drawer.querySelector('.juhani-mini-cart-title');
                        if (title) {
                            title.innerHTML = 'YOUR CART' + (itemCount ? ' <span class="juhani-mini-cart-count">(' + itemCount + ')</span>' : '');
                        }
                        const heading = drawer.querySelector('.elementor-menu-cart__heading');
                        if (heading) {
                            heading.innerHTML = 'Your Cart' + (itemCount ? ' <span class="juhani-mini-cart-count">(' + itemCount + ')</span>' : '');
                        }

                        // Remove redundant total notes
                        drawer.querySelectorAll('.juhani-mini-cart-tax-note').forEach(note => note.remove());
                        drawer.querySelectorAll('.woocommerce-mini-cart__total, p.total').forEach(total => {
                            if (!total.classList.contains('elementor-menu-cart__subtotal')) {
                                total.style.display = 'none';
                            }
                        });

                        // Footer buttons text
                        const footerButtons = drawer.querySelector('.elementor-menu-cart__footer-buttons');
                        if (footerButtons) {
                            const buttons = footerButtons.querySelectorAll('a, .elementor-button');
                            if (buttons[0]) buttons[0].textContent = 'VIEW CART';
                            if (buttons[1]) buttons[1].textContent = 'PROCEED TO CHECKOUT';
                        }

                        // 2. Enhance each product item
                        items.forEach(item => {
                            if (!item.dataset.juhaniMiniCartEnhanced) {
                                item.dataset.juhaniMiniCartEnhanced = '1';
                            }

                            // Ensure remove button has clean SVG without breaking inner <a> attributes
                            const removeLinks = item.querySelectorAll('.elementor_remove_from_cart_button, .remove_from_cart_button, a.remove, a[data-cart_item_key]');
                            removeLinks.forEach(link => {
                                const key = link.dataset.cart_item_key || link.getAttribute('data-cart_item_key');
                                if (key) item.dataset.cartItemKey = key;
                                if (!link.querySelector('svg')) {
                                    link.innerHTML = removeSvg;
                                }
                            });
                            if (!item.dataset.cartItemKey) {
                                const anyLink = item.querySelector('a[href*="remove_item="]');
                                if (anyLink) {
                                    const m = anyLink.href.match(/remove_item=([a-f0-9]+)/i);
                                    if (m) item.dataset.cartItemKey = m[1];
                                }
                            }

                            // Keep variation list inside item container
                            const variation = item.querySelector('.variation');
                            if (variation && variation.parentElement !== item) {
                                item.appendChild(variation);
                            }

                            // Clean up variation attribute labels
                            item.querySelectorAll('.variation dt').forEach(label => {
                                label.textContent = label.textContent
                                    .replace(/:/g, '')
                                    .replace(/^pa[-_\s]*/i, '')
                                    .replace(/[-_]+/g, ' ')
                                    .trim();
                            });

                            // Extract quantity and unit price
                            const qtyEl = item.querySelector('.quantity:not(.juhani-mini-cart-qty-source), .elementor-menu-cart__product-price');
                            if (qtyEl && !item.querySelector('.juhani-mini-cart-qty')) {
                                const qtyText = qtyEl.textContent.replace(/\s+/g, ' ').trim();
                                const qtyMatch = qtyText.match(/(?:\u00d7|x)?\s*(\d+)/i);
                                const priceMatch = qtyText.match(/(?:\u00d7|x)\s*\d+\s*(.*)$/i);
                                const qty = qtyMatch ? qtyMatch[1] : '1';
                                const unitPrice = priceMatch && priceMatch[1] ? priceMatch[1].trim() : '';
                                const amount = qtyEl.querySelector('.amount') || item.querySelector('.amount');
                                const amountHtml = amount ? amount.outerHTML : unitPrice;

                                qtyEl.classList.add('juhani-mini-cart-qty-source');
                                if (unitPrice) {
                                    qtyEl.innerHTML = amountHtml;
                                }

                                // Stepper: single clean minus, number, plus
                                const qtyControl = document.createElement('div');
                                qtyControl.className = 'juhani-mini-cart-qty';
                                qtyControl.setAttribute('aria-label', 'Quantity');
                                qtyControl.innerHTML = '<button type="button" class="juhani-mini-cart-minus" aria-label="Decrease quantity" tabindex="-1">&#8722;</button><span>' + qty + '</span><button type="button" class="juhani-mini-cart-plus" aria-label="Increase quantity" tabindex="-1">&#43;</button>';
                                qtyEl.insertAdjacentElement('afterend', qtyControl);

                                // Single clean price below attributes
                                let mobilePrice = item.querySelector('.juhani-mobile-price');
                                if (!mobilePrice) {
                                    mobilePrice = document.createElement('div');
                                    mobilePrice.className = 'juhani-mobile-price';
                                    const varEl = item.querySelector('.variation');
                                    if (varEl) varEl.insertAdjacentElement('afterend', mobilePrice);
                                    else qtyControl.insertAdjacentElement('afterend', mobilePrice);
                                }
                                mobilePrice.innerHTML = qty + ' &times; ' + amountHtml;

                                item.querySelectorAll('.juhani-mini-cart-line-total, .elementor-menu-cart__product-subtotal').forEach(total => total.remove());
                            }

                            // Update mobile price if qty span changed
                            const existingQtyControl = item.querySelector('.juhani-mini-cart-qty span');
                            const existingMobilePrice = item.querySelector('.juhani-mobile-price');
                            if (existingQtyControl && existingMobilePrice) {
                                const qtyElUpdate = item.querySelector('.juhani-mini-cart-qty-source');
                                const amt = qtyElUpdate ? qtyElUpdate.querySelector('.amount') : item.querySelector('.amount');
                                const amtHtml = amt ? amt.outerHTML : '';
                                if (amtHtml) {
                                    existingMobilePrice.innerHTML = existingQtyControl.textContent.trim() + ' &times; ' + amtHtml;
                                }
                            }
                        });
                    });
                };

                // Ensure cart icon visible: prefer font, inject SVG fallback only if font missing
                const injectFooterCartIcon = () => {
                    const btns = document.querySelectorAll('.elementor-element-77a58cb .elementor-button-icon');
                    btns.forEach(icon => {
                        if (!icon.querySelector('svg.juhani-cart-svg')) {
                            const qty = icon.querySelector('.elementor-button-icon-qty');
                            const holder = document.createElement('span');
                            holder.innerHTML = cartSvg;
                            const svgEl = holder.firstChild;
                            if (qty) icon.insertBefore(svgEl, qty);
                            else icon.appendChild(svgEl);
                        }
                        const eicon = icon.querySelector('i.eicon, i[class*="fa-"], i');
                        let fontOk = false;
                        if (eicon) {
                            const cs = window.getComputedStyle(eicon, '::before');
                            const c = cs && cs.getPropertyValue('content');
                            fontOk = c && c !== 'none' && c !== '""' && c !== "''" && c.length > 2;
                        }
                        if (fontOk) {
                            icon.classList.remove('juhani-no-font');
                        } else {
                            icon.classList.add('juhani-no-font');
                        }
                    });
                };

                window.enhanceMiniCartDrawer = enhanceMiniCartDrawer;
                window.injectFooterCartIcon = injectFooterCartIcon;
                injectFooterCartIcon();
                enhanceMiniCartDrawer();

                // Listen for WooCommerce cart events to re-enhance mini cart
                if (window.jQuery) {
                    jQuery(document.body).off('wc_fragments_refreshed.juhaniMiniCart added_to_cart.juhaniMiniCart removed_from_cart.juhaniMiniCart');
                    jQuery(document.body).on('wc_fragments_refreshed.juhaniMiniCart added_to_cart.juhaniMiniCart removed_from_cart.juhaniMiniCart', function() {
                        setTimeout(() => { injectFooterCartIcon(); enhanceMiniCartDrawer(); }, 60);
                    });
                }

                // MutationObserver on all cart widgets to keep body.juhani-cart-open in sync
                const cartObserver = new MutationObserver(() => {
                    let hasShown = false;
                    document.querySelectorAll('.elementor-widget-woocommerce-menu-cart').forEach(w => {
                        if (w.classList.contains('elementor-menu-cart--shown')) hasShown = true;
                    });
                    if (hasShown) {
                        if (!document.body.classList.contains('juhani-cart-open')) {
                            document.body.classList.add('juhani-cart-open');
                            document.body.style.overflow = 'hidden';
                        }
                        enhanceMiniCartDrawer();
                    } else if (!hasShown && document.body.classList.contains('juhani-cart-open')) {
                        document.body.classList.remove('juhani-cart-open');
                        document.body.style.removeProperty('overflow');
                    }
                });
                document.querySelectorAll('.elementor-widget-woocommerce-menu-cart').forEach(w => {
                    cartObserver.observe(w, { attributes: true, attributeFilter: ['class'] });
                });

                // ===== MINI-CART QUANTITY STEPPER (AJAX) - delegated =====
                if (!document.body.dataset.juhaniQtyBound) {
                    document.body.dataset.juhaniQtyBound = '1';
                    document.addEventListener('click', function(e) {
                        const btn = e.target.closest('.juhani-mini-cart-minus, .juhani-mini-cart-plus');
                        if (!btn) return;
                        const item = btn.closest('.elementor-menu-cart__product, .woocommerce-mini-cart-item');
                        if (!item) return;
                        const drawer = item.closest('.elementor-menu-cart__main');
                        if (!drawer) return;
                        let cartItemKey = item.dataset.cartItemKey;
                        if (!cartItemKey) {
                            const removeLink = item.querySelector('a[data-cart_item_key], a.remove, a.elementor_remove_from_cart_button, a[href*="remove_item="]');
                            if (removeLink) {
                                cartItemKey = removeLink.dataset.cart_item_key || removeLink.getAttribute('data-cart_item_key');
                                if (!cartItemKey && removeLink.href) {
                                    const m = removeLink.href.match(/remove_item=([a-f0-9]+)/i);
                                    if (m) cartItemKey = m[1];
                                }
                            }
                        }
                        if (!cartItemKey) {
                            console.warn('[Juhani Cart] cart_item_key not found for qty update');
                            return;
                        }
                        const qtyWrap = btn.closest('.juhani-mini-cart-qty');
                        const qtySpan = qtyWrap ? qtyWrap.querySelector('span') : null;
                        if (!qtySpan) return;
                        let currentQty = parseInt(qtySpan.textContent, 10) || 1;
                        let newQty = currentQty;
                        if (btn.classList.contains('juhani-mini-cart-minus')) {
                            newQty = Math.max(1, currentQty - 1);
                        } else {
                            newQty = currentQty + 1;
                        }
                        if (newQty === currentQty) return;
                        e.preventDefault();
                        e.stopPropagation();
                        if (btn.disabled) return;
                        const originalText = qtySpan.textContent;
                        qtySpan.textContent = newQty;
                        qtySpan.style.opacity = '0.5';
                        item.querySelectorAll('.juhani-mini-cart-minus, .juhani-mini-cart-plus').forEach(function(b){
                            b.style.pointerEvents = 'none';
                            b.disabled = true;
                        });
                        if (window.jQuery) {
                            jQuery.ajax({
                                url: '<?php echo esc_js(admin_url('admin-ajax.php')); ?>',
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    action: 'juhani_update_cart_qty',
                                    cart_item_key: cartItemKey,
                                    quantity: newQty,
                                    nonce: '<?php echo esc_js(wp_create_nonce('juhani_cart_nonce')); ?>'
                                },
                                success: function(resp) {
                                    if (resp && resp.success) {
                                        jQuery(document.body).trigger('wc_fragment_refresh');
                                        jQuery(document.body).trigger('updated_wc_div');
                                        qtySpan.textContent = newQty;
                                    } else {
                                        qtySpan.textContent = originalText;
                                        console.warn('[Juhani Cart] qty update failed', resp);
                                    }
                                },
                                error: function(xhr) {
                                    qtySpan.textContent = originalText;
                                    console.warn('[Juhani Cart] ajax error', xhr);
                                },
                                complete: function() {
                                    qtySpan.style.opacity = '';
                                    item.querySelectorAll('.juhani-mini-cart-minus, .juhani-mini-cart-plus').forEach(function(b){
                                        b.style.pointerEvents = '';
                                        b.disabled = false;
                                    });
                                }
                            });
                        }
                    });
                }

                // Delegated Close button click (capture phase for immediate responsive reaction)
                document.addEventListener('click', function(e) {
                    const closeBtn = e.target.closest('.juhani-mini-cart-close, .elementor-menu-cart__close-button, .elementor-menu-cart__close-button-custom');
                    if (closeBtn) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeJuhaniCartDrawer();
                    }
                }, true);

                // Overlay click: clicking outside the drawer (.elementor-menu-cart__container)
                document.addEventListener('click', function(e) {
                    const target = e.target;
                    if (target && target.classList && target.classList.contains('elementor-menu-cart__container')) {
                        e.preventDefault();
                        e.stopPropagation();
                        closeJuhaniCartDrawer();
                        return;
                    }
                }, true);

                // ESC key closes cart drawer
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape' || e.keyCode === 27) {
                        if (document.body.classList.contains('juhani-cart-open') || document.querySelector('.elementor-widget-woocommerce-menu-cart.elementor-menu-cart--shown')) {
                            e.preventDefault();
                            closeJuhaniCartDrawer();
                        }
                    }
                });

                // Toggle Click handling: works for both desktop header cart and mobile bottom bar cart (including heading widget)
                document.addEventListener('click', function(e) {
                    const toggle = e.target.closest('#elementor-menu-cart__toggle_button, .elementor-menu-cart__toggle, .elementor-element-0133b7d #elementor-menu-cart__toggle_button, .elementor-element-77a58cb #elementor-menu-cart__toggle_button, [data-id="0133b7d"] #elementor-menu-cart__toggle_button, [data-id="77a58cb"] #elementor-menu-cart__toggle_button, .elementor-element-77a58cb .elementor-widget-heading, .elementor-element-77a58cb .elementor-heading-title');
                    if (toggle) {
                        const parentWidget = toggle.closest('.elementor-widget-woocommerce-menu-cart') || document.querySelector('.elementor-element-77a58cb .elementor-widget-woocommerce-menu-cart');
                        setTimeout(() => {
                            const isShown = parentWidget && parentWidget.classList.contains('elementor-menu-cart--shown');
                            const anyShown = document.querySelector('.elementor-widget-woocommerce-menu-cart.elementor-menu-cart--shown');
                            if (isShown || anyShown) {
                                document.body.classList.add('juhani-cart-open');
                                document.body.style.overflow = 'hidden';
                                enhanceMiniCartDrawer();
                            } else if (parentWidget && !isShown) {
                                // Fallback open if native Elementor JS did not trigger
                                parentWidget.classList.add('elementor-menu-cart--shown');
                                const container = parentWidget.querySelector('.elementor-menu-cart__container');
                                const main = parentWidget.querySelector('.elementor-menu-cart__main');
                                if (container) {
                                    container.setAttribute('aria-hidden', 'false');
                                    container.style.opacity = '1';
                                    container.style.visibility = 'visible';
                                    container.style.pointerEvents = 'auto';
                                }
                                if (main) main.setAttribute('aria-hidden', 'false');
                                toggle.setAttribute('aria-expanded', 'true');
                                document.body.classList.add('juhani-cart-open');
                                document.body.style.overflow = 'hidden';
                                enhanceMiniCartDrawer();
                            }
                        }, 50);
                    }
                });

                console.log('[Juhani Cart Fix] All fixes applied');
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryInit);
            } else {
                tryInit();
            }

            window.addEventListener('load', () => setTimeout(tryInit, 1000));
        }

        // ===== INDEPENDENT NAV MENU (Modern Toggle Icon & Polished Right-side Drawer) =====
        function initJuhaniMobileDrawer() {
            const homeUrl = '<?php echo esc_js(home_url('/')); ?>';
            const logoUrl = '<?php echo esc_js(content_url('/uploads/2026/04/Juhani-Logo-02.png')); ?>';

            const tryInitDrawer = () => {
                const menuToggle = document.querySelector('.elementor-71 .elementor-menu-toggle, .elementor-widget-nav-menu .elementor-menu-toggle');
                const menuDropdown = document.querySelector('.elementor-71 .elementor-nav-menu--dropdown, .elementor-widget-nav-menu .elementor-nav-menu--dropdown');

                // 1. Ensure independent backdrop overlay exists in body
                let overlay = document.querySelector('.juhani-drawer-overlay');
                if (!overlay) {
                    overlay = document.createElement('div');
                    overlay.className = 'juhani-drawer-overlay';
                    document.body.appendChild(overlay);
                }

                // 2. Inject modern 3-line hamburger icon into toggle button and remove old icon/cross
                if (menuToggle) {
                    if (!menuToggle.querySelector('.juhani-modern-hamburger')) {
                        const burger = document.createElement('div');
                        burger.className = 'juhani-modern-hamburger';
                        burger.setAttribute('aria-hidden', 'true');
                        burger.innerHTML = '<span></span><span></span><span></span>';
                        menuToggle.appendChild(burger);
                        menuToggle.classList.add('has-modern-burger');
                    }
                }

                // 3. Inject Drawer Header (Logo + Close button) & Drawer Footer into Drawer
                if (menuDropdown) {
                    if (!menuDropdown.querySelector('.juhani-drawer-header')) {
                        const header = document.createElement('div');
                        header.className = 'juhani-drawer-header';
                        header.innerHTML = `
                            <a href="${homeUrl}" class="juhani-drawer-logo-wrap" title="Juhani">
                                <img src="${logoUrl}" alt="Juhani" class="juhani-drawer-logo" />
                            </a>
                            <button type="button" class="juhani-drawer-close" aria-label="Close menu">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="square">
                                    <line x1="18" y1="6" x2="6" y2="18"></line>
                                    <line x1="6" y1="6" x2="18" y2="18"></line>
                                </svg>
                            </button>
                        `;
                        menuDropdown.insertBefore(header, menuDropdown.firstChild);
                    }

                    if (!menuDropdown.querySelector('.juhani-drawer-footer')) {
                        const footer = document.createElement('div');
                        footer.className = 'juhani-drawer-footer';
                        footer.innerHTML = '<span class="juhani-drawer-tagline">Quality Nets &amp; Fishing Supplies</span>';
                        menuDropdown.appendChild(footer);
                    }
                }

                // 4. Bind drawer events
                if (menuToggle && menuDropdown && !menuToggle.dataset.juhaniDrawerBound) {
                    menuToggle.dataset.juhaniDrawerBound = '1';

                    const closeDrawer = () => {
                        if (menuToggle.classList.contains('elementor-active')) {
                            menuToggle.click();
                        }
                        document.body.classList.remove('juhani-drawer-open');
                    };

                    // Toggle body scroll and overlay
                    menuToggle.addEventListener('click', function() {
                        setTimeout(() => {
                            if (menuToggle.classList.contains('elementor-active')) {
                                document.body.classList.add('juhani-drawer-open');
                            } else {
                                document.body.classList.remove('juhani-drawer-open');
                            }
                        }, 50);
                    });

                    // Overlay click closes menu
                    overlay.addEventListener('click', function(e) {
                        e.preventDefault();
                        closeDrawer();
                    });

                    // Close button inside drawer closes menu
                    const closeBtn = menuDropdown.querySelector('.juhani-drawer-close');
                    if (closeBtn) {
                        closeBtn.addEventListener('click', function(e) {
                            e.preventDefault();
                            closeDrawer();
                        });
                    }

                    // Footer Bar Menu Toggle Click
                    document.addEventListener('click', function(e) {
                        const menuTrigger = e.target.closest('[data-id="menu60abcea"], .elementor-element-menu60abcea, .elementor-element-menu74918f8, a[href="#juhani-mobile-menu"]');
                        if (menuTrigger) {
                            e.preventDefault();
                            if (menuToggle) {
                                menuToggle.click();
                            }
                        }
                    });

                    // Click outside drawer closes menu
                    document.addEventListener('click', function(e) {
                        const target = e.target;
                        if (menuToggle.contains(target) || menuDropdown.contains(target) || overlay.contains(target) || target.closest('[data-id="menu60abcea"], .elementor-element-menu60abcea, .elementor-element-menu74918f8, a[href="#juhani-mobile-menu"]')) return;
                        if (menuToggle.classList.contains('elementor-active')) {
                            closeDrawer();
                        }
                    });

                    // Click any nav link inside dropdown closes drawer
                    menuDropdown.addEventListener('click', function(e) {
                        if (e.target.closest('a')) {
                            closeDrawer();
                        }
                    });

                    // ESC key closes drawer
                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'Escape' && menuToggle.classList.contains('elementor-active')) {
                            closeDrawer();
                        }
                    });
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryInitDrawer);
            } else {
                tryInitDrawer();
            }

            window.addEventListener('load', () => setTimeout(tryInitDrawer, 500));
            // Also run shortly after init in case Elementor initialized late
            setTimeout(tryInitDrawer, 300);
            setTimeout(tryInitDrawer, 1000);
        }

        initJuhaniCartFix();
        initJuhaniMobileDrawer();
    })();
    </script>
    <?php
}, 100);

// ============================================================
// HIDE "PRODUCT HAS BEEN ADDED TO YOUR CART" MESSAGE ON CHECKOUT
// ============================================================
add_action('template_redirect', function() {
    if (function_exists('is_checkout') && is_checkout() && !is_order_received_page()) {
        if (function_exists('wc_get_notices') && function_exists('wc_set_notices')) {
            $notices = wc_get_notices();
            if (!empty($notices['success'])) {
                foreach ($notices['success'] as $key => $notice) {
                    $text = is_array($notice) ? ($notice['notice'] ?? '') : (string) $notice;
                    if (stripos($text, 'added to your cart') !== false || stripos($text, 'wc-forward') !== false || stripos($text, 'cart') !== false) {
                        unset($notices['success'][$key]);
                    }
                }
                wc_set_notices($notices);
            }
        }
    }
}, 10);

add_action('woocommerce_before_checkout_form', function() {
    if (function_exists('wc_get_notices') && function_exists('wc_set_notices')) {
        $notices = wc_get_notices();
        if (!empty($notices['success'])) {
            foreach ($notices['success'] as $key => $notice) {
                $text = is_array($notice) ? ($notice['notice'] ?? '') : (string) $notice;
                if (stripos($text, 'added to your cart') !== false || stripos($text, 'wc-forward') !== false || stripos($text, 'cart') !== false) {
                    unset($notices['success'][$key]);
                }
            }
            wc_set_notices($notices);
        }
    }
}, 1);

add_filter('wc_add_to_cart_message_html', function($message) {
    if (function_exists('is_checkout') && is_checkout()) {
        return '';
    }
    return $message;
}, 10, 1);
