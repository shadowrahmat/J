/*equal height*/
(function($){	
	$.fn.equalHeights = function() {
		var max_height = 0;

		jQuery(this).each(function() {
			max_height = Math.max( jQuery(this).outerHeight(), max_height );
		});

		jQuery(this).each(function() {
			jQuery(this).css( 'min-height', max_height );
		});
	};

	jQuery(document).ready(function() {
		EqualHeightsLoadded();
	});

	jQuery(window).on("load resize",function() {
		EqualHeightsLoadded();
	});

	/**Equal Height*/
	function EqualHeightsLoadded(){
		var container = jQuery('.elementor-element[data-tp-equal-height-loadded]');
		if( container.length > 0 ){
			container.each(function() {
				var id = jQuery(this).data('id'),
					new_find = jQuery(this).data('tp-equal-height-loadded');

					var items = jQuery(`.elementor-element-${id} ${new_find}`);
					items.equalHeights();
					tpaeObserveEqualHeightItems( items );
			});
		}
	}

	/*
	 * EXT-012. This used to only recalculate on document.ready and window
	 * load/resize, so a container starting inside a display:none tab/
	 * accordion panel measured 0 height at load and baked in min-height:0,
	 * staying broken until the browser window happened to be resized;
	 * content added later (AJAX "Load More", popups) never got equal-height
	 * applied at all. A ResizeObserver on each matched item picks up the
	 * display:none -> visible transition (and any later resize from
	 * AJAX-loaded content) without needing to know about every possible
	 * tab/accordion/popup widget's own show/hide mechanism.
	 */
	var tpaeEqualHeightObserved = ( 'WeakSet' in window ) ? new WeakSet() : null;
	var tpaeEqualHeightResizeTimer = null;

	function tpaeObserveEqualHeightItems( items ) {
		if ( ! ( 'ResizeObserver' in window ) || ! tpaeEqualHeightObserved ) {
			return;
		}

		items.each(function() {
			if ( tpaeEqualHeightObserved.has( this ) ) {
				return;
			}
			tpaeEqualHeightObserved.add( this );

			var ro = new ResizeObserver(function() {
				clearTimeout( tpaeEqualHeightResizeTimer );
				tpaeEqualHeightResizeTimer = setTimeout( EqualHeightsLoadded, 100 );
			});
			ro.observe( this );
		});
	}

}(jQuery));

