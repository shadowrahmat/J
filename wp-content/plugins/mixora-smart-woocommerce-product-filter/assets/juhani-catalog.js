(() => {
  'use strict';
  const initialized = new WeakSet();
  let dialogLocks = 0;
  let previousOverflow = '';
  const normalize = value => String(value).normalize('NFC').toLocaleLowerCase()
    .replace(/[০-৯]/g, digit => String('০১২৩৪৫৬৭৮৯'.indexOf(digit)));

  function init(root) {
    if (initialized.has(root)) return;
    const find = selector => root.querySelector(selector);
    const grid = find('[data-mjc-grid]');
    const panel = find('[data-mjc-panel]');
    const dialog = find('.mjc-dialog');
    if (!grid || !panel || !dialog) return;
    initialized.add(root);
    const minInput = find('[data-mjc-min]');
    const maxInput = find('[data-mjc-max]');
    const search = find('[data-mjc-search]');
    const sort = find('[data-mjc-sort]');
    const checkboxes = [...panel.querySelectorAll('input[data-group]')];
    const chips = find('[data-mjc-active]');
    const openButton = find('[data-mjc-open]');
    const media = window.matchMedia('(max-width: 767px)');
    const position = document.createComment('Mixora filter panel position');
    panel.before(position);
    const cards = [...grid.querySelectorAll('.mjc-product')].map(node => ({
      node, attributes: JSON.parse(node.dataset.attributes || '{}'),
      search: normalize(node.dataset.search || ''),
      prices: JSON.parse(node.dataset.prices || '[]').map(Number),
      min: Number(node.dataset.min), index: Number(node.dataset.index),
      date: Number(node.dataset.date), sales: Number(node.dataset.sales), rating: Number(node.dataset.rating),
    }));
    let locked = false;
    let removalObserver;
    function chip(label, remove) {
      const button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('aria-label', `Remove filter: ${label}`);
      const text = document.createElement('span');
      text.textContent = label;
      button.append(text);
      button.insertAdjacentHTML('beforeend', '<svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true"><path d="m6 6 12 12M18 6 6 18"/></svg>');
      button.addEventListener('click', () => { remove(); update(); });
      chips.append(button);
    }
    function update() {
      const selected = checkboxes.filter(input => input.checked);
      const groups = {};
      selected.forEach(input => (groups[input.dataset.group] ||= []).push(input.value));
      const query = normalize(search?.value || '').trim();
      const min = minInput.value === '' ? 0 : Math.max(0, Number(minInput.value));
      const max = maxInput.value === '' ? Infinity : Math.max(0, Number(maxInput.value));
      const invalid = min > max;
      find('[data-mjc-price-error]').hidden = !invalid;
      minInput.setAttribute('aria-invalid', String(invalid));
      maxInput.setAttribute('aria-invalid', String(invalid));
      const comparator = {
        featured: (a, b) => a.index - b.index,
        newest: (a, b) => b.date - a.date,
        'price-low': (a, b) => a.min - b.min,
        'price-high': (a, b) => b.min - a.min,
        popular: (a, b) => b.sales - a.sales,
        rating: (a, b) => b.rating - a.rating,
      }[sort.value] || ((a, b) => a.index - b.index);
      let count = 0;
      [...cards].sort((a, b) => comparator(a, b) || a.index - b.index).forEach(card => {
        card.node.hidden = invalid || !card.search.includes(query)
          || ((minInput.value !== '' || maxInput.value !== '') && !card.prices.some(price => price >= min && price <= max))
          || !Object.entries(groups).every(([group, values]) => values.some(value => (card.attributes[group] || []).map(String).includes(value)));
        if (!card.node.hidden) count++;
        grid.append(card.node);
      });
      find('[data-mjc-status]').textContent = `${count} of ${cards.length} products`;
      find('[data-mjc-empty]').hidden = count !== 0;
      chips.replaceChildren();
      selected.forEach(input => chip(`${input.dataset.mjcGroupLabel || input.dataset.group}: ${input.value}`, () => { input.checked = false; }));
      if (search?.value.trim()) chip(`Search: ${search.value.trim()}`, () => { search.value = ''; });
      if (minInput.value || maxInput.value) chip(`৳ ${minInput.value || '0'} – ${maxInput.value || '∞'}`, () => { minInput.value = ''; maxInput.value = ''; });
      chips.hidden = chips.children.length === 0;
      const badge = find('.mjc-filter-count');
      const filterCount = selected.length + Number(Boolean(minInput.value || maxInput.value));
      badge.textContent = String(filterCount);
      badge.hidden = filterCount === 0;
    }
    function reset() {
      checkboxes.forEach(input => { input.checked = false; });
      if (search) search.value = '';
      minInput.value = ''; maxInput.value = ''; sort.value = 'featured';
      update();
    }
    function unlock() {
      if (!locked) return;
      locked = false;
      if (--dialogLocks === 0) document.documentElement.style.overflow = previousOverflow;
    }
    function close() {
      if (dialog.open) dialog.close();
      unlock();
      removalObserver?.disconnect();
      openButton.setAttribute('aria-expanded', 'false');
    }
    function sync() {
      close();
      if (!root.isConnected) { media.removeEventListener('change', sync); return; }
      if (media.matches) dialog.append(panel);
      else position.parentNode.insertBefore(panel, position.nextSibling);
    }
    [search, minInput, maxInput].filter(Boolean).forEach(input => input.addEventListener('input', update));
    checkboxes.forEach(input => input.addEventListener('change', update));
    sort.addEventListener('change', update);
    root.querySelectorAll('[data-mjc-reset]').forEach(button => button.addEventListener('click', reset));
    search?.addEventListener('keydown', event => { if (event.key === 'Escape') { search.value = ''; update(); } });
    openButton.addEventListener('click', () => {
      if (!media.matches || dialog.open) return;
      dialog.showModal();
      openButton.setAttribute('aria-expanded', 'true');
      if (dialogLocks++ === 0) {
        previousOverflow = document.documentElement.style.overflow;
        document.documentElement.style.overflow = 'hidden';
      }
      locked = true;
      // Elementor can replace a widget while its mobile preview dialog is open.
      removalObserver = new MutationObserver(() => {
        if (!root.isConnected) { close(); media.removeEventListener('change', sync); }
      });
      removalObserver.observe(document.documentElement, {childList: true, subtree: true});
    });
    root.querySelectorAll('[data-mjc-close],[data-mjc-apply]').forEach(button => button.addEventListener('click', close));
    dialog.addEventListener('click', event => {
      if (event.target !== dialog) return;
      const rect = dialog.getBoundingClientRect();
      if (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom) close();
    });
    dialog.addEventListener('close', () => {
      unlock();
      removalObserver?.disconnect();
      openButton.setAttribute('aria-expanded', 'false');
      if (media.matches && root.isConnected) openButton.focus();
    });
    media.addEventListener('change', sync);
    sync();
    update();
  }
  function scan(scope = document) {
    if (scope.matches?.('.mixora-juhani-catalog')) init(scope);
    scope.querySelectorAll('.mixora-juhani-catalog').forEach(init);
  }
  let editorHookAdded = false;
  function editorHook() {
    if (editorHookAdded || !window.elementorFrontend?.hooks) return;
    editorHookAdded = true;
    window.elementorFrontend.hooks.addAction('frontend/element_ready/mixora_juhani_catalog.default', $scope => scan($scope[0]));
  }
  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', () => { scan(); editorHook(); });
  else { scan(); editorHook(); }
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', editorHook);
})();
