<?php
/**
 * Plugin Name: Juhani Blog Rahmat Search
 * Description: Loads article search/filter behaviour without a hidden Elementor HTML widget.
 */
if(!defined('ABSPATH'))exit;
add_action('wp_enqueue_scripts',function(){
    if(!is_page(1274))return;
    $path=ABSPATH.'preview-assets/blog.js';
    if(is_file($path))wp_enqueue_script('juhani-blog-rahmat-search',home_url('/preview-assets/blog.js'),[],filemtime($path),true);
});
