<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('WP_ADMIN',true);
require dirname(__DIR__).'/wp-load.php';
$page_id=1278;$page=get_post($page_id);
if(!$page||$page->post_name!=='shop')throw new RuntimeException('Shop page identity mismatch');
$data=json_decode(get_post_meta($page_id,'_elementor_data',true),true);
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);
$backup=dirname(__DIR__).'/.shop-before-toolbar-update-20261013.json';
if(!file_exists($backup))file_put_contents($backup,wp_json_encode(['post'=>(array)$page,'meta'=>get_post_meta($page_id)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$counts=[];$changed=[];
$add_class=function(&$s,$key,$class){$classes=preg_split('/\s+/',trim($s[$key]??''));$s[$key]=trim(implode(' ',array_unique(array_filter(array_merge($classes,[$class])))));};
$gap=['unit'=>'px','size'=>12,'column'=>'12','row'=>'12','isLinked'=>true];
$zero=['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true];
$walk=function(&$nodes)use(&$walk,&$counts,&$changed,$add_class,$gap,$zero){foreach($nodes as&$n){$title=$n['settings']['_title']??'';$s=&$n['settings'];
 if($title==='Results - Sort and Mobile Filters'){
  $add_class($s,'css_classes','juhani-shop-toolbar');
  $s['flex_direction_mobile']='row';$s['flex_wrap_mobile']='nowrap';$s['flex_align_items_mobile']='center';$s['flex_justify_content_mobile']='space-between';$s['flex_gap_mobile']=$gap;
 }elseif($title==='Mobile - Filters Button'){
  $add_class($s,'css_classes','juhani-shop-filter-toggle');
  $s['flex_direction_mobile']='row';$s['flex_wrap_mobile']='nowrap';$s['width_mobile']=['unit'=>'custom','size'=>'auto'];$s['_flex_size_mobile']='none';$s['margin_mobile']=$zero;
 }elseif($title==='Products - Sort Dropdown'){
  $add_class($s,'_css_classes','juhani-shop-sort');
  $s['_element_width_mobile']='auto';$s['_flex_size_mobile']='grow';$s['_margin_mobile']=$zero;
 }
 if(in_array($title,['Results - Sort and Mobile Filters','Mobile - Filters Button','Products - Sort Dropdown'],true)){$counts[$title]=($counts[$title]??0)+1;$changed[]=$n['id'];}
 unset($s);$walk($n['elements']);
}};$walk($data);
foreach(['Results - Sort and Mobile Filters','Mobile - Filters Button','Products - Sort Dropdown']as$title)if(($counts[$title]??0)!==1)throw new RuntimeException('Expected one target: '.$title);
$component_css=file_get_contents(__DIR__.'/shop-toolbar.css');
$old_css=$settings['custom_css']??'';
// Replace only the old sorting rules; preserve the rest of the user's saved CSS.
$css=preg_replace('/^[ \t]*:where\(\.elementor-1278 \.jsp-sort select\)\{[^}]*\}\R?/m','',$old_css);
$marker='/* Shop sorting field. Assign juhani-shop-sort to the sorting HTML widget. */';
if(($pos=strpos($css,$marker))!==false)$css=substr($css,0,$pos);
$settings['custom_css']=rtrim($css)."\n\n".$component_css;$settings['post_status']=$page->post_status;
global $wpdb;$protected=function()use($wpdb,$page_id){return hash('sha256',serialize($wpdb->get_results($wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",$page_id),ARRAY_A)));};$before=$protected();
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);wp_set_current_user($admins[0]);
$doc=Elementor\Plugin::$instance->documents->get($page_id);
if(!$doc->save(['elements'=>$data,'settings'=>$settings]))throw new RuntimeException('Shop save failed');
delete_post_meta($page_id,'_elementor_element_cache');Elementor\Core\Files\CSS\Post::create($page_id)->update();
if($before!==$protected())throw new RuntimeException('Another page changed');
file_put_contents(__DIR__.'/shop-current-custom.css',$settings['custom_css']);
echo wp_json_encode(['page_id'=>$page_id,'updated'=>$counts,'other_pages'=>'unchanged','url'=>get_permalink($page_id),'edit_url'=>admin_url('post.php?post='.$page_id.'&action=elementor')],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
