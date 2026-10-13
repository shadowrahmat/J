(() => {
  if (window.elementorFrontend?.isEditMode?.()) return;
  const root = document.querySelector('.elementor-1278') || document.querySelector('.jsp-preview');
  if (!root) return;
  const search = root.querySelector('#jsp-search');
  const minInput = root.querySelector('#jsp-price-min');
  const maxInput = root.querySelector('#jsp-price-max');
  const sort = root.querySelector('#jsp-sort');
  const checkboxes = [...root.querySelectorAll('.jsp-filter-options input')];
  const grid = root.querySelector('#jsp-product-grid');
  const cards = [...grid.querySelectorAll('.jsp-product')].map(node => ({
    node, attributes: JSON.parse(node.dataset.attributes),
    search: node.dataset.search.normalize('NFC').toLocaleLowerCase(),
    min: Number(node.dataset.min), max: Number(node.dataset.max),
    prices: JSON.parse(node.dataset.prices).map(Number),
    index: Number(node.dataset.index), date: Number(node.dataset.date),
    sales: Number(node.dataset.sales), rating: Number(node.dataset.rating),
  }));
  const activeFilters = root.querySelector('#jsp-active-filters');
  const nativePanel = root.querySelector('[data-jsp-filter-panel]');
  let dialog = root.querySelector('#jsp-filter-dialog');
  let panelPosition;
  if (nativePanel) {
    panelPosition = document.createComment('Editable filter panel position');
    nativePanel.parentNode.insertBefore(panelPosition, nativePanel);
    dialog = document.createElement('dialog');
    dialog.className = 'jsp-native-dialog';
    dialog.setAttribute('aria-labelledby', 'jsp-filter-title');
    dialog.setAttribute('lang', 'bn');
    root.append(dialog);
  }
  const openButton = root.querySelector('#jsp-open-filters');
  const media = window.matchMedia('(max-width: 767px)');
  const closeIcon = '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>';
  const groupLabels = { ghor: 'Ghor', pa_haat: 'Haat', pa_suta: 'Suta', 'pa_making-type': 'Making', 'pa_git-type': 'Git', 'pa_rock-weight-kg': 'Weight', category: 'Category' };
  function addChip(label, remove) {
    const button = document.createElement('button');
    button.type = 'button';
    const text = document.createElement('span');
    text.textContent = label;
    button.append(text);
    button.insertAdjacentHTML('beforeend', closeIcon);
    button.setAttribute('aria-label', `Remove filter: ${label}`);
    button.addEventListener('click', () => { remove(); update(); });
    activeFilters.append(button);
  }
  function update() {
    const groups = {};
    const selected = checkboxes.filter(input => input.checked);
    selected.forEach(input => (groups[input.dataset.group] ||= []).push(input.value));
    const query = (search?.value || '').normalize('NFC').trim().toLocaleLowerCase().replace(/[০-৯]/g, digit => String('০১২৩৪৫৬৭৮৯'.indexOf(digit)));
    const min = minInput.value === '' ? 0 : Math.max(0, Number(minInput.value));
    const max = maxInput.value === '' ? Infinity : Math.max(0, Number(maxInput.value));
    const invalid = min > max;
    root.querySelector('#jsp-price-error').hidden = !invalid;
    minInput.setAttribute('aria-invalid', String(invalid));
    maxInput.setAttribute('aria-invalid', String(invalid));
    const comparator = {
      featured: (a, b) => a.index - b.index,
      newest: (a, b) => b.date - a.date,
      'price-low': (a, b) => a.min - b.min,
      'price-high': (a, b) => b.min - a.min,
      popular: (a, b) => b.sales - a.sales,
      rating: (a, b) => b.rating - a.rating,
    }[sort.value];
    let count = 0;
    [...cards].sort((a, b) => comparator(a, b) || a.index - b.index).forEach(card => {
      card.node.hidden = invalid || !card.search.includes(query) || !card.prices.some(price => price >= min && price <= max) || !Object.entries(groups).every(([group, values]) => values.some(value => (card.attributes[group] || []).map(String).includes(value)));
      if (!card.node.hidden) count++;
      grid.append(card.node);
    });
    root.querySelector('#jsp-result-status').textContent = `${count} of ${cards.length} products`;
    root.querySelector('#jsp-empty').hidden = count !== 0;
    activeFilters.replaceChildren();
    selected.forEach(input => addChip(`${groupLabels[input.dataset.group]}: ${input.value}`, () => { input.checked = false; }));
    if (search?.value.trim()) addChip(`Search: ${search.value.trim()}`, () => { search.value = ''; });
    if (minInput.value || maxInput.value) addChip(`৳ ${minInput.value || '0'} – ${maxInput.value || '∞'}`, () => { minInput.value = ''; maxInput.value = ''; });
    activeFilters.hidden = activeFilters.children.length === 0;
    const badge = root.querySelector('#jsp-filter-count');
    if (badge) {
      const filterCount = selected.length + Number(Boolean(minInput.value || maxInput.value));
      badge.textContent = String(filterCount);
      badge.hidden = filterCount === 0;
    }
  }
  function reset() {
    checkboxes.forEach(input => { input.checked = false; });
    if (search) search.value = '';
    minInput.value = ''; maxInput.value = ''; sort.value = 'featured';
    update();
  }
  [search, minInput, maxInput].filter(Boolean).forEach(input => input.addEventListener('input', update));
  checkboxes.forEach(input => input.addEventListener('change', update));
  sort.addEventListener('change', update);
  root.querySelectorAll('.jsp-reset').forEach(button => button.addEventListener('click', event => { event.preventDefault(); reset(); }));
  search?.addEventListener('keydown', event => { if (event.key === 'Escape') { search.value = ''; update(); } });
  function syncDialog() {
    if (dialog.open) dialog.close();
    if (nativePanel) {
      if (media.matches) dialog.append(nativePanel);
      else panelPosition.parentNode.insertBefore(nativePanel, panelPosition);
    } else if (!media.matches) dialog.show();
  }
  const focusTrigger = () => (openButton.querySelector?.('a,button') || openButton).focus();
  openButton.addEventListener('click', event => { event.preventDefault(); if (media.matches && !dialog.open) dialog.showModal(); });
  ['#jsp-close-filters', '#jsp-apply-filters'].forEach(selector => root.querySelector(selector).addEventListener('click', event => { event.preventDefault(); dialog.close(); focusTrigger(); }));
  dialog.addEventListener('click', event => { if (event.target === dialog) { const rect = dialog.getBoundingClientRect(); if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) dialog.close(); } });
  dialog.addEventListener('close', () => { if (media.matches) focusTrigger(); });
  media.addEventListener('change', syncDialog);
  syncDialog();
  update();
})();
