(() => {
 function initialize() {
  const root = document.querySelector('.elementor-1274') || document.querySelector('.jb-preview');
  if (!root) return;
  const search = root.querySelector('#jb-search');
  const filters = [...root.querySelectorAll('.jb-filter')];
  const cards = [...root.querySelectorAll('.jb-article')];
  const status = root.querySelector('#jb-result-status');
  const empty = root.querySelector('#jb-empty');
  if (!search || !cards.length || root.dataset.jbReady === 'true') return;
  root.dataset.jbReady = 'true';
  let selected = 'all';
  const normalize = value => value.normalize('NFC').trim().toLocaleLowerCase();
  const bangla = value => String(value).replace(/[0-9]/g, digit => '০১২৩৪৫৬৭৮৯'[Number(digit)]);
  function update() {
    const query = normalize(search.value);
    let count = 0;
    cards.forEach(card => {
      card.hidden = !((selected === 'all' || card.dataset.topic === selected) && normalize(card.dataset.search).includes(query));
      if (!card.hidden) count++;
    });
    filters.forEach(button => {
      const active = button.dataset.topic === selected;
      button.classList.toggle('is-active', active);
      button.setAttribute('aria-pressed', String(active));
    });
    if (empty) empty.hidden = count !== 0;
    if (status) status.textContent = `${bangla(count)}টি নিবন্ধ দেখানো হচ্ছে`;
  }
  filters.forEach(button => button.addEventListener('click', () => { selected = button.dataset.topic; update(); }));
  search.addEventListener('input', update);
  search.addEventListener('keydown', event => { if (event.key === 'Escape') { search.value = ''; update(); } });
  root.querySelector('#jb-reset')?.addEventListener('click', () => { selected = 'all'; search.value = ''; update(); search.focus(); });
  update();
 }
 if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', initialize, { once: true });
 else initialize();
 if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', () => {
   window.elementorFrontend?.hooks.addAction('frontend/element_ready/global', initialize);
 });
})();
