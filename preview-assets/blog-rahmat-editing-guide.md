# Blog Rahmat

Page: http://localhost/juhani/blog/

Elementor: http://localhost/juhani/wp-admin/post.php?post=1274&action=elementor

The page now has four separate top-level containers in Navigator / Structure:

1. **01 - Journal Banner**
2. **02 - Featured Guide**
3. **03 - Article Library**
4. **04 - Contact and WhatsApp**

There is no single parent container enclosing these sections. Expand Article Library → Library - Content Width → Articles - Individual Cards to find **Article 1**, **Article 2** and **Article 3**. Text, photographs and links remain separate native widgets. The restructuring keeps the current saved edits and widget IDs.

- Change text in the Heading or Text Editor widget.
- Replace photographs in Image widgets and update article links in the linked container, heading and read button.
- Change a section's radius under Style → Border → Border Radius. The default is 12px; page CSS does not force native radius values.
- Shared content width is 1350px, with responsive gutters and stacked mobile layouts.

The search/topic controls use a small HTML widget. Search JavaScript loads automatically for page 1274, without a hidden script widget, and also works if the optional no-results widget has been removed. The page is a curated snapshot of the three current articles. When duplicating an article card, update its Advanced → Attributes `data-topic` and `data-search` values so filtering finds the new text; also update the displayed counts and topic controls.

The existing header and footer remain assigned by WordPress.

## CSS কোথায় পাবেন

**Elementor → Page Settings → Advanced → Custom CSS**-এ page-এর active styling আছে। Container-এর **Advanced → CSS Classes** field-এ শুধু selector-এর নাম থাকে; CSS code সেখানে থাকে না। Custom CSS-এ `jb-hero`, `jb-feature`, `jb-article` বা `jb-help` search করলে সংশ্লিষ্ট styling পাবেন।

একই CSS-এর file copy: `D:\xampp\htdocs\Juhani\preview-assets\blog-rahmat.css`। এটি reference copy; শুধু এই file edit করলে live page বদলাবে না। Live design বদলাতে Elementor-এর Page Settings-এর Custom CSS edit করে Update / Publish করুন।

WordPress generates the served CSS in `wp-content/uploads/elementor/css/post-1274.css`; do not manually edit that generated file, because Elementor rebuilds it.

Importable Elementor template: `preview-assets/blog-rahmat-elementor.json`.

Original page backup: `.blog-rahmat-before-design.json`.

Backup before removing the outer parent: `.blog-rahmat-before-separate-containers.json`.
