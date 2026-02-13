<?php

// 从card_project表中删除不需要的功能开关字段
require_once __DIR__ . '/vendor/autoload.php';

use support\DB;

// 错误处理
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    echo "错误: $errstr 在 $errfile 第 $errline 行\n";
    return true;
});

set_exception_handler(function ($exception) {
    echo "异常: {$exception->getMessage()} 在 {$exception->getFile()} 第 {$exception->getLine()} 行\n";
    return true;
});

// 连接数据库
try {
    $config = require __DIR__ . '/config/database.php';
    $defaultConnection = $config['default'];
    $connectionConfig = $config['connections'][$defaultConnection];
    
    $pdo = new PDO(
        "mysql:host={$connectionConfig['host']};port={$connectionConfig['port']};dbname={$connectionConfig['database']}",
        $connectionConfig['username'],
        $connectionConfig['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    echo "数据库连接成功\n";
} catch (PDOException $e) {
    die("数据库连接失败: " . $e->getMessage() . "\n");
} catch (Exception $e) {
    die("配置读取失败: " . $e->getMessage() . "\n");
}

// 从card_project表中删除不需要的字段
function removeProjectFeatureFields($pdo) {
    echo "\n从card_project表中删除不需要的功能开关字段...\n";
    
    // 需要删除的字段
    $fieldsToRemove = [
        'cooperation_project',
        'ym_project',
        'special_project',
        'big_project',
        'small_project'
    ];
    
    foreach ($fieldsToRemove as $field) {
        try {
            $pdo->exec("ALTER TABLE `card_project` DROP COLUMN `$field`");
            echo "删除字段 $field 成功\n";
        } catch (PDOException $e) {
            if (strpos($e->getMessage(), 'Unknown column') !== false) {
                echo "字段 $field 不存在\n";
            } else {
                echo "删除字段 $field 失败: " . $e->getMessage() . "\n";
            }
        }
    }
}

// 执行删除操作
function main() {
    global $pdo;
    
    try {
        removeProjectFeatureFields($pdo);
        echo "\n字段删除完成！\n";
    } catch (Exception $e) {
        echo "\n执行失败: " . $e->getMessage() . "\n";
    }
}

// 运行脚本
main();

// 关闭连接
$pdo = null;
echo "\n数据库连接已关闭\n";
