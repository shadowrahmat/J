<?php
require_once 'wp-load.php';

// Test what Refresh_Search produces for different post_id parameters
$refresh = new \ElementorPro\Modules\Search\Data\Endpoints\Refresh_Search(
    new \ElementorPro\Modules\Search\Data\Controller()
);

echo "=== TEST WITH post_id = 1267 (Header) ===\n";
try {
    $req1 = new \WP_REST_Request('POST', '/elementor-pro/v1/refresh-search');
    $req1->set_param('post_id', 1267);
    $req1->set_param('widget_id', 'c66d644');
    $req1->set_param('search_term', 'cast');
    $res1 = $refresh->get_updated_search_widget_markup($req1);
    echo "Markup length: " . strlen($res1['data']) . "\n";
    file_put_contents('scratch_markup_1267.html', $res1['data']);
    echo "Contains product title? " . (strpos($res1['data'], 'Cast Net') !== false ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Error 1267: " . $e->getMessage() . "\n";
}

echo "\n=== TEST WITH post_id = 30 (Contact Us) ===\n";
try {
    $req2 = new \WP_REST_Request('POST', '/elementor-pro/v1/refresh-search');
    $req2->set_param('post_id', 30);
    $req2->set_param('widget_id', 'c66d644');
    $req2->set_param('search_term', 'cast');
    $res2 = $refresh->get_updated_search_widget_markup($req2);
    echo "Markup length: " . strlen($res2['data']) . "\n";
    file_put_contents('scratch_markup_30.html', $res2['data']);
    echo "Contains product title? " . (strpos($res2['data'], 'Cast Net') !== false ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Error 30: " . $e->getMessage() . "\n";
}

echo "\n=== TEST WITH post_id = 50 (About) ===\n";
try {
    $req3 = new \WP_REST_Request('POST', '/elementor-pro/v1/refresh-search');
    $req3->set_param('post_id', 50);
    $req3->set_param('widget_id', 'c66d644');
    $req3->set_param('search_term', 'cast');
    $res3 = $refresh->get_updated_search_widget_markup($req3);
    echo "Markup length: " . strlen($res3['data']) . "\n";
    file_put_contents('scratch_markup_50.html', $res3['data']);
    echo "Contains product title? " . (strpos($res3['data'], 'Cast Net') !== false ? 'YES' : 'NO') . "\n";
} catch (\Throwable $e) {
    echo "Error 50: " . $e->getMessage() . "\n";
}

