<?php
/** Plugin Name: Juhani Search Result Link Fix */
if(!defined("ABSPATH")) exit;
add_action("wp_footer", function(){ ?>
<script id="juhani-search-link-fix-js">
(function(){
  function enhanceSearchLinks(){
    const items = document.querySelectorAll(".e-search-results .e-loop-item, .e-search-results-container .e-loop-item, .e-search-results .elementor-1212, .e-search-results-container .elementor-1212");
    items.forEach(item=>{
      const isLoop = item.classList.contains("e-loop-item") || item.classList.contains("elementor-1212");
      if(!isLoop) return;
      // Avoid re-linking
      if(item.dataset.juhaniLinked) return;
      const cls=item.className;
      const m=cls.match(/post-(\d+)/);
      const m2=cls.match(/e-loop-item-(\d+)/);
      const postId=m?m[1]:(m2?m2[1]:null);
      if(!postId) return;
      const permalink = "/juhani/?p=" + postId;
      // Link image — target any img inside image widget
      const imgWraps=item.querySelectorAll(".elementor-widget-image, .elementor-widget-theme-post-featured-image");
      imgWraps.forEach(w=>{
        const img=w.querySelector("img");
        if(img && !img.closest("a")){
          const a=document.createElement("a");
          a.href=permalink;
          a.style.display="block";
          a.style.cursor="pointer";
          img.parentNode.insertBefore(a, img);
          a.appendChild(img);
        }
      });
      // Link title
      const titleEls=item.querySelectorAll(".elementor-heading-title, .elementor-widget-heading .elementor-heading-title, .product_title, h1.product_title");
      titleEls.forEach(titleEl=>{
        if(titleEl.closest("a")) return;
        const a2=document.createElement("a");
        a2.href=permalink;
        a2.style.display="block";
        a2.style.color="inherit";
        a2.style.textDecoration="none";
        a2.style.cursor="pointer";
        const cloned=titleEl.cloneNode(true);
        titleEl.replaceWith(a2);
        a2.appendChild(cloned);
      });
      // Also make whole card clickable as fallback
      if(!item.querySelector(":scope > a.juhani-card-link")){
        item.style.cursor="pointer";
        item.addEventListener("click", function(e){
          if(e.target.closest("a")) return;
          window.location.href=permalink;
        });
      }
      item.dataset.juhaniLinked="1";
    });
  }
  // Observe results container for live search injection
  const tryObserve=()=>{
    const containers=document.querySelectorAll(".e-search-results-container, .e-search-results, .e-search-form, body");
    let ok=false;
    containers.forEach(el=>{
      try{ const obs=new MutationObserver(enhanceSearchLinks); obs.observe(el, {childList:true, subtree:true}); ok=true; }catch(e){}
    });
    return ok;
  };
  // Also run on wc_fragments / elementor events
  if(window.jQuery){
    jQuery(document.body).on("wc_fragments_refreshed added_to_cart removed_from_cart", ()=>setTimeout(enhanceSearchLinks,60));
    jQuery(document).on("e-search:results-rendered", enhanceSearchLinks);
  }
  document.addEventListener("DOMContentLoaded", ()=>{ tryObserve(); enhanceSearchLinks(); });
  window.addEventListener("load", ()=>setTimeout(()=>{ tryObserve(); enhanceSearchLinks(); },400));
  // Poll for first 5s after load (Elementor renders async)
  let polls=0; const iv=setInterval(()=>{ tryObserve(); enhanceSearchLinks(); if(++polls>20) clearInterval(iv); },250);
})();
</script>
<?php }, 100);