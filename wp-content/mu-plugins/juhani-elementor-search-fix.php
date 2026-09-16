<?php
/**
 * Plugin Name: Juhani Elementor Search Fix
 * Description: Fixes Elementor Pro live search for popup widgets with 8-character Elementor IDs.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'rest_pre_dispatch', function ( $result, $server, $request ) {
	if ( '/elementor-pro/v1/refresh-search' !== $request->get_route() || 'POST' !== $request->get_method() ) {
		return $result;
	}

	$params      = $request->get_params();
	$post_id     = isset( $params['post_id'] ) ? absint( $params['post_id'] ) : 0;
	$widget_id   = isset( $params['widget_id'] ) ? sanitize_key( $params['widget_id'] ) : '';
	$search_term = isset( $params['search_term'] ) ? sanitize_text_field( wp_unslash( $params['search_term'] ) ) : '';
	$page_number = isset( $params['page_number'] ) ? max( 1, absint( $params['page_number'] ) ) : 1;
	$breakpoint  = isset( $params['breakpoint'] ) ? sanitize_key( $params['breakpoint'] ) : null;

	if ( ! $post_id || ! $widget_id || ! preg_match( '/^[a-zA-Z0-9]{7,12}$/', $widget_id ) ) {
		return $result;
	}

	if (
		! class_exists( '\Elementor\Utils' ) ||
		! class_exists( '\ElementorPro\Plugin' ) ||
		! class_exists( '\ElementorPro\Core\Utils' )
	) {
		return $result;
	}

	$post = get_post( $post_id );
	if ( ! $post || 'publish' !== $post->post_status ) {
		return $result;
	}

	$document = \ElementorPro\Plugin::elementor()->documents->get( $post_id );
	if ( ! $document ) {
		return $result;
	}

	$widget_data = \Elementor\Utils::find_element_recursive( $document->get_elements_data(), $widget_id );
	if ( empty( $widget_data ) || 'widget' !== ( $widget_data['elType'] ?? '' ) || 'search' !== ( $widget_data['widgetType'] ?? '' ) ) {
		return $result;
	}

	$widget_instance = \ElementorPro\Core\Utils::create_widget_instance_from_db( $post_id, $widget_id );
	if ( ! $widget_instance || ! method_exists( $widget_instance, 'render_results' ) ) {
		return $result;
	}

	ob_start();
	$widget_instance->set_search_term( $search_term );
	$widget_instance->set_page_number( $page_number );
	$widget_instance->set_breakpoint( $breakpoint );
	$widget_instance->render_results();
	$markup = ob_get_clean();

	$widget_instance->set_search_term( '' );

	ob_start();
	$widget_instance->render_pagination();
	$pagination = ob_get_clean();

	return rest_ensure_response( [
		'data'       => $markup,
		'pagination' => $pagination,
	] );
}, 10, 3 );
