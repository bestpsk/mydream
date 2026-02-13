<?php
require 'vendor/autoload.php';

$db = new PDO('mysql:host=localhost;dbname=mydream', 'root', '123456');

// 检查 sys_user_role 表
echo 'sys_user_role table structure:' . PHP_EOL;
echo '-----------------------------------' . PHP_EOL;
$stmt = $db->query('DESCRIBE sys_user_role');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
}
echo '-----------------------------------' . PHP_EOL . PHP_EOL;

// 检查 sys_user_store 表
echo 'sys_user_store table structure:' . PHP_EOL;
echo '-----------------------------------' . PHP_EOL;
$stmt = $db->query('DESCRIBE sys_user_store');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo 'Field: ' . $row['Field'] . ', Type: ' . $row['Type'] . PHP_EOL;
}
echo '-----------------------------------' . PHP_EOL;
?>