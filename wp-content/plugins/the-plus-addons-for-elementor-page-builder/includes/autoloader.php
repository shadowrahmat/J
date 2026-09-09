<?php
/**
 * TPAE Hybrid Classmap Autoloader
 *
 * Implements a classmap-based autoloader for The Plus Addons for Elementor.
 * Uses explicit class-to-file mapping because file naming conventions are
 * inconsistent across the codebase (tp_*.php, class-tp-*.php, plus-*.php).
 *
 * Only handles known plugin classes — does not interfere with other plugins.
 *
 * @package the-plus-addons-for-elementor-page-builder
 * @since   6.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Tpae_Autoloader
 *
 * @since 6.5.0
 */
class Tpae_Autoloader {

	/**
	 * Plugin root directory path (with trailing separator).
	 *
	 * @var string
	 */
	private static $base_path = '';

	/**
	 * Class-to-file map (populated on register).
	 *
	 * @var array
	 */
	private static $classmap = array();

	/**
	 * Initialize and register the autoloader.
	 *
	 * @since 6.5.0
	 *
	 * @param string $base_path Plugin root directory path (with trailing separator).
	 */
	public static function register( $base_path ) {
		self::$base_path = $base_path;
		self::$classmap  = self::build_classmap();

		spl_autoload_register( array( __CLASS__, 'autoload' ) );
	}

	/**
	 * Autoload callback registered with spl_autoload_register.
	 *
	 * @since 6.5.0
	 *
	 * @param string $class Fully qualified class name.
	 */
	public static function autoload( $class ) {

		// 1. Check classmap first (fastest path).
		if ( isset( self::$classmap[ $class ] ) ) {
			$file = self::$base_path . self::$classmap[ $class ];

			if ( file_exists( $file ) ) {
				require_once $file;
			}

			return;
		}

		// 2. Only attempt resolution for known namespace prefixes.
		$known_prefixes = array(
			'TheplusAddons\\',
			'ThePlusAddons\\',
			'Theplus\\',
			'Tp\\',
			'ElementPack\\',
		);

		$matched = false;

		foreach ( $known_prefixes as $prefix ) {
			if ( 0 === strpos( $class, $prefix ) ) {
				$matched = true;
				break;
			}
		}

		if ( ! $matched ) {
			return; // Not our class — bail early so other autoloaders can handle it.
		}

		// 3. PSR-4 fallback is reserved for future classes that follow conventions.
		// Currently all existing classes are covered by the classmap above.
	}

	/**
	 * Build the classmap array.
	 *
	 * Maps fully-qualified class names to relative file paths from the plugin root.
	 * This covers all plugin classes EXCEPT widget files in modules/widgets/
	 * (those are already lazy-loaded by Elementor's widget registration system).
	 *
	 * @since 6.5.0
	 *
	 * @return array Class name => relative file path.
	 */
	private static function build_classmap() {
		return array(

			// === Enqueue / Generator / Manager (no namespace) ===.
			'L_Plus_Generator'                     => 'modules/enqueue/plus-generator.php',
			'L_Plus_Library'                       => 'modules/enqueue/plus-library.php',
			'Plus_Widgets_Manager'                 => 'modules/enqueue/plus-widgets-manager.php',

			// === Core ===.
			'TheplusAddons\\L_Theplus_Element_Load'       => 'widgets_loader.php',
			'TheplusAddons\\L_Theplus_Widgets_Include'    => 'modules/theplus-include-widgets.php',
			'TheplusAddons\\L_Theplus_Module'             => 'modules/query-control/module.php',
			'L_Theplus_MetaBox'                           => 'includes/plus_addon.php',
			'Tp_LazyLoad_Images'                          => 'includes/tp-lazy-function.php',

			// === Extensions ===.
			'L_Tpae_Extensions_Main'               => 'modules/extensions/class-tpae-extensions-main.php',
			'Tpae_Advanced_Shadow'                 => 'modules/extensions/class-tpae-advanced-shadow.php',
			'Tpae_Copy_Paste'                      => 'modules/extensions/copy-paste/class-tpae-copy-paste.php',
			'Tpae_Equal_Height'                    => 'modules/extensions/class-tpae-equal-height.php',
			'Tpae_Glass_Morphism'                  => 'modules/extensions/class-tpae-glass-morphism.php',
			'Tpae_Gsap_Main_Animation'             => 'modules/extensions/animation/class-tp-gsap-main.php',
			'Tpae_Gsap_animation'                  => 'modules/extensions/animation/class-tpae-gsap-animation.php',
			'Tpae_Wrapper_Link'                    => 'modules/extensions/wrapper-link/class-tpae-wrapper-link.php',

			// === Global Controls ===.
			'ThePlusAddons\\Elementor\\TP_GSAP_Global'
				=> 'modules/extensions/global-control/class-tp-basic-global-controller.php',
			'ThePlusAddons\\Elementor\\BoxShadow\\TP_Box_Shadow_Global'
				=> 'modules/extensions/global-control/class-tp-global-box-shadow-controller.php',
			'ThePlusAddons\\Elementor\\BoxShadow\\TP_Group_Control_Box_Shadow'
				=> 'modules/extensions/global-control/class-tp-group-box-shadow.php',
			'ThePlusAddons\\Elementor\\ButtonStyle\\TP_Button_Style_Global'
				=> 'modules/extensions/global-control/class-tp-global-button-style-controller.php',
			'ThePlusAddons\\Elementor\\Dimensions\\TP_Control_Dimensions'
				=> 'modules/extensions/global-control/class-tp-control-dimensions.php',
			'ThePlusAddons\\Elementor\\Dimensions\\TP_Dimensions_Global'
				=> 'modules/extensions/global-control/class-tp-global-dimensions-controller.php',
			'ThePlusAddons\\Elementor\\Gradient\\TP_Gradient_Global'
				=> 'modules/extensions/global-control/class-tp-global-gradient-color-controller.php',
			'ThePlusAddons\\Elementor\\Gradient\\TP_Group_Control_Background'
				=> 'modules/extensions/global-control/class-tp-group-background.php',
			'ThePlusAddons\\Elementor\\Image\\TP_GSAP_Image_Global'
				=> 'modules/extensions/global-control/class-tp-image-global-controller.php',
			'ThePlusAddons\\Elementor\\ScrollAnimation\\TP_Global_Scroll_Animation_Controller'
				=> 'modules/extensions/global-control/class-tp-global-scroll-animation-controller.php',
			'ThePlusAddons\\Elementor\\ScrollAnimation\\TP_Global_Scroll_Animation_Helper'
				=> 'modules/extensions/global-control/class-tp-global-scroll-animation-helper.php',
			'ThePlusAddons\\Elementor\\Text\\TP_GSAP_Text_Global'
				=> 'modules/extensions/global-control/class-tp-text-global-controller.php',
			'Tpae_Global_Controllers_Main'
				=> 'modules/extensions/global-control/class-tp-global-controller-main.php',

			// === Dynamic Tags ===.
			'ElementPack\\Includes\\DynamicContent\\Tpae_Dynamic_Tag'
				=> 'modules/extensions/dynamic-tag/class-tpae-dynamic-tag.php',
			'ElementPack\\Includes\\DynamicContent\\TPAE_Pro_Dummy_Tag'
				=> 'modules/extensions/dynamic-tag/tags/pro/tpae-pro-dummy.php',

			// Dynamic Tags — Text.
			'ThePlus_Dynamic_Tag_Post_Author'              => 'modules/extensions/dynamic-tag/tags/text/post-author.php',
			'ThePlus_Dynamic_Tag_Post_Category'            => 'modules/extensions/dynamic-tag/tags/text/post-category.php',
			'ThePlus_Dynamic_Tag_Post_Category_Description' => 'modules/extensions/dynamic-tag/tags/text/post-cat-desc.php',
			'ThePlus_Dynamic_Tag_Post_Category_Post_Count' => 'modules/extensions/dynamic-tag/tags/text/post-cat-post-count.php',
			'ThePlus_Dynamic_Tag_Post_Content'             => 'modules/extensions/dynamic-tag/tags/text/post-content.php',
			'ThePlus_Dynamic_Tag_Post_Date'                => 'modules/extensions/dynamic-tag/tags/text/post-date.php',
			'ThePlus_Dynamic_Tag_Post_Excerpt'             => 'modules/extensions/dynamic-tag/tags/text/post-excerpt.php',
			'ThePlus_Dynamic_Tag_Post_Featured_Image_Data' => 'modules/extensions/dynamic-tag/tags/text/post-featured-image.php',
			'ThePlus_Dynamic_Tag_Post_ID'                  => 'modules/extensions/dynamic-tag/tags/text/post-id.php',
			'ThePlus_Dynamic_Tag_Post_Slug'                => 'modules/extensions/dynamic-tag/tags/text/post-slug.php',
			'ThePlus_Dynamic_Tag_Post_Status'              => 'modules/extensions/dynamic-tag/tags/text/post-status.php',
			'ThePlus_Dynamic_Tag_Post_Tag_Description'     => 'modules/extensions/dynamic-tag/tags/text/post-tag-desc.php',
			'ThePlus_Dynamic_Tag_Post_Tag_Post_Count'      => 'modules/extensions/dynamic-tag/tags/text/post-tag-post-count.php',
			'ThePlus_Dynamic_Tag_Post_Tags'                => 'modules/extensions/dynamic-tag/tags/text/post-tag.php',
			'ThePlus_Dynamic_Tag_Post_Terms'               => 'modules/extensions/dynamic-tag/tags/text/post-terms.php',
			'ThePlus_Dynamic_Tag_Post_Time'                => 'modules/extensions/dynamic-tag/tags/text/post-time.php',
			'ThePlus_Dynamic_Tag_Post_Title'               => 'modules/extensions/dynamic-tag/tags/text/post-title.php',
			'ThePlus_Dynamic_Tag_Post_Type'                => 'modules/extensions/dynamic-tag/tags/text/post-type.php',
			'ThePlus_Dynamic_Tag_Site_Current_Date_Time'   => 'modules/extensions/dynamic-tag/tags/text/site-current-date-time.php',
			'ThePlus_Dynamic_Tag_Site_Tagline'             => 'modules/extensions/dynamic-tag/tags/text/site-tagline.php',
			'ThePlus_Dynamic_Tag_Site_Title'               => 'modules/extensions/dynamic-tag/tags/text/site-title.php',

			// Dynamic Tags — Images.
			'ThePlus_Dynamic_Tag_Post_Author_Avatar'  => 'modules/extensions/dynamic-tag/tags/image/post-author-avatar.php',
			'ThePlus_Dynamic_Tag_Post_Category_Image' => 'modules/extensions/dynamic-tag/tags/image/post-cat-image.php',
			'ThePlus_Dynamic_Tag_Post_Featured_Image' => 'modules/extensions/dynamic-tag/tags/image/post-featured-image.php',
			'ThePlus_Dynamic_Tag_Site_Icon'            => 'modules/extensions/dynamic-tag/tags/image/site-icon.php',
			'ThePlus_Dynamic_Tag_Site_Logo'            => 'modules/extensions/dynamic-tag/tags/image/site-logo.php',

			// Dynamic Tags — URLs.
			'ThePlus_Dynamic_Tag_Post_Author_URL' => 'modules/extensions/dynamic-tag/tags/url/post-author-url.php',
			'ThePlus_Dynamic_Tag_Post_Term_URL'   => 'modules/extensions/dynamic-tag/tags/url/post-term-url.php',
			'ThePlus_Dynamic_Tag_Post_URL'        => 'modules/extensions/dynamic-tag/tags/url/post-url.php',
			'ThePlus_Dynamic_Tag_Site_URL'        => 'modules/extensions/dynamic-tag/tags/url/site-url.php',

			// === Controls ===.
			'L_Theplus_Query'          => 'modules/controls/plus-query.php',
			'Tpae_Need_Help_Control'   => 'modules/controls/tpae-need-help.php',
			'Tpae_Preset_Controller'   => 'modules/controls/tpae-preset-controller.php',
			'Tpae_Pro_Feature'         => 'modules/controls/tpae-pro-features.php',
			'Tpae_Theme_builder'       => 'modules/controls/tpae-theme-builder.php',
			'Tp_Nxt_Download'          => 'modules/controls/theme-builder/tpae-class-nxt-download.php',

			// === Admin: Dashboard ===.
			'Tpae_Dashboard_Ajax'      => 'includes/admin/dashboard/class-tpae-dashboard-ajax.php',
			'Tpae_Dashboard_Listing'   => 'includes/admin/dashboard/class-tpae-dashboard-listing.php',
			'Tpae_Dashboard_Main'      => 'includes/admin/dashboard/class-tpae-dashboard-main.php',
			'Tpae_Dashboard_Meta'      => 'includes/admin/dashboard/class-tpae-dashboard-meta.php',
			'Wdk_Widget_Api'           => 'includes/admin/dashboard/class-wdk-widget-api.php',

			// === Admin: Extra Options ===.
			'Tpae_Clients_Options'     => 'includes/admin/extra-options/clients_options.php',
			'Tpae_Custom_Code'         => 'includes/admin/extra-options/class-tpae-custom-code.php',
			'Tpae_Teammember_Options'  => 'includes/admin/extra-options/teammember_options.php',
			'Tpae_Testimonial_Options' => 'includes/admin/extra-options/testimonial_option.php',

			// === Admin: Hooks ===.
			'Tpae_Hooks'               => 'includes/admin/tpae-hooks/class-tpae-hooks.php',
			'Tpae_Main_Hooks'          => 'includes/admin/tpae-hooks/class-tpae-main-hooks.php',
			'Tpae_Widgets_Scan'        => 'includes/admin/tpae-hooks/class-tpae-widgets-scan.php',

			// === Admin: White Label ===.
			'Tpae_White_Label'         => 'includes/admin/white-label/class-tpae-white-label.php',

			// === Notices ===.
			'Theplus\\Notices\\Tp_Dashboard_Overview'                             => 'includes/notices/class-tp-dashboard-overview.php',
			'Theplus\\Notices\\Tp_Notices_Main'                                   => 'includes/notices/class-tp-notices-main.php',
			'Theplus\\Notices\\Tp_Wdkit_Preview_Popup'                            => 'includes/notices/class-tp-wdkit-preview-popup.php',
			'Tp\\Notices\\PluginPage\\Tp_Plugin_Page'                             => 'includes/notices/class-tp-plugin-page.php',
			'Tp\\Notices\\Remove\\Tp_Notices_Remove'                              => 'includes/notices/class-tp-notices-remove.php',
			'Tp\\Notices\\TPAEActivateLicense\\Tp_Activate_License_Notice'        => 'includes/notices/class-tp-activate-license-notice.php',
			'Tp\\Notices\\TPAEAskReview\\Tp_Ask_Review_Notice'                    => 'includes/notices/class-tp-ask-review-notice.php',
			'Tp\\Notices\\TPAEAskReview\\Tp_Join_Community_Notice'                => 'includes/notices/class-tp-join-community-notice.php',
			'Tp\\Notices\\TPAEExpiredLicense\\Tp_Expired_License_Notice'           => 'includes/notices/class-tp-expired-license-notice.php',
			'Tp\\Notices\\TPAEPInstallNotice\\Tp_Tpaepro_Notice'                  => 'includes/notices/class-tp-tpaepro-notice.php',
			'Tp\\Notices\\TPAEWinterSaleBanner\\Tpae_PluginFeatures_Banner'       => 'includes/notices/class-tp-plugin-features-banner.php',
			'Tp\\Notices\\TPAGInstallNotice\\Tp_Nexter_Extension_Promo_Notice'    => 'includes/notices/class-tp-nexter-extension-promo.php',
			'Tp\\Notices\\TPAGInstallNotice\\Tp_Nexter_Notice'                    => 'includes/notices/class-tp-nexter-notice.php',
			'Tp\\Notices\\WidgetNotice\\Tp_Widget_Notice'                         => 'includes/notices/class-tp-widget-notice.php',

			// === Preset ===.
			'Tp_Wdkit_Preset'          => 'includes/preset/class-wdkit-preset.php',

			// === User Experience ===.
			'Theplus\\UserExperience\\Tp_Deactivate_Survey' => 'includes/user-experience/class-tp-deactivate-survey.php',
			'Theplus\\Notices\\Tp_User_Experience_Main'   => 'includes/user-experience/class-tp-user-experience-main.php',
			'Theplus\\Notices\\Tpae_Update_Popup'         => 'includes/user-experience/update-popup/class-tp-update-popup.php',

			// === Widget Features ===.
			'Tp_Form_Handler'          => 'modules/widgets-features/class-tp-form-handler.php',
			'Tp_load_more'             => 'modules/widgets-features/class-tp-load-more.php',
			'TP_Widgets_Feature_Main'  => 'modules/widgets-features/class-tp-widgets-feature-main.php',
			'Tpae_Create_Template'     => 'modules/widgets-features/template-editor/class-tp-create-template.php',

			// === Widget Promotion ===.
			'TP_Widgets_Promotion_Main'                            => 'modules/widget-promotion/tp-widget-promotion-main.php',
			'Tp\\Notices\\Tp_Widget_Promotion\\Tp_Widget_Promotion' => 'modules/widget-promotion/tp-widget-promotion/class-tp-widget-promotion.php',
			'Tp_Widget_Show'                                       => 'modules/widget-promotion/tp-widgets-show/class-tp-widget-show.php',
		);
	}
}
