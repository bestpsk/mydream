<?php
require 'config/database.php';

// 连接数据库
$config = require 'config/database.php';
$dsn = 'mysql:host=' . $config['connections']['mysql']['host'] . ';dbname=' . $config['connections']['mysql']['database'] . ';charset=utf8mb4';
$pdo = new PDO($dsn, $config['connections']['mysql']['username'], $config['connections']['mysql']['password']);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

try {
    // 创建充值卡表
    $sql = "CREATE TABLE IF NOT EXISTS `card_recharge` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `company_id` INT(10) UNSIGNED NOT NULL,
        `department_id` INT(10) UNSIGNED NOT NULL,
        `card_name` VARCHAR(100) NOT NULL,
        `amount` DECIMAL(10,2) NOT NULL,
        `gift_amount` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `project_discount` DECIMAL(5,2) NOT NULL DEFAULT '10.00',
        `product_discount` DECIMAL(5,2) NOT NULL DEFAULT '10.00',
        `consume_rate` DECIMAL(5,2) NOT NULL DEFAULT '100.00',
        `min_recharge_limit` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `start_time` DATETIME NULL,
        `end_time` DATETIME NULL,
        `expire_date` DATE NULL,
        `expire_type` TINYINT(1) NOT NULL DEFAULT '3' COMMENT '过期类型（1:开卡计算 2:消耗计算 3:固定日期）',
        `description` TEXT NULL,
        `remark` TEXT NULL,
        `is_modifiable` TINYINT(1) NOT NULL DEFAULT '1',
        `is_limit_once` TINYINT(1) NOT NULL DEFAULT '0',
        `is_expire_invalid` TINYINT(1) NOT NULL DEFAULT '1',
        `is_project_expire` TINYINT(1) NOT NULL DEFAULT '1',
        `is_prohibit_discount_modify` TINYINT(1) NOT NULL DEFAULT '0',
        `is_cross_department_consume` TINYINT(1) NOT NULL DEFAULT '0',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `isDelete` TINYINT(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        INDEX `idx_company_id` (`company_id`),
        INDEX `idx_department_id` (`department_id`),
        INDEX `idx_is_delete` (`isDelete`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡表';";
    $pdo->exec($sql);
    echo "成功创建 card_recharge 表！\n";
    
    // 创建充值卡销售分店表
    $sql = "CREATE TABLE IF NOT EXISTS `card_recharge_sale_store` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `recharge_id` INT(10) UNSIGNED NOT NULL,
        `store_id` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_recharge_id` (`recharge_id`),
        INDEX `idx_store_id` (`store_id`),
        FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `uniq_recharge_store` (`recharge_id`, `store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡销售分店表';";
    $pdo->exec($sql);
    echo "成功创建 card_recharge_sale_store 表！\n";
    
    // 创建充值卡消费分店表
    $sql = "CREATE TABLE IF NOT EXISTS `card_recharge_consume_store` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `recharge_id` INT(10) UNSIGNED NOT NULL,
        `store_id` INT(10) UNSIGNED NOT NULL,
        PRIMARY KEY (`id`),
        INDEX `idx_recharge_id` (`recharge_id`),
        INDEX `idx_store_id` (`store_id`),
        FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE,
        UNIQUE KEY `uniq_recharge_store` (`recharge_id`, `store_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡消费分店表';";
    $pdo->exec($sql);
    echo "成功创建 card_recharge_consume_store 表！\n";
    
    // 创建配赠项目表
    $sql = "CREATE TABLE IF NOT EXISTS `card_recharge_gift_project` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `recharge_id` INT(10) UNSIGNED NOT NULL,
        `project_id` INT(10) UNSIGNED NOT NULL,
        `times` INT(10) NOT NULL DEFAULT '1',
        `unit_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `consume` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `manual_salary` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_recharge_id` (`recharge_id`),
        INDEX `idx_project_id` (`project_id`),
        FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配赠项目表';";
    $pdo->exec($sql);
    echo "成功创建 card_recharge_gift_project 表！\n";
    
    // 创建配赠产品表
    $sql = "CREATE TABLE IF NOT EXISTS `card_recharge_gift_product` (
        `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `recharge_id` INT(10) UNSIGNED NOT NULL,
        `product_id` INT(10) UNSIGNED NOT NULL,
        `times` INT(10) NOT NULL DEFAULT '1',
        `unit_price` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `consume` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `manual_salary` DECIMAL(10,2) NOT NULL DEFAULT '0.00',
        `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        INDEX `idx_recharge_id` (`recharge_id`),
        INDEX `idx_product_id` (`product_id`),
        FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配赠产品表';";
    $pdo->exec($sql);
    echo "成功创建 card_recharge_gift_product 表！\n";
    
    echo "\n所有充值卡相关表创建成功！\n";
    
} catch (PDOException $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
?>