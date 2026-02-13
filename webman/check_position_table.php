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
    
    echo "检查 sys_position 表结构...\n";
    $stmt = $pdo->query("DESCRIBE sys_position");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "sys_position 表字段：\n";
    foreach ($columns as $column) {
        echo $column['Field'] . "\n";
    }
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>