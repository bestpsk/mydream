<?php

namespace app\controller;

use app\model\Role;
use app\model\Menu;
use app\model\Permission;
use support\Request;

class RoleMenuController
{
    // 角色管理
    public function getRoles(Request $request)
    {
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
        
        $query = Role::where('status', 1);
        
        // 非超级管理员只能看到自己公司的角色
        if (!$isSuper && $currentCompanyId) {
            $query->where('company_id', $currentCompanyId);
        }
        
        $roles = $query->with('menus', 'permissions')->get();
        return json(['code' => 200, 'message' => '获取角色列表成功', 'data' => $roles]);
    }
    
    public function addRole(Request $request)
    {
        $data = $request->post();
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
        
        // 非超级管理员只能为自己公司添加角色
        if (!$isSuper && $currentCompanyId) {
            $data['company_id'] = $currentCompanyId;
        }
        
        // 检查是否尝试设置超级管理员权限
        if (isset($data['isSuper']) && $data['isSuper'] == 1) {
            // 获取当前用户ID
            $userId = $GLOBALS['user_id'] ?? null;
            if (!$userId) {
                return json(['code' => 401, 'message' => '未登录或登录已过期']);
            }
            
            // 查询用户角色信息
            $user = \app\model\User::with('roles')->find($userId);
            if (!$user) {
                return json(['code' => 404, 'message' => '用户不存在']);
            }
            
            // 检查用户是否为超级管理员
            $isSuperAdmin = false;
            foreach ($user->roles as $role) {
                if ($role->is_super == 1) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            if (!$isSuperAdmin) {
                return json(['code' => 403, 'message' => '权限不足，只有超级管理员才能设置超级管理员角色']);
            }
        }
        
        $role = Role::create($data);
        return json(['code' => 200, 'message' => '添加角色成功', 'data' => $role]);
    }
    
    public function updateRole(Request $request, $id)
    {
        $data = $request->post();
        $role = Role::find($id);
        if (!$role) {
            return json(['code' => 404, 'message' => '角色不存在']);
        }
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
        
        // 非超级管理员只能更新自己公司的角色
        if (!$isSuper && $currentCompanyId && $role->company_id != $currentCompanyId) {
            return json(['code' => 403, 'message' => '权限不足，只能更新自己公司的角色']);
        }
        
        // 非超级管理员只能为自己公司更新角色
        if (!$isSuper && $currentCompanyId) {
            $data['company_id'] = $currentCompanyId;
        }
        
        // 检查是否尝试设置超级管理员权限
        if (isset($data['isSuper']) && $data['isSuper'] == 1) {
            // 获取当前用户ID
            $userId = $GLOBALS['user_id'] ?? null;
            if (!$userId) {
                return json(['code' => 401, 'message' => '未登录或登录已过期']);
            }
            
            // 查询用户角色信息
            $user = \app\model\User::with('roles')->find($userId);
            if (!$user) {
                return json(['code' => 404, 'message' => '用户不存在']);
            }
            
            // 检查用户是否为超级管理员
            $isSuperAdmin = false;
            foreach ($user->roles as $role) {
                if ($role->is_super == 1) {
                    $isSuperAdmin = true;
                    break;
                }
            }
            
            if (!$isSuperAdmin) {
                return json(['code' => 403, 'message' => '权限不足，只有超级管理员才能设置超级管理员角色']);
            }
        }
        
        $role->update($data);
        return json(['code' => 200, 'message' => '更新角色成功', 'data' => $role]);
    }
    
    public function deleteRole(Request $request, $id)
    {
        $role = Role::find($id);
        if (!$role) {
            return json(['code' => 404, 'message' => '角色不存在']);
        }
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
        
        // 非超级管理员只能删除自己公司的角色
        if (!$isSuper && $currentCompanyId && $role->company_id != $currentCompanyId) {
            return json(['code' => 403, 'message' => '权限不足，只能删除自己公司的角色']);
        }
        
        $role->update(['status' => 0]);
        return json(['code' => 200, 'message' => '删除角色成功']);
    }
    
    public function getRolePermissions(Request $request, $id)
    {
        $role = Role::with('menus', 'permissions')->find($id);
        if (!$role) {
            return json(['code' => 404, 'message' => '角色不存在']);
        }
        
        $menuIds = $role->menus->pluck('id')->toArray();
        // 改为返回权限代码而不是权限ID
        $permissionCodes = $role->permissions->pluck('code')->toArray();
        
        return json([
            'code' => 200,
            'message' => '获取角色权限成功',
            'data' => [
                'menu_ids' => $menuIds,
                'permission_ids' => $permissionCodes
            ]
        ]);
    }
    
    public function updateRolePermissions(Request $request, $id)
    {
        try {
            $data = $request->post();
            $menuIds = $data['menu_ids'] ?? [];
            $permissionCodes = $data['permission_ids'] ?? [];
            
            $role = Role::find($id);
            if (!$role) {
                return json(['code' => 404, 'message' => '角色不存在']);
            }
            
            // 处理菜单权限
            $role->menus()->sync($menuIds);
            
            // 处理按钮权限
            if (!empty($permissionCodes)) {
                // 转换权限代码为权限ID
                $permissionIds = [];
                foreach ($permissionCodes as $code) {
                    try {
                        // 查找或创建对应的权限记录
                        // 截断name字段，确保不超过长度限制
                        $name = substr($code, 0, 50);
                        $description = substr($code, 0, 255);
                        
                        $permission = Permission::firstOrCreate(
                            ['code' => $code],
                            ['name' => $name, 'description' => $description]
                        );
                        $permissionIds[] = $permission->id;
                    } catch (\Exception $e) {
                        // 记录错误但继续处理其他权限
                        error_log('Error creating permission: ' . $e->getMessage());
                    }
                }
                
                // 同步权限
                $role->permissions()->sync($permissionIds);
            } else {
                // 如果没有权限代码，清空权限
                $role->permissions()->sync([]);
            }
            
            return json(['code' => 200, 'message' => '更新角色权限成功']);
        } catch (\Exception $e) {
            // 记录错误
            error_log('Error updating role permissions: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新角色权限失败: ' . $e->getMessage()]);
        }
    }
    
    // 菜单管理
    public function getMenus(Request $request)
    {
        // 获取所有菜单，包括状态为0的，除非被软删除才隐藏
        // 注意：如果 Menu 模型使用软删除，应该使用 withTrashed() 方法
        $menus = Menu::get();
        $menuTree = $this->buildMenuTree($menus);
        return json(['code' => 200, 'message' => '获取菜单列表成功', 'data' => $menuTree]);
    }
    
    public function addMenu(Request $request)
    {
        $data = $request->post();
        $menu = Menu::create($data);
        return json(['code' => 200, 'message' => '添加菜单成功', 'data' => $menu]);
    }
    
    public function updateMenu(Request $request, $id)
    {
        // 处理PUT请求的数据
        $data = $request->post();
        // 如果是PUT请求且没有数据，尝试从原始输入中获取
        if (empty($data) && $request->method() === 'PUT') {
            $rawData = $request->rawBody();
            if ($rawData) {
                $data = json_decode($rawData, true);
            }
        }
        
        $menu = Menu::find($id);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        if (empty($data)) {
            return json(['code' => 400, 'message' => '没有要更新的数据']);
        }
        
        $menu->update($data);
        return json(['code' => 200, 'message' => '更新菜单成功', 'data' => $menu]);
    }
    
    public function deleteMenu(Request $request, $id)
    {
        $menu = Menu::find($id);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        // 检查是否有子菜单
        $children = Menu::where('parent_id', $id)->count();
        if ($children > 0) {
            return json(['code' => 400, 'message' => '该菜单下有子菜单，无法删除']);
        }
        
        // 软删除菜单
        $menu->update(['status' => 0]);
        return json(['code' => 200, 'message' => '删除菜单成功']);
    }
    
    // 菜单权限管理
    public function getMenuPermissions(Request $request, $menuId)
    {
        $menu = Menu::find($menuId);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        $permissions = Permission::where('menu_id', $menuId)->where('status', 1)->get();
        return json(['code' => 200, 'message' => '获取菜单权限成功', 'data' => $permissions]);
    }
    
    public function addMenuPermission(Request $request, $menuId)
    {
        $menu = Menu::find($menuId);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        $data = $request->post();
        $data['menu_id'] = $menuId;
        
        // 验证必填字段
        if (!$data['name'] || !$data['code']) {
            return json(['code' => 400, 'message' => '权限名称和权限代码不能为空']);
        }
        
        // 检查权限代码是否已存在
        $existingPermission = Permission::where('code', $data['code'])->first();
        if ($existingPermission) {
            return json(['code' => 400, 'message' => '权限代码已存在']);
        }
        
        try {
            $permission = Permission::create($data);
            return json(['code' => 200, 'message' => '添加菜单权限成功', 'data' => $permission]);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '添加菜单权限失败: ' . $e->getMessage()]);
        }
    }
    
    public function updateMenuPermission(Request $request, $menuId, $permissionId)
    {
        $menu = Menu::find($menuId);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        $permission = Permission::find($permissionId);
        if (!$permission) {
            return json(['code' => 404, 'message' => '权限不存在']);
        }
        
        // 确保权限属于该菜单
        if ($permission->menu_id != $menuId) {
            return json(['code' => 400, 'message' => '权限不属于该菜单']);
        }
        
        $data = $request->post();
        
        try {
            // 检查权限代码是否已存在（排除当前权限）
            if (isset($data['code']) && $data['code'] != $permission->code) {
                $existingPermission = Permission::where('code', $data['code'])->where('id', '!=', $permissionId)->first();
                if ($existingPermission) {
                    return json(['code' => 400, 'message' => '权限代码已存在']);
                }
            }
            
            $permission->update($data);
            return json(['code' => 200, 'message' => '更新菜单权限成功', 'data' => $permission]);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '更新菜单权限失败: ' . $e->getMessage()]);
        }
    }
    
    public function deleteMenuPermission(Request $request, $menuId, $permissionId)
    {
        $menu = Menu::find($menuId);
        if (!$menu) {
            return json(['code' => 404, 'message' => '菜单不存在']);
        }
        
        $permission = Permission::find($permissionId);
        if (!$permission) {
            return json(['code' => 404, 'message' => '权限不存在']);
        }
        
        // 确保权限属于该菜单
        if ($permission->menu_id != $menuId) {
            return json(['code' => 400, 'message' => '权限不属于该菜单']);
        }
        
        try {
            // 软删除权限
            $permission->update(['status' => 0]);
            return json(['code' => 200, 'message' => '删除菜单权限成功']);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '删除菜单权限失败: ' . $e->getMessage()]);
        }
    }
    
    private function buildMenuTree($menus, $parentId = 0)
    {
        $tree = [];
        
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildMenuTree($menus, $menu->id);
                // 确保每个节点都有children属性，即使是空数组
                $menu->children = $children;
                $tree[] = $menu;
            }
        }
        
        return $tree;
    }
}
