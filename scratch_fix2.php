<?php
require 'wp-load.php';
global $wpdb;
$meta=get_post_meta(1207,'_elementor_data',true);
$j=is_array($meta)?json_encode($meta):$meta;
$orig=$j;
$j=str_replace('"template_id":1341','"template_id":134',$j);
$j=str_replace('"results_custom_width":{"unit":"px","size":576','"results_custom_width":{"unit":"px","size":720',$j);
$j=str_replace('"results_max_height":{"unit":"px","size":380','"results_max_height":{"unit":"px","size":480',$j);
if($j!==$orig){
  update_post_meta(1207,'_elementor_data', json_decode($j,true));
  $wpdb->query("DELETE FROM ef_postmeta WHERE post_id=1207 AND meta_key='_elementor_css'");
  @unlink('wp-content/uploads/elementor/css/post-1207.css');
  echo "1207 fixed\n";
} else echo "1207 no change\n";
$m2=get_post_meta(1207,'_elementor_data',true);
if(is_array($m2)) $m2=json_encode($m2);
echo substr($m2, strpos($m2,'7adadd6a')-100, 700)."\n";

// override CSS for search results popup width + image fix via mu-plugin
$css = '
add_action("wp_head", function(){
  if(!is_admin()){
    echo \'<style id="juhani-search-fix-override">
      /* Search popup results width fix */
      #elementor-popup-modal-1208 .dialog-message{ width: 720px !important; max-width: 92vw !important; }
      #elementor-popup-modal-1207 .dialog-message{ width: 720px !important; max-width: 92vw !important; }
      .elementor-1208 .e-search-results-container{ width: 100% !important; max-width: 720px !important; }
      .elementor-1207 .e-search-results-container{ width: 100% !important; max-width: 720px !important; }
      /* Force product image visible in loop template 134 */
      .e-search-results-container .theme-post-featured-image img,
      .e-search-results-container .elementor-widget-theme-post-featured-image img{ display:block !important; width:100% !important; height:auto !important; visibility:visible !important; opacity:1 !important; }
      @media(max-width:767px){
        #elementor-popup-modal-1208 .dialog-message, #elementor-popup-modal-1207 .dialog-message{ width: 96vw !important; }
      }
    </style>\';
  }
}, 100);
';

$file='wp-content/mu-plugins/juhani-search-width-fix.php';
if(!file_exists($file)){
  file_put_contents($file, "<?php\n/** Plugin Name: Juhani Search Width & Image Fix */\nif(!defined(\"ABSPATH\")) exit;\n".$css);
  echo "created $file\n";
} else {
  echo "$file exists\n";
}
echo "DONE\n";
