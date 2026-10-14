<?php
/**
 * Plugin Name: Juhani Blog Rahmat Search
 * Description: Loads article search/filter behaviour without a hidden Elementor HTML widget.
 */
if(!defined('ABSPATH'))exit;
add_action('wp_enqueue_scripts',function(){
    if(!is_page(1274))return;
    $path=__DIR__.'/juhani-assets/js/blog.js';
    if(is_file($path))wp_enqueue_script('juhani-blog-rahmat-search',content_url('/mu-plugins/juhani-assets/js/blog.js'),[],filemtime($path),true);
});
