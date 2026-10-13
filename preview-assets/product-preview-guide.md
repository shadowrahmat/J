# Single product preview

Preview: http://localhost/juhani/product-preview.php

Example product: **Cast Net - 25 Ghor**. Photos, prices, stock, SKU, attributes, available variations and related products are read from WooCommerce on each request. The existing product page, Elementor templates, header and footer are not replaced.

The layout includes a gallery/lightbox, a variation picker, quantity controls, specifications/review tabs and related products. Partial choices show the price range of matching available variations. Clicking a selected option again clears that individual choice; a completed selection shows the selected measurements and SKU. Quantity typing is preserved while editing and normalized on change/submission. Gallery thumbnails and the lightbox support arrow keys, and variation-specific photographs are used when available.

The selector at the bottom opens previews for other published variable products. It navigates the preview and does not modify a product.

Invalid or unavailable combinations cannot enable the order button. The form submits the selected variation and attributes to the real product page; preview verification did not submit to cart or place an order.

## CSS class locations

All classes are explicitly assigned in `product-preview.php`; no Elementor-generated selectors are used.

| CSS Class | Current location / corresponding Elementor component |
| --- | --- |
| `juhani-product-preview` | Preview's main layout wrapper |
| `juhani-product-breadcrumb` | Breadcrumb navigation |
| `juhani-product-overview` | Gallery and product summary section |
| `juhani-product-gallery` | Product images/gallery container |
| `juhani-product-summary` | Title, price and purchase information container |
| `juhani-product-options` | Variation and quantity form |
| `juhani-product-details` | Specifications and reviews section |
| `juhani-product-related` | Related products section |
| `juhani-product-preview-note` / `juhani-product-preview-picker` | Preview footer and product selector |

For a future Elementor implementation, assign these exact component classes under **Advanced → CSS Classes** to their corresponding containers/widgets. Keep functional input names and variation metadata intact.

## Complete CSS and implementation

The complete, active CSS is in **`D:\xampp\htdocs\Juhani\preview-assets\product.css`**:

http://localhost/juhani/preview-assets/product.css

`product-preview.php` enqueues this stylesheet only for this preview. Edit this file to change the preview styling. It is not an automatically generated Elementor CSS file or a reference-only copy. JavaScript is in `preview-assets/product.js`.

The stylesheet is modular, scoped to manually assigned classes and includes desktop, tablet and mobile layouts. There are no `!important` declarations or generated Elementor ID/class selectors.

Checks: published product data and 138 available variations, exact selected prices/SKUs, invalid combinations, quantity bounds, reset, gallery/lightbox state, keyboard tabs, asset URLs, and form field/action structure. No connected browser was available for visual inspection or a real browser interaction check.
