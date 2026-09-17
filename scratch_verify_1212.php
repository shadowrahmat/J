<?php
require 'wp-load.php';
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $j=json_encode($mm, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES); else $j=json_encode(json_decode($mm,true), JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES);
echo $j."\n";
echo "\n=== check link fields ===\n";
$jj=json_decode($j,true);
foreach($jj[0]['elements'][0]['elements'] as $col){
  foreach($col['elements'] as $w){
    echo $w['id']." type=".$w['widgetType']." settings=".json_encode($w['settings'], JSON_PRETTY_PRINT)."\n\n";
  }
}
echo "\n=== live render test one product ===\n";
if(class_exists('\ElementorPro\Plugin')){
  $doc=\ElementorPro\Plugin::elementor()->documents->get(71);
  $inst=\ElementorPro\Core\Utils::create_widget_instance_from_db(71,'681c745');
  if($inst){
    $inst->set_search_term('cast');
    $inst->set_page_number(1);
    ob_start(); $inst->render_results(); $out=ob_get_clean();
    // check for anchor in product 1080 block
    $p=strpos($out,'e-loop-item-1080');
    echo $p!==false?substr($out,max(0,$p-2000),6000):"1080 not found\n".substr($out,0,8000);
    echo "\n\n=== has <a in image block? ===\n";
    $q=strpos($out,'249f2f9');
    echo $q!==false?substr($out,max(0,$q-800),2500):"249f2f9 not in output, searching for wp-image-203\n".(strpos($out,'wp-image-203')!==false?substr($out,strpos($out,'wp-image-203')-1000,3000):"no image");
    echo "\n\n=== title link check ===\n";
    $r=strpos($out,'6190c89');
    echo $r!==false?substr($out,max(0,$r-500),2000):"6190c89 not found, search for product-title\n".(strpos($out,'woocommerce-product-title')!==false?substr($out,strpos($out,'woocommerce-product-title')-500,3000):"no title");
  }
}
