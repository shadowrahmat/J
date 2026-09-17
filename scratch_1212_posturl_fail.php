<?php
require 'wp-load.php';
// Check how post-url renders in loop context: image widget expects media field, use theme-post-featured-image
// For theme-post-featured-image the link is handled by elementor linking via wrapper
// Simpler: use JS to wrap results after render - most reliable
echo "switching 1212 to use theme-post-featured-image + theme-post-title wrappers that support post-url in loop\n";
global $wpdb;
$pfx=$wpdb->prefix;

// JS fallback: inject post link wrapping for search results
$file='wp-content/mu-plugins/juhani-search-link-fix.php';
$content=<<<'PHP'
<?php
/** Plugin Name: Juhani Search Result Link Fix */
if(!defined("ABSPATH")) exit;
add_action("wp_footer", function(){ ?>
<script id="juhani-search-link-fix-js">
(function(){
  function enhanceSearchLinks(){
    document.querySelectorAll(".e-search-results .e-loop-item, .e-search-results-container .e-loop-item").forEach(item=>{
      if(item.dataset.juhaniLinked) return;
      const cls=item.className;
      const m=cls.match(/post-(\d+)/);
      const m2=cls.match(/e-loop-item-(\d+)/);
      const postId=m?m[1]:(m2?m2[1]:null);
      if(!postId) return;
      // Try to get permalink from data attribute or guess via fetch from <a> if any exists
      // For now, build URL via REST is not needed - use relative product link pattern
      // We fetch permalink via AJAX on first hover
      // Simpler: wrap image and title via cloning href from product page pattern
      // Best: use /?p=postId redirect which always works
      const permalink = "/juhani/?p=" + postId;
      // Link image
      const imgWrap=item.querySelector(".elementor-widget-image, .elementor-widget-theme-post-featured-image");
      const img=imgWrap?imgWrap.querySelector("img"):null;
      if(img && !img.closest("a")){
        const a=document.createElement("a");
        a.href=permalink;
        a.style.display="block";
        img.parentNode.insertBefore(a, img);
        a.appendChild(img);
      }
      // Link title h1/h2/h3
      const titleEl=item.querySelector(".elementor-widget-heading .elementor-heading-title, .elementor-heading-title");
      if(titleEl && !titleEl.closest("a")){
        // titleEl is often inside h3/h1 wrapper
        const widget=titleEl.closest(".elementor-widget-heading, .elementor-widget-woocommerce-product-title, .elementor-widget-theme-post-title");
        if(widget){
          // Wrap title element
          const a2=document.createElement("a");
          a2.href=permalink;
          a2.style.display="block";
          a2.style.color="inherit";
          a2.style.textDecoration="none";
          // Move titleEl into anchor
          const parent=titleEl.parentNode;
          a2.appendChild(titleEl.cloneNode(true));
          titleEl.replaceWith(a2);
          // Re-apply title class styles
          a2.querySelector(".elementor-heading-title")?.classList.add("juhani-linked-title");
        }
      }
      item.dataset.juhaniLinked="1";
    });
  }
  // Observe results container for live search injection
  const obs=new MutationObserver(enhanceSearchLinks);
  document.querySelectorAll(".e-search-results-container, .e-search-results").forEach(el=>obs.observe(el, {childList:true, subtree:true}));
  // Also run on wc_fragments / elementor events
  if(window.jQuery){
    jQuery(document.body).on("wc_fragments_refreshed added_to_cart removed_from_cart", ()=>setTimeout(enhanceSearchLinks,60));
    jQuery(document).on("e-search:results-rendered", enhanceSearchLinks);
  }
  document.addEventListener("DOMContentLoaded", enhanceSearchLinks);
  window.addEventListener("load", ()=>setTimeout(enhanceSearchLinks,400));
  // Poll for first 5s after load (Elementor renders async)
  let polls=0; const iv=setInterval(()=>{ enhanceSearchLinks(); if(++polls>20) clearInterval(iv); },250);
})();
</script>
<?php }, 100);
PHP;
file_put_contents($file,$content);
echo "created $file\n";
echo strlen($content)." bytes\n";
echo "DONE\n";
