<?php

namespace app\controller;

use app\model\Menu;
use support\Request;
use support\DB;

class AppointmentController
{
    /**
     * 获取预约列表
     * @param Request $request 请求对象
     * @return array 预约列表数据
     */
    public function getAppointments(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 构建查询
            $query = DB::table('card_appointment')->where('company_id', $currentCompanyId)->where('isDelete', 0);
            
            // 应用搜索条件
            $appointmentDate = $request->get('appointmentDate');
            $departmentId = $request->get('departmentId');
            $employeeId = $request->get('employeeId');
            $status = $request->get('status');
            $customerName = $request->get('customerName');
            
            if ($appointmentDate) {
                $query->where('appointment_date', $appointmentDate);
            }
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            if ($employeeId) {
                $query->where('employee_id', $employeeId);
            }
            if ($status) {
                $query->where('status', $status);
            }
            if ($customerName) {
                $query->where('customer_name', 'like', '%' . $customerName . '%');
            }
            
            // 执行查询
            $appointments = $query->orderBy('appointment_date', 'desc')->orderBy('start_time', 'asc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedAppointments = $appointments->map(function($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointmentNo' => $appointment->appointment_no,
                    'customerId' => $appointment->customer_id,
                    'customerName' => $appointment->customer_name,
                    'customerPhone' => $appointment->customer_phone,
                    'departmentId' => $appointment->department_id,
                    'employeeId' => $appointment->employee_id,
                    'employeeName' => $appointment->employee_name,
                    'managerId' => $appointment->manager_id,
                    'managerName' => $appointment->manager_name,
                    'roomId' => $appointment->room_id,
                    'roomName' => $appointment->room_name,
                    'appointmentDate' => $appointment->appointment_date,
                    'startTime' => $appointment->start_time,
                    'endTime' => $appointment->end_time,
                    'appointmentType' => $appointment->appointment_type,
                    'status' => $appointment->status,
                    'remark' => $appointment->remark,
                    'createdAt' => $appointment->created_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取预约列表成功', 'data' => $formattedAppointments]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get appointments error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取预约列表失败，请稍后重试']);
        }
    }
    
    /**
     * 添加预约
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addAppointment(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['customerId'])) {
                return json(['code' => 400, 'message' => '客户ID不能为空']);
            }
            if (empty($data['employeeId'])) {
                return json(['code' => 400, 'message' => '服务员工ID不能为空']);
            }
            if (empty($data['roomId'])) {
                return json(['code' => 400, 'message' => '房间ID不能为空']);
            }
            if (empty($data['appointmentDate'])) {
                return json(['code' => 400, 'message' => '预约日期不能为空']);
            }
            if (empty($data['startTime'])) {
                return json(['code' => 400, 'message' => '开始时间不能为空']);
            }
            if (empty($data['endTime'])) {
                return json(['code' => 400, 'message' => '结束时间不能为空']);
            }
            if (empty($data['appointmentItems'])) {
                return json(['code' => 400, 'message' => '预约项目不能为空']);
            }
            
            // 生成预约编号
            $appointmentNo = 'APT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            
            // 开始事务
            DB::beginTransaction();
            
            try {
                // 获取客户信息
                $customer = DB::table('card_customer')->where('id', $data['customerId'])->where('company_id', $currentCompanyId)->first();
                if (!$customer) {
                    return json(['code' => 404, 'message' => '客户不存在']);
                }
                
                // 获取员工信息
                $employee = DB::table('card_employee')->where('id', $data['employeeId'])->where('company_id', $currentCompanyId)->first();
                if (!$employee) {
                    return json(['code' => 404, 'message' => '员工不存在']);
                }
                
                // 获取房间信息
                $room = DB::table('card_room')->where('id', $data['roomId'])->where('company_id', $currentCompanyId)->first();
                if (!$room) {
                    return json(['code' => 404, 'message' => '房间不存在']);
                }
                
                // 获取部门信息
                $departmentName = '';
                if ($data['departmentId']) {
                    $department = DB::table('card_department')->where('id', $data['departmentId'])->where('company_id', $currentCompanyId)->first();
                    if ($department) {
                        $departmentName = $department->name;
                    }
                }
                
                // 获取管理者信息
                $managerName = '';
                if ($data['managerId']) {
                    $manager = DB::table('card_employee')->where('id', $data['managerId'])->where('company_id', $currentCompanyId)->first();
                    if ($manager) {
                        $managerName = $manager->name;
                    }
                }
                
                // 检查时间冲突
                $conflict = DB::table('card_appointment')
                    ->where('company_id', $currentCompanyId)
                    ->where('isDelete', 0)
                    ->where('status', '!=', 'cancelled')
                    ->where('room_id', $data['roomId'])
                    ->where('appointment_date', $data['appointmentDate'])
                    ->where(function($query) use ($data) {
                        $query->where(function($q) use ($data) {
                            $q->where('start_time', '<', $data['endTime'])
                              ->where('end_time', '>', $data['startTime']);
                        });
                    })
                    ->first();
                
                if ($conflict) {
                    return json(['code' => 400, 'message' => '该时间段房间已被预约']);
                }
                
                // 添加预约
                $appointmentId = DB::table('card_appointment')->insertGetId([
                    'company_id' => $currentCompanyId,
                    'appointment_no' => $appointmentNo,
                    'customer_id' => $data['customerId'],
                    'customer_name' => $customer->name,
                    'customer_phone' => $customer->phone,
                    'department_id' => $data['departmentId'] ?? 0,
                    'employee_id' => $data['employeeId'],
                    'employee_name' => $employee->name,
                    'manager_id' => $data['managerId'] ?? 0,
                    'manager_name' => $managerName,
                    'room_id' => $data['roomId'],
                    'room_name' => $room->name,
                    'appointment_date' => $data['appointmentDate'],
                    'start_time' => $data['startTime'],
                    'end_time' => $data['endTime'],
                    'appointment_type' => $data['appointmentType'] ?? 'point',
                    'status' => $data['status'] ?? 'pending',
                    'remark' => $data['remark'] ?? '',
                    'isDelete' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // 添加预约项目
                foreach ($data['appointmentItems'] as $item) {
                    DB::table('card_appointment_item')->insert([
                        'company_id' => $currentCompanyId,
                        'appointment_id' => $appointmentId,
                        'project_id' => $item['projectId'],
                        'project_name' => $item['projectName'],
                        'price' => $item['price'],
                        'quantity' => $item['quantity'] ?? 1,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
                
                // 提交事务
                DB::commit();
                
                return json(['code' => 200, 'message' => '添加预约成功', 'data' => ['appointmentId' => $appointmentId, 'appointmentNo' => $appointmentNo]]);
            } catch (\Exception $e) {
                // 回滚事务
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add appointment error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加预约失败，请稍后重试']);
        }
    }
    
    /**
     * 更新预约
     * @param Request $request 请求对象
     * @param int $id 预约ID
     * @return array 更新结果
     */
    public function updateAppointment(Request $request, $id)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $data = $request->post();
            
            // 检查预约是否存在
            $appointment = DB::table('card_appointment')->where('id', $id)->where('company_id', $currentCompanyId)->first();
            if (!$appointment) {
                return json(['code' => 404, 'message' => '预约不存在']);
            }
            
            // 开始事务
            DB::beginTransaction();
            
            try {
                // 获取客户信息
                $customerName = $appointment->customer_name;
                $customerPhone = $appointment->customer_phone;
                if ($data['customerId'] != $appointment->customer_id) {
                    $customer = DB::table('card_customer')->where('id', $data['customerId'])->where('company_id', $currentCompanyId)->first();
                    if (!$customer) {
                        return json(['code' => 404, 'message' => '客户不存在']);
                    }
                    $customerName = $customer->name;
                    $customerPhone = $customer->phone;
                }
                
                // 获取员工信息
                $employeeName = $appointment->employee_name;
                if ($data['employeeId'] != $appointment->employee_id) {
                    $employee = DB::table('card_employee')->where('id', $data['employeeId'])->where('company_id', $currentCompanyId)->first();
                    if (!$employee) {
                        return json(['code' => 404, 'message' => '员工不存在']);
                    }
                    $employeeName = $employee->name;
                }
                
                // 获取房间信息
                $roomName = $appointment->room_name;
                if ($data['roomId'] != $appointment->room_id) {
                    $room = DB::table('card_room')->where('id', $data['roomId'])->where('company_id', $currentCompanyId)->first();
                    if (!$room) {
                        return json(['code' => 404, 'message' => '房间不存在']);
                    }
                    $roomName = $room->name;
                }
                
                // 获取管理者信息
                $managerName = $appointment->manager_name;
                if (isset($data['managerId']) && $data['managerId'] != $appointment->manager_id) {
                    if ($data['managerId']) {
                        $manager = DB::table('card_employee')->where('id', $data['managerId'])->where('company_id', $currentCompanyId)->first();
                        if ($manager) {
                            $managerName = $manager->name;
                        }
                    } else {
                        $managerName = '';
                    }
                }
                
                // 检查时间冲突
                if ($data['appointmentDate'] != $appointment->appointment_date || 
                    $data['startTime'] != $appointment->start_time || 
                    $data['endTime'] != $appointment->end_time || 
                    $data['roomId'] != $appointment->room_id) {
                    $conflict = DB::table('card_appointment')
                        ->where('company_id', $currentCompanyId)
                        ->where('id', '!=', $id)
                        ->where('isDelete', 0)
                        ->where('status', '!=', 'cancelled')
                        ->where('room_id', $data['roomId'])
                        ->where('appointment_date', $data['appointmentDate'])
                        ->where(function($query) use ($data) {
                            $query->where(function($q) use ($data) {
                                $q->where('start_time', '<', $data['endTime'])
                                  ->where('end_time', '>', $data['startTime']);
                            });
                        })
                        ->first();
                    
                    if ($conflict) {
                        return json(['code' => 400, 'message' => '该时间段房间已被预约']);
                    }
                }
                
                // 更新预约
                DB::table('card_appointment')->where('id', $id)->update([
                    'customer_id' => $data['customerId'],
                    'customer_name' => $customerName,
                    'customer_phone' => $customerPhone,
                    'department_id' => $data['departmentId'] ?? 0,
                    'employee_id' => $data['employeeId'],
                    'employee_name' => $employeeName,
                    'manager_id' => $data['managerId'] ?? 0,
                    'manager_name' => $managerName,
                    'room_id' => $data['roomId'],
                    'room_name' => $roomName,
                    'appointment_date' => $data['appointmentDate'],
                    'start_time' => $data['startTime'],
                    'end_time' => $data['endTime'],
                    'appointment_type' => $data['appointmentType'] ?? 'point',
                    'status' => $data['status'] ?? 'pending',
                    'remark' => $data['remark'] ?? '',
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                
                // 更新预约项目
                if (isset($data['appointmentItems'])) {
                    // 删除原有项目
                    DB::table('card_appointment_item')->where('appointment_id', $id)->delete();
                    
                    // 添加新项目
                    foreach ($data['appointmentItems'] as $item) {
                        DB::table('card_appointment_item')->insert([
                            'company_id' => $currentCompanyId,
                            'appointment_id' => $id,
                            'project_id' => $item['projectId'],
                            'project_name' => $item['projectName'],
                            'price' => $item['price'],
                            'quantity' => $item['quantity'] ?? 1,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
                
                // 提交事务
                DB::commit();
                
                return json(['code' => 200, 'message' => '更新预约成功']);
            } catch (\Exception $e) {
                // 回滚事务
                DB::rollback();
                throw $e;
            }
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update appointment error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新预约失败，请稍后重试']);
        }
    }
    
    /**
     * 删除预约
     * @param Request $request 请求对象
     * @param int $id 预约ID
     * @return array 删除结果
     */
    public function deleteAppointment(Request $request, $id)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 检查预约是否存在
            $appointment = DB::table('card_appointment')->where('id', $id)->where('company_id', $currentCompanyId)->first();
            if (!$appointment) {
                return json(['code' => 404, 'message' => '预约不存在']);
            }
            
            // 软删除预约
            DB::table('card_appointment')->where('id', $id)->update([
                'isDelete' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            // 软删除预约项目
            DB::table('card_appointment_item')->where('appointment_id', $id)->update([
                'isDelete' => 1,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return json(['code' => 200, 'message' => '删除预约成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete appointment error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除预约失败，请稍后重试']);
        }
    }
    
    /**
     * 获取预约详情
     * @param Request $request 请求对象
     * @param int $id 预约ID
     * @return array 预约详情数据
     */
    public function getAppointmentDetail(Request $request, $id)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 获取预约信息
            $appointment = DB::table('card_appointment')->where('id', $id)->where('company_id', $currentCompanyId)->where('isDelete', 0)->first();
            if (!$appointment) {
                return json(['code' => 404, 'message' => '预约不存在']);
            }
            
            // 获取预约项目
            $appointmentItems = DB::table('card_appointment_item')
                ->where('appointment_id', $id)
                ->where('company_id', $currentCompanyId)
                ->where('isDelete', 0)
                ->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedAppointment = [
                'id' => $appointment->id,
                'appointmentNo' => $appointment->appointment_no,
                'customerId' => $appointment->customer_id,
                'customerName' => $appointment->customer_name,
                'customerPhone' => $appointment->customer_phone,
                'departmentId' => $appointment->department_id,
                'employeeId' => $appointment->employee_id,
                'employeeName' => $appointment->employee_name,
                'managerId' => $appointment->manager_id,
                'managerName' => $appointment->manager_name,
                'roomId' => $appointment->room_id,
                'roomName' => $appointment->room_name,
                'appointmentDate' => $appointment->appointment_date,
                'startTime' => $appointment->start_time,
                'endTime' => $appointment->end_time,
                'appointmentType' => $appointment->appointment_type,
                'status' => $appointment->status,
                'remark' => $appointment->remark,
                'createdAt' => $appointment->created_at,
                'updatedAt' => $appointment->updated_at,
                'appointmentItems' => $appointmentItems->map(function($item) {
                    return [
                        'id' => $item->id,
                        'projectId' => $item->project_id,
                        'projectName' => $item->project_name,
                        'price' => $item->price,
                        'quantity' => $item->quantity
                    ];
                })
            ];
            
            return json(['code' => 200, 'message' => '获取预约详情成功', 'data' => $formattedAppointment]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get appointment detail error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取预约详情失败，请稍后重试']);
        }
    }
    
    /**
     * 获取可用时间槽
     * @param Request $request 请求对象
     * @return array 可用时间槽数据
     */
    public function getAvailableTimeSlots(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $appointmentDate = $request->get('appointmentDate');
            $roomId = $request->get('roomId');
            
            if (!$appointmentDate || !$roomId) {
                return json(['code' => 400, 'message' => '请提供预约日期和房间ID']);
            }
            
            // 生成时间槽（半小时为单位）
            $timeSlots = [];
            $startHour = 9;
            $endHour = 21;
            
            for ($hour = $startHour; $hour < $endHour; $hour++) {
                for ($minute = 0; $minute < 60; $minute += 30) {
                    $startTime = sprintf('%02d:%02d', $hour, $minute);
                    $endMinute = $minute + 30;
                    $endHourAdjusted = $hour;
                    if ($endMinute >= 60) {
                        $endMinute -= 60;
                        $endHourAdjusted += 1;
                    }
                    $endTime = sprintf('%02d:%02d', $endHourAdjusted, $endMinute);
                    
                    // 检查是否已被预约
                    $isAvailable = true;
                    $conflict = DB::table('card_appointment')
                        ->where('company_id', $currentCompanyId)
                        ->where('isDelete', 0)
                        ->where('status', '!=', 'cancelled')
                        ->where('room_id', $roomId)
                        ->where('appointment_date', $appointmentDate)
                        ->where(function($query) use ($startTime, $endTime) {
                            $query->where(function($q) use ($startTime, $endTime) {
                                $q->where('start_time', '<', $endTime)
                                  ->where('end_time', '>', $startTime);
                            });
                        })
                        ->first();
                    
                    if ($conflict) {
                        $isAvailable = false;
                    }
                    
                    $timeSlots[] = [
                        'startTime' => $startTime,
                        'endTime' => $endTime,
                        'isAvailable' => $isAvailable
                    ];
                }
            }
            
            return json(['code' => 200, 'message' => '获取可用时间槽成功', 'data' => $timeSlots]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get available time slots error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取可用时间槽失败，请稍后重试']);
        }
    }
    
    /**
     * 获取客户项目列表
     * @param Request $request 请求对象
     * @return array 客户项目列表数据
     */
    public function getCustomerProjects(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $customerId = $request->get('customerId');
            if (!$customerId) {
                return json(['code' => 400, 'message' => '请提供客户ID']);
            }
            
            // 获取客户已有项目
            $customerProjects = DB::table('card_customer_project')
                ->where('customer_id', $customerId)
                ->where('company_id', $currentCompanyId)
                ->where('isDelete', 0)
                ->where('remaining', '>', 0)
                ->join('card_project', 'card_customer_project.project_id', '=', 'card_project.id')
                ->select('card_project.id', 'card_project.name', 'card_project.price', 'card_project.duration', 'card_customer_project.remaining')
                ->get();
            
            // 获取所有项目
            $allProjects = DB::table('card_project')
                ->where('company_id', $currentCompanyId)
                ->where('isDelete', 0)
                ->select('id', 'name', 'price', 'duration')
                ->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCustomerProjects = $customerProjects->map(function($project) {
                return [
                    'projectId' => $project->id,
                    'projectName' => $project->name,
                    'price' => $project->price,
                    'duration' => $project->duration,
                    'remaining' => $project->remaining
                ];
            });
            
            $formattedAllProjects = $allProjects->map(function($project) {
                return [
                    'projectId' => $project->id,
                    'projectName' => $project->name,
                    'price' => $project->price,
                    'duration' => $project->duration
                ];
            });
            
            return json([
                'code' => 200, 
                'message' => '获取客户项目列表成功', 
                'data' => [
                    'customerProjects' => $formattedCustomerProjects,
                    'allProjects' => $formattedAllProjects
                ]
            ]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get customer projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取客户项目列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取部门列表
     * @param Request $request 请求对象
     * @return array 部门列表数据
     */
    public function getDepartments(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 获取部门列表
            $departments = DB::table('card_department')
                ->where('company_id', $currentCompanyId)
                ->where('isDelete', 0)
                ->orderBy('sort', 'asc')
                ->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedDepartments = $departments->map(function($department) {
                return [
                    'id' => $department->id,
                    'name' => $department->name,
                    'description' => $department->description,
                    'sort' => $department->sort
                ];
            });
            
            return json(['code' => 200, 'message' => '获取部门列表成功', 'data' => $formattedDepartments]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get departments error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取部门列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取客户列表
     * @param Request $request 请求对象
     * @return array 客户列表数据
     */
    public function getCustomers(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 构建查询
            $query = DB::table('card_customer')->where('company_id', $currentCompanyId)->where('isDelete', 0);
            
            // 应用搜索条件
            $keyword = $request->get('keyword');
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('phone', 'like', '%' . $keyword . '%')
                      ->orWhere('member_id', 'like', '%' . $keyword . '%');
                });
            }
            
            // 执行查询
            $customers = $query->orderBy('created_at', 'desc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCustomers = $customers->map(function($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'memberId' => $customer->member_id,
                    'memberLevel' => $customer->member_level,
                    'balance' => $customer->balance,
                    'points' => $customer->points
                ];
            });
            
            return json(['code' => 200, 'message' => '获取客户列表成功', 'data' => $formattedCustomers]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get customers error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取客户列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取员工列表
     * @param Request $request 请求对象
     * @return array 员工列表数据
     */
    public function getEmployees(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 构建查询
            $query = DB::table('card_employee')->where('company_id', $currentCompanyId)->where('isDelete', 0);
            
            // 应用搜索条件
            $departmentId = $request->get('departmentId');
            $storeId = $request->get('storeId');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
            
            // 执行查询
            $employees = $query->orderBy('created_at', 'desc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedEmployees = $employees->map(function($employee) {
                return [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'position' => $employee->position,
                    'departmentId' => $employee->department_id,
                    'storeId' => $employee->store_id,
                    'level' => $employee->level
                ];
            });
            
            return json(['code' => 200, 'message' => '获取员工列表成功', 'data' => $formattedEmployees]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get employees error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取员工列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取房间列表
     * @param Request $request 请求对象
     * @return array 房间列表数据
     */
    public function getRooms(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 构建查询
            $query = DB::table('card_room')->where('company_id', $currentCompanyId)->where('isDelete', 0);
            
            // 应用搜索条件
            $storeId = $request->get('storeId');
            if ($storeId) {
                $query->where('store_id', $storeId);
            }
            
            // 执行查询
            $rooms = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedRooms = $rooms->map(function($room) {
                return [
                    'id' => $room->id,
                    'storeId' => $room->store_id,
                    'name' => $room->name,
                    'type' => $room->type,
                    'status' => $room->status
                ];
            });
            
            return json(['code' => 200, 'message' => '获取房间列表成功', 'data' => $formattedRooms]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get rooms error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取房间列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取项目列表
     * @param Request $request 请求对象
     * @return array 项目列表数据
     */
    public function getProjects(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            // 构建查询
            $query = DB::table('card_project')->where('company_id', $currentCompanyId)->where('isDelete', 0);
            
            // 应用搜索条件
            $categoryId = $request->get('categoryId');
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            // 执行查询
            $projects = $query->orderBy('created_at', 'desc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProjects = $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'name' => $project->name,
                    'description' => $project->description,
                    'price' => $project->price,
                    'duration' => $project->duration,
                    'categoryId' => $project->category_id
                ];
            });
            
            return json(['code' => 200, 'message' => '获取项目列表成功', 'data' => $formattedProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取项目列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取员工可服务项目
     * @param Request $request 请求对象
     * @return array 员工可服务项目数据
     */
    public function getEmployeeProjects(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $employeeId = $request->get('employeeId');
            if (!$employeeId) {
                return json(['code' => 400, 'message' => '请提供员工ID']);
            }
            
            // 获取员工可服务项目
            $employeeProjects = DB::table('card_employee_project')
                ->where('employee_id', $employeeId)
                ->where('company_id', $currentCompanyId)
                ->where('isDelete', 0)
                ->join('card_project', 'card_employee_project.project_id', '=', 'card_project.id')
                ->select('card_project.id', 'card_project.name', 'card_project.price', 'card_project.duration')
                ->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedEmployeeProjects = $employeeProjects->map(function($project) {
                return [
                    'projectId' => $project->id,
                    'projectName' => $project->name,
                    'price' => $project->price,
                    'duration' => $project->duration
                ];
            });
            
            return json(['code' => 200, 'message' => '获取员工可服务项目成功', 'data' => $formattedEmployeeProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get employee projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取员工可服务项目失败，请稍后重试']);
        }
    }
    
    /**
     * 更新预约状态
     * @param Request $request 请求对象
     * @param int $id 预约ID
     * @return array 更新结果
     */
    public function updateAppointmentStatus(Request $request, $id)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            if (!$currentCompanyId) {
                return json(['code' => 400, 'message' => '未获取到公司信息']);
            }
            
            $data = $request->post();
            $status = $data['status'];
            
            if (!$status) {
                return json(['code' => 400, 'message' => '请提供状态']);
            }
            
            // 检查预约是否存在
            $appointment = DB::table('card_appointment')->where('id', $id)->where('company_id', $currentCompanyId)->first();
            if (!$appointment) {
                return json(['code' => 404, 'message' => '预约不存在']);
            }
            
            // 更新状态
            DB::table('card_appointment')->where('id', $id)->update([
                'status' => $status,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return json(['code' => 200, 'message' => '更新预约状态成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update appointment status error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新预约状态失败，请稍后重试']);
        }
    }
}
