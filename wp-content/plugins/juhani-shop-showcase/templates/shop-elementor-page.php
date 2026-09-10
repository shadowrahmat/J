<?php
/**
 * Elementor-editable shop page shell.
 *
 * @package JuhaniShopShowcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$shop_page = get_page_by_path( 'shop' );

if ( $shop_page && did_action( 'elementor/loaded' ) ) {
	$doc = \Elementor\Plugin::$instance->documents->get( $shop_page->ID );
	if ( $doc && $doc->is_built_with_elementor() ) {
		$doc->print_elements_with_wrapper();
	} else {
		echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $shop_page->ID ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
} elseif ( $shop_page ) {
	echo apply_filters( 'the_content', $shop_page->post_content ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
}

get_footer();
