<?php
/**
 * Exit if accessed directly.
 *
 * @link       https://posimyth.com/
 * @since      6.5.0
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\RemovedWidgets;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Removed_Widgets_Notice' ) ) {

	/**
	 * This class notifies users which widgets were removed in 6.5.0 and which
	 * are now deprecated and scheduled for removal in an upcoming release.
	 *
	 * @since 6.5.0
	 */
	class Tp_Removed_Widgets_Notice {

		/**
		 * Instance
		 *
		 * @since 6.5.0
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.5.0
		 *
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
		 * @since 6.5.0
		 */
		public function __construct() {
			add_action( 'wp_ajax_theplus_removed_widgets_notice_dismiss', array( $this, 'theplus_removed_widgets_notice_dismiss' ) );

			if ( ! get_option( 'tpae_removed_widgets_notice' ) ) {
				add_action( 'admin_notices', array( $this, 'theplus_removed_widgets_notice' ) );
			}
		}

		/**
		 * Removed & Deprecated Widgets Notice
		 *
		 * @since 6.5.0
		 */
		public function theplus_removed_widgets_notice() {

			$nonce  = wp_create_nonce( 'tpae-removed-widgets' );
			$screen = get_current_screen();

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' );

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			/**
			 * Detect Pro by its constant rather than by folder name. is_plugin_active()
			 * matches on the directory, so a renamed Pro folder reports "inactive" and the
			 * Pro-only entries below would be hidden from exactly the sites that need them.
			 * THEPLUS_VERSION is defined unconditionally by Pro before any of its gates.
			 */
			$is_pro_on = defined( 'THEPLUS_VERSION' );

			/** Widgets removed in 6.5.0 (Design Tool is Pro-only). */
			$removed_pro_item = '';
			if ( $is_pro_on ) {
				$removed_pro_item = '<li>' . esc_html__( 'Design Tool', 'tpebl' ) . ' &mdash; ' . esc_html__( 'Use Elementor\'s native styling controls.', 'tpebl' ) . '</li>';
			}

			/** Reusable bullet marker for the inline deprecated list. */
			$dep_bullet = '<span style="display: inline-block; width: 5px; height: 5px; border-radius: 50%; background: #1e1e1e; flex: 0 0 auto;"></span>';

			/** Widgets deprecated in 6.5.0 (Advanced Separators is Pro-only). */
			$deprecated_pro_item = '';
			if ( $is_pro_on ) {
				$deprecated_pro_item = '<li style="display: flex; align-items: center; gap: 8px;">' . $dep_bullet . esc_html__( 'Advanced Separators', 'tpebl' ) . '</li>';
			}

			echo '<div class="notice notice-warning is-dismissible tpae-notice-show tpae-removed-widgets" style="border-left-color: #6660EF;">
					<div class="tp-nexter-werp" style="display: flex; column-gap: 12px; align-items: flex-start; padding: 15px 10px; position: relative; margin-left: 0;">

						<div class="tp-notice-wrap" style="display: flex; padding-top: 14px;">
							<img style="max-width: 28px; max-height: 28px; border-radius: 5px;" src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" alt="' . esc_attr__( 'The Plus Addons for Elementor', 'tpebl' ) . '" />
						</div>
						<div style="margin: 0 10px; color: #000;">
							<h3 style="margin: 10px 0 7px;">' . esc_html__( 'The Plus Addons for Elementor 6.5.0: Widget Changes', 'tpebl' ) . '</h3>

							<p style="color: #1e1e1e; margin-bottom: 6px;">' . esc_html__( 'The following widgets have been REMOVED in this version. If you used them on any page, please switch to the suggested replacement to avoid layout or content issues:', 'tpebl' ) . '</p>

							<ul style="color: #1e1e1e; margin: 6px 0 10px 18px; list-style: disc;">
								<li>' . esc_html__( 'Post Search', 'tpebl' ) . ' &mdash; ' . esc_html__( 'Use the new Search Bar widget instead.', 'tpebl' ) . '</li>
								<li>' . esc_html__( 'Caldera Forms', 'tpebl' ) . ' &mdash; ' . esc_html__( 'The Caldera Forms plugin is no longer maintained.', 'tpebl' ) . '</li>
								' . $removed_pro_item . '
							</ul>

							<p style="color: #1e1e1e; margin-bottom: 6px;">' . esc_html__( 'These widgets are now DEPRECATED and will be permanently removed in an upcoming release. Please migrate away from them soon:', 'tpebl' ) . '</p>

							<ul style="color: #1e1e1e; margin: 6px 0 10px 0; padding: 0; list-style: none; display: flex; flex-wrap: wrap; gap: 6px 26px;">
								<li style="display: flex; align-items: center; gap: 8px;">' . $dep_bullet . esc_html__( 'Syntax Highlighter', 'tpebl' ) . '</li>
								<li style="display: flex; align-items: center; gap: 8px;">' . $dep_bullet . esc_html__( 'Hover Card', 'tpebl' ) . '</li>
								' . $deprecated_pro_item . '
							</ul>

							<div class="tp-tpae-button" style="margin-top: 10px;">
								<button type="button" class="button got-it" style="margin-right: 10px; color: #6660EF; background: #fff; border: #6660EF 1px solid">' . esc_html__( 'Got it, dismiss', 'tpebl' ) . '</button>
							</div>
						</div>
					</div>
				</div>';

			?>
			<script>
				jQuery(document).on('click', '.tpae-removed-widgets .notice-dismiss,.tpae-removed-widgets .button.got-it', function(e) {
					e.preventDefault();

					var $notice = jQuery(this).closest('.tpae-removed-widgets');

					$notice.fadeTo(100, 0, function () {
						$notice.slideUp(100, function () {
							$notice.remove();
						});
					});

					jQuery.ajax({
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_removed_widgets_notice_dismiss',
							security: "<?php echo esc_html( $nonce ); ?>",
						},
						success: function(response) {
							jQuery('.tpae-removed-widgets').hide();
						}
					});
				});
			</script>
			<?php
		}

		/**
		 * Save dismiss state in database
		 *
		 * @since 6.5.0
		 */
		public function theplus_removed_widgets_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( ! isset( $get_security ) || empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-removed-widgets' ) ) {
				die( esc_html__( 'Security checked!', 'tpebl' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			update_option( 'tpae_removed_widgets_notice', true );

			wp_send_json_success();
		}
	}

	Tp_Removed_Widgets_Notice::instance();
}
