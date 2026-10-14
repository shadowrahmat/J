<?php
/** Plugin Name: Juhani Order Design
 * Description: Approved cart, checkout and cart drawer design using live WooCommerce data.
 */
if (!defined('ABSPATH')) exit;
define('JUHANI_ORDER_DESIGN_DIR', __DIR__ . '/juhani-order-design');

add_action('wp_enqueue_scripts', function () {
    $base=content_url('/mu-plugins/juhani-order-design/assets/');
    if (is_cart() || is_checkout()) wp_enqueue_style('juhani-order-flow',$base.'order-flow.css',[],filemtime(JUHANI_ORDER_DESIGN_DIR.'/assets/order-flow.css'));
    wp_enqueue_style('juhani-order-drawer',$base.'order-drawer.css',[],filemtime(JUHANI_ORDER_DESIGN_DIR.'/assets/order-drawer.css'));
    wp_enqueue_script('juhani-order-design',$base.'order-design.js',['jquery','wc-cart-fragments'],filemtime(JUHANI_ORDER_DESIGN_DIR.'/assets/order-design.js'),true);
    wp_localize_script('juhani-order-design','juhaniOrderDesign',['ajax'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('juhani-order-quantity'),'cart'=>is_cart(),'checkout'=>is_checkout()&&!is_order_received_page()]);
},100);
add_filter('woocommerce_locate_template',function($template,$name){
    $map=['cart/cart.php'=>'cart.php','cart/cart-totals.php'=>'cart-totals.php','cart/cart-empty.php'=>'cart-empty.php','cart/proceed-to-checkout-button.php'=>'proceed-to-checkout.php','checkout/form-checkout.php'=>'checkout.php','checkout/review-order.php'=>'review-order.php','checkout/payment.php'=>'payment.php'];
    return isset($map[$name])?JUHANI_ORDER_DESIGN_DIR.'/templates/'.$map[$name]:$template;
},110,2);

function juhani_order_shop_url(){ $page=get_page_by_path('shop');return $page&&$page->post_status==='publish'?get_permalink($page):home_url('/shop/'); }
function juhani_order_svg($icon='arrow'){
    $paths=['arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>','close'=>'<path d="m6 6 12 12M18 6 6 18"/>','remove'=>'<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13M10 10v7m4-7v7"/>','bag'=>'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>','check'=>'<rect x="4" y="4" width="16" height="16" rx="3"/><path d="m8 12 3 3 5-6"/>'];
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">'.($paths[$icon]??$paths['arrow']).'</svg>';
}
function juhani_order_specs($item){
    $specs=[];$product=$item['data'];
    foreach(($item['variation']??[]) as $key=>$value){$taxonomy=str_replace('attribute_','',$key);$term=taxonomy_exists($taxonomy)?get_term_by('slug',$value,$taxonomy):false;$display=$term&&!is_wp_error($term)?$term->name:$value;if($display!==''&&!wc_is_attribute_in_product_name($display,$product->get_name()))$specs[]=['key'=>wc_attribute_label($taxonomy,$product),'value'=>$display];}
    return apply_filters('woocommerce_get_item_data',$specs,$item);
}
function juhani_order_spec_chips($item,$class='jop-specs'){
    $chips=[];$details=[];foreach(juhani_order_specs($item) as $spec){if(!empty($spec['hidden']))continue;$label=wp_strip_all_tags($spec['key']??$spec['name']??'');$value=wp_strip_all_tags($spec['display']??$spec['value']??'');if(preg_match('/haat|suta|rock/i',$label))$chips[]=$value.' '.(stripos($label,'rock')!==false?'kg rock':$label);else $details[]=$value;}
    echo '<div class="'.esc_attr($class).'">';foreach($chips as $chip)echo '<span>'.esc_html($chip).'</span>';echo '</div>';
    if($class==='jop-specs'&&$details)echo '<p class="jop-item-details">'.esc_html(implode(' · ',$details)).'</p>';
}
function juhani_order_support($checkout=false){return '<div class="jop-support"><span class="jop-support-icon">?</span><div><strong>'.($checkout?'A question before you order?':'Not sure about your net?').'</strong><p>'.($checkout?'Let’s help you get it right.':'We’re here to help you choose.').'</p><a href="tel:+8801889164315">Talk to Juhani ↗</a></div></div>';}
add_shortcode('juhani_order_header',function(){
    $checkout=is_checkout();$logo=wp_get_attachment_image_url(get_theme_mod('custom_logo'),'full');
    return '<div class="jop-top"><div class="jop-wrap"><span>Good gear. Ready for your next catch.</span><span>Need help? <a href="tel:+8801889164315">+880 1889 164315</a></span></div></div><header class="jop-header jop-wrap"><a class="jop-logo" href="'.esc_url(home_url('/')).'"><img src="'.esc_url($logo).'" alt="Juhani Nets &amp; Fishing"></a><nav class="jop-steps" aria-label="Shopping steps"><a class="'.($checkout?'is-complete':'is-current').'" href="'.esc_url(wc_get_cart_url()).'"><span>'.($checkout?'✓':'01').'</span> Your cart</a><i></i><a class="'.($checkout?'is-current':'').'" href="'.esc_url(wc_get_checkout_url()).'"><span>02</span> Checkout</a><i></i><span class="jop-step-muted"><b>03</b> All set</span></nav><a class="jop-header-help" href="tel:+8801889164315">Need a hand? ↗</a></header>';
});
add_shortcode('juhani_order_back',function(){return '<a class="jop-back" href="'.esc_url(is_checkout()?wc_get_cart_url():juhani_order_shop_url()).'">← '.(is_checkout()?'Back to your cart':'Continue shopping').'</a>';});
add_shortcode('juhani_order_count',function(){return is_checkout()?'<span class="jop-checkout-tag">'.juhani_order_svg('check').' Simple. Cash on delivery.</span>':'<span class="jop-count juhani-order-page-count">'.esc_html(WC()->cart?WC()->cart->get_cart_contents_count():0).' items in your cart</span>';});
add_shortcode('juhani_order_tagline',function(){return '<div class="jop-bottom-note"><span>GOOD GEAR FOR EVERY CATCH.</span><p>Made for life on the water.</p></div>';});
add_shortcode('juhani_order_footer',function(){return '<footer class="jop-footer jop-wrap"><span>© '.date('Y').' Juhani. All rights reserved.</span><div><a href="'.esc_url(home_url('/shipping-policy/')).'">Shipping policy</a><a href="'.esc_url(home_url('/privacy-policy/')).'">Privacy policy</a><a href="'.esc_url(home_url('/refund-returns/')).'">Return policy</a></div><a href="'.esc_url(juhani_order_shop_url()).'">Back to the shop →</a></footer>';});

add_filter('woocommerce_checkout_fields',function($fields){
    if(isset($fields['billing']['billing_full_name'])){$fields['billing']['billing_full_name']['label']='আপনার নাম';$fields['billing']['billing_full_name']['placeholder']='আপনার পুরো নাম লিখুন';$fields['billing']['billing_full_name']['class']=['form-row-first'];}
    if(isset($fields['billing']['billing_phone'])){$fields['billing']['billing_phone']['label']='মোবাইল নম্বর';$fields['billing']['billing_phone']['class']=['form-row-last'];$fields['billing']['billing_phone']['description']='অর্ডারের বিষয়ে এই নম্বরে যোগাযোগ করা হবে।';}
    if(isset($fields['billing']['billing_full_address'])){$fields['billing']['billing_full_address']['label']='সম্পূর্ণ ঠিকানা';$fields['billing']['billing_full_address']['placeholder']='বাড়ি / রাস্তা, এলাকা, থানা ও জেলা';$fields['billing']['billing_full_address']['description']='সঠিক ঠিকানা দিলে আপনার কাছে পৌঁছানো সহজ হবে।';}
    if(isset($fields['order']['order_comments'])){$fields['order']['order_comments']['label']='অতিরিক্ত নির্দেশনা';$fields['order']['order_comments']['placeholder']='ডেলিভারি সম্পর্কে কিছু জানাতে চাইলে লিখুন';}
    return $fields;
},30);
function juhani_order_submit(){wc_get_template('juhani-order-submit.php',[], '', JUHANI_ORDER_DESIGN_DIR.'/templates/');}
add_filter('woocommerce_update_order_review_fragments',function($fragments){ob_start();juhani_order_submit();$fragments['.juhani-checkout-submit']=ob_get_clean();return $fragments;});
function juhani_order_drawer_content(){ob_start();include JUHANI_ORDER_DESIGN_DIR.'/templates/drawer.php';return ob_get_clean();}
add_action('wp_footer',function(){if(!function_exists('WC')||!WC()->cart)return;echo '<dialog class="juhani-cart-drawer jdp-drawer" aria-labelledby="juhani-order-drawer-title">'.juhani_order_drawer_content().'</dialog>';},15);
add_filter('woocommerce_add_to_cart_fragments',function($fragments){if(WC()->cart){$fragments['div.juhani-order-drawer-content']=juhani_order_drawer_content();$fragments['span.juhani-order-page-count']='<span class="jop-count juhani-order-page-count">'.esc_html(WC()->cart->get_cart_contents_count()).' items in your cart</span>';}return $fragments;});
add_action('wp_ajax_juhani_order_quantity','juhani_order_quantity');
add_action('wp_ajax_nopriv_juhani_order_quantity','juhani_order_quantity');
function juhani_order_quantity(){
    check_ajax_referer('juhani-order-quantity','nonce');
    $key=sanitize_text_field(wp_unslash($_POST['cart_item_key']??''));$cart=WC()->cart;if(!$cart)wp_send_json_error(['message'=>'Cart unavailable'],400);$item=$cart->get_cart_item($key);if(!$item)wp_send_json_error(['message'=>'This item is no longer in your cart.'],400);
    $qty=wc_stock_amount(wp_unslash($_POST['quantity']??0));$product=$item['data'];
    if($qty<0)wp_send_json_error(['message'=>'Invalid quantity'],400);
    if($qty>0){$max=$product->get_max_purchase_quantity();if(($product->is_sold_individually()&&$qty>1)||($max>0&&$qty>$max)||!$product->has_enough_stock($qty)||!apply_filters('woocommerce_update_cart_validation',true,$key,$item,$qty))wp_send_json_error(['message'=>'That quantity is not available.'],400);$cart->set_quantity($key,$qty,false);}else $cart->remove_cart_item($key);
    $cart->calculate_totals();$cart->maybe_set_cart_cookies();WC_AJAX::get_refreshed_fragments();
}
