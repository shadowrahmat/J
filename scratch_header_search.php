<?php
require 'wp-load.php';
$m=get_post_meta(71,'_elementor_data',true);
if(is_array($m)) $m=json_encode($m, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
else $m=json_encode(json_decode($m,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
$p=strpos($m,'681c745');
echo substr($m, max(0,$p-800), 9000);
echo "\n\n=== check products have thumbs ===\n";
global $wpdb;
$pfx=$wpdb->prefix;
$rows=$wpdb->get_results("SELECT ID, post_title FROM {$pfx}posts WHERE post_type='product' AND post_status='publish' LIMIT 3", ARRAY_A);
foreach($rows as $r){
  $id=$r['ID'];
  $thumb=get_post_thumbnail_id($id);
  $url=$thumb?wp_get_attachment_url($thumb):'NO_THUMB';
  echo "product $id {$r['post_title']} thumb=$thumb url=$url\n";
  $prod=wc_get_product($id);
  if($prod) echo "  wc image: ".json_encode($prod->get_image_id())." type=".$prod->get_type()."\n";
}
echo "\n=== template 1204 full ===\n";
$mm=get_post_meta(1204,'_elementor_data',true);
if(is_array($mm)) $mm=json_encode($mm, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
else $mm=json_encode(json_decode($mm,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo substr($mm,0,8000);
