<?php
/**
 * Plugin Name: Juhani Live Product Search Design
 * Description: Product data tags and scoped dropdown enhancements for editable search cards.
 */
if(!defined('ABSPATH'))exit;

function juhani_search_current_product(){
    return function_exists('wc_get_product')?wc_get_product(get_the_ID()):false;
}
function juhani_search_display_price($value){
    return wc_price($value,['decimals'=>(float)$value===floor((float)$value)?0:wc_get_price_decimals()]);
}

add_action('elementor/dynamic_tags/register',function($manager){
    $manager->register_group('juhani-search',['title'=>'Juhani Product Search']);
    abstract class Juhani_Search_Data_Tag extends \Elementor\Core\DynamicTags\Tag {
        public function get_group(){return 'juhani-search';}
        public function get_categories(){return [\Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY];}
    }
    class Juhani_Search_Price_Tag extends Juhani_Search_Data_Tag {
        public function get_name(){return 'juhani-search-price';}
        public function get_title(){return 'Product price range';}
        public function render(){
            $product=juhani_search_current_product();if(!$product)return;
            $min=$product->is_type('variable')?$product->get_variation_price('min',true):wc_get_price_to_display($product);
            $max=$product->is_type('variable')?$product->get_variation_price('max',true):$min;
            echo wp_kses_post(juhani_search_display_price($min));
            if((float)$min!==(float)$max)echo '<span class="juhani-search-result-price-dash"> – </span>'.wp_kses_post(juhani_search_display_price($max));
        }
    }
    class Juhani_Search_Stock_Tag extends Juhani_Search_Data_Tag {
        public function get_name(){return 'juhani-search-stock';}
        public function get_title(){return 'Product stock status';}
        public function render(){
            $product=juhani_search_current_product();if(!$product)return;
            echo '<span class="juhani-search-result-stock-label'.($product->is_in_stock()?'':' is-unavailable').'">'.($product->is_in_stock()?'In stock':'Out of stock').'</span>';
        }
    }
    class Juhani_Search_Category_Tag extends Juhani_Search_Data_Tag {
        public function get_name(){return 'juhani-search-category';}
        public function get_title(){return 'Product category label';}
        public function render(){
            $product=juhani_search_current_product();if(!$product)return;
            $names=wc_get_product_terms($product->get_id(),'product_cat',['fields'=>'names']);
            echo esc_html(!is_wp_error($names)&&$names?reset($names):'Product');
        }
    }
    class Juhani_Search_Measure_Tag extends Juhani_Search_Data_Tag {
        public function get_name(){return 'juhani-search-measurements';}
        public function get_title(){return 'Product Haat / Suta';}
        public function render(){
            $product=juhani_search_current_product();if(!$product)return;$parts=[];
            $haat=wc_get_product_terms($product->get_id(),'pa_haat',['fields'=>'names']);
            $suta=wc_get_product_terms($product->get_id(),'pa_suta',['fields'=>'names']);
            if(!is_wp_error($haat)&&$haat){usort($haat,'strnatcasecmp');$parts[]=reset($haat).(count($haat)>1?'–'.end($haat):'').' Haat';}
            if(!is_wp_error($suta)&&$suta){usort($suta,'strnatcasecmp');$parts[]=implode(' / ',$suta).' Suta';}
            echo esc_html(implode(' · ',$parts));
        }
    }
    $manager->register(new Juhani_Search_Price_Tag());
    $manager->register(new Juhani_Search_Stock_Tag());
    $manager->register(new Juhani_Search_Category_Tag());
    $manager->register(new Juhani_Search_Measure_Tag());
});

add_action('wp_enqueue_scripts',function(){
    // Search's AJAX dynamic CSS does not reliably include the Loop Item's page CSS.
    // Read the editable source from Elementor rather than maintaining a second active copy.
    $settings=(array)get_post_meta(1270,'_elementor_page_settings',true);
    if(!empty($settings['custom_css'])){
        wp_register_style('juhani-live-search-card',false);
        wp_enqueue_style('juhani-live-search-card');
        wp_add_inline_style('juhani-live-search-card',$settings['custom_css']);
    }
    $path=ABSPATH.'preview-assets/live-product-search.js';
    wp_enqueue_script('juhani-live-product-search-design',home_url('/preview-assets/live-product-search.js'),['elementor-frontend'],filemtime($path),true);
},40);
