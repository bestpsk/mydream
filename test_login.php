<?php

// 测试登录接口
$url = 'http://localhost:8787/api/auth/login';
$data = [
    'username' => 'admin',
    'password' => 'admin123'
];

$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo 'Error: ' . error_get_last()['message'] . "\n";
} else {
    echo 'Response: ' . $response . "\n";
}
