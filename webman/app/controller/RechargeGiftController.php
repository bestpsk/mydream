<?php

namespace app\controller;

use support\Request;
use support\DB;

class RechargeGiftController
{
    /**
     * 获取配赠项目列表
     * @param Request $request 请求对象
     * @param int $rechargeId 充值卡ID
     * @return array 配赠项目列表数据
     */
    public function getGiftProjects(Request $request, $rechargeId)
    {
        try {
            // 检查充值卡是否存在
            $recharge = DB::table('card_recharge')->where('id', $rechargeId)->where('isDelete', 0)->first();
            if (!$recharge) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 构建查询
            $query = DB::table('card_recharge_gift_project')->where('recharge_id', $rechargeId);
            
            // 执行查询
            $giftProjects = $query->get();
            
            // 获取项目名称
            $projectIds = $giftProjects->pluck('project_id')->toArray();
            $projects = [];
            if (!empty($projectIds)) {
                $projects = DB::table('card_project')->whereIn('id', $projectIds)->get()->keyBy('id');
            }
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProjects = $giftProjects->map(function($project) use ($projects) {
                $projectName = '';
                if (isset($projects[$project->project_id])) {
                    $projectName = $projects[$project->project_id]->project_name;
                }
                return [
                    'id' => $project->id,
                    'rechargeId' => $project->recharge_id,
                    'projectId' => $project->project_id,
                    'projectName' => $projectName,
                    'times' => $project->times,
                    'unitPrice' => $project->unit_price,
                    'consume' => $project->consume,
                    'manualSalary' => $project->manual_salary,
                    'createdAt' => $project->created_at,
                    'updatedAt' => $project->updated_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取配赠项目列表成功', 'data' => $formattedProjects]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get gift projects error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取配赠项目列表失败，请稍后重试']);
        }
    }
    
    /**
     * 新增配赠项目
     * @param Request $request 请求对象
     * @return array 新增结果
     */
    public function addGiftProject(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['rechargeId'])) {
                return json(['code' => 400, 'message' => '充值卡ID不能为空']);
            }
            if (empty($data['projectId'])) {
                return json(['code' => 400, 'message' => '项目ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '次数不能为空']);
            }
            
            // 检查充值卡是否存在
            $recharge = DB::table('card_recharge')->where('id', $data['rechargeId'])->where('isDelete', 0)->first();
            if (!$recharge) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 检查项目是否存在
            $project = DB::table('card_project')->where('id', $data['projectId'])->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'recharge_id' => $data['rechargeId'],
                'project_id' => $data['projectId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'] ?? 0,
                'consume' => $data['consume'] ?? 0,
                'manual_salary' => $data['manualSalary'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 插入配赠项目数据
            $id = DB::table('card_recharge_gift_project')->insertGetId($dbData);
            
            return json(['code' => 200, 'message' => '新增配赠项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '新增配赠项目失败，请稍后重试']);
        }
    }
    
    /**
     * 更新配赠项目
     * @param Request $request 请求对象
     * @param int $id 配赠项目ID
     * @return array 更新结果
     */
    public function updateGiftProject(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查配赠项目是否存在
            $giftProject = DB::table('card_recharge_gift_project')->where('id', $id)->first();
            if (!$giftProject) {
                return json(['code' => 404, 'message' => '配赠项目不存在']);
            }
            
            // 验证必要参数
            if (empty($data['projectId'])) {
                return json(['code' => 400, 'message' => '项目ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '次数不能为空']);
            }
            
            // 检查项目是否存在
            $project = DB::table('card_project')->where('id', $data['projectId'])->first();
            if (!$project) {
                return json(['code' => 404, 'message' => '项目不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'project_id' => $data['projectId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'] ?? 0,
                'consume' => $data['consume'] ?? 0,
                'manual_salary' => $data['manualSalary'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 更新配赠项目数据
            DB::table('card_recharge_gift_project')->where('id', $id)->update($dbData);
            
            return json(['code' => 200, 'message' => '更新配赠项目成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新配赠项目失败，请稍后重试']);
        }
    }
    
    /**
     * 删除配赠项目
     * @param Request $request 请求对象
     * @param int $id 配赠项目ID
     * @return array 删除结果
     */
    public function deleteGiftProject(Request $request, $id)
    {
        try {
            // 检查配赠项目是否存在
            $giftProject = DB::table('card_recharge_gift_project')->where('id', $id)->first();
            if (!$giftProject) {
                return json(['code' => 404, 'message' => '配赠项目不存在']);
            }
            
            // 删除配赠项目
            DB::table('card_recharge_gift_project')->where('id', $id)->delete();
            
            return json(['code' => 200, 'message' => '删除配赠项目成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete gift project error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除配赠项目失败，请稍后重试']);
        }
    }
    
    /**
     * 获取配赠产品列表
     * @param Request $request 请求对象
     * @param int $rechargeId 充值卡ID
     * @return array 配赠产品列表数据
     */
    public function getGiftProducts(Request $request, $rechargeId)
    {
        try {
            // 检查充值卡是否存在
            $recharge = DB::table('card_recharge')->where('id', $rechargeId)->where('isDelete', 0)->first();
            if (!$recharge) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 构建查询
            $query = DB::table('card_recharge_gift_product')->where('recharge_id', $rechargeId);
            
            // 执行查询
            $giftProducts = $query->get();
            
            // 获取产品名称
            $productIds = $giftProducts->pluck('product_id')->toArray();
            $products = [];
            if (!empty($productIds)) {
                $products = DB::table('card_product')->whereIn('id', $productIds)->get()->keyBy('id');
            }
            
            // 转换字段名：数据库字段名 转 camelCase
            $formattedProducts = $giftProducts->map(function($product) use ($products) {
                $productName = '';
                if (isset($products[$product->product_id])) {
                    $productName = $products[$product->product_id]->product_name;
                }
                return [
                    'id' => $product->id,
                    'rechargeId' => $product->recharge_id,
                    'productId' => $product->product_id,
                    'productName' => $productName,
                    'times' => $product->times,
                    'unitPrice' => $product->unit_price,
                    'consume' => $product->consume,
                    'manualSalary' => $product->manual_salary,
                    'createdAt' => $product->created_at,
                    'updatedAt' => $product->updated_at
                ];
            });
            
            return json(['code' => 200, 'message' => '获取配赠产品列表成功', 'data' => $formattedProducts]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Get gift products error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '获取配赠产品列表失败，请稍后重试']);
        }
    }
    
    /**
     * 新增配赠产品
     * @param Request $request 请求对象
     * @return array 新增结果
     */
    public function addGiftProduct(Request $request)
    {
        try {
            $data = $request->post();
            
            // 验证必要参数
            if (empty($data['rechargeId'])) {
                return json(['code' => 400, 'message' => '充值卡ID不能为空']);
            }
            if (empty($data['productId'])) {
                return json(['code' => 400, 'message' => '产品ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '次数不能为空']);
            }
            
            // 检查充值卡是否存在
            $recharge = DB::table('card_recharge')->where('id', $data['rechargeId'])->where('isDelete', 0)->first();
            if (!$recharge) {
                return json(['code' => 404, 'message' => '充值卡不存在']);
            }
            
            // 检查产品是否存在
            $product = DB::table('card_product')->where('id', $data['productId'])->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'recharge_id' => $data['rechargeId'],
                'product_id' => $data['productId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'] ?? 0,
                'consume' => $data['consume'] ?? 0,
                'manual_salary' => $data['manualSalary'] ?? 0,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 插入配赠产品数据
            $id = DB::table('card_recharge_gift_product')->insertGetId($dbData);
            
            return json(['code' => 200, 'message' => '新增配赠产品成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Add gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '新增配赠产品失败，请稍后重试']);
        }
    }
    
    /**
     * 更新配赠产品
     * @param Request $request 请求对象
     * @param int $id 配赠产品ID
     * @return array 更新结果
     */
    public function updateGiftProduct(Request $request, $id)
    {
        try {
            $data = $request->post();
            
            // 检查配赠产品是否存在
            $giftProduct = DB::table('card_recharge_gift_product')->where('id', $id)->first();
            if (!$giftProduct) {
                return json(['code' => 404, 'message' => '配赠产品不存在']);
            }
            
            // 验证必要参数
            if (empty($data['productId'])) {
                return json(['code' => 400, 'message' => '产品ID不能为空']);
            }
            if (empty($data['times'])) {
                return json(['code' => 400, 'message' => '次数不能为空']);
            }
            
            // 检查产品是否存在
            $product = DB::table('card_product')->where('id', $data['productId'])->first();
            if (!$product) {
                return json(['code' => 404, 'message' => '产品不存在']);
            }
            
            // 转换字段名：camelCase 转 snake_case
            $dbData = [
                'product_id' => $data['productId'],
                'times' => $data['times'],
                'unit_price' => $data['unitPrice'] ?? 0,
                'consume' => $data['consume'] ?? 0,
                'manual_salary' => $data['manualSalary'] ?? 0,
                'updated_at' => date('Y-m-d H:i:s')
            ];
            
            // 更新配赠产品数据
            DB::table('card_recharge_gift_product')->where('id', $id)->update($dbData);
            
            return json(['code' => 200, 'message' => '更新配赠产品成功', 'data' => ['id' => $id, ...$dbData]]);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Update gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '更新配赠产品失败，请稍后重试']);
        }
    }
    
    /**
     * 删除配赠产品
     * @param Request $request 请求对象
     * @param int $id 配赠产品ID
     * @return array 删除结果
     */
    public function deleteGiftProduct(Request $request, $id)
    {
        try {
            // 检查配赠产品是否存在
            $giftProduct = DB::table('card_recharge_gift_product')->where('id', $id)->first();
            if (!$giftProduct) {
                return json(['code' => 404, 'message' => '配赠产品不存在']);
            }
            
            // 删除配赠产品
            DB::table('card_recharge_gift_product')->where('id', $id)->delete();
            
            return json(['code' => 200, 'message' => '删除配赠产品成功']);
        } catch (\Exception $e) {
            // 记录错误日志
            error_log('Delete gift product error: ' . $e->getMessage());
            return json(['code' => 500, 'message' => '删除配赠产品失败，请稍后重试']);
        }
    }
}
