(function ($) {
	'use strict';
	$(document).ready(function () {
		if($(".plus-navigation-wrap .plus-navigation-inner.menu-click").length>=1){
			theplus_ele_menu_clicking();
		}
	});
	
	var id;
	$(window).on("load resize",function(e){
		e.preventDefault();
		var inner_width = window.innerWidth;
		if(inner_width > 991){
			if($(".plus-navigation-wrap .plus-navigation-inner").hasClass("menu-hover")){
				theplus_navmenu_hover();
			}
		}
		//Mobile Menu Full Width
		if($('.plus-mobile-menu-content').length){
			var offeset=$(".plus-mobile-menu-content").closest(".plus-navigation-wrap");
			var window_width = $(window).width();
			var menu_content=$(".plus-mobile-menu-content");
			var offset_left = 0 - offeset.offset().left;
			menu_content.css({
					left: offset_left,
					"box-sizing": "border-box",
					width: window_width
			});
		}
	});
} )(jQuery);
function theplus_navmenu_hover(){
	var $= jQuery;
	/*
	 * N2 (widget-test/navigation-menu): dropdowns opened on mouseenter/mouseleave
	 * only, so focusing the parent link with Tab never opened the submenu --
	 * its children never entered the tab order. Add focusin/focusout alongside
	 * the existing mouse handlers, the JS equivalent of CSS :focus-within, since
	 * this widget's open/close is driven by slideDown/slideUp, not a plain CSS
	 * :hover rule. focusout's relatedTarget tells us whether focus moved to
	 * another element still inside this dropdown (down into a nested submenu)
	 * or left it entirely -- only close in the latter case, otherwise Tab-ing
	 * through a dropdown's own items would close it after the first item.
	 */
	$(".plus-navigation-wrap .menu-hover .navbar-nav .dropdown").on("mouseenter focusin", function() {
		var $this = $(this);
		var $container = $this.closest(".plus-navigation-inner");
		var transition_style = $container.data("menu_transition");
		if(transition_style=='' || transition_style=='style-1'){
			$this.find("> .dropdown-menu").stop().slideDown();
		}else if(transition_style=='style-2'){
			$this.find("> .dropdown-menu").stop(true, true).delay(100).fadeIn(600);
		}
		// N4: keep the desktop parent's aria-expanded in sync with the visible state.
		$this.children('a[aria-haspopup="true"]').attr("aria-expanded", "true");
	}).on("mouseleave focusout", function(e) {
		var $this = $(this);
		if(e.type === "focusout" && e.relatedTarget && $this.has(e.relatedTarget).length){
			return;
		}
		var $container = $this.closest(".plus-navigation-inner");
		var transition_style = $container.data("menu_transition");
		if(transition_style=='' || transition_style=='style-1'){
			$this.find("> .dropdown-menu").stop().slideUp();
		}else if(transition_style=='style-2'){
			$this.find("> .dropdown-menu").stop(true, true).delay(100).fadeOut(400);
		}
		$this.children('a[aria-haspopup="true"]').attr("aria-expanded", "false");
	});
	$(".plus-navigation-wrap .menu-hover .navbar-nav .dropdown-submenu").on("mouseenter focusin", function() {
		var $this = $(this);
		var $container = $this.closest(".plus-navigation-inner");
		var transition_style = $container.data("menu_transition");
		if(transition_style=='' || transition_style=='style-1'){
			$this.find("> .dropdown-menu").stop().slideDown();
		}else if(transition_style=='style-2'){
			$this.find("> .dropdown-menu").stop(true, true).delay(100).fadeIn(600);
		}
	}).on("mouseleave focusout", function(e) {
		var $this = $(this);
		if(e.type === "focusout" && e.relatedTarget && $this.has(e.relatedTarget).length){
			return;
		}
		var $container = $this.closest(".plus-navigation-inner");
		var transition_style = $container.data("menu_transition");
		if(transition_style=='' || transition_style=='style-1'){
			$this.find("> .dropdown-menu").stop().slideUp();
		}else if(transition_style=='style-2'){
			$this.find("> .dropdown-menu").stop(true, true).delay(100).fadeOut(400);
		}
	});
}
function theplus_ele_menu_clicking(){
	"use strict";	
	var $=jQuery;
		$('.plus-navigation-wrap .menu-click .plus-navigation-menu .navbar-nav li.menu-item-has-children > a').on('click', function (event) {
			event.preventDefault();
			event.stopPropagation();
			if($(this).closest(".plus-navigation-inner.menu-click")){
				var navSideBut = $(this),
				navSideItem = navSideBut.parent(),
				navSideUl = navSideBut.parent().parent(),
				navSideItemSub = navSideItem.find('> ul.dropdown-menu');
				// N4: only the desktop depth-0 parent link carries aria-haspopup/aria-expanded.
				var isDesktopParent = navSideBut.attr('aria-haspopup') === 'true';
				if (navSideItem.hasClass('open')) {
					navSideItemSub.slideUp(400);
					navSideItem.removeClass('open');
					if(isDesktopParent){
						navSideBut.attr('aria-expanded', 'false');
					}
				} else {
				navSideUl.css("height","auto");
				navSideUl.find('li.dropdown.open ul.dropdown-menu').slideUp(400);
				navSideUl.find('li.dropdown-submenu.open ul.dropdown-menu').slideUp(400);
				navSideUl.find('li.dropdown,li.dropdown-submenu.open').removeClass('open');
				navSideUl.find('> li.dropdown > a[aria-haspopup="true"]').attr('aria-expanded', 'false');
					navSideItemSub.slideDown(400);
					navSideItem.addClass('open');
					if(isDesktopParent){
						navSideBut.attr('aria-expanded', 'true');
					}
				}
			}
		});
		$(document).on('mouseup', function (e) {
			var $menu = $('li.dropdown');
			if (!$menu.is(e.target) && $menu.has(e.target).length === 0){
				$menu.find('ul.dropdown-menu').slideUp(400);
				$menu.find('li.dropdown-submenu.open ul.dropdown-menu').slideUp(400);
				$menu.removeClass('open');
				$menu.children('a[aria-haspopup="true"]').attr('aria-expanded', 'false');
		   }
		});
}
(function ($) {
	'use strict';
	var WidgetHeaderNavigation = function($scope, $) {
		var $plus_navigation = $scope.find('.plus-navigation-wrap');
        if($(".mobile-plus-toggle-menu", $scope).length > 0){
			$(".mobile-plus-toggle-menu", $scope).on('click', function() {
				var target = $(this).data("target");
				var $toggle = $(this);
				$toggle.toggleClass("plus-collapsed");
				if ($(target +'.collapse:not(".in")').length) {

				  $(target +'.collapse:not(".in")').slideDown(400);
				  $(target +'.collapse:not(".in")').addClass('in');
				  $toggle.attr('aria-expanded', 'true');
				} else {
				  $(target + '.collapse.in').slideUp(400);
				  $(target +'.collapse.in').removeClass('in');
				  $toggle.attr('aria-expanded', 'false');
				}
			});
		}

		if ($(".plus-mobile-menu .navbar-nav li.menu-item-has-children.open", $scope).length === 0) {
			$(".plus-mobile-menu .navbar-nav li.menu-item-has-children > ul.dropdown-menu", $scope).css("display", "none");
		}

        $(".plus-mobile-menu .navbar-nav li.menu-item-has-children > a", $scope).on("click", function(a) {
            a.preventDefault(),
            a.stopPropagation();
            var b = $(this)
              , c = b.parent()
              , d = b.parent().parent()
              , e = c.find("> ul.dropdown-menu");
            c.hasClass("open") ? (e.slideUp(400),
            c.removeClass("open")) : (d.css("height", "auto"),
            d.find("li.dropdown.open ul.dropdown-menu").slideUp(400),
            d.find("li.dropdown-submenu.open ul.dropdown-menu").slideUp(400),
            d.find("li.dropdown,li.dropdown-submenu.open").removeClass("open"),
            e.slideDown(400),
            c.addClass("open"))
        })
		
		if($plus_navigation.find(".hover-inverse-effect").length >0){
			$(".plus-navigation-menu .nav > li > a").on({
			  mouseenter: function() {
				$( this ).closest(".hover-inverse-effect").addClass("is-hover-inverse");
				$( this ).addClass( "is-hover" );
			  }, mouseleave: function() {
				$( this ).closest(".hover-inverse-effect").removeClass("is-hover-inverse");
				$( this ).removeClass( "is-hover" );
			  }
			});
		}
		if($plus_navigation.find(".submenu-hover-inverse-effect").length >0){
			$(".plus-navigation-menu .nav li.dropdown .dropdown-menu > li > a").on({
			  mouseenter: function() {
				$( this ).closest(".submenu-hover-inverse-effect").addClass("is-submenu-hover-inverse");
				$( this ).addClass( "is-hover" );
			  }, mouseleave: function() {
				$( this ).closest(".submenu-hover-inverse-effect").removeClass("is-submenu-hover-inverse");
				$( this ).removeClass( "is-hover" );
			  }
			});
		}
		
		var inner_width = window.innerWidth;
		if(inner_width > 991){
			if($plus_navigation.find(".plus-navigation-inner").hasClass("menu-hover")){
				theplus_navmenu_hover();
			}
		}
	};
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction('frontend/element_ready/tp-navigation-menu-lite.default', WidgetHeaderNavigation);
	});
})(jQuery);