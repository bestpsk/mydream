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
    
    // 修改 card_recharge 表结构
    echo "开始修改 card_recharge 表结构...\n";
    
    // 检查并删除不需要的字段
    $result = $connection->query("SHOW COLUMNS FROM `card_recharge` LIKE 'card_value'");
    if ($result->rowCount() > 0) {
        $connection->exec("ALTER TABLE `card_recharge` DROP COLUMN `card_value`");
        echo "删除字段 card_value 成功\n";
    }
    
    $result = $connection->query("SHOW COLUMNS FROM `card_recharge` LIKE 'sale_price'");
    if ($result->rowCount() > 0) {
        $connection->exec("ALTER TABLE `card_recharge` DROP COLUMN `sale_price`");
        echo "删除字段 sale_price 成功\n";
    }
    
    // 检查需要添加的字段
    $fieldsToAdd = [
        'amount' => 'decimal(10,2) NOT NULL DEFAULT \'0.00\' COMMENT \'金额\'',
        'gift_amount' => 'decimal(10,2) NOT NULL DEFAULT \'0.00\' COMMENT \'赠送金额\'',
        'project_discount' => 'decimal(5,2) NOT NULL DEFAULT \'10.00\' COMMENT \'项目折扣\'',
        'product_discount' => 'decimal(5,2) NOT NULL DEFAULT \'10.00\' COMMENT \'产品折扣\'',
        'consume_rate' => 'int(11) NOT NULL DEFAULT \'100\' COMMENT \'消费率\'',
        'min_recharge_limit' => 'decimal(10,2) NOT NULL DEFAULT \'0.00\' COMMENT \'最小充值限额\'',
        'start_time' => 'datetime DEFAULT NULL COMMENT \'开始时间\'',
        'end_time' => 'datetime DEFAULT NULL COMMENT \'结束时间\'',
        'expire_date' => 'datetime DEFAULT NULL COMMENT \'过期日期\'',
        'expire_type' => 'int(11) NOT NULL DEFAULT \'3\' COMMENT \'过期类型\'',
        'description' => 'text DEFAULT NULL COMMENT \'描述\'',
        'remark' => 'text DEFAULT NULL COMMENT \'备注\'',
        'is_modifiable' => 'tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'是否可修改\'',
        'is_limit_once' => 'tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'是否限制一次\'',
        'is_expire_invalid' => 'tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'过期是否失效\'',
        'is_project_expire' => 'tinyint(1) NOT NULL DEFAULT \'1\' COMMENT \'项目是否过期\'',
        'is_prohibit_discount_modify' => 'tinyint(1) NOT NULL DEFAULT \'0\' COMMENT \'是否禁止折扣修改\''
    ];
    
    // 添加缺失的字段
    foreach ($fieldsToAdd as $field => $definition) {
        $result = $connection->query("SHOW COLUMNS FROM `card_recharge` LIKE '$field'");
        if ($result->rowCount() == 0) {
            $connection->exec("ALTER TABLE `card_recharge` ADD COLUMN `$field` $definition");
            echo "添加字段 $field 成功\n";
        }
    }
    
    // 修改现有字段
    $connection->exec("ALTER TABLE `card_recharge` MODIFY COLUMN `company_id` int(11) NOT NULL COMMENT '所属公司ID'");
    $connection->exec("ALTER TABLE `card_recharge` MODIFY COLUMN `card_name` varchar(255) NOT NULL COMMENT '卡名称'");
    $connection->exec("ALTER TABLE `card_recharge` MODIFY COLUMN `created_at` datetime NOT NULL COMMENT '创建时间'");
    $connection->exec("ALTER TABLE `card_recharge` MODIFY COLUMN `updated_at` datetime NOT NULL COMMENT '更新时间'");
    echo "修改现有字段成功\n";
    
    echo "表结构修改成功\n";
    
    // 验证修改后的表结构
    $result = $connection->query('SHOW CREATE TABLE card_recharge');
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "修改后的表结构:\n" . $row['Create Table'] . "\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
}
