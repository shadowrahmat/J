import fs from 'node:fs';
export async function connect() {
  const tabs=await (await fetch('http://127.0.0.1:9238/json')).json();
  const tab=tabs.find(t=>t.type==='page');
  const ws=new WebSocket(tab.webSocketDebuggerUrl);
  await new Promise(r=>ws.addEventListener('open',r,{once:true}));
  let id=0;const pending=new Map();
  ws.addEventListener('message',e=>{const m=JSON.parse(e.data);if(m.id){const p=pending.get(m.id);pending.delete(m.id);m.error?p.reject(m.error):p.resolve(m.result);}});
  const call=(method,params={})=>new Promise((resolve,reject)=>{pending.set(++id,{resolve,reject});ws.send(JSON.stringify({id,method,params}));});
  const evaluate=async expression=>{const r=await call('Runtime.evaluate',{expression,returnByValue:true,awaitPromise:true});if(r.exceptionDetails)throw new Error(JSON.stringify(r.exceptionDetails));return r.result.value;};
  return {call,evaluate,close:()=>ws.close()};
}
if(process.argv[2]==='extract') {
  const b=await connect();
  const raw=fs.readFileSync(new URL('./source.html',import.meta.url),'utf8');
  const data=await b.evaluate(`(()=>{
    const d=new DOMParser().parseFromString(${JSON.stringify(raw)},'text/html');
    const txt=e=>e?.textContent.replace(/\\s+/g,' ').trim()||'';
    const intro=d.querySelector('.entry-content .row.no-gutters');
    const btu=d.querySelector('.row.mt-5');
    const why=d.querySelector('.border_line');
    const main=d.querySelector('.entry-content').cloneNode(true);
    main.querySelectorAll('[class*="wonderplugin"]').forEach(e=>e.remove());
    return {
      title:txt(d.querySelector('h1')),
      products:[...d.querySelectorAll('.product_in > [class*="col-"]')].map(e=>({title:txt(e.querySelector('.pro_titl')),price:txt(e.querySelector('strong')),href:e.querySelector('a').getAttribute('href')})),
      introTitle:txt(intro.querySelector('h2')),
      terms:[...intro.querySelectorAll('ul:first-child li')].map(txt),
      intro:txt(intro.querySelector('p')),
      keyTitle:txt(intro.querySelectorAll('.pro_titl')[1]),
      points:[...intro.querySelectorAll('ul')][1]?[...[...intro.querySelectorAll('ul')][1].querySelectorAll('li')].map(txt):[],
      btuTitle:txt(btu.querySelector('h2')),
      btu:[...btu.querySelectorAll('.pro_txt p')].map(e=>e.innerHTML),
      whyTitle:txt(why.querySelector('.pro_titl')),
      why:[...why.querySelectorAll('.pro_txt p')].map(e=>e.innerHTML),
      mixed:'Mixed Hardwoods burn Hot with approximately 5% less heat output than Red Gum Firewood, but is around 25% cheaper to buy.',
      areas:[...d.querySelectorAll('.area_list a')].map(e=>({title:txt(e),href:e.getAttribute('href')})),
      expected:[...main.querySelectorAll('h1,h2,.pro_titl,.pro_txt p,.pro_txt li,.pro_txt strong')].map(txt).filter(Boolean),
      links:[...main.querySelectorAll('a[href]')].map(e=>e.getAttribute('href'))
    };
  })()`);
  // The first list supplies the price and minimum-order terms, independently of the points list.
  data.terms=data.terms.slice(0,2);
  fs.writeFileSync(new URL('./content.json',import.meta.url),JSON.stringify(data,null,2));
  console.log(JSON.stringify({products:data.products.length,points:data.points.length,areas:data.areas.length,paragraphs:data.btu.length+data.why.length}));
  b.close();
}
