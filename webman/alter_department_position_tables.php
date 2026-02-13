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
    
    echo "开始修改数据库表结构...\n";
    
    // 修改 sys_department 表，将 name 字段改为 dept_name
    echo "修改 sys_department 表...\n";
    $stmt = $pdo->query("ALTER TABLE sys_department CHANGE COLUMN name dept_name VARCHAR(50) NOT NULL");
    echo "sys_department 表修改成功！\n";
    
    // 修改 sys_position 表，将 name 字段改为 position_name
    echo "修改 sys_position 表...\n";
    $stmt = $pdo->query("ALTER TABLE sys_position CHANGE COLUMN name position_name VARCHAR(50) NOT NULL");
    echo "sys_position 表修改成功！\n";
    
    echo "数据库表结构修改完成！\n";
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>