<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/wp-load.php';
$p=wc_get_product(570);if(!$p)throw new RuntimeException('Example product missing');
$attrs=[];foreach($p->get_attributes()as$key=>$a)$attrs[$key]=['label'=>wc_attribute_label($key),'values'=>$a->is_taxonomy()?wc_get_product_terms($p->get_id(),$key,['fields'=>'all']):$a->get_options()];
$variations=$p->is_type('variable')?$p->get_available_variations():[];
$data=['id'=>$p->get_id(),'title'=>$p->get_name(),'url'=>$p->get_permalink(),'price'=>$p->get_price_html(),'short_description'=>$p->get_short_description(),'description'=>$p->get_description(),'attributes'=>$attrs,'image_ids'=>array_values(array_unique(array_filter(array_merge([$p->get_image_id()],$p->get_gallery_image_ids())))),'variations'=>$variations];
file_put_contents(__DIR__.'/product-source.json',wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$data['variations']=['count'=>count($variations),'example'=>$variations[0]??null];echo wp_json_encode($data,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
