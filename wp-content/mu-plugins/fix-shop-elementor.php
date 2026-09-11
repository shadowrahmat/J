<?php
/**
 * Plugin Name: Disable WooCommerce Shop Archive Link
 * Description: Disables the automatic WooCommerce /shop/ product archive so the link is closed/404.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Disable WooCommerce product post type archive registration
add_filter( 'woocommerce_register_post_type_product', function( $args ) {
	$args['has_archive'] = false;
	return $args;
}, 99 );

// 2. Intercept any archive or shop request for products and return 404
add_action( 'template_redirect', function() {
	if ( is_post_type_archive( 'product' ) || ( function_exists( 'is_shop' ) && is_shop() ) ) {
		// Only trigger 404 if there is no normal published page currently handling the request
		$page_id = get_queried_object_id();
		$post = $page_id ? get_post( $page_id ) : null;
		if ( ! $post || 'page' !== $post->post_type || 'publish' !== $post->post_status ) {
			global $wp_query;
			$wp_query->set_404();
			status_header( 404 );
			nocache_headers();
		}
	}
}, 1 );

