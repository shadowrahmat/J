<?php
/** Convert the journal preview to editable Elementor widgets. CLI only. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/wp-load.php';
$page_id=1274;
$page=get_post($page_id);
if (!$page || $page->post_name!=='blog-rahmat') throw new RuntimeException('Blog Rahmat identity mismatch');
$response=wp_remote_get(home_url('/blog-preview.php'),['timeout'=>30]);
if (is_wp_error($response)||wp_remote_retrieve_response_code($response)!==200) throw new RuntimeException('Preview unavailable');
$dom=new DOMDocument();libxml_use_internal_errors(true);
$dom->loadHTML('<?xml encoding="UTF-8">'.wp_remote_retrieve_body($response));libxml_clear_errors();
$xpath=new DOMXPath($dom);
$main=$xpath->query('//main[contains(concat(" ",normalize-space(@class)," ")," jb-preview ")]')->item(0);
if (!$main) throw new RuntimeException('Preview body missing');
$seq=0x8300000;
$id=function()use(&$seq){return dechex(++$seq);};
$zero=['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true];
$radius=['unit'=>'px','top'=>'12','right'=>'12','bottom'=>'12','left'=>'12','isLinked'=>true];
$inner=function($node)use($dom){$s='';foreach($node->childNodes as$c)$s.=$dom->saveHTML($c);return $s;};
$widget=function($type,$settings,$class,$title)use($id,$zero){return ['id'=>$id(),'elType'=>'widget','widgetType'=>$type,'settings'=>['_css_classes'=>$class,'_title'=>$title,'_margin'=>$zero]+$settings,'elements'=>[]];};
$container=function($class,$children,$title,$settings=[])use($id,$zero,$radius){return ['id'=>$id(),'elType'=>'container','isInner'=>false,'settings'=>array_merge(['content_width'=>'full','flex_direction'=>'column','flex_gap'=>['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true],'padding'=>$zero,'border_radius'=>$radius,'css_classes'=>$class,'_title'=>$title],$settings),'elements'=>$children];};
$attrs=function($node){$s=[];$a=[];if($node->hasAttribute('id')&&$node->getAttribute('id')!=='content')$s['_element_id']=$node->getAttribute('id');foreach(['lang','role','aria-live','aria-label','aria-labelledby','aria-current','aria-hidden','data-topic','data-search']as$k)if($node->hasAttribute($k))$a[]=$k.'|'.$node->getAttribute($k);if($a)$s['_attributes']=implode("\n",$a);return $s;};
$convert=null;
$convert=function($node)use(&$convert,$widget,$container,$attrs,$inner,$dom,$radius){
    $tag=strtolower($node->tagName);$class=trim($node->getAttribute('class'));$settings=$attrs($node);
    $plain=trim(preg_replace('/\s+/u',' ',$node->textContent));
    // Only interactive form controls use HTML; all page layout and articles remain native.
    if ($class==='jb-toolbar'||$class==='jb-empty') return $widget('html',['html'=>$dom->saveHTML($node)],$class==='jb-toolbar'?'jb-controls-widget':'jb-empty-widget',$class==='jb-toolbar'?'Article search and topic controls':'No matching articles message');
    if ($tag==='img') {$url=$node->getAttribute('src');return $widget('image',['image'=>['url'=>$url,'id'=>attachment_url_to_postid($url),'alt'=>$node->getAttribute('alt')],'image_size'=>'full','link_to'=>'none']+$settings,$class,'Editable article photograph');}
    if(preg_match('/^h[1-6]$/',$tag)){$link=$node->getElementsByTagName('a')->item(0);$s=['title'=>$inner($link?:$node),'header_size'=>$tag];if($link)$s['link']=['url'=>$link->getAttribute('href')];return $widget('heading',$s+$settings,$class,$plain);}
    if($tag==='a'&&!in_array($class,['jb-feature-image','jb-article-image'],true)){
        $s=['text'=>$plain,'link'=>['url'=>$node->getAttribute('href'),'is_external'=>$node->getAttribute('target')==='_blank'?'on':''],'selected_icon'=>['value'=>'','library'=>''],'border_radius'=>$radius];
        if($node->getElementsByTagName('svg')->length)$class.=' jb-native-arrow';
        return $widget('button',$s+$settings,trim($class?:'jb-plain-link'),$plain);
    }
    if($tag==='p')return $widget('text-editor',['editor'=>$dom->saveHTML($node)]+$settings,trim($class.' jb-native-paragraph'),$plain);
    if(in_array($tag,['span','time'],true)){
        if(trim($inner($node))===''){
            $s=$settings;
            if($class==='jb-dot'){$circle=$radius;foreach(['top','right','bottom','left']as$k)$circle[$k]='50';$circle['unit']='%';$s['border_radius']=$circle;}
            return $container(trim($class.' jb-native-marker'),[],'Decorative detail',$s);
        }
        // Preserve small inline icons, dates and meaningful inline semantics in editable text.
        if($node->getElementsByTagName('svg')->length||$node->getElementsByTagName('span')->length||$tag==='time')return $widget('text-editor',['editor'=>$tag==='time'?$dom->saveHTML($node):$inner($node),'_border_radius'=>$radius]+$settings,trim($class.' jb-native-inline'),$plain);
        if($class==='jb-dot'){$circle=$radius;foreach(['top','right','bottom','left']as$k)$circle[$k]='50';$circle['unit']='%';$settings['_border_radius']=$circle;}
        elseif(in_array($class,['jb-topic','jb-image-arrow'],true))$settings['_border_radius']=$radius;
        return $widget('heading',['title'=>$inner($node),'header_size'=>'span']+$settings,trim($class.' jb-native-span'),$plain?:'Decorative marker');
    }
    $has_text=false;foreach($node->childNodes as$c)if($c->nodeType===XML_TEXT_NODE&&trim($c->textContent)!=='')$has_text=true;
    if($has_text)return $widget('text-editor',['editor'=>$inner($node)]+$settings,trim($class.' jb-native-inline'),$plain);
    $children=[];foreach($node->childNodes as$c)if($c instanceof DOMElement)$children[]=$convert($c);
    if(in_array($tag,['section','article','nav','a'],true))$settings['html_tag']=$tag;
    if($tag==='a')$settings['link']=['url'=>$node->getAttribute('href')];
    $titles=['jb-hero'=>'Journal hero banner','jb-feature'=>'Featured guide','jb-library'=>'Article library','jb-article'=>'Editable article card','jb-help'=>'Contact Juhani'];
    $title=$class?:'Layout';foreach($titles as$k=>$v)if(in_array($k,explode(' ',$class),true)){$title=$v;break;}
    return $container($class,$children,$title,$settings);
};
$root=$convert($main);
$root['settings']['_title']='Blog Rahmat - editable journal';
// The source-page preview caption is not part of the published design.
$root['elements']=array_values(array_filter($root['elements'],function($e){return strpos($e['settings']['css_classes']??'','jb-preview-note')===false;}));
$root['elements'][]=$widget('html',['html'=>'<script src="'.esc_url(home_url('/preview-assets/blog.js')).'?v='.filemtime(__DIR__.'/blog.js').'" defer></script>'],'jb-script-widget','Article search functionality');
$scope='.elementor-'.$page_id.' .jb-preview';
function jb_native_selectors($s,$scope){
    $s=str_replace('.juhani-blog-preview #content.jb-preview','.jb-preview',$s);
    // HTML controls retain their original element selectors.
    $s=str_replace('.jb-button.jb-button-light','.__lightbutton__',$s);
    $s=preg_replace('/\.jb-button(?![\w-])/','.__button__',$s);
    $s=str_replace('.__button__',':is(.jb-button .elementor-button,.jb-empty .jb-button)',$s);
    $s=str_replace('.__lightbutton__','.jb-button.jb-button-light .elementor-button',$s);
    $s=str_replace('.jb-text-link','.jb-text-link .elementor-button',$s);
    $s=str_replace(['.jb-hero-image>img','.jb-hero-copy>p','.jb-article-copy>p','.jb-card-footer>a','.jb-card-footer>span','.jb-hero-foot>span:first-child','.jb-breadcrumb>span:nth-child(2)','.jb-meta>span:last-child','.jb-library-end>span','.jb-section-heading p','.jb-issue'],['.jb-hero-image img','.jb-hero-copy>.jb-native-paragraph','.jb-article-copy>.jb-native-paragraph','.jb-card-footer>.elementor-widget-button .elementor-button','.jb-card-footer>.jb-native-inline','.jb-hero-foot>.jb-native-inline:first-child','.jb-breadcrumb>.jb-native-span:nth-child(2)','.jb-meta>.jb-native-inline:last-child','.jb-library-end>.jb-native-span','.jb-section-heading p','.jb-issue'],$s);
    return str_replace('.jb-preview',$scope,$s);
}
function jb_native_css($css,$scope){
    $css=preg_replace('/\/\*[\s\S]*?\*\//','',$css);$out='';$at=0;$len=strlen($css);
    while($at<$len){$open=strpos($css,'{',$at);if($open===false)break;$selector=trim(substr($css,$at,$open-$at));$depth=1;$end=$open+1;while($end<$len&&$depth){if($css[$end]==='{')$depth++;elseif($css[$end]==='}')$depth--;$end++;}$body=substr($css,$open+1,$end-$open-2);
        if(strpos($selector,'@media')===0)$body=jb_native_css($body,$scope);
        else{$selector=jb_native_selectors($selector,$scope);
            // Native radius controls remain editable. Form-control radius uses the shared variable.
            $html_radius=preg_match('/jb-(filter|search|empty)|::?(before|after)/',$selector)&&strpos($selector,':is(')===false;
            if(!$html_radius)$body=preg_replace('/(?<![\w-])border-radius\s*:[^;{}]+;?/i','',$body);
        }
        if(trim($body)!=='')$out.=$selector.'{'.$body.'}'.PHP_EOL;$at=$end;
    }return $out;
}
$arrow=home_url('/preview-assets/icons/arrow.svg');$phone=home_url('/preview-assets/icons/phone.svg');
$reset=<<<CSS
$scope.e-con,$scope .e-con{display:block;--width:100%;--container-widget-width:100%;--container-widget-flex-grow:0;--padding-top:0px;--padding-right:0px;--padding-bottom:0px;--padding-left:0px;--gap:0px;gap:0;min-width:0;max-width:none;flex-direction:row;flex-wrap:nowrap;align-items:stretch;justify-content:normal;flex-shrink:1;margin:0;padding:0;border:0}
$scope .elementor-widget{margin:0;min-width:0;max-width:100%;--container-widget-width:100%;--container-widget-flex-grow:0}
$scope .elementor-widget-text-editor{font:inherit;color:inherit}
$scope .elementor-heading-title{font-family:inherit;color:inherit;padding:0}
$scope .elementor-button{font:inherit;color:inherit;background:transparent;border:0;padding:0;box-shadow:none}
$scope .elementor-button-content-wrapper{display:flex;align-items:center;justify-content:center}
$scope .elementor-button-text{flex-grow:0}
CSS;
$adapter=<<<CSS
$scope .jb-native-span .elementor-heading-title{font:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0}
$scope .jb-eyebrow{width:fit-content}
$scope .jb-eyebrow .elementor-widget-container{display:flex;align-items:center;gap:10px}
$scope .jb-breadcrumb>.elementor-widget,$scope .jb-meta>.elementor-widget,$scope .jb-help-actions>.elementor-widget,$scope .jb-hero-copy>.elementor-widget-button,$scope .jb-feature-copy>.elementor-widget-button,$scope .jb-card-footer>.elementor-widget{width:auto}
$scope .jb-hero-foot>.jb-native-inline>.elementor-widget-container,$scope .jb-meta>.jb-native-inline>.elementor-widget-container,$scope .jb-card-footer>.jb-native-inline>.elementor-widget-container,$scope .jb-feature-badge>.elementor-widget-container{display:flex;align-items:center;gap:inherit}
$scope .jb-button .elementor-button-content-wrapper{gap:22px}
$scope .jb-native-arrow .elementor-button-content-wrapper:after{content:'';display:block;width:20px;height:20px;flex-shrink:0;background:currentColor;mask:url('$arrow') center/contain no-repeat}
$scope .jb-text-link .elementor-button-content-wrapper{gap:20px}
$scope .jb-text-link .elementor-button-content-wrapper:after{width:18px;height:18px}
$scope .jb-phone .elementor-button-content-wrapper{gap:9px}
$scope .jb-phone .elementor-button-content-wrapper:after{display:none}
$scope .jb-phone .elementor-button-content-wrapper:before{content:'';width:16px;height:16px;background:currentColor;mask:url('$phone') center/contain no-repeat}
$scope .jb-card-footer>.elementor-widget-button .elementor-button-content-wrapper{gap:12px}
$scope .jb-card-footer .jb-native-arrow .elementor-button-content-wrapper:after{width:16px;height:16px}
$scope .jb-hero-image>.elementor-widget-image,$scope .jb-hero-image>.elementor-widget-image>.elementor-widget-container,$scope .jb-feature-image>.elementor-widget-image,$scope .jb-feature-image>.elementor-widget-image>.elementor-widget-container,$scope .jb-article-image>.elementor-widget-image,$scope .jb-article-image>.elementor-widget-image>.elementor-widget-container{height:100%}
$scope .jb-hero-image img,$scope .jb-feature-image img,$scope .jb-article-image img{width:100%;height:100%;object-fit:cover}
$scope .jb-article-image img{object-fit:contain}
$scope .jb-image-note{width:auto}
$scope .jb-image-note>.jb-native-span:last-child{flex:1;width:auto}
$scope .jb-dot{flex:0 0 6px;width:6px;height:6px}
$scope .jb-feature-badge{width:auto}
$scope .jb-image-arrow>.elementor-widget-container{display:grid;place-items:center;height:100%}
$scope .jb-section-heading>.e-con,$scope .jb-help>.e-con:first-child{width:auto;flex:1}
$scope .jb-section-heading>.jb-total{width:auto;flex-shrink:0}
$scope .jb-help>.jb-help-actions{width:auto}
$scope .jb-results-line>.elementor-widget{width:auto}
$scope .jb-results-line>.jb-native-span:last-child{font-family:'Segoe UI',Arial,sans-serif;font-size:8px;letter-spacing:1.4px}
$scope .jb-library-end>.jb-native-paragraph{width:auto}
$scope .jb-library-end>.jb-native-marker{width:40px;height:1px;background:#d7e4ed}
$scope .jb-script-widget{display:none}
$scope .jb-empty .jb-button{border-radius:var(--jb-radius)}
body.elementor-page-$page_id .site-main{max-width:none;margin:0;padding:0}
body.elementor-page-$page_id .entry-content{margin:0}
@media(max-width:767px){
$scope .jb-button .elementor-button-content-wrapper{gap:18px}
$scope .jb-text-link .elementor-button-content-wrapper{gap:15px}
$scope .jb-hero-image>.elementor-widget-image{position:absolute;inset:0}
$scope .jb-section-heading>.e-con,$scope .jb-help>.e-con:first-child{width:100%;flex:none}
$scope .jb-help>.jb-help-actions{width:100%}
$scope .jb-help-actions>.jb-button,$scope .jb-help-actions>.jb-button .elementor-button{width:100%}
$scope .jb-results-line>.jb-native-span:last-child{display:none}
$scope .jb-library-end>.jb-native-marker{width:24px}
}
CSS;
$css=$reset.PHP_EOL.jb_native_css(file_get_contents(__DIR__.'/blog.css'),$scope).$adapter;
$template=['version'=>'0.4','title'=>'Blog Rahmat - Juhani Journal','type'=>'page','content'=>[$root],'page_settings'=>['hide_title'=>'yes','template'=>'elementor_header_footer','custom_css'=>$css]];
file_put_contents(__DIR__.'/blog-rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL);
file_put_contents(__DIR__.'/blog-rahmat.css',$css);
$nodes=[];$counts=[];$walk=function($items)use(&$walk,&$nodes,&$counts){foreach($items as$n){if(isset($nodes[$n['id']]))throw new RuntimeException('Duplicate element ID');$nodes[$n['id']]=true;if($n['elType']==='widget')$counts[$n['widgetType']]=($counts[$n['widgetType']]??0)+1;$walk($n['elements']??[]);}};$walk($template['content']);
if(($argv[1]??'')!=='apply'){echo wp_json_encode(['page_id'=>$page_id,'nodes'=>count($nodes),'widgets'=>$counts,'status'=>'Ready']).PHP_EOL;exit;}
$backup=dirname(__DIR__).'/.blog-rahmat-before-design.json';
if(!file_exists($backup)&&file_put_contents($backup,wp_json_encode(['post'=>['ID'=>$page_id,'post_content'=>$page->post_content,'post_status'=>$page->post_status],'meta'=>get_post_meta($page_id)],JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))===false)throw new RuntimeException('Backup failed');
global $wpdb;
$hash=function()use($wpdb){return hash('sha256',serialize($wpdb->get_results("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id IN (2,55,211,1261,1267,1271) AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",ARRAY_A)));};$before=$hash();
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admins)throw new RuntimeException('Admin missing');wp_set_current_user($admins[0]);
$document=\Elementor\Plugin::$instance->documents->get($page_id);$document->set_is_built_with_elementor(true);
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);$settings['hide_title']='yes';$settings['custom_css']=$css;$settings['post_status']=$page->post_status;
if(!$document->save(['elements'=>$template['content'],'settings'=>$settings]))throw new RuntimeException('Save failed');
update_post_meta($page_id,'_wp_page_template','elementor_header_footer');\Elementor\Core\Files\CSS\Post::create($page_id)->update();
if($before!==$hash())throw new RuntimeException('Unexpected change to protected page templates');
echo wp_json_encode(['page_id'=>$page_id,'url'=>get_permalink($page_id),'edit_url'=>admin_url('post.php?post='.$page_id.'&action=elementor'),'nodes'=>count($nodes),'widgets'=>$counts,'protected_pages'=>'unchanged'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
