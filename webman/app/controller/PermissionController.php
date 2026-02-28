<?php

namespace app\controller;

use app\model\User;
use app\model\Menu;
use app\model\Permission;
use app\model\Role;
use support\Request;
use support\Response;

class PermissionController
{
    public function getRoutes(Request $request)
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
        header('Access-Control-Max-Age: 86400');
        
        if ($request->method() === 'OPTIONS') {
            http_response_code(204);
            exit;
        }
        
        $userId = $GLOBALS['user_id'];
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        
        $user = User::with('roles.menus')->find($userId);
        if (!$user) {
            return json(['code' => 404, 'message' => '用户不存在']);
        }
        
        $menuIds = $user->roles->flatMap(function ($role) {
            return $role->menus->pluck('id')->toArray();
        })->unique()->toArray();
        
        $query = Menu::whereIn('id', $menuIds)->where('status', 1)->where('is_delete', 0);
        
        if (!$isSuper) {
            $query->where('is_tenant_visible', 1);
        }
        
        $menus = $query->get();
        
        $menus = $this->filterMenusWithDisabledParent($menus);
        
        $menuTree = $this->buildMenuTree($menus);
        
        $routes = $this->convertToRoutes($menuTree);
        
        return json([
            'code' => 200,
            'message' => '获取路由成功',
            'data' => $routes
        ]);
    }
    
    public function getPermissions(Request $request)
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
        
        $userId = $GLOBALS['user_id'];
        
        $user = User::with('roles.permissions')->find($userId);
        if (!$user) {
            return json(['code' => 404, 'message' => '用户不存在']);
        }
        
        // 判断是否为超级管理员
        $isSuper = false;
        foreach ($user->roles as $role) {
            if ($role->is_super == 1) {
                $isSuper = true;
                break;
            }
        }
        
        // 获取用户拥有的权限代码
        $permissionCodes = [];
        if ($isSuper) {
            // 超级管理员拥有所有权限，返回特殊标识
            $permissionCodes = ["*:*:*"];
        } else {
            $permissionCodes = $user->roles->flatMap(function ($role) {
                return $role->permissions->pluck('code')->toArray();
            })->unique()->toArray();
        }
        
        return json([
            'code' => 200,
            'message' => '获取权限成功',
            'data' => $permissionCodes
        ]);
    }
    
    public function getMenus(Request $request)
    {
        // 获取所有菜单
        $menus = Menu::where('status', 1)->get();
        
        // 构建菜单树
        $menuTree = $this->buildMenuTree($menus);
        
        return json([
            'code' => 200,
            'message' => '获取菜单树成功',
            'data' => $menuTree
        ]);
    }
    
    public function getPermissionList(Request $request)
    {
        $menuId = $request->get('menu_id');
        
        $query = Permission::query();
        if ($menuId) {
            $query->where('menu_id', $menuId);
        }
        
        $permissions = $query->get();
        
        return json([
            'code' => 200,
            'message' => '获取权限列表成功',
            'data' => $permissions
        ]);
    }
    
    public function assignPermissions(Request $request)
    {
        $roleId = $request->post('role_id');
        $menuIds = $request->post('menu_ids', []);
        $permissionIds = $request->post('permission_ids', []);
        
        if (!$roleId) {
            return json(['code' => 400, 'message' => '角色ID不能为空']);
        }
        
        $role = Role::find($roleId);
        if (!$role) {
            return json(['code' => 404, 'message' => '角色不存在']);
        }
        
        // 同步菜单权限
        $role->menus()->sync($menuIds);
        
        // 同步按钮权限
        $role->permissions()->sync($permissionIds);
        
        return json([
            'code' => 200,
            'message' => '权限分配成功'
        ]);
    }
    
    public function getRolePermissions(Request $request)
    {
        $roleId = $request->get('role_id');
        
        if (!$roleId) {
            return json(['code' => 400, 'message' => '角色ID不能为空']);
        }
        
        $role = Role::with('menus', 'permissions')->find($roleId);
        if (!$role) {
            return json(['code' => 404, 'message' => '角色不存在']);
        }
        
        $menuIds = $role->menus->pluck('id')->toArray();
        $permissionIds = $role->permissions->pluck('id')->toArray();
        
        return json([
            'code' => 200,
            'message' => '获取角色权限成功',
            'data' => [
                'menu_ids' => $menuIds,
                'permission_ids' => $permissionIds
            ]
        ]);
    }
    
    public function getMenuPermissions(Request $request)
    {
        $menuId = $request->get('menu_id');
        
        if (!$menuId) {
            return json(['code' => 400, 'message' => '菜单ID不能为空']);
        }
        
        $permissions = Permission::where('menu_id', $menuId)->where('status', 1)->get();
        
        return json([
            'code' => 200,
            'message' => '获取菜单权限成功',
            'data' => $permissions
        ]);
    }
    
    public function getAllMenuPermissions(Request $request)
    {
        // 获取所有菜单的权限
        $permissions = Permission::where('status', 1)->get();
        
        return json([
            'code' => 200,
            'message' => '获取所有菜单权限成功',
            'data' => $permissions
        ]);
    }
    
    public function addPermission(Request $request)
    {
        $data = $request->post();
        
        // 验证必填字段
        if (!$data['name'] || !$data['code'] || !$data['menu_id']) {
            return json(['code' => 400, 'message' => '权限名称、权限代码和菜单ID不能为空']);
        }
        
        try {
            // 检查权限代码是否已存在
            $existingPermission = Permission::where('code', $data['code'])->first();
            if ($existingPermission) {
                return json(['code' => 400, 'message' => '权限代码已存在']);
            }
            
            // 创建权限
            $permission = Permission::create($data);
            
            return json([
                'code' => 200,
                'message' => '添加权限成功',
                'data' => $permission
            ]);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '添加权限失败: ' . $e->getMessage()]);
        }
    }
    
    public function updatePermission(Request $request, $id)
    {
        $data = $request->post();
        
        try {
            $permission = Permission::find($id);
            if (!$permission) {
                return json(['code' => 404, 'message' => '权限不存在']);
            }
            
            // 检查权限代码是否已存在（排除当前权限）
            if (isset($data['code']) && $data['code'] != $permission->code) {
                $existingPermission = Permission::where('code', $data['code'])->where('id', '!=', $id)->first();
                if ($existingPermission) {
                    return json(['code' => 400, 'message' => '权限代码已存在']);
                }
            }
            
            // 更新权限
            $permission->update($data);
            
            return json([
                'code' => 200,
                'message' => '更新权限成功',
                'data' => $permission
            ]);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '更新权限失败: ' . $e->getMessage()]);
        }
    }
    
    public function deletePermission(Request $request, $id)
    {
        try {
            $permission = Permission::find($id);
            if (!$permission) {
                return json(['code' => 404, 'message' => '权限不存在']);
            }
            
            // 删除权限（软删除）
            $permission->update(['status' => 0]);
            
            return json([
                'code' => 200,
                'message' => '删除权限成功'
            ]);
        } catch (Exception $e) {
            return json(['code' => 500, 'message' => '删除权限失败: ' . $e->getMessage()]);
        }
    }
    
    private function buildMenuTree($menus, $parentId = 0)
    {
        $tree = [];
        $menuIds = $menus->pluck('id')->toArray();
        
        foreach ($menus as $menu) {
            if ($menu->parent_id == $parentId) {
                $children = $this->buildMenuTree($menus, $menu->id);
                if ($children) {
                    $menu->children = $children;
                }
                $tree[] = $menu;
            }
        }
        
        return $tree;
    }
    
    /**
     * 过滤掉父菜单被禁用的子菜单
     * 递归检查每个菜单的父级链，确保所有父级都是启用状态
     */
    private function filterMenusWithDisabledParent($menus)
    {
        $validMenuIds = [];
        $menusById = [];
        
        foreach ($menus as $menu) {
            $menusById[$menu->id] = $menu;
        }
        
        foreach ($menus as $menu) {
            $parentId = $menu->parent_id;
            $allParentsEnabled = true;
            
            while ($parentId > 0) {
                if (!isset($menusById[$parentId])) {
                    $allParentsEnabled = false;
                    break;
                }
                $parentId = $menusById[$parentId]->parent_id;
            }
            
            if ($allParentsEnabled) {
                $validMenuIds[] = $menu->id;
            }
        }
        
        return $menus->filter(function ($menu) use ($validMenuIds) {
            return in_array($menu->id, $validMenuIds);
        });
    }
    
    private function convertToRoutes($menuTree)
    {
        $routes = [];
        
        foreach ($menuTree as $menu) {
            // 构建路由名称：将路径转换为驼峰命名（如 /welcome 转换为 Welcome）
            $routeName = $this->convertPathToName($menu->path);
            
            // 构建组件路径：确保与前端组件路径一致
            $component = $menu->component;
            if (empty($component) && !empty($menu->path)) {
                // 如果没有指定组件路径，根据路径生成
                $component = $this->convertPathToComponent($menu->path);
            }
            
            $route = [
                'path' => $menu->path,
                'name' => $routeName,
                'component' => $component,
                'redirect' => $menu->redirect,
                'meta' => [
                    'title' => $menu->name,
                    'icon' => $menu->icon,
                    'showLink' => $menu->show_link == 1,
                    'isFrame' => $menu->is_frame == 1,
                    'frameSrc' => $menu->frame_src,
                    'showParent' => false // 默认值，可根据需要修改
                ]
            ];
            
            if (isset($menu->children) && $menu->children) {
                $childrenRoutes = $this->convertToRoutes($menu->children);
                if (!empty($childrenRoutes)) {
                    $route['children'] = $childrenRoutes;
                    
                    // 处理子菜单，当只有一个子菜单时，在子菜单的 meta 中添加 showParent: true
                    if (count($route['children']) == 1) {
                        $route['children'][0]['meta']['showParent'] = true;
                    }
                }
            }
            
            $routes[] = $route;
        }
        
        return $routes;
    }
    
    private function convertPathToComponent($path)
    {
        // 将路径转换为组件路径（如 /welcome 转换为 welcome/index.vue）
        $path = trim($path, '/');
        if (empty($path)) {
            return 'layout/index.vue';
        }
        
        // 处理特殊路径，确保与前端组件路径一致
        if (strpos($path, 'enterprise/') === 0 || $path === 'menu') {
            // 企业管理和菜单管理的组件位于 system 目录下
            $path = 'system/' . $path;
        }
        
        // 处理多级路径
        $parts = explode('/', $path);
        $componentPath = implode('/', $parts);
        
        // 添加文件扩展名
        return $componentPath . '/index.vue';
    }
    
    private function convertPathToName($path)
    {
        // 移除路径中的斜杠，将每个单词首字母大写，然后拼接
        $parts = explode('/', trim($path, '/'));
        $name = '';
        foreach ($parts as $part) {
            $name .= ucfirst($part);
        }
        return $name ?: 'Home';
    }
}
