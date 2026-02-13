<?php

// 扩展项目表结构
require_once __DIR__ . '/vendor/autoload.php';

use support\DB;

// 错误处理
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "错误: $errstr 在 $errfile 第 $errline 行\n";
    return true;
});

set_exception_handler(function ($exception) {
    echo "异常: {$exception->getMessage()} 在 {$exception->getFile()} 第 {$exception->getLine()} 行\n";
    return true;
});

// 连接数据库
$config = require __DIR__ . '/config/database.php';
try {
    $defaultConnection = $config['default'];
    $connectionConfig = $config['connections'][$defaultConnection];
    
    $pdo = new PDO(
        "mysql:host={$connectionConfig['host']};port={$connectionConfig['port']};dbname={$connectionConfig['database']}",
        $connectionConfig['username'],
        $connectionConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "数据库连接成功\n";
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("配置读取失败: " . $e->getMessage() . "\n");
}

// 扩展 card_project 表
function extendProjectTable($pdo) {
    echo "\n扩展 card_project 表...\n";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'card_project'");
    if (!$stmt->fetch()) {
        echo "card_project 表不存在，正在创建...\n";
        $sql = "CREATE TABLE `card_project` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company_id` int(11) NOT NULL,
            `project_name` varchar(255) NOT NULL,
            `category_id` int(11) NOT NULL,
            `category_type` varchar(255) DEFAULT NULL,
            `price` decimal(10,2) DEFAULT '0.00',
            `original_price` decimal(10,2) DEFAULT '0.00',
            `single_sale_price` decimal(10,2) DEFAULT '0.00',
            `experience_price` decimal(10,2) DEFAULT '0.00',
            `external_display_name` varchar(255) DEFAULT NULL,
            `project_times` int(11) DEFAULT '0',
            `supplier_id` int(11) DEFAULT '0',
            `project_type` varchar(255) DEFAULT NULL,
            `monthly_limit` int(11) DEFAULT '0',
            `consumption_interval` int(11) DEFAULT '0',
            `work_hours` decimal(5,2) DEFAULT '0.00',
            `service_time` int(11) DEFAULT '0',
            `status` tinyint(1) DEFAULT '1',
            `reminder_type` tinyint(1) DEFAULT '0',
            `reminder_days` int(11) DEFAULT '0',
            `reminder_date` datetime DEFAULT NULL,
            `reminder_repeat` tinyint(1) DEFAULT '0',
            `cooperation_project` tinyint(1) DEFAULT '0',
            `ym_project` tinyint(1) DEFAULT '0',
            `special_project` tinyint(1) DEFAULT '0',
            `big_project` tinyint(1) DEFAULT '0',
            `small_project` tinyint(1) DEFAULT '0',
            `no_recharge_discount` tinyint(1) DEFAULT '0',
            `no_project_times` tinyint(1) DEFAULT '0',
            `no_consumption` tinyint(1) DEFAULT '0',
            `no_consumption_notification` tinyint(1) DEFAULT '0',
            `mini_program_bookable` tinyint(1) DEFAULT '0',
            `limited_sale_stores` text DEFAULT NULL,
            `limited_service_stores` text DEFAULT NULL,
            `limited_service_employees` text DEFAULT NULL,
            `allow_gift` tinyint(1) DEFAULT '0',
            `remark` text DEFAULT NULL,
            `isDelete` tinyint(1) DEFAULT '0',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `company_id` (`company_id`),
            KEY `category_id` (`category_id`),
            KEY `supplier_id` (`supplier_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "card_project 表创建成功\n";
    } else {
        echo "card_project 表已存在，正在添加新字段...\n";
        
        // 添加新字段
        $fields = [
            'original_price' => 'decimal(10,2) DEFAULT \'0.00\'',
            'single_sale_price' => 'decimal(10,2) DEFAULT \'0.00\'',
            'experience_price' => 'decimal(10,2) DEFAULT \'0.00\'',
            'external_display_name' => 'varchar(255) DEFAULT NULL',
            'project_times' => 'int(11) DEFAULT \'0\'',
            'supplier_id' => 'int(11) DEFAULT \'0\'',
            'project_type' => 'varchar(255) DEFAULT NULL',
            'monthly_limit' => 'int(11) DEFAULT \'0\'',
            'consumption_interval' => 'int(11) DEFAULT \'0\'',
            'work_hours' => 'decimal(5,2) DEFAULT \'0.00\'',
            'service_time' => 'int(11) DEFAULT \'0\'',
            'status' => 'tinyint(1) DEFAULT \'1\'',
            'reminder_type' => 'tinyint(1) DEFAULT \'0\'',
            'reminder_days' => 'int(11) DEFAULT \'0\'',
            'reminder_date' => 'datetime DEFAULT NULL',
            'reminder_repeat' => 'tinyint(1) DEFAULT \'0\'',
            'cooperation_project' => 'tinyint(1) DEFAULT \'0\'',
            'ym_project' => 'tinyint(1) DEFAULT \'0\'',
            'special_project' => 'tinyint(1) DEFAULT \'0\'',
            'big_project' => 'tinyint(1) DEFAULT \'0\'',
            'small_project' => 'tinyint(1) DEFAULT \'0\'',
            'no_recharge_discount' => 'tinyint(1) DEFAULT \'0\'',
            'no_project_times' => 'tinyint(1) DEFAULT \'0\'',
            'no_consumption' => 'tinyint(1) DEFAULT \'0\'',
            'no_consumption_notification' => 'tinyint(1) DEFAULT \'0\'',
            'mini_program_bookable' => 'tinyint(1) DEFAULT \'0\'',
            'limited_sale_stores' => 'text DEFAULT NULL',
            'limited_service_stores' => 'text DEFAULT NULL',
            'limited_service_employees' => 'text DEFAULT NULL',
            'allow_gift' => 'tinyint(1) DEFAULT \'0\'',
            'remark' => 'text DEFAULT NULL'
        ];
        
        foreach ($fields as $field => $type) {
            try {
                $pdo->exec("ALTER TABLE `card_project` ADD COLUMN `$field` $type");
                echo "添加字段 $field 成功\n";
            } catch (PDOException $e) {
                if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
                    echo "字段 $field 已存在\n";
                } else {
                    echo "添加字段 $field 失败: " . $e->getMessage() . "\n";
                }
            }
        }
    }
}

// 创建项目配料单表
function createProjectIngredientTable($pdo) {
    echo "\n创建项目配料单表...\n";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'card_project_ingredient'");
    if (!$stmt->fetch()) {
        $sql = "CREATE TABLE `card_project_ingredient` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company_id` int(11) NOT NULL,
            `project_id` int(11) NOT NULL,
            `product` varchar(255) NOT NULL,
            `type` varchar(255) DEFAULT NULL,
            `quantity` decimal(10,2) DEFAULT '0.00',
            `unit` varchar(50) DEFAULT NULL,
            `remark` text DEFAULT NULL,
            `isDelete` tinyint(1) DEFAULT '0',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `company_id` (`company_id`),
            KEY `project_id` (`project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "card_project_ingredient 表创建成功\n";
    } else {
        echo "card_project_ingredient 表已存在\n";
    }
}

// 创建子项目配置表
function createSubProjectTable($pdo) {
    echo "\n创建子项目配置表...\n";
    
    // 检查表是否存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'card_project_sub'");
    if (!$stmt->fetch()) {
        $sql = "CREATE TABLE `card_project_sub` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `company_id` int(11) NOT NULL,
            `project_id` int(11) NOT NULL,
            `sub_project_id` int(11) NOT NULL,
            `consumption_ratio` decimal(5,2) DEFAULT '0.00',
            `isDelete` tinyint(1) DEFAULT '0',
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `company_id` (`company_id`),
            KEY `project_id` (`project_id`),
            KEY `sub_project_id` (`sub_project_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $pdo->exec($sql);
        echo "card_project_sub 表创建成功\n";
    } else {
        echo "card_project_sub 表已存在\n";
    }
}

// 执行扩展
function main() {
    global $pdo;
    
    try {
        extendProjectTable($pdo);
        createProjectIngredientTable($pdo);
        createSubProjectTable($pdo);
        
        echo "\n项目表扩展完成！\n";
    } catch (Exception $e) {
        echo "\n执行失败: " . $e->getMessage() . "\n";
    }
}

// 运行脚本
main();

// 关闭连接
$pdo = null;
echo "\n数据库连接已关闭\n";
