<?php
/**
 * Plugin Name: Juhani Homepage FAQ Motion
 * Description: Corrects FAQ accordion height animation without changing its content or layout.
 */
if(!defined('ABSPATH'))exit;
add_action('wp_enqueue_scripts',function(){
    if(!is_page(1261))return;
    $path=__DIR__.'/juhani-assets/js/home-faq-motion.js';
    if(is_file($path))wp_enqueue_script('juhani-home-faq-motion',content_url('/mu-plugins/juhani-assets/js/home-faq-motion.js'),['elementor-frontend'],filemtime($path),true);
},30);
