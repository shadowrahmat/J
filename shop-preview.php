<?php
/** Isolated shop design: reads the existing catalog, without saving live pages. */
require_once __DIR__.'/wp-load.php';
$shop=get_page_by_path('shop');
if(!$shop){status_header(404);exit('Shop not found');}
$wp_query=new WP_Query(['page_id'=>$shop->ID]);$wp_the_query=$wp_query;$post=$shop;setup_postdata($post);do_action('wp',$wp);
status_header(200);nocache_headers();header('X-Robots-Tag: noindex, nofollow',true);
add_filter('pre_get_document_title',function(){return 'Juhani Shop - Design Preview';});
add_filter('wp_robots',function($r){$r['noindex']=true;$r['nofollow']=true;return $r;});
add_filter('body_class',function($c){$c[]='juhani-shop-preview';return $c;});
add_action('wp_enqueue_scripts',function(){wp_enqueue_style('juhani-shop-preview',home_url('/preview-assets/shop.css'),[],filemtime(__DIR__.'/preview-assets/shop.css'));wp_enqueue_script('juhani-shop-preview',home_url('/preview-assets/shop.js'),[],filemtime(__DIR__.'/preview-assets/shop.js'),true);});
function jsp_icon($name){$paths=['arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>','search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>','filter'=>'<path d="M4 7h16M4 17h16"/><circle cx="9" cy="7" r="2" fill="currentColor"/><circle cx="15" cy="17" r="2" fill="currentColor"/>','close'=>'<path d="m6 6 12 12M18 6 6 18"/>','check'=>'<path d="m5 12 4 4L19 6"/>','help'=>'<circle cx="12" cy="12" r="9"/><path d="M9 9a3 3 0 0 1 6 0c0 2-3 2-3 4m0 3h.01"/>','net'=>'<path d="M12 3 3 19h18L12 3Zm-5 9h10M5 16h14M9 8l1 11m5-11-1 11"/>'];return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['arrow']).'</svg>';}
$products=wc_get_products(['status'=>'publish','limit'=>-1,'orderby'=>'menu_order','order'=>'ASC']);
$groups=['ghor'=>['label'=>'Ghor / ঘর','values'=>[]],'pa_haat'=>['label'=>'Haat / হাত','values'=>[]],'pa_suta'=>['label'=>'Suta / সুতা','values'=>[]],'pa_making-type'=>['label'=>'Making Type','values'=>[]],'pa_git-type'=>['label'=>'Git Type','values'=>[]],'pa_rock-weight-kg'=>['label'=>'Rock Weight (kg)','values'=>[]],'category'=>['label'=>'Category','values'=>[]]];
$rows=[];$lowest=PHP_FLOAT_MAX;$highest=0;
foreach($products as$p){
 $attributes=[];foreach($p->get_attributes()as$key=>$a)$attributes[$key]=$a->is_taxonomy()?wc_get_product_terms($p->get_id(),$key,['fields'=>'names']):$a->get_options();
 preg_match('/(\d+)\s*Ghor/i',$p->get_name(),$m);$ghor=$m[1]??'';$attributes['ghor']=$ghor?[$ghor]:[];$attributes['category']=wp_get_post_terms($p->get_id(),'product_cat',['fields'=>'names']);
 foreach($groups as$key=>&$group)foreach($attributes[$key]??[]as$value)$group['values'][(string)$value]=true;unset($group);
 $min=(float)($p->is_type('variable')?$p->get_variation_price('min',true):wc_get_price_to_display($p));$max=(float)($p->is_type('variable')?$p->get_variation_price('max',true):$min);
 $lowest=min($lowest,$min);$highest=max($highest,$max);
 $prices=$p->is_type('variable')?array_values($p->get_variation_prices(true)['price']):[$min];
 $rows[]=['product'=>$p,'attributes'=>$attributes,'ghor'=>$ghor,'min'=>$min,'max'=>$max,'prices'=>array_values(array_unique(array_map('floatval',$prices)))];
}
if(!$rows)$lowest=0;
foreach($groups as&$group){$group['values']=array_keys($group['values']);natsort($group['values']);$group['values']=array_values($group['values']);}unset($group);
get_header();
?>
<main id="content" class="jsp-preview" lang="bn">
 <nav class="jsp-container jsp-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>" lang="en">Home</a><span aria-hidden="true">/</span><span aria-current="page" lang="en">Shop</span></nav>
 <section class="jsp-container jsp-hero" aria-labelledby="jsp-title">
  <div class="jsp-hero-copy"><span class="jsp-eyebrow" lang="en">THE JUHANI COLLECTION</span><h1 id="jsp-title">সঠিক জাল বেছে নিন।<br><em>আত্মবিশ্বাস নিয়ে মাছ ধরুন।</em></h1><p>ঘর, হাত ও সুতার মাপ মিলিয়ে আপনার কাজের জন্য উপযুক্ত Cast Net খুঁজে নিন—সব বিকল্প এক জায়গায়।</p><a href="#jsp-catalog" class="jsp-button">জালগুলো দেখুন <?php echo jsp_icon('arrow'); ?></a><div class="jsp-hero-note"><span></span><span lang="en"><?php echo count($rows); ?> CAST NETS. BUILT AROUND YOUR NEEDS.</span></div></div>
  <div class="jsp-hero-photo"><img src="<?php echo esc_url(home_url('/wp-content/uploads/2026/04/Banner-02.webp')); ?>" alt="পানিতে মাছ ধরার জাল ছুড়ছেন একজন জেলে" width="1500" height="1040" fetchpriority="high"><div class="jsp-photo-label"><span lang="en">MADE FOR THE WATER.</span><strong>প্রতিটি ছোড়ায়, সঠিক পছন্দ।</strong></div></div>
 </section>
 <div class="jsp-container jsp-service-strip"><span><?php echo jsp_icon('net'); ?> <span>১৫–৪৪ ঘরের Cast Net</span></span><span><?php echo jsp_icon('check'); ?> <span>হাত, সুতা ও ওজনের বিকল্প</span></span><a href="https://wa.me/8801889164315" target="_blank" rel="noopener"><?php echo jsp_icon('help'); ?> <span>সঠিক মাপ বাছাইয়ে সহায়তা</span> <?php echo jsp_icon('arrow'); ?></a></div>
 <section id="jsp-catalog" class="jsp-container jsp-catalog" aria-labelledby="jsp-catalog-title">
  <div class="jsp-catalog-heading"><div><span class="jsp-eyebrow" lang="en">FIND THE NET THAT WORKS FOR YOU</span><h2 id="jsp-catalog-title">আপনার জাল, আপনার পছন্দ।</h2><p>মাপ বেছে নিন, বিকল্প দেখুন, তারপর আপনার জালটি ঠিক করুন।</p></div><label class="jsp-search"><?php echo jsp_icon('search'); ?><span class="jsp-sr-only">পণ্যের নাম দিয়ে খুঁজুন</span><input id="jsp-search" type="search" placeholder="যেমন: 25 Ghor…" autocomplete="off"></label></div>
  <div class="jsp-layout">
   <aside class="jsp-sidebar"><dialog open id="jsp-filter-dialog" class="jsp-filters" aria-labelledby="jsp-filter-title">
    <div class="jsp-filter-heading"><div><?php echo jsp_icon('filter'); ?><h3 id="jsp-filter-title" lang="en">Refine your choice</h3></div><button type="button" id="jsp-close-filters" class="jsp-icon-button jsp-mobile-only" aria-label="Close filters"><?php echo jsp_icon('close'); ?></button></div>
    <div class="jsp-filter-intro"><span lang="en">FILTERS</span><button class="jsp-reset" type="button">Reset all</button></div>
    <details class="jsp-filter-group" open><summary>Price range <span>৳</span></summary><div class="jsp-price-inputs"><label><span>Minimum</span><input id="jsp-price-min" type="number" min="0" step="1" placeholder="<?php echo esc_attr($lowest); ?>" aria-label="Minimum price in taka"></label><span>–</span><label><span>Maximum</span><input id="jsp-price-max" type="number" min="0" step="1" placeholder="<?php echo esc_attr($highest); ?>" aria-label="Maximum price in taka"></label></div><small>কোনো বিকল্পের দাম এই সীমার মধ্যে থাকলে পণ্যটি দেখাবে।</small><p id="jsp-price-error" class="jsp-price-error" role="status" hidden>Minimum দাম Maximum-এর চেয়ে বেশি হয়েছে।</p></details>
    <?php foreach($groups as$key=>$group): if(!$group['values'])continue; ?>
    <details class="jsp-filter-group" <?php echo in_array($key,['ghor','pa_haat','pa_suta'],true)?'open':''; ?>><summary><?php echo esc_html($group['label']); ?><span><?php echo count($group['values']); ?></span></summary><div class="jsp-filter-options <?php echo in_array($key,['ghor','pa_haat','pa_suta','pa_rock-weight-kg'],true)?'jsp-number-options':''; ?>">
     <?php foreach($group['values']as$value): ?><label><input type="checkbox" data-group="<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value); ?>"><span><?php echo esc_html($value); ?></span></label><?php endforeach; ?>
    </div></details>
    <?php endforeach; ?>
    <button type="button" id="jsp-apply-filters" class="jsp-button jsp-mobile-only">পণ্যগুলো দেখুন <?php echo jsp_icon('arrow'); ?></button>
   </dialog><div class="jsp-sidebar-help"><?php echo jsp_icon('help'); ?><h4>মাপ নিয়ে দ্বিধায়?</h4><p>আপনার প্রয়োজন বলুন। উপযুক্ত জাল বেছে নিতে সাহায্য করব।</p><a href="https://wa.me/8801889164315" target="_blank" rel="noopener">WhatsApp-এ কথা বলুন <?php echo jsp_icon('arrow'); ?></a></div></aside>
   <div class="jsp-results">
    <div class="jsp-results-toolbar"><div><span class="jsp-collection-label" lang="en">Cast Nets</span><span id="jsp-result-status" role="status" aria-live="polite"><?php echo count($rows); ?> products</span></div><div class="jsp-results-tools"><button type="button" id="jsp-open-filters" class="jsp-filter-toggle jsp-mobile-only" aria-haspopup="dialog"><?php echo jsp_icon('filter'); ?> Filters <span id="jsp-filter-count" hidden>0</span></button><label class="jsp-sort"><span class="jsp-sr-only">Sort products</span><select id="jsp-sort" aria-label="Sort products"><option value="featured">Featured</option><option value="newest">Newest</option><option value="price-low">Price: Low to High</option><option value="price-high">Price: High to Low</option><option value="popular">Most Popular</option><option value="rating">Top Rated</option></select></label></div></div>
    <div id="jsp-active-filters" class="jsp-active-filters" aria-label="Active filters" hidden></div>
    <div id="jsp-product-grid" class="jsp-product-grid">
     <?php foreach($rows as$i=>$row):$p=$row['product'];$atts=$row['attributes'];$haat=$atts['pa_haat']??[];natsort($haat);$haat=array_values($haat);$haat_text=$haat?(count($haat)>1?$haat[0].'–'.end($haat):$haat[0]):'';$date=$p->get_date_created(); ?>
     <article class="jsp-product" data-index="<?php echo $i; ?>" data-search="<?php echo esc_attr($p->get_name().' '.$row['ghor'].' ঘর '.implode(' ',array_merge(...array_values($atts)))); ?>" data-attributes="<?php echo esc_attr(wp_json_encode($atts,JSON_UNESCAPED_UNICODE)); ?>" data-min="<?php echo esc_attr($row['min']); ?>" data-max="<?php echo esc_attr($row['max']); ?>" data-prices="<?php echo esc_attr(wp_json_encode($row['prices'])); ?>" data-date="<?php echo $date?$date->getTimestamp():0; ?>" data-sales="<?php echo esc_attr($p->get_total_sales()); ?>" data-rating="<?php echo esc_attr($p->get_average_rating()); ?>">
      <a class="jsp-product-photo" href="<?php echo esc_url($p->get_permalink()); ?>" aria-label="<?php echo esc_attr($p->get_name()); ?>"><span class="jsp-ghor-tag"><?php echo esc_html($row['ghor']); ?> <span lang="en">GHOR</span></span><img src="<?php echo esc_url(wp_get_attachment_image_url($p->get_image_id(),'large')?:wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr($p->get_name()); ?>" width="600" height="600" loading="lazy"><span class="jsp-product-arrow"><?php echo jsp_icon('arrow'); ?></span></a>
      <div class="jsp-product-copy"><div class="jsp-product-meta"><span lang="en">CAST NET</span><span class="jsp-stock <?php echo !$p->is_in_stock()?'jsp-out-of-stock':''; ?>"><?php echo $p->is_in_stock()?'In stock':'Out of stock'; ?></span></div><h3 lang="en"><a href="<?php echo esc_url($p->get_permalink()); ?>"><?php echo esc_html($p->get_name()); ?></a></h3><div class="jsp-product-specs"><?php if($haat_text): ?><span><?php echo esc_html($haat_text); ?> <small>Haat</small></span><?php endif; ?><span><?php echo esc_html(implode(' / ',$atts['pa_suta']??[])); ?> <small>Suta</small></span></div><div class="jsp-price" lang="en"><?php echo wp_kses_post(wc_price($row['min'],['decimals'=>0])); ?><?php if($row['max']!==$row['min']): ?><span class="jsp-price-dash">–</span><?php echo wp_kses_post(wc_price($row['max'],['decimals'=>0])); ?><?php endif; ?></div><a class="jsp-product-button" href="<?php echo esc_url($p->get_permalink()); ?>">মাপ ও বিকল্প বাছাই করুন <?php echo jsp_icon('arrow'); ?></a></div>
     </article>
     <?php endforeach; ?>
    </div>
    <div id="jsp-empty" class="jsp-empty" hidden><?php echo jsp_icon('search'); ?><h3>এই পছন্দে কোনো জাল পাওয়া যায়নি।</h3><p>অন্য মাপ বেছে নিন বা filters সরিয়ে সব পণ্য দেখুন।</p><button type="button" class="jsp-reset jsp-button">সব পণ্য দেখুন <?php echo jsp_icon('arrow'); ?></button></div>
    <div class="jsp-catalog-end"><span></span><p>আপনার প্রয়োজন অনুযায়ী মাপ ও বিকল্প বেছে নিন।</p><span></span></div>
   </div>
  </div>
 </section>
 <section class="jsp-container jsp-guidance"><div class="jsp-guidance-icon"><?php echo jsp_icon('net'); ?></div><div><span class="jsp-eyebrow" lang="en">A LITTLE GUIDANCE GOES A LONG WAY</span><h2>প্রথমবার জাল কিনছেন?</h2><p>ঘর, হাত ও সুতার পার্থক্য বুঝে নিন। সঠিক জাল বেছে নেওয়ার গাইডটি পড়ুন।</p></div><a class="jsp-button jsp-button-light" href="<?php echo esc_url(home_url('/how-to-choose-the-right-fishing-net-in-bangladesh/')); ?>">গাইডটি পড়ুন <?php echo jsp_icon('arrow'); ?></a></section>
 <div class="jsp-container jsp-preview-note"><span lang="en">Shop design preview</span><a href="<?php echo esc_url(get_permalink($shop)); ?>">বর্তমান Shop page <?php echo jsp_icon('arrow'); ?></a></div>
</main>
<?php wp_reset_postdata();get_footer(); ?>
