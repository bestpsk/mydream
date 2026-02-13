<?php
// 直接使用PDO连接数据库
$host = '127.0.0.1';
$port = '3306';
$dbname = 'mydream';
$username = 'root';
$password = '123456';

try {
    // 连接数据库
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "数据库连接成功！\n";
    
    // 需要添加 isDelete 字段的表
    $tables = [
        'sys_company',
        'sys_store',
        'sys_department',
        'sys_position',
        'sys_user_employee'
    ];
    
    foreach ($tables as $table) {
        echo "\n处理表: {$table}\n";
        
        // 检查表是否存在
        $stmt = $pdo->prepare("SHOW TABLES LIKE ?");
        $stmt->execute([$table]);
        $tableExists = $stmt->fetch();
        if (!$tableExists) {
            echo "表 {$table} 不存在，跳过\n";
            continue;
        }
        
        // 检查 isDelete 字段是否已存在
        $stmt = $pdo->prepare("SHOW COLUMNS FROM {$table} LIKE 'isDelete'");
        $stmt->execute();
        $columnExists = $stmt->fetch();
        if ($columnExists) {
            echo "字段 isDelete 已存在，跳过\n";
            continue;
        }
        
        // 添加 isDelete 字段
        try {
            $pdo->exec("ALTER TABLE {$table} ADD COLUMN isDelete TINYINT(1) DEFAULT 0 NOT NULL COMMENT '是否删除：0-未删除，1-已删除'");
            echo "成功为表 {$table} 添加 isDelete 字段\n";
        } catch (Exception $e) {
            echo "添加字段失败: {$e->getMessage()}\n";
        }
    }
    
    echo "\n所有表处理完成！\n";
    
} catch (\Exception $e) {
    echo "数据库操作失败：" . $e->getMessage() . "\n";
}
?>