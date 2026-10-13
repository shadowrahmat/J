<?php
/** Isolated search-results design preview. Reads products; does not save live templates. */
require_once __DIR__.'/wp-load.php';
$context=get_page_by_path('about');
if($context){$wp_query=new WP_Query(['page_id'=>$context->ID]);$wp_the_query=$wp_query;$post=$context;setup_postdata($post);do_action('wp',$wp);}
// Standalone preview bypasses template_redirect; initialize the existing header/footer runtime.
if(class_exists('Elementor\\Plugin')){
 \Elementor\Plugin::$instance->frontend->init_render_mode();
 \Elementor\Plugin::$instance->frontend->init();
}
status_header(200);nocache_headers();header('X-Robots-Tag: noindex, nofollow',true);
add_filter('pre_get_document_title',function(){return 'Product Search — Juhani Design Preview';});
add_filter('wp_robots',function($robots){$robots['noindex']=true;$robots['nofollow']=true;return $robots;});
add_action('wp_enqueue_scripts',function(){
 wp_enqueue_style('juhani-search-preview',home_url('/preview-assets/search-preview.css'),[],filemtime(__DIR__.'/preview-assets/search-preview.css'));
 wp_enqueue_script('juhani-search-preview',home_url('/preview-assets/search-preview.js'),[],filemtime(__DIR__.'/preview-assets/search-preview.js'),true);
});
$products=wc_get_products(['status'=>'publish','visibility'=>'catalog','limit'=>24,'orderby'=>'date','order'=>'DESC']);
$query=isset($_GET['q'])?sanitize_text_field(wp_unslash($_GET['q'])):'cast';
function jusr_icon($type){$paths=['search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>','close'=>'<path d="m6 6 12 12M18 6 6 18"/>','chevron'=>'<path d="m9 5 7 7-7 7"/>','arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>','check'=>'<path d="m5 12 4 4L19 6"/>'];return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$type]??$paths['search']).'</svg>';}
function jusr_price($number){return wc_price($number,['decimals'=>(float)$number===floor((float)$number)?0:wc_get_price_decimals()]);}
function jusr_terms($product,$taxonomy){$names=wc_get_product_terms($product->get_id(),$taxonomy,['fields'=>'names']);if(is_wp_error($names))return [];usort($names,'strnatcasecmp');return $names;}
get_header();
?>
<main class="juhani-search-preview" lang="bn">
 <div class="juhani-search-preview-container">
  <nav class="juhani-search-preview-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">Search preview</span></nav>
  <section class="juhani-search-preview-stage" aria-labelledby="juhani-search-preview-title">
   <div class="juhani-search-preview-intro">
    <span class="juhani-search-preview-eyebrow" lang="en">JUHANI / PRODUCT SEARCH</span>
    <h1 id="juhani-search-preview-title">আপনার জাল খুঁজে নিন,<br> <span>এক মুহূর্তে।</span></h1>
    <p>ঘর বা product name লিখুন। ছবি, মাপ ও দাম একসঙ্গে দেখে আপনার পছন্দের জালটি বেছে নিন।</p>
    <div class="juhani-search-preview-suggestions"><span lang="en">TRY A SEARCH</span><div><button type="button" data-search-query="15 Ghor">15 Ghor</button><button type="button" data-search-query="25 Ghor">25 Ghor</button><button type="button" data-search-query="44 Ghor">44 Ghor</button></div></div>
    <div class="juhani-search-preview-note"><?php echo jusr_icon('check'); ?><span>হাত, সুতা ও ওজন মিলিয়ে আপনার জাল বেছে নিন।</span></div>
   </div>
   <div class="juhani-search-preview-demo">
    <div class="juhani-search-preview-demo-heading"><span class="juhani-search-preview-eyebrow" lang="en">FIND YOUR NEXT NET</span><span class="juhani-search-preview-demo-tag" lang="en">Design preview</span></div>
    <div class="juhani-search-preview-panel">
     <form class="juhani-search-preview-form" action="<?php echo esc_url(home_url('/search-preview.php')); ?>" method="get" role="search">
      <label class="juhani-search-preview-sr-only" for="juhani-search-preview-query">Search products</label>
      <span class="juhani-search-preview-search-icon"><?php echo jusr_icon('search'); ?></span>
      <input id="juhani-search-preview-query" class="juhani-search-preview-input" type="search" name="q" value="<?php echo esc_attr($query); ?>" placeholder="যেমন: 25 Ghor" autocomplete="off" aria-controls="juhani-search-preview-results">
      <button class="juhani-search-preview-clear" type="button" aria-label="Clear product search"><?php echo jusr_icon('close'); ?></button>
     </form>
     <div class="juhani-search-preview-results-heading"><span lang="en">PRODUCTS</span><span class="juhani-search-preview-count" role="status" aria-live="polite" lang="en"><?php echo count($products); ?> results</span></div>
     <div id="juhani-search-preview-results" class="juhani-search-preview-results" aria-label="Matching products">
      <?php foreach($products as$product):
       $min=$product->is_type('variable')?$product->get_variation_price('min',true):wc_get_price_to_display($product);
       $max=$product->is_type('variable')?$product->get_variation_price('max',true):$min;
       $haat=jusr_terms($product,'pa_haat');$suta=jusr_terms($product,'pa_suta');
       $measure=$haat?reset($haat).(count($haat)>1?'–'.end($haat):'').' Haat':'';
       $thread=$suta?implode(' / ',$suta).' Suta':'';
       $categories=wc_get_product_terms($product->get_id(),'product_cat',['fields'=>'names']);$category=!is_wp_error($categories)&&$categories?reset($categories):'Product';
       $search=implode(' ',[$product->get_name(),$measure,$thread,'ghor ঘর cast net জাল',$category]);
      ?>
      <a class="juhani-search-preview-card" href="<?php echo esc_url($product->get_permalink()); ?>" data-product-id="<?php echo esc_attr($product->get_id()); ?>" data-search="<?php echo esc_attr($search); ?>">
       <div class="juhani-search-preview-photo"><img src="<?php echo esc_url(wp_get_attachment_image_url($product->get_image_id(),'woocommerce_thumbnail')?:wc_placeholder_img_src()); ?>" alt="<?php echo esc_attr($product->get_name()); ?>" width="300" height="300" loading="lazy"></div>
       <div class="juhani-search-preview-copy"><div class="juhani-search-preview-card-topline"><span class="juhani-search-preview-category" lang="en"><?php echo esc_html($category); ?></span><span class="juhani-search-preview-stock<?php echo $product->is_in_stock()?'':' is-unavailable'; ?>" lang="en"><?php echo $product->is_in_stock()?'In stock':'Out of stock'; ?></span></div><h3 lang="en"><?php echo esc_html($product->get_name()); ?></h3><p class="juhani-search-preview-price" lang="en"><?php echo wp_kses_post(jusr_price($min)); ?><?php if((float)$min!==(float)$max): ?><span class="juhani-search-preview-price-dash">–</span><?php echo wp_kses_post(jusr_price($max)); ?><?php endif; ?></p><span class="juhani-search-preview-measure" lang="en"><?php echo esc_html(implode(' · ',array_filter([$measure,$thread]))); ?></span></div>
       <span class="juhani-search-preview-card-arrow"><?php echo jusr_icon('chevron'); ?></span>
      </a>
      <?php endforeach; ?>
      <div class="juhani-search-preview-empty" hidden><?php echo jusr_icon('search'); ?><h3>এই নামে কোনো জাল পাওয়া যায়নি।</h3><p>অন্য ঘর বা product name দিয়ে খুঁজুন।</p><button type="button" class="juhani-search-preview-show-all">সব পণ্য দেখুন</button></div>
     </div>
     <div class="juhani-search-preview-panel-footer"><span class="juhani-search-preview-keyboard" lang="en"><kbd>↓</kbd><span>Browse results</span></span><a class="juhani-search-preview-view-all" href="<?php echo esc_url(add_query_arg(['s'=>$query,'post_type'=>'product'],home_url('/'))); ?>" data-search-url="<?php echo esc_url(home_url('/')); ?>">সব ফলাফল দেখুন <?php echo jusr_icon('arrow'); ?></a></div>
    </div>
    <p class="juhani-search-preview-demo-caption">Product-এ click করে বিস্তারিত ও available options দেখুন।</p>
   </div>
  </section>
 </div>
</main>
<?php wp_reset_postdata();get_footer(); ?>
