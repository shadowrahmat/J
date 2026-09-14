<?php
/**
 * Cart Page - Juhani Brand Redesign
 *
 * Overrides standard WooCommerce cart template with clean product cards,
 * 2-column attribute details, interactive quantity stepper, line subtotals,
 * and continue shopping button.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 11.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_cart' ); ?>

<form class="woocommerce-cart-form juhani-cart-form" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
	<?php do_action( 'woocommerce_before_cart_table' ); ?>

	<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents juhani-cart-table" cellspacing="0">
		<thead class="juhani-cart-thead-hidden">
			<tr>
				<th scope="col" class="product-thumbnail"><span class="screen-reader-text"><?php esc_html_e( 'Product', 'woocommerce' ); ?></span></th>
				<th scope="col" class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th scope="col" class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
				<th scope="col" class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
				<th scope="col" class="product-subtotal"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
				<th scope="col" class="product-remove"><span class="screen-reader-text"><?php esc_html_e( 'Remove item', 'woocommerce' ); ?></span></th>
			</tr>
		</thead>
		<tbody class="juhani-cart-tbody">
			<?php do_action( 'woocommerce_before_cart_contents' ); ?>

			<?php
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

				$visible = apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key );

				if ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 && $visible ) {
					$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
					$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
					$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
					$product_subtotal  = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
					$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image( array( 130, 130 ) ), $cart_item, $cart_item_key );

					if ( $_product->is_sold_individually() ) {
						$min_quantity = 1;
						$max_quantity = 1;
					} else {
						$min_quantity = 0;
						$max_quantity = $_product->get_max_purchase_quantity();
					}

					$product_quantity = woocommerce_quantity_input(
						array(
							'input_name'   => "cart[{$cart_item_key}][qty]",
							'input_value'  => $cart_item['quantity'],
							'max_value'    => $max_quantity,
							'min_value'    => $min_quantity,
							'product_name' => $product_name,
						),
						$_product,
						false
					);
					$product_quantity = apply_filters( 'woocommerce_cart_item_quantity', $product_quantity, $cart_item_key, $cart_item );
					?>
					<tr class="woocommerce-cart-form__cart-item cart_item juhani-cart-row <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
						<td colspan="6" class="juhani-cart-card-cell">
							<div class="juhani-cart-card">

								<!-- Top Section: Image on left, Name & Unit Price beside it, Remove button top right -->
								<div class="juhani-cart-card__header">
									<div class="product-thumbnail juhani-cart-card__thumb">
										<?php
										if ( ! $product_permalink ) {
											echo $thumbnail;
										} else {
											printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail );
										}
										?>
									</div>

									<div class="juhani-cart-card__meta">
										<h3 class="product-name juhani-cart-card__title">
											<?php
											if ( ! $product_permalink ) {
												echo wp_kses_post( $product_name );
											} else {
												printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), wp_kses_post( $product_name ) );
											}
											?>
										</h3>

										<div class="product-price juhani-cart-card__unit-price">
											<?php echo $product_price; ?>
										</div>
									</div>

									<div class="product-remove juhani-cart-card__remove">
										<?php
										echo apply_filters(
											'woocommerce_cart_item_remove_link',
											sprintf(
												'<a role="button" href="%s" class="remove juhani-remove-btn" aria-label="%s" data-product_id="%s" data-product_sku="%s" title="%s">&times;</a>',
												esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
												esc_attr( sprintf( __( 'Remove %s from cart', 'woocommerce' ), wp_strip_all_tags( $product_name ) ) ),
												esc_attr( $product_id ),
												esc_attr( $_product->get_sku() ),
												esc_attr__( 'Remove item', 'woocommerce' )
											),
											$cart_item_key
										);
										?>
									</div>
								</div>

								<!-- Middle Section: Product Details / Attributes (Light Gray Box, 2-Column Grid) -->
								<?php
								$item_details_html = wc_get_formatted_cart_item_data( $cart_item );
								if ( ! empty( $item_details_html ) ) :
									$item_details_html = preg_replace( '/:\s*<\/dt>/i', '</dt>', $item_details_html );
								?>
									<div class="juhani-cart-card__details">
										<?php echo $item_details_html; ?>
									</div>
								<?php endif; ?>

								<?php if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) : ?>
									<p class="backorder_notification"><?php esc_html_e( 'Available on backorder', 'woocommerce' ); ?></p>
								<?php endif; ?>

								<!-- Bottom Section: Quantity Stepper on left, Subtotal on right -->
								<div class="juhani-cart-card__footer">
									<div class="product-quantity juhani-cart-card__qty-col">
										<span class="juhani-qty-label">Quantity</span>
										<div class="juhani-qty-stepper">
											<button type="button" class="juhani-qty-btn minus" aria-label="<?php esc_attr_e( 'Decrease quantity', 'woocommerce' ); ?>">&minus;</button>
											<?php echo $product_quantity; ?>
											<button type="button" class="juhani-qty-btn plus" aria-label="<?php esc_attr_e( 'Increase quantity', 'woocommerce' ); ?>">&plus;</button>
										</div>
									</div>

									<div class="product-subtotal juhani-cart-card__subtotal-col">
										<span class="juhani-subtotal-label">Subtotal</span>
										<div class="juhani-subtotal-amount"><?php echo $product_subtotal; ?></div>
									</div>
								</div>

							</div>
						</td>
					</tr>
					<?php
				}
			}
			?>

			<?php do_action( 'woocommerce_cart_contents' ); ?>

			<!-- Actions: Coupon + Hidden/Auto Update Cart -->
			<tr class="juhani-cart-actions-row <?php echo ! wc_coupons_enabled() ? 'juhani-no-coupons' : ''; ?>">
				<td colspan="6" class="actions juhani-actions-cell">
					<?php if ( wc_coupons_enabled() ) { ?>
						<div class="coupon juhani-coupon-wrapper">
							<label for="coupon_code" class="screen-reader-text"><?php esc_html_e( 'Coupon:', 'woocommerce' ); ?></label>
							<input type="text" name="coupon_code" class="input-text juhani-coupon-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Coupon code', 'woocommerce' ); ?>" />
							<button type="submit" class="button juhani-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply coupon', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply coupon', 'woocommerce' ); ?></button>
							<?php do_action( 'woocommerce_cart_coupon' ); ?>
						</div>
					<?php } ?>

					<button type="submit" class="button juhani-update-cart-btn" name="update_cart" value="<?php esc_attr_e( 'Update cart', 'woocommerce' ); ?>"><?php esc_html_e( 'Update cart', 'woocommerce' ); ?></button>

					<?php do_action( 'woocommerce_cart_actions' ); ?>
					<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				</td>
			</tr>

			<?php do_action( 'woocommerce_after_cart_contents' ); ?>
		</tbody>
	</table>

	<!-- Continue Shopping Button below Product Cards -->
	<div class="juhani-continue-shopping-wrapper">
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ?: home_url( '/' ) ); ?>" class="juhani-continue-shopping-btn">
			<span class="juhani-cs-arrow">&larr;</span> Continue Shopping
		</a>
	</div>

	<?php do_action( 'woocommerce_after_cart_table' ); ?>
</form>

<?php do_action( 'woocommerce_before_cart_collaterals' ); ?>

<div class="cart-collaterals juhani-cart-collaterals">
	<?php
		/**
		 * Cart collaterals hook.
		 *
		 * @hooked woocommerce_cross_sell_display
		 * @hooked woocommerce_cart_totals - 10
		 */
		do_action( 'woocommerce_cart_collaterals' );
	?>
</div>

<?php do_action( 'woocommerce_after_cart' ); ?>
