<?php
// 数据库连接信息
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

// 查询门店表
try {
    $stmt = $pdo->query('SELECT id, store_name FROM sys_store');
    $stores = $stmt->fetchAll();
    
    echo "门店列表：\n";
    echo "-----------------------------------\n";
    foreach ($stores as $store) {
        echo "ID: {$store['id']} - 名称: {$store['store_name']}\n";
    }
    echo "-----------------------------------\n";
} catch (Exception $e) {
    echo "查询失败：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
?>