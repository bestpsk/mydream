<?php
require 'config/database.php';

$config = config('database');
$connection = $config['connections']['mysql'];

$dsn = 'mysql:host=' . $connection['host'] . ';dbname=' . $connection['database'] . ';charset=utf8mb4';
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $connection['username'], $connection['password'], $options);
    
    // 查询所有菜单
    $stmt = $pdo->query('SELECT * FROM sys_menu WHERE status = 1 ORDER BY parent_id, menu_rank');
    $menus = $stmt->fetchAll();
    
    echo "所有菜单数据：\n";
    foreach ($menus as $menu) {
        echo "ID: {$menu['id']}, Name: {$menu['name']}, Path: {$menu['path']}, Component: {$menu['component']}, Parent ID: {$menu['parent_id']}\n";
    }
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}
?>