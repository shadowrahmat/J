<?php
/**
 * Widget Dependency Registry
 *
 * Centralized configuration of CSS/JS dependencies for all widgets and extensions.
 * Each entry maps a widget slug to its asset dependencies.
 *
 * Structure:
 *   'widget-slug' => array(
 *       'context'    => 'view'|'edit',  // Optional. Defaults to 'view' (frontend).
 *       'dependency' => array(
 *           'css' => array( ...file paths... ),
 *           'js'  => array( ...file paths... ),
 *       ),
 *   )
 *
 * Context values:
 *   'view' (default) — Loaded on frontend page views.
 *   'edit'           — Loaded only in the Elementor editor.
 *
 * @package the-plus-addons-for-elementor-page-builder
 * @since   6.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pre-compute base path once instead of 271 runtime concatenations.
$tpae_base = L_THEPLUS_PATH . DIRECTORY_SEPARATOR;

return
		array(

			'tp-adv-text-block'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/text-block/tp-text-block.css',
					),
				),
			),
			'tp-text-block-animation'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/main/text-block/tp-text-block.min.js',
						$tpae_base . 'assets/js/main/text-animation/tp-text-animation.js',
						$tpae_base . 'assets/js/extra/gsap/gsap.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrollToPlugin.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrollTrigger.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrambleText.js',
						$tpae_base . 'assets/js/extra/gsap/SplitText.min.js',
						$tpae_base . 'assets/js/extra/gsap/TextPlugin.min.js',
					),
				),
			),
			'tp-accordion'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tabs-tours/plus-tabs-tours.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/accordion/plus-accordion.min.js',
					),
				),
			),
			'tp-age-gate'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/age-gate/plus-method.css',
					),
					'js'  => array(

						$tpae_base . 'assets/js/main/age-gate/plus-age-gate.min.js',
					),
				),
			),
			'tp-ag-method-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/age-gate/plus-method-1.css',
					),
				),
			),
			'tp-ag-method-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/age-gate/plus-method-2.css',
					),
				),
			),
			'tp-ag-method-3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/age-gate/plus-method-3.css',
					),
				),
			),
			'tp-blockquote'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/block-quote/plus-block-quote.css',
					),
				),
			),
			'tp-bq-bl_1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/block-quote/plus-block-layout1.css',
					),
				),
			),
			'tp-bq-bl_2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/block-quote/plus-block-layout2.css',
					),
				),
			),
			'tp-bq-bl_3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/block-quote/plus-block-layout3.css',
					),
				),
			),
			'tp-blog-listout'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/blog-list/plus-bloglist-style.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-listing.min.js',
					),
				),
			),
			'tp-bloglistout-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/blog-list/plus-bloglist-style-1.css',
					),
				),
			),
			'tp-bloglistout-style-5'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/blog-list/plus-bloglist-style-5.css',
					),
				),
			),
			'plus-listing-metro'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/imagesloaded.pkgd.min.js',
						$tpae_base . 'assets/js/extra/isotope.pkgd.js',
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-metro-list.min.js',
					),
				),
			),
			'plus-listing-masonry'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/imagesloaded.pkgd.min.js',
						$tpae_base . 'assets/js/extra/isotope.pkgd.js',
						$tpae_base . 'assets/js/extra/packery-mode.pkgd.min.js',
					),
				),
			),
			'tp-breadcrumbs-bar'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/breadcrumbs-bar/plus-breadcrumbs-bar.css',
					),
				),
			),
			'tp-breadcrumbs-bar-style_1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/breadcrumbs-bar/plus-bb-style1.css',
					),
				),
			),
			'tp-breadcrumbs-bar-style_2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/breadcrumbs-bar/plus-bb-style2.css',
					),
				),
			),
			'tp-button'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style.css',
					),
				),
			),
			'tp-button-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-1.css',
					),
				),
			),
			'tp-button-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-2.css',
					),
				),
			),
			'tp-button-style-3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-3.css',
					),
				),
			),
			'tp-button-style-4'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-4.css',
					),
				),
			),
			'tp-button-style-5'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-5.css',
					),
				),
			),
			'tp-button-style-6'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-6.css',
					),
				),
			),
			'tp-button-style-7'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-7.css',
					),
				),
			),
			'tp-button-style-8'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-8.css',
					),
				),
			),
			'tp-button-style-9'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-9.css',
					),
				),
			),
			'tp-button-style-10'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-10.css',
					),
				),
			),
			'tp-button-style-11'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-11.css',
					),
				),
			),
			'tp-button-style-12'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-12.css',
					),
				),
			),
			'tp-button-style-13'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-13.css',
					),
				),
			),
			'tp-button-style-14'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-14.css',
					),
				),
			),
			'tp-button-style-15'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-15.css',
					),
				),
			),
			'tp-button-style-16'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-16.css',
					),
				),
			),
			'tp-button-style-17'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-17.css',
					),
				),
			),
			'tp-button-style-18'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-18.css',
					),
				),
			),
			'tp-button-style-19'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-19.css',
					),
				),
			),
			'tp-button-style-20'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-20.css',
					),
				),
			),
			'tp-button-style-21'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-21.css',
					),
				),
			),
			'tp-button-style-22'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-22.css',
					),
				),
			),
			'tp-button-style-24'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tp-button/tp-button-style-24.css',
					),
				),
			),
			'tp-carousel-anything'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/slick.min.css',
						$tpae_base . 'assets/css/main/carousel/plus-carousel.css',
						$tpae_base . 'assets/css/main/carousel-anything/plus-carousel-anything.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/slick.min.js',
						$tpae_base . 'assets/js/main/general/plus-slick-carousel.min.js',
						$tpae_base . 'assets/js/main/carousel-anything/plus-carousel-anything.min.js',
					),
				),
			),
			'tp-contact-form-7'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/forms-style/plus-cf7-style.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/forms-style/plus-cf7-form.js',
					),
				),
			),
			'tp-clients-listout'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/client-list/plus-client-list.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-listing.min.js',
					),
				),
			),
			'tp-countdown'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/countdown/plus-cd-style.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/countdown/plus-countdown.min.js',
					),
				),
			),
			'tp-countdown-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/countdown/plus-cd-s-1.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/jquery.downCount.js',
					),
				),
			),
			'tp-countdown-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/countdown/plus-cd-s-2.css',
						$tpae_base . 'assets/css/extra/countdown/flipdown.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/countdown/flipdown.min.js',
					),

				),
			),
			'tp-countdown-style-3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/countdown/plus-cd-s-3.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/countdown/progressbar.min.js',
					),
				),
			),
			'tp-dark-mode'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/darkmode/plus-dark-mode.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/darkmode.min.js',
						$tpae_base . 'assets/js/main/darkmode/plus-dark-mode.min.js',
					),
				),
			),
			'tp-dynamic-categories'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/dynamic-categories/plus-dynamic-categories.css',
					),
				),
			),
			'tp-dynamic-categories-style_1'  => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/dynamic-categories/dynamic-style-1.css',
					),
				),
			),
			'tp-dynamic-categories-style_2'  => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/dynamic-categories/dynamic-style-2.css',
					),
				),
			),
			'tp-dynamic-categories-style_3'  => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/dynamic-categories/dynamic-style-3.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/dynamic-category/plus-dynamic-category.min.js',
					),
				),
			),
			'tp-everest-form'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/forms-style/plus-everest-form.css',
					),
				),
			),
			'tp-plus-form'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-form/plus-form-widget.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/plus-form/plus-form-widget.min.js',
					),
				),
			),
			'tp-smooth-scroll'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/smooth-scroll.js',
						$tpae_base . 'assets/js/main/smooth-scroll/plus-smooth-scroll.min.js',
					),
				),
			),
			'tp-smooth-scroll-lenis'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/lenis/lenis.min.js',
					),
				),
			),
			'tp-style-list'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/stylist-list/plus-style-list.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/stylist-list/plus-stylist-list.min.js',
					),
				),
			),
			'tp-flip-box'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/info-box/plus-infobox-style.css',
					),
				),
			),
			'tp-gallery-listout'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/gallery-list/plus-gallery-list.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-listing.min.js',
					),
				),
			),
			'tp-gallery-listout-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/gallery-list/plus-gl-style1.css',
					),
				),
			),
			'tp-gallery-listout-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/gallery-list/plus-gl-style2.css',
					),
				),
			),
			'tp-gravityt-form'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/forms-style/plus-gravity-form.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/forms-style/plus-gravity-form.js',
					),
				),
			),
			'tp-heading-animation'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/tp-heading-animation.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/heading-animation/plus-heading-animation.min.js',
					),
				),
			),
			'tp-heading-animation-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-1.css',
					),
				),
			),
			'tp-heading-animation-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-2.css',
					),
				),
			),
			'tp-heading-animation-style-3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-3.css',
					),
				),
			),
			'tp-heading-animation-style-4'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-4.css',
					),
				),
			),
			'tp-heading-animation-style-5'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-5.css',
					),
				),
			),
			'tp-heading-animation-style-6'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-animation/heading-animation-style-6.css',
					),
				),
			),
			'tp-header-extras'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/header-extras/plus-header-extras.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/header-extras/plus-header-extras.min.js',
					),
				),
			),
			'tp-gsap-heading-animation'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/main/heading-title/tp-heading-title.js',
						$tpae_base . 'assets/js/main/text-animation/tp-text-animation.js',
						$tpae_base . 'assets/js/extra/gsap/gsap.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrollToPlugin.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrollTrigger.min.js',
						$tpae_base . 'assets/js/extra/gsap/ScrambleText.js',
						$tpae_base . 'assets/js/extra/gsap/SplitText.min.js',
						$tpae_base . 'assets/js/extra/gsap/TextPlugin.min.js',
					),
				),
			),
			'tp-heading-title'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style.css',
					),
				),
			),
			'tp-heading-title-style_1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-1.css',
					),
				),
			),
			'tp-heading-title-style_2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-2.css',
					),
				),
			),
			'tp-heading-title-style_3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-3.css',
					),
				),
			),
			'tp-heading-title-style_4'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-4.css',
					),
				),
			),
			'tp-heading-title-style_5'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-5.css',
					),
				),
			),
			'tp-heading-title-style_6'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-6.css',
					),
				),
			),
			'tp-heading-title-style_7'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-7.css',
					),
				),
			),
			'tp-heading-title-style_8'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-8.css',
					),
				),
			),
			'tp-heading-title-style_9'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-9.css',
					),
				),
			),
			'tp-heading-title-style_10'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-10.css',
					),
				),
			),
			'tp-heading-title-style_11'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/heading-title/plus-ht-style-11.css',
					),
				),
			),
			'tp-icon'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/icon/icon.css',
					),
				),
			),
			'tp-info-box'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/info-box/plus-infobox-style.css',
					),
				),
			),
			'tp-info-box-style_1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/info-box/plus-infobox-style-1.css',
					),
				),
			),
			'tp-info-box-style_3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/info-box/plus-infobox-style-3.css',
					),
				),
			),
			'tp-info-box-style_4'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/info-box/plus-infobox-style-4.css',
					),
				),
			),
			'tp-messagebox'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/messagebox/plus-messagebox.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/messagebox/plus-messagebox.min.js',
					),
				),
			),
			'tp-navigation-menu-lite'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/navigation-menu-lite/plus-nav-menu-lite.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/navigation-menu-lite/plus-nav-menu-lite.min.js',
					),
				),
			),
			'tp-ninja-form'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/forms-style/plus-ninja-form.css',
					),
				),
			),
			'tp-number-counter'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/number-counter/plus-nc.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/numscroller.js',
					),
				),
			),
			'tp-number-counter-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/number-counter/plus-nc-style-1.css',
					),
				),
			),
			'tp-number-counter-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/number-counter/plus-nc-style-2.css',
					),
				),
			),
			'tp-post-featured-image'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-feature-image/plus-post-image.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/post-feature-image/plus-post-feature-image.min.js',
					),
				),
			),
			'tp-post-title'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-title/plus-post-title.min.css',
					),
				),
			),
			'tp-post-content'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-content/plus-post-content.min.css',
					),
				),
			),
			'tp-post-meta'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-meta-info/plus-post-meta-info.min.css',
					),

				),
			),
			'tp-post-author'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-author/plus-post-author.min.css',
					),
				),
			),
			'tp-post-comment'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/post-comment/plus-post-comment.min.css',
					),
				),
			),
			'tp-post-navigation'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/post-navigation/plus-post-navigation.min.css',
					),
				),
			),
			'tp-page-scroll'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/page-scroll/plus-page-scroll.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/page-scroll/plus-page-scroll.min.js',
					),
				),
			),
			'tp-fullpage'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/fullpage.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/fullpage.js',
					),
				),
			),
			'tp-pricing-table'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/pricing-table/plus-pricing-table.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/pricing-table/plus-pricing-table.min.js',
					),
				),
			),
			'tp-pricing-table-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/pricing-table/plus-pricing-style-1.css',
					),
				),
			),
			'tp-pricing-ribbon'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/pricing-table/plus-table-ribbon.css',
					),
				),
			),
			'tp-progress-bar'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/progress-piechart/plus-progress.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/jquery.waypoints.min.js',
						$tpae_base . 'assets/js/main/progress-bar/plus-progress-bar.min.js',
					),
				),
			),
			'tp-piechart'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/progress-piechart/plus-piechart.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/circle-progress.js',
					),
				),
			),
			'tp-process-steps'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/process-steps/plus-process-steps.css',
					),
				),
			),
			'tp-process-bg'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/process-steps/plus-process-bg.css',
					),
				),
			),
			'tp-process-counter'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/process-steps/plus-process-counter.css',
					),
				),
			),
			'tp-process-steps-js'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/main/process-steps/plus-process-steps.min.js',
					),
				),
			),
			'tp-scroll-navigation'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/scroll-navigation/plus-scroll-navigation.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/pagescroll2id.js',
						$tpae_base . 'assets/js/main/scroll-navigation/plus-scroll-navigation.min.js',
					),
				),
			),
			'tp-scroll-navigation-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/scroll-navigation/plus-sn-style-1.css',
					),
				),
			),
			'tp-social-embed'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-embed/plus-social-embed.min.css',
					),
				),
			),
			'tp-social-icon'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style.css',
					),
				),
			),
			'tp-social-icon-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-1.css',
					),
				),
			),
			'tp-social-icon-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-2.css',
					),
				),
			),
			'tp-social-icon-style-3'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-3.css',
					),
				),
			),
			'tp-social-icon-style-4'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-4.css',
					),
				),
			),
			'tp-social-icon-style-5'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-5.css',
					),
				),
			),
			'tp-social-icon-style-6'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-6.css',
					),
				),
			),
			'tp-social-icon-style-7'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-7.css',
					),
				),
			),
			'tp-social-icon-style-8'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-8.css',
					),
				),
			),
			'tp-social-icon-style-9'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-9.css',
					),
				),
			),
			'tp-social-icon-style-10'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-10.css',
					),
				),
			),
			'tp-social-icon-style-11'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-11.css',
					),
				),
			),
			'tp-social-icon-style-12'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-12.css',
					),
				),
			),
			'tp-social-icon-style-13'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-13.css',
					),
				),
			),
			'tp-social-icon-style-14'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-14.css',
					),
				),
			),
			'tp-social-icon-style-15'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/social-icon/plus-social-icon-style-15.css',
					),
				),
			),
			'tp-syntax-highlighter'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-syntax-highlighter.css',
					),
				),
			),
			'tp-syntax-highlighter-icons'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/tp-copy-dow-icons.js',
					),
				),
			),
			'tp-switcher'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/switcher/plus-switcher.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/switcher/plus-switcher.min.js',
					),
				),
			),
			'prism_default'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-default-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-default.js',
					),
				),
			),
			'prism_coy'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-copy-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-coy.js',
					),
				),
			),
			'prism_dark'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-dark-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-dark.js',
					),
				),
			),
			'prism_funky'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-funky-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-funky.js',
					),
				),
			),
			'prism_okaidia'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-okaidia-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-okaidia.js',
					),
				),
			),
			'prism_solarizedlight'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-solarized.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-solarizedlight.js',
					),
				),
			),
			'prism_tomorrownight'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-tomorrow-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-tomorrownight.js',
					),
				),
			),
			'prism_twilight'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/syntax-highlighter/plus-twilight-theme.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/syntax-highlighter/prism-twilight.js',
					),
				),
			),
			'tp-tabs-tours'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/tabs-tours/plus-tabs-tours.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/tabs-tours/plus-tabs-tours.min.js',
					),
				),
			),
			'tp-team-member-listout'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
						$tpae_base . 'assets/css/main/team-member-list/plus-team-member-style.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-listing.min.js',
					),
				),
			),
			'tp-team-member-listout-style-1' => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/team-member-list/plus-team-member-style-1.css',
					),
				),
			),
			'tp-team-member-listout-style-3' => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/team-member-list/plus-team-member-style-3.css',
					),
				),
			),
			'tp-table'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/data-table/plus-data-table.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/jquery.datatables.min.js',
						$tpae_base . 'assets/js/main/data-table/plus-data-table.min.js',
					),
				),
			),
			'tp-carosual-extra'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/slick.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/slick.min.js',
						$tpae_base . 'assets/js/main/general/plus-slick-carousel.min.js',
					),
				),
			),
			'tp-bootstrap-grid'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
					),
				),
			),
			'tp-testimonial-listout'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/testimonial/plus-testimonial.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/imagesloaded.pkgd.min.js',
						$tpae_base . 'assets/js/main/testimonial/plus-testimonial.min.js',
						$tpae_base . 'assets/js/main/posts-listing/plus-posts-listing.min.js',
					),
				),
			),
			'tp-testimonial-listout-style-1' => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/testimonial/plus-ts1.css',
					),
				),
			),
			'tp-testimonial-listout-style-2' => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/testimonial/plus-ts2.css',
					),
				),
			),
			'tp-testimonial-listout-style-4' => array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/testimonial/plus-ts4.css',
						$tpae_base . 'assets/css/extra/tp-bootstrap-grid.css',
					),
				),
			),
			'tp-arrows-style-2'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/arrows/plus-arrows-style-2.css',
					),
				),
			),
			'tp-arrows-style'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/arrows/plus-arrows-style.css',
					),
				),
			),
			'tp-carousel-style-1'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/carousel/plus-carousel-style-1.css',
					),
				),
			),
			'tp-carousel-style'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/carousel/plus-carousel.css',
					),
				),
			),
			'tp-video-player'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/video-player/plus-video-player.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/video-player/plus-video-player.min.js',
					),
				),
			),
			'tp-lity-extra'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/extra/lity.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/lity.min.js',
					),
				),
			),
			'tp-wp-forms'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/forms-style/plus-wpforms-form.css',
					),
				),
			),
			'plus-velocity'=> array(
				'dependency' => array(
					'js' => array(
						$tpae_base . 'assets/js/extra/jquery.waypoints.min.js',
						$tpae_base . 'assets/js/extra/velocity/velocity.min.js',
						$tpae_base . 'assets/js/extra/velocity/velocity.ui.js',
						$tpae_base . 'assets/js/main/general/plus-animation-load.min.js',
					),
				),
			),
			'plus-alignmnet-effect'=> array(
				'context' => 'edit',
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-alignmnet.css',
					),
				),
			),
			'plus-widget-error'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-widget-error.css',
					),
				),
			),
			'plus-responsive-visibility'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-responsive-visibility.css',
					),
				),
			),
			'plus-content-hover-effect'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-content-hover-effect.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/main/general/plus-content-hover-effect.min.js',
					),
				),
			),
			'plus-lazyLoad'=> array(
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/lazy_load/tp-lazy_load.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/lazyload.min.js',
						$tpae_base . 'assets/js/main/lazy_load/tp-lazy_load.js',
					),
				),
			),
			'tp-temp-notice'=> array(
				'context' => 'edit',
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-temp-notice.css',
					),
				),
			),
			'plus-backend-editor'=> array(
				'context' => 'edit',
				'dependency' => array(
					'css' => array(
						$tpae_base . 'assets/css/main/plus-extra-adv/plus-content-hover-effect.min.css',
					),
					'js'  => array(
						$tpae_base . 'assets/js/extra/jquery.waypoints.min.js',
						$tpae_base . 'assets/js/extra/general/modernizr.min.js',
						$tpae_base . 'assets/js/extra/velocity/velocity.min.js',
						$tpae_base . 'assets/js/extra/velocity/velocity.ui.js',
						$tpae_base . 'assets/js/main/plus-extra-adv/plus-backend-editor.min.js',
						$tpae_base . 'assets/js/main/general/plus-animation-load.min.js',
						$tpae_base . 'assets/js/main/general/plus-content-hover-effect.min.js',
						$tpae_base . 'assets/js/admin/tp-advanced-shadow-layout.js',
					),
				),
			),
		)
;
