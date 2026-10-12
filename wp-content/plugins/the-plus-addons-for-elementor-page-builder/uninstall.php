<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * On multisite the opt-in data wipe runs once per site (see the switch_to_blog
 * loop below), so each subsite's own options, postmeta and generated cache
 * directory are resolved and removed - not just the main site's. The analytics /
 * consent purge at the bottom runs once, because its consent state is stored as
 * network-wide site options.
 *
 * @link        https://posimyth.com/
 * @since       5.6.6
 *
 * @package     the-plus-addons-for-elementor-page-builder
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

if ( ! function_exists( 'theplus_free_uninstall_site' ) ) {

	/**
	 * Remove Free-owned, opt-in data for the current site.
	 *
	 * Relies on the calling context (single-site, or each switch_to_blog() below)
	 * to have the right site active, so get_option(), $wpdb->options /
	 * $wpdb->postmeta and wp_upload_dir() all resolve to that site.
	 *
	 * @since 6.5.0
	 */
	function theplus_free_uninstall_site() {

		$theplus_options = get_option( 'theplus_api_connection_data' );
		$remove_db = ! empty( $theplus_options['plus_remove_db'] ) ? $theplus_options['plus_remove_db'] : '';

		if( 'enable' === $remove_db ) {
			$remove_db_promotion = ! empty( $theplus_options['tpae_db_promotion'] ) ? $theplus_options['tpae_db_promotion'] : '';
			$remove_db_alldata   = ! empty( $theplus_options['tpae_db_alldata'] ) ? $theplus_options['tpae_db_alldata'] : '';

			if( 'enable' === $remove_db_promotion ) {
				delete_option('tp-rateus-notice');
				delete_option('tp_wdkit_preview_popup');
				delete_option('tp_editor_onbording_popup');
				delete_option('tpae_uichemy_notice_dismissed');
			}

			if( 'enable' === $remove_db_alldata ) {
				delete_option('tp_key_random_generate');
				delete_option('tpaep_licence_data');

				/* Pro stores this with set_transient(), so delete_option() alone never matched it. */
				delete_option('tpaep_licence_time_data');
				delete_transient('tpaep_licence_time_data');

				delete_option('tpae_backend_cache');
				delete_option('theplus_performance');
				delete_option('theplus_options');
				delete_option('theplus_api_connection_data');
				delete_option('theplus_styling_data');
				delete_option('tp_dynamic_tag_seen');
				delete_option('tpae_dynamictag_notice_dismissed');

				// Pro
				delete_option('theplus_activation_redirect');
				delete_option('theplus_white_label');

				/*
				 * Written by Free but never removed until now (#756). Verified by
				 * grepping for a writer before adding each one -- two other keys
				 * that were sitting commented out at the bottom of this file
				 * (default_plus_options, on_first_load_cache) have NO writer left
				 * anywhere in either plugin, so they are not reinstated here.
				 * tpae_version_cache, the third key in that same commented-out
				 * block, is NOT in that "no writer" group -- it already has its own
				 * pre-existing delete_option() call further down this file, so it
				 * was already being removed before this comment was written.
				 */
				delete_option('theplus_widgets_settings');
				delete_option('tpae_menu_notification');
				delete_option('tpae_onboarding_time');
				delete_option('tpae_onboarding_version');
				delete_option('tpae_whats_new_notification');
				delete_option('tp_update_popup_dismiss');
				delete_option('tpae_pluginfeatures_notice_dismissed');

				/*
				 * ARCH-003 (#768): the three CPTs this plugin registers -- clients,
				 * testimonials, team members (class-tpae-dashboard-listing.php:191,
				 * 335,483) -- were never purged here, even under this full opt-in
				 * "remove all data" flag. Each is only registered under a post_type
				 * status the site owner opted into (empty/'disable' = never
				 * registered), and each slug can be renamed via post_type_options
				 * (the *_plugin_name fields) -- so read that option BEFORE deleting
				 * it below, and fall back to the plugin's own defaults only when a
				 * site never customised the name. wp_delete_post( , true ) is used
				 * (not a raw query) so WP core also cleans up each post's own
				 * postmeta correctly, including any that isn't covered by the
				 * targeted DELETE two lines below.
				 */
				$tpae_cpt_options = get_option( 'post_type_options' );
				$tpae_cpt_slugs   = array(
					'client_plugin_name'      => 'theplus_clients',
					'testimonial_plugin_name' => 'theplus_testimonial',
					'team_member_plugin_name' => 'theplus_team_member',
				);

				foreach ( $tpae_cpt_slugs as $tpae_cpt_field => $tpae_cpt_default ) {
					$tpae_cpt_slug = ! empty( $tpae_cpt_options[ $tpae_cpt_field ] ) ? $tpae_cpt_options[ $tpae_cpt_field ] : $tpae_cpt_default;
					$tpae_cpt_slug = sanitize_key( $tpae_cpt_slug );

					// The slug is a site-configurable option field; a value of a WP core
					// post type (page, post, attachment, ...) would purge core content
					// instead of this plugin's own CPT posts. Not reachable with any of
					// the plugin's own defaults, but one line is cheap insurance.
					if ( in_array( $tpae_cpt_slug, get_post_types( array( '_builtin' => true ) ), true ) ) {
						continue;
					}

					$tpae_cpt_post_ids = get_posts(
						array(
							'post_type'      => $tpae_cpt_slug,
							'post_status'    => 'any',
							'numberposts'    => -1,
							'fields'         => 'ids',
							'no_found_rows'  => true,
						)
					);

					foreach ( $tpae_cpt_post_ids as $tpae_cpt_post_id ) {
						wp_delete_post( $tpae_cpt_post_id, true );
					}
				}

				/*
				 * post_type_options is genuinely unprefixed (not a tpae_/theplus_
				 * name), and Pro reads it directly -- includes/plus_addon.php:69,233
				 * in the Pro repo, which even registers its own
				 * delete_option_post_type_options cache-invalidation hook, confirming
				 * Pro treats it as live, actively-used data. A site can have Free
				 * uninstalled with this full-data-removal flag while Pro stays
				 * active (a common configuration, not an edge case) -- deleting this
				 * option here would silently blank Pro's CPT slug configuration
				 * (team member, testimonial, client post types) out from under it.
				 * An earlier commit here added this delete_option() call (it had
				 * previously been commented out deliberately); reverted back to not
				 * deleting it, since the risk of breaking a co-installed Pro site is
				 * worse than leaving one generic-named option behind after Free's own
				 * uninstall. Left as a genuinely open question the option's own name
				 * should be prefixed to a tpae_-owned key (a data migration, not a
				 * one-line fix) rather than deleted.
				 */

				// Bulk-delete TPAE-owned transients. Each prefix below is unambiguously
				// TPAE — using specific prefixes (not just 'tp_') to avoid collision
				// with other plugins. Underscores in keys are escaped (\\_) so MySQL
				// LIKE treats them as literal characters, not single-char wildcards.
				global $wpdb;
				$wpdb->query(
					"DELETE FROM {$wpdb->options} WHERE
						option_name LIKE '\\_transient\\_tp\\_chart\\_api\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_chart\\_api\\_%'
						OR option_name LIKE '\\_transient\\_tp\\_table\\_api\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_table\\_api\\_%'
						OR option_name LIKE '\\_transient\\_tp\\_draw\\_svg\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_draw\\_svg\\_%'
						OR option_name LIKE '\\_transient\\_tp\\_gmap\\_geocode\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_gmap\\_geocode\\_%'
						OR option_name LIKE '\\_transient\\_tp\\_lottie\\_json\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_lottie\\_json\\_%'
						OR option_name LIKE '\\_transient\\_tp\\_review\\_api\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tp\\_review\\_api\\_%'
						OR option_name LIKE '\\_transient\\_tpae\\_rollback\\_version\\_%' OR option_name LIKE '\\_transient\\_timeout\\_tpae\\_rollback\\_version\\_%'
						OR option_name = '_transient_tp_dashboard_overview' OR option_name = '_transient_timeout_tp_dashboard_overview'
						OR option_name = '_transient_theplus_verify_trans_api_store' OR option_name = '_transient_timeout_theplus_verify_trans_api_store'
						OR option_name = '_transient_theplus_verify_trans_licence' OR option_name = '_transient_timeout_theplus_verify_trans_licence'"
				);

				/*
				 * theplus-term-* is one option row per term (plus-generator.php:269,
				 * :392), so the orphan count scales with the site's taxonomy, not
				 * with a fixed list -- a large site is left with hundreds. These are
				 * real option rows, not transients, so the bulk transient query
				 * above cannot reach them. Underscores are not escaped here because
				 * the key uses hyphens; the '-' is a literal in LIKE.
				 */
				$wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", 'theplus-term-%' ) );

				// Cache-control markers (safe to remove on full uninstall).
				delete_option( 'tp_save_update_at' );
				delete_option( 'tpae_version_cache' );
				delete_option( 'tpae_version_active' );

				// Remove per-post widget-detection meta (written on every Elementor page)
				// + Pro form-submission meta.
				$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE meta_key IN ( 'tp_widgets', 'tpaep_submission_id' )" );

				// Remove the generated CSS/JS cache directory (uploads/theplus-addons).
				// L_THEPLUS_ASSET_PATH is NOT defined during uninstall, so derive the path
				// from wp_upload_dir() and delete recursively via WP_Filesystem.
				$tpae_upload    = wp_upload_dir();
				$tpae_cache_dir = trailingslashit( $tpae_upload['basedir'] ) . 'theplus-addons';
				if ( is_dir( $tpae_cache_dir ) ) {
					require_once ABSPATH . 'wp-admin/includes/file.php';
					WP_Filesystem();
					global $wp_filesystem;
					if ( $wp_filesystem && $wp_filesystem->is_dir( $tpae_cache_dir ) ) {
						$wp_filesystem->rmdir( $tpae_cache_dir, true );
					}
				}
			}

			delete_option('plusextra-v6.2.6');
			delete_option('tpae_onbording_end');
			delete_option('theplus_verified');
			delete_option('theplus_purchase_code');
			delete_transient('theplus_verify_trans_api_store');

			delete_option( 'tpae_nxt_ext_pnotice' );
			delete_option( 'tpae_nexter_extension_notice' );
			delete_option( 'tpae_nexter_block_notice' );

			delete_option( 'tpae_pro_promo_notice' );
			delete_option( 'tpae_activate_license_notice' );
			delete_option( 'tpae_expired_license_month_notice' );
			delete_option( 'tpae_expired_license_week_notice' );
			delete_option( 'tpae_expired_license_notice' );
			delete_option( 'tpae_review_show_later' );
			delete_option( 'tpae_ask_review_notice' );
			delete_option( 'tpae_join_community_notice' );
			delete_option( 'tpae_removed_widgets_notice' );
			delete_option( 'tpae_whats_new_dismissed' );
			delete_option( 'tpae_whats_new_seen' );
			delete_option( 'tpae_widget_recipes_notice' );
			delete_option( 'tpae_widget_recipes_since' );
			delete_option( 'tpae_install_time' );

			/*
			 * The deletes above include raw DELETE queries against wp_options,
			 * wp_postmeta and wp_usermeta, which bypass the object cache. On a site
			 * with a persistent cache (Redis, Memcached) the `alloptions` entry can
			 * outlive the DELETE and keep serving values that no longer exist in the
			 * database. Flush once, after all of them (#756).
			 */
			wp_cache_flush();

		}
	}
}

/*
 * Multisite: run the opt-in wipe for every site so each subsite's options,
 * postmeta and cache directory are removed, not just the main site's. On
 * single-site, run it once.
 */
if ( is_multisite() ) {
	$theplus_free_site_ids = get_sites( array( 'fields' => 'ids', 'number' => 0 ) );

	foreach ( $theplus_free_site_ids as $theplus_free_blog_id ) {
		switch_to_blog( $theplus_free_blog_id );
		theplus_free_uninstall_site();
		restore_current_blog();
	}
} else {
	theplus_free_uninstall_site();
}

/*
 * Analytics / consent state — cleared UNCONDITIONALLY, outside the plus_remove_db gate above.
 *
 * That gate exists so a delete does not throw away someone's configuration unless they asked for it,
 * and that is right for settings. This is not settings: it is the record of an answer to a question we
 * asked. Deleting the plugin withdraws it, so a reinstall has to start from an unanswered state — if
 * the consent survived, a reinstalled TPAE would silently resume reporting on an old yes and would
 * never ask again. Leaving it behind the gate would mean that happens for every site that never turned
 * the flag on, which is nearly all of them.
 *
 * Runs once, not per-site: the consent is stored as network-wide site options (delete_site_option),
 * so a single purge covers the whole network.
 *
 * No sibling check here, unlike Nexter Extension's and Nexter Blocks' uninstall scripts. Those three
 * share one consent under `nexter_suite`, so they must not clear it while another member is still
 * installed. TPAE deliberately has its OWN key and its own suite (see the notice config in
 * theplus_elementor_addon.php) and is the only member, so there is nothing to preserve for anyone else.
 */
$tpae_sdk_base = __DIR__ . '/includes/posimyth-sdk/class-posimyth-tracker-base.php';
$tpae_tracker  = __DIR__ . '/includes/posimyth-sdk/class-posimyth-tracker-tpae.php';

if ( file_exists( $tpae_sdk_base ) && file_exists( $tpae_tracker ) ) {
	require_once $tpae_sdk_base;
	require_once $tpae_tracker;
}

// method_exists too, not only class_exists: an active POSIMYTH sibling loads before uninstall.php runs,
// so an OLDER copy of Posimyth_Tracker_Base may already be defined without purge_state() — our subclass
// then extends that copy, and calling the missing method would fatal mid-uninstall.
if ( class_exists( 'Posimyth_Tracker_TPAE' ) && method_exists( 'Posimyth_Tracker_TPAE', 'purge_state' ) ) {
	Posimyth_Tracker_TPAE::purge_state( true, 'tpae_suite' );
} else {
	// Fall back to clearing by name, so a broken or partial install still cleans up after itself.
	wp_clear_scheduled_hook( 'posimyth_heartbeat_tpae' );

	delete_option( 'posimyth_tpae_install_time' );
	delete_option( 'posimyth_tpae_usage' );
	delete_option( 'posimyth_tpae_first_use_at' );
	delete_option( 'posimyth_tpae_activate_reported' );
	delete_transient( 'posimyth_tpae_deact_reported' );

	// Site options first (that is how they are written), then the legacy per-blog shape.
	delete_site_option( 'posimyth_tpae_share_analytics' );
	delete_site_option( 'posi_consent_dismissed_tpae_suite' );
	delete_site_option( 'posi_consent_snoozed_until_tpae_suite' );
	delete_site_option( 'posi_consent_grace_start_tpae_suite' );
	delete_option( 'posimyth_tpae_share_analytics' );
	delete_option( 'posi_consent_dismissed_tpae_suite' );
	delete_option( 'posi_consent_snoozed_until_tpae_suite' );
	delete_option( 'posi_consent_grace_start_tpae_suite' );
}
