import fs from 'node:fs';
import assert from 'node:assert/strict';
import {connect} from './browser.mjs';
const c=JSON.parse(fs.readFileSync(new URL('content.json',import.meta.url)));
const b=await connect();
await b.call('Page.enable');
await b.call('Page.navigate',{url:'http://localhost/Juhani/woodend-preview/'});
await b.evaluate('document.fonts.ready.then(()=>Promise.all([...document.images].map(i=>{i.loading="eager";return i.decode().catch(()=>{})})))');
const content=await b.evaluate(`(()=>{const c=${JSON.stringify(c)};const norm=s=>s.replace(/\\s+/g,' ').trim();const main=document.querySelector('main');const text=norm(main.innerText);const links=[...main.querySelectorAll('a[href]')].map(a=>a.href);const blocks=[...main.querySelectorAll('h1,h2,p,figcaption')].map(e=>norm(e.textContent));return {expectedBlocks:c.expected.length,renderedBlocks:blocks.length,missing:c.expected.filter(s=>!text.includes(norm(s))),added:blocks.filter(s=>!c.expected.map(norm).includes(s)),missingLinks:c.links.filter(s=>!links.includes(s)),productSection:!!main.querySelector('[class*=product]'),productNames:/Blue Gum|Sugar Gum/.test(text),gallery:!!main.querySelector('[class*=gallery]'),deliveryAreas:/Delivery Areas/.test(text),map:!!document.querySelector('iframe'),fonts:[...document.fonts].filter(f=>f.status==='loaded').map(f=>f.family)}})()`);
const viewports=[];
for(const width of [1440,1024,768,390,320]){
 await b.call('Emulation.setDeviceMetricsOverride',{width,height:960,deviceScaleFactor:1,mobile:false});
 await b.evaluate('scrollTo(0,0)');
 await new Promise(r=>setTimeout(r,150));
 viewports.push(await b.evaluate('({width:innerWidth,scrollWidth:document.documentElement.scrollWidth,overflow:document.documentElement.scrollWidth>innerWidth,brokenImages:[...document.images].filter(i=>!i.complete||!i.naturalWidth).length})'));
 if([1440,390].includes(width)){
  for(const [label,selector] of [['top','.woodend-page-title'],['supply','.woodend-supply'],['specialist','.woodend-specialist'],['why','.woodend-why'],['eco','.woodend-eco']]){
   await b.evaluate(`document.querySelector('${selector}').scrollIntoView()`);
   if(label==='top')await b.evaluate('scrollTo(0,0)');
   const shot=await b.call('Page.captureScreenshot',{format:'png'});
   fs.writeFileSync(new URL(`preview-${width}-${label}.png`,import.meta.url),Buffer.from(shot.data,'base64'));
  }
 }
}
console.log(JSON.stringify({content,viewports},null,2));
fs.writeFileSync(new URL('verification.json',import.meta.url),JSON.stringify({content,viewports},null,2));
b.close();
assert.equal(content.missing.length,0);assert.equal(content.added.length,0);assert.equal(content.missingLinks.length,0);assert(!content.productSection&&!content.productNames&&!content.gallery&&!content.deliveryAreas&&!content.map);assert(!viewports.some(v=>v.overflow||v.brokenImages));
