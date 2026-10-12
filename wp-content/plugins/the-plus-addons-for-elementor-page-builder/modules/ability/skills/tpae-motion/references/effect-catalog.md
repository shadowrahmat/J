# Motion effect catalog - per-widget notes

Detailed notes for each TPAE motion/interaction widget: what it's for, the shape of its key settings, and when to reach for it. **`list-widgets` is authoritative** for availability; `get-widget-schema` is authoritative for the exact keys. `(Pro)` = `tpaep/`.

## Comparison & reveal

- **Before After (`tpaep-before-after`)** - draggable image comparison. For "show the transformation" (retouch, renovation, product config). Two images, orientation (horizontal/vertical), starting handle position. One per section; needs two genuinely comparable images.
- **Flip Box (`tpaep-flip-box`)** - front/back card that flips on hover or click. For feature cards where the back holds detail/CTA. Keep both faces short; don't hide essential info only on the back (accessibility). Trigger: hover (desktop) vs tap (mobile) - declare it.
- **Unfold (`tpaep-unfold`)** - reveal/collapse long content. For dense sections (specs, long copy) where a "read more" reduces scroll fatigue.

## Image interaction

- **Hotspot (`tpaep-hotspot`)** - clickable markers on an image with tooltips/popovers. For annotated diagrams, shoppable images, maps. Each marker: position (x/y %), content, trigger (hover/click). Keep marker count low; ensure keyboard access.
- **Cascading Image (`tpaep-cascading-image`)** - images that stack/reveal in sequence on scroll. For a composed hero or storytelling visual.
- **Image Factory (`tpaep-image-factory`)** - advanced masked/shaped image compositions. For a designed, non-rectangular hero image. Heavier - one per page.
- **Draw SVG (`tpaep-draw-svg`)** - animates an SVG path being drawn, on scroll or load. For a signature line, underline, or illustration accent. Needs a clean single-path SVG; complex SVGs animate poorly.

## Scroll-driven

- **Scroll Sequence (`tpaep-scroll-sequence`)** - scrubs an image frame sequence as the user scrolls (the "Apple product" effect). Heavy: many frames = large payload. Optimize/limit frames; provide a static fallback and a simpler mobile path.
- **Horizontal Scroll Advance (`tpaep-horizontal-scroll-advance`)** - a section that scrolls sideways as the page scrolls vertically. For a gallery/timeline/story strip. Confirm it degrades to vertical stacking on mobile.
- **Morphing Layouts (`tpaep-morphing-layouts`)** - a grid/layout that reshapes between states on scroll or interaction. Signature moment only; never for core content that must always be legible.
- **Smooth Scroll (`tpae-smooth-scroll`, free)** - eases anchor-link scrolling. Safe, low-cost; pairs with any anchored nav.

## Structure & ambient motion

- **Timeline (`tpaep-timeline`)** - vertical/horizontal milestone timeline. For history, process, roadmap. Real dates/steps only.
- **Circle Menu (`tpaep-circle-menu`)** - radial expanding menu/action button. For a compact floating action set; not a primary nav.
- **Off Canvas (`tpaep-off-canvas`)** - slide-in panel (menu, cart, filters). For secondary content that shouldn't occupy the page. Confirm the trigger and the close affordance.
- **Row Background (`tpaep-row-background`)** - animated/effect backgrounds on a container. Ambient only; keep contrast for foreground text.
- **Shape Divider (`tpaep-shape-divider`)** - decorative/animated section edges. One style, used consistently; not on every section.
- **Pre Loader (`tpaep-pre-loader`)** - page loading screen. For launch/brand moments; adds perceived delay, so justify it.
- **WP Bodymovin (`tpaep-wp-bodymovin`)** - Lottie/JSON animations. For a crafted illustration/icon animation. Heaviest asset class - optimize the JSON, one or two per page.

## Free interaction (no Pro needed)

- **Heading Animation (`tpae-heading-animation`)** - typing/rotating/highlighted headline words. One emphasized element in the hero.
- **Hover Card (`tpae-hovercard`)** - hover-reveal content card. For feature/team cards with a light reveal.

## Selection rules

1. One signature scroll/motion effect per page; supporting hover/entrance effects may accompany it if motivated.
2. Read `get-widget-schema` before writing any trigger/timing keys.
3. On a free site, Pro widgets here get the free fallback first (SKILL.md), then one capped Pro mention.
4. Always declare mobile behavior and honor reduced-motion.
