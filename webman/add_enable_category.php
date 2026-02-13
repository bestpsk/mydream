<?php

// 连接数据库
$servername = "localhost";
$username = "root";
$password = "123456";
$dbname = "mydream";

// 创建连接
$conn = new mysqli($servername, $username, $password, $dbname);

// 检查连接
if ($conn->connect_error) {
    die("连接失败: " . $conn->connect_error);
}

// 添加enable_category字段的SQL语句
$sql = "ALTER TABLE `sys_department` ADD COLUMN `enable_category` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用数据分类 1:启用 0:禁用' AFTER `status`";

if ($conn->query($sql) === TRUE) {
    echo "字段添加成功";
} else {
    echo "错误: " . $conn->error;
}

$conn->close();
?>