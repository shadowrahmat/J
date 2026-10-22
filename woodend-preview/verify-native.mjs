import fs from 'node:fs';
import assert from 'node:assert/strict';
import {connect} from './browser.mjs';
const c=JSON.parse(fs.readFileSync(new URL('content.json',import.meta.url)));
const t=JSON.parse(fs.readFileSync(new URL('woodend-elementor.json',import.meta.url)));
const saved=JSON.parse(fs.readFileSync(new URL('native-saved.json',import.meta.url)));
assert.deepEqual(saved,t.content);assert.equal(saved.length,6);assert(saved.every(n=>n.isInner===false));
const widgets={};
function audit(ns){for(const n of ns){assert(!n.settings.custom_css);if(n.widgetType){widgets[n.widgetType]=(widgets[n.widgetType]||0)+1;assert(!['html','gallery','google_maps'].includes(n.widgetType))}audit(n.elements||[])}}
audit(saved);
const b=await connect();
await b.call('Page.enable');
await b.call('Page.navigate',{url:'http://localhost/macedonrang/woodend-firewood/'});
await b.evaluate('document.fonts.ready.then(()=>Promise.all([...document.images].map(i=>{i.loading="eager";return i.decode().catch(()=>{})})))');
const report=await b.evaluate(`(()=>{const root=document.querySelector('.mrfw-woodend-native-01-woodend-page-title').closest('[data-elementor-id]');const norm=s=>s.replace(/\\s+/g,' ').trim();const text=norm(root.innerText);const c=${JSON.stringify(c)};const links=[...root.querySelectorAll('a[href]')].map(a=>a.href);return {page:root.getAttribute('data-elementor-id'),sections:root.querySelectorAll(':scope > .e-con').length,expectedBlocks:c.expected.length,missingText:c.expected.filter(s=>!text.includes(norm(s))),missingLinks:c.links.filter(s=>!links.includes(s)),productNames:/Blue Gum|Sugar Gum/.test(text),deliveryAreas:/Delivery Areas/.test(text),iframes:root.querySelectorAll('iframe').length,htmlWidgets:root.querySelectorAll('.elementor-widget-html').length,gallery:root.querySelectorAll('.elementor-widget-gallery').length,fonts:[...new Set([...document.fonts].filter(f=>f.status==='loaded').map(f=>f.family))].filter(f=>f.includes('DM'))}})()`);
const viewports=[];
const sections=[['top','.mrfw-woodend-native-01-woodend-page-title'],['supply','.mrfw-woodend-native-02-split-and-seasoned-redgum-supply'],['specialist','.mrfw-woodend-native-03-specialist-redgum-wood-service'],['why','.mrfw-woodend-native-04-why-choose-us'],['eco','.mrfw-woodend-native-05-eco-friendly-heating']];
for(const width of [1440,1024,768,390,320]){
 await b.call('Emulation.setDeviceMetricsOverride',{width,height:960,deviceScaleFactor:1,mobile:false});
 await b.evaluate('scrollTo(0,0)');await new Promise(r=>setTimeout(r,150));
 viewports.push(await b.evaluate(`(()=>{const r=document.querySelector('.mrfw-woodend-native-01-woodend-page-title').closest('[data-elementor-id]');return {width:innerWidth,rootWidth:r.clientWidth,rootScrollWidth:r.scrollWidth,rootOverflow:r.scrollWidth>r.clientWidth,pageWidth:document.documentElement.scrollWidth,brokenImages:[...r.querySelectorAll('img')].filter(i=>!i.complete||!i.naturalWidth).length}})()`));
 if([1440,390].includes(width))for(const [label,sel]of sections){
  await b.evaluate(`document.querySelector('${sel}').scrollIntoView()`);if(label==='top')await b.evaluate('scrollTo(0,0)');
  const shot=await b.call('Page.captureScreenshot',{format:'png'});fs.writeFileSync(new URL(`native-${width}-${label}.png`,import.meta.url),Buffer.from(shot.data,'base64'));
 }
}
fs.writeFileSync(new URL('native-verification.json',import.meta.url),JSON.stringify({widgets,content:report,viewports},null,2));
console.log(JSON.stringify({widgets,content:report,viewports},null,2));
b.close();
assert.equal(report.page,'19');assert.equal(report.sections,6);assert.equal(report.missingText.length,0);assert.equal(report.missingLinks.length,0);assert(!report.productNames&&!report.deliveryAreas&&!report.iframes&&!report.htmlWidgets&&!report.gallery);assert(!viewports.some(v=>v.rootOverflow||v.brokenImages));
