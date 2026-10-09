const searchToggle = document.querySelector('#search-toggle');
const searchPanel = document.querySelector('#search-panel');
const menuToggle = document.querySelector('#menu-toggle');
const navigation = document.querySelector('#navigation');
function closeSearch() { searchPanel.hidden = true; searchToggle.setAttribute('aria-expanded', 'false'); }
function closeMenu() { navigation.classList.remove('open'); menuToggle.setAttribute('aria-expanded', 'false'); }
searchToggle.addEventListener('click', () => {
  const open = searchPanel.hidden;
  searchPanel.hidden = !open;
  searchToggle.setAttribute('aria-expanded', String(open));
  if (open) { closeMenu(); document.querySelector('#product-search').focus(); }
});
menuToggle.addEventListener('click', () => {
  const open = !navigation.classList.contains('open');
  navigation.classList.toggle('open', open);
  menuToggle.setAttribute('aria-expanded', String(open));
  if (open) closeSearch();
});
navigation.addEventListener('click', event => { if (event.target.closest('a')) closeMenu(); });
document.addEventListener('keydown', event => {
  if (event.key === 'Escape') {
    if (!searchPanel.hidden) { closeSearch(); searchToggle.focus(); }
    if (navigation.classList.contains('open')) { closeMenu(); menuToggle.focus(); }
  }
});
document.addEventListener('click', event => { if (!event.target.closest('.header')) { closeSearch(); closeMenu(); } });

const collectionSearch = document.querySelector('#collection-search');
const filterStatus = document.querySelector('#filter-status');
const productCards = [...document.querySelectorAll('.product-card')];
collectionSearch?.addEventListener('input', () => {
  const query = collectionSearch.value.trim().toLocaleLowerCase();
  let matches = 0;
  productCards.forEach(card => {
    const name = card.querySelector('h3')?.textContent.toLocaleLowerCase() || '';
    card.hidden = !name.includes(query);
    if (!card.hidden) matches++;
  });
  filterStatus.hidden = !query;
  filterStatus.textContent = matches
    ? `${matches} ${matches === 1 ? 'product matches' : 'products match'} your search.`
    : 'No matching nets in this preview collection. Try a different size or browse the full shop.';
});
