<?php
/**
 * UiChemy Composer cross-promotion for The Plus Addons.
 *
 * One class, a few placements that point the user at UiChemy Composer to build their own design with
 * AI when no shipped style fits, then send them to UiChemy's AI Website Creator:
 *
 *  - Widget Style notice : an inline line under the widget's Style picker.
 *  - Widget section      : a "Build Your Own Design" section at the bottom of the Content tab.
 *  - No-widget-found card: shown in the widget search panel when a search returns nothing.
 *  - GSAP reverse hint   : shown in the shared Scroll Interactions panel once an animation is picked.
 *  - Admin notice        : a one-shot, dismissible notice on the main wp-admin screens.
 *  - The flow            : any "Build" button installs UiChemy (if needed), then opens UiChemy's own
 *                          AI Website Creator screen in wp-admin.
 *
 * Every build button opens UiChemy's AI Website Creator (it works on UiChemy's Free plan). The
 * "See how it works" links follow the placement: the admin notice links the AI Website Creator
 * docs, the widget placements link that widget's own Composer page, the widget-search card links the
 * "What is UiChemy Composer" page, and the GSAP hint links the "Build Your Own Widget Design" overview.
 *
 * The admin docs URL also switches the whole promotion: set the constant
 * TPAE_UICHEMY_COMPOSER_DOCS_URL or the `tpae_uichemy_composer_docs_url` filter to an empty value to
 * hide every placement.
 *
 * @link       https://posimyth.com/
 * @since      6.5.3
 *
 * @package    ThePlus
 */

use Elementor\Controls_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Tpae_UiChemy_Notice' ) ) {

	/**
	 * Registers the UiChemy Composer promotion across widgets, extensions and wp-admin.
	 *
	 * When Pro is active its own widget classes replace these Free widgets, so the widget/extension
	 * ways bail and Pro registers its own copy. The admin ways run from Free regardless.
	 *
	 * @since 6.5.3
	 */
	class Tpae_UiChemy_Notice {

		/**
		 * Option that records the one-shot admin notice has been dismissed.
		 *
		 * @since 6.5.3
		 * @var string
		 */
		const OPT_NOTICE_DISMISSED = 'tpae_uichemy_notice_dismissed';

		/**
		 * Nonce action for the dismiss AJAX call.
		 *
		 * @since 6.5.3
		 * @var string
		 */
		const NONCE = 'tpae-uichemy-notice';

		/**
		 * Widgets that get the inline line and the section.
		 *
		 * Per widget name:
		 *  - inline:  control id after which the inline notice is injected, or null to put it at the
		 *             end of the first Content section (Carousel Anything has no picker).
		 *  - section: the Content section that holds that control.
		 *  - last:    the last Content section; the "Build Your Own Design" section is added after it.
		 *  - docs:    slug of the widget's own "Build Your Own" page in the UiChemy Composer docs.
		 *
		 * @since 6.5.3
		 * @var array
		 */
		private static $widgets = array(
			'tp-blog-listout'        => array( 'inline' => 'style', 'section' => 'content_section', 'last' => 'extra_option_section', 'docs' => 'blog-post-listing' ),
			'tp-gallery-listout'     => array( 'inline' => 'style', 'section' => 'layout_content_section', 'last' => 'extra_option_section', 'docs' => 'gallery' ),
			'tp-testimonial-listout' => array( 'inline' => 'style', 'section' => 'content_section', 'last' => 'content_extra_options_section', 'docs' => 'testimonial' ),
			'tp-team-member-listout' => array( 'inline' => 'style', 'section' => 'content_section', 'last' => 'extra_option_section', 'docs' => 'team-member' ),
			'tp-pricing-table'       => array( 'inline' => 'pricing_table_style', 'section' => 'content_section', 'last' => 'ribbon_pin_section', 'docs' => 'pricing-table' ),
			'tp-info-box'            => array( 'inline' => 'main_style', 'section' => 'content_section', 'last' => 'content_section', 'docs' => 'info-box' ),
			'tp-carousel-anything'   => array( 'inline' => null, 'section' => 'content_section', 'last' => 'extra_opts_section', 'docs' => 'carousel-anything' ),
			'tp-process-steps'       => array( 'inline' => 'ps_style', 'section' => 'section_process_steps', 'last' => 'content_section', 'docs' => 'process-steps' ),
			'tp-accordion'           => array( 'inline' => 'accordion_type', 'section' => 'content_section', 'last' => 'extra_content_section', 'docs' => 'accordion' ),
			'tp-tabs-tours'          => array( 'inline' => 'tabs_type', 'section' => 'layout_section', 'last' => 'extraoption_section', 'docs' => 'tabs-tours' ),
			'tp-table'               => array( 'inline' => 'table_selection', 'section' => 'section_table', 'last' => 'section_advance_settings', 'docs' => 'table' ),
		);

		/**
		 * Default "See how it works" page: what the UiChemy AI Website Creator is and how it works.
		 *
		 * @since 6.5.3
		 * @var string
		 */
		const DOCS_URL = 'https://docs.uichemy.com/ai-website-creator/overview/what-is-ai-website-creator';

		/**
		 * The "See how it works" URL, or an empty string when the promotion is switched off.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		public static function get_docs_url(): string {

			$url = defined( 'TPAE_UICHEMY_COMPOSER_DOCS_URL' ) ? (string) constant( 'TPAE_UICHEMY_COMPOSER_DOCS_URL' ) : self::DOCS_URL;

			/**
			 * Filters the UiChemy docs URL behind every "See how it works" link. An empty value hides
			 * every placement.
			 *
			 * @since 6.5.3
			 *
			 * @param string $url Docs page URL.
			 */
			$url = apply_filters( 'tpae_uichemy_composer_docs_url', $url );

			return is_string( $url ) && '' !== $url ? esc_url_raw( $url, array( 'http', 'https' ) ) : '';
		}

		/**
		 * Base of the UiChemy Composer "Build Your Own Widget Design" docs.
		 *
		 * @since 6.5.3
		 * @var string
		 */
		const WIDGET_DOCS_BASE = 'https://docs.uichemy.com/uichemy-composer/widget-styles/';

		/**
		 * "What is UiChemy Composer" page, for placements that are not tied to one widget.
		 *
		 * @since 6.5.3
		 * @var string
		 */
		const COMPOSER_DOCS_URL = 'https://docs.uichemy.com/uichemy-composer/getting-started/what-is-uichemy-composer';

		/**
		 * The Composer docs page for a widget placement.
		 *
		 * A mapped widget gets its own "Build Your Own" page; anything else (the GSAP hint, the
		 * widget-search card) gets the overview of every widget page.
		 *
		 * @since 6.5.3
		 *
		 * @param string $widget Optional. Elementor widget name, e.g. tp-blog-listout.
		 *
		 * @return string
		 */
		public static function get_widget_docs_url( $widget = '' ): string {

			$url = ( is_string( $widget ) && isset( self::$widgets[ $widget ]['docs'] ) )
				? self::WIDGET_DOCS_BASE . 'populate-widgets/' . self::$widgets[ $widget ]['docs']
				: self::WIDGET_DOCS_BASE . 'overview';

			/**
			 * Filters the Composer docs URL used by a widget placement.
			 *
			 * @since 6.5.3
			 *
			 * @param string $url    Docs URL.
			 * @param string $widget Elementor widget name, empty for the non-widget placements.
			 */
			$url = apply_filters( 'tpae_uichemy_widget_docs_url', $url, $widget );

			return is_string( $url ) && '' !== $url ? esc_url_raw( $url, array( 'http', 'https' ) ) : self::get_docs_url();
		}

		/**
		 * Whether the editor placements should be added to the current request's control stack.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		private static function is_active(): bool {

			if ( '' === self::get_docs_url() || self::wl_hidden() ) {
				return false;
			}

			// Controls are only needed by the editor panel and its admin-ajax calls.
			return is_admin() || ( class_exists( '\Elementor\Plugin' ) && \Elementor\Plugin::$instance->editor->is_edit_mode() );
		}

		/**
		 * Whether promotions are suppressed by a white-label configuration.
		 *
		 * Same rule as tpae_wl_pluginads_enabled(): when white-label plugin ads are on, every
		 * UiChemy placement hides. The option is read directly because that helper lives in the
		 * TheplusAddons\Widgets namespace (and only loads with the widgets), so a global
		 * function_exists() check on it is always false.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		private static function wl_hidden(): bool {
			$whitelabel = get_option( 'theplus_white_label' );

			return is_array( $whitelabel ) && ! empty( $whitelabel['plugin_ads'] ) && 'on' === $whitelabel['plugin_ads'];
		}

		/**
		 * Whether UiChemy promotions may be shown at all: docs URL set and not white-labelled.
		 *
		 * Public so shared controls (e.g. the Need Help control) can gate their own link.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		public static function is_enabled(): bool {
			return '' !== self::get_docs_url() && ! self::wl_hidden();
		}

		/**
		 * The editor-panel notice markup: one italic line and a full-width button.
		 *
		 * @since 6.5.3
		 *
		 * @param string $widget    Optional. Elementor widget name, to link that widget's own guide.
		 * @param string $placement Optional. utm_content value: editor-style-picker, or editor-gsap-hint from the GSAP panel.
		 *
		 * @return string
		 */
		public static function get_html( $widget = '', $placement = 'editor-style-picker' ): string {

			$build = self::can_build()
				? sprintf(
					'<a class="tpae-uichemy-install" role="button" tabindex="0" data-tpae-uc-install="1" style="font-weight:600;color:#F5934A;text-decoration:none;cursor:pointer;">%s</a> <span style="color:#6d7882;">&middot;</span> ',
					self::build_label_short()
				)
				: '';

			return sprintf(
				'<p class="tpae-uichemy-note" style="margin:0;border-left:3px solid #FD6A35;padding-left:12px;color:#a4afb7;font-size:12px;line-height:1.5;">%1$s %2$s<a class="tpae-uichemy-docs-link" href="%3$s" target="_blank" rel="noopener noreferrer" style="color:#c3c8ce;text-decoration:none;">%4$s</a></p>',
				esc_html__( 'Can\'t find a style that fits? Describe the design you want and UiChemy writes it in real HTML, CSS and JavaScript.', 'tpebl' ),
				$build,
				esc_url( self::utm( self::get_widget_docs_url( $widget ), $placement ) ),
				esc_html__( 'See how it works', 'tpebl' )
			);
		}

		/**
		 * The section card: pill badge, heading, a line of copy and a full-width button.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		public static function get_section_html( $widget = '' ): string {

			$build = self::can_build()
				? sprintf(
					'<a class="tpae-uichemy-install" role="button" tabindex="0" data-tpae-uc-install="1" style="cursor:pointer;flex:1 1 auto;white-space:nowrap;box-sizing:border-box;display:flex;align-items:center;justify-content:center;text-align:center;background:#FD6A35;color:#fff;border:1px solid #FD6A35;border-radius:6px;padding:8px 12px;font-size:12.5px;font-weight:600;text-decoration:none;">%s</a>',
					self::build_label()
				)
				: '';

			return sprintf(
				'<div class="tpae-uichemy-box" style="border:1px solid rgba(253,106,53,.30);border-radius:8px;padding:16px;background:rgba(253,106,53,.07);">
					<span style="display:inline-block;font-size:11px;font-weight:600;line-height:1.4;color:#F5934A;background:rgba(253,106,53,.16);border-radius:20px;padding:3px 10px;">%1$s</span>
					<h4 style="margin:12px 0 6px;font-size:14px;font-weight:600;line-height:1.35;">%2$s</h4>
					<p style="margin:0 0 14px;font-size:12.5px;line-height:1.5;color:#a4afb7;">%3$s</p>
					<div style="display:flex;flex-wrap:wrap;gap:8px;">
						%4$s
						<a class="tpae-uichemy-docs" href="%5$s" target="_blank" rel="noopener noreferrer" style="flex:1 1 auto;white-space:nowrap;box-sizing:border-box;display:flex;align-items:center;justify-content:center;text-align:center;background:transparent;color:#F5934A;border:1px solid rgba(253,106,53,.5);border-radius:6px;padding:8px 12px;font-size:12.5px;font-weight:600;text-decoration:none;">%6$s</a>
					</div>
					<div class="tpae-uc-progress" style="display:none;margin-top:10px;font-size:12px;font-weight:500;color:#a4afb7;"></div>
				</div>',
				esc_html__( 'Powered by UiChemy', 'tpebl' ),
				esc_html__( 'Describe It. UiChemy Builds It.', 'tpebl' ),
				esc_html__( 'Describe this section, or your whole website, and UiChemy writes it as real, editable HTML, CSS and JavaScript, right in your builder.', 'tpebl' ),
				$build,
				esc_url( self::utm( self::get_widget_docs_url( $widget ), 'editor-build-your-own-panel' ) ),
				esc_html__( 'See how it works', 'tpebl' )
			);
		}

		/**
		 * Label of the primary button: install when UiChemy is missing, build once it is active.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function build_label(): string {
			if ( self::is_uichemy_active() ) {
				return esc_html__( 'Build with UiChemy', 'tpebl' );
			}

			return self::is_uichemy_installed()
				? esc_html__( 'Activate UiChemy & Build', 'tpebl' )
				: esc_html__( 'Install UiChemy & Build', 'tpebl' );
		}

		/**
		 * Short label for the inline Style-picker link.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function build_label_short(): string {
			if ( self::is_uichemy_active() ) {
				return esc_html__( 'Build with UiChemy', 'tpebl' );
			}

			return self::is_uichemy_installed()
				? esc_html__( 'Activate UiChemy', 'tpebl' )
				: esc_html__( 'Install UiChemy', 'tpebl' );
		}

		/**
		 * Whether the current user can use the build button: open UiChemy once it is active, or
		 * install and activate it when it is not. False when plugin installs are blocked
		 * (DISALLOW_FILE_MODS) or the role lacks the capability; then only the docs link shows.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		private static function can_build(): bool {
			// UiChemy's own screen needs manage_options in every state.
			if ( ! current_user_can( 'manage_options' ) ) {
				return false;
			}
			if ( self::is_uichemy_active() ) {
				return true;
			}
			// Already installed: activating only needs activate_plugins.
			if ( self::is_uichemy_installed() ) {
				return current_user_can( 'activate_plugins' );
			}

			return current_user_can( 'install_plugins' ) && current_user_can( 'activate_plugins' );
		}

		/**
		 * UiChemy's AI Website Creator screen in wp-admin. Its dashboard routes by hash.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function creator_url(): string {
			return admin_url( 'admin.php?page=uichemy' ) . '#/ai-website';
		}

		/**
		 * Hook every placement. Called once when the plugin loads.
		 *
		 * @since 6.5.3
		 */
		public static function init() {

			// Admin notice: renders on top (priority 0) of the main wp-admin screens.
			add_action( 'in_admin_header', array( __CLASS__, 'admin_notice' ), 0 );
			add_action( 'wp_ajax_tpae_uichemy_notice_dismiss', array( __CLASS__, 'ajax_notice_dismiss' ) );
			add_action( 'wp_ajax_tpae_uichemy_build', array( __CLASS__, 'ajax_build' ) );

			// The editor-side "Install UiChemy & Build" buttons are driven from the editor footer.
			add_action( 'elementor/editor/footer', array( __CLASS__, 'editor_footer' ) );

			// Editor placements (the line under the Style picker + the "Build Your Own Design"
			// section) are added directly from each Free widget's register_controls() by calling
			// self::add_widget_notices( $this ). When Pro is active it overrides those widget
			// classes, so the Free calls never run and Pro's own copy takes over.
		}

		/**
		 * Widgets Ways A + B - add both editor placements to a widget.
		 *
		 * Called directly from each Free widget's register_controls(): pass $this. Adds the inline
		 * line right after the widget's Style picker (Way A) and the "Build Your Own Design" section
		 * at the bottom of the Content tab (Way B). Renders nothing while the docs URL is unset.
		 *
		 * @since 6.5.3
		 *
		 * @param object $element Elementor widget (the widget calling this from register_controls).
		 */
		public static function add_widget_notices( $element ) {

			if ( ! self::is_active() ) {
				return;
			}

			$name   = $element->get_name();
			$anchor = isset( self::$widgets[ $name ]['inline'] ) ? self::$widgets[ $name ]['inline'] : null;

			// Way A - the inline line, injected right after the Style picker. Widgets without a Style
			// picker (e.g. Carousel Anything) skip it: with no open section and no injection point,
			// add_control() would wp_die and take the whole editor panel down.
			if ( null !== $anchor && $element->get_controls( $anchor ) ) {
				$element->start_injection(
					array(
						'of' => $anchor,
						'at' => 'after',
					)
				);

				$element->add_control(
					'tpae_uichemy_inline_notice',
					array(
						'type'            => Controls_Manager::RAW_HTML,
						'raw'             => self::get_html( $name ),
						'content_classes' => 'tpae-uichemy-inline',
					)
				);

				$element->end_injection();
			}

			// Way B - the section at the bottom of the Content tab.
			$element->start_controls_section(
				'tpae_uichemy_section',
				array(
					'label' => esc_html__( 'Build Your Own Design', 'tpebl' ),
					'tab'   => Controls_Manager::TAB_CONTENT,
				)
			);
			$element->add_control(
				'tpae_uichemy_section_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => self::get_section_html( $name ),
					'content_classes' => 'tpae-uichemy-section',
				)
			);
			$element->end_controls_section();
		}



		/**
		 * The reverse hint inside the shared GSAP Scroll Interactions panel.
		 *
		 * Called from the panel's own section, so it lands inside that section, once an animation
		 * type is picked.
		 *
		 * @since 6.5.3
		 *
		 * @param object $element Elementor element that owns the GSAP panel.
		 */
		public static function add_gsap_notice( $element ) {

			if ( ! self::is_active() ) {
				return;
			}

			$element->add_control(
				'tpae_uichemy_gsap_notice',
				array(
					'type'            => Controls_Manager::RAW_HTML,
					'raw'             => self::get_html( '', 'editor-gsap-hint' ),
					'content_classes' => 'tpae-uichemy-gsap',
					'condition'       => array(
						'plus_gsap_animation_type!' => array( '', 'none' ),
					),
				)
			);
		}

		/**
		 * Whether the admin promotion has been dismissed. The flag is site-wide: the first
		 * administrator to dismiss it hides it for every administrator.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		private static function is_dismissed(): bool {
			return (bool) get_option( self::OPT_NOTICE_DISMISSED, false );
		}

		/**
		 * URL of the UiChemy product logo.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function logo_url(): string {
			return defined( 'L_THEPLUS_ASSETS_URL' ) ? L_THEPLUS_ASSETS_URL . 'images/products/uichemy_logo.png' : '';
		}

		/**
		 * Hover styles for every notice button and link.
		 *
		 * The markup is inline-styled, so a class rule needs !important to win over the base
		 * inline colour on :hover. Static, self-authored CSS; echoed directly (not via wp_kses,
		 * which strips <style>).
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function hover_css(): string {
			return '<style id="tpae-uichemy-hover">
				.tpae-uc-build,.tpae-uc-docs,.tpae-uc-way,.tpae-uichemy-note a,.tpae-uichemy-install,.tpae-uichemy-docs,.tpae-uc-empty a{transition:background .15s ease,border-color .15s ease,color .15s ease,transform .15s ease,box-shadow .15s ease;}
				.tpae-uc-build:hover{background:#34313d !important;transform:translateY(-1px);box-shadow:0 10px 22px -10px rgba(0,0,0,.55);}
				.tpae-uc-docs:hover{background:#f6f5f8 !important;border-color:#bdb9c6 !important;color:#000 !important;}
				.tpae-uc-way:hover{color:#000 !important;text-decoration:none !important;}
				.tpae-uichemy-note a,.tpae-uichemy-note a:hover,.tpae-uichemy-note a:focus{border-block-end:0 !important;border-bottom:0 !important;}
				.tpae-uichemy-note .tpae-uichemy-docs-link:hover{color:#fff !important;}
				.tpae-uichemy-note a:hover{color:#FD6A35 !important;text-decoration:none !important;}
				.tpae-uichemy-box:not(.tpae-uc-empty) .tpae-uichemy-install:hover{background:#FF5A1F !important;border-color:#FF5A1F !important;}
				.tpae-uichemy-box .tpae-uichemy-docs:hover{background:rgba(253,106,53,.12) !important;border-color:#FD6A35 !important;}
				.tpae-uc-empty .tpae-uichemy-install:hover{background:#34313d !important;}
				.tpae-uc-empty a:not(.tpae-uichemy-install):hover{color:#e4e6eb !important;text-decoration:none !important;}
			</style>';
		}

		/**
		 * Add campaign tracking to a UiChemy URL, in the UiChemy team's scheme: a fixed source and
		 * medium, and the clicked location in utm_content.
		 *
		 * @since 6.5.3
		 *
		 * @param string $url      URL to tag.
		 * @param string $location admin-notice, editor-style-picker, editor-build-your-own-panel,
		 *                         editor-widget-search or editor-gsap-hint.
		 * @return string
		 */
		private static function utm( $url, $location = 'admin-notice' ): string {
			if ( '' === $url ) {
				return '';
			}

			return add_query_arg(
				array(
					'utm_source'   => 'tpae-plugin',
					'utm_medium'   => 'plugin',
					'utm_campaign' => 'uichemy-promo',
					'utm_content'  => $location,
				),
				$url
			);
		}

		/**
		 * The four UiChemy pathways shown in the notice grid.
		 *
		 * @since 6.5.3
		 *
		 * @return array
		 */
		private static function get_categories(): array {
			return array(
				array(
					'title' => esc_html__( 'Figma to WordPress', 'tpebl' ),
					'text'  => esc_html__( 'Turn a Figma design into real, editable WordPress in your builder, pixel accurate and responsive.', 'tpebl' ),
					'cta'   => esc_html__( 'Convert Figma', 'tpebl' ),
					'url'   => self::utm( 'https://uichemy.com/figma-to-wordpress/' ),
				),
				array(
					'title' => esc_html__( 'AI Website Creator', 'tpebl' ),
					'text'  => esc_html__( 'Turn a prompt or client brief into a full WordPress site you own, from brief to sitemap to export.', 'tpebl' ),
					'cta'   => esc_html__( 'Create with AI', 'tpebl' ),
					'url'   => self::utm( 'https://uichemy.com/ai-website-creator/' ),
				),
				array(
					'title' => esc_html__( 'AI to WordPress', 'tpebl' ),
					'text'  => esc_html__( 'Bring a Lovable, Claude or v0 build straight into WordPress, editable and yours.', 'tpebl' ),
					'cta'   => esc_html__( 'Connect an AI', 'tpebl' ),
					'url'   => self::utm( 'https://uichemy.com/ai-to-wordpress/' ),
				),
				array(
					'title' => esc_html__( 'UiChemy Composer', 'tpebl' ),
					'text'  => esc_html__( 'Describe it in the builder and get real, editable HTML, CSS and JavaScript.', 'tpebl' ),
					'cta'   => esc_html__( 'Explore Composer', 'tpebl' ),
					'url'   => self::utm( 'https://uichemy.com/uichemy-composer/' ),
				),
			);
		}

		/**
		 * The 4-category admin notice: logo, headline and a 2x2 grid of the UiChemy pathways.
		 *
		 * @since 6.5.3
		 *
		 * @param string $context Extra CSS class for the notice wrapper.
		 *
		 * @return string
		 */
		private static function admin_html( $context ): string {

			$nonce  = wp_create_nonce( self::NONCE );
			$logo   = self::logo_url();
			$active = self::is_uichemy_active();

			$logo_img = '' !== $logo
				? sprintf( '<img src="%1$s" alt="%2$s" style="width:30px;height:30px;border-radius:7px;flex:0 0 auto;" />', esc_url( $logo ), esc_attr__( 'UiChemy', 'tpebl' ) )
				: '';

			// The primary action label and its tooltip depend on whether UiChemy is already active.
			$build_label = self::build_label();
			if ( $active ) {
				$build_note = esc_html__( 'Opens the UiChemy AI Website Creator.', 'tpebl' );
			} elseif ( self::is_uichemy_installed() ) {
				$build_note = esc_html__( 'Activates UiChemy, then opens its AI Website Creator.', 'tpebl' );
			} else {
				$build_note = esc_html__( 'Installs UiChemy, then opens its AI Website Creator.', 'tpebl' );
			}

			// No button when the user cannot install or open UiChemy (e.g. DISALLOW_FILE_MODS).
			$build_button = self::can_build()
				? sprintf(
					'<button type="button" class="tpae-uc-build" title="%1$s" style="display:inline-flex;align-items:center;gap:8px;height:38px;padding:0 20px;border-radius:9px;font-size:13px;font-weight:600;color:#fff;background:#26242e;border:0;cursor:pointer;box-shadow:0 8px 18px -10px rgba(0,0,0,.5);"><span style="color:#F5A63C;">&#10022;</span> %2$s</button>',
					esc_attr( $build_note ),
					$build_label
				)
				: '';

			// "Other ways in" links - the three UiChemy pathways besides the AI Website Creator, which the
			// main button already opens: Figma to WordPress, UiChemy Composer and AI to WordPress.
			$cats  = self::get_categories();
			$links = '';
			foreach ( array( $cats[0], $cats[3], $cats[2] ) as $i => $cat ) {
				if ( $i > 0 ) {
					$links .= '<span style="color:#d5d3dd;margin:0 8px;">&middot;</span>';
				}
				$links .= sprintf(
					'<a class="tpae-uc-way" href="%1$s" target="_blank" rel="noopener noreferrer" style="color:#1b1930;font-weight:600;text-decoration:none;">%2$s</a>',
					esc_url( $cat['url'] ),
					esc_html( $cat['title'] )
				);
			}

			$markup = sprintf(
				'<div class="notice is-dismissible tpae-uichemy-admin %1$s" data-tpae-uichemy-nonce="%2$s" data-tpae-uichemy-installed="%3$s" data-tpae-uichemy-creator="%13$s" data-tpae-uichemy-opening="%14$s" data-tpae-uichemy-setup="%15$s" data-tpae-uichemy-error="%16$s" style="padding:0;border:1px solid #e6e7ea;border-left:3px solid #FD6A35;border-radius:10px;background:#fff;box-shadow:0 1px 2px rgba(16,16,32,.05);">
					<div style="padding:16px 40px 16px 20px;">
						<div style="display:flex;align-items:center;gap:12px;margin-bottom:9px;">%4$s<h3 style="margin:0;font-size:15.5px;font-weight:700;color:#111114;letter-spacing:-.01em;">%5$s</h3></div>
						<p style="font-size:13px;line-height:1.6;color:#565269;margin:0 0 16px;">%6$s</p>
						<div class="tpae-uc-actions" style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;">
							%8$s
							<a class="tpae-uc-docs" href="%9$s" target="_blank" rel="noopener noreferrer" style="display:inline-flex;align-items:center;gap:6px;height:38px;padding:0 16px;border-radius:9px;font-size:13px;font-weight:600;color:#1b1930;background:#fff;border:1px solid #d9d6de;text-decoration:none;">%10$s <span style="font-size:13px;">&#8599;</span></a>
							<span style="margin-left:auto;font-size:12.5px;"><span style="color:#9a96a6;font-weight:500;margin-right:10px;">%11$s</span>%12$s</span>
						</div>
						<div class="tpae-uc-progress" style="display:none;margin-top:14px;font-size:13px;font-weight:500;color:#565269;"></div>
					</div>
				</div>',
				esc_attr( $context ),
				esc_attr( $nonce ),
				$active ? '1' : '0',
				$logo_img,
				esc_html__( 'Create A Website With One Prompt', 'tpebl' ),
				esc_html__( 'Describe your business. UiChemy turns it into a real WordPress site, pages, sections and copy, all editable in your own builder. Nothing locked, nothing rendered from someone else\'s server.', 'tpebl' ),
				esc_attr( $build_note ),
				$build_button,
				esc_url( self::utm( self::get_docs_url() ) ),
				esc_html__( 'See how it works', 'tpebl' ),
				esc_html__( 'Other ways in', 'tpebl' ),
				$links,
				esc_url( self::creator_url() ),
				esc_attr__( 'Opening UiChemy...', 'tpebl' ),
				esc_attr__( 'Setting up UiChemy...', 'tpebl' ),
				esc_attr__( 'Something went wrong. Please try again.', 'tpebl' )
			);

			return $markup;
		}

		/**
		 * The notice flow JS. Printed with wp_print_inline_script_tag so it is NOT run through
		 * wp_kses, which would entity-encode operators like && and break the script.
		 *
		 * All copy here is UI text for the progress states; it carries no dynamic PHP data
		 * (the nonce is read from the notice's data attribute at run time).
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function flow_js(): string {
			return "( function() {
				function spinStyle() {
					if ( document.getElementById( 'tpae-uc-spin-style' ) ) { return; }
					var st = document.createElement( 'style' );
					st.id = 'tpae-uc-spin-style';
					st.textContent = '@keyframes tpaeUcSpin{to{transform:rotate(360deg)}} .tpae-uc-spin{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:tpaeUcSpin .7s linear infinite;vertical-align:-2px;margin-right:8px;}';
					document.head.appendChild( st );
				}
				function busy( btn, text ) {
					spinStyle();
					btn.disabled = true;
					btn.style.opacity = '.9';
					btn.style.cursor = 'default';
					btn.textContent = '';
					var sp = document.createElement( 'span' );
					sp.className = 'tpae-uc-spin';
					btn.appendChild( sp );
					btn.appendChild( document.createTextNode( text ) );
				}

				// Coming back with the browser's Back button restores a page frozen mid-click.
				window.addEventListener( 'pageshow', function( ev ) {
					if ( ! ev.persisted ) { return; }
					document.querySelectorAll( '.tpae-uichemy-admin .tpae-uc-build[data-orig]' ).forEach( function( b ) {
						b.disabled = false;
						b.style.opacity = '';
						b.style.cursor = 'pointer';
						b.innerHTML = b.getAttribute( 'data-orig' );
					} );
				} );

				document.querySelectorAll( '.tpae-uichemy-admin' ).forEach( function( box ) {
					var nonce     = box.getAttribute( 'data-tpae-uichemy-nonce' );
					var installed = box.getAttribute( 'data-tpae-uichemy-installed' ) === '1';
					var creator   = box.getAttribute( 'data-tpae-uichemy-creator' );
					var tOpening  = box.getAttribute( 'data-tpae-uichemy-opening' ) || '';
					var tSetup    = box.getAttribute( 'data-tpae-uichemy-setup' ) || '';
					var tError    = box.getAttribute( 'data-tpae-uichemy-error' ) || '';
					var buildBtn  = box.querySelector( '.tpae-uc-build' );
					var progress  = box.querySelector( '.tpae-uc-progress' );

					box.addEventListener( 'click', function( e ) {
						if ( e.target.classList.contains( 'notice-dismiss' ) ) {
							var d = new FormData();
							d.append( 'action', 'tpae_uichemy_notice_dismiss' );
							d.append( 'nonce', nonce );
							fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: d } );
							return;
						}

						if ( buildBtn && ( e.target === buildBtn || buildBtn.contains( e.target ) ) ) {
							e.preventDefault();
							if ( buildBtn.disabled ) { return; }
							var orig = buildBtn.innerHTML;
							buildBtn.setAttribute( 'data-orig', orig );

							// Already active: nothing to install, go straight to the AI Website Creator.
							if ( installed && creator ) {
								busy( buildBtn, tOpening );
								window.location.href = creator;
								return;
							}

							busy( buildBtn, tSetup );

							var f = new FormData();
							f.append( 'action', 'tpae_uichemy_build' );
							f.append( 'nonce', nonce );

							fetch( ajaxurl, { method: 'POST', credentials: 'same-origin', body: f } )
								.then( function( r ) { return r.json(); } )
								.then( function( j ) {
									if ( j && j.success && j.data && j.data.url ) {
										busy( buildBtn, tOpening );
										window.location.href = j.data.url;
									} else {
										buildBtn.disabled = false;
										buildBtn.style.opacity = '';
										buildBtn.style.cursor = 'pointer';
										buildBtn.innerHTML = orig;
										if ( progress ) {
											progress.style.display = 'block';
											progress.textContent = ( j && j.data && j.data.message ) ? j.data.message : tError;
										}
									}
								} )
								.catch( function() {
									buildBtn.disabled = false;
									buildBtn.style.opacity = '';
									buildBtn.style.cursor = 'pointer';
									buildBtn.innerHTML = orig;
									if ( progress ) {
										progress.style.display = 'block';
										progress.textContent = tError;
									}
								} );
						}
					} );
				} );
			} )();";
		}

		/**
		 * Print the editor-side flow config + handler for the widget-panel section buttons.
		 *
		 * The section markup is a RAW_HTML control, so its own inline script never runs; this
		 * delegated handler (printed in the editor footer) drives it instead.
		 *
		 * @since 6.5.3
		 */
		public static function editor_footer() {

			if ( ! self::is_enabled() ) {
				return;
			}

			$config = wp_json_encode(
				array(
					'ajaxurl'    => admin_url( 'admin-ajax.php' ),
					'nonce'      => wp_create_nonce( self::NONCE ),
					'installed'  => self::is_uichemy_active(),
					'docsUrl'      => self::utm( self::COMPOSER_DOCS_URL, 'editor-widget-search' ),
					'creatorUrl'   => self::creator_url(),
					'activeLabel'  => esc_html__( 'Build with UiChemy', 'tpebl' ),
					'readyLabel'   => esc_html__( 'UiChemy is ready, open it', 'tpebl' ),
					'readyText'    => esc_html__( 'UiChemy is installed. Click the button to open the AI Website Creator.', 'tpebl' ),
					'settingUp'    => esc_html__( 'Setting up UiChemy...', 'tpebl' ),
					'errorText'    => __( 'Something went wrong. Please try again.', 'tpebl' ),
					'buildLabel'   => self::build_label(),
					'canBuild'     => self::can_build(),
					'emptyTitle'   => esc_html__( 'No Widget For That? Describe It To UiChemy', 'tpebl' ),
					'emptyText'    => esc_html__( 'From one section to a full website, describe what you need and UiChemy builds it as real, editable code you keep.', 'tpebl' ),
					'docsLabel'    => esc_html__( 'See how it works', 'tpebl' ),
				)
			);

			wp_print_inline_script_tag( 'window.tpaeUichemyFlow = ' . $config . ';' . self::editor_flow_js() );

			echo self::hover_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, self-authored CSS.
		}

		/**
		 * Delegated click handler for the widget-panel section "Install & Build" flow.
		 *
		 * @since 6.5.3
		 *
		 * @return string
		 */
		private static function editor_flow_js(): string {
			return "( function() {
				var cfg = window.tpaeUichemyFlow || {};

				function spinStyle() {
					if ( document.getElementById( 'tpae-uc-spin-style' ) ) { return; }
					var st = document.createElement( 'style' );
					st.id = 'tpae-uc-spin-style';
					st.textContent = '@keyframes tpaeUcSpin{to{transform:rotate(360deg)}} .tpae-uc-spin{display:inline-block;width:13px;height:13px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:tpaeUcSpin .7s linear infinite;vertical-align:-2px;margin-right:8px;}';
					document.head.appendChild( st );
				}

				// The build links carry no href, so Enter/Space must trigger them like a button.
				document.addEventListener( 'keydown', function( e ) {
					if ( ( e.key === 'Enter' || e.key === ' ' ) && e.target.closest && e.target.closest( '[data-tpae-uc-install]' ) ) {
						e.preventDefault();
						e.target.closest( '[data-tpae-uc-install]' ).click();
					}
				} );

				document.addEventListener( 'click', function( e ) {
					if ( ! e.target.closest ) { return; }

					// 'Install UiChemy & Build' buttons: install if needed (progress shown on the button),
					// then open the AI Website Creator in a NEW tab, so the Elementor page stays open.
					var installBtn = e.target.closest( '[data-tpae-uc-install]' );
					if ( installBtn ) {
						e.preventDefault();
						if ( installBtn.getAttribute( 'data-busy' ) === '1' ) { return; }
						var box      = installBtn.closest( '.tpae-uichemy-box' );
						var progress = box ? box.querySelector( '.tpae-uc-progress' ) : null;
						if ( progress ) { progress.style.display = 'none'; }

						// Already active: nothing to install, open the screen straight away.
						if ( cfg.installed && cfg.creatorUrl ) {
							window.open( cfg.creatorUrl, '_blank', 'noopener' );
							return;
						}

						// Show the install progress on the button itself, in the editor.
						var orig = installBtn.innerHTML;
						spinStyle();
						installBtn.setAttribute( 'data-busy', '1' );
						installBtn.style.opacity = '.9';
						installBtn.innerHTML = '<span class=\"tpae-uc-spin\"></span>' + ( cfg.settingUp || '' );

						var fail = function( msg ) {
							installBtn.removeAttribute( 'data-busy' );
							installBtn.style.opacity = '';
							installBtn.innerHTML = orig;
							if ( progress ) {
								progress.style.display = 'block';
								progress.textContent = msg || cfg.errorText || '';
							}
						};

						var f = new FormData();
						f.append( 'action', 'tpae_uichemy_build' );
						f.append( 'nonce', cfg.nonce );
						fetch( cfg.ajaxurl, { method: 'POST', credentials: 'same-origin', body: f } )
							.then( function( r ) { return r.json(); } )
							.then( function( j ) {
								if ( ! ( j && j.success && j.data && j.data.url ) ) {
									fail( j && j.data && j.data.message );
									return;
								}

								// UiChemy is active now: every build button opens it directly from here on.
								cfg.installed  = true;
								cfg.creatorUrl = j.data.url;
								document.querySelectorAll( '[data-tpae-uc-install]' ).forEach( function( b ) {
									b.removeAttribute( 'data-busy' );
									b.style.opacity = '';
									b.textContent = cfg.activeLabel || 'Build with UiChemy';
								} );

								// Try to open it now. Browsers only allow a new tab right after a click, and
								// the install takes longer than that, so this is often blocked; then the
								// clicked button asks for one more click, which always works.
								var tab = window.open( j.data.url, '_blank' );
								if ( tab ) {
									try { tab.opener = null; } catch ( err ) {}
								} else {
									installBtn.textContent = cfg.readyLabel || 'UiChemy is ready, open it';
									if ( progress ) {
										progress.style.display = 'block';
										progress.textContent = cfg.readyText || 'UiChemy is installed. Click the button to open the AI Website Creator.';
									}
								}
							} )
							.catch( function() { fail(); } );
						return;
					}
				} );
				// Widget-search 'no results' state: inject a UiChemy card above Elementor's own CTA.
				function syncEmptyState() {
					var input = document.getElementById( 'elementor-panel-elements-search-input' );
					var term  = input ? ( input.value || '' ).trim() : '';
					var mine  = document.querySelector( '.tpae-uc-empty' );
					// Cheap exit first: with no search term there is nothing to show or count.
					if ( term.length < 2 ) { if ( mine ) { mine.remove(); } return; }
					var area = document.getElementById( 'elementor-panel-elements-widget-creation-area' );
					if ( ! area ) { return; }
					var visible = 0;
					document.querySelectorAll( '#elementor-panel-elements .elementor-element-wrapper' ).forEach( function( el ) { if ( el.offsetParent !== null ) { visible++; } } );
					if ( ! ( term.length > 1 && visible === 0 ) ) { if ( mine ) { mine.remove(); } return; }
					if ( mine ) { return; }
					var card = document.createElement( 'div' );
					card.className = 'tpae-uichemy-box tpae-uc-empty';
					card.style.cssText = 'border:1px solid rgba(255,255,255,.12);border-radius:8px;padding:16px;background:rgba(255,255,255,.04);margin:8px 16px 14px;';
					card.innerHTML =
						'<div style=\"font-size:13px;font-weight:600;color:#e4e6eb;margin-bottom:6px;\">' + ( cfg.emptyTitle || '' ) + '</div>' +
						'<p style=\"margin:0 0 16px;font-size:12px;line-height:1.5;color:#a4afb7;\">' + ( cfg.emptyText || '' ) + '</p>' +
						'<div style=\"display:flex;flex-wrap:wrap;justify-content:center;align-items:center;gap:12px 18px;\">' +
							( cfg.canBuild ? '<a class=\"tpae-uichemy-install\" role=\"button\" tabindex=\"0\" data-tpae-uc-install=\"1\" style=\"cursor:pointer;display:inline-flex;align-items:center;justify-content:center;background:#26242e;color:#fff;border:1px solid rgba(255,255,255,.18);border-radius:6px;padding:9px 18px;font-size:12.5px;font-weight:600;text-decoration:none;\">' + ( cfg.buildLabel || 'Install UiChemy & Build' ) + '</a>' : '' ) +
							( cfg.docsUrl ? '<a href=\"' + cfg.docsUrl + '\" target=\"_blank\" rel=\"noopener noreferrer\" style=\"color:#c3c8ce;font-size:12px;text-decoration:none;white-space:nowrap;\">' + ( cfg.docsLabel || 'See how it works' ) + '</a>' : '' ) +
						'</div>' +
						'<div class=\"tpae-uc-progress\" style=\"display:none;margin-top:10px;font-size:12px;font-weight:500;color:#a4afb7;text-align:center;\"></div>';
					area.insertBefore( card, area.firstChild );
				}
				// Re-check only when the widget search changes (plus short follow-ups for Elementor's own
				// re-render) or the panel is clicked, at most once per frame. Watching the whole editor
				// would run on every editor change and slow big pages down.
				var tpaeUcQueued = false;
				function queueEmptyState() {
					if ( tpaeUcQueued ) { return; }
					tpaeUcQueued = true;
					( window.requestAnimationFrame || window.setTimeout )( function() { tpaeUcQueued = false; syncEmptyState(); } );
				}
				document.addEventListener( 'input', function( e ) {
					if ( e.target && e.target.id === 'elementor-panel-elements-search-input' ) {
						queueEmptyState();
						setTimeout( queueEmptyState, 250 );
						setTimeout( queueEmptyState, 700 );
					}
				}, true );
				document.addEventListener( 'click', function( e ) {
					if ( e.target.closest && e.target.closest( '#elementor-panel' ) ) { setTimeout( queueEmptyState, 250 ); }
				}, true );

			} )();";
		}

		/**
		 * Admin Way B - a one-shot, dismissible notice shown on the main wp-admin screens.
		 *
		 * @since 6.5.3
		 */
		public static function admin_notice() {

			if ( '' === self::get_docs_url() || self::is_dismissed() || ! current_user_can( 'manage_options' ) || self::wl_hidden() ) {
				return;
			}

			$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

			// Keep it to the high-traffic screens; skip the plugin's own dashboard (that has its own banner).
			$allowed = array( 'dashboard', 'plugins', 'edit-page', 'edit-post' );
			if ( ! $screen || ! in_array( $screen->id, $allowed, true ) ) {
				return;
			}

			echo wp_kses( self::admin_html( 'tpae-uichemy-oneshot' ), self::kses() );
			echo self::hover_css(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- static, self-authored CSS.
			wp_print_inline_script_tag( self::flow_js() );
		}


		/**
		 * Store the dismissal so no admin promotion shows again.
		 *
		 * @since 6.5.3
		 */
		public static function ajax_notice_dismiss() {

			if ( ! current_user_can( 'manage_options' ) || ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
				wp_send_json_error( '', 403 );
			}

			update_option( self::OPT_NOTICE_DISMISSED, 1, false );
			wp_send_json_success();
		}

		/**
		 * Whether the UiChemy plugin is installed and active.
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		public static function is_uichemy_active(): bool {
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			return is_plugin_active( 'uichemy/uichemy.php' );
		}

		/**
		 * Whether UiChemy's files are on the site (active or not).
		 *
		 * @since 6.5.3
		 *
		 * @return bool
		 */
		private static function is_uichemy_installed(): bool {
			return file_exists( WP_PLUGIN_DIR . '/uichemy/uichemy.php' );
		}

		/**
		 * Install and activate UiChemy from the WordPress.org repository.
		 *
		 * @since 6.5.3
		 *
		 * @return true|\WP_Error
		 */
		private static function install_uichemy() {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/misc.php';
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
			require_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$api = plugins_api(
				'plugin_information',
				array(
					'slug'   => 'uichemy',
					'fields' => array( 'sections' => false ),
				)
			);

			if ( is_wp_error( $api ) ) {
				return $api;
			}

			// Hosts that need FTP/SSH details cannot install from an AJAX request; say so plainly.
			ob_start();
			$creds = request_filesystem_credentials( '', '', false, false, null );
			ob_end_clean();
			if ( false === $creds || ! WP_Filesystem( $creds ) ) {
				return new \WP_Error( 'tpae_uichemy_fs', esc_html__( 'This site cannot install plugins automatically. Install UiChemy from Plugins > Add New, then try again.', 'tpebl' ) );
			}

			$skin     = new \WP_Ajax_Upgrader_Skin();
			$upgrader = new \Plugin_Upgrader( $skin );
			$result   = $upgrader->install( $api->download_link );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( $skin->get_errors()->has_errors() ) {
				return new \WP_Error( 'tpae_uichemy_install_failed', $skin->get_error_messages() );
			}
			if ( true !== $result ) {
				return new \WP_Error( 'tpae_uichemy_install_failed', esc_html__( 'Could not install UiChemy. Install it from Plugins > Add New, then try again.', 'tpebl' ) );
			}

			return self::activate_uichemy();
		}

		/**
		 * Activate the installed UiChemy plugin and skip its first-run redirect.
		 *
		 * @since 6.5.3
		 *
		 * @return true|\WP_Error
		 */
		private static function activate_uichemy() {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';

			$activate = activate_plugin( 'uichemy/uichemy.php' );
			if ( is_wp_error( $activate ) ) {
				return $activate;
			}

			// UiChemy's activation sets this transient to send first-time activators to its own
			// onboarding wizard on the next admin_init. Delete it so our flow lands the user
			// straight on the AI Website Creator instead.
			delete_transient( 'uich_do_activation_redirect' );

			return true;
		}

		/**
		 * Install UiChemy if needed, then return the URL of its AI Website Creator screen.
		 *
		 * @since 6.5.3
		 */
		public static function ajax_build() {

			if ( ! check_ajax_referer( self::NONCE, 'nonce', false ) ) {
				wp_send_json_error( array( 'message' => esc_html__( 'Security check failed. Reload and try again.', 'tpebl' ) ), 403 );
			}

			// Same rule as the button: manage_options, plus activate_plugins (and install_plugins
			// when UiChemy is missing). Core maps these to do_not_allow under DISALLOW_FILE_MODS and to
			// super admins on multisite.
			if ( ! self::can_build() ) {
				wp_send_json_error( array( 'message' => esc_html__( 'You do not have permission to do this.', 'tpebl' ) ), 403 );
			}

			if ( ! self::is_uichemy_active() ) {
				$ready = self::is_uichemy_installed() ? self::activate_uichemy() : self::install_uichemy();
				if ( is_wp_error( $ready ) ) {
					wp_send_json_error( array( 'message' => $ready->get_error_message() ), 500 );
				}
			}

			// edit_url repeats the URL for editor tabs opened before this reply changed shape.
			wp_send_json_success(
				array(
					'url'      => self::creator_url(),
					'edit_url' => self::creator_url(),
				)
			);
		}

		/**
		 * Allowed tags for the admin notice markup.
		 *
		 * @since 6.5.3
		 *
		 * @return array
		 */
		private static function kses(): array {
			return array(
				'div'    => array(
					'class'                       => array(),
					'style'                       => array(),
					'data-tpae-uichemy-nonce'     => array(),
					'data-tpae-uichemy-installed' => array(),
					'data-tpae-uichemy-creator'   => array(),
					'data-tpae-uichemy-opening'   => array(),
					'data-tpae-uichemy-setup'     => array(),
					'data-tpae-uichemy-error'     => array(),
				),
				'h3'     => array( 'style' => array() ),
				'p'      => array( 'style' => array() ),
				'span'   => array( 'style' => array() ),
				'button' => array(
					'type'      => array(),
					'class'     => array(),
					'style'     => array(),
					'title'     => array(),
				),
				'strong' => array(),
				'img'    => array(
					'style' => array(),
					'src'   => array(),
					'alt'   => array(),
				),
				'a'      => array(
					'class'  => array(),
					'href'   => array(),
					'target' => array(),
					'rel'    => array(),
					'title'  => array(),
					'style'  => array(),
				),
				'script' => array(),
			);
		}
	}

	Tpae_UiChemy_Notice::init();
}
