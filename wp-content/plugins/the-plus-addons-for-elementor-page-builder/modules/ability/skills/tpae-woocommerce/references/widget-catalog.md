# WooCommerce widget catalog - per-widget notes

Notes for each TPAE WooCommerce widget: what it's for, its template context, and what to watch. **`list-widgets` is authoritative** for availability (all need WooCommerce active); `get-widget-schema` is authoritative for keys. All are Pro (`tpaep/`).

## Single product (build in a Single Product theme template)

- **Woo Single Image (`tpaep-woo-single-image`)** - product gallery: main image + thumbnails, zoom, lightbox, layout (grid/slider/stacked). The visual anchor of the product page.
- **Woo Single Basic (`tpaep-woo-single-basic`)** - title, short description, rating, meta (SKU/category), stock, add-to-cart, quantity. The core buy block; toggle the elements you want and order them.
- **Woo Single Pricing (`tpaep-woo-single-pricing`)** - price display (regular/sale), sale badge. Use when you want price styled separately from the basic block.
- **Woo Single Tabs (`tpaep-woo-single-tabs`)** - description / additional information / reviews as styled tabs or accordion. Keep the reviews tab; it's conversion-relevant.

Compose these inside one Single Product template; preview against a real product so Verify is meaningful.

## Cart & checkout (build in the matching template)

- **Woo Cart (`tpaep-woo-cart`)** - cart table, totals, coupon field, cross-sells, proceed-to-checkout. Keep the coupon and totals visible; ensure mobile stacking.
- **Woo Checkout (`tpaep-woo-checkout`)** - billing/shipping, order review, payment, place-order. Style only - never intercept the transaction. Mobile usability is critical.
- **Woo Multi Step (`tpaep-woo-multi-step`)** - checkout split into steps (cart -> details -> payment). Reduces perceived friction; confirm every step's fields are reachable and the final place-order works.

## Account & post-purchase

- **Woo My Account (`tpaep-woo-myaccount`)** - dashboard, orders, downloads, addresses, account details. Build in a My Account template.
- **Woo Thank You (`tpaep-woo-thank-you`)** - order-received / confirmation. Show order summary + next steps; a good upsell/subscribe moment (with consent).
- **Woo Order Track (`tpaep-woo-order-track`)** - order status lookup by id/email. Place on a dedicated tracking page or template.

## Merchandising (ordinary pages)

- **Product Listout (`tpaep-product-listout`)** - product grids/carousels: shop pages, "featured", "related", curated collections. Query by category/tag/featured/on-sale; columns, card elements, pagination/load-more. Bind the query; don't hard-code product ids.
- **WP Quickview (`tpaep-wp-quickview`)** - quick-view modal on product cards, so shoppers preview without leaving the grid. Pairs with Product Listout.
- **Woo Wishlist (`tpaep-woo-wishlist`)** - save-for-later button + a wishlist page. Confirm the wishlist page exists/target.
- **Woo Compare (`tpaep-woo-compare`)** - add-to-compare + a comparison table. Keep the compared attribute set focused.
- **Coupon Code (`tpaep-coupon-code`)** - display/copy a promo code (marketing), distinct from the checkout coupon field.

## Rules

1. Confirm WooCommerce is active and the correct template context exists (create via tpae-theme-builder) before building.
2. Preview single-product / cart / account widgets against real data.
3. Read `get-widget-schema` before writing; Woo widgets have dense element toggles.
4. Style and lay out only - never fabricate prices/stock/orders or intercept checkout logic.
5. Declare mobile behavior for every grid, cart, and checkout section.
