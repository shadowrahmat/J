<?php
/**
 * The file that defines pro widgets
 *
 * @link       https://posimyth.com/
 * @since      6.4.1
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

use Elementor\Widgets_Manager;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'TP_Widgets_Promotion_Main' ) ) {

	/**
	 * It is Main Class for load all widet feature.
	 *
	 * @since 6.4.1
	 */
	class TP_Widgets_Promotion_Main {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;


		/**
		 *  Initiator
		 *
		 *  @since6.4.1
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Define the core functionality of the plugin.
		 *
		 * @since 6.4.1
		 */
		public function __construct() {
			if ( ! defined( 'THEPLUS_VERSION' ) ) {
				$this->init();
			}
			$this->tp_load_ajax_files();
		}

		/**
		 * Initalize integration hooks
		 *
		 * @return void
		 */
		public function init() {
			// Priority 100 ensures promo widgets register AFTER all real widgets,
			// preventing duplicate registration races with the main widget loader.
			add_action( 'elementor/widgets/register', array( $this, 'add_widgets' ), 100 );
		}

		/**
		 * Add new controls.
		 *
		 * @param  object $widgets_manager Controls manager instance.
		 * @return void
		 */
		public function add_widgets( $widgets_manager ) {
			if ( ! defined( 'THEPLUS_VERSION' ) ) {
				include L_THEPLUS_PATH . 'modules/widget-promotion/tp-widget-promotion/class-tp-widget-promotion.php';
			}
		}

		public function tp_load_ajax_files() {
			// File hooks elementor/editor/* and wp_ajax_* only. Both fire in
			// admin context (admin-ajax.php returns true for is_admin()).
			// Skip the ~9k-line file on frontend requests.
			if ( ! is_admin() ) {
				return;
			}
			$file_path = L_THEPLUS_PATH . 'modules/widget-promotion/tp-widgets-show/class-tp-widget-show.php';
			if ( file_exists( $file_path ) ) {
				include_once $file_path;
			}
		}
	}

	return TP_Widgets_Promotion_Main::get_instance();
}