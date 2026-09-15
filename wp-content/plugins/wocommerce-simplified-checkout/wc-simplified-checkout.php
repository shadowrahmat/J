<?php
/**
 * Plugin Name: Simplified WooCommerce Checkout
 * Description: Keep only Full Name, Phone Number, and Full Address on WooCommerce checkout and map them to order billing fields.
 * Version:     1.0.0
 * Author:      Marketorr
 * License:     GPLv2 or later
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Remove default checkout fields and add our simplified ones.
 */
add_filter( 'woocommerce_checkout_fields', function( $fields ) {

    // Start clean: remove all billing fields
    $fields['billing'] = array();

    // Add Full Name
    $fields['billing']['billing_full_name'] = array(
        'type'        => 'text',
        'label'       => __( 'Full Name', 'sunlightbd' ),
        'placeholder' => __( 'Full Name', 'sunlightbd' ),
        'required'    => true,
        'priority'    => 10,
        'class'       => array('form-row-wide'),
    );

    // Add Phone
    $fields['billing']['billing_phone'] = array(
        'type'        => 'tel',
        'label'       => __( 'Phone Number', 'sunlightbd' ),
        'placeholder' => __( '01XXXXXXXXX', 'sunlightbd' ),
        'required'    => true,
        'priority'    => 20,
        'class'       => array('form-row-wide'),
        'validate'    => array('phone'),
    );

    // Add Full Address (textarea)
    $fields['billing']['billing_full_address'] = array(
        'type'        => 'textarea',
        'label'       => __( 'Full Address', 'sunlightbd' ),
        'placeholder' => __( 'Full Address', 'sunlightbd' ),
        'required'    => true,
        'priority'    => 30,
        'class'       => array('form-row-wide'),
    );

    // Also ensure shipping & account sections are empty (pure single-step)
    if ( isset( $fields['shipping'] ) ) {
        $fields['shipping'] = array();
    }
    if ( isset( $fields['account'] ) ) {
        // keep as-is if you allow account creation; else:
        // $fields['account'] = array();
    }

    return $fields;
} );


/**
 * Server-side validation (all 3 required).
 */
add_action( 'woocommerce_checkout_process', function() {

    if ( empty( $_POST['billing_full_name'] ) ) {
        wc_add_notice( __( 'Please enter your full name.', 'sunlightbd' ), 'error' );
    }

    if ( empty( $_POST['billing_phone'] ) ) {
        wc_add_notice( __( 'Please enter your phone number.', 'sunlightbd' ), 'error' );
    }

    if ( empty( $_POST['billing_full_address'] ) ) {
        wc_add_notice( __( 'Please enter your full address.', 'sunlightbd' ), 'error' );
    }
} );


/**
 * Save & map custom fields to order billing fields.
 * - Split full name into first/last
 * - Put full address into billing_address_1
 */
add_action( 'woocommerce_checkout_create_order', function( $order, $data ) {

    // Full name -> split into first/last (last word as last name)
    $full_name = isset($_POST['billing_full_name']) ? trim( wp_strip_all_tags( $_POST['billing_full_name'] ) ) : '';
    $first_name = $full_name;
    $last_name  = '';

    if ( strpos( $full_name, ' ' ) !== false ) {
        $parts = preg_split( '/\s+/', $full_name );
        $last_name = array_pop( $parts );
        $first_name = implode( ' ', $parts );
    }

    if ( method_exists( $order, 'set_billing_first_name' ) ) {
        $order->set_billing_first_name( $first_name );
        $order->set_billing_last_name( $last_name );
    } else {
        update_post_meta( $order->get_id(), '_billing_first_name', $first_name );
        update_post_meta( $order->get_id(), '_billing_last_name',  $last_name );
    }

    // Phone
    $phone = isset($_POST['billing_phone']) ? trim( wp_strip_all_tags( $_POST['billing_phone'] ) ) : '';
    if ( method_exists( $order, 'set_billing_phone' ) ) {
        $order->set_billing_phone( $phone );
    } else {
        update_post_meta( $order->get_id(), '_billing_phone', $phone );
    }

    // Full address -> billing_address_1
    $full_address = isset($_POST['billing_full_address']) ? trim( wp_kses_post( $_POST['billing_full_address'] ) ) : '';
    if ( method_exists( $order, 'set_billing_address_1' ) ) {
        $order->set_billing_address_1( $full_address );
        // Clear the rest so templates don’t show empties weirdly
        $order->set_billing_address_2( '' );
        $order->set_billing_city( '' );
        $order->set_billing_state( '' );
        $order->set_billing_postcode( '' );
        // Optionally set country default (Bangladesh)
        if ( empty( $order->get_billing_country() ) ) {
            $order->set_billing_country( 'BD' );
        }
    } else {
        update_post_meta( $order->get_id(), '_billing_address_1', $full_address );
        update_post_meta( $order->get_id(), '_billing_city', '' );
        update_post_meta( $order->get_id(), '_billing_state', '' );
        update_post_meta( $order->get_id(), '_billing_postcode', '' );
        update_post_meta( $order->get_id(), '_billing_country', 'BD' );
    }

    // Store the raw fields as order meta (for admin visibility)
    $order->update_meta_data( '_sun_full_name', $full_name );
    $order->update_meta_data( '_sun_full_address', $full_address );

}, 10, 2 );


/**
 * Show the custom fields in admin order screen (billing box).
 */
add_action( 'woocommerce_admin_order_data_after_billing_address', function( $order ) {
    $full_name    = $order->get_meta( '_sun_full_name' );
    $full_address = $order->get_meta( '_sun_full_address' );

    if ( $full_name ) {
        echo '<p><strong>' . esc_html__( 'Full Name (raw)', 'sunlightbd' ) . ':</strong> ' . esc_html( $full_name ) . '</p>';
    }
    if ( $full_address ) {
        echo '<p><strong>' . esc_html__( 'Full Address (raw)', 'sunlightbd' ) . ':</strong><br>' . nl2br( esc_html( $full_address ) ) . '</p>';
    }
} );


/**
 * Hide shipping fields section entirely (optional).
 */
add_filter( 'woocommerce_cart_needs_shipping_address', function( $needs_address ) {
    return false; // no shipping address step; we use the single Full Address field
}, 99 );

add_action( 'woocommerce_after_checkout_form', 'sunlightbd_force_checkout_field_change' );

function sunlightbd_force_checkout_field_change() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Update label and placeholder for phone number field
            $('label[for="billing_phone"]').text('Phone Number');
            $('#billing_phone').attr('placeholder', '01XXXXXXXXX');

            // Add the required mark (*) next to the phone field label
            $('label[for="billing_phone"]').append(' <span class="required">*</span>');
        });
    </script>
    <?php
}


/*
 Custom function
 */

add_filter('woocommerce_add_to_cart_redirect', 'redirect_to_checkout_after_add_to_cart');
function redirect_to_checkout_after_add_to_cart() {
    return wc_get_checkout_url();
}


add_filter( 'woocommerce_product_single_add_to_cart_text', 'woocommerce_add_to_cart_button_text_single' ); 
function woocommerce_add_to_cart_button_text_single() {
    return __( 'Order Now', 'woocommerce' ); 
}

add_filter( 'woocommerce_product_add_to_cart_text', 'woocommerce_add_to_cart_button_text_archives' );  
function woocommerce_add_to_cart_button_text_archives() {
    return __( 'Order Now', 'woocommerce' );
}

/**
 * Checkout review order quantity handlers & styling
 */
function add_quantity_buttons_to_elementor_checkout() {
    if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) return;
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        function updateQuantity(productKey, newQty, $wrapper) {
            $wrapper.addClass('updating');
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url("admin-ajax.php"); ?>',
                data: {
                    action: 'update_checkout_quantity',
                    product_key: productKey,
                    quantity: newQty,
                    nonce: '<?php echo wp_create_nonce("update_checkout_quantity_nonce"); ?>'
                },
                success: function(response) {
                    $wrapper.removeClass('updating');
                    if (response && response.success) {
                        $(document.body).trigger('update_checkout');
                    } else {
                        location.reload();
                    }
                },
                error: function() {
                    $wrapper.removeClass('updating');
                    location.reload();
                }
            });
        }

        $(document).on('click', '.checkout-qty-plus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $wrapper = $(this).closest('.checkout-quantity-wrapper');
            var productKey = $wrapper.data('product-key');
            var $display = $wrapper.find('.checkout-qty-display');
            var currentQty = parseInt($display.text(), 10) || 1;
            var newQty = currentQty + 1;
            if (productKey) {
                $display.text(newQty);
                updateQuantity(productKey, newQty, $wrapper);
            }
        });

        $(document).on('click', '.checkout-qty-minus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $wrapper = $(this).closest('.checkout-quantity-wrapper');
            var productKey = $wrapper.data('product-key');
            var $display = $wrapper.find('.checkout-qty-display');
            var currentQty = parseInt($display.text(), 10) || 1;
            var newQty = currentQty - 1;
            if (newQty > 0 && productKey) {
                $display.text(newQty);
                updateQuantity(productKey, newQty, $wrapper);
            }
        });
    });
    </script>

    <style id="juhani-checkout-review-order-css">
    /* ====================================================
       JUHANI CHECKOUT REVIEW ORDER (CLEAN 2-COL ALIGNED)
       ==================================================== */

    /* Kill any Elementor / WooCommerce responsive before pseudo-elements */
    .woocommerce-checkout table.shop_table_responsive tr td::before,
    .woocommerce-checkout table.shop_table_responsive tr th::before,
    .woocommerce-checkout table.shop_table_responsive tr::before,
    .woocommerce table.shop_table_responsive tr td::before,
    .elementor-widget-woocommerce-checkout-page table.shop_table_responsive tr td::before,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tr td::before,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tr th::before {
        display: none !important;
        content: none !important;
    }

    /* Outer order review table */
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table {
        border: 0 !important;
        border-collapse: collapse !important;
        width: 100% !important;
        display: table !important;
        margin: 0 !important;
    }
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table thead {
        display: none !important;
    }

    /* Cart item rows - full width card container */
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tbody {
        display: block !important;
        width: 100% !important;
    }
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tbody tr.juhani-checkout-cart-item-row {
        display: block !important;
        width: 100% !important;
        border: 0 !important;
        padding: 0 !important;
        margin: 0 0 16px 0 !important;
        background: transparent !important;
    }
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tbody td.juhani-checkout-cart-item-cell {
        display: block !important;
        width: 100% !important;
        max-width: 100% !important;
        padding: 0 !important;
        border: 0 !important;
        background: transparent !important;
        text-align: left !important;
    }

    /* Card container */
    .juhani-checkout-card {
        width: 100% !important;
        background: #ffffff !important;
        border: 1px solid #dce6ef !important;
        box-sizing: border-box !important;
        margin: 0 !important;
    }

    /* Each row: CSS GRID with 140px label and remaining width for value */
    .juhani-co-row {
        display: grid !important;
        grid-template-columns: 140px minmax(0, 1fr) !important;
        align-items: center !important;
        border-bottom: 1px solid #eef2f7 !important;
        box-sizing: border-box !important;
        min-height: 42px !important;
    }
    .juhani-co-row:last-child {
        border-bottom: 0 !important;
    }

    /* Left column (Labels): Product, Quantity, Suta, Haat, etc. */
    .juhani-co-label {
        padding: 10px 14px !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #4a607a !important;
        background: #fbfcfd !important;
        border-right: 1px solid #edf2f7 !important;
        box-sizing: border-box !important;
        display: flex !important;
        align-items: center !important;
        height: 100% !important;
        text-align: left !important;
        line-height: 1.35 !important;
    }

    /* Right column (Values) */
    .juhani-co-value {
        padding: 10px 14px !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 13.5px !important;
        font-weight: 500 !important;
        color: #0f2238 !important;
        box-sizing: border-box !important;
        display: flex !important;
        align-items: center !important;
        word-break: break-word !important;
        line-height: 1.35 !important;
        text-align: left !important;
    }

    /* Row 1: Product Name header */
    .juhani-co-row--product {
        background: #f0f6fb !important;
        border-bottom: 1px solid #d8e5f0 !important;
    }
    .juhani-co-row--product .juhani-co-label {
        background: #eaf2f8 !important;
        color: #10395E !important;
        font-size: 11.5px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    .juhani-co-row--product .juhani-co-value strong {
        font-size: 14.5px !important;
        font-weight: 700 !important;
        color: #10395E !important;
    }

    /* Row Last: Subtotal footer */
    .juhani-co-row--subtotal {
        background: #f4f8fb !important;
        border-top: 1.5px solid #d8e5f0 !important;
    }
    .juhani-co-row--subtotal .juhani-co-label {
        background: #eaf2f8 !important;
        color: #10395E !important;
        font-size: 12px !important;
        font-weight: 700 !important;
        text-transform: uppercase !important;
        letter-spacing: 0.4px !important;
    }
    .juhani-co-subtotal-val {
        font-size: 15px !important;
        font-weight: 700 !important;
        color: #10395E !important;
    }
    .juhani-co-subtotal-val .amount {
        color: #10395E !important;
        font-weight: 800 !important;
    }

    /* Quantity stepper */
    .checkout-quantity-wrapper {
        display: inline-flex !important;
        align-items: center !important;
        border: 1.5px solid #10395E !important;
        border-radius: 0 !important;
        overflow: hidden !important;
        background: #ffffff !important;
        height: 34px !important;
        width: auto !important;
        box-shadow: none !important;
    }
    .checkout-quantity-wrapper.updating {
        opacity: 0.5 !important;
        pointer-events: none !important;
    }
    .checkout-qty-minus,
    .checkout-qty-plus {
        width: 32px !important;
        height: 34px !important;
        min-width: 32px !important;
        max-width: 32px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background: #10395E !important;
        border: 0 !important;
        border-radius: 0 !important;
        color: #ffffff !important;
        font-size: 18px !important;
        font-weight: 700 !important;
        line-height: 1 !important;
        cursor: pointer !important;
        transition: background-color 0.16s ease !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
    }
    .checkout-qty-minus:hover,
    .checkout-qty-plus:hover {
        background: #0f2e4a !important;
    }
    .checkout-qty-display {
        min-width: 44px !important;
        max-width: 44px !important;
        width: 44px !important;
        height: 34px !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        font-family: "Poppins", sans-serif !important;
        font-weight: 700 !important;
        font-size: 14px !important;
        color: #0f2238 !important;
        background: #ffffff !important;
        padding: 0 !important;
        flex-shrink: 0 !important;
    }

    /* Table Footer (Subtotal / Shipping / Total) */
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot {
        display: block !important;
        width: 100% !important;
        margin-top: 14px !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot tr {
        display: flex !important;
        flex-direction: row !important;
        justify-content: space-between !important;
        align-items: center !important;
        border-top: 1px solid #e5edf5 !important;
        padding: 12px 0 !important;
        width: 100% !important;
        box-sizing: border-box !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.woocommerce-shipping-totals,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot tr.woocommerce-shipping-totals {
        align-items: center !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot th,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot th {
        border: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
        max-width: none !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #344b63 !important;
        text-align: left !important;
        display: inline-flex !important;
        align-items: center !important;
        flex: 0 0 auto !important;
        line-height: 1 !important;
        background: transparent !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot td,
    body.woocommerce-checkout .woocommerce-checkout-review-order-table .woocommerce-shipping-totals td,
    .elementor-widget-woocommerce-checkout-page .woocommerce-shipping-totals td,
    .elementor-widget-woocommerce-checkout-page .woocommerce .woocommerce-shipping-totals td,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot td {
        border: 0 !important;
        padding: 0 !important;
        margin: 0 !important;
        width: auto !important;
        max-width: none !important;
        font-family: "Poppins", sans-serif !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        color: #0f2238 !important;
        text-align: right !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        flex: 1 1 auto !important;
        line-height: 1 !important;
        background: transparent !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot th + td,
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot td + td {
        margin-top: 0 !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.order-total,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot tr.order-total {
        border-top: 2px solid #10395E !important;
        padding-top: 16px !important;
        padding-bottom: 6px !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.order-total th,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot tr.order-total th {
        font-size: 16px !important;
        font-weight: 800 !important;
        color: #10395E !important;
        text-transform: uppercase !important;
        letter-spacing: 0.5px !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.order-total td,
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot tr.order-total td .amount,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot tr.order-total td .amount {
        font-size: 18px !important;
        font-weight: 800 !important;
        color: #10395E !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot .shipping ul,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot .shipping ul {
        list-style: none !important;
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        flex-direction: row !important;
        flex-wrap: wrap !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 16px !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot .shipping ul li,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot .shipping ul li {
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        justify-content: flex-end !important;
        gap: 6px !important;
        white-space: nowrap !important;
        line-height: 1 !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot .shipping ul li input[type="radio"],
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot .shipping ul li input[type="radio"] {
        margin: 0 !important;
        padding: 0 !important;
        vertical-align: middle !important;
        cursor: pointer !important;
    }
    body.woocommerce-checkout .woocommerce-checkout-review-order-table tfoot .shipping ul li label,
    .elementor-widget-woocommerce-checkout-page .woocommerce-checkout-review-order-table tfoot .shipping ul li label {
        margin: 0 !important;
        padding: 0 !important;
        display: inline-flex !important;
        align-items: center !important;
        gap: 5px !important;
        font-size: 14px !important;
        font-weight: 600 !important;
        color: #0d2a43 !important;
        line-height: 1 !important;
        cursor: pointer !important;
    }

    /* Mobile adjustments */
    @media (max-width: 767px) {
        .juhani-co-row {
            grid-template-columns: 110px minmax(0, 1fr) !important;
        }
        .juhani-co-label {
            padding: 8px 10px !important;
            font-size: 12px !important;
        }
        .juhani-co-value {
            padding: 8px 10px !important;
            font-size: 12.5px !important;
        }
        .checkout-quantity-wrapper {
            height: 30px !important;
        }
        .checkout-qty-minus,
        .checkout-qty-plus {
            width: 28px !important;
            min-width: 28px !important;
            max-width: 28px !important;
            height: 30px !important;
            font-size: 16px !important;
        }
        .checkout-qty-display {
            min-width: 36px !important;
            max-width: 36px !important;
            width: 36px !important;
            height: 30px !important;
            font-size: 13px !important;
        }
    }
    </style>
    <?php
}
add_action('wp_footer', 'add_quantity_buttons_to_elementor_checkout');

/**
 * Handle AJAX quantity updates
 */
function handle_checkout_quantity_update() {
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'update_checkout_quantity_nonce')) {
        wp_send_json_error('Security verification failed');
    }

    if (!isset($_POST['product_key']) || !isset($_POST['quantity'])) {
        wp_send_json_error('Missing required data');
    }

    $product_key = sanitize_text_field($_POST['product_key']);
    $quantity = intval($_POST['quantity']);

    if ($quantity < 1) {
        wp_send_json_error('Quantity must be at least 1');
    }

    if (!WC()->cart) {
        wp_send_json_error('Cart not available');
    }

    try {
        $cart_items = WC()->cart->get_cart();
        
        // Verify the product key exists
        if (!isset($cart_items[$product_key])) {
            wp_send_json_error('Cart item not found. Available keys: ' . implode(', ', array_keys($cart_items)));
        }
        
        // Update the quantity
        $updated = WC()->cart->set_quantity($product_key, $quantity);
        
        if ($updated) {
            WC()->cart->calculate_totals();
            wp_send_json_success(array(
                'message' => 'Quantity updated successfully',
                'new_quantity' => $quantity
            ));
        } else {
            wp_send_json_error('Failed to update quantity in cart');
        }
        
    } catch (Exception $e) {
        wp_send_json_error('Error: ' . $e->getMessage());
    }
}
add_action('wp_ajax_update_checkout_quantity', 'handle_checkout_quantity_update');
add_action('wp_ajax_nopriv_update_checkout_quantity', 'handle_checkout_quantity_update');

add_filter( 'product_type_options', function( $options ) {
	// remove "Virtual" checkbox
	if( isset( $options[ 'virtual' ] ) ) {
		unset( $options[ 'virtual' ] );
	}
	// remove "Downloadable" checkbox
	if( isset( $options[ 'downloadable' ] ) ) {
		unset( $options[ 'downloadable' ] );
	}
	return $options;
} );
