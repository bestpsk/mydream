<?php
require __DIR__.'/vendor/autoload.php';

use app\model\Store;

// 获取所有门店
$stores = Store::get();

// 输出门店信息
foreach($stores as $store) {
    echo 'ID: '.$store->id.' - Name: '.$store->store_name. PHP_EOL;
}
?>