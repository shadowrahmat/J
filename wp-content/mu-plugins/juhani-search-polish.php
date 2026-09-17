<?php
/** Plugin Name: Juhani Search Polished Design */
if(!defined("ABSPATH")) exit;

add_action("wp_head", function(){
  if(is_admin()) return;
  echo '<style id="juhani-search-polished">
    /* Popup shell — sharp, no radius per brand */
    #elementor-popup-modal-1214 .dialog-widget-content,
    #elementor-popup-modal-1208 .dialog-widget-content{
      border-radius: 0 !important;
      overflow: hidden !important;
      border: 1px solid #e5e7eb !important;
      box-shadow: 0 20px 60px rgba(16,57,94,.18) !important;
    }
    #elementor-popup-modal-1214 .dialog-close-button i,
    #elementor-popup-modal-1208 .dialog-close-button i{ color:#10395E !important; }

    /* Search input — fused with icon, single field look */
    .elementor-1214 .elementor-element-b579efa .e-search-form,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-form,
    .elementor-1207 .elementor-element-7adadd6a .e-search-form{
      display:flex !important;
      align-items:center !important;
      gap:0 !important;
      background:#f8fafc !important;
      border:1px solid #e2e8f0 !important;
      border-radius:0 !important;
      overflow:visible !important;
      padding:0 !important;
      position:relative !important;
      transition:border-color .18s, box-shadow .18s, background .18s !important;
    }
    .elementor-1214 .elementor-element-b579efa .e-search-form:focus-within,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-form:focus-within,
    .elementor-1207 .elementor-element-7adadd6a .e-search-form:focus-within{
      background:#ffffff !important;
      border-color:#4CA2D9 !important;
      box-shadow:0 0 0 4px rgba(76,162,217,.16) !important;
    }
    .elementor-1214 .elementor-element-b579efa .e-search-input,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-input,
    .elementor-1207 .elementor-element-7adadd6a .e-search-input{
      flex:1 1 auto !important;
      min-width:0 !important;
      background:transparent !important;
      border:none !important;
      border-radius:0 !important;
      box-shadow:none !important;
      padding:10px 6px 10px 12px !important;
      height:36px !important;
      min-height:36px !important;
      font-size:14px !important;
      font-family:"Poppins",sans-serif !important;
      outline:none !important;
    }
    .elementor-1214 .elementor-element-b579efa .e-search-input:focus,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-input:focus,
    .elementor-1207 .elementor-element-7adadd6a .e-search-input:focus{
      background:transparent !important;
      border:none !important;
      box-shadow:none !important;
    }
    .elementor-1214 .elementor-element-b579efa .e-search-submit,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-submit,
    .elementor-1207 .elementor-element-7adadd6a .e-search-submit{
      flex:0 0 auto !important;
      display:inline-flex !important;
      align-items:center !important;
      justify-content:center !important;
      width:36px !important;
      height:36px !important;
      min-width:36px !important;
      margin:0 !important;
      padding:0 !important;
      background:transparent !important;
      border:none !important;
      border-radius:0 !important;
      box-shadow:none !important;
      color:#10395E !important;
    }
    .elementor-1214 .elementor-element-b579efa .e-search-submit:hover,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-submit:hover{ background:rgba(16,57,94,.06) !important; }
    .elementor-1214 .elementor-element-b579efa .e-search-input::placeholder,
    .elementor-1208 .elementor-element-2a92cc5f .e-search-input::placeholder{ color:#94a3b8 !important; }

    /* Results container — desktop: keep as is, no forced 100% (preserve 720px polished) */
    .elementor-1214 .e-search-results-container,
    .elementor-1208 .e-search-results-container,
    .elementor-1207 .e-search-results-container{
      background:transparent !important;
      padding:4px 0 8px !important;
      gap:10px !important;
    }
    .elementor-1214 .e-search-results-container > div,
    .elementor-1208 .e-search-results-container > div,
    .elementor-1207 .e-search-results-container > div{
      border:none !important;
      background:transparent !important;
    }

    /* Result card — premium row, zero radius per brand */
    .elementor-1212, .elementor-134{
      --card-radius:0;
      --card-border:#eef2f7;
      --card-shadow:0 6px 22px rgba(16,57,94,.06);
    }
    .elementor-1214 .e-search-results-container .elementor-1212,
    .elementor-1214 .e-search-results-container .elementor-134{
      border-radius:0 !important;
      overflow:hidden !important;
    }

    /* Card row polish: target the loop item wrapper inside results */
    .e-search-results-container .elementor-1212 .elementor-element-d8a74b5,
    .e-search-results-container .elementor-1212 .elementor-element-f1f3ea9{
      background:#ffffff !important;
      border:1px solid var(--card-border) !important;
      border-radius:0 !important;
      overflow:hidden !important;
      box-shadow:var(--card-shadow) !important;
      transition:transform .18s ease, box-shadow .18s ease, border-color .18s ease !important;
    }
    .e-search-results-container .elementor-1212:hover .elementor-element-d8a74b5{
      transform:translateY(-1px);
      border-color:#dbe7f2 !important;
      box-shadow:0 10px 28px rgba(16,57,94,.10) !important;
    }

    /* Image polish — zero radius */
    .e-search-results-container .elementor-1212 .elementor-widget-image img,
    .e-search-results-container .elementor-widget-image img{
      border-radius:0 !important;
      aspect-ratio:1 / 1 !important;
      object-fit:cover !important;
      transition:transform .35s ease !important;
    }
    .e-search-results-container .elementor-1212:hover .elementor-widget-image img{
      transform:scale(1.03);
    }
    /* Make image + title fully clickable (link covers card) */
    .e-search-results-container .elementor-1212 a{ text-decoration:none !important; }
    .e-search-results-container .elementor-1212 .elementor-widget-image a,
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-title a{ display:block !important; cursor:pointer !important; }
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-title a:hover{ color:#4CA2D9 !important; }

    /* Title & price */
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-title .elementor-heading-title,
    .e-search-results-container .elementor-widget-woocommerce-product-title .elementor-heading-title{
      font-family:"Poppins",sans-serif !important;
      font-weight:700 !important;
      color:#0f2f44 !important;
      line-height:1.25 !important;
      display:-webkit-box !important;
      -webkit-line-clamp:2 !important;
      -webkit-box-orient:vertical !important;
      overflow:hidden !important;
    }
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-price .price,
    .e-search-results-container .elementor-widget-woocommerce-product-price .price{
      font-family:"Poppins",sans-serif !important;
      font-weight:700 !important;
      color:#10395E !important;
      font-size:15px !important;
    }
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-price del,
    .e-search-results-container del{ opacity:.55 !important; font-weight:500 !important; }
    .e-search-results-container .elementor-1212 .elementor-widget-woocommerce-product-price ins{ text-decoration:none !important; }

    /* Empty / loader — zero radius */
    .e-search-results-container .e-search-nothing-found{
      font-family:"Poppins",sans-serif !important;
      color:#64748b !important;
      background:#f8fafc !important;
      border:1px dashed #e2e8f0 !important;
      border-radius:0 !important;
      padding:18px !important;
      text-align:center !important;
    }
    .e-search-results-container .e-search-loader{
      border-width:2px !important;
      border-top-color:#10395E !important;
    }

    /* Phone only: product card width = search input width */
    @media(max-width:767px){
      #elementor-popup-modal-1214 .dialog-widget-content,
      #elementor-popup-modal-1208 .dialog-widget-content{ border-radius:0 !important; width:96vw !important; max-width:96vw !important; }
      #elementor-popup-modal-1214 .dialog-message,
      #elementor-popup-modal-1208 .dialog-message{ width:100% !important; max-width:100% !important; padding:16px 14px !important; box-sizing:border-box !important; }
      .elementor-1214 .elementor-element-a581a7a,
      .elementor-1214 .elementor-element-abc3ada,
      .elementor-1214 .elementor-element-b579efa,
      .elementor-1208 .elementor-element-2a92cc5f{ width:100% !important; max-width:100% !important; box-sizing:border-box !important; --e-search-results-width:100% !important; }
      .elementor-1214 .e-search,
      .elementor-1208 .e-search{ width:100% !important; max-width:100% !important; display:block !important; }

      /* Make search form relative container so results container attaches to form width */
      .elementor-1214 .elementor-element-b579efa .e-search-form,
      .elementor-1208 .elementor-element-2a92cc5f .e-search-form,
      .e-search-form{
        width:100% !important;
        max-width:100% !important;
        box-sizing:border-box !important;
        margin:0 !important;
        position:relative !important;
      }

      /* Allow results container to break out of input wrapper and match form 100% */
      .elementor-1214 .e-search-input-wrapper,
      .elementor-1208 .e-search-input-wrapper,
      .e-search-input-wrapper{
        position:static !important;
      }

      /* Results container matches search form outer border 1:1 */
      .elementor-1214 .e-search-results-container,
      .elementor-1208 .e-search-results-container,
      .e-search-results-container{
        position:absolute !important;
        width:calc(100% + 2px) !important;
        max-width:calc(100% + 2px) !important;
        left:-1px !important;
        right:-1px !important;
        margin:0 !important;
        padding:0 !important;
        box-sizing:border-box !important;
        overflow-y:auto !important;
        scrollbar-width:none !important;
        -ms-overflow-style:none !important;
      }
      .elementor-1214 .e-search-results-container::-webkit-scrollbar,
      .elementor-1208 .e-search-results-container::-webkit-scrollbar,
      .e-search-results-container::-webkit-scrollbar{ width:0 !important; height:0 !important; display:none !important; }

      /* Results list wrapper: 100% width, remove default 16px horizontal padding */
      .elementor-1214 .e-search-results-container .e-search-results,
      .elementor-1208 .e-search-results-container .e-search-results,
      .e-search-results-container .e-search-results{
        width:100% !important;
        max-width:100% !important;
        box-sizing:border-box !important;
        padding-left:0 !important;
        padding-right:0 !important;
        padding-top:10px !important;
        padding-bottom:10px !important;
        scrollbar-width:none !important;
        -ms-overflow-style:none !important;
      }
      .e-search-results-container .e-search-results::-webkit-scrollbar{ width:0 !important; height:0 !important; display:none !important; }

      /* Loop items / product cards: flush 100% width, exactly matching search form */
      .elementor-1214 .e-search-results-container .elementor-1212,
      .elementor-1208 .e-search-results-container .elementor-1212,
      .elementor-1214 .e-search-results-container .e-loop-item,
      .elementor-1208 .e-search-results-container .e-loop-item,
      .e-search-results-container .e-loop-item,
      .e-search-results-container .elementor-1212,
      .e-search-results-container .elementor-134{
        width:100% !important;
        max-width:100% !important;
        margin-left:0 !important;
        margin-right:0 !important;
        padding-left:0 !important;
        padding-right:0 !important;
        box-sizing:border-box !important;
      }
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-element-d8a74b5,
      .elementor-1208 .e-search-results-container .elementor-1212 .elementor-element-d8a74b5,
      .e-search-results-container .elementor-element-d8a74b5{
        width:100% !important;
        max-width:100% !important;
        margin-left:0 !important;
        margin-right:0 !important;
        box-sizing:border-box !important;
      }

      /* Keep row: 46% image + 54% text */
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-element-f1f3ea9{
        flex-direction:row !important; flex-wrap:nowrap !important; align-items:center !important; gap:10px !important;
      }
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-element-8042cc7{ flex:0 0 46% !important; max-width:46% !important; width:46% !important; }
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-element-048affb{ flex:1 1 auto !important; min-width:0 !important; }
      .elementor-1214 .elementor-element-b579efa .e-search-input,
      .elementor-1208 .elementor-element-2a92cc5f .e-search-input{ padding:12px 10px !important; }
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-widget-image,
      .elementor-1214 .e-search-results-container .elementor-1212 .elementor-widget-image img{ width:100% !important; max-width:100% !important; }
    }
    /* Global zero-radius safety for search popup — override any Elementor inline radius */
    #elementor-popup-modal-1214 .dialog-widget-content *,
    #elementor-popup-modal-1208 .dialog-widget-content *,
    .elementor-1214 .e-search-input, .elementor-1208 .e-search-input, .elementor-1207 .e-search-input,
    .elementor-1214 .elementor-widget-image img, .elementor-1212 .elementor-widget-image img{ border-radius:0 !important; }
  </style>';
}, 101);
