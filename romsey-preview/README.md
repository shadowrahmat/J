# Romsey Firewood design preview

Open: http://localhost/Juhani/romsey-preview/

The approved design has now been implemented on the existing [Romsey WordPress page](http://localhost/macedonrang/romsey-firewood/) with native Elementor widgets. See [ELEMENTOR-EDITING.md](ELEMENTOR-EDITING.md) for the active implementation, editor link, export and backup details. The files described below remain the separate standalone preview.

Current design follows the selected direction: light cream backgrounds, elegant serif headings, and image–text sections. The split banner pairs its title and original introduction with the original Redgum photo and delivery/price terms. Three product cards use warm paper backgrounds. The key points have an inset image–text panel; BTU content is paired with the original delivery photograph. All original wording is unchanged. Earlier previews are retained in `backup-v1/` through `backup-v4/`.

Source: https://macedonrangesfirewoodyard.com.au/romsey-firewood/

This is a standalone local preview. No WordPress pages, Elementor data, global styles, header or footer templates were changed. Main content and original links are retained, including all three products and 33 delivery-area links. Map, gallery, and the commented-out comparison table are omitted. No new marketing copy was added. Whitespace is normalized.

Nine separate main sections: banner with delivery terms, products, supply heading, key points, BTU information, off-cuts information, contact copy, hardwood comparison, and delivery areas. These can be implemented later as separate Elementor containers with native widgets.

## CSS implementation handoff

- **CSS Class:** `romsey-preview` (all authored selectors use this root).
- **Class Location:** the `<body>` element of this standalone preview, `index.html`. No Elementor widget or container received a class during this preview-only task.
- **CSS Code:** the complete authored CSS is in [style.css](style.css).
- **Implementation:** `index.html` loads `style.css` directly. This is the active styling source for the standalone preview only. `assets/fonts.css` loads local DM Serif Display and DM Sans font files. Neither stylesheet is WordPress or Elementor styling or a generated Elementor CSS output file.
- **Elementor UI:** no Elementor implementation was requested in this turn; the installed CSS editor location was not inspected. Prefer native widget/container controls when applying the approved design rather than pasting preview CSS globally.

## Verification

`content.json` records original wording and links from the downloaded source. `verification.json` records matching body text/links, three products, 33 area links, zero gallery or iframe elements, zero broken images, and no horizontal overflow at 1440, 1024, 768, 390 and 320 pixels. Desktop and mobile screenshots were inspected.

The only images used are the original site's images. Header/footer are presentation context using existing source labels and information; the map is omitted.
