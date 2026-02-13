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
    
    // 1. 创建供应商表 (supplier)
    echo "\n1. 创建供应商表 (supplier)\n";
    $sql = "CREATE TABLE IF NOT EXISTS supplier (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        supplier_name VARCHAR(255) NOT NULL COMMENT '供应商名称',
        contact VARCHAR(100) NOT NULL COMMENT '联系人',
        phone VARCHAR(20) NOT NULL COMMENT '联系电话',
        address VARCHAR(255) COMMENT '地址',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供应商表';";
    $pdo->exec($sql);
    echo "供应商表创建成功！\n";
    
    // 2. 创建项目分类表 (project_category)
    echo "\n2. 创建项目分类表 (project_category)\n";
    $sql = "CREATE TABLE IF NOT EXISTS project_category (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        category_name VARCHAR(100) NOT NULL COMMENT '分类名称',
        sort INT DEFAULT 0 COMMENT '排序',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目分类表';";
    $pdo->exec($sql);
    echo "项目分类表创建成功！\n";
    
    // 3. 创建项目表 (project)
    echo "\n3. 创建项目表 (project)\n";
    $sql = "CREATE TABLE IF NOT EXISTS project (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        project_name VARCHAR(255) NOT NULL COMMENT '项目名称',
        category_id INT UNSIGNED NOT NULL COMMENT '所属分类ID',
        price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '价格',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
        INDEX idx_category_id (category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目表';";
    $pdo->exec($sql);
    echo "项目表创建成功！\n";
    
    // 4. 创建充值卡表 (card_recharge)
    echo "\n4. 创建充值卡表 (card_recharge)\n";
    $sql = "CREATE TABLE IF NOT EXISTS card_recharge (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        card_name VARCHAR(100) NOT NULL COMMENT '卡名称',
        card_value DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '卡面值',
        sale_price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '销售价格',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡表';";
    $pdo->exec($sql);
    echo "充值卡表创建成功！\n";
    
    // 5. 创建疗程卡表 (card_course)
    echo "\n5. 创建疗程卡表 (card_course)\n";
    $sql = "CREATE TABLE IF NOT EXISTS card_course (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        card_name VARCHAR(100) NOT NULL COMMENT '卡名称',
        course_count INT NOT NULL DEFAULT 0 COMMENT '疗程次数',
        price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '价格',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='疗程卡表';";
    $pdo->exec($sql);
    echo "疗程卡表创建成功！\n";
    
    // 6. 创建套餐卡表 (card_package)
    echo "\n6. 创建套餐卡表 (card_package)\n";
    $sql = "CREATE TABLE IF NOT EXISTS card_package (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        card_name VARCHAR(100) NOT NULL COMMENT '卡名称',
        price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '价格',
        project_count INT NOT NULL DEFAULT 0 COMMENT '包含项目数',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐卡表';";
    $pdo->exec($sql);
    echo "套餐卡表创建成功！\n";
    
    // 7. 创建时效卡表 (card_time)
    echo "\n7. 创建时效卡表 (card_time)\n";
    $sql = "CREATE TABLE IF NOT EXISTS card_time (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        card_name VARCHAR(100) NOT NULL COMMENT '卡名称',
        valid_days INT NOT NULL DEFAULT 0 COMMENT '有效期(天)',
        price DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT '价格',
        isDelete TINYINT(1) DEFAULT 0 COMMENT '是否删除',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='时效卡表';";
    $pdo->exec($sql);
    echo "时效卡表创建成功！\n";
    
    // 8. 验证所有表是否创建成功
    echo "\n8. 验证表结构\n";
    $tables = ['supplier', 'project_category', 'project', 'card_recharge', 'card_course', 'card_package', 'card_time'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "表 $table 存在\n";
        } else {
            echo "表 $table 不存在\n";
        }
    }
    
    echo "\n所有表结构创建完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
echo "操作完成！\n";