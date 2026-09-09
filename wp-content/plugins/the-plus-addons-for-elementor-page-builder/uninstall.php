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

				// Cache-control markers (safe to remove on full uninstall).
				delete_option( 'tp_save_update_at' );
				delete_option( 'tpae_version_cache' );

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

			// if ( file_exists( L_THEPLUS_ASSET_PATH . '/theplus.min.css' ) ) {
			// 	wp_delete_file( L_THEPLUS_ASSET_PATH . DIRECTORY_SEPARATOR . '/theplus.min.css' );
			// }
			// if ( file_exists( L_THEPLUS_ASSET_PATH . '/theplus.min.js' ) ) {
			// 	wp_delete_file( L_THEPLUS_ASSET_PATH . DIRECTORY_SEPARATOR . '/theplus.min.js' );
			// }
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

// delete_option('default_plus_options');

// delete_option('post_type_options');
// delete_option('on_first_load_cache');

// delete_option('tp_save_update_at');
// delete_option('tpae_version_cache');
