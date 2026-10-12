# Motion patterns - where each effect earns its place

Motion section recipes: the common "I want it to feel alive" requests and the motivated way to build them. Design taste and the build workflow come from tpae-design; these are the motion-layer compositions. `(Pro)` = `tpaep/`; give the free path first on free sites.

## Signature hero moment (pick ONE)

- **Product/portfolio** -> `tpaep-scroll-sequence` (Pro) scrubbing a short frame set, OR `tpaep-cascading-image` (Pro) revealing a composed visual. Free: entrance-animated image + `tpae-heading-animation`.
- **Editorial/brand** -> `tpaep-draw-svg` (Pro) drawing a signature line/underline as the hero enters. Free: a static on-palette accent.
- **Rule:** the hero gets at most one motion moment, and it must not delay the headline/CTA being readable.

## "Show the value" section

- Transformation/results -> `tpaep-before-after` (Pro) with two genuine comparison images.
- Annotated product/feature -> `tpaep-hotspot` (Pro) markers on a product shot.
- Feature reveal -> `tpaep-flip-box` (Pro) only when the back genuinely adds detail; otherwise a static card.

## Storytelling / long-scroll

- Sideways story strip -> `tpaep-horizontal-scroll-advance` (Pro); confirm vertical fallback on mobile.
- Milestones/roadmap/history -> `tpaep-timeline` (Pro), real dates only.
- Reshaping showcase -> `tpaep-morphing-layouts` (Pro) as the one signature; never for must-read content.
- Free fallback for all three: sectioned layout with Elementor entrance animations + `tpae-smooth-scroll`; state the difference, one capped Pro CTA.

## Navigation & panels

- Slide-in menu/cart/filters -> `tpaep-off-canvas` (Pro); declare trigger + close.
- Compact floating actions -> `tpaep-circle-menu` (Pro); not a primary nav.
- Anchored one-page nav -> `tpae-smooth-scroll` (free) + the section anchors.

## Ambient / finishing touches (use at most one)

- Section edges -> `tpaep-shape-divider` (Pro), one consistent style.
- Background motion -> `tpaep-row-background` (Pro), keep foreground contrast.
- Brand load moment -> `tpaep-pre-loader` (Pro), only when justified.
- Crafted micro-animation -> `tpaep-wp-bodymovin` (Pro, Lottie), optimized asset, one or two max.

## Composition rules

1. One signature scroll/motion effect per page. Ambient/hover/entrance touches may support it if motivated.
2. Every motion widget declares its mobile behavior and honors reduced-motion.
3. Read `get-widget-schema` before writing trigger/timing keys.
4. Mention a cache-clear after adding heavy motion (per-post CSS/JS bundle grows).
5. Never let motion hide or delay essential content, navigation, or the primary CTA.
