(function($){
    'use strict';

    function initFilter($root){
        var page = 1;
        var loading = false;
        var refreshAttributesOnLoad = false;
        var $sidebar = $root.find('.mixora-sidebar');
        var $products = $root.find('.mixora-products');
        var $count = $root.find('.mixora-result-count');
        var $title = $root.find('.mixora-results-title');
        var $dynamicAttributes = $root.find('.mixora-dynamic-attributes');
        var $pagination = $root.find('.mixora-pagination');

        function currentSort(){
            if (window.matchMedia('(max-width: 767px)').matches) return $root.find('.mixora-sort-mobile').val() || 'menu_order';
            return $root.find('.mixora-sort-desktop').val() || 'menu_order';
        }

        function ajaxData(){
            var data = {
                action: 'mixora_filter_products',
                nonce: MixoraSmartWooFilter.nonce,
                page: page,
                per_page: parseInt($root.data('per-page'), 10) || 9,
                sort: currentSort(),
                current_category: parseInt($root.data('current-category'), 10) || 0
            };
            $sidebar.find('input[name="min_price"], input[name="max_price"]').each(function(){
                if ($(this).val() !== '') data[$(this).attr('name')] = $(this).val();
            });
            $sidebar.find('input[type="checkbox"]:checked').each(function(){
                var name = $(this).attr('name'), value = $(this).val();
                if (name && /\[\]$/.test(name)) {
                    var key = name.replace(/\[\]$/, '');
                    data[key] = data[key] || [];
                    data[key].push(value);
                }
            });
            return data;
        }

        function updateBrowserUrl(data) {
            if (!window.history || !window.history.pushState) return;
            var params = new URLSearchParams();
            if (data.page && data.page > 1) params.set('filter_page', data.page);
            if (data.sort && data.sort !== 'menu_order') params.set('sort', data.sort);
            if (data.min_price) params.set('min_price', data.min_price);
            if (data.max_price) params.set('max_price', data.max_price);
            if (data.category && data.category.length) {
                params.set('category', data.category.join(','));
            }
            for (var k in data) {
                if (k.indexOf('pa_') === 0 && Array.isArray(data[k]) && data[k].length) {
                    params.set(k, data[k].join(','));
                }
            }
            var qs = params.toString();
            var newUrl = window.location.pathname + (qs ? '?' + qs : '');
            if (window.location.search !== (qs ? '?' + qs : '')) {
                window.history.pushState({ mixoraFilter: true }, '', newUrl);
            }
        }

        function applyUrlParams() {
            var params = new URLSearchParams(window.location.search);
            var hasParams = false;
            if (params.get('filter_page')) {
                page = parseInt(params.get('filter_page'), 10) || 1;
                hasParams = true;
            }
            if (params.get('sort')) {
                $root.find('.mixora-sort').val(params.get('sort'));
                hasParams = true;
            }
            if (params.get('min_price')) {
                $sidebar.find('input[name="min_price"]').val(params.get('min_price'));
                hasParams = true;
            }
            if (params.get('max_price')) {
                $sidebar.find('input[name="max_price"]').val(params.get('max_price'));
                hasParams = true;
            }
            params.forEach(function(val, key) {
                if (key === 'category' || key.indexOf('pa_') === 0) {
                    var vals = val.split(',');
                    vals.forEach(function(v) {
                        $sidebar.find('input[name="' + key + '[]"][value="' + v + '"]').prop('checked', true);
                    });
                    hasParams = true;
                }
            });
            return hasParams;
        }

        function renderPagination(pages, active){
            $pagination.empty();
            if (!pages || pages <= 1) return;
            var start = Math.max(1, active - 2), end = Math.min(pages, active + 2);
            if (active > 1) $pagination.append('<button type="button" data-page="'+(active-1)+'" aria-label="Previous page">‹</button>');
            if (start > 1) {
                $pagination.append('<button type="button" data-page="1">1</button>');
                if (start > 2) $pagination.append('<span>…</span>');
            }
            for (var i=start;i<=end;i++) {
                $pagination.append('<button type="button" data-page="'+i+'" class="'+(i===active?'is-active':'')+'">'+i+'</button>');
            }
            if (end < pages) {
                if (end < pages-1) $pagination.append('<span>…</span>');
                $pagination.append('<button type="button" data-page="'+pages+'">'+pages+'</button>');
            }
            if (active < pages) $pagination.append('<button type="button" data-page="'+(active+1)+'" aria-label="Next page">›</button>');
        }

        function updateActiveCount(){
            var n = $sidebar.find('input[type="checkbox"]:checked').length;
            if ($sidebar.find('input[name="min_price"]').val()) n++;
            if ($sidebar.find('input[name="max_price"]').val()) n++;
            $root.find('.mixora-active-count').text(n ? '('+n+')' : '');
        }

        function loadProducts(scrollToResults, skipPushState){
            if (loading) return;
            loading = true;
            $root.addClass('is-loading');
            var reqData = ajaxData();
            if (!skipPushState) {
                updateBrowserUrl(reqData);
            }
            $.ajax({
                url: MixoraSmartWooFilter.ajaxUrl,
                type: 'POST',
                dataType: 'json',
                data: reqData
            }).done(function(resp){
                if (!resp || !resp.success) return;
                $products.html(resp.data.html || '');
                $count.text((resp.data.count || 0) + ' Products');
                $title.text(resp.data.result_title || $root.data('current-category-name') || 'All Products');
                if (refreshAttributesOnLoad && typeof resp.data.attributes_html !== 'undefined') {
                    $dynamicAttributes.html(resp.data.attributes_html || '');
                    refreshAttributesOnLoad = false;
                }
                renderPagination(parseInt(resp.data.pages,10)||0, parseInt(resp.data.page,10)||1);
                if (scrollToResults && window.matchMedia('(max-width: 767px)').matches) {
                    $('html,body').animate({scrollTop: $root.offset().top - 80}, 250);
                }
            }).always(function(){
                loading = false;
                $root.removeClass('is-loading');
                updateActiveCount();
            });
        }

        function closeDrawer(){
            $root.removeClass('filter-open');
            $root.find('.mixora-filter-toggle').attr('aria-expanded','false');
            $('body').removeClass('mixora-filter-lock');
        }

        $root.on('click', '.mixora-filter-toggle', function(){
            $root.addClass('filter-open');
            $(this).attr('aria-expanded','true');
            $('body').addClass('mixora-filter-lock');
            window.setTimeout(function(){ $root.find('.mixora-close').trigger('focus'); }, 80);
        });
        $root.on('click', '.mixora-close, .mixora-filter-overlay', closeDrawer);

        $root.on('click', '.mixora-apply', function(){
            page = 1;
            closeDrawer();
            loadProducts(true);
        });

        $root.on('click', '.mixora-reset, .mixora-no-results-reset', function(){
            $sidebar.find('input[type="checkbox"]').prop('checked', false);
            $sidebar.find('input[name="min_price"], input[name="max_price"]').val('');
            $root.find('.mixora-sort').val('menu_order');
            refreshAttributesOnLoad = true;
            page = 1;
            loadProducts(false);
        });

        $root.on('change', '.mixora-sort', function(){
            var value = $(this).val();
            $root.find('.mixora-sort').val(value);
            page = 1;
            loadProducts(false);
        });

        // Desktop: filter immediately. Mobile: use the Show products button.
        $root.on('change', '.mixora-sidebar input[type="checkbox"]', function(){
            var isCategory = $(this).attr('name') === 'category[]';
            if (isCategory) {
                $dynamicAttributes.find('input[type="checkbox"]').prop('checked', false);
                refreshAttributesOnLoad = true;
            }
            updateActiveCount();
            if (!window.matchMedia('(max-width: 767px)').matches) {
                page = 1;
                loadProducts(false);
            }
        });

        var priceTimer;
        $root.on('input', '.mixora-price-inputs input', function(){
            updateActiveCount();
            if (!window.matchMedia('(max-width: 767px)').matches) {
                clearTimeout(priceTimer);
                priceTimer = setTimeout(function(){ page = 1; loadProducts(false); }, 450);
            }
        });

        $(document).on('keydown.mixoraSmartFilter', function(e){
            if (e.key === 'Escape' && $root.hasClass('filter-open')) closeDrawer();
        });

        window.addEventListener('popstate', function(e){
            $sidebar.find('input[type="checkbox"]').prop('checked', false);
            $sidebar.find('input[name="min_price"], input[name="max_price"]').val('');
            $root.find('.mixora-sort').val('menu_order');
            page = 1;
            applyUrlParams();
            updateActiveCount();
            loadProducts(false, true);
        });

        $root.on('click', '.mixora-pagination button', function(){
            page = parseInt($(this).data('page'),10) || 1;
            loadProducts(false);
            $('html,body').animate({scrollTop: $root.find('.mixora-results').offset().top - 110}, 200);
        });

        var hasUrlParams = applyUrlParams();
        updateActiveCount();

        var initialPages = parseInt($pagination.data('initial-pages'), 10) || 1;
        if (initialPages > 1) {
            renderPagination(initialPages, 1);
        }

        // If URL parameters are present, load matching products immediately.
        // Otherwise, initial products were already pre-rendered by PHP.
        if (hasUrlParams) {
            loadProducts(false);
        }
    }

    $(function(){
        if (typeof MixoraSmartWooFilter === 'undefined') return;
        $('.mxswpf-root').each(function(){ initFilter($(this)); });
    });
})(jQuery);
