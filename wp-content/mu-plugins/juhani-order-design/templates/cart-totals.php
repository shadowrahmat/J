<?php defined('ABSPATH')||exit;?>
<section class="cart_totals jop-summary <?php echo WC()->customer->has_calculated_shipping()?'calculated_shipping':'';?>">
<?php do_action('woocommerce_before_cart_totals');?>
<div class="jop-summary-title"><span class="jop-eyebrow">THE FINAL DETAILS</span><h2>Order summary</h2></div>
<table class="shop_table shop_table_responsive juhani-order-totals"><tbody>
<tr class="cart-subtotal"><th>Subtotal <small>(<?php echo esc_html(WC()->cart->get_cart_contents_count());?> items)</small></th><td><?php wc_cart_totals_subtotal_html();?></td></tr>
<?php foreach(WC()->cart->get_coupons() as $code=>$coupon):?><tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code));?>"><th><?php wc_cart_totals_coupon_label($coupon);?></th><td><?php wc_cart_totals_coupon_html($coupon);?></td></tr><?php endforeach;?>
<?php if(WC()->cart->needs_shipping()&&WC()->cart->show_shipping()):do_action('woocommerce_cart_totals_before_shipping');wc_cart_totals_shipping_html();do_action('woocommerce_cart_totals_after_shipping');elseif(WC()->cart->needs_shipping()):?><tr class="shipping"><th>Delivery</th><td><?php if('yes'===get_option('woocommerce_enable_shipping_calc'))woocommerce_shipping_calculator();else echo 'Calculated at checkout';?></td></tr><?php endif;?>
<?php foreach(WC()->cart->get_fees() as $fee):?><tr class="fee"><th><?php echo esc_html($fee->name);?></th><td><?php wc_cart_totals_fee_html($fee);?></td></tr><?php endforeach;?>
<?php if(wc_tax_enabled()&&!WC()->cart->display_prices_including_tax()):if(get_option('woocommerce_tax_total_display')==='itemized'):foreach(WC()->cart->get_tax_totals() as $tax):?><tr class="tax-rate"><th><?php echo esc_html($tax->label);?></th><td><?php echo wp_kses_post($tax->formatted_amount);?></td></tr><?php endforeach;else:?><tr class="tax-total"><th><?php echo esc_html(WC()->countries->tax_or_vat());?></th><td><?php wc_cart_totals_taxes_total_html();?></td></tr><?php endif;endif;?>
<?php do_action('woocommerce_cart_totals_before_order_total');?><tr class="order-total"><th>Total</th><td><?php wc_cart_totals_order_total_html();?></td></tr><?php do_action('woocommerce_cart_totals_after_order_total');?></tbody></table>
<div class="wc-proceed-to-checkout"><?php do_action('woocommerce_proceed_to_checkout');?></div><div class="jop-assurance"><?php echo juhani_order_svg('check');?> Cash on delivery available</div>
<?php do_action('woocommerce_after_cart_totals');?>
</section>
