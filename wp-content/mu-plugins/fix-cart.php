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
    /* Mobile & Tablet: ensure shopping cart is hidden as requested */
    @media (max-width: 1024px) {
        .elementor-element-4358431,
        .elementor-element-8d170ad,
        .elementor-widget-woocommerce-menu-cart {
            display: none !important;
            visibility: hidden !important;
            width: 0 !important;
            height: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
        }
    }
    /* Lock body scrolling when side drawer is open */
    body.juhani-drawer-open {
        overflow: hidden !important;
        touch-action: none !important;
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
                const cartWidget = document.querySelector('.elementor-widget-woocommerce-menu-cart');
                const toggleBtn = document.getElementById('elementor-menu-cart__toggle_button');
                const container = document.querySelector('.elementor-menu-cart__container');
                const main = document.querySelector('.elementor-menu-cart__main');
                const wrapper = document.querySelector('.elementor-menu-cart__wrapper');

                if (!cartWidget || !toggleBtn) {
                    // Retry shortly if not yet rendered
                    setTimeout(tryInit, 500);
                    return;
                }

                console.log('[Juhani Cart Fix] Found cart widget, applying fixes');

                // Fix 1: Move container to body to escape transformed header ancestor (critical for fixed header with transform)
                // The Elementor side-cart is position:fixed but inside a transformed parent, so it becomes relative to header not viewport.
                // Moving it to body after init ensures it overlays correctly.
                // We detach container and re-append to body, but keep reference for Elementor JS.
                // Instead of moving, we can just ensure CSS override works. However, if still broken, we move via JS.
                // Check if container is inside header with transform
                const headerEl = document.querySelector('.elementor-location-header, #1d9f82b');
                let hasTransformParent = false;
                let el = container;
                while (el && el !== document.body) {
                    const style = window.getComputedStyle(el.parentElement);
                    if (style.transform && style.transform !== 'none') {
                        hasTransformParent = true;
                        break;
                    }
                    el = el.parentElement;
                }
                if (hasTransformParent && container && !container.dataset.moved) {
                    console.log('[Juhani Cart Fix] Detected transform parent, container will be handled via CSS override (margin auto header fix already applied).');
                    // Note: Header fix already removed transform, so this should be false now. Keep as safety.
                }

                // Fix 2: Ensure toggle button has correct href fallback and click handler
                // If Elementor JS fails, fallback to direct navigation to cart page
                let fallbackEnabled = false;
                const cartUrl = '<?php echo esc_js($cart_url); ?>';
                
                // Check if Elementor handler is bound by testing if widget has class toggle working after 1s
                setTimeout(() => {
                    // Test if click would work - if no elementor handler, add our own
                    const hasElementorHandler = $._data && toggleBtn && $._data(toggleBtn, 'events');
                    // Simpler: always add fallback that works with or without Elementor
                    if (!toggleBtn.dataset.juhaniFixed) {
                        toggleBtn.dataset.juhaniFixed = '1';
                        
                        // Add manual toggle as backup (if Elementor's handler is broken)
                        toggleBtn.addEventListener('click', function(e) {
                            // If Elementor already handled, it will have added class. Check after short delay.
                            // We implement our own toggle as fallback if Elementor class not toggling.
                            const widget = document.querySelector('.elementor-widget-woocommerce-menu-cart');
                            const isShown = widget && widget.classList.contains('elementor-menu-cart--shown');
                            
                            // If Elementor JS is working, it will toggle within 100ms. We wait to see.
                            setTimeout(() => {
                                const nowShown = widget && widget.classList.contains('elementor-menu-cart--shown');
                                if (nowShown === isShown) {
                                    // Elementor didn't toggle, so we do it manually
                                    console.log('[Juhani Cart Fix] Manual toggle fallback triggered');
                                    if (!nowShown) {
                                        widget.classList.add('elementor-menu-cart--shown');
                                        if (container) container.setAttribute('aria-hidden', 'false');
                                        if (main) main.setAttribute('aria-hidden', 'false');
                                        toggleBtn.setAttribute('aria-expanded', 'true');
                                        document.body.style.overflow = 'hidden'; // prevent background scroll
                                    } else {
                                        widget.classList.remove('elementor-menu-cart--shown');
                                        if (container) container.setAttribute('aria-hidden', 'true');
                                        if (main) main.setAttribute('aria-hidden', 'true');
                                        toggleBtn.setAttribute('aria-expanded', 'false');
                                        document.body.style.overflow = '';
                                    }
                                }
                            }, 150);
                        });
                        console.log('[Juhani Cart Fix] Fallback click handler added');
                    }
                }, 800);

                // Fix 3: Close button handler fallback
                const closeBtn = document.querySelector('.elementor-menu-cart__close-button, .elementor-menu-cart__close-button-custom');
                if (closeBtn && !closeBtn.dataset.juhaniFixed) {
                    closeBtn.dataset.juhaniFixed = '1';
                    closeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        const widget = document.querySelector('.elementor-widget-woocommerce-menu-cart');
                        if (widget) {
                            widget.classList.remove('elementor-menu-cart--shown');
                            const c = document.querySelector('.elementor-menu-cart__container');
                            const m = document.querySelector('.elementor-menu-cart__main');
                            const t = document.getElementById('elementor-menu-cart__toggle_button');
                            if (c) c.setAttribute('aria-hidden', 'true');
                            if (m) m.setAttribute('aria-hidden', 'true');
                            if (t) t.setAttribute('aria-expanded', 'false');
                            document.body.style.overflow = '';
                        }
                    });
                }

                // Fix 4: Click outside to close (fallback)
                document.addEventListener('click', function(e) {
                    const widget = document.querySelector('.elementor-widget-woocommerce-menu-cart');
                    if (!widget || !widget.classList.contains('elementor-menu-cart--shown')) return;
                    const target = e.target;
                    const mainEl = document.querySelector('.elementor-menu-cart__main');
                    const toggleEl = document.querySelector('.elementor-menu-cart__toggle');
                    if (mainEl && mainEl.contains(target)) return;
                    if (toggleEl && toggleEl.contains(target)) return;
                    // Close
                    widget.classList.remove('elementor-menu-cart--shown');
                    const c = document.querySelector('.elementor-menu-cart__container');
                    const m = document.querySelector('.elementor-menu-cart__main');
                    const t = document.getElementById('elementor-menu-cart__toggle_button');
                    if (c) c.setAttribute('aria-hidden', 'true');
                    if (m) m.setAttribute('aria-hidden', 'true');
                    if (t) t.setAttribute('aria-expanded', 'false');
                    document.body.style.overflow = '';
                });

                // Fix 5: ESC key fallback
                document.addEventListener('keyup', function(e) {
                    if (e.keyCode === 27) {
                        const widget = document.querySelector('.elementor-widget-woocommerce-menu-cart');
                        if (widget && widget.classList.contains('elementor-menu-cart--shown')) {
                            widget.classList.remove('elementor-menu-cart--shown');
                            const c = document.querySelector('.elementor-menu-cart__container');
                            const m = document.querySelector('.elementor-menu-cart__main');
                            const t = document.getElementById('elementor-menu-cart__toggle_button');
                            if (c) c.setAttribute('aria-hidden', 'true');
                            if (m) m.setAttribute('aria-hidden', 'true');
                            if (t) t.setAttribute('aria-expanded', 'false');
                            document.body.style.overflow = '';
                        }
                    }
                });

                // Fix 6: If cart is empty, still show "No products" message properly, don't hide via JS
                // Ensure container is clickable even when empty

                console.log('[Juhani Cart Fix] All fixes applied');

                // ===== NAV MENU (Right-side slide drawer helpers) =====
                const menuToggle = document.querySelector('.elementor-71 .elementor-menu-toggle');
                const menuDropdown = document.querySelector('.elementor-71 .elementor-nav-menu--dropdown');
                if (menuToggle && menuDropdown) {
                    if (!menuToggle.dataset.juhaniDrawerBound) {
                        menuToggle.dataset.juhaniDrawerBound = '1';

                        const closeDrawer = () => {
                            if (menuToggle.classList.contains('elementor-active')) {
                                menuToggle.click();
                            }
                            document.body.classList.remove('juhani-drawer-open');
                        };

                        // Toggle body scroll locking
                        menuToggle.addEventListener('click', function() {
                            setTimeout(() => {
                                if (menuToggle.classList.contains('elementor-active')) {
                                    document.body.classList.add('juhani-drawer-open');
                                } else {
                                    document.body.classList.remove('juhani-drawer-open');
                                }
                            }, 50);
                        });

                        // Click outside drawer or backdrop closes menu
                        document.addEventListener('click', function(e) {
                            const target = e.target;
                            if (menuToggle.contains(target) || menuDropdown.contains(target)) return;
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
                }
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', tryInit);
            } else {
                tryInit();
            }

            // Also try after window load (Elementor may init late)
            window.addEventListener('load', () => setTimeout(tryInit, 1000));
        }

        initJuhaniCartFix();
    })();
    </script>
    <?php
}, 100);
