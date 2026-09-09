<?php
/**
 * Plugin Name: The Plus Addons for Elementor
 * Plugin URI: https://theplusaddons.com/
 * Description: Highly Customisable 120+ Advanced Elementor Widgets & Extensions for Performance Driven Website.
 * Version: 6.5.1
 * Author: POSIMYTH
 * Author URI: https://posimyth.com/
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 7.1
 * Text Domain: tpebl
 * Domain Path: /languages
 * License: GPLv3
 * License URI: https://opensource.org/licenses/GPL-3.0
 * Elementor tested up to: 4.2
 * Elementor Pro tested up to: 4.2
 *
 * @package the-plus-addons-for-elementor-page-builder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'L_THEPLUS_VERSION', '6.5.1' );
define( 'L_THEPLUS_MINIMUM_ELEMENTOR_VERSION', '3.5.0' );
define( 'L_THEPLUS_FILE', __FILE__ );
define( 'L_THEPLUS_PATH', plugin_dir_path( __FILE__ ) );
define( 'L_THEPLUS_PBNAME', plugin_basename( __FILE__ ) );
define( 'L_THEPLUS_PNAME', basename( __DIR__ ) );
define( 'L_THEPLUS_URL', plugins_url( '/', __FILE__ ) );
define( 'L_THEPLUS_ASSETS_URL', L_THEPLUS_URL . 'assets/' );
define( 'L_THEPLUS_ASSET_PATH', wp_upload_dir()['basedir'] . DIRECTORY_SEPARATOR . 'theplus-addons' );
define( 'L_THEPLUS_ASSET_URL', wp_upload_dir()['baseurl'] . '/theplus-addons' );
define( 'L_THEPLUS_INCLUDES_URL', L_THEPLUS_PATH . 'includes/' );
define( 'L_THEPLUS_WSTYLES', L_THEPLUS_PATH . 'modules/widgets-styles/' );
define( 'L_THEPLUS_TPDOC', 'https://theplusaddons.com/docs/' );
define( 'L_THEPLUS_WDKIT_URL', 'https://wdesignkit.com/' );
define( 'L_THEPLUS_HELP', 'https://wordpress.org/support/plugin/the-plus-addons-for-elementor-page-builder/#new-topic-0' );
define( 'TPAE_MENU_NOTIFICETIONS', '4' );
define( 'TPAE_WHATS_NEW_NOTIFICETIONS', '4' );

require L_THEPLUS_PATH . 'widgets_loader.php';

/*
 * POSIMYTH Analytics SDK — registered, not required (E6).
 *
 * Direct requires behind class_exists guards would mean whichever POSIMYTH plugin loaded FIRST
 * supplied the shared classes to every sibling, so an outdated plugin could silently downgrade the
 * whole suite. Each plugin registers its bundled copy instead, and the loader requires the NEWEST one
 * at plugins_loaded priority 0. The TPAE subclass loads inside the consumer callback below, after the
 * winning copy exists.
 *
 * The shared files here are byte-identical to Nexter Extension's except for the text domain (tpebl),
 * and are kept that way by .dev/sync-posimyth-sdk.py in the Nexter Extension repo. Edit them there,
 * not here, and bump posimyth-sdk/version.php or an older sibling copy wins the loader.
 */
require_once L_THEPLUS_PATH . 'includes/posimyth-sdk/posimyth-sdk-loader.php';
posimyth_sdk_register( L_THEPLUS_PATH . 'includes/posimyth-sdk' );

/**
 * White label (Pro): a rebranded install must never surface POSIMYTH-branded UI AND must never phone
 * api.posimyth.com.
 *
 * Testable BEFORE the tracker boots — that ordering is the whole point. In the Nexter Blocks
 * integration this check originally sat below the tracker's init(), which hid the UI but left the
 * activate / deactivate / heartbeat pings running on a rebranded site whose consent was already on.
 *
 * Only the REBRANDING fields count. `theplus_white_label` is not a rebranding-only option: it also
 * stores plain UI preferences written by the same dashboard screen — help_link, plugin_news,
 * plugin_ads, template_tab, licence_tab, rollback_tab, tp_white_label_hidden, tp_hidden_label — all
 * of which hold 'on' when ticked. Treating any non-empty value as rebranding meant a Pro user who
 * merely ticked "Hide all Help Links?" was classed as white-labelled, which silently disabled the
 * tracker, the consent notice and the deactivation survey with nothing to indicate why.
 *
 * No Pro-constant gate: the stored values are the evidence and they outlive Pro, so deactivating Pro
 * on a rebranded install must not bring POSIMYTH branding back or resume the pings.
 *
 * @return bool
 */
function tpae_posimyth_is_white_labelled() {
	$tpae_wl = get_option( 'theplus_white_label' );
	if ( empty( $tpae_wl ) || ! is_array( $tpae_wl ) ) {
		return false;
	}

	/*
	 * The fields that actually rebrand the plugin, as written by TPAE Pro's white-label screen and
	 * read back by Tpaep_White_Label / theplus_white_label_option(). Keep this list in step with that
	 * screen: a new rebranding field added there must be added here too, or a rebranded install would
	 * start phoning home again.
	 */
	$tpae_wl_brand_keys = array(
		'tp_plugin_name',
		'tp_plugin_desc',
		'tp_author_name',
		'tp_author_uri',
		'tp_plus_logo',
	);

	foreach ( $tpae_wl_brand_keys as $tpae_wl_key ) {
		if ( ! isset( $tpae_wl[ $tpae_wl_key ] ) || ! is_scalar( $tpae_wl[ $tpae_wl_key ] ) ) {
			continue;
		}
		if ( '' !== trim( (string) $tpae_wl[ $tpae_wl_key ] ) ) {
			return true;
		}
	}

	return false;
}

add_action(
	'plugins_loaded',
	function () {
		// Nothing below may run on a rebranded install — tracker included.
		if ( tpae_posimyth_is_white_labelled() ) {
			return;
		}

		// Shared base already loaded by the SDK loader at priority 0; the subclass is ours alone.
		require_once L_THEPLUS_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpae.php';
		if ( ! class_exists( 'Posimyth_Tracker_TPAE' ) ) {
			return;
		}

		// Registers activate / deactivate / weekly-heartbeat hooks + cron (all consent-gated).
		Posimyth_Tracker_TPAE::init();

		if ( ! is_admin() ) {
			return;
		}

		/*
		 * Consent notice — on TPAE's OWN key and its OWN suite, not Nexter's.
		 *
		 * Nexter Extension and Nexter Blocks share one answer because they are one brand with one
		 * dashboard; answering once there is what a user expects. The Plus Addons for Elementor is a
		 * separate product, so it asks separately and stores separately. A site running both will see
		 * two notices, one per product — that is the intent, not a bug: consenting to share Nexter data
		 * is not consenting to share TPAE data.
		 *
		 * suite_key `tpae_suite` gives it its own answer flag, its own Dismiss snooze and its own
		 * post-install quiet period. The SDK's contract check requires everyone under one suite_key to
		 * pass the same opt_in_option — TPAE is the only member of this one, so that holds.
		 *
		 * The constructor registers its own hooks; nothing else needs the instance.
		 *
		 * Guarded on class_exists, like the tracker above. The SDK loader requires the three shared
		 * files as a set, gated on Posimyth_Tracker_Base alone — and the TPAE subclass self-requires
		 * that base. So any path that defines the base without the loader having run the full set
		 * (a sibling requiring it directly, or an older SDK revision) leaves these two classes
		 * undefined, and an unguarded `new` would fatal on every admin page load.
		 */
		if ( class_exists( 'Posimyth_Consent_Notice' ) ) {
			new Posimyth_Consent_Notice(
				array(
					'plugin_name'      => 'The Plus Addons for Elementor',
					'plugin_slug'      => 'the-plus-addons-for-elementor-page-builder',
					'opt_in_option'    => 'posimyth_tpae_share_analytics',
					'ajax_action'      => 'posimyth_consent_tpae',
					'installed_option' => 'posimyth_tpae_first_use_at',
					'tracker_cb'       => array( 'Posimyth_Tracker_TPAE', 'send_first_ping' ),
					// The SDK's suite_name default is 'Nexter', and it is what the notice prints instead
					// of plugin_name once more than one product registers. Unset, a site running TPAE next
					// to a Nexter product showed TPAE's own notice as "Help make Nexter faster and more
					// stable" while storing consent under posimyth_tpae_share_analytics. TPAE is its own
					// suite of one, so its suite name is simply its own name.
					'suite_name'       => 'The Plus Addons for Elementor',
					// TPAE's own docs, not nexterwp.com — a Nexter link here would send TPAE users to the
					// wrong product's documentation. L_THEPLUS_TPDOC is the docs root the plugin already
					// uses elsewhere; a dedicated data-sharing page under it still has to be published.
					// The data-sharing page itself, not the docs root — "See what's shared" has to
					// land on the page that answers that question. UTM matches the shape the SDK
					// uses for its own default (wpbackend / admin / datasharingnotice), so this
					// notice is distinguishable from the other places the docs are linked.
					'docs_url'         => L_THEPLUS_TPDOC . 'data-sharing/?utm_source=wpbackend&utm_medium=admin&utm_campaign=datasharingnotice',
					'suite_key'        => 'tpae_suite',
					// Legacy hook only, kept so any host rule targeting `.tpae-notice-wrap` still matches.
					// The SDK stylesheet itself keys off the stable `posi-*` classes.
					'css_prefix'       => 'tpae',
					// TPAE's own purple, passed rather than baked into the shared stylesheet. One copy of
					// the notice class serves every active POSIMYTH plugin and its stylesheet is printed
					// once for the whole page, so a literal colour in there painted EVERY product's notice
					// — that is how this purple ended up on Sticky Header Effects' notice. As an inline
					// --posi-accent on this instance's markup it is scoped to TPAE's notice alone.
					'accent'           => '#6660EF',
				)
			);
		}

		/*
		 * "Why are you leaving?" survey. Submitting with a reason is itself the consent for that one
		 * submission; Skip sends nothing. Its own ajax action and slug, so the hub records this churn
		 * against TPAE rather than a sibling.
		 *
		 * This is now the ONLY thing bound to the Deactivate link. TPAE's legacy dialog
		 * (includes/user-experience/class-tp-deactivate-feedback.php, posting to the tpae/v2 endpoints)
		 * was removed — two handlers on that one link stacked two dialogs, which is the bug Nexter
		 * Extension shipped until its own legacy popup was deleted. Do not reintroduce a second one.
		 *
		 * Guarded on class_exists for the same reason as the consent notice above.
		 */
		if ( class_exists( 'Posimyth_Deactivation_Survey' ) ) {
			/*
			 * The argument set moved out to its own file: the eight reason icons are inline SVG and ran
			 * to roughly 8 KB, which buried this one call in the middle of the main plugin file. Required
			 * here rather than at the top so a front-end request never compiles it — this whole block is
			 * already behind the is_admin() gate above.
			 */
			require_once L_THEPLUS_PATH . 'includes/user-experience/class-tp-deactivate-survey.php';

			new Posimyth_Deactivation_Survey( \Theplus\UserExperience\Tp_Deactivate_Survey::args() );
		}
	}
);

/**
 * Analytics activation ping.
 *
 * Must run from the activation hook, NOT the SDK's `activated_plugin` hook: during a plugin's own
 * activation request WordPress fires `plugins_loaded` before it includes this file, so the callback
 * above never ran and nothing is listening when `activated_plugin` fires. Consent-gated inside
 * on_self_activate(), so a fresh install still sends nothing.
 */
register_activation_hook(
	L_THEPLUS_FILE,
	function () {
		// Rebranded installs must not phone home from here either — this runs during our own
		// activation request, before the plugins_loaded gate has had a chance to short-circuit.
		if ( tpae_posimyth_is_white_labelled() ) {
			return;
		}
		/*
		 * The plugins_loaded callback never ran in this request, so load the subclass here; the loader
		 * already loaded the shared base immediately (its did_action branch) on include.
		 *
		 * file_exists first, because this hook can fire against a directory that is no longer ours.
		 * A dashboard rollback calls activate_plugin() AFTER Plugin_Upgrader has replaced this folder
		 * with the target version, and every version the rollback screen offers predates
		 * includes/posimyth-sdk/ — the SDK arrived in 6.4.18. A bare require_once then fatally errored
		 * mid-AJAX: the response became an HTML error page instead of JSON, so the dashboard read
		 * data.success as undefined, and activation never finished, leaving the plugin deactivated on
		 * a site whose files had in fact rolled back correctly.
		 */
		$tpae_tracker_file = L_THEPLUS_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpae.php';
		if ( ! file_exists( $tpae_tracker_file ) ) {
			return;
		}

		require_once $tpae_tracker_file;
		if ( class_exists( 'Posimyth_Tracker_TPAE' ) ) {
			Posimyth_Tracker_TPAE::on_self_activate();
		}
	}
);

/**
 * Remove the weekly heartbeat schedule on deactivation.
 *
 * Without this the cron event stays registered in WordPress permanently after the plugin is gone.
 */
register_deactivation_hook(
	L_THEPLUS_FILE,
	function () {
		require_once L_THEPLUS_PATH . 'includes/posimyth-sdk/class-posimyth-tracker-tpae.php';
		if ( class_exists( 'Posimyth_Tracker_TPAE' ) && method_exists( 'Posimyth_Tracker_TPAE', 'unschedule' ) ) {
			Posimyth_Tracker_TPAE::unschedule();
		}
	}
);
