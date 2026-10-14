(() => {
    const config=window.juhaniOrderDesign;
    if(!config)return;
    const $=window.jQuery;
    let opener=null,savedOverflow='',closeTimer=null,quantityBusy=false,cartTimer=null;
    const drawer=()=>document.querySelector('dialog.juhani-cart-drawer');
    function open(trigger){const modal=drawer();if(!modal)return;clearTimeout(closeTimer);modal.classList.remove('is-closing');opener=trigger||opener;if(!modal.open){savedOverflow=document.body.style.overflow;modal.showModal();document.body.style.overflow='hidden';}opener?.setAttribute('aria-expanded','true');modal.querySelector('#juhani-order-drawer-title')?.focus({preventScroll:true});}
    function close(){const modal=drawer();if(!modal?.open||modal.classList.contains('is-closing'))return;modal.classList.add('is-closing');closeTimer=setTimeout(()=>{modal.close();modal.classList.remove('is-closing');document.body.style.overflow=savedOverflow;opener?.setAttribute('aria-expanded','false');opener?.focus({preventScroll:true});},matchMedia('(prefers-reduced-motion:reduce)').matches?0:200);}
    function showError(message){const modal=drawer();if(modal?.open){let notice=modal.querySelector('.juhani-drawer-error');if(!notice){notice=document.createElement('p');notice.className='juhani-drawer-error';notice.setAttribute('role','alert');modal.querySelector('.jdp-scroll')?.prepend(notice);}notice.textContent=message;return;}const target=document.querySelector('.juhani-order-flow .woocommerce');if(target){const notice=document.createElement('ul');notice.className='woocommerce-error';notice.setAttribute('role','alert');const item=document.createElement('li');item.textContent=message;notice.append(item);target.prepend(notice);notice.scrollIntoView({block:'center'});}else window.alert(message);}
    async function quantity(key,value){
        if(quantityBusy)return;
        quantityBusy=true;document.querySelectorAll('[data-order-step],[data-order-remove],[data-order-quantity]').forEach(node=>node.disabled=true);
        try {
            const body=new URLSearchParams({action:'juhani_order_quantity',nonce:config.nonce,cart_item_key:key,quantity:String(value)});
            const response=await fetch(config.ajax,{method:'POST',body,credentials:'same-origin'}),result=await response.json();
            if(!response.ok||result.success===false)throw new Error(result.data?.message||'Unable to update this item. Please try again.');
            Object.entries(result.fragments||{}).forEach(([selector,html])=>$(selector).replaceWith(html));
            $(document.body).trigger('wc_fragments_refreshed');
            if(config.cart)$(document).trigger('wc_update_cart');
            if(config.checkout)$(document.body).trigger('update_checkout');
        } catch(error){showError(error.message);$(document.body).trigger('wc_fragment_refresh');}
        finally{quantityBusy=false;document.querySelectorAll('[data-order-step],[data-order-remove],[data-order-quantity]').forEach(node=>node.disabled=false);}
    }
    document.addEventListener('click',event=>{
        const trigger=event.target.closest('.juhani-cart-drawer .elementor-menu-cart__toggle, .juhani-cart-drawer-trigger, a[href="#juhani-mobile-cart"]');
        if(trigger&&!trigger.closest('dialog')){event.preventDefault();event.stopImmediatePropagation();open(trigger);return;}
        if(event.target.closest('[data-close-order-drawer]')){event.preventDefault();close();return;}
        if(event.target.closest('dialog [aria-disabled="true"]')){event.preventDefault();return;}
        const remove=event.target.closest('[data-order-remove]');if(remove){event.preventDefault();quantity(remove.dataset.orderRemove,0);return;}
        const step=event.target.closest('[data-order-step]');if(step){event.preventDefault();quantity(step.dataset.cartKey,Math.max(1,Number(step.dataset.quantity)+Number(step.dataset.orderStep)));return;}
        const cartStep=event.target.closest('.juhani-order-flow [data-cart-step]');if(cartStep){event.preventDefault();const input=cartStep.closest('.jop-quantity').querySelector('input.qty');const max=Number(input.max)||Infinity;input.value=Math.max(1,Math.min(max,Number(input.value||1)+Number(cartStep.dataset.cartStep)));$(input).trigger('change');}
    },true);
    document.addEventListener('change',event=>{const input=event.target.closest('[data-order-quantity]');if(input)quantity(input.dataset.orderQuantity,Math.max(1,Math.trunc(Number(input.value))||1));});
    $(document).on('change.juhaniOrder','.juhani-order-flow .woocommerce-cart-form input.qty',function(){const form=$(this).closest('form');form.find('[name="update_cart"]').prop('disabled',false);if(!form.find('input[type="hidden"][name="update_cart"]').length)form.append('<input type="hidden" name="update_cart" value="Update Cart">');clearTimeout(cartTimer);cartTimer=setTimeout(()=>form.trigger('submit'),350);});
    function bind(){const modal=drawer();if(!modal||modal.dataset.bound)return;modal.dataset.bound='1';modal.addEventListener('cancel',event=>{event.preventDefault();close();});modal.addEventListener('click',event=>{if(event.target!==modal)return;const r=modal.getBoundingClientRect();if(event.clientX<r.left||event.clientX>r.right||event.clientY<r.top||event.clientY>r.bottom)close();});}
    bind();if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',bind);
    $(document.body).on('updated_checkout.juhaniOrder payment_method_selected.juhaniOrder country_to_state_changed.juhaniOrder',()=>{const phoneLabel=document.querySelector('.juhani-order-flow #billing_phone_field label');if(phoneLabel?.firstChild?.nodeType===3)phoneLabel.firstChild.textContent='মোবাইল নম্বর '; const button=document.querySelector('.juhani-order-flow #place_order');if(button&&!button.querySelector('.juhani-place-order-total')){const label=button.textContent;const amount=document.querySelector('.juhani-order-flow .order-total .amount');button.innerHTML='';const text=document.createElement('span');text.textContent=label;button.append(text);if(amount){const total=document.createElement('span');total.className='juhani-place-order-total';total.innerHTML=amount.outerHTML+' →';button.append(total);}}});
})();
