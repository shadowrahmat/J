<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Footer mobile popup 1214: check elementor css and widget width
$mm=get_post_meta(1214,'_elementor_data',true);
if(is_array($mm)) $j=json_encode($mm); else $j=$mm;
echo substr($j, strpos($j,'b579efa')-400, 2000)."\n\n";
echo "=== CSS mobile width ===\n";
$css=@file_get_contents('wp-content/uploads/elementor/css/post-1214.css');
echo $css?substr($css,0,1200):"no css";

// What is the actual rendered search widget width on mobile? Check wrapper
echo "\n=== 1214 popup container settings ===\n";
$jj=json_decode($j,true);
echo json_encode($jj, JSON_PRETTY_PRINT)."\n";
