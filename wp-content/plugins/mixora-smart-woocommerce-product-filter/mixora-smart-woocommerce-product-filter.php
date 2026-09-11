<?php
/**
 * Plugin Name: Mixora Smart WooCommerce Product Filter
 * Description: User-friendly AJAX WooCommerce product filter with responsive sidebar/drawer, categories, category-aware attributes, price range, sorting, pagination and product cards.
 * Version: 2.0.30
 * Author: Mixora
 * Requires Plugins: woocommerce
 */
if (!defined('ABSPATH')) exit;

class Mixora_Smart_Product_Filter {
    const VERSION = '2.0.30';

    public function __construct() {
        add_shortcode('mixora_product_filter', [$this, 'shortcode']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue']);
        add_action('wp_ajax_mixora_filter_products', [$this, 'ajax']);
        add_action('wp_ajax_nopriv_mixora_filter_products', [$this, 'ajax']);
        add_action('save_post_product', [$this, 'flush_cache']);
        add_action('woocommerce_update_product', [$this, 'flush_cache']);
        add_action('created_term', [$this, 'flush_cache']);
        add_action('edited_term', [$this, 'flush_cache']);
        add_action('delete_term', [$this, 'flush_cache']);
    }

    public function flush_cache() {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_mx_attrs_%' OR option_name LIKE '_transient_timeout_mx_attrs_%'");
    }

    public function enqueue() {
        if (!class_exists('WooCommerce')) return;
        wp_register_style('mixora-smart-wc-filter', plugins_url('assets/filter.css', __FILE__), [], self::VERSION);
        wp_register_script('mixora-smart-wc-filter', plugins_url('assets/filter.js', __FILE__), ['jquery'], self::VERSION, true);
        wp_localize_script('mixora-smart-wc-filter', 'MixoraSmartWooFilter', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('mixora_filter_nonce'),
        ]);
    }

    private function get_current_category_id() {
        if (function_exists('is_product_category') && is_product_category()) {
            $term = get_queried_object();
            if ($term && !empty($term->term_id)) return (int) $term->term_id;
        }
        $product_cat = get_query_var('product_cat');
        if ($product_cat) {
            $term = get_term_by('slug', sanitize_title($product_cat), 'product_cat');
            if ($term && !is_wp_error($term)) return (int) $term->term_id;
        }
        return 0;
    }

    private function size_rank($value) {
        $order = [
            'xs'=>5, 's'=>10, 'm'=>20, 'l'=>30, 'xl'=>40,
            '2xl'=>50, 'xxl'=>50, '3xl'=>60, 'xxxl'=>60,
            '38'=>65, '40'=>70, '42'=>80, '44'=>90, '46'=>100, '48'=>110,
        ];
        $key = sanitize_title(trim((string) $value));
        return $order[$key] ?? 1000;
    }

    private function sort_terms($terms, $label = '', $taxonomy = '') {
        $is_size = sanitize_title($label) === 'size' || sanitize_title(str_replace('pa_', '', $taxonomy)) === 'size';
        usort($terms, function($a, $b) use ($is_size) {
            $an = is_object($a) ? $a->name : ($a['name'] ?? '');
            $bn = is_object($b) ? $b->name : ($b['name'] ?? '');
            if ($is_size) {
                $ap = $this->size_rank($an);
                $bp = $this->size_rank($bn);
                if ($ap !== $bp) return $ap <=> $bp;
            }
            return strnatcasecmp((string) $an, (string) $bn);
        });
        return array_values($terms);
    }

    /**
     * Build attribute groups from the products that actually exist in the current scope.
     * Uses direct indexed database queries and WordPress transient caching to prevent
     * N+1 object loading and memory exhaustion.
     */
    private function get_attributes($category_id = 0) {
        $category_ids = array_values(array_filter(array_map('absint', (array) $category_id)));
        $cache_key = 'mx_attrs_' . md5(json_encode($category_ids));
        $cached = get_transient($cache_key);
        if ($cached !== false && is_array($cached)) {
            return $cached;
        }

        global $wpdb;

        $global = [];
        $global_labels = [];
        foreach ((array) wc_get_attribute_taxonomies() as $attr) {
            $taxonomy = wc_attribute_taxonomy_name($attr->attribute_name);
            $label = $attr->attribute_label ?: wc_attribute_label($taxonomy);
            $global[$taxonomy] = [
                'type'     => 'taxonomy',
                'taxonomy' => $taxonomy,
                'label'    => $label,
                'terms'    => [],
            ];
            $global_labels[sanitize_title($label)] = true;
            $global_labels[sanitize_title(str_replace('pa_', '', $taxonomy))] = true;
        }

        foreach (get_object_taxonomies('product', 'objects') as $taxonomy => $tax_obj) {
            if (strpos($taxonomy, 'pa_') !== 0 || isset($global[$taxonomy])) continue;
            $label = !empty($tax_obj->labels->singular_name)
                ? $tax_obj->labels->singular_name
                : ucwords(str_replace(['pa_', '-', '_'], ['', ' ', ' '], $taxonomy));
            $global[$taxonomy] = [
                'type'     => 'taxonomy',
                'taxonomy' => $taxonomy,
                'label'    => $label,
                'terms'    => [],
            ];
            $global_labels[sanitize_title($label)] = true;
            $global_labels[sanitize_title(str_replace('pa_', '', $taxonomy))] = true;
        }

        if (empty($global)) {
            return [];
        }

        if ($category_ids) {
            $all_cat_ids = [];
            foreach ($category_ids as $cid) {
                $all_cat_ids[] = $cid;
                $children = get_term_children($cid, 'product_cat');
                if (!is_wp_error($children) && $children) {
                    $all_cat_ids = array_merge($all_cat_ids, $children);
                }
            }
            $all_cat_ids = array_values(array_unique(array_map('absint', $all_cat_ids)));
            $cat_placeholders = implode(',', array_fill(0, count($all_cat_ids), '%d'));

            $sql = $wpdb->prepare("
                SELECT tt.taxonomy, t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as term_count
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND tr.object_id IN (
                    SELECT tr2.object_id
                    FROM {$wpdb->term_relationships} tr2
                    INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                    WHERE tt2.taxonomy = 'product_cat' AND tt2.term_id IN ($cat_placeholders)
                )
                AND tt.taxonomy LIKE 'pa_%%'
                GROUP BY tt.taxonomy, t.term_id
            ", $all_cat_ids);
        } else {
            $sql = "
                SELECT tt.taxonomy, t.term_id, t.name, t.slug, COUNT(DISTINCT tr.object_id) as term_count
                FROM {$wpdb->term_relationships} tr
                INNER JOIN {$wpdb->term_taxonomy} tt ON tr.term_taxonomy_id = tt.term_taxonomy_id
                INNER JOIN {$wpdb->terms} t ON tt.term_id = t.term_id
                INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                WHERE p.post_type = 'product' AND p.post_status = 'publish'
                AND tt.taxonomy LIKE 'pa_%'
                GROUP BY tt.taxonomy, t.term_id
            ";
        }

        $term_rows = $wpdb->get_results($sql);
        if ($term_rows) {
            foreach ($term_rows as $row) {
                $taxonomy = $row->taxonomy;
                if (!isset($global[$taxonomy])) continue;
                $global[$taxonomy]['terms'][$row->term_id] = [
                    'term_id' => (int) $row->term_id,
                    'name'    => $row->name,
                    'slug'    => $row->slug,
                    'count'   => (int) $row->term_count,
                ];
            }
        }

        $out = [];
        foreach ($global as $taxonomy => $attribute) {
            if (empty($attribute['terms'])) continue;
            $attribute['terms'] = $this->sort_terms(array_values($attribute['terms']), $attribute['label'], $taxonomy);
            $out[] = $attribute;
        }

        set_transient($cache_key, $out, HOUR_IN_SECONDS);
        return $out;
    }

    private function render_attribute_groups($category_id = 0) {
        ob_start();
        foreach ($this->get_attributes($category_id) as $attr) {
            $is_size = sanitize_title($attr['label']) === 'size' || sanitize_title(str_replace('pa_', '', $attr['taxonomy'])) === 'size';
            ?>
            <div class="mixora-group mixora-attribute-group <?php echo $is_size ? 'mixora-size-group' : ''; ?>"
                 data-attribute-type="<?php echo esc_attr($attr['type']); ?>"
                 data-taxonomy="<?php echo esc_attr($attr['taxonomy']); ?>">
                <h4><?php echo esc_html($attr['label']); ?></h4>
                <div class="mixora-options">
                    <?php foreach ($attr['terms'] as $term):
                        $term_slug  = is_object($term) ? $term->slug : ($term['slug'] ?? sanitize_title($term['name'] ?? ''));
                        $term_name  = is_object($term) ? $term->name : ($term['name'] ?? '');
                        $term_count = is_object($term) ? $term->count : (int) ($term['count'] ?? 0);
                        $input_name = $attr['type'] === 'local'
                            ? 'local_attr[' . $attr['taxonomy'] . '][]'
                            : $attr['taxonomy'] . '[]';
                    ?>
                        <label>
                            <input type="checkbox" name="<?php echo esc_attr($input_name); ?>" value="<?php echo esc_attr($term_slug); ?>">
                            <span><?php echo esc_html($term_name); ?></span>
                            <?php if (!$is_size): ?><em><?php echo esc_html($term_count); ?></em><?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php
        }
        return ob_get_clean();
    }

    public function shortcode($atts) {
        if (!class_exists('WooCommerce')) return '<p>WooCommerce is required.</p>';

        wp_enqueue_style('mixora-smart-wc-filter');
        wp_enqueue_style('mixora-red-hat-display', 'https://fonts.googleapis.com/css2?family=Red+Hat+Display:wght@400;500;600;700&display=swap', [], null);
        wp_enqueue_script('mixora-smart-wc-filter');

        $atts = shortcode_atts(['per_page' => 9, 'columns' => 3], $atts, 'mixora_product_filter');
        $per_page = max(1, min(24, (int) $atts['per_page']));
        $columns = max(1, min(4, (int) $atts['columns']));

        $current_category_id = $this->get_current_category_id();
        $current_category_name = 'All Products';
        if ($current_category_id) {
            $current_term = get_term($current_category_id, 'product_cat');
            if ($current_term && !is_wp_error($current_term)) $current_category_name = $current_term->name;
        }
        $cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => true, 'orderby' => 'name', 'order' => 'ASC']);
        $currency_symbol = function_exists('get_woocommerce_currency_symbol') ? get_woocommerce_currency_symbol() : '৳';

        // Pre-render initial products for instant display and SEO
        $initial_args = [
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => $per_page,
            'paged'               => 1,
            'orderby'             => ['menu_order' => 'ASC', 'title' => 'ASC'],
            'ignore_sticky_posts' => true,
        ];
        if ($current_category_id) {
            $initial_args['tax_query'] = [[
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => [$current_category_id],
                'include_children' => true,
            ]];
        }
        $initial_query = new WP_Query($initial_args);

        ob_start(); ?>
        <div class="mxswpf-root mixora-smart-filter"
             data-per-page="<?php echo esc_attr($per_page); ?>"
             data-columns="<?php echo esc_attr($columns); ?>"
             data-current-category="<?php echo esc_attr($current_category_id); ?>"
             data-current-category-name="<?php echo esc_attr($current_category_name); ?>"
             data-initial-rendered="1">

            <div class="mixora-mobile-toolbar">
                <button type="button" class="mixora-filter-toggle" aria-label="Open product filters" aria-expanded="false">
                    <span class="mixora-filter-icon" aria-hidden="true">☰</span>
                    <span class="mixora-filter-label">Filters</span>
                    <span class="mixora-active-count" aria-live="polite"></span>
                </button>
                <select class="mixora-sort mixora-sort-mobile" aria-label="Sort products">
                    <option value="menu_order">Featured</option>
                    <option value="date">Newest</option>
                    <option value="price">Price: Low to High</option>
                    <option value="price-desc">Price: High to Low</option>
                    <option value="popularity">Most Popular</option>
                    <option value="rating">Top Rated</option>
                </select>
            </div>

            <div class="mixora-filter-overlay"></div>
            <aside class="mixora-sidebar">
                <div class="mixora-sidebar-head">
                    <h3>Filter</h3>
                    <button type="button" class="mixora-reset">Reset all</button>
                    <button type="button" class="mixora-close" aria-label="Close filters" title="Close filters">
                        <svg class="mixora-close-icon" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                            <path d="M6 6L18 18M18 6L6 18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                        </svg>
                    </button>
                </div>

                <div class="mixora-group mixora-price-group">
                    <h4>Price range</h4>
                    <div class="mixora-price-inputs">
                        <input type="number" name="min_price" min="0" placeholder="<?php echo esc_attr(sprintf('Min %s', $currency_symbol)); ?>" inputmode="numeric">
                        <span>–</span>
                        <input type="number" name="max_price" min="0" placeholder="<?php echo esc_attr(sprintf('Max %s', $currency_symbol)); ?>" inputmode="numeric">
                    </div>
                </div>

                <?php if (!is_wp_error($cats) && $cats): ?>
                    <div class="mixora-group mixora-category-group">
                        <h4>Category</h4>
                        <div class="mixora-options">
                            <?php foreach ($cats as $cat): ?>
                                <label>
                                    <input type="checkbox" name="category[]" value="<?php echo esc_attr($cat->term_id); ?>">
                                    <span><?php echo esc_html($cat->name); ?></span>
                                    <em><?php echo esc_html($cat->count); ?></em>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <div class="mixora-dynamic-attributes">
                    <?php echo $this->render_attribute_groups($current_category_id); ?>
                </div>

                <button type="button" class="mixora-apply">Show products</button>
            </aside>

            <main class="mixora-results">
                <div class="mixora-results-head">
                    <div class="mixora-results-tools">
                        <div class="mixora-results-title" aria-live="polite"><?php echo esc_html($current_category_name); ?></div>
                        <div class="mixora-result-count" aria-live="polite"><?php echo esc_html($initial_query->found_posts . ' Products'); ?></div>
                    </div>
                    <select class="mixora-sort mixora-sort-desktop" aria-label="Sort products">
                        <option value="menu_order">Featured</option>
                        <option value="date">Newest</option>
                        <option value="price">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="popularity">Most Popular</option>
                        <option value="rating">Top Rated</option>
                    </select>
                </div>
                <div class="mixora-products" aria-live="polite">
                    <?php if ($initial_query->have_posts()): ?>
                        <div class="mixora-product-grid">
                            <?php while ($initial_query->have_posts()): $initial_query->the_post(); ?>
                                <?php $this->render_product_card(wc_get_product(get_the_ID())); ?>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="mixora-no-results"><strong>No products found</strong><span>Try removing a filter or changing your price range.</span><button type="button" class="mixora-no-results-reset">Clear filters</button></div>
                    <?php endif; ?>
                </div>
                <div class="mixora-pagination" data-initial-pages="<?php echo esc_attr((int) $initial_query->max_num_pages); ?>"></div>
            </main>
        </div>
        <?php
        wp_reset_postdata();
        return ob_get_clean();
    }

    private function normalize_selected_values($raw) {
        $out = [];
        foreach ((array) $raw as $value) {
            $value = sanitize_title(wp_unslash($value));
            if ($value !== '') $out[] = $value;
        }
        return array_values(array_unique($out));
    }

    private function render_product_card($product) {
        if (!$product) return;
        ?>
        <article class="mixora-product-card">
            <a class="mixora-product-image" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">
                <?php echo $product->get_image('woocommerce_thumbnail'); ?>
                <?php if ($product->is_on_sale()): ?><span class="mixora-badge">Sale</span><?php endif; ?>
            </a>
            <div class="mixora-product-info">
                <a class="mixora-product-title" href="<?php echo esc_url(get_permalink($product->get_id())); ?>"><?php echo esc_html($product->get_name()); ?></a>
                <div class="mixora-product-price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
                <?php if ($product->is_type('variable')): ?>
                    <a class="mixora-order mixora-product-action" href="<?php echo esc_url(get_permalink($product->get_id())); ?>">Select options</a>
                <?php else: ?>
                    <a class="mixora-order mixora-product-action" href="<?php echo esc_url($product->add_to_cart_url()); ?>">Order Now</a>
                <?php endif; ?>
            </div>
        </article>
        <?php
    }

    public function ajax() {
        check_ajax_referer('mixora_filter_nonce', 'nonce');
        if (!class_exists('WooCommerce')) wp_send_json_error(['message' => 'WooCommerce is required.']);

        $page = max(1, absint($_POST['page'] ?? 1));
        $per_page = max(1, min(24, absint($_POST['per_page'] ?? 9)));
        $sort = sanitize_key($_POST['sort'] ?? 'menu_order');
        $current_category = absint($_POST['current_category'] ?? 0);
        $selected_categories = array_values(array_filter(array_map('absint', (array) ($_POST['category'] ?? []))));
        $min_price = (isset($_POST['min_price']) && $_POST['min_price'] !== '') ? (float) $_POST['min_price'] : null;
        $max_price = (isset($_POST['max_price']) && $_POST['max_price'] !== '') ? (float) $_POST['max_price'] : null;

        $global_attrs = [];
        foreach ($_POST as $key => $value) {
            if (strpos((string) $key, 'pa_') !== 0 || !is_array($value)) continue;
            $tax = sanitize_key($key);
            if (!taxonomy_exists($tax)) continue;
            $vals = $this->normalize_selected_values($value);
            if ($vals) $global_attrs[$tax] = $vals;
        }

        // Build single optimized native WP_Query
        $tax_query = ['relation' => 'AND'];

        // Category filter
        $cat_ids = $selected_categories ?: ($current_category ? [(int) $current_category] : []);
        if ($cat_ids) {
            $tax_query[] = [
                'taxonomy'         => 'product_cat',
                'field'            => 'term_id',
                'terms'            => $cat_ids,
                'operator'         => 'IN',
                'include_children' => true,
            ];
        }

        // Attribute taxonomies filter (direct indexed SQL JOINs)
        foreach ($global_attrs as $taxonomy => $slugs) {
            if (!empty($slugs)) {
                $tax_query[] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $slugs,
                    'operator' => 'IN',
                ];
            }
        }

        // Price range meta query
        $meta_query = ['relation' => 'AND'];
        if ($min_price !== null && $max_price !== null) {
            $meta_query[] = [
                'key'     => '_price',
                'value'   => [$min_price, $max_price],
                'type'    => 'NUMERIC',
                'compare' => 'BETWEEN',
            ];
        } elseif ($min_price !== null) {
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $min_price,
                'type'    => 'NUMERIC',
                'compare' => '>=',
            ];
        } elseif ($max_price !== null) {
            $meta_query[] = [
                'key'     => '_price',
                'value'   => $max_price,
                'type'    => 'NUMERIC',
                'compare' => '<=',
            ];
        }

        $args = [
            'post_type'           => 'product',
            'post_status'         => 'publish',
            'posts_per_page'      => $per_page,
            'paged'               => $page,
            'ignore_sticky_posts' => true,
        ];

        if (count($tax_query) > 1) {
            $args['tax_query'] = $tax_query;
        }
        if (count($meta_query) > 1) {
            $args['meta_query'] = $meta_query;
        }

        switch ($sort) {
            case 'date':
                $args['orderby'] = 'date';
                $args['order']   = 'DESC';
                break;
            case 'price':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'ASC';
                break;
            case 'price-desc':
                $args['meta_key'] = '_price';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'popularity':
                $args['meta_key'] = 'total_sales';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            case 'rating':
                $args['meta_key'] = '_wc_average_rating';
                $args['orderby']  = 'meta_value_num';
                $args['order']    = 'DESC';
                break;
            default:
                $args['orderby'] = ['menu_order' => 'ASC', 'title' => 'ASC'];
                break;
        }

        $q = new WP_Query($args);
        ob_start();
        if ($q->have_posts()) {
            echo '<div class="mixora-product-grid">';
            while ($q->have_posts()) {
                $q->the_post();
                $this->render_product_card(wc_get_product(get_the_ID()));
            }
            echo '</div>';
        } else {
            echo '<div class="mixora-no-results"><strong>No products found</strong><span>Try removing a filter or changing your price range.</span><button type="button" class="mixora-no-results-reset">Clear filters</button></div>';
        }
        $html = ob_get_clean();
        wp_reset_postdata();

        $result_title = 'All Products';
        $label_category_ids = $selected_categories ?: ($current_category ? [$current_category] : []);
        if ($label_category_ids) {
            $label_names = [];
            foreach ($label_category_ids as $label_category_id) {
                $label_term = get_term((int) $label_category_id, 'product_cat');
                if ($label_term && !is_wp_error($label_term)) $label_names[] = $label_term->name;
            }
            if ($label_names) $result_title = implode(' + ', $label_names);
        }

        $attribute_scope = $selected_categories ?: ($current_category ? [$current_category] : []);

        wp_send_json_success([
            'html'            => $html,
            'count'           => (int) $q->found_posts,
            'pages'           => (int) $q->max_num_pages,
            'page'            => $page,
            'result_title'    => $result_title,
            'attributes_html' => $this->render_attribute_groups($attribute_scope),
        ]);
    }
}

new Mixora_Smart_Product_Filter();
