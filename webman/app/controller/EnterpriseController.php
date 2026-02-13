<?php

namespace app\controller;

use app\model\Company;
use app\model\Store;
use app\model\Department;
use app\model\Position;
use app\model\Employee;
use app\model\User;
use app\model\Bedroom;
use support\Request;
use support\DB;

class EnterpriseController
{
    /**
     * 公司管理
     * 获取公司列表
     * @param Request $request 请求对象
     * @return array 公司列表数据
     */
    public function getCompanies(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = Company::where('isDelete', 0);
            
            // 普通用户只能看到自己所属的公司
            if (!$isSuper && $currentCompanyId) {
                $query->where('id', $currentCompanyId);
            }
            
            // 执行查询
            $companies = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCompanies = $companies->map(function($company) {
                return [
                    'id' => $company->id,
                    'companyName' => $company->company_name,
                    'code' => $company->code,
                    'boss' => $company->boss,
                    'phone' => $company->phone,
                    'address' => $company->address,
                    'companyType' => $company->enterprise_type,
                    'storeCount' => $company->store_count,
                    'servicePerson' => $company->service_people,
                    'createTime' => $company->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取公司列表成功', 'data' => $formattedCompanies]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get companies error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取公司列表失败，请稍后重试']);
        }
    }
    
    /**
     * 添加公司
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addCompany(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['companyName']) || empty($data['code'])) {
                return json(['code' => 400, 'message' => '公司名称和编码不能为空']);
            }
            
            // 检查公司编码是否已存在
            $existingCompany = Company::where('code', $data['code'])->first();
            if ($existingCompany) {
                return json(['code' => 400, 'message' => '公司编码已存在，请使用其他编码']);
            }
            
            // 转换字段名：camelCase 转 数据库实际字段名
            $dbData = [
                'company_name' => $data['companyName'] ?? '',
                'code' => $data['code'] ?? '',
                'boss' => $data['boss'] ?? '',
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'enterprise_type' => $data['companyType'] ?? '',
                'store_count' => $data['storeCount'] ?? 0,
                'service_people' => $data['servicePerson'] ?? 0,
                'status' => 1,
                'isDelete' => 0
            ];
            
            $company = Company::create($dbData);
            return json(['code' => 200, 'message' => '添加公司成功', 'data' => $company]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add company error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加公司失败，请稍后重试']);
        }
    }
    
    /**
     * 更新公司
     * @param Request $request 请求对象
     * @param int $id 公司ID
     * @return array 更新结果
     */
    public function updateCompany(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            $company = Company::find($id);
            if (!$company) {
                return json(['code' => 404, 'message' => '公司不存在']);
            }
            
            // 检查公司编码是否已被其他公司使用
            if (!empty($data['code']) && $data['code'] !== $company->code) {
                $existingCompany = Company::where('code', $data['code'])->where('id', '!=', $id)->first();
                if ($existingCompany) {
                    return json(['code' => 400, 'message' => '公司编码已存在，请使用其他编码']);
                }
            }
            
            // 直接设置属性并保存
            $company->company_name = $data['companyName'] ?? $company->company_name;
            $company->code = $data['code'] ?? $company->code;
            $company->boss = $data['boss'] ?? $company->boss;
            $company->phone = $data['phone'] ?? $company->phone;
            $company->address = $data['address'] ?? $company->address;
            $company->enterprise_type = $data['companyType'] ?? $company->enterprise_type;
            $company->store_count = $data['storeCount'] ?? $company->store_count;
            $company->service_people = $data['servicePerson'] ?? $company->service_people;
            
            $company->save();
            
            return json(['code' => 200, 'message' => '更新公司成功', 'data' => $company]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update company error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新公司失败，请稍后重试']);
        }
    }
    
    /**
     * 删除公司
     * @param Request $request 请求对象
     * @param int $id 公司ID
     * @return array 删除结果
     */
    public function deleteCompany(Request $request, $id)
    {
        try {
            $company = Company::find($id);
            if (!$company) {
                return json(['code' => 404, 'message' => '公司不存在']);
            }
            
            // 软删除公司
            $company->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除公司成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete company error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除公司失败，请稍后重试']);
        }
    }
    
    // 门店管理
    public function getStores(Request $request)
    {
        $companyId = $request->get('company_id');
        $storeName = $request->get('storeName');
        $storeType = $request->get('storeType');
        $query = Store::where('isDelete', 0);
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        if (!$isSuper && isset($GLOBALS['company_id'])) {
            // 非超级管理员只能看到自己公司的门店
            $query->where('company_id', $GLOBALS['company_id']);
        }
        
        if ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($storeName) {
            $query->where('store_name', 'like', '%' . $storeName . '%');
        }
        if ($storeType) {
            $query->where('store_type', $storeType);
        }
        $stores = $query->get();
        // 转换字段名：数据库字段名 转 camelCase
        $formattedStores = $stores->map(function($store) {
            // 获取门店的部门信息
            $departments = DB::table('sys_store_department')
                ->where('store_id', $store->id)
                ->pluck('department_id')
                ->toArray();
            $departmentIds = implode(',', $departments);
            
            return [
                'id' => $store->id,
                'storeName' => $store->store_name,
                'phone' => $store->phone,
                'address' => $store->address,
                'storeType' => $store->store_type,
                'departments' => $departmentIds,
                'companyId' => $store->company_id,
                'status' => $store->status,
                'createTime' => $store->created_at
            ];
        });
        return json(['code' => 200, 'message' => '获取门店列表成功', 'data' => $formattedStores]);
    }
    
    public function addStore(Request $request)
    {
        try {
            // 在webman框架中，使用post()方法获取POST请求的数据，它会自动处理JSON和表单编码的数据
            $data = $request->post();
            // 调试：输出接收到的数据
            error_log('addStore request data: ' . json_encode($data));
            
            // 转换字段名：camelCase 转 数据库实际字段名
            $dbData = [
                'store_name' => $data['storeName'] ?? '',
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'store_type' => $data['storeType'] ?? '',
                'company_id' => $data['companyId'] ?? 0,
                'status' => 1,
                'isDelete' => 0
            ];
            
            error_log('addStore db data: ' . json_encode($dbData));
            
            $store = Store::create($dbData);
            error_log('addStore created store: ' . json_encode($store));
            
            // 处理门店和部门的关系
            if (isset($data['departments'])) {
                error_log('addStore departments: ' . $data['departments']);
                $departments = explode(',', $data['departments']);
                foreach ($departments as $deptId) {
                    if (trim($deptId)) {
                        DB::table('sys_store_department')->insert([
                            'store_id' => $store->id,
                            'department_id' => trim($deptId)
                        ]);
                    }
                }
            }
            
            return json(['code' => 200, 'message' => '添加门店成功', 'data' => $store]);
        } catch (\Exception $e) {
            error_log('addStore error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加门店失败：' . $e->getMessage()]);
        }
    }
    
    public function updateStore(Request $request, $id)
    {
        // 在webman框架中，使用post()方法获取POST请求的数据，它会自动处理JSON和表单编码的数据
        $data = $request->post();
        // 转换字段名：camelCase 转 数据库实际字段名
        $dbData = [
            'store_name' => $data['storeName'] ?? '',
            'phone' => $data['phone'] ?? '',
            'address' => $data['address'] ?? '',
            'store_type' => $data['storeType'] ?? '',
            'company_id' => $data['companyId'] ?? 0
        ];
        $store = Store::find($id);
        if (!$store) {
            return json(['code' => 404, 'message' => '门店不存在']);
        }
        $store->update($dbData);
        
        // 处理门店和部门的关系
        if (isset($data['departments'])) {
            // 先删除现有的门店-部门关系
            DB::table('sys_store_department')->where('store_id', $id)->delete();
            
            // 添加新的门店-部门关系
            $departments = explode(',', $data['departments']);
            foreach ($departments as $deptId) {
                if (trim($deptId)) {
                    DB::table('sys_store_department')->insert([
                        'store_id' => $id,
                        'department_id' => trim($deptId)
                    ]);
                }
            }
        }
        
        return json(['code' => 200, 'message' => '更新门店成功', 'data' => $store]);
    }
    
    public function deleteStore(Request $request, $id)
    {
        $store = Store::find($id);
        if (!$store) {
            return json(['code' => 404, 'message' => '门店不存在']);
        }
        $store->update(['isDelete' => 1]);
        return json(['code' => 200, 'message' => '删除门店成功']);
    }
    
    // 部门管理
    public function getDepartments(Request $request)
    {
        $companyId = $request->get('company_id');
        $deptName = $request->get('deptName');
        $query = Department::where('isDelete', 0);
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        if (!$isSuper && isset($GLOBALS['company_id'])) {
            // 非超级管理员只能看到自己公司的部门
            $query->where('company_id', $GLOBALS['company_id']);
        } elseif ($companyId) {
            $query->where('company_id', $companyId);
        }
        if ($deptName) {
            $query->where('dept_name', 'like', '%' . $deptName . '%');
        }
        $departments = $query->orderBy('sort', 'asc')->get();
        // 转换字段名：数据库字段名 转 camelCase
        $formattedDepartments = $departments->map(function($department) {
            return [
                'id' => $department->id,
                'deptName' => $department->dept_name,
                'parentId' => $department->parent_id,
                'sort' => $department->sort,
                'companyId' => $department->company_id,
                'status' => $department->status,
                'enable_category' => $department->enable_category,
                'createTime' => $department->created_at
            ];
        });
        return json(['code' => 200, 'message' => '获取部门列表成功', 'data' => $formattedDepartments]);
    }
    
    public function addDepartment(Request $request)
    {
        $data = $request->post();
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'dept_name' => $data['deptName'] ?? '',
            'parent_id' => $data['parentId'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'company_id' => $data['companyId'] ?? 0,
            'status' => $data['status'] ?? 1,
            'enable_category' => $data['enable_category'] ?? 0,
            'isDelete' => 0
        ];
        $department = Department::create($snakeData);
        // 转换字段名：数据库字段名 转 camelCase
        $formattedDepartment = [
            'id' => $department->id,
            'deptName' => $department->dept_name,
            'parentId' => $department->parent_id,
            'sort' => $department->sort,
            'companyId' => $department->company_id,
            'status' => $department->status,
            'enable_category' => $department->enable_category,
            'createTime' => $department->created_at
        ];
        return json(['code' => 200, 'message' => '添加部门成功', 'data' => $formattedDepartment]);
    }
    
    public function updateDepartment(Request $request, $id)
    {
        $data = $request->post();
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'dept_name' => $data['deptName'] ?? '',
            'parent_id' => $data['parentId'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'company_id' => $data['companyId'] ?? 0,
            'status' => $data['status'] ?? 1,
            'enable_category' => $data['enable_category'] ?? 0
        ];
        $department = Department::find($id);
        if (!$department) {
            return json(['code' => 404, 'message' => '部门不存在']);
        }
        $department->update($snakeData);
        // 转换字段名：数据库字段名 转 camelCase
        $formattedDepartment = [
            'id' => $department->id,
            'deptName' => $department->dept_name,
            'parentId' => $department->parent_id,
            'sort' => $department->sort,
            'companyId' => $department->company_id,
            'status' => $department->status,
            'enable_category' => $department->enable_category,
            'createTime' => $department->created_at
        ];
        return json(['code' => 200, 'message' => '更新部门成功', 'data' => $formattedDepartment]);
    }
    
    public function deleteDepartment(Request $request, $id)
    {
        $department = Department::find($id);
        if (!$department) {
            return json(['code' => 404, 'message' => '部门不存在']);
        }
        $department->update(['isDelete' => 1]);
        return json(['code' => 200, 'message' => '删除部门成功']);
    }
    
    // 职位管理
    public function getPositions(Request $request)
    {
        try {
            $companyId = $request->get('company_id');
            $positionName = $request->get('positionName');
            $deptId = $request->get('deptId');
            $departmentId = $request->get('department_id');
            $storeId = $request->get('store_id');
            
            error_log('getPositions request params: companyId=' . $companyId . ', deptId=' . $deptId . ', departmentId=' . $departmentId . ', storeId=' . $storeId);
            
            $query = Position::where('isDelete', 0);
            
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            if (!$isSuper && isset($GLOBALS['company_id'])) {
                // 非超级管理员只能看到自己公司的职位
                $query->where('company_id', $GLOBALS['company_id']);
            } elseif ($companyId) {
                $query->where('company_id', $companyId);
            }
            if ($positionName) {
                $query->where('position_name', 'like', '%' . $positionName . '%');
            }
            if ($deptId) {
                $query->where('dept_id', $deptId);
            }
            if ($departmentId) {
                $query->where('dept_id', $departmentId);
            }
            
            // 按门店筛选（如果提供了门店ID）
            if ($storeId && $storeId !== 'other') {
                // 检查部门表是否有store_id字段
                $columns = DB::getSchemaBuilder()->getColumnListing('sys_department');
                if (in_array('store_id', $columns)) {
                    // 如果有store_id字段，先获取该门店下的所有部门ID
                    $departmentIds = DB::table('sys_department')->where('store_id', $storeId)->pluck('id')->toArray();
                    error_log('getPositions departmentIds: ' . json_encode($departmentIds));
                    if (!empty($departmentIds)) {
                        $query->whereIn('dept_id', $departmentIds);
                    } else {
                        // 如果该门店下没有部门，返回空数组
                        return json(['code' => 200, 'message' => '获取职位列表成功', 'data' => []]);
                    }
                } else {
                    // 如果部门表没有store_id字段，跳过门店筛选
                    error_log('sys_department table does not have store_id column');
                }
            }
            
            $positions = $query->orderBy('sort', 'asc')->get();
            error_log('getPositions positions found: ' . $positions->count());
            // 转换字段名：数据库字段名 转 camelCase
            $formattedPositions = $positions->map(function($position) {
                return [
                    'id' => $position->id,
                    'positionName' => $position->position_name,
                    'deptId' => $position->dept_id,
                    'sort' => $position->sort,
                    'companyId' => $position->company_id,
                    'status' => $position->status,
                    'createTime' => $position->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取职位列表成功', 'data' => $formattedPositions]);
        } catch (Exception $e) {
            error_log('getPositions error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取职位列表失败: ' . $e->getMessage()]);
        }
    }
    
    public function addPosition(Request $request)
    {
        $data = $request->post();
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'position_name' => $data['positionName'] ?? '',
            'dept_id' => $data['deptId'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'company_id' => $data['companyId'] ?? 0,
            'status' => 1,
            'isDelete' => 0
        ];
        $position = Position::create($snakeData);
        return json(['code' => 200, 'message' => '添加职位成功', 'data' => $position]);
    }
    
    public function updatePosition(Request $request, $id)
    {
        $data = $request->post();
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'position_name' => $data['positionName'] ?? '',
            'dept_id' => $data['deptId'] ?? 0,
            'sort' => $data['sort'] ?? 0,
            'company_id' => $data['companyId'] ?? 0
        ];
        $position = Position::find($id);
        if (!$position) {
            return json(['code' => 404, 'message' => '职位不存在']);
        }
        $position->update($snakeData);
        return json(['code' => 200, 'message' => '更新职位成功', 'data' => $position]);
    }
    
    public function deletePosition(Request $request, $id)
    {
        $position = Position::find($id);
        if (!$position) {
            return json(['code' => 404, 'message' => '职位不存在']);
        }
        $position->update(['isDelete' => 1]);
        return json(['code' => 200, 'message' => '删除职位成功']);
    }
    
    // 员工管理
    public function getEmployees(Request $request)
    {
        $companyId = $request->get('company_id');
        $departmentId = $request->get('department_id');
        $storeId = $request->get('storeId');
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
        
        // 以 sys_user 为基准表进行查询
        $query = User::query();
        
        // 关联查询相关信息
        $users = $query->with('employee', 'employee.company', 'employee.department', 'employee.position', 'employee.superior', 'employee.store', 'profile')->get();
        
        // 处理用户数据，构建员工管理列表所需的信息
        $formattedEmployees = $users->map(function($user) {
            // 获取用户拥有的角色
            $roles = DB::table('sys_user_role')
                ->where('user_id', $user->id)
                ->pluck('role_id')
                ->toArray();
            
            // 获取角色名称
            $roleNames = [];
            if ($roles) {
                foreach ($roles as $roleId) {
                    $role = DB::table('sys_role')->where('id', $roleId)->first();
                    if ($role) {
                        // 尝试获取角色名称，兼容不同的字段名
                        $roleNames[] = $role->name ?? $role->role_name ?? $roleId;
                    }
                }
            }
            
            // 获取用户可查看的门店
            $storeIds = DB::table('sys_user_store')
                ->where('user_id', $user->id)
                ->pluck('store_id')
                ->toArray();
            
            // 获取员工信息
            $employee = $user->employee;
            
            // 检查员工是否已删除
            if ($employee && $employee->isDelete == 1) {
                return null; // 跳过已删除的员工
            }
            
            // 获取所属门店名称
            $storeName = '';
            if ($employee && $employee->store_id && $employee->store_id != 0) {
                $store = DB::table('sys_store')->where('id', $employee->store_id)->first();
                if ($store) {
                    $storeName = $store->store_name;
                }
            }
            
            // 构建员工数据
            $data = [
                'id' => $user->id, // 使用用户ID作为列表中的唯一标识
                'employeeId' => $employee->id ?? 0, // 保留员工ID
                'employeeName' => $employee->name ?? '', // 使用 employeeName 字段，与前端保持一致
                'name' => $employee->name ?? '',
                'positionId' => $employee->position_id ?? 0,
                'position_id' => $employee->position_id ?? 0,
                'positionName' => $employee->position->position_name ?? '', // 添加职位名称字段
                'companyId' => $employee->company_id ?? 0,
                'company_id' => $employee->company_id ?? 0,
                'companyName' => $employee->company->company_name ?? '', // 添加公司名称字段
                'storeId' => $employee->store_id ?? '',
                'store_id' => $employee->store_id ?? '',
                'storeName' => $storeName, // 添加门店名称字段
                'deptId' => $employee->department_id ?? 0,
                'department_id' => $employee->department_id ?? 0,
                'deptName' => $employee->department->dept_name ?? '', // 添加部门名称字段
                'superiorId' => $employee->superior_id ?? 0,
                'superior_id' => $employee->superior_id ?? 0,
                'superiorName' => $employee->superior->name ?? '', // 添加上级员工名称字段
                'status' => $employee->status ?? 1,
                'isDelete' => $employee->isDelete ?? 0,
                'user_id' => $user->id,
                'username' => $user->username, // 添加账号字段
                'roles' => $roles, // 添加拥有的角色字段
                'roleNames' => $roleNames, // 添加角色名称字段
                'storeIds' => $storeIds, // 添加可查看的门店字段
                'company' => $employee->company ?? null,
                'department' => $employee->department ?? null,
                'position' => $employee->position ?? null,
                'superior' => $employee->superior ?? null,
                'store' => $employee->store ?? null,
                'user' => [
                    'id' => $user->id,
                    'username' => $user->username,
                    'nickname' => $user->nickname,
                    'avatar' => $user->avatar,
                    'status' => $user->status,
                    'profile' => $user->profile
                ]
            ];
            
            return $data;
        })->filter();
        
        // 应用过滤条件
        // 默认只显示自己所属公司的数据（非超级管理员）
        if (!$isSuper && $currentCompanyId) {
            $formattedEmployees = $formattedEmployees->filter(function($employee) use ($currentCompanyId) {
                return $employee['company_id'] == $currentCompanyId;
            });
        } elseif ($companyId) {
            // 如果指定了公司ID，则按指定的过滤
            $formattedEmployees = $formattedEmployees->filter(function($employee) use ($companyId) {
                return $employee['company_id'] == $companyId;
            });
        }
        
        if ($departmentId) {
            $formattedEmployees = $formattedEmployees->filter(function($employee) use ($departmentId) {
                return $employee['department_id'] == $departmentId;
            });
        }
        
        if ($storeId) {
            if ($storeId === 'other') {
                // 筛选不在任何门店中的员工
                $formattedEmployees = $formattedEmployees->filter(function($employee) {
                    return empty($employee['store_id']) || $employee['store_id'] === null || $employee['store_id'] === '';
                });
            } else {
                // 筛选指定门店的员工
                $formattedEmployees = $formattedEmployees->filter(function($employee) use ($storeId) {
                    return $employee['store_id'] == $storeId;
                });
            }
        }
        
        return json(['code' => 200, 'message' => '获取员工列表成功', 'data' => $formattedEmployees->values()->toArray()]);
    }
    
    public function addEmployee(Request $request)
    {
        try {
            $data = $request->post();
            $roles = $data['roles'] ?? [];
            // 确保 $roles 是数组
            if (is_string($roles)) {
                $roles = explode(',', $roles);
                // 过滤空值
                $roles = array_filter(array_map('trim', $roles));
            }
            $username = $data['username'] ?? '';
            $storeIds = $data['storeIds'] ?? [];
            // 确保 $storeIds 是数组
            if (is_string($storeIds)) {
                $storeIds = explode(',', $storeIds);
                // 过滤空值
                $storeIds = array_filter(array_map('trim', $storeIds));
            }
            
            // 创建用户账号
            $user = null;
            if ($username) {
                // 检查用户名是否已被使用
                $existingUser = User::where('username', $username)->first();
                if ($existingUser) {
                    return json(['code' => 400, 'message' => '用户名已存在，请使用其他用户名']);
                }
                $userData = [
                    'username' => $username,
                    'password' => password_hash('aa123456', PASSWORD_DEFAULT),
                    'nickname' => '',
                    'avatar' => '',
                    'status' => 1
                ];
                
                $user = User::create($userData);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $snakeData = [
                'name' => $data['employeeName'] ?? '',
                'company_id' => $data['companyId'] ?? 0,
                'department_id' => $data['deptId'] ?? 0,
                'position_id' => $data['positionId'] ?? 0,
                'user_id' => $user->id ?? 0,
                'superior_id' => $data['superiorId'] ?? 0,
                'store_id' => $data['storeId'] ?? null,
                'status' => 1,
                'isDelete' => 0
            ];
            
            $employee = Employee::create($snakeData);
            
            // 处理角色关联
            if ($roles && $user) {
                foreach ($roles as $roleId) {
                    DB::table('sys_user_role')->insert([
                        'user_id' => $user->id,
                        'role_id' => $roleId
                    ]);
                }
            }
            
            // 分配门店权限
            if ($storeIds && $user) {
                foreach ($storeIds as $storeId) {
                    DB::table('sys_user_store')->insert([
                        'user_id' => $user->id,
                        'store_id' => $storeId
                    ]);
                }
            }
            
            // 创建用户详细信息
            if ($user) {
                $profileData = [
                    'user_id' => $user->id,
                    'phone' => $data['phone'] ?? '',
                    'email' => $data['email'] ?? '',
                    'birthday_solar' => $data['birthdaySolar'] ?? null,
                    'birthday_lunar' => $data['birthdayLunar'] ?? '',
                    'id_card' => $data['idCard'] ?? '',
                    'address' => $data['address'] ?? '',
                    'emergency_contact' => $data['emergencyContact'] ?? '',
                    'emergency_phone' => $data['emergencyPhone'] ?? '',
                    'entry_date' => $data['entryDate'] ?? null,
                    'leave_date' => $data['leaveDate'] ?? null
                ];
                
                DB::table('sys_user_profile')->insert($profileData);
            }
            
            $employee->load('company', 'department', 'user', 'user.profile');
            return json(['code' => 200, 'message' => '添加员工成功', 'data' => $employee]);
        } catch (\Exception $e) {
            error_log('addEmployee error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加员工失败：' . $e->getMessage()]);
        }
    }
    
    public function updateEmployee(Request $request, $id)
    {
        try {
            $data = $request->post();
            $roles = $data['roles'] ?? [];
            // 确保 $roles 是数组
            if (is_string($roles)) {
                $roles = explode(',', $roles);
                // 过滤空值
                $roles = array_filter(array_map('trim', $roles));
            }
            $username = $data['username'] ?? '';
            $storeIds = $data['storeIds'] ?? [];
            // 确保 $storeIds 是数组
            if (is_string($storeIds)) {
                $storeIds = explode(',', $storeIds);
                // 过滤空值
                $storeIds = array_filter(array_map('trim', $storeIds));
            }
            
            $employee = Employee::find($id);
            if (!$employee) {
                return json(['code' => 404, 'message' => '员工不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $snakeData = [
                'name' => $data['employeeName'] ?? '',
                'company_id' => $data['companyId'] ?? 0,
                'department_id' => $data['deptId'] ?? 0,
                'position_id' => $data['positionId'] ?? 0,
                'superior_id' => $data['superiorId'] ?? 0,
                'store_id' => $data['storeId'] ?? null
            ];
            
            $employee->update($snakeData);
            
            // 更新或创建用户账号
            $user = null;
            if ($username) {
                // 首先尝试通过员工的 user_id 查找用户
                if ($employee->user_id) {
                    $user = User::find($employee->user_id);
                }
                
                if ($user) {
                    // 只有当用户名发生变化时，才检查用户名是否已被其他用户使用
                    if ($user->username !== $username) {
                        $existingUser = User::where('username', $username)->where('id', '!=', $user->id)->first();
                        if ($existingUser) {
                            return json(['code' => 400, 'message' => '用户名已存在，请使用其他用户名']);
                        }
                        // 只有当用户名发生变化时，才更新用户名
                        $userData = [
                            'username' => $username,
                            'nickname' => '',
                            'avatar' => ''
                        ];
                    } else {
                        // 用户名没有变化，只更新其他字段
                        $userData = [
                            'nickname' => '',
                            'avatar' => ''
                        ];
                    }
                    $user->update($userData);
                } else {
                    // 检查用户名是否已被使用
                    $existingUser = User::where('username', $username)->first();
                    if ($existingUser) {
                        return json(['code' => 400, 'message' => '用户名已存在，请使用其他用户名']);
                    }
                    // 创建新用户
                    $userData = [
                        'username' => $username,
                        'password' => password_hash('aa123456', PASSWORD_DEFAULT),
                        'nickname' => '',
                        'avatar' => '',
                        'status' => 1
                    ];
                    $user = User::create($userData);
                    
                    // 更新员工的 user_id
                    $employee->update(['user_id' => $user->id]);
                }
                
                // 更新角色关联
                if ($user) {
                    // 先删除旧的角色关联
                    DB::table('sys_user_role')->where('user_id', $user->id)->delete();
                    
                    // 添加新的角色关联
                    if ($roles) {
                        foreach ($roles as $roleId) {
                            DB::table('sys_user_role')->insert([
                                'user_id' => $user->id,
                                'role_id' => $roleId
                            ]);
                        }
                    }
                    
                    // 更新门店权限
                    // 先删除旧的权限
                    DB::table('sys_user_store')->where('user_id', $user->id)->delete();
                    
                    // 添加新的权限
                    if ($storeIds) {
                        foreach ($storeIds as $storeId) {
                            DB::table('sys_user_store')->insert([
                                'user_id' => $user->id,
                                'store_id' => $storeId
                            ]);
                        }
                    }
                    
                    // 更新用户详细信息
                    $profile = DB::table('sys_user_profile')->where('user_id', $user->id)->first();
                    $profileData = [
                        'phone' => $data['phone'] ?? '',
                        'email' => $data['email'] ?? '',
                        'birthday_solar' => $data['birthdaySolar'] ?? null,
                        'birthday_lunar' => $data['birthdayLunar'] ?? '',
                        'id_card' => $data['idCard'] ?? '',
                        'address' => $data['address'] ?? '',
                        'emergency_contact' => $data['emergencyContact'] ?? '',
                        'emergency_phone' => $data['emergencyPhone'] ?? '',
                        'entry_date' => $data['entryDate'] ?? null,
                        'leave_date' => $data['leaveDate'] ?? null
                    ];
                    
                    if ($profile) {
                        DB::table('sys_user_profile')->where('user_id', $user->id)->update($profileData);
                    } else {
                        $profileData['user_id'] = $user->id;
                        DB::table('sys_user_profile')->insert($profileData);
                    }
                }
            }
            
            $employee->load('company', 'department', 'position', 'superior', 'user', 'user.profile');
            return json(['code' => 200, 'message' => '更新员工成功', 'data' => $employee]);
        } catch (\Exception $e) {
            error_log('updateEmployee error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新员工失败：' . $e->getMessage()]);
        }
    }
    
    public function getEmployeeDetail(Request $request, $id)
    {
        try {
            $employee = Employee::find($id);
            if (!$employee) {
                return json(['code' => 404, 'message' => '员工不存在']);
            }
            
            // 加载所有关联数据
            $employee->load('company', 'department', 'position', 'user', 'user.profile');
            
            // 获取用户拥有的角色
            if ($employee->user) {
                $roles = DB::table('sys_user_role')
                    ->where('user_id', $employee->user->id)
                    ->pluck('role_id')
                    ->toArray();
                $employee->user->roles = $roles;
                
                // 获取用户可查看的门店
                $storeIds = DB::table('sys_user_store')
                    ->where('user_id', $employee->user->id)
                    ->pluck('store_id')
                    ->toArray();
                $employee->user->storeIds = $storeIds;
            }
            
            return json(['code' => 200, 'message' => '获取员工详情成功', 'data' => $employee]);
        } catch (\Exception $e) {
            error_log('getEmployeeDetail error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取员工详情失败：' . $e->getMessage()]);
        }
    }
    
    public function deleteEmployee(Request $request, $id)
    {
        $employee = Employee::find($id);
        if (!$employee) {
            return json(['code' => 404, 'message' => '员工不存在']);
        }
        $employee->update(['isDelete' => 1]);
        return json(['code' => 200, 'message' => '删除员工成功']);
    }
    
    // 床位管理
    public function getBedrooms(Request $request)
    {
        $companyId = $request->get('companyId');
        $storeId = $request->get('storeId');
        $roomName = $request->get('roomName');
        $query = Bedroom::query();
        
        // 检查用户权限
        $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
        if (!$isSuper && isset($GLOBALS['company_id'])) {
            // 非超级管理员只能看到自己公司的床位
            // 先获取自己公司的所有门店ID
            $storeIds = Store::where('company_id', $GLOBALS['company_id'])->pluck('id')->toArray();
            if ($storeIds) {
                $query->whereIn('store_id', $storeIds);
            } else {
                // 如果没有门店，返回空数组
                return json(['code' => 200, 'message' => '获取床位列表成功', 'data' => []]);
            }
        } elseif ($companyId) {
            // 如果指定了公司ID，获取该公司的所有门店ID
            $storeIds = Store::where('company_id', $companyId)->pluck('id')->toArray();
            if ($storeIds) {
                $query->whereIn('store_id', $storeIds);
            } else {
                // 如果没有门店，返回空数组
                return json(['code' => 200, 'message' => '获取床位列表成功', 'data' => []]);
            }
        }
        
        if ($storeId) {
            $query->where('store_id', $storeId);
        }
        if ($roomName) {
            $query->where('room_name', 'like', '%' . $roomName . '%');
        }
        
        $bedrooms = $query->get();
        
        // 转换字段名：数据库字段名 转 camelCase
        $formattedBedrooms = $bedrooms->map(function($bedroom) {
            return [
                'id' => $bedroom->id,
                'storeId' => $bedroom->store_id,
                'roomName' => $bedroom->room_name,
                'bedCount' => $bedroom->bed_count,
                'createTime' => $bedroom->created_at
            ];
        });
        
        return json(['code' => 200, 'message' => '获取床位列表成功', 'data' => $formattedBedrooms]);
    }
    
    public function addBedroom(Request $request)
    {
        $data = $request->post();
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'store_id' => $data['storeId'] ?? 0,
            'room_name' => $data['roomName'] ?? '',
            'bed_count' => $data['bedCount'] ?? 0
        ];
        
        // 验证必要参数
        if (empty($snakeData['store_id']) || empty($snakeData['room_name']) || empty($snakeData['bed_count'])) {
            return json(['code' => 400, 'message' => '所属门店、房间名称和床位数量不能为空']);
        }
        
        $bedroom = Bedroom::create($snakeData);
        
        // 转换字段名：数据库字段名 转 camelCase
        $formattedBedroom = [
            'id' => $bedroom->id,
            'storeId' => $bedroom->store_id,
            'roomName' => $bedroom->room_name,
            'bedCount' => $bedroom->bed_count,
            'createTime' => $bedroom->created_at
        ];
        
        return json(['code' => 200, 'message' => '添加床位成功', 'data' => $formattedBedroom]);
    }
    
    public function updateBedroom(Request $request, $id)
    {
        $data = $request->post();
        $bedroom = Bedroom::find($id);
        if (!$bedroom) {
            return json(['code' => 404, 'message' => '床位不存在']);
        }
        
        // 转换字段名：camelCase 转 snake_case
        $snakeData = [
            'store_id' => $data['storeId'] ?? $bedroom->store_id,
            'room_name' => $data['roomName'] ?? $bedroom->room_name,
            'bed_count' => $data['bedCount'] ?? $bedroom->bed_count
        ];
        
        $bedroom->update($snakeData);
        
        // 转换字段名：数据库字段名 转 camelCase
        $formattedBedroom = [
            'id' => $bedroom->id,
            'storeId' => $bedroom->store_id,
            'roomName' => $bedroom->room_name,
            'bedCount' => $bedroom->bed_count,
            'createTime' => $bedroom->created_at
        ];
        
        return json(['code' => 200, 'message' => '更新床位成功', 'data' => $formattedBedroom]);
    }
    
    public function deleteBedroom(Request $request, $id)
    {
        $bedroom = Bedroom::find($id);
        if (!$bedroom) {
            return json(['code' => 404, 'message' => '床位不存在']);
        }
        $bedroom->delete();
        return json(['code' => 200, 'message' => '删除床位成功']);
    }
}
