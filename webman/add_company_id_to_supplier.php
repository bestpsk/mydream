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
    
    // 为card_supplier表添加company_id字段
    echo "\n为card_supplier表添加company_id字段：\n";
    $sql = "ALTER TABLE card_supplier ADD COLUMN company_id INT UNSIGNED COMMENT '所属公司ID' AFTER id";
    $pdo->exec($sql);
    echo "✓ 添加company_id字段成功\n";
    
    // 添加索引
    echo "\n为company_id字段添加索引：\n";
    $sql = "ALTER TABLE card_supplier ADD INDEX idx_company_id (company_id)";
    $pdo->exec($sql);
    echo "✓ 添加索引成功\n";
    
    // 验证表结构
    echo "\n验证表结构：\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM card_supplier");
    $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($columns as $column) {
        echo "字段名: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
    }
    
    // 检查sys_company表中的数据
    echo "\n检查sys_company表中的数据：\n";
    $stmt = $pdo->query("SELECT id, code, company_name FROM sys_company WHERE isDelete = 0");
    $companies = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($companies as $company) {
        echo "ID: {$company->id}, Code: {$company->code}, Name: {$company->company_name}\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);