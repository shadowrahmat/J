<?php
/**
 * Plugin Name: Juhani Single Product Customizer
 * Description: Customizes the WooCommerce Single Product Add to Cart area: clean aligned variation swatches, custom quantity stepper, "Order Now" button with cart icon, wishlist button, and trust badges (WhatsApp, Call Us, Delivery) with zero border radius.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Disable showing selected attribute name/value in WooCommerce Variation Swatches
add_filter( 'option_woo_variation_swatches', function( $options ) {
	if ( ! is_array( $options ) ) {
		$options = array();
	}
	$options['show_variation_label'] = 'no';
	return $options;
} );
add_filter( 'default_option_woo_variation_swatches', function( $default ) {
	if ( ! is_array( $default ) ) {
		$default = array();
	}
	$default['show_variation_label'] = 'no';
	return $default;
} );

// 1. Add Trust Badges after Add to Cart form
add_action( 'woocommerce_after_add_to_cart_form', 'juhani_render_single_product_trust_badges', 20 );

function juhani_render_single_product_trust_badges() {
	?>
	<div class="juhani-product-trust-badges">
		<a href="https://wa.me/8801889164315" target="_blank" rel="noopener" class="juhani-trust-col juhani-trust-whatsapp">
			<div class="juhani-trust-icon-box whatsapp-box">
				<svg width="30" height="30" viewBox="0 0 24 24" fill="#25D366"><path d="M12.004 2C6.48 2 2 6.48 2 12c0 1.83.496 3.55 1.363 5.03L2 22l5.13-1.34A9.957 9.957 0 0 0 12.004 22c5.523 0 10.004-4.48 10.004-10s-4.481-10-10.004-10zm5.83 14.33c-.24.68-1.2 1.33-1.66 1.38-.45.05-.98.07-3.17-.81-2.31-.92-3.83-3.23-3.95-3.38-.11-.16-.94-1.25-.94-2.39 0-1.13.59-1.69.8-1.92.21-.24.47-.3.62-.3.16 0 .32 0 .46.01.15.01.35-.06.55.42.21.49.71 1.73.77 1.86.06.12.1.27.02.43-.08.15-.12.25-.24.39-.12.14-.25.3-.36.41-.12.11-.24.24-.1.48.14.24.63 1.04 1.35 1.68.93.83 1.71 1.09 1.95 1.21.24.12.38.1.52-.06.15-.16.63-.73.8-1 .17-.26.35-.22.59-.13.24.08 1.51.71 1.77.84.26.13.43.19.49.3.07.11.07.65-.17 1.33z"/></svg>
			</div>
			<div class="juhani-trust-text">
				<span class="juhani-trust-title">Chat on WhatsApp</span>
				<span class="juhani-trust-desc">Get quick support</span>
			</div>
		</a>

		<a href="tel:+8801889164315" class="juhani-trust-col juhani-trust-phone">
			<div class="juhani-trust-icon-box phone-box">
				<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#00447c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
			</div>
			<div class="juhani-trust-text">
				<span class="juhani-trust-title">Call Us</span>
				<span class="juhani-trust-desc">+8801889164315</span>
			</div>
		</a>

		<div class="juhani-trust-col juhani-trust-delivery">
			<div class="juhani-trust-icon-box delivery-box">
				<svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#00447c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13"></rect><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"></polygon><circle cx="5.5" cy="18.5" r="2.5"></circle><circle cx="18.5" cy="18.5" r="2.5"></circle></svg>
			</div>
			<div class="juhani-trust-text">
				<span class="juhani-trust-title">Delivery</span>
				<span class="juhani-trust-desc">Worldwide shipping</span>
			</div>
		</div>
	</div>
	<?php
}

// 2. Inject CSS in wp_head
add_action( 'wp_head', function() {
	?>
	<style id="juhani-product-single-css">
	/* ============================================================
	   JUHANI SINGLE PRODUCT - STRICT ZERO BORDER RADIUS
	   ============================================================ */

	/* Absolute ZERO border-radius on all components */
	.elementor-widget-woocommerce-product-add-to-cart,
	.variations_form.cart,
	.variations_form.cart *,
	.woo-variation-swatches,
	.woo-variation-swatches *,
	.variable-items-wrapper,
	.variable-items-wrapper *,
	.woocommerce-variation-add-to-cart,
	.woocommerce-variation-add-to-cart *,
	.quantity,
	.quantity *,
	.single_add_to_cart_button,
	.juhani-qty-btn,
	.juhani-wishlist-btn,
	.juhani-product-trust-badges,
	.juhani-product-trust-badges * {
		border-radius: 0px !important;
		-webkit-border-radius: 0px !important;
		-moz-border-radius: 0px !important;
	}

	/* ============================================================
	   PRODUCT PARENT SECTION - REMOVE ONLY OUTER CARD BORDER
	   Keep inner variation table / quantity / tabs borders intact
	   ============================================================ */
	/* Only the thin outer border around the full product container/card */
	.elementor-element-68036ea0,
	.elementor-element-68036ea0 > .e-con-inner {
		border: none !important;
		border-width: 0 !important;
		border-style: none !important;
		border-color: transparent !important;
		box-shadow: none !important;
	}

	/* Outer product image card - remove its surrounding border only */
	.elementor-element-780ec386 {
		border: none !important;
		border-width: 0 !important;
		--border-width: 0px !important;
		box-shadow: none !important;
	}
	.elementor-element-780ec386 .woocommerce-product-gallery__trigger + .woocommerce-product-gallery__wrapper,
	.elementor-element-780ec386 .flex-viewport {
		border: none !important;
	}

	/* Do NOT touch inner elements:
	   - table.variations, .variable-item, .quantity, .juhani-qty-btn,
	   - .woocommerce-tabs ul.wc-tabs / .woocommerce-Tabs-panel keep their 1px borders
	 */

	/* 1. Variations Table Layout: Labels left, Swatches right (Compact Gaps) */
	table.variations {
		width: 100% !important;
		border-collapse: collapse !important;
		border-spacing: 0 !important;
		margin: 0 !important;
		background: transparent !important;
		border: none !important;
	}

	table.variations tbody {
		display: block !important;
		background: transparent !important;
	}

	table.variations tr {
		display: grid !important;
		grid-template-columns: 155px minmax(0, 1fr) !important;
		column-gap: 12px !important;
		align-items: flex-start !important;
		margin-bottom: 10px !important;
		background: transparent !important;
		border: none !important;
		padding: 0 !important;
	}

	table.variations tr:last-child {
		margin-bottom: 0px !important;
		padding-bottom: 0px !important;
	}

	table.variations th.label {
		flex: 0 0 155px !important;
		width: 155px !important;
		max-width: 155px !important;
		text-align: left !important;
		padding: 4px 12px 0 0 !important;
		align-self: flex-start !important;
		margin: 0 !important;
		border: none !important;
		background: transparent !important;
	}

	table.variations th.label label {
		font-size: 14px !important;
		font-weight: 700 !important;
		color: #111827 !important;
		margin: 0 !important;
		line-height: 1.2 !important;
		display: flex !important;
		align-items: center !important;
		min-height: 34px !important;
		height: 34px !important;
		text-transform: capitalize !important;
		letter-spacing: 0.1px !important;
		white-space: nowrap !important;
	}

	table.variations td.value {
		align-self: flex-start !important;
		padding: 0 !important;
	}

	/* Hide selected attribute value label under attribute name (e.g. : 3, : 6.5, etc.) */
	.woo-selected-variation-item-name,
	.woo-variation-item-label .woo-selected-variation-item-name,
	table.variations th.label .woo-selected-variation-item-name,
	.wvs-show-label .woo-selected-variation-item-name {
		display: none !important;
		visibility: hidden !important;
		opacity: 0 !important;
		height: 0 !important;
		width: 0 !important;
		margin: 0 !important;
		padding: 0 !important;
		overflow: hidden !important;
	}

	table.variations td.value {
		flex: 1 !important;
		padding: 0 !important;
		margin: 0 !important;
		border: none !important;
		background: transparent !important;
		display: flex !important;
		align-items: center !important;
		flex-wrap: wrap !important;
	}

	/* 2. Swatches Items Wrapper & Items (Reduced Gap) */
	.woo-variation-swatches .variable-items-wrapper {
		display: flex !important;
		flex-wrap: wrap !important;
		gap: 6px !important;
		margin: 0 !important;
		padding: 0 !important;
		align-items: center !important;
	}

	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item) {
		background-color: #ffffff !important;
		border: 1.5px solid #d1d5db !important;
		border-radius: 0px !important;
		color: #1f2937 !important;
		font-size: 13px !important;
		font-weight: 600 !important;
		padding: 5px 12px !important;
		min-width: 38px !important;
		height: 34px !important;
		box-sizing: border-box !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		box-shadow: none !important;
		transition: all 0.15s ease-in-out !important;
		cursor: pointer !important;
		outline: none !important;
	}

	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item) .variable-item-contents {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
	}

	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item) .variable-item-span {
		font-size: 13px !important;
		font-weight: 600 !important;
		line-height: 1 !important;
	}

	/* Swatch Hover */
	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item):hover {
		border-color: #0084d6 !important;
		color: #0084d6 !important;
		background-color: #f0f7fd !important;
	}

	/* Standard Selected: Solid Blue, White Text */
	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item).selected,
	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item).selected:hover {
		background-color: #0084d6 !important;
		border-color: #0084d6 !important;
		color: #ffffff !important;
		font-weight: 700 !important;
		box-shadow: 0 2px 4px rgba(0, 132, 214, 0.25) !important;
	}

	.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item).selected .variable-item-span {
		color: #ffffff !important;
		font-weight: 700 !important;
	}

	/* Multi-word Button types (Machine Made, Comilla Bandha) - Soft cyan/blue fill as in mock */
	.woo-variation-swatches .variable-items-wrapper .variable-item[data-title*=" "].selected,
	.woo-variation-swatches .variable-items-wrapper .variable-item[data-title*=" "].selected:hover {
		background-color: #e6f3fb !important;
		border-color: #0084d6 !important;
		color: #005c96 !important;
		box-shadow: none !important;
	}

	.woo-variation-swatches .variable-items-wrapper .variable-item[data-title*=" "].selected .variable-item-span {
		color: #005c96 !important;
		font-weight: 700 !important;
	}

	/* Disabled Swatches */
	.woo-variation-swatches .variable-items-wrapper .variable-item.disabled {
		opacity: 0.35 !important;
		filter: grayscale(1) !important;
		border-style: dashed !important;
		cursor: not-allowed !important;
	}

	a.reset_variations {
		font-size: 12px !important;
		color: #ef4444 !important;
		font-weight: 600 !important;
		text-decoration: underline !important;
		margin-left: 14px !important;
		padding-left: 6px !important;
		display: inline-block !important;
	}

	a.reset_variations[style*="hidden"],
	a.reset_variations.hide {
		display: none !important;
	}

	/* 3. Single Variation Details & Add to Cart Action Row */
	form.cart,
	.variations_form.cart {
		margin-bottom: 0px !important;
	}

	.single_variation_wrap {
		margin: 0 !important;
		padding: 0 !important;
	}

	.woocommerce-variation.single_variation {
		margin: 0 !important;
		padding: 0 !important;
	}

	.woocommerce-variation.single_variation:empty,
	.woocommerce-variation.single_variation[style*="display: none"] {
		display: none !important;
		margin: 0 !important;
		padding: 0 !important;
		height: 0 !important;
	}

	/* Variation Price — show below when all attributes selected */
	.woocommerce-variation.single_variation {
		margin: 14px 0 10px !important;
		padding: 0 !important;
		background: transparent !important;
		border: none !important;
	}
	.woocommerce-variation.single_variation:empty,
	.woocommerce-variation.single_variation[style*="display: none"] {
		display: none !important;
		margin: 0 !important;
		padding: 0 !important;
		height: 0 !important;
	}
	.woocommerce-variation-price {
		display: block !important;
		visibility: visible !important;
		height: auto !important;
		margin: 0 !important;
		padding: 4px 0 !important;
		overflow: visible !important;
		background: transparent !important;
		background-color: transparent !important;
		border: none !important;
		border-width: 0 !important;
		box-shadow: none !important;
		border-radius: 0 !important;
		text-align: left !important;
	}
	.woocommerce-variation-price .price,
	.single_variation .woocommerce-variation-price .price,
	.woocommerce-variation.single_variation .price {
		display: block !important;
		visibility: visible !important;
		height: auto !important;
		overflow: visible !important;
		font-size: 22px !important;
		font-weight: 800 !important;
		color: #0f172a !important;
		line-height: 1.2 !important;
		margin: 0 !important;
		padding: 0 !important;
	}
	.woocommerce-variation-price .price .amount,
	.woocommerce-variation-price .amount {
		font-size: 22px !important;
		font-weight: 800 !important;
		color: #0f172a !important;
	}

	.woocommerce-variation-availability {
		margin: 2px 0 4px 0 !important;
	}

	.woocommerce-variation-add-to-cart,
	form.cart:not(.variations_form) {
		display: flex !important;
		align-items: center !important;
		gap: 12px !important;
		margin-top: 8px !important;
		margin-bottom: 14px !important;
		flex-wrap: wrap !important;
	}

	/* Quantity Box Stepper: [ -  1  + ] */
	.woocommerce-variation-add-to-cart .quantity {
		display: inline-flex !important;
		align-items: center !important;
		border: 1.5px solid #d1d5db !important;
		background: #ffffff !important;
		height: 48px !important;
		padding: 0 !important;
		margin: 0 !important;
		border-radius: 0px !important;
		box-sizing: border-box !important;
	}

	.juhani-qty-btn {
		width: 38px !important;
		height: 100% !important;
		border: none !important;
		background: transparent !important;
		font-size: 18px !important;
		font-weight: 600 !important;
		color: #374151 !important;
		cursor: pointer !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		padding: 0 !important;
		margin: 0 !important;
		border-radius: 0px !important;
		transition: background-color 0.15s ease !important;
		user-select: none !important;
	}

	.juhani-qty-btn:hover {
		background-color: #f3f4f6 !important;
		color: #111827 !important;
	}

	.woocommerce-variation-add-to-cart .quantity input.qty {
		width: 48px !important;
		height: 100% !important;
		border: none !important;
		background: transparent !important;
		text-align: center !important;
		font-size: 16px !important;
		font-weight: 700 !important;
		color: #111827 !important;
		padding: 0 !important;
		margin: 0 !important;
		border-radius: 0px !important;
		box-shadow: none !important;
		-moz-appearance: textfield !important;
	}

	.woocommerce-variation-add-to-cart .quantity input.qty::-webkit-outer-spin-button,
	.woocommerce-variation-add-to-cart .quantity input.qty::-webkit-inner-spin-button {
		-webkit-appearance: none !important;
		margin: 0 !important;
	}

	/* Order Now Button */
	.woocommerce-variation-add-to-cart button.single_add_to_cart_button.button {
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		gap: 10px !important;
		height: 48px !important;
		min-width: 220px !important;
		padding: 0 32px !important;
		background-color: #00447c !important;
		color: #ffffff !important;
		font-size: 16px !important;
		font-weight: 700 !important;
		letter-spacing: 0.3px !important;
		border: none !important;
		border-radius: 0px !important;
		box-shadow: 0 2px 5px rgba(0, 68, 124, 0.25) !important;
		cursor: pointer !important;
		transition: background-color 0.15s ease, transform 0.1s ease !important;
		line-height: 1 !important;
	}

	.woocommerce-variation-add-to-cart button.single_add_to_cart_button.button:hover {
		background-color: #00335e !important;
		color: #ffffff !important;
	}

	.juhani-btn-cart-icon {
		display: inline-block !important;
		vertical-align: middle !important;
		stroke: #ffffff !important;
		fill: none !important;
	}

	/* Wishlist Button */
	.juhani-wishlist-btn {
		width: 48px !important;
		height: 48px !important;
		border: 1.5px solid #d1d5db !important;
		background: #ffffff !important;
		border-radius: 0px !important;
		display: inline-flex !important;
		align-items: center !important;
		justify-content: center !important;
		cursor: pointer !important;
		transition: all 0.15s ease !important;
		padding: 0 !important;
		margin: 0 !important;
	}

	.juhani-wishlist-btn:hover {
		border-color: #00447c !important;
		background-color: #f8fafc !important;
	}

	.juhani-wishlist-btn svg {
		stroke: #00447c !important;
		transition: transform 0.2s ease !important;
	}

	.juhani-wishlist-btn:hover svg {
		transform: scale(1.15) !important;
	}

	.juhani-wishlist-btn.is-active svg {
		fill: #ef4444 !important;
		stroke: #ef4444 !important;
	}

	/* 4. Trust Badges Row */
	.juhani-product-trust-badges {
		display: flex !important;
		align-items: center !important;
		justify-content: space-between !important;
		gap: 20px !important;
		margin-top: 14px !important;
		padding-top: 14px !important;
		border-top: 1px solid #e5e7eb !important;
		width: 100% !important;
	}

	.juhani-trust-col {
		display: flex !important;
		align-items: center !important;
		gap: 12px !important;
		text-decoration: none !important;
		color: inherit !important;
		cursor: default !important;
	}

	a.juhani-trust-col {
		cursor: pointer !important;
	}

	a.juhani-trust-col:hover .juhani-trust-title {
		color: #0084d6 !important;
	}

	.juhani-trust-icon-box {
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		width: 38px !important;
		height: 38px !important;
		flex-shrink: 0 !important;
	}

	.juhani-trust-text {
		display: flex !important;
		flex-direction: column !important;
		line-height: 1.25 !important;
	}

	.juhani-trust-title {
		font-size: 14px !important;
		font-weight: 700 !important;
		color: #111827 !important;
	}

	.juhani-trust-desc {
		font-size: 12px !important;
		color: #6b7280 !important;
		margin-top: 2px !important;
	}

	/* Responsive for Mobile Devices (<= 767px) */
	@media (max-width: 767px) {
		/* 1. Attribute Label & Options on Same Horizontal Row & Aligned */
		table.variations {
			width: 100% !important;
			margin-bottom: 6px !important;
		}

		table.variations tr {
			display: grid !important;
			grid-template-columns: 120px minmax(0, 1fr) !important;
			column-gap: 12px !important;
			align-items: flex-start !important;
			margin-bottom: 10px !important;
			padding: 0 !important;
			width: 100% !important;
		}

		table.variations tr:last-child {
			margin-bottom: 0px !important;
		}

		table.variations th.label {
			flex: none !important;
			width: 120px !important;
			min-width: 120px !important;
			max-width: 120px !important;
			padding: 4px 0 0 0 !important;
			align-self: flex-start !important;
			margin: 0 !important;
			box-sizing: border-box !important;
			text-align: left !important;
		}

		table.variations th.label label {
			display: flex !important;
			align-items: center !important;
			font-size: 13px !important;
			font-weight: 700 !important;
			color: #0f172a !important;
			min-height: 34px !important;
			height: 34px !important;
			margin: 0 !important;
			line-height: 1.2 !important;
			white-space: nowrap !important;
			text-transform: capitalize !important;
		}

		table.variations td.value {
			align-self: flex-start !important;
			width: 100% !important;
			max-width: 100% !important;
			flex: 1 1 auto !important;
			display: flex !important;
			align-items: flex-start !important;
			flex-wrap: wrap !important;
			padding: 0 !important;
			margin: 0 !important;
		}

		.woo-variation-swatches .variable-items-wrapper {
			display: flex !important;
			flex-wrap: wrap !important;
			gap: 6px !important;
			align-items: center !important;
			align-content: flex-start !important;
			justify-content: flex-start !important;
			margin: 0 !important;
			padding: 0 !important;
			width: 100% !important;
		}

		.woo-variation-swatches .variable-items-wrapper .variable-item:not(.radio-variable-item) {
			min-width: 36px !important;
			height: 34px !important;
			font-size: 12.5px !important;
			padding: 4px 10px !important;
		}

		a.reset_variations {
			margin: 6px 0 0 14px !important;
			padding-left: 6px !important;
			font-size: 12px !important;
		}

		/* Variation price below when all attributes selected — mobile compact */
		.woocommerce-variation.single_variation {
			margin: 12px 0 8px !important;
		}
		.woocommerce-variation-price {
			padding: 4px 0 !important;
			background: transparent !important;
			border: none !important;
		}
		.woocommerce-variation-price .price,
		.woocommerce-variation-price .amount {
			font-size: 18px !important;
		}

		/* 2. Organized 1-Row Add to Cart Action Bar (Qty + Order Now + Wishlist) */
		.woocommerce-variation-add-to-cart,
		form.cart:not(.variations_form) {
			display: flex !important;
			flex-direction: row !important;
			flex-wrap: nowrap !important;
			align-items: center !important;
			gap: 8px !important;
			margin-top: 14px !important;
			margin-bottom: 14px !important;
			width: 100% !important;
			box-sizing: border-box !important;
		}

		/* Quantity Stepper [ -  1  + ] */
		.woocommerce-variation-add-to-cart .quantity {
			display: inline-flex !important;
			align-items: center !important;
			height: 46px !important;
			width: 98px !important;
			min-width: 98px !important;
			max-width: 98px !important;
			flex: 0 0 98px !important;
			box-sizing: border-box !important;
			margin: 0 !important;
			border: 1.5px solid #d1d5db !important;
		}

		.juhani-qty-btn {
			width: 29px !important;
			height: 100% !important;
			font-size: 16px !important;
			flex-shrink: 0 !important;
		}

		.woocommerce-variation-add-to-cart .quantity input.qty {
			width: 40px !important;
			height: 100% !important;
			font-size: 15px !important;
			font-weight: 700 !important;
			padding: 0 !important;
			flex-grow: 1 !important;
		}

		/* Order Now Primary Button */
		.woocommerce-variation-add-to-cart button.single_add_to_cart_button.button {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			gap: 6px !important;
			height: 46px !important;
			flex: 1 1 auto !important;
			min-width: 0 !important;
			width: auto !important;
			padding: 0 12px !important;
			margin: 0 !important;
			font-size: 14.5px !important;
			font-weight: 700 !important;
			letter-spacing: 0.2px !important;
			white-space: nowrap !important;
			background-color: #00447c !important;
			box-shadow: 0 2px 6px rgba(0, 68, 124, 0.25) !important;
			border: none !important;
			border-radius: 0px !important;
			cursor: pointer !important;
		}

		.juhani-btn-cart-icon {
			width: 18px !important;
			height: 18px !important;
			margin-right: 4px !important;
			flex-shrink: 0 !important;
		}

		/* Wishlist Button */
		.juhani-wishlist-btn {
			display: inline-flex !important;
			align-items: center !important;
			justify-content: center !important;
			width: 46px !important;
			min-width: 46px !important;
			max-width: 46px !important;
			height: 46px !important;
			flex: 0 0 46px !important;
			box-sizing: border-box !important;
			margin: 0 !important;
			border: 1.5px solid #d1d5db !important;
			border-radius: 0px !important;
			background: #ffffff !important;
		}

		.juhani-wishlist-btn svg {
			width: 19px !important;
			height: 19px !important;
		}

		/* 3. Modern Compact Trust Badges Grid */
		.juhani-product-trust-badges {
			display: grid !important;
			grid-template-columns: repeat(3, 1fr) !important;
			gap: 8px !important;
			margin-top: 14px !important;
			padding-top: 14px !important;
			border-top: 1px solid #e2e8f0 !important;
			width: 100% !important;
			box-sizing: border-box !important;
		}

		.juhani-trust-col {
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
			text-align: center !important;
			padding: 8px 4px !important;
			background: #f8fafc !important;
			border: 1px solid #e2e8f0 !important;
			border-radius: 0px !important;
			gap: 4px !important;
			text-decoration: none !important;
			box-sizing: border-box !important;
			min-height: 64px !important;
		}

		.juhani-trust-icon-box {
			width: 26px !important;
			height: 26px !important;
		}

		.juhani-trust-icon-box svg {
			width: 22px !important;
			height: 22px !important;
		}

		.juhani-trust-text {
			display: flex !important;
			flex-direction: column !important;
			align-items: center !important;
			justify-content: center !important;
		}

		.juhani-trust-title {
			font-size: 11px !important;
			font-weight: 700 !important;
			color: #0f172a !important;
			line-height: 1.2 !important;
			text-align: center !important;
		}

		.juhani-trust-desc {
			display: none !important;
		}
	}
	</style>
	<?php
}, 100 );

// 3. Inject JavaScript for Quantity Stepper and Cart Icon
add_action( 'wp_footer', function() {
	?>
	<script id="juhani-single-product-js">
	(function() {
		function initJuhaniProductEnhancements() {
			// 1. Enhance Quantity Stepper: add minus & plus buttons
			const qtyContainers = document.querySelectorAll('.woocommerce-variation-add-to-cart .quantity, form.cart .quantity');
			qtyContainers.forEach(container => {
				if (container.dataset.juhaniQtyEnhanced) return;
				const input = container.querySelector('input.qty');
				if (!input) return;

				container.dataset.juhaniQtyEnhanced = '1';

				// Minus button
				const minusBtn = document.createElement('button');
				minusBtn.type = 'button';
				minusBtn.className = 'juhani-qty-btn juhani-qty-minus';
				minusBtn.setAttribute('aria-label', 'Decrease quantity');
				minusBtn.innerHTML = '−';

				// Plus button
				const plusBtn = document.createElement('button');
				plusBtn.type = 'button';
				plusBtn.className = 'juhani-qty-btn juhani-qty-plus';
				plusBtn.setAttribute('aria-label', 'Increase quantity');
				plusBtn.innerHTML = '+';

				minusBtn.addEventListener('click', function(e) {
					e.preventDefault();
					let val = parseInt(input.value) || 1;
					const min = parseInt(input.getAttribute('min')) || 1;
					const step = parseInt(input.getAttribute('step')) || 1;
					if (val > min) {
						input.value = val - step;
						input.dispatchEvent(new Event('change', { bubbles: true }));
					}
				});

				plusBtn.addEventListener('click', function(e) {
					e.preventDefault();
					let val = parseInt(input.value) || 1;
					const max = parseInt(input.getAttribute('max')) || 9999;
					const step = parseInt(input.getAttribute('step')) || 1;
					if (val < max) {
						input.value = val + step;
						input.dispatchEvent(new Event('change', { bubbles: true }));
					}
				});

				container.insertBefore(minusBtn, input);
				container.appendChild(plusBtn);
			});

			// 2. Enhance "Order Now" Button with Shopping Cart Icon
			const cartButtons = document.querySelectorAll('.single_add_to_cart_button');
			cartButtons.forEach(btn => {
				if (btn.dataset.juhaniIconAdded) return;
				btn.dataset.juhaniIconAdded = '1';

				// Ensure text is Order Now
				if (!btn.querySelector('.juhani-btn-cart-icon')) {
					const cartSvg = '<svg class="juhani-btn-cart-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;"><circle cx="9" cy="21" r="1"></circle><circle cx="20" cy="21" r="1"></circle><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path></svg>';
					btn.innerHTML = cartSvg + '<span>' + (btn.textContent.trim() || 'Order Now') + '</span>';
				}

				// Add Wishlist button right after Order Now button if not exists
				const parent = btn.parentElement;
				if (parent && !parent.querySelector('.juhani-wishlist-btn')) {
					const wishBtn = document.createElement('button');
					wishBtn.type = 'button';
					wishBtn.className = 'juhani-wishlist-btn';
					wishBtn.title = 'Add to Wishlist';
					wishBtn.setAttribute('aria-label', 'Add to Wishlist');
					wishBtn.innerHTML = '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#00447c" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
					
					wishBtn.addEventListener('click', function(e) {
						e.preventDefault();
						wishBtn.classList.toggle('is-active');
					});

					btn.insertAdjacentElement('afterend', wishBtn);
				}
			});

			// 3. Clean up any selected variation text under attribute names
			document.querySelectorAll('.woo-selected-variation-item-name').forEach(el => el.remove());

			// 4. Dynamic Top Price Syncing on Variation Change
			function getSingleProductTopPrice($form) {
				if (!window.jQuery) return null;
				// 1. Same parent container
				let $price = $form.closest('.elementor-widget-woocommerce-product-add-to-cart').parent().find('.elementor-widget-woocommerce-product-price .price');
				if ($price.length) return $price.first();

				// 2. Sibling before add-to-cart
				$price = $form.closest('.elementor-widget-woocommerce-product-add-to-cart').prevAll('.elementor-widget-woocommerce-product-price').find('.price');
				if ($price.length) return $price.first();

				// 3. WooCommerce standard single product container
				$price = $form.closest('.product, .type-product').find('.summary .price, .elementor-widget-woocommerce-product-price .price');
				if ($price.length) return $price.first();

				return window.jQuery('.elementor-widget-woocommerce-product-price .price').first();
			}

			if (window.jQuery) {
				const $ = window.jQuery;
				$('.variations_form').each(function() {
					const $form = $(this);
					const $topPrice = getSingleProductTopPrice($form);

					if ($topPrice && $topPrice.length && !$topPrice.attr('data-original-price')) {
						$topPrice.attr('data-original-price', $topPrice.html());
					}

					if ($form.data('juhaniPriceBound')) return;
					$form.data('juhaniPriceBound', true);

					// When a valid matching variation is selected
					$form.on('found_variation.juhaniPrice', function(event, variation) {
						if (!variation) return;
						const $targetPrice = getSingleProductTopPrice($form);
						if (!$targetPrice || !$targetPrice.length) return;

						let newPriceHtml = variation.price_html || '';
						if (!newPriceHtml && variation.display_price) {
							const formattedPrice = Number(variation.display_price).toLocaleString('en-US', {
								minimumFractionDigits: 2,
								maximumFractionDigits: 2
							});
							newPriceHtml = '<span class="price"><span class="woocommerce-Price-amount amount"><bdi>' +
								formattedPrice +
								'<span class="woocommerce-Price-currencySymbol" translate="no">&#2547;&nbsp;</span></bdi></span></span>';
						}

						if (newPriceHtml) {
							// Strip outer .price wrapper if present so styling remains clean inside p.price
							const temp = document.createElement('div');
							temp.innerHTML = newPriceHtml;
							const innerPrice = temp.querySelector('.price');
							if (innerPrice) {
								newPriceHtml = innerPrice.innerHTML;
							}
							$targetPrice.html(newPriceHtml);
						}
					});

					// When variation is reset or incomplete
					$form.on('reset_data.juhaniPrice hide_variation.juhaniPrice', function() {
						const $targetPrice = getSingleProductTopPrice($form);
						if ($targetPrice && $targetPrice.length) {
							const originalHtml = $targetPrice.attr('data-original-price');
							if (originalHtml) {
								$targetPrice.html(originalHtml);
							}
						}
					});
				});
			}
		}

		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', initJuhaniProductEnhancements);
		} else {
			initJuhaniProductEnhancements();
		}

		window.addEventListener('load', () => setTimeout(initJuhaniProductEnhancements, 300));
		if (window.jQuery) {
			jQuery(document).on('woocommerce_variation_has_changed show_variation hide_variation wvs-selected-item', function() {
				jQuery('.woo-selected-variation-item-name').remove();
				setTimeout(initJuhaniProductEnhancements, 50);
			});
		}
	})();
	</script>
	<?php
}, 100 );
