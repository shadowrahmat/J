<?php
/** Isolated About concept. Reads the existing page; never saves page data. */
require_once __DIR__.'/wp-load.php';
$about_page = get_page_by_path('about');
if (!$about_page) { status_header(404); exit('About page not found.'); }
$wp_query = new WP_Query(['page_id'=>$about_page->ID]);
$wp_the_query = $wp_query;
$post = $about_page;
setup_postdata($post);
do_action('wp', $wp);
status_header(200);
nocache_headers();
header('X-Robots-Tag: noindex, nofollow', true);
add_filter('pre_get_document_title', function(){ return 'About Juhani — Design Preview'; });
add_filter('wp_robots', function($robots){ $robots['noindex']=true; $robots['nofollow']=true; return $robots; });
add_filter('body_class', function($classes){ $classes[]='juhani-about-preview'; return $classes; });
add_action('wp_enqueue_scripts', function(){
    wp_enqueue_style('juhani-about-preview',home_url('/preview-assets/about.css'),[],filemtime(__DIR__.'/preview-assets/about.css'));
});
$asset = home_url('/wp-content/uploads/2026/04/');
$ocean = home_url('/wp-content/uploads/2026/09/ChatGPT-Image-Sep-10-2026-12_25_27-PM.webp');
$shop = function_exists('wc_get_page_permalink')?wc_get_page_permalink('shop'):home_url('/shop/');
$blog = get_option('page_for_posts')?get_permalink(get_option('page_for_posts')):home_url('/?post_type=post');
$articles = get_posts(['post_type'=>'post','post_status'=>'publish','numberposts'=>3]);
function ja_icon($name) {
    $icons = [
        'arrow'=>'<path d="M5 12h14m-6-6 6 6-6 6"/>',
        'shield'=>'<path d="m12 2 9 4v6c0 5-9 10-9 10S3 17 3 12V6l9-4Z"/><path d="m8 12 3 3 5-6"/>',
        'truck'=>'<path d="M2 4h13v13H2zm13 5h4l3 4v4h-7"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/>',
        'people'=>'<circle cx="9" cy="8" r="3"/><path d="M3 21v-2a6 6 0 0 1 12 0v2M16 5a3 3 0 0 1 0 6m2 4a5 5 0 0 1 3 4v2"/>',
        'leaf'=>'<path d="M20 3c-8-1-16 3-16 10a7 7 0 0 0 12 5c4-4 4-10 4-15Z"/><path d="m4 21 11-11"/>',
        'compass'=>'<circle cx="12" cy="12" r="10"/><path d="m16 8-3 5-5 3 3-5 5-3Z"/>',
        'check'=>'<path d="m5 12 4 4L19 6"/>',
        'phone'=>'<path d="M5 3h4l2 5-3 2a15 15 0 0 0 6 6l2-3 5 2v4c0 2-2 3-4 2C9 19 5 15 3 7c-1-2 0-4 2-4Z"/>'
    ];
    return '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'.($icons[$name]??$icons['arrow']).'</svg>';
}
get_header();
?>
<main id="content" class="ja-preview">
  <section class="ja-hero ja-container" aria-labelledby="ja-title" style="--ja-hero-image:url('<?php echo esc_url($asset.'Banner-02.webp'); ?>')">
    <div class="ja-hero-copy">
      <nav class="ja-breadcrumb" aria-label="Breadcrumb"><a href="<?php echo esc_url(home_url('/')); ?>">Home</a><span aria-hidden="true">/</span><span aria-current="page">About us</span></nav>
      <span class="ja-eyebrow ja-light">OUR STORY. YOUR NEXT CATCH.</span>
      <h1 id="ja-title" lang="en">Stronger nets.<br><em>Brighter tomorrows.</em></h1>
      <p lang="bn">আপনার নির্ভরযোগ্য ফিশিং গিয়ার সাপ্লায়ার।<br>জেলে ও মাছচাষিদের প্রতিদিনের পথচলায়, ভরসার সঙ্গে।</p>
      <a class="ja-button ja-button-light" href="#ja-story">আমাদের গল্প জানুন <?php echo ja_icon('arrow'); ?></a>
    </div>
    <div class="ja-hero-note"><span></span> GOOD GEAR. STRONGER COMMUNITIES.</div>
  </section>

  <section id="ja-story" class="ja-story ja-section ja-container" aria-labelledby="ja-story-title" lang="bn">
    <div class="ja-story-visual">
      <img src="<?php echo esc_url($asset.'About-01-1.webp'); ?>" alt="সূর্যাস্তের আলোয় জাল নিয়ে নৌকায় কর্মরত মৎস্যজীবী" width="900" height="599" loading="lazy">
      <div class="ja-experience"><strong>১০<span>+</span></strong><div>বছরের অভিজ্ঞতা<span lang="en">BUILT ON EXPERIENCE</span></div></div>
    </div>
    <div class="ja-story-copy">
      <span class="ja-eyebrow" lang="en">THE PEOPLE BEHIND JUHANI</span>
      <h2 id="ja-story-title">অভিজ্ঞতার শক্তিতে,<br>গুণমানের ধারাবাহিকতায়।</h2>
      <p class="ja-lead">আপনার নির্ভরযোগ্য ফিশিং গিয়ার সাপ্লায়ার</p>
      <p>আমরা দৈনন্দিন ব্যবহার-উপযোগী টেকসই ফিশিং নেট ও মাছ ধরার অন্যান্য সরঞ্জাম সরবরাহ করি, যাতে জেলে ও মাছচাষিদের কাজ আরও সহজ হয়।</p>
      <div class="ja-story-line"><span><?php echo ja_icon('check'); ?> টেকসই ফিশিং নেট</span><span><?php echo ja_icon('check'); ?> নির্ভরযোগ্য সরঞ্জাম</span></div>
      <a class="ja-link" href="<?php echo esc_url($shop); ?>">আমাদের কালেকশন দেখুন <?php echo ja_icon('arrow'); ?></a>
    </div>
  </section>

  <section class="ja-mission ja-container" aria-labelledby="ja-mission-title" lang="bn">
    <div class="ja-mission-heading"><span class="ja-icon-tile"><?php echo ja_icon('compass'); ?></span><span class="ja-eyebrow" lang="en">OUR PURPOSE</span><h2 id="ja-mission-title">প্রত্যেক মৎস্যজীবীর জন্য<br>একটি নিরাপদ ও শক্তিশালী ভবিষ্যৎ।</h2></div>
    <div class="ja-mission-copy"><span class="ja-small-label">আমাদের লক্ষ্য</span><p>আমরা নির্ভরযোগ্য মাছ ধরার জাল ও সরঞ্জাম সরবরাহে প্রতিশ্রুতিবদ্ধ, যা জেলেদের আত্মবিশ্বাসের সাথে কাজ করতে সহায়তা করে।</p><p>মানসম্মত পণ্য, কার্যকর সমাধান এবং দীর্ঘমেয়াদী সহায়তার মাধ্যমে আমরা মাছ ধরার অভিজ্ঞতাকে আরও নিরাপদ এবং জনগোষ্ঠীকে আরও শক্তিশালী করে গড়ে তোলার লক্ষ্য রাখি।</p></div>
  </section>

  <section class="ja-values ja-section ja-container" aria-labelledby="ja-values-title" lang="bn">
    <div class="ja-section-heading"><div><span class="ja-eyebrow" lang="en">WHY CHOOSE JUHANI</span><h2 id="ja-values-title">উন্নত মান ও নির্ভরযোগ্যতার অঙ্গীকার।</h2></div><p>সাশ্রয়ী ও কার্যকর ফিশিং সলিউশন, যা আপনার কাজকে করে আরও সহজ ও দ্রুত।</p></div>
    <div class="ja-value-grid">
      <?php $values=[['shield','কঠোর মান নিয়ন্ত্রণ','আমাদের প্রতিটি জাল শক্তি এবং স্থায়িত্বের পরীক্ষায় উত্তীর্ণ।'],['truck','দ্রুত ও সুরক্ষিত ডেলিভারি','প্রতিটি অর্ডার পৌঁছে যায় নিরাপদে এবং নির্ধারিত সময়ে।'],['people','মাঠপর্যায়ে পরীক্ষিত ও সমাদৃত','জেলে ও মাছচাষিদের বিশ্বস্ততা ও নির্ভরতার প্রতীক।'],['leaf','টেকসই ভবিষ্যৎ','দায়িত্বশীল মৎস্য আহরণ পদ্ধতি ও উপকূলীয় জনগোষ্ঠীকে সহায়তা করা।']]; foreach($values as $i=>$value): ?>
      <article class="ja-value-card"><div class="ja-value-top"><span class="ja-icon-tile"><?php echo ja_icon($value[0]); ?></span><span class="ja-card-number" aria-hidden="true"><?php echo esc_html(str_pad($i+1,2,'0',STR_PAD_LEFT)); ?></span></div><h3><?php echo esc_html($value[1]); ?></h3><p><?php echo esc_html($value[2]); ?></p></article>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="ja-impact ja-container" aria-labelledby="ja-impact-title" lang="bn" style="--ja-impact-image:url('<?php echo esc_url($ocean); ?>')">
    <div class="ja-impact-copy"><span class="ja-eyebrow ja-light" lang="en">BEYOND THE WATER</span><h2 id="ja-impact-title">আরও শক্তিশালী সম্প্রদায়।<br>আরও সুখী আগামীকাল।</h2><p>সমুদ্রে ও তার বাইরে আরও ভালো সুযোগ সৃষ্টি করে—এমন নির্ভরযোগ্য পণ্য পৌঁছে দেওয়ার লক্ষ্যে আমরা জেলে ও অংশীদারদের সাথে নিবিড়ভাবে কাজ করি।</p><a class="ja-link ja-link-light" href="#ja-contact">আমাদের সঙ্গে কথা বলুন <?php echo ja_icon('arrow'); ?></a></div>
    <div class="ja-impact-stats"><div><strong>৫০০<span>+</span></strong><span>জেলেদের সহায়তা প্রদান</span></div><div><strong>২৫<span>+</span></strong><span>উপকূলীয় সম্প্রদায়</span></div><div><strong>১০<span>+</span></strong><span>দীর্ঘদিনের সম্মিলিত অভিজ্ঞতা</span></div></div>
  </section>

  <section class="ja-reviews ja-section ja-container" aria-labelledby="ja-reviews-title" lang="bn">
    <div class="ja-review-intro"><div><span class="ja-eyebrow" lang="en">TRUST, IN THEIR WORDS</span><h2 id="ja-reviews-title">ভরসার কথা,<br>আমাদের গ্রাহকদের মুখে।</h2></div><div class="ja-rating" aria-label="৫ এর মধ্যে ৪.৯ রেটিং"><strong>৪.৯<span>/ ৫</span></strong><div><span class="ja-stars" aria-hidden="true">★★★★★</span><span>এক্সিলেন্ট · ৯০০০+ গ্রাহকের মতামত</span></div></div></div>
    <div class="ja-review-grid">
      <?php $reviews=[['চমৎকার মানের এবং খুব টেকসই জাল।','রহমান ফিশারিজ'],['পাইকারি অর্ডারের জন্য নির্ভরযোগ্য সরবরাহকারী।','গ্লোবাল অ্যাকুয়া সাপ্লাইস'],['শক্তিশালী, হালকা এবং সহজে ব্যবহারযোগ্য।','কামাল হাসান, মৎস্যজীবী']]; foreach($reviews as $review): ?>
      <figure class="ja-review-card"><span class="ja-quote-mark" aria-hidden="true">“</span><blockquote><?php echo esc_html($review[0]); ?></blockquote><figcaption><span></span><?php echo esc_html($review[1]); ?></figcaption></figure>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="ja-insights-wrap" aria-labelledby="ja-insights-title" lang="bn"><div class="ja-section ja-container">
    <div class="ja-section-heading"><div><span class="ja-eyebrow" lang="en">FROM OUR JOURNAL</span><h2 id="ja-insights-title">লেটেস্ট ইনসাইট ও এক্সপার্ট গাইড।</h2><p>বিশেষজ্ঞসুলভ জ্ঞান, মাছ ধরার পরামর্শ এবং সংশ্লিষ্ট শিল্পের হালনাগাদ তথ্য।</p></div><a class="ja-link" href="<?php echo esc_url($blog); ?>">আরও পড়ুন <?php echo ja_icon('arrow'); ?></a></div>
    <div class="ja-insight-grid">
      <?php foreach($articles as $i=>$article): $photo=get_the_post_thumbnail_url($article->ID,'large')?:$asset.'Blog-0'.($i+1).'.webp'; ?>
      <article class="ja-insight-card"><a class="ja-insight-image" href="<?php echo esc_url(get_permalink($article->ID)); ?>"><img src="<?php echo esc_url($photo); ?>" alt="" loading="lazy" width="600" height="400"><span class="ja-read-arrow" aria-hidden="true"><?php echo ja_icon('arrow'); ?></span></a><div class="ja-insight-copy"><span class="ja-small-label">ফিশিং গাইড</span><h3><a href="<?php echo esc_url(get_permalink($article->ID)); ?>"><?php echo esc_html(get_the_title($article)); ?></a></h3><a class="ja-article-link" href="<?php echo esc_url(get_permalink($article->ID)); ?>">গাইডটি পড়ুন <?php echo ja_icon('arrow'); ?></a></div></article>
      <?php endforeach; ?>
      <?php if(!$articles): ?><p class="ja-empty">আমাদের পরবর্তী গাইড শীঘ্রই আসছে। জাল বাছাইয়ে সাহায্য পেতে আমাদের সঙ্গে কথা বলুন।</p><?php endif; ?>
    </div>
  </div></section>

  <section id="ja-contact" class="ja-contact ja-container" aria-labelledby="ja-contact-title" lang="bn">
    <div class="ja-contact-image"><img src="<?php echo esc_url($asset.'About-02.webp'); ?>" alt="নদীর পানিতে জাল ছুড়ছেন একজন মৎস্যজীবী" loading="lazy" width="900" height="600"></div>
    <div class="ja-contact-copy"><span class="ja-eyebrow" lang="en">LET’S TALK FISHING</span><h2 id="ja-contact-title">আপনার পরবর্তী পথচলায়,<br>আমরা আছি পাশে।</h2><p>সঠিক জাল বাছাই কিংবা পাইকারি অর্ডার—আপনার প্রয়োজন নিয়ে আমাদের সঙ্গে কথা বলুন।</p><div class="ja-contact-buttons"><a class="ja-button" href="https://wa.me/8801889164315" target="_blank" rel="noopener">WhatsApp-এ কথা বলুন <?php echo ja_icon('arrow'); ?></a><a class="ja-phone" href="tel:+8801889164315"><?php echo ja_icon('phone'); ?> <span lang="en">+880 1889 164315</span></a></div></div>
  </section>
  <div class="ja-preview-note ja-container"><span><span class="ja-status-dot"></span> About Us design preview</span><a href="<?php echo esc_url(get_permalink($about_page->ID)); ?>">বর্তমান About page দেখুন <?php echo ja_icon('arrow'); ?></a></div>
</main>
<?php wp_reset_postdata(); get_footer(); ?>
