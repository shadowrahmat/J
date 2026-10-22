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
const cls=n=>'mrfw-romsey-native-'+n;
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
const heroTitle=container('Banner title',[heading('Romsey — title','Romsey',103,{header_size:'h1',typography_line_height:val(.98,'em'),typography_letter_spacing:val(-.045,'em'),typography_font_size_tablet:px(80),typography_font_size_mobile:val(20.5,'vw')}),heading('Firewood — title','Firewood',103,{header_size:'div',title_color:C.accent,typography_font_style:'italic',typography_line_height:val(.98,'em'),typography_letter_spacing:val(-.045,'em'),typography_font_size_tablet:px(80),typography_font_size_mobile:val(20.5,'vw')})]);
const breadcrumb=text('Breadcrumb','<p><a href="http://localhost/macedonrang/">Home</a> &nbsp; / &nbsp; Romsey Firewood</p>',11,{_margin:box(0,0,32,0),_margin_mobile:box(0,0,24,0),typography_letter_spacing:val(.02,'em')});
const heroCopy=container('Banner text',[breadcrumb,heroTitle,text('Romsey introduction','<p>'+esc(c.intro)+'</p>',15,{_margin:box(27,0,0,0),_margin_mobile:box(24,0,0,0)})]);
const terms=container('Price and delivery terms',[
 heading('Redgum cubic metre price',esc(c.terms[0]),24,{header_size:'div',title_color:C.accent,typography_line_height:val(1.2,'em'),typography_letter_spacing:val(-.03,'em'),typography_font_size_tablet:px(21),typography_font_size_mobile:px(22)}),
 container('Minimum delivery order',[text('Free delivery minimum',esc(c.terms[1]),11,{typography_line_height:val(1.7,'em'),typography_font_size_mobile:px(11)})],{border_border:'solid',border_width:box(0,0,0,1),border_color:'#e2d9cb',padding:box(0,0,0,22),padding_tablet:box(0,0,0,14),padding_mobile:box(0,0,0,16)})
],{...grid('.95fr 1.05fr',22,'.95fr 1.05fr','.9fr 1.1fr'),grid_gaps_mobile:gap(16),background_background:'classic',background_color:C.paper,padding:box(22,24),padding_tablet:box(18),padding_mobile:box(20)});
const heroVisual=container('Banner image and terms',[image('Redgum firewood — banner','supply',365,350,270),terms]);
const hero=section('01 Banner',[heroCopy,heroVisual],42,48,{...grid('1fr 1.04fr',88),grid_gaps_mobile:gap(30),padding_mobile:box(30,20,32,20)});
const productCards=c.products.map((p,i)=>{
 const title=heading(p.title+' — product name',esc(p.title),26,{header_size:'h3',typography_font_size_tablet:px(23),typography_font_size_mobile:px(28),_margin:box(0,0,7,0)});
 const price=text(p.title+' — price','<p>'+esc(p.price)+'</p>',12,{typography_font_size_mobile:px(13)});
 const arrow=widget('icon',p.title+' — product link',{selected_icon:{value:'fas fa-arrow-right',library:'fa-solid'},view:'framed',shape:'square',primary_color:C.accent,secondary_color:C.paper,size:px(12),icon_padding:px(10),border_width:box(1),border_radius:px(0),_element_width:'auto'});
 const info=container(p.title+' — details',[container(p.title+' — text',[title,price]),arrow],{...grid('1fr auto',12,'1fr auto','1fr auto'),grid_gaps_mobile:gap(12),padding:box(21,22),padding_tablet:box(18),padding_mobile:box(19,20)});
 return container(p.title+' product card',[image(p.title+' — image',['blue-gum','redgum','sugar-gum'][i],225,190,195),info],{html_tag:'a',link:{url:local(p.href)},background_background:'classic',background_color:C.paper});
});
const products=section('02 Firewood products',productCards,0,78,{...grid('repeat(3, minmax(0, 1fr))',26),grid_align_items:'stretch',grid_gaps_mobile:gap(22)});
const intro=section('03 Redgum supply heading',[container('Supply heading',[rule('Supply heading accent'),heading('Romsey Redgum supply heading',esc(c.introTitle),44,{align:'center',typography_font_size_mobile:px(33)})],{width:val(70,'%'),width_tablet:val(100,'%'),width_mobile:val(100,'%')})],0,36,{flex_align_items:'center',padding_mobile:box(0,20,28,20)});
const points=widget('icon-list','Redgum key points',{icon_list:c.points.map(p=>({_id:id().slice(0,7),text:p,selected_icon:{value:'fas fa-check',library:'fa-solid'},link:{url:''}})),space_between:px(14),icon_color:C.accent,icon_size:px(10),text_color:C.muted,text_indent:px(12),icon_align:'left',icon_vertical_align:'flex-start',icon_self_align:'flex-start',icon_typography_typography:'custom',icon_typography_font_family:'DM Sans',icon_typography_font_size:px(14),icon_typography_font_size_mobile:px(13),icon_typography_font_weight:'400',icon_typography_line_height:val(1.8,'em'),divider:'yes',divider_style:'solid',divider_weight:px(1),divider_color:'#e8e2d7'});
const pointsPanel=container('Key points panel',[image('Seasoned Redgum firewood','supply',530,620,260),container('Key points text',[heading('Key points heading',esc(c.keyTitle),33,{typography_font_size_tablet:px(30),typography_font_size_mobile:px(30),_margin:box(0,0,23,0),_element_width:'initial',_element_custom_width:val(75,'%'),_element_custom_width_tablet:val(100,'%'),_element_custom_width_mobile:val(100,'%')}),points])],{...grid('.9fr 1.1fr',56,'.8fr 1.2fr'),grid_align_items:'stretch',grid_gaps_mobile:gap(26),background_background:'classic',background_color:C.paper,padding:box(40),padding_tablet:box(28),padding_mobile:box(22)});
const keyPoints=section('04 Redgum key points',[pointsPanel],0,80);
const btuCopy=container('BTU text',[
 heading('High BTU heading',esc(c.btuTitle),40,{_margin:box(0,0,27,0),typography_font_size_mobile:px(34)}),
 text('What BTU means','<p>'+c.btu[0]+'</p>',15),
 text('Loading and delivery policy','<p>'+c.btu[1]+'</p>',12,{_margin:box(23,0,0,0),_padding:box(0,0,0,22),_border_border:'solid',_border_width:box(0,0,0,1),_border_color:'#c9ad97'}),
 text('Warmth and burn time','<p>'+c.btu[2]+'</p>',15,{_margin:box(23,0,0,0)})
]);
const btu=section('05 High BTU and delivery',[btuCopy,image('Firewood being loaded for delivery','delivery',580,720,290)],0,80,{...grid('1.08fr .92fr',80)});
const why=section('06 Why Redgum off-cuts',[heading('Why Redgum off-cuts heading',esc(c.whyTitle),39,{typography_font_size_mobile:px(33)}),text('Why Redgum is sought after','<p>'+c.why[0]+'</p>',15,{typography_line_height:val(1.9,'em')})],66,66,{...grid('.9fr 1.1fr',80),background_color:'#ece8de'});
const contactPanel=container('Contact information panel',[
 heading('Firewood requirements',c.why[2],36,{header_size:'h2',title_color:C.accent,typography_font_size_tablet:px(32),typography_font_size_mobile:px(32),typography_line_height:val(1.18,'em')}),
 text('Contact Romsey firewood suppliers','<p>'+c.why[1].replace('>macedonrangesfirewoodyard@gmail.com<','>macedonranges<wbr>firewoodyard@<wbr>gmail.com<')+'</p>',14,{typography_line_height:val(1.9,'em')})
],{...grid('.85fr 1.15fr',64),padding:box(44,48),padding_tablet:box(32),padding_mobile:box(26,24),background_background:'classic',background_color:C.paper});
const contact=section('07 Contact and supply',[contactPanel],68,0);
const comparison=section('08 Hardwood comparison',[container('Hardwood comparison text',[text('Mixed hardwood comparison','<p>'+esc(c.mixed).replace('5%','<strong>5%</strong>').replace('25%','<strong>25%</strong>')+'</p>',24,{align:'center',typography_font_family:'DM Serif Display',typography_line_height:val(1.5,'em'),typography_font_size_mobile:px(23),text_color:'#69705f'})],{width:val(78,'%'),width_tablet:val(90,'%'),width_mobile:val(100,'%')})],36,68,{flex_align_items:'center'});
const areaHeading=container('Delivery Areas heading',[heading('Delivery heading','Delivery',39,{_element_width:'auto',typography_font_size_mobile:px(34)}),heading('Areas heading','Areas',39,{_element_width:'auto',title_color:C.accent,typography_font_style:'italic',typography_font_size_mobile:px(34)})],{flex_direction:'row',flex_align_items:'center',flex_gap:gap(8),_margin:box(0,0,28,0)});
const areaLinks=c.areas.map(a=>widget('button','Delivery area — '+a.title,{text:a.title,link:{url:local(a.href)},align:'justify',typography_typography:'custom',typography_font_family:'DM Sans',typography_font_size:px(12),typography_font_weight:'400',typography_line_height:val(1.8,'em'),button_text_color:C.ink,background_color:'#00000000',hover_color:C.accent,button_background_hover_color:'#00000000',border_border:'solid',border_width:box(0,0,1,0),border_color:C.line,border_radius:box(0),text_padding:box(11,0),selected_icon:{value:'fas fa-arrow-right',library:'fa-solid'},icon_align:'right',icon_indent:px(12),icon_size:px(10)}));
const areaGrid=container('Delivery area links',areaLinks,{...grid('repeat(5, minmax(0, 1fr))',28,'repeat(4, minmax(0, 1fr))','repeat(2, minmax(0, 1fr))'),grid_gaps:gap(28,0),grid_gaps_tablet:gap(28,0),grid_gaps_mobile:gap(22,0)});
const areas=section('09 Delivery areas',[areaHeading,areaGrid],55,65,{border_border:'solid',border_width:box(1,0,0,0),border_color:C.line});
const roots=[hero,products,intro,keyPoints,btu,why,contact,comparison,areas];
const existingSettings=state.meta.find(m=>m.meta_key==='_elementor_page_settings');
const template={version:'0.4',title:'Romsey Firewood — Cream Native Elementor',type:'page',content:roots,page_settings:{hide_title:'yes'}};
fs.writeFileSync(new URL('romsey-elementor.json',dir),JSON.stringify(template,null,2));
const types={};
function audit(nodes){for(const n of nodes){if(n.widgetType)types[n.widgetType]=(types[n.widgetType]||0)+1;if(n.settings.custom_css)throw Error('Unexpected custom CSS');audit(n.elements)}}
audit(roots);
console.log(JSON.stringify({sections:roots.length,widgets:types,oldSettings:!!existingSettings}));
