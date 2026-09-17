<?php
require 'wp-load.php';
global $wpdb;
$pfx=$wpdb->prefix;
// Rebuild 1212 with reliable linked widgets: theme-post-featured-image + theme-post-title
$template = [
  [
    "id"=>"d8a74b5",
    "elType"=>"container",
    "settings"=>[
      "background_background"=>"classic","background_color"=>"#FFFFFF",
      "border_border"=>"solid","border_width"=>["unit"=>"px","top"=>"1","right"=>"1","bottom"=>"1","left"=>"1","isLinked"=>true],
      "border_color"=>"#ECECEC",
      "padding"=>["unit"=>"px","top"=>"0","right"=>"0","bottom"=>"0","left"=>"0","isLinked"=>false]
    ],
    "elements"=>[
      [
        "id"=>"f1f3ea9",
        "elType"=>"container",
        "settings"=>[
          "content_width"=>"full","flex_direction"=>"row",
          "flex_gap"=>["column"=>"10","row"=>"0","isLinked"=>false,"unit"=>"px","size"=>10],
          "padding"=>["unit"=>"px","top"=>"10","right"=>"10","bottom"=>"10","left"=>"10","isLinked"=>true]
        ],
        "elements"=>[
          [
            "id"=>"8042cc7",
            "elType"=>"container",
            "settings"=>["content_width"=>"full","width"=>["unit"=>"%","size"=>34,"sizes"=>[]],"width_mobile"=>["unit"=>"%","size"=>46,"sizes"=>[]]],
            "elements"=>[
              [
                "id"=>"249f2f9",
                "elType"=>"widget",
                "settings"=>[
                  "__dynamic__"=>[
                    "image"=>"[elementor-tag id=\"3f250ac\" name=\"post-featured-image\" settings=\"%7B%22fallback%22%3A%7B%22url%22%3A%22%22%2C%22id%22%3A%22%22%2C%22size%22%3A%22%22%7D%7D\"]",
                    "link"=>"[elementor-tag id=\"adeeb66\" name=\"post-url\" settings=\"%7B%7D\"]"
                  ],
                  "image_size"=>"full","link_to"=>"custom","align"=>"start","width"=>["unit"=>"%","size"=>100,"sizes"=>[]]
                ],
                "elements"=>[],
                "widgetType"=>"theme-post-featured-image"
              ]
            ],
            "isInner"=>true
          ],
          [
            "id"=>"048affb",
            "elType"=>"container",
            "settings"=>["content_width"=>"full","flex_gap"=>["column"=>"0","row"=>"0","isLinked"=>false,"unit"=>"px","size"=>0],"width_mobile"=>["unit"=>"%","size"=>48,"sizes"=>[]]],
            "elements"=>[
              [
                "id"=>"6190c89",
                "elType"=>"widget",
                "settings"=>[
                  "__dynamic__"=>[
                    "title"=>"[elementor-tag id=\"\" name=\"post-title\" settings=\"%7B%22before%22%3A%22%22%2C%22after%22%3A%22%22%2C%22fallback%22%3A%22%22%7D\"]",
                    "link"=>"[elementor-tag id=\"bdf4ce8\" name=\"post-url\" settings=\"%7B%7D\"]"
                  ],
                  "title"=>"Add Your Heading Text Here","header_size"=>"h3",
                  "typography_typography"=>"custom","typography_font_family"=>"Poppins","typography_font_size"=>["unit"=>"px","size"=>16,"sizes"=>[]],"typography_font_weight"=>"600","typography_line_height"=>["unit"=>"px","size"=>22,"sizes"=>[]],
                  "title_color"=>"#0f2f44"
                ],
                "elements"=>[],
                "widgetType"=>"theme-post-title"
              ],
              [
                "id"=>"d4ff7ff",
                "elType"=>"widget",
                "settings"=>[],
                "elements"=>[],
                "widgetType"=>"woocommerce-product-price"
              ]
            ],
            "isInner"=>true
          ]
        ],
        "isInner"=>true
      ]
    ],
    "isInner"=>false
  ]
];
update_post_meta(1212,'_elementor_data',$template);
$wpdb->query("DELETE FROM {$pfx}postmeta WHERE post_id=1212 AND meta_key='_elementor_css'");
@unlink('wp-content/uploads/elementor/css/loop-1212.css');
@unlink('wp-content/uploads/elementor/css/post-1212.css');
echo "1212 rebuilt with theme-post-featured-image + theme-post-title linked\n";
$mm=get_post_meta(1212,'_elementor_data',true);
if(is_array($mm)) $mm=json_encode($mm);
echo substr($mm,0,800)."\n";
echo "DONE\n";
