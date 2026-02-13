<?php
require 'vendor/autoload.php';

use support\Db;

// 更新菜单结构，将部门职位管理移动到门店管理下
try {
    // 连接数据库
    $pdo = Db::connection()->getPdo();
    echo "数据库连接成功！\n";
    
    // 1. 查看当前的菜单数据
    echo "\n1. 当前菜单数据：\n";
    $menus = Db::select('SELECT id, name, path, component, parent_id, menu_rank, status FROM menu ORDER BY parent_id, menu_rank');
    foreach ($menus as $menu) {
        echo "ID: {$menu->id}, Name: {$menu->name}, Path: {$menu->path}, ParentID: {$menu->parent_id}, Rank: {$menu->menu_rank}, Status: {$menu->status}\n";
    }
    
    // 2. 查找企业管理菜单
    echo "\n2. 查找企业管理菜单：\n";
    $enterpriseMenu = Db::table('menu')->where('path', '/enterprise')->first();
    if ($enterpriseMenu) {
        echo "企业管理菜单 - ID: {$enterpriseMenu->id}, Name: {$enterpriseMenu->name}, Path: {$enterpriseMenu->path}\n";
    } else {
        echo "企业管理菜单未找到！\n";
    }
    
    // 3. 查找门店管理菜单
    echo "\n3. 查找门店管理菜单：\n";
    $storeMenu = Db::table('menu')->where('path', '/enterprise/store')->first();
    if ($storeMenu) {
        echo "门店管理菜单 - ID: {$storeMenu->id}, Name: {$storeMenu->name}, Path: {$storeMenu->path}, ParentID: {$storeMenu->parent_id}\n";
    } else {
        echo "门店管理菜单未找到！\n";
    }
    
    // 4. 查找部门职位管理菜单
    echo "\n4. 查找部门职位管理菜单：\n";
    $deptPositionMenu = Db::table('menu')->where('path', '/enterprise/dept-position')->first();
    if ($deptPositionMenu) {
        echo "部门职位管理菜单 - ID: {$deptPositionMenu->id}, Name: {$deptPositionMenu->name}, Path: {$deptPositionMenu->path}, ParentID: {$deptPositionMenu->parent_id}\n";
    } else {
        echo "部门职位管理菜单未找到！\n";
    }
    
    // 5. 更新菜单结构
    echo "\n5. 更新菜单结构：\n";
    
    // 5.1 如果找到部门职位管理菜单，将其状态设置为0（禁用）
    if ($deptPositionMenu) {
        Db::table('menu')->where('id', $deptPositionMenu->id)->update(['status' => 0]);
        echo "已禁用部门职位管理菜单（ID: {$deptPositionMenu->id}）\n";
    }
    
    // 5.2 确保门店管理菜单存在
    if ($storeMenu) {
        // 5.3 检查门店管理菜单是否已经有子菜单
        $storeChildren = Db::table('menu')->where('parent_id', $storeMenu->id)->get();
        echo "门店管理菜单当前有 {$storeChildren->count()} 个子菜单\n";
        
        // 5.4 检查是否已经存在部门管理子菜单
        $deptSubMenu = Db::table('menu')->where('parent_id', $storeMenu->id)->where('path', '/enterprise/store/dept')->first();
        if (!$deptSubMenu) {
            // 创建部门管理子菜单
            $deptSubMenuId = Db::table('menu')->insertGetId([
                'name' => '部门管理',
                'path' => '/enterprise/store/dept',
                'component' => 'enterprise/store/index.vue',
                'parent_id' => $storeMenu->id,
                'menu_rank' => 1,
                'status' => 1,
                'is_frame' => 0,
                'show_link' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            echo "已创建部门管理子菜单（ID: {$deptSubMenuId}）\n";
        } else {
            echo "部门管理子菜单已存在（ID: {$deptSubMenu->id}）\n";
        }
        
        // 5.5 检查是否已经存在职位管理子菜单
        $positionSubMenu = Db::table('menu')->where('parent_id', $storeMenu->id)->where('path', '/enterprise/store/position')->first();
        if (!$positionSubMenu) {
            // 创建职位管理子菜单
            $positionSubMenuId = Db::table('menu')->insertGetId([
                'name' => '职位管理',
                'path' => '/enterprise/store/position',
                'component' => 'enterprise/store/index.vue',
                'parent_id' => $storeMenu->id,
                'menu_rank' => 2,
                'status' => 1,
                'is_frame' => 0,
                'show_link' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            echo "已创建职位管理子菜单（ID: {$positionSubMenuId}）\n";
        } else {
            echo "职位管理子菜单已存在（ID: {$positionSubMenu->id}）\n";
        }
    }
    
    // 6. 查看更新后的菜单结构
    echo "\n6. 更新后的菜单结构：\n";
    $updatedMenus = Db::select('SELECT id, name, path, component, parent_id, menu_rank, status FROM menu ORDER BY parent_id, menu_rank');
    foreach ($updatedMenus as $menu) {
        echo "ID: {$menu->id}, Name: {$menu->name}, Path: {$menu->path}, ParentID: {$menu->parent_id}, Rank: {$menu->menu_rank}, Status: {$menu->status}\n";
    }
    
    echo "\n菜单结构更新完成！\n";
    
} catch (\Exception $e) {
    echo "数据库操作失败：" . $e->getMessage() . "\n";
}
?>