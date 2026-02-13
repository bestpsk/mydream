<?php

// 检查数据库中的关联关系
require_once __DIR__ . '/webman/vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

// 初始化数据库连接
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => '3306',
    'database'  => 'mydream',
    'username'  => 'root',
    'password'  => '123456',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// 检查用户
$user = App\Model\User::with('roles.menus')->find(1);
echo "User info:\n";
echo "ID: " . $user->id . "\n";
echo "Username: " . $user->username . "\n";
echo "Nickname: " . $user->nickname . "\n";
echo "Roles: " . count($user->roles) . "\n";

// 检查角色和菜单
foreach ($user->roles as $role) {
    echo "\nRole: " . $role->name . "\n";
    echo "Menus count: " . count($role->menus) . "\n";
    foreach ($role->menus as $menu) {
        echo "  Menu: " . $menu->name . " (ID: " . $menu->id . ")\n";
        echo "    Path: " . $menu->path . "\n";
        echo "    Component: " . $menu->component . "\n";
        echo "    Status: " . $menu->status . "\n";
    }
}

// 检查所有菜单
echo "\nAll menus in database:\n";
$allMenus = App\Model\Menu::all();
foreach ($allMenus as $menu) {
    echo "Menu: " . $menu->name . " (ID: " . $menu->id . ")\n";
    echo "  Path: " . $menu->path . "\n";
    echo "  Component: " . $menu->component . "\n";
    echo "  Status: " . $menu->status . "\n";
}

// 检查用户角色关联
echo "\nUser-role relationships:\n";
$userRoles = App\Model\User::with('roles')->find(1);
foreach ($userRoles->roles as $role) {
    echo "User " . $userRoles->username . " has role: " . $role->name . "\n";
}

// 检查角色菜单关联
echo "\nRole-menu relationships:\n";
$roles = App\Model\Role::with('menus')->get();
foreach ($roles as $role) {
    echo "Role " . $role->name . " has " . count($role->menus) . " menus\n";
    foreach ($role->menus as $menu) {
        echo "  Menu: " . $menu->name . "\n";
    }
}
?>