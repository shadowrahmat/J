<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Reference image order: Home | Search | Cart | Shop
// Current footer: da447ad(Home) | f413d57(Search) | 32bab7d(Shop) | 77a58cb(Cart)
// Need: Home | Search | Cart | Shop => da447ad, f413d57, 77a58cb, 32bab7d
$m=get_post_meta(55,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$data=json_decode($j,true);
function findAndReorder(&$els){
  foreach($els as &$el){
    if(($el['id']??'')==='3949973'){
      echo "found 3949973 with ".count($el['elements'])." children\n";
      foreach($el['elements'] as $c) echo "  child ".$c['id']."\n";
      // Map id->element
      $map=[];
      foreach($el['elements'] as $c) $map[$c['id']]=$c;
      // desired order
      $order=['da447ad','f413d57','77a58cb','32bab7d'];
      $new=[];
      foreach($order as $id){
        if(isset($map[$id])) $new[]=$map[$id];
        else echo "missing $id\n";
      }
      // keep any leftover at end
      foreach($el['elements'] as $c) if(!in_array($c['id'],$order)) $new[]=$c;
      $el['elements']=$new;
      echo "reordered to: ";
      foreach($new as $c) echo $c['id']." ";
      echo "\n";
      return true;
    }
    if(!empty($el['elements']) && findAndReorder($el['elements'])) return true;
  }
  return false;
}
findAndReorder($data);
update_post_meta(55,'_elementor_data',$data);
$wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=55 AND meta_key='_elementor_css'");
@unlink('wp-content/uploads/elementor/css/post-55.css');
echo "55 reordered Home|Search|Cart|Shop DONE\n";
$m2=get_post_meta(55,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
$p=strpos($m2,'3949973'); echo substr($m2,$p-100,1800)."\n";
