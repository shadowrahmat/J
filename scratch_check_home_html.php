<?php
$h=file_get_contents(getenv('TEMP').'\home.html');
echo "len=".strlen($h)."\n";
$p=strpos($h,'e-search-form');
echo "e-search-form pos: ".($p===false?'NOT FOUND':$p)."\n";
$p2=strpos($h,'681c745');
echo "681c745 pos: ".($p2===false?'NOT FOUND':$p2)."\n";
$p3=strpos($h,'search');
echo "search pos: ".($p3===false?'NOT FOUND':$p3)."\n";
if($p!==false) echo substr($h,max(0,$p-1500),5000)."\n";
if($p2!==false) echo "\n\n=== 681c745 context ===\n".substr($h,max(0,$p2-2000),6000)."\n";
// also check if polished css is loaded
$pol=strpos($h,'juhani-search-polished');
echo "\npolished css pos: ".($pol===false?'NOT FOUND':$pol)."\n";
if($pol!==false) echo substr($h,$pol-200,2000)."\n";
$b=strpos($h,'b579efa');
echo "\nb579efa pos: ".($b===false?'NOT FOUND':$b)."\n";
