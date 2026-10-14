<?php
defined('ABSPATH')||exit;
do_action('woocommerce_before_cart');
?>
<div class="jop-layout">
<form class="woocommerce-cart-form juhani-cart-form jop-cart-panel" action="<?php echo esc_url(wc_get_cart_url());?>" method="post">
<?php do_action('woocommerce_before_cart_table');?>
<div class="jop-panel-heading"><h2>Your fishing gear</h2><span>PRICE / QUANTITY</span></div>
<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents juhani-cart-table"><tbody>
<?php do_action('woocommerce_before_cart_contents');foreach(WC()->cart->get_cart() as $key=>$item):
$product=apply_filters('woocommerce_cart_item_product',$item['data'],$item,$key);
if(!$product||!$product->exists()||$item['quantity']<=0||!apply_filters('woocommerce_cart_item_visible',true,$item,$key))continue;
$name=apply_filters('woocommerce_cart_item_name',$product->get_name(),$item,$key);$url=apply_filters('woocommerce_cart_item_permalink',$product->is_visible()?$product->get_permalink($item):'',$item,$key);
?>
<tr class="woocommerce-cart-form__cart-item cart_item juhani-cart-row"><td colspan="6"><article class="jop-item">
<a class="jop-item-image" href="<?php echo esc_url($url);?>"><?php echo apply_filters('woocommerce_cart_item_thumbnail',$product->get_image('woocommerce_thumbnail'),$item,$key);?></a>
<div><div class="jop-item-heading"><div><span class="jop-item-category">CAST NET / FISHING GEAR</span><h3><a href="<?php echo esc_url($url);?>"><?php echo wp_kses_post($name);?></a></h3><?php if($product->backorders_require_notification()&&$product->is_on_backorder($item['quantity']))echo '<p>'.esc_html__('Available on backorder','woocommerce').'</p>';?></div>
<?php echo apply_filters('woocommerce_cart_item_remove_link',sprintf('<a href="%s" class="remove juhani-remove-btn jop-remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">×</a>',esc_url(wc_get_cart_remove_url($key)),esc_attr('Remove '.wp_strip_all_tags($name)),esc_attr($item['product_id']),esc_attr($product->get_sku())),$key);?></div>
<?php juhani_order_spec_chips($item);?>
<div class="jop-item-bottom"><div class="jop-quantity"><button type="button" data-cart-step="-1" aria-label="Decrease quantity">−</button><?php
$quantity=woocommerce_quantity_input(['input_name'=>"cart[{$key}][qty]",'input_value'=>$item['quantity'],'min_value'=>0,'max_value'=>$product->is_sold_individually()?1:$product->get_max_purchase_quantity(),'product_name'=>$product->get_name()],$product,false);
echo apply_filters('woocommerce_cart_item_quantity',$quantity,$key,$item);
?><button type="button" data-cart-step="1" aria-label="Increase quantity">+</button></div><div class="jop-item-price"><strong><?php echo apply_filters('woocommerce_cart_item_subtotal',WC()->cart->get_product_subtotal($product,$item['quantity']),$item,$key);?></strong><small><?php echo apply_filters('woocommerce_cart_item_price',WC()->cart->get_product_price($product),$item,$key);?> each</small></div></div>
</div></article></td></tr>
<?php endforeach;do_action('woocommerce_cart_contents');?>
<tr class="juhani-cart-actions"><td colspan="6" class="actions"><?php if(wc_coupons_enabled()):?><div class="coupon"><label class="screen-reader-text" for="coupon_code">Coupon code</label><input type="text" name="coupon_code" id="coupon_code" placeholder="Coupon code"><button class="button" type="submit" name="apply_coupon" value="Apply coupon">Apply coupon</button><?php do_action('woocommerce_cart_coupon');?></div><?php endif;?>
<button type="submit" class="button juhani-cart-update" name="update_cart" value="Update Cart">Update cart</button><?php do_action('woocommerce_cart_actions');wp_nonce_field('woocommerce-cart','woocommerce-cart-nonce');?></td></tr>
<?php do_action('woocommerce_after_cart_contents');?></tbody></table>
<?php do_action('woocommerce_after_cart_table');?>
<div class="jop-cart-foot"><span class="jop-small-icon">↺</span><div><strong>A little peace of mind.</strong><p>Check your sizes and details before you continue.</p></div><a href="<?php echo esc_url(home_url('/refund-returns/'));?>">Return policy ↗</a></div>
</form>
<aside class="jop-side"><?php do_action('woocommerce_before_cart_collaterals');?><div class="cart-collaterals juhani-cart-collaterals"><?php do_action('woocommerce_cart_collaterals');?></div><?php echo juhani_order_support();?></aside>
</div>
<?php do_action('woocommerce_after_cart');?>
