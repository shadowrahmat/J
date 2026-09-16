<?php
require 'wp-load.php';
global $wpdb;
$pfx = $wpdb->prefix;

// --- Check 1341 existence ---
$id = 1341;
$t = $wpdb->get_row($wpdb->prepare("SELECT ID, post_title, post_type, post_status FROM {$pfx}posts WHERE ID=%d", $id), ARRAY_A);
echo "1341 row: ".json_encode($t)."\n";
if(!$t) echo "=> 1341 DOES NOT EXIST (ghost template)\n\n";

// --- Fix 1208 popup search widget: template_id 1341 -> 134, width 576 -> 720, mobile 340 -> 680 fallback ---
$meta = get_post_meta(1208, '_elementor_data', true);
$json = is_array($meta) ? json_encode($meta) : $meta;
$orig = $json;
$changed = false;

if(strpos($json,'"template_id":1341')!==false){
  $json = str_replace('"template_id":1341','"template_id":134',$json);
  $changed = true;
  echo "1208: template_id 1341 -> 134 FIXED\n";
} else {
  echo "1208: template_id 1341 not found (already fixed or different)\n";
}
// widen results
if(strpos($json,'"results_custom_width":{"unit":"px","size":576')!==false){
  $json = str_replace('"results_custom_width":{"unit":"px","size":576','"results_custom_width":{"unit":"px","size":720',$json);
  $changed = true;
  echo "1208: results_custom_width 576 -> 720\n";
}
if(strpos($json,'"results_custom_width_mobile":{"unit":"px","size":340')!==false){
  $json = str_replace('"results_custom_width_mobile":{"unit":"px","size":340','"results_custom_width_mobile":{"unit":"px","size":380',$json);
  $changed = true;
  echo "1208: mobile width 340 -> 380\n";
}
// also increase max-height a bit for better grid
if(strpos($json,'"results_max_height":{"unit":"px","size":380')!==false){
  $json = str_replace('"results_max_height":{"unit":"px","size":380','"results_max_height":{"unit":"px","size":480',$json);
  $changed = true;
  echo "1208: max_height 380 -> 480\n";
}

if($changed){
  $arr = json_decode($json,true);
  update_post_meta(1208, '_elementor_data', $arr);
  echo "1208 _elementor_data updated & saved as array\n";
  // delete elementor css cache to regenerate
  $wpdb->query($wpdb->prepare("DELETE FROM {$pfx}postmeta WHERE post_id=%d AND meta_key='_elementor_css'", 1208));
  @unlink('wp-content/uploads/elementor/css/post-1208.css');
  echo "1208 css cache cleared\n";
} else {
  echo "1208: no change\n";
}

echo "\n--- Fix header search widget 71/681c745 to product-only ---\n";
$meta71 = get_post_meta(71, '_elementor_data', true);
$json71 = is_array($meta71) ? json_encode($meta71) : $meta71;
$changed71 = false;
if(strpos($json71,'"search_query_post_type"')===false && strpos($json71,'"id":"681c745"')!==false){
  // inject product post_type near search_input_placeholder_text
  $json71 = str_replace('"search_input_placeholder_text":"Search Product....."','"search_input_placeholder_text":"Search Product.....","search_query_post_type":"product","search_query_include":["terms"]',$json71);
  $changed71 = true;
  echo "71/681c745: injected search_query_post_type=product\n";
} else {
  echo "71/681c745: already has product query or not found\n";
}
if($changed71){
  $arr71 = json_decode($json71,true);
  update_post_meta(71, '_elementor_data', $arr71);
  $wpdb->query($wpdb->prepare("DELETE FROM {$pfx}postmeta WHERE post_id=%d AND meta_key='_elementor_css'", 71));
  @unlink('wp-content/uploads/elementor/css/post-71.css');
  echo "71 css cache cleared\n";
}

// Verify
echo "\n=== VERIFY 1208 ===\n";
$check = get_post_meta(1208,'_elementor_data',true);
if(is_array($check)) $check=json_encode($check);
echo substr($check, strpos($check,'2a92cc5f')-200, 1200)."\n";
echo "\n=== VERIFY 71 widget 681c745 ===\n";
$check71 = get_post_meta(71,'_elementor_data',true);
if(is_array($check71)) $check71=json_encode($check71);
$p=strpos($check71,'681c745'); echo substr($check71,$p-200, 900)."\n";
echo "\nDONE\n";
