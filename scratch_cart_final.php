<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Set footer cart widget 0133b7d to custom icon with reliable FA cart
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$orig=$j;
$data=json_decode($j,true);
// find 0133b7d
function &findW(&$els,$id){
  foreach($els as &$c){
    if(($c['id']??'')===$id) return $c;
    if(!empty($c['elements'])){
      $r=findW($c['elements'],$id);
      if($r!==null) return $r;
    }
  }
  $n=null; return $n;
}
$found = &findW($data,$id='0133b7d');
if($found){
  echo "found 0133b7d before: ".json_encode($found['settings'])."\n";
  $found['settings']['icon']='custom';
  $found['settings']['menu_icon_svg']=['value'=>'fas fa-shopping-cart','library'=>'fa-solid'];
  // ensure show_subtotal stays off etc
  echo "after: ".json_encode($found['settings'])."\n";
  update_post_meta(55,'_elementor_data',$data);
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=55 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-55.css');
  echo "55 updated to custom fa-shopping-cart DONE\n";
} else echo "not found 0133b7d\n";

$m2=get_post_meta(55,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'0133b7d'); echo substr($m2,$p-200,1200)."\n";
