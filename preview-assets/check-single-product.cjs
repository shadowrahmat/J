const fs=require('fs');
const {chromium}=require('C:/Users/Skytech/AppData/Local/npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright-core');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.LOCALAPPDATA+'/ms-playwright/chromium-1243/chrome-win64/chrome.exe'});
 const page=await browser.newPage({viewport:{width:1440,height:1050}});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://localhost/juhani/product/cast-net-25-ghor/?design-check='+Date.now(),{waitUntil:'networkidle'});
 await page.screenshot({path:'preview-assets/single-product-desktop.png',fullPage:true});
 const desktop=await page.evaluate(()=>{
  const box=s=>{const el=document.querySelector(s);if(!el)return null;const r=el.getBoundingClientRect(),c=getComputedStyle(el);return {x:r.x,y:r.y,width:r.width,height:r.height,fontSize:c.fontSize,color:c.color,borderRadius:c.borderRadius,display:c.display};};
  return {title:document.querySelector('.juhani-single-title')?.textContent.trim(),overflow:document.documentElement.scrollWidth>innerWidth,header:!!document.querySelector('header'),footer:!!document.querySelector('footer'),gallery:box('.juhani-product-gallery'),thumbnail:box('.juhani-product-gallery .flex-control-thumbs li'),summary:box('.juhani-single-summary'),titleStyle:box('.juhani-single-title h1'),price:box('.juhani-single-price .price'),purchase:box('.juhani-single-purchase'),swatch:box('.juhani-single-purchase .variable-item'),specifications:document.querySelectorAll('.juhani-single-specification').length,related:document.querySelectorAll('.juhani-single-related-card').length,relatedLinks:[...document.querySelectorAll('.juhani-single-related-card h3 a')].map(a=>a.href),variants:jQuery('.juhani-single-purchase .variations_form').data('product_variations')?.length,formAction:document.querySelector('.juhani-single-purchase form')?.action,cartText:document.querySelector('.juhani-single-purchase .single_add_to_cart_button')?.textContent.trim(),galleryInitialized:!!jQuery('.juhani-product-gallery .woocommerce-product-gallery').data('product_gallery')};
 });
 const variations=await page.evaluate(async()=>{
  const f=document.querySelector('.juhani-single-purchase .variations_form'),$f=jQuery(f),variants=$f.data('product_variations'),selects=[...f.querySelectorAll('select')];let checked=0;
  for(const v of variants.filter(v=>v.is_in_stock&&v.is_purchasable&&v.variation_is_active)) {
   // Change inputs only. Never submit a cart or order.
   $f.find('select').val('').trigger('change');for(const s of selects)s.value=v.attributes[s.name]||s.options[1]?.value||'';
   $f.trigger('check_variations');await new Promise(r=>setTimeout(r,320));
   if(Number(f.querySelector('[name=variation_id]').value)!==v.variation_id)throw Error('Variation ID mismatch '+v.variation_id);
   if(f.querySelector('.single_add_to_cart_button').classList.contains('disabled'))throw Error('Valid variation disabled');checked++;
   const visiblePrice=Number(document.querySelector('.juhani-single-price .price .amount:last-of-type bdi').textContent.replace(/[^\d.]/g,''));
   if(visiblePrice!==Number(v.display_price))throw Error('Price mismatch '+v.variation_id);
   const sku=document.querySelector('.juhani-single-meta .sku')?.textContent;
   if(v.sku&&sku!==v.sku)throw Error('SKU mismatch '+v.variation_id);
  }
  $f.find('.reset_variations').trigger('click');await new Promise(r=>setTimeout(r,750));
  return {checked,resetVariationId:f.querySelector('[name=variation_id]').value,resetDisabled:f.querySelector('.single_add_to_cart_button').classList.contains('disabled'),status:f.querySelector('.juhani-single-selection-status').textContent};
 });
 await page.locator('.juhani-single-tabs .reviews_tab a').click();
 const reviewsVisible=await page.locator('.juhani-single-tabs #reviews').isVisible();
 await page.locator('.juhani-single-tabs .additional_information_tab a').click();
 await page.locator('.juhani-product-gallery .flex-control-thumbs li:nth-child(2) img').click();
 const galleryChanged=await page.evaluate(()=>jQuery('.juhani-product-gallery .woocommerce-product-gallery').data('flexslider')?.currentSlide===1);
 await page.locator('.juhani-product-gallery .flex-control-thumbs li:nth-child(1) img').click();
 await page.setViewportSize({width:390,height:844});await page.screenshot({path:'preview-assets/single-product-mobile.png',fullPage:true});
 const mobile=await page.evaluate(()=>({overflow:document.documentElement.scrollWidth>innerWidth,bodyWidth:document.documentElement.scrollWidth,gallery:document.querySelector('.juhani-product-gallery').getBoundingClientRect().width,summary:document.querySelector('.juhani-single-summary').getBoundingClientRect().width,order:document.querySelector('.single_add_to_cart_button').getBoundingClientRect().toJSON(),quantity:document.querySelector('.juhani-single-purchase .quantity').getBoundingClientRect().toJSON()}));
 fs.writeFileSync('preview-assets/single-product-check.json',JSON.stringify({desktop,mobile,variations,reviewsVisible,galleryChanged,errors},null,2));console.log(JSON.stringify({desktop,mobile,variations,reviewsVisible,galleryChanged,errors},null,2));await browser.close();
})().catch(e=>{console.error(e);process.exitCode=1;});
