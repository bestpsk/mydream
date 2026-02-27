<?php

namespace app\controller;

use app\model\Menu;
use support\Request;
use support\DB;

class CardItemController
{
    /**
     * 项目管理
     * 获取供应商列表
     * @param Request $request 请求对象
     * @return array 供应商列表数据
     */
    public function getSuppliers(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_supplier')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的供应商
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            $supplierName = $request->get('supplierName');
            $contact = $request->get('contact');
            if ($supplierName) {
                $query->where('supplier_name', 'like', '%' . $supplierName . '%');
            }
            if ($contact) {
                $query->where('contact', 'like', '%' . $contact . '%');
            }
            
            // 执行查询
            $suppliers = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedSuppliers = $suppliers->map(function($supplier) {
                return [
                    'id' => $supplier->id,
                    'supplierName' => $supplier->supplier_name,
                    'contact' => $supplier->contact,
                    'phone' => $supplier->phone,
                    'address' => $supplier->address,
                    'bank' => $supplier->bank,
                    'bankCard' => $supplier->bank_card,
                    'email' => $supplier->email,
                    'prepayBalance' => $supplier->prepay_balance,
                    'deliveryBalance' => $supplier->delivery_balance,
                    'createTime' => $supplier->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取供应商列表成功', 'data' => $formattedSuppliers]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get suppliers error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取供应商列表失败，请稍后重试']);
        }
    }
    
    /**
     * 添加供应商
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addSupplier(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['supplierName'])) {
                return json(['code' => 400, 'message' => '供应商名称不能为空']);
            }
            
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'supplier_name' => $data['supplierName'] ?? '',
                'contact' => $data['contact'] ?? '',
                'phone' => $data['phone'] ?? '',
                'address' => $data['address'] ?? '',
                'bank' => $data['bank'] ?? '',
                'bank_card' => $data['bankCard'] ?? '',
                'email' => $data['email'] ?? '',
                'prepay_balance' => $data['prepayBalance'] ?? 0,
                'delivery_balance' => $data['deliveryBalance'] ?? 0,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_supplier')->insertGetId($dbData);
            return json(['code' => 200, 'message' => '添加供应商成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add supplier error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加供应商失败，请稍后重试']);
        }
    }
    
    /**
     * 更新供应商
     * @param Request $request 请求对象
     * @param int $id 供应商ID
     * @return array 更新结果
     */
    public function updateSupplier(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查供应商是否存在
            $supplier = DB::table('card_supplier')->where('id', $id)->first();
            if (!$supplier) {
                return json(['code' => 404, 'message' => '供应商不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'supplier_name' => $data['supplierName'] ?? $supplier->supplier_name,
                'contact' => $data['contact'] ?? $supplier->contact,
                'phone' => $data['phone'] ?? $supplier->phone,
                'address' => $data['address'] ?? $supplier->address,
                'bank' => $data['bank'] ?? $supplier->bank,
                'bank_card' => $data['bankCard'] ?? $supplier->bank_card,
                'email' => $data['email'] ?? $supplier->email,
                'prepay_balance' => $data['prepayBalance'] ?? $supplier->prepay_balance,
                'delivery_balance' => $data['deliveryBalance'] ?? $supplier->delivery_balance,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_supplier')->where('id', $id)->update($dbData);
            return json(['code' => 200, 'message' => '更新供应商成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update supplier error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新供应商失败，请稍后重试']);
        }
    }
    
    /**
     * 删除供应商
     * @param Request $request 请求对象
     * @param int $id 供应商ID
     * @return array 删除结果
     */
    public function deleteSupplier(Request $request, $id)
    {
        try {
            // 检查供应商是否存在
            $supplier = DB::table('card_supplier')->where('id', $id)->first();
            if (!$supplier) {
                return json(['code' => 404, 'message' => '供应商不存在']);
            }
            
            // 软删除供应商
            DB::table('card_supplier')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除供应商成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete supplier error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除供应商失败，请稍后重试']);
        }
    }
    
    /**
     * 获取项目分类列表
     * @param Request $request 请求对象
     * @return array 项目分类列表数据
     */
    public function getCategories(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_project_category')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的项目分类
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            $categoryName = $request->get('categoryName');
            $departmentId = $request->get('departmentId');
            if ($categoryName) {
                $query->where('category_name', 'like', '%' . $categoryName . '%');
            }
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            
            // 执行查询
            $categories = $query->orderBy('sort', 'asc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCategories = $categories->map(function($category) {
                return [
                    'id' => $category->id,
                    'categoryName' => $category->category_name,
                    'departmentId' => $category->department_id,
                    'parentId' => $category->parent_id,
                    'sort' => $category->sort,
                    'createTime' => $category->created_at
                ];
            });
            
            // 转换为树形结构
            $treeCategories = $this->buildCategoryTree($formattedCategories);
            
            return json(['code' => 200, 'message' => '获取项目分类列表成功', 'data' => $treeCategories]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get categories error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取项目分类列表失败，请稍后重试']);
        }
    }
    
    /**
     * 构建分类树形结构
     * @param array $categories 分类列表
     * @return array 树形结构分类列表
     */
    private function buildCategoryTree($categories)
    {
        $categoryMap = [];
        $tree = [];
        
        // 构建分类映射
        foreach ($categories as $category) {
            $categoryMap[$category['id']] = $category;
            $categoryMap[$category['id']]['children'] = [];
        }
        
        // 构建树形结构
        foreach ($categories as $category) {
            if ($category['parentId'] == 0) {
                // 一级分类
                $tree[] = $categoryMap[$category['id']];
            } else {
                // 二级分类
                if (isset($categoryMap[$category['parentId']])) {
                    $categoryMap[$category['parentId']]['children'][] = $categoryMap[$category['id']];
                }
            }
        }
        
        return $tree;
    }
    
    /**
     * 添加项目分类
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addCategory(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['categoryName'])) {
                return json(['code' => 400, 'message' => '分类名称不能为空']);
            }
            
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'category_name' => $data['categoryName'] ?? '',
                'department_id' => $data['departmentId'] ?? null,
                'parent_id' => 0, // 固定为一级分类
                'sort' => $data['sort'] ?? 0,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_project_category')->insertGetId($dbData);
            return json(['code' => 200, 'message' => '添加项目分类成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加项目分类失败，请稍后重试']);
        }
    }
    
    /**
     * 更新项目分类
     * @param Request $request 请求对象
     * @param int $id 项目分类ID
     * @return array 更新结果
     */
    public function updateCategory(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查项目分类是否存在
            $category = DB::table('card_project_category')->where('id', $id)->first();
            if (!$category) {
                return json(['code' => 404, 'message' => '项目分类不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'category_name' => $data['categoryName'] ?? $category->category_name,
                'department_id' => $data['departmentId'] ?? $category->department_id,
                'parent_id' => 0, // 固定为一级分类
                'sort' => $data['sort'] ?? $category->sort,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_project_category')->where('id', $id)->update($dbData);
            return json(['code' => 200, 'message' => '更新项目分类成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新项目分类失败，请稍后重试']);
        }
    }
    
    /**
     * 删除项目分类
     * @param Request $request 请求对象
     * @param int $id 项目分类ID
     * @return array 删除结果
     */
    public function deleteCategory(Request $request, $id)
    {
        try {
            // 检查项目分类是否存在
            $category = DB::table('card_project_category')->where('id', $id)->first();
            if (!$category) {
                return json(['code' => 404, 'message' => '项目分类不存在']);
            }
            
            // 软删除项目分类
            DB::table('card_project_category')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除项目分类成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除项目分类失败，请稍后重试']);
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
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_project')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的项目
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            $projectName = $request->get('projectName');
            $categoryId = $request->get('categoryId');
            $status = $request->get('status');
            
            if ($projectName) {
                $query->where('project_name', 'like', '%' . $projectName . '%');
            }
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            if ($status !== null) {
                $query->where('status', $status);
            }
            
            // 执行查询
            $projects = $query->get();
            
            // 获取所有供应商信息
            $suppliers = DB::table('card_supplier')->where('isDelete', 0)->get();
            $supplierMap = [];
            foreach ($suppliers as $supplier) {
                $supplierMap[$supplier->id] = $supplier->supplier_name;
            }
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProjects = $projects->map(function($project) use ($supplierMap) {
                return [
                    'id' => $project->id,
                    'projectName' => $project->project_name,
                    'projectCode' => $project->project_code ?? '',
                    'categoryId' => $project->category_id,
                    'originalPrice' => $project->original_price,
                    'singleSalePrice' => $project->single_sale_price,
                    'experiencePrice' => $project->experience_price,
                    'externalDisplayName' => $project->external_display_name,
                    'supplierId' => $project->supplier_id,
                    'supplierName' => isset($supplierMap[$project->supplier_id]) ? $supplierMap[$project->supplier_id] : '',
                    'projectType' => $project->project_type,
                    'monthlyLimit' => $project->monthly_limit,
                    'consumptionInterval' => $project->consumption_interval,
                    'workHours' => $project->work_hours,
                    'serviceTime' => $project->service_time,
                    'status' => $project->status,
                    'remark' => $project->remark,
                    'createTime' => $project->created_at
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
     * 添加项目
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addProject(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['projectName'])) {
                return json(['code' => 400, 'message' => '项目名称不能为空']);
            }
            if (empty($data['categoryId'])) {
                return json(['code' => 400, 'message' => '所属分类不能为空']);
            }
            
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'project_name' => $data['projectName'] ?? '',
                'project_code' => $data['projectCode'] ?? '',
                'category_id' => $data['categoryId'] ?? 0,
                'original_price' => $data['originalPrice'] ?? 0,
                'single_sale_price' => $data['singleSalePrice'] ?? 0,
                'experience_price' => $data['experiencePrice'] ?? 0,
                'external_display_name' => $data['externalDisplayName'] ?? null,
                'supplier_id' => $data['supplierId'] ?? 0,
                'project_type' => $data['projectType'] ?? null,
                'monthly_limit' => $data['monthlyLimit'] ?? 0,
                'consumption_interval' => $data['consumptionInterval'] ?? 0,
                'work_hours' => $data['workHours'] ?? 0,
                'service_time' => $data['serviceTime'] ?? 0,
                'status' => $data['status'] ?? 1,
                'reminder_type' => $data['reminderType'] ?? 0,
                'reminder_days' => $data['reminderDays'] ?? 0,
                'reminder_date' => $data['reminderDate'] ?? null,
                'reminder_repeat' => $data['reminderRepeat'] ?? 0,

                'no_recharge_discount' => $data['noRechargeDiscount'] ?? 0,
                'no_project_times' => $data['noProjectTimes'] ?? 0,
                'no_consumption' => $data['noConsumption'] ?? 0,
                'no_consumption_notification' => $data['noConsumptionNotification'] ?? 0,
                'mini_program_bookable' => $data['miniProgramBookable'] ?? 0,
                'limited_sale_stores' => $data['limitedSaleStores'] ?? null,
                'limited_service_stores' => $data['limitedServiceStores'] ?? null,
                'allow_gift' => $data['allowGift'] ?? 0,
                'remark' => $data['remark'] ?? null,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_project')->insertGetId($dbData);
            
            // 处理项目配料单
            if (!empty($data['ingredients'])) {
                foreach ($data['ingredients'] as $ingredient) {
                    DB::table('card_project_ingredient')->insert([
                        'company_id' => $data['companyId'] ?? $currentCompanyId,
                        'project_id' => $id,
                        'product' => $ingredient['product'] ?? '',
                        'type' => $ingredient['type'] ?? null,
                        'quantity' => $ingredient['quantity'] ?? 0,
                        'unit' => $ingredient['unit'] ?? null,
                        'remark' => $ingredient['remark'] ?? null,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            // 处理子项目配置
            if (!empty($data['subProjects'])) {
                foreach ($data['subProjects'] as $subProject) {
                    DB::table('card_project_sub')->insert([
                        'company_id' => $data['companyId'] ?? $currentCompanyId,
                        'project_id' => $id,
                        'sub_project_id' => $subProject['subProjectId'] ?? 0,
                        'consumption_ratio' => $subProject['consumptionRatio'] ?? 0,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            return json(['code' => 200, 'message' => '添加项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加项目失败，请稍后重试']);
        }
    }
    
    /**
     * 更新项目
     * @param Request $request 请求对象
     * @param int $id 项目ID
     * @return array 更新结果
     */
    public function updateProject(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查项目是否存在
            $project = DB::table('card_project')->where('id', $id)->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'project_name' => $data['projectName'] ?? $project->project_name,
                'project_code' => $data['projectCode'] ?? $project->project_code,
                'category_id' => $data['categoryId'] ?? $project->category_id,
                'original_price' => $data['originalPrice'] ?? $project->original_price,
                'single_sale_price' => $data['singleSalePrice'] ?? $project->single_sale_price,
                'experience_price' => $data['experiencePrice'] ?? $project->experience_price,
                'external_display_name' => $data['externalDisplayName'] ?? $project->external_display_name,
                'supplier_id' => $data['supplierId'] ?? $project->supplier_id,
                'project_type' => $data['projectType'] ?? $project->project_type,
                'monthly_limit' => $data['monthlyLimit'] ?? $project->monthly_limit,
                'consumption_interval' => $data['consumptionInterval'] ?? $project->consumption_interval,
                'work_hours' => $data['workHours'] ?? $project->work_hours,
                'service_time' => $data['serviceTime'] ?? $project->service_time,
                'status' => $data['status'] ?? $project->status,
                'reminder_type' => $data['reminderType'] ?? $project->reminder_type,
                'reminder_days' => $data['reminderDays'] ?? $project->reminder_days,
                'reminder_date' => $data['reminderDate'] ?? $project->reminder_date,
                'reminder_repeat' => $data['reminderRepeat'] ?? $project->reminder_repeat,

                'no_recharge_discount' => $data['noRechargeDiscount'] ?? $project->no_recharge_discount,
                'no_project_times' => $data['noProjectTimes'] ?? $project->no_project_times,
                'no_consumption' => $data['noConsumption'] ?? $project->no_consumption,
                'no_consumption_notification' => $data['noConsumptionNotification'] ?? $project->no_consumption_notification,
                'mini_program_bookable' => $data['miniProgramBookable'] ?? $project->mini_program_bookable,
                'limited_sale_stores' => $data['limitedSaleStores'] ?? $project->limited_sale_stores,
                'limited_service_stores' => $data['limitedServiceStores'] ?? $project->limited_service_stores,

                'allow_gift' => $data['allowGift'] ?? $project->allow_gift,
                'remark' => $data['remark'] ?? $project->remark,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_project')->where('id', $id)->update($dbData);
            
            // 处理项目配料单
            if (isset($data['ingredients'])) {
                // 删除旧的配料单
                DB::table('card_project_ingredient')->where('project_id', $id)->update(['isDelete' => 1]);
                
                // 添加新的配料单
                if (!empty($data['ingredients'])) {
                    $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
                    foreach ($data['ingredients'] as $ingredient) {
                        DB::table('card_project_ingredient')->insert([
                            'company_id' => $data['companyId'] ?? $currentCompanyId,
                            'project_id' => $id,
                            'product' => $ingredient['product'] ?? '',
                            'type' => $ingredient['type'] ?? null,
                            'quantity' => $ingredient['quantity'] ?? 0,
                            'unit' => $ingredient['unit'] ?? null,
                            'remark' => $ingredient['remark'] ?? null,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            // 处理子项目配置
            if (isset($data['subProjects'])) {
                // 删除旧的子项目配置
                DB::table('card_project_sub')->where('project_id', $id)->update(['isDelete' => 1]);
                
                // 添加新的子项目配置
                if (!empty($data['subProjects'])) {
                    $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
                    foreach ($data['subProjects'] as $subProject) {
                        DB::table('card_project_sub')->insert([
                            'company_id' => $data['companyId'] ?? $currentCompanyId,
                            'project_id' => $id,
                            'sub_project_id' => $subProject['subProjectId'] ?? 0,
                            'consumption_ratio' => $subProject['consumptionRatio'] ?? 0,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            return json(['code' => 200, 'message' => '更新项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新项目失败，请稍后重试']);
        }
    }
    
    /**
     * 删除项目
     * @param Request $request 请求对象
     * @param int $id 项目ID
     * @return array 删除结果
     */
    public function deleteProject(Request $request, $id)
    {
        try {
            // 检查项目是否存在
            $project = DB::table('card_project')->where('id', $id)->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 软删除项目
            DB::table('card_project')->where('id', $id)->update(['isDelete' => 1]);
            
            // 软删除相关的配料单和子项目
            DB::table('card_project_ingredient')->where('project_id', $id)->update(['isDelete' => 1]);
            DB::table('card_project_sub')->where('project_id', $id)->update(['isDelete' => 1]);
            
            return json(['code' => 200, 'message' => '删除项目成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除项目失败，请稍后重试']);
        }
    }
    
    /**
     * 获取项目配料单
     * @param Request $request 请求对象
     * @param int $projectId 项目ID
     * @return array 项目配料单数据
     */
    public function getProjectIngredients(Request $request, $projectId)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_project_ingredient')->where('isDelete', 0)->where('project_id', $projectId);
            
            // 非超级管理员只能查看自己公司的配料单
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $ingredients = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedIngredients = $ingredients->map(function($ingredient) {
                return [
                    'id' => $ingredient->id,
                    'projectId' => $ingredient->project_id,
                    'product' => $ingredient->product,
                    'type' => $ingredient->type,
                    'quantity' => $ingredient->quantity,
                    'unit' => $ingredient->unit,
                    'remark' => $ingredient->remark,
                    'createTime' => $ingredient->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取项目配料单成功', 'data' => $formattedIngredients]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get project ingredients error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取项目配料单失败，请稍后重试']);
        }
    }
    
    /**
     * 获取项目子项目配置
     * @param Request $request 请求对象
     * @param int $projectId 项目ID
     * @return array 项目子项目配置数据
     */
    public function getProjectSubProjects(Request $request, $projectId)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_project_sub')->where('isDelete', 0)->where('project_id', $projectId);
            
            // 非超级管理员只能查看自己公司的子项目配置
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $subProjects = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedSubProjects = $subProjects->map(function($subProject) {
                // 获取子项目名称
                $subProjectInfo = DB::table('card_project')->where('id', $subProject->sub_project_id)->first();
                return [
                    'id' => $subProject->id,
                    'projectId' => $subProject->project_id,
                    'subProjectId' => $subProject->sub_project_id,
                    'subProjectName' => $subProjectInfo ? $subProjectInfo->project_name : '',
                    'consumptionRatio' => $subProject->consumption_ratio,
                    'createTime' => $subProject->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取项目子项目配置成功', 'data' => $formattedSubProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get project sub projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取项目子项目配置失败，请稍后重试']);
        }
    }
    
    /**
     * 按拼音首字母搜索项目
     * @param Request $request 请求对象
     * @return array 搜索结果
     */
    public function searchProjectsByPinyin(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 获取搜索参数
            $keyword = $request->get('keyword', '');
            
            // 构建查询
            $query = DB::table('card_project')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的项目
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            if ($keyword) {
                // 这里可以使用拼音首字母搜索，需要根据实际的拼音处理方案来实现
                // 暂时使用普通的模糊搜索
                $query->where('project_name', 'like', '%' . $keyword . '%');
            }
            
            // 执行查询
            $projects = $query->limit(20)->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProjects = $projects->map(function($project) {
                return [
                    'id' => $project->id,
                    'projectName' => $project->project_name,
                    'categoryId' => $project->category_id
                ];
            });
            return json(['code' => 200, 'message' => '搜索项目成功', 'data' => $formattedProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Search projects by pinyin error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '搜索项目失败，请稍后重试']);
        }
    }
    
    /**
     * 卡项管理
     * 获取充值卡列表
     * @param Request $request 请求对象
     * @return array 充值卡列表数据
     */
    public function getRechargeCards(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_recharge')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的充值卡
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            $keyword = $request->get('keyword');
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('card_name', 'like', '%' . $keyword . '%')
                      ->orWhere('card_code', 'like', '%' . $keyword . '%');
                });
            }
            
            // 执行查询
            $cards = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCards = $cards->map(function($card) {
                return [
                    'id' => $card->id,
                    'cardName' => $card->card_name,
                    'cardValue' => $card->card_value,
                    'salePrice' => $card->sale_price,
                    'createTime' => $card->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取充值卡列表成功', 'data' => $formattedCards]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get recharge cards error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取充值卡列表失败，请稍后重试']);
        }
    }
    
    /**
     * 添加充值卡
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addRechargeCard(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            if (empty($data['cardName'])) {
                return json(['code' => 400, 'message' => '卡名称不能为空']);
            }
            if (empty($data['cardValue'])) {
                return json(['code' => 400, 'message' => '卡面值不能为空']);
            }
            if (empty($data['salePrice'])) {
                return json(['code' => 400, 'message' => '销售价格不能为空']);
            }
            
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'card_name' => $data['cardName'] ?? '',
                'card_value' => $data['cardValue'] ?? 0,
                'sale_price' => $data['salePrice'] ?? 0,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_recharge')->insertGetId($dbData);
            return json(['code' => 200, 'message' => '添加充值卡成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add recharge card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加充值卡失败，请稍后重试']);
        }
    }
    
    /**
     * 更新充值卡
     * @param Request $request 请求对象
     * @param int $id 充值卡ID
     * @return array 更新结果
     */
    public function updateRechargeCard(Request $request, $id)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            $card = DB::table('card_recharge')->where('id', $id)->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'card_name' => $data['cardName'] ?? $card->card_name,
                'card_value' => $data['cardValue'] ?? $card->card_value,
                'sale_price' => $data['salePrice'] ?? $card->sale_price,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_recharge')->where('id', $id)->update($dbData);
            return json(['code' => 200, 'message' => '更新充值卡成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update recharge card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新充值卡失败，请稍后重试']);
        }
    }
    
    /**
     * 删除充值卡
     * @param Request $request 请求对象
     * @param int $id 充值卡ID
     * @return array 删除结果
     */
    public function deleteRechargeCard(Request $request, $id)
    {
        try {
            // 检查充值卡是否存在
            $card = DB::table('card_recharge')->where('id', $id)->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 软删除充值卡
            DB::table('card_recharge')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除充值卡成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete recharge card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除充值卡失败，请稍后重试']);
        }
    }
    

    /**
     * 获取套餐卡列表
     * @param Request $request 请求对象
     * @return array 套餐卡列表数据
     */
    public function getPackageCards(Request $request)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_package')->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的套餐卡
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 应用搜索条件
            $cardName = $request->get('cardName');
            if ($cardName) {
                $query->where('card_name', 'like', '%' . $cardName . '%');
            }
            
            // 执行查询
            $cards = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCards = $cards->map(function($card) {
                return [
                    'id' => $card->id,
                    'cardName' => $card->card_name,
                    'cardCode' => $card->card_code,
                    'originalPrice' => $card->original_price,
                    'price' => $card->price,
                    'projectCount' => $card->project_count,
                    'description' => $card->description,
                    'remark' => $card->remark,
                    'isModifiable' => $card->is_modifiable,
                    'isAddNewItemForbidden' => $card->is_add_new_item_forbidden,
                    'isLimitOnce' => $card->is_limit_once,
                    'isExpireInvalid' => $card->is_expire_invalid,
                    'isGiftForbidden' => $card->is_gift_forbidden,
                    'onlineTime' => $card->online_time,
                    'offlineTime' => $card->offline_time,
                    'expireType' => $card->expire_type,
                    'expireDate' => $card->expire_date,
                    'expireMonths' => $card->expire_months,
                    'saleStoreIds' => $card->sale_store_ids ? json_decode($card->sale_store_ids) : [],
                    'consumeStoreIds' => $card->consume_store_ids ? json_decode($card->consume_store_ids) : [],
                    'saleDepartmentIds' => $card->sale_department_ids ? json_decode($card->sale_department_ids) : [],
                    'consumeDepartmentIds' => $card->consume_department_ids ? json_decode($card->consume_department_ids) : [],
                    'createTime' => $card->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取套餐卡列表成功', 'data' => $formattedCards]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get package cards error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取套餐卡列表失败，请稍后重试']);
        }
    }
    
    /**
     * 添加套餐卡
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addPackageCard(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            $projectCount = isset($data['giftProjects']) ? count($data['giftProjects']) : ($data['projectCount'] ?? 0);
            
            if (empty($data['cardName'])) {
                \support\Log::warning('addPackageCard: cardName is empty');
                return json(['code' => 400, 'message' => '卡名称不能为空']);
            }
            if (empty($data['price'])) {
                \support\Log::warning('addPackageCard: price is empty');
                return json(['code' => 400, 'message' => '价格不能为空']);
            }
            
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $formatDateTime = function($value) {
                if (empty($value)) return null;
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
                }
                return $value;
            };
            
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'card_name' => $data['cardName'] ?? '',
                'card_code' => $data['cardCode'] ?? '',
                'original_price' => $data['originalPrice'] ?? 0,
                'price' => $data['price'] ?? 0,
                'project_count' => $projectCount,
                'description' => $data['description'] ?? '',
                'remark' => $data['remark'] ?? '',
                'is_modifiable' => $data['isModifiable'] ?? 0,
                'is_add_new_item_forbidden' => $data['isAddItemDisabled'] ?? 0,
                'is_limit_once' => $data['isLimitOnce'] ?? 0,
                'is_expire_invalid' => $data['isExpireInvalid'] ?? 0,
                'is_gift_forbidden' => $data['isProhibitGift'] ?? 0,
                'online_time' => $formatDateTime($data['onlineTime'] ?? null),
                'offline_time' => $formatDateTime($data['offlineTime'] ?? null),
                'expire_type' => $data['expireType'] ?? 1,
                'expire_date' => $formatDateTime($data['expireDate'] ?? null),
                'expire_months' => $data['expireMonths'] ?? 12,
                'sale_store_ids' => isset($data['saleStoreIds']) ? json_encode($data['saleStoreIds']) : null,
                'consume_store_ids' => isset($data['consumeStoreIds']) ? json_encode($data['consumeStoreIds']) : null,
                'sale_department_ids' => isset($data['saleDepartmentIds']) ? json_encode($data['saleDepartmentIds']) : null,
                'consume_department_ids' => isset($data['consumeDepartmentIds']) ? json_encode($data['consumeDepartmentIds']) : null,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_package')->insertGetId($dbData);
            
            if (!empty($data['giftProjects'])) {
                foreach ($data['giftProjects'] as $project) {
                    $projectData = [
                        'company_id' => $currentCompanyId,
                        'package_id' => $id,
                        'project_id' => $project['projectId'] ?? 0,
                        'times' => $project['times'] ?? 1,
                        'unit_price' => $project['unitPrice'] ?? 0,
                        'total_price' => $project['totalPrice'] ?? 0,
                        'consume' => $project['consume'] ?? 0,
                        'manual_salary' => $project['manualSalary'] ?? 0,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    DB::table('card_package_gift_project')->insert($projectData);
                }
            }
            
            if (!empty($data['giftProducts'])) {
                foreach ($data['giftProducts'] as $product) {
                    $productData = [
                        'company_id' => $currentCompanyId,
                        'package_id' => $id,
                        'product_id' => $product['productId'] ?? 0,
                        'times' => $product['times'] ?? 1,
                        'unit_price' => $product['unitPrice'] ?? 0,
                        'total_price' => $product['totalPrice'] ?? 0,
                        'consume' => $product['consume'] ?? 0,
                        'manual_salary' => $product['manualSalary'] ?? 0,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    DB::table('card_package_gift_product')->insert($productData);
                }
            }
            
            return json(['code' => 200, 'message' => '添加套餐卡成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            \support\Log::error('Add package card error: ' . $e->getMessage());
            \support\Log::error('Add package card trace: ' . $e->getTraceAsString());
            return json(['code' => 500, 'message' => '添加套餐卡失败，请稍后重试']);
        }
    }
    
    /**
     * 更新套餐卡
     * @param Request $request 请求对象
     * @param int $id 套餐卡ID
     * @return array 更新结果
     */
    public function updatePackageCard(Request $request, $id)
    {
        try {
            $data = $request->post();
            \support\Log::info('updatePackageCard POST data: ' . json_encode($data));
            if (empty($data)) {
                $rawBody = $request->rawBody();
                \support\Log::info('updatePackageCard Raw body: ' . $rawBody);
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                    \support\Log::info('updatePackageCard Decoded: ' . json_encode($data));
                }
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_package')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                \support\Log::warning('updatePackageCard: card not found, id=' . $id);
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            $projectCount = isset($data['giftProjects']) ? count($data['giftProjects']) : ($data['projectCount'] ?? $card->project_count);
            
            $formatDateTime = function($value, $default = null) {
                if (empty($value)) return $default;
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : $default;
                }
                return $value;
            };
            
            $dbData = [
                'card_name' => $data['cardName'] ?? $card->card_name,
                'card_code' => $data['cardCode'] ?? $card->card_code,
                'original_price' => $data['originalPrice'] ?? $card->original_price,
                'price' => $data['price'] ?? $card->price,
                'project_count' => $projectCount,
                'description' => $data['description'] ?? $card->description,
                'remark' => $data['remark'] ?? $card->remark,
                'is_modifiable' => $data['isModifiable'] ?? $card->is_modifiable,
                'is_add_new_item_forbidden' => $data['isAddItemDisabled'] ?? $card->is_add_new_item_forbidden,
                'is_limit_once' => $data['isLimitOnce'] ?? $card->is_limit_once,
                'is_expire_invalid' => $data['isExpireInvalid'] ?? $card->is_expire_invalid,
                'is_gift_forbidden' => $data['isProhibitGift'] ?? $card->is_gift_forbidden,
                'online_time' => $formatDateTime($data['onlineTime'] ?? null, $card->online_time),
                'offline_time' => $formatDateTime($data['offlineTime'] ?? null, $card->offline_time),
                'expire_type' => $data['expireType'] ?? $card->expire_type,
                'expire_date' => $formatDateTime($data['expireDate'] ?? null, $card->expire_date),
                'expire_months' => $data['expireMonths'] ?? $card->expire_months,
                'sale_store_ids' => isset($data['saleStoreIds']) ? json_encode($data['saleStoreIds']) : $card->sale_store_ids,
                'consume_store_ids' => isset($data['consumeStoreIds']) ? json_encode($data['consumeStoreIds']) : $card->consume_store_ids,
                'sale_department_ids' => isset($data['saleDepartmentIds']) ? json_encode($data['saleDepartmentIds']) : $card->sale_department_ids,
                'consume_department_ids' => isset($data['consumeDepartmentIds']) ? json_encode($data['consumeDepartmentIds']) : $card->consume_department_ids,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_package')->where('id', $id)->update($dbData);
            
            if (!empty($data['giftProjects'])) {
                DB::table('card_package_gift_project')
                    ->where('package_id', $id)
                    ->update(['isDelete' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                    
                foreach ($data['giftProjects'] as $project) {
                    if (!empty($project['id'])) {
                        DB::table('card_package_gift_project')
                            ->where('id', $project['id'])
                            ->update([
                                'project_id' => $project['projectId'] ?? 0,
                                'times' => $project['times'] ?? 1,
                                'unit_price' => $project['unitPrice'] ?? 0,
                                'total_price' => $project['totalPrice'] ?? 0,
                                'consume' => $project['consume'] ?? 0,
                                'manual_salary' => $project['manualSalary'] ?? 0,
                                'isDelete' => 0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        DB::table('card_package_gift_project')->insert([
                            'company_id' => $currentCompanyId,
                            'package_id' => $id,
                            'project_id' => $project['projectId'] ?? 0,
                            'times' => $project['times'] ?? 1,
                            'unit_price' => $project['unitPrice'] ?? 0,
                            'total_price' => $project['totalPrice'] ?? 0,
                            'consume' => $project['consume'] ?? 0,
                            'manual_salary' => $project['manualSalary'] ?? 0,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            if (!empty($data['giftProducts'])) {
                DB::table('card_package_gift_product')
                    ->where('package_id', $id)
                    ->update(['isDelete' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                    
                foreach ($data['giftProducts'] as $product) {
                    if (!empty($product['id'])) {
                        DB::table('card_package_gift_product')
                            ->where('id', $product['id'])
                            ->update([
                                'product_id' => $product['productId'] ?? 0,
                                'times' => $product['times'] ?? 1,
                                'unit_price' => $product['unitPrice'] ?? 0,
                                'total_price' => $product['totalPrice'] ?? 0,
                                'consume' => $product['consume'] ?? 0,
                                'manual_salary' => $product['manualSalary'] ?? 0,
                                'isDelete' => 0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        DB::table('card_package_gift_product')->insert([
                            'company_id' => $currentCompanyId,
                            'package_id' => $id,
                            'product_id' => $product['productId'] ?? 0,
                            'times' => $product['times'] ?? 1,
                            'unit_price' => $product['unitPrice'] ?? 0,
                            'total_price' => $product['totalPrice'] ?? 0,
                            'consume' => $product['consume'] ?? 0,
                            'manual_salary' => $product['manualSalary'] ?? 0,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            return json(['code' => 200, 'message' => '更新套餐卡成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update package card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新套餐卡失败，请稍后重试']);
        }
    }
    
    /**
     * 删除套餐卡
     * @param Request $request 请求对象
     * @param int $id 套餐卡ID
     * @return array 删除结果
     */
    public function deletePackageCard(Request $request, $id)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查套餐卡是否存在，并且确保只能删除自己公司的套餐卡
            $query = DB::table('card_package')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 软删除套餐卡
            DB::table('card_package')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除套餐卡成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete package card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除套餐卡失败，请稍后重试']);
        }
    }
    
    /**
     * 获取套餐卡详情
     * @param Request $request 请求对象
     * @param int $id 套餐卡ID
     * @return array 套餐卡详情数据
     */
    public function getPackageCardDetail(Request $request, $id)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询
            $query = DB::table('card_package')->where('id', $id)->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的套餐卡
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCard = [
                'id' => $card->id,
                'cardName' => $card->card_name,
                'cardCode' => $card->card_code,
                'originalPrice' => $card->original_price,
                'price' => $card->price,
                'projectCount' => $card->project_count,
                'description' => $card->description,
                'remark' => $card->remark,
                'isModifiable' => $card->is_modifiable,
                'isAddNewItemForbidden' => $card->is_add_new_item_forbidden,
                'isLimitOnce' => $card->is_limit_once,
                'isExpireInvalid' => $card->is_expire_invalid,
                'isGiftForbidden' => $card->is_gift_forbidden,
                'onlineTime' => $card->online_time,
                'offlineTime' => $card->offline_time,
                'expireType' => $card->expire_type,
                'expireDate' => $card->expire_date,
                'expireMonths' => $card->expire_months,
                'saleStoreIds' => $card->sale_store_ids ? json_decode($card->sale_store_ids) : [],
                'consumeStoreIds' => $card->consume_store_ids ? json_decode($card->consume_store_ids) : [],
                'saleDepartmentIds' => $card->sale_department_ids ? json_decode($card->sale_department_ids) : [],
                'consumeDepartmentIds' => $card->consume_department_ids ? json_decode($card->consume_department_ids) : [],
                'createTime' => $card->created_at
            ];
            
            $giftProjects = DB::table('card_package_gift_project')
                ->where('package_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            $giftProjectsList = [];
            foreach ($giftProjects as $project) {
                $projectName = '';
                if ($project->project_id) {
                    $projectInfo = DB::table('card_project')->where('id', $project->project_id)->first();
                    if ($projectInfo) {
                        $projectName = $projectInfo->project_name ?? '';
                    }
                }
                $giftProjectsList[] = [
                    'id' => $project->id,
                    'projectId' => $project->project_id,
                    'projectName' => $projectName,
                    'times' => $project->times,
                    'unitPrice' => $project->unit_price,
                    'totalPrice' => $project->total_price,
                    'consume' => $project->consume,
                    'manualSalary' => $project->manual_salary
                ];
            }
            
            $giftProducts = DB::table('card_package_gift_product')
                ->where('package_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            $giftProductsList = [];
            foreach ($giftProducts as $product) {
                $productName = '';
                if ($product->product_id) {
                    $productInfo = DB::table('card_product')->where('id', $product->product_id)->first();
                    if ($productInfo) {
                        $productName = $productInfo->product_name ?? '';
                    }
                }
                $giftProductsList[] = [
                    'id' => $product->id,
                    'productId' => $product->product_id,
                    'productName' => $productName,
                    'times' => $product->times,
                    'unitPrice' => $product->unit_price,
                    'totalPrice' => $product->total_price,
                    'consume' => $product->consume,
                    'manualSalary' => $product->manual_salary
                ];
            }
            
            $formattedCard['giftProjects'] = $giftProjectsList;
            $formattedCard['giftProducts'] = $giftProductsList;
            
            return json(['code' => 200, 'message' => '获取套餐卡详情成功', 'data' => $formattedCard]);
        } catch (\Exception $e) {
            \support\Log::error('Get package card detail error: ' . $e->getMessage());
            \support\Log::error('Get package card detail trace: ' . $e->getTraceAsString());
            return json(['code' => 500, 'message' => '获取套餐卡详情失败，请稍后重试']);
        }
    }
    
    /**
     * 获取套餐卡包含项目
     * @param Request $request 请求对象
     * @param int $packageId 套餐卡ID
     * @return array 套餐卡包含项目数据
     */
    public function getPackageCardGiftProjects(Request $request, $packageId)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查套餐卡是否存在，并且确保只能查看自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $packageId)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 构建查询
            $query = DB::table('card_package_gift_project')->where('package_id', $packageId)->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的项目
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $projects = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProjects = $projects->map(function($project) {
                // 获取项目名称
                $projectInfo = DB::table('card_project')->where('id', $project->project_id)->first();
                return [
                    'id' => $project->id,
                    'packageId' => $project->package_id,
                    'projectId' => $project->project_id,
                    'projectName' => $projectInfo ? $projectInfo->project_name : '',
                    'times' => $project->times,
                    'unitPrice' => $project->unit_price,
                    'totalPrice' => $project->total_price,
                    'consume' => $project->consume,
                    'manualSalary' => $project->manual_salary,
                    'createTime' => $project->created_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取套餐卡包含项目成功', 'data' => $formattedProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get package card gift projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取套餐卡包含项目失败，请稍后重试']);
        }
    }
    
    /**
     * 获取套餐卡包含产品
     * @param Request $request 请求对象
     * @param int $packageId 套餐卡ID
     * @return array 套餐卡包含产品数据
     */
    public function getPackageCardGiftProducts(Request $request, $packageId)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查套餐卡是否存在，并且确保只能查看自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $packageId)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 构建查询
            $query = DB::table('card_package_gift_product')->where('package_id', $packageId)->where('isDelete', 0);
            
            // 非超级管理员只能查看自己公司的产品
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $products = $query->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProducts = $products->map(function($product) {
                // 获取产品名称
                $productInfo = DB::table('card_product')->where('id', $product->product_id)->first();
                return [
                    'id' => $product->id,
                    'packageId' => $product->package_id,
                    'productId' => $product->product_id,
                    'productName' => $productInfo ? $productInfo->product_name : '',
                    'times' => $product->times,
                    'unitPrice' => $product->unit_price,
                    'totalPrice' => $product->total_price,
                    'manualSalary' => $product->manual_salary,
                    'createTime' => $product->created_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取套餐卡包含产品成功', 'data' => $formattedProducts]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get package card gift products error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取套餐卡包含产品失败，请稍后重试']);
        }
    }
    
    /**
     * 添加套餐卡包含项目
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addPackageCardGiftProject(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            if (empty($data['packageId'])) {
                return json(['code' => 400, 'message' => '套餐卡ID不能为空']);
            }
            if (empty($data['projectId'])) {
                return json(['code' => 400, 'message' => '项目ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '总次数不能为空']);
            }
            if (empty($data['unitPrice'])) {
                return json(['code' => 400, 'message' => '单价不能为空']);
            }
            if (empty($data['consume'])) {
                return json(['code' => 400, 'message' => '耗卡不能为空']);
            }
            if (empty($data['manualSalary'])) {
                return json(['code' => 400, 'message' => '手工不能为空']);
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $data['packageId'])->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 计算总价
            $totalPrice = $data['totalPrice'] ?? ($data['times'] * $data['unitPrice']);
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'package_id' => $data['packageId'],
                'project_id' => $data['projectId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'],
                'total_price' => $totalPrice,
                'consume' => $data['consume'],
                'manual_salary' => $data['manualSalary'],
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_package_gift_project')->insertGetId($dbData);
            return json(['code' => 200, 'message' => '添加套餐卡包含项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add package card gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加套餐卡包含项目失败，请稍后重试']);
        }
    }
    
    /**
     * 更新套餐卡包含项目
     * @param Request $request 请求对象
     * @param int $id 项目ID
     * @return array 更新结果
     */
    public function updatePackageCardGiftProject(Request $request, $id)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查项目是否存在，并且确保只能修改自己公司的项目
            $projectQuery = DB::table('card_package_gift_project')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $projectQuery->where('company_id', $currentCompanyId);
            }
            $project = $projectQuery->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $project->package_id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 计算总价
            $totalPrice = $data['totalPrice'] ?? ($data['times'] * $data['unitPrice']);
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'times' => $data['times'] ?? $project->times,
                'unit_price' => $data['unitPrice'] ?? $project->unit_price,
                'total_price' => $totalPrice,
                'consume' => $data['consume'] ?? $project->consume,
                'manual_salary' => $data['manualSalary'] ?? $project->manual_salary,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_package_gift_project')->where('id', $id)->update($dbData);
            return json(['code' => 200, 'message' => '更新套餐卡包含项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update package card gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新套餐卡包含项目失败，请稍后重试']);
        }
    }
    
    /**
     * 删除套餐卡包含项目
     * @param Request $request 请求对象
     * @param int $id 项目ID
     * @return array 删除结果
     */
    public function deletePackageCardGiftProject(Request $request, $id)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查项目是否存在，并且确保只能删除自己公司的项目
            $projectQuery = DB::table('card_package_gift_project')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $projectQuery->where('company_id', $currentCompanyId);
            }
            $project = $projectQuery->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $project->package_id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 软删除项目
            DB::table('card_package_gift_project')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除套餐卡包含项目成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete package card gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除套餐卡包含项目失败，请稍后重试']);
        }
    }
    
    /**
     * 添加套餐卡包含产品
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addPackageCardGiftProduct(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            if (empty($data['packageId'])) {
                return json(['code' => 400, 'message' => '套餐卡ID不能为空']);
            }
            if (empty($data['productId'])) {
                return json(['code' => 400, 'message' => '产品ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '数量不能为空']);
            }
            if (empty($data['unitPrice'])) {
                return json(['code' => 400, 'message' => '单价不能为空']);
            }
            if (empty($data['manualSalary'])) {
                return json(['code' => 400, 'message' => '手工不能为空']);
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $data['packageId'])->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 计算总价
            $totalPrice = $data['totalPrice'] ?? ($data['times'] * $data['unitPrice']);
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'package_id' => $data['packageId'],
                'product_id' => $data['productId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'],
                'total_price' => $totalPrice,
                'manual_salary' => $data['manualSalary'],
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_package_gift_product')->insertGetId($dbData);
            return json(['code' => 200, 'message' => '添加套餐卡包含产品成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add package card gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加套餐卡包含产品失败，请稍后重试']);
        }
    }
    
    /**
     * 更新套餐卡包含产品
     * @param Request $request 请求对象
     * @param int $id 产品ID
     * @return array 更新结果
     */
    public function updatePackageCardGiftProduct(Request $request, $id)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查产品是否存在，并且确保只能修改自己公司的产品
            $productQuery = DB::table('card_package_gift_product')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $productQuery->where('company_id', $currentCompanyId);
            }
            $product = $productQuery->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $product->package_id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 计算总价
            $totalPrice = $data['totalPrice'] ?? ($data['times'] * $data['unitPrice']);
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'times' => $data['times'] ?? $product->times,
                'unit_price' => $data['unitPrice'] ?? $product->unit_price,
                'total_price' => $totalPrice,
                'manual_salary' => $data['manualSalary'] ?? $product->manual_salary,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_package_gift_product')->where('id', $id)->update($dbData);
            return json(['code' => 200, 'message' => '更新套餐卡包含产品成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update package card gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新套餐卡包含产品失败，请稍后重试']);
        }
    }
    
    /**
     * 删除套餐卡包含产品
     * @param Request $request 请求对象
     * @param int $id 产品ID
     * @return array 删除结果
     */
    public function deletePackageCardGiftProduct(Request $request, $id)
    {
        try {
            // 检查用户权限
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 检查产品是否存在，并且确保只能删除自己公司的产品
            $productQuery = DB::table('card_package_gift_product')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $productQuery->where('company_id', $currentCompanyId);
            }
            $product = $productQuery->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            // 检查套餐卡是否存在，并且确保只能操作自己公司的套餐卡
            $packageQuery = DB::table('card_package')->where('id', $product->package_id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $packageQuery->where('company_id', $currentCompanyId);
            }
            $package = $packageQuery->first();
            if (!$package) {
                return json(['code' => 404, 'message' => '套餐卡不存在']);
            }
            
            // 软删除产品
            DB::table('card_package_gift_product')->where('id', $id)->update(['isDelete' => 1]);
            return json(['code' => 200, 'message' => '删除套餐卡包含产品成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete package card gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除套餐卡包含产品失败，请稍后重试']);
        }
    }
    

    /**
     * 获取时效卡列表
     * @param Request $request 请求对象
     * @return array 时效卡列表数据
     */
    public function getTimeCards(Request $request)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('isDelete', 0);
            
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $keyword = $request->get('keyword');
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('card_name', 'like', '%' . $keyword . '%')
                      ->orWhere('card_code', 'like', '%' . $keyword . '%');
                });
            }
            
            $status = $request->get('status');
            if ($status !== null) {
                $query->where('status', $status);
            }
            
            $cards = $query->orderBy('created_at', 'desc')->get();
            
            $formattedCards = $cards->map(function($card) {
                $useRuleText = '不限次数';
                if ($card->use_rule_type == 2) {
                    $useRuleText = '限' . $card->max_use_count . '次';
                } elseif ($card->use_rule_type == 3) {
                    $intervalText = $card->interval_hours . '小时';
                    if ($card->interval_hours == 24) $intervalText = '每天1次';
                    elseif ($card->interval_hours == 72) $intervalText = '每3天1次';
                    elseif ($card->interval_hours == 168) $intervalText = '每周1次';
                    $useRuleText = '限频率(' . $intervalText . ')';
                }
                
                $projectBindText = '单选项目';
                if ($card->project_bind_type == 2) $projectBindText = '多选项目';
                elseif ($card->project_bind_type == 3) $projectBindText = '全店通用';
                
                return [
                    'id' => $card->id,
                    'cardName' => $card->card_name,
                    'cardCode' => $card->card_code,
                    'originalPrice' => $card->original_price,
                    'price' => $card->price,
                    'validDays' => $card->valid_days,
                    'validType' => $card->valid_type,
                    'useRuleType' => $card->use_rule_type,
                    'useRuleText' => $useRuleText,
                    'maxUseCount' => $card->max_use_count,
                    'intervalHours' => $card->interval_hours,
                    'projectBindType' => $card->project_bind_type,
                    'projectBindText' => $projectBindText,
                    'customerCount' => $card->customer_count,
                    'description' => $card->description,
                    'remark' => $card->remark,
                    'status' => $card->status,
                    'onlineTime' => $card->online_time,
                    'offlineTime' => $card->offline_time,
                    'saleStoreIds' => $card->sale_store_ids ? json_decode($card->sale_store_ids) : [],
                    'consumeStoreIds' => $card->consume_store_ids ? json_decode($card->consume_store_ids) : [],
                    'saleDepartmentIds' => $card->sale_department_ids ? json_decode($card->sale_department_ids) : [],
                    'consumeDepartmentIds' => $card->consume_department_ids ? json_decode($card->consume_department_ids) : [],
                    'isModifiable' => $card->is_modifiable,
                    'createTime' => $card->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取时效卡列表成功', 'data' => $formattedCards]);
        } catch (\Exception $e) {
            error_log('Get time cards error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取时效卡列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取时效卡详情
     * @param Request $request 请求对象
     * @param int $id 时效卡ID
     * @return array 时效卡详情数据
     */
    public function getTimeCardDetail(Request $request, $id)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('id', $id)->where('isDelete', 0);
            
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '时效卡不存在']);
            }
            
            $formattedCard = [
                'id' => $card->id,
                'cardName' => $card->card_name,
                'cardCode' => $card->card_code,
                'originalPrice' => $card->original_price,
                'price' => $card->price,
                'validDays' => $card->valid_days,
                'validType' => $card->valid_type,
                'useRuleType' => $card->use_rule_type,
                'maxUseCount' => $card->max_use_count,
                'intervalHours' => $card->interval_hours,
                'projectBindType' => $card->project_bind_type,
                'customerCount' => $card->customer_count,
                'description' => $card->description,
                'remark' => $card->remark,
                'status' => $card->status,
                'onlineTime' => $card->online_time,
                'offlineTime' => $card->offline_time,
                'saleStoreIds' => $card->sale_store_ids ? json_decode($card->sale_store_ids) : [],
                'consumeStoreIds' => $card->consume_store_ids ? json_decode($card->consume_store_ids) : [],
                'saleDepartmentIds' => $card->sale_department_ids ? json_decode($card->sale_department_ids) : [],
                'consumeDepartmentIds' => $card->consume_department_ids ? json_decode($card->consume_department_ids) : [],
                'isModifiable' => $card->is_modifiable,
                'createTime' => $card->created_at
            ];
            
            $projects = DB::table('card_time_project')
                ->where('time_card_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            $projectsList = [];
            foreach ($projects as $project) {
                $projectName = '';
                if ($project->project_id) {
                    $projectInfo = DB::table('card_project')->where('id', $project->project_id)->first();
                    if ($projectInfo) {
                        $projectName = $projectInfo->project_name ?? '';
                    }
                }
                $projectsList[] = [
                    'id' => $project->id,
                    'projectId' => $project->project_id,
                    'projectName' => $projectName,
                    'times' => $project->times,
                    'unitPrice' => $project->unit_price,
                    'totalPrice' => $project->total_price,
                    'consume' => $project->consume,
                    'manualSalary' => $project->manual_salary
                ];
            }
            
            $products = DB::table('card_time_product')
                ->where('time_card_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            $productsList = [];
            foreach ($products as $product) {
                $productName = '';
                if ($product->product_id) {
                    $productInfo = DB::table('card_product')->where('id', $product->product_id)->first();
                    if ($productInfo) {
                        $productName = $productInfo->product_name ?? '';
                    }
                }
                $productsList[] = [
                    'id' => $product->id,
                    'productId' => $product->product_id,
                    'productName' => $productName,
                    'times' => $product->times,
                    'unitPrice' => $product->unit_price,
                    'totalPrice' => $product->total_price,
                    'manualSalary' => $product->manual_salary
                ];
            }
            
            $formattedCard['projects'] = $projectsList;
            $formattedCard['products'] = $productsList;
            
            return json(['code' => 200, 'message' => '获取时效卡详情成功', 'data' => $formattedCard]);
        } catch (\Exception $e) {
            \support\Log::error('Get time card detail error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取时效卡详情失败，请稍后重试']);
        }
    }
    
    /**
     * 添加时效卡
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addTimeCard(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            if (empty($data['cardName'])) {
                return json(['code' => 400, 'message' => '卡名称不能为空']);
            }
            if (empty($data['validDays'])) {
                return json(['code' => 400, 'message' => '有效期不能为空']);
            }
            if (empty($data['price'])) {
                return json(['code' => 400, 'message' => '价格不能为空']);
            }
            
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $formatDateTime = function($value) {
                if (empty($value)) return null;
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : null;
                }
                return $value;
            };
            
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'card_name' => $data['cardName'] ?? '',
                'card_code' => $data['cardCode'] ?? '',
                'original_price' => $data['originalPrice'] ?? 0,
                'valid_days' => $data['validDays'] ?? 0,
                'valid_type' => $data['validType'] ?? 1,
                'price' => $data['price'] ?? 0,
                'use_rule_type' => $data['useRuleType'] ?? 1,
                'max_use_count' => $data['maxUseCount'] ?? null,
                'interval_hours' => $data['intervalHours'] ?? null,
                'project_bind_type' => $data['projectBindType'] ?? 1,
                'customer_count' => 0,
                'description' => $data['description'] ?? '',
                'remark' => $data['remark'] ?? '',
                'status' => $data['status'] ?? 1,
                'online_time' => $formatDateTime($data['onlineTime'] ?? null),
                'offline_time' => $formatDateTime($data['offlineTime'] ?? null),
                'sale_store_ids' => isset($data['saleStoreIds']) ? json_encode($data['saleStoreIds']) : null,
                'consume_store_ids' => isset($data['consumeStoreIds']) ? json_encode($data['consumeStoreIds']) : null,
                'sale_department_ids' => isset($data['saleDepartmentIds']) ? json_encode($data['saleDepartmentIds']) : null,
                'consume_department_ids' => isset($data['consumeDepartmentIds']) ? json_encode($data['consumeDepartmentIds']) : null,
                'is_modifiable' => $data['isModifiable'] ?? 0,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_time')->insertGetId($dbData);
            
            if (!empty($data['projects'])) {
                foreach ($data['projects'] as $project) {
                    DB::table('card_time_project')->insert([
                        'company_id' => $currentCompanyId,
                        'time_card_id' => $id,
                        'project_id' => $project['projectId'] ?? 0,
                        'times' => $project['times'] ?? 1,
                        'unit_price' => $project['unitPrice'] ?? 0,
                        'total_price' => $project['totalPrice'] ?? 0,
                        'consume' => $project['consume'] ?? 0,
                        'manual_salary' => $project['manualSalary'] ?? 0,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            if (!empty($data['products'])) {
                foreach ($data['products'] as $product) {
                    DB::table('card_time_product')->insert([
                        'company_id' => $currentCompanyId,
                        'time_card_id' => $id,
                        'product_id' => $product['productId'] ?? 0,
                        'times' => $product['times'] ?? 1,
                        'unit_price' => $product['unitPrice'] ?? 0,
                        'total_price' => $product['totalPrice'] ?? 0,
                        'manual_salary' => $product['manualSalary'] ?? 0,
                        'isDelete' => 0,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }
            
            return json(['code' => 200, 'message' => '添加时效卡成功', 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            error_log('Add time card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '添加时效卡失败，请稍后重试']);
        }
    }
    
    /**
     * 更新时效卡
     * @param Request $request 请求对象
     * @param int $id 时效卡ID
     * @return array 更新结果
     */
    public function updateTimeCard(Request $request, $id)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '时效卡不存在']);
            }
            
            $formatDateTime = function($value, $default = null) {
                if (empty($value)) return $default;
                if (is_string($value)) {
                    $timestamp = strtotime($value);
                    return $timestamp ? date('Y-m-d H:i:s', $timestamp) : $default;
                }
                return $value;
            };
            
            $dbData = [
                'card_name' => $data['cardName'] ?? $card->card_name,
                'card_code' => $data['cardCode'] ?? $card->card_code,
                'original_price' => $data['originalPrice'] ?? $card->original_price,
                'valid_days' => $data['validDays'] ?? $card->valid_days,
                'valid_type' => $data['validType'] ?? $card->valid_type,
                'price' => $data['price'] ?? $card->price,
                'use_rule_type' => $data['useRuleType'] ?? $card->use_rule_type,
                'max_use_count' => $data['maxUseCount'] ?? $card->max_use_count,
                'interval_hours' => $data['intervalHours'] ?? $card->interval_hours,
                'project_bind_type' => $data['projectBindType'] ?? $card->project_bind_type,
                'description' => $data['description'] ?? $card->description,
                'remark' => $data['remark'] ?? $card->remark,
                'status' => $data['status'] ?? $card->status,
                'online_time' => $formatDateTime($data['onlineTime'] ?? null, $card->online_time),
                'offline_time' => $formatDateTime($data['offlineTime'] ?? null, $card->offline_time),
                'sale_store_ids' => isset($data['saleStoreIds']) ? json_encode($data['saleStoreIds']) : $card->sale_store_ids,
                'consume_store_ids' => isset($data['consumeStoreIds']) ? json_encode($data['consumeStoreIds']) : $card->consume_store_ids,
                'sale_department_ids' => isset($data['saleDepartmentIds']) ? json_encode($data['saleDepartmentIds']) : $card->sale_department_ids,
                'consume_department_ids' => isset($data['consumeDepartmentIds']) ? json_encode($data['consumeDepartmentIds']) : $card->consume_department_ids,
                'is_modifiable' => $data['isModifiable'] ?? $card->is_modifiable,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_time')->where('id', $id)->update($dbData);
            
            if (!empty($data['projects'])) {
                DB::table('card_time_project')
                    ->where('time_card_id', $id)
                    ->update(['isDelete' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                    
                foreach ($data['projects'] as $project) {
                    if (!empty($project['id'])) {
                        DB::table('card_time_project')
                            ->where('id', $project['id'])
                            ->update([
                                'project_id' => $project['projectId'] ?? 0,
                                'times' => $project['times'] ?? 1,
                                'unit_price' => $project['unitPrice'] ?? 0,
                                'total_price' => $project['totalPrice'] ?? 0,
                                'consume' => $project['consume'] ?? 0,
                                'manual_salary' => $project['manualSalary'] ?? 0,
                                'isDelete' => 0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        DB::table('card_time_project')->insert([
                            'company_id' => $currentCompanyId,
                            'time_card_id' => $id,
                            'project_id' => $project['projectId'] ?? 0,
                            'times' => $project['times'] ?? 1,
                            'unit_price' => $project['unitPrice'] ?? 0,
                            'total_price' => $project['totalPrice'] ?? 0,
                            'consume' => $project['consume'] ?? 0,
                            'manual_salary' => $project['manualSalary'] ?? 0,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            if (!empty($data['products'])) {
                DB::table('card_time_product')
                    ->where('time_card_id', $id)
                    ->update(['isDelete' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
                    
                foreach ($data['products'] as $product) {
                    if (!empty($product['id'])) {
                        DB::table('card_time_product')
                            ->where('id', $product['id'])
                            ->update([
                                'product_id' => $product['productId'] ?? 0,
                                'times' => $product['times'] ?? 1,
                                'unit_price' => $product['unitPrice'] ?? 0,
                                'total_price' => $product['totalPrice'] ?? 0,
                                'manual_salary' => $product['manualSalary'] ?? 0,
                                'isDelete' => 0,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                    } else {
                        DB::table('card_time_product')->insert([
                            'company_id' => $currentCompanyId,
                            'time_card_id' => $id,
                            'product_id' => $product['productId'] ?? 0,
                            'times' => $product['times'] ?? 1,
                            'unit_price' => $product['unitPrice'] ?? 0,
                            'total_price' => $product['totalPrice'] ?? 0,
                            'manual_salary' => $product['manualSalary'] ?? 0,
                            'isDelete' => 0,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }
                }
            }
            
            return json(['code' => 200, 'message' => '更新时效卡成功']);
        } catch (\Exception $e) {
            error_log('Update time card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新时效卡失败，请稍后重试']);
        }
    }
    
    /**
     * 删除时效卡
     * @param Request $request 请求对象
     * @param int $id 时效卡ID
     * @return array 删除结果
     */
    public function deleteTimeCard(Request $request, $id)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '时效卡不存在']);
            }
            
            DB::table('card_time')->where('id', $id)->update(['isDelete' => 1]);
            DB::table('card_time_project')->where('time_card_id', $id)->update(['isDelete' => 1]);
            DB::table('card_time_product')->where('time_card_id', $id)->update(['isDelete' => 1]);
            
            return json(['code' => 200, 'message' => '删除时效卡成功']);
        } catch (\Exception $e) {
            error_log('Delete time card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除时效卡失败，请稍后重试']);
        }
    }
    
    /**
     * 复制时效卡
     * @param Request $request 请求对象
     * @param int $id 时效卡ID
     * @return array 复制结果
     */
    public function copyTimeCard(Request $request, $id)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '时效卡不存在']);
            }
            
            $newCardData = [
                'company_id' => $card->company_id,
                'card_name' => $card->card_name . '(副本)',
                'card_code' => $card->card_code,
                'original_price' => $card->original_price,
                'valid_days' => $card->valid_days,
                'valid_type' => $card->valid_type,
                'price' => $card->price,
                'use_rule_type' => $card->use_rule_type,
                'max_use_count' => $card->max_use_count,
                'interval_hours' => $card->interval_hours,
                'project_bind_type' => $card->project_bind_type,
                'customer_count' => 0,
                'description' => $card->description,
                'remark' => $card->remark,
                'status' => 0,
                'online_time' => null,
                'offline_time' => null,
                'sale_store_ids' => $card->sale_store_ids,
                'consume_store_ids' => $card->consume_store_ids,
                'sale_department_ids' => $card->sale_department_ids,
                'consume_department_ids' => $card->consume_department_ids,
                'is_modifiable' => $card->is_modifiable,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $newId = DB::table('card_time')->insertGetId($newCardData);
            
            $projects = DB::table('card_time_project')
                ->where('time_card_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            foreach ($projects as $project) {
                DB::table('card_time_project')->insert([
                    'company_id' => $project->company_id,
                    'time_card_id' => $newId,
                    'project_id' => $project->project_id,
                    'times' => $project->times,
                    'unit_price' => $project->unit_price,
                    'total_price' => $project->total_price,
                    'consume' => $project->consume,
                    'manual_salary' => $project->manual_salary,
                    'isDelete' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            $products = DB::table('card_time_product')
                ->where('time_card_id', $id)
                ->where('isDelete', 0)
                ->get();
            
            foreach ($products as $product) {
                DB::table('card_time_product')->insert([
                    'company_id' => $product->company_id,
                    'time_card_id' => $newId,
                    'product_id' => $product->product_id,
                    'times' => $product->times,
                    'unit_price' => $product->unit_price,
                    'total_price' => $product->total_price,
                    'manual_salary' => $product->manual_salary,
                    'isDelete' => 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }
            
            return json(['code' => 200, 'message' => '复制时效卡成功', 'data' => ['id' => $newId]]);
        } catch (\Exception $e) {
            error_log('Copy time card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '复制时效卡失败，请稍后重试']);
        }
    }
    
    /**
     * 切换时效卡状态
     * @param Request $request 请求对象
     * @param int $id 时效卡ID
     * @return array 切换结果
     */
    public function toggleTimeCardStatus(Request $request, $id)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')->where('id', $id)->where('isDelete', 0);
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '时效卡不存在']);
            }
            
            $newStatus = $card->status == 1 ? 0 : 1;
            
            if ($newStatus == 0 && $card->customer_count > 0) {
                return json([
                    'code' => 400, 
                    'message' => '该卡已有 ' . $card->customer_count . ' 位顾客办理，确定要禁用吗？',
                    'data' => ['customerCount' => $card->customer_count]
                ]);
            }
            
            DB::table('card_time')->where('id', $id)->update([
                'status' => $newStatus,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return json(['code' => 200, 'message' => $newStatus == 1 ? '启用成功' : '禁用成功', 'data' => ['status' => $newStatus]]);
        } catch (\Exception $e) {
            error_log('Toggle time card status error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '切换状态失败，请稍后重试']);
        }
    }
    
    /**
     * 批量修改时效卡状态
     * @param Request $request 请求对象
     * @return array 批量修改结果
     */
    public function batchTimeCardStatus(Request $request)
    {
        try {
            $data = $request->post();
            if (empty($data)) {
                $rawBody = $request->rawBody();
                if ($rawBody) {
                    $data = json_decode($rawBody, true);
                }
            }
            
            if (empty($data['ids']) || !is_array($data['ids'])) {
                return json(['code' => 400, 'message' => '请选择要操作的时效卡']);
            }
            
            if (!isset($data['status'])) {
                return json(['code' => 400, 'message' => '请指定状态']);
            }
            
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_time')
                ->whereIn('id', $data['ids'])
                ->where('isDelete', 0);
            
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $count = $query->update([
                'status' => $data['status'],
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return json(['code' => 200, 'message' => '批量修改成功', 'data' => ['count' => $count]]);
        } catch (\Exception $e) {
            error_log('Batch time card status error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '批量修改失败，请稍后重试']);
        }
    }
    
    /**
     * 获取核心业务部门列表
     * @param Request $request 请求对象
     * @return array 核心业务部门列表数据
     */
    public function getCoreDepartments(Request $request)
    {
        try {
            // 获取当前用户所属公司ID
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            // 构建查询，只返回核心业务部门（enable_category = 1）
            $query = DB::table('sys_department')->where('isDelete', 0)->where('status', 1)->where('enable_category', 1);
            
            // 非超级管理员只能查看自己公司的部门
            if ($currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            // 执行查询
            $departments = $query->orderBy('sort', 'asc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedDepartments = $departments->map(function($department) {
                return [
                    'id' => $department->id,
                    'deptName' => $department->dept_name,
                    'sort' => $department->sort
                ];
            });
            return json(['code' => 200, 'message' => '获取核心业务部门列表成功', 'data' => $formattedDepartments]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get core departments error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取核心业务部门列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取产品列表
     * @param Request $request 请求对象
     * @return array 产品列表数据
     */
    public function getProducts(Request $request)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_product')->where('isDelete', 0);
            
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $productName = $request->get('productName');
            if ($productName) {
                $query->where('product_name', 'like', '%' . $productName . '%');
            }
            
            $categoryId = $request->get('categoryId');
            if ($categoryId) {
                $query->where('category_id', $categoryId);
            }
            
            $products = $query->get();
            
            $formattedProducts = $products->map(function($product) {
                $supplierName = null;
                if ($product->supplier_id) {
                    $supplier = DB::table('card_supplier')->where('id', $product->supplier_id)->first();
                    if ($supplier) {
                        $supplierName = $supplier->supplier_name;
                    }
                }
                return [
                    'id' => $product->id,
                    'productName' => $product->product_name,
                    'productCode' => $product->product_code ?? '',
                    'externalName' => $product->external_name ?? '',
                    'barcode' => $product->barcode ?? '',
                    'categoryId' => $product->category_id,
                    'supplierId' => $product->supplier_id,
                    'supplierName' => $supplierName,
                    'productType' => $product->product_type ?? '',
                    'unit' => $product->unit ?? '',
                    'monthlyLimit' => $product->monthly_limit ?? 0,
                    'consumptionInterval' => $product->consumption_interval ?? 0,
                    'specification' => $product->specification ?? '',
                    'originalPrice' => $product->original_price ?? 0,
                    'salePrice' => $product->sale_price ?? 0,
                    'experiencePrice' => $product->experience_price ?? 0,
                    'purchasePrice' => $product->purchase_price ?? 0,
                    'onlineDate' => $product->online_date,
                    'offlineDate' => $product->offline_date,
                    'stockMin' => $product->stock_min ?? 0,
                    'stockMax' => $product->stock_max ?? 0,
                    'approvalNumber' => $product->approval_number ?? '',
                    'expiryDate' => $product->expiry_date,
                    'status' => $product->status ?? 1,
                    'remark' => $product->remark ?? '',
                    'limitedSaleStores' => $product->limited_sale_stores ? json_decode($product->limited_sale_stores, true) : [],
                    'limitedConsumeStores' => $product->limited_consume_stores ? json_decode($product->limited_consume_stores, true) : [],
                    'limitedSaleDepts' => $product->limited_sale_depts ? json_decode($product->limited_sale_depts, true) : [],
                    'limitedConsumeDepts' => $product->limited_consume_depts ? json_decode($product->limited_consume_depts, true) : [],
                    'noDiscount' => $product->no_discount ?? 0,
                    'allowGift' => $product->allow_gift ?? 0,
                    'noConsumption' => $product->no_consumption ?? 0,
                    'noModify' => $product->no_modify ?? 0,
                    'isCooperative' => $product->is_cooperative ?? 0,
                    'isYm' => $product->is_ym ?? 0,
                    'isSpecial' => $product->is_special ?? 0,
                    'createTime' => $product->created_at
                ];
            });
            return json(['code' => 200, 'message' => '获取产品列表成功', 'data' => $formattedProducts]);
        } catch (\Exception $e) {
            error_log('Get products error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取产品列表失败，请稍后重试']);
        }
    }
    
    /**
     * 新增产品
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addProduct(Request $request)
    {
        try {
            $data = $request->post();
            
            if (empty($data['productName'])) {
                return json(['code' => 400, 'message' => '产品名称不能为空']);
            }
            
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $dbData = [
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'product_name' => $data['productName'] ?? '',
                'product_code' => $data['productCode'] ?? '',
                'external_name' => $data['externalName'] ?? '',
                'barcode' => $data['barcode'] ?? '',
                'category_id' => $data['categoryId'] ?? null,
                'supplier_id' => $data['supplierId'] ?? null,
                'product_type' => $data['productType'] ?? '',
                'unit' => $data['unit'] ?? '',
                'monthly_limit' => $data['monthlyLimit'] ?? 0,
                'consumption_interval' => $data['consumptionInterval'] ?? 0,
                'specification' => $data['specification'] ?? '',
                'original_price' => $data['originalPrice'] ?? 0,
                'sale_price' => $data['salePrice'] ?? 0,
                'experience_price' => $data['experiencePrice'] ?? 0,
                'purchase_price' => $data['purchasePrice'] ?? 0,
                'online_date' => $data['onlineDate'] ?? null,
                'offline_date' => $data['offlineDate'] ?? null,
                'stock_min' => $data['stockMin'] ?? 0,
                'stock_max' => $data['stockMax'] ?? 0,
                'approval_number' => $data['approvalNumber'] ?? '',
                'expiry_date' => $data['expiryDate'] ?? null,
                'status' => $data['status'] ?? 1,
                'remark' => $data['remark'] ?? '',
                'limited_sale_stores' => isset($data['limitedSaleStores']) ? json_encode($data['limitedSaleStores']) : null,
                'limited_consume_stores' => isset($data['limitedConsumeStores']) ? json_encode($data['limitedConsumeStores']) : null,
                'limited_sale_depts' => isset($data['limitedSaleDepts']) ? json_encode($data['limitedSaleDepts']) : null,
                'limited_consume_depts' => isset($data['limitedConsumeDepts']) ? json_encode($data['limitedConsumeDepts']) : null,
                'no_discount' => $data['noDiscount'] ?? 0,
                'allow_gift' => $data['allowGift'] ?? 0,
                'no_consumption' => $data['noConsumption'] ?? 0,
                'no_modify' => $data['noModify'] ?? 0,
                'is_cooperative' => $data['isCooperative'] ?? 0,
                'is_ym' => $data['isYm'] ?? 0,
                'is_special' => $data['isSpecial'] ?? 0,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_product')->insertGetId($dbData);
            
            return json(['code' => 200, 'message' => '新增产品成功', 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            error_log('Add product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '新增产品失败，请稍后重试']);
        }
    }
    
    /**
     * 更新产品
     * @param Request $request 请求对象
     * @param int $id 产品ID
     * @return array 更新结果
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            if (empty($data['productName'])) {
                return json(['code' => 400, 'message' => '产品名称不能为空']);
            }
            
            $product = DB::table('card_product')->where('id', $id)->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            $dbData = [
                'product_name' => $data['productName'] ?? '',
                'product_code' => $data['productCode'] ?? '',
                'external_name' => $data['externalName'] ?? '',
                'barcode' => $data['barcode'] ?? '',
                'category_id' => $data['categoryId'] ?? null,
                'supplier_id' => $data['supplierId'] ?? null,
                'product_type' => $data['productType'] ?? '',
                'unit' => $data['unit'] ?? '',
                'monthly_limit' => $data['monthlyLimit'] ?? 0,
                'consumption_interval' => $data['consumptionInterval'] ?? 0,
                'specification' => $data['specification'] ?? '',
                'original_price' => $data['originalPrice'] ?? 0,
                'sale_price' => $data['salePrice'] ?? 0,
                'experience_price' => $data['experiencePrice'] ?? 0,
                'purchase_price' => $data['purchasePrice'] ?? 0,
                'online_date' => $data['onlineDate'] ?? null,
                'offline_date' => $data['offlineDate'] ?? null,
                'stock_min' => $data['stockMin'] ?? 0,
                'stock_max' => $data['stockMax'] ?? 0,
                'approval_number' => $data['approvalNumber'] ?? '',
                'expiry_date' => $data['expiryDate'] ?? null,
                'status' => $data['status'] ?? 1,
                'remark' => $data['remark'] ?? '',
                'limited_sale_stores' => isset($data['limitedSaleStores']) ? json_encode($data['limitedSaleStores']) : null,
                'limited_consume_stores' => isset($data['limitedConsumeStores']) ? json_encode($data['limitedConsumeStores']) : null,
                'limited_sale_depts' => isset($data['limitedSaleDepts']) ? json_encode($data['limitedSaleDepts']) : null,
                'limited_consume_depts' => isset($data['limitedConsumeDepts']) ? json_encode($data['limitedConsumeDepts']) : null,
                'no_discount' => $data['noDiscount'] ?? 0,
                'allow_gift' => $data['allowGift'] ?? 0,
                'no_consumption' => $data['noConsumption'] ?? 0,
                'no_modify' => $data['noModify'] ?? 0,
                'is_cooperative' => $data['isCooperative'] ?? 0,
                'is_ym' => $data['isYm'] ?? 0,
                'is_special' => $data['isSpecial'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_product')->where('id', $id)->update($dbData);
            
            return json(['code' => 200, 'message' => '更新产品成功']);
        } catch (\Exception $e) {
            error_log('Update product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新产品失败，请稍后重试']);
        }
    }
    
    /**
     * 删除产品
     * @param Request $request 请求对象
     * @param int $id 产品ID
     * @return array 删除结果
     */
    public function deleteProduct(Request $request, $id)
    {
        try {
            $product = DB::table('card_product')->where('id', $id)->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            DB::table('card_product')->where('id', $id)->update(['isDelete' => 1]);
            
            return json(['code' => 200, 'message' => '删除产品成功']);
        } catch (\Exception $e) {
            error_log('Delete product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除产品失败，请稍后重试']);
        }
    }
    
    /**
     * 获取产品分类列表
     * @param Request $request 请求对象
     * @return array 分类列表数据
     */
    public function getProductCategories(Request $request)
    {
        try {
            $isSuper = isset($GLOBALS['is_super']) && $GLOBALS['is_super'];
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $query = DB::table('card_product_category')->where('isDelete', 0);
            
            if (!$isSuper && $currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $departmentId = $request->get('departmentId');
            if ($departmentId) {
                $query->where('department_id', $departmentId);
            }
            
            $categoryName = $request->get('categoryName');
            if ($categoryName) {
                $query->where('category_name', 'like', '%' . $categoryName . '%');
            }
            
            $categories = $query->orderBy('sort', 'asc')->get();
            
            $departments = DB::table('sys_department')->where('isDelete', 0)->get();
            $deptMap = [];
            foreach ($departments as $dept) {
                $deptMap[$dept->id] = $dept->dept_name;
            }
            
            $formattedCategories = $categories->map(function($category) use ($deptMap) {
                return [
                    'id' => $category->id,
                    'categoryName' => $category->category_name,
                    'departmentId' => $category->department_id,
                    'departmentName' => isset($deptMap[$category->department_id]) ? $deptMap[$category->department_id] : '',
                    'sort' => $category->sort,
                    'createTime' => $category->created_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取产品分类列表成功', 'data' => $formattedCategories]);
        } catch (\Exception $e) {
            error_log('Get product categories error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取产品分类列表失败，请稍后重试']);
        }
    }
    
    /**
     * 新增产品分类
     * @param Request $request 请求对象
     * @return array 添加结果
     */
    public function addProductCategory(Request $request)
    {
        try {
            $data = $request->post();
            
            if (empty($data['categoryName'])) {
                return json(['code' => 400, 'message' => '分类名称不能为空']);
            }
            
            $currentCompanyId = isset($GLOBALS['company_id']) ? $GLOBALS['company_id'] : null;
            
            $dbData = [
                'category_name' => $data['categoryName'] ?? '',
                'department_id' => $data['departmentId'] ?? null,
                'sort' => $data['sort'] ?? 0,
                'company_id' => $data['companyId'] ?? $currentCompanyId,
                'isDelete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            $id = DB::table('card_product_category')->insertGetId($dbData);
            
            return json(['code' => 200, 'message' => '新增产品分类成功', 'data' => ['id' => $id]]);
        } catch (\Exception $e) {
            error_log('Add product category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '新增产品分类失败，请稍后重试']);
        }
    }
    
    /**
     * 更新产品分类
     * @param Request $request 请求对象
     * @param int $id 分类ID
     * @return array 更新结果
     */
    public function updateProductCategory(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            if (empty($data['categoryName'])) {
                return json(['code' => 400, 'message' => '分类名称不能为空']);
            }
            
            $category = DB::table('card_product_category')->where('id', $id)->first();
            if (!$category) {
                return json(['code' => 404, 'message' => '分类不存在']);
            }
            
            $dbData = [
                'category_name' => $data['categoryName'] ?? '',
                'department_id' => $data['departmentId'] ?? null,
                'sort' => $data['sort'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            DB::table('card_product_category')->where('id', $id)->update($dbData);
            
            return json(['code' => 200, 'message' => '更新产品分类成功']);
        } catch (\Exception $e) {
            error_log('Update product category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新产品分类失败，请稍后重试']);
        }
    }
    
    /**
     * 删除产品分类
     * @param Request $request 请求对象
     * @param int $id 分类ID
     * @return array 删除结果
     */
    public function deleteProductCategory(Request $request, $id)
    {
        try {
            $category = DB::table('card_product_category')->where('id', $id)->first();
            if (!$category) {
                return json(['code' => 404, 'message' => '分类不存在']);
            }
            
            DB::table('card_product_category')->where('id', $id)->update(['isDelete' => 1]);
            
            return json(['code' => 200, 'message' => '删除产品分类成功']);
        } catch (\Exception $e) {
            error_log('Delete product category error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除产品分类失败，请稍后重试']);
        }
    }
}
