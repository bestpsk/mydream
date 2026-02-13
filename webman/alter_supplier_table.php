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
    
    // 为供应商表添加新字段
    echo "\n为供应商表添加新字段：\n";
    
    // 1. 添加开户银行字段
    $sql = "ALTER TABLE supplier ADD COLUMN bank VARCHAR(100) COMMENT '开户银行' AFTER address";
    $pdo->exec($sql);
    echo "✓ 添加开户银行字段成功\n";
    
    // 2. 添加银行卡号字段
    $sql = "ALTER TABLE supplier ADD COLUMN bank_card VARCHAR(50) COMMENT '银行卡号' AFTER bank";
    $pdo->exec($sql);
    echo "✓ 添加银行卡号字段成功\n";
    
    // 3. 添加邮箱字段
    $sql = "ALTER TABLE supplier ADD COLUMN email VARCHAR(100) COMMENT '邮箱' AFTER bank_card";
    $pdo->exec($sql);
    echo "✓ 添加邮箱字段成功\n";
    
    // 4. 添加预存余额字段
    $sql = "ALTER TABLE supplier ADD COLUMN prepay_balance DECIMAL(10,2) DEFAULT 0 COMMENT '预存余额' AFTER email";
    $pdo->exec($sql);
    echo "✓ 添加预存余额字段成功\n";
    
    // 5. 添加配送余额字段
    $sql = "ALTER TABLE supplier ADD COLUMN delivery_balance DECIMAL(10,2) DEFAULT 0 COMMENT '配送余额' AFTER prepay_balance";
    $pdo->exec($sql);
    echo "✓ 添加配送余额字段成功\n";
    
    // 验证表结构
    echo "\n验证表结构：\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM supplier");
    $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($columns as $column) {
        echo "字段名: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);