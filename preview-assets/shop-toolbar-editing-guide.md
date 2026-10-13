# Shop sorting and mobile Filters row

Page: http://localhost/juhani/shop/

Elementor editor: http://localhost/juhani/wp-admin/post.php?post=1278&action=elementor

Desktop sorting: 1px `#d8e5ee` border, 12px radius, 44px minimum height.
Mobile: Filters and sorting share one row, 12px gap, matching 44px height. Sorting fills the available width. The badge beside Filters has been removed; the product-results total and filter chips remain.

## CSS Class and Class Location

All classes are already assigned in **Advanced → CSS Classes**:

| CSS Class | Elementor location |
| --- | --- |
| `juhani-shop-toolbar` | Results — Sort and Mobile Filters container |
| `juhani-shop-filter-toggle` | Mobile — Filters Button container |
| `juhani-shop-sort` | Products — Sort Dropdown HTML widget |

The row direction, wrap, alignment, gap and responsive widths are also saved in native Elementor controls, so they can be edited there.

## CSS Code and Implementation

Complete component CSS: http://localhost/juhani/preview-assets/shop-toolbar.css

Local reference: `D:\xampp\htdocs\Juhani\preview-assets\shop-toolbar.css`.

**Active implementation:** Shop page → Elementor **Page Settings → Advanced → Custom CSS**, under the comment `Shop sorting field`. The reference file is not separately enqueued. Paste the complete component CSS into this field to update it; replace the existing component block rather than duplicating it.

The sorting rules use assigned custom classes and contain no generated Elementor selectors or `!important`. Existing unrelated legacy rules are preserved.

Verified in Chromium: desktop border/radius, one-line mobile alignment at 320/390/425px, price sorting, opening/applying mobile filters with no count badge, no horizontal overflow or JavaScript errors. Other page/template Elementor data is unchanged.

Backup: `.shop-before-toolbar-update-20261013.json`.
