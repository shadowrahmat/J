<?php
/**
 * Plugin Name: Juhani Editable Single Product
 * Description: Scoped enhancements for the editable Single Product Elementor template.
 */
if (!defined('ABSPATH')) exit;

function juhani_single_layout_enabled(): bool {
    return (bool) get_post_meta(37, '_juhani_single_preview_design', true)
        && ((function_exists('is_product') && is_product())
            || absint($_GET['elementor-preview'] ?? 0) === 37
            || absint($_GET['elementor_library'] ?? 0) === 37);
}

add_action('wp_enqueue_scripts', function () {
    if (!juhani_single_layout_enabled()) return;
    $file = __DIR__ . '/juhani-assets/js/single-product.js';
    wp_enqueue_script('juhani-editable-single-product', content_url('/mu-plugins/juhani-assets/js/single-product.js'), ['jquery', 'wc-add-to-cart-variation'], filemtime($file), true);
    wp_localize_script('juhani-editable-single-product', 'juhaniSingleProductSettings', ['decimals'=>wc_get_price_decimals()]);
}, 30);

add_filter('woocommerce_ajax_variation_threshold', function ($threshold, $product) {
    return juhani_single_layout_enabled() ? max(300, $threshold) : $threshold;
}, 20, 2);

// Reorder displayed options without changing saved product data.
add_filter('woocommerce_product_get_attributes', function ($attributes, $product) {
    if (!juhani_single_layout_enabled()) return $attributes;
    $ordered = [];
    foreach (['pa_haat','pa_suta','pa_making-type','pa_git-type','pa_rock-weight-kg'] as $key) {
        if (isset($attributes[$key])) $ordered[$key] = $attributes[$key];
    }
    return $ordered + $attributes;
}, 20, 2);

add_filter('option_woo_variation_swatches', function ($options) {
    if (juhani_single_layout_enabled() && is_array($options)) $options['show_variation_label'] = 'no';
    return $options;
});

add_filter('woocommerce_product_single_add_to_cart_text', function ($text, $product) {
    return juhani_single_layout_enabled() ? 'কার্টে যোগ করুন' : $text;
}, 20, 2);

add_filter('woocommerce_get_availability', function ($availability, $product) {
    if (juhani_single_layout_enabled() && $product->is_in_stock() && empty($availability['availability'])) {
        $availability['availability'] = 'In stock';
    }
    return $availability;
}, 20, 2);

add_filter('woocommerce_product_tabs', function ($tabs) {
    if (juhani_single_layout_enabled() && isset($tabs['additional_information'])) {
        $tabs['additional_information']['title'] = 'Specifications';
        $tabs['additional_information']['callback'] = 'juhani_single_render_specifications';
    }
    return $tabs;
}, 50);

function juhani_single_render_specifications(): void {
    global $product;
    if (!$product instanceof WC_Product) return;
    echo '<dl class="juhani-single-specifications">';
    foreach ($product->get_attributes() as $key => $attribute) {
        if (!$attribute->get_visible()) continue;
        $values = $attribute->is_taxonomy() ? wc_get_product_terms($product->get_id(), $key, ['fields'=>'names']) : $attribute->get_options();
        if (!$values || is_wp_error($values)) continue;
        usort($values, 'strnatcasecmp');
        echo '<div class="juhani-single-specification"><dt>'.esc_html(wc_attribute_label($key, $product)).'</dt><dd>'.esc_html(implode(', ', $values)).'</dd></div>';
    }
    if ($product->has_weight()) echo '<div class="juhani-single-specification"><dt>Weight</dt><dd>'.esc_html(wc_format_weight($product->get_weight())).'</dd></div>';
    if ($product->has_dimensions()) echo '<div class="juhani-single-specification"><dt>Dimensions</dt><dd>'.esc_html(wc_format_dimensions($product->get_dimensions(false))).'</dd></div>';
    echo '</dl>';
}

// The installed Loop Product skin retains the previous global WC_Product.
// Set the current loop product at our explicitly named card boundary only.
add_action('elementor/frontend/before_render', function ($element) {
    $classes = preg_split('/\s+/', (string)$element->get_settings('css_classes'));
    if (!in_array('juhani-single-related-card', $classes, true)) return;
    $current = wc_get_product(get_the_ID());
    if ($current) $GLOBALS['product'] = $current;
});
