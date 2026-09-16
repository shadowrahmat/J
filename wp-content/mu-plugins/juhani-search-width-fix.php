<?php
/** Plugin Name: Juhani Search Width & Image Fix */
if(!defined("ABSPATH")) exit;

add_action("wp_head", function(){
  if(!is_admin()){
    echo '<style id="juhani-search-fix-override">
      /* Search popup results width fix */
      #elementor-popup-modal-1208 .dialog-message, #elementor-popup-modal-1214 .dialog-message, #elementor-popup-modal-1207 .dialog-message{ width: 720px !important; max-width: 92vw !important; }
      .elementor-1208 .e-search-results-container, .elementor-1214 .e-search-results-container, .elementor-1207 .e-search-results-container{ width: 100% !important; max-width: 720px !important; }
      /* Header inline search dropdown — force image visible (681c745 now uses template 134) */
      .elementor-71 .e-search-results-container .theme-post-featured-image,
      .elementor-71 .e-search-results-container .elementor-widget-theme-post-featured-image,
      .elementor-71 .e-search-results-container .elementor-widget-image{ display:block !important; visibility:visible !important; }
      .elementor-71 .e-search-results-container .theme-post-featured-image img,
      .elementor-71 .e-search-results-container .elementor-widget-theme-post-featured-image img,
      .elementor-71 .e-search-results-container .elementor-widget-image img{ display:block !important; width:100% !important; height:auto !important; max-height:180px !important; object-fit:cover !important; visibility:visible !important; opacity:1 !important; }
      /* Force product image visible in loop template 134 (popup) */
      .e-search-results-container .theme-post-featured-image img,
      .e-search-results-container .elementor-widget-theme-post-featured-image img{ display:block !important; width:100% !important; height:auto !important; visibility:visible !important; opacity:1 !important; }
      /* Footer search popup 1214 — ensure image + row layout visible */
      .elementor-1214 .e-search-results-container .elementor-widget-image{ display:block !important; }
      .elementor-1214 .e-search-results-container .elementor-widget-image img{ display:block !important; width:100% !important; height:auto !important; max-height:180px !important; object-fit:cover !important; }
      @media(max-width:767px){
        #elementor-popup-modal-1208 .dialog-message, #elementor-popup-modal-1214 .dialog-message, #elementor-popup-modal-1207 .dialog-message{ width: 96vw !important; }
      }
    </style>';
  }
}, 100);
