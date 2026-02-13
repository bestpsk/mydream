<?php

namespace app\middleware;

use support\Request;
use app\model\User;

class PermissionMiddleware
{
    private $requiredPermission;
    
    public function __construct($permission = null)
    {
        $this->requiredPermission = $permission;
    }
    
    public function process(Request $request, callable $next)
    {
        $userId = $GLOBALS['user_id'] ?? null;
        
        if (!$userId) {
            return json(['code' => 401, 'message' => '未认证']);
        }
        
        $user = User::with('roles.permissions')->find($userId);
        if (!$user) {
            return json(['code' => 404, 'message' => '用户不存在']);
        }
        
        // 检查是否为超级管理员
        $isSuper = false;
        foreach ($user->roles as $role) {
            if ($role->is_super == 1) {
                $isSuper = true;
                break;
            }
        }
        
        if ($isSuper) {
            return $next($request);
        }
        
        // 检查是否有权限
        if ($this->requiredPermission) {
            $hasPermission = false;
            $userPermissions = $user->roles->flatMap(function ($role) {
                return $role->permissions->pluck('code')->toArray();
            })->unique()->toArray();
            
            if (in_array($this->requiredPermission, $userPermissions)) {
                $hasPermission = true;
            }
            
            if (!$hasPermission) {
                return json(['code' => 403, 'message' => '无权限执行此操作']);
            }
        }
        
        return $next($request);
    }
}
