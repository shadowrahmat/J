# Woodend page — native Elementor implementation

Page: http://localhost/macedonrang/woodend-firewood/

Editor: http://localhost/macedonrang/wp-admin/post.php?post=19&action=elementor

The approved preview is saved on existing WordPress page 19, using six independent top-level containers:

1. **01 Woodend page title** — breadcrumb and title widgets. Top padding clears the existing floating header.
2. **02 Split and seasoned Redgum supply** — original photo, heading and three separate Text Editor widgets.
3. **03 Specialist Redgum wood service** — heading, delivery photo, loading/refund policy and service paragraphs.
4. **04 Why choose us** — heading and original text.
5. **05 Eco-friendly heating** — heading, two-column information and original phone contact paragraph.
6. **06 Mixed hardwood comparison** — original comparison text.

Select each section or widget by its saved descriptive name in Elementor. Headings and Text Editor widgets contain editable copy; Image widgets use existing Media Library attachments. Fonts, colours, spacing, image heights, borders and responsive layouts use native Elementor controls. No custom CSS or HTML widgets were added. Product cards, gallery, delivery-area section and map are omitted.

Existing global header/footer templates and Romsey page data were preserved and their Elementor data hashes checked after saving. All 16 retained source heading/paragraph blocks and the retained phone link match the original content. Existing wording and typos remain unchanged.

## Active styling and export

Active styling is saved in the Woodend page's Elementor settings. Elementor generates output CSS using its own API; do not manually edit that generated output.

Manually assigned container/widget classes start with `mrfw-woodend-native-`. No authored custom CSS targets them.

`index.html`, `style.css` and `assets/fonts.css` remain the separate standalone preview; they are not the active WordPress styling sources.

Editable native template: [woodend-elementor.json](woodend-elementor.json).

Pre-change backup location: recorded in [applied-state.json](applied-state.json), under `.backup/woodend-native/`. The backup preserves the original post and metadata.

## Verification

[native-verification.json](native-verification.json) records saved-template equality, six top-level containers, native widget types, source-content/link matching, two working images, and zero map/gallery/HTML widgets. Page-body sections fit at 1440, 1024, 768, 390 and 320 pixels. Desktop/mobile screenshots were inspected, including title clearance below the existing header. At 320 pixels the surrounding global site layout reports 327 pixels overall width; the Woodend body itself fits its 305-pixel available content width. Global templates were outside this change.

Native widget structure and frontend rendering were verified; browser interactions inside the Elementor editor were not exercised.
