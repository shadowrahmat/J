<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
foreach([1214,1213,1212,1207] as $id){
  $t=get_post_type($id); $title=get_the_title($id); $st=get_post_status($id);
  echo "=== $id: $t | $title | $st ===\n";
  $m=get_post_meta($id,'_elementor_data',true);
  if(is_array($m)) $j=json_encode($m, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES); else { $j=json_decode($m,true); $j=json_encode($j, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES); }
  echo substr($j,0,7000)."\n\n";
  echo "meta _elementor_data len=".strlen(json_encode($m))."\n";
  $css=@file_get_contents("wp-content/uploads/elementor/css/post-$id.css");
  echo "css ".($css?substr($css,0,1200):"no css")."\n\n---\n\n";
}
