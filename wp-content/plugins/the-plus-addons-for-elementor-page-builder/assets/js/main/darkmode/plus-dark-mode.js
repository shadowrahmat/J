/*dark mode */
(function($) {	
	
    var WidgetDarkMode = function($scope, $) {
		if($("body").hasClass('darkmode--activated')){			
			$( '.darkmode-toggle' ).addClass( "darkmode-toggle--white" );	
		}
		var container = $scope.find(".tp-dark-mode-wrapper"),
			time = container.data('time'),
			dm_mixcolor = container.data('dm_mixcolor'),
			bgcolor = container.data('bgcolor'),
			save_cookies = container.data('save-cookies'),
			auto_match_os_theme = container.data('auto-match-os-theme'),
			style = container.data('style'),
			toggle_label = container.data('toggle-label');
		var label_tag = '';
		$( "body" ).addClass( style );
		if(style=="tp_dm_style2"){
			label_tag = '<span class="tp-dark-mode-slider tp-dark-mode-round "></span>';
		}else{
			label_tag = '🌓';
		}
		var options = {
			left: 'unset',
			time: time,
			mixColor: dm_mixcolor,
			backgroundColor: bgcolor,
			buttonColorDark: '#100f2c',
			buttonColorLight: '#fff',
			saveInCookies: save_cookies,
			label: label_tag,
			autoMatchOsTheme: auto_match_os_theme
		}
		const darkmode = new Darkmode(options);
        darkmode.showWidget();

		/*
		 * A11Y-029: the vendored darkmode.min.js library builds its <button>
		 * with innerHTML = options.label -- a decorative <span> (style 2) or a
		 * bare emoji (style 1), neither of which gives the button a discernible
		 * accessible name (axe: button-name, critical). Fixed here, not in the
		 * vendored library, since darkmode.min.js is third-party and any
		 * hand-patch there would need to be reapplied on every library update.
		 * darkmode.button is the exact element showWidget() attaches the click
		 * handler to. The label itself comes from the widget's own
		 * data-toggle-label attribute (PHP-rendered, translatable via tpebl),
		 * matching the data-attribute pattern this widget already uses for
		 * every other server-to-JS value above -- a plain JS string can't be
		 * run through WordPress's translation functions.
		 */
		if ( darkmode.button ) {
			darkmode.button.setAttribute( 'aria-label', toggle_label || 'Toggle dark mode' );
		}

    };
    $(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/tp-dark-mode.default', WidgetDarkMode);
    });
})(jQuery);
/*dark mode */