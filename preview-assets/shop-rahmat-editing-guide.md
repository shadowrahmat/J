# Shop Rahmat — Elementor editing

Page: http://localhost/juhani/shop/

Editor: http://localhost/juhani/wp-admin/post.php?post=1278&action=elementor

Navigator / Structure-এর প্রথম level-এ পাঁচটি আলাদা section আছে:

1. **01 - Breadcrumb**
2. **02 - Shop Banner**
3. **03 - Collection Information**
4. **04 - Product Catalog**
5. **05 - Buying Guide**

এগুলোকে ঘিরে একটি বড় parent container নেই। প্রতিটি section আলাদাভাবে move, duplicate বা edit করা যায়।

## Product card edit

**04 - Product Catalog → Catalog - Filters and Products → Catalog - Product Results → Products - Editable Card Grid** খুলুন।

প্রতিটি product-এর নিজের **Product 01 / Product 02…** container আছে। এর ভিতরে:

- **Image → Editable Image**: ছবি replace করুন। Image ও card padding বর্তমানে 0; ছবির ওপর Ghor badge নেই।
- **Text Price and Button → Product Title**: নাম edit করুন।
- **Haat and Suta**: মাপের তথ্য edit করুন।
- **Price**: displayed price edit করুন।
- **Select Options**: button text ও link edit করুন। Product বদলালে photo container ও title-এর link-ও update করুন।

Colour, font, background, border, radius, spacing ও columns **Layout / Style / Advanced**-এর native Elementor controls-এ আছে। এগুলো edit করতে Custom CSS লিখতে হবে না। Default card radius 12px।

## Banner and filters

Banner-এর **Text and Browse Button** ও **Photograph and Caption** আলাদা container। Banner photo বদলাতে photograph container-এর **Style → Background** খুলুন।

Filter panel-এর প্রতিটি group আলাদা native Accordion widget। Accordion-এর Content-এ group title, আর Style-এ title typography / icon / spacing edit করুন। Sort select এবং checkbox/price input fields ছোট HTML widgets-এ আছে; সাধারণ page layout বা product cards HTML widget-এ একসঙ্গে রাখা হয়নি। Shop page-এর search box সরানো হয়েছে।

## CSS location

**Page Settings → Advanced → Custom CSS**-এ শুধু form fields, active filter chips, hidden state ও mobile dialog-এর supporting rules আছে। Section বা product card layout এই CSS দিয়ে নিয়ন্ত্রণ করা হয়নি। File reference: `preview-assets/shop-rahmat-controls.css`। CSS Classes মূলত search/filter-এর hooks হিসেবে ব্যবহৃত হয়েছে।

বর্তমান ৯টি product-এর content ও prices preview থেকে নেওয়া snapshot। WooCommerce-এ নতুন product/price update করলে এই editable cards এবং তাদের Advanced → Attributes-এর filter metadata-ও update করতে হবে; Select Options link আসল WooCommerce product page-এ যায়।

Template: `preview-assets/shop-rahmat-elementor.json`। Original page backup: `.shop-rahmat-before-design.json`।
