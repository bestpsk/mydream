-- 时效卡类型管理数据库更新脚本
-- 执行日期: 2026-02-27

-- 1. 扩展 card_time 表字段
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `card_code` varchar(50) DEFAULT NULL COMMENT '卡编码' AFTER `card_name`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `original_price` decimal(10,2) DEFAULT '0.00' COMMENT '原价' AFTER `card_code`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `valid_type` tinyint(1) DEFAULT '1' COMMENT '有效期类型:1=固定天数,2=自定义' AFTER `valid_days`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `use_rule_type` tinyint(1) DEFAULT '1' COMMENT '使用规则:1=不限次数,2=限制总次数,3=限制频率' AFTER `price`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `max_use_count` int(11) DEFAULT NULL COMMENT '最大使用次数' AFTER `use_rule_type`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `interval_hours` int(11) DEFAULT NULL COMMENT '使用间隔小时数' AFTER `max_use_count`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `project_bind_type` tinyint(1) DEFAULT '1' COMMENT '项目绑定类型:1=单选,2=多选,3=全店通用' AFTER `interval_hours`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `customer_count` int(11) DEFAULT '0' COMMENT '已办理顾客数' AFTER `project_bind_type`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `description` text COMMENT '描述' AFTER `customer_count`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `remark` text COMMENT '备注' AFTER `description`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `status` tinyint(1) DEFAULT '1' COMMENT '状态:0=禁用,1=启用' AFTER `remark`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `online_time` datetime DEFAULT NULL COMMENT '上线时间' AFTER `status`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `offline_time` datetime DEFAULT NULL COMMENT '下线时间' AFTER `online_time`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `sale_store_ids` text COMMENT '限定销售分店(JSON数组)' AFTER `offline_time`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `consume_store_ids` text COMMENT '限定消费分店(JSON数组)' AFTER `sale_store_ids`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `sale_department_ids` text COMMENT '限定销售部门(JSON数组)' AFTER `consume_store_ids`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `consume_department_ids` text COMMENT '限定消费部门(JSON数组)' AFTER `sale_department_ids`;
ALTER TABLE `card_time` ADD COLUMN IF NOT EXISTS `is_modifiable` tinyint(1) DEFAULT '0' COMMENT '是否禁止修改' AFTER `consume_department_ids`;

-- 2. 创建时效卡包含项目表
CREATE TABLE IF NOT EXISTS `card_time_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '公司ID',
  `time_card_id` int(10) unsigned NOT NULL COMMENT '时效卡ID',
  `project_id` int(10) unsigned NOT NULL COMMENT '项目ID',
  `times` int(11) NOT NULL DEFAULT '1' COMMENT '次数',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
  `consume` int(11) NOT NULL DEFAULT '0' COMMENT '耗卡',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手工费',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_time_card_id` (`time_card_id`),
  KEY `idx_project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='时效卡包含项目表';

-- 3. 创建时效卡包含产品表
CREATE TABLE IF NOT EXISTS `card_time_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '公司ID',
  `time_card_id` int(10) unsigned NOT NULL COMMENT '时效卡ID',
  `product_id` int(10) unsigned NOT NULL COMMENT '产品ID',
  `times` int(11) NOT NULL DEFAULT '1' COMMENT '数量',
  `unit_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '单价',
  `total_price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总价',
  `manual_salary` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '手工费',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_time_card_id` (`time_card_id`),
  KEY `idx_product_id` (`product_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='时效卡包含产品表';
