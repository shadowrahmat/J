# Woodend Firewood preview

Preview: http://localhost/Juhani/woodend-preview/

Original page: https://macedonrangesfirewoodyard.com.au/woodend-firewood/

The approved preview is now implemented on the existing [Woodend WordPress page](http://localhost/macedonrang/woodend-firewood/) with native Elementor widgets. See [ELEMENTOR-EDITING.md](ELEMENTOR-EDITING.md) for the editor link, export, backup and active styling details. The `index.html` and styles in this directory remain the separate standalone preview.

The first three-product section (Blue Gum, Redgum Firewood, Sugar Gum), gallery, delivery-area section and map are omitted. All other visible body content is retained, including the minimum order/delivery terms, seasoned Redgum information, specialist service information, loading/refund policy, Why Choose Us, eco-friendly information, phone contact and mixed-hardwood comparison. The two body photos come from the original page. Header/footer provide existing brand context; their delivery-area navigation links are ordinary links, not a delivery-area section.

The presentation uses the previously selected light cream background, DM Serif Display headings, DM Sans body text, separate image–text sections and square corners. No new marketing copy or claims were added. Original wording is preserved, including its punctuation and typos; whitespace is normalized. The specialist policy paragraph appears as the caption below its delivery photograph.

## Content and layout verification

`source.html` is the downloaded original; `content.json` records the retained text and links. All **16 retained heading/paragraph blocks** match the source, with zero missing blocks, zero added blocks and no missing retained links. The original commented-out table is not rendered; the visible hardwood comparison sentence remains.

`verification.json` records checks at 1440, 1024, 768, 390 and 320 pixels: no horizontal overflow, no broken images, and no product section, gallery, delivery-area section or iframe. Desktop and mobile screenshots were visually inspected.

## CSS implementation handoff

- **CSS Class:** `woodend-preview` (root of every authored selector).
- **Class Location:** `<body class="woodend-preview">` in the standalone `index.html`.
- **CSS Code:** the complete authored stylesheet is [style.css](style.css).
- **Implementation:** `index.html` loads `style.css`; `assets/fonts.css` loads the local fonts. These are active styling sources for the standalone preview only. No Elementor container/widget received a class, and no WordPress-generated CSS file was edited.
- **Elementor UI:** not inspected for this preview-only task. If this design is later applied to WordPress, prefer native Elementor widgets and controls, retaining separate top-level sections.
