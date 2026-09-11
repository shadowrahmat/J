<?php
/**
 * Plugin Name: Juhani Product Attribute Layout
 * Description: Organizes WooCommerce single product attributes into a cleaner responsive specification layout.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_filter( 'woocommerce_product_tabs', function( array $tabs ): array {
	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title']    = __( 'Specifications', 'juhani' );
		$tabs['additional_information']['callback'] = 'juhani_render_product_specifications';
	}

	return $tabs;
}, 30 );

function juhani_render_product_specifications(): void {
	global $product;

	if ( ! $product instanceof WC_Product ) {
		return;
	}

	$specs = juhani_get_product_specifications( $product );

	if ( empty( $specs ) ) {
		echo '<p class="juhani-specs-empty">' . esc_html__( 'No specifications available for this product.', 'juhani' ) . '</p>';
		return;
	}

	$groups = [
		'net' => [
			'title' => __( 'Net Details', 'juhani' ),
			'items' => [],
		],
		'build' => [
			'title' => __( 'Build & Making', 'juhani' ),
			'items' => [],
		],
		'other' => [
			'title' => __( 'Other Information', 'juhani' ),
			'items' => [],
		],
	];

	foreach ( $specs as $spec ) {
		$key = $spec['key'];
		if ( in_array( $key, [ 'pa_suta', 'pa_haat', 'pa_rock-weight-kg' ], true ) ) {
			$groups['net']['items'][] = $spec;
		} elseif ( in_array( $key, [ 'pa_making-type', 'pa_git-type' ], true ) ) {
			$groups['build']['items'][] = $spec;
		} else {
			$groups['other']['items'][] = $spec;
		}
	}

	echo '<section class="juhani-specs" aria-label="' . esc_attr__( 'Product specifications', 'juhani' ) . '">';
	echo '<div class="juhani-specs__header">';
	echo '<span class="juhani-specs__eyebrow">' . esc_html__( 'Product Details', 'juhani' ) . '</span>';
	echo '<h2>' . esc_html__( 'Specifications', 'juhani' ) . '</h2>';
	echo '</div>';

	foreach ( $groups as $group ) {
		if ( empty( $group['items'] ) ) {
			continue;
		}

		echo '<div class="juhani-spec-group">';
		echo '<h3>' . esc_html( $group['title'] ) . '</h3>';
		echo '<dl class="juhani-spec-grid">';
		foreach ( $group['items'] as $item ) {
			echo '<div class="juhani-spec-card">';
			echo '<dt>' . esc_html( $item['label'] ) . '</dt>';
			echo '<dd>' . wp_kses_post( $item['value'] ) . '</dd>';
			echo '</div>';
		}
		echo '</dl>';
		echo '</div>';
	}

	echo '</section>';
}

function juhani_get_product_specifications( WC_Product $product ): array {
	$ordered_keys = [
		'pa_suta',
		'pa_haat',
		'pa_rock-weight-kg',
		'pa_making-type',
		'pa_git-type',
	];
	$attributes = $product->get_attributes();
	$specs = [];

	foreach ( $ordered_keys as $key ) {
		if ( isset( $attributes[ $key ] ) ) {
			$spec = juhani_format_product_attribute( $product, $attributes[ $key ] );
			if ( $spec ) {
				$specs[] = $spec;
			}
		}
	}

	foreach ( $attributes as $key => $attribute ) {
		if ( in_array( $key, $ordered_keys, true ) || ! $attribute->get_visible() ) {
			continue;
		}

		$spec = juhani_format_product_attribute( $product, $attribute );
		if ( $spec ) {
			$specs[] = $spec;
		}
	}

	if ( $product->has_weight() ) {
		$specs[] = [
			'key' => 'weight',
			'label' => __( 'Weight', 'juhani' ),
			'value' => wc_format_weight( $product->get_weight() ),
		];
	}

	if ( $product->has_dimensions() ) {
		$specs[] = [
			'key' => 'dimensions',
			'label' => __( 'Dimensions', 'juhani' ),
			'value' => wc_format_dimensions( $product->get_dimensions( false ) ),
		];
	}

	return $specs;
}

function juhani_format_product_attribute( WC_Product $product, WC_Product_Attribute $attribute ): ?array {
	if ( ! $attribute->get_visible() ) {
		return null;
	}

	$name = $attribute->get_name();
	$label = wc_attribute_label( $name, $product );

	if ( $attribute->is_taxonomy() ) {
		$values = wc_get_product_terms( $product->get_id(), $name, [ 'fields' => 'names' ] );
	} else {
		$values = $attribute->get_options();
	}

	$values = array_filter( array_map( 'trim', array_map( 'wp_strip_all_tags', $values ) ) );
	if ( empty( $values ) ) {
		return null;
	}

	return [
		'key' => $name,
		'label' => $label,
		'value' => implode( ', ', $values ),
	];
}

add_action( 'wp_head', function(): void {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}
	?>
	<style id="juhani-product-specs">
	.woocommerce div.product .woocommerce-tabs .panel .juhani-specs {
		margin-top: 8px;
		color: #173248;
	}
	.juhani-specs__header {
		margin-bottom: 22px;
	}
	.juhani-specs__eyebrow {
		display: block;
		margin-bottom: 6px;
		color: #0b6f9f;
		font-size: 13px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0;
	}
	.woocommerce div.product .woocommerce-tabs .panel .juhani-specs h2 {
		margin: 0;
		font-size: 30px;
		line-height: 1.15;
		font-weight: 700;
	}
	.juhani-spec-group {
		margin-top: 26px;
	}
	.juhani-spec-group h3 {
		margin: 0 0 14px;
		font-size: 17px;
		line-height: 1.25;
		color: #0f2f44;
	}
	.juhani-spec-grid {
		display: grid;
		grid-template-columns: repeat(3, minmax(0, 1fr));
		gap: 12px;
		margin: 0;
	}
	.juhani-spec-card {
		min-height: 94px;
		padding: 16px;
		border: 1px solid #d8e4ea;
		border-radius: 8px;
		background: #ffffff;
		box-shadow: 0 8px 20px rgba(15, 47, 68, 0.06);
	}
	.juhani-spec-card dt {
		margin: 0 0 8px;
		color: #607487;
		font-size: 13px;
		font-weight: 700;
		text-transform: uppercase;
		letter-spacing: 0;
	}
	.juhani-spec-card dd {
		margin: 0;
		color: #102f43;
		font-size: 17px;
		font-weight: 700;
		line-height: 1.35;
	}
	.juhani-specs-empty {
		margin: 0;
		padding: 18px;
		border: 1px solid #d8e4ea;
		border-radius: 8px;
		background: #f7fafb;
		color: #405668;
	}
	@media (max-width: 900px) {
		.juhani-spec-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr));
		}
	}
	@media (max-width: 560px) {
		.juhani-spec-grid {
			grid-template-columns: 1fr;
		}
		.woocommerce div.product .woocommerce-tabs .panel .juhani-specs h2 {
			font-size: 24px;
		}
	}
	</style>
	<?php
}, 30 );
