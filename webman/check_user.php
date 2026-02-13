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
    
    // 查询所有用户
    $stmt = $pdo->query("SELECT * FROM sys_user");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "数据库中的用户信息：\n";
    echo "---------------------------------------------------\n";
    echo "ID\t用户名\t昵称\t公司ID\t门店ID\t员工ID\t状态\n";
    echo "---------------------------------------------------\n";
    
    foreach ($users as $user) {
        echo sprintf("%d\t%s\t%s\t%d\t%d\t%d\t%d\n", 
            $user['id'], 
            $user['username'], 
            $user['nickname'],
            $user['company_id'] ?? 0,
            $user['store_id'] ?? 0,
            $user['employee_id'] ?? 0,
            $user['status']
        );
    }
    
    echo "---------------------------------------------------\n";
    
    // 检查是否存在用户名但没有公司ID的用户
    $stmt = $pdo->query("SELECT * FROM sys_user WHERE company_id IS NULL OR company_id = 0");
    $usersWithoutCompany = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if ($usersWithoutCompany) {
        echo "存在没有公司ID的用户：\n";
        echo "---------------------------------------------------\n";
        echo "ID\t用户名\t昵称\t状态\n";
        echo "---------------------------------------------------\n";
        
        foreach ($usersWithoutCompany as $user) {
            echo sprintf("%d\t%s\t%s\t%d\n", 
                $user['id'], 
                $user['username'], 
                $user['nickname'],
                $user['status']
            );
        }
        
        echo "---------------------------------------------------\n";
    } else {
        echo "所有用户都有公司ID\n";
    }
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>