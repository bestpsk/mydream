<?php

namespace app\controller;

use support\Request;
use support\DB;

class RechargeCardController extends BaseController
{
    public function getList(Request $request)
    {
        try {
            $query = DB::table('card_recharge')->where('is_delete', 0);
            
            $currentCompanyId = $this->getCompanyId();
            if ($currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $keyword = $request->get('keyword');
            if ($keyword) {
                $query->where(function($q) use ($keyword) {
                    $q->where('card_name', 'like', '%' . $keyword . '%')
                      ->orWhere('card_code', 'like', '%' . $keyword . '%');
                });
            }
            
            $rechargeCards = $query->orderBy('created_at', 'desc')->get();
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCards = $rechargeCards->map(function($card) {
                return [
                    'id' => $card->id,
                    'companyId' => $card->company_id,
                    'cardName' => $card->card_name,
                    'cardCode' => $card->card_code ?? '',
                    'amount' => $card->amount,
                    'giftAmount' => $card->gift_amount,
                    'projectDiscount' => $card->project_discount,
                    'productDiscount' => $card->product_discount,
                    'consumeRate' => $card->consume_rate,
                    'minRechargeLimit' => $card->min_recharge_limit,
                    'onlineTime' => $card->start_time,
                    'offlineTime' => $card->end_time,
                    'expireDate' => $card->expire_date,
                    'expireType' => $card->expire_type,
                    'description' => $card->description,
                    'remark' => $card->remark,
                    'isModifiable' => $card->is_modifiable,
                    'isLimitOnce' => $card->is_limit_once,
                    'isExpireInvalid' => $card->is_expire_invalid,
                    'isProjectExpire' => $card->is_project_expire,
                    'isProhibitDiscountModify' => $card->is_prohibit_discount_modify,
                    'status' => $card->status,
                    'createdAt' => $card->created_at,
                    'updatedAt' => $card->updated_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取充值卡列表成功', 'data' => $formattedCards]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get recharge card list error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取充值卡列表失败，请稍后重试']);
        }
    }
    
    /**
     * 获取充值卡详情
     * @param Request $request 请求对象
     * @param int $id 充值卡ID
     * @return array 充值卡详情数据
     */
    public function getDetail(Request $request, $id)
    {
        try {
            $query = DB::table('card_recharge')->where('id', $id)->where('is_delete', 0);
            
            $currentCompanyId = $this->getCompanyId();
            if ($currentCompanyId) {
                $query->where('company_id', $currentCompanyId);
            }
            
            $card = $query->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 获取销售分店
            $saleStores = DB::table('card_recharge_sale_store')
                ->where('recharge_id', $id)
                ->pluck('store_id')
                ->toArray();
            
            // 获取消费分店
            $consumeStores = DB::table('card_recharge_consume_store')
                ->where('recharge_id', $id)
                ->pluck('store_id')
                ->toArray();
            
            // 获取销售部门
            $saleDepartments = [];
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_sale_department` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `department_id` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `department_id` (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $saleDepartments = DB::table('card_recharge_sale_department')
                ->where('recharge_id', $id)
                ->pluck('department_id')
                ->toArray();
            
            // 获取消费部门
            $consumeDepartments = [];
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_consume_department` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `department_id` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `department_id` (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $consumeDepartments = DB::table('card_recharge_consume_department')
                ->where('recharge_id', $id)
                ->pluck('department_id')
                ->toArray();
            
            // 获取包含项目
            $giftProjects = [];
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_project` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `project_id` int(11) NOT NULL,
                `times` int(11) NOT NULL,
                `unit_price` decimal(10,2) NOT NULL,
                `total_price` decimal(10,2) NOT NULL,
                `consume` decimal(10,2) NOT NULL,
                `manual_salary` decimal(10,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            $giftProjectRecords = DB::table('card_recharge_gift_project')
                ->where('recharge_id', $id)
                ->get();
            foreach ($giftProjectRecords as $record) {
                // 查找项目名称
                $projectName = DB::table('card_project')
                    ->where('id', $record->project_id)
                    ->value('project_name');
                
                // 获取总价，如果数据库中没有则根据times和unitPrice计算
                $totalPrice = isset($record->total_price) ? $record->total_price : ($record->times * $record->unit_price);
                
                $giftProjects[] = [
                    'id' => $record->id,
                    'projectId' => $record->project_id,
                    'projectName' => $projectName,
                    'times' => $record->times,
                    'unitPrice' => $record->unit_price,
                    'totalPrice' => $totalPrice,
                    'consume' => $record->consume,
                    'manualSalary' => $record->manual_salary
                ];
            }
            
            // 获取包含产品
            $giftProducts = [];
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_product` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `times` int(11) NOT NULL,
                `unit_price` decimal(10,2) NOT NULL,
                `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
                `manual_salary` decimal(10,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            // 添加 total_price 字段（如果不存在）
            $columnExists = DB::select("SHOW COLUMNS FROM `card_recharge_gift_product` LIKE 'total_price'");
            if (empty($columnExists)) {
                DB::statement('ALTER TABLE `card_recharge_gift_product` ADD COLUMN `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `unit_price`');
            }
            $giftProductRecords = DB::table('card_recharge_gift_product')
                ->where('recharge_id', $id)
                ->get();
            foreach ($giftProductRecords as $record) {
                // 查找产品名称
                $productName = DB::table('card_product')
                    ->where('id', $record->product_id)
                    ->value('product_name');
                
                $giftProducts[] = [
                    'id' => $record->id,
                    'productId' => $record->product_id,
                    'productName' => $productName,
                    'times' => $record->times,
                    'unitPrice' => $record->unit_price,
                    'totalPrice' => $record->total_price ?? ($record->times * $record->unit_price),
                    'manualSalary' => $record->manual_salary
                ];
            }
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedCard = [
                'id' => $card->id,
                'companyId' => $card->company_id,
                'cardName' => $card->card_name,
                'cardCode' => $card->card_code ?? '',
                'amount' => $card->amount,
                'giftAmount' => $card->gift_amount,
                'projectDiscount' => $card->project_discount,
                'productDiscount' => $card->product_discount,
                'consumeRate' => $card->consume_rate,
                'minRechargeLimit' => $card->min_recharge_limit,
                'onlineTime' => $card->start_time,
                'offlineTime' => $card->end_time,
                'expireDate' => $card->expire_date,
                'expireType' => $card->expire_type,
                'description' => $card->description,
                'remark' => $card->remark,
                'isModifiable' => $card->is_modifiable,
                'isLimitOnce' => $card->is_limit_once,
                'isExpireInvalid' => $card->is_expire_invalid,
                'isProjectExpire' => $card->is_project_expire,
                'isProhibitDiscountModify' => $card->is_prohibit_discount_modify,
                'createdAt' => $card->created_at,
                'updatedAt' => $card->updated_at,
                'saleStoreIds' => $saleStores,
                'consumeStoreIds' => $consumeStores,
                'saleDepartmentIds' => $saleDepartments,
                'consumeDepartmentIds' => $consumeDepartments,
                'giftProjects' => $giftProjects,
                'giftProducts' => $giftProducts,
                'status' => 1 // 默认状态为上线
            ];
            
            return json(['code' => 200, 'message' => '获取充值卡详情成功', 'data' => $formattedCard]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get recharge card detail error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取充值卡详情失败，请稍后重试']);
        }
    }
    
    /**
     * 新增充值卡
     * @param Request $request 请求对象
     * @return array 新增结果
     */
    public function add(Request $request)
    {
        try {
            $data = $request->post();
            
            if (empty($data['cardName'])) {
                error_log('Recharge card add error: cardName is required');
                return json(['code' => 400, 'message' => '充值卡名称不能为空']);
            }
            if (empty($data['amount'])) {
                error_log('Recharge card add error: amount is required');
                return json(['code' => 400, 'message' => '金额不能为空']);
            }
            
            $currentCompanyId = $this->getCompanyId();
            if (!$currentCompanyId) {
                $currentCompanyId = $data['companyId'] ?? 1;
            }
            
            $startTime = !empty($data['startTime']) ? $data['startTime'] : null;
            $endTime = !empty($data['endTime']) ? $data['endTime'] : null;
            $expireDate = !empty($data['expireDate']) ? $data['expireDate'] : null;
            
            if (!empty($startTime)) {
                $startTime = $this->convertDateTimeFormat($startTime);
            }
            if (!empty($endTime)) {
                $endTime = $this->convertDateTimeFormat($endTime);
            }
            if (!empty($expireDate)) {
                $expireDate = $this->convertDateTimeFormat($expireDate);
            }
            
            $dbData = [
                'company_id' => $currentCompanyId,
                'card_name' => $data['cardName'],
                'card_code' => $data['cardCode'] ?? '',
                'amount' => $data['amount'],
                'gift_amount' => $data['giftAmount'] ?? 0,
                'project_discount' => $data['projectDiscount'] ?? 10,
                'product_discount' => $data['productDiscount'] ?? 10,
                'consume_rate' => $data['consumeRate'] ?? 100,
                'min_recharge_limit' => $data['minRechargeLimit'] ?? 0,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'expire_date' => $expireDate,
                'expire_type' => $data['expireType'] ?? 3,
                'description' => $data['description'] ?? null,
                'remark' => $data['remark'] ?? null,
                'is_modifiable' => $data['isModifiable'] ?? 1,
                'is_limit_once' => $data['isLimitOnce'] ?? 0,
                'is_expire_invalid' => $data['isExpireInvalid'] ?? 1,
                'is_project_expire' => $data['isProjectExpire'] ?? 1,
                'is_prohibit_discount_modify' => $data['isProhibitDiscountModify'] ?? 0,
                'is_delete' => 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 记录请求数据
            error_log('Recharge card add request data: ' . json_encode($data));
            error_log('Current company ID: ' . $currentCompanyId);
            error_log('Processed datetime values - startTime: ' . var_export($startTime, true) . ', endTime: ' . var_export($endTime, true) . ', expireDate: ' . var_export($expireDate, true));
            error_log('Inserting recharge card data: ' . json_encode($dbData));
            
            // 插入充值卡数据
            $id = DB::table('card_recharge')->insertGetId($dbData);
            error_log('Recharge card inserted successfully with ID: ' . $id);
            
            // 处理销售分店
            if (!empty($data['saleStoreIds'])) {
                error_log('Processing sale stores: ' . json_encode($data['saleStoreIds']));
                foreach ($data['saleStoreIds'] as $storeId) {
                    DB::table('card_recharge_sale_store')->insert([
                        'recharge_id' => $id,
                        'store_id' => $storeId
                    ]);
                }
            }
            
            // 处理消费分店
            if (!empty($data['consumeStoreIds'])) {
                error_log('Processing consume stores: ' . json_encode($data['consumeStoreIds']));
                foreach ($data['consumeStoreIds'] as $storeId) {
                    DB::table('card_recharge_consume_store')->insert([
                        'recharge_id' => $id,
                        'store_id' => $storeId
                    ]);
                }
            }
            
            // 处理销售部门
            if (!empty($data['saleDepartmentIds'])) {
                error_log('Processing sale departments: ' . json_encode($data['saleDepartmentIds']));
                // 确保表存在
                DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_sale_department` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `recharge_id` int(11) NOT NULL,
                    `department_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `recharge_id` (`recharge_id`),
                    KEY `department_id` (`department_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                
                foreach ($data['saleDepartmentIds'] as $departmentId) {
                    DB::table('card_recharge_sale_department')->insert([
                        'recharge_id' => $id,
                        'department_id' => $departmentId
                    ]);
                }
            }
            
            // 处理消费部门
            if (!empty($data['consumeDepartmentIds'])) {
                error_log('Processing consume departments: ' . json_encode($data['consumeDepartmentIds']));
                // 确保表存在
                DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_consume_department` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `recharge_id` int(11) NOT NULL,
                    `department_id` int(11) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `recharge_id` (`recharge_id`),
                    KEY `department_id` (`department_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                
                foreach ($data['consumeDepartmentIds'] as $departmentId) {
                    DB::table('card_recharge_consume_department')->insert([
                        'recharge_id' => $id,
                        'department_id' => $departmentId
                    ]);
                }
            }
            
            // 处理包含项目
            if (!empty($data['giftProjects'])) {
                error_log('Processing gift projects: ' . json_encode($data['giftProjects']));
                // 确保表存在
                DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_project` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `recharge_id` int(11) NOT NULL,
                    `project_id` int(11) NOT NULL,
                    `times` int(11) NOT NULL,
                    `unit_price` decimal(10,2) NOT NULL,
                    `consume` decimal(10,2) NOT NULL,
                    `manual_salary` decimal(10,2) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `recharge_id` (`recharge_id`),
                    KEY `project_id` (`project_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                
                foreach ($data['giftProjects'] as $project) {
                    // 检查必要字段是否存在
                    if (!empty($project['projectId']) && !empty($project['times']) && !empty($project['unitPrice']) && !empty($project['consume']) && !empty($project['manualSalary'])) {
                        // 计算总价，如果没有提供则根据times和unitPrice计算
                        $totalPrice = isset($project['totalPrice']) ? $project['totalPrice'] : ($project['times'] * $project['unitPrice']);
                        
                        DB::table('card_recharge_gift_project')->insert([
                            'recharge_id' => $id,
                            'project_id' => $project['projectId'],
                            'times' => $project['times'],
                            'unit_price' => $project['unitPrice'],
                            'total_price' => $totalPrice,
                            'consume' => $project['consume'],
                            'manual_salary' => $project['manualSalary']
                        ]);
                    } else {
                        error_log('Skipping invalid gift project: ' . json_encode($project));
                    }
                }
            }
            
            // 处理包含产品
            if (!empty($data['giftProducts'])) {
                error_log('Processing gift products: ' . json_encode($data['giftProducts']));
                // 确保表存在
                DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_product` (
                    `id` int(11) NOT NULL AUTO_INCREMENT,
                    `recharge_id` int(11) NOT NULL,
                    `product_id` int(11) NOT NULL,
                    `times` int(11) NOT NULL,
                    `unit_price` decimal(10,2) NOT NULL,
                    `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
                    `manual_salary` decimal(10,2) NOT NULL,
                    PRIMARY KEY (`id`),
                    KEY `recharge_id` (`recharge_id`),
                    KEY `product_id` (`product_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
                // 添加 total_price 字段（如果不存在）
                $columnExists = DB::select("SHOW COLUMNS FROM `card_recharge_gift_product` LIKE 'total_price'");
                if (empty($columnExists)) {
                    DB::statement('ALTER TABLE `card_recharge_gift_product` ADD COLUMN `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `unit_price`');
                }
                
                foreach ($data['giftProducts'] as $product) {
                    // 检查必要字段是否存在
                    if (!empty($product['productId']) && !empty($product['times']) && !empty($product['unitPrice']) && !empty($product['manualSalary'])) {
                        DB::table('card_recharge_gift_product')->insert([
                            'recharge_id' => $id,
                            'product_id' => $product['productId'],
                            'times' => $product['times'],
                            'unit_price' => $product['unitPrice'],
                            'total_price' => $product['totalPrice'] ?? ($product['times'] * $product['unitPrice']),
                            'manual_salary' => $product['manualSalary']
                        ]);
                    } else {
                        error_log('Skipping invalid gift product: ' . json_encode($product));
                    }
                }
            }
            
            error_log('Recharge card added successfully with ID: ' . $id);
            return json(['code' => 200, 'message' => '新增充值卡成功', 'data' => ['id' => $id, ...$dbData]]);
            
        } catch (\Exception $e) {
            // 记录错误信息
            $errorMessage = '新增充值卡失败: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine();
            error_log($errorMessage);
            
            // 返回错误信息
            return json(['code' => 500, 'message' => $errorMessage]);
        }
    }
    
    /**
     * 更新充值卡
     * @param Request $request 请求对象
     * @param int $id 充值卡ID
     * @return array 更新结果
     */
    public function update(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查充值卡是否存在
            $card = DB::table('card_recharge')->where('id', $id)->where('is_delete', 0)->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            // 处理时间字段名映射：前端使用onlineTime/offlineTime，后端使用startTime/endTime
            $startTime = $data['startTime'] ?? $data['onlineTime'] ?? null;
            $endTime = $data['endTime'] ?? $data['offlineTime'] ?? null;
            $expireDate = $data['expireDate'] ?? null;
            
            // 转换ISO 8601格式的日期时间为MySQL支持的格式
            if (!empty($startTime)) {
                $startTime = $this->convertDateTimeFormat($startTime);
            }
            if (!empty($endTime)) {
                $endTime = $this->convertDateTimeFormat($endTime);
            }
            if (!empty($expireDate)) {
                $expireDate = $this->convertDateTimeFormat($expireDate);
            }
            
            // 构建更新数据
            $dbData = [
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 只更新提供的字段
            if (isset($data['cardName'])) {
                $dbData['card_name'] = $data['cardName'];
            }
            if (isset($data['cardCode'])) {
                $dbData['card_code'] = $data['cardCode'];
            }
            if (isset($data['amount'])) {
                $dbData['amount'] = $data['amount'];
            }
            if (isset($data['giftAmount'])) {
                $dbData['gift_amount'] = $data['giftAmount'];
            }
            if (isset($data['projectDiscount'])) {
                $dbData['project_discount'] = $data['projectDiscount'];
            }
            if (isset($data['productDiscount'])) {
                $dbData['product_discount'] = $data['productDiscount'];
            }
            if (isset($data['consumeRate'])) {
                $dbData['consume_rate'] = $data['consumeRate'];
            }
            if (isset($data['minRechargeLimit'])) {
                $dbData['min_recharge_limit'] = $data['minRechargeLimit'];
            }
            if (isset($data['status'])) {
                $dbData['status'] = $data['status'];
            }
            if (isset($startTime)) {
                $dbData['start_time'] = $startTime;
            }
            if (isset($endTime)) {
                $dbData['end_time'] = $endTime;
            }
            if (isset($expireDate)) {
                $dbData['expire_date'] = $expireDate;
            }
            if (isset($data['expireType'])) {
                $dbData['expire_type'] = $data['expireType'];
            }
            if (isset($data['description'])) {
                $dbData['description'] = $data['description'];
            }
            if (isset($data['remark'])) {
                $dbData['remark'] = $data['remark'];
            }
            if (isset($data['isModifiable'])) {
                $dbData['is_modifiable'] = $data['isModifiable'];
            }
            if (isset($data['isLimitOnce'])) {
                $dbData['is_limit_once'] = $data['isLimitOnce'];
            }
            if (isset($data['isExpireInvalid'])) {
                $dbData['is_expire_invalid'] = $data['isExpireInvalid'];
            }
            if (isset($data['isProjectExpire'])) {
                $dbData['is_project_expire'] = $data['isProjectExpire'];
            }
            if (isset($data['isProhibitDiscountModify'])) {
                $dbData['is_prohibit_discount_modify'] = $data['isProhibitDiscountModify'];
            }
            
            // 更新充值卡数据
            DB::table('card_recharge')->where('id', $id)->update($dbData);
            
            // 删除旧的销售分店关联
            DB::table('card_recharge_sale_store')->where('recharge_id', $id)->delete();
            
            // 处理销售分店
            if (!empty($data['saleStoreIds'])) {
                foreach ($data['saleStoreIds'] as $storeId) {
                    DB::table('card_recharge_sale_store')->insert([
                        'recharge_id' => $id,
                        'store_id' => $storeId
                    ]);
                }
            }
            
            // 删除旧的消费分店关联
            DB::table('card_recharge_consume_store')->where('recharge_id', $id)->delete();
            
            // 处理消费分店
            if (!empty($data['consumeStoreIds'])) {
                foreach ($data['consumeStoreIds'] as $storeId) {
                    DB::table('card_recharge_consume_store')->insert([
                        'recharge_id' => $id,
                        'store_id' => $storeId
                    ]);
                }
            }
            
            // 删除旧的销售部门关联
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_sale_department` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `department_id` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `department_id` (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::table('card_recharge_sale_department')->where('recharge_id', $id)->delete();
            
            // 处理销售部门
            if (!empty($data['saleDepartmentIds'])) {
                foreach ($data['saleDepartmentIds'] as $departmentId) {
                    DB::table('card_recharge_sale_department')->insert([
                        'recharge_id' => $id,
                        'department_id' => $departmentId
                    ]);
                }
            }
            
            // 删除旧的消费部门关联
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_consume_department` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `department_id` int(11) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `department_id` (`department_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::table('card_recharge_consume_department')->where('recharge_id', $id)->delete();
            
            // 处理消费部门
            if (!empty($data['consumeDepartmentIds'])) {
                foreach ($data['consumeDepartmentIds'] as $departmentId) {
                    DB::table('card_recharge_consume_department')->insert([
                        'recharge_id' => $id,
                        'department_id' => $departmentId
                    ]);
                }
            }
            
            // 删除旧的包含项目关联
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_project` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `project_id` int(11) NOT NULL,
                `times` int(11) NOT NULL,
                `unit_price` decimal(10,2) NOT NULL,
                `total_price` decimal(10,2) NOT NULL,
                `consume` decimal(10,2) NOT NULL,
                `manual_salary` decimal(10,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `project_id` (`project_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            DB::table('card_recharge_gift_project')->where('recharge_id', $id)->delete();
            
            // 处理包含项目
            if (!empty($data['giftProjects'])) {
                foreach ($data['giftProjects'] as $project) {
                    // 计算总价，如果没有提供则根据times和unitPrice计算
                    $totalPrice = isset($project['totalPrice']) ? $project['totalPrice'] : ($project['times'] * $project['unitPrice']);
                    
                    DB::table('card_recharge_gift_project')->insert([
                        'recharge_id' => $id,
                        'project_id' => $project['projectId'],
                        'times' => $project['times'],
                        'unit_price' => $project['unitPrice'],
                        'total_price' => $totalPrice,
                        'consume' => $project['consume'],
                        'manual_salary' => $project['manualSalary']
                    ]);
                }
            }
            
            // 删除旧的包含产品关联
            DB::statement('CREATE TABLE IF NOT EXISTS `card_recharge_gift_product` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `recharge_id` int(11) NOT NULL,
                `product_id` int(11) NOT NULL,
                `times` int(11) NOT NULL,
                `unit_price` decimal(10,2) NOT NULL,
                `total_price` decimal(10,2) NOT NULL DEFAULT 0.00,
                `manual_salary` decimal(10,2) NOT NULL,
                PRIMARY KEY (`id`),
                KEY `recharge_id` (`recharge_id`),
                KEY `product_id` (`product_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
            // 添加 total_price 字段（如果不存在）
            $columnExists = DB::select("SHOW COLUMNS FROM `card_recharge_gift_product` LIKE 'total_price'");
            if (empty($columnExists)) {
                DB::statement('ALTER TABLE `card_recharge_gift_product` ADD COLUMN `total_price` decimal(10,2) NOT NULL DEFAULT 0.00 AFTER `unit_price`');
            }
            DB::table('card_recharge_gift_product')->where('recharge_id', $id)->delete();
            
            // 处理包含产品
            if (!empty($data['giftProducts'])) {
                foreach ($data['giftProducts'] as $product) {
                    DB::table('card_recharge_gift_product')->insert([
                        'recharge_id' => $id,
                        'product_id' => $product['productId'],
                        'times' => $product['times'],
                        'unit_price' => $product['unitPrice'],
                        'total_price' => $product['totalPrice'] ?? ($product['times'] * $product['unitPrice']),
                        'manual_salary' => $product['manualSalary']
                    ]);
                }
            }
            
            return json(['code' => 200, 'message' => '更新充值卡成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update recharge card error: ' . $e->getMessage() . ' in ' . $e->getFile() . ' on line ' . $e->getLine());
            return json(['code' => 500, 'message' => '更新充值卡失败: ' . $e->getMessage()]);
        }
    }
    
    /**
     * 删除充值卡
     * @param Request $request 请求对象
     * @param int $id 充值卡ID
     * @return array 删除结果
     */
    public function delete(Request $request, $id)
    {
        try {
            // 检查充值卡是否存在
            $card = DB::table('card_recharge')->where('id', $id)->where('is_delete', 0)->first();
            if (!$card) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 开始事务
            DB::beginTransaction();
            
            try {
                // 软删除充值卡
                DB::table('card_recharge')->where('id', $id)->update(['is_delete' => 1]);
                
                // 提交事务
                DB::commit();
                
                return json(['code' => 200, 'message' => '删除充值卡成功']);
            } catch (\Exception $e) {
                // 回滚事务
                DB::rollBack();
                throw $e;
            }
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete recharge card error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除充值卡失败，请稍后重试']);
        }
    }
    
    /**
     * 转换日期时间格式
     * 将ISO 8601格式转换为MySQL支持的datetime格式
     * @param string $dateTime ISO 8601格式的日期时间字符串
     * @return string MySQL支持的datetime格式字符串
     */
    private function convertDateTimeFormat($dateTime)
    {
        // 如果已经是MySQL格式，直接返回
        if (preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $dateTime)) {
            return $dateTime;
        }
        
        // 尝试使用DateTime对象解析
        try {
            $datetime = new \DateTime($dateTime);
            return $datetime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            // 如果解析失败，记录错误并返回null
            error_log('Failed to parse datetime: ' . $dateTime . ', error: ' . $e->getMessage());
            return null;
        }
    }
}
