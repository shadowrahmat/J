<?php
require_once 'wp-load.php';

$posts = get_posts([
    'post_type' => 'elementor_library',
    'numberposts' => -1,
    'post_status' => 'any'
]);

foreach ($posts as $p) {
    echo $p->ID . ' | ' . $p->post_title . ' | ' . get_post_meta($p->ID, '_elementor_template_type', true) . PHP_EOL;
}

