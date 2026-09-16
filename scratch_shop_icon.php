<?php
require 'wp-load.php';
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$decoded=json_decode($j,true);
function dump(&$els,$depth=0){
  foreach($els as &$e){
    $id=$e['id']??'';
    $type=$e['widgetType']??$e['elType'];
    $settings=$e['settings']??[];
    if(in_array($id,['da447ad','cf834f4','f413d57','bb2203d','32bab7d','6c82ec1','8f4f6a6','77a58cb','0133b7d']) || $type=='icon-box' || $type=='woocommerce-menu-cart'){
      echo str_repeat('  ',$depth)."$id => $type\n";
      if(isset($settings['selected_icon'])) echo str_repeat('  ',$depth)."  icon: ".json_encode($settings['selected_icon'])."\n";
      if(isset($settings['icon'])) echo str_repeat('  ',$depth)."  menu_cart icon: ".json_encode($settings['icon'])."\n";
      if(isset($settings['menu_icon_svg'])) echo str_repeat('  ',$depth)."  menu_icon_svg: ".json_encode($settings['menu_icon_svg'])."\n";
      if(isset($settings['title_text'])) echo str_repeat('  ',$depth)."  title: ".$settings['title_text']."\n";
    }
    if(!empty($e['elements'])) dump($e['elements'],$depth+1);
  }
}
dump($decoded);
echo "\n=== raw 32bab7d ===\n";
$p=strpos($j,'32bab7d'); echo substr($j,$p-200,2000)."\n";
