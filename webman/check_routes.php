<?php
require 'vendor/autoload.php';

echo '检查 webman 路由配置...' . PHP_EOL;

// 检查路由配置文件
$routeFiles = [
    'config/route.php',
    'config/plugin/*/route.php'
];

foreach ($routeFiles as $pattern) {
    $files = glob($pattern);
    foreach ($files as $file) {
        if (file_exists($file)) {
            echo '找到路由文件: ' . $file . PHP_EOL;
            echo '-----------------------------------' . PHP_EOL;
            echo file_get_contents($file) . PHP_EOL;
            echo '-----------------------------------' . PHP_EOL . PHP_EOL;
        }
    }
}

echo '检查完成！' . PHP_EOL;
?>