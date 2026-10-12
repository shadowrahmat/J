# Rahmat — editable Elementor homepage

Template: `rahmat-elementor.json`

Page: http://localhost/juhani/rahmat/

Edit directly: http://localhost/juhani/wp-admin/post.php?post=1261&action=elementor

The page matches the latest homepage preview body using native Elementor containers, headings, images, buttons, text, and a nested accordion. Only the collection search uses a small HTML widget. The site's existing header and footer are preserved.

The seven body sections are Hero, Shopping Benefits, Guidance, Product Collection, Our Story, FAQ, and Contact. The removed **FIND YOUR FIT** category section is omitted. The banner and content sections share the site's Elementor container width (currently 1350px), with 20px side gutters on desktop/mobile and 24px on tablet. Tablet/mobile breakpoints follow Elementor at 1024px/767px. Changing the site's container width also updates this body layout.

Responsive styling is in Page Settings → Advanced → Custom CSS, provided by the installed Pro Elements plugin. It is scoped to the Rahmat body. `rahmat-body.css` is a reference copy; editing that file alone does not update WordPress.

Layout panels, cards, images, FAQ panels, and buttons start with 12px corners stored in native Elementor settings. Change a container's **Style → Border → Border Radius**, an image's **Style → Border Radius**, a button's **Style → Border Radius**, or the Accordion's **Style → Border Radius**. For small badge/arrow widget wrappers, use **Advanced → Border → Border Radius**. Existing custom radius values are preserved.

`rahmat-radius.css` now contains a binding to the Accordion's native radius and a low-specificity default for the HTML collection filter. It does not lock native widgets. Site-wide forced radius declarations in the custom theme/plugins and Elementor Custom CSS were removed; their prior versions are backed up in `.radius-fix-backup-20261012`.

## Import the JSON later

1. In WordPress, open Templates → Saved Templates → Import Templates.
2. Select `rahmat-elementor.json` and import it.
3. Edit the target page with Elementor, click the template folder icon, open My Templates, and insert the imported Rahmat template. Apply its page settings when prompted.

Use the Elementor Full Width page layout so the site's existing header and footer appear. The JSON contains the page body and its styling. It uses the current site's image and product URLs.

## Editing

- Use Navigator to select a section, product card, heading, image, or button.
- Edit product names in the Heading widgets and prices in the Text Editor widgets.
- Update both image and button links when replacing a product.
- The product cards are editable snapshots of the current catalog. Catalog price or image changes must also be updated in these cards.
- Keep the existing CSS classes on containers and widgets so the responsive layout and collection search keep working.
- Use the native Accordion widget to edit question titles, and its nested Text Editor widgets to edit the answers.
- Layout and typography rules in Page Settings → Custom CSS take precedence over some widget style controls. Border radius uses native controls and is independently editable.

The prior Rahmat page is backed up in `.rahmat-before-preview-match.json` at the project root. `build-rahmat.mjs` rebuilds the import template from the local preview; `php preview-assets/apply-rahmat.php apply` applies it to page 1261 and refreshes only that page's Elementor CSS.

The original preview remains available at http://localhost/juhani/home-preview.php.
