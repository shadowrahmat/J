<?php
/** Isolated blog concept. Reads published posts; never saves pages or posts. */
require_once __DIR__.'/wp-load.php';
$blogs_page=get_page_by_path('blogs');
if (!$blogs_page) { status_header(404); exit('Blogs page not found.'); }
$wp_query=new WP_Query(['page_id'=>$blogs_page->ID]);
$wp_the_query=$wp_query;
$post=$blogs_page;
setup_postdata($post);
do_action('wp',$wp);
status_header(200);
nocache_headers();
header('X-Robots-Tag: noindex, nofollow',true);
add_filter('pre_get_document_title',function(){return 'Juhani Journal — Blog Design Preview';});
add_filter('wp_robots',function($robots){$robots['noindex']=true;$robots['nofollow']=true;return $robots;});
add_filter('body_class',function($classes){$classes[]='juhani-blog-preview';return $classes;});
add_action('wp_enqueue_scripts',function(){
    wp_enqueue_style('juhani-blog-preview',home_url('/preview-assets/blog.css'),[],filemtime(__DIR__.'/preview-assets/blog.css'));
    wp_enqueue_script('juhani-blog-preview',home_url('/preview-assets/blog.js'),[],filemtime(__DIR__.'/preview-assets/blog.js'),true);
});
$asset=home_url('/wp-content/uploads/2026/04/');
$articles=get_posts(['post_type'=>'post','post_status'=>'publish','numberposts'=>-1]);
function jb_number($number){return strtr((string)$number,['0'=>'০','1'=>'১','2'=>'২','3'=>'৩','4'=>'৪','5'=>'৫','6'=>'৬','7'=>'৭','8'=>'৮','9'=>'৯']);}
function jb_topic($article){
    $title=get_the_title($article);
    if(mb_strpos($title,'চাষ')!==false)return ['aquaculture','মৎস্য চাষ'];
    if(mb_strpos($title,'নাইলন')!==false)return ['nylon','নাইলন জাল'];
    if(mb_strpos($title,'বাছাই')!==false||mb_strpos($title,'নির্বাচন')!==false)return ['net-selection','জাল নির্বাচন'];
    return ['fishing','ফিশিং গাইড'];
}
function jb_reading_time($article){$plain=html_entity_decode(wp_strip_all_tags($article->post_content),ENT_QUOTES,'UTF-8');return max(1,(int)ceil(preg_match_all('/[\p{L}\p{N}]+/u',$plain)/200));}
function jb_excerpt($article){$text=html_entity_decode(wp_strip_all_tags(get_the_excerpt($article)),ENT_QUOTES,'UTF-8');$text=preg_replace('/\s+/u',' ',$text);return wp_html_excerpt(trim($text),145,'…');}
function jb_icon($name){
    $paths=['arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>','search'=>'<circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/>','clock'=>'<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>','book'=>'<path d="M12 5C8 2 4 3 2 4v15c3-2 7-2 10 0 3-2 7-2 10 0V4c-2-1-6-2-10 1Zm0 0v14"/>','phone'=>'<path d="M5 3h4l2 5-3 2a15 15 0 0 0 6 6l2-3 5 2v4c0 2-2 3-4 2C9 19 5 15 3 7c-1-2 0-4 2-4Z"/>'];
    return '<svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($paths[$name]??$paths['arrow']).'</svg>';
}
$topics=[];$featured=$articles[0]??null;
foreach($articles as$article){$topic=jb_topic($article);$topics[$topic[0]]=$topic[1];if($topic[0]==='net-selection')$featured=$article;}
get_header();
?>
<main id="content" class="jb-preview" lang="bn">
  <nav class="jb-breadcrumb jb-container" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>" lang="en">Home</a><span aria-hidden="true">/</span><span aria-current="page" lang="en">Journal</span></nav>
  <section class="jb-hero jb-container" aria-labelledby="jb-title">
    <div class="jb-hero-copy"><span class="jb-eyebrow" lang="en">THE JUHANI JOURNAL</span><span class="jb-kicker">শিল্প বিষয়ক অন্তর্দৃষ্টি</span><h1 id="jb-title">টেকসই ভবিষ্যতের পথে,<br><em>মাছ ধরার জালের ভূমিকা।</em></h1><p>আধুনিক মাছ ধরার জাল এবং দায়িত্বশীল কর্মপদ্ধতি কীভাবে জেলেদের কর্মদক্ষতা বাড়াতে এবং একই সাথে সামুদ্রিক বাস্তুতন্ত্র রক্ষায় সহায়তা করছে, তা জানুন।</p><a class="jb-button" href="#jb-library">সব নিবন্ধ দেখুন <?php echo jb_icon('arrow'); ?></a><div class="jb-hero-foot"><span><?php echo jb_icon('book'); ?> বাস্তব অভিজ্ঞতা। কার্যকর পরামর্শ।</span><span class="jb-issue" lang="en">A LITTLE KNOWLEDGE. A BETTER CATCH.</span></div></div>
    <div class="jb-hero-image"><img src="<?php echo esc_url($asset.'Blog-03.webp'); ?>" alt="বিভিন্ন বুনন ও মাপের মাছ ধরার জাল" width="900" height="600" fetchpriority="high"><div class="jb-image-note"><span class="jb-dot"></span><span lang="en">GOOD GEAR STARTS WITH GOOD KNOWLEDGE.</span></div></div>
  </section>

  <?php if($featured): $feature_topic=jb_topic($featured); ?>
  <section class="jb-feature jb-container" aria-labelledby="jb-feature-title">
    <a class="jb-feature-image" href="<?php echo esc_url(get_permalink($featured)); ?>" aria-label="<?php echo esc_attr(get_the_title($featured)); ?>"><img src="<?php echo esc_url($asset.'Blog-01.webp'); ?>" alt="জালে ধরা মাছ—সঠিক ফিশিং নেট বেছে নেওয়ার একটি বাস্তব দৃশ্য" loading="lazy" width="900" height="600"><span class="jb-feature-badge"><?php echo jb_icon('book'); ?> নির্বাচিত গাইড</span></a>
    <div class="jb-feature-copy"><span class="jb-eyebrow" lang="en">START WITH THE ESSENTIALS</span><div class="jb-meta"><span class="jb-topic"><?php echo esc_html($feature_topic[1]); ?></span><span><?php echo jb_icon('clock'); ?> প্রায় <?php echo esc_html(jb_number(jb_reading_time($featured))); ?> মিনিট</span></div><h2 id="jb-feature-title"><a href="<?php echo esc_url(get_permalink($featured)); ?>"><?php echo esc_html(get_the_title($featured)); ?></a></h2><p><?php echo esc_html(jb_excerpt($featured)); ?></p><a class="jb-text-link" href="<?php echo esc_url(get_permalink($featured)); ?>">গাইডটি পড়ুন <?php echo jb_icon('arrow'); ?></a></div>
  </section>
  <?php endif; ?>

  <section id="jb-library" class="jb-library" aria-labelledby="jb-library-title"><div class="jb-container jb-section">
    <div class="jb-section-heading"><div><span class="jb-eyebrow" lang="en">READ. LEARN. CAST WITH CONFIDENCE.</span><h2 id="jb-library-title">আপনার পরবর্তী ভালো সিদ্ধান্তের জন্য।</h2><p>মৎস্য চাষ, নাইলন জাল ও সঠিক নেট নির্বাচন—সব গাইড এক জায়গায়।</p></div><span class="jb-total"><?php echo esc_html(jb_number(count($articles))); ?>টি নিবন্ধ</span></div>
    <div class="jb-toolbar"><div class="jb-topics" role="group" aria-label="নিবন্ধের বিষয় নির্বাচন"><button type="button" class="jb-filter is-active" data-topic="all" aria-pressed="true">সব নিবন্ধ <span><?php echo esc_html(jb_number(count($articles))); ?></span></button><?php foreach($topics as$slug=>$label): ?><button type="button" class="jb-filter" data-topic="<?php echo esc_attr($slug); ?>" aria-pressed="false"><?php echo esc_html($label); ?></button><?php endforeach; ?></div><label class="jb-search"><?php echo jb_icon('search'); ?><span class="jb-sr-only">শিরোনাম বা বিষয় দিয়ে নিবন্ধ খুঁজুন</span><input id="jb-search" type="search" placeholder="নিবন্ধ খুঁজুন…" autocomplete="off"></label></div>
    <div class="jb-results-line"><span id="jb-result-status" role="status" aria-live="polite"><?php echo esc_html(jb_number(count($articles))); ?>টি নিবন্ধ দেখানো হচ্ছে</span><span lang="en">PRACTICAL GUIDES, FROM JUHANI</span></div>
    <div class="jb-article-grid">
      <?php foreach($articles as$i=>$article): $topic=jb_topic($article);$photo=get_the_post_thumbnail_url($article->ID,'large')?:$asset.'Blog-0'.(($i%3)+1).'.webp';$excerpt=jb_excerpt($article); ?>
      <article class="jb-article" data-topic="<?php echo esc_attr($topic[0]); ?>" data-search="<?php echo esc_attr(get_the_title($article).' '.$excerpt.' '.$topic[1]); ?>"><a class="jb-article-image" href="<?php echo esc_url(get_permalink($article)); ?>" aria-label="<?php echo esc_attr(get_the_title($article)); ?>"><img src="<?php echo esc_url($photo); ?>" alt="" loading="lazy" width="600" height="400"><span class="jb-image-arrow" aria-hidden="true"><?php echo jb_icon('arrow'); ?></span></a><div class="jb-article-copy"><div class="jb-meta"><span class="jb-topic"><?php echo esc_html($topic[1]); ?></span><time datetime="<?php echo esc_attr(get_the_date('Y-m-d',$article)); ?>" lang="en"><?php echo esc_html(get_the_date('d M Y',$article)); ?></time></div><h3><a href="<?php echo esc_url(get_permalink($article)); ?>"><?php echo esc_html(get_the_title($article)); ?></a></h3><p><?php echo esc_html($excerpt); ?></p><div class="jb-card-footer"><span><?php echo jb_icon('clock'); ?> প্রায় <?php echo esc_html(jb_number(jb_reading_time($article))); ?> মিনিট</span><a href="<?php echo esc_url(get_permalink($article)); ?>">পড়ুন <?php echo jb_icon('arrow'); ?></a></div></div></article>
      <?php endforeach; ?>
    </div>
    <div id="jb-empty" class="jb-empty" hidden><?php echo jb_icon('search'); ?><h3>কোনো নিবন্ধ পাওয়া যায়নি।</h3><p>অন্য কোনো শব্দ দিয়ে খুঁজুন বা সব নিবন্ধ দেখুন।</p><button id="jb-reset" class="jb-button" type="button">সব নিবন্ধ দেখুন <?php echo jb_icon('arrow'); ?></button></div>
    <div class="jb-library-end"><span></span><p>আরও ভালো গিয়ার। আরও ভালো সিদ্ধান্ত।</p><span></span></div>
  </div></section>

  <section class="jb-help jb-container" aria-labelledby="jb-help-title"><div><span class="jb-eyebrow jb-light" lang="en">A LITTLE PERSONAL GUIDANCE</span><h2 id="jb-help-title">গাইড পড়েছেন।<br>এবার আপনার জন্য সঠিক জাল খুঁজি।</h2><p>জাল নির্বাচন বা পাইকারি অর্ডার নিয়ে আমাদের সঙ্গে সরাসরি কথা বলুন।</p></div><div class="jb-help-actions"><a class="jb-button jb-button-light" href="https://wa.me/8801889164315" target="_blank" rel="noopener">WhatsApp-এ কথা বলুন <?php echo jb_icon('arrow'); ?></a><a class="jb-phone" href="tel:+8801889164315"><?php echo jb_icon('phone'); ?> <span lang="en">+880 1889 164315</span></a></div></section>
  <div class="jb-preview-note jb-container"><span><span class="jb-dot"></span><span lang="en">Blog design preview</span></span><a href="<?php echo esc_url(get_permalink($blogs_page)); ?>">বর্তমান Blogs page দেখুন <?php echo jb_icon('arrow'); ?></a></div>
</main>
<?php wp_reset_postdata();get_footer(); ?>
