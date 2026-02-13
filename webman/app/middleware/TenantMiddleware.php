<?php

namespace app\middleware;

use support\Request;
use app\model\User;
use app\model\Employee;

class TenantMiddleware
{
    public function process(Request $request, callable $next)
    {
        try {
            // 从GLOBALS中获取用户信息
            if (!isset($GLOBALS['user_id'])) {
                return json([
                    'code' => 401,
                    'message' => '未授权，请重新登录'
                ]);
            }
            
            $userId = $GLOBALS['user_id'];
            $user = User::find($userId);
            
            if (!$user) {
                return json([
                    'code' => 404,
                    'message' => '用户不存在'
                ]);
            }
            
            // 检查用户是否为超级管理员
            $isSuper = false;
            $roles = $user->roles;
            foreach ($roles as $role) {
                if (isset($role->is_super) && $role->is_super == 1) {
                    $isSuper = true;
                    break;
                }
            }
            
            // 设置是否超级管理员
            $GLOBALS['is_super'] = $isSuper;
            
            // 对于非超级管理员，获取公司ID
            if (!$isSuper) {
                $employee = $user->employee;
                if ($employee && $employee->company_id) {
                    $GLOBALS['company_id'] = $employee->company_id;
                } else {
                    return json([
                        'code' => 403,
                        'message' => '用户未关联公司，请联系管理员'
                    ]);
                }
            }
            
            return $next($request);
        } catch (\Exception $e) {
            return json([
                'code' => 500,
                'message' => '服务器内部错误: ' . $e->getMessage()
            ]);
        }
    }
}
