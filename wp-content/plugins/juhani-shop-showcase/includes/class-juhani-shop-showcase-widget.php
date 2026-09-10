<?php
namespace Juhani\Shop_Showcase;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Repeater;
use Elementor\Widget_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\Elementor\Widget_Base' ) ) {
	return;
}

require_once JUHANI_SHOP_SHOWCASE_PATH . 'includes/class-juhani-shop-showcase-renderer.php';

class Juhani_Shop_Showcase_Widget extends Widget_Base {
	public function get_name(): string {
		return 'juhani_shop_showcase';
	}

	public function get_title(): string {
		return esc_html__( 'Juhani Shop Showcase', 'juhani-shop-showcase' );
	}

	public function get_icon(): string {
		return 'eicon-products';
	}

	public function get_categories(): array {
		return [ 'general', 'woocommerce-elements' ];
	}

	public function get_keywords(): array {
		return [ 'juhani', 'shop', 'showcase', 'product', 'woocommerce', 'fishing', 'net' ];
	}

	public function get_style_depends(): array {
		return [ 'juhani-shop-showcase' ];
	}

	public function get_script_depends(): array {
		return [ 'juhani-shop-showcase' ];
	}

	protected function register_controls(): void {
		$this->start_controls_section( 'hero_section', [
			'label' => esc_html__( 'Hero', 'juhani-shop-showcase' ),
		] );

		$this->add_control( 'eyebrow', [
			'label' => esc_html__( 'Eyebrow', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Premium Fishing Equipment',
		] );

		$this->add_control( 'hero_title', [
			'label' => esc_html__( 'Title', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Explore Our Products',
		] );

		$this->add_control( 'hero_description', [
			'label' => esc_html__( 'Description', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXTAREA,
			'default' => 'High-quality fishing nets and equipment for a stronger, safer and more sustainable tomorrow.',
		] );

		$this->add_control( 'hero_note', [
			'label' => esc_html__( 'Right Note', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXTAREA,
			'default' => 'Built for the People of the Sea',
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name' => 'hero_background',
			'types' => [ 'classic' ],
			'selector' => '{{WRAPPER}} .jss-hero',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'product_section', [
			'label' => esc_html__( 'Products', 'juhani-shop-showcase' ),
		] );

		$this->add_control( 'products_per_page', [
			'label' => esc_html__( 'Products Per Page', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::NUMBER,
			'default' => 9,
			'min' => 1,
			'max' => 48,
		] );

		$this->add_control( 'columns', [
			'label' => esc_html__( 'Desktop Columns', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::SELECT,
			'default' => '3',
			'options' => [
				'2' => '2',
				'3' => '3',
				'4' => '4',
			],
		] );

		$this->add_control( 'show_sidebar', [
			'label' => esc_html__( 'Show Category Sidebar', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'show_toolbar', [
			'label' => esc_html__( 'Show Search & Sort', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::SWITCHER,
			'default' => 'yes',
		] );

		$this->add_control( 'card_badges', [
			'label' => esc_html__( 'Card Badges', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Best Seller,Premium,New,Popular',
			'description' => esc_html__( 'Comma separated badges used across product cards.', 'juhani-shop-showcase' ),
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'promo_section', [
			'label' => esc_html__( 'Promo Banner', 'juhani-shop-showcase' ),
		] );

		$this->add_control( 'promo_eyebrow', [
			'label' => esc_html__( 'Eyebrow', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Quality You Can Trust',
		] );

		$this->add_control( 'promo_title', [
			'label' => esc_html__( 'Title', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'High-Performance Fishing Nets for Every Need',
		] );

		$this->add_control( 'promo_text', [
			'label' => esc_html__( 'Text', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXTAREA,
			'default' => 'Durable. Reliable. Built for a better tomorrow. Our fishing nets are crafted with premium materials.',
		] );

		$this->add_control( 'promo_button_text', [
			'label' => esc_html__( 'Button Text', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Explore Now',
		] );

		$this->add_control( 'promo_button_url', [
			'label' => esc_html__( 'Button URL', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::URL,
			'default' => [ 'url' => '#products' ],
		] );

		$this->add_group_control( Group_Control_Background::get_type(), [
			'name' => 'promo_background',
			'types' => [ 'classic' ],
			'selector' => '{{WRAPPER}} .jss-promo',
		] );

		$this->end_controls_section();

		$this->start_controls_section( 'trust_section', [
			'label' => esc_html__( 'Trust Items', 'juhani-shop-showcase' ),
		] );

		$repeater = new Repeater();
		$repeater->add_control( 'icon', [
			'label' => esc_html__( 'Icon', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::ICONS,
			'default' => [ 'value' => 'fas fa-shipping-fast', 'library' => 'fa-solid' ],
		] );
		$repeater->add_control( 'title', [
			'label' => esc_html__( 'Title', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'Worldwide Shipping',
		] );
		$repeater->add_control( 'text', [
			'label' => esc_html__( 'Text', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::TEXT,
			'default' => 'To 50+ Countries',
		] );

		$this->add_control( 'trust_items', [
			'label' => esc_html__( 'Items', 'juhani-shop-showcase' ),
			'type' => Controls_Manager::REPEATER,
			'fields' => $repeater->get_controls(),
			'default' => [
				[ 'title' => 'Worldwide Shipping', 'text' => 'To 50+ Countries' ],
				[ 'title' => 'Secure Payments', 'text' => '100% Protected' ],
				[ 'title' => 'Premium Quality', 'text' => 'Tested & Trusted' ],
				[ 'title' => 'Dedicated Support', 'text' => 'Always Here to Help' ],
			],
			'title_field' => '{{{ title }}}',
		] );

		$this->end_controls_section();
	}

	protected function render(): void {
		Renderer::render( $this->get_settings_for_display() );
	}

	private function render_promo( array $settings ): void {
		$url = ! empty( $settings['promo_button_url']['url'] ) ? $settings['promo_button_url']['url'] : '#products';
		?>
		<section class="jss-promo">
			<div class="jss-promo__content">
				<p class="jss-eyebrow"><?php echo esc_html( $settings['promo_eyebrow'] ); ?></p>
				<h2><?php echo esc_html( $settings['promo_title'] ); ?></h2>
				<p><?php echo esc_html( $settings['promo_text'] ); ?></p>
				<a class="jss-promo__button" href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $settings['promo_button_text'] ); ?></a>
			</div>
		</section>
		<?php
	}

	private function render_trust_items( array $settings ): void {
		if ( empty( $settings['trust_items'] ) || ! is_array( $settings['trust_items'] ) ) {
			return;
		}
		echo '<section class="jss-trust">';
		foreach ( $settings['trust_items'] as $item ) {
			echo '<div class="jss-trust__item">';
			echo '<span class="jss-trust__icon">';
			if ( ! empty( $item['icon'] ) ) {
				\Elementor\Icons_Manager::render_icon( $item['icon'], [ 'aria-hidden' => 'true' ] );
			}
			echo '</span>';
			echo '<span><strong>' . esc_html( $item['title'] ) . '</strong><small>' . esc_html( $item['text'] ) . '</small></span>';
			echo '</div>';
		}
		echo '</section>';
	}
}
