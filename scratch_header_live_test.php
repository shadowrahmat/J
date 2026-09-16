<?php
require 'wp-load.php';
echo "=== header widget live test POST simulation ===\n";
$_POST=null;
$req = new WP_REST_Request('POST','/elementor-pro/v1/refresh-search');
$req->set_param('post_id',71);
$req->set_param('widget_id','681c745');
$req->set_param('search_term','cast');
$req->set_param('page_number',1);
$response = apply_filters('rest_pre_dispatch', null, null, $req);
if($response instanceof WP_REST_Response){
  $data=$response->get_data();
  echo "got response data len=".strlen($data['data']??'')."\n";
  echo "pagination len=".strlen($data['pagination']??'')."\n";
  echo substr($data['data']??'',0,3000)."\n";
  if(empty(trim($data['data']??''))) echo "EMPTY HEADER RESULTS\n";
} else {
  echo "filter returned null, fell through to default\n";
  var_dump($response);
}
echo "\n=== footer popup 1214 live test ===\n";
$req2=new WP_REST_Request('POST','/elementor-pro/v1/refresh-search');
$req2->set_param('post_id',1214);
$req2->set_param('widget_id','b579efa');
$req2->set_param('search_term','cast');
$req2->set_param('page_number',1);
$response2=apply_filters('rest_pre_dispatch', null, null, $req2);
if($response2 instanceof WP_REST_Response){
  $data2=$response2->get_data();
  echo "footer data len=".strlen($data2['data']??'')."\n";
  echo substr($data2['data']??'',0,3000)."\n";
} else var_dump($response2);
