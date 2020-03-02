<?php
$path='./bq';
$data=scandir($path);
//print_r($data);

unset($data[0]);
unset($data[1]);

echo json_encode($data,JSON_UNESCAPED_UNICODE);