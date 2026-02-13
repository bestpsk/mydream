<?php

require_once __DIR__.'/vendor/autoload.php';

use support\DB;

// 创建销售部门关联表
echo "Creating card_recharge_sale_department table...\n";
try {
    DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_sale_department` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `recharge_id` int(11) NOT NULL,
        `department_id` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `recharge_id` (`recharge_id`),
        KEY `department_id` (`department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    echo "card_recharge_sale_department table created successfully!\n";
} catch (Exception $e) {
    echo "Error creating card_recharge_sale_department table: " . $e->getMessage() . "\n";
}

// 创建消费部门关联表
echo "\nCreating card_recharge_consume_department table...\n";
try {
    DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_consume_department` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `recharge_id` int(11) NOT NULL,
        `department_id` int(11) NOT NULL,
        PRIMARY KEY (`id`),
        KEY `recharge_id` (`recharge_id`),
        KEY `department_id` (`department_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    echo "card_recharge_consume_department table created successfully!\n";
} catch (Exception $e) {
    echo "Error creating card_recharge_consume_department table: " . $e->getMessage() . "\n";
}

echo "\nTable creation process completed!\n";
