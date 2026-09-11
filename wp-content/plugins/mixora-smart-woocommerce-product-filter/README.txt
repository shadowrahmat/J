Mixora Smart WooCommerce Product Filter v2.0.24

What changed in v2.0.22
- Category-aware attribute options.
- On a WooCommerce product category archive, the filter now scans only products in that category (including child categories).
- Attribute groups with no values in the current category are hidden automatically.
- Attribute term values are limited to values actually used by products in the current category.
- Example: if Panjabi products use only Size 40, 42 and 44, the Panjabi category page will show only 40, 42 and 44 instead of M/L/XL values from other categories.
- Supports WooCommerce global attributes (pa_*) and custom per-product attributes.
- Retains AJAX price/category/attribute filtering, sorting, pagination and responsive drawer.

Additional changes in v2.0.21
- Increased desktop product title, price and action-button text sizing for better readability.
- Product action buttons now force white text on the dark button background.
- Hardened CSS isolation: filter styles are scoped under a unique .mxswpf-root wrapper so they cannot leak into header live-search result cards or other WooCommerce/Elementor widgets.
- Hardened JavaScript isolation: renamed the localized JS object and WordPress asset handles to plugin-specific names to avoid collisions with other Mixora custom/live-search scripts.
- Retains category-aware attribute values introduced in v2.0.20.

Shortcode
[mixora_product_filter]
Optional: [mixora_product_filter per_page="9" columns="3"]

Install
1. Back up the existing plugin.
2. WordPress > Plugins > Add New > Upload Plugin.
3. Upload this ZIP. If WordPress detects the existing plugin, choose Replace current with uploaded.
4. Clear site/plugin cache after update.

New in v2.0.22
- Results header now shows the active product category name (for example: Panjabi) alongside the product count.
- Selecting a category in the filter updates the results header to that category name automatically via AJAX.
- If multiple categories are selected, their names are shown together.
- Category changes refresh the attribute controls so the visible attribute values stay relevant to the newly selected category scope.
- Desktop sorting dropdown is constrained to a compact 200px width so it no longer stretches across the result header.
- Mobile sorting remains responsive.


New in v2.0.23
- Fixed the mobile Filter button label/icon becoming invisible due to theme/global button styles.
- Mobile Filter control now explicitly renders a readable black icon + “Filters” label on a white button.
- Added a compact active-filter count badge.
- Reworked the mobile toolbar into a balanced two-control layout with full-width Filter and Sort controls.
- Fixed the mobile drawer Close (×) button so it is always visible, touch-friendly and resistant to theme CSS overrides.
- Improved drawer header spacing and Reset/Close control sizing.
- Added aria-expanded state, focus on open, and Escape-key closing for better accessibility and usability.
- Retains all v2.0.22 category-aware attributes, AJAX filtering, category title, sorting and product-grid behavior.


New in v2.0.24
- Redesigned the mobile drawer close control for a cleaner, more modern appearance.
- Replaced the font-based × character with a centered inline SVG icon so it renders consistently across themes/devices.
- Close button is now a compact 40×40 white control with a subtle border/shadow, rounded corners, and precise icon alignment.
- Added clear hover/focus inversion and a small press interaction while retaining accessibility labels and keyboard behavior.
