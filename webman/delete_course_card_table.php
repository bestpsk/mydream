<?php

// 直接使用PDO连接数据库
$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';
$charset = 'utf8mb4';

try {
    // 连接数据库
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=$charset";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    $pdo = new PDO($dsn, $username, $password, $options);
    echo "数据库连接成功！\n";
    
    // 删除疗程卡表 (card_course)
    echo "\n删除疗程卡表 (card_course)\n";
    $sql = "DROP TABLE IF EXISTS card_course";
    $pdo->exec($sql);
    echo "疗程卡表删除成功！\n";
    
    // 验证表是否删除成功
    echo "\n验证表结构\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'card_course'");
    if ($stmt->rowCount() > 0) {
        echo "表 card_course 仍然存在\n";
    } else {
        echo "表 card_course 已成功删除\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
