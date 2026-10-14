/* Keep Elementor's accordion events and controls; correct this homepage's height targets. */
(() => {
  async function initialize() {
    const manager = window.elementorFrontend?.elementsHandler;
    if (!manager?.getHandler || !document.querySelector('.elementor-1261 .faq-list')) return;
    const Handler = await manager.getHandler('nested-accordion.default');
    const prototype = Handler?.prototype;
    if (!prototype?.animateItem || prototype.juhaniFaqHeightFixed) return;
    const original = prototype.animateItem;
    prototype.animateItem = function (item, startHeight, endHeight, isOpen) {
      if (!item.closest('.elementor-1261 .faq-list')) {
        return original.call(this, item, startHeight, endHeight, isOpen);
      }
      // Capture the displayed height before cancelling an interrupted animation.
      const current = item.getBoundingClientRect().height;
      this.animations.get(item)?.cancel();
      const style = window.getComputedStyle(item);
      const pixels = property => parseFloat(style[property]) || 0;
      const outer = pixels('paddingTop') + pixels('paddingBottom') + pixels('borderTopWidth') + pixels('borderBottomWidth');
      const contentBox = style.boxSizing !== 'border-box';
      let target;
      if (isOpen) {
        // Measuring the natural box includes padding, borders and collapsed text margins.
        item.style.height = 'auto';
        target = item.getBoundingClientRect().height;
      } else {
        const summary = item.querySelector('summary');
        target = summary.getBoundingClientRect().height + outer;
      }
      const from = Math.max(0, current - (contentBox ? outer : 0));
      const to = Math.max(0, target - (contentBox ? outer : 0));
      item.style.height = `${from}px`;
      original.call(this, item, `${from}px`, `${to}px`, isOpen);
      const animation = this.animations.get(item);
      animation?.effect?.updateTiming({
        easing: 'ease-in-out',
        ...(window.matchMedia('(prefers-reduced-motion: reduce)').matches ? { duration: 0 } : {}),
      });
    };
    prototype.juhaniFaqHeightFixed = true;
  }
  if (window.jQuery) window.jQuery(window).on('elementor/frontend/init', initialize);
  if (window.elementorFrontend?.elementsHandler) initialize();
})();
