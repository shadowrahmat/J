<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
echo "=== permalink test ===\n";
foreach([1080,1020,960] as $id){
  $link=get_permalink($id);
  echo "$id => $link\n";
  // test /?p= redirect
  $short="/juhani/?p=$id";
  echo "  short $short\n";
}
echo "\n=== home.html contains link fix? ===\n";
$h=@file_get_contents(getenv('TEMP').'\home.html');
echo strpos($h,'juhani-search-link-fix')!==false?"found link fix script\n":"NOT FOUND link fix\n";
$p=strpos($h,'juhani-search-link-fix');
if($p!==false) echo substr($h,max(0,$p-300),1200)."\n";
echo "\n=== live render again with fresh instance ===\n";
if(class_exists('\ElementorPro\Plugin')){
  $inst=\ElementorPro\Core\Utils::create_widget_instance_from_db(71,'681c745');
  if($inst){
    $inst->set_search_term('cast'); $inst->set_page_number(1);
    ob_start(); $inst->render_results(); $out=ob_get_clean();
    echo "has anchor? ".(strpos($out,'<a ')!==false?"YES count ".substr_count($out,'<a '):"NO")."\n";
    echo "image anchor? ".(preg_match('/<a[^>]*><[^>]*img/s',$out)?"YES":"NO")."\n";
    echo "title anchor? ".(preg_match('/<a[^>]*>.*Cast Net/s',$out)?"YES":"NO")."\n";
    echo substr($out, strpos($out,'249f2f9')-800, 2500)."\n";
  }
}
echo "\n=== 1212 widgetType check ===\n";
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $j=json_encode($mm); else $j=$mm;
echo substr($j,0,900)."\n";
echo "widgetType present: ".(strpos($j,'theme-post-featured-image')!==false?"yes":"no")." vs image: ".(strpos($j,'\"widgetType\":\"image\"')!==false?"yes":"no")."\n";
