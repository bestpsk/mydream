<?php
require 'vendor/autoload.php';

use app\model\Menu;

// 获取所有菜单
$menus = Menu::all();

// 打印菜单数据
echo "当前菜单数据：\n";
echo json_encode($menus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";

// 获取门店管理菜单
$storeMenu = Menu::where('path', '/enterprise/store')->first();
echo "\n门店管理菜单：\n";
echo json_encode($storeMenu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";

// 获取部门职位管理菜单
$deptPositionMenu = Menu::where('path', '/enterprise/dept-position')->first();
echo "\n部门职位管理菜单：\n";
echo json_encode($deptPositionMenu, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
echo "\n";
?>