import fs from 'node:fs';
import assert from 'node:assert/strict';
import {connect} from './browser.mjs';
const c=JSON.parse(fs.readFileSync(new URL('content.json',import.meta.url)));
const template=JSON.parse(fs.readFileSync(new URL('romsey-elementor.json',import.meta.url)));
const saved=JSON.parse(fs.readFileSync(new URL('native-saved.json',import.meta.url)));
assert.deepEqual(saved,template.content);
assert.equal(saved.length,9);
const types={};
function audit(nodes){for(const n of nodes){if(n.widgetType)types[n.widgetType]=(types[n.widgetType]||0)+1;assert(!['html','gallery','google_maps'].includes(n.widgetType));assert(!n.settings.custom_css);audit(n.elements||[])}}
audit(saved);
const b=await connect();
await b.call('Page.enable');
await b.call('Page.navigate',{url:'http://localhost/macedonrang/romsey-firewood/'});
for(let i=0;i<40;i++){if(await b.evaluate('!!document.querySelector(".mrfw-romsey-native-01-banner")'))break;await new Promise(r=>setTimeout(r,500))}
await b.evaluate('document.fonts.ready.then(()=>Promise.all([...document.images].map(i=>{i.loading="eager";return i.decode().catch(()=>{})})))');
const content=await b.evaluate(`(()=>{const root=document.querySelector('.mrfw-romsey-native-01-banner').closest('[data-elementor-id]');const norm=s=>s.replace(/\\s+/g,' ').trim();const text=norm(root.innerText);const c=${JSON.stringify(c)};const local=s=>s.replace(/^https?:\\/\\/(www\\.)?macedonrangesfirewoodyard\\.com\\.au/,'http://localhost/macedonrang').replace('/pipers-creek-firewood','/pippers-creek-firewood');const links=[...root.querySelectorAll('a[href]')].map(a=>a.href);return {page:root.getAttribute('data-elementor-id'),sections:root.querySelectorAll(':scope > .e-con').length,missingText:c.expected.concat(c.mixed).filter(s=>!text.includes(norm(s))),missingLinks:c.links.filter(s=>!links.includes(local(s))),areas:root.querySelectorAll('.mrfw-romsey-native-delivery-area-links .elementor-widget-button').length,iframes:root.querySelectorAll('iframe').length,htmlWidgets:root.querySelectorAll('.elementor-widget-html').length,gallery:root.querySelectorAll('.elementor-widget-gallery').length,fonts:[...document.fonts].map(f=>({family:f.family,status:f.status})).filter(f=>f.family.includes('DM'))}})()`);
const viewports=[];
for(const width of [1440,1024,768,390,320]){
 await b.call('Emulation.setDeviceMetricsOverride',{width,height:960,deviceScaleFactor:1,mobile:false});
 await b.evaluate('scrollTo(0,0)');
 await new Promise(r=>setTimeout(r,250));
 viewports.push(await b.evaluate(`(()=>{const r=document.querySelector('.mrfw-romsey-native-01-banner').closest('[data-elementor-id]');return {width:innerWidth,pageWidth:document.documentElement.scrollWidth,pageOverflow:document.documentElement.scrollWidth>innerWidth,rootWidth:r.clientWidth,rootScrollWidth:r.scrollWidth,rootOverflow:r.scrollWidth>r.clientWidth,brokenImages:[...r.querySelectorAll('img')].filter(i=>!i.complete||!i.naturalWidth).length}})()`));
 if([1440,390].includes(width)){
  for(const [label,target] of [['top','.mrfw-romsey-native-01-banner'],['products','.mrfw-romsey-native-02-firewood-products'],['points','.mrfw-romsey-native-04-redgum-key-points'],['btu','.mrfw-romsey-native-05-high-btu-and-delivery'],['why','.mrfw-romsey-native-06-why-redgum-off-cuts'],['contact','.mrfw-romsey-native-07-contact-and-supply'],['areas','.mrfw-romsey-native-09-delivery-areas']]){
   await b.evaluate(`document.querySelector('${target}').scrollIntoView()`);
   if(label==='top')await b.evaluate('scrollTo(0,0)');
   await new Promise(r=>setTimeout(r,120));
   const shot=await b.call('Page.captureScreenshot',{format:'png'});
   fs.writeFileSync(new URL(`native-${width}-${label}.png`,import.meta.url),Buffer.from(shot.data,'base64'));
  }
 }
}
const report={types,content,viewports};
fs.writeFileSync(new URL('native-verification.json',import.meta.url),JSON.stringify(report,null,2));
console.log(JSON.stringify(report,null,2));
b.close();
assert.equal(content.page,'18');assert.equal(content.sections,9);assert.equal(content.areas,33);assert.equal(content.missingText.length,0);assert.equal(content.missingLinks.length,0);assert.equal(content.iframes,0);assert.equal(content.htmlWidgets,0);assert.equal(content.gallery,0);assert(!viewports.some(v=>v.rootOverflow||v.brokenImages));
const state=JSON.parse(fs.readFileSync(new URL('wordpress-state.json',import.meta.url)));
fs.writeFileSync(new URL('applied-state.json',import.meta.url),JSON.stringify({page:18,url:'http://localhost/macedonrang/romsey-firewood/',sections:9,backup:state.backup,verified:new Date().toISOString()},null,2));
