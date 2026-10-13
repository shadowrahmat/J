<?php
require_once 'wp-load.php';

echo "=== WIDGET c66d644 SETTINGS ===\n";
$data_1267 = json_decode(get_post_meta(1267, '_elementor_data', true), true);

function find_widget_by_id($elements, $id) {
    foreach ($elements as $el) {
        if (isset($el['id']) && $el['id'] === $id) {
            return $el;
        }
        if (!empty($el['elements'])) {
            $found = find_widget_by_id($el['elements'], $id);
            if ($found) return $found;
        }
    }
    return null;
}

$search_widget = find_widget_by_id($data_1267, 'c66d644');
echo "Custom CSS in search widget:\n";
echo ($search_widget['settings']['custom_css'] ?? 'NONE') . "\n";

echo "\n=== TEMPLATE 1270 DATA ===\n";
$data_1270 = get_post_meta(1270, '_elementor_data', true);
echo substr($data_1270, 0, 500) . "...\n";
file_put_contents('scratch_1270.json', $data_1270);

$page_settings_1270 = get_post_meta(1270, '_elementor_page_settings', true);
echo "Page settings 1270:\n";
print_r($page_settings_1270);

echo "\n=== CONTACT PAGE (30) CUSTOM CSS / META ===\n";
$contact = get_post(30);
echo "Contact Title: " . ($contact ? $contact->post_title : 'not found') . "\n";
print_r(get_post_meta(30, '_elementor_page_settings', true));

echo "\n=== ABOUT PAGE (50) CUSTOM CSS / META ===\n";
$about = get_post(50);
echo "About Title: " . ($about ? $about->post_title : 'not found') . "\n";
print_r(get_post_meta(50, '_elementor_page_settings', true));


