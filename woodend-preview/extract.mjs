import fs from 'node:fs';
import {connect} from './browser.mjs';
const b=await connect();
const raw=fs.readFileSync(new URL('source.html',import.meta.url),'utf8');
const c=await b.evaluate(`(()=>{
 const d=new DOMParser().parseFromString(${JSON.stringify(raw)},'text/html');
 const norm=s=>s.replace(/\\s+/g,' ').trim();
 const txt=e=>norm(e.textContent);
 const entry=d.querySelector('.entry-content');
 const intro=entry.querySelector('.row.no-gutters');
 const specialist=entry.querySelector('.row.mt-5');
 const lower=entry.querySelector('.border_line');
 const remaining=entry.cloneNode(true);
 remaining.querySelectorAll('.product_in,.delivery_page_gallery,script,style,iframe').forEach(e=>e.remove());
 const mixed=norm(remaining.textContent.match(/Mixed Hardwoods burn[\\s\\S]*?cheaper to buy\\./)[0]);
 const expected=[...remaining.querySelectorAll('.pro_titl,.pro_txt p,.pro_txt:not(:has(p))')].map(txt).filter(Boolean);
 return {
  title:txt(d.querySelector('h1')),
  supplyTitle:txt(intro.querySelector('h2')),
  supply:[...intro.querySelectorAll('.pro_txt p')].map(e=>e.innerHTML),
  specialistTitle:txt(specialist.querySelector('h2')),
  specialist:[...specialist.querySelectorAll('.pro_txt p')].map(e=>e.innerHTML),
  whyTitle:txt(lower.querySelectorAll('.pro_titl')[0]),
  why:lower.querySelectorAll('.pro_txt')[0].innerHTML,
  ecoTitle:txt(lower.querySelectorAll('.pro_titl')[1]),
  eco:[...lower.querySelectorAll('.pro_txt')[1].querySelectorAll('p')].map(e=>e.innerHTML),
  mixed,
  images:[intro.querySelector('img').src,specialist.querySelector('img').src],
  expected:[txt(d.querySelector('h1')),...expected,mixed],
  links:[...remaining.querySelectorAll('a[href]')].map(a=>a.getAttribute('href')),
  omitted:['Three-product section','Gallery','Delivery areas','Map']
 };
})()`);
fs.writeFileSync(new URL('content.json',import.meta.url),JSON.stringify(c,null,2));
console.log(JSON.stringify(c,null,2));
b.close();
