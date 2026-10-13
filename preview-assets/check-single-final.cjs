const fs=require('fs');const assert=require('assert');
const {chromium}=require('C:/Users/Skytech/AppData/Local/npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright-core');
(async()=>{
 const urls=JSON.parse(fs.readFileSync('preview-assets/single-product-live-urls.json','utf8'));
 const renders=await Promise.all(urls.map(async product=>{const r=await fetch(product.url),html=await r.text();assert.equal(r.status,200);assert(html.includes('juhani-single-summary'));assert(html.includes('name="product_id" value="'+product.id+'"'));assert(!html.includes('Add Your Heading Text Here'));return {id:product.id,http:r.status};}));
 const b=await chromium.launch({headless:true,executablePath:process.env.LOCALAPPDATA+'/ms-playwright/chromium-1243/chrome-win64/chrome.exe'});
 try{const p=await b.newPage({viewport:{width:1440,height:1050}});const errors=[];p.on('pageerror',e=>errors.push(e.message));await p.goto('http://localhost/juhani/product/cast-net-25-ghor/',{waitUntil:'networkidle'});
 const behavior=await p.evaluate(async()=>{
  const form=document.querySelector('.juhani-single-purchase form'),$f=jQuery(form),v=$f.data('product_variations').find(v=>v.is_in_stock&&v.is_purchasable);
  const cardPrices=()=>[...document.querySelectorAll('.juhani-single-related-price')].map(e=>e.textContent.trim());const initialPrices=cardPrices();
  for(const s of form.querySelectorAll('select'))s.value=v.attributes[s.name];$f.trigger('check_variations');await new Promise(r=>setTimeout(r,400));
  const ready=!form.querySelector('.single_add_to_cart_button').classList.contains('disabled');const currentPrices=cardPrices();
  const qty=form.querySelector('input.qty');qty.value='';qty.dispatchEvent(new Event('input'));const blankPreserved=qty.value==='';jQuery(qty).trigger('change');const normalized=qty.value==='1';
  const buttons=form.querySelectorAll('.juhani-single-quantity-button');buttons[1].click();const plus=qty.value==='2';buttons[0].click();const minus=qty.value==='1';
  $f.find('.reset_variations').trigger('click');await new Promise(r=>setTimeout(r,750));
  const panel=document.querySelector('.juhani-single-tabs .woocommerce-Tabs-panel');
  return {ready,relatedPricesPreserved:JSON.stringify(initialPrices)===JSON.stringify(currentPrices),blankPreserved,normalized,plus,minus,resetDisabled:form.querySelector('.single_add_to_cart_button').classList.contains('disabled'),resetId:form.querySelector('[name=variation_id]').value,panelPadding:getComputedStyle(panel).padding,panelBackground:getComputedStyle(panel).backgroundColor,initialPrices};
 });
 for(const name of ['ready','relatedPricesPreserved','blankPreserved','normalized','plus','minus','resetDisabled'])assert(behavior[name],name+' failed');assert(!behavior.resetId);assert.equal(behavior.panelPadding,'0px');
 await p.screenshot({path:'preview-assets/single-product-desktop.png',fullPage:true});
 const responsive=[];
 for(const width of [320,390,768,1024,1440]){await p.setViewportSize({width,height:900});const result=await p.evaluate(()=>({width:innerWidth,overflow:document.documentElement.scrollWidth>innerWidth,cardRadius:getComputedStyle(document.querySelector('.juhani-single-related-card')).borderRadius,photoRadius:getComputedStyle(document.querySelector('.juhani-product-gallery .flex-viewport')).borderRadius,summaryRadius:getComputedStyle(document.querySelector('.juhani-single-summary')).borderRadius,formWidth:document.querySelector('.juhani-single-purchase').getBoundingClientRect().width,holderWidth:document.querySelector('.juhani-single-purchase .e-atc-qty-button-holder').getBoundingClientRect().width}));assert(!result.overflow);assert.equal(result.cardRadius,'12px');assert(Math.abs(result.formWidth-result.holderWidth)<2);responsive.push(result);}
 await p.setViewportSize({width:390,height:844});await p.screenshot({path:'preview-assets/single-product-mobile.png',fullPage:true});await p.setViewportSize({width:768,height:1024});await p.screenshot({path:'preview-assets/single-product-tablet.png',fullPage:true});assert.equal(errors.length,0);
 const result={renders,behavior,responsive,errors};fs.writeFileSync('preview-assets/single-product-final-check.json',JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
 }finally{await b.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
