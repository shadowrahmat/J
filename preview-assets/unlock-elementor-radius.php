<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/wp-load.php';
global $wpdb;
$backup_dir = dirname(__DIR__).'/.radius-fix-backup-20261012';
if (!is_dir($backup_dir)) { wp_mkdir_p($backup_dir); }
$backup = [];
$changed = [];
$forced = '/(?<![\w-])(?:-webkit-|-moz-)?border(?:-(?:top|bottom)-(?:left|right))?-radius\s*:\s*[^;{}]*!\s*important\s*;?/i';

// Remove authored CSS radius locks; native Elementor control settings stay intact.
function unlock_css_values($value, &$count) {
    global $forced;
    if (!is_array($value)) { return $value; }
    foreach ($value as $key=>&$item) {
        if ($key==='custom_css' && is_string($item)) {
            $item=preg_replace($forced,'',$item,-1,$removed);
            $count+=$removed;
            $item=preg_replace('/--border-radius\s*:\s*0(?:px)?\s*;?/i','',$item);
        } elseif (is_array($item)) { $item=unlock_css_values($item,$count); }
    }
    return $value;
}

// Rahmat uses native radius controls, so its presentation CSS must not replace them.
function native_radius_css($css) {
    $marker=strpos($css,'/* Rahmat shared 12px layout corners */');
    if ($marker!==false) { $css=substr($css,0,$marker); }
    $css=preg_replace('/\/\*[\s\S]*?\*\//','',$css);
    $out=''; $at=0; $length=strlen($css);
    while ($at<$length) {
        $open=strpos($css,'{',$at);
        if ($open===false) { break; }
        $selector=trim(substr($css,$at,$open-$at));
        $depth=1; $end=$open+1;
        while ($end<$length && $depth) { if ($css[$end]==='{') {$depth++;} elseif ($css[$end]==='}') {$depth--;} $end++; }
        $body=substr($css,$open+1,$end-$open-2);
        if (strpos($selector,'@media')===0) { $body=native_radius_css($body); }
        elseif (!preg_match('/::?(?:before|after)|\bsvg\b/',$selector)) {
            $body=preg_replace('/(?<![\w-])(?:-webkit-|-moz-)?border(?:-(?:top|bottom)-(?:left|right))?-radius\s*:[^;{}]+;?/i','',$body);
        }
        if (trim($body)!=='') { $out.=$selector.'{'.$body.'}'.PHP_EOL; }
        $at=$end;
    }
    return $out;
}

$rows=$wpdb->get_results("SELECT pm.post_id,pm.meta_key FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE pm.meta_key IN ('_elementor_data','_elementor_page_settings') AND p.post_type<>'revision' AND pm.meta_value LIKE '%radius%'",ARRAY_A);
$removed=0;
foreach ($rows as $row) {
    $id=(int)$row['post_id']; $key=$row['meta_key']; $original=get_post_meta($id,$key,true);
    $data=$key==='_elementor_data'?json_decode($original,true):$original;
    if (!is_array($data)) { continue; }
    $clean=unlock_css_values($data,$removed);
    if ($id===1261 && $key==='_elementor_page_settings') {
        $clean['custom_css']=native_radius_css($clean['custom_css']).file_get_contents(__DIR__.'/rahmat-radius.css');
    }
    if ($clean!==$data) {
        $backup[]=['id'=>$id,'key'=>$key,'value'=>$original];
        $changed[$id]=true;
        $value=$key==='_elementor_data'?wp_slash(wp_json_encode($clean)):$clean;
        update_post_meta($id,$key,$value);
    }
}

$id=1261;
$original=get_post_meta($id,'_elementor_data',true);
$data=json_decode($original,true);
if (!$data || get_post_field('post_name',$id)!=='rahmat') { throw new RuntimeException('Rahmat page not found'); }
$corners=['unit'=>'px','top'=>'12','right'=>'12','bottom'=>'12','left'=>'12','isLinked'=>true];
$native=0;
$default=function(&$settings,$key) use ($corners,&$native) {
    if (!isset($settings[$key]) || !isset($settings[$key]['top']) || $settings[$key]['top']==='') {
        $settings[$key]=$corners; $native++;
    }
};
$walk=function(&$nodes) use (&$walk,$default) {
    foreach ($nodes as &$node) {
        $settings=&$node['settings'];
        if ($node['elType']==='container') { $default($settings,'border_radius'); }
        elseif (($node['widgetType']??'')==='button') { $default($settings,'border_radius'); }
        elseif (($node['widgetType']??'')==='image') { $default($settings,'image_border_radius'); }
        elseif (($node['widgetType']??'')==='nested-accordion') { $default($settings,'accordion_border_radius'); }
        if (preg_match('/(?:product-open|product-tag|image-label|hero-scroll|product-arrow|step-number)/',$settings['_css_classes']??'')) { $default($settings,'_border_radius'); }
        if (!empty($node['elements'])) { $walk($node['elements']); }
    }
};
$walk($data);
if ($native) {
    $backup[]=['id'=>$id,'key'=>'_elementor_data','value'=>$original];
    update_post_meta($id,'_elementor_data',wp_slash(wp_json_encode($data)));
    $changed[$id]=true;
}
$backup_path=$backup_dir.'/elementor-meta.json';
if (!file_exists($backup_path)) { file_put_contents($backup_path,wp_json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE)); }
foreach (array_keys($changed) as $post_id) {
    delete_post_meta($post_id,\Elementor\Core\Base\Document::CACHE_META_KEY);
    \Elementor\Core\Files\CSS\Post::create($post_id)->update();
}
$settings=get_post_meta($id,'_elementor_page_settings',true);
file_put_contents(__DIR__.'/rahmat-body.css',$settings['custom_css']);
$template=json_decode(file_get_contents(__DIR__.'/rahmat-elementor.json'),true);
$template['content']=$data;
$template['page_settings']['custom_css']=$settings['custom_css'];
file_put_contents(__DIR__.'/rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE).PHP_EOL);
echo wp_json_encode(['css_locks_removed'=>$removed,'documents_refreshed'=>array_keys($changed),'native_radius_controls_added'=>$native,'backup'=>basename($backup_dir)],JSON_PRETTY_PRINT).PHP_EOL;
