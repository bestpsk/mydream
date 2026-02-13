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
    
    // 检查card_product表是否存在
    echo "\n检查card_product表是否存在\n";
    $stmt = $pdo->query("SHOW TABLES LIKE 'card_product'");
    if ($stmt->rowCount() > 0) {
        echo "card_product表已存在\n";
    } else {
        echo "card_product表不存在，开始创建\n";
        // 创建card_product表
        $sql = "CREATE TABLE IF NOT EXISTS card_product (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            company_id INT UNSIGNED COMMENT '所属公司ID',
            product_name VARCHAR(255) NOT NULL COMMENT '产品名称',
            price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '价格',
            isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
            INDEX idx_company_id (company_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品表';";
        $pdo->exec($sql);
        echo "card_product表创建成功！\n";
    }
    
    echo "\n操作完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
