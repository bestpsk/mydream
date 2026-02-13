<?php

$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 检查菜单表
    echo "=== 菜单列表 ===\n";
    $stmt = $pdo->query('SELECT id, name, path, parent_id FROM menus WHERE status = 1 ORDER BY id');
    $menus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($menus as $menu) {
        echo "ID: {$menu['id']}, 名称: {$menu['name']}, 路径: {$menu['path']}, 父ID: {$menu['parent_id']}\n";
    }
    
    // 检查权限表
    echo "\n=== 权限列表 ===\n";
    $stmt = $pdo->query('SELECT id, name, code, menu_id FROM permissions WHERE status = 1 ORDER BY menu_id, id');
    $permissions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($permissions as $permission) {
        echo "ID: {$permission['id']}, 名称: {$permission['name']}, 代码: {$permission['code']}, 菜单ID: {$permission['menu_id']}\n";
    }
    
    // 检查菜单与权限关联
    echo "\n=== 菜单与权限关联检查 ===\n";
    foreach ($permissions as $permission) {
        $menuFound = false;
        foreach ($menus as $menu) {
            if ($menu['id'] == $permission['menu_id']) {
                echo "权限 {$permission['code']} 关联到菜单 {$menu['name']} (ID: {$menu['id']})\n";
                $menuFound = true;
                break;
            }
        }
        if (!$menuFound) {
            echo "权限 {$permission['code']} (ID: {$permission['id']}) 关联的菜单ID {$permission['menu_id']} 不存在！\n";
        }
    }
    
    // 检查是否有门店管理的菜单
    echo "\n=== 门店管理菜单检查 ===\n";
    $stmt = $pdo->query('SELECT id, name, path, parent_id FROM menus WHERE name LIKE "%门店管理%" AND status = 1');
    $storeMenus = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($storeMenus)) {
        echo "未找到门店管理菜单！\n";
    } else {
        foreach ($storeMenus as $menu) {
            echo "ID: {$menu['id']}, 名称: {$menu['name']}, 路径: {$menu['path']}, 父ID: {$menu['parent_id']}\n";
        }
    }
    
} catch (PDOException $e) {
    echo "数据库连接失败: " . $e->getMessage() . "\n";
}
