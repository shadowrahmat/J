import fs from 'node:fs';
import path from 'node:path';

// Native Elementor widgets; only the small collection filter needs an HTML widget.
const base = 'http://localhost/juhani/';
const scope = '.elementor-1261 .rahmat-page';
const response = await fetch(base + 'home-preview.php');
if (!response.ok) throw new Error('Homepage preview did not load');
const html = await response.text();
let sequence = 0x8100000;
const id = () => (++sequence).toString(16);
const zero = {unit:'px',top:'0',right:'0',bottom:'0',left:'0',isLinked:true};
const corners = {unit:'px',top:'12',right:'12',bottom:'12',left:'12',isLinked:true};
function container(classes, elements, title, settings={}) {
  return {id:id(),elType:'container',isInner:false,settings:{content_width:'full',flex_direction:'column',flex_gap:{unit:'px',size:0,column:'0',row:'0',isLinked:true},padding:zero,border_radius:corners,css_classes:classes,_title:title,...settings},elements};
}
function widget(type,settings,classes='',title='',elements=[]) {
  const radius = /(?:product-open|product-tag|image-label|hero-scroll|product-arrow|step-number)/.test(classes) ? {_border_radius:corners} : {};
  return {id:id(),elType:'widget',widgetType:type,settings:{_css_classes:classes,_title:title,_margin:zero,...radius,...settings},elements};
}
const heading = (title,tag='h2',classes='',url='') => widget('heading',{title,header_size:tag,...(url?{link:{url}}:{})},classes,title.replace(/<[^>]*>/g,''));
const text = (editor,classes='',title='Text') => widget('text-editor',{editor},classes,title);
const button = (label,url,classes='jp-button',external=false) => widget('button',{text:label,border_radius:corners,link:{url,is_external:external?'on':''},selected_icon:{value:'',library:''}},classes,label);
const image = (url,alt,classes='',link='') => widget('image',{image:{url,id:0,alt},image_size:'full',image_border_radius:corners,...(link?{link_to:'custom',link:{url:link}}:{link_to:'none'})},classes,alt);
const eyebrow = value => heading(value,'span','eyebrow');
const paths = {
  arrow:'<path d="M5 12h14m-6-6 6 6-6 6"/>',
  search:'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>',
  check:'<path d="m5 12 4 4L19 6"/>',
  shield:'<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6l9-4Z"/><path d="m8 12 3 3 5-6"/>',
  truck:'<path d="M2 4h13v13H2zm13 5h4l3 4v4h-7"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>',
  bag:'<path d="M5 7h14l1 14H4L5 7Z"/><path d="M8 8V6a4 4 0 0 1 8 0v2"/>',
  phone:'<path d="M5 3h4l2 5-3 2a15 15 0 0 0 6 6l2-3 5 2v4c0 2-2 3-4 2C9 19 5 15 3 7c-1-2 0-4 2-4Z"/>'
};
const svg = name => `<svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name]}</svg>`;
fs.mkdirSync('preview-assets/icons',{recursive:true});
for(const name of ['shield','truck','bag','phone']) fs.writeFileSync(`preview-assets/icons/${name}.svg`,svg(name).replace('currentColor','#236b9c'));
const shop = html.match(/<a class="button mint" href="([^"]+)"/)?.[1] || base+'shop/';
const asset = base+'wp-content/uploads/2026/04/';
const hero = container('hero',[
  container('hero-content',[
    heading('MADE FOR LIFE ON THE WATER','span','eyebrow light'),
    heading('A better net.<br>A better <em>catch.</em>','h1'),
    text('<p>Reliable fishing nets and gear, crafted for the way you fish. From your local waters to the open sea.</p>','hero-description','Hero description'),
    container('hero-actions',[button('Explore our collection',shop,'jp-button mint'),button('Find your fishing net ↗','#collection','text-link')],'Hero buttons'),
    text('<div class="notes-list">'+['Durable nylon','Export quality','Bulk orders'].map(x=>`<span>${svg('check')}${x}</span>`).join('')+'</div>','hero-notes','Hero quality notes')
  ],'Hero content'),
  text('<span class="caption-line"></span> GOOD GEAR. GREAT DAYS ON THE WATER.','hero-caption','Banner caption'),
  button('↓','#collection','hero-scroll')
],'Hero banner',{html_tag:'section'});
const benefits = container('benefits container',[
  ['shield','Quality you can count on','Strong materials. Made to last.'],
  ['truck','Worldwide shipping','From our store to your shore.'],
  ['bag','Bulk orders welcome','Gear up for your next big catch.'],
  ['phone','Personal support','We help you find the right fit.']
].map(([icon,title,description])=>container('benefit',[
  image(base+`preview-assets/icons/${icon}.svg`,'','benefit-icon'),
  container('benefit-copy',[heading(title,'span','benefit-title'),text(`<small>${description}</small>`,'','Benefit detail')],title)
],title)),'Shopping benefits',{html_tag:'section'});
const steps = [
  ['Choose your net','Browse the collection for the way you fish.','#collection'],
  ['Find your size','Open a product to explore its available options.','#collection'],
  ['Get a little expert help','Talk through sizes, materials, or a bulk order.','#contact']
];
const finder = container('finder container',[
  container('finder-intro',[
    eyebrow('A LITTLE GUIDANCE'),heading('Your next catch.<br>Your kind of net.'),
    text('<p>Not sure where to start? A few simple choices make finding the right net easier.</p>'),
    button('Help me choose ↗','#contact','underlined no-arrow')
  ],'Guidance introduction'),
  container('finder-steps',steps.map(([title,description,url],i)=>container('finder-step',[
    heading(String(i+1).padStart(2,'0'),'span','step-number'),
    container('step-copy',[heading(title,'h3'),text(`<p>${description}</p>`)],title),
    text('<span aria-hidden="true">↗</span>','step-arrow','Step arrow')
  ],title,{html_tag:'a',link:{url}})),'Guidance steps')
],'Find the right net',{html_tag:'section'});
const productMatches = [...html.matchAll(/<article class="product-card">([\s\S]*?)<\/article>/g)];
if(productMatches.length!==8) throw new Error('Expected eight products in the preview');
const products = productMatches.map(([,card])=>{
  const url=card.match(/<a class="product-image" href="([^"]+)"/)[1];
  const photo=card.match(/<img\b[^>]*src="([^"]+)"/)[1];
  const name=card.match(/<h3><a[^>]*>([\s\S]*?)<\/a><\/h3>/)[1];
  const price=card.match(/<div class="price-row"><span class="price">([\s\S]*?)<\/span><a /)[1];
  return container('product-card',[
    container('product-image',[
      image(photo,name,'product-photo',url),
      ...(card.includes('product-tag')?[heading('On sale','span','product-tag')]:[]),
      button('View details',url,'product-open')
    ],'Product image and link'),
    container('product-info',[
      heading('FISHING ESSENTIALS','span','product-type'),heading(name,'h3','',url),
      container('price-row',[text(price,'price','Product price'),button('View '+name,url,'product-arrow')],'Price and product link')
    ],'Product details')
  ],name,{html_tag:'article'});
});
const filter = `<div class="collection-tools"><div class="collection-label"><span class="feature-dot"></span> Explore the collection</div><label class="collection-search"><span class="screen-reader-text">Filter products in this collection</span>${svg('search')}<input class="rahmat-search" type="search" placeholder="Find a net in this collection…"></label></div><p class="filter-status rahmat-search-status" role="status" hidden></p><script>(()=>{const setup=()=>{document.querySelectorAll('.rahmat-search').forEach(input=>{if(input.dataset.ready)return;input.dataset.ready='1';input.addEventListener('input',()=>{const section=input.closest('.collection'),query=input.value.trim().toLocaleLowerCase();let count=0;section.querySelectorAll('.product-card').forEach(card=>{card.hidden=!card.querySelector('h3').textContent.toLocaleLowerCase().includes(query);if(!card.hidden)count++});const status=section.querySelector('.rahmat-search-status');status.hidden=!query;status.textContent=count?count+' '+(count===1?'product matches':'products match')+' your search.':'No matching nets in this preview collection. Try a different size or browse the full shop.'})})};if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',setup);else setup();if(window.jQuery)jQuery(window).on('elementor/frontend/init',()=>elementorFrontend.hooks.addAction('frontend/element_ready/html.default',setup));})();</script>`;
const collection = container('collection',[
  container('container section',[
    container('section-heading',[
      container('section-heading-copy',[eyebrow('THE JUHANI COLLECTION'),heading('Ready for your next catch.'),text('<p>Find your next net. Compare sizes and see the details before you choose.</p>')],'Collection introduction'),
      button('Shop the collection',shop,'underlined')
    ],'Collection heading'),
    widget('html',{html:filter},'collection-filter','Collection filter'),
    container('product-grid',products,'Editable product collection')
  ],'Collection layout')
],'Product collection',{html_tag:'section',_element_id:'collection'});
const story = container('story section container',[
  container('story-image',[
    image(asset+'About-01-1.webp','Fishing nets and the craft behind Juhani','story-photo'),
    heading('CRAFTED WITH PURPOSE','span','image-label')
  ],'Story photograph'),
  container('story-copy',[
    eyebrow('MORE THAN JUST A NET'),heading('For the people<br>who know the water.'),
    text('<p>Every fishing trip starts with trust in your gear. At Juhani, we bring together durable nets, dependable materials, and practical choices for everyday fishing.</p>'),
    text('<p>Whether you’re choosing your first net or sourcing for your business, we’re here to help you find what works.</p>'),
    text(`<ul class="story-points"><li>${svg('check')} Dependable materials</li><li>${svg('check')} Individual &amp; bulk orders</li></ul>`,'','Story quality points'),
    button('Get to know Juhani',base+'about/','underlined')
  ],'Our story')
],'Our story',{html_tag:'section',_element_id:'story'});
const faq = [...html.matchAll(/<details><summary>([\s\S]*?)<span aria-hidden="true">\+<\/span><\/summary><p>([\s\S]*?)<\/p><\/details>/g)];
if(faq.length!==3) throw new Error('Expected three FAQ items');
const questions = container('questions container section',[
  container('faq-intro',[eyebrow('GOOD TO KNOW'),heading('A few helpful answers.'),text('<p>Less guesswork.<br>More time on the water.</p>'),button('Still need a hand? Talk to us','#contact','faq-help no-arrow')],'FAQ introduction'),
  widget('nested-accordion',{
    items:faq.map(([,title])=>({_id:id(),item_title:title})),accordion_border_radius:corners,default_state:'all_collapsed',max_items_expended:'multiple',title_tag:'span',accordion_item_title_icon:{value:'',library:''},accordion_item_title_icon_active:{value:'',library:''}
  },'faq-list','Helpful answers',faq.map(([,title,answer])=>container('faq-answer',[text(`<p>${answer}</p>`)],title)))
],'Frequently asked questions',{html_tag:'section'});
const contact = container('contact-wrap container',[
  container('contact-banner',[
    container('contact-copy',[
      eyebrow('LET’S TALK FISHING'),heading('A bigger order?<br>Let’s find your perfect fit.'),
      text('<p>Tell us what you need. We’ll help with the right nets,<br class="desktop-break"> sizes, and options for your business.</p>')
    ],'Bulk order introduction'),
    container('contact-actions',[
      button('Chat on WhatsApp','https://wa.me/8801889164315','jp-button mint',true),
      button('+880 1889 164315','tel:+8801889164315','phone-link no-arrow'),
      text('<small>Personal guidance for individual &amp; bulk orders</small>')
    ],'Contact buttons')
  ],'Bulk order banner')
],'Contact and bulk orders',{html_tag:'section',_element_id:'contact'});

// Scope all preview rules to this page body; the site's header/footer are excluded.
function selectors(value) {
  return value.split(',').filter(s=>!/(?:\.header|\.footer|\.announcement|\.skip|\.search-panel|\.icon-button|\.menu-toggle|\.brand|\.account)/.test(s)).map(s=>{
    s=s.trim().replaceAll('.button.mint','.jp-button.mint .elementor-button').replace(/\.button\b/g,'.jp-button .elementor-button');
    s=s.replace(/\.hero-content>p/g,'.hero-content .hero-description p')
      .replace(/\.product-image>img/g,'.product-image .product-photo img')
      .replace(/\.story-image img/g,'.story-image .story-photo img')
      .replace(/\.benefits strong/g,'.benefits .benefit-title .elementor-heading-title')
      .replace(/\.benefits svg/g,'.benefits .benefit-icon img');
    if(s===':root'||s==='body'||s==='html'||s==='main') return scope;
    if(/^main\b/.test(s)) return s.replace(/^main/,scope);
    return scope+' '+s;
  }).join(',');
}
function scopeCss(css) {
  css=css.replace(/\/\*[\s\S]*?\*\//g,'');
  let out='',at=0;
  while(at<css.length){
    const open=css.indexOf('{',at);if(open<0)break;
    const pre=css.slice(at,open).trim();let depth=1,end=open+1;
    while(end<css.length&&depth){if(css[end]==='{')depth++;if(css[end]==='}')depth--;end++}
    const body=css.slice(open+1,end-1);
    if(pre.startsWith('@media')) out+=pre.replaceAll('max-width:1100px','max-width:1024px').replaceAll('max-width:760px','max-width:767px').replaceAll('min-width:761px','min-width:768px')+'{'+scopeCss(body)+'}\n';
    else if(!pre.startsWith('@')){const sel=selectors(pre);const editableBody=/::?(?:before|after)|\bsvg\b/.test(pre)?body:body.replace(/(?:-webkit-|-moz-)?border(?:-(?:top|bottom)-(?:left|right))?-radius\s*:[^;{}]+;?/gi,'');if(sel)out+=sel+'{'+editableBody.replace(/url\(['"]?([^'"\)]+)['"]?\)/g,(_,url)=>`url('${new URL(url,base+'preview-assets/').href}')`)+'}\n'}
    at=end;
  }
  return out;
}
const reset = `
${scope},${scope} *{box-sizing:border-box}
${scope}.e-con,${scope} .e-con{display:block;--width:100%;--container-widget-width:100%;--container-widget-flex-grow:0;--padding-top:0px;--padding-right:0px;--padding-bottom:0px;--padding-left:0px;--gap:0px;gap:0;min-width:0;max-width:none;flex-direction:row;flex-wrap:nowrap;align-items:stretch;justify-content:normal;flex-shrink:1;margin:0;padding:0;border:0}
${scope}.e-con{width:100%;padding:0;overflow:visible}
${scope} .elementor-widget{margin:0;min-width:0;max-width:100%;--container-widget-width:100%;--container-widget-flex-grow:0}
${scope} .elementor-widget-text-editor{font:inherit;color:inherit}
${scope} .elementor-widget-text-editor p{margin:0}
${scope} .elementor-heading-title{font-family:inherit;color:inherit;padding:0}
${scope} .elementor-widget-image a{display:block}
${scope} .elementor-widget-image img{display:block;width:100%;max-width:100%}
${scope} .elementor-button{font:inherit;color:inherit;background:transparent;border:0;padding:0;box-shadow:none}
${scope} .elementor-button-content-wrapper{display:flex;align-items:center;justify-content:space-between;width:100%;gap:22px}
${scope} .elementor-button-text{flex-grow:1}
`;
const adapter = `
${scope}{--rahmat-layout-width:var(--container-max-width,1350px);--rahmat-layout-gutter:20px;--rahmat-hero-inset:48px}
${scope} .container,${scope} .hero{width:min(var(--rahmat-layout-width),calc(100% - var(--rahmat-layout-gutter) - var(--rahmat-layout-gutter)));max-width:var(--rahmat-layout-width);margin-inline:auto}
${scope} .hero-content{width:calc(100% - var(--rahmat-hero-inset) - var(--rahmat-hero-inset));max-width:none;margin-inline:auto}
${scope} .hero-scroll{left:var(--rahmat-hero-inset)}
${scope} .collection{width:100%}
${scope} .product-grid,${scope} .section-heading-copy,${scope} .story-copy,${scope} .faq-intro{min-width:0}
${scope} .eyebrow .elementor-heading-title,${scope} .product-type .elementor-heading-title,${scope} .image-label .elementor-heading-title,${scope} .product-tag .elementor-heading-title,${scope} .step-number .elementor-heading-title{font:inherit;letter-spacing:inherit;line-height:inherit;color:inherit;margin:0}
${scope} .eyebrow .elementor-heading-title{white-space:normal}
${scope} .hero-actions>.elementor-widget,${scope} .section-heading>.underlined{width:auto;flex-shrink:0}
${scope} .underlined,${scope} .text-link,${scope} .faq-help,${scope} .phone-link{width:fit-content;--container-widget-width:auto}
${scope} .hero-caption,${scope} .image-label,${scope} .product-tag{width:auto;--container-widget-width:auto}
${scope} .hero-notes .notes-list,${scope} .hero-caption>.elementor-widget-container{display:flex;align-items:center;gap:inherit}
${scope} .hero-notes .notes-list{flex-wrap:wrap}
${scope} .hero-scroll .elementor-button{font-size:inherit;color:inherit}
${scope} .hero-content .hero-description p{margin-top:0}
${scope} .benefits .benefit-icon{width:44px;flex:0 0 44px}
${scope} .benefits .benefit-copy{width:auto}
${scope} .benefits .benefit-title .elementor-heading-title{font-family:inherit;color:inherit;letter-spacing:0;line-height:1.5}
${scope} .benefits .benefit-title{margin-bottom:4px}
${scope} .benefits .benefit-title .elementor-heading-title{margin-bottom:0}
${scope} .finder-step{display:flex}
${scope} .step-number{width:44px;flex:0 0 44px}
${scope} .step-copy{width:auto;flex:1}
${scope} .step-arrow{width:auto;margin-left:auto;font-size:20px;color:#236b9c}
${scope} .step-arrow span{background:none;width:auto;height:auto;display:inline}
${scope} .collection-filter{width:100%}
${scope} .product-image .product-photo,${scope} .product-image .product-photo .elementor-widget-container,${scope} .product-image .product-photo a,${scope} .story-photo,${scope} .story-photo .elementor-widget-container{height:100%}
${scope} .product-image .product-photo img{height:100%;object-fit:contain}
${scope} .product-info .price{width:auto;flex:1}
${scope} .product-arrow{display:flex;align-items:center;justify-content:center}
${scope} .product-arrow .elementor-button{display:flex;align-items:center;justify-content:center;width:100%;height:100%}
${scope} .product-open .elementor-button{display:block;width:100%;color:inherit;font-size:inherit;font-weight:inherit}
${scope} .product-image:focus-within .product-open{opacity:1;transform:translateY(0)}
${scope} .jp-button .elementor-button-content-wrapper:after,${scope} .underlined:not(.no-arrow) .elementor-button-content-wrapper:after,${scope} .product-arrow .elementor-button-content-wrapper:after,${scope} .product-open .elementor-button-content-wrapper:after{content:'';display:block;width:22px;height:22px;flex-shrink:0;background:currentColor;mask:url('${base}preview-assets/icons/arrow.svg') center/contain no-repeat}
${scope} .product-arrow .elementor-button-content-wrapper{width:15px;height:15px;gap:0}
${scope} .product-arrow .elementor-button-text{position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap}
${scope} .product-arrow .elementor-button-content-wrapper:after{width:15px;height:15px}
${scope} .underlined .elementor-button{font-size:inherit;font-weight:600;color:inherit}
${scope} .underlined .elementor-button-content-wrapper:after{width:18px;height:18px}
${scope} .text-link .elementor-button{font-size:inherit;color:inherit}
${scope} .story-copy>.elementor-widget-text-editor p{margin-top:14px;margin-bottom:14px}
${scope} .story-points{margin-top:10px;margin-bottom:24px}
${scope} .faq-list .e-n-accordion{display:flex;flex-direction:column;gap:12px}
${scope} .faq-list details>summary{padding:0;background:none;border:0;color:inherit}
${scope} .faq-list .e-n-accordion-item-title-header{display:block;width:auto;height:auto;background:none;font:inherit;transform:none!important}
${scope} .faq-list .e-n-accordion-item-title-text{font:inherit;color:inherit;line-height:inherit}
${scope} .faq-list summary:after{content:'+';width:28px;height:28px;display:grid;place-items:center;flex-shrink:0;border-radius:50%;background:white;color:#236b9c;font-size:20px;font-weight:400;transition:transform .2s}
${scope} .faq-list details[open] summary:after{transform:rotate(45deg)}
${scope} .faq-list .faq-answer{padding:0;margin:0;border:0;background:none}
${scope} .faq-list details:not([open])>.faq-answer{display:none}
${scope} .faq-list details[open]>.faq-answer{display:block}
${scope} .faq-help .elementor-button{font-size:inherit;font-weight:inherit;color:inherit}
${scope} .faq-help .elementor-button-content-wrapper:before,${scope} .phone-link .elementor-button-content-wrapper:before{content:'';width:16px;height:16px;display:block;flex-shrink:0;background:currentColor;mask:url('${base}preview-assets/icons/phone.svg') center/contain no-repeat}
${scope} .faq-help .elementor-button-content-wrapper,${scope} .phone-link .elementor-button-content-wrapper{gap:9px}
${scope} .contact-copy{width:auto;flex:1}
${scope} .contact-actions{display:flex;width:auto;flex-direction:column}
${scope} .contact-actions>.elementor-widget{width:auto}
${scope} .contact-actions>.jp-button,${scope} .contact-actions>.jp-button .elementor-button{width:100%}
${scope} .contact-actions .phone-link{color:white}
${scope} .contact-actions small{white-space:nowrap}
${scope} .contact-banner:after{display:block}
body.elementor-page-1261 .site-main{max-width:none;padding:0;margin:0}
body.elementor-page-1261 .entry-content{margin:0}
@media(max-width:1024px){${scope}{--rahmat-layout-gutter:24px;--rahmat-hero-inset:32px}${scope} .step-copy{width:auto}${scope} .contact-copy{width:auto}}
@media(max-width:767px){
${scope}{--rahmat-layout-gutter:20px;--rahmat-hero-inset:20px}
${scope} .benefits .benefit-icon{width:34px;flex-basis:34px}
${scope} .step-number{width:36px;flex-basis:36px}
${scope} .step-arrow{font-size:18px}
${scope} .faq-list .e-n-accordion{gap:10px}
${scope} .faq-list summary:after{width:25px;height:25px}
${scope} .contact-actions{width:100%}
${scope} .contact-actions small{white-space:normal}
${scope} .hero-notes .notes-list{gap:12px}
${scope} .product-arrow .elementor-button-content-wrapper,${scope} .product-arrow .elementor-button-content-wrapper:after{width:13px;height:13px}
${scope} .jp-button .elementor-button-content-wrapper{gap:17px}
}
@media(prefers-reduced-motion:reduce){${scope} .faq-list summary:after{transition:none}}
`;
fs.writeFileSync('preview-assets/icons/arrow.svg',svg('arrow'));
const css = reset + scopeCss(fs.readFileSync('preview-assets/home.css','utf8')) + scopeCss(fs.readFileSync('preview-assets/refined.css','utf8')) + adapter + fs.readFileSync('preview-assets/rahmat-radius.css','utf8');
fs.writeFileSync('preview-assets/rahmat-body.css',css);
const template={version:'0.4',title:'Rahmat — Juhani Homepage',type:'page',content:[container('rahmat-page',[hero,benefits,finder,collection,story,questions,contact],'Juhani homepage body')],page_settings:{hide_title:'yes',template:'elementor_header_footer',custom_css:css}};
fs.writeFileSync('preview-assets/rahmat-elementor.json',JSON.stringify(template,null,2)+'\n');
console.log('Generated native Elementor body with 7 sections and 8 product cards.');
