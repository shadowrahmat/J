import fs from 'node:fs';
import {c,C,px,val,box,gap,esc,container,grid,section,heading,text,image,rule} from './native-helpers.mjs';
const titleRow=container('Woodend Firewood title',[
 heading('Woodend — page title','Woodend',82,{header_size:'h1',_element_width:'auto',typography_line_height:val(1.1,'em'),typography_letter_spacing:val(-.045,'em'),typography_font_size_tablet:px(65),typography_font_size_mobile:px(53)}),
 heading('Firewood — page title','Firewood',82,{header_size:'div',_element_width:'auto',title_color:C.accent,typography_font_style:'italic',typography_line_height:val(1.1,'em'),typography_letter_spacing:val(-.045,'em'),typography_font_size_tablet:px(65),typography_font_size_mobile:px(53)})
],{flex_direction:'row',flex_justify_content:'center',flex_align_items:'center',flex_wrap:'wrap',flex_gap:gap(15,0),flex_gap_mobile:gap(10,0)});
const banner=section('01 Woodend page title',[
 text('Breadcrumb','<p><a href="http://localhost/macedonrang/">Home</a> &nbsp; / &nbsp; Woodend Firewood</p>',11,{align:'center',typography_font_size_mobile:px(10),_margin:box(0,0,22,0)}),titleRow
],150,46,{padding_mobile:box(130,20,34,20)});
const supplyCopy=container('Seasoned Redgum supply text',[
 heading('Split and seasoned supply heading',esc(c.supplyTitle),41,{typography_line_height:val(1.13,'em'),typography_font_size_mobile:px(33),_margin:box(0,0,25,0)}),
 text('Minimum order and recycled firewood','<p>'+c.supply[0]+'</p>',14,{text_color:'#535a4d',typography_line_height:val(1.95,'em'),_padding:box(0,0,0,20),_border_border:'solid',_border_width:box(0,0,0,1),_border_color:'#c9a990'}),
 text('Aged Redgum and moisture content','<p>'+c.supply[1]+'</p>',14,{typography_line_height:val(1.95,'em'),_margin:box(22,0,0,0)}),
 text('Domestic and commercial supply','<p>'+c.supply[2]+'</p>',14,{typography_line_height:val(1.95,'em'),_margin:box(22,0,0,0)})
]);
const supply=section('02 Split and seasoned Redgum supply',[
 image('Seasoned Redgum firewood','supply',600,750,285),supplyCopy
],0,78,{...grid('.9fr 1.1fr',64),grid_align_items:'stretch'});
function sectionHeading(name,title){return container(name,[rule(name+' accent'),heading(name+' title',esc(title),44,{align:'center',typography_font_size_mobile:px(34)})],{width:val(100,'%'),margin:box(0,0,38,0),margin_mobile:box(0,0,28,0)});}
const specialistPhoto=container('Delivery photo and loading policy',[
 image('Firewood being loaded for delivery','delivery',345,330,275),
 container('Loading and refund policy',[text('Loading and refund policy text','<p>'+c.specialist[1]+'</p>',12,{typography_line_height:val(1.85,'em'),text_color:'#75786c'})],{background_background:'classic',background_color:C.paper,padding:box(23,27),padding_mobile:box(21)})
]);
const specialistCopy=container('Specialist service information',[
 text('Specialist service and customer supply','<p>'+c.specialist[0]+'</p>',15,{typography_line_height:val(1.95,'em')}),
 container('Firewood heating comparison',[text('Firewood versus gas or electricity','<p>'+c.specialist[2]+'</p>',15,{typography_line_height:val(1.95,'em')})],{margin:box(30,0,0,0),padding:box(25,0,0,0),border_border:'solid',border_width:box(1,0,0,0),border_color:C.line})
]);
const specialist=section('03 Specialist Redgum wood service',[
 sectionHeading('Specialist service heading',c.specialistTitle),
 container('Specialist service layout',[specialistPhoto,specialistCopy],{...grid('1.02fr .98fr',68)})
],0,78);
const why=section('04 Why choose us',[
 heading('Why choose us heading',esc(c.whyTitle),48,{typography_font_size_tablet:px(43),typography_font_size_mobile:px(38)}),
 text('High BTU sourcing and recycling','<p>'+c.why+'</p>',16,{typography_line_height:val(1.9,'em')})
],62,62,{...grid('.8fr 1.2fr',90),background_color:'#eae6dc'});
const ecoLeft=text('Eco-friendly heating and carbon footprint','<p>'+c.eco[0]+'</p>',14,{typography_line_height:val(1.95,'em'),typography_font_size_mobile:px(13)});
const ecoRight=container('Orders and contact',[
 text('Homes and commercial customers','<p>'+c.eco[1]+'</p>',14,{typography_line_height:val(1.95,'em'),typography_font_size_mobile:px(13)}),
 container('Call for firewood requirements',[text('Call for firewood requirements text','<p>'+c.eco[2]+'</p>',24,{typography_font_family:'DM Serif Display',typography_line_height:val(1.5,'em'),typography_letter_spacing:val(-.015,'em'),typography_font_size_mobile:px(23),text_color:C.ink,link_color:C.accent})],{margin:box(24,0,0,0),padding:box(24,0,0,0),border_border:'solid',border_width:box(1,0,0,0),border_color:C.line})
],{border_border:'solid',border_width:box(0,0,0,1),border_width_mobile:box(1,0,0,0),border_color:'#e6ded0',padding:box(0,0,0,38),padding_tablet:box(0,0,0,28),padding_mobile:box(24,0,0,0)});
const eco=section('05 Eco-friendly heating',[
 sectionHeading('Eco-friendly heating heading',c.ecoTitle),
 container('Eco-friendly information panel',[ecoLeft,ecoRight],{...grid('1fr 1fr',64),grid_align_items:'start',background_background:'classic',background_color:C.paper,padding:box(40,44),padding_tablet:box(30),padding_mobile:box(24),grid_gaps_tablet:gap(30),grid_gaps_mobile:gap(24)})
],72,50);
const comparison=section('06 Mixed hardwood comparison',[
 container('Hardwood comparison text',[text('Mixed hardwood comparison paragraph','<p>'+esc(c.mixed).replace('5%','<strong>5%</strong>').replace('25%','<strong>25%</strong>')+'</p>',25,{align:'center',typography_font_family:'DM Serif Display',typography_line_height:val(1.5,'em'),typography_font_size_mobile:px(23),text_color:'#69705f'})],{width:val(77,'%'),width_tablet:val(90,'%'),width_mobile:val(100,'%')})
],0,65,{flex_align_items:'center',padding_mobile:box(0,20,42,20)});
const content=[banner,supply,specialist,why,eco,comparison];
const types={};
function check(nodes){for(const n of nodes){if(n.settings.custom_css)throw Error('Custom CSS not permitted in native build');if(n.widgetType){types[n.widgetType]=(types[n.widgetType]||0)+1;if(['html','gallery','google_maps'].includes(n.widgetType))throw Error('Unexpected widget')}check(n.elements||[])}}
check(content);
fs.writeFileSync(new URL('woodend-elementor.json',import.meta.url),JSON.stringify({version:'0.4',title:'Woodend Firewood — Cream Native Elementor',type:'page',content,page_settings:{hide_title:'yes'}},null,2));
console.log(JSON.stringify({sections:content.length,widgets:types}));
