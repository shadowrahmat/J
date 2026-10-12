<?php
/** Restructure the current saved page, preserving edits and widget IDs. CLI only. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
require dirname(__DIR__).'/wp-load.php';
require __DIR__.'/blog-container-layout.php';
$page_id=1274;$page=get_post($page_id);
if(!$page||$page->post_type!=='page')throw new RuntimeException('Page identity mismatch');
$content=json_decode(get_post_meta($page_id,'_elementor_data',true),true);
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);
if(!$content)throw new RuntimeException('Saved Elementor layout missing');
if(strpos($content[0]['settings']['css_classes']??'','jb-')===false)throw new RuntimeException('Expected journal layout missing');
$backup=dirname(__DIR__).'/.blog-rahmat-before-separate-containers.json';
if(!file_exists($backup)&&file_put_contents($backup,wp_json_encode(['post'=>['ID'=>$page_id,'post_content'=>$page->post_content,'post_status'=>$page->post_status],'content'=>$content,'page_settings'=>$settings,'meta'=>get_post_meta($page_id)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))===false)throw new RuntimeException('Backup failed');
$separate=juhani_blog_separate_sections($content);
$css=juhani_blog_flat_css($settings['custom_css']??'');
$settings['custom_css']=$css;$settings['post_status']=$page->post_status;
$ids=function($elements)use(&$ids){$result=[];foreach($elements as $element){$result[]=$element['id'];$result=array_merge($result,$ids($element['elements']??[]));}return $result;};
$before_ids=$ids($content);$after_ids=$ids($separate);
if(count($after_ids)!==count(array_unique($after_ids)))throw new RuntimeException('Duplicate widget IDs');
$removed=array_values(array_diff($before_ids,$after_ids));
if(count($removed)>1)throw new RuntimeException('Unexpected widget removal');
global $wpdb;
$protected=function()use($wpdb){return hash('sha256',serialize($wpdb->get_results("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id IN (2,55,211,1177,1261,1267,1271) AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",ARRAY_A)));};
$before=$protected();$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admins)throw new RuntimeException('Admin unavailable');wp_set_current_user($admins[0]);
$document=\Elementor\Plugin::$instance->documents->get($page_id);
if(!$document->save(['elements'=>$separate,'settings'=>$settings]))throw new RuntimeException('Elementor save failed');
\Elementor\Core\Files\CSS\Post::create($page_id)->update();
if($protected()!==$before)throw new RuntimeException('Protected page changed');
$template=['version'=>'0.4','title'=>'Blog Rahmat - Separate Sections','type'=>'page','content'=>$separate,'page_settings'=>['hide_title'=>'yes','template'=>'elementor_header_footer','custom_css'=>$css]];
file_put_contents(__DIR__.'/blog-rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL);
file_put_contents(__DIR__.'/blog-rahmat.css',$css);
echo wp_json_encode(['top_level_containers'=>count($separate),'sections'=>array_map(function($e){return $e['settings']['_title'];},$separate),'removed_parent_ids'=>$removed,'preserved_elements'=>count($after_ids),'protected_pages'=>'unchanged','css_location'=>'Page Settings > Advanced > Custom CSS'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
