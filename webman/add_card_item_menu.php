<?php
require 'vendor/autoload.php';

// 直接使用PDO连接数据库
$host = '127.0.0.1';
$port = 3306;
$dbname = 'mydream';
$username = 'root';
$password = '123456';
$charset = 'utf8mb4';

// 添加卡项管理菜单数据
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
    
    // 1. 插入一级菜单：卡项管理
    echo "\n1. 插入一级菜单：卡项管理\n";
    $stmt = $pdo->prepare("INSERT INTO sys_menu (name, path, component, parent_id, icon, menu_rank, is_frame, show_link, status, created_at, updated_at) VALUES (:name, :path, :component, :parent_id, :icon, :menu_rank, :is_frame, :show_link, :status, :created_at, :updated_at)");
    $stmt->execute([
        ':name' => '卡项管理',
        ':path' => '/card-item',
        ':component' => 'Layout',
        ':parent_id' => 0,
        ':icon' => 'el-icon-credit-card',
        ':menu_rank' => 5,
        ':is_frame' => 0,
        ':show_link' => 1,
        ':status' => 1,
        ':created_at' => date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s')
    ]);
    $cardItemMenuId = $pdo->lastInsertId();
    echo "卡项管理一级菜单插入成功，ID: {$cardItemMenuId}\n";
    
    // 2. 插入二级菜单：项目管理
    echo "\n2. 插入二级菜单：项目管理\n";
    $stmt = $pdo->prepare("INSERT INTO sys_menu (name, path, component, parent_id, menu_rank, is_frame, show_link, status, created_at, updated_at) VALUES (:name, :path, :component, :parent_id, :menu_rank, :is_frame, :show_link, :status, :created_at, :updated_at)");
    $stmt->execute([
        ':name' => '项目管理',
        ':path' => 'project',
        ':component' => 'card-item/project/index.vue',
        ':parent_id' => $cardItemMenuId,
        ':menu_rank' => 1,
        ':is_frame' => 0,
        ':show_link' => 1,
        ':status' => 1,
        ':created_at' => date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s')
    ]);
    $projectMenuId = $pdo->lastInsertId();
    echo "项目管理二级菜单插入成功，ID: {$projectMenuId}\n";
    
    // 3. 插入二级菜单：卡项管理
    echo "\n3. 插入二级菜单：卡项管理\n";
    $stmt = $pdo->prepare("INSERT INTO sys_menu (name, path, component, parent_id, menu_rank, is_frame, show_link, status, created_at, updated_at) VALUES (:name, :path, :component, :parent_id, :menu_rank, :is_frame, :show_link, :status, :created_at, :updated_at)");
    $stmt->execute([
        ':name' => '卡项管理',
        ':path' => 'card',
        ':component' => 'card-item/card/index.vue',
        ':parent_id' => $cardItemMenuId,
        ':menu_rank' => 2,
        ':is_frame' => 0,
        ':show_link' => 1,
        ':status' => 1,
        ':created_at' => date('Y-m-d H:i:s'),
        ':updated_at' => date('Y-m-d H:i:s')
    ]);
    $cardMenuId = $pdo->lastInsertId();
    echo "卡项管理二级菜单插入成功，ID: {$cardMenuId}\n";
    
    // 4. 查看当前的菜单数据
    echo "\n4. 当前菜单数据：\n";
    $stmt = $pdo->query("SELECT id, name, path, component, parent_id, menu_rank, status FROM sys_menu ORDER BY parent_id, menu_rank");
    $menus = $stmt->fetchAll(PDO::FETCH_OBJ);
    foreach ($menus as $menu) {
        echo "ID: {$menu->id}, Name: {$menu->name}, Path: {$menu->path}, ParentID: {$menu->parent_id}, Rank: {$menu->menu_rank}, Status: {$menu->status}\n";
    }
    
    echo "\n菜单数据插入完成！\n";
    
} catch (Exception $e) {
    echo "错误：" . $e->getMessage() . "\n";
}

// 关闭连接
unset($pdo);
echo "操作完成！\n";
