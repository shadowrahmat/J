# Single Product — Elementor editing guide

Live product: http://localhost/juhani/product/cast-net-25-ghor/

**Main template editor:** http://localhost/juhani/wp-admin/post.php?post=37&action=elementor

Template: **Single Product**, ID **37**. Its existing **Include → Products** display condition remains active, so this layout applies to the site's single product pages.

## সহজে edit করবেন যেভাবে

1. উপরের Main template editor link খুলুন। অথবা WordPress → Templates → Theme Builder → Single Product → Edit with Elementor।
2. Elementor-এর Structure/Navigator খুলুন। Sectionগুলো নাম দেওয়া আছে:
   - 01 — Cart notices
   - 02 — Breadcrumb
   - 03 — Product gallery and purchase
   - 04 — Specifications and reviews
   - 05 — Related products
3. Gallery, product information, price note, purchase heading, WhatsApp/telephone links, details heading ও buying guide আলাদা container/widget। যে অংশ edit করবেন সেটি select করে **Content / Style / Advanced** ব্যবহার করুন। Width, gap, padding, typography, colour ও card radius native Elementor controls-এ আছে।
4. **Product name, photographs, price, stock, SKU, attributes, description ও variations** বদলাবেন WordPress → **Products → Edit Product** থেকে। Template এগুলো dynamic ভাবে দেখায়, তাই প্রতিটি product-এর জন্য নতুন template বানাতে হবে না।
5. Related products হলো একটি native **Loop Grid**, যার Query → Source হলো **Related Products**। Card-এর photo, title, price ও button আলাদা Elementor widget। Card edit করুন এই link থেকে:

   http://localhost/juhani/wp-admin/post.php?post=1294&action=elementor

   নাম: **Juhani Single Product - Related Card**। বর্তমান home/shop product card template বদলানো হয়নি।

## CSS Class ও Class Location

Classগুলো ইতিমধ্যে assigned আছে। পুনরায় paste করার প্রয়োজন নেই। Container-এর **Advanced → CSS Classes** অথবা widget-এর একই field-এ পাবেন।

| CSS Class | Class Location |
| --- | --- |
| `juhani-single-section` | প্রতিটি top-level section container |
| `juhani-single-notices` | 01 — Cart notices section |
| `juhani-single-breadcrumb` | WooCommerce Breadcrumb widget |
| `juhani-single-overview` | Product — Two columns container |
| `juhani-single-gallery-column` | Gallery — Images and note container |
| `juhani-product-gallery` | Product gallery — dynamic photos widget |
| `juhani-single-summary` | Product — Editable information container |
| `juhani-single-stock` | Current stock widget |
| `juhani-single-title` | Product — Dynamic title widget |
| `juhani-single-price` | Product — Dynamic price widget |
| `juhani-single-short-description` | WooCommerce short description widget |
| `juhani-single-purchase` | Purchase — Native options, quantity and add to cart widget |
| `juhani-single-support` | Support — WhatsApp and telephone container |
| `juhani-single-meta` | Product — SKU and categories widget |
| `juhani-single-details` | 04 — Specifications and reviews section |
| `juhani-single-tabs` | Details — Native specifications, description and reviews widget |
| `juhani-single-guide` | Details — Buying guide card container |
| `juhani-single-related` | 05 — Related products section |
| `juhani-single-related-grid` | Related products — Editable Loop Grid widget |
| `juhani-single-related-card` | Related card Loop Item-এর outer container |
| `juhani-single-related-photo` | Related card-এর dynamic Image widget |
| `juhani-single-related-price` | Related card-এর dynamic Price Heading widget |

## CSS Code ও Implementation

**Active CSS location:** Single Product template editor → **Page/Template Settings → Advanced → Custom CSS**। Installed Elementor document-এর Custom CSS control `advanced` tab-এ আছে বলে যাচাই করা হয়েছে। এটি পৃথক widget-এর CSS Classes field নয়।

**সম্পূর্ণ CSS code-এর reference copy:**

http://localhost/juhani/preview-assets/single-product.css

Local file: `D:\xampp\htdocs\Juhani\preview-assets\single-product.css`.

এই file সরাসরি enqueue হয় না; active CSS template-এর Custom CSS field-এ saved আছে। File থেকে পরিবর্তন করলে সম্পূর্ণ code ওই field-এ paste করে Update/Publish করুন। সাধারণ layout/style পরিবর্তন সংশ্লিষ্ট native controls থেকেই করুন।

Elementor নিজে `wp-content/uploads/elementor/css/post-37.css` generate করে। **ওই generated file edit করবেন না।** Authored CSS-এ কোনো Elementor-generated ID/class selector বা `!important` নেই। WooCommerce-এর internal markup-এর জন্য custom class দিয়ে scoped rules দেওয়া হয়েছে।

## Dynamic behaviour

Native WooCommerce variation matching, stock checks, quantity limits, cart submission, gallery/lightbox and reviews remain in use. The scoped enhancement adds quantity +/- controls, selected-price feedback and partial-choice price ranges. Swatches remain supplied by the installed Variation Swatches plugin.

Active runtime: `wp-content/mu-plugins/juhani-single-product.php`.

Active enhancement JavaScript: `preview-assets/single-product.js`.

The old single-product customization plugin and template were backed up before replacement:

- `.single-product-before-preview-design-20261013.json`
- `.single-product-plugin-before-preview-design-20261013.php`
- `.single-product-theme-css-before-preview-20261013.css`

The legacy single-product-only override block was removed from the theme's `juhani-mobile-fixes.css`, because its forced mobile grid/width rules overrode the new editable layout. The rest of that stylesheet is byte-for-byte preserved.

No products, prices, orders, header/footer templates or other page layouts were saved by this implementation. Checks use read-only page loads and option selection; no actual cart/order submission is made.

## Verification

- All 9 published product URLs render the new template with HTTP 200 and the correct product form ID.
- The 25 Ghor product's 138 available combinations select the exact WooCommerce variation, price and SKU; reset disables ordering and clears the variation ID.
- Changing the main product's variation preserves each related card's own price.
- Quantity typing, normalization, +/- controls, gallery navigation and the reviews tab were checked in a headless Chromium browser.
- Screenshots were inspected for desktop and tablet. Responsive checks at 320, 390, 768, 1024 and 1440px show no horizontal overflow; product images/cards/layout containers have editable 12px radius.
- PHP/JavaScript syntax and authored selector standards pass. Saved Custom CSS matches the complete reference copy.

Evidence files: `single-product-check.json`, `single-product-final-check.json`, `single-product-desktop.png`, `single-product-mobile.png`, `single-product-tablet.png`.
