<?php
require 'vendor/autoload.php';

use app\controller\EnterpriseController;

// 模拟请求数据
$testData = [
    'username' => 'testuser' . time(),
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

// 创建模拟请求对象
class MockRequest
{
    private $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function post($key = null, $default = null)
    {
        if ($key === null) {
            return $this->data;
        }
        return $this->data[$key] ?? $default;
    }
}

// 测试 addEmployee 方法
try {
    echo '开始测试员工新增功能...' . PHP_EOL;
    
    $request = new MockRequest($testData);
    $controller = new EnterpriseController();
    
    // 直接调用 addEmployee 方法
    ob_start();
    $response = $controller->addEmployee($request);
    $output = ob_get_clean();
    
    echo '测试结果：' . $output . PHP_EOL;
    
    $result = json_decode($output, true);
    if ($result['code'] == 200) {
        echo '员工新增功能测试成功！' . PHP_EOL;
        
        // 检查数据库记录
        $employeeId = $result['data']['id'];
        echo '创建的员工ID：' . $employeeId . PHP_EOL;
        
        // 检查用户记录
        $userId = $result['data']['user']['id'];
        echo '创建的用户ID：' . $userId . PHP_EOL;
        
        // 检查用户详细信息记录
        $userProfile = $result['data']['user']['profile'];
        if ($userProfile) {
            echo '用户详细信息创建成功！' . PHP_EOL;
        } else {
            echo '用户详细信息创建失败！' . PHP_EOL;
        }
        
    } else {
        echo '员工新增功能测试失败：' . $result['message'] . PHP_EOL;
    }
    
} catch (\Exception $e) {
    echo '测试过程中发生错误：' . $e->getMessage() . PHP_EOL;
}

echo '测试完成！' . PHP_EOL;
?>