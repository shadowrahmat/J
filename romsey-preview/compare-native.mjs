import fs from 'node:fs';
const s=JSON.parse(fs.readFileSync(new URL('native-saved.json',import.meta.url)));
const e=JSON.parse(fs.readFileSync(new URL('romsey-elementor.json',import.meta.url))).content;
const differences=[];
function diff(a,b,path='root'){
 if(JSON.stringify(a)===JSON.stringify(b))return;
 if(a&&b&&typeof a==='object'&&typeof b==='object'){
   for(const k of new Set([...Object.keys(a),...Object.keys(b)]))diff(a[k],b[k],path+'.'+k);
 }else differences.push({path,expected:a,actual:b});
}
diff(e,s);
console.log(JSON.stringify({expectedRoots:e.length,savedRoots:s.length,differences:differences.slice(0,25),total:differences.length},null,2));
