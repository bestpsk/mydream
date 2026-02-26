-- --------------------------------------------------------
-- 主机:                           127.0.0.1
-- 服务器版本:                        8.0.12 - MySQL Community Server - GPL
-- 服务器操作系统:                      Win64
-- HeidiSQL 版本:                  11.0.0.5919
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- 导出  表 mydream.bedroom 结构
CREATE TABLE IF NOT EXISTS `bedroom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `room_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `bed_count` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_room_name` (`room_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  mydream.bedroom 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `bedroom` DISABLE KEYS */;
INSERT INTO `bedroom` (`id`, `store_id`, `room_name`, `bed_count`, `created_at`, `updated_at`) VALUES
	(1, 1, 'VIP1', 1, '2026-02-03 21:43:41', '2026-02-03 21:44:07'),
	(2, 2, '爱琴海', 1, '2026-02-04 02:08:45', '2026-02-04 02:08:45'),
	(3, 1, '大草原', 2, '2026-02-12 12:08:17', '2026-02-12 12:08:17'),
	(4, 2, '哈哈镜', 2, '2026-02-12 12:08:30', '2026-02-12 12:08:30');
/*!40000 ALTER TABLE `bedroom` ENABLE KEYS */;

-- 导出  表 mydream.card_package 结构
CREATE TABLE IF NOT EXISTS `card_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `card_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
  `original_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '原价',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `project_count` int(11) NOT NULL DEFAULT '0' COMMENT '包含项目数',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `card_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '编码，用于首字母搜索',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '套餐卡描述',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `is_modifiable` tinyint(1) DEFAULT '0' COMMENT '是否禁止修改',
  `is_add_new_item_forbidden` tinyint(1) DEFAULT '0' COMMENT '是否禁止添加新项',
  `is_limit_once` tinyint(1) DEFAULT '0' COMMENT '是否限购1次',
  `is_expire_invalid` tinyint(1) DEFAULT '0' COMMENT '是否过期作废',
  `is_gift_forbidden` tinyint(1) DEFAULT '0' COMMENT '是否禁止赠送',
  `online_time` datetime DEFAULT NULL COMMENT '上线时间',
  `offline_time` datetime DEFAULT NULL COMMENT '下线时间',
  `expire_type` tinyint(1) DEFAULT '1' COMMENT '过期类型：1-固定过期日期，2-从开卡时计算，3-从消耗时计算',
  `expire_date` date DEFAULT NULL COMMENT '固定过期日期',
  `expire_months` int(11) DEFAULT '12' COMMENT '过期月数',
  `sale_store_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '限定销售分店',
  `consume_store_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '限定消费分店',
  `sale_department_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '限定销售部门',
  `consume_department_ids` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '限定消费部门',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐卡表';

-- 正在导出表  mydream.card_package 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `card_package` DISABLE KEYS */;
INSERT INTO `card_package` (`id`, `company_id`, `card_name`, `original_price`, `price`, `project_count`, `isDelete`, `created_at`, `updated_at`, `card_code`, `description`, `remark`, `is_modifiable`, `is_add_new_item_forbidden`, `is_limit_once`, `is_expire_invalid`, `is_gift_forbidden`, `online_time`, `offline_time`, `expire_type`, `expire_date`, `expire_months`, `sale_store_ids`, `consume_store_ids`, `sale_department_ids`, `consume_department_ids`) VALUES
	(1, 2, 'aaa', 111.00, 111.00, 1, 0, '2026-02-25 14:47:44', '2026-02-25 17:14:18', 'aaa', '22', '22', 0, 1, 1, 1, 1, '2026-02-25 14:42:04', '2026-02-28 00:00:00', 1, '2026-02-28', 12, '[1,2]', '[1,2]', '[1,2]', '[1,2]');
/*!40000 ALTER TABLE `card_package` ENABLE KEYS */;

-- 导出  表 mydream.card_package_gift_product 结构
CREATE TABLE IF NOT EXISTS `card_package_gift_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `package_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `times` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 正在导出表  mydream.card_package_gift_product 的数据：0 rows
/*!40000 ALTER TABLE `card_package_gift_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_package_gift_product` ENABLE KEYS */;

-- 导出  表 mydream.card_package_gift_project 结构
CREATE TABLE IF NOT EXISTS `card_package_gift_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `package_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `times` int(11) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `consume` int(11) NOT NULL DEFAULT '0',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_package_id` (`package_id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 正在导出表  mydream.card_package_gift_project 的数据：1 rows
/*!40000 ALTER TABLE `card_package_gift_project` DISABLE KEYS */;
INSERT INTO `card_package_gift_project` (`id`, `company_id`, `package_id`, `project_id`, `times`, `unit_price`, `total_price`, `consume`, `manual_salary`, `isDelete`, `created_at`, `updated_at`) VALUES
	(1, 2, 1, 1, 1, 198.00, 198.00, 98, 60.00, 0, '2026-02-25 16:43:02', '2026-02-25 17:14:18');
/*!40000 ALTER TABLE `card_package_gift_project` ENABLE KEYS */;

-- 导出  表 mydream.card_product 结构
CREATE TABLE IF NOT EXISTS `card_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `product_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '产品编码',
  `external_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '外部显示名',
  `barcode` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '条码',
  `category_id` int(10) unsigned DEFAULT NULL,
  `product_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '产品类型',
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `original_price` decimal(10,2) DEFAULT '0.00',
  `sale_price` decimal(10,2) DEFAULT '0.00',
  `experience_price` decimal(10,2) DEFAULT '0.00' COMMENT '体验价格',
  `purchase_price` decimal(10,2) DEFAULT '0.00' COMMENT '进货价格',
  `online_date` date DEFAULT NULL COMMENT '上线日期',
  `offline_date` date DEFAULT NULL COMMENT '下线日期',
  `stock_min` int(11) DEFAULT '0' COMMENT '库存下限',
  `stock_max` int(11) DEFAULT '0' COMMENT '库存上限',
  `approval_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '批准文号',
  `expiry_date` date DEFAULT NULL COMMENT '到期日期',
  `limited_sale_stores` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '限定销售分店',
  `limited_consume_stores` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '限定消耗分店',
  `limited_sale_depts` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '限定销售部门',
  `limited_consume_depts` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '限定消耗部门',
  `no_discount` tinyint(4) DEFAULT '0' COMMENT '充值卡不打折',
  `allow_gift` tinyint(4) DEFAULT '0' COMMENT '允许赠送',
  `no_consumption` tinyint(4) DEFAULT '0' COMMENT '不计消耗',
  `is_cooperative` tinyint(4) DEFAULT '0' COMMENT '合作产品',
  `is_ym` tinyint(4) DEFAULT '0' COMMENT 'YM产品',
  `is_special` tinyint(4) DEFAULT '0' COMMENT '特项产品',
  `supplier_id` int(11) DEFAULT '0',
  `unit` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '单位',
  `monthly_limit` int(11) DEFAULT '0' COMMENT '每月限制次数',
  `consumption_interval` int(11) DEFAULT '0' COMMENT '消费间隔天数',
  `specification` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '规格',
  `status` tinyint(1) DEFAULT '1',
  `remark` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '备注',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='产品表';

-- 正在导出表  mydream.card_product 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `card_product` DISABLE KEYS */;
INSERT INTO `card_product` (`id`, `company_id`, `product_name`, `product_code`, `external_name`, `barcode`, `category_id`, `product_type`, `isDelete`, `created_at`, `updated_at`, `original_price`, `sale_price`, `experience_price`, `purchase_price`, `online_date`, `offline_date`, `stock_min`, `stock_max`, `approval_number`, `expiry_date`, `limited_sale_stores`, `limited_consume_stores`, `limited_sale_depts`, `limited_consume_depts`, `no_discount`, `allow_gift`, `no_consumption`, `is_cooperative`, `is_ym`, `is_special`, `supplier_id`, `unit`, `monthly_limit`, `consumption_interval`, `specification`, `status`, `remark`) VALUES
	(1, 2, '测试产品', '', '测试', '111', 1, '', 0, '2026-02-25 22:18:51', '2026-02-25 22:18:51', 690.00, 390.00, 190.00, 230.00, '2026-02-02', '2026-02-27', 12, 999, '111', '2030-02-01', '[1,2]', '[1,2]', '[1,2]', '[1,2]', 1, 0, 0, 0, 0, 0, 1, '套', 2, 2, '12个', 1, '123');
/*!40000 ALTER TABLE `card_product` ENABLE KEYS */;

-- 导出  表 mydream.card_product_category 结构
CREATE TABLE IF NOT EXISTS `card_product_category` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_name` varchar(100) NOT NULL COMMENT '分类名称',
  `department_id` int(11) DEFAULT NULL COMMENT '所属部门ID',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `company_id` int(11) DEFAULT NULL COMMENT '公司ID',
  `isDelete` tinyint(4) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='产品分类表';

-- 正在导出表  mydream.card_product_category 的数据：~2 rows (大约)
/*!40000 ALTER TABLE `card_product_category` DISABLE KEYS */;
INSERT INTO `card_product_category` (`id`, `category_name`, `department_id`, `sort`, `company_id`, `isDelete`, `created_at`, `updated_at`) VALUES
	(1, '院装-烫发产品', 1, 0, 2, 0, '2026-02-25 20:30:07', '2026-02-25 20:30:07'),
	(2, '家居-面部', 2, 0, 2, 0, '2026-02-25 20:43:28', '2026-02-25 20:43:28');
/*!40000 ALTER TABLE `card_product_category` ENABLE KEYS */;

-- 导出  表 mydream.card_project 结构
CREATE TABLE IF NOT EXISTS `card_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `project_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '项目名称',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类ID',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `original_price` decimal(10,2) DEFAULT '0.00',
  `single_sale_price` decimal(10,2) DEFAULT '0.00',
  `experience_price` decimal(10,2) DEFAULT '0.00',
  `external_display_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_id` int(11) DEFAULT '0',
  `project_type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `monthly_limit` int(11) DEFAULT '0',
  `consumption_interval` int(11) DEFAULT '0',
  `work_hours` decimal(5,2) DEFAULT '0.00',
  `service_time` int(11) DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `reminder_type` tinyint(1) DEFAULT '0',
  `reminder_days` int(11) DEFAULT '0',
  `reminder_date` datetime DEFAULT NULL,
  `reminder_repeat` tinyint(1) DEFAULT '0',
  `no_recharge_discount` tinyint(1) DEFAULT '0',
  `no_project_times` tinyint(1) DEFAULT '0',
  `no_consumption` tinyint(1) DEFAULT '0',
  `no_consumption_notification` tinyint(1) DEFAULT '0',
  `mini_program_bookable` tinyint(1) DEFAULT '0',
  `limited_sale_stores` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `limited_service_stores` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `allow_gift` tinyint(1) DEFAULT '0',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目表';

-- 正在导出表  mydream.card_project 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `card_project` DISABLE KEYS */;
INSERT INTO `card_project` (`id`, `company_id`, `project_name`, `category_id`, `isDelete`, `created_at`, `updated_at`, `original_price`, `single_sale_price`, `experience_price`, `external_display_name`, `supplier_id`, `project_type`, `monthly_limit`, `consumption_interval`, `work_hours`, `service_time`, `status`, `reminder_type`, `reminder_days`, `reminder_date`, `reminder_repeat`, `no_recharge_discount`, `no_project_times`, `no_consumption`, `no_consumption_notification`, `mini_program_bookable`, `limited_sale_stores`, `limited_service_stores`, `allow_gift`, `remark`) VALUES
	(1, 2, '暨大美塑面部补水', 8, 0, '2026-02-07 15:40:45', '2026-02-25 22:02:34', 580.00, 198.00, 98.00, '暨大美塑', 4, '面部', 0, 0, 0.00, 60, 1, 3, 0, NULL, 1, 0, 0, 0, 0, 0, '0', '0', 0, '12222'),
	(2, 2, '测试2', 6, 0, '2026-02-12 12:21:48', '2026-02-12 12:21:48', 980.00, 498.00, 398.00, '测试', 1, '眼部', 0, 0, 1.00, 60, 1, 1, 0, NULL, 0, 0, 0, 0, 0, 0, '0', '0', 0, ''),
	(3, 2, '测试3', 9, 0, '2026-02-13 11:26:43', '2026-02-13 11:26:43', 980.00, 580.00, 480.00, '测试', 6, '全身', 0, 0, 1.00, 60, 1, 1, 0, NULL, 0, 0, 0, 0, 0, 0, '0', '0', 0, ''),
	(4, 2, '测试4', 8, 0, '2026-02-13 11:27:28', '2026-02-13 11:27:28', 680.00, 588.00, 388.00, '测试', 5, '腹部', 0, 0, 0.00, 0, 1, 1, 0, NULL, 0, 0, 0, 0, 0, 0, '0', '0', 0, '');
/*!40000 ALTER TABLE `card_project` ENABLE KEYS */;

-- 导出  表 mydream.card_project_category 结构
CREATE TABLE IF NOT EXISTS `card_project_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `category_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
  `sort` int(11) DEFAULT '0' COMMENT '排序',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `department_id` int(10) unsigned DEFAULT NULL COMMENT '所属部门ID',
  `parent_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '父分类ID，0表示一级分类',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目分类表';

-- 正在导出表  mydream.card_project_category 的数据：~12 rows (大约)
/*!40000 ALTER TABLE `card_project_category` DISABLE KEYS */;
INSERT INTO `card_project_category` (`id`, `company_id`, `category_name`, `sort`, `isDelete`, `created_at`, `updated_at`, `department_id`, `parent_id`) VALUES
	(1, 2, '洗发', 0, 0, '2026-02-05 17:45:36', '2026-02-05 17:45:36', 1, 0),
	(2, 2, '洗剪吹', 0, 0, '2026-02-05 22:40:59', '2026-02-05 22:40:59', 1, 0),
	(3, 2, '烫发', 0, 0, '2026-02-05 22:42:35', '2026-02-05 22:42:35', 1, 0),
	(4, 2, '染发', 0, 0, '2026-02-05 22:42:53', '2026-02-05 22:42:53', 1, 0),
	(5, 2, '酸性', 0, 0, '2026-02-05 22:43:05', '2026-02-05 22:43:05', 1, 0),
	(6, 2, '自营面部', 0, 0, '2026-02-05 22:45:54', '2026-02-05 23:19:26', 2, 0),
	(7, 2, '自营身体', 0, 0, '2026-02-05 22:46:11', '2026-02-05 23:19:33', 2, 0),
	(8, 2, '合作面部', 0, 0, '2026-02-05 22:46:28', '2026-02-05 23:20:11', 2, 0),
	(9, 2, '合作身体', 0, 0, '2026-02-05 22:47:27', '2026-02-05 23:20:20', 2, 0),
	(10, 2, '合作特项', 0, 0, '2026-02-05 22:48:16', '2026-02-05 23:23:41', 2, 0),
	(11, 2, '特项-仪器', 0, 1, '2026-02-05 23:00:39', '2026-02-05 23:24:13', 2, 0),
	(12, 2, '11', 0, 1, '2026-02-05 23:23:54', '2026-02-05 23:23:57', 2, 0);
/*!40000 ALTER TABLE `card_project_category` ENABLE KEYS */;

-- 导出  表 mydream.card_project_ingredient 结构
CREATE TABLE IF NOT EXISTS `card_project_ingredient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `product` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `unit` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  mydream.card_project_ingredient 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `card_project_ingredient` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_project_ingredient` ENABLE KEYS */;

-- 导出  表 mydream.card_project_sub 结构
CREATE TABLE IF NOT EXISTS `card_project_sub` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `sub_project_id` int(11) NOT NULL,
  `consumption_ratio` decimal(5,2) DEFAULT '0.00',
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `project_id` (`project_id`),
  KEY `sub_project_id` (`sub_project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  mydream.card_project_sub 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `card_project_sub` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_project_sub` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge 结构
CREATE TABLE IF NOT EXISTS `card_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL COMMENT '所属公司ID',
  `card_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '金额',
  `gift_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '赠送金额',
  `project_discount` decimal(5,2) NOT NULL DEFAULT '10.00' COMMENT '项目折扣',
  `product_discount` decimal(5,2) NOT NULL DEFAULT '10.00' COMMENT '产品折扣',
  `consume_rate` int(11) NOT NULL DEFAULT '100' COMMENT '消费率',
  `min_recharge_limit` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '最小充值限额',
  `start_time` datetime DEFAULT NULL COMMENT '开始时间',
  `end_time` datetime DEFAULT NULL COMMENT '结束时间',
  `expire_date` datetime DEFAULT NULL COMMENT '过期日期',
  `expire_type` int(11) NOT NULL DEFAULT '3' COMMENT '过期类型',
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_modifiable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可修改',
  `is_limit_once` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否限制一次',
  `is_expire_invalid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '过期是否失效',
  `is_project_expire` tinyint(1) NOT NULL DEFAULT '1' COMMENT '项目是否过期',
  `is_prohibit_discount_modify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁止折扣修改',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡表';

-- 正在导出表  mydream.card_recharge 的数据：~21 rows (大约)
/*!40000 ALTER TABLE `card_recharge` DISABLE KEYS */;
INSERT INTO `card_recharge` (`id`, `company_id`, `card_name`, `isDelete`, `created_at`, `updated_at`, `amount`, `gift_amount`, `project_discount`, `product_discount`, `consume_rate`, `min_recharge_limit`, `start_time`, `end_time`, `expire_date`, `expire_type`, `description`, `remark`, `status`, `is_modifiable`, `is_limit_once`, `is_expire_invalid`, `is_project_expire`, `is_prohibit_discount_modify`) VALUES
	(1, 1, '测试充值卡', 0, '2026-02-08 00:39:22', '2026-02-08 00:39:22', 1000.00, 100.00, 10.00, 10.00, 100, 0.00, NULL, NULL, NULL, 3, '测试充值卡描述', '测试充值卡备注', 0, 1, 0, 1, 1, 0),
	(2, 2, '?????', 1, '2026-02-08 00:53:34', '2026-02-08 00:53:34', 1000.00, 0.00, 10.00, 10.00, 100, 0.00, NULL, NULL, NULL, 3, NULL, NULL, 0, 1, 0, 1, 1, 0),
	(3, 2, '?????', 1, '2026-02-08 00:54:44', '2026-02-08 00:54:44', 1000.00, 0.00, 10.00, 10.00, 100, 0.00, NULL, NULL, NULL, 3, NULL, NULL, 0, 1, 0, 1, 1, 0),
	(4, 2, '?????', 1, '2026-02-08 00:55:35', '2026-02-08 00:55:35', 1000.00, 0.00, 10.00, 10.00, 100, 0.00, NULL, NULL, NULL, 3, NULL, NULL, 0, 1, 0, 1, 1, 0),
	(5, 2, '发过的', 1, '2026-02-08 02:51:41', '2026-02-08 02:51:41', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(6, 2, '大撒大声地', 1, '2026-02-08 02:51:47', '2026-02-08 02:51:47', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(7, 2, '撒大声地', 1, '2026-02-08 02:52:21', '2026-02-08 02:52:21', 3333.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(8, 2, '发过的', 1, '2026-02-08 02:52:39', '2026-02-08 02:52:39', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(9, 2, '大萨达', 1, '2026-02-08 02:53:29', '2026-02-08 02:53:29', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(10, 2, '大萨达', 1, '2026-02-08 02:54:05', '2026-02-08 02:54:05', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(11, 2, '大萨达', 1, '2026-02-08 02:54:53', '2026-02-08 02:54:53', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(12, 2, '大萨达', 0, '2026-02-08 02:54:55', '2026-02-24 18:19:59', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 1, 1, 0, 1, 1, 0),
	(13, 2, '大萨达', 1, '2026-02-08 02:55:38', '2026-02-08 02:55:38', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(14, 2, '大萨达', 1, '2026-02-08 02:55:43', '2026-02-08 02:55:43', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(15, 2, '大萨达', 1, '2026-02-08 02:55:59', '2026-02-08 02:55:59', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(16, 2, '大萨达', 1, '2026-02-08 02:56:17', '2026-02-08 02:56:17', 4444.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(17, 2, '实打实', 1, '2026-02-08 02:56:47', '2026-02-08 02:56:47', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(18, 2, '实打实', 1, '2026-02-08 02:57:24', '2026-02-08 02:57:24', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '', '', 0, 1, 0, 1, 1, 0),
	(19, 2, '实打实', 0, '2026-02-08 02:58:11', '2026-02-24 18:19:58', 5555.00, 0.00, 100.00, 100.00, 100, 0.00, '2026-02-02 16:00:00', '2026-02-26 16:00:00', NULL, 3, '11111', '2222', 1, 0, 1, 1, 1, 1),
	(20, 2, '测试1', 0, '2026-02-24 16:58:02', '2026-02-24 21:02:50', 100.00, 10.00, 100.00, 100.00, 100, 0.00, NULL, NULL, NULL, 3, '11', '12', 1, 0, 0, 1, 1, 0),
	(21, 2, '琴琴', 0, '2026-02-24 21:25:12', '2026-02-25 16:46:56', 5980.00, 1000.00, 100.00, 100.00, 100, 0.00, '2026-02-24 16:00:00', '2026-03-03 16:00:00', NULL, 1, '113', '221', 1, 0, 0, 1, 1, 0);
/*!40000 ALTER TABLE `card_recharge` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_consume_department 结构
CREATE TABLE IF NOT EXISTS `card_recharge_consume_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_id` int(11) NOT NULL COMMENT '充值卡ID',
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=127 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='充值卡消费部门关联表';

-- 正在导出表  mydream.card_recharge_consume_department 的数据：~30 rows (大约)
/*!40000 ALTER TABLE `card_recharge_consume_department` DISABLE KEYS */;
INSERT INTO `card_recharge_consume_department` (`id`, `recharge_id`, `department_id`, `created_at`, `updated_at`) VALUES
	(1, 5, 1, '2026-02-08 02:51:41', '2026-02-08 02:51:41'),
	(2, 5, 2, '2026-02-08 02:51:41', '2026-02-08 02:51:41'),
	(3, 6, 1, '2026-02-08 02:51:47', '2026-02-08 02:51:47'),
	(4, 6, 2, '2026-02-08 02:51:47', '2026-02-08 02:51:47'),
	(5, 7, 1, '2026-02-08 02:52:21', '2026-02-08 02:52:21'),
	(6, 7, 2, '2026-02-08 02:52:21', '2026-02-08 02:52:21'),
	(7, 8, 1, '2026-02-08 02:52:39', '2026-02-08 02:52:39'),
	(8, 8, 2, '2026-02-08 02:52:39', '2026-02-08 02:52:39'),
	(9, 9, 1, '2026-02-08 02:53:30', '2026-02-08 02:53:30'),
	(10, 9, 2, '2026-02-08 02:53:30', '2026-02-08 02:53:30'),
	(11, 10, 1, '2026-02-08 02:54:05', '2026-02-08 02:54:05'),
	(12, 10, 2, '2026-02-08 02:54:05', '2026-02-08 02:54:05'),
	(13, 11, 1, '2026-02-08 02:54:53', '2026-02-08 02:54:53'),
	(14, 11, 2, '2026-02-08 02:54:53', '2026-02-08 02:54:53'),
	(17, 13, 1, '2026-02-08 02:55:39', '2026-02-08 02:55:39'),
	(18, 13, 2, '2026-02-08 02:55:39', '2026-02-08 02:55:39'),
	(19, 14, 1, '2026-02-08 02:55:43', '2026-02-08 02:55:43'),
	(20, 14, 2, '2026-02-08 02:55:43', '2026-02-08 02:55:43'),
	(21, 15, 1, '2026-02-08 02:55:59', '2026-02-08 02:55:59'),
	(22, 15, 2, '2026-02-08 02:55:59', '2026-02-08 02:55:59'),
	(23, 16, 1, '2026-02-08 02:56:17', '2026-02-08 02:56:17'),
	(24, 16, 2, '2026-02-08 02:56:17', '2026-02-08 02:56:17'),
	(25, 17, 1, '2026-02-08 02:56:47', '2026-02-08 02:56:47'),
	(26, 17, 2, '2026-02-08 02:56:47', '2026-02-08 02:56:47'),
	(27, 18, 1, '2026-02-08 02:57:25', '2026-02-08 02:57:25'),
	(28, 18, 2, '2026-02-08 02:57:25', '2026-02-08 02:57:25'),
	(111, 20, 1, '2026-02-24 21:02:50', '2026-02-24 21:02:50'),
	(112, 20, 2, '2026-02-24 21:02:50', '2026-02-24 21:02:50'),
	(125, 21, 1, '2026-02-25 16:46:57', '2026-02-25 16:46:57'),
	(126, 21, 2, '2026-02-25 16:46:57', '2026-02-25 16:46:57');
/*!40000 ALTER TABLE `card_recharge_consume_department` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_consume_store 结构
CREATE TABLE IF NOT EXISTS `card_recharge_consume_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_recharge_store` (`recharge_id`,`store_id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_store_id` (`store_id`),
  CONSTRAINT `card_recharge_consume_store_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=63 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡消费分店表';

-- 正在导出表  mydream.card_recharge_consume_store 的数据：~5 rows (大约)
/*!40000 ALTER TABLE `card_recharge_consume_store` DISABLE KEYS */;
INSERT INTO `card_recharge_consume_store` (`id`, `recharge_id`, `store_id`) VALUES
	(1, 1, 1),
	(47, 20, 1),
	(48, 20, 2),
	(61, 21, 1),
	(62, 21, 2);
/*!40000 ALTER TABLE `card_recharge_consume_store` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_gift_product 结构
CREATE TABLE IF NOT EXISTS `card_recharge_gift_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `product_id` int(10) unsigned NOT NULL,
  `times` int(10) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `consume` decimal(10,2) NOT NULL DEFAULT '0.00',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_product_id` (`product_id`),
  CONSTRAINT `card_recharge_gift_product_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配赠产品表';

-- 正在导出表  mydream.card_recharge_gift_product 的数据：~1 rows (大约)
/*!40000 ALTER TABLE `card_recharge_gift_product` DISABLE KEYS */;
INSERT INTO `card_recharge_gift_product` (`id`, `recharge_id`, `product_id`, `times`, `unit_price`, `consume`, `manual_salary`, `created_at`, `updated_at`) VALUES
	(1, 4, 1, 5, 200.00, 0.00, 20.00, '2026-02-08 00:55:35', '2026-02-08 00:55:35');
/*!40000 ALTER TABLE `card_recharge_gift_product` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_gift_project 结构
CREATE TABLE IF NOT EXISTS `card_recharge_gift_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `project_id` int(10) unsigned NOT NULL,
  `times` int(10) NOT NULL DEFAULT '1',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `consume` decimal(10,2) NOT NULL DEFAULT '0.00',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_project_id` (`project_id`),
  CONSTRAINT `card_recharge_gift_project_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=40 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配赠项目表';

-- 正在导出表  mydream.card_recharge_gift_project 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `card_recharge_gift_project` DISABLE KEYS */;
INSERT INTO `card_recharge_gift_project` (`id`, `recharge_id`, `project_id`, `times`, `unit_price`, `total_price`, `consume`, `manual_salary`, `created_at`, `updated_at`) VALUES
	(1, 4, 1, 10, 100.00, 0.00, 50.00, 10.00, '2026-02-08 00:55:35', '2026-02-08 00:55:35'),
	(32, 20, 1, 1, 198.00, 198.00, 2.00, 21.00, '2026-02-24 21:02:50', '2026-02-24 21:02:50'),
	(39, 21, 1, 1, 198.00, 198.00, 22.00, 33.00, '2026-02-25 16:46:57', '2026-02-25 16:46:57');
/*!40000 ALTER TABLE `card_recharge_gift_project` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_sale_department 结构
CREATE TABLE IF NOT EXISTS `card_recharge_sale_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_id` int(11) NOT NULL COMMENT '充值卡ID',
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=128 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='充值卡销售部门关联表';

-- 正在导出表  mydream.card_recharge_sale_department 的数据：~30 rows (大约)
/*!40000 ALTER TABLE `card_recharge_sale_department` DISABLE KEYS */;
INSERT INTO `card_recharge_sale_department` (`id`, `recharge_id`, `department_id`, `created_at`, `updated_at`) VALUES
	(1, 5, 1, '2026-02-08 02:51:41', '2026-02-08 02:51:41'),
	(2, 5, 2, '2026-02-08 02:51:41', '2026-02-08 02:51:41'),
	(3, 6, 1, '2026-02-08 02:51:47', '2026-02-08 02:51:47'),
	(4, 6, 2, '2026-02-08 02:51:47', '2026-02-08 02:51:47'),
	(5, 7, 1, '2026-02-08 02:52:21', '2026-02-08 02:52:21'),
	(6, 7, 2, '2026-02-08 02:52:21', '2026-02-08 02:52:21'),
	(7, 8, 1, '2026-02-08 02:52:39', '2026-02-08 02:52:39'),
	(8, 8, 2, '2026-02-08 02:52:39', '2026-02-08 02:52:39'),
	(9, 9, 1, '2026-02-08 02:53:30', '2026-02-08 02:53:30'),
	(10, 9, 2, '2026-02-08 02:53:30', '2026-02-08 02:53:30'),
	(11, 10, 1, '2026-02-08 02:54:05', '2026-02-08 02:54:05'),
	(12, 10, 2, '2026-02-08 02:54:05', '2026-02-08 02:54:05'),
	(13, 11, 1, '2026-02-08 02:54:53', '2026-02-08 02:54:53'),
	(14, 11, 2, '2026-02-08 02:54:53', '2026-02-08 02:54:53'),
	(17, 13, 1, '2026-02-08 02:55:39', '2026-02-08 02:55:39'),
	(18, 13, 2, '2026-02-08 02:55:39', '2026-02-08 02:55:39'),
	(19, 14, 1, '2026-02-08 02:55:43', '2026-02-08 02:55:43'),
	(20, 14, 2, '2026-02-08 02:55:43', '2026-02-08 02:55:43'),
	(21, 15, 1, '2026-02-08 02:55:59', '2026-02-08 02:55:59'),
	(22, 15, 2, '2026-02-08 02:55:59', '2026-02-08 02:55:59'),
	(23, 16, 1, '2026-02-08 02:56:17', '2026-02-08 02:56:17'),
	(24, 16, 2, '2026-02-08 02:56:17', '2026-02-08 02:56:17'),
	(25, 17, 1, '2026-02-08 02:56:47', '2026-02-08 02:56:47'),
	(26, 17, 2, '2026-02-08 02:56:47', '2026-02-08 02:56:47'),
	(27, 18, 1, '2026-02-08 02:57:25', '2026-02-08 02:57:25'),
	(28, 18, 2, '2026-02-08 02:57:25', '2026-02-08 02:57:25'),
	(112, 20, 1, '2026-02-24 21:02:50', '2026-02-24 21:02:50'),
	(113, 20, 2, '2026-02-24 21:02:50', '2026-02-24 21:02:50'),
	(126, 21, 1, '2026-02-25 16:46:57', '2026-02-25 16:46:57'),
	(127, 21, 2, '2026-02-25 16:46:57', '2026-02-25 16:46:57');
/*!40000 ALTER TABLE `card_recharge_sale_department` ENABLE KEYS */;

-- 导出  表 mydream.card_recharge_sale_store 结构
CREATE TABLE IF NOT EXISTS `card_recharge_sale_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_recharge_store` (`recharge_id`,`store_id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_store_id` (`store_id`),
  CONSTRAINT `card_recharge_sale_store_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡销售分店表';

-- 正在导出表  mydream.card_recharge_sale_store 的数据：~5 rows (大约)
/*!40000 ALTER TABLE `card_recharge_sale_store` DISABLE KEYS */;
INSERT INTO `card_recharge_sale_store` (`id`, `recharge_id`, `store_id`) VALUES
	(1, 1, 2),
	(48, 20, 1),
	(49, 20, 2),
	(62, 21, 1),
	(63, 21, 2);
/*!40000 ALTER TABLE `card_recharge_sale_store` ENABLE KEYS */;

-- 导出  表 mydream.card_supplier 结构
CREATE TABLE IF NOT EXISTS `card_supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `supplier_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '供应商名称',
  `contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系人',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系电话',
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地址',
  `bank` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '开户银行',
  `bank_card` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '银行卡号',
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `prepay_balance` decimal(10,2) DEFAULT '0.00' COMMENT '预存余额',
  `delivery_balance` decimal(10,2) DEFAULT '0.00' COMMENT '配送余额',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供应商表';

-- 正在导出表  mydream.card_supplier 的数据：~6 rows (大约)
/*!40000 ALTER TABLE `card_supplier` DISABLE KEYS */;
INSERT INTO `card_supplier` (`id`, `company_id`, `supplier_name`, `contact`, `phone`, `address`, `bank`, `bank_card`, `email`, `prepay_balance`, `delivery_balance`, `isDelete`, `created_at`, `updated_at`) VALUES
	(1, 2, '宝迪佳', '', '', '', '', '', '', 0.00, 0.00, 0, '2026-02-04 02:15:57', '2026-02-07 17:09:18'),
	(2, 2, '文强的', '', '', '', '', '', '', 0.00, 0.00, 1, '2026-02-04 02:18:40', '2026-02-07 17:09:01'),
	(3, 2, '文强的', '', '', '', '', '', '', 0.00, 0.00, 1, '2026-02-04 02:18:40', '2026-02-07 17:09:06'),
	(4, 2, '暨大美塑', '', '', '', '', '', '', 0.00, 0.00, 0, '2026-02-05 22:53:45', '2026-02-05 22:53:45'),
	(5, 2, '英特波', '', '', '', '', '', '', 0.00, 0.00, 0, '2026-02-05 22:54:08', '2026-02-07 17:09:34'),
	(6, 2, '艾灸', '', '', '', '', '', '', 0.00, 0.00, 0, '2026-02-07 16:56:50', '2026-02-07 17:09:50');
/*!40000 ALTER TABLE `card_supplier` ENABLE KEYS */;

-- 导出  表 mydream.card_time 结构
CREATE TABLE IF NOT EXISTS `card_time` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `card_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
  `valid_days` int(11) NOT NULL DEFAULT '0' COMMENT '有效期(天)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='时效卡表';

-- 正在导出表  mydream.card_time 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `card_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_time` ENABLE KEYS */;

-- 导出  表 mydream.cust_customer 结构
CREATE TABLE IF NOT EXISTS `cust_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL COMMENT '所属门店ID',
  `department_id` int(11) NOT NULL COMMENT '所属部门ID',
  `member_card` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '会员卡号',
  `name` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `gender` tinyint(1) NOT NULL COMMENT '性别(1:男,2:女)',
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '电话',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `birthday_type` tinyint(1) DEFAULT '1' COMMENT '生日类别(1:阳历,2:阴历)',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `register_time` datetime NOT NULL COMMENT '注册时间',
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '来源',
  `avatar` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `archive_number` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '档案编号',
  `level` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT '普通客户' COMMENT '客户级别',
  `service_staff_id` int(11) DEFAULT NULL COMMENT '服务人ID(美容师)',
  `manager_id` int(11) DEFAULT NULL COMMENT '管理人ID(顾问)',
  `last_consume_time` datetime DEFAULT NULL COMMENT '最近消费时间',
  `last_consume_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消费金额',
  `last_deplete_time` datetime DEFAULT NULL COMMENT '最近消耗时间',
  `last_deplete_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消耗金额',
  `remark` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1:正常,0:禁用)',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `name_pinyin` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名拼音首字母',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_card` (`member_card`),
  UNIQUE KEY `archive_number` (`archive_number`),
  UNIQUE KEY `phone` (`phone`),
  KEY `store_id` (`store_id`),
  KEY `department_id` (`department_id`),
  KEY `level` (`level`),
  KEY `register_time` (`register_time`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户信息表';

-- 正在导出表  mydream.cust_customer 的数据：~13 rows (大约)
/*!40000 ALTER TABLE `cust_customer` DISABLE KEYS */;
INSERT INTO `cust_customer` (`id`, `store_id`, `department_id`, `member_card`, `name`, `gender`, `phone`, `birthday`, `birthday_type`, `points`, `register_time`, `source`, `avatar`, `archive_number`, `level`, `service_staff_id`, `manager_id`, `last_consume_time`, `last_consume_amount`, `last_deplete_time`, `last_deplete_amount`, `remark`, `status`, `created_at`, `updated_at`, `name_pinyin`) VALUES
	(1, 1, 2, 'VIP001', '张三', 1, '13800138000', '1990-01-01', 1, 1000, '2026-02-02 15:09:00', '门店推荐', '', 'ST001-DEP001-20260202001', '银卡客户', 1, 1, '2026-02-02 15:09:00', 500.00, '2026-02-02 15:09:00', 200.00, '测试客户', 1, '2026-02-02 15:09:00', '2026-02-05 16:47:28', 'ZS'),
	(2, 2, 2, 'VIP002', '李四', 2, '13900139000', '1992-02-02', 2, 2000, '2026-02-02 15:09:00', '线上推广', NULL, 'ST001-DEP002-20260202002', '金卡客户', 1, 5, '2026-02-02 15:09:00', 1000.00, '2026-02-02 15:09:00', 500.00, '测试客户2', 1, '2026-02-02 15:09:00', '2026-02-24 12:59:32', 'LS'),
	(3, 1, 1, 'MC1202602058551', '顺义客户1', 2, '13367376545', '1984-03-18', 1, 374, '2026-02-05 14:25:01', '门店推荐', '', 'AR11202602058870', '银卡客户', 0, 0, NULL, 0.00, NULL, 0.00, '顺义店测试客户1', 1, '2026-02-05 14:25:01', '2026-02-05 16:46:58', ''),
	(4, 1, 2, 'MC1202602052646', '顺义客户2', 1, '13771814258', '1981-05-22', 2, 120, '2026-02-05 14:25:01', '门店推荐', '', 'AR11202602052033', '金卡客户', 0, 0, NULL, 0.00, NULL, 0.00, '顺义店测试客户2', 1, '2026-02-05 14:25:01', '2026-02-05 16:47:06', ''),
	(5, 2, 1, 'MC1202602055123', '顺义客户3', 1, '13891905613', '1980-11-01', 1, 591, '2026-02-05 14:25:01', '网络推广', '', 'AR12202602055771', '银卡客户', 1, 5, NULL, 0.00, NULL, 0.00, '顺义店测试客户3', 1, '2026-02-05 14:25:01', '2026-02-12 12:00:19', ''),
	(6, 2, 1, 'MC1202602059060', '顺义客户4', 1, '13375745115', '1992-09-20', 1, 353, '2026-02-05 14:25:01', '网络推广', '', 'AR12202602056649', '银卡客户', NULL, NULL, NULL, 0.00, NULL, 0.00, '顺义店测试客户4', 1, '2026-02-05 14:25:01', '2026-02-12 11:44:32', ''),
	(7, 1, 1, 'MC1202602058711', '顺义客户5', 2, '13433010886', '1996-04-14', 2, 416, '2026-02-05 14:25:01', '门店推荐', '', 'AR11202602050868', '普通客户', 4, NULL, NULL, 0.00, NULL, 0.00, '顺义店测试客户55', 1, '2026-02-05 14:25:01', '2026-02-12 12:27:31', ''),
	(8, 2, 1, 'MC2202602051244', '肇嘉浜客户1', 2, '13529141046', '1999-01-14', 2, 123, '2026-02-05 14:25:01', '门店推荐', '', 'AR21202602050095', '银卡客户', 7, 6, NULL, 0.00, NULL, 0.00, '肇嘉浜店测试客户1', 1, '2026-02-05 14:25:01', '2026-02-06 18:17:25', ''),
	(9, 2, 1, 'MC2202602050205', '肇嘉浜客户2', 1, '13446696135', '1995-10-07', 2, 249, '2026-02-05 14:25:01', '线下活动', '', 'AR21202602057121', '金卡客户', 7, 6, NULL, 0.00, NULL, 0.00, '肇嘉浜店测试客户2', 1, '2026-02-05 14:25:01', '2026-02-06 18:25:40', ''),
	(10, 2, 1, 'MC2202602050763', '肇嘉浜客户3', 1, '13170284614', '1996-09-22', 2, 50, '2026-02-05 14:25:01', '其他', '', 'AR21202602055913', '金卡客户', 0, 0, NULL, 0.00, NULL, 0.00, '肇嘉浜店测试客户3', 1, '2026-02-05 14:25:01', '2026-02-05 16:57:06', ''),
	(11, 2, 2, 'MC2202602059453', '肇嘉浜客户4', 1, '13919932097', '2001-11-16', 1, 399, '2026-02-05 14:25:01', '网络推广', '', 'AR21202602051349', '金卡客户', 1, 5, NULL, 0.00, NULL, 0.00, '肇嘉浜店测试客户4', 1, '2026-02-05 14:25:01', '2026-02-06 18:25:49', ''),
	(12, 2, 1, 'MC2202602051465', '肇嘉浜客户5', 2, '13070147288', '1986-01-14', 2, 890, '2026-02-05 14:25:01', '线下活动', '', 'AR21202602056956', '金卡客户', 0, 0, NULL, 0.00, NULL, 0.00, '肇嘉浜店测试客户5', 1, '2026-02-05 14:25:01', '2026-02-05 16:57:21', ''),
	(13, 4, 6, '0001', '测试客户1', 1, '15888888888', NULL, 1, 0, '2026-02-24 03:55:14', NULL, NULL, 'ST004-DEP006-20260224001', '普通客户', 8, NULL, NULL, 0.00, NULL, 0.00, NULL, 1, '2026-02-24 12:00:04', '2026-02-24 12:00:04', '');
/*!40000 ALTER TABLE `cust_customer` ENABLE KEYS */;

-- 导出  表 mydream.cust_customer_department 结构
CREATE TABLE IF NOT EXISTS `cust_customer_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_customer_department` (`customer_id`,`department_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 正在导出表  mydream.cust_customer_department 的数据：17 rows
/*!40000 ALTER TABLE `cust_customer_department` DISABLE KEYS */;
INSERT INTO `cust_customer_department` (`id`, `customer_id`, `department_id`, `created_at`, `updated_at`) VALUES
	(28, 8, 1, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(29, 8, 2, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(38, 7, 2, '2026-02-12 12:27:31', '2026-02-12 12:27:31'),
	(37, 7, 1, '2026-02-12 12:27:31', '2026-02-12 12:27:31'),
	(6, 3, 1, '2026-02-05 16:46:58', '2026-02-05 16:46:58'),
	(7, 4, 2, '2026-02-05 16:47:06', '2026-02-05 16:47:06'),
	(35, 5, 1, '2026-02-12 12:00:19', '2026-02-12 12:00:19'),
	(36, 5, 2, '2026-02-12 12:00:19', '2026-02-12 12:00:19'),
	(32, 6, 1, '2026-02-12 11:44:32', '2026-02-12 11:44:32'),
	(11, 1, 2, '2026-02-05 16:47:28', '2026-02-05 16:47:28'),
	(30, 9, 1, '2026-02-06 18:25:40', '2026-02-06 18:25:40'),
	(15, 10, 1, '2026-02-05 16:57:06', '2026-02-05 16:57:06'),
	(31, 11, 2, '2026-02-06 18:25:49', '2026-02-06 18:25:49'),
	(17, 12, 1, '2026-02-05 16:57:21', '2026-02-05 16:57:21'),
	(18, 12, 2, '2026-02-05 16:57:21', '2026-02-05 16:57:21'),
	(41, 2, 2, '2026-02-24 12:59:32', '2026-02-24 12:59:32'),
	(39, 13, 6, '2026-02-24 12:00:04', '2026-02-24 12:00:04');
/*!40000 ALTER TABLE `cust_customer_department` ENABLE KEYS */;

-- 导出  表 mydream.cust_customer_manager 结构
CREATE TABLE IF NOT EXISTS `cust_customer_manager` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `manager_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_manager_id` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  mydream.cust_customer_manager 的数据：~6 rows (大约)
/*!40000 ALTER TABLE `cust_customer_manager` DISABLE KEYS */;
INSERT INTO `cust_customer_manager` (`id`, `customer_id`, `manager_id`, `created_at`, `updated_at`) VALUES
	(1, 8, 6, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(2, 8, 5, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(3, 9, 6, '2026-02-06 18:25:40', '2026-02-06 18:25:40'),
	(4, 11, 5, '2026-02-06 18:25:49', '2026-02-06 18:25:49'),
	(5, 5, 5, '2026-02-12 12:00:19', '2026-02-12 12:00:19'),
	(8, 2, 5, '2026-02-24 12:59:32', '2026-02-24 12:59:32');
/*!40000 ALTER TABLE `cust_customer_manager` ENABLE KEYS */;

-- 导出  表 mydream.cust_customer_service_staff 结构
CREATE TABLE IF NOT EXISTS `cust_customer_service_staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `service_staff_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_service_staff_id` (`service_staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 正在导出表  mydream.cust_customer_service_staff 的数据：~8 rows (大约)
/*!40000 ALTER TABLE `cust_customer_service_staff` DISABLE KEYS */;
INSERT INTO `cust_customer_service_staff` (`id`, `customer_id`, `service_staff_id`, `created_at`, `updated_at`) VALUES
	(1, 8, 7, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(2, 8, 1, '2026-02-06 18:25:09', '2026-02-06 18:25:09'),
	(3, 9, 7, '2026-02-06 18:25:40', '2026-02-06 18:25:40'),
	(4, 11, 1, '2026-02-06 18:25:49', '2026-02-06 18:25:49'),
	(5, 5, 1, '2026-02-12 12:00:19', '2026-02-12 12:00:19'),
	(6, 7, 4, '2026-02-12 12:27:31', '2026-02-12 12:27:31'),
	(7, 13, 8, '2026-02-24 12:00:04', '2026-02-24 12:00:04'),
	(10, 2, 1, '2026-02-24 12:59:32', '2026-02-24 12:59:32');
/*!40000 ALTER TABLE `cust_customer_service_staff` ENABLE KEYS */;

-- 导出  表 mydream.sys_company 结构
CREATE TABLE IF NOT EXISTS `sys_company` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL DEFAULT '',
  `company_name` varchar(100) NOT NULL,
  `boss` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `enterprise_type` varchar(20) NOT NULL DEFAULT '',
  `store_count` int(11) NOT NULL DEFAULT '0',
  `service_people` varchar(50) NOT NULL DEFAULT '',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除：0-未删除，1-已删除',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`company_name`),
  UNIQUE KEY `idx_code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='公司表';

-- 正在导出表  mydream.sys_company 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `sys_company` DISABLE KEYS */;
INSERT INTO `sys_company` (`id`, `code`, `company_name`, `boss`, `phone`, `address`, `enterprise_type`, `store_count`, `service_people`, `status`, `created_at`, `updated_at`, `isDelete`) VALUES
	(1, 'admin', '系统管理公司', '彭彭', '13900139000', '111', '美容', 20, '鹏鹏', 1, '2026-01-30 10:18:41', '2026-01-31 17:58:21', 0),
	(2, 'fts', '馥田诗', '汪志', '11', '11', '综合', 2, '鹏鹏', 1, '2026-01-30 15:06:47', '2026-01-31 17:58:29', 0),
	(3, 'csgs1', '测试公司1', '彭世奎', '15888888888', '防守打法鼎折覆餗', '美容', 1, '大幅度', 1, '2026-02-24 09:32:28', '2026-02-24 09:32:28', 0);
/*!40000 ALTER TABLE `sys_company` ENABLE KEYS */;

-- 导出  表 mydream.sys_department 结构
CREATE TABLE IF NOT EXISTS `sys_department` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `dept_name` varchar(50) NOT NULL,
  `parent_id` int(10) unsigned DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `company_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `enable_category` tinyint(4) NOT NULL DEFAULT '0' COMMENT '是否启用数据分类 1:启用 0:禁用',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除：0-未删除，1-已删除',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='部门表';

-- 正在导出表  mydream.sys_department 的数据：~7 rows (大约)
/*!40000 ALTER TABLE `sys_department` DISABLE KEYS */;
INSERT INTO `sys_department` (`id`, `dept_name`, `parent_id`, `sort`, `company_id`, `status`, `enable_category`, `created_at`, `updated_at`, `isDelete`) VALUES
	(1, '美发部', 3, 1, 2, 1, 1, '2026-01-30 15:52:16', '2026-02-02 14:31:59', 0),
	(2, '美容部', 3, 2, 2, 1, 1, '2026-01-30 15:53:33', '2026-02-02 14:32:02', 0),
	(3, '总部', 0, 0, 2, 1, 0, '2026-01-30 16:12:07', '2026-02-06 12:09:02', 0),
	(4, '系统管理', 0, 0, 1, 1, 0, '2026-01-31 13:40:29', '2026-01-31 13:40:29', 0),
	(5, '二房东', 0, 0, 0, 1, 0, '2026-01-31 18:30:49', '2026-01-31 18:30:57', 1),
	(6, '测试部门1', 0, 0, 3, 1, 1, '2026-02-24 09:31:05', '2026-02-24 11:41:49', 0),
	(7, '测试部门1', 0, 0, 3, 1, 1, '2026-02-24 09:34:57', '2026-02-24 09:35:04', 1);
/*!40000 ALTER TABLE `sys_department` ENABLE KEYS */;

-- 导出  表 mydream.sys_employee_position 结构
CREATE TABLE IF NOT EXISTS `sys_employee_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `position_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_position` (`employee_id`,`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='员工职位关联表';

-- 正在导出表  mydream.sys_employee_position 的数据：~0 rows (大约)
/*!40000 ALTER TABLE `sys_employee_position` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_employee_position` ENABLE KEYS */;

-- 导出  表 mydream.sys_menu 结构
CREATE TABLE IF NOT EXISTS `sys_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `path` varchar(100) NOT NULL,
  `component` varchar(255) DEFAULT NULL,
  `redirect` varchar(100) DEFAULT NULL,
  `parent_id` int(10) unsigned DEFAULT '0',
  `icon` varchar(50) DEFAULT NULL,
  `menu_rank` int(11) DEFAULT '0',
  `is_frame` tinyint(4) NOT NULL DEFAULT '0',
  `frame_src` varchar(255) DEFAULT NULL,
  `show_link` tinyint(4) NOT NULL DEFAULT '1',
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_delete` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='菜单表';

-- 正在导出表  mydream.sys_menu 的数据：~22 rows (大约)
/*!40000 ALTER TABLE `sys_menu` DISABLE KEYS */;
INSERT INTO `sys_menu` (`id`, `name`, `path`, `component`, `redirect`, `parent_id`, `icon`, `menu_rank`, `is_frame`, `frame_src`, `show_link`, `status`, `created_at`, `updated_at`, `is_delete`) VALUES
	(1, '企业管理', '/system/enterprise', 'Layout', '/system/enterprise/company', 0, 'ep:chrome-filled', 1, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-01-29 19:49:01', 0),
	(2, '公司管理', '/system/enterprise/company', 'system/enterprise/company/index.vue', NULL, 1, 'el:briefcase', 1, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-02-02 19:30:11', 0),
	(3, '门店管理', '/system/enterprise/store', 'system/enterprise/store/index.vue', NULL, 1, 'ep:shop', 2, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-01-29 19:47:29', 0),
	(4, '部门职位', '/system/enterprise/department-position', 'system/enterprise/department-position/index.vue', NULL, 1, 'ep:stamp', 3, 0, NULL, 1, 0, '2026-01-29 19:24:46', '2026-02-01 21:55:47', 0),
	(5, '员工管理', '/system/enterprise/employee', 'system/enterprise/employee/index.vue', NULL, 1, 'ep:user-filled', 4, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-01-29 19:44:28', 0),
	(6, '角色管理', '/system/enterprise/role', 'system/enterprise/role/index.vue', NULL, 12, 'ep:lock', 5, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-02-02 16:04:48', 0),
	(7, '菜单管理', '/system/systemset/menu', 'system/systemset/menu/index.vue', NULL, 12, 'ep:histogram', 2, 0, NULL, 1, 1, '2026-01-29 19:24:46', '2026-02-01 01:48:35', 0),
	(8, '欢迎页', '/welcome', 'welcome/index.vue', NULL, 0, 'HomeFilled', 0, 0, NULL, 1, 0, '2026-01-29 19:24:46', '2026-02-02 19:39:15', 1),
	(12, '系统设置', '/system/systemset', 'Layout', '/system/systemset/menu', 0, 'ep:tools', 1, 0, NULL, 1, 1, '2026-02-01 01:19:15', '2026-02-02 16:53:23', 0),
	(13, '客户管理', '/customer', 'Layout', NULL, 0, 'ep:avatar', 0, 0, NULL, 1, 1, '2026-02-02 09:47:02', '2026-02-02 09:47:39', 0),
	(14, '客户信息', '/customer/info', 'customer/info/index.vue', NULL, 13, 'ep:info-filled', 0, 0, NULL, 1, 1, '2026-02-02 09:49:13', '2026-02-04 00:44:47', 0),
	(15, '到店管理', '/customer/visit', 'customer/visit/index', NULL, 13, 'ep:checked', 0, 0, NULL, 1, 1, '2026-02-02 09:51:52', '2026-02-04 00:44:50', 0),
	(16, '消费分析', '/customer/consumption', 'customer/consumption/index.vue', NULL, 13, 'ep:histogram', 0, 0, NULL, 1, 1, '2026-02-02 10:00:21', '2026-02-04 00:44:54', 0),
	(17, '品项分析', '/customer/item', 'customer/item/index.vue', NULL, 13, 'ep:switch-filled', 0, 0, NULL, 1, 1, '2026-02-02 10:01:53', '2026-02-04 00:44:56', 0),
	(18, '卡项管理', '/card-item', 'Layout', NULL, 0, 'ep:checked', 5, 0, NULL, 1, 1, '2026-02-03 22:56:09', '2026-02-04 00:53:56', 0),
	(19, '项目管理', '/card-item/project', 'card-item/project/index.vue', NULL, 18, 'ep:list', 1, 0, NULL, 1, 1, '2026-02-03 22:56:09', '2026-02-04 00:46:58', 0),
	(20, '主卡管理', '/card-item/card', 'card-item/card/index.vue', '', 18, 'ep:ticket', 2, 0, NULL, 1, 1, '2026-02-03 22:56:09', '2026-02-25 13:09:07', 0),
	(24, '收银管理', '/cashier', 'Layout', NULL, 0, 'ep:platform', 0, 0, NULL, 1, 1, '2026-02-09 23:21:56', '2026-02-10 00:31:34', 0),
	(25, '前台收银', '/cashier/front-desk', '/cashier/front-desk/index.vue', NULL, 24, 'ep:shopping-cart-full', 0, 0, NULL, 1, 1, '2026-02-09 23:23:33', '2026-02-10 00:20:53', 0),
	(26, '预约管理', '/cashier/appointment-management', '/cashier/appointment-management/index.vue', NULL, 24, 'ep:calendar', 0, 0, NULL, 1, 1, '2026-02-11 10:52:57', '2026-02-11 10:54:18', 0),
	(30, '供应商管理', '/card-item/supplier', 'card-item/supplier/index', NULL, 18, 'ep:goods', 3, 0, NULL, 1, 1, '2026-02-25 19:29:01', '2026-02-25 19:29:01', 0),
	(31, '产品管理', '/card-item/product', 'card-item/product/index', NULL, 18, 'ep:goods', 4, 0, NULL, 1, 1, '2026-02-25 20:24:27', '2026-02-25 20:24:27', 0);
/*!40000 ALTER TABLE `sys_menu` ENABLE KEYS */;

-- 导出  表 mydream.sys_permission 结构
CREATE TABLE IF NOT EXISTS `sys_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `menu_id` int(10) unsigned DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` tinyint(4) NOT NULL DEFAULT '2' COMMENT '权限类型(1:菜单权限,2:按钮权限)',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=85 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='权限表';

-- 正在导出表  mydream.sys_permission 的数据：~82 rows (大约)
/*!40000 ALTER TABLE `sys_permission` DISABLE KEYS */;
INSERT INTO `sys_permission` (`id`, `name`, `code`, `description`, `menu_id`, `status`, `created_at`, `updated_at`, `type`) VALUES
	(1, '公司管理-查看', 'company:view', NULL, 2, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(2, '公司管理-添加', 'company:add', NULL, 2, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(3, '公司管理-编辑', 'company:edit', NULL, 2, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(4, '公司管理-删除', 'company:delete', NULL, 2, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(5, '门店管理-查看', 'store:view', NULL, 3, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(6, '门店管理-添加', 'store:add', NULL, 3, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(7, '门店管理-编辑', 'store:edit', NULL, 3, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(8, '门店管理-删除', 'store:delete', NULL, 3, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(9, '部门管理-查看', 'department:view', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(10, '部门管理-添加', 'department:add', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(11, '部门管理-编辑', 'department:edit', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(12, '部门管理-删除', 'department:delete', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(13, '职位管理-查看', 'position:view', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(14, '职位管理-添加', 'position:add', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(15, '职位管理-编辑', 'position:edit', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(16, '职位管理-删除', 'position:delete', NULL, 4, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(17, '员工管理-查看', 'employee:view', NULL, 5, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(18, '员工管理-添加', 'employee:add', NULL, 5, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(19, '员工管理-编辑', 'employee:edit', NULL, 5, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(20, '员工管理-删除', 'employee:delete', NULL, 5, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(21, '角色管理-查看', 'role:view', NULL, 6, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(22, '角色管理-添加', 'role:add', NULL, 6, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(23, '角色管理-编辑', 'role:edit', NULL, 6, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(24, '角色管理-删除', 'role:delete', NULL, 6, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(25, '菜单管理-查看', 'menu:view', NULL, 7, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(26, '菜单管理-添加', 'menu:add', NULL, 7, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(27, '菜单管理-编辑', 'menu:edit', NULL, 7, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(28, '菜单管理-删除', 'menu:delete', NULL, 7, 1, '2026-01-29 19:24:46', '2026-01-31 21:38:11', 2),
	(29, '菜单管理-权限分配', 'menu:permission', '', 7, 1, '2026-01-31 21:49:23', '2026-01-31 22:52:00', 2),
	(30, '角色管理-权限分配', 'role:permission', NULL, 6, 1, '2026-01-31 22:51:41', '2026-01-31 22:51:57', 2),
	(31, '菜单管理-权限添加', 'menu:permission:add', NULL, 7, 1, '2026-01-31 22:56:13', '2026-01-31 23:50:21', 2),
	(32, '菜单管理-权限编辑', 'menu:permission:edit', '', 7, 1, '2026-01-31 23:03:59', '2026-01-31 23:03:59', 2),
	(33, '菜单管理-权限删除', 'menu:permission:delete', '', 7, 1, '2026-01-31 23:04:46', '2026-01-31 23:04:46', 2),
	(34, '超级管理员', 'role:issuper', '', 6, 0, '2026-02-01 00:00:42', '2026-02-01 00:07:38', 2),
	(35, '部门管理-查看', 'store:department:view', '部门查看', 3, 1, '2026-02-01 21:32:26', '2026-02-01 21:34:43', 2),
	(36, '部门管理-新增', 'store:department:add', '', 3, 1, '2026-02-01 21:32:57', '2026-02-01 21:34:39', 2),
	(37, '部门管理-编辑', 'store:department:edit', '', 3, 1, '2026-02-01 21:33:29', '2026-02-01 21:34:34', 2),
	(38, '部门管理-删除', 'store:department:delete', '', 3, 1, '2026-02-01 21:34:28', '2026-02-01 21:34:28', 2),
	(39, '职位管理-查看', 'store:position:view', '', 3, 1, '2026-02-01 21:42:53', '2026-02-01 21:42:53', 2),
	(40, '职位管理-新增', 'store:position:add', '', 3, 1, '2026-02-01 21:43:22', '2026-02-01 21:43:22', 2),
	(41, '职位管理-编辑', 'store:position:edit', '', 3, 1, '2026-02-01 21:43:39', '2026-02-01 21:43:39', 2),
	(42, '职位管理-删除', 'store:position:delete', '', 3, 1, '2026-02-01 21:44:16', '2026-02-01 21:44:16', 2),
	(43, '客户信息-查看', 'customer:info:view', '', 14, 1, '2026-02-02 17:02:37', '2026-02-02 17:02:37', 2),
	(44, '客户信息-新增', 'customer:info:add', '', 14, 1, '2026-02-02 17:03:23', '2026-02-02 17:04:10', 2),
	(45, '客户信息-编辑', 'customer:info:edit', '', 14, 1, '2026-02-02 17:03:43', '2026-02-02 17:04:31', 2),
	(46, '客户信息-删除', 'customer:info:delete', '', 14, 1, '2026-02-02 17:04:49', '2026-02-02 17:04:49', 2),
	(47, '床位管理-查看', 'store:bedroom:view', '', 2, 1, '2026-02-03 21:29:55', '2026-02-03 21:29:55', 2),
	(48, '床位管理-新增', 'store:bedroom:add', '', 2, 1, '2026-02-03 21:30:13', '2026-02-03 21:30:13', 2),
	(49, '床位管理-编辑', 'store:bedroom:edit', '', 2, 1, '2026-02-03 21:30:31', '2026-02-03 21:30:31', 2),
	(50, '床位管理-删除', 'store:bedroom:delete', '', 2, 1, '2026-02-03 21:30:50', '2026-02-03 21:30:50', 2),
	(51, '充值卡-查看', 'card:recharge:view', '', 20, 1, '2026-02-04 00:34:56', '2026-02-25 13:13:40', 2),
	(52, '充值卡-新增', 'card:recharge:add', '', 20, 1, '2026-02-04 00:35:18', '2026-02-25 13:13:41', 2),
	(53, '充值卡-编辑', 'card:recharge:edit', '', 20, 1, '2026-02-04 00:35:46', '2026-02-25 13:13:36', 2),
	(54, '充值卡-删除', 'card:recharge:delete', '', 20, 1, '2026-02-04 00:36:08', '2026-02-25 13:13:38', 2),
	(55, '供应商-查看', 'project:supplier:view', '', 19, 1, '2026-02-04 01:02:22', '2026-02-06 16:53:51', 2),
	(56, '供应商-新增', 'project:supplier:add', '', 19, 1, '2026-02-04 01:02:42', '2026-02-06 16:53:56', 2),
	(57, '供应商-编辑', 'project:supplier:edit', '', 19, 1, '2026-02-04 01:03:55', '2026-02-06 16:54:01', 2),
	(58, '供应商-删除', 'project:supplier:delete', '', 19, 1, '2026-02-04 01:04:16', '2026-02-06 16:54:06', 2),
	(59, '项目分类-查看', 'project:category:view', '', 19, 1, '2026-02-04 01:20:20', '2026-02-04 01:20:20', 2),
	(60, '项目分类-新增', 'project:category:add', '', 19, 1, '2026-02-04 01:20:36', '2026-02-04 01:20:36', 2),
	(61, '项目分类-编辑', 'project:category:edit', '', 19, 1, '2026-02-04 01:20:51', '2026-02-04 01:20:51', 2),
	(62, '项目分类-删除', 'project:category:delete', '', 19, 1, '2026-02-04 01:21:06', '2026-02-04 01:21:06', 2),
	(63, '房间设置-查看', 'store:room:view', '', 3, 1, '2026-02-05 10:29:56', '2026-02-05 10:29:56', 2),
	(64, '房间设置-新增', 'store:room:add', '', 3, 1, '2026-02-05 10:30:08', '2026-02-05 10:30:08', 2),
	(65, '房间设置-编辑', 'store:room:edit', '', 3, 1, '2026-02-05 10:30:30', '2026-02-05 10:30:30', 2),
	(66, '房间设置-删除', 'store:room:delete', '', 3, 1, '2026-02-05 10:30:45', '2026-02-05 10:30:45', 2),
	(67, '项目-查看', 'project:project:view', '', 19, 1, '2026-02-06 16:54:36', '2026-02-06 16:54:36', 2),
	(68, '项目-新增', 'project:project:add', '', 19, 1, '2026-02-06 16:54:50', '2026-02-06 16:54:50', 2),
	(69, '项目-编辑', 'project:project:edit', '', 19, 1, '2026-02-06 16:55:05', '2026-02-06 16:55:05', 2),
	(70, '项目-删除', 'project:project:delete', '', 19, 1, '2026-02-06 16:55:18', '2026-02-06 16:55:18', 2),
	(71, '套餐卡-查看', 'card:package:view', '', 20, 1, '2026-02-13 10:38:44', '2026-02-13 10:38:44', 2),
	(72, '套餐卡-新增', 'card:package:add', '', 20, 1, '2026-02-13 10:39:03', '2026-02-13 10:39:03', 2),
	(73, '套餐卡-编辑', 'card:package:edit', '', 20, 1, '2026-02-13 10:39:15', '2026-02-13 10:39:15', 2),
	(74, '套餐卡-删除', 'card:package:delete', '', 20, 1, '2026-02-13 10:39:35', '2026-02-13 10:39:35', 2),
	(77, '查看产品', 'product:product:view', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(78, '新增产品', 'product:product:add', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(79, '编辑产品', 'product:product:edit', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(80, '删除产品', 'product:product:delete', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(81, '查看产品分类', 'product:category:view', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(82, '新增产品分类', 'product:category:add', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(83, '编辑产品分类', 'product:category:edit', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2),
	(84, '删除产品分类', 'product:category:delete', NULL, 31, 1, '2026-02-25 20:28:25', '2026-02-25 20:28:25', 2);
/*!40000 ALTER TABLE `sys_permission` ENABLE KEYS */;

-- 导出  表 mydream.sys_position 结构
CREATE TABLE IF NOT EXISTS `sys_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `position_name` varchar(50) NOT NULL,
  `dept_id` int(10) unsigned NOT NULL DEFAULT '0',
  `sort` int(10) unsigned NOT NULL DEFAULT '0',
  `company_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除：0-未删除，1-已删除',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='职位表';

-- 正在导出表  mydream.sys_position 的数据：~13 rows (大约)
/*!40000 ALTER TABLE `sys_position` DISABLE KEYS */;
INSERT INTO `sys_position` (`id`, `position_name`, `dept_id`, `sort`, `company_id`, `description`, `status`, `created_at`, `updated_at`, `isDelete`) VALUES
	(1, '美容总经理', 2, 30, 2, '公司最高管理者', 1, '2026-01-29 06:57:25', '2026-01-30 16:39:00', 0),
	(2, '收银', 3, 19, 2, '协助总经理管理公司', 1, '2026-01-29 06:57:25', '2026-01-30 16:40:02', 0),
	(3, '美发店长', 1, 20, 2, '管理部门日常事务', 1, '2026-01-29 06:57:25', '2026-01-30 16:38:46', 0),
	(4, '美容店长', 2, 31, 2, '管理团队日常工作', 1, '2026-01-29 06:57:25', '2026-01-30 16:39:13', 0),
	(5, '专员', 0, 0, 0, '执行具体工作任务', 0, '2026-01-29 06:57:25', '2026-02-02 23:41:23', 1),
	(6, '助理', 0, 0, 0, '协助上级完成工作', 0, '2026-01-29 06:57:25', '2026-02-02 23:41:26', 1),
	(61, '董事长', 3, 10, 2, NULL, 1, '2026-01-30 16:19:59', '2026-01-30 16:38:36', 0),
	(62, '美容师', 2, 33, 2, NULL, 1, '2026-01-30 16:35:50', '2026-01-30 16:38:53', 0),
	(63, '顾问老师', 2, 32, 2, NULL, 1, '2026-01-30 16:39:49', '2026-01-30 16:39:49', 0),
	(64, '财务', 3, 12, 2, NULL, 1, '2026-01-30 16:40:38', '2026-01-30 16:40:38', 0),
	(65, '运营主管', 3, 13, 2, NULL, 1, '2026-01-30 16:41:11', '2026-01-30 16:41:11', 0),
	(66, '系统管理', 4, 0, 1, NULL, 1, '2026-01-31 13:41:35', '2026-01-31 13:41:35', 0),
	(67, '测试职位1', 6, 0, 3, NULL, 1, '2026-02-24 09:38:20', '2026-02-24 09:38:20', 0);
/*!40000 ALTER TABLE `sys_position` ENABLE KEYS */;

-- 导出  表 mydream.sys_role 结构
CREATE TABLE IF NOT EXISTS `sys_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `is_super` tinyint(1) NOT NULL DEFAULT '0',
  `company_id` int(10) unsigned DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色表';

-- 正在导出表  mydream.sys_role 的数据：~3 rows (大约)
/*!40000 ALTER TABLE `sys_role` DISABLE KEYS */;
INSERT INTO `sys_role` (`id`, `name`, `is_super`, `company_id`, `description`, `status`, `created_at`, `updated_at`) VALUES
	(1, '超级管理员', 1, 1, '拥有所有权限', 1, '2026-01-29 19:24:46', '2026-02-03 21:37:08'),
	(2, '管理员', 0, 2, '拥有大部分权限', 1, '2026-01-29 19:24:46', '2026-02-03 21:37:09'),
	(3, '普通用户', 0, 2, '拥有基础权限', 1, '2026-01-29 19:24:46', '2026-02-03 21:37:13');
/*!40000 ALTER TABLE `sys_role` ENABLE KEYS */;

-- 导出  表 mydream.sys_role_menu 结构
CREATE TABLE IF NOT EXISTS `sys_role_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `menu_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu` (`role_id`,`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色菜单关联表';

-- 正在导出表  mydream.sys_role_menu 的数据：~41 rows (大约)
/*!40000 ALTER TABLE `sys_role_menu` DISABLE KEYS */;
INSERT INTO `sys_role_menu` (`id`, `role_id`, `menu_id`) VALUES
	(1, 1, 1),
	(2, 1, 2),
	(18, 1, 3),
	(5, 1, 5),
	(6, 1, 6),
	(7, 1, 7),
	(34, 1, 12),
	(49, 1, 13),
	(50, 1, 14),
	(51, 1, 15),
	(52, 1, 16),
	(53, 1, 17),
	(63, 1, 18),
	(64, 1, 19),
	(65, 1, 20),
	(68, 1, 24),
	(69, 1, 25),
	(9, 2, 1),
	(10, 2, 2),
	(11, 2, 3),
	(13, 2, 5),
	(14, 2, 6),
	(19, 2, 7),
	(45, 2, 12),
	(35, 3, 1),
	(48, 3, 2),
	(37, 3, 3),
	(46, 3, 5),
	(47, 3, 6),
	(42, 3, 7),
	(44, 3, 12),
	(54, 3, 13),
	(55, 3, 14),
	(57, 3, 18),
	(58, 3, 19),
	(59, 3, 20),
	(66, 3, 24),
	(67, 3, 25),
	(70, 3, 26),
	(74, 3, 30),
	(75, 3, 31);
/*!40000 ALTER TABLE `sys_role_menu` ENABLE KEYS */;

-- 导出  表 mydream.sys_role_permission 结构
CREATE TABLE IF NOT EXISTS `sys_role_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`,`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=307 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色权限关联表';

-- 正在导出表  mydream.sys_role_permission 的数据：~150 rows (大约)
/*!40000 ALTER TABLE `sys_role_permission` DISABLE KEYS */;
INSERT INTO `sys_role_permission` (`id`, `role_id`, `permission_id`) VALUES
	(115, 1, 1),
	(116, 1, 2),
	(117, 1, 3),
	(118, 1, 4),
	(119, 1, 5),
	(121, 1, 6),
	(120, 1, 7),
	(122, 1, 8),
	(123, 1, 9),
	(124, 1, 10),
	(125, 1, 11),
	(126, 1, 12),
	(127, 1, 13),
	(128, 1, 14),
	(129, 1, 15),
	(130, 1, 16),
	(131, 1, 17),
	(132, 1, 18),
	(133, 1, 19),
	(134, 1, 20),
	(138, 1, 21),
	(137, 1, 22),
	(136, 1, 23),
	(135, 1, 24),
	(139, 1, 25),
	(140, 1, 26),
	(141, 1, 27),
	(142, 1, 28),
	(143, 1, 29),
	(144, 1, 30),
	(145, 1, 31),
	(146, 1, 32),
	(147, 1, 33),
	(203, 1, 35),
	(204, 1, 36),
	(218, 1, 37),
	(219, 1, 38),
	(220, 1, 39),
	(221, 1, 40),
	(222, 1, 41),
	(223, 1, 42),
	(243, 1, 43),
	(244, 1, 44),
	(245, 1, 45),
	(246, 1, 46),
	(263, 1, 47),
	(264, 1, 48),
	(265, 1, 49),
	(266, 1, 50),
	(275, 1, 51),
	(276, 1, 52),
	(277, 1, 53),
	(278, 1, 54),
	(267, 1, 55),
	(268, 1, 56),
	(269, 1, 57),
	(270, 1, 58),
	(271, 1, 59),
	(272, 1, 60),
	(273, 1, 61),
	(274, 1, 62),
	(289, 1, 63),
	(290, 1, 64),
	(287, 1, 65),
	(288, 1, 66),
	(291, 1, 67),
	(292, 1, 68),
	(293, 1, 69),
	(294, 1, 70),
	(190, 2, 21),
	(191, 2, 22),
	(192, 2, 23),
	(193, 2, 24),
	(195, 2, 25),
	(194, 2, 30),
	(208, 3, 1),
	(224, 3, 2),
	(225, 3, 3),
	(226, 3, 4),
	(214, 3, 5),
	(227, 3, 6),
	(228, 3, 7),
	(229, 3, 8),
	(188, 3, 9),
	(189, 3, 13),
	(217, 3, 17),
	(236, 3, 18),
	(237, 3, 19),
	(238, 3, 20),
	(199, 3, 21),
	(205, 3, 22),
	(198, 3, 23),
	(206, 3, 24),
	(202, 3, 25),
	(209, 3, 26),
	(210, 3, 27),
	(200, 3, 28),
	(211, 3, 29),
	(207, 3, 30),
	(212, 3, 31),
	(213, 3, 32),
	(201, 3, 33),
	(215, 3, 35),
	(230, 3, 36),
	(231, 3, 37),
	(232, 3, 38),
	(216, 3, 39),
	(235, 3, 40),
	(234, 3, 41),
	(233, 3, 42),
	(239, 3, 43),
	(240, 3, 44),
	(241, 3, 45),
	(242, 3, 46),
	(247, 3, 47),
	(248, 3, 48),
	(249, 3, 49),
	(250, 3, 50),
	(258, 3, 51),
	(257, 3, 52),
	(256, 3, 53),
	(255, 3, 54),
	(251, 3, 55),
	(252, 3, 56),
	(253, 3, 57),
	(254, 3, 58),
	(259, 3, 59),
	(260, 3, 60),
	(261, 3, 61),
	(262, 3, 62),
	(279, 3, 63),
	(280, 3, 64),
	(281, 3, 65),
	(282, 3, 66),
	(283, 3, 67),
	(284, 3, 68),
	(285, 3, 69),
	(286, 3, 70),
	(295, 3, 71),
	(296, 3, 72),
	(297, 3, 73),
	(298, 3, 74),
	(299, 3, 77),
	(300, 3, 78),
	(301, 3, 79),
	(302, 3, 80),
	(303, 3, 81),
	(304, 3, 82),
	(305, 3, 83),
	(306, 3, 84);
/*!40000 ALTER TABLE `sys_role_permission` ENABLE KEYS */;

-- 导出  表 mydream.sys_store 结构
CREATE TABLE IF NOT EXISTS `sys_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` varchar(255) NOT NULL,
  `store_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `company_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除：0-未删除，1-已删除',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店表';

-- 正在导出表  mydream.sys_store 的数据：~4 rows (大约)
/*!40000 ALTER TABLE `sys_store` DISABLE KEYS */;
INSERT INTO `sys_store` (`id`, `store_name`, `phone`, `address`, `store_type`, `company_id`, `status`, `created_at`, `updated_at`, `isDelete`) VALUES
	(1, '顺义店', '021-11111111', '上海市普陀区', '综合', 2, 1, '2026-01-30 18:38:01', '2026-01-30 18:40:26', 0),
	(2, '肇嘉浜店', '13800138000', '上海市徐汇区肇嘉浜路201号', '综合', 2, 1, '2026-01-30 18:42:21', '2026-01-30 23:51:01', 0),
	(3, '系统管理', '13888888888', '111111', '综合', 1, 1, '2026-01-31 13:38:31', '2026-01-31 13:38:31', 0),
	(4, '测试门店1', '122222222', '第三方文身断发', '美容', 3, 1, '2026-02-24 09:35:41', '2026-02-24 09:35:41', 0);
/*!40000 ALTER TABLE `sys_store` ENABLE KEYS */;

-- 导出  表 mydream.sys_store_department 结构
CREATE TABLE IF NOT EXISTS `sys_store_department` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_department` (`store_id`,`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店部门关联表';

-- 正在导出表  mydream.sys_store_department 的数据：~6 rows (大约)
/*!40000 ALTER TABLE `sys_store_department` DISABLE KEYS */;
INSERT INTO `sys_store_department` (`id`, `store_id`, `department_id`) VALUES
	(4, 1, 1),
	(5, 1, 2),
	(9, 2, 1),
	(10, 2, 2),
	(14, 3, 3),
	(15, 4, 6);
/*!40000 ALTER TABLE `sys_store_department` ENABLE KEYS */;

-- 导出  表 mydream.sys_user 结构
CREATE TABLE IF NOT EXISTS `sys_user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nickname` varchar(50) DEFAULT NULL,
  `avatar` varchar(255) DEFAULT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `idx_sys_user_username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户表';

-- 正在导出表  mydream.sys_user 的数据：~7 rows (大约)
/*!40000 ALTER TABLE `sys_user` DISABLE KEYS */;
INSERT INTO `sys_user` (`id`, `username`, `password`, `nickname`, `avatar`, `status`, `created_at`, `updated_at`) VALUES
	(1, 'admin', '$2y$10$S.T/5Z23QFHR5w47N/go1.XxQVCr.oXHRjTzl5FSpT2PkqYYyGE3u', '', '', 1, '2026-01-29 19:24:46', '2026-01-30 23:20:15'),
	(2, 'test', '$2y$10$X6fnPLs9N0rSL9ld3bAdTOUWF7LCRzQZ/OZR9B5uLESju4hpxGJOm', '', '', 1, '2026-01-30 22:30:39', '2026-02-02 23:02:28'),
	(3, 'test1', '$2y$10$WkNRuAi17Z8Use2gMVDpQeZ9B.gry7K4Ho4t1YpkWofCDvvPNaO62', '', '', 1, '2026-01-30 22:34:38', '2026-01-30 22:34:38'),
	(4, 'test2', '$2y$10$xsS.bdvysisnp4A/9D.h2Oa2e7E04kGDr5pSNUzYjSQUpC5ZmTJDi', '', '', 1, '2026-01-31 00:46:31', '2026-01-31 00:46:31'),
	(6, 'meifa2', '$2y$10$GD4MR.Si39ufWH45WtEr9O4kJuNdyPGkuJqYkt3psuqF63TtrF2Da', '', '', 1, '2026-02-06 18:04:49', '2026-02-06 18:04:49'),
	(7, 'meifa1', '$2y$10$qv.3TfY09OGZtpnRWIX5WeyU/OgQpzHWPYqYzeOhh3yrq1BPFtE5i', '', '', 1, '2026-02-06 18:10:51', '2026-02-06 18:10:51'),
	(8, 'ceshi1', '$2y$10$XcB4ENsCVI.hPlw7JgJBDuJYAJP2Lrk5qKep0ovvtRUf1fu8aHOtK', '', '', 1, '2026-02-24 09:38:50', '2026-02-24 09:38:50');
/*!40000 ALTER TABLE `sys_user` ENABLE KEYS */;

-- 导出  表 mydream.sys_user_employee 结构
CREATE TABLE IF NOT EXISTS `sys_user_employee` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `user_id` int(10) unsigned DEFAULT NULL,
  `company_id` int(10) unsigned DEFAULT NULL,
  `store_id` int(10) NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  `position_id` int(10) unsigned DEFAULT NULL,
  `superior_id` int(10) unsigned NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `isDelete` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否删除：0-未删除，1-已删除',
  PRIMARY KEY (`id`,`superior_id` DESC) USING BTREE,
  KEY `department_id` (`department_id`),
  KEY `superior_id` (`superior_id`),
  KEY `idx_sys_employee_user_id` (`user_id`),
  KEY `idx_sys_employee_company_id` (`company_id`),
  KEY `idx_sys_employee_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='员工表';

-- 正在导出表  mydream.sys_user_employee 的数据：~7 rows (大约)
/*!40000 ALTER TABLE `sys_user_employee` DISABLE KEYS */;
INSERT INTO `sys_user_employee` (`id`, `name`, `user_id`, `company_id`, `store_id`, `department_id`, `position_id`, `superior_id`, `status`, `created_at`, `updated_at`, `isDelete`) VALUES
	(1, 'test', 2, 2, 2, 2, 62, 5, 1, '2026-01-30 18:55:03', '2026-02-06 17:54:24', 0),
	(2, '超级管理员', 1, 1, 3, 4, 66, 0, 0, '2026-01-30 18:55:06', '2026-02-01 09:01:30', 0),
	(4, 'test22', 3, 2, 1, 2, 62, 0, 1, '2026-01-30 22:34:38', '2026-02-05 22:33:44', 0),
	(5, 'test的上级', 4, 2, 2, 2, 63, 0, 1, '2026-01-31 00:46:31', '2026-02-05 14:16:41', 0),
	(6, '美发2', 6, 2, 2, 1, 3, 0, 1, '2026-02-06 18:04:49', '2026-02-06 18:10:00', 0),
	(7, '美发1', 7, 2, 2, 1, 3, 6, 1, '2026-02-06 18:10:51', '2026-02-06 18:10:51', 0),
	(8, '测试1', 8, 3, 4, 6, 67, 0, 1, '2026-02-24 09:38:51', '2026-02-24 09:38:51', 0);
/*!40000 ALTER TABLE `sys_user_employee` ENABLE KEYS */;

-- 导出  表 mydream.sys_user_profile 结构
CREATE TABLE IF NOT EXISTS `sys_user_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthday_solar` date DEFAULT NULL,
  `birthday_lunar` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_card` varchar(18) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_contact` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entry_date` date DEFAULT NULL,
  `leave_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sys_user_profile_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- 正在导出表  mydream.sys_user_profile 的数据：7 rows
/*!40000 ALTER TABLE `sys_user_profile` DISABLE KEYS */;
INSERT INTO `sys_user_profile` (`id`, `user_id`, `phone`, `email`, `birthday_solar`, `birthday_lunar`, `id_card`, `address`, `emergency_contact`, `emergency_phone`, `entry_date`, `leave_date`, `created_at`, `updated_at`) VALUES
	(1, 2, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-01-30 22:32:20', '2026-01-30 22:32:20'),
	(2, 3, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-01-30 22:34:38', '2026-01-30 22:34:38'),
	(3, 1, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-01-30 23:20:15', '2026-01-30 23:20:15'),
	(4, 4, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-01-31 00:46:31', '2026-01-31 00:46:31'),
	(5, 6, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-02-06 18:04:49', '2026-02-06 18:04:49'),
	(6, 7, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-02-06 18:10:51', '2026-02-06 18:10:51'),
	(7, 8, '', '', NULL, '', '', '', '', '', NULL, NULL, '2026-02-24 09:38:51', '2026-02-24 09:38:51');
/*!40000 ALTER TABLE `sys_user_profile` ENABLE KEYS */;

-- 导出  表 mydream.sys_user_role 结构
CREATE TABLE IF NOT EXISTS `sys_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`,`role_id`),
  KEY `idx_sys_user_role_user_id` (`user_id`),
  KEY `idx_sys_user_role_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户角色关联表';

-- 正在导出表  mydream.sys_user_role 的数据：~7 rows (大约)
/*!40000 ALTER TABLE `sys_user_role` DISABLE KEYS */;
INSERT INTO `sys_user_role` (`id`, `user_id`, `role_id`) VALUES
	(21, 1, 1),
	(44, 2, 3),
	(30, 3, 2),
	(25, 4, 2),
	(45, 6, 3),
	(46, 7, 3),
	(47, 8, 3);
/*!40000 ALTER TABLE `sys_user_role` ENABLE KEYS */;

-- 导出  表 mydream.sys_user_store 结构
CREATE TABLE IF NOT EXISTS `sys_user_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_user_store` (`user_id`,`store_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_sys_user_store_user_id` (`user_id`),
  KEY `idx_sys_user_store_store_id` (`store_id`),
  CONSTRAINT `fk_user_store_store` FOREIGN KEY (`store_id`) REFERENCES `sys_store` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_user_store_user` FOREIGN KEY (`user_id`) REFERENCES `sys_user` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=66 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户门店权限表';

-- 正在导出表  mydream.sys_user_store 的数据：~9 rows (大约)
/*!40000 ALTER TABLE `sys_user_store` DISABLE KEYS */;
INSERT INTO `sys_user_store` (`id`, `user_id`, `store_id`) VALUES
	(18, 1, 3),
	(61, 2, 1),
	(62, 2, 2),
	(33, 3, 1),
	(34, 3, 2),
	(24, 4, 1),
	(63, 6, 2),
	(64, 7, 2),
	(65, 8, 4);
/*!40000 ALTER TABLE `sys_user_store` ENABLE KEYS */;

/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
