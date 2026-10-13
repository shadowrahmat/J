const assert=require('assert');const fs=require('fs');
const {chromium}=require('C:/Users/Skytech/AppData/Local/npm-cache/_npx/e41f203b7505f1fb/node_modules/playwright-core');
(async()=>{
 const expected=JSON.parse(fs.readFileSync('preview-assets/live-search-expected.json','utf8'));
 const browser=await chromium.launch({headless:true,executablePath:process.env.LOCALAPPDATA+'/ms-playwright/chromium-1243/chrome-win64/chrome.exe'});
 try{
  const page=await browser.newPage({viewport:{width:1440,height:1000}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
  await page.goto('http://localhost/juhani/',{waitUntil:'networkidle'});
  const response=page.waitForResponse(r=>r.url().includes('refresh-search'));
  await page.locator('header .e-search-input').fill('cast');await response;await page.locator('header .juhani-search-result-card').first().waitFor({state:'visible'});
  const result=await page.evaluate(()=>{
   const root=document.querySelector('.juhani-live-product-search'),panel=root.querySelector('.e-search-results-container'),r=panel.getBoundingClientRect();
   return {count:root.querySelector('.juhani-live-search-count')?.textContent,panel:{width:r.width,left:r.left,right:r.right,radius:getComputedStyle(panel).borderRadius,height:r.height},
    cards:[...root.querySelectorAll('.juhani-search-result-card')].map(c=>({href:c.href,name:c.querySelector('.juhani-search-product-title').textContent.trim(),stock:c.querySelector('.juhani-search-result-stock').textContent.trim(),amounts:[...c.querySelectorAll('.juhani-search-result-price .amount')].map(e=>Number(e.textContent.replace(/[^\d.]/g,''))),measure:c.querySelector('.juhani-search-result-measurements').textContent.trim(),image:c.querySelector('img').getAttribute('src'),photoWidth:c.querySelector('.juhani-search-result-photo').getBoundingClientRect().width,radius:getComputedStyle(c).borderRadius,visible:c.getBoundingClientRect().height>0})),overflow:document.documentElement.scrollWidth>innerWidth};
  });
  console.log(JSON.stringify({result,errors},null,2));
  await page.screenshot({path:'preview-assets/live-search-home-desktop.png',fullPage:false});
  await page.locator('header .e-search-results-container').screenshot({path:'preview-assets/live-search-dropdown.png'});
  assert.equal(result.cards.length,9);assert(!result.overflow);assert(result.panel.left>=0&&result.panel.right<=1440);assert.equal(result.panel.radius,'12px');
  for(const c of result.cards){const e=expected[c.href];assert(e,'Unexpected product link');assert.equal(c.name.replace(/\u2013/g,'-'),e.name);assert.equal(c.stock,e.stock);assert.equal(c.amounts[0],Number(e.min));assert.equal(c.amounts.at(-1),Number(e.max));assert.equal(c.image,e.image);assert.equal(c.radius,'12px');assert(c.visible);assert(c.measure);}
  assert.equal(errors.length,0);
  fs.writeFileSync('preview-assets/live-search-first-check.json',JSON.stringify({result,errors},null,2));
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exitCode=1;});
