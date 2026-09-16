<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Match cart to Home's thin line style: use Themify ti-shopping-cart (line), same library as Home/Search
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
// ti-shopping-cart is the line cart in Themify
if(strpos($j,'"icon":"custom"')!==false){
  $j=str_replace('"menu_icon_svg":{"value":"fas fa-shopping-cart","library":"fa-solid"}','"menu_icon_svg":{"value":" ti-shopping-cart","library":"skb_cife-themify-icon"}',$j);
  update_post_meta(55,'_elementor_data', json_decode($j,true));
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=55 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-55.css');
  echo "55 cart -> ti-shopping-cart (themify, matches ti-home) DONE\n";
} else {
  echo "custom icon pattern not found\n";
}
$m2=get_post_meta(55,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'0133b7d'); echo substr($m2,$p-100,900)."\n";
