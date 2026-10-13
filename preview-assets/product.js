(() => {
  const root = document.querySelector('.juhani-product-preview');
  if (!root) return;
  const data = JSON.parse(root.querySelector('.juhani-product-variation-data').textContent);
  const form = root.querySelector('.juhani-product-options');
  const groups = [...form.querySelectorAll('.juhani-product-option-group')].map(node => ({ node, key: node.dataset.attribute, select: node.querySelector('select'), buttons: [...node.querySelectorAll('.juhani-product-option')] }));
  const variants = data.variants.filter(v => v.active && v.visible && v.purchasable && v.stock);
  const quantity = form.querySelector('[name="quantity"]');
  const variationId = form.querySelector('[name="variation_id"]');
  const order = form.querySelector('.juhani-product-order-button');
  const minus = form.querySelector('.juhani-product-quantity-minus');
  const plus = form.querySelector('.juhani-product-quantity-plus');
  const price = root.querySelector('.juhani-product-price');
  const priceNote = root.querySelector('#juhani-product-price-note');
  const status = form.querySelector('.juhani-product-selection-status');
  const sku = root.querySelector('#juhani-product-sku');
  const mainImage = root.querySelector('.juhani-product-main-photo>img');
  const thumbs = [...root.querySelectorAll('.juhani-product-thumbnail')];
  const count = root.querySelector('.juhani-product-photo-count');
  const dialog = root.querySelector('.juhani-product-lightbox');
  const expand = root.querySelector('.juhani-product-expand');
  const defaultPhoto = { image: mainImage.src, full: mainImage.dataset.full, alt: mainImage.alt };
  const groupNames = { attribute_pa_haat: 'Haat', attribute_pa_suta: 'Suta', 'attribute_pa_making-type': 'Making', 'attribute_pa_git-type': 'Git', 'attribute_pa_rock-weight-kg': 'kg' };
  let selectedVariant = null;
  let activePhoto = 0;
  let variationPhotoApplied = false;
  const money = value => new Intl.NumberFormat('en-BD', { style: 'currency', currency: data.currency, maximumFractionDigits: Number.isInteger(value) ? 0 : data.decimals ?? 2 }).format(value);
  const matches = (variant, values) => Object.entries(values).every(([key, value]) => !value || !variant.attributes[key] || variant.attributes[key] === value);
  function quantityButtons() {
    const min = Number(quantity.min) || 1;
    const max = quantity.max ? Number(quantity.max) : Infinity;
    minus.disabled = Number(quantity.value) <= min;
    plus.disabled = Number(quantity.value) >= max;
  }
  function limitQuantity() {
    const min = Number(quantity.min) || 1;
    const max = quantity.max ? Number(quantity.max) : Infinity;
    const value = Number(quantity.value);
    quantity.value = String(Math.min(max, Math.max(min, Number.isFinite(value) ? Math.floor(value) : min)));
    quantityButtons();
  }
  function showPhoto(photo, index) {
    mainImage.src = photo.image; mainImage.dataset.full = photo.full; mainImage.alt = photo.alt;
    activePhoto = index;
    thumbs.forEach((thumb, i) => { const active = i === index; thumb.classList.toggle('is-active', active); thumb.setAttribute('aria-pressed', String(active)); });
    count.textContent = index >= 0 ? `${String(index + 1).padStart(2, '0')} / ${String(thumbs.length).padStart(2, '0')}` : 'VARIANT';
    if (dialog.open) { const image = dialog.querySelector('img'); image.src = photo.full; image.alt = photo.alt; }
  }
  function update() {
    const values = Object.fromEntries(groups.map(group => [group.key, group.select.value]));
    groups.forEach(group => {
      const others = { ...values }; delete others[group.key];
      group.buttons.forEach(button => {
        const active = values[group.key] === button.dataset.value;
        button.classList.toggle('is-selected', active);
        button.setAttribute('aria-pressed', String(active));
        button.disabled = !variants.some(v => matches(v, { ...others, [group.key]: button.dataset.value }));
      });
      [...group.select.options].forEach(option => { if (option.value) option.disabled = !variants.some(v => matches(v, { ...others, [group.key]: option.value })); });
    });
    const complete = groups.every(group => group.select.value);
    selectedVariant = complete ? variants.find(v => matches(v, values)) || null : null;
    variationId.value = selectedVariant ? String(selectedVariant.id) : '0';
    order.disabled = !selectedVariant;
    status.classList.toggle('is-ready', Boolean(selectedVariant));
    const message = status.querySelector('span');
    if (selectedVariant) {
      price.textContent = money(selectedVariant.price);
      priceNote.textContent = 'নির্বাচিত বিকল্পের মূল্য';
      const choices = groups.map(group => {
        const label = [...group.select.options].find(option => option.value === group.select.value)?.textContent || group.select.value;
        return `${groupNames[group.key] || group.node.getAttribute('aria-label')}: ${label}`;
      });
      message.textContent = `${choices.join(' · ')} — কার্টে যোগ করার জন্য প্রস্তুত।`;
      sku.textContent = selectedVariant.sku || data.base_sku || '—';
      quantity.min = String(selectedVariant.min_qty || 1);
      quantity.max = selectedVariant.sold_individually || data.sold_individually ? '1' : selectedVariant.max_qty && Number(selectedVariant.max_qty) > 0 ? String(selectedVariant.max_qty) : '';
      if (selectedVariant.image && selectedVariant.image !== defaultPhoto.full) {
        const index = thumbs.findIndex(thumb => thumb.dataset.full === selectedVariant.image);
        showPhoto({ image: selectedVariant.image, full: selectedVariant.image, alt: defaultPhoto.alt }, index);
        variationPhotoApplied = true;
      } else if (variationPhotoApplied) {
        showPhoto(defaultPhoto, 0); variationPhotoApplied = false;
      }
    } else {
      const candidates = variants.filter(variant => matches(variant, values));
      const selectedCount = groups.filter(group => group.select.value).length;
      if (selectedCount && candidates.length) {
        const prices = candidates.map(variant => Number(variant.price));
        const min = Math.min(...prices), max = Math.max(...prices);
        price.textContent = min === max ? money(min) : `${money(min)} – ${money(max)}`;
        priceNote.textContent = 'আপনার বর্তমান বাছাইয়ের মূল্যসীমা';
      } else {
        price.innerHTML = data.initial_price_html;
        priceNote.textContent = 'বিকল্প অনুযায়ী দাম পরিবর্তিত হবে।';
      }
      message.textContent = !variants.length ? 'এই পণ্যের বিকল্পগুলো এখন স্টকে নেই। সাহায্যের জন্য আমাদের সঙ্গে কথা বলুন।' : complete || (selectedCount && !candidates.length) ? 'এই combination এখন পাওয়া যাচ্ছে না। অন্য বিকল্প বেছে নিন।' : selectedCount ? `আর ${groups.length - selectedCount}টি option বেছে নিন। নির্বাচিত option আবার click করলে সেটি সরবে।` : 'সবগুলো option বেছে নিলে নির্বাচিত জালের দাম ও availability দেখাবে।';
      sku.textContent = data.base_sku || '—';
      quantity.min = '1'; quantity.max = data.sold_individually ? '1' : '';
      if (variationPhotoApplied) { showPhoto(defaultPhoto, 0); variationPhotoApplied = false; }
    }
    limitQuantity();
  }
  form.classList.add('is-enhanced');
  groups.forEach(group => {
    group.select.addEventListener('change', update);
    group.buttons.forEach(button => button.addEventListener('click', () => { if (!button.disabled) { group.select.value = group.select.value === button.dataset.value ? '' : button.dataset.value; update(); } }));
  });
  form.querySelector('.juhani-product-reset').addEventListener('click', () => { groups.forEach(group => { group.select.value = ''; }); update(); });
  minus.addEventListener('click', () => { quantity.value = String(Number(quantity.value) - 1); limitQuantity(); });
  plus.addEventListener('click', () => { quantity.value = String(Number(quantity.value) + 1); limitQuantity(); });
  quantity.addEventListener('input', quantityButtons);
  quantity.addEventListener('change', limitQuantity);
  form.addEventListener('submit', event => {
    limitQuantity();
    if (!selectedVariant) { event.preventDefault(); groups.find(group => !group.select.value)?.buttons.find(button => !button.disabled)?.focus(); }
  });
  update();
  function selectPhoto(index) { showPhoto(thumbs[index].dataset, index); variationPhotoApplied = false; }
  thumbs.forEach((button, index) => {
    button.addEventListener('click', () => selectPhoto(index));
    button.addEventListener('keydown', event => {
      let next; if (event.key === 'ArrowRight') next = (index + 1) % thumbs.length; if (event.key === 'ArrowLeft') next = (index + thumbs.length - 1) % thumbs.length; if (event.key === 'Home') next = 0; if (event.key === 'End') next = thumbs.length - 1;
      if (next !== undefined) { event.preventDefault(); selectPhoto(next); thumbs[next].focus(); }
    });
  });
  expand.addEventListener('click', () => { const image = dialog.querySelector('img'); image.src = mainImage.dataset.full; image.alt = mainImage.alt; dialog.showModal(); });
  dialog.querySelector('button').addEventListener('click', () => dialog.close());
  dialog.addEventListener('close', () => expand.focus());
  dialog.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); } });
  dialog.addEventListener('keydown', event => {
    if (event.key === 'ArrowRight' || event.key === 'ArrowLeft') { event.preventDefault(); const next = activePhoto < 0 ? (event.key === 'ArrowRight' ? 0 : thumbs.length - 1) : (activePhoto + (event.key === 'ArrowRight' ? 1 : thumbs.length - 1)) % thumbs.length; selectPhoto(next); }
  });
  const tabs = [...root.querySelectorAll('.juhani-product-tab')];
  function selectTab(button) {
    tabs.forEach(tab => { const active = tab === button; tab.classList.toggle('is-active', active); tab.setAttribute('aria-selected', String(active)); tab.tabIndex = active ? 0 : -1; root.querySelector('#' + tab.getAttribute('aria-controls')).hidden = !active; });
  }
  tabs.forEach((button, index) => {
    button.addEventListener('click', () => selectTab(button));
    button.addEventListener('keydown', event => {
      let next; if (event.key === 'ArrowRight') next = (index + 1) % tabs.length; if (event.key === 'ArrowLeft') next = (index + tabs.length - 1) % tabs.length; if (event.key === 'Home') next = 0; if (event.key === 'End') next = tabs.length - 1;
      if (next !== undefined) { event.preventDefault(); selectTab(tabs[next]); tabs[next].focus(); }
    });
  });
  root.querySelector('.juhani-product-preview-picker select')?.addEventListener('change', event => {
    const picker = event.currentTarget;
    window.location.assign(`${picker.dataset.previewUrl}?product_id=${encodeURIComponent(picker.value)}`);
  });
})();
