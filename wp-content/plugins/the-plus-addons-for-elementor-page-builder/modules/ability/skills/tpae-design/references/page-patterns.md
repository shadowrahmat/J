# Page patterns - section recipes

Composition recipes: which TPAE widgets build which section, in what order. All **design** judgment - layout limits, color, type, spacing, motion - comes from the `design-guide`; these recipes never override it. Build the hero first as the quality bar, judge the rendered result, then continue. `(Pro)` marks a `tpaep/` widget - give the free path first on free sites. **->skill** means that domain has a dedicated skill (load it for depth).

## Landing page (default arc)

Hero -> value/features -> proof -> offer -> action. One message per section; a layout family appears once per page. Use `build-page` for the whole arc from a clear brief, or granular `add-container` + widgets for control.

**Hero** - container + Heading (core or `tpae-heading-animation` for one emphasized element) + short subtext (`tpae-text-block`) + core Button. Optional: `tpae-video-player` background; `tpaep-cascading-image` / `tpaep-image-factory` (Pro, ->tpae-motion) for a composed visual. Guide limits: fits first viewport, ≤2-line headline, ~4 text elements, primary action visible.

**Features / services** - vary the family: uneven split (large feature + stacked smaller), `tpaep-animated-service-boxes` (Pro), or `tpaep-info-box` (Pro) / `tpae-messagebox` (free) - not identical icon boxes. `tpaep-flip-box` (Pro, ->tpae-motion) when a reveal is motivated.

**Social proof** - `tpaep-testimonial-listout` (Pro) or a free container of `tpae-blockquote` cards; `tpaep-number-counter` (Pro) / `tpae-progress-bar` (free) for numbers (real numbers only); `tpaep-team-member-listout` (Pro) where people are the proof; `tpaep-social-reviews` (Pro) for Google/Facebook reviews.

**Pricing** - `tpaep-pricing-table` (Pro) per plan in one container, or `tpaep-pricing-list` (Pro); declare mobile collapse; badge the recommended plan via the widget's own options; `tpaep-switcher` (Pro) for a monthly/yearly toggle. Free path: styled core widgets in a container.

**Conversion close** - container with Heading + Button, or `tpaep-advertisement-banner` (Pro); `tpaep-countdown` (Pro) only for a real deadline.

## Named recipes

**Header / footer / archive templates** -> **tpae-theme-builder** (`create-theme-template` + `set-template-conditions`, mega/mobile menu, site logo, breadcrumbs).

**Popup** - `create-popup` -> build content (Heading + form/CTA) -> `set-popup-settings` (trigger: exit-intent / delay / scroll; targeting; frequency). Confirm triggers with the user; stays unpublished until approved. `tpaep-off-canvas` (Pro, ->tpae-motion) for a slide-in panel instead of a modal.

**Blog / archive** - `tpaep-blog-listout` (Pro, grid/list/carousel) or `tpaep-dynamic-listing` (Pro) bound with `set-dynamic-tag`; `tpaep-search-filter` (Pro) for faceted filtering; free path: core Posts widget.

**Portfolio / gallery** - `tpaep-gallery-listout` (Pro) with filters; `tpaep-hotspot` (Pro, ->tpae-motion) for annotated images; `tpaep-before-after` (Pro, ->tpae-motion) for comparisons.

**WooCommerce sections** -> **tpae-woocommerce** (needs WooCommerce active).

**Coming-soon / 404** - `tpaep-countdown` (Pro) + `tpae-heading-animation` (free) + a form; `tpaep-pre-loader` (Pro) for the launch feel. 404: Heading + short copy + Button home + `tpaep-draw-svg` (Pro, ->tpae-motion) accent.

**Contact section** - a form widget (CF7/Gravity/Ninja/WPForms/Everest, each needs its plugin) + `tpaep-google-map` (Pro). Confirm which form plugin is active first.

## Composition rules

1. Sections are sibling containers, top-level; never nest a full section inside another.
2. Every multi-column container declares its mobile collapse.
3. Existing pages: new sections insert at a stated, zero-based position among top-level elements - confirm placement in the Plan.
4. Reuse before rebuild: the site's saved templates (`list-templates` -> `apply-template`), then a scratch build.
5. Customize what you built; never rebuild a "fix this" request (SKILL.md Editing discipline).
