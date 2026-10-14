<?php defined('ABSPATH')||exit;do_action('woocommerce_before_checkout_form',$checkout);
if(!$checkout->is_registration_enabled()&&$checkout->is_registration_required()&&!is_user_logged_in()){echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message',__('You must be logged in to checkout.','woocommerce')));return;}
?>
<form name="checkout" method="post" class="checkout woocommerce-checkout jop-layout jop-checkout-layout" action="<?php echo esc_url(wc_get_checkout_url());?>" enctype="multipart/form-data" aria-label="Checkout">
<div class="jop-checkout-left">
<section class="jop-form-panel"><div class="jop-section-title"><span class="jop-section-number">01</span><div><h2>Delivery details</h2><p>Where should we send your fishing gear?</p></div></div>
<?php do_action('woocommerce_checkout_before_customer_details');?><div class="col2-set" id="customer_details"><div class="col-1"><?php do_action('woocommerce_checkout_billing');?></div><div class="col-2"><?php do_action('woocommerce_checkout_shipping');?></div></div><?php do_action('woocommerce_checkout_after_customer_details');?></section>
<?php woocommerce_checkout_payment();?></div>
<aside class="jop-side"><section class="jop-summary jop-checkout-summary"><div class="jop-summary-title jop-order-heading"><div><span class="jop-eyebrow">COMING YOUR WAY</span><h2>Your order</h2></div><a href="<?php echo esc_url(wc_get_cart_url());?>">Edit cart ↗</a></div>
<?php do_action('woocommerce_checkout_before_order_review_heading');do_action('woocommerce_checkout_before_order_review');?>
<div id="order_review" class="woocommerce-checkout-review-order"><?php remove_action('woocommerce_checkout_order_review','woocommerce_checkout_payment',20);do_action('woocommerce_checkout_order_review');add_action('woocommerce_checkout_order_review','woocommerce_checkout_payment',20);juhani_order_submit();?></div>
<?php do_action('woocommerce_checkout_after_order_review');?></section><?php echo juhani_order_support(true);?></aside>
</form>
<?php do_action('woocommerce_after_checkout_form',$checkout);?>
