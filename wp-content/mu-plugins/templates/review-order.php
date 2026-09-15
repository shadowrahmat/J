<?php
/**
 * Review order table override
 */
defined( 'ABSPATH' ) || exit;
?>
<table class="shop_table woocommerce-checkout-review-order-table juhani-checkout-table">
	<tbody>
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( ! ( $_product instanceof WC_Product && $_product->exists() && $cart_item['quantity'] > 0 ) ) {
				continue;
			}

			// 1. Product Name
			$product_name = $_product->get_name();

			// 2. Attributes
			$attributes = [];
			if ( $_product->is_type( 'variation' ) && is_array( $cart_item['variation'] ) ) {
				foreach ( $cart_item['variation'] as $name => $value ) {
					$taxonomy = wc_attribute_taxonomy_name( str_replace( 'attribute_pa_', '', urldecode( $name ) ) );
					if ( taxonomy_exists( $taxonomy ) ) {
						$term = get_term_by( 'slug', $value, $taxonomy );
						$val_label = ( ! is_wp_error( $term ) && $term && $term->name ) ? $term->name : $value;
						$attr_label = wc_attribute_label( $taxonomy );
					} else {
						$val_label = apply_filters( 'woocommerce_variation_option_name', $value, null, $taxonomy, $_product );
						$attr_label = wc_attribute_label( str_replace( 'attribute_', '', $name ), $_product );
					}
					if ( '' === $val_label || wc_is_attribute_in_product_name( $val_label, $_product->get_name() ) ) {
						continue;
					}
					$attr_label = rtrim( trim( $attr_label ), ':' );
					$attr_label = preg_replace( '/^pa[-_\s]*/i', '', $attr_label );
					$attributes[] = [
						'label' => ucwords( str_replace( [ '-', '_' ], ' ', $attr_label ) ),
						'value' => $val_label,
					];
				}
			}

			// Subtotal
			$subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );
			?>
			<tr class="cart_item juhani-checkout-cart-item-row">
				<td colspan="2" class="juhani-checkout-cart-item-cell">
					<div class="juhani-checkout-card" data-product-key="<?php echo esc_attr( $cart_item_key ); ?>">
						<!-- Product Name -->
						<div class="juhani-co-row juhani-co-row--product">
							<div class="juhani-co-label">Product</div>
							<div class="juhani-co-value"><strong><?php echo esc_html( $product_name ); ?></strong></div>
						</div>

						<!-- Quantity -->
						<div class="juhani-co-row juhani-co-row--qty">
							<div class="juhani-co-label">Quantity</div>
							<div class="juhani-co-value">
								<div class="checkout-quantity-wrapper" data-product-key="<?php echo esc_attr( $cart_item_key ); ?>">
									<button type="button" class="checkout-qty-minus" aria-label="Decrease quantity">−</button>
									<span class="checkout-qty-display"><?php echo intval( $cart_item['quantity'] ); ?></span>
									<button type="button" class="checkout-qty-plus" aria-label="Increase quantity">+</button>
								</div>
							</div>
						</div>

						<!-- Attributes -->
						<?php foreach ( $attributes as $attr ) : ?>
						<div class="juhani-co-row juhani-co-row--attr">
							<div class="juhani-co-label"><?php echo esc_html( $attr['label'] ); ?></div>
							<div class="juhani-co-value"><?php echo esc_html( $attr['value'] ); ?></div>
						</div>
						<?php endforeach; ?>

						<!-- Subtotal -->
						<div class="juhani-co-row juhani-co-row--subtotal">
							<div class="juhani-co-label">Subtotal</div>
							<div class="juhani-co-value juhani-co-subtotal-val"><?php echo $subtotal; ?></div>
						</div>
					</div>
				</td>
			</tr>
			<?php
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</tbody>
	<tfoot>
		<tr class="cart-subtotal">
			<th><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_subtotal_html(); ?></td>
		</tr>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<tr class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
				<th><?php wc_cart_totals_coupon_label( $coupon ); ?></th>
				<td><?php wc_cart_totals_coupon_html( $coupon ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
			<?php do_action( 'woocommerce_review_order_before_shipping' ); ?>
			<?php wc_cart_totals_shipping_html(); ?>
			<?php do_action( 'woocommerce_review_order_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<tr class="fee">
				<th><?php echo esc_html( $fee->name ); ?></th>
				<td><?php wc_cart_totals_fee_html( $fee ); ?></td>
			</tr>
		<?php endforeach; ?>

		<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) : ?>
			<?php if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
				<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
					<tr class="tax-rate tax-rate-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
						<th><?php echo esc_html( $tax->label ); ?></th>
						<td><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr class="tax-total">
					<th><?php echo esc_html( WC()->countries->tax_or_vat() ); ?></th>
					<td><?php wc_cart_totals_taxes_total_html(); ?></td>
				</tr>
			<?php endif; ?>
		<?php endif; ?>

		<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

		<tr class="order-total">
			<th><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			<td><?php wc_cart_totals_order_total_html(); ?></td>
		</tr>

		<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>
	</tfoot>
</table>

