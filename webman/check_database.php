<?php

// 直接使用数据库配置
$config = [
    'host' => '127.0.0.1',
    'database' => 'mydream',
    'username' => 'root',
    'password' => '123456'
];

try {
    // 连接数据库
    $connection = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
        $config['username'],
        $config['password']
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查 card_recharge 表是否存在
    $stmt = $connection->query("SHOW TABLES LIKE 'card_recharge'");
    $tableExists = $stmt->rowCount() > 0;
    
    echo "card_recharge 表存在: " . ($tableExists ? '是' : '否') . "\n";
    
    if ($tableExists) {
        // 检查表结构
        $result = $connection->query('SHOW CREATE TABLE card_recharge');
        $row = $result->fetch(PDO::FETCH_ASSOC);
        echo "表结构:\n" . $row['Create Table'] . "\n";
    } else {
        // 如果表不存在，尝试创建
        echo "表不存在，尝试创建...\n";
        
        $createTableSql = "
        CREATE TABLE IF NOT EXISTS `card_recharge` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company_id` int(11) NOT NULL,
            `card_name` varchar(255) NOT NULL,
            `amount` decimal(10,2) NOT NULL,
            `gift_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
            `project_discount` decimal(5,2) NOT NULL DEFAULT '10.00',
            `product_discount` decimal(5,2) NOT NULL DEFAULT '10.00',
            `consume_rate` int(11) NOT NULL DEFAULT '100',
            `min_recharge_limit` decimal(10,2) NOT NULL DEFAULT '0.00',
            `start_time` datetime DEFAULT NULL,
            `end_time` datetime DEFAULT NULL,
            `expire_date` datetime DEFAULT NULL,
            `expire_type` int(11) NOT NULL DEFAULT '3',
            `description` text DEFAULT NULL,
            `remark` text DEFAULT NULL,
            `is_modifiable` tinyint(1) NOT NULL DEFAULT '1',
            `is_limit_once` tinyint(1) NOT NULL DEFAULT '0',
            `is_expire_invalid` tinyint(1) NOT NULL DEFAULT '1',
            `is_project_expire` tinyint(1) NOT NULL DEFAULT '1',
            `is_prohibit_discount_modify` tinyint(1) NOT NULL DEFAULT '0',
            `isDelete` tinyint(1) NOT NULL DEFAULT '0',
            `created_at` datetime NOT NULL,
            `updated_at` datetime NOT NULL,
            PRIMARY KEY (`id`),
            KEY `company_id` (`company_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
        ";
        
        $connection->exec($createTableSql);
        echo "表创建成功\n";
    }
    
    // 检查其他相关表
    $tables = [
        'card_recharge_sale_store',
        'card_recharge_consume_store',
        'card_recharge_sale_department',
        'card_recharge_consume_department',
        'card_recharge_gift_project',
        'card_recharge_gift_product'
    ];
    
    foreach ($tables as $table) {
        $stmt = $connection->query("SHOW TABLES LIKE '$table'");
        $exists = $stmt->rowCount() > 0;
        echo "$table 表存在: " . ($exists ? '是' : '否') . "\n";
    }
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
