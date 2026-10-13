# Live product search — approved preview design

**Live website:** http://localhost/juhani/

Header-এ `cast`, `25` বা `২৫` লিখলে নতুন dropdown design দেখা যাবে। Mobile-এর **Search** popup-ও একই product card template ব্যবহার করছে।

## Elementor দিয়ে edit

**Product card template:** http://localhost/juhani/wp-admin/post.php?post=1270&action=elementor

নাম: **JUHANI Live Product Search**। Structure/Navigator-এ আলাদা অংশ আছে:

- Search result — Linked product card
- 01 — Product image
- 02 — Product information
- 02a — Category and stock
- Product — Dynamic title
- Product — Dynamic price range
- Product — Haat and Suta
- 03 — Open product indicator

Image, category, stock, title, price, measurements ও icon আলাদা native Elementor widget। Width, padding, gap, colour, typography, hover ও 12px radius native Style/Advanced controls থেকে edit করুন। Card-এর entire row dynamic **Post URL**-এ linked।

Product name/photos/prices/stock/attributes বদলাবেন **WordPress → Products → Edit Product** থেকে। Category, price, stock ও Haat/Suta-এর Heading widgets-এর Dynamic Tags menu-তে **Juhani Product Search** group আছে। এসব fields current product data পড়ছে।

**Header Search widget:** http://localhost/juhani/wp-admin/post.php?post=1267&action=elementor

Widget name: **Header — Live product search**। Content/Style settings-এ result count limit, minimum characters, dropdown width, distance ও radius edit করা যায়। বর্তমানে 2 characters থেকে search শুরু হয়, সর্বোচ্চ 9 result দেখায়।

Mobile popup editor: http://localhost/juhani/wp-admin/post.php?post=1214&action=elementor

The other existing Search popup, template 1207, also uses the same result card. Existing device visibility rules remain in use.

## CSS Class / Class Location

সব class ইতিমধ্যে manually assigned। Elementor-এর **Advanced → CSS Classes**-এ পাবেন।

| CSS Class | Class Location |
| --- | --- |
| `juhani-live-product-search` | Header এবং popup-এর Search widgets |
| `juhani-header-product-search` | Header Search widget; isolates existing form styles |
| `juhani-live-product-search-popup` | Search popup widgets |
| `juhani-search-result-card` | Linked product card container, template 1270 |
| `juhani-search-result-photo` | Product image container |
| `juhani-search-result-copy` | Product information container |
| `juhani-search-result-category` | Category Heading widget |
| `juhani-search-result-stock` | Stock Heading widget |
| `juhani-search-product-title` | Product title Heading widget |
| `juhani-search-result-price` | Price Heading widget |
| `juhani-search-result-measurements` | Haat/Suta Heading widget |
| `juhani-search-result-chevron` | Open product Icon widget |

The `juhani-live-search-heading`, `juhani-live-search-footer` and count/view-all classes belong to the small runtime bars inserted inside the marked Search widget. They are authored deterministic classes, not Elementor-generated classes.

## CSS Code / Active Implementation

**Complete header/dropdown CSS:** http://localhost/juhani/preview-assets/live-product-search.css

**Active source:** Header template → select **Header — Live product search** widget → **Advanced → Custom CSS**.

**Complete card helper CSS:** http://localhost/juhani/preview-assets/live-search-card.css

**Active source:** Product card template 1270 → **Template/Page Settings → Advanced → Custom CSS**.

These files are complete reference copies of the saved CSS, not separately enqueued stylesheet files. To change the active code, edit the matching Elementor Custom CSS field. The runtime reads the saved card CSS into a scoped inline stylesheet, because native search AJAX escapes CSS string content in its loop styles.

New authored CSS uses custom class roots throughout and has no generated Elementor selector or `!important`. Earlier search-only styling overrides are disabled while this design is active; their files are backed up. Elementor's generated files in `wp-content/uploads/elementor/css/` should not be edited manually.

## Runtime files

- `wp-content/mu-plugins/juhani-live-product-search.php` — dynamic product tags, saved CSS loading and script enqueue.
- `preview-assets/live-product-search.js` — result count, view-all link, keyboard browsing and popup initialization.
- `wp-content/mu-plugins/juhani-elementor-search-fix.php` — native live-search rendering, now normalizes Bengali digits for marked widgets.

The current product catalogue, stock, prices, orders and unrelated page layouts are not saved by this change. Original search templates/settings are preserved in `.live-search-before-design-<template-id>-20261013.json` backups for 1267, 1270, 1214 and 1207.

## Verification

Chromium checks pass for Home, About, Shop, Blog, Contact Us and a single product page: 9 cards, visible titles, correct images/prices/stock, 12px radius, valid product links, no nested anchors or JavaScript errors. Dropdowns fit 1100/1440/1920px desktop widths; the popup/card layout fits 320/390px phones and the 768px tablet viewport. Existing tablet search-entry visibility was preserved; tablet layout was checked by resizing an open popup.

Numeric and Bengali-digit search, empty results, result counts, view-all query links and ArrowDown/Escape keyboard browsing were verified. Other header elements and unrelated page/template data remain unchanged. Evidence is in `live-search-first-check.json`, `live-search-mobile-check.json` and `live-search-pages-check.json`.
