<?php

require_once __DIR__ . '/vendor/autoload.php';

// 加载配置
$config = require __DIR__ . '/config/database.php';

// 创建数据库连接
$dsn = "mysql:host={$config['connections']['mysql']['host']};port={$config['connections']['mysql']['port']};dbname={$config['connections']['mysql']['database']};charset={$config['connections']['mysql']['charset']}";
$username = $config['connections']['mysql']['username'];
$password = $config['connections']['mysql']['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查字段是否已存在
    $stmt = $pdo->query("DESCRIBE sys_user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $fields = [];
    
    foreach ($columns as $column) {
        $fields[] = $column['Field'];
    }
    
    // 添加employee_id字段
    if (!in_array('employee_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD COLUMN `employee_id` INT UNSIGNED DEFAULT NULL AFTER `avatar`";
        $pdo->exec($sql);
        echo "成功添加employee_id字段到sys_user表\n";
    } else {
        echo "employee_id字段已存在，跳过添加\n";
    }
    
    // 添加company_id字段
    if (!in_array('company_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD COLUMN `company_id` INT UNSIGNED DEFAULT NULL AFTER `employee_id`";
        $pdo->exec($sql);
        echo "成功添加company_id字段到sys_user表\n";
    } else {
        echo "company_id字段已存在，跳过添加\n";
    }
    
    // 添加store_id字段
    if (!in_array('store_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD COLUMN `store_id` INT UNSIGNED DEFAULT NULL AFTER `company_id`";
        $pdo->exec($sql);
        echo "成功添加store_id字段到sys_user表\n";
    } else {
        echo "store_id字段已存在，跳过添加\n";
    }
    
    // 添加索引
    if (!in_array('employee_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD INDEX `idx_employee_id` (`employee_id`)";
        $pdo->exec($sql);
        echo "成功添加索引到employee_id字段\n";
    }
    
    if (!in_array('company_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD INDEX `idx_company_id` (`company_id`)";
        $pdo->exec($sql);
        echo "成功添加索引到company_id字段\n";
    }
    
    if (!in_array('store_id', $fields)) {
        $sql = "ALTER TABLE sys_user ADD INDEX `idx_store_id` (`store_id`)";
        $pdo->exec($sql);
        echo "成功添加索引到store_id字段\n";
    }
    
    // 验证修改结果
    $stmt = $pdo->query("DESCRIBE sys_user");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n修改后的 sys_user 表结构：\n";
    echo "---------------------------------------------------\n";
    echo "字段名\t\t类型\t\t是否为空\t默认值\t注释\n";
    echo "---------------------------------------------------\n";
    
    foreach ($columns as $column) {
        echo sprintf("%s\t\t%s\t\t%s\t\t%s\t\t%s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default'] ?? 'NULL', 
            $column['Extra']
        );
    }
    
    echo "---------------------------------------------------\n";
    
} catch (PDOException $e) {
    echo "修改表结构失败: " . $e->getMessage() . "\n";
}
?>