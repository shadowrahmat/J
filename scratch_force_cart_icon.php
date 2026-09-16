<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Force footer cart widget to a solid unicode cart icon (bypasses eicons font)
// Change cart-light -> cart-solid which maps to fa solid (more reliable)
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$orig=$j;
$j=str_replace('"icon":"cart-light"','"icon":"cart-solid"',$j);
if($j!==$orig){
  update_post_meta(55,'_elementor_data', json_decode($j,true));
  $wpdb->query("DELETE FROM ef_postmeta WHERE post_id=55 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-55.css');
  echo "55 footer cart-light -> cart-solid DONE\n";
} else echo "no change\n";
$m2=get_post_meta(55,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'0133b7d'); echo substr($m2,$p-100,900)."\n";
echo "DONE\n";
