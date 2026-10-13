<?php
require_once 'wp-load.php';

$data_1270 = json_decode(get_post_meta(1270, '_elementor_data', true), true);

function dump_elements($elements, $depth = 0) {
    $indent = str_repeat("  ", $depth);
    foreach ($elements as $el) {
        $type = $el['elType'] ?? 'unknown';
        $widgetType = $el['widgetType'] ?? '';
        $id = $el['id'] ?? '';
        echo "{$indent}[{$type}" . ($widgetType ? ": $widgetType" : "") . " id: $id]\n";
        if (!empty($el['settings'])) {
            echo "{$indent}  settings: " . json_encode($el['settings'], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . "\n";
        }
        if (!empty($el['elements'])) {
            dump_elements($el['elements'], $depth + 1);
        }
    }
}

dump_elements($data_1270);

