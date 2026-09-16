<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Footer search uses popup 1214 with template 1212, results width narrow + image widget is generic image with woocommerce-product-image-tag
// Fix 1214 widget b579efa: widen results, set template correctly, ensure product query
$m=get_post_meta(1214,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$orig=$j;
// Ensure results custom width 720 and max height 480
if(strpos($j,'"results_is_custom_width"')===false){
  $j=str_replace('"results_max_height_mobile"','"results_is_custom_width":"yes","results_custom_width":{"unit":"px","size":720,"sizes":[]},"results_max_height":{"unit":"px","size":480,"sizes":[]},"results_max_height_mobile"',$j);
  echo "1214 added width 720\n";
} else {
  if(strpos($j,'"results_custom_width"')!==false){
    $j=preg_replace('/"results_custom_width":\{"unit":"px","size":[0-9]+/', '"results_custom_width":{"unit":"px","size":720', $j);
    echo "1214 width -> 720\n";
  }
  $j=preg_replace('/"results_max_height":\{"unit":"px","size":[0-9]+/', '"results_max_height":{"unit":"px","size":480', $j);
  echo "1214 height -> 480\n";
}
// Ensure search_query_post_type product
if(strpos($j,'"search_query_post_type":"product"')===false){
  $j=str_replace('"live_results":"yes"','"live_results":"yes","search_query_post_type":"product","search_query_include":["terms"]',$j);
  echo "1214 added product query\n";
}
// Ensure template 1212 stays (it's valid) - no change needed, 1212 has proper image

if($j!==$orig){
  update_post_meta(1214,'_elementor_data', json_decode($j,true));
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=1214 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-1214.css');
  echo "1214 updated and css cleared\n";
} else echo "1214 no change\n";

// Fix template 1212: image widget currently widgetType=image with woocommerce-product-image-tag works but ensure theme-post-featured-image for consistency
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $jj=json_encode($mm); else $jj=$mm;
$oo=$jj;
if(strpos($jj,'"widgetType":"image"')!==false && strpos($jj,'woocommerce-product-image')!==false){
  // keep as is for now - image widget with woocommerce-product-image works in search loop context
  // Just ensure it has link to product
  echo "1212 image widget uses woocommerce-product-image - valid for search result context, keeping\n";
}

// Update mu-plugin to cover 1214 as well
$file='wp-content/mu-plugins/juhani-search-width-fix.php';
$c=file_get_contents($file);
if(strpos($c,'1214')===false){
  $c=str_replace('#elementor-popup-modal-1207','#elementor-popup-modal-1214, #elementor-popup-modal-1207',$c);
  $c=str_replace('.elementor-1207 .e-search-results-container','.elementor-1214 .e-search-results-container, .elementor-1207 .e-search-results-container',$c);
  file_put_contents($file,$c);
  echo "mu-plugin updated to cover 1214\n";
} else echo "mu-plugin already covers 1214\n";

$m2=get_post_meta(1214,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'b579efa'); echo substr($m2,$p-100,900)."\n";
echo "DONE\n";
