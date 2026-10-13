/* Progressive enhancements for the native WooCommerce Add to Cart widget.
 * WooCommerce remains responsible for variation validation and cart submission. */
(function ($) {
 'use strict';
 function init(scope) {
  const root=scope?.querySelectorAll?scope:document;
  root.querySelectorAll('.juhani-single-purchase form.cart').forEach(form=>{
   if(form.dataset.juhaniSingleReady)return;
   form.dataset.juhaniSingleReady='1';
   const summary=form.closest('.juhani-single-summary');
   const price=summary?.querySelector('.juhani-single-price .price');
   const basePrice=price?.innerHTML;
   const selects=Array.from(form.querySelectorAll('.variations select'));
   const $form=$(form);
   let variants=$form.data('product_variations');
   if(!Array.isArray(variants))variants=[];
   const status=document.createElement('div');
   status.className='juhani-single-selection-status';status.setAttribute('role','status');status.setAttribute('aria-live','polite');
   const variationWrap=form.querySelector('.single_variation_wrap');
   if(selects.length&&variationWrap)variationWrap.insertAdjacentElement('beforebegin',status);
   const qty=form.querySelector('input.qty');
   if(qty&&qty.type!=='hidden') {
    const minus=document.createElement('button'),plus=document.createElement('button');
    minus.type=plus.type='button';minus.className=plus.className='juhani-single-quantity-button';
    minus.textContent='−';plus.textContent='+';minus.setAttribute('aria-label','Decrease quantity');plus.setAttribute('aria-label','Increase quantity');
    qty.before(minus);qty.after(plus);
    const limits=()=>({min:Number(qty.min)||1,max:qty.max&&Number(qty.max)>0?Number(qty.max):Infinity,step:Number(qty.step)||1});
    const buttons=()=>{const {min,max}=limits(),value=Number(qty.value)||min;minus.disabled=value<=min;plus.disabled=value>=max;};
    const normalize=()=>{const {min,max,step}=limits();const value=Number(qty.value)||min;qty.value=Math.min(max,Math.max(min,min+Math.floor((value-min)/step)*step));buttons();};
    minus.addEventListener('click',()=>{normalize();qty.value=Math.max(limits().min,Number(qty.value)-limits().step);$(qty).trigger('change');});
    plus.addEventListener('click',()=>{normalize();qty.value=Math.min(limits().max,Number(qty.value)+limits().step);$(qty).trigger('change');});
    qty.addEventListener('input',buttons);$(qty).on('change',normalize);form.addEventListener('submit',normalize);buttons();
    $form.on('found_variation.juhaniSingle reset_data.juhaniSingle',()=>setTimeout(buttons,0));
   }
   const amount=value=>new Intl.NumberFormat('en-BD',{maximumFractionDigits:Number.isInteger(value)?0:(window.juhaniSingleProductSettings?.decimals??2)}).format(value);
   function progress() {
    const chosen=selects.filter(s=>s.value),remaining=selects.length-chosen.length;
    status.classList.remove('is-ready');
    status.textContent=chosen.length?`আর ${remaining}টি option বেছে নিন। নির্বাচিত option আবার click করে সরাতে পারেন।`:'সবগুলো option বেছে নিলে নির্দিষ্ট দাম ও availability দেখাবে।';
    if(price&&remaining) {
     const possible=variants.filter(v=>v.is_in_stock&&v.is_purchasable&&v.variation_is_active&&v.variation_is_visible&&chosen.every(s=>!v.attributes[s.name]||v.attributes[s.name]===s.value));
     if(chosen.length&&possible.length) {
      const values=possible.map(v=>Number(v.display_price)),min=Math.min(...values),max=Math.max(...values);
      // Keep WooCommerce's actual currency markup instead of guessing a symbol.
      const currency=price.querySelector('.woocommerce-Price-currencySymbol')?.outerHTML||'';
      price.innerHTML=`<span class="woocommerce-Price-amount amount"><bdi>${amount(min)}${currency}</bdi></span>${min!==max?' – <span class="woocommerce-Price-amount amount"><bdi>'+amount(max)+currency+'</bdi></span>':''}`;
     }else price.innerHTML=basePrice;
    }
   }
   $form.on('woocommerce_variation_select_change.juhaniSingle',progress);
   $form.on('found_variation.juhaniSingle',function(event,variation){
    if(price) {
     if(variation.price_html)price.innerHTML=variation.price_html;
     else {const currency=price.querySelector('.woocommerce-Price-currencySymbol')?.outerHTML||'';price.innerHTML=`<span class="woocommerce-Price-amount amount"><bdi>${amount(Number(variation.display_price))}${currency}</bdi></span>`;}
    }
    if(variation.is_in_stock&&variation.is_purchasable&&variation.variation_is_active) {
     status.textContent=selects.map(s=>s.options[s.selectedIndex]?.text||'').join(' · ')+' — এই বিকল্পটি কার্টে যোগ করতে পারেন।';status.classList.add('is-ready');
    }else status.textContent='এই বিকল্পটি এখন পাওয়া যাচ্ছে না। অন্য option বেছে নিন।';
   });
   $form.on('reset_data.juhaniSingle hide_variation.juhaniSingle',progress);
   // WooCommerce handles unavailable choices, selected swatches and submit validation.
   if(selects.length) {progress();$form.trigger('check_variations');}
  });
 }
 $(function(){init(document);});
 $(window).on('elementor/frontend/init',function(){
  window.elementorFrontend.hooks.addAction('frontend/element_ready/woocommerce-product-add-to-cart.default',function($scope){init($scope[0]);});
 });
})(jQuery);
