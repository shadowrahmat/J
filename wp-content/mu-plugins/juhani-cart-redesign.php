<?php
/**
 * Plugin Name: Juhani Cart Attribute Formatting
 * Description: Preserves the product attribute labels used by the live WooCommerce order design.
 */
if (!defined('ABSPATH')) exit;

add_filter( 'woocommerce_get_item_data', 'juhani_cart_clean_item_data', 20, 2 );
function juhani_cart_clean_item_data( $item_data, $cart_item ) {
	if ( is_array( $item_data ) ) {
		foreach ( $item_data as &$data ) {
			if ( isset( $data['key'] ) ) {
				$key = trim( $data['key'] );
				$key = rtrim( $key, ':' );
				$key = preg_replace( '/^pa[-_\s]*/i', '', $key );
				$data['key'] = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
			}
		}
	}
	return $item_data;
}

