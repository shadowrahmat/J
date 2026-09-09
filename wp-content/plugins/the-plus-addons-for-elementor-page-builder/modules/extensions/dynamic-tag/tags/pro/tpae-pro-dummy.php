<?php
namespace ElementPack\Includes\DynamicContent;

if ( ! defined( 'ABSPATH' ) ) { exit; } // Exit if accessed directly

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

class TPAE_Pro_Dummy_Tag extends Tag {

    private $group;
    private $slug;

    public function __construct( $group, $slug ) {
        $this->group = $group;
        $this->slug  = $slug;
    }

    public function get_name() {
        return 'tpae-pro-placeholder-' . $this->slug;
    }

    public function get_title() {
        return esc_html__( 'PRO Feature (Locked)', 'tpebl' );
    }

    public function get_group() {
        return $this->group;
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {}
}
