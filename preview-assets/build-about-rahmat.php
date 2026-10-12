<?php
/** Build an editable Elementor page from the isolated About preview. CLI only. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__).'/wp-load.php';
$page_id=1271;
$page=get_post($page_id);
if (!$page || $page->post_name!=='about-us-rahmat') { throw new RuntimeException('About us Rahmat page identity did not match.'); }
$response=wp_remote_get(home_url('/about-preview.php'),['timeout'=>30]);
if (is_wp_error($response) || wp_remote_retrieve_response_code($response)!==200) { throw new RuntimeException('About preview did not load.'); }
$html=wp_remote_retrieve_body($response);
$dom=new DOMDocument();
libxml_use_internal_errors(true);
$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
libxml_clear_errors();
$xpath=new DOMXPath($dom);
$main=$xpath->query('//main[contains(concat(" ",normalize-space(@class)," ")," ja-preview ")]')->item(0);
if (!$main) { throw new RuntimeException('About preview body is missing.'); }
$sequence=0x8200000;
$new_id=function() use (&$sequence) { return dechex(++$sequence); };
$zero=['unit'=>'px','top'=>'0','right'=>'0','bottom'=>'0','left'=>'0','isLinked'=>true];
$corners=['unit'=>'px','top'=>'12','right'=>'12','bottom'=>'12','left'=>'12','isLinked'=>true];
$inner=function(DOMNode $node) use ($dom) { $s=''; foreach($node->childNodes as $child) {$s.=$dom->saveHTML($child);} return $s; };
$classes=function(DOMElement $node) { return trim($node->getAttribute('class')); };
$widget=function($type,$settings,$class='',$title='') use ($new_id,$zero) {
    return ['id'=>$new_id(),'elType'=>'widget','widgetType'=>$type,'settings'=>['_css_classes'=>$class,'_title'=>$title,'_margin'=>$zero]+$settings,'elements'=>[]];
};
$container=function($class,$children,$title='',$settings=[]) use ($new_id,$zero,$corners) {
    return ['id'=>$new_id(),'elType'=>'container','isInner'=>false,'settings'=>array_merge(['content_width'=>'full','flex_direction'=>'column','flex_gap'=>['unit'=>'px','size'=>0,'column'=>'0','row'=>'0','isLinked'=>true],'padding'=>$zero,'border_radius'=>$corners,'css_classes'=>$class,'_title'=>$title],$settings),'elements'=>$children];
};
$attribute_settings=function(DOMElement $node) {
    $s=[]; $attrs=[];
    if ($node->hasAttribute('id') && $node->getAttribute('id')!=='content') {$s['_element_id']=$node->getAttribute('id');}
    foreach (['lang','aria-label','aria-labelledby'] as $key) { if ($node->hasAttribute($key)) {$attrs[]=$key.'|'.$node->getAttribute($key);} }
    if ($attrs) {$s['_attributes']=implode("\n",$attrs);}
    return $s;
};
wp_mkdir_p(__DIR__.'/about-icons');
$icon_files=[];
$convert=null;
$convert=function(DOMElement $node) use (&$convert,$classes,$inner,$widget,$container,$attribute_settings,$corners,$xpath,$dom,&$icon_files) {
    $tag=strtolower($node->tagName); $class=$classes($node); $attrs=$attribute_settings($node);
    $plain=trim(preg_replace('/\s+/u',' ',$node->textContent));
    if ($tag==='span' && (strpos($class,'ja-icon-tile')!==false || strpos($class,'ja-read-arrow')!==false)) {
        $svg=$node->getElementsByTagName('svg')->item(0);
        if ($svg) {
            $svg_html=$dom->saveHTML($svg);
            $svg_html=str_replace('viewbox=','viewBox=',$svg_html);
            if (strpos($svg_html,'xmlns=')===false) {$svg_html=str_replace('<svg ','<svg xmlns="http://www.w3.org/2000/svg" ',$svg_html);}
            $svg_html=str_replace('currentColor','#236b9c',$svg_html);
            $name='icon-'.substr(hash('sha256',$svg_html),0,12).'.svg';
            file_put_contents(__DIR__.'/about-icons/'.$name,$svg_html);
            $icon_files[$name]=true;
            return $widget('image',['image'=>['url'=>home_url('/preview-assets/about-icons/'.$name),'id'=>0,'alt'=>''],'image_size'=>'full','link_to'=>'none','_border_radius'=>$corners]+$attrs,$class,'Editable section icon');
        }
    }
    if ($tag==='img') {
        $url=$node->getAttribute('src'); $attachment_id=attachment_url_to_postid(preg_replace('/-\d+x\d+(\.[a-zA-Z0-9]+)$/','$1',$url));
        return $widget('image',['image'=>['url'=>$url,'id'=>$attachment_id,'alt'=>$node->getAttribute('alt')],'image_size'=>'full','link_to'=>'none']+$attrs,$class,'Section photograph');
    }
    if (preg_match('/^h[1-6]$/',$tag) || $tag==='strong') {
        $link=$node->getElementsByTagName('a')->item(0);
        $settings=['title'=>$link?$inner($link):$inner($node),'header_size'=>$tag==='strong'?'span':$tag];
        if ($link) {$settings['link']=['url'=>$link->getAttribute('href')];}
        return $widget('heading',$settings+$attrs,trim($class.($tag==='strong'?' ja-native-strong':'')),$plain);
    }
    if ($tag==='a' && strpos($class,'ja-insight-image')===false) {
        $settings=['text'=>$plain,'link'=>['url'=>$node->getAttribute('href'),'is_external'=>$node->getAttribute('target')==='_blank'?'on':''],'selected_icon'=>['value'=>'','library'=>''],'border_radius'=>$corners];
        return $widget('button',$settings+$attrs,$class?:'ja-plain-link',$plain);
    }
    if ($tag==='p' || $tag==='blockquote' || $tag==='figcaption') {
        return $widget('text-editor',['editor'=>$dom->saveHTML($node)]+$attrs,$tag==='blockquote'?'ja-native-blockquote':($tag==='figcaption'?'ja-native-caption':''),$plain);
    }
    if ($tag==='span') {
        if ($node->getElementsByTagName('svg')->length || $node->getElementsByTagName('span')->length) {
            return $widget('text-editor',['editor'=>$inner($node)]+$attrs,trim($class.' ja-native-inline'),$plain);
        }
        $settings=['title'=>$inner($node),'header_size'=>'span'];
        if (preg_match('/(?:ja-read-arrow|ja-icon-tile)/',$class)) {$settings['_border_radius']=$corners;}
        return $widget('heading',$settings+$attrs,trim($class.' ja-native-span'),$plain);
    }
    // Inline copy stays in a native Text Editor; the layout remains native containers.
    $has_text=false;
    foreach ($node->childNodes as $child) { if ($child->nodeType===XML_TEXT_NODE && trim($child->textContent)!=='') {$has_text=true;} }
    if ($has_text) {
        return $widget('text-editor',['editor'=>$inner($node)]+$attrs,trim($class.' ja-native-inline'),$plain);
    }
    $children=[];
    foreach ($node->childNodes as $child) { if ($child instanceof DOMElement) {$children[]=$convert($child);} }
    $settings=$attrs;
    if (in_array($tag,['section','article','nav','a'],true)) {$settings['html_tag']=$tag;}
    if ($tag==='a') {$settings['link']=['url'=>$node->getAttribute('href')];}
    if ($node->hasAttribute('style') && preg_match('/--ja-(hero|impact)-image:url\([\x27\x22]?([^\x27\x22\)]+)/',$node->getAttribute('style'),$m)) {
        $settings['background_background']='classic';
        $settings['background_image']=['url'=>$m[2],'id'=>attachment_url_to_postid($m[2])];
        $settings['background_position']='initial';
        $settings['background_xpos']=['unit'=>'%','size'=>50];
        $settings['background_ypos']=['unit'=>'%','size'=>$m[1]==='hero'?55:50];
        if ($m[1]==='hero') {
            $settings['background_xpos_mobile']=['unit'=>'%','size'=>65];
            $settings['background_ypos_mobile']=['unit'=>'%','size'=>50];
        }
        $settings['background_size']='cover';
        $settings['background_repeat']='no-repeat';
    }
    $section_titles=['ja-hero'=>'About hero banner','ja-story'=>'Our story and experience','ja-mission'=>'Our mission','ja-values'=>'Why choose Juhani','ja-impact'=>'Community impact','ja-reviews'=>'Customer reviews','ja-insights-wrap'=>'Journal articles','ja-contact'=>'Talk to Juhani'];
    $title=$class?:ucfirst($tag).' content';
    foreach($section_titles as $key=>$label) {if(in_array($key,explode(' ',$class),true)) {$title=$label;break;}}
    return $container($class,$children,$title,$settings);
};
$root=$convert($main);
$root['settings']['_title']='About Us — editable preview body';
$scope='.elementor-'.$page_id.' .ja-preview';

// Reuse the preview CSS, adapting selectors only for native widget wrappers.
function native_about_selectors($selectors,$scope) {
    $selectors=str_replace('.juhani-about-preview #content.ja-preview','.ja-preview',$selectors);
    $selectors=str_replace('.ja-button.ja-button-light','.__ja_light_button__',$selectors);
    $selectors=preg_replace('/\.ja-button(?![\w-])/','.ja-button .elementor-button',$selectors);
    $selectors=str_replace('.__ja_light_button__','.ja-button.ja-button-light .elementor-button',$selectors);
    $selectors=preg_replace('/>strong/','>.ja-native-strong .elementor-heading-title',$selectors);
    $selectors=preg_replace('/(?<![\w-])strong\b/','.ja-native-strong .elementor-heading-title',$selectors);
    $selectors=str_replace('>.ja-native-.ja-native-strong .elementor-heading-title .elementor-heading-title','>.ja-native-strong .elementor-heading-title',$selectors);
    $selectors=str_replace('.ja-story-visual>img','.ja-story-visual img',$selectors);
    $selectors=str_replace('.ja-experience>div>span','.ja-experience>.ja-native-inline>span',$selectors);
    $selectors=str_replace('.ja-impact-stats div>span','.ja-impact-stats .ja-native-span .elementor-heading-title',$selectors);
    $selectors=str_replace('.ja-rating>div>span','.ja-rating>.e-con>.ja-native-span .elementor-heading-title',$selectors);
    $selectors=str_replace('.ja-story-line>span','.ja-story-line>.ja-native-inline',$selectors);
    $selectors=str_replace('.ja-breadcrumb>span:nth-child(2)','.ja-breadcrumb>.ja-native-span:nth-child(2)',$selectors);
    $selectors=str_replace(' svg',' svg',$selectors);
    return str_replace('.ja-preview',$scope,$selectors);
}
function native_about_css($css,$scope) {
    $css=preg_replace('/\/\*[\s\S]*?\*\//','',$css);$out='';$at=0;$length=strlen($css);
    while ($at<$length) {
        $open=strpos($css,'{',$at);if($open===false)break;
        $selector=trim(substr($css,$at,$open-$at));$depth=1;$end=$open+1;
        while($end<$length&&$depth){if($css[$end]==='{')$depth++;elseif($css[$end]==='}')$depth--;$end++;}
        $body=substr($css,$open+1,$end-$open-2);
        if(strpos($selector,'@media')===0){$body=native_about_css($body,$scope);}
        else {
            $selector=native_about_selectors($selector,$scope);
            if (!preg_match('/::?(?:before|after)|\bsvg\b/',$selector)) {$body=preg_replace('/(?<![\w-])border-radius\s*:[^;{}]+;?/i','',$body);}
            // Background images live in editable native Elementor image controls.
            $body=str_replace('background:#122e43 var(--ja-hero-image) center 55%/cover no-repeat','background-color:#122e43',$body);
            $body=str_replace('background:#122e43 var(--ja-impact-image) center/cover no-repeat','background-color:#122e43',$body);
        }
        if(trim($body)!=='')$out.=$selector.'{'.$body.'}'.PHP_EOL;
        $at=$end;
    }
    return $out;
}
$reset=<<<CSS
$scope.e-con,$scope .e-con{display:block;--width:100%;--container-widget-width:100%;--container-widget-flex-grow:0;--padding-top:0px;--padding-right:0px;--padding-bottom:0px;--padding-left:0px;--gap:0px;gap:0;min-width:0;max-width:none;flex-direction:row;flex-wrap:nowrap;align-items:stretch;justify-content:normal;flex-shrink:1;margin:0;padding:0;border:0}
$scope .elementor-widget{margin:0;min-width:0;max-width:100%;--container-widget-width:100%;--container-widget-flex-grow:0}
$scope .elementor-widget-text-editor{font:inherit;color:inherit}
$scope .elementor-heading-title{font-family:inherit;color:inherit;padding:0}
$scope .elementor-button{font:inherit;color:inherit;background:transparent;border:0;padding:0;box-shadow:none}
$scope .elementor-button-content-wrapper{display:flex;align-items:center;justify-content:center;gap:inherit}
$scope .elementor-button-text{flex-grow:0}
CSS;
$arrow=home_url('/preview-assets/icons/arrow.svg');
$phone=home_url('/preview-assets/icons/phone.svg');
$adapter=<<<CSS
$scope.e-con{padding:24px 0 0}
$scope .ja-eyebrow .elementor-heading-title,$scope .ja-small-label .elementor-heading-title,$scope .ja-native-span .elementor-heading-title{font:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0}
$scope .ja-hero-copy>.elementor-widget-button,$scope .ja-contact-buttons>.elementor-widget,$scope .ja-breadcrumb>.elementor-widget{width:auto}
$scope .ja-button .elementor-button-content-wrapper{gap:24px}
$scope .ja-button .elementor-button-content-wrapper:after,$scope .ja-link .elementor-button-content-wrapper:after,$scope .ja-article-link .elementor-button-content-wrapper:after{content:'';display:block;width:20px;height:20px;flex-shrink:0;background:currentColor;mask:url('$arrow') center/contain no-repeat}
$scope .ja-link .elementor-button-content-wrapper{gap:20px}
$scope .ja-article-link .elementor-button-content-wrapper{gap:15px}
$scope .ja-link .elementor-button-content-wrapper:after{width:18px;height:18px}
$scope .ja-article-link .elementor-button-content-wrapper:after{width:16px;height:16px}
$scope .ja-button .elementor-button{gap:24px}
$scope .ja-phone .elementor-button-content-wrapper{gap:9px}
$scope .ja-phone .elementor-button-content-wrapper:before{content:'';width:17px;height:17px;background:currentColor;mask:url('$phone') center/contain no-repeat}
$scope .ja-link,$scope .ja-article-link,$scope .ja-phone{width:fit-content}
$scope .ja-link .elementor-button,$scope .ja-article-link .elementor-button,$scope .ja-phone .elementor-button,$scope .ja-plain-link .elementor-button{font:inherit;color:inherit}
$scope .ja-hero-note{width:auto}
$scope .ja-story-visual>.elementor-widget-image,$scope .ja-story-visual>.elementor-widget-image>.elementor-widget-container,$scope .ja-contact-image>.elementor-widget-image,$scope .ja-contact-image>.elementor-widget-image>.elementor-widget-container,$scope .ja-insight-image>.elementor-widget-image,$scope .ja-insight-image>.elementor-widget-image>.elementor-widget-container{height:100%}
$scope .ja-story-visual img,$scope .ja-contact-image img,$scope .ja-insight-image img{display:block;width:100%;height:100%;object-fit:cover}
$scope .ja-insight-image img{object-fit:contain}
$scope .ja-experience{width:auto}
$scope .ja-experience>.ja-native-strong{width:auto;flex-shrink:0}
$scope .ja-experience>.ja-native-inline{width:auto}
$scope .ja-mission-heading>.ja-icon-tile,$scope .ja-value-top>.ja-icon-tile{width:48px;flex:0 0 48px}
$scope .ja-icon-tile img{width:24px;height:24px;margin:auto}
$scope .ja-card-number{width:auto}
$scope .ja-mission-copy>.elementor-widget-text-editor+.elementor-widget-text-editor{margin-top:16px}
$scope .ja-mission-copy p+p{margin-top:16px}
$scope .ja-section-heading>.e-con{max-width:730px;flex:1;width:auto}
$scope .ja-section-heading>.elementor-widget-text-editor{max-width:350px;flex:0 1 350px}
$scope .ja-section-heading>.ja-link{flex-shrink:0}
$scope .ja-review-intro>.e-con{width:auto}
$scope .ja-rating>.ja-native-strong{width:auto;flex-shrink:0}
$scope .ja-rating>.e-con{width:auto}
$scope .ja-rating .ja-stars .elementor-heading-title{font:inherit;color:inherit}
$scope .ja-review-card>.ja-native-blockquote{flex:1;width:100%}
$scope .ja-review-card>.ja-native-caption{width:100%}
$scope .ja-read-arrow{width:36px}
$scope .ja-read-arrow img{width:17px;height:17px;margin:auto}
$scope .ja-preview-note>.ja-native-inline{display:flex;align-items:center;gap:8px;width:auto}
$scope .ja-preview-note>.elementor-widget-button{width:auto}
$scope .ja-preview-note>.ja-plain-link .elementor-button-content-wrapper{gap:8px}
$scope .ja-preview-note>.ja-plain-link .elementor-button-content-wrapper:after{content:'';width:14px;height:14px;background:currentColor;mask:url('$arrow') center/contain no-repeat}
body.elementor-page-$page_id .site-main{max-width:none;margin:0;padding:0}
body.elementor-page-$page_id .entry-content{margin:0}
@media(max-width:1024px){ $scope .ja-section-heading>.e-con{width:auto}$scope .ja-rating>.e-con{width:auto}}
@media(max-width:767px){
$scope.e-con{padding-top:12px}
$scope .ja-link .elementor-button-content-wrapper{gap:15px}
$scope .ja-mission-heading>.ja-icon-tile,$scope .ja-value-top>.ja-icon-tile{width:44px;flex-basis:44px}
$scope .ja-button .elementor-button-content-wrapper{gap:18px}
$scope .ja-hero-copy>.ja-button{width:auto}
$scope .ja-contact-buttons>.ja-button,$scope .ja-contact-buttons>.ja-button .elementor-button{width:100%}
$scope .ja-contact-buttons .ja-button .elementor-button-content-wrapper{gap:18px}
$scope .ja-section-heading>.elementor-widget-text-editor{max-width:none;flex:none}
$scope .ja-review-intro>.ja-rating{width:100%}
}
CSS;
$css=$reset.PHP_EOL.native_about_css(file_get_contents(__DIR__.'/about.css'),$scope).$adapter;
$template=['version'=>'0.4','title'=>'About us Rahmat — Juhani','type'=>'page','content'=>[$root],'page_settings'=>['hide_title'=>'yes','template'=>'elementor_header_footer','custom_css'=>$css]];
file_put_contents(__DIR__.'/about-rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL);
file_put_contents(__DIR__.'/about-rahmat.css',$css);
$nodes=[];$widget_counts=[];
$walk=function($items) use (&$walk,&$nodes,&$widget_counts) {foreach($items as $n){if(isset($nodes[$n['id']]))throw new RuntimeException('Duplicate ID');$nodes[$n['id']]=true;if($n['elType']==='widget'){$widget_counts[$n['widgetType']]=($widget_counts[$n['widgetType']]??0)+1;}$walk($n['elements']??[]);}};
$walk($template['content']);
if (($argv[1]??'')!=='apply') {echo wp_json_encode(['page_id'=>$page_id,'nodes'=>count($nodes),'widgets'=>$widget_counts,'status'=>'Ready to apply'],JSON_PRETTY_PRINT).PHP_EOL;exit;}
$backup_path=dirname(__DIR__).'/.about-us-rahmat-before-design.json';
if (!file_exists($backup_path)) {
    $backup=['post'=>['ID'=>$page_id,'post_content'=>$page->post_content,'post_status'=>$page->post_status],'meta'=>get_post_meta($page_id)];
    if (file_put_contents($backup_path,wp_json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))===false) {throw new RuntimeException('Backup failed');}
}
global $wpdb;
$protected_hash=function() use ($wpdb) {return hash('sha256',serialize($wpdb->get_results("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id IN (2,55,1261,1267) AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",ARRAY_A)));};
$before=$protected_hash();
$admin=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);if(!$admin)throw new RuntimeException('Local administrator missing');wp_set_current_user($admin[0]);
$document=\Elementor\Plugin::$instance->documents->get($page_id);
$settings=(array)get_post_meta($page_id,'_elementor_page_settings',true);
$settings['hide_title']='yes';$settings['custom_css']=$css;$settings['post_status']=$page->post_status;
$document->set_is_built_with_elementor(true);
if (!$document->save(['elements'=>$template['content'],'settings'=>$settings])) {throw new RuntimeException('Elementor page save failed');}
update_post_meta($page_id,'_wp_page_template','elementor_header_footer');
\Elementor\Core\Files\CSS\Post::create($page_id)->update();
if ($before!==$protected_hash()) {throw new RuntimeException('Unexpected change to existing pages/header/footer');}
echo wp_json_encode(['page_id'=>$page_id,'url'=>get_permalink($page_id),'edit_url'=>admin_url('post.php?post='.$page_id.'&action=elementor'),'nodes'=>count($nodes),'widgets'=>$widget_counts,'existing_pages'=>'About, Rahmat, header and footer preserved'],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
