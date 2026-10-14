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

add_action('wp_head', function() {
    ?>
    <style id="juhani-cart-fix">
body.juhani-drawer-open{
        overflow: hidden !important;
        touch-action: none !important;
    }

    /* Single Product Gallery Zoom / Lightbox Trigger: Hide when cart drawer or menu is open */
    .woocommerce-product-gallery__trigger {
        z-index: 5 !important;
    }

    body.juhani-drawer-open .woocommerce-product-gallery__trigger{
        display: none !important;
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
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
        .juhani-cart-drawer-trigger {
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
        .juhani-cart-drawer-trigger .elementor-widget-woocommerce-menu-cart {
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
.juhani-cart-drawer-trigger .elementor-widget-container{
            width: 100% !important;
            height: 26px !important;
            margin: 0 !important;
            padding: 0 !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
        }
.juhani-cart-drawer-trigger #elementor-menu-cart__toggle_button,
        .juhani-cart-drawer-trigger .elementor-button{
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
        .juhani-cart-drawer-trigger .elementor-button-text,
        .juhani-cart-drawer-trigger .elementor-screen-only {
            display: none !important;
        }
        .juhani-cart-drawer-trigger .elementor-button-icon {
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
        .juhani-cart-drawer-trigger .elementor-button-icon i,
        .juhani-cart-drawer-trigger .elementor-button-icon svg,
        .juhani-cart-drawer-trigger .elementor-button-icon i[class*="ti-"],
        .juhani-cart-drawer-trigger .elementor-button-icon i.eicon {
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
        .juhani-cart-drawer-trigger .elementor-button-icon i[class*="ti-"] {
            font-family: "themify" !important;
            font-weight: 400 !important;
            speak: none !important;
            font-style: normal !important;
        }
        .juhani-cart-drawer-trigger .elementor-button-icon svg,
        .juhani-cart-drawer-trigger .elementor-button-icon svg.juhani-cart-svg {
            display: none !important;
        }
        .juhani-cart-drawer-trigger .elementor-button-icon.juhani-no-font svg.juhani-cart-svg {
            display: block !important;
            stroke: #10395E !important;
            fill: none !important;
            width: 24px !important;
            height: 24px !important;
        }
        .juhani-cart-drawer-trigger .elementor-button-icon.juhani-no-font i {
            display: none !important;
        }
        .juhani-cart-drawer-trigger .elementor-button-icon-qty {
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
        .juhani-cart-drawer-trigger .elementor-widget-heading {
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
        .juhani-cart-drawer-trigger .elementor-widget-heading .elementor-heading-title,
        .juhani-cart-drawer-trigger .elementor-heading-title {
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
       MOBILE CART DRAWER — RESPONSIVE VERSION OF DESKTOP
       ==================================================== */
    @media (max-width: 767px) {
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

    form.checkout .woocommerce-message,
    .elementor-widget-woocommerce-checkout-page .woocommerce-message{
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
    ?>
    <script id="juhani-cart-fix-js">
    (function() {
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
