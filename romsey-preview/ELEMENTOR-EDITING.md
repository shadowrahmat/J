# Romsey page — native Elementor implementation

Page: http://localhost/macedonrang/romsey-firewood/

Editor: http://localhost/macedonrang/wp-admin/post.php?post=18&action=elementor

The approved cream design is saved on existing WordPress page 18. It uses nine independent top-level containers:

1. **01 Banner** — native headings, introduction, image and price/delivery terms.
2. **02 Firewood products** — three separate product containers with image, name, price and icon widgets.
3. **03 Redgum supply heading** — heading and small divider.
4. **04 Redgum key points** — image, heading and native Icon List.
5. **05 High BTU and delivery** — heading, three Text Editor widgets and image.
6. **06 Why Redgum off-cuts** — heading and original description.
7. **07 Contact and supply** — heading and contact paragraph.
8. **08 Hardwood comparison** — original comparison paragraph.
9. **09 Delivery areas** — heading and 33 native Button widgets in a responsive grid.

Select each saved section or widget by its descriptive name in Elementor. Change text through its native content controls, replace photos through the Image widget's media picker, and change font/colour/spacing using native styling and layout controls. Product links are set on each **product card container**; each card is a single clickable container. Delivery links are set on the corresponding Button widget. The email paragraph includes `<wbr>` break opportunities so the original address wraps on small screens without changing the displayed address or mail link.

All fonts, colours, image heights, gaps, padding and responsive column settings are saved in Elementor data. DM Serif Display and DM Sans use Elementor's existing font support. No new custom CSS, HTML widgets, map, gallery or JavaScript were added to the WordPress page. Existing global header/footer templates were preserved.

## Styling sources

The active design is stored in page 18's Elementor data and native page settings. Elementor generates its own output stylesheet through its API. Do not manually edit that generated file.

Stable, manually assigned component classes start with `mrfw-romsey-native-`; they are saved in the container/widget CSS Classes settings. No authored CSS targets these classes at present.

`style.css`, `assets/fonts.css`, and `index.html` belong to the separate standalone preview. They are **reference preview files**, not active WordPress styling sources. Do not paste the preview stylesheet into a global CSS field.

## Export, backup and verification

- Editable native template export: [romsey-elementor.json](romsey-elementor.json).
- Pre-change backup: the exact path is recorded in [applied-state.json](applied-state.json), under `.backup/romsey-native/`. It preserves the original post and metadata.
- [native-verification.json](native-verification.json) records the saved template comparison, nine top-level sections, original text/link matches, 33 area links and six working images.
- Body sections passed overflow checks at 1440, 1024, 768, 390 and 320 pixels. Desktop/mobile screenshots were inspected. At 320 pixels the surrounding site layout still reports 327 pixels overall width; the Romsey page body itself fits its 305-pixel available content width. Global templates were outside this change.
- Elementor editing interactions were not exercised in the browser; the saved native widget structure and frontend rendering were verified.
