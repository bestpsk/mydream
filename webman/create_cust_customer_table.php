<?php

require_once __DIR__ . '/vendor/autoload.php';

// 加载配置
$config = require __DIR__ . '/config/database.php';

// 创建数据库连接
$dsn = "mysql:host={$config['connections']['mysql']['host']};port={$config['connections']['mysql']['port']};dbname={$config['connections']['mysql']['database']};charset={$config['connections']['mysql']['charset']}";
$username = $config['connections']['mysql']['username'];
$password = $config['connections']['mysql']['password'];

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "开始创建cust_customer表...\n";
    
    // 创建cust_customer表
    $sql = "CREATE TABLE IF NOT EXISTS `cust_customer` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `store_id` int(11) NOT NULL COMMENT '所属门店ID',
        `department_id` int(11) NOT NULL COMMENT '所属部门ID',
        `member_card` varchar(50) NOT NULL COMMENT '会员卡号',
        `name` varchar(50) NOT NULL COMMENT '姓名',
        `gender` tinyint(1) NOT NULL COMMENT '性别(1:男,2:女)',
        `phone` varchar(20) NOT NULL COMMENT '电话',
        `birthday` date DEFAULT NULL COMMENT '生日',
        `birthday_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '生日类别(1:阳历,2:阴历)',
        `points` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
        `register_time` datetime NOT NULL COMMENT '注册时间',
        `source` varchar(100) DEFAULT NULL COMMENT '来源',
        `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
        `archive_number` varchar(100) NOT NULL COMMENT '档案编号',
        `level` varchar(50) DEFAULT '普通客户' COMMENT '客户级别',
        `service_staff_id` int(11) DEFAULT NULL COMMENT '服务人ID(美容师)',
        `manager_id` int(11) DEFAULT NULL COMMENT '管理人ID(顾问)',
        `last_consume_time` datetime DEFAULT NULL COMMENT '最近消费时间',
        `last_consume_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消费金额',
        `last_deplete_time` datetime DEFAULT NULL COMMENT '最近消耗时间',
        `last_deplete_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消耗金额',
        `remark` text COMMENT '备注',
        `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1:正常,0:禁用)',
        `created_at` datetime NOT NULL COMMENT '创建时间',
        `updated_at` datetime NOT NULL COMMENT '更新时间',
        PRIMARY KEY (`id`),
        UNIQUE KEY `member_card` (`member_card`),
        UNIQUE KEY `archive_number` (`archive_number`),
        UNIQUE KEY `phone` (`phone`),
        KEY `store_id` (`store_id`),
        KEY `department_id` (`department_id`),
        KEY `level` (`level`),
        KEY `register_time` (`register_time`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户信息表';";
    
    $pdo->exec($sql);
    echo "cust_customer表创建成功！\n";
    
    // 插入测试数据
    echo "开始插入测试数据...\n";
    
    $testData = [
        [
            'store_id' => 1,
            'department_id' => 1,
            'member_card' => 'VIP001',
            'name' => '张三',
            'gender' => 1,
            'phone' => '13800138000',
            'birthday' => '1990-01-01',
            'birthday_type' => 1,
            'points' => 1000,
            'register_time' => date('Y-m-d H:i:s'),
            'source' => '门店推荐',
            'avatar' => '',
            'archive_number' => 'ST001-DEP001-'.date('Ymd').'001',
            'level' => '银卡客户',
            'service_staff_id' => 1,
            'manager_id' => 1,
            'last_consume_time' => date('Y-m-d H:i:s'),
            'last_consume_amount' => 500.00,
            'last_deplete_time' => date('Y-m-d H:i:s'),
            'last_deplete_amount' => 200.00,
            'remark' => '测试客户',
            'status' => 1
        ],
        [
            'store_id' => 1,
            'department_id' => 2,
            'member_card' => 'VIP002',
            'name' => '李四',
            'gender' => 2,
            'phone' => '13900139000',
            'birthday' => '1992-02-02',
            'birthday_type' => 2,
            'points' => 2000,
            'register_time' => date('Y-m-d H:i:s'),
            'source' => '线上推广',
            'avatar' => '',
            'archive_number' => 'ST001-DEP002-'.date('Ymd').'002',
            'level' => '金卡客户',
            'service_staff_id' => 2,
            'manager_id' => 2,
            'last_consume_time' => date('Y-m-d H:i:s'),
            'last_consume_amount' => 1000.00,
            'last_deplete_time' => date('Y-m-d H:i:s'),
            'last_deplete_amount' => 500.00,
            'remark' => '测试客户2',
            'status' => 1
        ]
    ];
    
    foreach ($testData as $data) {
        $stmt = $pdo->prepare("INSERT INTO `cust_customer` (
            `store_id`, `department_id`, `member_card`, `name`, `gender`, `phone`, 
            `birthday`, `birthday_type`, `points`, `register_time`, `source`, `avatar`, 
            `archive_number`, `level`, `service_staff_id`, `manager_id`, `last_consume_time`, 
            `last_consume_amount`, `last_deplete_time`, `last_deplete_amount`, `remark`, `status`, 
            `created_at`, `updated_at`
        ) VALUES (
            :store_id, :department_id, :member_card, :name, :gender, :phone, 
            :birthday, :birthday_type, :points, :register_time, :source, :avatar, 
            :archive_number, :level, :service_staff_id, :manager_id, :last_consume_time, 
            :last_consume_amount, :last_deplete_time, :last_deplete_amount, :remark, :status, 
            NOW(), NOW()
        )");
        
        $stmt->execute($data);
    }
    
    echo "测试数据插入成功！\n";
    
} catch (PDOException $e) {
    echo "连接数据库失败: " . $e->getMessage() . "\n";
}
?>