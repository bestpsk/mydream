<?php

// 测试登录API
$loginUrl = 'http://localhost:8787/api/auth/login';
$loginData = json_encode([
    'companyCode' => 'admin',
    'username' => 'admin',
    'password' => 'admin123'
]);

$loginOptions = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => $loginData,
    ],
];

$loginContext = stream_context_create($loginOptions);
$loginResponse = file_get_contents($loginUrl, false, $loginContext);
echo "Login Response:\n";
echo $loginResponse . "\n\n";

// 解析登录响应，获取accessToken
$loginData = json_decode($loginResponse, true);
if (isset($loginData['code']) && $loginData['code'] == 200) {
    $accessToken = $loginData['data']['accessToken'];
    
    // 测试路由API
    $routesUrl = 'http://localhost:8787/api/permission/routes';
    $routesOptions = [
        'http' => [
            'header' => "Authorization: Bearer $accessToken\r\n",
            'method' => 'GET',
        ],
    ];
    
    $routesContext = stream_context_create($routesOptions);
    $routesResponse = file_get_contents($routesUrl, false, $routesContext);
    echo "Routes Response:\n";
    echo $routesResponse . "\n\n";
    
    // 检查响应编码
    echo "Response Encoding: " . mb_detect_encoding($routesResponse) . "\n";
} else {
    echo "Login failed!\n";
}
?>