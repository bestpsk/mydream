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
    
    // 检查表是否已存在
    $stmt = $pdo->query("SHOW TABLES LIKE 'sys_user_store'");
    $tableExists = $stmt->rowCount() > 0;
    
    if (!$tableExists) {
        // 创建sys_user_store表
        $sql = "CREATE TABLE `sys_user_store` (
            `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `user_id` INT UNSIGNED NOT NULL,
            `store_id` INT UNSIGNED NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE INDEX `idx_user_store` (`user_id`, `store_id`),
            INDEX `idx_user_id` (`user_id`),
            INDEX `idx_store_id` (`store_id`),
            CONSTRAINT `fk_user_store_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_user_store_store` FOREIGN KEY (`store_id`) REFERENCES `sys_store` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户门店权限表'";
        
        $pdo->exec($sql);
        echo "成功创建sys_user_store表\n";
    } else {
        echo "sys_user_store表已存在，跳过创建\n";
    }
    
    // 验证表结构
    $stmt = $pdo->query("DESCRIBE sys_user_store");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\nsys_user_store 表结构：\n";
    echo "---------------------------------------------------\n";
    echo "字段名\t\t类型\t\t是否为空\t默认值\t注释\n";
    echo "---------------------------------------------------\n";
    
    foreach ($columns as $column) {
        echo sprintf("%s\t\t%s\t\t%s\t\t%s\t\t%s\n", 
            $column['Field'], 
            $column['Type'], 
            $column['Null'], 
            $column['Default'] ?? 'NULL', 
            $column['Extra']
        );
    }
    
    echo "---------------------------------------------------\n";
    
} catch (PDOException $e) {
    echo "创建表失败: " . $e->getMessage() . "\n";
}
?>