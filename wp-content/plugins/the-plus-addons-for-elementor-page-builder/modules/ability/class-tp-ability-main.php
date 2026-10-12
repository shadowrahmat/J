<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.4.8
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */


/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Ability_Main' ) ) {

	/**
	 * This class used for only load widget notice
	 *
	 * @since 6.4.8
	 */
	class Tp_Ability_Main {

		/**
		 * Ability files that should only load from the free plugin when Pro is inactive.
		 *
		 * @since 6.4.8
		 * @var string[]
		 */
		private $free_only_ability_files = array(
			'tp-accordion.php',
			'tp-heading-title.php',
			'tp-button.php',
			'tp-info-box.php',
			'tp-number-counter.php',
			'tp-countdown.php',
			'tp-pricing-table.php',
			'tp-flip-box.php',
			'tp-tabs-tours.php',
			'tp-testimonial-listout.php',
			'tp-team-member-listout.php',
			'tp-blog-listout.php',
			'tp-gallery-listout.php',
			'tp-clients-listout.php',
			'tp-social-icon.php',
			'tp-carousel-anything.php',
			'tp-style-list.php',
			'tp-process-steps.php',
			'tp-switcher.php',
			'tp-table.php',
			'tp-breadcrumbs-bar.php',
			'tp-scroll-navigation.php',
			'tp-page-scroll.php',
			'tp-plus-form.php',
			'tp-dynamic-categories.php',
			'tp-header-extras.php',
		);

		/**
		 * Instance
		 *
		 * @since 6.4.8
		 * @access private
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.4.8
		 * @access public
		 * @static
		 * @return instance of the class.
		 */
		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * Perform some compatibility checks to make sure basic requirements are meet.
		 *
		 * @since 6.4.8
		 */
		public function __construct() {
			add_action( 'wp_abilities_api_categories_init', array( $this, 'tp_register_ability_category' ) );
			add_action( 'wp_abilities_api_init', array( $this, 'tp_register_abilities' ) );

			/**
			 * Optional in-editor Angie channel; self-gated, dormant until the built
			 * JS bundle exists (see angie/README.md).
			 *
			 * @since 6.5.2
			 */
			require_once L_THEPLUS_PATH . 'modules/ability/core-elementor/angie/class-tp-angie-bridge.php';
			Tp_Angie_Bridge::instance();
		}

		/**
		 * Register the TPAE ability category.
		 *
		 * @since 6.4.8
		 */
		public function tp_register_ability_category() {
			if ( ! function_exists( 'wp_has_ability_category' ) || ! function_exists( 'wp_register_ability_category' ) ) {
				return;
			}

			if ( wp_has_ability_category( 'tpae' ) ) {
				return;
			}

			wp_register_ability_category( 'tpae', array(
				'label'       => __( 'The Plus Addons for Elementor', 'tpebl' ),
				'description' => __( 'Abilities for The Plus Addons for Elementor widgets.', 'tpebl' ),
			) );
		}

		/**
		 * Mark this plugin's abilities as public.
		 *
		 * WordPress 7.1 introduced a unified `meta.public` flag that defaults to false.
		 * Clients that honour it would otherwise skip every tpae/ ability. An explicit
		 * value set by an ability definition always wins.
		 *
		 * @param array  $args Ability registration arguments.
		 * @param string $name Ability name.
		 * @return array
		 * @since 6.5.0
		 */
		public function tp_ability_public_flag( $args, $name ) {
			if ( ! is_string( $name ) || 0 !== strpos( $name, 'tpae/' ) ) {
				return $args;
			}

			if ( ! isset( $args['meta'] ) || ! is_array( $args['meta'] ) ) {
				$args['meta'] = array();
			}

			if ( ! isset( $args['meta']['public'] ) ) {
				$args['meta']['public'] = true;
			}

			/**
			 * When the Angie bundle is present AND Angie is actually active,
			 * reclassify free tpae/ abilities off mcp.type='tool' so the bridge
			 * is their sole in-editor channel.
			 *
			 * ABL-001: is_built() only checks the bundle JS exists on disk -- true
			 * on every 6.5.2 install regardless of whether Angie itself is even
			 * installed. Without the ANGIE_VERSION check this reclassified all 80
			 * tpae/ abilities to mcp.type='bridge' unconditionally, which every
			 * standards-based MCP discovery implementation (WP core's own
			 * reference client, Angie's own generic discovery, Novamira's) filters
			 * out by only accepting mcp.type==='tool' -- hiding every Free AI
			 * ability from any non-Angie MCP client on every site, Angie active or
			 * not.
			 *
			 * @since 6.5.2
			 */
			if ( defined( 'ANGIE_VERSION' ) && class_exists( 'Tp_Angie_Bridge' ) && Tp_Angie_Bridge::is_built() ) {
				if ( ! isset( $args['meta']['mcp'] ) || ! is_array( $args['meta']['mcp'] ) ) {
					$args['meta']['mcp'] = array();
				}
				$args['meta']['mcp']['type'] = 'bridge';
			}

			return $args;
		}

		/**
		 * Dynamically load and register all abilities from the widgets-ability folder.
		 *
		 * @since 6.4.8
		 */
		public function tp_register_abilities() {
			if ( ! function_exists( 'wp_register_ability' ) || ! function_exists( 'wp_has_ability_category' ) ) {
				return;
			}

			if ( ! wp_has_ability_category( 'tpae' ) ) {
				return;
			}

			/* WP 7.1 added a unified meta.public flag that defaults to false; set it before the ability files register. */
			add_filter( 'wp_register_ability_args', array( $this, 'tp_ability_public_flag' ), 10, 2 );

			$ability_dir = L_THEPLUS_PATH . 'modules/ability/widgets-ability';
			require_once L_THEPLUS_PATH . 'modules/ability/core-elementor/layout-abilities.php';
			require_once L_THEPLUS_PATH . 'modules/ability/core-elementor/design-guide-ability.php';
			require_once L_THEPLUS_PATH . 'modules/ability/core-elementor/page-lifecycle-abilities.php';
			require_once L_THEPLUS_PATH . 'modules/ability/core-elementor/elementor-mcp-bridge.php';

			if ( ! is_dir( $ability_dir ) ) {
				return;
			}

			$ability_files = glob( $ability_dir . '/*.php' );

			if ( empty( $ability_files ) ) {
				return;
			}

			foreach ( $ability_files as $ability_file ) {
				if ( defined( 'THEPLUS_VERSION' ) && in_array( basename( $ability_file ), $this->free_only_ability_files, true ) ) {
					continue;
				}

				if ( is_file( $ability_file ) ) {
					require_once $ability_file;
				}
			}
		}

	}

	Tp_Ability_Main::instance();
}
