<?php
/** Native Elementor controls for the reusable WooCommerce catalog. */
if (!defined('ABSPATH')) exit;

class Mixora_Juhani_Catalog_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'mixora_juhani_catalog'; }
    public function get_title() { return 'Mixora Product Filter'; }
    public function get_icon() { return 'eicon-products'; }
    public function get_categories() { return ['general']; }
    public function get_keywords() { return ['mixora', 'juhani', 'woocommerce', 'shop', 'product', 'filter']; }
    public function get_style_depends() { return ['mixora-juhani-catalog-style']; }
    public function get_script_depends() { return ['mixora-juhani-catalog-script']; }
    protected function is_dynamic_content(): bool { return true; }

    // This also displays the default component class in Advanced → CSS Classes.
    public function get_stack($with_common_controls = true) {
        $stack = parent::get_stack($with_common_controls);
        if ($with_common_controls && isset($stack['controls']['_css_classes'])) {
            $stack['controls']['_css_classes']['default'] = 'mixora-juhani-widget';
        }
        return $stack;
    }

    protected function register_controls() {
        $this->start_controls_section('catalog_content', ['label' => 'Products & filters']);
        $categories = [];
        if (taxonomy_exists('product_cat')) {
            $terms = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
            if (!is_wp_error($terms)) foreach ($terms as $term) $categories[$term->term_id] = $term->name;
        }
        $this->add_control('category_ids', [
            'label' => 'Product categories', 'type' => \Elementor\Controls_Manager::SELECT2,
            'multiple' => true, 'options' => $categories, 'default' => [],
            'description' => 'Leave empty for the full catalog. Category archives use their current category.',
        ]);
        foreach (['collection_label' => ['Collection label', 'Cast Nets'], 'filter_title' => ['Filter title', 'Refine your choice'], 'reset_text' => ['Reset button', 'Reset all'], 'action_text' => ['Product button', 'মাপ ও বিকল্প বাছাই করুন']] as $key => $field) {
            $this->add_control($key, ['label' => $field[0], 'type' => \Elementor\Controls_Manager::TEXT, 'default' => $field[1]]);
        }
        $this->add_control('show_search', ['label' => 'Catalog search', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => '', 'return_value' => 'yes']);
        $this->add_control('search_placeholder', ['label' => 'Search placeholder', 'type' => \Elementor\Controls_Manager::TEXT, 'default' => 'Search products…', 'condition' => ['show_search' => 'yes']]);
        $this->add_control('note', ['label' => 'Closing note', 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => 'আপনার প্রয়োজন অনুযায়ী মাপ ও বিকল্প বেছে নিন।']);
        $this->end_controls_section();

        $this->start_controls_section('catalog_help', ['label' => 'Help card']);
        $this->add_control('show_help', ['label' => 'Show help card', 'type' => \Elementor\Controls_Manager::SWITCHER, 'default' => 'yes', 'return_value' => 'yes']);
        foreach (['help_title' => ['Title', 'মাপ নিয়ে দ্বিধায়?'], 'help_text' => ['Description', 'আপনার প্রয়োজন বলুন। উপযুক্ত জাল বেছে নিতে সাহায্য করব।'], 'help_link_text' => ['Link text', 'WhatsApp-এ কথা বলুন']] as $key => $field) {
            $this->add_control($key, ['label' => $field[0], 'type' => \Elementor\Controls_Manager::TEXTAREA, 'default' => $field[1], 'condition' => ['show_help' => 'yes']]);
        }
        $this->add_control('help_url', ['label' => 'Link', 'type' => \Elementor\Controls_Manager::URL, 'default' => ['url' => 'https://wa.me/8801889164315', 'is_external' => true], 'condition' => ['show_help' => 'yes']]);
        $this->end_controls_section();

        $this->start_controls_section('catalog_layout', ['label' => 'Catalog layout', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        $this->add_responsive_control('columns', [
            'label' => 'Product columns', 'type' => \Elementor\Controls_Manager::NUMBER,
            'min' => 1, 'max' => 4, 'default' => 3, 'tablet_default' => 2, 'mobile_default' => 2,
        ]);
        $this->add_responsive_control('product_gap', [
            'label' => 'Product spacing', 'type' => \Elementor\Controls_Manager::SLIDER,
            'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 60]],
            'default' => ['size' => 23, 'unit' => 'px'], 'tablet_default' => ['size' => 20, 'unit' => 'px'], 'mobile_default' => ['size' => 17, 'unit' => 'px'],
        ]);
        $this->add_responsive_control('sidebar_width', [
            'label' => 'Sidebar width', 'type' => \Elementor\Controls_Manager::SLIDER,
            'devices' => ['desktop', 'tablet'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 180, 'max' => 420]],
            'default' => ['size' => 265, 'unit' => 'px'], 'tablet_default' => ['size' => 235, 'unit' => 'px'],
        ]);
        $this->add_responsive_control('layout_gap', [
            'label' => 'Sidebar spacing', 'type' => \Elementor\Controls_Manager::SLIDER,
            'devices' => ['desktop', 'tablet'], 'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 80]],
            'default' => ['size' => 32, 'unit' => 'px'], 'tablet_default' => ['size' => 24, 'unit' => 'px'],
        ]);
        $this->add_control('card_radius', ['label' => 'Corner radius', 'type' => \Elementor\Controls_Manager::SLIDER, 'size_units' => ['px'], 'range' => ['px' => ['min' => 0, 'max' => 40]], 'default' => ['size' => 12, 'unit' => 'px']]);
        $this->end_controls_section();

        $this->start_controls_section('catalog_colors', ['label' => 'Catalog colors', 'tab' => \Elementor\Controls_Manager::TAB_STYLE]);
        foreach (['accent_color' => ['Accent', '#236b9c'], 'ink_color' => ['Text', '#223747'], 'line_color' => ['Border', '#e1eaf0']] as $key => $field) {
            $this->add_control($key, ['label' => $field[0], 'type' => \Elementor\Controls_Manager::COLOR, 'alpha' => false, 'default' => $field[1]]);
        }
        $this->end_controls_section();
    }

    protected function render() {
        echo Mixora_Juhani_Catalog::render($this->get_settings_for_display());
    }
}
