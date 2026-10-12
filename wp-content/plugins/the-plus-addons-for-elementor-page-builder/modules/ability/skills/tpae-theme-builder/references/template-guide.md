# Template guide - types, conditions, and what goes in each

How to build each theme-template type with TPAE, and how to scope it. Design taste and workflow come from tpae-design; WooCommerce templates come from tpae-woocommerce; these are the theme-layer recipes. Create with `create-theme-template`, scope with `set-template-conditions`, preview against a real target, get approval before publishing (it goes live across every matching page).

## Header

- Type: **header**. Condition: usually **entire site** (or exclude the landing page if it wants a bespoke header).
- Compose: a container row with **Site Logo** (`tpaep-site-logo`, Pro) or a static logo image · the menu (**Navigation Menu** mega `tpaep-navigation-menu` Pro, or **Navigation Menu Lite** `tpae-navigation-menu-lite` free) · **Header Extras** (`tpaep-header-extras`, Pro: search/cart/CTA) · the mobile trigger (**Mobile Menu** `tpaep-mobile-menu`, Pro).
- Declare desktop vs mobile explicitly; set sticky/scroll behavior if wanted and confirm no content overlap.

## Footer

- Type: **footer**. Condition: **entire site**.
- Compose: link columns, a small nav, social (`tpaep-social-icon`), newsletter (a form widget / `tpaep-mailchimp`), copyright. Keep it lighter than the header; stack cleanly on mobile.

## Single (post / page / CPT)

- Type: **single**, targeted to the post type via conditions. Preview against a **real post**.
- Compose with dynamic widgets only: **Post Featured Image** · **Post Title** · **Post Meta** (author/date/category) · **Post Content** · **Post Author** box · **Post Comment** · **Post Navigation** (`tpae/tpae-post-*`, free). Optional **Table of Content** (`tpaep-table-content`, Pro) for long posts; a related **Blog Listout** (`tpaep-blog-listout`, Pro) row.
- Every element bound; nothing typed for one specific post.

## Archive (blog / category / tag / CPT archive)

- Type: **archive**, conditioned to the archive(s) it serves.
- Compose: archive title/description (dynamic) · the loop via **Dynamic Listing** (`tpaep-dynamic-listing`, Pro) or **Blog Listout** (`tpaep-blog-listout`, Pro) · optional **Search Filter** (`tpaep-search-filter`, Pro) for faceted narrowing · pagination/load-more.
- Bind the query to the archive context; declare mobile columns.

## 404

- Type: **404**. Compose: a clear heading, short copy, a Button back home, site search (**Search Bar** `tpaep-search-bar`, Pro), maybe a `tpaep-draw-svg` (->tpae-motion) accent. No dynamic post data.

## Search results

- Type: **search-results**. Compose: the results loop (**Dynamic Listing** / **Blog Listout**) + a prominent **Search Bar** + an empty-state message for no results.

## Condition hygiene

1. State the intended scope in the Plan and confirm before publishing.
2. Two templates of the same type with overlapping conditions cause the wrong one to render - check for an existing template on that scope first.
3. Header/footer default to site-wide; singles target a post type; archives target their archive.
4. Preview the exact target (a real post for a single) so Verify is meaningful.
