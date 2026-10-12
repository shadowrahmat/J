# About us Rahmat — editable About preview

Page: http://localhost/juhani/about-us-rahmat/

Edit: http://localhost/juhani/wp-admin/post.php?post=1271&action=elementor

Reference preview: http://localhost/juhani/about-preview.php

The body is built from native Elementor containers, Heading, Text Editor, Image, and Button widgets. It has no HTML layout widget. The existing site header/footer, original About page, and homepage Rahmat page are preserved.

Use Navigator to select Hero, Our Story, Mission, Values, Impact, Reviews, Journal, or Contact. Edit the relevant text, photo, link, or button directly. The images in Hero and Impact are editable in the containers' Style → Background controls. The small section icons use Image widgets with local SVG files.

Border radius starts at 12px in native Elementor controls. For containers, use Style → Border → Border Radius; for buttons, use Style → Border Radius. Other spacing/typography rules live in Page Settings → Advanced → Custom CSS and match the reference preview.

Blog cards are editable copies of the current published articles. Update both their heading and image-container links when replacing an article. Reviews and statistics retain the source About page's content.

`about-rahmat-elementor.json` is the import template, and `about-rahmat.css` is a reference copy of the page CSS. The CSS is scoped to page 1271. Importing into another page requires updating that scope. The page uses the Elementor Full Width template so the site's header/footer appear.

The prior empty page data is backed up in `.about-us-rahmat-before-design.json` at the project root. `php preview-assets/build-about-rahmat.php apply` regenerates and applies the body from the current isolated preview; it replaces page 1271's body, so preserve later manual edits before rerunning it.
