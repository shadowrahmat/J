# Blog Rahmat

Page: http://localhost/juhani/blog-rahmat/

Elementor: http://localhost/juhani/wp-admin/post.php?post=1274&action=elementor

The journal banner, featured guide, article cards, photographs and contact section use native Elementor containers, Heading, Text Editor, Image and Button widgets. Use Navigator to select the named sections.

- Change text in the Heading or Text Editor widget.
- Replace photographs in Image widgets and update article links in the linked container, heading and read button.
- Change a section's radius under Style → Border → Border Radius. The default is 12px; page CSS does not force native radius values.
- Shared content width is 1350px, with responsive gutters and stacked mobile layouts.

The three small HTML widgets contain the search/topic controls, no-results message and script loader. The page is a curated snapshot of the three current articles. When duplicating an article card, update its Advanced → Attributes `data-topic` and `data-search` values so filtering finds the new text; also update the displayed counts and topic controls.

The existing header and footer remain assigned by WordPress. Page-specific layout rules are under Page Settings → Advanced → Custom CSS.

Importable Elementor template: `preview-assets/blog-rahmat-elementor.json`.

Original page backup: `.blog-rahmat-before-design.json`.
