<?php

// 生成唯一的用户名
$username = 'testuser' . time();

// 准备测试数据
$testData = [
    'username' => $username,
    'employeeName' => '测试员工',
    'companyId' => 1,
    'deptId' => 1,
    'position' => '测试职位',
    'storeId' => 1,
    'superiorId' => 0,
    'roles' => [1],
    'storeIds' => [1, 2],
    'phone' => '13800138000',
    'email' => 'test@example.com',
    'birthdaySolar' => '1990-01-01',
    'birthdayLunar' => '庚午年冬月十五',
    'idCard' => '110101199001011234',
    'address' => '北京市朝阳区',
    'emergencyContact' => '紧急联系人',
    'emergencyPhone' => '13900139000',
    'entryDate' => '2024-01-01',
    'leaveDate' => null
];

// 发送请求
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'http://localhost:8787/api/enterprise/addEmployee');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json'
]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo '测试结果（HTTP 状态码：' . $httpCode . '）：' . PHP_EOL;
echo $response . PHP_EOL;

// 解析结果
$result = json_decode($response, true);
if ($result && $result['code'] == 200) {
    echo '员工新增功能测试成功！' . PHP_EOL;
    
    // 检查数据库记录
    $employeeId = $result['data']['id'] ?? null;
    if ($employeeId) {
        echo '创建的员工ID：' . $employeeId . PHP_EOL;
    }
    
    // 检查用户记录
    $userId = $result['data']['user']['id'] ?? null;
    if ($userId) {
        echo '创建的用户ID：' . $userId . PHP_EOL;
    }
    
    // 检查用户详细信息记录
    $userProfile = $result['data']['user']['profile'] ?? null;
    if ($userProfile) {
        echo '用户详细信息创建成功！' . PHP_EOL;
    } else {
        echo '用户详细信息创建失败！' . PHP_EOL;
    }
    
} else {
    echo '员工新增功能测试失败：' . ($result['message'] ?? '未知错误') . PHP_EOL;
}

echo '测试完成！' . PHP_EOL;
?>