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
    
    // 获取公司编码为"admin"的公司ID
    $stmt = $pdo->query("SELECT id FROM sys_company WHERE code = 'admin'");
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($company) {
        $companyId = $company['id'];
        echo "公司编码为'admin'的公司ID: {$companyId}\n";
        
        // 更新用户admin的company_id为该公司ID
        $stmt = $pdo->prepare("UPDATE sys_user SET company_id = :companyId WHERE username = 'admin'");
        $stmt->bindParam(':companyId', $companyId);
        $result = $stmt->execute();
        
        if ($result) {
            echo "成功更新用户admin的公司ID为: {$companyId}\n";
            
            // 验证更新结果
            $stmt = $pdo->query("SELECT * FROM sys_user WHERE username = 'admin'");
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($user) {
                echo "更新后的用户信息：\n";
                echo sprintf("ID: %d, 用户名: %s, 昵称: %s, 公司ID: %d, 状态: %d\n", 
                    $user['id'], 
                    $user['username'], 
                    $user['nickname'],
                    $user['company_id'],
                    $user['status']
                );
            }
        } else {
            echo "更新用户admin的公司ID失败\n";
        }
    } else {
        echo "不存在公司编码为'admin'的公司\n";
    }
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>