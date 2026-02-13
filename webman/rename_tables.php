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
    
    // 1. 修改表名：supplier -> card_supplier
    echo "\n1. 修改表名：supplier -> card_supplier\n";
    $sql = "ALTER TABLE supplier RENAME TO card_supplier";
    $pdo->exec($sql);
    echo "✓ 表名修改成功\n";
    
    // 2. 修改表名：project_category -> card_project_category
    echo "\n2. 修改表名：project_category -> card_project_category\n";
    $sql = "ALTER TABLE project_category RENAME TO card_project_category";
    $pdo->exec($sql);
    echo "✓ 表名修改成功\n";
    
    // 3. 修改表名：project -> card_project
    echo "\n3. 修改表名：project -> card_project\n";
    $sql = "ALTER TABLE project RENAME TO card_project";
    $pdo->exec($sql);
    echo "✓ 表名修改成功\n";
    
    // 4. 验证表结构
    echo "\n4. 验证表结构：\n";
    $tables = ['card_supplier', 'card_project_category', 'card_project', 'card_recharge', 'card_course', 'card_package', 'card_time'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "表 $table 存在\n";
            // 显示表结构
            $stmt = $pdo->query("SHOW COLUMNS FROM $table");
            $columns = $stmt->fetchAll(PDO::FETCH_OBJ);
            foreach ($columns as $column) {
                echo "  - 字段: {$column->Field}, 类型: {$column->Type}, 默认值: {$column->Default}\n";
            }
        } else {
            echo "表 $table 不存在\n";
        }
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);