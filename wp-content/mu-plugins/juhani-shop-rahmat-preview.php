<?php
/**
 * Plugin Name: Juhani Shop Rahmat Controls
 * Description: Supports the editable Shop Rahmat catalog search and mobile filters.
 */
if(!defined('ABSPATH'))exit;
add_filter('elementor/fonts/additional_fonts',function($fonts){$fonts['Segoe UI']='system';$fonts['Nirmala UI']='system';return $fonts;});
add_action('wp_enqueue_scripts',function(){
 if(!is_page(1278))return;
 $path=ABSPATH.'preview-assets/shop.js';
 if(is_file($path))wp_enqueue_script('juhani-shop-rahmat-controls',home_url('/preview-assets/shop.js'),['elementor-frontend'],filemtime($path),true);
},30);
