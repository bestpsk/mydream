<?php

// 直接使用PDO连接数据库
$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';
$charset = 'utf8mb4';

try {
    // 连接数据库
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "数据库连接成功！\n";
    
    // 需要添加company_id字段的表
    $tables = [
        'card_project_category',
        'card_project',
        'card_recharge',
        'card_course',
        'card_package',
        'card_time'
    ];
    
    foreach ($tables as $table) {
        echo "\n处理表 $table：\n";
        
        // 检查是否已经有company_id字段
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'company_id'");
        if ($stmt->rowCount() > 0) {
            echo "✓ company_id字段已存在\n";
        } else {
            // 添加company_id字段
            $sql = "ALTER TABLE $table ADD COLUMN company_id INT UNSIGNED COMMENT '所属公司ID' AFTER id";
            $pdo->exec($sql);
            echo "✓ 添加company_id字段成功\n";
            
            // 添加索引
            $sql = "ALTER TABLE $table ADD INDEX idx_company_id (company_id)";
            $pdo->exec($sql);
            echo "✓ 添加索引成功\n";
        }
        
        // 验证表结构
        $stmt = $pdo->query("SHOW COLUMNS FROM $table");
        $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($columns as $column) {
            if ($column->Field === 'company_id') {
                echo "  - 字段: {$column->Field}, 类型: {$column->Type}\n";
                break;
            }
        }
    }
    
    // 验证所有表结构
    echo "\n验证所有卡项管理相关表的company_id字段：\n";
    $allTables = [
        'card_supplier',
        'card_project_category',
        'card_project',
        'card_recharge',
        'card_course',
        'card_package',
        'card_time'
    ];
    
    foreach ($allTables as $table) {
        $stmt = $pdo->query("SHOW COLUMNS FROM $table LIKE 'company_id'");
        if ($stmt->rowCount() > 0) {
            echo "✓ 表 $table 存在 company_id 字段\n";
        } else {
            echo "✗ 表 $table 不存在 company_id 字段\n";
        }
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);