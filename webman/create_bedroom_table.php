<?php

require __DIR__ . '/vendor/autoload.php';

// 直接使用PDO连接数据库
$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';
$charset = 'utf8mb4';

// 连接数据库
try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "数据库连接成功！\n";
} catch (Exception $e) {
    echo "数据库连接失败：" . $e->getMessage() . "\n";
    exit(1);
}

// 创建bedroom表
try {
    $sql = "
    CREATE TABLE IF NOT EXISTS `bedroom` (
        `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        `store_id` INT UNSIGNED NOT NULL,
        `room_name` VARCHAR(255) NOT NULL,
        `bed_count` INT UNSIGNED NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX `idx_store_id` (`store_id`),
        INDEX `idx_room_name` (`room_name`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    
    $pdo->exec($sql);
    echo "bedroom表创建成功！\n";
    
    // 检查是否创建成功
    $stmt = $pdo->query("SHOW TABLES LIKE 'bedroom'");
    if ($stmt->rowCount() > 0) {
        echo "bedroom表已存在或创建成功！\n";
    } else {
        echo "bedroom表创建失败！\n";
    }
    
} catch (Exception $e) {
    echo "创建表失败：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
echo "操作完成！\n";
