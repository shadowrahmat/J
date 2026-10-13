/* Enhance only Search widgets with the manually assigned live-search class. */
(() => {
 const bindings=new WeakMap();
 const searchTerm=value=>value.trim().replace(/[০-৯]/g,digit=>String('০১২৩৪৫৬৭৮৯'.indexOf(digit)));
 function init(root){
  const results=root.querySelector('.e-search-results'),output=root.querySelector('.e-search-results-container'),input=root.querySelector('.e-search-input'),form=root.querySelector('form');
  if(!results||!output||!input||!form)return;
  const previous=bindings.get(root);
  if(previous?.results===results&&previous.output===output&&previous.input===input)return;
  previous?.dispose();root.dataset.juhaniSearchReady='1';
  let heading=output.querySelector('.juhani-live-search-heading'),footer=output.querySelector('.juhani-live-search-footer');
  if(!heading||!footer){heading?.remove();footer?.remove();heading=footer=null;}
  function update(){
   const cards=[...results.querySelectorAll('.juhani-search-result-card')];
   const empty=!!results.querySelector('.e-search-nothing-found-message');
   if(!heading&&!(cards.length||empty))return;
   if(!heading){
    heading=document.createElement('div');heading.className='juhani-live-search-heading';
    const label=document.createElement('span');label.className='juhani-live-search-heading-label';label.textContent='PRODUCTS';
    const count=document.createElement('span');count.className='juhani-live-search-count';heading.append(label,count);
    output.insertBefore(heading,results);
    footer=document.createElement('div');footer.className='juhani-live-search-footer';
    const hint=document.createElement('span');hint.className='juhani-live-search-keyboard';hint.innerHTML='<kbd>↓</kbd><span>Browse results</span>';
    const link=document.createElement('a');link.className='juhani-live-search-view-all';
    link.innerHTML='<span>সব ফলাফল দেখুন</span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>';
    footer.append(hint,link);output.append(footer);
   }
   heading.hidden=footer.hidden=!(cards.length||empty);
   heading.querySelector('.juhani-live-search-count').textContent=`${cards.length} ${cards.length===1?'result':'results'}`;
   const url=new URL(form.action||window.location.origin,window.location.href);url.searchParams.set('s',searchTerm(input.value));url.searchParams.set('post_type','product');
   footer.querySelector('a').href=url.href;
   cards.forEach(card=>{if(card.dataset.juhaniKeys)return;card.dataset.juhaniKeys='1';card.addEventListener('keydown',event=>{
    if(event.key==='Escape'){event.preventDefault();input.focus();return;}
    if(!['ArrowDown','ArrowUp','Home','End'].includes(event.key))return;event.preventDefault();const items=[...root.querySelectorAll('.e-search-results .juhani-search-result-card')],index=items.indexOf(card);const next=event.key==='Home'?0:event.key==='End'?items.length-1:(index+(event.key==='ArrowDown'?1:-1)+items.length)%items.length;items[next]?.focus();
   });});
  }
  const onKey=event=>{if(event.key==='ArrowDown'){const first=results.querySelector('.juhani-search-result-card');if(first){event.preventDefault();first.focus();}}};
  const onInput=()=>{if(footer){const url=new URL(form.action||window.location.origin,window.location.href);url.searchParams.set('s',searchTerm(input.value));url.searchParams.set('post_type','product');footer.querySelector('a').href=url.href;}};
  const onSubmit=()=>{input.value=searchTerm(input.value);};
  input.addEventListener('keydown',onKey);input.addEventListener('input',onInput);form.addEventListener('submit',onSubmit);
  const observer=new MutationObserver(update);observer.observe(results,{childList:true,subtree:true});
  bindings.set(root,{results,output,input,dispose:()=>{observer.disconnect();input.removeEventListener('keydown',onKey);input.removeEventListener('input',onInput);form.removeEventListener('submit',onSubmit);}});
  update();
 }
 const scan=()=>document.querySelectorAll('.juhani-live-product-search').forEach(init);
 if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',scan);else scan();
 window.addEventListener('load',scan);
 // Popup markup can arrive after both its show event and widget-ready callbacks.
 // Rebind if Elementor replaces a popup's original result container.
 const observePopups=()=>new MutationObserver(records=>{
  for(const record of records){
   const parent=record.target.nodeType===1?record.target.closest('.juhani-live-product-search'):null;
   if(parent)init(parent);
   for(const node of record.addedNodes){
    if(node.nodeType!==1)continue;
    if(node.matches('.juhani-live-product-search'))init(node);
    node.querySelectorAll('.juhani-live-product-search').forEach(init);
   }
  }
 }).observe(document.body,{childList:true,subtree:true});
 if(document.body)observePopups();else document.addEventListener('DOMContentLoaded',observePopups);
 if(window.jQuery){
  jQuery(document).on('elementor/popup/show',()=>{scan();requestAnimationFrame(scan);});
  jQuery(window).on('elementor/frontend/init',()=>{
   window.elementorFrontend.hooks.addAction('frontend/element_ready/search.default',$scope=>{
    if($scope[0]?.classList.contains('juhani-live-product-search'))init($scope[0]);
   });
  });
 }
})();
