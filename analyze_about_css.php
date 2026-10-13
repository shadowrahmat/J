<?php
$about_html = file_get_contents('scratch_about.html');

// Extract all <link rel="stylesheet">
preg_match_all('/<link[^>]+rel=[\'"]stylesheet[\'"][^>]+href=[\'"]([^\'"]+)[\'"]/i', $about_html, $links);

$css_contents = [];
foreach ($links[1] as $href) {
    // Convert url to local file
    $local_path = str_replace(['http://localhost/juhani/', '//localhost/juhani/'], '', $href);
    $local_path = preg_replace('/\?.*$/', '', $local_path);
    if (file_exists($local_path)) {
        $css_contents[$local_path] = file_get_contents($local_path);
    }
}

// Extract all <style> blocks
preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $about_html, $styles);
foreach ($styles[1] as $idx => $s) {
    $css_contents["inline_style_{$idx}"] = $s;
}

echo "Total CSS sources checked: " . count($css_contents) . "\n";

$targets = [
    'elementor-page-title',
    'product_title',
    'elementor-widget-woocommerce-product-title',
    'entry-title',
    'elementor-heading-title',
    '.price',
    'e-search'
];

foreach ($css_contents as $source => $css) {
    foreach ($targets as $target) {
        if (strpos($css, $target) !== false) {
            echo "--- Source: $source has target: $target ---\n";
            // Find matches and print surrounding context
            preg_match_all('/([^{}]*' . preg_quote($target, '/') . '[^{}]*\{[^}]+\})/is', $css, $m);
            foreach (array_slice($m[1], 0, 5) as $rule) {
                echo trim(preg_replace('/\s+/', ' ', $rule)) . "\n";
            }
        }
    }
}

