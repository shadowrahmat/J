<?php
/** Native Elementor single product template. CLI-only; first run builds a reviewable JSON. */
if (PHP_SAPI !== 'cli') {http_response_code(404);exit;}
define('WP_ADMIN', true);
require dirname(__DIR__).'/wp-load.php';
$template_id=37;
$post=get_post($template_id);
if (!$post || $post->post_type!=='elementor_library' || get_post_meta($template_id,'_elementor_template_type',true)!=='product') throw new RuntimeException('Single product template identity mismatch');
$admins=get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);wp_set_current_user($admins[0]);
$seq=0x8500000;$id=function()use(&$seq){return dechex(++$seq);};
$dims=function($top=0,$right=null,$bottom=null,$left=null){return ['unit'=>'px','top'=>(string)$top,'right'=>(string)($right??$top),'bottom'=>(string)($bottom??$top),'left'=>(string)($left??$right??$top),'isLinked'=>$right===null];};
$gap=function($n){return ['unit'=>'px','size'=>$n,'column'=>(string)$n,'row'=>(string)$n,'isLinked'=>true];};
$zero=$dims();$radius=$dims(12);
$typography=function($size,$weight=400,$line=1.85,$prefix='typography'){return [$prefix.'_typography'=>'custom',$prefix.'_font_family'=>'Segoe UI',$prefix.'_font_size'=>['unit'=>'px','size'=>$size],$prefix.'_font_weight'=>(string)$weight,$prefix.'_line_height'=>['unit'=>'em','size'=>$line]];};
$widget=function($type,$title,$settings=[],$classes='')use($id,$zero){return ['id'=>$id(),'elType'=>'widget','widgetType'=>$type,'settings'=>array_merge(['_title'=>$title,'_css_classes'=>$classes,'_margin'=>$zero,'_padding'=>$zero,'_element_width'=>'inherit','_element_cache'=>'yes'],$settings),'elements'=>[]];};
$container=function($title,$children=[],$settings=[],$classes='')use($id,$zero,$gap,$radius){return ['id'=>$id(),'elType'=>'container','isInner'=>false,'settings'=>array_merge(['_title'=>$title,'css_classes'=>$classes,'container_type'=>'flex','content_width'=>'full','width'=>['unit'=>'%','size'=>100],'flex_direction'=>'column','flex_gap'=>$gap(0),'flex_align_items'=>'stretch','padding'=>$zero,'margin'=>$zero,'border_radius'=>$radius],$settings),'elements'=>$children];};
$row=function($title,$children,$settings=[],$classes='')use($container,$gap){return $container($title,$children,array_merge(['flex_direction'=>'row','flex_align_items'=>'center','flex_gap'=>$gap(12)],$settings),$classes);};
$section=function($title,$children,$top=0,$bottom=0,$classes='')use($container,$dims){return $container($title,$children,['html_tag'=>'section','content_width'=>'boxed','boxed_width'=>['unit'=>'px','size'=>1350],'padding'=>$dims($top,20,$bottom),'padding_tablet'=>$dims($top,24,$bottom),'padding_mobile'=>$dims($top,20,$bottom),'_attributes'=>'lang|bn'],'juhani-single-section '.$classes);};
$heading=function($text,$title,$size=13,$color='#223747',$weight=400,$tag='span',$settings=[],$classes='')use($widget,$typography){return $widget('heading',$title,array_merge(['title'=>$text,'header_size'=>$tag,'title_color'=>$color],$typography($size,$weight),$settings),$classes);};
$text=function($html,$title,$size=13,$color='#657783',$settings=[],$classes='')use($widget,$typography){return $widget('text-editor',$title,array_merge(['editor'=>$html,'text_color'=>$color],$typography($size),$settings),$classes);};
$eyebrow=function($label,$title,$settings=[])use($heading,$dims){return $heading($label,$title,9,'#236b9c',700,'span',array_merge(['typography_letter_spacing'=>['unit'=>'px','size'=>1.75],'typography_line_height'=>['unit'=>'em','size'=>1.6],'_margin'=>$dims(0,0,12,0)],$settings));};
$button=function($label,$url,$title,$settings=[],$classes='')use($widget,$typography,$dims,$radius){return $widget('button',$title,array_merge(['text'=>$label,'link'=>['url'=>$url],'align'=>'left','selected_icon'=>['value'=>'fas fa-arrow-right','library'=>'fa-solid'],'icon_align'=>'right','icon_indent'=>['unit'=>'px','size'=>18],'button_text_color'=>'#236b9c','background_background'=>'classic','background_color'=>'#00000000','button_background_hover_background'=>'classic','button_background_hover_color'=>'#00000000','hover_color'=>'#122e43','border_border'=>'none','border_radius'=>$radius,'text_padding'=>$dims(0),'_element_width'=>'auto'],$typography(11,600),$settings),$classes);};
$dynamic=function($name,$settings=[])use($id){return '[elementor-tag id="'.$id().'" name="'.$name.'" settings="'.rawurlencode(wp_json_encode($settings)).'"]';};

// Related card is its own native Loop Item, so image/title/price/button remain separately editable.
$card=[
 $container('Product card - Photo',[
  $widget('image','Product card - Dynamic image',['image'=>['url'=>'','id'=>0],'__dynamic__'=>['image'=>$dynamic('woocommerce-product-image-tag'),'link'=>$dynamic('post-url')],'image_size'=>'woocommerce_thumbnail','width'=>['unit'=>'%','size'=>100],'space'=>['unit'=>'%','size'=>100],'height'=>['unit'=>'custom','size'=>'auto'],'object-fit'=>'contain','link_to'=>'custom','link'=>['url'=>''],'align'=>'center'],'juhani-single-related-photo')
 ],['background_background'=>'classic','background_color'=>'#eef4f8']),
 $container('Product card - Information',[
  $eyebrow('CAST NET','Product card - Category label',['title_color'=>'#8a9eac','typography_font_size'=>['unit'=>'px','size'=>8],'typography_font_weight'=>'400','typography_letter_spacing'=>['unit'=>'px','size'=>1.2],'_margin'=>$dims(0,0,10,0)]),
  $heading('','Product card - Dynamic title',18,'#223747',600,'h3',['__dynamic__'=>['title'=>$dynamic('woocommerce-product-title-tag'),'link'=>$dynamic('post-url')],'link'=>['url'=>''],'_margin'=>$dims(0,0,11,0),'typography_line_height'=>['unit'=>'em','size'=>1.5],'typography_font_size_mobile'=>['unit'=>'px','size'=>14]]),
  $heading('','Product card - Dynamic price',16,'#122e43',600,'span',['__dynamic__'=>['title'=>$dynamic('woocommerce-product-price-tag',['format'=>'both'])],'_margin'=>$dims(0,0,17,0),'typography_line_height'=>['unit'=>'em','size'=>1.5]],'juhani-single-related-price'),
  $button('বিকল্প দেখুন','','Product card - Product link',['__dynamic__'=>['link'=>$dynamic('post-url')],'align'=>'justify','_element_width'=>'inherit','text_padding'=>$dims(9,13),'border_border'=>'solid','border_width'=>$dims(1),'border_color'=>'#d8e5ee','typography_font_size_mobile'=>['unit'=>'px','size'=>10]])
 ],['padding'=>$dims(20,21),'padding_mobile'=>$dims(15,13)])
];
$card_data=[$container('Related product - Editable card',$card,['border_border'=>'solid','border_width'=>$dims(1),'border_color'=>'#e1eaf0','border_radius'=>$radius,'overflow'=>'hidden','background_background'=>'classic','background_color'=>'#ffffff'],'juhani-single-related-card')];
$loop_id=(int)get_post_meta($template_id,'_juhani_related_loop_id',true);

$data=[];
$data[]=$section('01 - Cart notices',[$widget('woocommerce-notices','WooCommerce messages')],0,0,'juhani-single-notices');
$data[]=$section('02 - Breadcrumb',[$widget('woocommerce-breadcrumb','Home / Shop / Product',['text_color'=>'#657783','link_color'=>'#657783']+$typography(11,400,1.8),'juhani-single-breadcrumb')],28,29);
$gallery=$container('Gallery - Images and note',[
 $widget('woocommerce-product-images','Product gallery - dynamic photos',['sale_flash'=>'','image_border_radius'=>$radius,'thumbs_border_radius'=>$radius,'image_border_border'=>'solid','image_border_width'=>$dims(1),'image_border_color'=>'#e3ebf1','thumbs_border_border'=>'solid','thumbs_border_width'=>$dims(1),'thumbs_border_color'=>'#e0e9f0','spacing'=>['unit'=>'px','size'=>17],'spacing_thumbs'=>['unit'=>'px','size'=>0]],'juhani-product-gallery'),
 $text('<p>জালের বুনন, বাঁধন ও ফিনিশিং—ছবিগুলোতে কাছ থেকে দেখুন।</p>','Gallery - Caption',10,'#8094a2',['_margin'=>$dims(18,1,0,1)])
],['width'=>['unit'=>'custom','size'=>'calc((100% - 65px) * 1.08 / 2.08)'],'width_tablet'=>['unit'=>'custom','size'=>'calc((100% - 35px) / 2)'],'width_mobile'=>['unit'=>'%','size'=>100]],'juhani-single-gallery-column');
$topline=$row('Product - Collection and stock',[
 $eyebrow('THE JUHANI COLLECTION / CAST NETS','Collection label',['_margin'=>$zero,'_element_width'=>'auto','typography_font_size'=>['unit'=>'px','size'=>8],'typography_letter_spacing'=>['unit'=>'px','size'=>1.5]]),
 $widget('woocommerce-product-stock','Current stock',['_element_width'=>'auto','text_color'=>'#4c806b']+$typography(10,400,1.8,'text_typography'),'juhani-single-stock')
],['flex_justify_content'=>'space-between','margin'=>$dims(0,0,17,0),'flex_wrap'=>'wrap']);
$product_title=$widget('woocommerce-product-title','Product - Dynamic title',['__dynamic__'=>['title'=>$dynamic('woocommerce-product-title-tag')],'header_size'=>'h1','title_color'=>'#223747','typography_font_size_tablet'=>['unit'=>'px','size'=>29],'typography_font_size_mobile'=>['unit'=>'px','size'=>29],'typography_letter_spacing'=>['unit'=>'px','size'=>-1.1],'_margin'=>$dims(0,0,14,0)]+$typography(37,650,1.25),'juhani-single-title');
$price_block=$container('Product - Price and note',[
 $widget('woocommerce-product-price','Product - Dynamic price',['price_color'=>'#122e43','sale_price_color'=>'#122e43','typography_letter_spacing'=>['unit'=>'px','size'=>-.7]]+$typography(29,600,1.4),'juhani-single-price'),
 $text('<p>বিকল্প অনুযায়ী দাম পরিবর্তিত হবে।</p>','Product - Price note',10,'#8b9ca8',['_margin'=>$dims(6,0,0,0)])
],['padding'=>$dims(0,0,23,0),'margin'=>$dims(0,0,23,0),'border_border'=>'solid','border_width'=>$dims(0,0,1,0),'border_color'=>'#e1eaf0']);
$purchase_heading=$row('Purchase - Heading',[
 $heading('01','Purchase - Step number',9,'#236b9c',600,'span',['_element_width'=>'auto','_background_background'=>'classic','_background_color'=>'#eef6fc','_border_radius'=>$radius,'_padding'=>$dims(6,9)]),
 $heading('আপনার মাপ বেছে নিন','Purchase - Heading text',17,'#223747',600,'h2',['_element_width'=>'auto','typography_line_height'=>['unit'=>'em','size'=>1.7]])
],['margin'=>$dims(0,0,5,0)]);
$purchase=$widget('woocommerce-product-add-to-cart','Purchase - Native options, quantity and add to cart',[
 'layout'=>'auto','show_quantity'=>'yes','button_text_color'=>'#ffffff','button_bg_color'=>'#236b9c','button_bg_color_hover'=>'#195781','button_text_color_hover'=>'#ffffff','button_border_border'=>'solid','button_border_width'=>$dims(1),'button_border_color'=>'#236b9c','button_border_radius'=>$radius,'button_padding'=>$dims(13,18),'quantity_text_color'=>'#223747','quantity_bg_color'=>'#ffffff','quantity_border_border'=>'none','quantity_padding'=>$zero,'variations_label_color'=>'#557086','variations_select_color'=>'#223747','variations_select_bg_color'=>'#ffffff','variations_select_border_border'=>'solid','variations_select_border_width'=>$dims(1),'variations_select_border_color'=>'#d8e5ee'
]+$typography(13,600,1.8,'button_typography')+$typography(13,400,1.8,'quantity_typography')+$typography(11,600,1.8,'variations_label_typography'),'juhani-single-purchase');
$support=$row('Support - WhatsApp and telephone',[
 $button('মাপ বাছাইয়ে সাহায্য চান?','https://wa.me/8801889164315','Support - WhatsApp',['link'=>['url'=>'https://wa.me/8801889164315','is_external'=>'on'],'typography_font_weight'=>'400']),
 $button('+880 1889 164315','tel:+8801889164315','Support - Phone',['selected_icon'=>['value'=>'fas fa-phone-alt','library'=>'fa-solid'],'icon_align'=>'left','button_text_color'=>'#728b9d','typography_font_weight'=>'400'])
],['flex_justify_content'=>'space-between','flex_wrap'=>'wrap','margin'=>$dims(20,0,19,0),'padding'=>$dims(21,0,0,0),'border_border'=>'solid','border_width'=>$dims(1,0,0,0),'border_color'=>'#e1eaf0'],'juhani-single-support');
$summary=$container('Product - Editable information',[
 $topline,$product_title,
 $text('<p>হাত, সুতা, বাঁধন ও ওজন মিলিয়ে আপনার কাজের জন্য উপযুক্ত বিকল্পটি বেছে নিন।</p>','Product - Introduction',13,'#657783',['_margin'=>$dims(0,0,22,0)]),
 $widget('woocommerce-product-short-description','Product - WooCommerce short description',['text_color'=>'#657783']+$typography(13),'juhani-single-short-description'),
 $price_block,$purchase_heading,$purchase,$support,
 $widget('woocommerce-product-meta','Product - SKU and categories',['text_color'=>'#8e9eaa','link_color'=>'#8e9eaa','caption_color'=>'#8e9eaa','sku_caption'=>'SKU:','category_caption_single'=>'Category:','category_caption_plural'=>'Categories:']+$typography(9,400,1.8),'juhani-single-meta')
],['width'=>['unit'=>'custom','size'=>'calc((100% - 65px) / 2.08)'],'width_tablet'=>['unit'=>'custom','size'=>'calc((100% - 35px) / 2)'],'width_mobile'=>['unit'=>'%','size'=>100],'padding'=>$dims(3,0,0,0)],'juhani-single-summary');
$data[]=$section('03 - Product gallery and purchase',[$row('Product - Two columns',[$gallery,$summary],['flex_align_items'=>'flex-start','flex_gap'=>$gap(65),'flex_gap_tablet'=>$gap(35),'flex_direction_mobile'=>'column','flex_gap_mobile'=>$gap(30)],'juhani-single-overview')],0,58);
$details_head=$container('Details - Heading',[
 $eyebrow('KNOW YOUR NET','Details - Eyebrow'),$heading('প্রতিটি মাপের তথ্য, এক নজরে।','Details - Title',28,'#223747',600,'h2',['typography_font_size_mobile'=>['unit'=>'px','size'=>23],'typography_line_height'=>['unit'=>'em','size'=>1.7]])
],['margin'=>$dims(0,0,20,0)]);
$tabs=$widget('woocommerce-product-data-tabs','Details - Native specifications, description and reviews',[
 '_element_width'=>'initial','_element_custom_width'=>['unit'=>'custom','size'=>'calc((100% - 28px) * 1.6 / 2.6)'],'_element_custom_width_mobile'=>['unit'=>'%','size'=>100],
 'tab_text_color'=>'#8195a3','tab_bg_color'=>'#ffffff','active_tab_text_color'=>'#236b9c','active_tab_bg_color'=>'#ffffff','tabs_border_color'=>'#ffffff','tab_border_radius'=>['unit'=>'px','size'=>12],'panel_border_width'=>$zero,'panel_border_color'=>'#ffffff','panel_border_radius'=>$radius,'panel_bg_color'=>'#ffffff','panel_padding'=>$zero,'content_text_color'=>'#657783','content_heading_color'=>'#223747'
]+$typography(11,500,1.7,'tab_typography')+$typography(13,400,1.85,'content_typography'),'juhani-single-tabs');
$help=$container('Details - Buying guide card',[
 $eyebrow('MAKE AN INFORMED CHOICE','Guide - Eyebrow',['typography_font_size'=>['unit'=>'px','size'=>8],'typography_letter_spacing'=>['unit'=>'px','size'=>1.5]]),
 $heading('সঠিক মাপ কোনটি?','Guide - Title',21,'#223747',600,'h3',['_margin'=>$dims(0,0,12,0)]),
 $text('<p>আপনার মাছ ধরার জায়গা ও প্রয়োজন বলুন। হাত, সুতা ও ওজনের বিকল্পগুলো বুঝতে সাহায্য করব।</p>','Guide - Description',12,'#657783',['_margin'=>$dims(0,0,20,0)]),
 $button('জাল বাছাইয়ের গাইড পড়ুন',home_url('/how-to-choose-the-right-fishing-net-in-bangladesh/'),'Guide - Read link',['border_border'=>'solid','border_width'=>$dims(0,0,1,0),'border_color'=>'#c6dae8','text_padding'=>$dims(0,0,8,0),'border_radius'=>$zero])
],['width'=>['unit'=>'custom','size'=>'calc((100% - 28px) / 2.6)'],'width_mobile'=>['unit'=>'%','size'=>100],'padding'=>$dims(27,30),'border_border'=>'solid','border_width'=>$dims(1),'border_color'=>'#ddeaf4','border_radius'=>$radius,'background_background'=>'classic','background_color'=>'#f0f7fc'],'juhani-single-guide');
$details=$container('Details - Editable heading and content',[$details_head,$row('Details - Tabs and buying guide',[$tabs,$help],['flex_gap'=>$gap(28),'flex_align_items'=>'flex-start','flex_direction_mobile'=>'column'])],['padding'=>$dims(35,0,47,0),'border_border'=>'solid','border_width'=>$dims(1,0,1,0),'border_color'=>'#e1eaf0']);
$data[]=$section('04 - Specifications and reviews',[$details],0,0,'juhani-single-details');
$related_heading=$row('Related products - Heading and shop link',[
 $container('Related products - Editable heading',[$eyebrow('EXPLORE THE COLLECTION','Related - Eyebrow'),$heading('আরও কিছু জাল দেখে নিন।','Related - Title',29,'#223747',600,'h2',['typography_font_size_mobile'=>['unit'=>'px','size'=>23],'typography_line_height'=>['unit'=>'em','size'=>1.7]])],['width'=>['unit'=>'custom','size'=>'auto'],'_flex_size'=>'grow']),
 $button('সব পণ্য',home_url('/shop/'),'Related - Shop link',['border_border'=>'solid','border_width'=>$dims(0,0,1,0),'border_color'=>'#cbdeeb','text_padding'=>$dims(0,0,8,0),'border_radius'=>$zero])
],['margin'=>$dims(0,0,28,0),'flex_justify_content'=>'space-between','flex_align_items'=>'flex-end','flex_wrap_mobile'=>'wrap']);
$grid=$widget('loop-grid','Related products - Editable Loop Grid',[
 '_skin'=>'product','template_id'=>$loop_id,'columns'=>4,'columns_tablet'=>2,'columns_mobile'=>2,'posts_per_page'=>4,'product_query_post_type'=>'related_products','product_query_orderby'=>'menu_order','product_query_order'=>'asc','column_gap'=>['unit'=>'px','size'=>24],'row_gap'=>['unit'=>'px','size'=>24],'column_gap_mobile'=>['unit'=>'px','size'=>12],'row_gap_mobile'=>['unit'=>'px','size'=>16],'pagination_type'=>'','nothing_found_message_text'=>'','alternate_templates'=>[]
],'juhani-single-related-grid');
$data[]=$section('05 - Related products',[$related_heading,$grid],51,35,'juhani-single-related');
$css=file_get_contents(__DIR__.'/single-product.css');
$template=['version'=>'0.4','title'=>'Single Product - Juhani Editable Design','type'=>'product','content'=>$data,'page_settings'=>['custom_css'=>$css,'preview_type'=>'product','preview_id'=>570]];
$widgets=[];$walk=function($elements)use(&$walk,&$widgets){foreach($elements as$element){if($element['elType']==='widget'){$name=$element['widgetType'];if(!Elementor\Plugin::$instance->widgets_manager->get_widget_types($name))throw new RuntimeException('Missing widget '.$name);$widgets[$name]=($widgets[$name]??0)+1;}if(in_array($element['widgetType']??'',['html','shortcode'],true))throw new RuntimeException('Unexpected monolithic widget');$walk($element['elements']);}};$walk($data);$walk($card_data);
if(($argv[1]??'')!=='apply') {file_put_contents(__DIR__.'/single-product-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));echo wp_json_encode(['status'=>'ready','template'=>37,'sections'=>5,'widgets'=>$widgets]).PHP_EOL;exit;}
global $wpdb;
$protected=function()use($wpdb,$template_id,&$loop_id){return hash('sha256',serialize($wpdb->get_results("SELECT post_id,meta_key,meta_value FROM {$wpdb->postmeta} WHERE post_id NOT IN (".(int)$template_id.','.(int)$loop_id.") AND meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY post_id,meta_key",ARRAY_A)));};
$before=$protected();
if(!$loop_id) {
 $loop=Elementor\Plugin::$instance->documents->create('loop-item',['post_title'=>'Juhani Single Product - Related Card','post_status'=>'publish'],['_elementor_template_type'=>'loop-item']);
 if(!$loop)throw new RuntimeException('Loop item creation failed');$loop_id=$loop->get_main_id();update_post_meta($template_id,'_juhani_related_loop_id',$loop_id);
 // The new card is within the authorized single-product change, excluded from protection.
 $before=$protected();
}else $loop=Elementor\Plugin::$instance->documents->get($loop_id);
$loop->set_is_built_with_elementor(true);
$loop->save(['elements'=>$card_data,'settings'=>['post_status'=>'publish','preview_type'=>'product','preview_id'=>570]]);
if(!get_post_meta($loop_id,'_elementor_data',true))throw new RuntimeException('Loop save failed');
// Set the freshly created Loop Item ID without hard-coding a generated CSS selector.
$data[4]['elements'][1]['settings']['template_id']=$loop_id;
$template['content']=$data;
$document=Elementor\Plugin::$instance->documents->get($template_id);$document->set_is_built_with_elementor(true);
$settings=(array)get_post_meta($template_id,'_elementor_page_settings',true);$settings['custom_css']=$css;$settings['preview_type']='product';$settings['preview_id']=570;$settings['post_status']=$post->post_status;
if(!$document->save(['elements'=>$data,'settings'=>$settings]))throw new RuntimeException('Template save failed');
update_post_meta($template_id,'_juhani_single_preview_design',1);
delete_post_meta($template_id,'_elementor_element_cache');delete_post_meta($loop_id,'_elementor_element_cache');
Elementor\Core\Files\CSS\Post::create($loop_id)->update();Elementor\Core\Files\CSS\Post::create($template_id)->update();
if($before!==$protected())throw new RuntimeException('Unexpected change outside single product template/card');
file_put_contents(__DIR__.'/single-product-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
echo wp_json_encode(['template_id'=>37,'loop_id'=>$loop_id,'url'=>get_permalink(570),'edit_url'=>admin_url('post.php?post=37&action=elementor'),'sections'=>array_column(array_column($data,'settings'),'_title'),'widgets'=>$widgets,'other_pages'=>'unchanged','conditions'=>get_post_meta(37,'_elementor_conditions',true)],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
