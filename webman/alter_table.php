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
    
    // 执行修改表结构的SQL语句
    $sql = "ALTER TABLE sys_company MODIFY COLUMN service_people VARCHAR(50) NOT NULL DEFAULT ''";
    $pdo->exec($sql);
    
    echo "修改表结构成功：service_people 字段类型已从 int(11) 改为 VARCHAR(50)\n";
    
    // 验证修改结果
    $stmt = $pdo->query("DESCRIBE sys_company");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "\n修改后的 sys_company 表结构：\n";
    echo "---------------------------------------------------\n";
    echo "字段名\t\t类型\t\t是否为空\t默认值\t注释\n";
    echo "---------------------------------------------------\n";
    
    foreach ($columns as $column) {
        if ($column['Field'] === 'service_people') {
            echo sprintf("%s\t\t%s\t\t%s\t\t%s\t\t%s\n", 
                $column['Field'], 
                $column['Type'], 
                $column['Null'], 
                $column['Default'] ?? 'NULL', 
                $column['Extra']
            );
        }
    }
    
    echo "---------------------------------------------------\n";
    
} catch (PDOException $e) {
    echo "修改表结构失败: " . $e->getMessage() . "\n";
}
?>