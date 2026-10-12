/*tabs & tours*/(function($) {
	"use strict";
	var WidgetTabHandler = function ($scope, $) {
		var $currentTab = $scope.find('.theplus-tabs-wrapper'),
            container = $scope[0].querySelectorAll('.theplus-tabs-wrapper'),
            $TabHover = $currentTab.data('tab-hover'),
            $currentTabId = '#' + $currentTab.attr('id').toString();

            if( container[0].classList.contains('mobile-accordion') ){
                var GetAccordion = container[0].querySelectorAll('.theplus-tabs-content-wrapper .elementor-tab-title');
                    if(GetAccordion.length > 0){
                        GetAccordion[0].classList.add('active');
                    }
            }

            $($currentTabId + ' ul.plus-tabs-nav li .plus-tab-header').each( function(index) {
                var default_active=$(this).closest('.theplus-tabs-wrapper').data("tab-default");
                if( default_active == index ) {
                    $(this).removeClass('inactive').addClass('active');
                }			
            });

            $($currentTabId + ' .theplus-tabs-content-wrapper .plus-tab-content').each( function(index) {
                var default_active=$(this).closest('.theplus-tabs-wrapper').data("tab-default");
                if( default_active == index ) {
                    $(this).removeClass('inactive').addClass('active');
                }
            });
            
            if('no' == $TabHover){
                $($currentTabId + ' ul.plus-tabs-nav li .plus-tab-header').on('click',function(){
                    var currentTabIndex = $(this).data("tab");
                    var tabsContainer = $(this).closest('.theplus-tabs-wrapper');
                    var tabsNav = $(tabsContainer).children('ul.plus-tabs-nav').children('li').children('.plus-tab-header');
                    var tabsContent = $(tabsContainer).children('.theplus-tabs-content-wrapper').children('.plus-tab-content');
                    
                    $(tabsContainer).find(">.theplus-tabs-nav-wrapper .plus-tab-header").removeClass('active default-active').addClass('inactive');
                    $(this).addClass('active').removeClass('inactive');
                    
                    $(tabsContainer).find(">.theplus-tabs-content-wrapper>.plus-tab-content").removeClass('active').addClass('inactive');
                    $(">.theplus-tabs-content-wrapper>.plus-tab-content[data-tab='"+currentTabIndex+"']",tabsContainer).addClass('active').removeClass('inactive');
                
                    $(tabsContent).each( function(index) {
                        $(this).removeClass('default-active');
                    });				
                    
                    if($($currentTabId+" .list-carousel-slick > .post-inner-loop").length){
                        $($currentTabId+" .list-carousel-slick > .post-inner-loop").slick('setPosition');	
                    }
                });
            }
            
            if($($currentTabId).hasClass("mobile-accordion")){
                $(window).on("resize",function() {
                    if($(window).innerWidth() <= 600){
                        $($currentTabId).addClass("mobile-accordion-tab");
                    }
                });
                $($currentTabId + ' .theplus-tabs-content-wrapper .elementor-tab-mobile-title').on('click',function(){
                    var currentTabIndex = $(this).data("tab");
                    var tabsContainer = $(this).closest('.theplus-tabs-wrapper');
                    var tabsNav = $(tabsContainer).children('.theplus-tabs-content-wrapper').children('.elementor-tab-mobile-title');
                    var tabsContent = $(tabsContainer).children('.theplus-tabs-content-wrapper').children('.plus-tab-content');
                
                    $(tabsContainer).find(">.theplus-tabs-content-wrapper .elementor-tab-mobile-title").removeClass('active default-active').addClass('inactive');
                    $(this).addClass('active').removeClass('inactive');
                
                    $(tabsContainer).find(">.theplus-tabs-content-wrapper>.plus-tab-content").removeClass('active').addClass('inactive');
                    $(">.theplus-tabs-content-wrapper>.plus-tab-content[data-tab='"+currentTabIndex+"']",tabsContainer).addClass('active').removeClass('inactive');
                
                    $(tabsContent).each( function(index) {
                        $(this).removeClass('default-active');
                    });
                    
                    if($($currentTabId+" .list-carousel-slick > .post-inner-loop").length){
                        $($currentTabId+" .list-carousel-slick > .post-inner-loop").slick('setPosition');
                    }
                });
            }		
	};
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-tabs-tours.default', WidgetTabHandler);
	});
})(jQuery);

/*
 * a11y: keep aria-selected in sync with the active tab (WCAG 4.1.2).
 *
 * Deliberately NOT emitted as a static attribute in PHP: the widget switches tabs
 * by toggling the `active` class, so a hardcoded aria-selected would go stale the
 * moment a visitor clicks, and an active tab reporting aria-selected="false" is
 * worse for a screen reader than no attribute at all. Deriving it from the class,
 * on init and after every switch, cannot desync.
 *
 * Purely additive: only ever ADDS an attribute. No class, handler or markup is
 * changed, so existing CSS and JS are untouched.
 *
 * The mobile titles carry elementor-tab-mobile-title, not plus-tab-header, so a
 * selector on plus-tab-header alone reached the desktop pair only - leaving the
 * mobile tabs with role=tab and tabindex=0 but no aria-selected, and unreachable
 * by Enter/Space. Both selectors are matched below.
 */
(function ($) {
	"use strict";

	function tpaeSyncTabSelected( root ) {
		$( root || document ).find( '.plus-tab-header[role="tab"], .elementor-tab-mobile-title[role="tab"]' ).each( function () {
			$( this ).attr( 'aria-selected', $( this ).hasClass( 'active' ) ? 'true' : 'false' );
		} );
	}

	$( document ).on( 'click keyup', '.plus-tab-header[role="tab"], .elementor-tab-mobile-title[role="tab"]', function () {
		var wrap = $( this ).closest( '.theplus-tabs-wrapper' );
		setTimeout( function () { tpaeSyncTabSelected( wrap.length ? wrap : document ); }, 0 );
	} );

	/*
	 * a11y: keyboard activation (WCAG 2.1.1). The tab header already has
	 * tabindex="0" and role="tab" (PHP), so it's in the tab order and
	 * reachable -- but nothing responded to Enter/Space, matching the fix
	 * already applied to the sibling Accordion widget's header. Triggers
	 * both 'click' and 'mouseover': WidgetTabHandler binds the tab switch
	 * to 'click' when tab-hover="no" (this file's own default) but to
	 * 'mouseover' when tab-hover="yes" (Pro's copy of this same file has a
	 * live, user-facing hover-mode setting that uses 'mouseover' instead --
	 * this is written to stay correct for both without duplicating the
	 * tab-switch logic itself; triggering an event with no bound handler
	 * is a harmless no-op).
	 */
	$( document ).on( 'keydown', '.plus-tab-header[role="tab"], .elementor-tab-mobile-title[role="tab"]', function ( e ) {
		if ( 13 === e.which || 32 === e.which ) {
			e.preventDefault();
			$( this ).trigger( 'click' ).trigger( 'mouseover' );
		}
	} );

	$( window ).on( 'elementor/frontend/init', function () {
		if ( window.elementorFrontend && elementorFrontend.hooks ) {
			elementorFrontend.hooks.addAction( 'frontend/element_ready/tp-tabs-tours.default', function ( $scope ) {
				tpaeSyncTabSelected( $scope );
			} );
		}
	} );
})(jQuery);
