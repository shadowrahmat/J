<?php
/**
 * The file store Database Default Entry
 *
 * @link    https://posimyth.com/
 * @since   6.0.0
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

/**Exit if accessed directly.*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tpae_Dashboard_Ajax' ) ) {

	/**
	 * Tpae_Dashboard_Ajax
	 *
	 * @since 6.0.0
	 */
	class Tpae_Dashboard_Ajax {

		/**
		 * Member Variable
		 *
		 * @var instance
		 */
		private static $instance;

		/**
		 * Member Variable
		 *
		 * @var global_setting
		 */
		public $global_setting = array();

		/**
		 *  Initiator
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
		 * @since    6.0.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_tpae_dashboard_ajax_call', array( $this, 'tpae_dashboard_ajax_call' ) );
		}

		/**
		 * Load the required dependencies for this plugin.
		 *
		 * @since 6.0.0
		 * @version 6.3.17
		 */
		public function tpae_dashboard_ajax_call() {

			if ( ! check_ajax_referer( 'tpae-db-nonce', 'nonce', false ) ) {

				$response = $this->tpae_set_response( false, 'Invalid nonce.', 'The security check failed. Please refresh the page and try again.' );

				wp_send_json( $response );
				wp_die();
			}

			if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
				$response = $this->tpae_set_response( false, 'Invalid Permission.', 'Something went wrong.' );

				wp_send_json( $response );
				wp_die();
			}

			$type = isset( $_POST['type'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['type'] ) ) ) : false;
			if ( ! $type ) {
				$response = $this->tpae_set_response( false, 'Invalid type.', 'Something went wrong.' );

				wp_send_json( $response );
				wp_die();
			}

			switch ( $type ) {
				case 'tpae_onload_data':
					$response = $this->tpae_onload_data();
					break;
				case 'tpae_set_widget_list':
					$response = $this->tpae_set_widget_list();
					break;
				case 'tpae_get_scan_widgets':
					$response = $this->tpae_get_elements_status_scan();
					break;
				case 'tpae_get_scan_extension':
					$response = $this->tpae_get_extension_status_scan();
					break;
				case 'tpae_set_extra_options':
					$response = $this->tpae_set_extra_options();
					break;
				case 'tpae_get_custom_css_js':
					$response = $this->tpae_get_custom_css_js();
					break;
				case 'tpae_set_custom_css_js':
					$response = $this->tpae_set_custom_css_js();
					break;
				case 'tpae_set_listing_data':
					$response = $this->tpae_set_listing_data();
					break;
				case 'tpae_prev_version':
					$response = $this->tpae_prev_version();
					break;
				case 'tpae_rollback_check':
					$response = $this->tpae_rollback_check();
					break;
				case 'tpae_performance_manage':
					$response = $this->tpae_performance_manage();
					break;
				case 'tpae_plugin_install':
					$response = $this->tpae_plugin_install();
					break;
				case 'tpae_theme_install':
					$response = $this->tpae_theme_install();
					break;
				case 'tpae_api_call':
					$response = $this->tpae_api_call();
					break;
				case 'tpae_transient_manage':
					$response = $this->tpae_transient_manage();
					break;
				case 'tpae_wp_option_manage':
					$response = $this->tpae_wp_option_manage();
					break;
				case 'tpae_update_wdk_widget':
					$response = apply_filters( 'wdk_widget_ajax_call', 'wdk_update_widget' );
					break;
				case 'tpae_license_manage':
					$response = apply_filters( 'tpaep_licence_ajax_call', 'tpaep_license_manage' );
					break;
				case 'set_whitelabel':
					$response = apply_filters( 'tpaep_dashboard_ajax_call', 'tpaep_set_whitelabel' );
					break;
				case 'tpae_widgets_setting_data':
					$response = $this->tpae_widgets_setting_data();

					break;
				case 'tpae_onboarding_setup':
					$response = $this->tpae_onboarding_setup();
					break;
				case 'tpae_user_meta_data':
					$response = $this->tpae_user_meta_data();
					break;
				case 'tpae_analytics_consent':
					$response = $this->tpae_analytics_consent();
					break;
				case 'tpae_whats_new_close':
					$response = $this->tpae_whats_new_close();
					break;
			}

			wp_send_json( $response );
			wp_die();
		}

		/**
		 * Set Response
		 *
		 * @since 6.0.0
		 */
		public function tpae_onload_data() {

			// $plugins = isset( $_POST['plugin_data'] ) ? json_decode( sanitize_text_field( wp_unslash( $_POST['plugin_data'] ) ) ) : array();

			$plugins = array(
				array(
					'name'        => 'wdesignkit',
					'status'      => '',
					'plugin_slug' => 'wdesignkit/wdesignkit.php',
				),
				array(
					'name'        => 'the-plus-addons-for-block-editor',
					'status'      => '',
					'plugin_slug' => 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php',
				),
				array(
					'name'        => 'uichemy',
					'status'      => '',
					'plugin_slug' => 'uichemy/uichemy.php',
				),
				array(
					'name'        => 'nexter-extension',
					'status'      => '',
					'plugin_slug' => 'nexter-extension/nexter-extension.php',
				),
				// array(
				// 'name'        => 'envato-elements',
				// 'status'      => '',
				// 'plugin_slug' => 'envato-elements/envato-elements.php',
				// ),
			);

			$plugin_details = $this->tpae_check_plugins_depends( $plugins );
			$plugin_details = ! empty( $plugin_details ) ? $plugin_details : $plugins;

			$user = wp_get_current_user();

			$user_image = get_avatar_url( $user->ID );

			$tpae_pro = defined( 'THEPLUS_VERSION' ) ? 1 : 0;

			$get_whats_new = get_transient( 'tp_dashboard_overview' );
			// $get_active_widgets = $this->tpae_get_elements_status_scan();

			$user_info = array(
				'user_image'      => $user_image,
				'roles'           => $user->roles,
				'user_name'       => $user->display_name,
				'tpae_pro'        => $tpae_pro,
				'whatsnew'        => $get_whats_new,
				'user_email'      => $user->user_email,
				// Ships with the initial payload so the Settings Privacy card renders in the
				// first paint alongside every other section, instead of waiting on its own
				// tpae_analytics_consent (operation=get) round-trip after mount.
				'analytics_state' => $this->tpae_analytics_state(),
				// 'used_widgets' => $get_active_widgets,
				'success'         => true,
			);

			$tp_form_settings = get_option( 'theplus_widgets_settings' );

			if ( false === $tp_form_settings ) {
				$form_default_settings = array(
					'tp_plus_form' => array(
						'google_site_key'       => '',
						'google_secret_key'     => '',
						'cloudflare_site_key'   => '',
						'cloudflare_secret_key' => '',
						'active_tab'            => 'google',
					),
				);

				add_option( 'theplus_widgets_settings', $form_default_settings );
			}

			$elementor_disabled = apply_filters( 'tpae_elementor_disable_widgets', null );
			$get_widget_list    = get_option( 'theplus_options', array() );
			$get_extra_option   = get_option( 'theplus_api_connection_data', array() );
			$get_listing_data   = get_option( 'post_type_options' );
			$get_custom_css_js  = get_option( 'theplus_styling_data' );
			$get_performance    = get_option( 'theplus_performance' );

			$wdk_widgets      = array();
			$wdk_widgets      = apply_filters( 'wdk_widget_ajax_call', 'wdk_get_widget_ajax' );
			$et_plugin_status = apply_filters( 'tpae_get_plugin_status', 'template-kit-import/template-kit-import.php' );

			$check_onboarding = get_option( 'tpae_onbording_end' );

			$set_onboarding['check_onboarding'] = 'show';
			if ( $check_onboarding || 'active' === $et_plugin_status || 'inactive' === $et_plugin_status ) {
				$set_onboarding['check_onboarding'] = 'hide';
			}

			$response = array(
				'success'            => true,
				'message'            => esc_html__( 'success', 'tpebl' ),
				'description'        => esc_html__( 'success', 'tpebl' ),
				'user_info'          => $user_info,
				'widgets'            => $get_widget_list,
				'extra_option'       => $get_extra_option,
				'listing_data'       => $get_listing_data,
				'plugin_detail'      => $plugin_details,
				'custom_css_js'      => $get_custom_css_js,
				'performance'        => $get_performance,
				'wdk_widgets'        => $wdk_widgets,
				'elementor_disabled' => $elementor_disabled,
				'tp_widgets_setting' => $tp_form_settings,
				'check_onboarding'   => $set_onboarding,
			);

			if ( defined( 'THEPLUS_VERSION' ) ) {

				$get_white_label = get_option( 'theplus_white_label' );

				$get_woo_thankyou_options = apply_filters( 'tpaep_dashboard_ajax_call', 'tpaep_woo_thankyou_options' );
				$get_licence_data         = apply_filters( 'tpaep_licence_ajax_call', 'tpaep_license_status' );

				$response['white_label']     = $get_white_label;
				$response['license_details'] = $get_licence_data;

				$response['extra_option']['thankyou_page'] = $get_woo_thankyou_options;
			}

			return $response;
		}

		/**
		 *
		 * It is Use for Check Plugin Dependency of template.
		 *
		 * @since 6.0.0
		 */
		public function tpae_check_plugins_depends( $plugins ) {
			$update_plugin = array();

			$all_plugins = get_plugins();

			foreach ( $plugins as $plugin ) {
				$pluginslug = ! empty( $plugin['plugin_slug'] ) ? sanitize_text_field( wp_unslash( $plugin['plugin_slug'] ) ) : '';

				if ( ! is_plugin_active( $pluginslug ) ) {
					if ( ! isset( $all_plugins[ $pluginslug ] ) ) {
							$plugin['status'] = 'unavailable';
					} else {
						$plugin['status'] = 'inactive';
					}

					$update_plugin[] = $plugin;
				} elseif ( is_plugin_active( $pluginslug ) ) {
					$plugin['status'] = 'active';
					$update_plugin[]  = $plugin;
				}
			}

			return $update_plugin;
		}

		/**
		 * Plugin Install
		 *
		 * @since 6.0.0
		 */
		public function tpae_set_widget_list() {

			$raw         = isset( $_POST['widget_data'] ) ? wp_unslash( $_POST['widget_data'] ) : '';
			$widget_data = is_string( $raw ) ? json_decode( $raw, true ) : null;

			if ( ! is_array( $widget_data ) ) {
				return $this->tpae_set_response( false, 'invalid_payload', 'Invalid payload.' );
			}

			$data = get_option( 'theplus_options' );

			if ( false === $data ) {
				return $this->tpae_set_response( false, 'oops.', 'oops.' );
			}

			update_option( 'theplus_options', $widget_data );

			// Remove Elementor Disable Widget.
			$elementor_disabled_elements = get_option( 'elementor_disabled_elements', false );
			$check_elements              = isset( $widget_data['check_elements'] ) && is_array( $widget_data['check_elements'] ) ? $widget_data['check_elements'] : array();

			if ( ! empty( $elementor_disabled_elements ) ) {
				$converted = array_map(
					function ( $widget ) {
						return str_replace( '-', '_', $widget );
					},
					$elementor_disabled_elements
				);

				$final = array_diff( $converted, $check_elements );
				$final = array_values( $final );

				update_option( 'elementor_disabled_elements', $final, 'yes' );
			}

			$this->tpae_backend_catch_remove();

			return $this->tpae_set_response( true, 'Successfully.', 'Successfully.' );
		}

		/**
		 * Extra Options
		 *
		 * @since 6.0.0
		 */
		public function tpae_set_extra_options() {
			$get_options_data = get_option( 'theplus_api_connection_data' );

			/**
			 * `sanitize_text_field()` must not run on the JSON envelope. It strips tags,
			 * percent-octets and line breaks from the serialised string, so one such character
			 * anywhere in the payload breaks the JSON, `json_decode()` returns null and the
			 * update below stores that null over every saved setting. The endpoint is already
			 * nonce-checked and gated on `manage_options`, so the raw body is decoded here and
			 * the individual values are escaped by their consumers.
			 *
			 * @since 6.5.1
			 */
			$extra_options_raw  = isset( $_POST['extra_options_data'] ) ? wp_unslash( $_POST['extra_options_data'] ) : '';
			$extra_options_data = json_decode( $extra_options_raw, true );

			/**
			 * A truncated or malformed body decodes to null, and writing that over the option
			 * would drop every stored setting. Refuse the write instead so the saved values
			 * survive a bad request.
			 *
			 * @since 6.5.1
			 */
			if ( ! is_array( $extra_options_data ) ) {
				return $this->tpae_set_response( false, 'Invalid data.', 'Could not read the submitted settings.' );
			}

			if ( empty( $get_options_data ) ) {
				add_option( 'theplus_api_connection_data', $extra_options_data, '', 'on' );
			} else {
				update_option( 'theplus_api_connection_data', $extra_options_data );
			}

			$this->tpae_backend_catch_remove();

			return $this->tpae_set_response( true, 'Data Updated.', 'Data Updated Successfully.' );
		}

		/**
		 * Scan Widget : Get all Scan Widget list
		 *
		 * @since 6.1.4
		 */
		public function tpae_get_elements_status_scan() {

			$type = array( 'get_unused_widgets' );

			return apply_filters( 'tpae_widget_scan', $type );
		}

		/**
		 * Scan Extension : Get all Scan Extension list
		 *
		 * @since 6.1.4
		 */
		public function tpae_get_extension_status_scan() {

			$type = array( 'get_unused_extentions' );

			return apply_filters( 'tpae_widget_scan', $type );
		}


		/**
		 * tpae_get_custom_css_js
		 *
		 * @since 6.0.0
		 */
		public function tpae_get_custom_css_js() {
			$theplus_styling_data = get_option( 'theplus_styling_data' );

			$css_rules = '';
			$js_rules  = '';

			if ( ! empty( $theplus_styling_data['theplus_custom_css_editor'] ) ) {
				$css_rules = $theplus_styling_data['theplus_custom_css_editor'];

				$css_rules     .= '<style>';
					$css_rules .= $theplus_styling_data['theplus_custom_css_editor'];
				$css_rules     .= '</style>';
			}

			if ( ! empty( $theplus_styling_data['theplus_custom_js_editor'] ) ) {
				$theplus_custom_js_editor = $theplus_styling_data['theplus_custom_js_editor'];
				$js_rules                 = $theplus_custom_js_editor;
				$js_rules                 = wp_print_inline_script_tag( $js_rules );
			}

			return array(
				'css' => $css_rules,
				'js'  => $js_rules,
			);
		}

		/**
		 * tpae_set_custom_css_js
		 *
		 * @since 6.0.0
		 */
		public function tpae_set_custom_css_js() {
			$theplus_styling_data = get_option( 'theplus_styling_data' );

			if ( ! isset( $_POST['new_code'] ) ) {
				return $this->tpae_set_response( false, 'Invalid request.', 'No code provided.' );
			}

			$new_code = json_decode( wp_unslash( $_POST['new_code'] ), true );

			$css = isset( $new_code['css'] ) ? $new_code['css'] : '';
			$js  = isset( $new_code['js'] ) ? $new_code['js'] : '';

			// get_option() returns false when unset; assigning an offset on false is deprecated.
			if ( ! is_array( $theplus_styling_data ) ) {
				$theplus_styling_data = array();
			}

			$theplus_styling_data['theplus_custom_css_editor'] = $css;

			/**
			 * Site-wide JavaScript is precisely what `unfiltered_html` governs in core. On
			 * single-site an administrator holds that capability, so this changes nothing. On
			 * multisite a Site Administrator has `manage_options` but NOT `unfiltered_html`, and
			 * this field would otherwise let them inject script into every page — a privilege
			 * WordPress deliberately withholds from them.
			 *
			 * An unchanged value is allowed through so a user who cannot edit the JS can still
			 * save the CSS field beside it; only an actual modification is refused.
			 *
			 * @since 6.5.0
			 */
			$stored_js = isset( $theplus_styling_data['theplus_custom_js_editor'] )
				? (string) $theplus_styling_data['theplus_custom_js_editor']
				: '';

			if ( current_user_can( 'unfiltered_html' ) ) {
				$theplus_styling_data['theplus_custom_js_editor'] = $js;
			} elseif ( (string) $js !== $stored_js ) {
				return $this->tpae_set_response( false, 'Insufficient permissions.', 'Saving custom JavaScript requires the unfiltered_html capability.' );
			}

			update_option( 'theplus_styling_data', $theplus_styling_data );

			return $theplus_styling_data;
		}

		/**
		 * tpae_set_custom_css_js
		 *
		 * @since 6.0.0
		 */
		public function tpae_set_listing_data() {
			$get_listing = get_option( 'post_type_options' );

			$listing_data = isset( $_POST['listing_data'] ) ? sanitize_text_field( wp_unslash( $_POST['listing_data'] ) ) : '';
			$listing_data = json_decode( $listing_data, true );

			if ( false === $get_listing ) {
				add_option( 'post_type_options', $listing_data, '', 'yes' );
			} else {
				update_option( 'post_type_options', $listing_data );
			}

			return $this->tpae_set_response( true, 'Data Updated.', 'Data Updated Successfully.' );
		}

		/**
		 * Get Plugin Previous Versions
		 *
		 * @since 6.0.0
		 */
		public function tpae_prev_version() {

			$versions_list = get_transient( 'tpae_rollback_version_' . L_THEPLUS_VERSION );
			if ( $versions_list === false ) {

				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

				$plugin_info = plugins_api(
					'plugin_information',
					array(
						'slug' => 'the-plus-addons-for-elementor-page-builder',
					)
				);

				if ( empty( $plugin_info->versions ) || ! is_array( $plugin_info->versions ) ) {
					return array();
				}

				/*
				 * Sort by version, not by string. krsort() compares the keys as strings, so "6.3.9"
				 * ranked above "6.3.16" and the 25-item cap below then kept the wrong releases:
				 * 6.3.10-6.3.16 were dropped from the list entirely while 6.3.3-6.3.9 took their
				 * place, so those versions could not be rolled back to at all. The dashboard sorts
				 * the list again for display, but it can only order what this method chose to send.
				 */
				uksort( $plugin_info->versions, 'version_compare' );
				$plugin_info->versions = array_reverse( $plugin_info->versions, true );

				$versions_list = array();

				$index = 0;
				foreach ( $plugin_info->versions as $version => $download_link ) {
					if ( 25 <= $index ) {
						break;
					}

					$lowercase_version      = strtolower( $version );
					$check_rollback_version = ! preg_match( '/(beta|rc|trunk|dev)/i', $lowercase_version );

					$check_rollback_version = apply_filters( 'tpae_check_rollback_version', $check_rollback_version, $lowercase_version );

					if ( ! $check_rollback_version ) {
						continue;
					}

					if ( version_compare( $version, L_THEPLUS_VERSION, '>=' ) ) {
						continue;
					}

					++$index;
					$versions_list[] = $version;
				}

				set_transient( 'tpae_rollback_version_' . L_THEPLUS_VERSION, $versions_list, WEEK_IN_SECONDS );
			}

			return $versions_list;
		}

		/**
		 * Rollback to Previous Versions
		 *
		 * @since 6.0.0
		 */
		public function tpae_rollback_check() {

			if ( ! current_user_can( 'update_plugins' ) ) {
				return $this->tpae_set_response( false, 'invalid_permission', 'You do not have permission to update plugins.' );
			}

			$current_ver = isset( $_POST['version'] ) ? sanitize_text_field( wp_unslash( $_POST['version'] ) ) : '';

			$rv = $this->tpae_prev_version();
			if ( empty( $current_ver ) || ! in_array( $current_ver, $rv ) ) {
				return $this->tpae_set_response( false, 'Invalid nonce.', 'Try selecting another version.' );
			}

			$plugin_slug = basename( L_THEPLUS_PNAME, '.php' );

			$this_version    = $current_ver;
			$this_pluginname = L_THEPLUS_PBNAME;
			$this_pluginslug = $plugin_slug;
			$this_plugin_url = sprintf( 'https://downloads.wordpress.org/plugin/%s.%s.zip', $this_pluginslug, $this_version );

			$plugin_info = array(
				'plugin_name' => $this_pluginname,
				'plugin_slug' => $this_pluginslug,
				'version'     => $this_version,
				'package_url' => $this_plugin_url,
			);

			$update_plugins_data = get_site_transient( 'update_plugins' );

			if ( ! is_object( $update_plugins_data ) ) {
				$update_plugins_data = new \stdClass();
			}

			$plugin_info              = new \stdClass();
			$plugin_info->new_version = $this_version;

			$plugin_info->slug    = $this_pluginslug;
			$plugin_info->package = $this_plugin_url;
			$plugin_info->url     = 'https://theplusaddons.com/';

			$update_plugins_data->response[ $this_pluginname ] = $plugin_info;

			set_site_transient( 'update_plugins', $update_plugins_data );

			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

			$logo_url = L_THEPLUS_URL . 'assets/images/tpae-logo-small.png';

			$args = array(
				'url'    => 'update.php?action=upgrade-plugin&plugin=' . rawurlencode( $this_pluginname ),
				'plugin' => $this_pluginname,
				'nonce'  => 'upgrade-plugin_' . $this_pluginname,
				'title'  => '<img src="' . esc_url( $logo_url ) . '" alt="theplus-logo"><div class="theplus-rb-subtitle">' . esc_html__( 'Rollback to Previous Version', 'tpebl' ) . '</div>',
			);

			/*
			 * A silent skin, because this is an AJAX endpoint and not update.php.
			 *
			 * Plugin_Upgrader_Skin prints a full admin page - heading, progress lines, a "Go to
			 * Plugins page" link. That HTML was emitted straight into this response and
			 * wp_send_json() then appended the JSON after it, so the body was markup followed by an
			 * object and no JSON parser could read it. The dashboard's `data.success` came back
			 * undefined on every rollback, which sent a SUCCESSFUL rollback down the error branch
			 * and told a user whose rollback had actually worked that it had failed.
			 *
			 * Output buffering does NOT fix this. That skin reports progress through show_message(),
			 * which calls wp_ob_end_flush_all() - a loop over ob_get_level() that flushes and closes
			 * every buffer on the stack, including any this method opened. The output has to not be
			 * produced in the first place.
			 *
			 * Automatic_Upgrader_Skin is core's own answer for non-interactive runs: it collects
			 * feedback into an array instead of echoing it, and its credentials prompt is
			 * self-buffered. class-wp-upgrader.php requires it, so it is already loaded above.
			 */
			$upgrader_skin   = new \Automatic_Upgrader_Skin( $args );
			$upgrader_plugin = new \Plugin_Upgrader( $upgrader_skin );

			// Second line of defence: the skin above stays quiet, but a third-party hook on any of
			// the upgrader_* actions can still echo. Unwound back to the level we came in at.
			$ob_level_before = ob_get_level();

			ob_start();
			$upgrade_result = $upgrader_plugin->upgrade( $this_pluginname );
			while ( ob_get_level() > $ob_level_before ) {
				ob_end_clean();
			}

			/*
			 * Report a failed rollback as a failure.
			 *
			 * The result of upgrade() was discarded and this method returned success unconditionally,
			 * so a rollback that never happened still answered "Roll Back Successfully" and the
			 * dashboard reloaded onto the same version it started on. The dashboard's error handling
			 * only ever saw success, so it could not surface any of this.
			 *
			 * upgrade() returns false or a WP_Error when it fails, and null when the skin bailed out
			 * before running, so anything other than true is treated as a failure here.
			 */
			if ( true !== $upgrade_result ) {
				$upgrade_error = '';

				if ( is_wp_error( $upgrade_result ) ) {
					$upgrade_error = $upgrade_result->get_error_message();
				} else {
					// upgrade() answers a bare false when the skin captured the reason instead of
					// returning it, so the last thing the skin recorded is the actual explanation.
					$skin_messages = $upgrader_skin->get_upgrade_messages();

					if ( ! empty( $skin_messages ) ) {
						$upgrade_error = (string) end( $skin_messages );
					}
				}

				if ( '' === $upgrade_error ) {
					$upgrade_error = __( 'The rollback could not be completed. Please try again.', 'tpebl' );
				}

				return $this->tpae_set_response( false, 'rollback_failed', $upgrade_error );
			}

			// Buffered for the same reason: activation fires the rolled-back plugin's activation
			// hooks, and any notice one of those echoes would corrupt the JSON just as badly.
			ob_start();
			$activation_result = activate_plugin( $this_pluginname );
			while ( ob_get_level() > $ob_level_before ) {
				ob_end_clean();
			}

			// The files were replaced but the plugin did not come back up; say so rather than
			// reporting success and leaving the user on a dashboard served by a deactivated plugin.
			if ( is_wp_error( $activation_result ) ) {
				return $this->tpae_set_response( false, 'rollback_activation_failed', $activation_result->get_error_message() );
			}

			return $this->tpae_set_response( true, 'Roll Back Successfully', 'Roll Back Successfully Done.' );
			// wp_redirect( esc_url( admin_url( 'admin.php?page=theplus_welcome_page' ) ) );
		}

		/**
		 * Performance Manage
		 *
		 * @since 6.0.0
		 */
		public function tpae_performance_manage() {
			$plus_cache_option = isset( $_POST['performance_option'] ) ? sanitize_text_field( wp_unslash( $_POST['performance_option'] ) ) : '';
			$plus_cache_option = json_decode( stripslashes( sanitize_text_field( wp_unslash( $plus_cache_option ) ) ), true );

			if ( ! empty( $plus_cache_option ) ) {
				update_option( 'theplus_performance', $plus_cache_option, 'yes' );
			}

			return $this->tpae_set_response( true, 'Successfully.', 'Change Successfully.' );
		}

		/**
		 * Plugin Install
		 *
		 * @since 6.0.0
		 */
		public function tpae_plugin_install() {

			if ( ! current_user_can( 'install_plugins' ) ) {
				$response = $this->tpae_set_response( false, 'Invalid nonce.', 'The security check failed. Please refresh the page and try again.' );
				return $response;
			}

			$slug = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
			$name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
			if ( ! $slug ) {
				return $this->tpae_set_response( false, 'Slug Not Found.', 'Something went wrong.' );
			}

			$installed_plugins = get_plugins();

			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
			include_once ABSPATH . 'wp-admin/includes/class-plugin-upgrader.php';

			$result = array();

			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$plugin_info = plugins_api(
				'plugin_information',
				array(
					'slug'   => $name,
					'fields' => array(
						'version' => false,
					),
				)
			);

			if ( is_wp_error( $plugin_info ) || ! $plugin_info ) {
				wp_send_json_error( array( 'content' => __( 'Failed to retrieve plugin information.', 'tpebl' ) ) );
			}

			$skin     = new \Automatic_Upgrader_Skin();
			$upgrader = new \Plugin_Upgrader( $skin );

			if ( 'nexter-extension' === $slug ) {
				$slug = 'nexter-extension/nexter-extension.php';
			}

			$plugin_basename = $slug;

			if ( ! isset( $installed_plugins[ $plugin_basename ] ) && empty( $installed_plugins[ $plugin_basename ] ) ) {

				$installed         = $upgrader->install( $plugin_info->download_link );
				$activation_result = activate_plugin( $plugin_basename );

				if ( 'wdesignkit' === $name ) {
					$this->tpae_wdkit_hook();
				}

				$success = null === $activation_result;
				$result  = $this->tpae_set_response( $success, 'Successfully Install', 'Successfully Install');

			} elseif ( isset( $installed_plugins[ $plugin_basename ] ) ) {

				$activation_result = activate_plugin( $plugin_basename );

				if ( 'wdesignkit' === $name ) {
					$this->tpae_wdkit_hook();
				}

				$success = null === $activation_result;
				$result  = $this->tpae_set_response( $success, 'Successfully Activate', 'Successfully Activate');

			}

			return $result;
		}

		/**
		 * Theme Install
		 *
		 * @since 6.0.0
		 */
		public function tpae_theme_install() {

			if ( ! current_user_can( 'install_themes' ) ) {
				$response = $this->tpae_set_response( false, 'Invalid nonce.', 'The security check failed. Please refresh the page and try again.' );
				return $response;
			}

			/**
			 * `sanitize_text_field()` preserves `/`, `\` and `..`, so it is the wrong sanitiser for a
			 * value that ends up in a filesystem path. `sanitize_key()` constrains the value to the
			 * lowercase alphanumerics, dashes and underscores a wordpress.org theme slug can contain.
			 *
			 * @since 6.5.0
			 */
			$name = isset( $_POST['name'] ) ? sanitize_key( wp_unslash( $_POST['name'] ) ) : '';

			if ( '' === $name ) {
				return $this->tpae_set_response( false, 'Invalid slug.', 'A valid theme slug is required.' );
			}

			if ( ! function_exists( 'themes_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/theme.php';
			}

			$theme_info = themes_api(
				'theme_information',
				array(
					'slug'   => $name,
					'fields' => array(
						'download_link' => true,
					),
				)
			);

			if ( is_wp_error( $theme_info ) || empty( $theme_info->download_link ) ) {
				return $this->tpae_set_response( false, 'oops', 'oops' );
			}

			include_once ABSPATH . 'wp-admin/includes/file.php';
			include_once ABSPATH . 'wp-admin/includes/misc.php';
			include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			include_once ABSPATH . 'wp-admin/includes/class-automatic-upgrader-skin.php';
			include_once ABSPATH . 'wp-admin/includes/class-theme-upgrader.php';

			/**
			 * Core's Theme_Upgrader replaces the hand-rolled download plus ZipArchive::extractTo()
			 * this handler used to perform. It downloads to a temp directory outside the web root
			 * rather than staging the archive inside wp-content/themes/ where it was briefly
			 * fetchable, checks the transfer for errors instead of dereferencing $theme['body']
			 * blind, and extracts through unzip_file(), which runs validate_file() on every archive
			 * entry and so rejects the `../` names that made the old extractTo() a Zip Slip.
			 *
			 * @since 6.5.0
			 */
			$upgrader  = new \Theme_Upgrader( new \Automatic_Upgrader_Skin() );
			$installed = $upgrader->install( $theme_info->download_link );

			if ( is_wp_error( $installed ) || ! $installed ) {
				return $this->tpae_set_response( false, 'oops', 'The theme could not be installed.' );
			}

			return $this->tpae_set_response( true, "Success $name", "Success $name" );
		}

		/**
		 * API call and get Response
		 *
		 * @since 6.0.0
		 */
		public function tpae_api_call() {

			$method  = isset( $_POST['method'] ) ? sanitize_text_field( wp_unslash( $_POST['method'] ) ) : 'POST';
			$api_url = isset( $_POST['api_url'] ) ? esc_url_raw( wp_unslash( $_POST['api_url'] ) ) : '';
			$body    = isset( $_POST['url_body'] ) ? json_decode( wp_unslash( $_POST['url_body'] ) ) : array();

			$final = array( 'HTTP_CODE' => 0 );

			if ( empty( $api_url ) || ! $this->tpae_is_safe_outbound_url( $api_url ) ) {
				$final['error'] = 'invalid_url';
				return $final;
			}

			if ( ! in_array( $method, array( 'GET', 'POST' ), true ) ) {
				$final['error'] = 'invalid_method';
				return $final;
			}

			/**
			 * Optional response cache, opt-in per request via `cache_ttl` (seconds), so existing
			 * callers of this proxy keep their live behaviour. The key covers url + method + body, so
			 * every filter combination caches separately. `cache_bust=1` forces a refetch.
			 *
			 * Deliberately placed AFTER the SSRF and method guards above: keying a transient on an
			 * unvalidated $api_url would let a rejected URL be served from cache on a later request,
			 * which is the guard being bypassed by a slower route.
			 *
			 * @since 6.4.18
			 */
			$cache_ttl = isset( $_POST['cache_ttl'] ) ? absint( wp_unslash( $_POST['cache_ttl'] ) ) : 0;
			$cache_ttl = min( $cache_ttl, DAY_IN_SECONDS );
			$cache_key = '';

			if ( $cache_ttl > 0 ) {
				$cache_key  = 'tpae_api_' . md5( $api_url . '|' . $method . '|' . wp_json_encode( $body ) );
				$cache_bust = isset( $_POST['cache_bust'] ) && '1' === sanitize_text_field( wp_unslash( $_POST['cache_bust'] ) );

				if ( $cache_bust ) {
					delete_transient( $cache_key );
				} else {
					$cached = get_transient( $cache_key );

					if ( false !== $cached && is_array( $cached ) ) {
						$cached['TPAE_CACHED'] = true;

						return $cached;
					}
				}
			}

			$args = array(
				'method'  => $method,
				'timeout' => 15,
				'headers' => array(
					'Content-Type' => 'application/json',
				),
			);

			if ( ! empty( $body ) ) {
				$args['body'] = wp_json_encode( $body );
			}

			/**
			 * `wp_remote_*` follows up to 5 redirects and never re-checks the destination, so the
			 * guard above only ever sees the first hop — a 302 to a private or link-local address
			 * would be fetched and its body handed straight back to the caller.
			 *
			 * `wp_safe_remote_*` sets `reject_unsafe_urls`, which re-runs `wp_http_validate_url()`
			 * on every hop of the redirect chain. Redirects are still permitted so legitimate
			 * http→https and trailing-slash hops keep working; each one is now validated.
			 *
			 * @since 6.5.0
			 */
			$args['reject_unsafe_urls'] = true;

			$response = ( 'POST' === $method )
				? wp_safe_remote_post( $api_url, $args )
				: wp_safe_remote_get( $api_url, $args );

			if ( is_wp_error( $response ) ) {
				$final['error'] = 'request_failed';
				return $final;
			}

			$status_code = wp_remote_retrieve_response_code( $response );
			$getdataone  = wp_remote_retrieve_body( $response );
			$statuscode  = array( 'HTTP_CODE' => $status_code );

			$response = json_decode( $getdataone, true );

			if ( is_array( $statuscode ) && is_array( $response ) ) {
				$final = array_merge( $statuscode, $response );
			}

			// Only cache successful responses, so a transient can't pin an error.
			if ( $cache_ttl > 0 && ! empty( $cache_key ) && 200 === (int) $status_code && ! empty( $response ) ) {
				$this->tpae_purge_expired_api_transients();
				set_transient( $cache_key, $final, $cache_ttl );
			}

			return $final;
		}

		/**
		 * Delete expired `tpae_api_*` response transients.
		 *
		 * WordPress only clears an expired transient when it is next requested, so keys never asked
		 * for again would linger in wp_options. Runs only on a cache miss, just before a new entry is
		 * written.
		 *
		 * @since 6.4.18
		 */
		public function tpae_purge_expired_api_transients() {
			global $wpdb;

			$expired = $wpdb->get_col(
				$wpdb->prepare(
					"SELECT option_name FROM {$wpdb->options}
					WHERE option_name LIKE %s AND option_value < %d LIMIT 100",
					$wpdb->esc_like( '_transient_timeout_tpae_api_' ) . '%',
					time()
				)
			);

			if ( empty( $expired ) ) {
				return;
			}

			foreach ( $expired as $timeout_name ) {
				delete_transient( substr( $timeout_name, strlen( '_transient_timeout_' ) ) );
			}
		}

		/**
		 * Validate outbound URL against SSRF.
		 *
		 * @since 6.4.15
		 *
		 * @param string $url URL to validate.
		 * @return bool
		 */
		private function tpae_is_safe_outbound_url( $url ) {

			$parts = wp_parse_url( $url );

			if ( empty( $parts['scheme'] ) || empty( $parts['host'] ) ) {
				return false;
			}

			if ( ! in_array( strtolower( $parts['scheme'] ), array( 'http', 'https' ), true ) ) {
				return false;
			}

			if ( ! empty( $parts['user'] ) || ! empty( $parts['pass'] ) ) {
				return false;
			}

			$host = strtolower( $parts['host'] );

			/**
			 * Reject local hostnames before any resolution, so a resolver that is misconfigured
			 * or poisoned cannot answer for them.
			 */
			$blocked_hosts = array( 'localhost', 'localhost.localdomain', '127.0.0.1', '0.0.0.0', '::1' );

			if ( in_array( $host, $blocked_hosts, true ) ) {
				return false;
			}

			/**
			 * Collect every A and AAAA record rather than the single IPv4 answer `gethostbyname()`
			 * returns. A host whose AAAA record points at ::1 or fd00::/8 passed the old check
			 * untouched, because the IPv6 address was never looked at.
			 *
			 * @since 6.5.0
			 */
			$ips = array();

			if ( filter_var( $host, FILTER_VALIDATE_IP ) ) {
				$ips[] = $host;
			} else {
				$records = @dns_get_record( $host, DNS_A + DNS_AAAA );

				if ( is_array( $records ) ) {
					foreach ( $records as $record ) {
						if ( ! empty( $record['ip'] ) ) {
							$ips[] = $record['ip'];
						} elseif ( ! empty( $record['ipv6'] ) ) {
							$ips[] = $record['ipv6'];
						}
					}
				}

				if ( empty( $ips ) ) {
					$resolved = gethostbyname( $host );

					if ( $resolved && $resolved !== $host ) {
						$ips[] = $resolved;
					}
				}
			}

			/**
			 * Fail closed. `gethostbyname()` hands back the hostname unchanged when resolution
			 * fails, which made `filter_var()` return false and skipped the private-range test
			 * entirely — an unresolvable host used to be treated as a safe one.
			 */
			if ( empty( $ips ) ) {
				return false;
			}

			foreach ( $ips as $ip ) {
				if ( ! filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
					return false;
				}
			}

			return (bool) wp_http_validate_url( $url );
		}

		/**
		 * Allowlist of TPAE-owned option/transient keys this dashboard may
		 * read or delete.
		 *
		 * @since 6.5.7
		 * @param string $key Candidate option or transient key.
		 * @return bool
		 */
		private function tpae_is_allowed_storage_key( $key ) {

			if ( ! is_string( $key ) || '' === $key ) {
				return false;
			}

			$exact = array(
				'theplus_options',
				'theplus_api_connection_data',
				'theplus_styling_data',
				'theplus_performance',
				'theplus_widgets_settings',
				'theplus_white_label',
				'post_type_options',
				'tp_dashboard_overview',
				'tpae_onbording_end',
				'tpae_data_allow',
				'tpae_menu_notification',
				'tpae_whats_new_notification',
				'tpae_onboarding_time',
				'tpae_onboarding_version',
			);

			if ( in_array( $key, $exact, true ) ) {
				return true;
			}

			$prefixes = array( 'tpae_', 'theplus_', 'tp_dashboard_', 'tpae_rollback_version_' );
			foreach ( $prefixes as $p ) {
				if ( 0 === strpos( $key, $p ) ) {
					return true;
				}
			}

			return false;
		}

		/**
		 * Manage Databash Transient
		 *
		 * @since 6.0.0
		 */
		public function tpae_transient_manage() {

			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			$key       = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

			if ( ! $this->tpae_is_allowed_storage_key( $key ) ) {
				return $this->tpae_set_response( false, 'Invalid key.', 'Key not permitted.' );
			}

			if ( 'get' === $operation ) {
				$data = get_transient( $key );

				if ( false === $data ) {
					return $this->tpae_set_response( false, 'oops.', 'oops.' );
				}

				return $data;
			} elseif ( 'delete' === $operation ) {
				/**
				 * Transients live in the prefixed `_transient_{$key}` row. Calling `delete_option()`
				 * here removed the unprefixed row instead — for keys such as `theplus_options` or
				 * `theplus_white_label` that is the live settings row, so clearing a transient wiped
				 * plugin configuration and left the transient in place.
				 *
				 * @since 6.5.0
				 */
				delete_transient( $key );

				return $this->tpae_set_response( true, 'Successfully.', 'Successfully.' );
			}

			return $this->tpae_set_response( false, 'Invalid operation.', 'Unsupported operation.' );
		}

		/**
		 * Manage Wp Option Table
		 *
		 * @since 6.0.0
		 */
		public function tpae_wp_option_manage() {
			$operation = isset( $_POST['operation'] ) ? sanitize_text_field( wp_unslash( $_POST['operation'] ) ) : '';
			$key       = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';

			if ( ! $this->tpae_is_allowed_storage_key( $key ) ) {
				return $this->tpae_set_response( false, 'Invalid key.', 'Key not permitted.' );
			}

			if ( 'get' === $operation ) {
				/**
				 * This handler reads the options table. It previously called `get_transient()`,
				 * a copy-paste from `tpae_transient_manage()`, so every read of a plain option
				 * returned false and the dashboard reported the value as missing.
				 *
				 * `null` is the sentinel for "no such option" — `false` is a legitimate stored
				 * value and must not be reported as absent.
				 *
				 * @since 6.5.0
				 */
				$data = get_option( $key, null );

				if ( null === $data ) {
					return $this->tpae_set_response( false, 'oops.', 'oops.' );
				}

				return $data;
			} elseif ( 'delete' === $operation ) {
				delete_option( $key );

				return $this->tpae_set_response( true, 'Successfully.', 'Successfully.' );
			}

			return $this->tpae_set_response( false, 'Invalid operation.', 'Unsupported operation.' );
		}

		/**
		 * Plugin Install
		 *
		 * @since 6.0.0
		 */
		public function tpae_backend_catch_remove() {
			l_theplus_library()->remove_backend_dir_files();
		}

		/**
		 * tpae_set_custom_css_js
		 *
		 * @since 6.0.0
		 */
		public function tpae_widgets_setting_data() {

			if ( ! check_ajax_referer( 'tpae-db-nonce', 'nonce', false ) ) {

				$response = $this->tpae_set_response( false, 'Invalid nonce.', 'The security check failed. Please refresh the page and try again.' );

				wp_send_json( $response );
				wp_die();
			}

			$settings_json = isset( $_POST['tp_widgets_setting'] ) ? wp_unslash( $_POST['tp_widgets_setting'] ) : '';

			if ( ! empty( $settings_json ) ) {
				$settings = json_decode( $settings_json, true );
				if ( ! is_array( $settings ) ) {
					return $this->tpae_set_response( false, 'Invalid data format.', 'Data is not in correct format.' );
				}

				update_option( 'theplus_widgets_settings', $settings );

				return $this->tpae_set_response( true, 'Data Updated.', 'Data Updated Successfully.' );
			}

			return $this->tpae_set_response( false, 'No data found.', 'Please send valid data.' );
		}

		/**
		 * Get User data
		 */
		public function tpae_user_meta_data() {

			/*
			 * The `tpae_data_allow` write that stood here was removed with the legacy telemetry.
			 *
			 * Its only reader was includes/user-experience/class-tp-deactivate-feedback.php, which is
			 * gone, so the option was pure write-only state: set on every call, consulted by nothing.
			 * It was never consent either — it was set unconditionally, so a user who had answered no
			 * question at all still got a `true`. Sharing consent now lives in
			 * posimyth_tpae_share_analytics, written only by the SDK's consent notice.
			 *
			 * The legacy onboarding telemetry was removed.
			 *
			 * This assembled server software, memory limit, max execution time, PHP and WP versions, the
			 * active theme, the full active-plugin list via get_plugins(), the widget list, the site URL,
			 * the site language and the raw admin_email, and POSTed the lot to
			 * api.posimyth.com/wp-json/tpae/v2/tpae_store_user_data — with no opt-in surface, no
			 * white-label suppression, and the administrator's email address, which the consent copy
			 * explicitly promises is never sent. Reporting is the shared SDK's job now
			 * (Posimyth_Tracker_TPAE, booted from theplus_elementor_addon.php) and is gated on the
			 * suite-wide opt-in.
			 *
			 * Consent is deliberately NOT written here. Nothing in this handler represents the user
			 * agreeing to anything — tpae_data_allow above is set unconditionally — so treating it as
			 * consent would switch sharing on for people who were never asked, which is the pattern being
			 * removed. The SDK's consent notice asks properly.
			 *
			 * The success path previously returned no JSON at all (only the error path answered), leaving
			 * the caller with nothing to resolve on. It answers unconditionally now.
			 */
			wp_send_json( array( 'onBoarding' => true ) );
		}

		/**
		 * Capability required to answer the analytics sharing question.
		 *
		 * On multisite the consent is ONE answer for the whole network — the SDK stores it as a site
		 * option and Posimyth_Consent_Notice gates its own notice on manage_network_options. This
		 * screen has to require the same thing, or a subsite administrator could decide for every
		 * other blog on the network through the dashboard even though the notice refuses to let them.
		 *
		 * @since 6.5.8
		 * @return string
		 */
		private function tpae_analytics_capability() {
			return is_multisite() ? 'manage_network_options' : 'manage_options';
		}

		/**
		 * Whether the analytics feature exists on this install at all.
		 *
		 * A white-labelled install never boots the tracker (see tpae_posimyth_is_white_labelled() in
		 * the main plugin file), so the dashboard must not offer a switch that cannot do anything.
		 *
		 * @since 6.5.8
		 * @return bool
		 */
		private function tpae_analytics_available() {
			if ( ! function_exists( 'tpae_posimyth_is_white_labelled' ) ) {
				return false;
			}

			return ! tpae_posimyth_is_white_labelled();
		}

		/**
		 * Current analytics state, for the dashboard and the onboarding step to render from.
		 *
		 * @since 6.5.8
		 * @return array
		 */
		private function tpae_analytics_state() {
			return array(
				// False on a rebranded install: hide the control entirely rather than showing a dead one.
				'available'  => $this->tpae_analytics_available(),
				'enabled'    => (bool) get_site_option( 'posimyth_tpae_share_analytics', false ),
				// False for a subsite admin on multisite — show the state, but read-only.
				'can_manage' => current_user_can( $this->tpae_analytics_capability() ),
			);
		}

		/**
		 * Records an explicit yes/no to analytics sharing.
		 *
		 * Kept separate from tpae_analytics_consent() so the onboarding step can call the same code
		 * when that step is built — the two answers must be stored identically or they will drift.
		 *
		 * Three things have to happen together, and each fails silently on its own:
		 *
		 * 1. SITE options, not per-blog options. Posimyth_Tracker_Base::has_consent() reads with
		 *    get_site_option(), so update_option() would write somewhere the SDK never looks — on
		 *    multisite the switch would appear to work while nothing was ever sent. On single site
		 *    get_site_option() falls back to the plain option, so this is equivalent there.
		 *
		 * 2. The suite-wide "answered" flag is set for BOTH answers. Posimyth_Consent_Notice::
		 *    should_show() treats "opt-in empty and never dismissed" as unanswered, so switching
		 *    sharing OFF here without this would bring the admin notice straight back to ask again —
		 *    immediately after the user deliberately said no.
		 *
		 * 3. Turning it on sends something now. Without a ping the hub does not learn about this
		 *    install until the weekly cron happens to fire.
		 *
		 * @since 6.5.8
		 * @param bool $enable Whether sharing is being switched on.
		 * @return void
		 */
		private function tpae_store_analytics_consent( $enable ) {

			$enable = (bool) $enable;

			update_site_option( 'posimyth_tpae_share_analytics', $enable ? 1 : 0 );
			update_site_option( 'posi_consent_dismissed_tpae_suite', 1 );

			if ( ! $enable || ! class_exists( 'Posimyth_Tracker_TPAE' ) ) {
				return;
			}

			/*
			 * report_activation() sends `activate` at most once per active period, so a user who
			 * switches sharing off and later back on would otherwise send nothing at all and stay
			 * invisible until the weekly heartbeat. Send the activation on the first opt-in, and a
			 * heartbeat on a re-opt-in — that reports current state now without inflating the hub's
			 * activation count, which is exactly what the once-per-period guard exists to protect.
			 */
			$already_reported = get_option( 'posimyth_tpae_activate_reported' );

			Posimyth_Tracker_TPAE::send_first_ping();

			if ( $already_reported ) {
				Posimyth_Tracker_TPAE::do_request( 'heartbeat' );
			}
		}

		/**
		 * Read or set the analytics sharing consent from the dashboard.
		 *
		 * Nonce and the logged-in manage_options check are already done by tpae_dashboard_ajax_call();
		 * this adds the network-scope capability on top — see tpae_analytics_capability().
		 *
		 * Deliberately its own endpoint rather than a key in tpae_wp_option_manage()'s allowlist.
		 * Consent has to be written as a site option, has to set the suite-wide answered flag and has
		 * to ping, none of which a generic option writer does — and a consent flag should not be
		 * reachable through a general-purpose read/write API in the first place.
		 *
		 * @since 6.5.8
		 * @return array
		 */
		public function tpae_analytics_consent() {

			if ( ! $this->tpae_analytics_available() ) {
				return $this->tpae_set_response( false, 'Unavailable.', 'Data sharing is not available on this installation.' );
			}

			if ( ! current_user_can( $this->tpae_analytics_capability() ) ) {
				return $this->tpae_set_response( false, 'Invalid Permission.', 'You do not have permission to change this setting.' );
			}

			$operation = isset( $_POST['operation'] ) ? strtolower( sanitize_text_field( wp_unslash( $_POST['operation'] ) ) ) : 'get';

			if ( 'set' === $operation ) {

				if ( ! isset( $_POST['share_analytics'] ) ) {
					return $this->tpae_set_response( false, 'No data found.', 'Please send valid data.' );
				}

				// Accept the truthy spellings a JS client may send; anything else — including the
				// strings "false" and "0", which are both truthy in PHP — counts as off.
				$raw = strtolower( sanitize_text_field( wp_unslash( $_POST['share_analytics'] ) ) );

				$this->tpae_store_analytics_consent( in_array( $raw, array( '1', 'true', 'on', 'yes' ), true ) );
			}

			/*
			 * Returned directly rather than through tpae_set_response(), which accepts a $data
			 * argument and then drops it — the state has to reach the client so the control can
			 * render in the right position.
			 */
			$response         = $this->tpae_set_response( true, 'Data Found.', 'Data Found Successfully.' );
			$response['data'] = $this->tpae_analytics_state();

			return $response;
		}

		/**
		 * Onboarding Setup
		 *
		 * @since 2.0
		 */
		public function tpae_onboarding_setup() {

			$onboarding = get_option( 'tpae_onbording_end' );

			if ( ! $onboarding ) {
				update_option( 'tpae_onbording_end', 'hide' );
			}

			$my_array = array(
				'elementor_builder'  => true,
				'elementor_template' => true,
			);

			$builder = array( 'nexter-blocks' );
			do_action( 'wdkit_active_settings', $my_array, $builder );

			if ( $onboarding ) {
				$response = $this->tpae_set_response( true, 'Onboarding Setup', 'Onboarding Setup');
			} else {
				$response = $this->tpae_set_response( false, 'Onboarding Setup Failed', 'Onboarding Setup Failed');
			}

			update_option( 'tpae_onboarding_time', current_time( 'mysql' ) );
			update_option( 'tpae_onboarding_version', L_THEPLUS_VERSION );
			update_option( 'tpae_menu_notification', TPAE_MENU_NOTIFICETIONS );

			return $response;
		}

		/**
		 * Whats New Close
		 *
		 * @since 2.0
		 */
		public function tpae_whats_new_close() {

			$updated = update_option( 'tpae_whats_new_notification', TPAE_WHATS_NEW_NOTIFICETIONS );
			update_option( 'tpae_menu_notification', TPAE_MENU_NOTIFICETIONS );

			if ( $updated ) {
				$response = $this->tpae_set_response( true, 'Whats New Closed', 'Whats New notification status updated successfully.');
			} else {
				$response = $this->tpae_set_response( false, 'Onboarding Setup Failed', 'Failed to update Whats New notification option.');
			}

			wp_send_json( $response );
		}

		/**
		 * Tpae Side Wdkit Hook Call after install
		 *
		 * @since 6.0.0
		 */
		public function tpae_wdkit_hook() {
			$my_array = array(
				'elementor_builder'  => true,
				'elementor_template' => true,
			);

			$builder = array( 'nexter-blocks' );
			do_action( 'wdkit_active_settings', $my_array, $builder );
		}

		/**
		 * Set the response data.
		 *
		 * @since 6.0.0
		 *
		 * @param bool   $success     Indicates whether the operation was successful. Default is false.
		 * @param string $message     The main message to include in the response. Default is an empty string.
		 * @param string $description A more detailed description of the message or error. Default is an empty string.
		 * @param mixed  $data        Optional additional data to include in the response. Default is an empty string.
		 */
		public function tpae_set_response( $success = false, $message = '', $description = '', $data = '' ) {

			$response = array(
				'success'     => $success,
				'message'     => esc_html( $message ),
				'description' => esc_html( $description ),
			);

			return $response;
		}
	}

	Tpae_Dashboard_Ajax::get_instance();
}
