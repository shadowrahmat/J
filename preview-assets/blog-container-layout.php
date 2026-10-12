<?php
/** Shared native Elementor structure helpers. No WordPress writes. */
function juhani_blog_separate_sections(array $content): array {
    $sections=[];
    foreach($content as $element){
        $classes=explode(' ',$element['settings']['css_classes']??'');
        if($element['elType']==='container'&&in_array('jb-preview',$classes,true)){
            foreach($element['elements']??[] as $child)$sections[]=$child;
        }else{$sections[]=$element;}
    }
    $scripts=[];
    $sections=array_values(array_filter($sections,function($element)use(&$scripts){
        if(strpos($element['settings']['_css_classes']??'','jb-script-widget')!==false){$scripts[]=$element;return false;}
        return true;
    }));
    foreach($sections as &$section){
        if(in_array('jb-library',explode(' ',$section['settings']['css_classes']??''),true)){
            foreach($scripts as $script)$section['elements'][]=$script;
            $scripts=[];
        }
        $attrs=$section['settings']['_attributes']??'';
        if(strpos($attrs,'lang|')===false)$section['settings']['_attributes']=trim($attrs."\nlang|bn");
    }unset($section);
    if($scripts)throw new RuntimeException('Article library missing: script cannot be relocated');
    $titles=[
        'jb-breadcrumb'=>'01 - Breadcrumb',
        'jb-hero'=>'02 - Journal Banner',
        'jb-hero-copy'=>'Banner - Text and Button',
        'jb-hero-image'=>'Banner - Photo and Caption',
        'jb-hero-foot'=>'Banner - Small Note',
        'jb-image-note'=>'Photo - Caption',
        'jb-feature'=>'03 - Featured Guide',
        'jb-feature-image'=>'Featured Guide - Photo',
        'jb-feature-copy'=>'Featured Guide - Text and Link',
        'jb-library'=>'04 - Article Library',
        'jb-section'=>'Library - Content Width',
        'jb-section-heading'=>'Library - Heading and Description',
        'jb-results-line'=>'Library - Results Count',
        'jb-article-grid'=>'Articles - Individual Cards',
        'jb-article-image'=>'Article - Photo and Link',
        'jb-article-copy'=>'Article - Text',
        'jb-meta'=>'Topic and Date',
        'jb-card-footer'=>'Reading Time and Read Link',
        'jb-library-end'=>'Library - Closing Note',
        'jb-help'=>'05 - Contact and WhatsApp',
        'jb-help-actions'=>'WhatsApp and Phone Buttons',
        'jb-dot'=>'Photo - Decorative Dot',
        'jb-native-marker'=>'Decorative Line',
    ];
    $article_number=0;
    $walk=function(&$elements)use(&$walk,$titles,&$article_number){
        foreach($elements as &$element){
            if($element['elType']==='container'){
                $classes=explode(' ',$element['settings']['css_classes']??'');
                foreach($titles as $class=>$title)if(in_array($class,$classes,true)){$element['settings']['_title']=$title;break;}
                if(in_array('jb-article',$classes,true))$element['settings']['_title']='Article '.(++$article_number).' - Separate Card';
            }
            $walk($element['elements']);
        }unset($element);
    };
    $walk($sections);
    foreach($sections as $index=>&$section){
        $section['settings']['_title']=sprintf('%02d',$index+1).' - '.preg_replace('/^\d+ - /','',$section['settings']['_title']);
    }unset($section);
    return $sections;
}
function juhani_blog_flat_css(string $css): string {
    return str_replace('.elementor-1274 .jb-preview','.elementor-1274',$css);
}
