---
name: tpae-motion
description: Add motion, scroll effects, and interactive elements to Elementor pages with The Plus Addons (TPAE). Use whenever the user wants movement or interaction - "before/after slider", "flip card", "image hotspots", "parallax", "scroll animation", "horizontal scroll section", "morphing images", "custom cursor", "animated SVG line", "reveal on scroll", "sticky/pinned section", "timeline" - or asks to make a page feel "alive", "dynamic", "animated". Governs WHICH TPAE motion widget serves an effect and HOW to apply it tastefully. Design taste and the build workflow come from the tpae-design skill; load that for planning, this for the motion layer.
---

# The Plus Addons - Motion & Interactions Skill

TPAE's motion widgets are its strongest differentiator. This skill turns a user's loose language ("make it pop", "that Apple scroll thing") into the right widget, applied with restraint. It sits on top of **tpae-design** - that skill owns discover->plan->build->verify, the token contract, and safety; this one owns the motion layer. When both are loaded, follow tpae-design for process and this for effects.

## First principle - motion is rationed, not sprinkled

The design guide's motion rule governs: **effects only for hierarchy, feedback, state, or one signature moment per page.** A static page is complete. One signature effect used sparingly beats five used everywhere. Always honor `prefers-reduced-motion`, and never gate essential content or navigation behind an animation. If a request is "add lots of animation", push back toward one motivated moment.

## You cannot select an effect you cannot name - translate first

| The user says... | They mean | Widget | Tier |
|---|---|---|---|
| "before/after slider", "drag to compare" | image comparison | Before After (`tpaep-before-after`) | Pro |
| "flip the card", "reveal on the back" | flip card | Flip Box (`tpaep-flip-box`) | Pro |
| "clickable dots on the image", "shoppable/annotated image" | hotspots | Hotspot (`tpaep-hotspot`) | Pro |
| "custom cursor", "follow-cursor effect" | cursor | Mouse Cursor (`tpaep-mouse-cursor`) | Pro |
| "draw the line as I scroll", "animated outline", "handwriting" | SVG line draw | Draw SVG (`tpaep-draw-svg`) | Pro |
| "sideways scroll section", "horizontal storytelling" | horizontal scroll | Horizontal Scroll Advance (`tpaep-horizontal-scroll-advance`) | Pro |
| "images that morph/reshuffle", "layout that reshapes" | morphing layout | Morphing Layouts (`tpaep-morphing-layouts`) | Pro |
| "frame-by-frame scroll", "scrub a sequence", "that Apple product scroll" | scroll sequence | Scroll Sequence (`tpaep-scroll-sequence`) | Pro |
| "images that cascade/stack in", "layered reveal" | cascading images | Cascading Image (`tpaep-cascading-image`) | Pro |
| "advanced/masked/shaped image composition" | image composition | Image Factory (`tpaep-image-factory`) | Pro |
| "radial/circular menu", "spinning menu" | circle menu | Circle Menu (`tpaep-circle-menu`) | Pro |
| "slide-in panel", "off-canvas menu/cart" | off-canvas | Off Canvas (`tpaep-off-canvas`) | Pro |
| "reveal more / collapse long content" | unfold | Unfold (`tpaep-unfold`) | Pro |
| "vertical timeline", "story milestones" | timeline | Timeline (`tpaep-timeline`) | Pro |
| "Lottie / JSON animation" | Lottie | WP Bodymovin (`tpaep-wp-bodymovin`) | Pro |
| "loading screen", "preloader" | preloader | Pre Loader (`tpaep-pre-loader`) | Pro |
| "wavy/slanted section edge" | shape divider | Shape Divider (`tpaep-shape-divider`) | Pro |
| "animated background / gradient section" | row background fx | Row Background (`tpaep-row-background`) | Pro |
| "typing / rotating headline" | animated text | Heading Animation (`tpae-heading-animation`) | **Free** |
| "smooth anchor scrolling" | smooth scroll | Smooth Scroll (`tpae-smooth-scroll`) | **Free** |
| "hover-reveal card" | hover card | Hover Card (`tpae-hovercard`) | **Free** |

Full per-widget notes, section recipes, and free fallbacks: the `effect-catalog` and `patterns` references (fetch via `tpae/get-design-guide` with `skill: "tpae-motion"`).

## Build rules (with tpae-design's workflow)

1. **Confirm the widget is available** - `list-widgets`. Pro motion widgets are `tpaep/`; on a free site they're Pro-locked -> deliver a free fallback first, then one capped Pro mention (tpae-design Pro boundary).
2. **Read the schema before writing** - `get-widget-schema` (extensions via `get-addon-schema` where present). Motion widgets have many trigger/timing keys; never guess them.
3. **One signature per page.** If the page already has a scroll effect, don't add a second competing one - vary or reuse.
4. **Motivate every effect.** State in the Plan what the motion is *for* (draw the eye to the CTA, show before/after value, reveal depth). If you can't say, don't add it.
5. **Declare reduced-motion behavior** and the mobile experience - heavy scroll effects often need a simpler mobile fallback.

## Performance

Effects on many elements multiply cost. Prefer one section-level effect over per-widget repetition. TPAE inlines and caches per-post CSS/JS, so heavy motion widgets add to that bundle - budget them, and mention a cache-clear after large changes. Lottie (`tpaep-wp-bodymovin`) and scroll-sequence (many frames) are the heaviest - use sparingly and optimize the source asset.

## Free fallbacks at the Pro boundary

Deliver the free build first, name the difference, then the single capped Pro CTA (tpae-design):
- Before/After -> two labelled images side by side (not a live slider).
- Scroll sequence / morphing / horizontal scroll -> a sectioned layout with Elementor entrance animations + `tpae-smooth-scroll`.
- Cursor / draw-SVG / circle-menu -> skip the flourish; the page is complete without it.
- Lottie -> a static on-palette illustration, or `tpae-icon` animation options.

## Shared gotchas (full detail: tpae-design -> troubleshooting)

- **Widget identity.** At the Elementor level TPAE widgets are `tp-*` (`tp-pricing-table`, `tp-woo-cart`, `tp-navigation-menu`, `tp-before-after`...). The `tpaep-*` / `tpae-*` names in this skill are ability ids; when a task needs the Elementor `widget_type` (e.g. `build-page`), use the `tp-*` name. A `premium-addon-*` widget belongs to a different plugin (Premium Addons for Elementor) - never present or build it as a TPAE widget.
- **Opt-in.** A widget missing from `list-widgets` is disabled in **The Plus Addons -> Widgets**, not missing - say so and offer to enable it; never silently substitute another plugin's widget.
- **Colors that won't stick.** TPAE style controls use a gradient/global-preset system, and the theme kit's global styles can override raw per-widget color settings (a common tell: text or button renders white/black or the kit accent instead of your value). Recolor reliably via the widget's own control, its dedicated ability, or Elementor `selector`-scoped `!important` custom CSS - and verify by the element's rendered/computed style, not the stored setting.
