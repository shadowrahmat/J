<?php
/** Fix the live search title using a native Heading with dynamic product data. CLI only. */
if(PHP_SAPI!=='cli'){http_response_code(404);exit;}
define('WP_ADMIN',true);
require dirname(__DIR__).'/wp-load.php';
$id=1270;$post=get_post($id);
if(!$post||$post->post_type!=='elementor_library'||get_post_meta($id,'_elementor_template_type',true)!=='loop-item')throw new RuntimeException('Search result template mismatch');
$raw=get_post_meta($id,'_elementor_data',true);$data=is_array($raw)?$raw:json_decode($raw,true);
$backup=dirname(__DIR__).'/.search-title-before-fix-20261013.json';
if(!file_exists($backup))file_put_contents($backup,wp_json_encode(['post'=>(array)$post,'meta'=>get_post_meta($id)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$changed=0;
$walk=function(&$nodes)use(&$walk,&$changed){foreach($nodes as&$node){
 if(($node['widgetType']??'')==='woocommerce-product-title'){
  if(empty($node['settings']['__dynamic__']['title']))throw new RuntimeException('Expected dynamic product title');
  $node['widgetType']='heading';$s=&$node['settings'];
  $s['_title']='Search - Product title (dynamic)';$s['header_size']='h3';$s['title_color']='#0f2f44';
  $s['typography_font_weight']='700';$s['typography_line_height']=['unit'=>'em','size'=>1.25];
  $s['_css_classes']=trim(($s['_css_classes']??'').' juhani-search-product-title');
  if(empty($s['__dynamic__']['link']))$s['__dynamic__']['link']='[elementor-tag id="a561d20" name="post-url" settings="%7B%7D"]';
  $s['link']=$s['link']??['url'=>''];unset($s);$changed++;
 }
 if(!empty($node['elements']))$walk($node['elements']);
}};$walk($data);
if($changed!==1)throw new RuntimeException('Expected one search Product Title widget');
global $wpdb;$protected=function()use($wpdb,$id){return hash('sha256',serialize($wpdb->get_results($wpdb->prepare("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id<>%d AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",$id),ARRAY_A)));};$before=$protected();
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);wp_set_current_user($admins[0]);
$document=Elementor\Plugin::$instance->documents->get($id);$settings=(array)get_post_meta($id,'_elementor_page_settings',true);$settings['post_status']=$post->post_status;
$document->save(['elements'=>$data,'settings'=>$settings]);
$saved=get_post_meta($id,'_elementor_data',true);$expected=wp_json_encode($data);
if(json_decode($saved,true)!==$data)throw new RuntimeException('Saved search title differs from expected data');
delete_post_meta($id,'_elementor_element_cache');Elementor\Core\Files\CSS\Post::create($id)->update();
if($before!==$protected())throw new RuntimeException('Unexpected change to another template/page');
echo wp_json_encode(['template'=>$id,'changed'=>'Product Title -> native Heading with dynamic product title and URL','class'=>'juhani-search-product-title','other_pages'=>'unchanged','edit_url'=>admin_url('post.php?post='.$id.'&action=elementor')],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
