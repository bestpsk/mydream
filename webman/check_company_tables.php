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
    
    // 检查是否有与公司相关的表
    echo "\n检查与公司相关的表：\n";
    $tables = ['sys_company', 'company', 'companies'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "表 $table 存在\n";
            // 检查表结构
            $stmt = $pdo->query("SHOW COLUMNS FROM $table");
            $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($columns as $column) {
                echo "  - 字段: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
            }
        } else {
            echo "表 $table 不存在\n";
        }
    }
    
    // 检查所有表
    echo "\n所有表：\n";
    $stmt = $pdo->query("SHOW TABLES");
    $allTables = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($allTables as $table) {
        echo "- {$table->Tables_in_mydream}\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);