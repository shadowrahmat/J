<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $j=json_encode($mm); else $j=$mm;
echo "=== 1212 before ===\n";
$jj=json_decode($j,true);
echo json_encode($jj, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
// The image widget 249f2f9 has no link, title widget has no link either? check: title widget should link to post
// We need to add link to image and title
// For loop item, post-url dynamic tag should work when rendered inside search results (loop context sets post ID)
// Add link_to custom + __dynamic__ link post-url to image widget, and link to title widget

$changed=false;
foreach($jj[0]['elements'][0]['elements'] as &$col){
  foreach($col['elements'] as &$w){
    if($w['id']=='249f2f9'){
      echo "\n=== fixing 249f2f9 image ===\n";
      $w['settings']['link_to']='custom';
      if(!isset($w['settings']['__dynamic__'])) $w['settings']['__dynamic__']=[];
      // Keep image tag, ensure link is post-url
      $w['settings']['__dynamic__']['link']='[elementor-tag id="adeeb66" name="post-url" settings="%7B%7D"]';
      $changed=true;
      echo json_encode($w['settings'], JSON_PRETTY_PRINT)."\n";
    }
    if($w['id']=='6190c89'){
      echo "\n=== fixing 6190c89 title ===\n";
      if(!isset($w['settings']['__dynamic__'])) $w['settings']['__dynamic__']=[];
      if(empty($w['settings']['__dynamic__']['link'])){
        $w['settings']['__dynamic__']['link']='[elementor-tag id="bdf4ce8" name="post-url" settings="%7B%7D"]';
        $changed=true;
        echo "added title link\n";
      } else echo "title already has link: ".json_encode($w['settings']['__dynamic__']['link'])."\n";
      echo json_encode($w['settings'], JSON_PRETTY_PRINT)."\n";
    }
  }
}
if($changed){
  update_post_meta(1212,'_elementor_data',$jj);
  $wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=1212 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/loop-1212.css');
  @unlink('wp-content/uploads/elementor/css/post-1212.css');
  echo "\n1212 updated and css cleared\n";
} else echo "\n1212 no change\n";

$mm2=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm2)) $j2=json_encode($mm2); else $j2=$mm2;
echo substr($j2,0,800)."\n";
echo "DONE\n";
