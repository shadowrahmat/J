<?php
/**
 * Plugin Name: Juhani Header Polish & Readability Fix
 * Description: Ensures the header has high-contrast solid navy branding, clear readability across all pages, and zero bleed-through when scrolling.
 * Version: 1.0.0
 * Author: Antigravity
 */

if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_head', function () {
    ?>
    <style id="juhani-header-readability-css">
    /* ==========================================================================
       JUHANI HEADER READABILITY & CONTRAST (ALL PAGES & ON SCROLL)
       ========================================================================== */

    /* 1. Desktop Floating Header: Modern Frosted Glass with Zero Border */
    .elementor-71 .elementor-element.elementor-element-1d9f82b,
    [data-id="1d9f82b"] {
        background: transparent !important;
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
        border: none !important;
        outline: none !important;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.22) !important;
    }

    /* Frosted Glass Underlayer - Keeps backdrop-filter off parent to prevent containing-block trap on fixed cart drawer */
    .elementor-71 .elementor-element.elementor-element-1d9f82b::before,
    [data-id="1d9f82b"]::before {
        content: "" !important;
        position: absolute !important;
        inset: 0 !important;
        background: rgba(22, 68, 110, 0.82) !important;
        opacity: 1 !important;
        backdrop-filter: blur(24px) saturate(170%) !important;
        -webkit-backdrop-filter: blur(24px) saturate(170%) !important;
        border: none !important;
        outline: none !important;
        pointer-events: none !important;
        z-index: 0 !important;
    }

    /* Hide distracting top glow gradient */
    .elementor-71 .elementor-element.elementor-element-1d9f82b::after,
    [data-id="1d9f82b"]::after {
        display: none !important;
    }

    /* Ensure all header widgets sit cleanly above the solid background layer */
    .elementor-71 .elementor-element.elementor-element-1d9f82b > *,
    [data-id="1d9f82b"] > * {
        position: relative !important;
        z-index: 2 !important;
    }

    /* 2. Menu Items Readability - Crisp, Bold, High Contrast */
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--main .elementor-item,
    .elementor-71 .elementor-nav-menu--main .elementor-item {
        font-family: "Poppins", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
        font-size: 15px !important;
        font-weight: 600 !important;
        letter-spacing: 0.8px !important;
        color: #FFFFFF !important;
        fill: #FFFFFF !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.28) !important;
        padding: 10px 16px !important;
        transition: color 0.2s ease, transform 0.2s ease !important;
    }

    /* Menu Hover & Active State */
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--main .elementor-item:hover,
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--main .elementor-item:focus,
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--main .elementor-item.elementor-item-active,
    .elementor-71 .elementor-element.elementor-element-3e464c6 .elementor-nav-menu--main .elementor-item.highlighted {
        color: #4CA2D9 !important;
        fill: #4CA2D9 !important;
    }

    /* 3. Search Box Readability */
    .elementor-71 .elementor-element.elementor-element-681c745 input[type="search"] {
        border-bottom: 1.5px solid rgba(255, 255, 255, 0.75) !important;
        color: #FFFFFF !important;
        font-size: 14px !important;
        font-weight: 500 !important;
        letter-spacing: 0.3px !important;
    }

    .elementor-71 .elementor-element.elementor-element-681c745 input[type="search"]::placeholder {
        color: rgba(255, 255, 255, 0.85) !important;
        font-weight: 400 !important;
        letter-spacing: 0.4px !important;
    }

    .elementor-71 .elementor-element.elementor-element-681c745 input[type="search"]:focus {
        border-bottom-color: #4CA2D9 !important;
    }

    .elementor-71 .elementor-element.elementor-element-681c745 button[type="submit"] svg,
    .elementor-71 .elementor-element.elementor-element-681c745 button[type="submit"] i {
        color: #FFFFFF !important;
        fill: #FFFFFF !important;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
    }

    /* 4. Desktop Menu Cart Icon */
    .elementor-71 .elementor-element.elementor-element-8d170ad .elementor-menu-cart__toggle .elementor-button-icon {
        color: #FFFFFF !important;
        fill: #FFFFFF !important;
        filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.3));
    }

    .elementor-71 .elementor-element.elementor-element-8d170ad .elementor-button-icon-qty {
        background-color: #4CA2D9 !important;
        color: #FFFFFF !important;
        font-weight: 700 !important;
        font-size: 11px !important;
    }

    /* 5. Single Product Gallery Trigger Hide on Cart Open */
    .woocommerce-product-gallery__trigger {
        z-index: 5 !important;
    }
    body.juhani-cart-open .woocommerce-product-gallery__trigger,
    body.juhani-drawer-open .woocommerce-product-gallery__trigger,
    body:has(.elementor-menu-cart--shown) .woocommerce-product-gallery__trigger {
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* 6. Mobile / Tablet Floating Header Container - Borderless Glass */
    @media (max-width: 1024px) {
        .elementor-71 .elementor-element.elementor-element-1d9f82b,
        [data-id="1d9f82b"] {
            background: transparent !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            border: none !important;
            outline: none !important;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.22) !important;
        }

        .elementor-71 .elementor-element.elementor-element-1d9f82b::before,
        [data-id="1d9f82b"]::before {
            background: rgba(22, 68, 110, 0.94) !important;
            opacity: 1 !important;
            backdrop-filter: blur(20px) saturate(170%) !important;
            -webkit-backdrop-filter: blur(20px) saturate(170%) !important;
            border: none !important;
            outline: none !important;
        }
    }
    </style>
    <?php
}, 9999);

