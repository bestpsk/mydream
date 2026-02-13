-- 预约管理系统数据库表结构

-- 部门表
CREATE TABLE IF NOT EXISTS `card_department` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '部门ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `name` VARCHAR(100) NOT NULL COMMENT '部门名称',
  `description` VARCHAR(255) DEFAULT '' COMMENT '部门描述',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门表';

-- 客户表
CREATE TABLE IF NOT EXISTS `card_customer` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '客户ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `name` VARCHAR(100) NOT NULL COMMENT '客户姓名',
  `phone` VARCHAR(20) NOT NULL COMMENT '客户手机号',
  `member_id` VARCHAR(50) DEFAULT '' COMMENT '会员ID',
  `member_level` VARCHAR(50) DEFAULT '' COMMENT '会员等级',
  `balance` DECIMAL(10,2) DEFAULT 0 COMMENT '余额',
  `points` INT DEFAULT 0 COMMENT '积分',
  `care_preference` VARCHAR(255) DEFAULT '' COMMENT '护理偏好',
  `service_requirement` VARCHAR(255) DEFAULT '' COMMENT '服务要求',
  `six_period_management` VARCHAR(50) DEFAULT '' COMMENT '六期管理',
  `service_taboo` VARCHAR(255) DEFAULT '' COMMENT '服务禁忌',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户表';

-- 员工表
CREATE TABLE IF NOT EXISTS `card_employee` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '员工ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `name` VARCHAR(100) NOT NULL COMMENT '员工姓名',
  `phone` VARCHAR(20) DEFAULT '' COMMENT '员工手机号',
  `position` VARCHAR(100) DEFAULT '' COMMENT '职位',
  `department_id` INT UNSIGNED DEFAULT 0 COMMENT '所属部门ID',
  `store_id` INT UNSIGNED NOT NULL COMMENT '所属门店ID',
  `level` VARCHAR(50) DEFAULT '' COMMENT '级别',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工表';

-- 门店表
CREATE TABLE IF NOT EXISTS `card_store` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '门店ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `name` VARCHAR(100) NOT NULL COMMENT '门店名称',
  `address` VARCHAR(255) DEFAULT '' COMMENT '门店地址',
  `phone` VARCHAR(20) DEFAULT '' COMMENT '门店电话',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店表';

-- 房间表（床位）
CREATE TABLE IF NOT EXISTS `card_room` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '房间ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `store_id` INT UNSIGNED NOT NULL COMMENT '所属门店ID',
  `name` VARCHAR(100) NOT NULL COMMENT '房间名称',
  `type` VARCHAR(50) DEFAULT '' COMMENT '房间类型',
  `status` VARCHAR(50) DEFAULT 'available' COMMENT '房间状态',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='房间表';

-- 项目表
CREATE TABLE IF NOT EXISTS `card_project` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '项目ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `name` VARCHAR(100) NOT NULL COMMENT '项目名称',
  `description` VARCHAR(255) DEFAULT '' COMMENT '项目描述',
  `price` DECIMAL(10,2) NOT NULL COMMENT '项目价格',
  `duration` INT DEFAULT 60 COMMENT '项目时长（分钟）',
  `category_id` INT UNSIGNED DEFAULT 0 COMMENT '项目分类ID',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目表';

-- 客户项目表
CREATE TABLE IF NOT EXISTS `card_customer_project` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `customer_id` INT UNSIGNED NOT NULL COMMENT '客户ID',
  `project_id` INT UNSIGNED NOT NULL COMMENT '项目ID',
  `quantity` INT DEFAULT 1 COMMENT '数量',
  `remaining` INT DEFAULT 1 COMMENT '剩余次数',
  `purchase_date` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '购买日期',
  `expire_date` DATETIME DEFAULT NULL COMMENT '过期日期',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  INDEX `idx_customer_project` (`customer_id`, `project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='客户项目表';

-- 预约表
CREATE TABLE IF NOT EXISTS `card_appointment` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '预约ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `appointment_no` VARCHAR(50) NOT NULL COMMENT '预约编号',
  `customer_id` INT UNSIGNED NOT NULL COMMENT '客户ID',
  `customer_name` VARCHAR(100) NOT NULL COMMENT '客户姓名',
  `customer_phone` VARCHAR(20) NOT NULL COMMENT '客户手机号',
  `department_id` INT UNSIGNED DEFAULT 0 COMMENT '部门ID',
  `employee_id` INT UNSIGNED NOT NULL COMMENT '服务员工ID',
  `employee_name` VARCHAR(100) NOT NULL COMMENT '服务员工姓名',
  `manager_id` INT UNSIGNED DEFAULT 0 COMMENT '管理者ID',
  `manager_name` VARCHAR(100) DEFAULT '' COMMENT '管理者姓名',
  `room_id` INT UNSIGNED NOT NULL COMMENT '房间ID',
  `room_name` VARCHAR(100) NOT NULL COMMENT '房间名称',
  `appointment_date` DATE NOT NULL COMMENT '预约日期',
  `start_time` TIME NOT NULL COMMENT '开始时间',
  `end_time` TIME NOT NULL COMMENT '结束时间',
  `appointment_type` VARCHAR(20) DEFAULT 'point' COMMENT '预约方式：point-点排，round-轮排',
  `status` VARCHAR(20) DEFAULT 'pending' COMMENT '状态：pending-待服务，processing-护理中，completed-已完成，cancelled-已取消',
  `remark` VARCHAR(255) DEFAULT '' COMMENT '备注',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  INDEX `idx_appointment_date` (`appointment_date`),
  INDEX `idx_customer_id` (`customer_id`),
  INDEX `idx_employee_id` (`employee_id`),
  INDEX `idx_room_id` (`room_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='预约表';

-- 预约项目表
CREATE TABLE IF NOT EXISTS `card_appointment_item` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `appointment_id` INT UNSIGNED NOT NULL COMMENT '预约ID',
  `project_id` INT UNSIGNED NOT NULL COMMENT '项目ID',
  `project_name` VARCHAR(100) NOT NULL COMMENT '项目名称',
  `price` DECIMAL(10,2) NOT NULL COMMENT '项目价格',
  `quantity` INT DEFAULT 1 COMMENT '数量',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  INDEX `idx_appointment_id` (`appointment_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='预约项目表';

-- 员工服务项目表
CREATE TABLE IF NOT EXISTS `card_employee_project` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT 'ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `employee_id` INT UNSIGNED NOT NULL COMMENT '员工ID',
  `project_id` INT UNSIGNED NOT NULL COMMENT '项目ID',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  INDEX `idx_employee_project` (`employee_id`, `project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工服务项目表';

-- 项目分类表
CREATE TABLE IF NOT EXISTS `card_project_category` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY COMMENT '分类ID',
  `company_id` INT UNSIGNED NOT NULL COMMENT '所属公司ID',
  `category_name` VARCHAR(100) NOT NULL COMMENT '分类名称',
  `sort` INT DEFAULT 0 COMMENT '排序',
  `isDelete` TINYINT DEFAULT 0 COMMENT '是否删除',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='项目分类表';

-- 插入示例数据

-- 部门示例数据
INSERT INTO `card_department` (`company_id`, `name`, `description`) VALUES
(1, '美容部', '提供美容相关服务'),
(1, '美发部', '提供美发相关服务'),
(1, '美甲部', '提供美甲相关服务'),
(1, '身体护理部', '提供身体护理相关服务');

-- 门店示例数据
INSERT INTO `card_store` (`company_id`, `name`, `address`, `phone`) VALUES
(1, '旗舰店', '北京市朝阳区建国路88号', '010-12345678'),
(1, '中心店', '北京市海淀区中关村大街1号', '010-87654321'),
(1, '分店', '北京市西城区西单大街100号', '010-11223344');

-- 房间示例数据
INSERT INTO `card_room` (`company_id`, `store_id`, `name`, `type`) VALUES
(1, 1, 'VIP房间1', 'VIP'),
(1, 1, 'VIP房间2', 'VIP'),
(1, 1, '普通房间1', '普通'),
(1, 2, 'VIP房间', 'VIP'),
(1, 2, '普通房间', '普通'),
(1, 3, '唯一房间', '普通');

-- 项目分类示例数据
INSERT INTO `card_project_category` (`company_id`, `category_name`, `sort`) VALUES
(1, '面部护理', 1),
(1, '身体护理', 2),
(1, '美发造型', 3),
(1, '美甲美睫', 4),
(1, '其他服务', 5);

-- 项目示例数据
INSERT INTO `card_project` (`company_id`, `name`, `description`, `price`, `duration`, `category_id`) VALUES
(1, '面部基础护理', '基础面部清洁护理', 198.00, 60, 1),
(1, '面部深层护理', '深层清洁+营养导入', 398.00, 90, 1),
(1, '身体基础按摩', '全身基础按摩', 298.00, 60, 2),
(1, '身体精油按摩', '精油全身按摩', 498.00, 90, 2),
(1, '精剪造型', '专业精剪+造型', 88.00, 45, 3),
(1, '烫发造型', '烫发+造型设计', 388.00, 180, 3),
(1, '基础美甲', '基础美甲服务', 68.00, 60, 4),
(1, '高级美甲', '高级美甲+钻饰', 168.00, 90, 4),
(1, '美容咨询', '专业美容顾问咨询', 50.00, 30, 5),
(1, '健康评估', '身体健康状况评估', 100.00, 45, 5);

-- 员工示例数据
INSERT INTO `card_employee` (`company_id`, `name`, `phone`, `position`, `department_id`, `store_id`, `level`) VALUES
(1, '张美容师', '13800138001', '高级美容师', 1, 1, '高级'),
(1, '李美容师', '13800138002', '中级美容师', 1, 1, '中级'),
(1, '王美发师', '13800138003', '高级美发师', 2, 1, '高级'),
(1, '赵美甲师', '13800138004', '中级美甲师', 3, 2, '中级'),
(1, '钱护理师', '13800138005', '高级护理师', 4, 2, '高级'),
(1, '孙经理', '13800138006', '店长', 1, 1, '管理层');

-- 客户示例数据
INSERT INTO `card_customer` (`company_id`, `name`, `phone`, `member_id`, `member_level`, `balance`, `points`) VALUES
(1, '张三', '13900139001', 'VIP001', '钻石会员', 1000.00, 2000),
(1, '李四', '13900139002', 'VIP002', '黄金会员', 500.00, 1000),
(1, '王五', '13900139003', 'VIP003', '白银会员', 200.00, 500),
(1, '赵六', '13900139004', 'VIP004', '普通会员', 0.00, 100),
(1, '孙七', '13900139005', 'VIP005', '黄金会员', 800.00, 1500);

-- 客户项目示例数据
INSERT INTO `card_customer_project` (`company_id`, `customer_id`, `project_id`, `quantity`, `remaining`) VALUES
(1, 1, 1, 10, 8),
(1, 1, 3, 5, 3),
(1, 2, 2, 5, 5),
(1, 3, 5, 3, 2),
(1, 4, 7, 2, 1),
(1, 5, 4, 3, 3);

-- 员工服务项目示例数据
INSERT INTO `card_employee_project` (`company_id`, `employee_id`, `project_id`) VALUES
(1, 1, 1),
(1, 1, 2),
(1, 1, 3),
(1, 1, 4),
(1, 2, 1),
(1, 2, 2),
(1, 3, 5),
(1, 3, 6),
(1, 4, 7),
(1, 4, 8),
(1, 5, 3),
(1, 5, 4);

-- 预约示例数据
INSERT INTO `card_appointment` (`company_id`, `appointment_no`, `customer_id`, `customer_name`, `customer_phone`, `department_id`, `employee_id`, `employee_name`, `manager_id`, `manager_name`, `room_id`, `room_name`, `appointment_date`, `start_time`, `end_time`, `appointment_type`, `status`) VALUES
(1, 'APT202602040001', 1, '张三', '13900139001', 1, 1, '张美容师', 6, '孙经理', 1, 'VIP房间1', '2026-02-04', '10:00:00', '11:00:00', 'point', 'confirmed'),
(1, 'APT202602040002', 2, '李四', '13900139002', 1, 2, '李美容师', 6, '孙经理', 3, '普通房间1', '2026-02-04', '14:00:00', '15:30:00', 'point', 'pending'),
(1, 'APT202602040003', 3, '王五', '13900139003', 2, 3, '王美发师', 6, '孙经理', 2, 'VIP房间2', '2026-02-04', '16:00:00', '17:30:00', 'round', 'completed');

-- 预约项目示例数据
INSERT INTO `card_appointment_item` (`company_id`, `appointment_id`, `project_id`, `project_name`, `price`, `quantity`) VALUES
(1, 1, 1, '面部基础护理', 198.00, 1),
(1, 1, 3, '身体基础按摩', 298.00, 1),
(1, 2, 2, '面部深层护理', 398.00, 1),
(1, 3, 5, '精剪造型', 88.00, 1),
(1, 3, 6, '烫发造型', 388.00, 1);
