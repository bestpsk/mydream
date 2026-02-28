<?php

namespace app\controller;

use support\Request;
use support\Response;
use app\model\Customer;
use app\model\Department;
use app\model\Store;
use Illuminate\Database\Capsule\Manager as DB;
use Overtrue\Pinyin\Pinyin;

class CustomerController extends BaseController
{
    /**
     * 获取客户列表
     * @param Request $request
     * @return Response
     */
    public function list(Request $request)
    {
        try {
            $page = $request->get('page', 1);
            $pageSize = $request->get('pageSize', 10);
            $name = $request->get('name');
            $phone = $request->get('phone');
            $level = $request->get('level');
            $storeId = $request->get('storeId');
            $departmentId = $request->get('departmentId');
            
            $query = Customer::where('status', 1);
            
            $query = $this->applyTenantScope($query, 'company_id');
            
            if ($name) {
                $query->where(function($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%')
                      ->orWhere('name_pinyin', 'like', '%' . strtoupper($name) . '%');
                });
            }
            
            if ($phone) {
                $query->where('phone', 'like', '%' . $phone . '%');
            }
            
            if ($level) {
                $query->where('level', $level);
            }
            
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
            
            if ($departmentId) {
                $query->where(function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId)
                      ->orWhereExists(function($subQuery) use ($departmentId) {
                          $subQuery->select(DB::raw(1))
                                   ->from('cust_customer_department')
                                   ->whereColumn('cust_customer_department.customer_id', 'cust_customer.id')
                                   ->where('cust_customer_department.department_id', $departmentId);
                      });
                });
            }
            
            $total = $query->count();
            $customers = $query->with(['store', 'department', 'serviceStaff', 'manager'])
                ->orderBy('created_at', 'desc')
                ->offset(($page - 1) * $pageSize)
                ->limit($pageSize)
                ->get();
            
            $formattedCustomers = $customers->map(function($customer) {
                $departmentIds = \app\model\CustomerDepartment::where('customer_id', $customer->id)->pluck('department_id')->toArray();
                $departmentNames = [];
                if (!empty($departmentIds)) {
                    $departments = \app\model\Department::whereIn('id', $departmentIds)->get();
                    foreach ($departments as $dept) {
                        $departmentNames[] = $dept->dept_name;
                    }
                }
                $departmentNameStr = !empty($departmentNames) ? implode(', ', $departmentNames) : ($customer->department ? $customer->department->dept_name : '');
                
                // 获取客户的多个服务人信息
                $serviceStaffIds = \app\model\CustomerServiceStaff::where('customer_id', $customer->id)->pluck('service_staff_id')->toArray();
                if (empty($serviceStaffIds) && $customer->service_staff_id) {
                    $serviceStaffIds = [$customer->service_staff_id];
                }
                $serviceStaffNames = [];
                if (!empty($serviceStaffIds)) {
                    $serviceStaffs = \app\model\UserEmployee::whereIn('id', $serviceStaffIds)->where('status', 1)->where('isDelete', 0)->get();
                    foreach ($serviceStaffs as $staff) {
                        $serviceStaffNames[] = $staff->name;
                    }
                }
                $serviceStaffName = !empty($serviceStaffNames) ? implode(', ', $serviceStaffNames) : '';
                
                // 获取客户的多个管理人信息
                $managerIds = \app\model\CustomerManager::where('customer_id', $customer->id)->pluck('manager_id')->toArray();
                if (empty($managerIds) && $customer->manager_id) {
                    $managerIds = [$customer->manager_id];
                }
                $managerNames = [];
                if (!empty($managerIds)) {
                    $managers = \app\model\UserEmployee::whereIn('id', $managerIds)->where('status', 1)->where('isDelete', 0)->get();
                    foreach ($managers as $manager) {
                        $managerNames[] = $manager->name;
                    }
                }
                $managerName = !empty($managerNames) ? implode(', ', $managerNames) : '';
                
                return [
                    'id' => $customer->id,
                    'storeId' => $customer->store_id,
                    'storeName' => $customer->store ? $customer->store->store_name : '',
                    'departmentId' => $customer->department_id,
                    'departmentIds' => $departmentIds,
                    'departmentName' => $departmentNameStr,
                    'memberCard' => $customer->member_card,
                    'name' => $customer->name,
                    'gender' => $customer->gender,
                    'genderText' => $customer->gender == 1 ? '男' : '女',
                    'phone' => $customer->phone,
                    'birthday' => $customer->birthday,
                    'birthdayType' => $customer->birthday_type,
                    'birthdayTypeText' => $customer->birthday_type == 1 ? '阳历' : '阴历',
                    'points' => $customer->points,
                    'registerTime' => $customer->register_time,
                    'source' => $customer->source,
                    'avatar' => $customer->avatar,
                    'archiveNumber' => $customer->archive_number,
                    'level' => $customer->level,
                    'serviceStaffId' => $customer->service_staff_id,
                    'serviceStaffIds' => $serviceStaffIds,
                    'serviceStaffName' => $serviceStaffName ? $serviceStaffName : '无',
                    'managerId' => $customer->manager_id,
                    'managerIds' => $managerIds,
                    'managerName' => $managerName ? $managerName : '无',
                    'lastConsumeTime' => $customer->last_consume_time,
                    'lastConsumeAmount' => $customer->last_consume_amount,
                    'lastDepleteTime' => $customer->last_deplete_time,
                    'lastDepleteAmount' => $customer->last_deplete_amount,
                    'remark' => $customer->remark,
                    'status' => $customer->status,
                    'createdAt' => $customer->created_at,
                    'updatedAt' => $customer->updated_at
                ];
            });
            
            return json([
                'code' => 200,
                'message' => '获取客户列表成功',
                'data' => [
                    'list' => $formattedCustomers,
                    'total' => $total,
                    'page' => $page,
                    'pageSize' => $pageSize
                ]
            ]);
            
        } catch (Exception $e) {
            error_log('Customer list error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取客户列表失败']);
        }
    }
    
    /**
     * 获取客户详情
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function detail(Request $request, $id)
    {
        try {
            $customer = Customer::with(['store', 'department', 'serviceStaff', 'manager'])->find($id);
            
            if (!$customer) {
                return json(['code' => 404, 'message' => '客户不存在']);
            }
            
            // 格式化数据
            $formattedCustomer = [
                'id' => $customer->id,
                'storeId' => $customer->store_id,
                'storeName' => $customer->store ? $customer->store->store_name : '',
                'departmentId' => $customer->department_id,
                'departmentName' => $customer->department ? $customer->department->dept_name : '',
                'memberCard' => $customer->member_card,
                'name' => $customer->name,
                'gender' => $customer->gender,
                'genderText' => $customer->gender == 1 ? '男' : '女',
                'phone' => $customer->phone,
                'birthday' => $customer->birthday,
                'birthdayType' => $customer->birthday_type,
                'birthdayTypeText' => $customer->birthday_type == 1 ? '阳历' : '阴历',
                'points' => $customer->points,
                'registerTime' => $customer->register_time,
                'source' => $customer->source,
                'avatar' => $customer->avatar,
                'archiveNumber' => $customer->archive_number,
                'level' => $customer->level,
                'serviceStaffId' => $customer->service_staff_id,
                'serviceStaffName' => $customer->serviceStaff ? $customer->serviceStaff->name : '',
                'managerId' => $customer->manager_id,
                'managerName' => $customer->manager ? $customer->manager->name : '',
                'lastConsumeTime' => $customer->last_consume_time,
                'lastConsumeAmount' => $customer->last_consume_amount,
                'lastDepleteTime' => $customer->last_deplete_time,
                'lastDepleteAmount' => $customer->last_deplete_amount,
                'remark' => $customer->remark,
                'status' => $customer->status,
                'createdAt' => $customer->created_at,
                'updatedAt' => $customer->updated_at
            ];
            
            return json(['code' => 200, 'message' => '获取客户详情成功', 'data' => $formattedCustomer]);
            
        } catch (Exception $e) {
            error_log('Customer detail error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取客户详情失败']);
        }
    }
    
    /**
     * 新增客户
     * @param Request $request
     * @return Response
     */
    public function add(Request $request)
    {
        try {
            $data = $request->post();
            
            // 处理部门ID，支持单个ID或ID数组
            $departmentId = null;
            if (isset($data['departmentIds']) && is_array($data['departmentIds']) && count($data['departmentIds']) > 0) {
                $departmentId = $data['departmentIds'][0]; // 暂时只取第一个部门ID
            } elseif (isset($data['departmentId'])) {
                $departmentId = $data['departmentId'];
            }
            
            // 生成档案编号
            $archiveNumber = Customer::generateArchiveNumber($data['storeId'], $departmentId);
            
            // 计算初始级别
            $level = Customer::calculateLevel(0);
            
            // 生成姓名拼音首字母
            $pinyin = new Pinyin();
            $namePinyin = strtoupper($pinyin->abbr($data['name']));

            
            $managerId = null;
            if (isset($data['managerIds']) && is_array($data['managerIds']) && count($data['managerIds']) > 0) {
                $managerId = $data['managerIds'][0]; // 暂时只取第一个管理人ID
            } elseif (isset($data['managerId'])) {
                $managerId = $data['managerId'];
            }
            
            // 处理服务人ID，支持单个ID或ID数组
            $serviceStaffId = null;
            if (isset($data['serviceStaffIds']) && is_array($data['serviceStaffIds']) && count($data['serviceStaffIds']) > 0) {
                $serviceStaffId = $data['serviceStaffIds'][0]; // 暂时只取第一个服务人ID
            } elseif (isset($data['serviceStaffId'])) {
                $serviceStaffId = $data['serviceStaffId'];
            }
            
            $customerData = [
                'store_id' => $data['storeId'],
                'department_id' => $departmentId,
                'member_card' => $data['memberCard'],
                'name' => $data['name'],
                'name_pinyin' => $namePinyin,
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'birthday_type' => $data['birthdayType'],
                'points' => $data['points'] ?? 0,
                'register_time' => $data['registerTime'] ?? date('Y-m-d H:i:s'),
                'source' => $data['source'],
                'avatar' => $data['avatar'],
                'archive_number' => $archiveNumber,
                'level' => $level,
                'service_staff_id' => $serviceStaffId,
                'manager_id' => $managerId,
                'remark' => $data['remark'],
                'status' => 1
            ];
            
            $customer = Customer::create($customerData);
            
            // 保存客户的多个部门信息到关联表
            if (isset($data['departmentIds']) && is_array($data['departmentIds']) && count($data['departmentIds']) > 0) {
                foreach ($data['departmentIds'] as $deptId) {
                    \app\model\CustomerDepartment::create([
                        'customer_id' => $customer->id,
                        'department_id' => $deptId
                    ]);
                }
            }
            
            // 保存客户的多个服务人信息到关联表
            if (isset($data['serviceStaffIds']) && is_array($data['serviceStaffIds']) && count($data['serviceStaffIds']) > 0) {
                foreach ($data['serviceStaffIds'] as $staffId) {
                    \app\model\CustomerServiceStaff::create([
                        'customer_id' => $customer->id,
                        'service_staff_id' => $staffId
                    ]);
                }
            }
            
            // 保存客户的多个管理人信息到关联表
            if (isset($data['managerIds']) && is_array($data['managerIds']) && count($data['managerIds']) > 0) {
                foreach ($data['managerIds'] as $managerId) {
                    \app\model\CustomerManager::create([
                        'customer_id' => $customer->id,
                        'manager_id' => $managerId
                    ]);
                }
            }
            
            return json(['code' => 200, 'message' => '新增客户成功', 'data' => $customer]);
            
        } catch (Exception $e) {
            error_log('Customer add error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '新增客户失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 编辑客户
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function edit(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);
            
            if (!$customer) {
                return json(['code' => 404, 'message' => '客户不存在']);
            }
            
            $data = $request->post();
            
            // 处理部门ID，支持单个ID或ID数组
            $departmentId = null;
            if (isset($data['departmentIds']) && is_array($data['departmentIds']) && count($data['departmentIds']) > 0) {
                $departmentId = $data['departmentIds'][0]; // 暂时只取第一个部门ID
            } elseif (isset($data['departmentId'])) {
                $departmentId = $data['departmentId'];
            }
            
            // 生成姓名拼音首字母
            $pinyin = new Pinyin();
            $namePinyin = strtoupper($pinyin->abbr($data['name']));

            
            $managerId = null;
            if (isset($data['managerIds']) && is_array($data['managerIds']) && count($data['managerIds']) > 0) {
                $managerId = $data['managerIds'][0]; // 暂时只取第一个管理人ID
            } elseif (isset($data['managerId'])) {
                $managerId = $data['managerId'];
            }
            
            // 处理服务人ID，支持单个ID或ID数组
            $serviceStaffId = null;
            if (isset($data['serviceStaffIds']) && is_array($data['serviceStaffIds']) && count($data['serviceStaffIds']) > 0) {
                $serviceStaffId = $data['serviceStaffIds'][0]; // 暂时只取第一个服务人ID
            } elseif (isset($data['serviceStaffId'])) {
                $serviceStaffId = $data['serviceStaffId'];
            }
            
            $customerData = [
                'store_id' => $data['storeId'],
                'department_id' => $departmentId,
                'member_card' => $data['memberCard'],
                'name' => $data['name'],
                'name_pinyin' => $namePinyin,
                'gender' => $data['gender'],
                'phone' => $data['phone'],
                'birthday' => $data['birthday'],
                'birthday_type' => $data['birthdayType'],
                'points' => $data['points'],
                'register_time' => $data['registerTime'],
                'source' => $data['source'],
                'avatar' => $data['avatar'],
                'service_staff_id' => $serviceStaffId,
                'manager_id' => $managerId,
                'remark' => $data['remark']
            ];
            
            $customer->update($customerData);
            
            // 更新客户的多个部门信息到关联表
            if (isset($data['departmentIds']) && is_array($data['departmentIds']) && count($data['departmentIds']) > 0) {
                // 先删除现有的关联记录
                \app\model\CustomerDepartment::where('customer_id', $customer->id)->delete();
                // 再添加新的关联记录
                foreach ($data['departmentIds'] as $deptId) {
                    \app\model\CustomerDepartment::create([
                        'customer_id' => $customer->id,
                        'department_id' => $deptId
                    ]);
                }
            }
            
            // 更新客户的多个服务人信息到关联表
            if (isset($data['serviceStaffIds']) && is_array($data['serviceStaffIds']) && count($data['serviceStaffIds']) > 0) {
                // 先删除现有的关联记录
                \app\model\CustomerServiceStaff::where('customer_id', $customer->id)->delete();
                // 再添加新的关联记录
                foreach ($data['serviceStaffIds'] as $staffId) {
                    \app\model\CustomerServiceStaff::create([
                        'customer_id' => $customer->id,
                        'service_staff_id' => $staffId
                    ]);
                }
            }
            
            // 更新客户的多个管理人信息到关联表
            if (isset($data['managerIds']) && is_array($data['managerIds']) && count($data['managerIds']) > 0) {
                // 先删除现有的关联记录
                \app\model\CustomerManager::where('customer_id', $customer->id)->delete();
                // 再添加新的关联记录
                foreach ($data['managerIds'] as $managerId) {
                    \app\model\CustomerManager::create([
                        'customer_id' => $customer->id,
                        'manager_id' => $managerId
                    ]);
                }
            }
            
            return json(['code' => 200, 'message' => '编辑客户成功', 'data' => $customer]);
            
        } catch (Exception $e) {
            error_log('Customer edit error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '编辑客户失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除客户
     * @param Request $request
     * @param int $id
     * @return Response
     */
    public function delete(Request $request, $id)
    {
        try {
            $customer = Customer::find($id);
            
            if (!$customer) {
                return json(['code' => 404, 'message' => '客户不存在']);
            }
            
            $customer->update(['status' => 0]);
            
            return json(['code' => 200, 'message' => '删除客户成功']);
            
        } catch (Exception $e) {
            error_log('Customer delete error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除客户失败']);
        }
    }
    
    /**
     * 获取核心业务部门
     * @param Request $request
     * @return Response
     */
    public function coreDepartments(Request $request)
    {
        try {
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = Department::where('enable_category', 1)
                ->where('status', 1)
                ->where('isDelete', 0);
            
            if ($currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $departments = $query->get();
            
            $formattedDepartments = $departments->map(function($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->dept_name
                ];
            });
            
            return json(['code' => 200, 'message' => '获取核心业务部门成功', 'data' => $formattedDepartments]);
            
        } catch (Exception $e) {
            error_log('Core departments error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取核心业务部门失败']);
        }
    }
    
    /**
     * 获取员工列表（服务人和管理人）
     * @param Request $request
     * @return Response
     */
    public function employees(Request $request)
    {
        try {
            $storeId = $request->get('storeId');
            $departmentId = $request->get('departmentId');
            $departmentIds = $request->get('departmentIds');
            
            error_log('Employees request params: storeId=' . $storeId . ', departmentId=' . $departmentId . ', departmentIds=' . json_encode($departmentIds));
            
            $query = \app\model\UserEmployee::where('status', 1)->where('isDelete', 0);
            
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
            
            // 处理部门查询条件
            if ($departmentIds && is_array($departmentIds) && count($departmentIds) > 0) {
                $query->whereIn('department_id', $departmentIds);
            } elseif ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            
            $employees = $query->get();
            
            error_log('Employees found: ' . $employees->count());
            
            $formattedEmployees = $employees->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'superior_id' => $employee->superior_id
                ];
            });
            
            error_log('Formatted employees: ' . json_encode($formattedEmployees));
            
            return json(['code' => 200, 'message' => '获取员工列表成功', 'data' => $formattedEmployees]);
            
        } catch (Exception $e) {
            error_log('Employees error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取员工列表失败']);
        }
    }
    
    /**
     * 根据员工ID获取其上级信息
     * @param Request $request
     * @return Response
     */
    public function getSuperior(Request $request)
    {
        try {
            $employeeId = $request->get('employeeId');
            
            if (!$employeeId) {
                return json(['code' => 400, 'message' => '员工ID不能为空']);
            }
            
            // 获取员工信息
            $employee = \app\model\UserEmployee::find($employeeId);
            
            if (!$employee) {
                return json(['code' => 404, 'message' => '员工不存在']);
            }
            
            // 获取上级信息
            $superior = null;
            if ($employee->superior_id) {
                $superior = \app\model\UserEmployee::find($employee->superior_id);
            }
            
            $result = [
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name
                ],
                'superior' => $superior ? [
                    'id' => $superior->id,
                    'name' => $superior->name
                ] : null
            ];
            
            return json(['code' => 200, 'message' => '获取上级信息成功', 'data' => $result]);
            
        } catch (Exception $e) {
            error_log('Get superior error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取上级信息失败']);
        }
    }
}
?>