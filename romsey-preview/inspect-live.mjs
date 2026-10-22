import fs from 'node:fs';
import {connect} from './browser.mjs';
const html=fs.readFileSync(new URL('native-source.html',import.meta.url),'utf8');
console.log(JSON.stringify({prefix:html.slice(0,150),nativeClass:html.includes('mrfw-romsey-native-01-banner'),pageIds:[...html.matchAll(/data-elementor-id="(\d+)"/g)].map(m=>m[1]),title:html.match(/<title>(.*?)<\/title>/)?.[1],content:html.slice(html.indexOf('data-elementor-id="18"'),html.indexOf('data-elementor-id="18"')+1600)},null,2));
const b=await connect();
console.log(await b.evaluate('({url:location.href,title:document.title,text:document.body.innerText.slice(0,900),native:!!document.querySelector(".mrfw-romsey-native-01-banner")})'));
const shot=await b.call('Page.captureScreenshot',{format:'png'});
fs.writeFileSync(new URL('native-debug.png',import.meta.url),Buffer.from(shot.data,'base64'));
b.close();
