import fs from 'node:fs';
import {connect} from './browser.mjs';
const c=JSON.parse(fs.readFileSync(new URL('content.json',import.meta.url),'utf8'));
const b=await connect();
await b.call('Page.enable');
await b.call('Page.navigate',{url:'http://localhost/Juhani/romsey-preview/'});
await new Promise(r=>setTimeout(r,1000));
await b.evaluate('document.fonts.ready.then(()=>Promise.all([...document.images].map(i=>{i.loading="eager";return i.decode().catch(()=>{})})))');
const content=await b.evaluate(`(()=>{const norm=s=>s.replace(/\\s+/g,' ').trim();const text=norm(document.querySelector('main').innerText);const content=${JSON.stringify(c)};const links=[...document.querySelectorAll('main a')].map(a=>a.href);return {missingText:content.expected.concat(content.mixed).filter(s=>!text.includes(norm(s))),missingLinks:content.links.filter(s=>!links.includes(s)),products:document.querySelectorAll('.romsey-product').length,areas:document.querySelectorAll('.romsey-areas li').length,iframes:document.querySelectorAll('iframe').length,gallery:document.querySelectorAll('[class*=gallery]').length}})()`);
const viewports=[];
for(const width of [1440,1024,768,390,320]){
  await b.call('Emulation.setDeviceMetricsOverride',{width,height:960,deviceScaleFactor:1,mobile:false});
  await b.evaluate('scrollTo(0,0)');
  await new Promise(r=>setTimeout(r,120));
  viewports.push(await b.evaluate(`({width:innerWidth,scrollWidth:document.documentElement.scrollWidth,overflow:document.documentElement.scrollWidth>innerWidth,brokenImages:[...document.images].filter(i=>!i.complete||!i.naturalWidth).length})`));
  if([1440,390].includes(width)){
    for(const [label,position] of [['top',0],['points',1200],['btu',2050],['bottom',3150]]){
      await b.evaluate(`scrollTo(0,${position})`);
      await new Promise(r=>setTimeout(r,120));
      const shot=await b.call('Page.captureScreenshot',{format:'png'});
      fs.writeFileSync(new URL(`preview-${width}-${label}.png`,import.meta.url),Buffer.from(shot.data,'base64'));
    }
  }
}
fs.writeFileSync(new URL('verification.json',import.meta.url),JSON.stringify({content,viewports},null,2));
console.log(JSON.stringify({content,viewports},null,2));
b.close();
if(content.missingText.length||content.missingLinks.length||viewports.some(v=>v.overflow||v.brokenImages)||content.iframes||content.gallery)process.exitCode=1;
