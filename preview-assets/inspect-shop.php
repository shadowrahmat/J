<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/wp-load.php';
$shop=get_page_by_path('shop');
$products=wc_get_products(['status'=>'publish','limit'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
$data=['page'=>['id'=>$shop->ID,'title'=>$shop->post_title,'url'=>get_permalink($shop),'elementor_hash'=>hash('sha256',get_post_meta($shop->ID,'_elementor_data',true))],'products'=>[]];
foreach($products as$p){
 $attributes=[];foreach($p->get_attributes()as$key=>$a){$attributes[$key]=['label'=>wc_attribute_label($key),'values'=>$a->is_taxonomy()?wc_get_product_terms($p->get_id(),$key,['fields'=>'names']):$a->get_options()];}
 $data['products'][]=['id'=>$p->get_id(),'title'=>$p->get_name(),'url'=>$p->get_permalink(),'type'=>$p->get_type(),'price'=>$p->get_price(),'price_html'=>$p->get_price_html(),'stock'=>$p->get_stock_status(),'image'=>wp_get_attachment_image_url($p->get_image_id(),'large'),'categories'=>wp_get_post_terms($p->get_id(),'product_cat',['fields'=>'names']),'attributes'=>$attributes];
}
$data['categories']=get_terms(['taxonomy'=>'product_cat','hide_empty'=>true]);
file_put_contents(__DIR__.'/shop-source.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
