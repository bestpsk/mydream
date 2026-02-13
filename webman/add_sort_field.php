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
    
    // 修改 sys_department 表，添加 sort 字段
    echo "修改 sys_department 表...\n";
    $stmt = $pdo->query("ALTER TABLE sys_department ADD COLUMN sort INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER parent_id");
    echo "sys_department 表修改成功！\n";
    
    // 修改 sys_position 表，添加 sort 字段
    echo "修改 sys_position 表...\n";
    $stmt = $pdo->query("ALTER TABLE sys_position ADD COLUMN sort INT(10) UNSIGNED NOT NULL DEFAULT 0 AFTER dept_id");
    echo "sys_position 表修改成功！\n";
    
    echo "数据库表结构修改完成！\n";
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>