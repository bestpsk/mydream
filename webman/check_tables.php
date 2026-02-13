<?php

require_once __DIR__.'/vendor/autoload.php';

use support\DB;

// 检查充值卡相关的表
$tables = DB::select('SHOW TABLES LIKE "card_recharge_%"');

echo "Recharge card related tables:\n";
foreach ($tables as $table) {
    foreach ($table as $key => $value) {
        echo $value . "\n";
    }
}

// 检查是否存在销售部门和消费部门的关联表
echo "\nChecking for department association tables:\n";
$saleDeptTable = DB::select('SHOW TABLES LIKE "card_recharge_sale_department"');
$consumeDeptTable = DB::select('SHOW TABLES LIKE "card_recharge_consume_department"');

echo "card_recharge_sale_department exists: " . (count($saleDeptTable) > 0 ? "Yes" : "No") . "\n";
echo "card_recharge_consume_department exists: " . (count($consumeDeptTable) > 0 ? "Yes" : "No") . "\n";
