<?php
/**
 * "Widget Recipes" feature-announcement admin notice.
 *
 * Points users to the Widget Recipes library on theplusaddons.com and the MCP
 * connection that powers it: copy a ready-made prompt and an MCP client builds
 * the widget in Elementor. Shown once, dismissible for good (stores a boolean
 * option, the same convention the review / community notices use).
 *
 * Styling and dismissal mirror class-tp-whats-new-notice.php: inline scoped
 * markup under .tpae-wr so nothing here can reach the rest of wp-admin, and so
 * the notice never depends on a stylesheet that is gated off the current screen.
 *
 * @link       https://posimyth.com/
 * @since      6.5.1
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\WidgetRecipes;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Widget_Recipes_Notice' ) ) {

	/**
	 * Widget Recipes announcement notice.
	 *
	 * @since 6.5.1
	 */
	class Tp_Widget_Recipes_Notice {

		/**
		 * Instance
		 *
		 * @since 6.5.1
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Option holding whether this notice was dismissed.
		 *
		 * @since 6.5.1
		 * @var string
		 */
		const OPTION = 'tpae_widget_recipes_notice';

		/**
		 * Option storing when both release notices were first seen cleared, as a Unix
		 * timestamp. The notice is held back for DELAY_HOURS after this moment.
		 *
		 * @since 6.5.1
		 * @var string
		 */
		const OPTION_SINCE = 'tpae_widget_recipes_since';

		/**
		 * Hours to hold the notice back after both release notices are dismissed, so
		 * it does not appear the instant the user closes the second one.
		 *
		 * @since 6.5.1
		 * @var int
		 */
		const DELAY_HOURS = 24;

		/**
		 * Instance
		 *
		 * Ensures only one instance of the class is loaded or can be loaded.
		 *
		 * @since 6.5.1
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
		 * @since 6.5.1
		 */
		public function __construct() {
			add_action( 'wp_ajax_theplus_widget_recipes_notice_dismiss', array( $this, 'theplus_widget_recipes_notice_dismiss' ) );

			if ( ! get_option( self::OPTION ) && $this->tp_release_notices_cleared() && $this->tp_delay_passed() ) {
				add_action( 'admin_notices', array( $this, 'theplus_widget_recipes_notice' ) );
			}
		}

		/**
		 * Whether the hold-back window has elapsed since both release notices cleared.
		 *
		 * Stamps the moment both were first seen cleared (Unix time, like the What's
		 * New notice) and holds the notice back until DELAY_HOURS have passed, so it
		 * does not appear the instant the user closes the second release notice. Only
		 * reached once tp_release_notices_cleared() is true, so the timer starts when
		 * both are already gone.
		 *
		 * @since 6.5.1
		 *
		 * @return bool True once the window has passed.
		 */
		private function tp_delay_passed() {
			$since = get_option( self::OPTION_SINCE );

			if ( empty( $since ) || ! is_numeric( $since ) ) {
				update_option( self::OPTION_SINCE, time(), false );

				return false;
			}

			return ( time() - (int) $since ) >= ( self::DELAY_HOURS * HOUR_IN_SECONDS );
		}

		/**
		 * Whether the two higher-priority release notices have been cleared.
		 *
		 * This promo is the lowest-priority notice, so it waits until the release
		 * notices are gone -- the Widget Changes (removed/deprecated widgets) notice
		 * and the What's New notice for the current release -- so the three never
		 * stack. In practice the What's New notice carries a Widget Recipes link and
		 * marks this notice seen when it is dismissed, so this standalone is the
		 * fallback for sites where What's New was already gone.
		 *
		 * @since 6.5.1
		 *
		 * @return bool
		 */
		private function tp_release_notices_cleared() {
			$release = defined( 'L_THEPLUS_VERSION' ) ? preg_replace( '/[-+].*$/', '', L_THEPLUS_VERSION ) : '';

			$whats_new_cleared      = '' !== $release && get_option( 'tpae_whats_new_dismissed' ) === $release;
			$widget_changes_cleared = (bool) get_option( 'tpae_removed_widgets_notice' );

			return $whats_new_cleared && $widget_changes_cleared;
		}

		/**
		 * Render the notice.
		 *
		 * @since 6.5.1
		 */
		public function theplus_widget_recipes_notice() {

			$screen = get_current_screen();

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' );

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			$nonce = wp_create_nonce( 'tpae-widget-recipes' );

			/**
			 * UTM follows the convention the other notices already use: source is
			 * always wpbackend, medium names the surface and campaign names the
			 * subject. See class-tp-whats-new-notice.php.
			 */
			$utm         = 'utm_source=wpbackend&utm_medium=adminpanel&utm_campaign=widgetrecipes';
			$recipes_url = 'https://theplusaddons.com/widget-recipes/?' . $utm;
			$mcp_url     = 'https://theplusaddons.com/mcp-abilities/?' . $utm;
			$logo_url    = defined( 'L_THEPLUS_ASSETS_URL' ) ? L_THEPLUS_ASSETS_URL . 'images/tpae-favicon-white.png' : '';

			$this->tp_styles();
			?>
			<div class="notice tpae-wr is-dismissible" role="region" aria-label="<?php echo esc_attr__( 'Widget Recipes for The Plus Addons', 'tpebl' ); ?>">

				<div class="tpae-wr__hero">
					<?php if ( $logo_url ) { ?>
						<img class="tpae-wr__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="22" height="22">
					<?php } ?>
					<span class="tpae-wr__brand"><?php echo esc_html__( 'The Plus Addons', 'tpebl' ); ?></span>
					<span class="tpae-wr__pill"><?php echo esc_html__( 'Widget Recipes', 'tpebl' ); ?></span>
				</div>

				<div class="tpae-wr__body">
					<h3 class="tpae-wr__title"><?php echo esc_html__( 'Widget Recipes: build any widget from a ready-made prompt', 'tpebl' ); ?></h3>

					<p class="tpae-wr__text">
						<?php echo esc_html__( 'Copy a ready-made prompt and your AI assistant builds the widget directly in Elementor. 1,100 recipes across 110 widgets.', 'tpebl' ); ?>
					</p>

					<span class="tpae-wr__btns">
						<a class="tpae-wr__btn tpae-wr__btn--on" href="<?php echo esc_url( $recipes_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html__( 'Browse Widget Recipes', 'tpebl' ); ?>
						</a>
						<a class="tpae-wr__btn tpae-wr__btn--gh" href="<?php echo esc_url( $mcp_url ); ?>" target="_blank" rel="noopener noreferrer">
							<?php echo esc_html__( 'Set up the MCP connection', 'tpebl' ); ?>
						</a>
					</span>
				</div>
			</div>
			<script>
				jQuery( document ).on( 'click', '.tpae-wr .notice-dismiss', function ( e ) {
					e.preventDefault();

					var $notice = jQuery( this ).closest( '.tpae-wr' );

					$notice.addClass( 'tpae-wr--out' );
					window.setTimeout( function () {
						$notice.slideUp( 180, function () {
							$notice.remove();
						} );
					}, 220 );

					jQuery.ajax( {
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_widget_recipes_notice_dismiss',
							security: "<?php echo esc_js( $nonce ); ?>"
						}
					} );
				} );
			</script>
			<?php
		}

		/**
		 * Scoped styles for the notice.
		 *
		 * Emitted inline and namespaced under .tpae-wr so nothing here can reach the
		 * rest of wp-admin. Design tokens match the sibling What's New notice and the
		 * plugin brand: #6660EF accent, the TPAE brand gradient
		 * linear-gradient(105deg,#3f37c9,#6660EF,#7c3aed), and 8px radius.
		 *
		 * @since 6.5.1
		 */
		public function tp_styles() {
			?>
			<style>
			.tpae-wr{position:relative;padding:0!important;border:1px solid #e7e1fb!important;border-radius:10px;overflow:hidden;background:#fff;box-shadow:0 1px 2px rgba(16,16,32,.07),0 10px 28px -16px rgba(79,70,214,.45);animation:tpaeWrIn .4s cubic-bezier(.21,1,.31,1) both}
			.tpae-wr.tpae-wr--out{opacity:0;transform:translateY(-6px) scale(.99);transition:opacity .2s ease,transform .2s ease}

			.tpae-wr__hero{position:relative;display:flex;align-items:center;gap:10px;padding:12px 46px 12px 18px;background:linear-gradient(105deg,#3f37c9,#6660EF 55%,#7c3aed);color:#fff;overflow:hidden}
			.tpae-wr__hero::after{content:"";position:absolute;top:-60%;left:-30%;width:60%;height:220%;z-index:0;background:linear-gradient(90deg,transparent,rgba(255,255,255,.18),transparent);transform:skewX(-18deg);animation:tpaeWrSweep 4.5s ease-in-out infinite;pointer-events:none}
			.tpae-wr__logo{position:relative;z-index:1;width:22px;height:22px;flex:0 0 auto}
			.tpae-wr__brand{position:relative;z-index:1;font-size:13.5px;font-weight:600}
			.tpae-wr__pill{position:relative;z-index:1;margin-left:auto;font-size:11px;font-weight:600;background:rgba(255,255,255,.18);padding:3px 10px;border-radius:6px}

			.tpae-wr .notice-dismiss:before{color:rgba(255,255,255,.85)}
			.tpae-wr .notice-dismiss:hover:before,.tpae-wr .notice-dismiss:focus:before{color:#fff}

			.tpae-wr__body{padding:16px 20px 16px;background:#fff}
			.tpae-wr__title{margin:0 0 6px;font-size:17px;font-weight:600;color:#1d2327;line-height:1.35}
			.tpae-wr__text{margin:0 0 13px;font-size:13px;line-height:1.55;color:#3c434a}

			.tpae-wr__btns{display:flex;gap:9px;align-items:center;flex-wrap:wrap}

			/* Core common.css styles `div.notice a` at (0,1,2), stronger than a single
			   class, and would force the theme link colour, an underline and its own
			   border-radius. Every rule below is qualified with the tag name so it
			   outranks core without needing !important. */
			.tpae-wr a.tpae-wr__btn{display:inline-flex;align-items:center;justify-content:center;height:33px;padding:0 16px;border-radius:8px;border:1px solid transparent;font-size:12.5px;font-weight:500;line-height:1;cursor:pointer;text-decoration:none;white-space:nowrap;transition:transform .16s ease,box-shadow .16s ease,background .16s ease,border-color .16s ease}
			.tpae-wr a.tpae-wr__btn:hover,.tpae-wr a.tpae-wr__btn:focus{text-decoration:none}
			.tpae-wr a.tpae-wr__btn--on{background:linear-gradient(96deg,#6660EF,#7c3aed);color:#fff;box-shadow:0 2px 11px -3px rgba(124,58,237,.7)}
			.tpae-wr a.tpae-wr__btn--on:hover{background:linear-gradient(96deg,#5a54e6,#6d28d9);transform:translateY(-1px);box-shadow:0 7px 18px -6px rgba(124,58,237,.85);color:#fff}
			.tpae-wr a.tpae-wr__btn--on:focus,.tpae-wr a.tpae-wr__btn--on:focus-visible{color:#fff;border-radius:8px;box-shadow:0 0 0 3px rgba(124,58,237,.5)}
			.tpae-wr a.tpae-wr__btn--gh{background:#fff;border-color:#d9d6ea;color:#5a52e0}
			.tpae-wr a.tpae-wr__btn--gh:hover{background:#f7f6fc;border-color:#bfbada;color:#413c5e}
			.tpae-wr a.tpae-wr__btn--gh:focus,.tpae-wr a.tpae-wr__btn--gh:focus-visible{color:#5a52e0;border-radius:8px;box-shadow:0 0 0 3px rgba(124,58,237,.3)}

			.tpae-wr .notice-dismiss{z-index:2;top:8px;right:2px}

			@keyframes tpaeWrIn{from{opacity:0;transform:translateY(-9px)}to{opacity:1;transform:none}}
			@keyframes tpaeWrSweep{0%,100%{left:-30%}55%{left:120%}}

			@media screen and (max-width:782px){
				.tpae-wr__hero{padding:11px 44px 11px 16px}
				.tpae-wr__body{padding:13px 16px 14px}
				.tpae-wr__btn{flex:1 1 auto}
			}

			@media (prefers-reduced-motion:reduce){
				.tpae-wr,.tpae-wr__hero::after{animation:none!important}
				.tpae-wr a.tpae-wr__btn{transition:none!important}
			}
			</style>
			<?php
		}

		/**
		 * Store the dismissal so the notice does not return.
		 *
		 * @since 6.5.1
		 */
		public function theplus_widget_recipes_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-widget-recipes' ) ) {
				wp_send_json_error( __( 'Security check failed', 'tpebl' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			update_option( self::OPTION, true, false );

			wp_send_json_success();
		}
	}

	Tp_Widget_Recipes_Notice::instance();
}
