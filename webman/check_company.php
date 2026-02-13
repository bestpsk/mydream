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
    
    // 查询公司信息
    $stmt = $pdo->query("SELECT * FROM sys_company");
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "数据库中的公司信息：\n";
    echo "---------------------------------------------------\n";
    echo "ID\t编码\t名称\t状态\n";
    echo "---------------------------------------------------\n";
    
    foreach ($companies as $company) {
        echo sprintf("%d\t%s\t%s\t%d\n", 
            $company['id'], 
            $company['code'], 
            $company['name'],
            $company['status']
        );
    }
    
    echo "---------------------------------------------------\n";
    
    // 检查是否存在公司编码为"admin"的公司
    $stmt = $pdo->query("SELECT * FROM sys_company WHERE code = 'admin'");
    $adminCompany = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($adminCompany) {
        echo "存在公司编码为'admin'的公司：\n";
        echo sprintf("ID: %d, 编码: %s, 名称: %s, 状态: %d\n", 
            $adminCompany['id'], 
            $adminCompany['code'], 
            $adminCompany['name'],
            $adminCompany['status']
        );
        
        // 查询与该公司关联的用户
        $stmt = $pdo->query("SELECT * FROM sys_user WHERE company_id = " . $adminCompany['id']);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "\n与该公司关联的用户：\n";
        echo "---------------------------------------------------\n";
        echo "ID\t用户名\t昵称\t状态\n";
        echo "---------------------------------------------------\n";
        
        foreach ($users as $user) {
            echo sprintf("%d\t%s\t%s\t%d\n", 
                $user['id'], 
                $user['username'], 
                $user['nickname'],
                $user['status']
            );
        }
        
        echo "---------------------------------------------------\n";
    } else {
        echo "不存在公司编码为'admin'的公司\n";
    }
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>