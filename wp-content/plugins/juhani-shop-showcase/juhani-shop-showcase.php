<?php
/**
 * Plugin Name: Juhani Shop Showcase
 * Description: Editable Elementor shop showcase widget for WooCommerce products.
 * Version: 1.0.0
 * Author: Juhani
 * Text Domain: juhani-shop-showcase
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'JUHANI_SHOP_SHOWCASE_VERSION', '1.0.0' );
define( 'JUHANI_SHOP_SHOWCASE_PATH', plugin_dir_path( __FILE__ ) );
define( 'JUHANI_SHOP_SHOWCASE_URL', plugin_dir_url( __FILE__ ) );

final class Juhani_Shop_Showcase_Plugin {
	public function __construct() {
		add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'register_assets' ] );
		add_action( 'elementor/frontend/after_enqueue_styles', [ $this, 'register_assets' ] );
		add_shortcode( 'juhani_shop_showcase', [ $this, 'render_shortcode' ] );
		add_filter( 'template_include', [ $this, 'use_elementor_shop_page_template' ], 99 );
	}

	public function register_widgets( $widgets_manager ): void {
		if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
			return;
		}

		require_once JUHANI_SHOP_SHOWCASE_PATH . 'includes/class-juhani-shop-showcase-widget.php';
		if ( class_exists( '\Juhani\Shop_Showcase\Juhani_Shop_Showcase_Widget' ) ) {
			$widgets_manager->register( new \Juhani\Shop_Showcase\Juhani_Shop_Showcase_Widget() );
		}
	}

	public function render_shortcode( $atts = [] ): string {
		if ( ! class_exists( 'WooCommerce' ) ) {
			return '';
		}

		require_once JUHANI_SHOP_SHOWCASE_PATH . 'includes/class-juhani-shop-showcase-renderer.php';
		$atts = shortcode_atts(
			[
				'products_per_page' => 9,
				'columns' => 3,
				'show_sidebar' => 'yes',
				'show_toolbar' => 'yes',
				'card_badges' => 'Best Seller,Premium,New,Popular',
			],
			$atts,
			'juhani_shop_showcase'
		);

		wp_enqueue_style( 'juhani-shop-showcase' );
		wp_enqueue_script( 'juhani-shop-showcase' );

		ob_start();
		\Juhani\Shop_Showcase\Renderer::render( $atts );
		return ob_get_clean();
	}

	public function use_elementor_shop_page_template( string $template ): string {
		if ( is_admin() || ! function_exists( 'is_shop' ) || ! is_shop() ) {
			return $template;
		}

		$page = get_page_by_path( 'shop' );

		if ( ! $page || 'page' !== $page->post_type ) {
			return $template;
		}

		return JUHANI_SHOP_SHOWCASE_PATH . 'templates/shop-elementor-page.php';
	}

	public function register_assets(): void {
		wp_register_style(
			'juhani-shop-showcase',
			JUHANI_SHOP_SHOWCASE_URL . 'assets/css/shop-showcase.css',
			[],
			JUHANI_SHOP_SHOWCASE_VERSION
		);

		wp_register_script(
			'juhani-shop-showcase',
			JUHANI_SHOP_SHOWCASE_URL . 'assets/js/shop-showcase.js',
			[],
			JUHANI_SHOP_SHOWCASE_VERSION,
			true
		);
	}
}

new Juhani_Shop_Showcase_Plugin();
