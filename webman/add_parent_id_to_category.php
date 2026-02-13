<?php
require 'config/database.php';

// 连接数据库
$config = require 'config/database.php';
$dsn = 'mysql:host=' . $config['connections']['mysql']['host'] . ';dbname=' . $config['connections']['mysql']['database'] . ';charset=utf8mb4';
$pdo = new PDO($dsn, $config['connections']['mysql']['username'], $config['connections']['mysql']['password']);

// 添加 parent_id 字段
try {
    $sql = "ALTER TABLE `card_project_category` ADD COLUMN `parent_id` INT(10) UNSIGNED NOT NULL DEFAULT 0 COMMENT '父分类ID，0表示一级分类';";
    $pdo->exec($sql);
    echo "成功添加 parent_id 字段到 card_project_category 表！\n";
    
    // 验证字段是否添加成功
    $stmt = $pdo->query('DESCRIBE card_project_category');
    echo "\n修改后的表结构：\n";
    echo "---------------------------------------------------\n";
    echo "字段名          类型            是否为空        默认值  注释\n";
    echo "---------------------------------------------------\n";
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $field = str_pad($row['Field'], 15);
        $type = str_pad($row['Type'], 18);
        $null = str_pad(($row['Null'] == 'YES' ? 'YES' : 'NO'), 12);
        $default = str_pad(($row['Default'] ?? 'NULL'), 10);
        $extra = $row['Extra'];
        echo $field . $type . $null . $default . $extra . PHP_EOL;
    }
    
    echo "---------------------------------------------------\n";
    
} catch (PDOException $e) {
    echo "错误：" . $e->getMessage() . "\n";
}
?>