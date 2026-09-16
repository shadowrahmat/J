<?php
$h=file_get_contents(getenv('TEMP').'\home.html');
$p=strpos($h,'681c745');
$ctx=substr($h, max(0,$p-500), 8000);
echo $ctx;
echo "\n\n=== check widget rendered ===\n";
$q=strpos($h,'elementor-element-681c745');
echo $q===false?"NOT RENDERED":"found at $q\n".substr($h,$q-500,5000);
echo "\n=== check e-search-form class in that widget ===\n";
$pp=strpos($ctx,'e-search');
echo $pp===false?"no e-search in ctx":"found\n";
echo "\n=== full snippet around search widget ===\n";
$qq=strpos($h,'Search Product.....');
echo $qq===false?"placeholder not found":"found at $qq\n".substr($h,max(0,$qq-3000),9000);
