<?php
/**
 * Plugin Name: Juhani Cart Redesign
 * Description: Redesigns WooCommerce Cart page into a modern 2-column layout with product cards, 2-column attribute box, quantity steppers, subtotals, continue shopping button, and brand navy styling.
 * Version: 1.0.0
 * Author: Marketorr
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// 1. Filter WooCommerce Cart Template to load our custom template
add_filter( 'woocommerce_locate_template', 'juhani_cart_locate_template', 99, 3 );
function juhani_cart_locate_template( $template, $template_name, $template_path ) {
	if ( 'cart/cart.php' === $template_name ) {
		$custom = __DIR__ . '/templates/cart.php';
		if ( file_exists( $custom ) ) {
			return $custom;
		}
	}
	return $template;
}

// 2. Clean attribute labels: remove trailing colons and clean prefix
add_filter( 'woocommerce_get_item_data', 'juhani_cart_clean_item_data', 20, 2 );
function juhani_cart_clean_item_data( $item_data, $cart_item ) {
	if ( is_array( $item_data ) ) {
		foreach ( $item_data as &$data ) {
			if ( isset( $data['key'] ) ) {
				$key = trim( $data['key'] );
				$key = rtrim( $key, ':' );
				$key = preg_replace( '/^pa[-_\s]*/i', '', $key );
				$data['key'] = ucwords( str_replace( array( '-', '_' ), ' ', $key ) );
			}
		}
	}
	return $item_data;
}

// 3. Inject Cart CSS and JavaScript
add_action( 'wp_head', 'juhani_cart_custom_styles_and_scripts', 100 );
function juhani_cart_custom_styles_and_scripts() {
	// Only load on cart page
	$is_cart_page = false;
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		$is_cart_page = true;
	}
	if ( ! $is_cart_page && is_page() ) {
		$page_id = get_queried_object_id();
		if ( 13 === $page_id || is_page( 'cart' ) ) {
			$is_cart_page = true;
		}
	}

	if ( ! $is_cart_page ) {
		return;
	}
	?>
	<style id="juhani-cart-redesign-css">
	/* ============================================================
	   JUHANI WOOCOMMERCE CART REDESIGN
	   Colors: Dark Navy #0D2A43 | Primary Blue #123F63 | Bg #F5F8FA | Border #E4EAF0
	   ============================================================ */

	/* Page Container & Background */
	body.woocommerce-cart {
		background-color: #F5F8FA !important;
	}

	/* Elementor Container 2-column Grid Layout - Desktop 1350px */
	body.woocommerce-cart .elementor-13 .elementor-element.elementor-element-545fcaa5,
	body.woocommerce-cart .elementor-13 .elementor-element.elementor-element-545fcaa5 > .e-con-inner,
	body.woocommerce-cart .elementor-widget-woocommerce-cart {
		max-width: 1350px !important;
		width: 100% !important;
		margin-left: auto !important;
		margin-right: auto !important;
	}

	body.woocommerce-cart .elementor-widget-woocommerce-cart .e-cart__container {
		display: grid !important;
		grid-template-columns: minmax(0, 1fr) 380px !important;
		gap: 36px !important;
		width: 100% !important;
		max-width: 1350px !important;
		margin: 0 auto !important;
		padding: 20px 16px 60px !important;
		box-sizing: border-box !important;
	}

	body.woocommerce-cart .e-cart__column-start {
		width: 100% !important;
		max-width: 100% !important;
		min-width: 0 !important;
		padding: 0 !important;
		border: none !important;
		box-shadow: none !important;
		background: transparent !important;
	}

	body.woocommerce-cart .e-cart__column-end {
		width: 100% !important;
		max-width: 100% !important;
		min-width: 0 !important;
		padding: 0 !important;
		border: none !important;
		box-shadow: none !important;
		background: transparent !important;
		position: -webkit-sticky !important;
		position: sticky !important;
		top: 30px !important;
		align-self: start !important;
	}

	.woocommerce-cart .woocommerce * {
		box-sizing: border-box !important;
	}

	/* Notices */
	body.woocommerce-cart .woocommerce-notices-wrapper {
		width: 100% !important;
		grid-column: 1 / -1 !important;
		margin-bottom: 12px !important;
	}

	/* Cart Table Reset */
	body.woocommerce-cart table.juhani-cart-table,
	body.woocommerce-cart table.shop_table.cart {
		display: block !important;
		width: 100% !important;
		border: none !important;
		background: transparent !important;
		margin: 0 !important;
		padding: 0 !important;
		border-collapse: separate !important;
		border-spacing: 0 !important;
	}

	body.woocommerce-cart thead.juhani-cart-thead-hidden,
	body.woocommerce-cart table.shop_table.cart thead {
		display: none !important;
	}

	body.woocommerce-cart tbody.juhani-cart-tbody,
	body.woocommerce-cart table.shop_table.cart tbody {
		display: flex !important;
		flex-direction: column !important;
		gap: 20px !important;
		width: 100% !important;
		border: none !important;
		padding: 0 !important;
		margin: 0 !important;
	}

	body.woocommerce-cart tr.juhani-cart-row,
	body.woocommerce-cart tr.woocommerce-cart-form__cart-item {
		display: block !important;
		width: 100% !important;
		border: none !important;
		background: transparent !important;
		padding: 0 !important;
		margin: 0 !important;
	}

	body.woocommerce-cart td.juhani-cart-card-cell {
		display: block !important;
		width: 100% !important;
		padding: 0 !important;
		border: none !important;
		background: transparent !important;
	}

	/* ============================================================
	   PRODUCT CARD COMPONENT
	   ============================================================ */
	.juhani-cart-card {
		background: #FFFFFF !important;
		border: none !important;
		border-radius: 0 !important;
		box-shadow: none !important;
		padding: 0px !important;
		position: relative !important;
		display: flex !important;
		flex-direction: column !important;
		gap: 12px !important;
		transition: none !important;
		width: 100% !important;
	}

	.juhani-cart-card:hover {
		border-color: transparent !important;
		box-shadow: none !important;
	}

	/* Top Section: Thumb + Meta + Remove */
	.juhani-cart-card__header {
		display: flex !important;
		align-items: flex-start !important;
		gap: 20px !important;
		position: relative !important;
		padding-right: 44px !important;
		width: 100% !important;
	}

	/* Thumbnail: 110-130px */
	.juhani-cart-card__thumb {
		width: 120px !important;
		height: 120px !important;
		min-width: 120px !important;
		max-width: 120px !important;
		border-radius: 2px !important;
		border: 1px solid #E4EAF0 !important;
		overflow: hidden !important;
		background: #FFFFFF !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		flex-shrink: 0 !important;
		padding: 0 !important;
	}

	.juhani-cart-card__thumb a {
		display: block !important;
		width: 100% !important;
		height: 100% !important;
	}

	.juhani-cart-card__thumb img {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover !important;
		display: block !important;
		border-radius: 0 !important;
	}

	/* Meta: Title & Price */
	.juhani-cart-card__meta {
		flex: 1 1 auto !important;
		min-width: 0 !important;
		padding-top: 2px !important;
		display: flex !important;
		flex-direction: column !important;
		justify-content: flex-start !important;
	}

	.juhani-cart-card__title,
	.juhani-cart-card__title a {
		font-family: "Poppins", sans-serif !important;
		font-size: 18px !important;
		font-weight: 700 !important;
		line-height: 1.35 !important;
		color: #0D2A43 !important;
		text-decoration: none !important;
		margin: 0 0 6px 0 !important;
		display: block !important;
		transition: color 0.15s ease !important;
	}

	.juhani-cart-card__title a:hover {
		color: #123F63 !important;
	}

	.juhani-cart-card__unit-price {
		font-size: 16px !important;
		font-weight: 600 !important;
		color: #123F63 !important;
		line-height: 1.3 !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
	}

	.juhani-cart-card__unit-price .amount {
		font-size: 16px !important;
		font-weight: 600 !important;
		color: #123F63 !important;
	}

	/* Remove Button at Top-Right Corner */
	.juhani-cart-card__remove {
		position: absolute !important;
		top: 0 !important;
		right: 0 !important;
		z-index: 5 !important;
		padding: 0 !important;
		border: none !important;
	}

	.juhani-remove-btn {
		width: 32px !important;
		height: 32px !important;
		min-width: 32px !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
		background: #FFFFFF !important;
		color: #64748B !important;
		font-size: 22px !important;
		font-weight: 400 !important;
		line-height: 1 !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		text-decoration: none !important;
		transition: all 0.18s ease !important;
		cursor: pointer !important;
		padding: 0 !important;
	}

	.juhani-remove-btn:hover {
		color: #E11D48 !important;
		border-color: #FECDD3 !important;
		background: #FFF1F2 !important;
	}

	/* ============================================================
	   PRODUCT DETAILS (ATTRIBUTES) IN LIGHT GRAY BOX
	   ============================================================ */
	.juhani-cart-card__details {
		background: #F8FAFC !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
		padding: 14px 20px !important;
		margin: 0 !important;
		width: 100% !important;
	}

	.juhani-cart-card__details dl.variation {
		display: grid !important;
		grid-template-columns: minmax(130px, max-content) 1fr !important;
		row-gap: 8px !important;
		column-gap: 24px !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
		background: transparent !important;
		width: 100% !important;
	}

	.juhani-cart-card__details dl.variation dt {
		color: #64748B !important;
		font-size: 13.5px !important;
		font-weight: 500 !important;
		line-height: 1.4 !important;
		margin: 0 !important;
		padding: 5px 0 !important;
		border-bottom: 1px solid #EDF2F7 !important;
		display: flex !important;
		align-items: center !important;
		text-transform: capitalize !important;
		white-space: nowrap !important;
	}

	.juhani-cart-card__details dl.variation dd {
		color: #0D2A43 !important;
		font-size: 13.5px !important;
		font-weight: 600 !important;
		line-height: 1.4 !important;
		margin: 0 !important;
		padding: 5px 0 !important;
		border-bottom: 1px solid #EDF2F7 !important;
		display: flex !important;
		align-items: center !important;
		justify-content: flex-start !important;
		white-space: nowrap !important;
	}

	.juhani-cart-card__details dl.variation dd p {
		margin: 0 !important;
		padding: 0 !important;
		display: inline !important;
	}

	/* Remove bottom border on last item */
	.juhani-cart-card__details dl.variation dt:nth-last-of-type(1),
	.juhani-cart-card__details dl.variation dd:last-child {
		border-bottom: none !important;
	}

	/* ============================================================
	   BOTTOM SECTION: QUANTITY STEPPER & SUBTOTAL
	   ============================================================ */
	.juhani-cart-card__footer {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		border-top: 1px solid #EDF2F7 !important;
		padding-top: 16px !important;
		margin-top: 2px !important;
		width: 100% !important;
	}

	/* Quantity Stepper: Quantity [ - ] [ 1 ] [ + ] */
	.juhani-cart-card__qty-col {
		display: inline-flex !important;
		align-items: center !important;
		gap: 12px !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
	}

	.juhani-qty-label {
		font-size: 14px !important;
		font-weight: 600 !important;
		color: #0D2A43 !important;
		margin-right: 2px !important;
	}

	.juhani-qty-stepper {
		display: inline-flex !important;
		align-items: center !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
		background: #FFFFFF !important;
		overflow: hidden !important;
		height: 38px !important;
	}

	.juhani-qty-btn {
		width: 36px !important;
		height: 38px !important;
		min-width: 36px !important;
		border: none !important;
		background: #F8FAFC !important;
		color: #0D2A43 !important;
		font-size: 18px !important;
		font-weight: 700 !important;
		line-height: 1 !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		cursor: pointer !important;
		transition: background-color 0.15s ease, color 0.15s ease !important;
		padding: 0 !important;
		margin: 0 !important;
		user-select: none !important;
		outline: none !important;
	}

	.juhani-qty-btn:hover {
		background: #EAF0F6 !important;
		color: #123F63 !important;
	}

	.juhani-qty-btn:active {
		background: #DCE5EE !important;
	}

	.juhani-qty-stepper .quantity {
		display: inline-block !important;
		margin: 0 !important;
		padding: 0 !important;
		float: none !important;
	}

	.juhani-qty-stepper .quantity input.qty {
		width: 48px !important;
		height: 38px !important;
		min-height: 38px !important;
		border: none !important;
		border-left: 1px solid #E4EAF0 !important;
		border-right: 1px solid #E4EAF0 !important;
		border-radius: 0 !important;
		text-align: center !important;
		font-size: 15px !important;
		font-weight: 600 !important;
		color: #0D2A43 !important;
		background: #FFFFFF !important;
		padding: 0 4px !important;
		margin: 0 !important;
		box-shadow: none !important;
		-moz-appearance: textfield !important;
	}

	.juhani-qty-stepper .quantity input.qty::-webkit-inner-spin-button,
	.juhani-qty-stepper .quantity input.qty::-webkit-outer-spin-button {
		-webkit-appearance: none !important;
		margin: 0 !important;
	}

	/* Subtotal Block on Right */
	.juhani-cart-card__subtotal-col {
		display: flex !important;
		flex-direction: column !important;
		align-items: flex-end !important;
		text-align: right !important;
		margin: 0 !important;
		padding: 0 !important;
		border: none !important;
	}

	.juhani-subtotal-label {
		font-size: 12px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		letter-spacing: 0.5px !important;
		color: #64748B !important;
		margin-bottom: 2px !important;
	}

	.juhani-subtotal-amount {
		font-size: 18px !important;
		font-weight: 800 !important;
		color: #0D2A43 !important;
		line-height: 1.2 !important;
	}

	.juhani-subtotal-amount .amount {
		font-size: 18px !important;
		font-weight: 800 !important;
		color: #0D2A43 !important;
	}

	/* ============================================================
	   ACTIONS ROW (COUPONS + UPDATE CART)
	   ============================================================ */
	body.woocommerce-cart tr.juhani-cart-actions-row {
		display: block !important;
		width: 100% !important;
		border: none !important;
		margin-top: 10px !important;
		padding: 0 !important;
	}

	body.woocommerce-cart td.juhani-actions-cell {
		display: block !important;
		width: 100% !important;
		border: none !important;
		padding: 0 !important;
		background: transparent !important;
	}

	body.woocommerce-cart tr.juhani-cart-actions-row.juhani-no-coupons,
	body.woocommerce-cart tr.juhani-cart-actions-row.juhani-no-coupons td.juhani-actions-cell {
		height: 0 !important;
		min-height: 0 !important;
		max-height: 0 !important;
		padding: 0 !important;
		margin: 0 !important;
		border: none !important;
		line-height: 0 !important;
		overflow: hidden !important;
		visibility: hidden !important;
	}

	.juhani-coupon-wrapper {
		display: flex !important;
		align-items: center !important;
		gap: 10px !important;
		max-width: 440px !important;
		margin: 0 !important;
		float: none !important;
	}

	.juhani-coupon-input {
		height: 42px !important;
		padding: 8px 14px !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
		font-size: 14px !important;
		background: #FFFFFF !important;
		color: #0D2A43 !important;
		flex: 1 1 auto !important;
		box-shadow: none !important;
	}

	.juhani-coupon-input:focus {
		border-color: #123F63 !important;
		outline: none !important;
	}

	.juhani-coupon-btn {
		height: 42px !important;
		padding: 0 18px !important;
		background: #123F63 !important;
		color: #FFFFFF !important;
		font-family: "Poppins", sans-serif !important;
		font-size: 13px !important;
		font-weight: 700 !important;
		border: none !important;
		border-radius: 2px !important;
		text-transform: uppercase !important;
		letter-spacing: 0.3px !important;
		cursor: pointer !important;
		transition: background 0.15s ease !important;
		white-space: nowrap !important;
	}

	.juhani-coupon-btn:hover {
		background: #0D2A43 !important;
		color: #FFFFFF !important;
	}

	/* Hidden Update Cart button (handled dynamically) */
	.juhani-update-cart-btn {
		display: none !important;
	}

	/* ============================================================
	   CONTINUE SHOPPING BUTTON
	   ============================================================ */
	.juhani-continue-shopping-wrapper {
		margin-top: 22px !important;
		display: block !important;
	}

	.juhani-continue-shopping-btn {
		display: inline-flex !important;
		align-items: center !important;
		gap: 8px !important;
		padding: 12px 24px !important;
		font-family: "Poppins", sans-serif !important;
		font-size: 14px !important;
		font-weight: 600 !important;
		color: #123F63 !important;
		background: #FFFFFF !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
		text-decoration: none !important;
		transition: all 0.2s ease !important;
		box-shadow: 0 1px 3px rgba(13, 42, 67, 0.04) !important;
	}

	.juhani-continue-shopping-btn:hover {
		background: #F8FAFC !important;
		color: #0D2A43 !important;
		border-color: #123F63 !important;
	}

	.juhani-cs-arrow {
		font-size: 17px !important;
		line-height: 1 !important;
		transition: transform 0.15s ease !important;
	}

	.juhani-continue-shopping-btn:hover .juhani-cs-arrow {
		transform: translateX(-3px) !important;
	}

	/* ============================================================
	   CART TOTALS CARD (RIGHT SIDE)
	   ============================================================ */
	body.woocommerce-cart .cart_totals {
		background: #FFFFFF !important;
		border: none !important;
		border-radius: 0 !important;
		box-shadow: none !important;
		padding: 24px !important;
		width: 100% !important;
		float: none !important;
		margin: 0 !important;
	}

	body.woocommerce-cart .cart_totals h2 {
		font-family: "Poppins", sans-serif !important;
		font-size: 20px !important;
		font-weight: 700 !important;
		color: #0D2A43 !important;
		margin: 0 0 16px 0 !important;
		padding: 0 0 14px 0 !important;
		border-bottom: 1px solid #E4EAF0 !important;
		text-transform: capitalize !important;
		letter-spacing: 0 !important;
	}

	/* Hide pseudo elements injected by WooCommerce responsive tables */
	body.woocommerce-cart .cart_totals table.shop_table td::before,
	body.woocommerce-cart .cart_totals table.shop_table th::before,
	body.woocommerce-cart .cart_totals td[data-title]::before {
		display: none !important;
		content: none !important;
	}

	body.woocommerce-cart .cart_totals table.shop_table {
		width: 100% !important;
		border: none !important;
		background: transparent !important;
		margin: 0 !important;
		border-collapse: collapse !important;
	}

	body.woocommerce-cart .cart_totals tr {
		border: none !important;
		background: transparent !important;
	}

	/* Force TH to be visible in Cart Totals (overriding Elementor display:none) */
	body.woocommerce-cart .cart_totals table.shop_table th {
		display: block !important;
		visibility: visible !important;
		opacity: 1 !important;
		color: #0D2A43 !important;
		font-size: 15px !important;
		font-weight: 600 !important;
		text-align: left !important;
		padding: 0 !important;
		margin: 0 !important;
		border: none !important;
		background: transparent !important;
	}

	body.woocommerce-cart .cart_totals table.shop_table td {
		display: block !important;
		padding: 0 !important;
		margin: 0 !important;
		border: none !important;
		background: transparent !important;
	}

	/* Subtotal Row */
	body.woocommerce-cart .cart_totals tr.cart-subtotal {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		padding: 12px 0 !important;
		border-bottom: 1px solid #E4EAF0 !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals tr.cart-subtotal th {
		color: #0D2A43 !important;
		font-size: 15px !important;
		font-weight: 600 !important;
		flex: 1 1 auto !important;
	}

	body.woocommerce-cart .cart_totals tr.cart-subtotal td {
		color: #0D2A43 !important;
		font-size: 16px !important;
		font-weight: 700 !important;
		text-align: right !important;
		flex: 0 0 auto !important;
	}

	body.woocommerce-cart .cart_totals tr.cart-subtotal td .amount {
		color: #0D2A43 !important;
		font-size: 16px !important;
		font-weight: 700 !important;
	}

	/* Shipment Section */
	body.woocommerce-cart .cart_totals tr.woocommerce-shipping-totals.shipping {
		display: block !important;
		padding: 16px 0 14px 0 !important;
		border-bottom: 1px solid #E4EAF0 !important;
		border-top: none !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals tr.woocommerce-shipping-totals.shipping th {
		display: block !important;
		color: #0D2A43 !important;
		font-size: 15px !important;
		font-weight: 700 !important;
		margin-bottom: 12px !important;
	}

	body.woocommerce-cart .cart_totals tr.woocommerce-shipping-totals.shipping td {
		display: block !important;
		width: 100% !important;
		text-align: left !important;
	}

	body.woocommerce-cart .cart_totals ul#shipping_method {
		list-style: none !important;
		margin: 0 0 12px 0 !important;
		padding: 0 !important;
		display: flex !important;
		flex-direction: column !important;
		gap: 10px !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals ul#shipping_method li {
		display: flex !important;
		align-items: center !important;
		gap: 12px !important;
		margin: 0 !important;
		padding: 0 !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals ul#shipping_method input[type="radio"] {
		accent-color: #123F63 !important;
		width: 17px !important;
		height: 17px !important;
		min-width: 17px !important;
		margin: 0 !important;
		cursor: pointer !important;
	}

	body.woocommerce-cart .cart_totals ul#shipping_method label {
		color: #0D2A43 !important;
		font-size: 14px !important;
		font-weight: 500 !important;
		cursor: pointer !important;
		margin: 0 !important;
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals ul#shipping_method label .amount {
		color: #0D2A43 !important;
		font-weight: 700 !important;
		margin-left: auto !important;
	}

	body.woocommerce-cart .woocommerce-shipping-destination {
		font-size: 13px !important;
		color: #64748B !important;
		margin: 8px 0 !important;
	}

	body.woocommerce-cart .shipping-calculator-button {
		font-size: 13px !important;
		font-weight: 600 !important;
		color: #123F63 !important;
		text-decoration: underline !important;
		display: inline-block !important;
		margin-top: 4px !important;
		cursor: pointer !important;
	}

	body.woocommerce-cart .shipping-calculator-form {
		margin-top: 14px !important;
		padding: 14px !important;
		background: #F8FAFC !important;
		border: 1px solid #E4EAF0 !important;
		border-radius: 2px !important;
	}

	body.woocommerce-cart .shipping-calculator-form p {
		margin-bottom: 10px !important;
	}

	body.woocommerce-cart .shipping-calculator-form select,
	body.woocommerce-cart .shipping-calculator-form input[type="text"] {
		height: 38px !important;
		border: 1px solid #E4EAF0 !important;
		background: #FFFFFF !important;
		font-size: 13px !important;
		border-radius: 2px !important;
		padding: 0 10px !important;
		width: 100% !important;
	}

	body.woocommerce-cart .shipping-calculator-form button.button {
		background: #123F63 !important;
		color: #FFFFFF !important;
		padding: 8px 18px !important;
		border: none !important;
		font-size: 13px !important;
		font-weight: 600 !important;
		text-transform: uppercase !important;
		border-radius: 2px !important;
		cursor: pointer !important;
	}

	/* Total Row */
	body.woocommerce-cart .cart_totals tr.order-total {
		display: flex !important;
		justify-content: space-between !important;
		align-items: center !important;
		padding: 16px 0 !important;
		border-top: none !important;
		border-bottom: none !important;
		width: 100% !important;
	}

	body.woocommerce-cart .cart_totals tr.order-total th {
		color: #0D2A43 !important;
		font-size: 17px !important;
		font-weight: 700 !important;
		flex: 1 1 auto !important;
	}

	body.woocommerce-cart .cart_totals tr.order-total td {
		color: #0D2A43 !important;
		font-size: 22px !important;
		font-weight: 800 !important;
		text-align: right !important;
		flex: 0 0 auto !important;
	}

	body.woocommerce-cart .cart_totals tr.order-total strong,
	body.woocommerce-cart .cart_totals tr.order-total .amount {
		color: #0D2A43 !important;
		font-size: 22px !important;
		font-weight: 800 !important;
	}

	/* Checkout Button */
	body.woocommerce-cart .cart_totals .wc-proceed-to-checkout {
		margin-top: 10px !important;
		padding: 0 !important;
	}

	body.woocommerce-cart .cart_totals a.checkout-button {
		display: block !important;
		width: 100% !important;
		text-align: center !important;
		background: #0D2A43 !important;
		color: #FFFFFF !important;
		font-family: "Poppins", sans-serif !important;
		font-size: 15px !important;
		font-weight: 700 !important;
		letter-spacing: 0.5px !important;
		text-transform: uppercase !important;
		padding: 16px 20px !important;
		border-radius: 2px !important;
		border: none !important;
		box-shadow: 0 4px 14px rgba(13, 42, 67, 0.18) !important;
		text-decoration: none !important;
		transition: all 0.2s ease !important;
		cursor: pointer !important;
	}

	body.woocommerce-cart .cart_totals a.checkout-button:hover {
		background: #081E31 !important;
		color: #FFFFFF !important;
		transform: translateY(-1px) !important;
		box-shadow: 0 6px 18px rgba(13, 42, 67, 0.25) !important;
	}

	/* ============================================================
	   RESPONSIVE LAYOUT
	   ============================================================ */
	@media (max-width: 1024px) {
		body.woocommerce-cart .elementor-widget-woocommerce-cart .e-cart__container {
			grid-template-columns: minmax(0, 1fr) 340px !important;
			gap: 24px !important;
			width: 92% !important;
			max-width: 1350px !important;
		}

		.juhani-cart-card {
			padding: 0px !important;
		}

		.juhani-cart-card__thumb {
			width: 100px !important;
			height: 100px !important;
			min-width: 100px !important;
			max-width: 100px !important;
		}
	}

	@media (max-width: 820px) {
		body.woocommerce-cart .elementor-widget-woocommerce-cart .e-cart__container {
			display: flex !important;
			flex-direction: column !important;
			gap: 20px !important;
			width: 95% !important;
			max-width: 1350px !important;
			padding: 12px 0 32px !important;
		}

		body.woocommerce-cart .e-cart__column-start,
		body.woocommerce-cart .e-cart__column-end {
			width: 100% !important;
			max-width: 100% !important;
			position: static !important;
		}

		/* Product Card - organized mobile */
		.juhani-cart-card {
			padding: 0px !important;
			gap: 12px !important;
		}

		.juhani-cart-card__header {
			gap: 12px !important;
			padding-right: 36px !important;
			align-items: center !important;
		}

		.juhani-cart-card__thumb {
			width: 84px !important;
			height: 84px !important;
			min-width: 84px !important;
			max-width: 84px !important;
		}

		.juhani-cart-card__title,
		.juhani-cart-card__title a {
			font-size: 15px !important;
			line-height: 1.3 !important;
			margin-bottom: 4px !important;
		}

		.juhani-cart-card__unit-price,
		.juhani-cart-card__unit-price .amount {
			font-size: 14px !important;
		}

		.juhani-cart-card__details {
			padding: 10px 12px !important;
		}

		.juhani-cart-card__details dl.variation {
			grid-template-columns: minmax(100px, auto) 1fr !important;
			row-gap: 6px !important;
			column-gap: 12px !important;
		}

		.juhani-cart-card__details dl.variation dt,
		.juhani-cart-card__details dl.variation dd {
			font-size: 12.5px !important;
			padding: 3px 0 !important;
		}

		/* Footer - keep qty + subtotal in one clean row */
		.juhani-cart-card__footer {
			padding-top: 12px !important;
			gap: 12px !important;
			flex-wrap: nowrap !important;
			align-items: center !important;
		}

		.juhani-cart-card__qty-col {
			gap: 8px !important;
		}

		.juhani-qty-label {
			font-size: 13px !important;
		}

		.juhani-qty-stepper {
			height: 36px !important;
		}

		.juhani-qty-btn {
			width: 32px !important;
			min-width: 32px !important;
			height: 36px !important;
			font-size: 16px !important;
		}

		.juhani-qty-stepper .quantity input.qty {
			width: 42px !important;
			height: 36px !important;
			min-height: 36px !important;
			font-size: 14px !important;
		}

		.juhani-subtotal-label {
			font-size: 11px !important;
		}

		.juhani-subtotal-amount,
		.juhani-subtotal-amount .amount {
			font-size: 16px !important;
		}

		/* Cart Totals - organized */
		body.woocommerce-cart .cart_totals {
			padding: 18px !important;
		}

		body.woocommerce-cart .cart_totals h2 {
			font-size: 17px !important;
			padding-bottom: 12px !important;
			margin-bottom: 12px !important;
		}

		body.woocommerce-cart .cart_totals tr.cart-subtotal {
			padding: 10px 0 !important;
		}

		body.woocommerce-cart .cart_totals tr.woocommerce-shipping-totals.shipping {
			padding: 12px 0 !important;
		}

		body.woocommerce-cart .cart_totals tr.order-total th {
			font-size: 15px !important;
		}

		body.woocommerce-cart .cart_totals tr.order-total td,
		body.woocommerce-cart .cart_totals tr.order-total strong,
		body.woocommerce-cart .cart_totals tr.order-total .amount {
			font-size: 19px !important;
		}

		/* Coupon - full width organized */
		.juhani-coupon-wrapper {
			max-width: 100% !important;
			gap: 8px !important;
		}

		.juhani-coupon-input {
			height: 40px !important;
			font-size: 13px !important;
		}

		.juhani-coupon-btn {
			height: 40px !important;
			padding: 0 14px !important;
			font-size: 12px !important;
		}

		.juhani-continue-shopping-btn {
			width: 100% !important;
			justify-content: center !important;
		}
	}

	@media (max-width: 480px) {
		body.woocommerce-cart .elementor-widget-woocommerce-cart .e-cart__container {
			width: 100% !important;
			padding: 10px 12px 28px !important;
			gap: 16px !important;
		}

		.juhani-cart-card {
			padding: 12px !important;
		}

		.juhani-cart-card__header {
			gap: 10px !important;
			padding-right: 32px !important;
		}

		.juhani-cart-card__thumb {
			width: 72px !important;
			height: 72px !important;
			min-width: 72px !important;
			max-width: 72px !important;
		}

		.juhani-cart-card__title,
		.juhani-cart-card__title a {
			font-size: 14px !important;
		}

		.juhani-cart-card__unit-price,
		.juhani-cart-card__unit-price .amount {
			font-size: 13px !important;
		}

		.juhani-cart-card__details {
			padding: 8px 10px !important;
		}

		/* Keep footer in single row - no dashed wrap */
		.juhani-cart-card__footer {
			flex-wrap: nowrap !important;
			gap: 10px !important;
			padding-top: 10px !important;
		}

		.juhani-cart-card__subtotal-col {
			align-items: flex-end !important;
			text-align: right !important;
			width: auto !important;
			border-top: none !important;
			padding-top: 0 !important;
			margin-top: 0 !important;
			flex: 0 0 auto !important;
		}

		.juhani-qty-label {
			display: none !important; /* hide label to save space */
		}

		.juhani-coupon-wrapper {
			flex-direction: column !important;
			align-items: stretch !important;
		}

		.juhani-coupon-input,
		.juhani-coupon-btn {
			width: 100% !important;
		}

		body.woocommerce-cart .cart_totals {
			padding: 14px !important;
		}

		body.woocommerce-cart .cart_totals h2 {
			font-size: 16px !important;
		}
	}
	</style>

	<script id="juhani-cart-redesign-js">
	(function() {
		function bindCartEvents() {
			if (typeof jQuery === 'undefined') {
				setTimeout(bindCartEvents, 100);
				return;
			}

			const $ = jQuery;

			// Handle plus and minus stepper clicks via document delegation
			$(document).off('click.juhaniQty', '.juhani-qty-btn').on('click.juhaniQty', '.juhani-qty-btn', function(e) {
				e.preventDefault();
				e.stopPropagation();

				const $btn = $(this);
				const $stepper = $btn.closest('.juhani-qty-stepper');
				const $input = $stepper.find('input.qty');
				if (!$input.length) return;

				let currentVal = parseFloat($input.val()) || 0;
				const step = parseFloat($input.attr('step')) || 1;
				const maxAttr = $input.attr('max');
				const max = (maxAttr !== '' && maxAttr !== undefined && !isNaN(parseFloat(maxAttr))) ? parseFloat(maxAttr) : Infinity;
				const minAttr = $input.attr('min');
				const min = (minAttr !== '' && minAttr !== undefined && !isNaN(parseFloat(minAttr))) ? parseFloat(minAttr) : 1;

				if ($btn.hasClass('plus')) {
					if (currentVal + step <= max) {
						$input.val(currentVal + step).trigger('change');
					}
				} else if ($btn.hasClass('minus')) {
					const floor = Math.max(min, 1);
					if (currentVal - step >= floor) {
						$input.val(currentVal - step).trigger('change');
					}
				}

				triggerCartUpdate($input);
			});

			// Also trigger update on direct manual keyboard change
			$(document).off('change.juhaniQty', 'form.woocommerce-cart-form input.qty').on('change.juhaniQty', 'form.woocommerce-cart-form input.qty', function() {
				triggerCartUpdate($(this));
			});

			function triggerCartUpdate($input) {
				const $form = $input.closest('form.woocommerce-cart-form');
				if (!$form.length) return;

				let $hiddenUpdate = $form.find('input[type="hidden"][name="update_cart"]');
				if (!$hiddenUpdate.length) {
					$hiddenUpdate = $('<input type="hidden" name="update_cart" value="Update Cart" />').appendTo($form);
				}
				const $btn = $form.find('button[name="update_cart"]');
				if ($btn.length) {
					$btn.prop('disabled', false).attr('clicked', 'true');
				}
				clearTimeout(window._juhaniCartUpdateTimer);
				window._juhaniCartUpdateTimer = setTimeout(() => {
					$form.trigger('submit');
				}, 350);
			}
		}

		bindCartEvents();
	})();
	</script>
	<?php
}
