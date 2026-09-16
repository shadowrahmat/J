<?php
require 'wp-load.php';
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $m=json_encode($m);
echo "footer len=".strlen($m)."\n";
// find 34832f4 and 77a58cb and 3949973
foreach(['34832f4','77a58cb','3949973','0133b7d'] as $id){
  $p=strpos($m,$id);
  if($p!==false){ echo "\n=== $id found at $p ===\n".substr($m,$p-400, 2500)."\n"; } else echo "\n=== $id NOT in footer ===\n";
}
echo "\n=== full search 77a ===\n";
$p=strpos($m,'77a'); if($p!==false) echo substr($m,$p-600,3000)."\n";
// also check all elementor_library for 3949973
echo "\n=== global search 3949973 ===\n";
global $wpdb;
$r=$wpdb->get_results("SELECT post_id FROM ef_postmeta WHERE meta_value LIKE '%3949973%'", ARRAY_A);
print_r($r);
foreach($r as $row){ $mid=$row['post_id']; echo "post $mid: ".get_the_title($mid)." type=".get_post_type($mid)."\n"; $mm=get_post_meta($mid,'_elementor_data',true); if(is_array($mm)) $mm=json_encode($mm); $pp=strpos($mm,'3949973'); echo substr($mm,$pp-500,2000)."\n\n"; }
