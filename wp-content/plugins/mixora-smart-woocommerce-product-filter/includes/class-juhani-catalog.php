<?php
/**
 * The reusable Juhani catalog view. All catalog content comes from WooCommerce.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Mixora_Juhani_Catalog {
	public static function register_assets() {
		$plugin_dir = dirname( __DIR__ );
		$plugin_file = $plugin_dir . '/mixora-smart-woocommerce-product-filter.php';
		$assets = [
			'mixora-juhani-catalog-style' => [ 'css', 'juhani-catalog.css' ],
			'mixora-juhani-catalog-script' => [ 'js', 'juhani-catalog.js' ],
		];
		foreach ( $assets as $handle => $asset ) {
			$path = $plugin_dir . '/assets/' . $asset[1];
			$version = is_file( $path ) ? (string) filemtime( $path ) : '1.0.0';
			$url = plugins_url( 'assets/' . $asset[1], $plugin_file );
			if ( 'css' === $asset[0] && ! wp_style_is( $handle, 'registered' ) ) {
				wp_register_style( $handle, $url, [], $version );
			} elseif ( 'js' === $asset[0] && ! wp_script_is( $handle, 'registered' ) ) {
				wp_register_script( $handle, $url, [], $version, true );
			}
		}
	}

	private static function enabled( $value ) {
		return ! in_array( $value, [ false, 0, '0', '', 'no', 'false', 'off' ], true );
	}

	private static function number_setting( $settings, $key, $default, $min, $max ) {
		$value = $settings[ $key ] ?? $default;
		if ( is_array( $value ) ) {
			$value = $value['size'] ?? $default;
		}
		return is_numeric( $value ) ? max( $min, min( $max, (float) $value ) ) : $default;
	}

	private static function root_style( $settings ) {
		$numbers = [
			'columns'        => [ 3, 1, 4, '--mjc-columns', '' ],
			'columns_tablet' => [ 2, 1, 4, '--mjc-columns-tablet', '' ],
			'columns_mobile' => [ 2, 1, 4, '--mjc-columns-mobile', '' ],
			'product_gap'    => [ 23, 0, 80, '--mjc-gap', 'px' ],
			'product_gap_tablet' => [ 20, 0, 80, '--mjc-gap-tablet', 'px' ],
			'product_gap_mobile' => [ 17, 0, 80, '--mjc-gap-mobile', 'px' ],
			'sidebar_width'  => [ 265, 180, 420, '--mjc-sidebar-width', 'px' ],
			'sidebar_width_tablet' => [ 235, 180, 420, '--mjc-sidebar-width-tablet', 'px' ],
			'layout_gap'     => [ 32, 0, 80, '--mjc-layout-gap', 'px' ],
			'layout_gap_tablet' => [ 24, 0, 80, '--mjc-layout-gap-tablet', 'px' ],
			'card_radius'    => [ 12, 0, 40, '--mjc-radius', 'px' ],
		];
		$styles = [];
		foreach ( $numbers as $key => $spec ) {
			$value = self::number_setting( $settings, $key, $spec[0], $spec[1], $spec[2] );
			if ( '' === $spec[4] ) {
				$value = (int) $value;
			}
			$styles[] = $spec[3] . ':' . $value . $spec[4];
		}
		foreach ( [
			'accent_color' => [ '--mjc-accent', '#236b9c' ],
			'ink_color' => [ '--mjc-ink', '#223747' ],
			'line_color' => [ '--mjc-line', '#e1eaf0' ],
		] as $key => $spec ) {
			$color = sanitize_hex_color( $settings[ $key ] ?? '' );
			$styles[] = $spec[0] . ':' . ( $color ?: $spec[1] );
		}
		return implode( ';', $styles );
	}

	private static function normalize_digits( $value ) {
		return strtr( (string) $value, [ '০' => '0', '১' => '1', '২' => '2', '৩' => '3', '৪' => '4', '৫' => '5', '৬' => '6', '৭' => '7', '৮' => '8', '৯' => '9' ] );
	}

	private static function ghor_values( $product, $attributes ) {
		foreach ( [ 'pa_ghor', 'ghor' ] as $key ) {
			if ( ! empty( $attributes[ $key ] ) ) {
				return array_values( array_unique( array_map( [ __CLASS__, 'normalize_digits' ], $attributes[ $key ] ) ) );
			}
		}
		foreach ( [ $product->get_name(), str_replace( '-', ' ', $product->get_slug() ) ] as $source ) {
			if ( preg_match( '/([0-9]+(?:\.[0-9]+)?)\s*(?:ghor|ঘর)/iu', self::normalize_digits( $source ), $matches ) ) {
				return [ $matches[1] ];
			}
		}
		return [];
	}

	private static function catalog_products( $settings ) {
		$category_ids = $settings['category_ids'] ?? [];
		if ( is_string( $category_ids ) ) {
			$category_ids = preg_split( '/[\s,]+/', $category_ids, -1, PREG_SPLIT_NO_EMPTY );
		}
		$category_ids = array_values( array_filter( array_map( 'absint', (array) $category_ids ) ) );
		if ( ! $category_ids && function_exists( 'is_product_category' ) && is_product_category() ) {
			$category_ids = [ absint( get_queried_object_id() ) ];
		}
		$args = [
			'status' => 'publish',
			'limit' => -1,
			'visibility' => 'catalog',
			'orderby' => [ 'menu_order' => 'ASC', 'title' => 'ASC', 'ID' => 'ASC' ],
			'order' => 'ASC',
		];
		if ( $category_ids ) {
			$slugs = [];
			foreach ( $category_ids as $category_id ) {
				$term = get_term( $category_id, 'product_cat' );
				if ( $term && ! is_wp_error( $term ) ) {
					$slugs[] = $term->slug;
				}
			}
			if ( ! $slugs ) {
				return [];
			}
			$args['category'] = $slugs;
		}
		$hide_out_of_stock = 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' );
		return array_values( array_filter( wc_get_products( $args ), static function( $product ) use ( $hide_out_of_stock ) {
			return $product && ( ! $hide_out_of_stock || $product->is_in_stock() );
		} ) );
	}

	private static function product_data( $product, $index ) {
		$attributes = [];
		$labels = [];
		foreach ( $product->get_attributes() as $attribute ) {
			$key = sanitize_title( $attribute->get_name() );
			$values = $attribute->is_taxonomy()
				? wc_get_product_terms( $product->get_id(), $attribute->get_name(), [ 'fields' => 'names' ] )
				: $attribute->get_options();
			if ( is_wp_error( $values ) ) {
				continue;
			}
			$values = array_values( array_filter( array_map( 'strval', (array) $values ), static function( $value ) { return '' !== $value; } ) );
			if ( $values ) {
				$attributes[ $key ] = $values;
				$labels[ $key ] = wc_attribute_label( $attribute->get_name(), $product );
			}
		}
		$attributes['ghor'] = self::ghor_values( $product, $attributes );
		$categories = wc_get_product_terms( $product->get_id(), 'product_cat', [ 'fields' => 'names' ] );
		$attributes['category'] = is_wp_error( $categories ) ? [] : array_values( $categories );
		if ( $product->is_type( 'variable' ) ) {
			$variation_prices = $product->get_variation_prices( true );
			$prices = $variation_prices['price'] ?? [];
		} else {
			$prices = '' !== $product->get_price() ? [ wc_get_price_to_display( $product ) ] : [];
		}
		$prices = array_values( array_unique( array_map( 'floatval', array_filter( $prices, 'is_numeric' ) ) ) );
		sort( $prices, SORT_NUMERIC );
		$search_parts = [ $product->get_name(), $product->get_sku() ];
		foreach ( $attributes as $values ) {
			$search_parts = array_merge( $search_parts, $values );
		}
		foreach ( $attributes['ghor'] as $value ) {
			$search_parts[] = $value . ' ঘর';
		}
		$date = $product->get_date_created();
		return [
			'product' => $product,
			'attributes' => $attributes,
			'labels' => $labels,
			'prices' => $prices,
			'min' => $prices ? min( $prices ) : 0,
			'max' => $prices ? max( $prices ) : 0,
			'search' => self::normalize_digits( implode( ' ', $search_parts ) ),
			'index' => $index,
			'date' => $date ? $date->getTimestamp() : 0,
			'sales' => $product->get_total_sales(),
			'rating' => (float) $product->get_average_rating(),
		];
	}

	private static function filter_groups( $cards ) {
		$groups = [
			'ghor' => [ 'label' => 'Ghor / ঘর', 'chip' => 'Ghor', 'numeric' => true, 'open' => true ],
			'pa_haat' => [ 'label' => 'Haat / হাত', 'chip' => 'Haat', 'numeric' => true, 'open' => true ],
			'pa_suta' => [ 'label' => 'Suta / সুতা', 'chip' => 'Suta', 'numeric' => true, 'open' => true ],
			'pa_making-type' => [ 'label' => 'Making Type', 'chip' => 'Making', 'numeric' => false, 'open' => false ],
			'pa_git-type' => [ 'label' => 'Git Type', 'chip' => 'Git', 'numeric' => false, 'open' => false ],
			'pa_rock-weight-kg' => [ 'label' => 'Rock Weight (kg)', 'chip' => 'Weight', 'numeric' => true, 'open' => false ],
		];
		$values = [];
		foreach ( $cards as $card ) {
			foreach ( $card['attributes'] as $key => $options ) {
				if ( 'pa_ghor' === $key ) {
					continue;
				}
				if ( ! isset( $groups[ $key ] ) && 'category' !== $key ) {
					$label = $card['labels'][ $key ] ?? ucwords( str_replace( '-', ' ', $key ) );
					$groups[ $key ] = [ 'label' => $label, 'chip' => $label, 'numeric' => false, 'open' => false ];
				}
				$values[ $key ] = array_merge( $values[ $key ] ?? [], $options );
			}
		}
		$groups['category'] = [ 'label' => 'Category', 'chip' => 'Category', 'numeric' => false, 'open' => false ];
		foreach ( $groups as $key => &$group ) {
			$options = array_values( array_unique( $values[ $key ] ?? [] ) );
			usort( $options, 'strnatcasecmp' );
			$group['values'] = $options;
		}
		unset( $group );
		return array_filter( $groups, static function( $group ) { return ! empty( $group['values'] ); } );
	}

	private static function icon( $name, $class = '' ) {
		// Preserve the existing Elementor icon shapes without an icon-font dependency.
		if ( 'plus' === $name ) {
			return '<svg class="' . esc_attr( $class ) . '" width="16" height="16" viewBox="0 0 448 512" fill="currentColor" aria-hidden="true" focusable="false"><path d="M416 208H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h384c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/><path d="M416 208H272V64c0-17.67-14.33-32-32-32h-32c-17.67 0-32 14.33-32 32v144H32c-17.67 0-32 14.33-32 32v32c0 17.67 14.33 32 32 32h144v144c0 17.67 14.33 32 32 32h32c17.67 0 32-14.33 32-32V304h144c17.67 0 32-14.33 32-32v-32c0-17.67-14.33-32-32-32z"/></svg>';
		}
		$solid = [
			'arrow' => [ '448 512', 'M190.5 66.9l22.2-22.2c9.4-9.4 24.6-9.4 33.9 0L441 239c9.4 9.4 9.4 24.6 0 33.9L246.6 467.3c-9.4 9.4-24.6 9.4-33.9 0l-22.2-22.2c-9.5-9.5-9.3-25 .4-34.3L311.4 296H24c-13.3 0-24-10.7-24-24v-32c0-13.3 10.7-24 24-24h287.4L190.9 101.2c-9.8-9.3-10-24.8-.4-34.3z' ],
			'close' => [ '352 512', 'M242.72 256l100.07-100.07c12.28-12.28 12.28-32.19 0-44.48l-22.24-22.24c-12.28-12.28-32.19-12.28-44.48 0L176 189.28 75.93 89.21c-12.28-12.28-32.19-12.28-44.48 0L9.21 111.45c-12.28 12.28-12.28 32.19 0 44.48L109.28 256 9.21 356.07c-12.28 12.28-12.28 32.19 0 44.48l22.24 22.24c12.28 12.28 32.2 12.28 44.48 0L176 322.72l100.07 100.07c12.28 12.28 32.2 12.28 44.48 0l22.24-22.24c12.28-12.28 12.28-32.19 0-44.48L242.72 256z' ],
			'filter' => [ '512 512', 'M496 384H160v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h80v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h336c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160h-80v-16c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16c-8.8 0-16 7.2-16 16v32c0 8.8 7.2 16 16 16h336v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h80c8.8 0 16-7.2 16-16v-32c0-8.8-7.2-16-16-16zm0-160H288V48c0-8.8-7.2-16-16-16h-32c-8.8 0-16 7.2-16 16v16H16C7.2 64 0 71.2 0 80v32c0 8.8 7.2 16 16 16h208v16c0 8.8 7.2 16 16 16h32c8.8 0 16-7.2 16-16v-16h208c8.8 0 16-7.2 16-16V80c0-8.8-7.2-16-16-16z' ],
		];
		if ( isset( $solid[ $name ] ) ) {
			return '<svg class="' . esc_attr( $class ) . '" width="16" height="16" viewBox="0 0 ' . $solid[ $name ][0] . '" fill="currentColor" aria-hidden="true" focusable="false"><path d="' . $solid[ $name ][1] . '"/></svg>';
		}
		$paths = [
			'help' => '<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 4m0 3h.01"/>',
			'empty' => '<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
		];
		return '<svg class="' . esc_attr( $class ) . '" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . ( $paths[ $name ] ?? $paths['arrow'] ) . '</svg>';
	}

	private static function render_card( $card, $settings ) {
		$product = $card['product'];
		$url = get_permalink( $product->get_id() );
		$stock_status = $product->get_stock_status();
		$stock_label = 'outofstock' === $stock_status ? 'Out of stock' : ( 'onbackorder' === $stock_status ? 'Available on backorder' : 'In stock' );
		$category = $card['attributes']['category'][0] ?? '';
		$tag = 'Cast Nets' === $category ? 'CAST NET' : $category;
		$haat = $card['attributes']['pa_haat'] ?? $card['attributes']['haat'] ?? [];
		$suta = $card['attributes']['pa_suta'] ?? $card['attributes']['suta'] ?? [];
		usort( $haat, 'strnatcasecmp' );
		usort( $suta, 'strnatcasecmp' );
		$haat_text = $haat ? reset( $haat ) . ( count( $haat ) > 1 ? '–' . end( $haat ) : '' ) : '';
		// Keep the existing catalog's whole-price presentation without changing store settings.
		$trim_zeros = static function() { return true; };
		add_filter( 'woocommerce_price_trim_zeros', $trim_zeros, 99 );
		try {
			$price_html = $product->get_price_html();
		} finally {
			remove_filter( 'woocommerce_price_trim_zeros', $trim_zeros, 99 );
		}
		$price_html = str_replace( ' &ndash; ', '–', $price_html );
		?>
		<article class="mjc-product" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" data-attributes="<?php echo esc_attr( wp_json_encode( $card['attributes'] ) ); ?>" data-search="<?php echo esc_attr( $card['search'] ); ?>" data-min="<?php echo esc_attr( $card['min'] ); ?>" data-max="<?php echo esc_attr( $card['max'] ); ?>" data-prices="<?php echo esc_attr( wp_json_encode( $card['prices'] ) ); ?>" data-index="<?php echo esc_attr( $card['index'] ); ?>" data-date="<?php echo esc_attr( $card['date'] ); ?>" data-sales="<?php echo esc_attr( $card['sales'] ); ?>" data-rating="<?php echo esc_attr( $card['rating'] ); ?>">
			<a class="mjc-photo" href="<?php echo esc_url( $url ); ?>" aria-label="<?php echo esc_attr( $product->get_name() ); ?>">
				<?php if ( $product->get_image_id() ) { echo $product->get_image( 'large' ); } ?>
			</a>
			<div class="mjc-product-body">
				<div class="mjc-meta"><span class="mjc-category"><?php echo esc_html( $tag ); ?></span><span class="mjc-stock mjc-stock-<?php echo esc_attr( $stock_status ); ?>"><?php echo esc_html( $stock_label ); ?></span></div>
				<h3 class="mjc-title"><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<?php if ( $haat || $suta ) : ?>
					<div class="mjc-specs">
						<?php if ( $haat ) : ?><span><?php echo esc_html( $haat_text ); ?> <small>Haat</small></span><?php endif; ?>
						<?php if ( $suta ) : ?><span><?php echo esc_html( implode( ' / ', $suta ) ); ?> <small>Suta</small></span><?php endif; ?>
					</div>
				<?php endif; ?>
				<div class="mjc-price"><?php echo wp_kses_post( $price_html ); ?></div>
				<a class="mjc-action" href="<?php echo esc_url( $url ); ?>"><span><?php echo esc_html( $settings['action_text'] ); ?></span><?php echo self::icon( 'arrow' ); ?></a>
			</div>
		</article>
		<?php
	}

	public static function render( $settings = [] ) {
		if ( ! function_exists( 'wc_get_products' ) ) {
			return '<p>WooCommerce is required.</p>';
		}
		$settings = wp_parse_args( $settings, [
			'collection_label' => 'Cast Nets',
			'filter_title' => 'Refine your choice',
			'reset_text' => 'Reset all',
			'action_text' => 'মাপ ও বিকল্প বাছাই করুন',
			'apply_text' => 'পণ্যগুলো দেখুন',
			'show_search' => false,
			'search_placeholder' => 'পণ্য খুঁজুন',
			'show_help' => true,
			'help_title' => 'মাপ নিয়ে দ্বিধায়?',
			'help_text' => 'আপনার প্রয়োজন বলুন। উপযুক্ত জাল বেছে নিতে সাহায্য করব।',
			'help_link_text' => 'WhatsApp-এ কথা বলুন',
			'help_url' => 'https://wa.me/8801889164315',
			'note' => 'আপনার প্রয়োজন অনুযায়ী মাপ ও বিকল্প বেছে নিন।',
		] );
		self::register_assets();
		wp_enqueue_style( 'mixora-juhani-catalog-style' );
		wp_enqueue_script( 'mixora-juhani-catalog-script' );
		$products = self::catalog_products( $settings );
		$cards = [];
		foreach ( $products as $index => $product ) {
			$cards[] = self::product_data( $product, $index );
		}
		$groups = self::filter_groups( $cards );
		$all_prices = [];
		foreach ( $cards as $card ) {
			$all_prices = array_merge( $all_prices, $card['prices'] );
		}
		// Editor AJAX renders happen in separate requests while sibling widgets remain on screen.
		$instance_id = 'mjc-catalog-' . wp_generate_uuid4();
		$title_id = $instance_id . '-title';
		$min_id = $instance_id . '-minimum';
		$max_id = $instance_id . '-maximum';
		$price_error_id = $instance_id . '-price-error';
		$price_min = $all_prices ? min( $all_prices ) : 0;
		$price_max = $all_prices ? max( $all_prices ) : 0;
		$help_url = $settings['help_url'];
		$help_external = true;
		$help_nofollow = false;
		if ( is_array( $help_url ) ) {
			$help_external = self::enabled( $help_url['is_external'] ?? false );
			$help_nofollow = self::enabled( $help_url['nofollow'] ?? false );
			$help_url = $help_url['url'] ?? '';
		}
		ob_start();
		?>
		<div id="<?php echo esc_attr( $instance_id ); ?>" class="mixora-juhani-catalog mixora-juhani-widget" lang="bn" data-mjc-catalog style="<?php echo esc_attr( self::root_style( $settings ) ); ?>">
			<div class="mjc-layout">
				<aside class="mjc-sidebar">
					<div class="mjc-panel" data-mjc-panel>
						<div class="mjc-panel-heading"><h3 id="<?php echo esc_attr( $title_id ); ?>"><?php echo esc_html( $settings['filter_title'] ); ?></h3><button class="mjc-close" type="button" data-mjc-close aria-label="Close filters"><?php echo self::icon( 'close' ); ?></button></div>
						<div class="mjc-reset-row"><span>FILTERS</span><button type="button" data-mjc-reset><?php echo esc_html( $settings['reset_text'] ); ?></button></div>
						<?php if ( self::enabled( $settings['show_search'] ) ) : ?>
							<label class="mjc-search"><span>Search products</span><input type="search" data-mjc-search placeholder="<?php echo esc_attr( $settings['search_placeholder'] ); ?>"></label>
						<?php endif; ?>
						<details class="mjc-group" open><summary><span>Price range</span><?php echo self::icon( 'plus', 'mjc-group-icon' ); ?></summary><div class="mjc-group-body">
							<div class="jsp-price-inputs"><label for="<?php echo esc_attr( $min_id ); ?>"><span>Minimum</span><input id="<?php echo esc_attr( $min_id ); ?>" type="number" min="0" step="1" data-mjc-min placeholder="<?php echo esc_attr( $price_min ); ?>" aria-label="Minimum price in taka" aria-describedby="<?php echo esc_attr( $price_error_id ); ?>"></label><span>–</span><label for="<?php echo esc_attr( $max_id ); ?>"><span>Maximum</span><input id="<?php echo esc_attr( $max_id ); ?>" type="number" min="0" step="1" data-mjc-max placeholder="<?php echo esc_attr( $price_max ); ?>" aria-label="Maximum price in taka" aria-describedby="<?php echo esc_attr( $price_error_id ); ?>"></label></div>
							<small>কোনো বিকল্পের দাম এই সীমার মধ্যে থাকলে পণ্যটি দেখাবে।</small><p id="<?php echo esc_attr( $price_error_id ); ?>" class="jsp-price-error" data-mjc-price-error role="status" hidden>Minimum দাম Maximum-এর চেয়ে বেশি হয়েছে।</p>
						</div></details>
						<?php foreach ( $groups as $key => $group ) : ?>
							<details class="mjc-group"<?php echo $group['open'] ? ' open' : ''; ?>><summary><span><?php echo esc_html( $group['label'] ); ?></span><?php echo self::icon( 'plus', 'mjc-group-icon' ); ?></summary><div class="mjc-group-body"><div class="jsp-filter-options<?php echo $group['numeric'] ? ' jsp-number-options' : ''; ?>">
								<?php foreach ( $group['values'] as $value ) : ?><label><input type="checkbox" data-group="<?php echo esc_attr( $key ); ?>" data-mjc-group-label="<?php echo esc_attr( $group['chip'] ); ?>" value="<?php echo esc_attr( $value ); ?>"><span><?php echo esc_html( $value ); ?></span></label><?php endforeach; ?>
							</div></div></details>
						<?php endforeach; ?>
						<button class="mjc-apply" type="button" data-mjc-apply><span><?php echo esc_html( $settings['apply_text'] ); ?></span><?php echo self::icon( 'arrow' ); ?></button>
					</div>
					<?php if ( self::enabled( $settings['show_help'] ) ) : ?>
						<div class="mjc-help"><?php echo self::icon( 'help', 'mjc-help-icon' ); ?><h3><?php echo esc_html( $settings['help_title'] ); ?></h3><p><?php echo esc_html( $settings['help_text'] ); ?></p><a href="<?php echo esc_url( $help_url ); ?>" <?php if ( $help_external ) : ?>target="_blank"<?php endif; ?> rel="<?php echo esc_attr( $help_nofollow ? 'noopener nofollow' : 'noopener' ); ?>"><span><?php echo esc_html( $settings['help_link_text'] ); ?></span><?php echo self::icon( 'arrow' ); ?></a></div>
					<?php endif; ?>
				</aside>
				<div class="mjc-results">
					<div class="mjc-toolbar"><div class="mjc-collection"><strong><?php echo esc_html( $settings['collection_label'] ); ?></strong><span class="mjc-result-status" data-mjc-status role="status" aria-live="polite"><?php echo esc_html( count( $cards ) . ' products' ); ?></span></div><div class="mjc-tools"><button class="mjc-open-filters" type="button" data-mjc-open aria-controls="<?php echo esc_attr( $instance_id . '-dialog' ); ?>" aria-expanded="false"><?php echo self::icon( 'filter' ); ?><span>Filters</span><span class="mjc-filter-count" data-mjc-count hidden>0</span></button><label class="mjc-sort"><span class="mjc-sr-only">Sort products</span><select data-mjc-sort aria-label="Sort products"><option value="featured">Featured</option><option value="newest">Newest</option><option value="price-low">Price: Low to High</option><option value="price-high">Price: High to Low</option><option value="popular">Most Popular</option><option value="rating">Top Rated</option></select></label></div></div>
					<div class="mjc-active-filters" data-mjc-active hidden></div>
					<div class="mjc-grid" data-mjc-grid><?php foreach ( $cards as $card ) { self::render_card( $card, $settings ); } ?></div>
					<div class="mjc-empty" data-mjc-empty<?php echo $cards ? ' hidden' : ''; ?>><?php echo self::icon( 'empty', 'mjc-empty-icon' ); ?><h3>এই পছন্দে কোনো জাল পাওয়া যায়নি।</h3><p>অন্য মাপ বেছে নিন বা filters সরিয়ে সব পণ্য দেখুন।</p></div>
					<?php if ( '' !== $settings['note'] ) : ?><p class="mjc-note"><?php echo esc_html( $settings['note'] ); ?></p><?php endif; ?>
				</div>
			</div>
			<dialog id="<?php echo esc_attr( $instance_id . '-dialog' ); ?>" class="mjc-dialog" aria-labelledby="<?php echo esc_attr( $title_id ); ?>" data-mjc-dialog></dialog>
		</div>
		<?php
		return ob_get_clean();
	}
}
