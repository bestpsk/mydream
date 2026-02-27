<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = require __DIR__ . '/../config/database.php';
$connections = $config['connections']['mysql'];

$host = $connections['host'] ?? '127.0.0.1';
$port = $connections['port'] ?? 3306;
$database = $connections['database'] ?? 'mydream';
$username = $connections['username'] ?? 'root';
$password = $connections['password'] ?? '123456';
$charset = $connections['charset'] ?? 'utf8mb4';

$dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "Connected to database successfully.\n";
    
    $alterStatements = [
        "ALTER TABLE `card_time` ADD COLUMN `card_code` varchar(50) DEFAULT NULL COMMENT '卡编码' AFTER `card_name`",
        "ALTER TABLE `card_time` ADD COLUMN `original_price` decimal(10,2) DEFAULT '0.00' COMMENT '原价' AFTER `card_code`",
        "ALTER TABLE `card_time` ADD COLUMN `valid_type` tinyint(1) DEFAULT '1' COMMENT '有效期类型:1=固定天数,2=自定义' AFTER `valid_days`",
        "ALTER TABLE `card_time` ADD COLUMN `use_rule_type` tinyint(1) DEFAULT '1' COMMENT '使用规则:1=不限次数,2=限制总次数,3=限制频率' AFTER `price`",
        "ALTER TABLE `card_time` ADD COLUMN `max_use_count` int(11) DEFAULT NULL COMMENT '最大使用次数'",
        "ALTER TABLE `card_time` ADD COLUMN `interval_hours` int(11) DEFAULT NULL COMMENT '使用间隔小时数'",
        "ALTER TABLE `card_time` ADD COLUMN `project_bind_type` tinyint(1) DEFAULT '1' COMMENT '项目绑定类型:1=单选,2=多选,3=全店通用'",
        "ALTER TABLE `card_time` ADD COLUMN `customer_count` int(11) DEFAULT '0' COMMENT '已办理顾客数'",
        "ALTER TABLE `card_time` ADD COLUMN `description` text COMMENT '描述'",
        "ALTER TABLE `card_time` ADD COLUMN `remark` text COMMENT '备注'",
        "ALTER TABLE `card_time` ADD COLUMN `status` tinyint(1) DEFAULT '1' COMMENT '状态:0=禁用,1=启用'",
        "ALTER TABLE `card_time` ADD COLUMN `online_time` datetime DEFAULT NULL COMMENT '上线时间'",
        "ALTER TABLE `card_time` ADD COLUMN `offline_time` datetime DEFAULT NULL COMMENT '下线时间'",
        "ALTER TABLE `card_time` ADD COLUMN `sale_store_ids` text COMMENT '限定销售分店(JSON数组)'",
        "ALTER TABLE `card_time` ADD COLUMN `consume_store_ids` text COMMENT '限定消费分店(JSON数组)'",
        "ALTER TABLE `card_time` ADD COLUMN `sale_department_ids` text COMMENT '限定销售部门(JSON数组)'",
        "ALTER TABLE `card_time` ADD COLUMN `consume_department_ids` text COMMENT '限定消费部门(JSON数组)'",
        "ALTER TABLE `card_time` ADD COLUMN `is_modifiable` tinyint(1) DEFAULT '0' COMMENT '是否禁止修改'",
    ];
    
    foreach ($alterStatements as $sql) {
        try {
            $pdo->exec($sql);
            echo "Executed: " . substr($sql, 0, 80) . "...\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "Column already exists, skipping...\n";
            } else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }
    
    $createProjectTable = "CREATE TABLE IF NOT EXISTS `card_time_project` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `company_id` int(10) unsigned DEFAULT NULL COMMENT '公司ID',
      `time_card_id` int(10) unsigned NOT NULL COMMENT '时效卡ID',
      `project_id` int(10) unsigned NOT NULL COMMENT '项目ID',
      `times` int(11) NOT NULL DEFAULT '1' COMMENT '次数',
      `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
      `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
      `consume` int(11) NOT NULL DEFAULT '0' COMMENT '耗卡',
      `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手工费',
      `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_time_card_id` (`time_card_id`),
      KEY `idx_project_id` (`project_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='时效卡包含项目表'";
    
    $pdo->exec($createProjectTable);
    echo "Created card_time_project table.\n";
    
    $createProductTable = "CREATE TABLE IF NOT EXISTS `card_time_product` (
      `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `company_id` int(10) unsigned DEFAULT NULL COMMENT '公司ID',
      `time_card_id` int(10) unsigned NOT NULL COMMENT '时效卡ID',
      `product_id` int(10) unsigned NOT NULL COMMENT '产品ID',
      `times` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
      `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
      `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
      `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手工费',
      `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
      `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`),
      KEY `idx_time_card_id` (`time_card_id`),
      KEY `idx_product_id` (`product_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='时效卡包含产品表'";
    
    $pdo->exec($createProductTable);
    echo "Created card_time_product table.\n";
    
    echo "\nDatabase update completed successfully!\n";
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
    exit(1);
}
