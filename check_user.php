<?php

// 检查数据库中的用户数据
$host = '127.0.0.1';
$port = '3306';
$dbname = 'mydream';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查用户表结构
    $stmt = $pdo->query("DESCRIBE sys_user");
    echo "用户表结构:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    echo "\n";
    
    // 检查用户数据
    $stmt = $pdo->query("SELECT id, username, password, nickname, status FROM sys_user");
    echo "用户数据:\n";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: " . $row['id'] . "\n";
        echo "用户名: " . $row['username'] . "\n";
        echo "密码哈希: " . $row['password'] . "\n";
        echo "昵称: " . $row['nickname'] . "\n";
        echo "状态: " . $row['status'] . "\n";
        echo "\n";
    }
    
    // 测试密码验证
    $stmt = $pdo->prepare("SELECT password FROM sys_user WHERE username = ?");
    $stmt->execute(['admin']);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user) {
        echo "测试密码验证:\n";
        $password_to_test = 'admin123';
        if (password_verify($password_to_test, $user['password'])) {
            echo "密码验证成功！\n";
        } else {
            echo "密码验证失败！\n";
        }
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
