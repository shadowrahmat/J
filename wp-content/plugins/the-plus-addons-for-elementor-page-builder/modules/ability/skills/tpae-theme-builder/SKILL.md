---
name: tpae-theme-builder
description: Build WordPress/Elementor theme templates and site chrome with The Plus Addons (TPAE) - headers, footers, single-post and archive templates, 404 and search-results pages, mega menus, mobile menus, breadcrumbs, site logo, and table of contents. Use whenever the user wants a custom header/footer, a dynamic single or archive template, a mega/mobile menu, breadcrumbs, or to set where a template applies - "build me a header", "custom footer", "design my blog post template", "mega menu", "add breadcrumbs", "sticky header". Design taste and the build workflow come from the tpae-design skill; WooCommerce templates come from tpae-woocommerce; this skill owns theme templates, display conditions, and navigation chrome.
---

# The Plus Addons - Theme Builder Skill

Build the reusable, dynamic parts of a site: headers, footers, single/archive templates, and the navigation chrome. This skill sits on top of **tpae-design** (owns discover->plan->build->verify, the token contract, safety) and hands WooCommerce templates to **tpae-woocommerce**. This skill owns *which template type, what conditions, and which nav/chrome widget*.

## The two-step spine: create the template, then set where it applies

Theme templates are not pages - they are Elementor library templates with a **type** and **display conditions**.

1. **Create** with `create-theme-template` - choose the type: header · footer · single (post/page/CPT) · archive · 404 · search-results. (WooCommerce single/archive/cart/checkout templates -> **tpae-woocommerce**.)
2. **Condition** with `set-template-conditions` - where it renders: entire site, specific post types, a taxonomy/term, front page, etc. A header/footer usually applies site-wide; a single template targets a post type. Conflicting conditions between templates cause the wrong one to show - state the intended scope in the Plan and confirm.
3. **Compose** with the widgets below (+ dynamic binding for single/archive).
4. **Verify** against a real preview target (a real post for a single template) and confirm the condition actually resolves.

**Publishing a template makes it live across every page it matches** - treat it as a high-impact change: draft, preview, and get explicit approval before publishing (tpae-design non-negotiable 1).

## Dynamic content in single & archive templates

Single/archive templates render for many posts, so their content must be **bound, not typed**: use the post widgets - Post Title · Post Content · Post Meta · Post Featured Image · Post Author · Post Comment · Post Navigation (`tpae/tpae-post-*`, free) - and `set-dynamic-tag` from `list-dynamic-tags`. For post/portfolio loops and grids: `tpaep-dynamic-listing` / `tpaep-blog-listout` (Pro). Never hard-code a specific post's text into a template.

## Navigation & chrome widgets

| Intent | Widget | Tier |
|---|---|---|
| Simple nav menu | Navigation Menu Lite (`tpae-navigation-menu-lite`) | Free |
| Mega menu (multi-column, rich content) | Navigation Menu (`tpaep-navigation-menu`) | Pro |
| Mobile / off-canvas menu | Mobile Menu (`tpaep-mobile-menu`) | Pro |
| Header utilities (search/cart/CTA/account) | Header Extras (`tpaep-header-extras`) | Pro |
| Dynamic site logo (theme logo) | Site Logo (`tpaep-site-logo`) | Pro |
| Search box | Search Bar (`tpaep-search-bar`) | Pro |
| Faceted archive filtering | Search Filter (`tpaep-search-filter`) | Pro |
| Breadcrumb trail | Breadcrumbs Bar (`tpaep-breadcrumbs-bar`) | Pro |
| In-page table of contents | Table of Content (`tpaep-table-content`) | Pro |
| Scroll-spy section nav | Scroll Navigation (`tpaep-scroll-navigation`) | Pro |

Full per-widget notes: `nav-and-chrome`. Template recipes (header/footer/single/archive/404/search): `template-guide`. (Fetch via `tpae/get-design-guide` with `skill: "tpae-theme-builder"`.)

## Build rules (with tpae-design's workflow)

1. **Confirm the widget is available** (`list-widgets`) and whether Pro is active - the mega menu, mobile menu, header extras, breadcrumbs, etc. are `tpaep/`; on a free site use `tpae-navigation-menu-lite` + core, then one capped Pro mention.
2. **Read the schema before writing** - `get-widget-schema`; menus and header widgets have dense source/layout/breakpoint keys.
3. **Declare the mobile header** explicitly: which items collapse, where the mobile menu trigger sits (`tpaep-mobile-menu`). A desktop-only header is incomplete.
4. **Sticky/scroll behavior is a choice** - state it; confirm it doesn't overlap content or trap focus.
5. **Menus read from WordPress menus / the site structure** - reference existing menus; don't invent menu items the site doesn't have.

## Verify (theme-builder specifics)

- The template renders for its intended target (preview a real post for a single template) and **only** there - check the condition scope didn't over-match.
- Header: logo, menu, and mobile trigger all present; menu opens on mobile; sticky (if set) behaves.
- Single/archive: every dynamic element is bound (no placeholder/literal leaking) and resolves for the previewed post.
- Confirm no conflicting template already claims the same condition.

## Shared gotchas (full detail: tpae-design -> troubleshooting)

- **Widget identity.** At the Elementor level TPAE widgets are `tp-*` (`tp-pricing-table`, `tp-woo-cart`, `tp-navigation-menu`, `tp-before-after`...). The `tpaep-*` / `tpae-*` names in this skill are ability ids; when a task needs the Elementor `widget_type` (e.g. `build-page`), use the `tp-*` name. A `premium-addon-*` widget belongs to a different plugin (Premium Addons for Elementor) - never present or build it as a TPAE widget.
- **Opt-in.** A widget missing from `list-widgets` is disabled in **The Plus Addons -> Widgets**, not missing - say so and offer to enable it; never silently substitute another plugin's widget.
- **Colors that won't stick.** TPAE style controls use a gradient/global-preset system, and the theme kit's global styles can override raw per-widget color settings (a common tell: text or button renders white/black or the kit accent instead of your value). Recolor reliably via the widget's own control, its dedicated ability, or Elementor `selector`-scoped `!important` custom CSS - and verify by the element's rendered/computed style, not the stored setting.
