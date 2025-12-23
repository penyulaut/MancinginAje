<?php
$data = json_encode(['order_id'=>'1-123','transaction_status'=>'settlement','transaction_id'=>'tx-test-php','status_code'=>'200','gross_amount'=>'10000']);
$opts = ['http'=>['method'=>'POST','header'=>'Content-Type: application/json\r\n','content'=>$data]];
$context = stream_context_create($opts);
$res = @file_get_contents('http://localhost:8000/beranda/payment/notification', false, $context);
echo isset($http_response_header[0]) ? $http_response_header[0] : 'no-response';
?>
