import fs from 'node:fs';
import crypto from 'node:crypto';
const dir=new URL('./',import.meta.url);
const c=JSON.parse(fs.readFileSync(new URL('content.json',dir)));
const state=JSON.parse(fs.readFileSync(new URL('wordpress-state.json',dir)));
const C={ink:'#34382f',accent:'#ac4d35',cream:'#f5f1e9',paper:'#fffcf6',line:'#dedacf',muted:'#6c7064'};
const px=size=>({unit:'px',size,sizes:[]});
const val=(size,unit)=>({unit,size,sizes:[]});
const box=(t=0,r=t,b=t,l=r)=>({unit:'px',top:String(t),right:String(r),bottom:String(b),left:String(l),isLinked:false});
const gap=(col=0,row=col)=>({unit:'px',column:String(col),row:String(row),isLinked:false,size:0});
const id=()=>crypto.randomBytes(4).toString('hex');
const esc=s=>s.replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;');
const local=s=>s.replace(/^https?:\/\/(www\.)?macedonrangesfirewoodyard\.com\.au/,'http://localhost/macedonrang').replace('/pipers-creek-firewood','/pippers-creek-firewood');
const cls=n=>'mrfw-woodend-native-'+n;
const base=n=>({_title:n,_css_classes:cls(n.toLowerCase().replace(/[^a-z0-9]+/g,'-').replace(/-$/,'')),_margin:box(0),_padding:box(0)});
function widget(type,name,settings={}){return {id:id(),elType:'widget',widgetType:type,settings:{...base(name),...settings},elements:[]};}
function container(name,children,settings={},root=false){const named=base(name);named.css_classes=named._css_classes;delete named._css_classes;return {id:id(),elType:'container',isInner:!root,settings:{...named,content_width:'full',width:val(100,'%'),width_mobile:val(100,'%'),container_type:'flex',flex_direction:'column',flex_gap:gap(0),padding:box(0),margin:box(0),border_radius:box(0),...settings},elements:children};}
function grid(columns,g=0,tabletColumns=columns,mobileColumns='1fr'){return {container_type:'grid',grid_columns_grid:val(columns,'custom'),grid_columns_grid_tablet:val(tabletColumns,'custom'),grid_columns_grid_mobile:val(mobileColumns,'custom'),grid_rows_grid:val('auto','custom'),grid_rows_grid_tablet:val('auto','custom'),grid_rows_grid_mobile:val('auto','custom'),grid_gaps:gap(g),grid_gaps_tablet:gap(Math.min(g,42)),grid_gaps_mobile:gap(28),grid_align_items:'center',grid_justify_items:'stretch',grid_auto_flow:'row'};}
function section(name,children,top=0,bottom=0,settings={}){return container(name,children,{content_width:'boxed',boxed_width:px(1160),background_background:'classic',background_color:C.cream,padding:box(top,48,bottom,48),padding_tablet:box(top,32,bottom,32),padding_mobile:box(Math.min(top,40),20,Math.min(bottom,44),20),...settings},true);}
function typo(size,family='DM Sans',weight='400',line=1.85){return {typography_typography:'custom',typography_font_family:family,typography_font_size:px(size),typography_font_weight:weight,typography_line_height:val(line,'em'),typography_letter_spacing:val(0,'px')};}
function heading(name,title,size=40,settings={}){const merged={title,header_size:'h2',title_color:C.ink,...typo(size,'DM Serif Display','400',1.16),typography_letter_spacing:val(-.025,'em'),typography_font_size_tablet:px(Math.min(size,35)),typography_font_size_mobile:px(Math.min(size,34)),...settings};if(merged.typography_font_size_mobile?.unit==='vw')merged.typography_font_size_mobile=val('clamp(64px, 20.5vw, 80px)','custom');return widget('heading',name,merged);}
function text(name,html,size=15,settings={}){return widget('text-editor',name,{editor:html,text_color:C.muted,link_color:C.ink,link_hover_color:C.accent,...typo(size),typography_font_size_mobile:px(size>=14?14:size),paragraph_spacing:px(0),...settings});}
const media={
 'blue-gum':{id:60,url:'http://localhost/macedonrang/wp-content/uploads/2026/09/459ad6fc-Blue-Gum-1-1.jpg'},
 'redgum':{id:73,url:'http://localhost/macedonrang/wp-content/uploads/2026/09/98fa3459-product_img.jpg'},
 'sugar-gum':{id:57,url:'http://localhost/macedonrang/wp-content/uploads/2026/09/3a0be6f4-Sugar-Gum-1-1.jpg'},
 'supply':{id:55,url:'http://localhost/macedonrang/wp-content/uploads/2026/09/23b0124e-Sunbury_img.jpg'},
 'delivery':{id:54,url:'http://localhost/macedonrang/wp-content/uploads/2026/09/23972b72-sunbury_img3.jpg'}
};
function image(name,key,height,tablet=height,mobile=height,link=''){return widget('image',name,{image:{...media[key],source:'library',alt:name},image_size:'full',width:val(100,'%'),space:val(100,'%'),height:px(height),height_tablet:px(tablet),height_mobile:px(mobile),'object-fit':'cover',image_border_radius:box(0),link_to:link?'custom':'none',...(link?{link:{url:local(link)}}:{})});}
function rule(name){return widget('divider',name,{style:'solid',weight:px(1),color:C.accent,width:val(40,'px'),align:'center',gap:px(0),_margin:box(0,0,24,0)});}

export {c,C,px,val,box,gap,esc,container,grid,section,heading,text,image,rule};
