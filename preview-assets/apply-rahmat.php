<?php
// Local maintenance helper. Never accepts browser requests.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/wp-load.php';
$page_id = 1261;
$post = get_post($page_id);
if (!$post || $post->post_name !== 'rahmat') { throw new RuntimeException('Rahmat page identity did not match.'); }
$template = json_decode(file_get_contents(__DIR__.'/rahmat-elementor.json'), true, 512, JSON_THROW_ON_ERROR);
if (!isset($template['content'][0]['settings']['css_classes']) || $template['content'][0]['settings']['css_classes'] !== 'rahmat-page') {
    throw new RuntimeException('Expected an isolated native Elementor body.');
}
$admin = get_users(['role'=>'administrator','number'=>1,'fields'=>'ID']);
if (!$admin) { throw new RuntimeException('No local administrator account available.'); }
wp_set_current_user($admin[0]);
$plugin = \Elementor\Plugin::$instance;
if (!isset($plugin->widgets_manager->get_widget_types()['nested-accordion'])) {
    throw new RuntimeException('Native nested accordion is unavailable.');
}
$widgets = [];
$ids = [];
$walk = function($elements) use (&$walk,&$widgets,&$ids,$plugin) {
    foreach ($elements as $element) {
        if (isset($ids[$element['id']])) { throw new RuntimeException('Duplicate Elementor node ID.'); }
        $ids[$element['id']] = true;
        if ($element['elType'] === 'widget') {
            $type = $element['widgetType'];
            if (!isset($plugin->widgets_manager->get_widget_types()[$type])) { throw new RuntimeException('Unavailable widget: '.$type); }
            $widgets[$type] = ($widgets[$type] ?? 0) + 1;
        }
        $walk($element['elements'] ?? []);
    }
};
$walk($template['content']);
if (($argv[1] ?? '') !== 'apply') {
    echo wp_json_encode(['page_id'=>$page_id,'status'=>'Ready to apply','nodes'=>count($ids),'widgets'=>$widgets],JSON_PRETTY_PRINT).PHP_EOL;
    exit;
}
$attach_images = function(&$elements) use (&$attach_images) {
    foreach ($elements as &$element) {
        if (($element['widgetType'] ?? '') === 'image') {
            $image_url = $element['settings']['image']['url'];
            if (strpos($image_url, '/wp-content/uploads/') !== false) {
                $original_url = preg_replace('/-\d+x\d+(\.[a-zA-Z0-9]+)$/', '$1', $image_url);
                $attachment_id = attachment_url_to_postid($original_url);
                if ($attachment_id) {
                    $element['settings']['image']['id'] = $attachment_id;
                    $element['settings']['image']['url'] = wp_get_attachment_url($attachment_id);
                    if (($element['settings']['_css_classes'] ?? '') === 'product-photo') {
                        $element['settings']['image_size'] = 'woocommerce_thumbnail';
                    }
                }
            }
        }
        if (!empty($element['elements'])) { $attach_images($element['elements']); }
    }
};
$attach_images($template['content']);
$backup_path = dirname(__DIR__).'/.rahmat-before-preview-match.json';
if (!file_exists($backup_path)) {
    $backup = ['post'=>['ID'=>$page_id,'post_content'=>$post->post_content,'post_status'=>$post->post_status], 'meta'=>get_post_meta($page_id)];
    if (file_put_contents($backup_path,wp_json_encode($backup,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) === false) {
        throw new RuntimeException('Could not preserve the existing Rahmat page.');
    }
}
global $wpdb;
$site_before = hash('sha256',serialize($wpdb->get_results("SELECT pm.post_id, pm.meta_key, pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.post_type='elementor_library' AND pm.meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY pm.post_id, pm.meta_key",ARRAY_A)));
$document = $plugin->documents->get($page_id);
$settings = (array)get_post_meta($page_id,'_elementor_page_settings',true);
$settings['hide_title'] = 'yes';
$settings['custom_css'] = $template['page_settings']['custom_css'];
$settings['post_status'] = $post->post_status;
$document->set_is_built_with_elementor(true);
if (!$document->save(['elements'=>$template['content'],'settings'=>$settings])) { throw new RuntimeException('Elementor did not save the page.'); }
update_post_meta($page_id,'_wp_page_template','elementor_header_footer');
\Elementor\Core\Files\CSS\Post::create($page_id)->update();
$site_after = hash('sha256',serialize($wpdb->get_results("SELECT pm.post_id, pm.meta_key, pm.meta_value FROM {$wpdb->postmeta} pm JOIN {$wpdb->posts} p ON p.ID=pm.post_id WHERE p.post_type='elementor_library' AND pm.meta_key IN ('_elementor_data','_elementor_page_settings','_elementor_conditions') ORDER BY pm.post_id, pm.meta_key",ARRAY_A)));
if ($site_before !== $site_after) { throw new RuntimeException('Unexpected change outside Rahmat.'); }
file_put_contents(__DIR__.'/rahmat-elementor.json',wp_json_encode($template,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL);
echo wp_json_encode(['page_id'=>$page_id,'url'=>get_permalink($page_id),'edit_url'=>admin_url('post.php?post='.$page_id.'&action=elementor'),'nodes'=>count($ids),'widgets'=>$widgets,'header_footer'=>'Existing templates preserved','backup'=>basename($backup_path)],JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES).PHP_EOL;
