(() => {
 const root=document.querySelector('.juhani-search-preview');if(!root)return;
 const input=root.querySelector('.juhani-search-preview-input');
 const cards=[...root.querySelectorAll('.juhani-search-preview-card')];
 const clear=root.querySelector('.juhani-search-preview-clear');
 const count=root.querySelector('.juhani-search-preview-count');
 const empty=root.querySelector('.juhani-search-preview-empty');
 const viewAll=root.querySelector('.juhani-search-preview-view-all');
 const normalize=value=>value.normalize('NFC').toLocaleLowerCase().replace(/[০-৯]/g,digit=>String('০১২৩৪৫৬৭৮৯'.indexOf(digit))).replace(/[\s\p{P}]+/gu,' ').trim();
 function update(){
  const words=normalize(input.value).split(' ').filter(Boolean);let visible=0;
  cards.forEach(card=>{const text=normalize(card.dataset.search);card.hidden=!words.every(word=>text.includes(word));if(!card.hidden)visible++;});
  count.textContent=`${visible} ${visible===1?'result':'results'}`;empty.hidden=visible!==0;clear.disabled=!input.value;
  const url=new URL(viewAll.dataset.searchUrl,window.location.href);url.searchParams.set('s',input.value.trim());url.searchParams.set('post_type','product');viewAll.href=url.href;
  root.querySelector('.juhani-search-preview-results').scrollTop=0;
 }
 input.addEventListener('input',update);
 root.querySelector('.juhani-search-preview-form').addEventListener('submit',event=>{event.preventDefault();update();});
 clear.addEventListener('click',()=>{input.value='';update();input.focus();});
 root.querySelector('.juhani-search-preview-show-all').addEventListener('click',()=>{input.value='';update();input.focus();});
 root.querySelectorAll('[data-search-query]').forEach(button=>button.addEventListener('click',()=>{input.value=button.dataset.searchQuery;update();input.focus();}));
 input.addEventListener('keydown',event=>{if(event.key==='ArrowDown'){event.preventDefault();cards.find(card=>!card.hidden)?.focus();}if(event.key==='Escape'){input.value='';update();}});
 cards.forEach(card=>card.addEventListener('keydown',event=>{if(event.key==='Escape'){event.preventDefault();input.focus();return;}if(!['ArrowDown','ArrowUp','Home','End'].includes(event.key))return;event.preventDefault();const visible=cards.filter(c=>!c.hidden),index=visible.indexOf(card),next=event.key==='Home'?0:event.key==='End'?visible.length-1:(index+(event.key==='ArrowDown'?1:-1)+visible.length)%visible.length;visible[next]?.focus();}));
 update();
})();
