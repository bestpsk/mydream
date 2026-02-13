<?php

// 直接使用数据库配置
$config = [
    'host' => '127.0.0.1',
    'database' => 'mydream',
    'username' => 'root',
    'password' => '123456'
];

try {
    // 连接数据库
    $connection = new PDO(
        'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
        $config['username'],
        $config['password']
    );
    $connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "数据库连接成功\n";
    
    // 测试插入充值卡数据
    echo "开始测试插入充值卡数据...\n";
    
    $currentTime = date('Y-m-d H:i:s');
    
    // 准备SQL语句
    $sql = "
    INSERT INTO `card_recharge` (
        `company_id`, 
        `card_name`, 
        `amount`, 
        `gift_amount`, 
        `project_discount`, 
        `product_discount`, 
        `consume_rate`, 
        `min_recharge_limit`, 
        `start_time`, 
        `end_time`, 
        `expire_date`, 
        `expire_type`, 
        `description`, 
        `remark`, 
        `is_modifiable`, 
        `is_limit_once`, 
        `is_expire_invalid`, 
        `is_project_expire`, 
        `is_prohibit_discount_modify`, 
        `isDelete`, 
        `created_at`, 
        `updated_at`
    ) VALUES (
        :company_id, 
        :card_name, 
        :amount, 
        :gift_amount, 
        :project_discount, 
        :product_discount, 
        :consume_rate, 
        :min_recharge_limit, 
        :start_time, 
        :end_time, 
        :expire_date, 
        :expire_type, 
        :description, 
        :remark, 
        :is_modifiable, 
        :is_limit_once, 
        :is_expire_invalid, 
        :is_project_expire, 
        :is_prohibit_discount_modify, 
        :isDelete, 
        :created_at, 
        :updated_at
    )
    ";
    
    $stmt = $connection->prepare($sql);
    
    // 绑定参数
    $stmt->bindParam(':company_id', $companyId);
    $stmt->bindParam(':card_name', $cardName);
    $stmt->bindParam(':amount', $amount);
    $stmt->bindParam(':gift_amount', $giftAmount);
    $stmt->bindParam(':project_discount', $projectDiscount);
    $stmt->bindParam(':product_discount', $productDiscount);
    $stmt->bindParam(':consume_rate', $consumeRate);
    $stmt->bindParam(':min_recharge_limit', $minRechargeLimit);
    $stmt->bindParam(':start_time', $startTime);
    $stmt->bindParam(':end_time', $endTime);
    $stmt->bindParam(':expire_date', $expireDate);
    $stmt->bindParam(':expire_type', $expireType);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':remark', $remark);
    $stmt->bindParam(':is_modifiable', $isModifiable);
    $stmt->bindParam(':is_limit_once', $isLimitOnce);
    $stmt->bindParam(':is_expire_invalid', $isExpireInvalid);
    $stmt->bindParam(':is_project_expire', $isProjectExpire);
    $stmt->bindParam(':is_prohibit_discount_modify', $isProhibitDiscountModify);
    $stmt->bindParam(':isDelete', $isDelete);
    $stmt->bindParam(':created_at', $createdAt);
    $stmt->bindParam(':updated_at', $updatedAt);
    
    // 设置参数值
    $companyId = 1; // 假设公司ID为1
    $cardName = "测试充值卡";
    $amount = 1000.00;
    $giftAmount = 100.00;
    $projectDiscount = 10.00;
    $productDiscount = 10.00;
    $consumeRate = 100;
    $minRechargeLimit = 0.00;
    $startTime = null;
    $endTime = null;
    $expireDate = null;
    $expireType = 3;
    $description = "测试充值卡描述";
    $remark = "测试充值卡备注";
    $isModifiable = 1;
    $isLimitOnce = 0;
    $isExpireInvalid = 1;
    $isProjectExpire = 1;
    $isProhibitDiscountModify = 0;
    $isDelete = 0;
    $createdAt = $currentTime;
    $updatedAt = $currentTime;
    
    // 执行插入操作
    $stmt->execute();
    
    $lastInsertId = $connection->lastInsertId();
    echo "充值卡创建成功，ID: $lastInsertId\n";
    
    // 测试关联表操作
    echo "开始测试关联表操作...\n";
    
    // 测试插入销售分店
    $saleStoreSql = "INSERT INTO `card_recharge_sale_store` (`recharge_id`, `store_id`) VALUES (:recharge_id, :store_id)";
    $saleStoreStmt = $connection->prepare($saleStoreSql);
    $saleStoreStmt->bindParam(':recharge_id', $lastInsertId);
    $saleStoreStmt->bindParam(':store_id', $storeId);
    
    $storeId = 1; // 假设店铺ID为1
    $saleStoreStmt->execute();
    echo "销售分店关联成功\n";
    
    // 测试插入消费分店
    $consumeStoreSql = "INSERT INTO `card_recharge_consume_store` (`recharge_id`, `store_id`) VALUES (:recharge_id, :store_id)";
    $consumeStoreStmt = $connection->prepare($consumeStoreSql);
    $consumeStoreStmt->bindParam(':recharge_id', $lastInsertId);
    $consumeStoreStmt->bindParam(':store_id', $storeId);
    $consumeStoreStmt->execute();
    echo "消费分店关联成功\n";
    
    echo "所有测试操作成功完成\n";
    
} catch (PDOException $e) {
    echo "数据库错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "其他错误: " . $e->getMessage() . "\n";
    echo "错误代码: " . $e->getCode() . "\n";
    echo "错误文件: " . $e->getFile() . "\n";
    echo "错误行号: " . $e->getLine() . "\n";
}
