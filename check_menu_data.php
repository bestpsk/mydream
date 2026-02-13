<?php
require 'webman/vendor/autoload.php';

use support\Db;

try {
    // 连接数据库
    $pdo = Db::connection()->getPdo();
    echo "数据库连接成功！\n";
    
    // 查看菜单数据
    echo "\n菜单数据：\n";
    $menus = Db::select('SELECT id, name, path, parent_id, status FROM menu ORDER BY parent_id, id');
    echo json_encode($menus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
    
    // 查看门店管理菜单的子菜单
    echo "\n门店管理菜单的子菜单：\n";
    $storeMenu = Db::table('menu')->where('path', '/system/enterprise/store')->first();
    if ($storeMenu) {
        $storeChildren = Db::table('menu')->where('parent_id', $storeMenu->id)->get();
        echo json_encode($storeChildren, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    } else {
        echo "门店管理菜单未找到！\n";
    }
    
} catch (\Exception $e) {
    echo "数据库操作失败：" . $e->getMessage() . "\n";
}
?>