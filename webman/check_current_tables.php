<?php
require 'vendor/autoload.php';

try {
    $db = new PDO('mysql:host=localhost;dbname=mydream', 'root', '123456');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo '检查当前数据库表结构...' . PHP_EOL;
    
    // 检查 sys_employee 表
    echo 'sys_employee 表结构：' . PHP_EOL;
    echo '-----------------------------------' . PHP_EOL;
    $stmt = $db->query('DESCRIBE sys_employee');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
    }
    echo '-----------------------------------' . PHP_EOL . PHP_EOL;
    
    // 检查 sys_employee_position 表
    echo '检查 sys_employee_position 表是否存在：' . PHP_EOL;
    try {
        $stmt = $db->query('DESCRIBE sys_employee_position');
        echo 'sys_employee_position 表存在，结构如下：' . PHP_EOL;
        echo '-----------------------------------' . PHP_EOL;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
        }
        echo '-----------------------------------' . PHP_EOL . PHP_EOL;
    } catch (PDOException $e) {
        echo 'sys_employee_position 表不存在' . PHP_EOL . PHP_EOL;
    }
    
    // 检查 sys_user_profile 表
    echo 'sys_user_profile 表结构：' . PHP_EOL;
    echo '-----------------------------------' . PHP_EOL;
    $stmt = $db->query('DESCRIBE sys_user_profile');
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
    }
    echo '-----------------------------------' . PHP_EOL . PHP_EOL;
    
} catch (PDOException $e) {
    echo '错误：' . $e->getMessage() . PHP_EOL;
    exit(1);
}
?>