/*slick carousel*/
( function( $ ) {
	"use strict";
	var WidgetThePlusHandler = function ($scope, $) {
		var wid_sec = $scope.parents('section.elementor-element,.elementor-element.e-container,.elementor-element.e-con');
		if( wid_sec.find('.list-carousel-slick').length > 0){
			var carousel_elem = $scope.find('.list-carousel-slick').eq(0);
				if (carousel_elem.length > 0) {
					if(!carousel_elem.hasClass("done-carousel")){
						theplus_carousel_list();
					}
				}
		}
	};

	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/global', WidgetThePlusHandler);
	});
})(jQuery);

function theplus_carousel_list(data_widget=''){	
	var $ = jQuery;
	$('.list-carousel-slick').each(function() {
		var $self = $(this);			
		var $uid = $self.data("id");
		var slide_speed = $self.data("slide_speed");
		var default_active_slide = $self.data("default_active_slide");
		var slider_desktop_column = $self.data("slider_desktop_column");
		var steps_slide =$self.data("steps_slide");
		var slider_padding = $self.data("slider_padding");
		
		var slider_draggable = $self.data("slider_draggable");
		var slider_infinite = $self.data("slider_infinite");
		var slider_adaptive_height = $self.data("slider_adaptive_height");
		var slider_autoplay = $self.data("slider_autoplay");
		var autoplay_speed = $self.data("autoplay_speed");
		var slider_rows = $self.data("slider_rows");
					
		var slider_dots = $self.data("slider_dots");
		var slider_dots_style = $self.data("slider_dots_style");
		
		var slider_arrows=$self.data("slider_arrows");

		var slider_tablet_column=$self.data("slider_tablet_column");
		var slider_mobile_column=$self.data("slider_mobile_column");

        var data = $self[0].dataset;

        const parsedData = data && data.result ? JSON.parse(data.result) : {};
        var getDIrection = parsedData.carousel_direction,
            rtlVal = false;

        if( 'rtl' === getDIrection ){
            rtlVal = true;
        }

		if( steps_slide == '1' ){
			steps_slide == '1';
		}else{
			steps_slide = slider_desktop_column;
		}	
		
		var prev_arrow = '<button type="button" class="slick-nav slick-prev style-2" aria-label="tp-previous-btn"><span class="icon-wrap"></span></button>';
		var next_arrow = '<button type="button" class="slick-nav slick-next style-2" aria-label="tp-next-btn"><span class="icon-wrap"></span></button>';
	
		if( default_active_slide == undefined ){
			default_active_slide = 0;
		}

		var args = {
			dots: slider_dots,
			vertical: false,
			fade:false,
			arrows: slider_arrows,
			infinite: slider_infinite,
			speed: slide_speed,
			initialSlide: default_active_slide,
			adaptiveHeight: slider_adaptive_height,
			autoplay: slider_autoplay,
			autoplaySpeed: autoplay_speed,
			pauseOnHover: false,
			centerMode: false,
			centerPadding: 0,
			prevArrow: prev_arrow,
			nextArrow: next_arrow,
			slidesToShow: slider_desktop_column,
			slidesToScroll: 1,
			draggable:slider_draggable,
			dotsClass:slider_dots_style,
            rtl: rtlVal,
            responsive: [
                {
                    breakpoint: 1024,
                    settings: {
                        slidesToShow: slider_tablet_column,
                    }
                },
                {
                    breakpoint: 768,
                    settings: {
                        slidesToShow: slider_mobile_column,
                    }
                }
            ]
            
		}

		if( !$(this).hasClass("done-carousel") ){
			var $track = $('> .post-inner-loop',this);
			$track.slick(args);
				setTimeout(function(){
					$(".slick-dots.style-2 li").each(function(){
						if($(this).find("svg").length==0){
							$(this).append('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="100%" height="100%" viewBox="0 0 16 16" preserveAspectRatio="none"><circle cx="8" cy="8" r="6.215"></circle></svg>');
						}
					});
				}, 1000);

			if( slider_autoplay ){
				tpaeAddCarouselPauseToggle( $self, $track );
			}

			tpaeFixCarouselDotAria( $track );
			tpaeObserveCarouselDotAria( $track );

			$(this).addClass("done-carousel");
		}
	});
}

/*
 * T5/T6 (reports/widget-test/testimonial): Slick's own dots markup sets
 * aria-selected="true" on the active tab but leaves it absent (not "false")
 * on every inactive one, and never wires aria-labelledby from a slide panel
 * back to the dot that controls it -- both real gaps in an otherwise-correct
 * tablist pattern.
 */
function tpaeFixCarouselDotAria( $track ) {
	var $ = jQuery;
	var $dots = $track.closest(".list-carousel-slick").find(".slick-dots > li > button");
	$dots.each( function( i ) {
		var $dot = $( this );
		if ( "true" !== $dot.attr( "aria-selected" ) && "false" !== $dot.attr( "aria-selected" ) ) {
			$dot.attr( "aria-selected", "false" );
		}
		if ( ! $dot.attr( "id" ) ) {
			$dot.attr( "id", $dot.attr( "aria-controls" ) + "-tab" );
		}
		var $panel = $( "#" + $dot.attr( "aria-controls" ) );
		if ( $panel.length && ! $panel.attr( "aria-labelledby" ) ) {
			$panel.attr( "aria-labelledby", $dot.attr( "id" ) );
		}
	});
}

/*
 * Binding a jQuery "afterChange" listener on the slick track turned out
 * unreliable in testing -- it does not fire for every transition path
 * (autoplay tick vs. dot click vs. arrow key each go through slightly
 * different internal Slick code). A MutationObserver watching the dots'
 * own aria-selected attribute is robust to that: it reacts to the actual
 * DOM change Slick makes (setting the new active dot's aria-selected to
 * "true"), regardless of what triggered it. The re-entrancy guard exists
 * because tpaeFixCarouselDotAria() itself sets aria-selected on the
 * *other* dots, which would otherwise re-trigger this same observer.
 */
function tpaeObserveCarouselDotAria( $track ) {
	if ( ! ( "MutationObserver" in window ) ) {
		return;
	}
	var $dotsContainer = $track.closest(".list-carousel-slick").find(".slick-dots").get(0);
	if ( ! $dotsContainer ) {
		return;
	}
	var handling = false;
	var observer = new MutationObserver( function() {
		if ( handling ) {
			return;
		}
		handling = true;
		tpaeFixCarouselDotAria( $track );
		handling = false;
	});
	observer.observe( $dotsContainer, { attributes: true, attributeFilter: [ "aria-selected" ], subtree: true } );
}

/*
 * WCAG 2.2.2 (Pause, Stop, Hide), Level A: this carousel autoplays with no
 * way to stop it -- pauseOnHover is hardcoded off a few lines above, and
 * there is no keyboard/touch equivalent to hover anyway. Every widget that
 * shares this init function (Testimonial, and any other .list-carousel-slick
 * user) gets a real pause/play toggle when autoplay is on, rather than
 * fixing this per widget. Visually hidden until it takes keyboard focus, so
 * existing layouts render unchanged for sighted mouse users -- same shape as
 * the Video Player pause control shipped on v6.5.2.
 */
function tpaeAddCarouselPauseToggle( $self, $track ) {
	var $ = jQuery;
	if ( $self.find(".tpae-carousel-pause-toggle").length > 0 ) {
		return;
	}
	tpaeEnsureCarouselPauseToggleStyle();

	/*
	 * Labels come from the globals Free's generator prints (translatable via
	 * tpebl). A plain JS string cannot go through WordPress translation
	 * functions; the English fallback keeps the control usable if the inline
	 * script is absent.
	 */
	var pausedLabel = ( "undefined" !== typeof theplus_carousel_play ) ? theplus_carousel_play : "Play automatic slideshow";
	var playingLabel = ( "undefined" !== typeof theplus_carousel_pause ) ? theplus_carousel_pause : "Pause automatic slideshow";
	var $btn = $( '<button type="button" class="tpae-carousel-pause-toggle" aria-pressed="false" aria-label="' + playingLabel + '">&#10073;&#10073;</button>' );

	$btn.on( "click", function() {
		var isPaused = "true" === $btn.attr("aria-pressed");
		if ( isPaused ) {
			$track.slick("slickPlay");
			$btn.attr( "aria-pressed", "false" ).attr( "aria-label", playingLabel ).html("&#10073;&#10073;");
		} else {
			$track.slick("slickPause");
			$btn.attr( "aria-pressed", "true" ).attr( "aria-label", pausedLabel ).html("&#9654;");
		}
	});

	/*
	 * Appended to a new zero-height, relatively-positioned anchor rather than
	 * setting position:relative on the carousel itself. .slick-dots inside it is
	 * position:absolute, so making the carousel a positioning context would
	 * re-anchor the dots and move them on any site where the carousel was static.
	 */
	var $anchor = $( '<div class="tpae-carousel-pause-anchor"></div>' );
	$self.prepend( $anchor );
	$anchor.append( $btn );
}

function tpaeEnsureCarouselPauseToggleStyle() {
	var $ = jQuery;
	if ( $("#tpae-carousel-pause-toggle-style").length > 0 ) {
		return;
	}
	$( "<style>", { id: "tpae-carousel-pause-toggle-style" } ).text(
		".tpae-carousel-pause-anchor{position:relative;height:0}" +
		".tpae-carousel-pause-toggle{position:absolute;top:8px;right:8px;z-index:9;" +
		"width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);" +
		"white-space:nowrap;border:0;background:rgba(0,0,0,.6);color:#fff;cursor:pointer;" +
		"font-size:14px;line-height:1;border-radius:3px;}" +
		".tpae-carousel-pause-toggle:focus{width:auto;height:auto;padding:6px 10px;margin:0;" +
		"overflow:visible;clip:auto;outline:2px solid #fff;outline-offset:2px;}"
	).appendTo("head");
}