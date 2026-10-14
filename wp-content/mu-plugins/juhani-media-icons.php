<?php
/**
 * Plugin Name: Juhani Media Library Icons
 * Description: Resolves the site's button icon masks from WordPress attachment URLs.
 */
if (!defined('ABSPATH')) exit;

add_action('wp_enqueue_scripts',function(){
    $ids=get_option('juhani_media_icons',[]);
    $arrow=wp_get_attachment_url((int)($ids['arrow']??0));
    $phone=wp_get_attachment_url((int)($ids['phone']??0));
    if(!$arrow||!$phone)return;
    wp_register_style('juhani-media-icons',false);
    wp_enqueue_style('juhani-media-icons');
    $css='.rahmat-page,.ja-preview,.jb-preview,.jb-native-arrow,.jb-phone,.juhani-loop-card{';
    $css.='--juhani-arrow-mask:url("'.esc_url_raw($arrow).'");';
    $css.='--juhani-phone-mask:url("'.esc_url_raw($phone).'");}';
    wp_add_inline_style('juhani-media-icons',$css);
},30);
