<?php
require 'vendor/autoload.php';

use support\Db;

// 查看数据库连接信息
try {
    $pdo = Db::connection()->getPdo();
    echo "数据库连接成功！\n";
    
    // 查看菜单表结构
    echo "\n菜单表结构：\n";
    $result = Db::select('SHOW CREATE TABLE menu');
    echo $result[0]->{'Create Table'};
    echo "\n";
    
    // 查看菜单数据
    echo "\n菜单数据：\n";
    $menus = Db::select('SELECT * FROM menu ORDER BY parent_id, menu_rank');
    echo json_encode($menus, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "\n";
    
} catch (\Exception $e) {
    echo "数据库操作失败：" . $e->getMessage() . "\n";
}
?>