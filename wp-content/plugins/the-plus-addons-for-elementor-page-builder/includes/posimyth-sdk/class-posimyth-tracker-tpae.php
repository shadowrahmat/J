<?php
/**
 * POSIMYTH Analytics tracker — The Plus Addons for Elementor (TPAE).
 *
 * Requires class-posimyth-tracker-base.php to be loaded first.
 * Boot from the main plugin file:  Posimyth_Tracker_TPAE::init();
 *
 * Verified against TPAE 6.4.17 in this repo:
 *   - version constant : L_THEPLUS_VERSION
 *   - white label      : theplus_white_label (array; see tpae_posimyth_is_white_labelled())
 *   - enabled widgets  : theplus_options['check_elements'] (NOT theplus_widgets_settings, which is
 *                        the Form widget's captcha config — see enabled_features())
 *   - widget usage     : scanned from _elementor_data by the shared base ('tp-' widget prefix,
 *                        confirmed from the widgets' own get_name() returns)
 *
 * Consent is deliberately TPAE's OWN — posimyth_tpae_share_analytics, under suite_key `tpae_suite`.
 * Nexter Extension and Nexter Blocks share one answer between them because they are one brand with one
 * dashboard; The Plus Addons for Elementor is a separate product, so it asks and stores separately. See
 * the OPT_IN_OPTION note below.
 *
 * @package POSIMYTH\Analytics\SDK
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Self-load the shared base so this subclass defines correctly regardless of require order.
if ( ! class_exists( 'Posimyth_Tracker_Base' ) ) {
	require_once __DIR__ . '/class-posimyth-tracker-base.php';
}

if ( ! class_exists( 'Posimyth_Tracker_TPAE' ) && class_exists( 'Posimyth_Tracker_Base' ) ) {

	/**
	 * TPAE's tracker: supplies the product-specific identity, version, Pro state and widget usage
	 * that the shared base assembles into a payload.
	 */
	class Posimyth_Tracker_TPAE extends Posimyth_Tracker_Base {

		/**
		 * TPAE's OWN consent option — deliberately NOT the one Nexter Extension and Nexter Blocks share.
		 *
		 * Nexter is a single brand: Extension and Blocks are two halves of one product with one
		 * dashboard, so one answer covering both is what a user expects. The Plus Addons for Elementor
		 * is a separate product with its own dashboard and its own audience, so folding it into that
		 * answer would mean consenting for a plugin the user never had in mind. It keeps its own key and
		 * its own suite_key, which also gives it its own notice and its own Allow/Dismiss.
		 *
		 * Do NOT point this at posimyth_nexter_share_analytics.
		 */
		const OPT_IN_OPTION = 'posimyth_tpae_share_analytics';

		/**
		 * Short internal id used for this product's own options (install time, usage cache).
		 *
		 * @return string
		 */
		protected static function id(): string {
			return 'tpae';
		}

		/**
		 * Plugin slug reported to the hub.
		 *
		 * Must match an entry in the hub's PLUGIN_SLUGS allowlist, or every ping is rejected.
		 *
		 * @return string
		 */
		protected static function slug(): string {
			return 'the-plus-addons-for-elementor-page-builder';
		}

		/**
		 * Name shown in WordPress's Privacy Policy suggestions.
		 *
		 * @return string
		 */
		protected static function display_name(): string {
			return 'The Plus Addons for Elementor';
		}

		/**
		 * Option holding TPAE's own sharing consent — see OPT_IN_OPTION.
		 *
		 * @return string
		 */
		protected static function opt_in_option(): string {
			return self::OPT_IN_OPTION;
		}

		/**
		 * Currently installed version of this plugin.
		 *
		 * @return string
		 */
		protected static function version(): string {
			return defined( 'L_THEPLUS_VERSION' ) ? L_THEPLUS_VERSION : '';
		}

		/**
		 * Whether the Pro build is active.
		 *
		 * TPAE Pro is a separate plugin whose folder is `theplus_elementor_addon` — not the free
		 * plugin's slug with a -pro suffix, which is why it is easy to miss. It defines THEPLUS_VERSION
		 * (the free build defines L_THEPLUS_VERSION, the "lite" prefix), so that constant is the marker.
		 *
		 * Deliberately not NXT_PRO_EXT: the free build's own dashboard reads that for its `is_pro` key,
		 * but NXT_PRO_EXT is Nexter Pro Extension, a different product entirely. Reporting it here would
		 * mark Nexter Pro users as TPAE Pro users and corrupt the free-vs-pro split on the hub.
		 *
		 * @return bool
		 */
		protected static function is_pro(): bool {
			return defined( 'THEPLUS_VERSION' );
		}

		/**
		 * License status/plan for the Pro build.
		 *
		 * Read from `tpaep_licence_data`, which TPAE Pro writes (see its class-tpaep-licence.php). The
		 * array holds `license_key`, `success` and `license` — the last being the status string, e.g.
		 * 'valid'. Only the status travels; the key itself must never be sent.
		 *
		 * The free build's tpae_*_license_notice options are NOT used: those record whether a warning
		 * was displayed, not whether a licence is valid, so reading them would report fiction.
		 *
		 * @return array{status:string, plan:string}
		 */
		protected static function license(): array {
			if ( ! static::is_pro() ) {
				return array(
					'status' => '',
					'plan'   => '',
				);
			}

			$raw = get_option( 'tpaep_licence_data', array() );
			if ( ! is_array( $raw ) ) {
				return array(
					'status' => '',
					'plan'   => '',
				);
			}

			$status = '';
			if ( ! empty( $raw['license'] ) && is_string( $raw['license'] ) ) {
				$status = sanitize_key( $raw['license'] );
			} elseif ( ! empty( $raw['success'] ) ) {
				// Older records carry only the success flag, with no status string.
				$status = 'valid';
			}

			return array(
				'status' => $status,
				// TPAE Pro is a single tier — nothing in tpaep_licence_data names a plan, so this stays
				// empty rather than inventing one.
				'plan'   => '',
			);
		}

		/**
		 * Which TPAE widgets are ENABLED in settings.
		 *
		 * Source is `theplus_options['check_elements']` — the numeric list of enabled widget slugs that
		 * the Widgets screen writes (see Tpae_Dashboard_Ajax::tpae_set_widget_list) and that the rest of
		 * the plugin reads to decide what to register.
		 *
		 * NOT `theplus_widgets_settings`, which an earlier revision of this method used. Despite the
		 * name, that option is the Form widget's captcha configuration —
		 * `tp_plus_form => { google_site_key, google_secret_key, cloudflare_site_key, … }` — so every
		 * install reported the single meaningless key `tp_plus_form` as its entire enabled-widget list.
		 * (No secret ever escaped: the loop only emitted keys and booleans, never values.)
		 *
		 * Slugs are stored with UNDERSCORES here (`tp_accordion`), while used_features() scans Elementor
		 * content for the widgets' own hyphenated get_name() values (`tp-accordion`). They are
		 * normalised to the hyphen form so `enabled_widgets` and `widget_usage` describe the same widget
		 * under the same key and the hub can join them.
		 *
		 * @return array<string,bool>
		 */
		protected static function enabled_features(): array {
			$opts = get_option( 'theplus_options', array() );
			if ( ! is_array( $opts ) || empty( $opts['check_elements'] ) || ! is_array( $opts['check_elements'] ) ) {
				return array();
			}

			$map = array();
			foreach ( $opts['check_elements'] as $key => $value ) {
				// Numeric list => the value is the slug. Assoc map => the key is the slug, and the value
				// says whether it is on. Both shapes are accepted so a future settings format change
				// degrades to wrong-but-harmless rather than empty.
				$slug = is_int( $key ) ? $value : $key;
				if ( ! is_string( $slug ) || '' === $slug ) {
					continue;
				}

				if ( is_int( $key ) ) {
					// Presence in the list IS the enablement — the list holds only enabled widgets.
					$on = true;
				} elseif ( is_string( $value ) ) {
					$on = ! in_array( strtolower( $value ), array( '', 'off', 'no', 'false', '0' ), true );
				} else {
					$on = (bool) $value;
				}

				// sanitize_key keeps both '-' and '_', so the underscore form has to be converted
				// explicitly to match the scanned widget names.
				$map[ str_replace( '_', '-', sanitize_key( $slug ) ) ] = $on;
			}

			return $map;
		}

		/**
		 * Whether the setup wizard has been finished on this site.
		 *
		 * The base class returns '' — a stub nothing ever filled — so the hub's "Onboarding Done" card
		 * and chart, which compare against 'completed', showed 0% for every site no matter what.
		 *
		 * TPAE tracks this in its own option, not the Nexter one: tpae_onbording_end (the spelling is
		 * the plugin's) is written as 'hide' by tpae_onboarding_setup() in class-tpae-dashboard-ajax.php.
		 * Deliberately NOT nxt_onboarding_done — that belongs to the Nexter wizard and has nothing to do
		 * with this product's flow.
		 *
		 * Read as truthy rather than compared to 'hide'. That handler is the only place that sets it
		 * meaningfully, but the key is also in tpae_is_allowed_storage_key(), so the generic
		 * tpae_wp_option_manage endpoint can write it to any value — a truthy test keeps reporting
		 * sane if that happens, and survives a future value change in the handler without silently
		 * resetting every site's onboarding figure to pending.
		 *
		 * @return string 'completed' or 'pending'.
		 */
		protected static function onboarding_status(): string {
			return get_option( 'tpae_onbording_end' ) ? 'completed' : 'pending';
		}

		/**
		 * Real widget usage counted from Elementor content.
		 *
		 * Uses the shared, bounded scanner in the base class (capped by the `posimyth_scan_post_cap`
		 * filter, keyset-batched, and only ever run on the weekly cron — activate/deactivate read a
		 * cached copy so the admin never waits on a scan).
		 *
		 * 'tp-' is TPAE's Elementor widget prefix, taken from the widgets' own get_name() returns
		 * (tp-accordion, tp-button, tp-table …), not from the plugin slug.
		 *
		 * @return array<string,int>
		 */
		protected static function used_features(): array {
			return self::scan_elementor_widgets( 'tp-' );
		}

		/**
		 * TPAE-only settings that matter for reproducing a report but do not warrant a shared column.
		 *
		 * Right now that is the Asset Manager choice from Extra Options → Performance, which decides how
		 * widget CSS and JS reach the page. It changes what a site actually loads more than most widget
		 * toggles do, so a conflict or slow-page report reads very differently depending on it.
		 *
		 * Deliberately an allowlist of individual keys rather than the whole `theplus_performance`
		 * option: that option is written by JSON-decoding a POST body wholesale, so anything a future
		 * dashboard build decides to put in it would otherwise start travelling to the hub unreviewed.
		 *
		 * Values are the STORED ones, not the dashboard's labels — 'external' is shown as
		 * "Smart Optimised (Recommended)" and 'separate' as "On Demand Assets". Reporting the label
		 * would split one setting into several series on the hub the first time the wording changes.
		 * A site that has never opened the screen reports 'default'.
		 *
		 * @return array<string,mixed>
		 */
		protected static function plugin_meta(): array {
			$perf = get_option( 'theplus_performance', array() );
			if ( ! is_array( $perf ) ) {
				$perf = array();
			}

			$asset_mode = isset( $perf['plus_cache_option'] ) && is_string( $perf['plus_cache_option'] )
				? sanitize_key( $perf['plus_cache_option'] )
				: '';

			$opts = get_option( 'theplus_options', array() );
			if ( ! is_array( $opts ) ) {
				$opts = array();
			}

			$lazy_load = isset( $opts['plus_lazyload_opt'] ) && is_scalar( $opts['plus_lazyload_opt'] )
				? sanitize_key( (string) $opts['plus_lazyload_opt'] )
				: '';

			return array(
				'performance' => array(
					'asset_mode' => '' !== $asset_mode ? $asset_mode : 'default',
					'lazy_load'  => '' !== $lazy_load ? $lazy_load : 'default',
				),
				'extensions'  => self::enabled_extensions( $opts ),
				'templates'   => array(
					'tpae_created' => self::tpae_template_count(),
				),
			);
		}

		/**
		 * How many templates were made with TPAE's own template editor.
		 *
		 * TPAE registers no post type of its own — its editor writes into Elementor's `elementor_library`
		 * (see tpae_template_create() in class-tp-create-template.php) and marks them with a post_name of
		 * `tp-create-template-<uniqid>`. That prefix is the only thing separating our templates from the
		 * page, section and Elementor Pro theme templates sharing that post type, so it is what gets
		 * counted here rather than the post type as a whole.
		 *
		 * A count rather than a yes/no: "made one and abandoned it" and "runs the site on thirty" are
		 * different stories, and the number costs nothing extra to collect.
		 *
		 * @return int
		 */
		private static function tpae_template_count(): int {
			if ( ! post_type_exists( 'elementor_library' ) ) {
				return 0;
			}

			global $wpdb;

			// phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- one bounded count while a payload is assembled; no cache layer covers a post_name prefix.
			$count = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts}
					WHERE post_type = 'elementor_library'
					AND post_status = 'publish'
					AND post_name LIKE %s",
					$wpdb->esc_like( 'tp-create-template-' ) . '%'
				)
			);
			// phpcs:enable

			return (int) $count;
		}

		/**
		 * Which TPAE Extensions are enabled.
		 *
		 * Source is `theplus_options['extras_elements']` — the Extensions screen's counterpart to
		 * `check_elements`, holding slugs like plus_global_button, plus_dynamic_tag, plus_custom_css.
		 * Extensions are not widgets: they add editor controls and global features rather than something
		 * you drop on a page, so there is no content to scan for usage and nothing to join them against.
		 * That is why they belong in plugin_meta and NOT in enabled_widgets, which is paired with
		 * widget_usage and has to stay a widget map.
		 *
		 * Returned as slug => true to match the shape of enabled_widgets, so the hub can aggregate both
		 * with the same code. Presence in the stored list is the enablement, exactly as with widgets;
		 * the assoc-map form is accepted too in case that format ever changes.
		 *
		 * @param array $opts Already-read `theplus_options`.
		 * @return array<string,bool>
		 */
		private static function enabled_extensions( array $opts ): array {
			if ( empty( $opts['extras_elements'] ) || ! is_array( $opts['extras_elements'] ) ) {
				return array();
			}

			$map = array();
			foreach ( $opts['extras_elements'] as $key => $value ) {
				$slug = is_int( $key ) ? $value : $key;
				if ( ! is_string( $slug ) || '' === $slug ) {
					continue;
				}

				if ( is_int( $key ) ) {
					$on = true;
				} elseif ( is_string( $value ) ) {
					$on = ! in_array( strtolower( $value ), array( '', 'off', 'no', 'false', '0' ), true );
				} else {
					$on = (bool) $value;
				}

				// Kept in the stored spelling — unlike widgets these have no scanned counterpart to
				// match, so rewriting the separator would only hide the real slug.
				$map[ sanitize_key( $slug ) ] = $on;
			}

			return $map;
		}
	}
}
