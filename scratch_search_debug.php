<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
echo "=== 1. WP_Query product search test ===\n";
$q=new WP_Query(['post_type'=>'product','s'=>'cast','posts_per_page'=>3,'post_status'=>'publish']);
echo "found: ".$q->found_posts." posts\n";
while($q->have_posts()){ $q->the_post(); echo "  - ID=".get_the_ID()." title=".get_the_title()." thumb=".get_post_thumbnail_id(get_the_ID())." type=".get_post_type()."\n"; }
wp_reset_postdata();
echo "\n=== 2. WP_Query product search 'Cast Net' exact ===\n";
$q2=new WP_Query(['post_type'=>'product','s'=>'Cast Net','posts_per_page'=>3,'post_status'=>'publish']);
echo "found: ".$q2->found_posts."\n";
while($q2->have_posts()){ $q2->the_post(); echo "  - ID=".get_the_ID()." title=".get_the_title()."\n"; }
wp_reset_postdata();

echo "\n=== 3. Header widget 681c745 current ===\n";
$m=get_post_meta(71,'_elementor_data',true);
if(is_array($m)) $j=json_encode($m); else $j=$m;
$p=strpos($j,'681c745'); echo substr($j, $p-300, 1400)."\n";

echo "\n=== 4. Popup 1214 widget b579efa current ===\n";
$mm=get_post_meta(1214,'_elementor_data',true);
if(is_array($mm)) $jj=json_encode($mm); else $jj=$mm;
$pb=strpos($jj,'b579efa'); echo substr($jj,$pb-300,1400)."\n";

echo "\n=== 5. Template 1212 check ===\n";
$tt=get_post(1212); echo "1212 type=".get_post_type(1212)." status=".get_post_status(1212)." title=".get_the_title(1212)."\n";
echo "template 134 type=".get_post_type(134)." status=".get_post_status(134)."\n";

echo "\n=== 6. Simulate render_results for header widget ===\n";
if(class_exists('\ElementorPro\Plugin')){
  $doc=\ElementorPro\Plugin::elementor()->documents->get(71);
  echo "doc ".($doc?"found":"NOT found")."\n";
  if($doc){
    $data=$doc->get_elements_data();
    $wd=\Elementor\Utils::find_element_recursive($data, '681c745');
    echo "widget_data: ".json_encode($wd, JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES)."\n";
    if($wd){
      $inst=\ElementorPro\Core\Utils::create_widget_instance_from_db(71,'681c745');
      echo "instance ".($inst?get_class($inst):"NULL")."\n";
      if($inst && method_exists($inst,'set_search_term')){
        $inst->set_search_term('cast');
        $inst->set_page_number(1);
        ob_start(); $inst->render_results(); $out=ob_get_clean();
        echo "render_results output len=".strlen($out)."\n";
        echo substr($out,0,4000)."\n";
        if(!trim($out)) echo "!!! EMPTY OUTPUT - widget rendered nothing !!!\n";
      }
    }
  }
} else echo "ElementorPro Plugin not loaded\n";

echo "\n=== 7. Mu-plugin search fix exists? ===\n";
echo @file_get_contents('wp-content/mu-plugins/juhani-elementor-search-fix.php') ? "exists len=".strlen(file_get_contents('wp-content/mu-plugins/juhani-elementor-search-fix.php')) : "missing";
echo "\n";
