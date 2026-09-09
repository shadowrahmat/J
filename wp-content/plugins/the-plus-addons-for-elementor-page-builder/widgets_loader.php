<?php
/**
 * The file that defines the core plugin class
 *
 * @link    https://posimyth.com/
 * @since   1.0.0
 *
 * @package Theplus
 */

namespace TheplusAddons;

use Elementor\Utils;
use Elementor\Core\Settings\Manager as SettingsManager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the plugin classmap autoloader.
 *
 * Loaded here (rather than in the main plugin file) so the plugin bootstrap
 * stays clean. Registers Tpae_Autoloader, which lazy-loads 120+ plugin classes
 * via spl_autoload_register only when a class is actually referenced.
 *
 * @since 6.5.0
 */
require_once L_THEPLUS_PATH . 'includes/autoloader.php';
\Tpae_Autoloader::register( L_THEPLUS_PATH );

/**
 * Autoload the shared widget base class and reload-preview trait on demand.
 *
 * Registered here (rather than in the main plugin file) so the plugin bootstrap
 * stays clean. The callback only fires when one of the listed FQCNs is actually
 * referenced — typically from inside a migrated widget's `extends` clause or
 * `use` statement during `elementor/widgets/register`.
 *
 * @since 6.4.13
 */
spl_autoload_register(
	function ( $class ) {
		if ( 'TheplusAddons\\Widgets\\Base\\Plus_Widget_Base' === $class ) {
			require_once L_THEPLUS_PATH . 'modules/widgets/base/class-plus-widget-base.php';
		}
		if ( 'TheplusAddons\\Widgets\\Base\\Reload_Preview_Trait' === $class ) {
			require_once L_THEPLUS_PATH . 'modules/widgets/base/trait-reload-preview.php';
		}
	}
);

/**
 * Whether Pro has actually taken over widget and control registration.
 *
 * THEPLUS_VERSION is defined the moment Pro's main file is *parsed*, long before
 * Pro decides whether it can run at all. When Pro bails out early -- an outdated
 * Free, an outdated Elementor -- it returns before loading its widget layer, so
 * reading that constant as "Pro is handling widgets" made BOTH plugins stand down
 * and register nothing. Elementor then silently dropped every tp- widget from the
 * document on save, with no fatal or notice anywhere.
 *
 * Only ever called from `elementor/controls/register` and
 * `elementor/widgets/register`, which both fire long after every plugin's
 * `plugins_loaded` callback, so Pro's real state is settled by then.
 *
 * @since 6.5.0
 *
 * @return bool True when Pro registers the widgets, false when Free must.
 */
function tpae_pro_handles_widgets() {

	if ( ! defined( 'THEPLUS_VERSION' ) ) {
		return false;
	}

	// Pro 6.5.0+ sets this immediately before it loads widgets_loader.php.
	if ( defined( 'THEPLUS_PRO_WIDGETS_LOADED' ) ) {
		return true;
	}

	/*
	 * Older Pro predates the marker, so detect its widget loader directly. No
	 * autoload pass -- the class is plain-required by Pro, and its absence here
	 * is exactly the "Pro never got that far" case we are testing for.
	 */
	return class_exists( '\TheplusAddons\Theplus_Widgets_Include', false );
}

/**
 * It Is load all widget and dashboard
 *
 * @since 1.0.0
 */
#[\AllowDynamicProperties]
final class L_Theplus_Element_Load {

	/**
	 * Core singleton class
	 *
	 * @var _instance pattern realization
	 */
	private static $instance;

	/**
	 * Get Elementor Plugin Instance
	 *
	 * @return \Elementor\Plugin
	 */
	public static function elementor() {
		return \Elementor\Plugin::$instance;
	}

	/**
	 * Get Singleton Instance
	 *
	 * This static method ensures that only one instance of the class is created
	 * and provides a way to access that instance.
	 *
	 * @since 1.0.0
	 *
	 * @return L_Theplus_Element_Load The single instance of the class.
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * ThePlus_Load Class
	 *
	 * This class is responsible for handling the loading of ThePlus Addons.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		add_action( 'in_plugin_update_message-' . L_THEPLUS_PBNAME, array( $this, 'tp_f_in_plugin_update_message' ), 10, 2 );

		register_activation_hook( L_THEPLUS_FILE, array( __CLASS__, 'tp_f_activation' ) );
		register_deactivation_hook( L_THEPLUS_FILE, array( __CLASS__, 'tp_f_deactivation' ) );

		// Force a cache regeneration whenever the plugin is updated in place
		// (zip replace / auto-update), even if the version string is unchanged.
		add_action( 'upgrader_process_complete', array( __CLASS__, 'tp_f_on_upgrade' ), 10, 2 );

		add_action( 'init', array( $this, 'tp_i18n' ) );
		add_action( 'plugins_loaded', array( $this, 'tp_f_plugin_loaded' ) );
	}

	/**
	 * When Show Update Notice that time this function is used
	 *
	 * @since 5.6.6
	 *
	 * @param array  $data     Array of plugin update data.
	 * @param object $response Object containing response data from the update check.
	 */
	public function tp_f_in_plugin_update_message( $data, $response ) {

		/*
		 * The notice arrives on $response, not $data (header fields only); read it
		 * from there, fall back to $data, and wp_kses_post() before wpautop().
		 * @since 6.5.1
		 */
		$upgrade_notice = '';
		if ( ! empty( $response->upgrade_notice ) ) {
			$upgrade_notice = $response->upgrade_notice;
		} elseif ( ! empty( $data['upgrade_notice'] ) ) {
			$upgrade_notice = $data['upgrade_notice'];
		}

		if ( '' !== $upgrade_notice ) {
			printf( '<div class="update-message">%s</div>', wpautop( wp_kses_post( $upgrade_notice ) ) );
		}
	}

	/**
	 * Elementor is active but older than L_THEPLUS_MINIMUM_ELEMENTOR_VERSION.
	 *
	 * @since 6.5.0
	 */
	public function tp_f_elementor_outdated_notice() {

		if ( ! current_user_can( 'update_plugins' ) ) {
			return;
		}

		echo '<div class="notice notice-error tpae-notice-show" style="border-left-color: #6660EF;">
			<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">
				<div style="margin: 0; color: #000;">
					<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Update Elementor to Continue', 'tpebl' ) . '</h3>
					<p>'
						/* translators: %s: minimum required Elementor version. */
						. sprintf( esc_html__( 'The Plus Addons for Elementor requires Elementor %s or newer. Widgets and extensions are paused until Elementor is updated.', 'tpebl' ), esc_html( L_THEPLUS_MINIMUM_ELEMENTOR_VERSION ) )
					. '</p>
					<div class="tp-tpae-button" style="margin-top: 10px;">
						<a href="' . esc_url( self_admin_url( 'plugins.php' ) ) . '" class="button button-primary">' . esc_html__( 'Go to Plugins', 'tpebl' ) . '</a>
					</div>
				</div>
			</div>
		</div>';
	}

	/**
	 * Elementor Plugin Not install than show this Notice
	 *
	 * @since 5.6.6
	 */
	public function tp_f_elementor_load_notice() {
		$plugin = 'elementor/elementor.php';

		$installed_plugins = get_plugins();

		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}

		$tp_ele_btn_txt = esc_html__( 'Install Now', 'tpebl' );

		if ( isset( $installed_plugins[ $plugin ] ) ) {
			$tp_ele_btn_txt = esc_html__( 'Activate Now', 'tpebl' );
		}

		echo '<div class="notice notice-error tpae-notice-show tpae-install-elementor" style="border-left-color: #6660EF;">
			<div class="tp-notice-wrap" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

				<div style="margin: 0; color: #000;">
					<h3 style="margin: 10px 0 7px;">' . esc_html__( 'Elementor Plugin Required', 'tpebl' ) . '</h3>
					<p>' . esc_html__( 'The Plus Addons for Elementor works as an extension of Elementor. Please install and activate Elementor to unlock all 120+ widgets and extensions. Without Elementor, the addon cannot function.', 'tpebl' ) . '</p>';
						echo '<div class="tp-tpae-button" style="margin-top: 10px;">
								<div style="background: #6660EF; color: #fff; position: relative;" class="button tpae-ele-btn" data-slug="elementor/elementor.php" data-name="elementor">
									'. esc_html( $tp_ele_btn_txt ) .'
								</div>
							</div>';
				echo '</div>
			</div>
		</div>';
	}

	/**
	 * Plugin Activation.
	 *
	 * @return void
	 */
	public static function tp_f_activation() {
		// Reset the version marker so tp_version_clear_cache() (admin_init)
		// performs a full purge + regeneration of the generated CSS/JS on the
		// next admin load. This makes a re-activated / re-installed build never
		// serve the previous build's stale editor + frontend cache, even when
		// the version string is unchanged (the common QA re-test case).
		delete_option( 'tpae_version_cache' );
	}

	/**
	 * Plugin deactivation.
	 *
	 * @return void
	 */
	public static function tp_f_deactivation() {}

	/**
	 * Reset the cache marker after this plugin is updated in place.
	 *
	 * register_activation_hook does not fire on plugin UPDATE (zip replace /
	 * auto-update), so on those paths we clear via upgrader_process_complete.
	 * Clearing the version marker lets tp_version_clear_cache() regenerate the
	 * cache on the next admin load through its normal, tested path.
	 *
	 * @since 6.5.0
	 *
	 * @param object $upgrader   WP_Upgrader instance (unused).
	 * @param array  $hook_extra Context of the completed upgrade.
	 */
	public static function tp_f_on_upgrade( $upgrader, $hook_extra ) {
		if ( empty( $hook_extra['type'] ) || 'plugin' !== $hook_extra['type'] ) {
			return;
		}
		if ( empty( $hook_extra['action'] ) || 'update' !== $hook_extra['action'] ) {
			return;
		}

		$plugins = ! empty( $hook_extra['plugins'] ) ? (array) $hook_extra['plugins'] : array();

		if ( in_array( L_THEPLUS_PBNAME, $plugins, true ) ) {
			delete_option( 'tpae_version_cache' );
		}
	}

	/**
	 * After Load Plugin All set than call this function
	 *
	 * @since 5.6.6
	 */
	public function tp_f_plugin_loaded() {

		if ( ! did_action( 'elementor/loaded' ) ) {
			add_action( 'admin_notices', array( $this, 'tp_f_elementor_load_notice' ) );
			add_action('wp_ajax_tpae_elementor_ajax_call', array($this, 'tpae_elementor_ajax_call'));
			add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_css_js'));
			return;
		}

		if ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, L_THEPLUS_MINIMUM_ELEMENTOR_VERSION, '<' ) ) {
			add_action( 'admin_notices', array( $this, 'tp_f_elementor_outdated_notice' ) );
			return;
		}

		// Register class automatically.
		$this->tp_manage_files();

		$this->includes();

		// Finally hooked up all things.
		$this->hooks();

		/*
		 * Always hook the control registration. Whether Pro supersedes it is
		 * decided inside add_controls(), once Pro's state is actually known --
		 * see tpae_pro_handles_widgets().
		 */
		L_Theplus_Elements_Integration()->init();

		$this->include_widgets();
		$tpae_s_options = get_option( 'theplus_api_connection_data' );
		$theplus_ability_switch = ! empty( $tpae_s_options['theplus_ability_switch'] ) ? $tpae_s_options['theplus_ability_switch'] : '';
		if('on' === $theplus_ability_switch){
			include L_THEPLUS_PATH . 'modules/ability/class-tp-ability-main.php';
		}
	}

	public function tpae_elementor_ajax_call() {

		check_ajax_referer("tpae-addons", "nonce");

		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error([
				'message' => __('Invalid permission. Only administrators can perform this action.', 'tpebl')
			], 403);
		}

		$tp_slug             = 'elementor';
		$tp_plugin_basename  = 'elementor/elementor.php';

		include_once ABSPATH . 'wp-admin/includes/file.php';
		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
		include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

		$installed_plugins = get_plugins();

		if ( ! function_exists( 'plugins_api' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		}

		$plugin_info = plugins_api(
			'plugin_information',
			[
				'slug'   => $tp_slug,
				'fields' => ['version' => false],
			]
		);

		if ( is_wp_error( $plugin_info ) || ! $plugin_info ) {
			wp_send_json_error([
				'message' => __('Failed to retrieve plugin information.', 'tpebl')
			]);
		}

		$skin     = new \Automatic_Upgrader_Skin();
		$upgrader = new \Plugin_Upgrader($skin);


		if (!isset($installed_plugins[$tp_plugin_basename])) {

			$installed = $upgrader->install($plugin_info->download_link);

			if (!$installed) {
				wp_send_json_error([
					'message' => __('Failed to install Elementor plugin.', 'tpebl')
				]);
			}

			$activation = activate_plugin($tp_plugin_basename);

			if (is_wp_error($activation)) {
				wp_send_json_error([
					'message' => __('Plugin installed but activation failed.', 'tpebl')
				]);
			}

			wp_send_json_success([
				'message' => __('Elementor installed & activated successfully!', 'tpebl'),
				'installed' => true,
				'activated' => true,
			]);
		}

		$activation = activate_plugin($tp_plugin_basename);

		if (is_wp_error($activation)) {
			wp_send_json_error([
				'message' => __('Elementor activation failed.', 'tpebl')
			]);
		}

		wp_send_json_success([
			'message' => __('Elementor activated successfully!', 'tpebl'),
			'installed' => true,
			'activated' => true,
		]);
	}


	/*
	* Admin Enqueue Scripts
	* @sinc 6.4.3
	**/
	public function admin_enqueue_css_js( $hook ){
		
		wp_enqueue_script( 'tpae-admins-js', L_THEPLUS_ASSETS_URL . 'js/admin/tp-elementor-install.js',array() , L_THEPLUS_VERSION, true );
		wp_localize_script(
			'tpae-admins-js',
			'tpae_admins_js',
			array(
				'ajax_url'   => esc_url( admin_url( 'admin-ajax.php' ) ),
				'tpae_nonce' => wp_create_nonce("tpae-addons"),
			)
		);

	}

	/**
	 * Load Text Domain.
	 * Text Domain : tpebl
	 *
	 * @since 5.6.6
	 */
	public function tp_i18n() {
		load_plugin_textdomain( 'tpebl', false, L_THEPLUS_PNAME . '/languages' );
	}

	/**
	 * Include and manage files related to notices.
	 *
	 * This function includes the class responsible for managing notices in ThePlus plugin.
	 * It includes the file class-tp-notices-main.php from the specified path.
	 *
	 * @since 5.1.18
	 */
	public function tp_manage_files() {

		if ( is_admin() ) {
			// Admin-only: DB setup, widget scan, notices, feedback, editor UI.
			// These hooks (wp_ajax_*, elementor/editor/*, admin_menu, admin_notices)
			// only fire in admin context where is_admin() returns true.
			require_once L_THEPLUS_PATH . 'includes/admin/tpae-hooks/class-tpae-main-hooks.php';

			include L_THEPLUS_PATH . 'includes/notices/class-tp-notices-main.php';
			include L_THEPLUS_PATH . 'includes/user-experience/class-tp-user-experience-main.php';

			// Editor-only: loop builder, preset, and theme builder controls.
			// Their hooks (elementor/editor/before_enqueue_scripts, elementor/editor/footer,
			// elementor/editor/after_enqueue_scripts, wp_ajax_*) all fire in admin context.
			include L_THEPLUS_PATH . 'includes/preset/class-wdkit-preset.php';
			include L_THEPLUS_PATH . 'modules/controls/theme-builder/tpae-class-nxt-download.php';
		}

		// MUST load on frontend:
		// - Registers custom post types (clients, testimonials, team members) via init hook.
		// - Outputs user-defined custom CSS/JS to wp_head and wp_footer.
		include L_THEPLUS_PATH . 'includes/admin/dashboard/class-tpae-dashboard-main.php';

		// MUST load on frontend:
		// - Defines Tp_LazyLoad_Images class and helper functions used by widget render methods
		//   (tp_get_image_rander, tp_has_lazyload, tp_bg_lazyLoad, tp_getAspectRatio).
		require_once L_THEPLUS_PATH . 'includes/tp-lazy-function.php';
	}

	/**
	 * Hooks Setup for ThePlus Load Class
	 *
	 * This private method sets up hooks and actions needed for the functionality of the ThePlus Load class.
	 *
	 * @since 5.1.18
	 * @version 6.4.1
	 */
	private function hooks() {
		$theplus_options = get_option( 'theplus_options' );

		$plus_extras = l_theplus_get_option( 'general', 'extras_elements' );
		$elements    = l_theplus_get_option( 'general', 'check_elements' );

		if ( ( isset( $plus_extras ) && empty( $plus_extras ) && empty( $theplus_options ) ) || ( ! empty( $plus_extras ) && in_array( 'plus_display_rules', $plus_extras ) ) ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'print_style' ) );
		}

		// add_action( 'elementor/init', array( $this, 'add_elementor_category' ) );
		add_action( 'elementor/elements/categories_registered', array( $this, 'add_elementor_category' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'theplus_editor_styles' ) );
		
		/*
		 * Register the icon-font handle only; consumers enqueue it on demand
		 * (Social Feed get_style_depends(), admin timeline), so pages without TPAE
		 * icons never load it. Replaces the 6.4.2 blanket enqueue.
		 * @since 6.5.1
		 */
		add_action( 'wp_enqueue_scripts', array( $this, 'theplus_register_icons_library' ) );
		add_action( 'elementor/frontend/after_register_styles', array( $this, 'theplus_register_icons_library' ) );

		add_filter( 'upload_mimes', array( $this, 'theplus_mime_types' ) );
		add_filter( 'wp_handle_upload_prefilter', array( $this, 'theplus_sanitize_svg_upload' ) );

		// Include some backend files.
		add_action( 'admin_enqueue_scripts', array( $this, 'theplus_elementor_admin_css' ) );

		// Notification UI hook + default seeding only matter in admin context.
		// admin_footer never fires on frontend, and add_option idempotency
		// checks still cost an alloptions lookup × 2 per request.
		if ( is_admin() ) {
			$get_notification = get_option( 'tpae_menu_notification' );

			if ( $get_notification !== TPAE_MENU_NOTIFICETIONS ) {
				add_action( 'admin_footer', array( $this, 'tpae_add_notificetion' ) );
			}

			add_option( 'tpae_menu_notification', '3' );
			add_option( 'tpae_whats_new_notification', '3' );
		}
	}

	/**
	 * Include Module Manager and Admin PHP Files
	 *
	 * This private method is called during the class instantiation and loads
	 * the required module manager and admin PHP files.
	 *
	 * @since 1.0.0
	 */
	private function includes() {

		require_once L_THEPLUS_INCLUDES_URL . 'plus_addon.php';
		require_once L_THEPLUS_PATH . 'modules/widgets-features/class-tp-widgets-feature-main.php';

		add_action( 'elementor/init', function() {
			require L_THEPLUS_PATH . 'modules/extensions/class-tpae-extensions-main.php';
		});
		// require L_THEPLUS_PATH . 'modules/theplus-core-cp.php';

		// Defines L_Theplus_Elements_Integration; loaded unconditionally so Free can
		// still register its controls if Pro turns out never to have loaded.
		require L_THEPLUS_PATH . 'modules/theplus-integration.php';
		include L_THEPLUS_PATH . 'modules/widget-promotion/tp-widget-promotion-main.php';

		require L_THEPLUS_PATH . 'modules/query-control/module.php';

		require_once L_THEPLUS_PATH . 'modules/helper-function.php';
	}

	/**
	 * Include Widget Files
	 *
	 * This method is responsible for including the required files related to widgets.
	 * It ensures that the necessary files for widgets are loaded.
	 *
	 * @since 1.0.0
	 */
	public function include_widgets() {
		require_once L_THEPLUS_PATH . 'modules/theplus-include-widgets.php';

		if ( defined( 'THEPLUS_VERSION' ) ) {
			require L_THEPLUS_PATH . 'includes/admin/white-label/class-tpae-white-label.php';
		}
	}

	/**
	 * Theplus_Element_Loader Class
	 *
	 * This class manages the inclusion of styles for Theplus Elementor Editor.
	 *
	 * @since 1.0.0
	 */
	public function theplus_editor_styles() {

		wp_enqueue_style( 'theplus-ele-admin', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin.css', array(), L_THEPLUS_VERSION, false );
		wp_enqueue_style( 'theplus-icons-library', L_THEPLUS_ASSETS_URL . 'fonts/style.css', array(), L_THEPLUS_VERSION, false );

		if ( defined( 'THEPLUS_VERSION' ) ) {
			$white_label_options = get_option( 'theplus_white_label', array() );

			if ( is_array( $white_label_options ) ) {
				$wl_logo = ! empty( $white_label_options['tp_plus_logo'] ) ? $white_label_options['tp_plus_logo'] : '';
				$wl_name = ! empty( $white_label_options['tp_plugin_name'] ) ? $white_label_options['tp_plugin_name'] : '';
				$wl_name = ! empty( $wl_name ) ? $wl_name : ( ! empty( $white_label_options['l_tp_plugin_name'] ) ? $white_label_options['l_tp_plugin_name'] : '' );

				if ( ! empty( $wl_logo ) || ! empty( $wl_name ) ) {
					$inline_css = '';

					if ( ! empty( $wl_logo ) ) {
						$wl_logo_escaped = esc_url( $wl_logo );
						$inline_css .= '.elementor-element .icon i.tpae-editor-logo:after { background-image: url("' . $wl_logo_escaped . '"); background-size: contain; background-repeat: no-repeat; }';
					} else {
						$inline_css .= '.elementor-element .icon i.tpae-editor-logo:after { background-image: none; }';
					}

					wp_add_inline_style( 'theplus-ele-admin', $inline_css );
				}
			}
		}

		$ui_theme = SettingsManager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );

		if ( ! empty( $ui_theme ) && 'dark' === $ui_theme ) {
			wp_enqueue_style( 'theplus-ele-admin-dark', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin-dark.css', array(), L_THEPLUS_VERSION, false );
		}
	}

	/**
	 * Register the icon-font stylesheet (register only; consumers enqueue on
	 * demand). Kept at its real plugin path so its bare-relative font URLs
	 * resolve -- never merge it into the uploads cache.
	 * @since 6.5.1
	 */
	public function theplus_register_icons_library() {
		if ( ! wp_style_is( 'theplus-icons-library', 'registered' ) ) {
			wp_register_style( 'theplus-icons-library', L_THEPLUS_ASSETS_URL . 'fonts/style.css', array(), L_THEPLUS_VERSION );
		}
	}

	/**
	 * Enqueue Theplus Elementor Admin CSS and JavaScript
	 *
	 * This method enqueues the necessary scripts and styles for Theplus Elementor Admin.
	 * It includes jQuery UI Dialog, Theplus Elementor Admin CSS, and a custom admin JavaScript file.
	 * Additionally, it sets up inline JavaScript variables for AJAX functionality.
	 *
	 * @since 6.1.0
	 */
	public function theplus_elementor_admin_css( $hook ) {

		/*
		 * The TPAE admin-menu styling (icon, submenu flyout, notification badge)
		 * lives inside theplus-ele-admin.css, but the WordPress admin menu is
		 * visible on EVERY admin screen -- so those menu rules must load
		 * everywhere, even though the rest of the stylesheet is gated to the
		 * plugin's own screens below. Emit just the admin-menu rules inline on all
		 * admin pages so the menu logo / submenu / badge are never left unstyled.
		 */
		$tp_menu_img = esc_url( L_THEPLUS_ASSETS_URL . 'images/tpae-favicon-white.png' );

		wp_register_style( 'tpae-admin-menu', false, array(), L_THEPLUS_VERSION );
		wp_enqueue_style( 'tpae-admin-menu' );
		wp_add_inline_style(
			'tpae-admin-menu',
			'.dashicons-plus-settings,.wp-menu-open.toplevel_page_theplus_welcome_page .dashicons-plus-settings,.current.toplevel_page_theplus_welcome_page .dashicons-plus-settings{background:url(' . $tp_menu_img . ') center/22px no-repeat;}'
			. '#toplevel_page_theplus_welcome_page .wp-submenu li>a{display:flex;align-items:center;gap:6px;}'
			. '#toplevel_page_theplus_welcome_page .wp-submenu li>a>i{font-size:16px;flex:0 0 auto;}'
			. '#toplevel_page_theplus_welcome_page .wp-submenu li>a>i.activate{color:#FF0004;}'
			. '#toplevel_page_theplus_welcome_page.tpae-admin-notice-active a[href="admin.php?page=theplus_welcome_page#/"].wp-has-submenu::after{content:"1";background:#ca2222;color:#fff;border-radius:50%;position:absolute;top:0;right:1px;width:18px;font-size:12px;height:18px;text-align:center;line-height:17px;border:unset!important}'
		);

		/*
		 * This callback fires on admin_enqueue_scripts for EVERY admin screen, so
		 * without a gate it injected ~21KB of CSS/JS plus a fresh nonce into
		 * Dashboard, Posts, Media, Users and Settings. The assets are only needed
		 * on TPAE's own admin pages and by the deactivation-feedback modal on the
		 * Plugins screen. Match on the page slug rather than $hook, because the
		 * submenu hook suffix derives from the (white-labelable) menu title. The
		 * Elementor editor loads the same stylesheet separately via
		 * theplus_editor_styles().
		 */
		$tp_admin_page = isset( $_GET['page'] ) ? sanitize_text_field( wp_unslash( $_GET['page'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check, no state change.
		$tp_our_pages  = array( 'theplus_welcome_page', 'tpae-form-submissions' );

		if ( 'plugins.php' !== $hook && ! in_array( $tp_admin_page, $tp_our_pages, true ) ) {
			return;
		}

		wp_enqueue_style( 'theplus-ele-admin', L_THEPLUS_ASSETS_URL . 'css/admin/theplus-ele-admin.css', array(), L_THEPLUS_VERSION, false );
		wp_enqueue_script( 'theplus-admin-js', L_THEPLUS_ASSETS_URL . 'js/admin/theplus-admin.js', array(), L_THEPLUS_VERSION, false );

		$script_handle = 'theplus-admin-js';

		$js_inline = 'var theplus_ajax_url = "' . esc_url(admin_url("admin-ajax.php")) . '";
        var theplus_ajax_post_url = "' . esc_url(admin_url("admin-post.php")) . '";
        var theplus_nonce = "' . esc_js(wp_create_nonce("theplus-addons")) . '";';

		wp_add_inline_script( $script_handle, $js_inline );
	}

	/**
	 * Modify Allowed MIME Types for File Uploads
	 *
	 * This function is a WordPress filter used to extend the list of allowed MIME types for file uploads.
	 * It adds support for SVG (Scalable Vector Graphics) and SVGZ (compressed SVG) file types.
	 *
	 * @param array $mimes Associative array of allowed MIME types.
	 * @return array Modified array of allowed MIME types.
	 *
	 * @since 1.0.0
	 */
	public function theplus_mime_types( $mimes ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $mimes;
		}

		$mimes['svg']  = 'image/svg+xml';
		$mimes['svgz'] = 'image/svg+xml';

		return $mimes;
	}

	/**
	 * Sanitize uploaded SVGs
	 *
	 * @since 6.3.16
	 */
	public function theplus_sanitize_svg_upload( $file ) {

		$ext = strtolower( pathinfo( $file['name'], PATHINFO_EXTENSION ) );

		if ( 'svg' !== $ext && 'svgz' !== $ext ) {
			return $file;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			$file['error'] = __( 'You are not allowed to upload SVG files.', 'tpebl' );
			return $file;
		}

		$contents = file_get_contents( $file['tmp_name'] );

		if ( false === $contents || '' === $contents ) {
			$file['error'] = __( 'SVG file could not be read.', 'tpebl' );
			return $file;
		}

		if ( 'svgz' === $ext ) {
			if ( ! function_exists( 'gzdecode' ) ) {
				$file['error'] = __( 'SVGZ uploads are not supported on this server (zlib unavailable).', 'tpebl' );
				return $file;
			}

			$decoded = @gzdecode( $contents );

			if ( false === $decoded || '' === $decoded ) {
				$file['error'] = __( 'Malformed SVGZ file rejected.', 'tpebl' );
				return $file;
			}

			$contents = $decoded;
		}

		// After decompression (if any) the payload must look like an SVG.
		// Reject anything that doesn't start with whitespace + `<` and contain a root <svg> tag.
		if ( ! preg_match( '/<\s*svg\b/i', $contents ) ) {
			$file['error'] = __( 'File does not appear to be a valid SVG.', 'tpebl' );
			return $file;
		}

		$bad_patterns = array(
			'/<\s*script/i',
			'/\son[a-z]+\s*=/i',
			'/<\s*foreignObject/i',
			'/<\s*(iframe|embed|object|frame|frameset)/i',
			'/<\s*(animate|animateMotion|animateTransform|set)\b/i',
			'/<\s*use\b[^>]*\b(?:xlink:)?href\s*=\s*["\']?\s*(?:https?:|\/\/|data:)/i',
			'/<!ENTITY/i',
			'/<!DOCTYPE[^>]*\[/i',
			'/SYSTEM\s+["\']/i',
			'/<\?xml-stylesheet/i',
			'/@import\b/i',
			'/expression\s*\(/i',
			'/javascript\s*:/i',
			'/vbscript\s*:/i',
			'/data\s*:\s*(?:text\/html|application\/(?:javascript|ecmascript|xhtml))/i',
		);

		foreach ( $bad_patterns as $re ) {
			if ( preg_match( $re, $contents ) ) {
				$file['error'] = __( 'SVG contains unsafe content', 'tpebl' );
				return $file;
			}
		}

		return $file;
	}

	/**
	 * Print style.
	 *
	 * Registers and attaches the small piece of CSS that hides elements
	 * marked with the .plus-conditions--hidden class on the frontend.
	 *
	 * Fired by the `wp_enqueue_scripts` action.
	 *
	 * @since 2.1.0
	 */
	public function print_style() {
		wp_register_style( 'tpae-display-conditions', false );
		wp_enqueue_style( 'tpae-display-conditions' );
		wp_add_inline_style(
			'tpae-display-conditions',
			'*:not(.elementor-editor-active) .plus-conditions--hidden { display: none; }'
		);
	}

	/**
	 * Add Elementor Category for PlusEssential Elements
	 *
	 * This method is responsible for adding a custom category to the Elementor Page Builder
	 * for PlusEssential elements.
	 *
	 * @since 6.0.5
	 */
	public function add_elementor_category() {

		$elementor = \Elementor\Plugin::$instance;

		$post_id = get_the_ID();
		$template_type = '';

		if ( $post_id ) {
			$document = \Elementor\Plugin::$instance->documents->get( $post_id );
			if ( $document ) {
				$template_type = $document->get_name();
				$source_type   = get_post_meta( $post_id, '_elementor_source', true );
			} else {
				$template_type = get_post_meta( $post_id, '_elementor_template_type', true );
			}
		}

		$plus_categories = array(
			'plus-essential'   => array( 'title' => esc_html__( 'Plus Essential', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-advanced'    => array( 'title' => esc_html__( 'Plus Advanced', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-creative'    => array( 'title' => esc_html__( 'Plus Creative', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-listing'     => array( 'title' => esc_html__( 'Plus Listing', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-social'      => array( 'title' => esc_html__( 'Plus Social', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-forms'       => array( 'title' => esc_html__( 'Plus Forms', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-woo-builder' => array( 'title' => esc_html__( 'Plus WooCommerce', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
			'plus-depreciated' => array( 'title' => esc_html__( 'Plus Depreciated', 'tpebl' ), 'icon'  => 'fa fa-plug' ),
		);

		if ( $post_id ) {
			$post_type = get_post_type( $post_id );

			/** For check header of the nexter extension */
			if ( in_array( $post_type, [ 'nxt_builder', 'nxt_template' ], true ) ) {
				$template_type = get_post_meta( $post_id, 'template_type', true );
			}
		}

		// if ( 'loop-item' === $template_type ) {
		// 	$template_type = $source_type;
		// }

		if ( in_array( $template_type, [ 'header' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();
        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-header'] = [
						'title' => esc_html__( 'Plus Header', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'archive', 'archives' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();
        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-archive'] = [
						'title' => esc_html__( 'Plus Archive', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'product-archive' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-product-archive'] = [
						'title' => esc_html__( 'Plus Product Archive', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'product', 'singular' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-product'] = [
						'title' => esc_html__( 'Plus Product', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

		if ( in_array( $template_type, [ 'single-page', 'single-post', 'singular' ] ) ) {
        	$all_categories = $elementor->elements_manager->get_categories();

        	$new_categories = [];

			foreach ( $all_categories as $key => $category ) {
				$new_categories[ $key ] = $category;

				if ( 'favorites' === $key ) {
					$new_categories['plus-single'] = [
						'title' => esc_html__( 'Plus Single', 'tpebl' ),
						'icon'  => 'fa fa-plug',
					];
				}
			}

			$reflection = new \ReflectionProperty( $elementor->elements_manager, 'categories' );
			$reflection->setAccessible( true );
			$reflection->setValue( $elementor->elements_manager, $new_categories );
		}

        foreach ( $plus_categories as $index => $plus_widgets ) {
            $elementor->elements_manager->add_category(
                $index,
                array(
                    'title' => $plus_widgets['title'],
                    'icon'  => $plus_widgets['icon'],
                ),
                1
            );
        }

	}

	/**
	 * The Plus Addon Menu Notifications icon
	 *
	 * @since 6.4.1
	 */
	public function tpae_add_notificetion() {

		?>
			<script type="text/javascript">
				document.addEventListener('DOMContentLoaded', function() {
					var menuItem = document.querySelector('#toplevel_page_theplus_welcome_page');
					if (menuItem) {
						menuItem.classList.add('tpae-admin-notice-active');
					}
				});
			</script>
		<?php
	}
}

L_Theplus_Element_Load::instance();