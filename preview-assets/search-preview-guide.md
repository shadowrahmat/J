# Product search design preview

**Preview:** http://localhost/juhani/search-preview.php

নতুন design page-এর মাঝের **Design preview** search box-এ আছে। সেখানে `cast`, `25 Ghor` বা `২৫` লিখে ফলাফল দেখুন। 15/25/44 Ghor suggestion buttons-ও ব্যবহার করতে পারেন।

এই standalone preview বর্তমান WooCommerce catalog থেকে actual product image, name, category, price range, stock ও Haat/Suta options পড়ছে। Product card click করলে আসল product page খুলবে। Existing live header search-এর template এই preview তৈরির সময় save করা হয়নি।

## CSS Class / Class Location

এই classes `search-preview.php`-এর HTML-এ manually assigned আছে। ভবিষ্যতে Elementor-এ layout বসালে corresponding container/widget-এর **Advanced → CSS Classes**-এ ব্যবহার করা যাবে।

| CSS Class | Current location / corresponding component |
| --- | --- |
| `juhani-search-preview` | Main preview layout wrapper |
| `juhani-search-preview-intro` | Introductory heading and suggestion buttons container |
| `juhani-search-preview-panel` | Search field, result list and footer container |
| `juhani-search-preview-form` | Search field and clear button form |
| `juhani-search-preview-input` | Product search input |
| `juhani-search-preview-results` | Scrollable results list |
| `juhani-search-preview-card` | Individually linked product row |
| `juhani-search-preview-photo` | Product image container |
| `juhani-search-preview-copy` | Category, stock, title, price and measurements container |
| `juhani-search-preview-price` | Dynamic price range |
| `juhani-search-preview-empty` | No-results message and reset button |
| `juhani-search-preview-panel-footer` | Keyboard hint and view-all link |

## CSS Code / Implementation

**Complete, active CSS:** http://localhost/juhani/preview-assets/search-preview.css

Local file: `D:\xampp\htdocs\Juhani\preview-assets\search-preview.css`.

`search-preview.php` enqueues this stylesheet only for this standalone preview. Edit this file to adjust the preview. It is not a reference-only copy or an Elementor-generated stylesheet. All authored selectors use manually assigned custom classes; no generated Elementor selectors or `!important` are used.

Preview interactions: `preview-assets/search-preview.js`.

The preview is currently PHP/HTML/CSS/JavaScript, not a saved Elementor page or a replacement for the live header's result template.

## Checks

HTTP 200/noindex; actual 9 catalog products; working product links; suggestion searches; Bengali digit search; empty/reset states; keyboard navigation; no horizontal overflow at 320/390/768/1440px; no browser JavaScript errors. Desktop, product-panel and mobile screenshots inspected in Chromium.
