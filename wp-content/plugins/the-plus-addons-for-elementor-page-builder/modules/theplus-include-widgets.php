<?php
/**
 * The file store Database Default Entry
 *
 * @link       https://posimyth.com/
 * @since      5.6.7
 *
 * @package    the-plus-addons-for-elementor-page-builder
 */

namespace TheplusAddons;

/**Exit if accessed directly.*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'L_Theplus_Widgets_Include' ) ) {

	/**
	 * Define L_Theplus_Widgets_Include class
	 */
	class L_Theplus_Widgets_Include {

		/**
		 * A reference to an instance of this class.
		 *
		 * @since 1.0.0
		 * @var   object
		 */
		private static $instance = null;

		/**
		 * Returns the instance.
		 *
		 * @since  1.0.0
		 * @return object
		 */
		public static function get_instance( $shortcodes = array() ) {

			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self( $shortcodes );
			}
			return self::$instance;
		}

		/**
		 * ThePlus_Load constructor.
		 */
		public function __construct() {

			$this->required_fiels();

			// The Free asset generator MUST initialise on every request, even when
			// Pro is active. It is the ONLY component that detects the widgets on a
			// page, builds the merged CSS/JS bundles into uploads/theplus-addons,
			// enqueues them, and registers the cache-clear/version-clear handlers.
			// Pro's Plus_Generator only enqueues vendor assets (lottie, iconsmind,
			// Google Maps, jquery-ui) and does NOT generate any bundle — so gating
			// this behind "Pro inactive" left the cache directory empty and every
			// page/editor preview unstyled.
			l_theplus_generator()->init();
			l_theplus_library();

			/*
			 * Always hook the widget registration. Whether Pro's (Pro) widget
			 * classes supersede Free's is decided inside add_widgets(), because
			 * at this point -- Free's plugins_loaded callback, which runs before
			 * Pro's -- Pro has not yet had the chance to bail or to load.
			 */
			$this->init();
		}

		/**
		 * Initalize integration hooks
		 *
		 * @return void
		 */
		public function init() {
			// Priority 100 ensures TPAE registers AFTER Elementor core and 3rd-party
			// plugins (Elementor Pro, etc.) finish registering custom controls,
			// dynamic tags, and global controls. Matches EA/PA convention.
			add_action( 'elementor/widgets/register', array( $this, 'add_widgets' ), 100 );

			/*
			 * Separate callback on purpose: add_widgets() stands down when Pro is
			 * handling the real widgets, but the removed-widget stubs must register
			 * either way -- neither plugin ships those widgets any more, and without
			 * a registered type Elementor deletes their saved nodes on the next save.
			 */
			add_action( 'elementor/widgets/register', array( $this, 'add_removed_widget_stubs' ), 100 );
		}

		/**
		 * Register data-preserving stubs for the widgets removed in 6.5.0.
		 *
		 * @since 6.5.0
		 *
		 * @param  object $widgets_manager Elementor widgets manager instance.
		 * @return void
		 */
		public function add_removed_widget_stubs( $widgets_manager ) {

			require_once L_THEPLUS_PATH . 'modules/widgets/base/class-tp-removed-widget.php';

			$tp_removed = array(
				'\TheplusAddons\Widgets\Base\Tp_Removed_Post_Search',
				'\TheplusAddons\Widgets\Base\Tp_Removed_Caldera_Forms',
				'\TheplusAddons\Widgets\Base\Tp_Removed_Design_Tool',
			);

			foreach ( $tp_removed as $tp_class ) {
				if ( class_exists( $tp_class ) ) {
					$widgets_manager->register( new $tp_class() );
				}
			}
		}

		/**
		 * Widget Include required files
		 *
		 * @since 6.0.0
		 */
		public function required_fiels() {
			require_once L_THEPLUS_PATH . 'modules/enqueue/plus-widgets-manager.php';
			require_once L_THEPLUS_PATH . 'modules/enqueue/plus-library.php';
			require_once L_THEPLUS_PATH . 'modules/enqueue/plus-generator.php';
		}

		/**
		 * Add new controls.
		 *
		 * @param  object $widgets_manager Controls manager instance.
		 * @return void
		 */
		public function add_widgets( $widgets_manager ) {

			// Pro registers the (Pro) widget classes for every shared widget, so
			// Free stands down -- but only once Pro has demonstrably loaded them.
			if ( tpae_pro_handles_widgets() ) {
				return;
			}

			$grouped = array(
				'tp_smooth_scroll'        => '\TheplusAddons\Widgets\ThePlus_Smooth_Scroll',
				'tp_accordion'            => '\TheplusAddons\Widgets\L_ThePlus_Accordion',
				'tp_adv_text_block'       => '\TheplusAddons\Widgets\ThePlus_Adv_Text_Block',
				'tp_age_gate'             => '\TheplusAddons\Widgets\ThePlus_Age_Gate',
				'tp_blockquote'           => '\TheplusAddons\Widgets\ThePlus_Block_Quote',
				'tp_blog_listout'         => '\TheplusAddons\Widgets\L_ThePlus_Blog_ListOut',
				'tp_breadcrumbs_bar'      => '\TheplusAddons\Widgets\L_ThePlus_Breadcrumbs_Bar',
				'tp_button'               => '\TheplusAddons\Widgets\L_ThePlus_Button',
				'tp_clients_listout'      => '\TheplusAddons\Widgets\L_ThePlus_Clients_ListOut',
				'tp_contact_form_7'       => '\TheplusAddons\Widgets\ThePlus_Contact_Form_7',
				'tp_countdown'            => '\TheplusAddons\Widgets\L_ThePlus_Countdown',
				'tp_carousel_anything'    => '\TheplusAddons\Widgets\L_ThePlus_Carousel_Anything',
				'tp_dark_mode'            => '\TheplusAddons\Widgets\ThePlus_Dark_Mode',
				'tp_dynamic_categories'   => '\TheplusAddons\Widgets\L_ThePlus_Dynamic_Categories',
				'tp_everest_form'         => '\TheplusAddons\Widgets\ThePlus_Everest_form',
				'tp_plus_form'            => '\TheplusAddons\Widgets\L_ThePlus_Plus_Form',
				'tp_flip_box'             => '\TheplusAddons\Widgets\L_ThePlus_Flip_Box',
				'tp_gallery_listout'      => '\TheplusAddons\Widgets\L_ThePlus_Gallery_ListOut',
				'tp_gravity_form'         => '\TheplusAddons\Widgets\ThePlus_Gravity_Form',
				'tp_heading_animation'    => '\TheplusAddons\Widgets\ThePlus_Heading_Animation',
				'tp_header_extras'        => '\TheplusAddons\Widgets\L_ThePlus_Header_Extras',
				'tp_heading_title'        => '\TheplusAddons\Widgets\L_Theplus_Ele_Heading_Title',
				'tp_hovercard'            => '\TheplusAddons\Widgets\ThePlus_Hovercard',
				'tp_info_box'             => '\TheplusAddons\Widgets\L_ThePlus_Info_Box',
				'tp_meeting_scheduler'    => '\TheplusAddons\Widgets\ThePlus_Meeting_Scheduler',
				'tp_messagebox'           => '\TheplusAddons\Widgets\ThePlus_MessageBox',
				'tp_navigation_menu_lite' => '\TheplusAddons\Widgets\ThePlus_Navigation_Menu_Lite',
				'tp_ninja_form'           => '\TheplusAddons\Widgets\ThePlus_Ninja_form',
				'tp_number_counter'       => '\TheplusAddons\Widgets\L_ThePlus_Number_Counter',
				'tp_post_title'           => '\TheplusAddons\Widgets\ThePlus_Post_Title',
				'tp_post_content'         => '\TheplusAddons\Widgets\ThePlus_Post_Content',
				'tp_post_featured_image'  => '\TheplusAddons\Widgets\ThePlus_Featured_Image',
				'tp_post_meta'            => '\TheplusAddons\Widgets\ThePlus_Post_Meta',
				'tp_post_author'          => '\TheplusAddons\Widgets\ThePlus_Post_Author',
				'tp_post_comment'         => '\TheplusAddons\Widgets\ThePlus_Post_Comment',
				'tp_post_navigation'      => '\TheplusAddons\Widgets\ThePlus_Post_Navigation',
				'tp_page_scroll'          => '\TheplusAddons\Widgets\L_ThePlus_Page_Scroll',
				'tp_pricing_table'        => '\TheplusAddons\Widgets\L_ThePlus_Pricing_Table',
				'tp_progress_bar'         => '\TheplusAddons\Widgets\ThePlus_Progress_Bar',
				'tp_process_steps'        => '\TheplusAddons\Widgets\L_ThePlus_Process_Steps',
				'tp_scroll_navigation'    => '\TheplusAddons\Widgets\L_ThePlus_Scroll_Navigation',
				'tp_social_icon'          => '\TheplusAddons\Widgets\L_ThePlus_Social_Icon',
				'tp_social_embed'         => '\TheplusAddons\Widgets\ThePlus_Social_Embed',
				'tp_syntax_highlighter'   => '\TheplusAddons\Widgets\ThePlus_Syntax_Highlighter',
				'tp_style_list'           => '\TheplusAddons\Widgets\L_ThePlus_Style_List',
				'tp_switcher'             => '\TheplusAddons\Widgets\L_ThePlus_Switcher',
				'tp_tabs_tours'           => '\TheplusAddons\Widgets\L_ThePlus_Tabs_Tours',
				'tp_team_member_listout'  => '\TheplusAddons\Widgets\L_ThePlus_Team_Member_ListOut',
				'tp_testimonial_listout'  => '\TheplusAddons\Widgets\L_ThePlus_Testimonial_ListOut',
				'tp_table'                => '\TheplusAddons\Widgets\L_ThePlus_Data_Table',
				'tp_video_player'         => '\TheplusAddons\Widgets\ThePlus_Video_Player',
				'tp_icon'                 => '\TheplusAddons\Widgets\ThePlus_Icon',
				'tp_wp_forms'             => '\TheplusAddons\Widgets\ThePlus_Wp_Forms',
			);

			$get_option = l_theplus_get_option( 'general', 'check_elements' );
			if ( ! empty( $get_option ) ) {

				// Skip form-styling widgets if their parent form plugin is not active.
				// Saves ~300-450 controls per skipped widget (significant editor memory reduction).
				$plugin_dependency = array(
					'tp_contact_form_7' => 'WPCF7',
					'tp_gravity_form'   => 'GFForms',
					'tp_ninja_form'     => 'Ninja_Forms',
					'tp_everest_form'   => 'EverestForms',
					'tp_wp_forms'       => 'wpforms',
				);

				foreach ( $grouped as $widget_id => $class_name ) {
					if ( in_array( $widget_id, $get_option ) ) {

						// Check if widget requires a specific plugin to be active.
						if ( isset( $plugin_dependency[ $widget_id ] ) ) {
							$required = $plugin_dependency[ $widget_id ];
							if ( ! class_exists( $required ) && ! function_exists( $required ) ) {
								continue; // Parent plugin not active — skip widget entirely.
							}
						}

						if ( $this->include_widget( $widget_id, true ) ) {
							$widgets_manager->register( new $class_name() );
						}
					}
				}
			}
		}


		/**
		 * Include control file by class name.
		 *
		 * @param  [type] $class_name [description]
		 * @return [type]             [description]
		 */
		public function include_widget( $widget_id, $grouped = false ) {

			$filename = sprintf( 'modules/widgets/' . $widget_id . '.php' );

			if ( ! file_exists( L_THEPLUS_PATH . $filename ) ) {
				return false;
			}

			require_once L_THEPLUS_PATH . $filename;

			return true;
		}
	}

	L_Theplus_Widgets_Include::get_instance();
}
