<?php

// 修复数据库中的密码哈希值
$host = '127.0.0.1';
$port = '3306';
$dbname = 'mydream';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 生成正确的密码哈希
    $password_hash = password_hash('admin123', PASSWORD_DEFAULT);
    echo "生成的密码哈希: $password_hash\n\n";
    
    // 更新用户密码
    $stmt = $pdo->prepare("UPDATE sys_user SET password = ? WHERE username = ?");
    $result = $stmt->execute([$password_hash, 'admin']);
    
    if ($result) {
        echo "密码更新成功！\n\n";
        
        // 验证更新后的密码
        $stmt = $pdo->prepare("SELECT password FROM sys_user WHERE username = ?");
        $stmt->execute(['admin']);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            echo "数据库中的密码哈希: " . $user['password'] . "\n\n";
            
            // 测试密码验证
            echo "测试密码验证:\n";
            $password_to_test = 'admin123';
            if (password_verify($password_to_test, $user['password'])) {
                echo "密码验证成功！\n";
            } else {
                echo "密码验证失败！\n";
            }
        }
    } else {
        echo "密码更新失败！\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
