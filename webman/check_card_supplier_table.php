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
    
    // 检查card_supplier表结构
    echo "\n检查card_supplier表结构：\n";
    $stmt = $pdo->query("SHOW COLUMNS FROM card_supplier");
    $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($columns as $column) {
        echo "字段名: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
    }
    
    // 检查company表是否存在
    echo "\n检查company表是否存在：\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'company'");
    if ($stmt->rowCount() > 0) {
        echo "company表存在\n";
        // 检查company表结构
        $stmt = $pdo->query("SHOW COLUMNS FROM company");
        $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
        foreach ($columns as $column) {
            echo "字段名: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
        }
    } else {
        echo "company表不存在\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);