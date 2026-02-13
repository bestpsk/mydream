<?php
require 'config/database.php';

// 连接数据库
$config = require 'config/database.php';
$dsn = 'mysql:host=' . $config['connections']['mysql']['host'] . ';dbname=' . $config['connections']['mysql']['database'] . ';charset=utf8mb4';
$pdo = new PDO($dsn, $config['connections']['mysql']['username'], $config['connections']['mysql']['password']);

// 查询表结构
$stmt = $pdo->query('DESCRIBE card_project_category');
echo 'card_project_category 表结构：' . PHP_EOL;
echo '---------------------------------------------------' . PHP_EOL;
echo '字段名          类型            是否为空        默认值  注释' . PHP_EOL;
echo '---------------------------------------------------' . PHP_EOL;

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $field = str_pad($row['Field'], 15);
    $type = str_pad($row['Type'], 18);
    $null = str_pad(($row['Null'] == 'YES' ? 'YES' : 'NO'), 12);
    $default = str_pad(($row['Default'] ?? 'NULL'), 10);
    $extra = $row['Extra'];
    echo $field . $type . $null . $default . $extra . PHP_EOL;
}

echo '---------------------------------------------------' . PHP_EOL;