<?php
require 'wp-load.php';
global $wpdb;
$pfx = $wpdb->prefix;
echo "prefix=$pfx\n";
$ids = [1208,1207,1204,57,134,1142,141];
foreach($ids as $id){
  $t=get_post_type($id);
  $title=get_the_title($id);
  $status=get_post_status($id);
  echo "\n=== $id: $t | $title | $status ===\n";
  $meta=get_post_meta($id,'_elementor_data',true);
  if(is_array($meta)) $meta=json_encode($meta);
  if(!$meta){ echo "no elementor_data\n"; continue; }
  echo "len=".strlen($meta)."\n";
  echo substr($meta,0,12000)."\n";
  if(strlen($meta)>12000) echo "\n...truncated...\n";
}
echo "\n=== CSS post-57 ===\n";
echo @file_get_contents('wp-content/uploads/elementor/css/post-57.css');
echo "\n=== CSS post-1208 ===\n";
echo @file_get_contents('wp-content/uploads/elementor/css/post-1208.css');
echo "\n=== loop template check template_id in 1208 ===\n";
$m=get_post_meta(1208,'_elementor_data',true);
if(is_array($m)) $m=json_encode($m);
if($m && strpos($m,'template_id')!==false){
  preg_match_all('/template_id[^0-9]*([0-9]+)/',$m,$mm);
  print_r($mm);
}
