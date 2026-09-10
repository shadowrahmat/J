<?php
/**
 * Plugin Name: Fix Shop Page Elementor Editor
 * Description: Enables editing the WooCommerce Shop page with Elementor by routing to the Product Archive template (Template 73) and ensuring correct preview query.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Ensure Elementor preview URL loads the product archive query for product archive Theme Builder templates
add_filter( 'elementor/document/urls/preview', function( $url, $document ) {
	if ( $document && 'product-archive' === $document->get_name() ) {
		return add_query_arg( [
			'post_type'         => 'product',
			'elementor-preview' => $document->get_main_id(),
			'ver'               => time(),
		], home_url( '/' ) );
	}
	return $url;
}, 10, 2 );

// 2. Ensure Admin Bar "Edit with Elementor" link on the Shop page points directly to the Shop Page (Post 1157)
add_action( 'admin_bar_menu', function( $wp_admin_bar ) {
	if ( ! is_admin() && function_exists( 'is_shop' ) && is_shop() && current_user_can( 'edit_posts' ) ) {
		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;
		if ( ! $shop_id ) {
			$shop_page = get_page_by_path( 'shop' );
			$shop_id = $shop_page ? $shop_page->ID : 0;
		}

		if ( $shop_id ) {
			$edit_node = $wp_admin_bar->get_node( 'elementor_edit_page' );
			if ( $edit_node ) {
				$edit_node->href = admin_url( 'post.php?post=' . $shop_id . '&action=elementor' );
				$wp_admin_bar->add_node( (array) $edit_node );
			}
		}
	}
}, 999 );

