<?php
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('WP_ADMIN',true);
require dirname(__DIR__).'/wp-load.php';
$id=1278;$p=get_post($id);if(!$p||$p->post_name!=='shop')throw new RuntimeException('Shop identity mismatch');
$backup=dirname(__DIR__).'/.shop-before-filter-count-removal-20261013.json';
if(!file_exists($backup))file_put_contents($backup,wp_json_encode(['post'=>(array)$p,'meta'=>get_post_meta($id)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$data=json_decode(get_post_meta($id,'_elementor_data',true),true);$removed=0;
$walk=function(&$nodes)use(&$walk,&$removed){foreach($nodes as$k=>&$n){if(($n['settings']['_element_id']??'')==='jsp-filter-count'&&($n['settings']['_title']??'')==='Mobile - Filter Count'){unset($nodes[$k]);$removed++;continue;}$walk($n['elements']);}unset($n);$nodes=array_values($nodes);};$walk($data);
if($removed!==1)throw new RuntimeException('Expected one mobile count badge');
global $wpdb;$protected=function()use($wpdb,$id){return hash('sha256',serialize($wpdb->get_results($wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",$id),ARRAY_A)));};$before=$protected();
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);wp_set_current_user($admins[0]);
$settings=(array)get_post_meta($id,'_elementor_page_settings',true);$settings['post_status']=$p->post_status;
if(!Elementor\Plugin::$instance->documents->get($id)->save(['elements'=>$data,'settings'=>$settings]))throw new RuntimeException('Shop save failed');
delete_post_meta($id,'_elementor_element_cache');Elementor\Core\Files\CSS\Post::create($id)->update();
if($before!==$protected())throw new RuntimeException('Other page changed');
echo wp_json_encode(['removed'=>'Mobile - Filter Count badge','other_pages'=>'unchanged']).PHP_EOL;
