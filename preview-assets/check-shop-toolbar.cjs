const assert=require('assert');const fs=require('fs');
const {chromium}=require('C:/Users/Skytech/AppData/Local/npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright-core');
(async()=>{
 const b=await chromium.launch({headless:true,executablePath:process.env.LOCALAPPDATA+'/ms-playwright/chromium-1243/chrome-win64/chrome.exe'});
 try {
  const p=await b.newPage({viewport:{width:1440,height:1050}}),errors=[];p.on('pageerror',e=>errors.push(e.message));await p.goto('http://localhost/juhani/shop/',{waitUntil:'networkidle'});
  const inspect=()=>p.evaluate(()=>{const s=document.querySelector('.juhani-shop-sort select'),button=document.querySelector('#jsp-open-filters a'),r=s.getBoundingClientRect(),br=button.getBoundingClientRect(),c=getComputedStyle(s);return {width:innerWidth,border:c.borderColor,radius:c.borderRadius,select:{x:r.x,y:r.y,width:r.width,height:r.height},filter:{x:br.x,y:br.y,width:br.width,height:br.height},overflow:document.documentElement.scrollWidth>innerWidth};});
  const desktop=await inspect();assert.equal(desktop.radius,'12px');assert.equal(desktop.border,'rgb(216, 229, 238)');
  await p.locator('.juhani-shop-toolbar').screenshot({path:'preview-assets/shop-toolbar-desktop.png'});
  await p.selectOption('.juhani-shop-sort select','price-high');
  const prices=await p.locator('#jsp-product-grid .jsp-product').evaluateAll(cards=>cards.filter(c=>!c.hidden).map(c=>Number(c.dataset.min)));assert(prices.every((v,i)=>i===0||prices[i-1]>=v));
  const mobile=[];
  for(const width of [320,390,425]){await p.setViewportSize({width,height:844});const check=await inspect();assert(!check.overflow);assert(Math.abs(check.select.y-check.filter.y)<2);assert(check.select.x>=check.filter.x+check.filter.width);assert.equal(check.radius,'12px');mobile.push(check);}
  await p.locator('.juhani-shop-toolbar').screenshot({path:'preview-assets/shop-toolbar-mobile.png'});
  await p.locator('#jsp-open-filters a').click();assert(await p.locator('dialog.jsp-native-dialog').evaluate(e=>e.open));
  const choice=p.locator('dialog.jsp-native-dialog input[data-group="ghor"][value="15"]');await choice.check({force:true});
  const filtered=await p.locator('#jsp-product-grid .jsp-product').evaluateAll(cards=>cards.filter(c=>!c.hidden).map(c=>JSON.parse(c.dataset.attributes).ghor));assert(filtered.length>0);assert(filtered.every(v=>v.map(String).includes('15')));
  await p.locator('#jsp-apply-filters a').click();assert(!(await p.locator('dialog.jsp-native-dialog').evaluate(e=>e.open)));
  assert.equal(await p.locator('#jsp-filter-count').count(),0);
  const active=await inspect();assert(Math.abs(active.select.y-active.filter.y)<2);assert(!active.overflow);
  const result={desktop,mobile,sorting:'descending prices verified',filter:'opens, filters, closes; count badge removed; remains aligned',errors};assert.equal(errors.length,0);fs.writeFileSync('preview-assets/shop-toolbar-check.json',JSON.stringify(result,null,2));console.log(JSON.stringify(result,null,2));
 }finally{await b.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
