<?php
require 'vendor/autoload.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=mydream', 'root', '123456');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '开始修改数据库表结构...' . PHP_EOL;
    
    // 1. 修改 sys_employee 表
    echo '修改 sys_employee 表...' . PHP_EOL;
    
    // 添加 position_id 字段
    $db->exec('ALTER TABLE sys_employee ADD COLUMN position_id INT(10) UNSIGNED AFTER department_id');
    echo '添加 position_id 字段成功！' . PHP_EOL;
    
    // 删除 position 字段
    $db->exec('ALTER TABLE sys_employee DROP COLUMN position');
    echo '删除 position 字段成功！' . PHP_EOL;
    
    // 删除 phone 字段
    $db->exec('ALTER TABLE sys_employee DROP COLUMN phone');
    echo '删除 phone 字段成功！' . PHP_EOL;
    
    // 删除 email 字段
    $db->exec('ALTER TABLE sys_employee DROP COLUMN email');
    echo '删除 email 字段成功！' . PHP_EOL;
    
    echo 'sys_employee 表修改完成！' . PHP_EOL . PHP_EOL;
    
    // 显示修改后的表结构
    echo '修改后的 sys_employee 表结构：' . PHP_EOL;
    echo '-----------------------------------' . PHP_EOL;
    $stmt = $db->query('DESCRIBE sys_employee');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
    }
    echo '-----------------------------------' . PHP_EOL;
    
    echo '数据库表结构修改完成！' . PHP_EOL;
    
} catch (PDOException $e) {
    echo '错误：' . $e->getMessage() . PHP_EOL;
    exit(1);
}
?>