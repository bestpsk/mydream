<?php

// 测试后端菜单接口
$url = 'http://localhost:8787/api/permission/routes';

// 创建一个包含认证信息的请求
$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'GET',
    ],
];

$context = stream_context_create($options);
$response = file_get_contents($url, false, $context);

if ($response === FALSE) {
    echo "Error: Failed to fetch routes\n";
} else {
    echo "Backend routes response:\n";
    echo $response;
    echo "\n";
}

// 测试登录接口，获取token
$loginUrl = 'http://localhost:8787/api/auth/login';
$loginData = json_encode(['username' => 'admin', 'password' => 'admin123']);

$loginOptions = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $loginData,
    ],
];

$loginContext = stream_context_create($loginOptions);
$loginResponse = file_get_contents($loginUrl, false, $loginContext);

echo "\nLogin response:\n";
echo $loginResponse;
echo "\n";

// 如果登录成功，使用token再次请求菜单接口
if ($loginResponse !== FALSE) {
    $loginData = json_decode($loginResponse, true);
    if (isset($loginData['code']) && $loginData['code'] == 200 && isset($loginData['data']['accessToken'])) {
        $token = $loginData['data']['accessToken'];
        
        $routesOptions = [
            'http' => [
                'header' => "Content-Type: application/json\r\nAuthorization: Bearer $token\r\n",
                'method' => 'GET',
            ],
        ];
        
        $routesContext = stream_context_create($routesOptions);
        $routesResponse = file_get_contents($url, false, $routesContext);
        
        echo "\nRoutes response with token:\n";
        echo $routesResponse;
        echo "\n";
    }
}
?>