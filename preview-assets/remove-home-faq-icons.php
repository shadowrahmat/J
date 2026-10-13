<?php
/** Remove only FAQ icons from the current homepage. CLI only. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/wp-load.php';
$page_id=(int)get_option('page_on_front');
$page=get_post($page_id);
if(!$page||$page->post_type!=='page')throw new RuntimeException('Static homepage unavailable');
$data=json_decode(get_post_meta($page_id,'_elementor_data',true),true);
if(!$data)throw new RuntimeException('Homepage Elementor data missing');
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);
$backup=dirname(__DIR__).'/.home-faq-before-remove-icons-'.$page_id.'.json';
if(!file_exists($backup))file_put_contents($backup,wp_json_encode(['post'=>['ID'=>$page_id,'post_content'=>$page->post_content,'post_status'=>$page->post_status],'content'=>$data,'page_settings'=>$settings],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$changed=[];$faq_count=0;
$walk=function(&$elements,$in_faq=false)use(&$walk,&$changed,&$faq_count){
 foreach($elements as&$element){
  $s=&$element['settings'];$class=($s['css_classes']??'').' '.($s['_css_classes']??'');
  $is_faq=$in_faq||preg_match('/(?:^|\s)(?:questions|faq[^\s]*)(?:\s|$)/',$class)||preg_match('/faq|frequently asked|helpful answers/i',$s['_title']??'');
  $type=$element['widgetType']??'';
  if(in_array($type,['nested-accordion','accordion','toggle'],true)&&$is_faq){
   $faq_count++;
   if($type==='nested-accordion'){$s['accordion_item_title_icon']=['value'=>'','library'=>''];$s['accordion_item_title_icon_active']=['value'=>'','library'=>''];}
   else{$s['selected_icon']=['value'=>'','library'=>''];$s['selected_active_icon']=['value'=>'','library'=>''];$s['icon']='';$s['icon_active']='';}
   $changed[]=$element['id'];
  }
  if($is_faq&&$type==='button'){$s['selected_icon']=['value'=>'','library'=>''];$s['icon']='';}
  $walk($element['elements'],$is_faq);
 }unset($element);
};
$walk($data);
if(!$faq_count)throw new RuntimeException('FAQ accordion not found; no changes saved');
$css=$settings['custom_css']??'';
$css=preg_replace_callback('/([^{}]+)\{([^{}]*)\}/',function($m){
 $selectors=explode(',',$m[1]);$keep=[];
 foreach($selectors as$selector){
  if(preg_match('/\.faq-list[^{}]*summary\s*::?after|\.faq-help\s+\.elementor-button-content-wrapper\s*::?before/',$selector))continue;
  $keep[]=$selector;
 }
 return $keep?implode(',',$keep).'{'.$m[2].'}':'';
},$css);
$settings['custom_css']=$css;$settings['post_status']=$page->post_status;
global $wpdb;
$protected=function()use($wpdb,$page_id){return hash('sha256',serialize($wpdb->get_results($wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",$page_id),ARRAY_A)));};
$before=$protected();$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admins)throw new RuntimeException('Administrator unavailable');wp_set_current_user($admins[0]);
$document=\Elementor\Plugin::$instance->documents->get($page_id);
if(!$document->save(['elements'=>$data,'settings'=>$settings]))throw new RuntimeException('Homepage save failed');
\Elementor\Core\Files\CSS\Post::create($page_id)->update();
if($before!==$protected())throw new RuntimeException('Unexpected change to another page');
echo wp_json_encode(['page_id'=>$page_id,'title'=>$page->post_title,'url'=>get_permalink($page_id),'faq_widgets'=>$changed,'other_pages'=>'unchanged'],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
