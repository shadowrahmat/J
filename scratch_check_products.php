<?php
require 'wp-load.php';
$products = wc_get_products(['limit' => 5]);
foreach ($products as $p) {
    echo "ID: " . $p->get_id() . " | Title: " . $p->get_name() . " | Type: " . $p->get_type() . "\n";
    if ($p->is_type('variable')) {
        $variations = $p->get_children();
        echo "  Variations: " . implode(', ', $variations) . "\n";
        foreach (array_slice($variations, 0, 2) as $vid) {
            $v = wc_get_product($vid);
            echo "    Var ID $vid: " . json_encode($v->get_attributes()) . "\n";
        }
    }
}

