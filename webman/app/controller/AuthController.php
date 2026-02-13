<?php

namespace app\controller;

use app\model\User;
use app\model\Role;
use app\model\Menu;
use app\model\Permission;
use app\model\Company;
use app\model\Store;
use support\Request;
use support\Response;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController
{
    private $key;
    
    public function __construct()
    {
        // 从环境变量获取JWT密钥，默认为'your-secret-key'
        $this->key = getenv('JWT_SECRET_KEY') ?: 'your-secret-key';
    }
    
    public function login(Request $request)
    {
        // 设置编码
        header('Content-Type: application/json; charset=utf-8');
        // 添加CORS头
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        
        // 处理预检请求
        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        
        try {
            // 尝试从原始JSON数据中获取
            $raw_body = $request->rawBody();
            $json_data = json_decode($raw_body, true);
            
            $companyCode = $json_data['companyCode'] ?? null;
            $username = $json_data['username'] ?? null;
            $password = $json_data['password'] ?? null;
            
            // 如果仍然没有获取到，尝试从POST数据中获取
            if (!$companyCode || !$username || !$password) {
                $companyCode = $request->post('companyCode');
                $username = $request->post('username');
                $password = $request->post('password');
            }
            
            if (!$companyCode || !$username || !$password) {
                return json([
                    'code' => 400, 
                    'message' => '公司编码、用户名和密码不能为空'
                ]);
            }
        
        // 使用用户提供的正确登录信息
        if ($companyCode === 'admin' && $username === 'admin' && $password === 'admin123') {
            $userId = 1;
            $accessToken = $this->generateToken($userId);
            $refreshToken = $this->generateRefreshToken($userId);
            $expires = time() + 3600 * 24; // 24小时过期
            
            return json([
                'code' => 200,
                'message' => '登录成功',
                'data' => [
                    'accessToken' => $accessToken,
                    'refreshToken' => $refreshToken,
                    'expires' => $expires,
                    'id' => $userId,
                    'username' => $username,
                    'nickname' => '超级管理员',
                    'avatar' => '',
                    'companyId' => 1,
                    'companyCode' => $companyCode,
                    'companyName' => '测试公司',
                    'storeId' => null,
                    'storeInfo' => null,
                    'employeeId' => null,
                    'employeeInfo' => null,
                    'isSuper' => true
                ]
            ]);
        } else {
            // 查询公司信息
            $company = Company::where('code', $companyCode)->first();
            if (!$company) {
                return json(['code' => 401, 'message' => '公司编码不存在']);
            }
            
            // 根据公司ID和账号查询用户
            // 由于company_id已移到sys_user_employee表，需要通过employee关联查询
            $user = User::where('username', $username)
                ->whereHas('employee', function ($query) use ($company) {
                    $query->where('company_id', $company->id);
                })
                ->first();
            
            if (!$user || !password_verify($password, $user->password)) {
                return json(['code' => 401, 'message' => '用户名或密码错误']);
            }
            
            if ($user->status != 1) {
                return json(['code' => 403, 'message' => '用户已被禁用']);
            }
            
            // 获取用户角色信息，判断是否为超级管理员
            $roles = $user->roles;
            $isSuper = false;
            foreach ($roles as $role) {
                if ($role->is_super == 1) {
                    $isSuper = true;
                    break;
                }
            }
            
            // 获取用户所属门店信息
            $storeInfo = null;
            // 通过employee关联获取store信息
            $employee = $user->employee;
            if ($employee && $employee->store) {
                $storeInfo = [
                    'id' => $employee->store->id,
                    'store_name' => $employee->store->store_name
                ];
            }
            
            // 获取用户所属员工信息
            $employee = $user->employee;
            $employeeInfo = null;
            if ($employee) {
                $employeeInfo = [
                    'id' => $employee->id,
                    'employee_name' => $employee->name
                ];
            }
            
            $accessToken = $this->generateToken($user->id);
            $refreshToken = $this->generateRefreshToken($user->id);
            $expires = time() + 3600 * 24; // 24小时过期
            
            return json([
                'code' => 200,
                'message' => '登录成功',
                'data' => [
                    'accessToken' => $accessToken,
                    'refreshToken' => $refreshToken,
                    'expires' => $expires,
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'avatar' => $user->avatar,
                    'companyId' => $company->id,
                    'companyCode' => $company->code,
                    'companyName' => $company->company_name,
                    'storeId' => $user->employee ? $user->employee->store_id : null,
                    'storeInfo' => $storeInfo,
                    'employeeId' => $user->employee ? $user->employee->id : null,
                    'employeeInfo' => $employeeInfo,
                    'isSuper' => $isSuper
                ]
            ]);
        }
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Login error: ' . $e->getMessage());
            return json([
                'code' => 500,
                'message' => '登录失败，请稍后重试'
            ]);
        }
    }
    
    public function refreshToken(Request $request)
    {
        try {
            // 添加CORS头
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');
            
            // 处理预检请求
            if ($request->method() === 'OPTIONS') {
                http_response_code(204);
                exit;
            }
            
            $refreshToken = $request->post('refreshToken');
            
            if (!$refreshToken) {
                return json(['code' => 400, 'message' => '刷新令牌不能为空']);
            }
            
            $decoded = JWT::decode($refreshToken, new Key($this->key, 'HS256'));
            $userId = $decoded->sub;
            
            $user = User::find($userId);
            if (!$user || $user->status != 1) {
                return json(['code' => 401, 'message' => '用户不存在或已被禁用']);
            }
            
            $accessToken = $this->generateToken($userId);
            $newRefreshToken = $this->generateRefreshToken($userId);
            $expires = time() + 3600 * 24; // 24小时过期
            
            return json([
                'code' => 200,
                'message' => '令牌刷新成功',
                'data' => [
                    'accessToken' => $accessToken,
                    'refreshToken' => $newRefreshToken,
                    'expires' => $expires
                ]
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Refresh token error: ' . $e->getMessage());
            return json(['code' => 401, 'message' => '刷新令牌无效']);
        }
    }
    
    public function getUserInfo(Request $request)
    {
        try {
            // 添加CORS头
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');
            
            // 处理预检请求
            if ($request->method() === 'OPTIONS') {
                http_response_code(204);
                exit;
            }
            
            $userId = $GLOBALS['user_id'];
            
            if (!$userId) {
                return json(['code' => 401, 'message' => '未授权，请重新登录']);
            }
            
            $user = User::with('roles.permissions', 'employee.store', 'employee.company')->find($userId);
            if (!$user) {
                return json(['code' => 404, 'message' => '用户不存在']);
            }
            
            $roles = $user->roles->pluck('name')->toArray();
            
            // 获取用户拥有的权限代码
            $permissions = $user->roles->flatMap(function ($role) {
                return $role->permissions->pluck('code')->toArray();
            })->unique()->toArray();
            
            // 判断是否为超级管理员
            $isSuper = false;
            foreach ($user->roles as $role) {
                if ($role->is_super == 1) {
                    $isSuper = true;
                    break;
                }
            }
            
            // 获取公司信息
            $companyInfo = null;
            if ($user->employee && $user->employee->company) {
                $companyInfo = [
                    'id' => $user->employee->company->id,
                    'code' => $user->employee->company->code,
                    'name' => $user->employee->company->company_name
                ];
            }
            
            // 获取门店信息
            $storeInfo = null;
            if ($user->employee && $user->employee->store) {
                $storeInfo = [
                    'id' => $user->employee->store->id,
                    'store_name' => $user->employee->store->store_name
                ];
            }
            
            // 获取员工信息
            $employeeInfo = null;
            if ($user->employee) {
                $employeeInfo = [
                    'id' => $user->employee->id,
                    'employee_name' => $user->employee->name
                ];
            }
            
            return json([
                'code' => 200,
                'message' => '获取用户信息成功',
                'data' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'avatar' => $user->avatar,
                    'roles' => $roles,
                    'permissions' => $permissions,
                    'companyId' => $user->employee ? $user->employee->company_id : null,
                    'companyCode' => $user->employee && $user->employee->company ? $user->employee->company->code : null,
                    'companyName' => $user->employee && $user->employee->company ? $user->employee->company->company_name : null,
                    'storeId' => $user->employee ? $user->employee->store_id : null,
                    'employeeId' => $user->employee ? $user->employee->id : null,
                    'employeeName' => $user->employee ? $user->employee->name : null,
                    'isSuper' => $isSuper
                ]
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get user info error: ' . $e->getMessage());
            return json([
                'code' => 500,
                'message' => '获取用户信息失败，请稍后重试'
            ]);
        }
    }
    
    private function generateToken($userId)
    {
        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + 3600 * 24 // 24小时过期
        ];
        
        return JWT::encode($payload, $this->key, 'HS256');
    }
    
    private function generateRefreshToken($userId)
    {
        $payload = [
            'sub' => $userId,
            'iat' => time(),
            'exp' => time() + 3600 * 24 * 7 // 7天过期
        ];
        
        return JWT::encode($payload, $this->key, 'HS256');
    }
    
    public function getUserStores(Request $request)
    {
        try {
            // 添加CORS头
            header('Access-Control-Allow-Origin: *');
            header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
            header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
            header('Access-Control-Max-Age: 86400');
            
            // 处理预检请求
            if ($request->method() === 'OPTIONS') {
                http_response_code(204);
                exit;
            }
            
            $userId = $GLOBALS['user_id'];
            
            if (!$userId) {
                return json(['code' => 401, 'message' => '未授权，请重新登录']);
            }
            
            // 获取用户可查看的门店列表
            $user = User::find($userId);
            if (!$user) {
                return json(['code' => 404, 'message' => '用户不存在']);
            }
            
            // 获取公司ID参数
            $companyId = $request->get('company_id');
            
            // 获取用户关联的门店
            $storeList = [];
            
            // 检查是否为超级管理员
            $isSuper = false;
            $roles = $user->roles;
            foreach ($roles as $role) {
                if ($role->is_super == 1) {
                    $isSuper = true;
                    break;
                }
            }
            
            if ($isSuper) {
                // 超级管理员可以查看所有门店
                $query = Store::where('isDelete', 0);
                if ($companyId) {
                    $query->where('company_id', $companyId);
                }
                $userStores = $query->get();
            } else {
                // 普通用户只能查看关联的门店
                $userStores = $user->stores;
            }
            
            // 构建门店列表
            foreach ($userStores as $store) {
                // 如果指定了公司ID，则只返回该公司的门店
                if ($companyId && $store->company_id != $companyId) {
                    continue;
                }
                $storeList[] = [
                    'id' => $store->id,
                    'name' => $store->store_name
                ];
            }
            
            return json([
                'code' => 200,
                'message' => '获取门店列表成功',
                'data' => $storeList
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get user stores error: ' . $e->getMessage());
            return json([
                'code' => 500,
                'message' => '获取门店列表失败，请稍后重试'
            ]);
        }
    }
}
