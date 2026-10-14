<?php defined('ABSPATH')||exit;$label=apply_filters('woocommerce_order_button_text',__('Place order','woocommerce'));?>
<div class="juhani-checkout-submit form-row place-order">
<noscript><p>Update totals before placing your order.</p><button type="submit" name="woocommerce_checkout_update_totals" value="Update totals">Update totals</button></noscript>
<?php do_action('woocommerce_review_order_before_submit');
echo apply_filters('woocommerce_order_button_html','<button type="submit" class="button alt jop-button" name="woocommerce_checkout_place_order" id="place_order" value="'.esc_attr($label).'" data-value="'.esc_attr($label).'"><span>'.esc_html($label).'</span><span class="juhani-place-order-total">'.WC()->cart->get_total().' →</span></button>');
do_action('woocommerce_review_order_after_submit');wc_get_template('checkout/terms.php');wp_nonce_field('woocommerce-process_checkout','woocommerce-process-checkout-nonce');
?>
</div>
