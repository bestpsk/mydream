<?php
require 'vendor/autoload.php';

use support\Db;

// 更新按钮权限数据，确保与新的菜单结构匹配
try {
    // 连接数据库
    $pdo = Db::connection()->getPdo();
    echo "数据库连接成功！\n";
    
    // 1. 查看当前的权限数据
    echo "\n1. 当前的权限数据：\n";
    $permissions = Db::select('SELECT id, name, code, menu_id, type, status FROM sys_permission WHERE status = 1 ORDER BY menu_id');
    foreach ($permissions as $permission) {
        echo "ID: {$permission->id}, Name: {$permission->name}, Code: {$permission->code}, MenuID: {$permission->menu_id}, Type: {$permission->type}, Status: {$permission->status}\n";
    }
    
    // 2. 查找与部门职位管理相关的权限
    echo "\n2. 查找与部门职位管理相关的权限：\n";
    $deptPositionPermissions = Db::select('SELECT id, name, code, menu_id, type, status FROM sys_permission WHERE code LIKE "%dept%" OR code LIKE "%position%" OR code LIKE "%department%" ORDER BY menu_id');
    foreach ($deptPositionPermissions as $permission) {
        echo "ID: {$permission->id}, Name: {$permission->name}, Code: {$permission->code}, MenuID: {$permission->menu_id}, Type: {$permission->type}, Status: {$permission->status}\n";
    }
    
    // 3. 查找门店管理菜单
    echo "\n3. 查找门店管理菜单：\n";
    $storeMenu = Db::table('menu')->where('path', '/enterprise/store')->first();
    if ($storeMenu) {
        echo "门店管理菜单 - ID: {$storeMenu->id}, Name: {$storeMenu->name}, Path: {$storeMenu->path}\n";
    } else {
        echo "门店管理菜单未找到！\n";
    }
    
    // 4. 更新与部门职位管理相关的权限，使其关联到门店管理菜单
    echo "\n4. 更新与部门职位管理相关的权限：\n";
    if ($storeMenu) {
        foreach ($deptPositionPermissions as $permission) {
            // 更新权限的menu_id字段，使其关联到门店管理菜单
            Db::table('sys_permission')->where('id', $permission->id)->update(['menu_id' => $storeMenu->id]);
            echo "已更新权限 - ID: {$permission->id}, Name: {$permission->name}, Code: {$permission->code}, 新MenuID: {$storeMenu->id}\n";
        }
    }
    
    // 5. 查看更新后的权限数据
    echo "\n5. 更新后的权限数据：\n";
    $updatedPermissions = Db::select('SELECT id, name, code, menu_id, type, status FROM sys_permission WHERE status = 1 ORDER BY menu_id');
    foreach ($updatedPermissions as $permission) {
        echo "ID: {$permission->id}, Name: {$permission->name}, Code: {$permission->code}, MenuID: {$permission->menu_id}, Type: {$permission->type}, Status: {$permission->status}\n";
    }
    
    echo "\n按钮权限数据更新完成！\n";
    
} catch (\Exception $e) {
    echo "数据库操作失败：" . $e->getMessage() . "\n";
}
?>