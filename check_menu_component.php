<?php

// 检查数据库中的菜单表component字段值
$host = '127.0.0.1';
$port = '3306';
$dbname = 'mydream';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查菜单表数据，包括component字段
    echo "=== 菜单表数据（包含component字段） ===\n";
    $stmt = $pdo->query("SELECT id, name, path, component, redirect, icon, show_link, is_frame, frame_src FROM sys_menu");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "ID: {$row['id']}, 名称: {$row['name']}, 路径: {$row['path']}\n";
        echo "Component: {$row['component']}, Redirect: {$row['redirect']}\n";
        echo "Icon: {$row['icon']}, ShowLink: {$row['show_link']}, IsFrame: {$row['is_frame']}, FrameSrc: {$row['frame_src']}\n";
        echo "\n";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
