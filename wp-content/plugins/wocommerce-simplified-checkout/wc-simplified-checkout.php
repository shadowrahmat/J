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
 * Add quantity buttons to Elementor checkout page
 */
function add_quantity_buttons_to_elementor_checkout() {
    if (!is_checkout()) return;
    
    // Get cart items with their keys
    $cart_items = array();
    if (WC()->cart) {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $cart_items[] = array(
                'key' => $cart_item_key,
                'name' => $cart_item['data']->get_name(),
                'quantity' => $cart_item['quantity']
            );
        }
    }
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var cartItems = <?php echo json_encode($cart_items); ?>;
        
        // Function to add quantity buttons
        function addQuantityButtons() {
            // Find all product rows in the checkout table
            var $productRows = $('.woocommerce-checkout-review-order-table tr.cart_item');
            
            $productRows.each(function(index) {
                var $row = $(this);
                
                // Find the product quantity element inside product-name td
                var $productNameCell = $row.find('td.product-name');
                var $quantityElement = $productNameCell.find('.product-quantity');
                
                if ($quantityElement.length === 0) {
                    return;
                }
                
                // Skip if already has our controls
                if ($quantityElement.find('.checkout-quantity-wrapper').length > 0) {
                    return;
                }
                
                // Get current quantity from the text
                var quantityText = $quantityElement.text();
                var match = quantityText.match(/×\s*(\d+)/);
                if (!match) {
                    return;
                }
                
                var currentQty = parseInt(match[1]);
                
                // Get product key from our cart items
                var productKey = cartItems[index] ? cartItems[index].key : null;
                if (!productKey) {
                    return;
                }
                
                // Create quantity controls HTML
                var quantityHTML = `
                    <br><div class="checkout-quantity-wrapper" style="display: inline-flex; align-items: center; gap: 8px;">
                        <button type="button" class="checkout-qty-minus">-</button>
                        <span class="checkout-qty-display" style="min-width: 30px; text-align: center; font-weight: bold; font-size: 14px;">${currentQty}</span>
                        <button type="button" class="checkout-qty-plus">+</button>
                    </div>
                `;
                
                // Replace the entire quantity element with our controls
                $quantityElement.replaceWith(quantityHTML);
                $row.data('product-key', productKey);
            });
        }
        
        // AJAX function to update quantity
        function updateQuantity(productKey, newQty, $row) {
            // Show loading state
            var $wrapper = $row.find('.checkout-quantity-wrapper');
            $wrapper.addClass('updating');
            
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: {
                    action: 'update_checkout_quantity',
                    product_key: productKey,
                    quantity: newQty,
                    nonce: '<?php echo wp_create_nonce('update_checkout_quantity_nonce'); ?>'
                },
                success: function(response) {
                    $wrapper.removeClass('updating');
                    
                    if (response.success) {
                        // Update the display
                        $wrapper.find('.checkout-qty-display').text(newQty);
                        // Trigger checkout update
                        $(document.body).trigger('update_checkout');
                    } else {
                        // Reload as fallback
                        location.reload();
                    }
                },
                error: function(xhr, status, error) {
                    $wrapper.removeClass('updating');
                    location.reload();
                }
            });
        }
        
        // Initialize immediately when DOM is ready
        addQuantityButtons();
        
        // Also initialize when window is fully loaded (for any dynamic content)
        $(window).on('load', function() {
            addQuantityButtons();
        });
        
        // Re-setup when checkout updates
        $(document.body).on('updated_checkout', function() {
            addQuantityButtons();
        });
        
        // Handle button clicks
        $(document).on('click', '.checkout-qty-plus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $wrapper = $(this).closest('.checkout-quantity-wrapper');
            var $row = $wrapper.closest('tr.cart_item');
            var productKey = $row.data('product-key');
            var $display = $wrapper.find('.checkout-qty-display');
            var currentQty = parseInt($display.text());
            var newQty = currentQty + 1;
            
            if (productKey) {
                $display.text(newQty);
                updateQuantity(productKey, newQty, $row);
            }
        });
        
        $(document).on('click', '.checkout-qty-minus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $wrapper = $(this).closest('.checkout-quantity-wrapper');
            var $row = $wrapper.closest('tr.cart_item');
            var productKey = $row.data('product-key');
            var $display = $wrapper.find('.checkout-qty-display');
            var currentQty = parseInt($display.text());
            var newQty = currentQty - 1;
            
            if (newQty > 0 && productKey) {
                $display.text(newQty);
                updateQuantity(productKey, newQty, $row);
            }
        });
    });
    </script>
    
    <style>
    .checkout-quantity-wrapper button:hover {
        background-color: var(--order-summary-totals-color)!important;
        border-color: #978241 !important;
    }
    .checkout-quantity-wrapper button:active {
        background-color: var(--order-summary-totals-color) !important;
    }
    .checkout-quantity-wrapper.updating {
        opacity: 0.6;
        pointer-events: none;
    }
	.checkout-quantity-wrapper{
	border: 1px solid;
    border-radius: 4px;
    border-color: var(--e-global-color-primary);
	margin: 5px 0px;
		}
		.checkout-qty-minus{
			width: 28px; 
			height: 28px; 
			cursor: pointer; 
			font-size: 20px; 
			display: flex; 
			align-items: center; 
			justify-content: center;
			border-radius: 0px!important;
		}
		.checkout-qty-plus{
			width: 28px; 
			height: 28px; 
			cursor: pointer; 
			font-size: 20px; 
			display: flex; 
			align-items: center; 
			justify-content: center;
			border-radius: 0px!important;
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
