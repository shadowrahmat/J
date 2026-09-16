<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
if(strpos($j,'"icon":"cart-solid"')!==false){
  $j=str_replace('"icon":"cart-solid","show_subtotal":""','"icon":"custom","show_subtotal":"","menu_icon_svg":{"value":"fas fa-shopping-cart","library":"fa-solid"}',$j);
  update_post_meta(55,'_elementor_data', json_decode($j,true));
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=55 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-55.css');
  echo "fixed via string replace\n";
} elseif(strpos($j,'"icon":"custom"')!==false){
  echo "already custom\n";
} else {
  echo "pattern not found\n";
  $p=strpos($j,'0133b7d'); echo substr($j,$p-300,1000)."\n";
}
$m2=get_post_meta(55,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'0133b7d'); echo substr($m2,$p-200,1400)."\n";
echo "DONE\n";
