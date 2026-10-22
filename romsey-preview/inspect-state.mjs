import fs from 'node:fs';
const s=JSON.parse(fs.readFileSync(new URL('wordpress-state.json',import.meta.url)));
for(const o of s.options) console.log(o.option_name,o.option_value);
const walk=(nodes,depth=0)=>{for(const n of nodes){console.log(' '.repeat(depth)+[n.id,n.elType,n.widgetType||'',n.settings?._title||'',n.settings?._css_classes||''].join(' | '));if(n.settings?.custom_css)console.log('CSS',n.settings.custom_css);walk(n.elements||[],depth+1)}};
walk(JSON.parse(s.meta.find(m=>m.meta_key==='_elementor_data').meta_value));
const sample=JSON.parse(s.macedon.find(m=>m.meta_key==='_elementor_data').meta_value);
console.log('MACEDON ROOT SETTINGS',JSON.stringify(sample[0].settings));
const types=new Set();
function examples(nodes){for(const n of nodes){if(n.widgetType&&!types.has(n.widgetType)){console.log('EXAMPLE',n.widgetType,JSON.stringify(n.settings));types.add(n.widgetType)}examples(n.elements||[])}}
examples(sample);
console.log('SAMPLE ATTACHMENTS',JSON.stringify(s.attachments.filter(a=>/Blue.Gum|Sunbury|product_img|Sugar.Gum|sunbury_img3/.test(a.guid))));
