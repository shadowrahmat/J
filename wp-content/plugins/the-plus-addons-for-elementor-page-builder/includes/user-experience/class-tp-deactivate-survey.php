<?php
/**
 * Configuration for the deactivation survey dialog.
 *
 * Lives in its own file purely for the main plugin file's sake: the eight reason icons are inline
 * SVG and account for roughly 8 KB on their own, which made theplus_elementor_addon.php hard to
 * read for what is a single call to the SDK. Nothing here runs on a front-end request — the caller
 * is already behind the `is_admin()` gate in that file, and this file is only required once the
 * SDK's Posimyth_Deactivation_Survey class is known to exist.
 *
 * @link       https://posimyth.com/
 * @since      6.4.18
 *
 * @package    Theplus
 * @subpackage ThePlus/UserExperience
 */

namespace Theplus\UserExperience;

/**
 * Exit if accessed directly.
 * */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Tp_Deactivate_Survey' ) ) {

	/**
	 * Supplies the arguments TPAE passes to the shared deactivation survey.
	 *
	 * @since 6.4.18
	 */
	class Tp_Deactivate_Survey {

		/**
		 * The config array for Posimyth_Deactivation_Survey.
		 *
		 * Kept as a method rather than a constant because the labels are translated and the logo is
		 * built from L_THEPLUS_URL, neither of which is available at compile time.
		 *
		 * @since 6.4.18
		 *
		 * @return array
		 */
		public static function args(): array {
			return
			array(
				'plugin_name'   => 'The Plus Addons for Elementor',
				'plugin_slug'   => 'the-plus-addons-for-elementor-page-builder',
				// Matched against the Deactivate link's href, which is locale-proof — unlike the row's
				// data-slug, which WordPress builds from the TRANSLATED plugin name.
				'plugin_file'   => L_THEPLUS_PBNAME,
				'ajax_action'   => 'posimyth_tpae_deact',
				// TPAE's own consent key — see the notice above.
				'opt_in_option' => 'posimyth_tpae_share_analytics',
				'tracker_cb'    => array( 'Posimyth_Tracker_TPAE', 'do_request' ),
				// TPAE's own mark and accent. The SDK defaults to Nexter's, so without these a
				// user deactivating The Plus Addons for Elementor was shown a Nexter logo in
				// Nexter blue — another product's branding, on this product's dialog.
				//
				// The logo is referenced by URL rather than inlined as SVG: this is the mark the
				// dashboard and the .org listing already use, and the file is ~16 KB, which would
				// be a lot to embed on every Plugins-screen load for a 22px image.
				'logo_html'     => '<img src="' . esc_url( L_THEPLUS_URL . 'assets/images/products/theplus-product.png' ) . '" width="22" height="22" alt="" />',
				// The reason set and icons TPAE showed before this SDK existed, restored here so the SDK
				// renders the familiar card layout rather than a plain list. Slugs are new: the old form
				// submitted the LABEL as its value ('Just Debugging'), which the hub cannot group and the
				// SDK's allowlist would reject outright. Labels are unchanged, so nothing looks different.
				//
				// A closure, not the array itself. args() is evaluated at `plugins_loaded`, and every label
				// below is an esc_html__() call — running them there is what raised WP 6.7's "translation
				// loading was triggered too early" _doing_it_wrong notice for the tpebl domain on every
				// admin page load. The SDK accepts a callable and resolves it only when it renders the
				// dialog or handles its submit, both long after `init`. Do not inline this back.
				'reasons'       => static function () {
					return array(
						'just-debugging' => array(
							'label' => esc_html__( 'Just Debugging', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><g stroke="#6660EF" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.667" clip-path="url(#a)"><path d="M10 18.333a8.333 8.333 0 1 0 0-16.667 8.333 8.333 0 0 0 0 16.667ZM8.333 12.5v-5M11.667 12.5v-5"/></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h20v20H0z"/></clipPath></defs></svg>',
						),
						'plugin-issues' => array(
							'label' => esc_html__( 'Plugin Issues', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M10.179 2.771a3.601 3.601 0 0 1 3.42 3.596l.113.007a.9.9 0 0 1 .273.08l2.73-1.745.08-.046a.9.9 0 0 1 .89 1.562L14.97 7.961c.244.623.391 1.283.428 1.956l.002.05h2.7l.092.004a.9.9 0 0 1 0 1.791l-.092.005h-2.7v.9l-.006.268a5.405 5.405 0 0 1-.172 1.103l2.44 1.457.076.05a.9.9 0 0 1-.918 1.537l-.082-.042-2.264-1.353a5.402 5.402 0 0 1-8.95.001L3.261 17.04l-.461-.773-.462-.772 2.44-1.457a5.403 5.403 0 0 1-.178-1.372v-.899H1.9a.901.901 0 0 1 0-1.8h2.7v-.05l.038-.42a6.301 6.301 0 0 1 .391-1.536L2.314 6.225l-.075-.054a.9.9 0 0 1 1.045-1.463l2.73 1.747a.9.9 0 0 1 .274-.081l.111-.007A3.602 3.602 0 0 1 10 2.767l.179.004ZM3.26 17.04a.9.9 0 0 1-.923-1.545l.923 1.545Zm3.652-8.873a4.499 4.499 0 0 0-.514 1.837v2.662a3.602 3.602 0 0 0 2.7 3.486v-4.385a.9.9 0 0 1 1.8 0v4.385a3.602 3.602 0 0 0 2.697-3.307l.004-.179V9.995a4.496 4.496 0 0 0-.514-1.829H6.913ZM10 4.566a1.802 1.802 0 0 0-1.8 1.8h3.6l-.009-.178a1.8 1.8 0 0 0-1.613-1.613L10 4.566Z"/></svg>',
						),
						'slow-performance' => array(
							'label' => esc_html__( 'Slow Performance', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M2.8 10.931c0 1.99.806 3.79 2.109 5.091l-1.272 1.272A8.972 8.972 0 0 1 1 10.931a9 9 0 0 1 9-9 9 9 0 0 1 6.364 15.364l-1.273-1.273A7.2 7.2 0 1 0 2.8 10.932Zm4.236-4.236 4.05 4.05-1.272 1.272-4.05-4.05 1.272-1.272Z"/></svg>',
						),
						'switched-alternative' => array(
							'label' => esc_html__( 'Switched to Alternative', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M5.532 9.195a.809.809 0 0 1 0 1.61l-.083.003H3.252a5.58 5.58 0 0 0 6.222 2.772l.352-.097a5.562 5.562 0 0 0 3.681-3.716.81.81 0 0 1 1.55.465 7.185 7.185 0 0 1-1.265 2.415l4.97 4.972.056.061a.81.81 0 0 1-1.137 1.14l-.062-.056-4.972-4.973a7.183 7.183 0 0 1-2.794 1.361v.001a7.199 7.199 0 0 1-7.236-2.406v.893a.808.808 0 1 1-1.617 0V10l.004-.083a.809.809 0 0 1 .805-.726h3.64l.083.004ZM6.506 1.2a7.199 7.199 0 0 1 5.084.646 7.196 7.196 0 0 1 2.151 1.76V2.72a.81.81 0 0 1 1.619 0v3.64a.81.81 0 0 1-.81.809h-3.64a.809.809 0 0 1 0-1.617h2.201a5.583 5.583 0 0 0-6.226-2.78h-.002a5.565 5.565 0 0 0-3.919 3.474l-.115.346a.81.81 0 0 1-1.551-.463l.071-.225a7.18 7.18 0 0 1 5.137-4.705v.001Z"/></svg>',
						),
						'no-longer-needed' => array(
							'label' => esc_html__( 'No Longer Needed', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M16.566 1.914a2.7 2.7 0 0 1 1.643 4.595c-.287.287-.633.5-1.009.633v8.259a2.701 2.701 0 0 1-2.7 2.7h-9a2.704 2.704 0 0 1-2.688-2.433L2.8 15.4V7.143a2.7 2.7 0 0 1-1.01-.634 2.701 2.701 0 0 1-.777-1.641L.999 4.6a2.702 2.702 0 0 1 2.7-2.7h12.6l.267.014ZM4.6 15.4l.004.089a.903.903 0 0 0 .896.811h9a.903.903 0 0 0 .9-.9V7.3H4.6v8.1Zm7.292-6.296a.9.9 0 0 1 0 1.791l-.092.005H8.2a.9.9 0 0 1 0-1.8h3.6l.092.004ZM3.699 3.701a.9.9 0 0 0-.9.9l.005.088a.902.902 0 0 0 .895.811h12.6l.09-.004A.901.901 0 0 0 17.2 4.6a.9.9 0 0 0-.811-.895l-.09-.004H3.7Z"/></svg>',
						),
						'compatibility-issues' => array(
							'label' => esc_html__( 'Compatibility Issues', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" fill-rule="evenodd" d="M19 10a9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 9-9 9 9 0 0 1 9 9Zm-9 7.2a7.2 7.2 0 1 0 0-14.4 7.2 7.2 0 0 0 0 14.4Z" clip-rule="evenodd"/><path fill="#6660EF" fill-rule="evenodd" d="M16.036 4.414a.9.9 0 0 1 0 1.272l-10.35 10.35a.9.9 0 0 1-1.272-1.272l10.35-10.35a.9.9 0 0 1 1.272 0Z" clip-rule="evenodd"/></svg>',
						),
						'missing-feature' => array(
							'label' => esc_html__( 'Missing Feature', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M17.363 10a1.158 1.158 0 0 0-.263-.734l-.075-.084-1.377-1.376a1.636 1.636 0 0 1 .774-2.749l.157-.048a1.23 1.23 0 0 0 .408-.26l.11-.12a1.228 1.228 0 1 0-2.105-1.207l-.049.155a1.638 1.638 0 0 1-2.585.919l-.164-.143-1.376-1.377a1.156 1.156 0 0 0-1.551-.077l-.085.077-1.378 1.376h.001l.184.05A2.864 2.864 0 1 1 4.404 7.99l-.051-.184-1.378 1.377a1.158 1.158 0 0 0-.338.818l.006.114a1.157 1.157 0 0 0 .331.703h.001l1.377 1.377.144.163a1.636 1.636 0 0 1-.92 2.585h.001a1.228 1.228 0 0 0-.024 2.381 1.228 1.228 0 0 0 1.504-.9 1.637 1.637 0 0 1 2.748-.775l1.377 1.376.085.077a1.16 1.16 0 0 0 .733.262l.113-.005a1.16 1.16 0 0 0 .705-.334l1.377-1.376a2.865 2.865 0 0 1-2.103-3.508 2.862 2.862 0 0 1 3.547-2.033 2.867 2.867 0 0 1 1.957 1.904l.05.183v.001h.002l1.377-1.377.075-.084a1.16 1.16 0 0 0 .263-.734ZM19 10a2.795 2.795 0 0 1-.634 1.771l-.185.204-1.377 1.375.001.001a1.638 1.638 0 0 1-2.75-.775v-.001a1.227 1.227 0 1 0-1.479 1.482l.207.064a1.637 1.637 0 0 1 .712 2.52l-.143.165-1.377 1.375a2.793 2.793 0 0 1-1.7.805l-.275.013a2.793 2.793 0 0 1-1.772-.633l-.203-.184-1.377-1.377v-.001a2.864 2.864 0 1 1-3.636-3.402l.184-.05-1.377-1.376v-.001a2.793 2.793 0 0 1-.805-1.701L1 10a2.793 2.793 0 0 1 .82-1.975l1.376-1.377a1.638 1.638 0 0 1 2.337.023c.202.21.344.47.411.753l.048.155a1.228 1.228 0 0 0 2.326-.776 1.227 1.227 0 0 0-.739-.81l-.155-.05a1.636 1.636 0 0 1-.776-2.748l1.377-1.376.203-.184a2.793 2.793 0 0 1 3.747.184l1.377 1.377.051-.185a2.864 2.864 0 1 1 4.85 2.78l-.133.138a2.863 2.863 0 0 1-1.132.67l-.184.05 1.377 1.376.185.203A2.797 2.797 0 0 1 19 10Z"/></svg>',
						),
						'other' => array(
							'label' => esc_html__( 'Other Reason', 'tpebl' ),
							'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20"><path fill="#6660EF" d="M10 1a9 9 0 0 1 9 9 9 9 0 0 1-9 9 9 9 0 0 1-9-9 9 9 0 0 1 9-9Zm0 1.8a7.2 7.2 0 1 0 0 14.4 7.2 7.2 0 0 0 0-14.4Zm0 10.8a.9.9 0 1 1 0 1.8.9.9 0 0 1 0-1.8Zm0-8.55a3.262 3.262 0 0 1 1.213 6.291.72.72 0 0 0-.274.18c-.04.046-.046.103-.045.163l.006.116a.9.9 0 0 1-1.794.105L9.1 11.8v-.225c0-1.038.837-1.66 1.444-1.904a1.463 1.463 0 1 0-2.006-1.358.9.9 0 1 1-1.8 0A3.262 3.262 0 0 1 10 5.05Z"/></svg>',
						),
					);
				},
				// TPAE purple. Passed rather than hardcoded in the SDK because that stylesheet is shared
				// by every active POSIMYTH product's dialog — a literal there put this colour on Sticky
				// Header Effects' dialog too.
				'accent'        => '#6660EF',
			)
			;
		}
	}
}
