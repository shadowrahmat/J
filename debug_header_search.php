<?php
require_once 'wp-load.php';

echo "=== HEADER 1267 DATA ===\n";
$data_1267 = get_post_meta(1267, '_elementor_data', true);
file_put_contents('scratch_header_1267.json', $data_1267);

echo "=== CHECKING SEARCH WIDGET IN 1267 ===\n";
$json = json_decode($data_1267, true);

function find_widgets($elements, &$found) {
    foreach ($elements as $el) {
        if (isset($el['widgetType'])) {
            $found[] = [
                'id' => $el['id'],
                'widgetType' => $el['widgetType'],
                'settings' => $el['settings'] ?? []
            ];
        }
        if (!empty($el['elements'])) {
            find_widgets($el['elements'], $found);
        }
    }
}

$widgets = [];
if (is_array($json)) {
    find_widgets($json, $widgets);
}

foreach ($widgets as $w) {
    if (strpos($w['widgetType'], 'search') !== false || strpos($w['widgetType'], 'loop') !== false || strpos($w['widgetType'], 'plus') !== false) {
        echo "Widget: " . $w['widgetType'] . " (ID: " . $w['id'] . ")\n";
        echo "Settings keys: " . implode(', ', array_keys($w['settings'])) . "\n";
        if (isset($w['settings']['template_id'])) {
            echo "template_id: " . $w['settings']['template_id'] . "\n";
        }
    }
}

