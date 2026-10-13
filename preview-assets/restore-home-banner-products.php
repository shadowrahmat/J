<?php
/** Restore only the previous Home banner and product collection. CLI only. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/wp-load.php';
$page_id=1261;$page=get_post($page_id);
if((int)get_option('page_on_front')!==$page_id||!$page||$page->post_type!=='page')throw new RuntimeException('Home page identity changed');
$data=json_decode(get_post_meta($page_id,'_elementor_data',true),true);
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);
$source=json_decode(file_get_contents(dirname(__DIR__).'/.home-faq-before-remove-icons-1261.json'),true);
$class=function($element,$name){return in_array($name,explode(' ',$element['settings']['css_classes']??''),true);};
$old_root=null;foreach($source['content'] as$element)if($class($element,'rahmat-page'))$old_root=$element;
if(!$old_root)throw new RuntimeException('Previous layout missing');
$hero=null;$collection=null;
foreach($old_root['elements']as$element){if($class($element,'hero'))$hero=$element;if($class($element,'collection'))$collection=$element;}
if(!$hero||!$collection)throw new RuntimeException('Previous banner or collection missing');
$backup=dirname(__DIR__).'/.home-before-restore-banner-products-20261013.json';
if(!file_exists($backup)&&file_put_contents($backup,wp_json_encode(['post'=>['ID'=>$page_id,'post_content'=>$page->post_content,'post_status'=>$page->post_status],'content'=>$data,'page_settings'=>$settings],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))===false)throw new RuntimeException('Backup failed');
// Repair the two collection navigation targets while restoring these sections.
$links=function(&$elements)use(&$links){foreach($elements as&$element){if($element['id']==='8100004')$element['settings']['link']['url']='#collection';if($element['id']==='810008e')$element['settings']['link']['url']=home_url('/shop/');if(isset($element['elements']))$links($element['elements']);}unset($element);};
$hero_elements=[$hero];$collection_elements=[$collection];$links($hero_elements);$links($collection_elements);$hero=$hero_elements[0];$collection=$collection_elements[0];
$replaced=[];$found=false;$result=[];$kept_before=[];
foreach($data as$element){
 if(in_array($element['id'],['1a38668','8365ad9'],true)){$replaced[]=$element['id'];continue;}
 if($class($element,'rahmat-page')){
  $found=true;$existing=[];
  foreach($element['elements']as$child)if(!$class($child,'hero')&&!$class($child,'collection')){$existing[]=$child;$kept_before[$child['id']]=$child;}
  $restored=[$hero];$inserted=false;
  foreach($existing as$child){$restored[]=$child;if($class($child,'benefits')){$restored[]=$collection;$inserted=true;}}
  if(!$inserted)array_splice($restored,1,0,[$collection]);
  $element['elements']=$restored;
 }
 $result[]=$element;
}
if(!$found)throw new RuntimeException('Existing Home body missing');
$ids=[];$cards=0;$walk=function($elements)use(&$walk,&$ids,&$cards,$class){foreach($elements as$element){if(isset($ids[$element['id']]))throw new RuntimeException('Duplicate Elementor ID');$ids[$element['id']]=true;if($class($element,'product-card'))$cards++;$walk($element['elements']??[]);}};$walk($result);
if($cards!==8)throw new RuntimeException('Expected eight previous product cards');
// Ensure every unrelated section is byte-for-byte preserved.
foreach($result as$element)if($class($element,'rahmat-page'))foreach($element['elements']as$child)if(isset($kept_before[$child['id']])&&$kept_before[$child['id']]!==$child)throw new RuntimeException('Unrelated section changed');
global $wpdb;
$protected=function()use($wpdb,$page_id){return hash('sha256',serialize($wpdb->get_results($wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",$page_id),ARRAY_A)));};$before=$protected();
$admin=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admin)throw new RuntimeException('Administrator missing');wp_set_current_user($admin[0]);
$document=\Elementor\Plugin::$instance->documents->get($page_id);$settings['post_status']=$page->post_status;
if(!$document->save(['elements'=>$result,'settings'=>$settings]))throw new RuntimeException('Home save failed');\Elementor\Core\Files\CSS\Post::create($page_id)->update();
if($before!==$protected())throw new RuntimeException('Another page or template changed');
$template=['version'=>'0.4','title'=>'Home - Restored Banner and Collection','type'=>'page','content'=>$result,'page_settings'=>['hide_title'=>'yes','template'=>'elementor_header_footer','custom_css'=>$settings['custom_css']??'']];
file_put_contents(__DIR__.'/rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));file_put_contents(__DIR__.'/rahmat-body.css',$settings['custom_css']??'');
echo wp_json_encode(['url'=>get_permalink($page_id),'restored_banner'=>$hero['id'],'restored_collection'=>$collection['id'],'product_cards'=>$cards,'replaced_new_sections'=>$replaced,'other_sections'=>'preserved','other_pages_and_templates'=>'unchanged'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
