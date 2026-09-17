<?php
/**
 * Plugin Name: Juhani Mixora Product Columns
 * Description: Makes the Mixora product filter shortcode columns attribute control the desktop product grid.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_head', function() {
	?>
	<style id="juhani-mixora-columns-css">
	.mxswpf-root .mixora-product-grid > article.mixora-product-card,
	.mxswpf-root .mixora-products article.mixora-product-card,
	.mxswpf-root article.mixora-product-card {
		background-color: #ffffff !important;
		border: 1px solid #e5e7eb !important;
		border-radius: 0 !important;
		padding: 8px !important;
		box-shadow: none !important;
		box-sizing: border-box !important;
	}

	@media (min-width: 1101px) {
		.mxswpf-root[data-columns="1"] .mixora-product-grid {
			grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
		}

		.mxswpf-root[data-columns="2"] .mixora-product-grid {
			grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
		}

		.mxswpf-root[data-columns="3"] .mixora-product-grid {
			grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
		}

		.mxswpf-root[data-columns="4"] .mixora-product-grid {
			grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
			gap: 26px 18px !important;
		}
	}
	</style>
	<?php
}, 130 );
