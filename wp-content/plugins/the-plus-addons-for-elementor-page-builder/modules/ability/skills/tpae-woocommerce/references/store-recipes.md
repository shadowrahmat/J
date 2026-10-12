# Store recipes - WooCommerce section compositions

How to compose TPAE Woo widgets into the standard store surfaces. Design taste and workflow come from tpae-design; the theme templates come from tpae-theme-builder; these are the Woo-layer recipes. All widgets Pro; WooCommerce must be active.

## Single product page (Single Product template)

Create the template (tpae-theme-builder: type Single Product + conditions), preview against a real product, then compose:
1. Top: **Woo Single Image** (gallery) beside **Woo Single Basic** (title, rating, short desc, price via the basic block or **Woo Single Pricing**, quantity, add-to-cart) - a two-column split that stacks on mobile.
2. Below: **Woo Single Tabs** (description / info / reviews).
3. Optional: a **Product Listout** "related / you may also like" row; **WP Quickview** enabled on those cards.
Verify: image, title, price, add-to-cart, tabs all bound to the previewed product.

## Shop / archive / collection (Product Archive template or a page)

- **Product Listout** as the grid: set the query (category / featured / on-sale / manual), columns, card elements (image, title, price, rating, add-to-cart), and pagination or load-more.
- Add **WP Quickview** for in-grid preview and **Woo Wishlist** buttons on cards.
- Declare mobile columns. Bind the query - never hard-code product ids.

## Cart page (Cart template)

- **Woo Cart**: items table, quantity, coupon field, totals, cross-sells, proceed-to-checkout. Keep coupon + totals prominent; stack cleanly on mobile.

## Checkout (Checkout template)

- **Woo Checkout** for a single-page flow, or **Woo Multi Step** to split cart -> details -> payment.
- Style only; never intercept the transaction. Verify the place-order control renders and every step's fields are reachable, desktop and mobile.

## My Account (My Account template)

- **Woo My Account**: dashboard, orders, downloads, addresses, details. Style the navigation and panels; don't remove functional tabs.

## Post-purchase

- **Thank-you** (Thank You template): **Woo Thank You** with order summary + next steps; optional consented subscribe/upsell.
- **Order tracking** (page/template): **Woo Order Track** lookup form + result.

## Wishlist & compare

- **Wishlist page**: **Woo Wishlist** table + a continue-shopping path; confirm the wishlist target page.
- **Compare**: **Woo Compare** button on cards + a comparison table on a compare page; keep the attribute set focused.

## Promo

- **Coupon Code** widget on landing/campaign pages to surface a code (distinct from the checkout coupon field).

## Composition rules

1. Build template-context widgets (single/cart/checkout/account/thank-you/order-track) inside their WooCommerce theme template, not on a plain page.
2. Preview against real product/order data before Verify.
3. Bind queries and dynamic values; never fabricate commerce data.
4. Mobile behavior declared for every grid, cart, and checkout section.
5. Never add code that intercepts checkout, payment, or tax logic - layout only.
