<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Check current 1212 structure
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $j=json_encode($mm); else $j=$mm;
echo "1212 raw len=".strlen($j)."\n";
echo json_encode(json_decode($j,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
// also dump 134 for reference style
echo "\n\n=== 134 reference ===\n";
$mm2=get_post_meta(134,'_elementor_data',true);
if(is_array($mm2)) $j2=json_encode($mm2); else $j2=$mm;
echo substr(json_encode(json_decode($j2,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES),0,6000);
