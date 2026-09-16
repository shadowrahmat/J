<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
echo "=== Footer Search container 3949973 order ===\n";
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$data=json_decode($j,true);
function findEl(&$els,$id){
  foreach($els as &$e){
    if(($e['id']??'')===$id) return $e;
    if(!empty($e['elements'])){ $r=findEl($e['elements'],$id); if($r) return $r; }
  } return null;
}
$el=findEl($data,'3949973');
if($el){
  echo "children in order:\n";
  foreach($el['elements'] as $c){
    $wid=$c['elements'][0]['widgetType']??'';
    $title=$c['elements'][0]['settings']['title_text']??'';
    $icon=$c['elements'][0]['settings']['selected_icon']['value']??($c['elements'][0]['settings']['icon']??'');
    if($wid=='icon-box') echo "  {$c['id']} => icon-box title=$title icon=$icon\n";
    else if($wid=='woocommerce-menu-cart') echo "  {$c['id']} => CART widget icon=".json_encode($c['elements'][0]['settings']['icon']??'')." custom=".json_encode($c['elements'][0]['settings']['menu_icon_svg']??'')."\n";
    else echo "  {$c['id']} => $wid\n";
  }
  // Search link popup
  foreach($el['elements'] as $c){
    if($c['id']=='f413d57'){
      echo "\nSearch container f413d57 settings:\n".json_encode($c, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
      $bb=$c['elements'][0]['settings']??[];
      echo "\n bb2203d dynamic link: ".json_encode($bb['__dynamic__']??[])."\n";
    }
  }
}
echo "\n=== Popup 1208 widget ===\n";
$mm=get_post_meta(1208,'_elementor_data',true);
if(is_array($mm)) $jj=json_encode($mm, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES); else $jj=json_encode(json_decode($mm,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo substr($jj,0,8000)."\n";
echo "\n=== Popup 1208 meta display condition ===\n";
$cond=get_post_meta(1208,'elementor_popup_display_settings',true);
echo json_encode($cond, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
$cond2=get_post_meta(1208,'_elementor_conditions',true);
echo json_encode($cond2, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
echo "\n=== CSS post-1208 current ===\n";
echo @file_get_contents('wp-content/uploads/elementor/css/post-1208.css');
echo "\n\n=== mu fix css ===\n";
echo @file_get_contents('wp-content/mu-plugins/juhani-search-width-fix.php');
echo "\n\n=== Footer CSS post-55 exists? ===\n";
echo @file_get_contents('wp-content/uploads/elementor/css/post-55.css') ? substr(@file_get_contents('wp-content/uploads/elementor/css/post-55.css'),0,3000) : "post-55.css not generated yet (will auto-regenerate)\n";
