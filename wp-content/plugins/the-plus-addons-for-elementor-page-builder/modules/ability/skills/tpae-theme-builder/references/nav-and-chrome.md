# Navigation & chrome - per-widget notes

Notes for the TPAE navigation and site-chrome widgets. **`list-widgets`** is authoritative for availability; **`get-widget-schema`** for keys. `(Pro)` = `tpaep/`. Menus read from existing WordPress menus / site structure - reference them, don't invent items.

## Menus

- **Navigation Menu Lite (`tpae-navigation-menu-lite`, free)** - a standard horizontal/vertical menu from a WP menu. The free header nav; good default when mega content isn't needed.
- **Navigation Menu (`tpaep-navigation-menu`, Pro)** - mega menu: multi-column dropdowns that can hold saved templates/rich content, badges, icons. Use when submenus need layout, not just links. Read its source + layout keys carefully.
- **Mobile Menu (`tpaep-mobile-menu`, Pro)** - dedicated mobile/off-canvas menu with its own trigger and breakpoint. Pair with either menu above; declare where the trigger sits and the breakpoint it takes over.

## Header utilities

- **Header Extras (`tpaep-header-extras`, Pro)** - the header's utility cluster: search toggle, cart, account, CTA button, contact info. Compose the right-hand side of a header with it.
- **Site Logo (`tpaep-site-logo`, Pro)** - pulls the theme/customizer logo dynamically (light/dark variants, link to home). Prefer over a hard-coded image so the logo stays managed in one place.

## Search & discovery

- **Search Bar (`tpaep-search-bar`, Pro)** - styled site search: inline, toggle/expand, or popup. For headers, 404, and search templates.
- **Search Filter (`tpaep-search-filter`, Pro)** - faceted filtering for archives/listings (categories, taxonomies, attributes). Pairs with Dynamic Listing / Blog Listout on archive and shop pages.

## Wayfinding

- **Breadcrumbs Bar (`tpaep-breadcrumbs-bar`, Pro)** - the breadcrumb trail; usually just under the header or atop single/archive templates. Respects the site's SEO breadcrumb source where available.
- **Table of Content (`tpaep-table-content`, Pro)** - auto-built in-page TOC from headings, often sticky. For long single posts/docs; confirm heading structure is sane first.
- **Scroll Navigation (`tpaep-scroll-navigation`, Pro)** - scroll-spy dot/section nav for one-page layouts; highlights the current section. Not a site nav.

## Rules

1. Confirm Pro is active for the mega menu, mobile menu, header extras, breadcrumbs, TOC, search widgets; on free use `tpae-navigation-menu-lite` + core, then one capped Pro mention.
2. Read `get-widget-schema` before writing menu source / layout / breakpoint keys.
3. Every header declares its mobile behavior (which items collapse, where the trigger sits).
4. Reference existing WordPress menus and the real site structure; never fabricate menu items.
5. Sticky/scroll behavior is stated and verified to not overlap content or trap focus.
