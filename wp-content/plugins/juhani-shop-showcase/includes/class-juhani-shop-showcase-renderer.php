<?php
namespace Juhani\Shop_Showcase;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Renderer {
	public static function render( array $settings ): void {
		if ( ! class_exists( 'WooCommerce' ) ) {
			echo '<div class="jss-empty">WooCommerce is required.</div>';
			return;
		}

		$category = isset( $_GET['jss_cat'] ) ? sanitize_title( wp_unslash( $_GET['jss_cat'] ) ) : '';
		$search = isset( $_GET['jss_search'] ) ? sanitize_text_field( wp_unslash( $_GET['jss_search'] ) ) : '';
		$sort = isset( $_GET['jss_sort'] ) ? sanitize_key( wp_unslash( $_GET['jss_sort'] ) ) : 'menu_order';
		$per_page = max( 1, min( 48, (int) ( $settings['products_per_page'] ?? 9 ) ) );
		$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );

		$args = [
			'post_type' => 'product',
			'post_status' => 'publish',
			'posts_per_page' => $per_page,
			'paged' => $paged,
			's' => $search,
		];

		if ( $category ) {
			$args['tax_query'] = [
				[
					'taxonomy' => 'product_cat',
					'field' => 'slug',
					'terms' => $category,
				],
			];
		}

		if ( 'price_asc' === $sort || 'price_desc' === $sort ) {
			$args['meta_key'] = '_price';
			$args['orderby'] = 'meta_value_num';
			$args['order'] = 'price_desc' === $sort ? 'DESC' : 'ASC';
		} elseif ( 'date' === $sort ) {
			$args['orderby'] = 'date';
			$args['order'] = 'DESC';
		} else {
			$args['orderby'] = [ 'menu_order' => 'ASC', 'title' => 'ASC' ];
		}

		$query = new \WP_Query( $args );
		$columns = in_array( (string) ( $settings['columns'] ?? '3' ), [ '2', '3', '4' ], true ) ? $settings['columns'] : '3';
		?>
		<div class="jss-wrap jss-cols-<?php echo esc_attr( $columns ); ?>">
			<section class="jss-shop" id="products">
				<div class="jss-products">
					<?php if ( 'yes' === ( $settings['show_toolbar'] ?? 'yes' ) ) : ?>
						<form class="jss-toolbar" method="get" action="<?php echo esc_url( self::get_shop_url() ); ?>">
							<div class="jss-count">
								<?php
								printf(
									esc_html__( 'Showing %1$d-%2$d of %3$d products', 'juhani-shop-showcase' ),
									$query->found_posts ? ( ( $paged - 1 ) * $per_page ) + 1 : 0,
									min( $paged * $per_page, $query->found_posts ),
									$query->found_posts
								);
								?>
							</div>
							<input type="hidden" name="jss_cat" value="<?php echo esc_attr( $category ); ?>">
							<label class="jss-search">
								<span class="screen-reader-text"><?php esc_html_e( 'Search products', 'juhani-shop-showcase' ); ?></span>
								<input type="search" name="jss_search" value="<?php echo esc_attr( $search ); ?>" placeholder="<?php esc_attr_e( 'Search products...', 'juhani-shop-showcase' ); ?>">
							</label>
							<label class="jss-sort">
								<span><?php esc_html_e( 'Sort by', 'juhani-shop-showcase' ); ?></span>
								<select name="jss_sort" onchange="this.form.submit()">
									<option value="menu_order" <?php selected( $sort, 'menu_order' ); ?>><?php esc_html_e( 'Featured', 'juhani-shop-showcase' ); ?></option>
									<option value="date" <?php selected( $sort, 'date' ); ?>><?php esc_html_e( 'Newest', 'juhani-shop-showcase' ); ?></option>
									<option value="price_asc" <?php selected( $sort, 'price_asc' ); ?>><?php esc_html_e( 'Price: Low to High', 'juhani-shop-showcase' ); ?></option>
									<option value="price_desc" <?php selected( $sort, 'price_desc' ); ?>><?php esc_html_e( 'Price: High to Low', 'juhani-shop-showcase' ); ?></option>
								</select>
							</label>
						</form>
					<?php endif; ?>

					<div class="jss-grid">
						<?php
						if ( $query->have_posts() ) :
							$index = 0;
							while ( $query->have_posts() ) :
								$query->the_post();
								self::render_product_card( wc_get_product( get_the_ID() ), $settings, $index );
								$index++;
							endwhile;
							wp_reset_postdata();
						else :
							echo '<p class="jss-empty">' . esc_html__( 'No products found.', 'juhani-shop-showcase' ) . '</p>';
						endif;
						?>
					</div>
				</div>
			</section>
		</div>
		<?php
	}

	private static function render_categories( string $current ): void {
		$terms = get_terms( [
			'taxonomy' => 'product_cat',
			'hide_empty' => false,
		] );

		echo '<ul class="jss-category-list">';
		$all_url = remove_query_arg( [ 'jss_cat', 'paged', 'product-page' ] );
		echo '<li><a class="' . ( '' === $current ? 'is-active' : '' ) . '" href="' . esc_url( $all_url ) . '"><span>All Products</span></a></li>';
		foreach ( $terms as $term ) {
			$url = add_query_arg( 'jss_cat', $term->slug, remove_query_arg( [ 'paged', 'product-page' ] ) );
			printf(
				'<li><a class="%1$s" href="%2$s"><span>%3$s</span><small>%4$d</small></a></li>',
				$current === $term->slug ? 'is-active' : '',
				esc_url( $url ),
				esc_html( $term->name ),
				(int) $term->count
			);
		}
		echo '</ul>';
	}

	private static function get_shop_url(): string {
		$shop_id = function_exists( 'wc_get_page_id' ) ? wc_get_page_id( 'shop' ) : 0;

		if ( $shop_id > 0 ) {
			return get_permalink( $shop_id );
		}

		return get_permalink();
	}

	private static function render_product_card( $product, array $settings, int $index ): void {
		if ( ! $product ) {
			return;
		}

		$image = self::get_product_image_url( $product, $index );
		$button_text = $product->is_type( 'variable' ) ? esc_html__( 'Select Options', 'juhani-shop-showcase' ) : esc_html__( 'Add to Cart', 'juhani-shop-showcase' );
		?>
		<article class="jss-card">
			<a class="jss-card__image" href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>">
				<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy">
			</a>
			<div class="jss-card__body">
				<h3><a href="<?php echo esc_url( get_permalink( $product->get_id() ) ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
				<div class="jss-price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
				<a class="jss-button" href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-product_id="<?php echo esc_attr( $product->get_id() ); ?>">
					<?php echo esc_html( $button_text ); ?>
				</a>
			</div>
		</article>
		<?php
	}

	private static function get_product_image_url( $product, int $index ): string {
		$image_id = $product->get_image_id();
		$gallery = $product->get_gallery_image_ids();
		$fallback_ids = [ 126, 124, 123, 121, 120, 116, 115, 114, 109, 108, 107, 106, 105 ];

		if ( $gallery ) {
			$image_id = $gallery[ $index % count( $gallery ) ];
		}

		if ( 203 === (int) $image_id && ! $gallery ) {
			$image_id = $fallback_ids[ $index % count( $fallback_ids ) ];
		}

		if ( $image_id ) {
			$url = wp_get_attachment_image_url( $image_id, 'large' );
			if ( $url ) {
				return $url;
			}
		}

		$fallback_id = $fallback_ids[ $index % count( $fallback_ids ) ];
		$url = wp_get_attachment_image_url( $fallback_id, 'large' );

		return $url ? $url : wc_placeholder_img_src( 'large' );
	}
}
