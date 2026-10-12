---
name: tpae-woocommerce
description: Build and customize WooCommerce store layouts with The Plus Addons (TPAE) - single-product pages, shop/archive grids, cart, checkout, my-account, order tracking, thank-you, wishlist, compare, and quick-view. Use whenever the user wants to design a store, product page, cart or checkout, or add Woo features with TPAE - "design my product page", "custom checkout", "add a wishlist", "make a shop grid", "multi-step checkout", "quick view on products". Requires WooCommerce active. Design taste and the build workflow come from the tpae-design skill; theme-template creation (single/archive/cart templates) comes from tpae-theme-builder; this skill owns the Woo widget layer.
---

# The Plus Addons - WooCommerce Skill

Build store layouts with TPAE's WooCommerce widgets. This skill sits on top of **tpae-design** (owns discover->plan->build->verify, the token contract, safety) and works with **tpae-theme-builder** (owns creating the WooCommerce theme templates these widgets live in). This skill owns *which Woo widget, where, and how*.

## Hard prerequisite - WooCommerce must be active

Every widget here needs WooCommerce active. Before planning: confirm it via `list-widgets` (the `tpaep-woo-*` abilities only register when Woo is present). If Woo is inactive, say so and stop - don't build store UI that can't render. All these widgets are **Pro** (`tpaep/`); on a free site, follow the tpae-design Pro boundary.

## Templates vs. pages - know where a widget belongs

Most Woo widgets only render correctly inside the matching **WooCommerce theme template** with the right preview + display conditions. Create those with **tpae-theme-builder** (`create-theme-template` + `set-template-conditions`), then compose with the widgets below. A few work on ordinary pages.

| Context (build as a theme template) | Widgets |
|---|---|
| **Single product** | Woo Single Image · Woo Single Basic · Woo Single Pricing · Woo Single Tabs |
| **Cart** | Woo Cart |
| **Checkout** | Woo Checkout · Woo Multi Step |
| **My Account** | Woo My Account |
| **Thank-you / order received** | Woo Thank You |
| **Order tracking** | Woo Order Track |

| Place on ordinary pages / anywhere | Widgets |
|---|---|
| Shop / archive / curated grids | Product Listout |
| Wishlist page or button | Woo Wishlist |
| Compare table / button | Woo Compare |
| Quick-view on any product card | WP Quickview |
| Coupon / promo | Coupon Code |

Full per-widget notes: `widget-catalog`. Section recipes: `store-recipes`. (Fetch via `tpae/get-design-guide` with `skill: "tpae-woocommerce"`.)

## Build rules (with tpae-design's workflow)

1. **Confirm Woo + the widget** via `list-widgets`; confirm the correct template context exists (or create it via tpae-theme-builder first).
2. **Preview against a real product/order.** A single-product template renders nothing useful without a product preview source - set it so Verify is meaningful.
3. **Read the schema before writing** - `get-widget-schema`; Woo widgets have many layout/column/element-toggle keys. Never guess.
4. **Respect Woo's own data and hooks.** Style and lay out; never fabricate prices, stock, or order data - those come from WooCommerce. Bind dynamic pieces with `set-dynamic-tag` where offered.
5. **Declare mobile behavior** for every grid and multi-column store section; checkout and cart especially must stay usable on mobile.
6. **Never touch payment, tax, or checkout *logic*.** You compose the layout; WooCommerce owns the transaction. Don't add custom JS/CSS that intercepts checkout (tpae-design non-negotiable 4 applies doubly here).

## Verify (Woo specifics, on top of tpae-design Verify)

- Single product: image, title, price, add-to-cart, and tabs all present and bound to the previewed product.
- Cart/checkout: totals, coupon field, and the pay/place-order control render; the flow isn't visually broken on mobile.
- Grids: product cards show image/title/price/button; pagination or load-more behaves.
- Resolve any missing-element warning before reporting done; say plainly if a piece depends on a WooCommerce setting the user must change.

## Free / boundary note

All Woo widgets here are Pro. On a free site, deliver what core WooCommerce blocks/shortcodes + Elementor can do first, name what the TPAE Woo widget adds (design control, extra elements), then one capped Pro mention per the tpae-design Pro boundary.

## Shared gotchas (full detail: tpae-design -> troubleshooting)

- **Widget identity.** At the Elementor level TPAE widgets are `tp-*` (`tp-pricing-table`, `tp-woo-cart`, `tp-navigation-menu`, `tp-before-after`...). The `tpaep-*` / `tpae-*` names in this skill are ability ids; when a task needs the Elementor `widget_type` (e.g. `build-page`), use the `tp-*` name. A `premium-addon-*` widget belongs to a different plugin (Premium Addons for Elementor) - never present or build it as a TPAE widget.
- **Opt-in.** A widget missing from `list-widgets` is disabled in **The Plus Addons -> Widgets**, not missing - say so and offer to enable it; never silently substitute another plugin's widget.
- **Colors that won't stick.** TPAE style controls use a gradient/global-preset system, and the theme kit's global styles can override raw per-widget color settings (a common tell: text or button renders white/black or the kit accent instead of your value). Recolor reliably via the widget's own control, its dedicated ability, or Elementor `selector`-scoped `!important` custom CSS - and verify by the element's rendered/computed style, not the stored setting.
