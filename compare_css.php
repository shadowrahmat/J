<?php
$about = file_get_contents('scratch_about.html');
$contact = file_get_contents('scratch_contact.html');

preg_match_all('/<link[^>]+rel=[\'"]stylesheet[\'"][^>]+href=[\'"]([^\'"]+)[\'"]/i', $about, $m_about);
preg_match_all('/<link[^>]+rel=[\'"]stylesheet[\'"][^>]+href=[\'"]([^\'"]+)[\'"]/i', $contact, $m_contact);

echo "CSS in About: " . count($m_about[1]) . "\n";
echo "CSS in Contact: " . count($m_contact[1]) . "\n";

$diff_about = array_diff($m_about[1], $m_contact[1]);
echo "\nCSS loaded ONLY in About:\n" . implode("\n", $diff_about) . "\n";

$diff_contact = array_diff($m_contact[1], $m_about[1]);
echo "\nCSS loaded ONLY in Contact:\n" . implode("\n", $diff_contact) . "\n";

// Also check <style> blocks
preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $about, $styles_about);
preg_match_all('/<style[^>]*>(.*?)<\/style>/is', $contact, $styles_contact);

echo "\nStyle tags in About: " . count($styles_about[1]) . "\n";
echo "Style tags in Contact: " . count($styles_contact[1]) . "\n";

