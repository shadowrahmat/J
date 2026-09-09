<?php
/**
 * "What's new in this release" admin notice.
 *
 * Shown once per release line. The dismissal stores the release version rather
 * than a boolean, so the next release shows its own notice without needing a new
 * option key -- bump nothing, just ship.
 *
 * Deliberately does NOT repeat the removed/deprecated widget list: that is owned
 * by class-tp-removed-widgets-notice.php, and two notices saying the same thing
 * on the same screen reads as noise.
 *
 * @link       https://posimyth.com/
 * @since      6.5.0
 *
 * @package    Theplus
 * @subpackage ThePlus/Notices
 * */

namespace Tp\Notices\WhatsNew;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Whats_New_Notice' ) ) {

	/**
	 * Release highlights notice.
	 *
	 * @since 6.5.0
	 */
	class Tp_Whats_New_Notice {

		/**
		 * Instance
		 *
		 * @since 6.5.0
		 * @static
		 * @var instance of the class.
		 */
		private static $instance = null;

		/**
		 * Option holding the release whose notice was last dismissed.
		 *
		 * @since 6.5.0
		 * @var string
		 */
		const OPTION = 'tpae_whats_new_dismissed';

		/**
		 * Option holding the release we first saw, and when, as "release|timestamp".
		 *
		 * @since 6.5.0
		 * @var string
		 */
		const OPTION_SEEN = 'tpae_whats_new_seen';

		/**
		 * Hours to hold the notice back after a release first appears.
		 *
		 * 6.5.0 also ships the removed/deprecated widgets notice, which is time
		 * critical because it asks people to migrate away from widgets that are
		 * already gone. Stacking both on the same screen buries it, so this one
		 * waits and gets the Dashboard to itself afterwards.
		 *
		 * @since 6.5.0
		 * @var int
		 */
		const DELAY_HOURS = 48;

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
			add_action( 'wp_ajax_theplus_whats_new_notice_dismiss', array( $this, 'theplus_whats_new_notice_dismiss' ) );

			if ( $this->tp_release() !== get_option( self::OPTION ) && $this->tp_delay_passed() ) {
				add_action( 'admin_notices', array( $this, 'theplus_whats_new_notice' ) );
			}
		}

		/**
		 * Whether the hold-back window has elapsed for the current release.
		 *
		 * Deliberately NOT keyed off tpae_install_time, which the review and
		 * community notices use: that option is stamped once on first install and
		 * never again, so on an existing site it is already months old and the
		 * window would count as elapsed the instant the update lands. This needs
		 * the moment THIS release first appeared, so it stamps its own.
		 *
		 * Stores a Unix timestamp rather than the mysql datetime the sibling
		 * notices store. current_time( 'mysql' ) is site-local while time() is UTC,
		 * so comparing the two is off by the site's UTC offset. That is harmless
		 * across a 14 or 30 day wait but not across 48 hours.
		 *
		 * @since 6.5.0
		 *
		 * @return bool True once the window has passed.
		 */
		public function tp_delay_passed() {
			$release = $this->tp_release();

			if ( '' === $release ) {
				return false;
			}

			$seen  = get_option( self::OPTION_SEEN );
			$parts = is_string( $seen ) ? explode( '|', $seen, 2 ) : array();

			/** First admin load on this release: stamp it and hold the notice back. */
			if ( 2 !== count( $parts ) || $parts[0] !== $release || ! is_numeric( $parts[1] ) ) {
				update_option( self::OPTION_SEEN, $release . '|' . time(), false );

				return false;
			}

			return ( time() - (int) $parts[1] ) >= ( self::DELAY_HOURS * HOUR_IN_SECONDS );
		}

		/**
		 * Current release line, without any beta or RC suffix.
		 *
		 * 6.5.0-beta.8 and 6.5.0 are the same release as far as this notice is
		 * concerned, so testers do not get it twice.
		 *
		 * @since 6.5.0
		 *
		 * @return string
		 */
		public function tp_release() {
			$version = defined( 'L_THEPLUS_VERSION' ) ? L_THEPLUS_VERSION : '';

			return (string) preg_replace( '/[-+].*$/', '', $version );
		}

		/**
		 * The secondary release points, in the order they are shown.
		 *
		 * Titles only. Abilities is the headline and carries the one explanatory
		 * line; these five are short enough to read as pills without prose.
		 *
		 * @since 6.5.0
		 *
		 * @return array
		 */
		public function tp_highlights() {
			return array(
				array(
					'icon'  => 'bolt',
					'title' => esc_html__( 'Lighter, faster pages', 'tpebl' ),
				),
				array(
					'icon'  => 'refresh',
					'title' => esc_html__( 'Reliable caching', 'tpebl' ),
				),
				array(
					'icon'  => 'shield',
					'title' => esc_html__( 'Sturdier output', 'tpebl' ),
				),
				array(
					'icon'  => 'layers',
					'title' => esc_html__( 'Widget changes', 'tpebl' ),
				),
				array(
					'icon'  => 'bug',
					'title' => esc_html__( 'Many fixes', 'tpebl' ),
				),
			);
		}

		/**
		 * Inline SVG for a highlight icon.
		 *
		 * Inline rather than an icon font so the notice never depends on a
		 * stylesheet that might be gated off the current screen.
		 *
		 * @since 6.5.0
		 *
		 * @param  string $name Icon key.
		 * @return string
		 */
		public function tp_icon( $name ) {
			$paths = array(
				'bolt'    => '<path d="M13 2 4.5 12.5h5L9 22l8.5-10.5h-5L13 2z"/>',
				'refresh' => '<path d="M20 12a8 8 0 1 1-2.3-5.6"/><path d="M20 3v4h-4"/>',
				'shield'  => '<path d="M12 3l7 3v6c0 4.2-2.9 7.9-7 9-4.1-1.1-7-4.8-7-9V6l7-3z"/><path d="M9 12l2 2 4-4"/>',
				'layers'  => '<path d="M12 3l9 5-9 5-9-5 9-5z"/><path d="M3 13l9 5 9-5"/>',
				'bug'     => '<path d="M9 4l1.5 2.5h3L15 4"/><rect x="7" y="7" width="10" height="12" rx="5"/><path d="M3 12h4M17 12h4M4 17l3-2M20 17l-3-2M4 8l3 2M20 8l-3 2"/>',
			);

			$path = isset( $paths[ $name ] ) ? $paths[ $name ] : $paths['bolt'];

			return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>';
		}

		/**
		 * Render the notice.
		 *
		 * @since 6.5.0
		 */
		public function theplus_whats_new_notice() {

			$screen = get_current_screen();

			$allowed_parents = array( 'index', 'elementor', 'themes', 'edit', 'plugins', 'theplus_welcome_page' );

			$parent_base = ! empty( $screen->parent_base ) && in_array( $screen->parent_base, $allowed_parents, true );

			if ( ! $parent_base ) {
				return;
			}

			$nonce    = wp_create_nonce( 'tpae-whats-new' );
			$release  = $this->tp_release();
			/**
			 * UTM follows the convention the other notices already use: source is always
			 * wpbackend, medium names the surface and campaign names the subject. See
			 * class-tp-nexter-notice.php, which tags an admin notice as adminpanel.
			 *
			 * The roadmap link is deliberately left clean, matching the existing roadmap
			 * links in class-tp-plugin-page.php, which carry no UTM.
			 */
			$utm         = 'utm_source=wpbackend&utm_medium=adminpanel&utm_campaign=abilities';
			$log_url     = 'https://roadmap.theplusaddons.com/updates/';
			$learn_url   = 'https://theplusaddons.com/mcp-abilities/?' . $utm;
			$recipes_url = 'https://theplusaddons.com/widget-recipes/?utm_source=wpbackend&utm_medium=adminpanel&utm_campaign=widgetrecipes';
			$logo_url    = defined( 'L_THEPLUS_ASSETS_URL' ) ? L_THEPLUS_ASSETS_URL . 'images/tpae-favicon-white.png' : '';

			/**
			 * The Abilities switch lives on the dashboard Settings route, in the group
			 * labelled "Abilities Access" (theplus_ability_switch, inside the
			 * theplus_api_connection_data option). It is off on every upgrading site,
			 * so this button is the activation path rather than a nicety.
			 */
			$on_url = admin_url( 'admin.php?page=theplus_welcome_page#/settings' );

			/**
			 * Read the switch the same way widgets_loader.php does, so the button only
			 * shows when it is actually actionable. On a site that already has Abilities
			 * on, a 'Turn on Abilities' button would be a dead end.
			 */
			$tpae_conn   = get_option( 'theplus_api_connection_data' );
			$ability_on  = ! empty( $tpae_conn['theplus_ability_switch'] ) && 'on' === $tpae_conn['theplus_ability_switch'];

			$this->tp_styles();
			?>
			<div class="notice tpae-wn is-dismissible" role="region" aria-label="<?php echo esc_attr__( 'What is new in The Plus Addons for Elementor', 'tpebl' ); ?>">

				<div class="tpae-wn__hero">
					<?php if ( $logo_url ) { ?>
						<img class="tpae-wn__logo" src="<?php echo esc_url( $logo_url ); ?>" alt="" width="22" height="22">
					<?php } ?>
					<span class="tpae-wn__v"><?php echo esc_html( $release ); ?></span>
					<span class="tpae-wn__ht"><?php echo esc_html__( 'What is new in The Plus Addons', 'tpebl' ); ?></span>
					<span class="tpae-wn__hs"><?php echo esc_html__( 'Update installed', 'tpebl' ); ?></span>
				</div>

				<div class="tpae-wn__body">

					<div class="tpae-wn__call">
						<span class="tpae-wn__callic">
							<?php echo $this->tp_icon( 'bolt' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup. ?>
						</span>
						<span class="tpae-wn__calltx">
							<span class="tpae-wn__callt">
								<?php echo esc_html__( 'Abilities: Now Builds Whole Pages, Not Just Widgets', 'tpebl' ); ?>
								<em><?php echo esc_html( $release ); ?></em>
							</span>
							<span class="tpae-wn__calls">
								<?php
								if ( $ability_on ) {
									echo esc_html__( 'Claude, or any MCP client, can build and edit your pages. Abilities is on.', 'tpebl' );
								} else {
									echo esc_html__( 'Claude, or any MCP client, can build and edit your pages. Off until you switch it on.', 'tpebl' );
								}
								?>
							</span>
						</span>
						<span class="tpae-wn__callacts">
							<?php if ( ! $ability_on ) { ?>
								<a class="tpae-wn__btn tpae-wn__btn--on" href="<?php echo esc_url( $on_url ); ?>">
									<?php echo esc_html__( 'Turn on Abilities', 'tpebl' ); ?>
								</a>
								<a class="tpae-wn__btn tpae-wn__btn--gh" href="<?php echo esc_url( $learn_url ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html__( 'Learn More', 'tpebl' ); ?>
								</a>
							<?php } else { ?>
								<a class="tpae-wn__btn tpae-wn__btn--on" href="<?php echo esc_url( $learn_url ); ?>" target="_blank" rel="noopener noreferrer">
									<?php echo esc_html__( 'Learn More', 'tpebl' ); ?>
								</a>
							<?php } ?>
						</span>
					</div>

					<ul class="tpae-wn__grid">
						<?php
						$i = 0;
						foreach ( $this->tp_highlights() as $item ) {
							++$i;
							?>
							<li class="tpae-wn__item" style="--tpae-i: <?php echo (int) $i; ?>">
								<?php echo $this->tp_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static inline SVG markup. ?>
								<strong><?php echo esc_html( $item['title'] ); ?></strong>
							</li>
							<?php
						}
						?>
						<li class="tpae-wn__logli">
							<a class="tpae-wn__log" href="<?php echo esc_url( $recipes_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html__( 'Browse Widget Recipes', 'tpebl' ); ?>
							</a>
							<span class="tpae-wn__logsep" aria-hidden="true">&middot;</span>
							<a class="tpae-wn__log" href="<?php echo esc_url( $log_url ); ?>" target="_blank" rel="noopener noreferrer">
								<?php echo esc_html__( 'See the full changelog', 'tpebl' ); ?>
							</a>
						</li>
					</ul>
				</div>
			</div>
			<script>
				jQuery( document ).on( 'click', '.tpae-wn .notice-dismiss', function ( e ) {
					e.preventDefault();

					var $notice = jQuery( this ).closest( '.tpae-wn' );

					$notice.addClass( 'tpae-wn--out' );
					window.setTimeout( function () {
						$notice.slideUp( 180, function () {
							$notice.remove();
						} );
					}, 220 );

					jQuery.ajax( {
						url: ajaxurl,
						type: 'POST',
						data: {
							action: 'theplus_whats_new_notice_dismiss',
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
		 * Emitted inline and namespaced under .tpae-wn so nothing here can reach
		 * the rest of wp-admin, and so the notice never depends on a stylesheet
		 * that is gated off this screen.
		 *
		 * @since 6.5.0
		 */
		public function tp_styles() {
			?>
			<style>
			.tpae-wn{position:relative;padding:0!important;border:0!important;border-radius:10px;overflow:hidden;box-shadow:0 1px 2px rgba(16,16,32,.07),0 10px 28px -16px rgba(79,70,214,.45);animation:tpaeWnIn .4s cubic-bezier(.21,1,.31,1) both}
			.tpae-wn.tpae-wn--out{opacity:0;transform:translateY(-6px) scale(.99);transition:opacity .2s ease,transform .2s ease}

			.tpae-wn__hero{position:relative;display:flex;align-items:center;gap:12px;padding:15px 46px 15px 20px;background:linear-gradient(105deg,#3f37c9,#6660EF 55%,#7c3aed);color:#fff;overflow:hidden;flex-wrap:wrap}
			.tpae-wn__hero::after{content:"";position:absolute;top:-60%;left:-30%;width:60%;height:220%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.16),transparent);transform:skewX(-18deg);animation:tpaeWnSweep 4.5s ease-in-out infinite;pointer-events:none}
			.tpae-wn__logo{position:relative;width:22px;height:22px;flex:0 0 auto;animation:tpaeWnPop .5s .1s cubic-bezier(.2,1.5,.4,1) both}
			.tpae-wn__v{position:relative;flex:0 0 auto;font-size:15px;font-weight:700;letter-spacing:-.01em;padding:3px 10px;border-radius:6px;background:rgba(255,255,255,.18)}
			.tpae-wn__ht{position:relative;flex:1 1 auto;font-size:14.5px;font-weight:600}
			.tpae-wn__hs{position:relative;flex:0 0 auto;font-size:12px;color:#fff}

			.tpae-wn .notice-dismiss{top:8px;right:2px}
			.tpae-wn .notice-dismiss:before{color:rgba(255,255,255,.82)}
			.tpae-wn .notice-dismiss:hover:before,.tpae-wn .notice-dismiss:focus:before{color:#fff}

			.tpae-wn__body{padding:14px 20px 15px;background:#fff}

			/* Abilities callout. The thick left edge is wp-admin's own callout language,
			   so it reads as "the important one" without a second gradient panel. */
			.tpae-wn__call{display:flex;align-items:center;gap:13px;flex-wrap:wrap;margin:0 0 13px;padding:13px 15px;border:1px solid #e7e1fb;border-left:4px solid #6660EF;border-radius:8px;background:linear-gradient(120deg,#fbfaff,#fdf8ff)}
			.tpae-wn__callic{flex:0 0 auto;width:34px;height:34px;border-radius:10px;background:#f0edfe;border:1px solid #e2dafb;display:flex;align-items:center;justify-content:center}
			.tpae-wn__callic svg{width:18px;height:18px;color:#6d28d9}
			.tpae-wn__calltx{flex:1 1 290px;min-width:0}
			.tpae-wn__callt{display:block;font-size:15px;font-weight:600;letter-spacing:-.018em;color:#191536;line-height:1.26}
			.tpae-wn__callt em{font-style:normal;display:inline-block;font-size:9.5px;font-weight:600;letter-spacing:.06em;color:#fff;background:linear-gradient(96deg,#6660EF,#7c3aed);border-radius:20px;padding:2px 8px;margin-left:8px;vertical-align:2px}
			.tpae-wn__calls{display:block;margin-top:3px;font-size:12px;color:#544e70;line-height:1.48;max-width:78ch}
			.tpae-wn__callacts{flex:0 0 auto;display:flex;align-items:center;gap:9px;flex-wrap:wrap}

			/* secondary release points as pills: a box holding only a short title reads
			   as unfinished, a pill reads as deliberate */
			.tpae-wn__grid{display:flex;flex-wrap:wrap;align-items:center;gap:8px;margin:0;padding:0;list-style:none}
			.tpae-wn__item{display:inline-flex;align-items:center;gap:7px;margin:0;padding:6px 11px;border:1px solid #e9e6f5;border-radius:20px;background:#f8f7fd;transition:border-color .18s ease,background .18s ease;animation:tpaeWnUp .4s ease both;animation-delay:calc(var(--tpae-i,1) * 45ms + 60ms)}
			.tpae-wn__item:hover{border-color:#cfc9ef;background:#f3f1fb}
			.tpae-wn__item svg{width:14px;height:14px;flex:0 0 auto;color:#5b52e0}
			.tpae-wn__item strong{font-size:11.5px;font-weight:600;color:#3f3a5c}

			.tpae-wn__logli{margin:0 0 0 auto;padding:0;border:0;background:none;display:inline-flex;align-items:center}

			/* Core common.css styles `div.notice a` at (0,1,2), stronger than a single
			   class, and would force the theme link colour, an underline, its own focus
			   ring and border-radius:2px. Every rule below is qualified with the tag name
			   so it outranks core without needing !important. */
			.tpae-wn a.tpae-wn__btn{display:inline-flex;align-items:center;justify-content:center;height:33px;padding:0 16px;border-radius:8px;border:1px solid transparent;font-size:12.5px;font-weight:500;line-height:1;cursor:pointer;text-decoration:none;white-space:nowrap;transition:transform .16s ease,box-shadow .16s ease,background .16s ease,border-color .16s ease}
			.tpae-wn a.tpae-wn__btn:hover,.tpae-wn a.tpae-wn__btn:focus{text-decoration:none}
			.tpae-wn a.tpae-wn__btn--on{background:linear-gradient(96deg,#6660EF,#7c3aed);color:#fff;box-shadow:0 2px 11px -3px rgba(124,58,237,.7)}
			.tpae-wn a.tpae-wn__btn--on:hover{background:linear-gradient(96deg,#5a54e6,#6d28d9);color:#fff;transform:translateY(-1px);box-shadow:0 7px 18px -6px rgba(124,58,237,.85)}
			.tpae-wn a.tpae-wn__btn--on:focus,.tpae-wn a.tpae-wn__btn--on:focus-visible{color:#fff;border-radius:8px;box-shadow:0 0 0 3px rgba(124,58,237,.5)}
			.tpae-wn a.tpae-wn__btn--gh{background:#fff;border-color:#d9d6ea;color:#413c5e}
			.tpae-wn a.tpae-wn__btn--gh:hover{background:#f7f6fc;border-color:#bfbada;color:#26234a}
			.tpae-wn a.tpae-wn__btn--gh:focus,.tpae-wn a.tpae-wn__btn--gh:focus-visible{color:#413c5e;border-radius:8px;box-shadow:0 0 0 3px rgba(124,58,237,.35)}
			.tpae-wn a.tpae-wn__log{font-size:11.5px;color:#524bcf;text-decoration:underline}
			.tpae-wn a.tpae-wn__log:hover,.tpae-wn a.tpae-wn__log:focus{color:#3b35a6}
			.tpae-wn__logsep{font-size:11.5px;color:#b8b3e0;margin:0 8px}

			@keyframes tpaeWnIn{from{opacity:0;transform:translateY(-9px)}to{opacity:1;transform:none}}
			@keyframes tpaeWnUp{from{opacity:0;transform:translateY(5px)}to{opacity:1;transform:none}}
			@keyframes tpaeWnPop{from{opacity:0;transform:scale(.55)}to{opacity:1;transform:none}}
			@keyframes tpaeWnSweep{0%,100%{left:-30%}55%{left:120%}}

			@media screen and (max-width:782px){
				.tpae-wn__hero{padding:13px 44px 13px 16px}
				.tpae-wn__ht{flex:1 1 100%;order:3}
				.tpae-wn__body{padding:13px 16px 14px}
				.tpae-wn__call{padding:12px 13px}
				.tpae-wn__callacts{flex:1 1 100%}
			}

			@media (prefers-reduced-motion:reduce){
				.tpae-wn,.tpae-wn__item,.tpae-wn__logo,.tpae-wn__hero::after{animation:none!important}
				.tpae-wn__item,.tpae-wn a.tpae-wn__btn{transition:none!important}
			}
			</style>
			<?php
		}

		/**
		 * Store the dismissed release so the notice does not return until the
		 * next one.
		 *
		 * @since 6.5.0
		 */
		public function theplus_whats_new_notice_dismiss() {
			$get_security = ! empty( $_POST['security'] ) ? sanitize_text_field( wp_unslash( $_POST['security'] ) ) : '';

			if ( empty( $get_security ) || ! wp_verify_nonce( $get_security, 'tpae-whats-new' ) ) {
				wp_send_json_error( __( 'Security check failed', 'tpebl' ) );
			}

			if ( ! current_user_can( 'manage_options' ) ) {
				wp_send_json_error( __( 'You are not allowed to do this action', 'tpebl' ) );
			}

			update_option( self::OPTION, $this->tp_release(), false );

			wp_send_json_success();
		}
	}

	Tp_Whats_New_Notice::instance();
}
