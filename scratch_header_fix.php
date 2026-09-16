<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;

// Dump full 681c745 settings
$m=get_post_meta(71,'_elementor_data',true);
if(is_array($m)) $m=json_encode($m);
$j=json_decode($m,true);
function findW(&$els,$id){
  foreach($els as &$c){
    if(($c['id']??'')===$id) return $c;
    if(!empty($c['elements'])){
      $r=findW($c['elements'],$id);
      if($r) return $r;
    }
  }
  return null;
}
$found=null;
function search(&$arr,$tid){
  global $found;
  foreach($arr as &$a){
    if(($a['id']??'')==='681c745'){ $found=&$a; return true; }
    if(!empty($a['elements']) && search($a['elements'],$tid)) return true;
  }
  return false;
}
search($j,'681c745');
echo "=== current 681c745 settings ===\n";
echo json_encode($found['settings']??[], JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
echo "widgetType=".$found['widgetType']."\n";

// Check template 1204 image widget details
$m2=get_post_meta(1204,'_elementor_data',true);
if(is_array($m2)) $mj=json_encode($m2);
else $mj=$m2;
$jj=json_decode($mj,true);
echo "\n=== 1204 widgets ===\n";
echo json_encode($jj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";

// Fix 681c745: switch to template 134, enable live_results, set width
echo "\n=== FIXING header search ===\n";
$changed=false;
$origJson=is_array(get_post_meta(71,'_elementor_data',true))?json_encode(get_post_meta(71,'_elementor_data',true)):get_post_meta(71,'_elementor_data',true);
$newJson=$origJson;

// Add/replace live_results yes, template_id 134, results width
// Ensure these fields exist in widget 681c745
$inject = '"search_input_placeholder_text":"Search Product....."';
if(strpos($newJson,'"live_results"')===false){
  $newJson=str_replace($inject, '"search_input_placeholder_text":"Search Product.....","live_results":"yes"', $newJson);
  echo "added live_results yes\n"; $changed=true;
} else if(strpos($newJson,'"live_results":"yes"')===false){
  $newJson=str_replace('"live_results":"no"','"live_results":"yes"',$newJson);
  echo "switched live_results to yes\n"; $changed=true;
}
if(strpos($newJson,'"template_id":"1204"')!==false){
  $newJson=str_replace('"template_id":"1204"','"template_id":134',$newJson);
  echo "template 1204 -> 134\n"; $changed=true;
}
if(strpos($newJson,'"template_id":134')===false && strpos($newJson,'"template_id":"134"')===false){
  // try numeric form
  if(strpos($newJson,'"template_id":1204')!==false){
    $newJson=str_replace('"template_id":1204','"template_id":134',$newJson);
    echo "template numeric 1204 ->134\n"; $changed=true;
  }
}
// Ensure results custom width 480 for header dropdown
if(strpos($newJson,'"results_is_custom_width"')===false){
  $newJson=str_replace('"enable_loader":"yes"','"enable_loader":"yes","results_is_custom_width":"yes","results_custom_width":{"unit":"px","size":480,"sizes":[]},"results_max_height":{"unit":"px","size":420,"sizes":[]}',$newJson);
  echo "added results custom width 480\n"; $changed=true;
} else {
  if(strpos($newJson,'"results_custom_width":{"unit":"px","size":576')!==false){
    $newJson=str_replace('"results_custom_width":{"unit":"px","size":576','"results_custom_width":{"unit":"px","size":480',$newJson); $changed=true; echo "width 576->480\n";
  }
  if(strpos($newJson,'"results_max_height":{"unit":"px","size":380')!==false){
    $newJson=str_replace('"results_max_height":{"unit":"px","size":380','"results_max_height":{"unit":"px","size":420',$newJson); $changed=true; echo "height 380->420\n";
  }
}
// Ensure search_query_post_type product and include terms
if(strpos($newJson,'"search_query_post_type":"product"')===false){
  $newJson=str_replace('"search_query_post_type":"product"','',$newJson); // clean
  $newJson=str_replace('"live_results":"yes"','"live_results":"yes","search_query_post_type":"product","search_query_include":["terms"]',$newJson);
  echo "added product query\n"; $changed=true;
}

if($changed){
  $arr=json_decode($newJson,true);
  update_post_meta(71,'_elementor_data',$arr);
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=71 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-71.css');
  echo "71 updated and css cleared\n";
} else {
  echo "71 no change needed\n";
}

// Also fix template 1204's image widget to use theme-post-featured-image for robustness (keep as fallback)
echo "\n=== FIXING 1204 image widget ===\n";
$origM2 = is_array(get_post_meta(1204,'_elementor_data',true))?json_encode(get_post_meta(1204,'_elementor_data',true)):get_post_meta(1204,'_elementor_data',true);
$newM2=$origM2;
// Replace image widget with theme-post-featured-image if it's using woocommerce-product-image
if(strpos($newM2,'"widgetType":"image"')!==false && strpos($newM2,'woocommerce-product-image')!==false){
  $newM2=str_replace('"widgetType":"image"','"widgetType":"theme-post-featured-image"',$newM2);
  $newM2=str_replace('woocommerce-product-image-tag','post-featured-image',$newM2);
  $newM2=str_replace('"image_size":"full"','"image_size":"woocommerce_thumbnail"',$newM2);
  echo "1204 widgetType image -> theme-post-featured-image\n";
  $arr2=json_decode($newM2,true);
  update_post_meta(1204,'_elementor_data',$arr2);
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=1204 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-1204.css');
  @unlink('wp-content/uploads/elementor/css/loop-1204.css');
  echo "1204 fixed\n";
} else {
  echo "1204 no image fix needed\n";
}

echo "DONE\n";
