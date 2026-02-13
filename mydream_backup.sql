-- MySQL dump 10.13  Distrib 5.7.26, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: mydream
-- ------------------------------------------------------
-- Server version	8.0.12

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bedroom`
--

DROP TABLE IF EXISTS `bedroom`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bedroom` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `room_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bed_count` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_id` (`store_id`),
  KEY `idx_room_name` (`room_name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bedroom`
--

LOCK TABLES `bedroom` WRITE;
/*!40000 ALTER TABLE `bedroom` DISABLE KEYS */;
INSERT INTO `bedroom` VALUES (1,1,'VIP1',1,'2026-02-03 13:43:41','2026-02-03 13:44:07'),(2,2,'爱琴海',1,'2026-02-03 18:08:45','2026-02-03 18:08:45'),(3,1,'大草原',2,'2026-02-12 04:08:17','2026-02-12 04:08:17'),(4,2,'哈哈镜',2,'2026-02-12 04:08:30','2026-02-12 04:08:30');
/*!40000 ALTER TABLE `bedroom` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_package`
--

DROP TABLE IF EXISTS `card_package`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_package` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `card_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `project_count` int(11) NOT NULL DEFAULT '0' COMMENT '包含项目数',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `card_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '编码，用于首字母搜索',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '套餐卡描述',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
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
  `sale_store_ids` text COLLATE utf8mb4_unicode_ci COMMENT '限定销售分店',
  `consume_store_ids` text COLLATE utf8mb4_unicode_ci COMMENT '限定消费分店',
  `sale_department_ids` text COLLATE utf8mb4_unicode_ci COMMENT '限定销售部门',
  `consume_department_ids` text COLLATE utf8mb4_unicode_ci COMMENT '限定消费部门',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='套餐卡表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_package`
--

LOCK TABLES `card_package` WRITE;
/*!40000 ALTER TABLE `card_package` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_package` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_package_gift_product`
--

DROP TABLE IF EXISTS `card_package_gift_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_package_gift_product` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_package_gift_product`
--

LOCK TABLES `card_package_gift_product` WRITE;
/*!40000 ALTER TABLE `card_package_gift_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_package_gift_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_package_gift_project`
--

DROP TABLE IF EXISTS `card_package_gift_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_package_gift_project` (
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
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_package_gift_project`
--

LOCK TABLES `card_package_gift_project` WRITE;
/*!40000 ALTER TABLE `card_package_gift_project` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_package_gift_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_product`
--

DROP TABLE IF EXISTS `card_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL,
  `product_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `original_price` decimal(10,2) DEFAULT '0.00',
  `sale_price` decimal(10,2) DEFAULT '0.00',
  `supplier_id` int(11) DEFAULT '0',
  `product_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) DEFAULT '1',
  `emark` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `category_id` (`category_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_product`
--

LOCK TABLES `card_product` WRITE;
/*!40000 ALTER TABLE `card_product` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_project`
--

DROP TABLE IF EXISTS `card_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_project` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `project_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '项目名称',
  `category_id` int(10) unsigned NOT NULL COMMENT '所属分类ID',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  `original_price` decimal(10,2) DEFAULT '0.00',
  `single_sale_price` decimal(10,2) DEFAULT '0.00',
  `experience_price` decimal(10,2) DEFAULT '0.00',
  `external_display_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_id` int(11) DEFAULT '0',
  `project_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
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
  `limited_sale_stores` text COLLATE utf8mb4_unicode_ci,
  `limited_service_stores` text COLLATE utf8mb4_unicode_ci,
  `allow_gift` tinyint(1) DEFAULT '0',
  `remark` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='项目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_project`
--

LOCK TABLES `card_project` WRITE;
/*!40000 ALTER TABLE `card_project` DISABLE KEYS */;
INSERT INTO `card_project` VALUES (1,2,'暨大美塑面部补水',8,0,'2026-02-07 07:40:45','2026-02-07 15:53:37',580.00,198.00,98.00,'暨大美塑',4,'面部',0,0,0.00,60,1,3,0,NULL,1,0,0,0,0,0,'0','0',0,''),(2,2,'测试2',6,0,'2026-02-12 04:21:48','2026-02-12 04:21:48',980.00,498.00,398.00,'测试',1,'眼部',0,0,1.00,60,1,1,0,NULL,0,0,0,0,0,0,'0','0',0,''),(3,2,'测试3',9,0,'2026-02-13 03:26:43','2026-02-13 03:26:43',980.00,580.00,480.00,'测试',6,'全身',0,0,1.00,60,1,1,0,NULL,0,0,0,0,0,0,'0','0',0,''),(4,2,'测试4',8,0,'2026-02-13 03:27:28','2026-02-13 03:27:28',680.00,588.00,388.00,'测试',5,'腹部',0,0,0.00,0,1,1,0,NULL,0,0,0,0,0,0,'0','0',0,'');
/*!40000 ALTER TABLE `card_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_project_category`
--

DROP TABLE IF EXISTS `card_project_category`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_project_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `category_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '分类名称',
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_project_category`
--

LOCK TABLES `card_project_category` WRITE;
/*!40000 ALTER TABLE `card_project_category` DISABLE KEYS */;
INSERT INTO `card_project_category` VALUES (1,2,'洗发',0,0,'2026-02-05 09:45:36','2026-02-05 09:45:36',1,0),(2,2,'洗剪吹',0,0,'2026-02-05 14:40:59','2026-02-05 14:40:59',1,0),(3,2,'烫发',0,0,'2026-02-05 14:42:35','2026-02-05 14:42:35',1,0),(4,2,'染发',0,0,'2026-02-05 14:42:53','2026-02-05 14:42:53',1,0),(5,2,'酸性',0,0,'2026-02-05 14:43:05','2026-02-05 14:43:05',1,0),(6,2,'自营面部',0,0,'2026-02-05 14:45:54','2026-02-05 15:19:26',2,0),(7,2,'自营身体',0,0,'2026-02-05 14:46:11','2026-02-05 15:19:33',2,0),(8,2,'合作面部',0,0,'2026-02-05 14:46:28','2026-02-05 15:20:11',2,0),(9,2,'合作身体',0,0,'2026-02-05 14:47:27','2026-02-05 15:20:20',2,0),(10,2,'合作特项',0,0,'2026-02-05 14:48:16','2026-02-05 15:23:41',2,0),(11,2,'特项-仪器',0,1,'2026-02-05 15:00:39','2026-02-05 15:24:13',2,0),(12,2,'11',0,1,'2026-02-05 15:23:54','2026-02-05 15:23:57',2,0);
/*!40000 ALTER TABLE `card_project_category` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_project_ingredient`
--

DROP TABLE IF EXISTS `card_project_ingredient`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_project_ingredient` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `product` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT '0.00',
  `unit` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remark` text COLLATE utf8mb4_unicode_ci,
  `isDelete` tinyint(1) DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `project_id` (`project_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_project_ingredient`
--

LOCK TABLES `card_project_ingredient` WRITE;
/*!40000 ALTER TABLE `card_project_ingredient` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_project_ingredient` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_project_sub`
--

DROP TABLE IF EXISTS `card_project_sub`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_project_sub` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_project_sub`
--

LOCK TABLES `card_project_sub` WRITE;
/*!40000 ALTER TABLE `card_project_sub` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_project_sub` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge`
--

DROP TABLE IF EXISTS `card_recharge`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(11) NOT NULL COMMENT '所属公司ID',
  `card_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
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
  `description` text COLLATE utf8mb4_unicode_ci COMMENT '描述',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `is_modifiable` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否可修改',
  `is_limit_once` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否限制一次',
  `is_expire_invalid` tinyint(1) NOT NULL DEFAULT '1' COMMENT '过期是否失效',
  `is_project_expire` tinyint(1) NOT NULL DEFAULT '1' COMMENT '项目是否过期',
  `is_prohibit_discount_modify` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否禁止折扣修改',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge`
--

LOCK TABLES `card_recharge` WRITE;
/*!40000 ALTER TABLE `card_recharge` DISABLE KEYS */;
INSERT INTO `card_recharge` VALUES (1,1,'测试充值卡',0,'2026-02-08 00:39:22','2026-02-08 00:39:22',1000.00,100.00,10.00,10.00,100,0.00,NULL,NULL,NULL,3,'测试充值卡描述','测试充值卡备注',0,1,0,1,1,0),(2,2,'?????',1,'2026-02-08 00:53:34','2026-02-08 00:53:34',1000.00,0.00,10.00,10.00,100,0.00,NULL,NULL,NULL,3,NULL,NULL,0,1,0,1,1,0),(3,2,'?????',1,'2026-02-08 00:54:44','2026-02-08 00:54:44',1000.00,0.00,10.00,10.00,100,0.00,NULL,NULL,NULL,3,NULL,NULL,0,1,0,1,1,0),(4,2,'?????',1,'2026-02-08 00:55:35','2026-02-08 00:55:35',1000.00,0.00,10.00,10.00,100,0.00,NULL,NULL,NULL,3,NULL,NULL,0,1,0,1,1,0),(5,2,'发过的',1,'2026-02-08 02:51:41','2026-02-08 02:51:41',5555.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(6,2,'大撒大声地',1,'2026-02-08 02:51:47','2026-02-08 02:51:47',5555.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(7,2,'撒大声地',1,'2026-02-08 02:52:21','2026-02-08 02:52:21',3333.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(8,2,'发过的',1,'2026-02-08 02:52:39','2026-02-08 02:52:39',5555.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(9,2,'大萨达',1,'2026-02-08 02:53:29','2026-02-08 02:53:29',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(10,2,'大萨达',1,'2026-02-08 02:54:05','2026-02-08 02:54:05',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(11,2,'大萨达',1,'2026-02-08 02:54:53','2026-02-08 02:54:53',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(12,2,'大萨达',0,'2026-02-08 02:54:55','2026-02-12 21:43:08',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',1,1,0,1,1,0),(13,2,'大萨达',1,'2026-02-08 02:55:38','2026-02-08 02:55:38',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(14,2,'大萨达',1,'2026-02-08 02:55:43','2026-02-08 02:55:43',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(15,2,'大萨达',1,'2026-02-08 02:55:59','2026-02-08 02:55:59',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(16,2,'大萨达',1,'2026-02-08 02:56:17','2026-02-08 02:56:17',4444.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(17,2,'实打实',1,'2026-02-08 02:56:47','2026-02-08 02:56:47',5555.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(18,2,'实打实',1,'2026-02-08 02:57:24','2026-02-08 02:57:24',5555.00,0.00,100.00,100.00,100,0.00,NULL,NULL,NULL,3,'','',0,1,0,1,1,0),(19,2,'实打实',0,'2026-02-08 02:58:11','2026-02-12 22:57:18',5555.00,0.00,100.00,100.00,100,0.00,'2026-02-02 16:00:00','2026-02-26 16:00:00',NULL,3,'11111','2222',1,0,1,1,1,1);
/*!40000 ALTER TABLE `card_recharge` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_consume_department`
--

DROP TABLE IF EXISTS `card_recharge_consume_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_consume_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_id` int(11) NOT NULL COMMENT '充值卡ID',
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='充值卡消费部门关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_consume_department`
--

LOCK TABLES `card_recharge_consume_department` WRITE;
/*!40000 ALTER TABLE `card_recharge_consume_department` DISABLE KEYS */;
INSERT INTO `card_recharge_consume_department` VALUES (1,5,1,'2026-02-07 18:51:41','2026-02-07 18:51:41'),(2,5,2,'2026-02-07 18:51:41','2026-02-07 18:51:41'),(3,6,1,'2026-02-07 18:51:47','2026-02-07 18:51:47'),(4,6,2,'2026-02-07 18:51:47','2026-02-07 18:51:47'),(5,7,1,'2026-02-07 18:52:21','2026-02-07 18:52:21'),(6,7,2,'2026-02-07 18:52:21','2026-02-07 18:52:21'),(7,8,1,'2026-02-07 18:52:39','2026-02-07 18:52:39'),(8,8,2,'2026-02-07 18:52:39','2026-02-07 18:52:39'),(9,9,1,'2026-02-07 18:53:30','2026-02-07 18:53:30'),(10,9,2,'2026-02-07 18:53:30','2026-02-07 18:53:30'),(11,10,1,'2026-02-07 18:54:05','2026-02-07 18:54:05'),(12,10,2,'2026-02-07 18:54:05','2026-02-07 18:54:05'),(13,11,1,'2026-02-07 18:54:53','2026-02-07 18:54:53'),(14,11,2,'2026-02-07 18:54:53','2026-02-07 18:54:53'),(17,13,1,'2026-02-07 18:55:39','2026-02-07 18:55:39'),(18,13,2,'2026-02-07 18:55:39','2026-02-07 18:55:39'),(19,14,1,'2026-02-07 18:55:43','2026-02-07 18:55:43'),(20,14,2,'2026-02-07 18:55:43','2026-02-07 18:55:43'),(21,15,1,'2026-02-07 18:55:59','2026-02-07 18:55:59'),(22,15,2,'2026-02-07 18:55:59','2026-02-07 18:55:59'),(23,16,1,'2026-02-07 18:56:17','2026-02-07 18:56:17'),(24,16,2,'2026-02-07 18:56:17','2026-02-07 18:56:17'),(25,17,1,'2026-02-07 18:56:47','2026-02-07 18:56:47'),(26,17,2,'2026-02-07 18:56:47','2026-02-07 18:56:47'),(27,18,1,'2026-02-07 18:57:25','2026-02-07 18:57:25'),(28,18,2,'2026-02-07 18:57:25','2026-02-07 18:57:25'),(91,19,1,'2026-02-12 14:57:18','2026-02-12 14:57:18'),(92,19,2,'2026-02-12 14:57:18','2026-02-12 14:57:18');
/*!40000 ALTER TABLE `card_recharge_consume_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_consume_store`
--

DROP TABLE IF EXISTS `card_recharge_consume_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_consume_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_recharge_store` (`recharge_id`,`store_id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_store_id` (`store_id`),
  CONSTRAINT `card_recharge_consume_store_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡消费分店表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_consume_store`
--

LOCK TABLES `card_recharge_consume_store` WRITE;
/*!40000 ALTER TABLE `card_recharge_consume_store` DISABLE KEYS */;
INSERT INTO `card_recharge_consume_store` VALUES (1,1,1),(25,19,1),(26,19,2);
/*!40000 ALTER TABLE `card_recharge_consume_store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_gift_product`
--

DROP TABLE IF EXISTS `card_recharge_gift_product`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_gift_product` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_gift_product`
--

LOCK TABLES `card_recharge_gift_product` WRITE;
/*!40000 ALTER TABLE `card_recharge_gift_product` DISABLE KEYS */;
INSERT INTO `card_recharge_gift_product` VALUES (1,4,1,5,200.00,0.00,20.00,'2026-02-07 16:55:35','2026-02-07 16:55:35');
/*!40000 ALTER TABLE `card_recharge_gift_product` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_gift_project`
--

DROP TABLE IF EXISTS `card_recharge_gift_project`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_gift_project` (
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='配赠项目表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_gift_project`
--

LOCK TABLES `card_recharge_gift_project` WRITE;
/*!40000 ALTER TABLE `card_recharge_gift_project` DISABLE KEYS */;
INSERT INTO `card_recharge_gift_project` VALUES (1,4,1,10,100.00,0.00,50.00,10.00,'2026-02-07 16:55:35','2026-02-07 16:55:35'),(19,19,1,1,0.00,0.00,0.00,40.00,'2026-02-12 14:57:18','2026-02-12 14:57:18'),(20,19,1,1,198.00,198.00,0.00,40.00,'2026-02-12 14:57:18','2026-02-12 14:57:18');
/*!40000 ALTER TABLE `card_recharge_gift_project` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_sale_department`
--

DROP TABLE IF EXISTS `card_recharge_sale_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_sale_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `recharge_id` int(11) NOT NULL COMMENT '充值卡ID',
  `department_id` int(11) NOT NULL COMMENT '部门ID',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_department_id` (`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=94 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='充值卡销售部门关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_sale_department`
--

LOCK TABLES `card_recharge_sale_department` WRITE;
/*!40000 ALTER TABLE `card_recharge_sale_department` DISABLE KEYS */;
INSERT INTO `card_recharge_sale_department` VALUES (1,5,1,'2026-02-07 18:51:41','2026-02-07 18:51:41'),(2,5,2,'2026-02-07 18:51:41','2026-02-07 18:51:41'),(3,6,1,'2026-02-07 18:51:47','2026-02-07 18:51:47'),(4,6,2,'2026-02-07 18:51:47','2026-02-07 18:51:47'),(5,7,1,'2026-02-07 18:52:21','2026-02-07 18:52:21'),(6,7,2,'2026-02-07 18:52:21','2026-02-07 18:52:21'),(7,8,1,'2026-02-07 18:52:39','2026-02-07 18:52:39'),(8,8,2,'2026-02-07 18:52:39','2026-02-07 18:52:39'),(9,9,1,'2026-02-07 18:53:30','2026-02-07 18:53:30'),(10,9,2,'2026-02-07 18:53:30','2026-02-07 18:53:30'),(11,10,1,'2026-02-07 18:54:05','2026-02-07 18:54:05'),(12,10,2,'2026-02-07 18:54:05','2026-02-07 18:54:05'),(13,11,1,'2026-02-07 18:54:53','2026-02-07 18:54:53'),(14,11,2,'2026-02-07 18:54:53','2026-02-07 18:54:53'),(17,13,1,'2026-02-07 18:55:39','2026-02-07 18:55:39'),(18,13,2,'2026-02-07 18:55:39','2026-02-07 18:55:39'),(19,14,1,'2026-02-07 18:55:43','2026-02-07 18:55:43'),(20,14,2,'2026-02-07 18:55:43','2026-02-07 18:55:43'),(21,15,1,'2026-02-07 18:55:59','2026-02-07 18:55:59'),(22,15,2,'2026-02-07 18:55:59','2026-02-07 18:55:59'),(23,16,1,'2026-02-07 18:56:17','2026-02-07 18:56:17'),(24,16,2,'2026-02-07 18:56:17','2026-02-07 18:56:17'),(25,17,1,'2026-02-07 18:56:47','2026-02-07 18:56:47'),(26,17,2,'2026-02-07 18:56:47','2026-02-07 18:56:47'),(27,18,1,'2026-02-07 18:57:25','2026-02-07 18:57:25'),(28,18,2,'2026-02-07 18:57:25','2026-02-07 18:57:25'),(92,19,1,'2026-02-12 14:57:18','2026-02-12 14:57:18'),(93,19,2,'2026-02-12 14:57:18','2026-02-12 14:57:18');
/*!40000 ALTER TABLE `card_recharge_sale_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_recharge_sale_store`
--

DROP TABLE IF EXISTS `card_recharge_sale_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_recharge_sale_store` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `recharge_id` int(10) unsigned NOT NULL,
  `store_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_recharge_store` (`recharge_id`,`store_id`),
  KEY `idx_recharge_id` (`recharge_id`),
  KEY `idx_store_id` (`store_id`),
  CONSTRAINT `card_recharge_sale_store_ibfk_1` FOREIGN KEY (`recharge_id`) REFERENCES `card_recharge` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='充值卡销售分店表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_recharge_sale_store`
--

LOCK TABLES `card_recharge_sale_store` WRITE;
/*!40000 ALTER TABLE `card_recharge_sale_store` DISABLE KEYS */;
INSERT INTO `card_recharge_sale_store` VALUES (1,1,2),(26,19,1),(27,19,2);
/*!40000 ALTER TABLE `card_recharge_sale_store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_supplier`
--

DROP TABLE IF EXISTS `card_supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_supplier` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '供应商名称',
  `contact` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系人',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '联系电话',
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '地址',
  `bank` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '开户银行',
  `bank_card` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '银行卡号',
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '邮箱',
  `prepay_balance` decimal(10,2) DEFAULT '0.00' COMMENT '预存余额',
  `delivery_balance` decimal(10,2) DEFAULT '0.00' COMMENT '配送余额',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='供应商表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_supplier`
--

LOCK TABLES `card_supplier` WRITE;
/*!40000 ALTER TABLE `card_supplier` DISABLE KEYS */;
INSERT INTO `card_supplier` VALUES (1,2,'宝迪佳','','','','','','',0.00,0.00,0,'2026-02-03 18:15:57','2026-02-07 09:09:18'),(2,2,'文强的','','','','','','',0.00,0.00,1,'2026-02-03 18:18:40','2026-02-07 09:09:01'),(3,2,'文强的','','','','','','',0.00,0.00,1,'2026-02-03 18:18:40','2026-02-07 09:09:06'),(4,2,'暨大美塑','','','','','','',0.00,0.00,0,'2026-02-05 14:53:45','2026-02-05 14:53:45'),(5,2,'英特波','','','','','','',0.00,0.00,0,'2026-02-05 14:54:08','2026-02-07 09:09:34'),(6,2,'艾灸','','','','','','',0.00,0.00,0,'2026-02-07 08:56:50','2026-02-07 09:09:50');
/*!40000 ALTER TABLE `card_supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `card_time`
--

DROP TABLE IF EXISTS `card_time`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `card_time` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `company_id` int(10) unsigned DEFAULT NULL COMMENT '所属公司ID',
  `card_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '卡名称',
  `valid_days` int(11) NOT NULL DEFAULT '0' COMMENT '有效期(天)',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '价格',
  `isDelete` tinyint(1) DEFAULT '0' COMMENT '是否删除',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='时效卡表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `card_time`
--

LOCK TABLES `card_time` WRITE;
/*!40000 ALTER TABLE `card_time` DISABLE KEYS */;
/*!40000 ALTER TABLE `card_time` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cust_customer`
--

DROP TABLE IF EXISTS `cust_customer`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cust_customer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL COMMENT '所属门店ID',
  `department_id` int(11) NOT NULL COMMENT '所属部门ID',
  `member_card` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '会员卡号',
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '姓名',
  `gender` tinyint(1) NOT NULL COMMENT '性别(1:男,2:女)',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '电话',
  `birthday` date DEFAULT NULL COMMENT '生日',
  `birthday_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '生日类别(1:阳历,2:阴历)',
  `points` int(11) NOT NULL DEFAULT '0' COMMENT '积分',
  `register_time` datetime NOT NULL COMMENT '注册时间',
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '来源',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT '头像',
  `archive_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '档案编号',
  `level` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT '普通客户' COMMENT '客户级别',
  `service_staff_id` int(11) DEFAULT NULL COMMENT '服务人ID(美容师)',
  `manager_id` int(11) DEFAULT NULL COMMENT '管理人ID(顾问)',
  `last_consume_time` datetime DEFAULT NULL COMMENT '最近消费时间',
  `last_consume_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消费金额',
  `last_deplete_time` datetime DEFAULT NULL COMMENT '最近消耗时间',
  `last_deplete_amount` decimal(10,2) DEFAULT '0.00' COMMENT '最近消耗金额',
  `remark` text COLLATE utf8mb4_unicode_ci COMMENT '备注',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1:正常,0:禁用)',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime NOT NULL COMMENT '更新时间',
  `name_pinyin` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT '姓名拼音首字母',
  PRIMARY KEY (`id`),
  UNIQUE KEY `member_card` (`member_card`),
  UNIQUE KEY `archive_number` (`archive_number`),
  UNIQUE KEY `phone` (`phone`),
  KEY `store_id` (`store_id`),
  KEY `department_id` (`department_id`),
  KEY `level` (`level`),
  KEY `register_time` (`register_time`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='客户信息表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cust_customer`
--

LOCK TABLES `cust_customer` WRITE;
/*!40000 ALTER TABLE `cust_customer` DISABLE KEYS */;
INSERT INTO `cust_customer` VALUES (1,1,2,'VIP001','张三',1,'13800138000','1990-01-01',1,1000,'2026-02-02 15:09:00','门店推荐','','ST001-DEP001-20260202001','银卡客户',1,1,'2026-02-02 15:09:00',500.00,'2026-02-02 15:09:00',200.00,'测试客户',1,'2026-02-02 15:09:00','2026-02-05 16:47:28','ZS'),(2,2,2,'VIP002','李四',2,'13900139000','1992-02-02',2,2000,'2026-02-02 15:09:00','线上推广','','ST001-DEP002-20260202002','金卡客户',2,2,'2026-02-02 15:09:00',1000.00,'2026-02-02 15:09:00',500.00,'测试客户2',1,'2026-02-02 15:09:00','2026-02-05 14:03:12','LS'),(3,1,1,'MC1202602058551','顺义客户1',2,'13367376545','1984-03-18',1,374,'2026-02-05 14:25:01','门店推荐','','AR11202602058870','银卡客户',0,0,NULL,0.00,NULL,0.00,'顺义店测试客户1',1,'2026-02-05 14:25:01','2026-02-05 16:46:58',''),(4,1,2,'MC1202602052646','顺义客户2',1,'13771814258','1981-05-22',2,120,'2026-02-05 14:25:01','门店推荐','','AR11202602052033','金卡客户',0,0,NULL,0.00,NULL,0.00,'顺义店测试客户2',1,'2026-02-05 14:25:01','2026-02-05 16:47:06',''),(5,2,1,'MC1202602055123','顺义客户3',1,'13891905613','1980-11-01',1,591,'2026-02-05 14:25:01','网络推广','','AR12202602055771','银卡客户',1,5,NULL,0.00,NULL,0.00,'顺义店测试客户3',1,'2026-02-05 14:25:01','2026-02-12 12:00:19',''),(6,2,1,'MC1202602059060','顺义客户4',1,'13375745115','1992-09-20',1,353,'2026-02-05 14:25:01','网络推广','','AR12202602056649','银卡客户',NULL,NULL,NULL,0.00,NULL,0.00,'顺义店测试客户4',1,'2026-02-05 14:25:01','2026-02-12 11:44:32',''),(7,1,1,'MC1202602058711','顺义客户5',2,'13433010886','1996-04-14',2,416,'2026-02-05 14:25:01','门店推荐','','AR11202602050868','普通客户',4,NULL,NULL,0.00,NULL,0.00,'顺义店测试客户55',1,'2026-02-05 14:25:01','2026-02-12 12:27:31',''),(8,2,1,'MC2202602051244','肇嘉浜客户1',2,'13529141046','1999-01-14',2,123,'2026-02-05 14:25:01','门店推荐','','AR21202602050095','银卡客户',7,6,NULL,0.00,NULL,0.00,'肇嘉浜店测试客户1',1,'2026-02-05 14:25:01','2026-02-06 18:17:25',''),(9,2,1,'MC2202602050205','肇嘉浜客户2',1,'13446696135','1995-10-07',2,249,'2026-02-05 14:25:01','线下活动','','AR21202602057121','金卡客户',7,6,NULL,0.00,NULL,0.00,'肇嘉浜店测试客户2',1,'2026-02-05 14:25:01','2026-02-06 18:25:40',''),(10,2,1,'MC2202602050763','肇嘉浜客户3',1,'13170284614','1996-09-22',2,50,'2026-02-05 14:25:01','其他','','AR21202602055913','金卡客户',0,0,NULL,0.00,NULL,0.00,'肇嘉浜店测试客户3',1,'2026-02-05 14:25:01','2026-02-05 16:57:06',''),(11,2,2,'MC2202602059453','肇嘉浜客户4',1,'13919932097','2001-11-16',1,399,'2026-02-05 14:25:01','网络推广','','AR21202602051349','金卡客户',1,5,NULL,0.00,NULL,0.00,'肇嘉浜店测试客户4',1,'2026-02-05 14:25:01','2026-02-06 18:25:49',''),(12,2,1,'MC2202602051465','肇嘉浜客户5',2,'13070147288','1986-01-14',2,890,'2026-02-05 14:25:01','线下活动','','AR21202602056956','金卡客户',0,0,NULL,0.00,NULL,0.00,'肇嘉浜店测试客户5',1,'2026-02-05 14:25:01','2026-02-05 16:57:21','');
/*!40000 ALTER TABLE `cust_customer` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cust_customer_department`
--

DROP TABLE IF EXISTS `cust_customer_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cust_customer_department` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_customer_department` (`customer_id`,`department_id`),
  KEY `department_id` (`department_id`)
) ENGINE=MyISAM AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cust_customer_department`
--

LOCK TABLES `cust_customer_department` WRITE;
/*!40000 ALTER TABLE `cust_customer_department` DISABLE KEYS */;
INSERT INTO `cust_customer_department` VALUES (28,8,1,'2026-02-06 18:25:09','2026-02-06 18:25:09'),(29,8,2,'2026-02-06 18:25:09','2026-02-06 18:25:09'),(38,7,2,'2026-02-12 12:27:31','2026-02-12 12:27:31'),(37,7,1,'2026-02-12 12:27:31','2026-02-12 12:27:31'),(6,3,1,'2026-02-05 16:46:58','2026-02-05 16:46:58'),(7,4,2,'2026-02-05 16:47:06','2026-02-05 16:47:06'),(35,5,1,'2026-02-12 12:00:19','2026-02-12 12:00:19'),(36,5,2,'2026-02-12 12:00:19','2026-02-12 12:00:19'),(32,6,1,'2026-02-12 11:44:32','2026-02-12 11:44:32'),(11,1,2,'2026-02-05 16:47:28','2026-02-05 16:47:28'),(30,9,1,'2026-02-06 18:25:40','2026-02-06 18:25:40'),(15,10,1,'2026-02-05 16:57:06','2026-02-05 16:57:06'),(31,11,2,'2026-02-06 18:25:49','2026-02-06 18:25:49'),(17,12,1,'2026-02-05 16:57:21','2026-02-05 16:57:21'),(18,12,2,'2026-02-05 16:57:21','2026-02-05 16:57:21'),(19,2,2,'2026-02-05 16:57:35','2026-02-05 16:57:35');
/*!40000 ALTER TABLE `cust_customer_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cust_customer_manager`
--

DROP TABLE IF EXISTS `cust_customer_manager`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cust_customer_manager` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `manager_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_manager_id` (`manager_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cust_customer_manager`
--

LOCK TABLES `cust_customer_manager` WRITE;
/*!40000 ALTER TABLE `cust_customer_manager` DISABLE KEYS */;
INSERT INTO `cust_customer_manager` VALUES (1,8,6,'2026-02-06 10:25:09','2026-02-06 10:25:09'),(2,8,5,'2026-02-06 10:25:09','2026-02-06 10:25:09'),(3,9,6,'2026-02-06 10:25:40','2026-02-06 10:25:40'),(4,11,5,'2026-02-06 10:25:49','2026-02-06 10:25:49'),(5,5,5,'2026-02-12 04:00:19','2026-02-12 04:00:19');
/*!40000 ALTER TABLE `cust_customer_manager` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cust_customer_service_staff`
--

DROP TABLE IF EXISTS `cust_customer_service_staff`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cust_customer_service_staff` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `service_staff_id` int(10) unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_service_staff_id` (`service_staff_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cust_customer_service_staff`
--

LOCK TABLES `cust_customer_service_staff` WRITE;
/*!40000 ALTER TABLE `cust_customer_service_staff` DISABLE KEYS */;
INSERT INTO `cust_customer_service_staff` VALUES (1,8,7,'2026-02-06 10:25:09','2026-02-06 10:25:09'),(2,8,1,'2026-02-06 10:25:09','2026-02-06 10:25:09'),(3,9,7,'2026-02-06 10:25:40','2026-02-06 10:25:40'),(4,11,1,'2026-02-06 10:25:49','2026-02-06 10:25:49'),(5,5,1,'2026-02-12 04:00:19','2026-02-12 04:00:19'),(6,7,4,'2026-02-12 04:27:31','2026-02-12 04:27:31');
/*!40000 ALTER TABLE `cust_customer_service_staff` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_company`
--

DROP TABLE IF EXISTS `sys_company`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_company` (
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='公司表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_company`
--

LOCK TABLES `sys_company` WRITE;
/*!40000 ALTER TABLE `sys_company` DISABLE KEYS */;
INSERT INTO `sys_company` VALUES (1,'admin','系统管理公司','彭彭','13900139000','111','美容',20,'鹏鹏',1,'2026-01-30 02:18:41','2026-01-31 09:58:21',0),(2,'fts','馥田诗','汪志','11','11','综合',2,'鹏鹏',1,'2026-01-30 07:06:47','2026-01-31 09:58:29',0);
/*!40000 ALTER TABLE `sys_company` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_department`
--

DROP TABLE IF EXISTS `sys_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_department` (
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='部门表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_department`
--

LOCK TABLES `sys_department` WRITE;
/*!40000 ALTER TABLE `sys_department` DISABLE KEYS */;
INSERT INTO `sys_department` VALUES (1,'美发部',3,1,2,1,1,'2026-01-30 07:52:16','2026-02-02 06:31:59',0),(2,'美容部',3,2,2,1,1,'2026-01-30 07:53:33','2026-02-02 06:32:02',0),(3,'总部',0,0,2,1,0,'2026-01-30 08:12:07','2026-02-06 04:09:02',0),(4,'系统管理',0,0,1,1,0,'2026-01-31 05:40:29','2026-01-31 05:40:29',0),(5,'二房东',0,0,0,1,0,'2026-01-31 10:30:49','2026-01-31 10:30:57',1);
/*!40000 ALTER TABLE `sys_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_employee_position`
--

DROP TABLE IF EXISTS `sys_employee_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_employee_position` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` int(10) unsigned NOT NULL,
  `position_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_position` (`employee_id`,`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='员工职位关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_employee_position`
--

LOCK TABLES `sys_employee_position` WRITE;
/*!40000 ALTER TABLE `sys_employee_position` DISABLE KEYS */;
/*!40000 ALTER TABLE `sys_employee_position` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_menu`
--

DROP TABLE IF EXISTS `sys_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_menu` (
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='菜单表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_menu`
--

LOCK TABLES `sys_menu` WRITE;
/*!40000 ALTER TABLE `sys_menu` DISABLE KEYS */;
INSERT INTO `sys_menu` VALUES (1,'企业管理','/system/enterprise','Layout','/system/enterprise/company',0,'ep:chrome-filled',1,0,NULL,1,1,'2026-01-29 11:24:46','2026-01-29 11:49:01',0),(2,'公司管理','/system/enterprise/company','system/enterprise/company/index.vue',NULL,1,'el:briefcase',1,0,NULL,1,1,'2026-01-29 11:24:46','2026-02-02 11:30:11',0),(3,'门店管理','/system/enterprise/store','system/enterprise/store/index.vue',NULL,1,'ep:shop',2,0,NULL,1,1,'2026-01-29 11:24:46','2026-01-29 11:47:29',0),(4,'部门职位','/system/enterprise/department-position','system/enterprise/department-position/index.vue',NULL,1,'ep:stamp',3,0,NULL,1,0,'2026-01-29 11:24:46','2026-02-01 13:55:47',0),(5,'员工管理','/system/enterprise/employee','system/enterprise/employee/index.vue',NULL,1,'ep:user-filled',4,0,NULL,1,1,'2026-01-29 11:24:46','2026-01-29 11:44:28',0),(6,'角色管理','/system/enterprise/role','system/enterprise/role/index.vue',NULL,12,'ep:lock',5,0,NULL,1,1,'2026-01-29 11:24:46','2026-02-02 08:04:48',0),(7,'菜单管理','/system/systemset/menu','system/systemset/menu/index.vue',NULL,12,'ep:histogram',2,0,NULL,1,1,'2026-01-29 11:24:46','2026-01-31 17:48:35',0),(8,'欢迎页','/welcome','welcome/index.vue',NULL,0,'HomeFilled',0,0,NULL,1,0,'2026-01-29 11:24:46','2026-02-02 11:39:15',1),(12,'系统设置','/system/systemset','Layout','/system/systemset/menu',0,'ep:tools',1,0,NULL,1,1,'2026-01-31 17:19:15','2026-02-02 08:53:23',0),(13,'客户管理','/customer','Layout',NULL,0,'ep:avatar',0,0,NULL,1,1,'2026-02-02 01:47:02','2026-02-02 01:47:39',0),(14,'客户信息','/customer/info','customer/info/index.vue',NULL,13,'ep:info-filled',0,0,NULL,1,1,'2026-02-02 01:49:13','2026-02-03 16:44:47',0),(15,'到店管理','/customer/visit','customer/visit/index',NULL,13,'ep:checked',0,0,NULL,1,1,'2026-02-02 01:51:52','2026-02-03 16:44:50',0),(16,'消费分析','/customer/consumption','customer/consumption/index.vue',NULL,13,'ep:histogram',0,0,NULL,1,1,'2026-02-02 02:00:21','2026-02-03 16:44:54',0),(17,'品项分析','/customer/item','customer/item/index.vue',NULL,13,'ep:switch-filled',0,0,NULL,1,1,'2026-02-02 02:01:53','2026-02-03 16:44:56',0),(18,'卡项管理','/card-item','Layout',NULL,0,'ep:checked',5,0,NULL,1,1,'2026-02-03 14:56:09','2026-02-03 16:53:56',0),(19,'项目管理','/card-item/project','card-item/project/index.vue',NULL,18,'ep:list',1,0,NULL,1,1,'2026-02-03 14:56:09','2026-02-03 16:46:58',0),(20,'主卡管理','/card-item/card','card-item/card/index.vue',NULL,18,'ep:ticket',2,0,NULL,1,1,'2026-02-03 14:56:09','2026-02-03 16:55:05',0),(24,'收银管理','/cashier','Layout',NULL,0,'ep:platform',0,0,NULL,1,1,'2026-02-09 15:21:56','2026-02-09 16:31:34',0),(25,'前台收银','/cashier/front-desk','/cashier/front-desk/index.vue',NULL,24,'ep:shopping-cart-full',0,0,NULL,1,1,'2026-02-09 15:23:33','2026-02-09 16:20:53',0),(26,'预约管理','/cashier/appointment-management','/cashier/appointment-management/index.vue',NULL,24,'ep:calendar',0,0,NULL,1,1,'2026-02-11 02:52:57','2026-02-11 02:54:18',0);
/*!40000 ALTER TABLE `sys_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_permission`
--

DROP TABLE IF EXISTS `sys_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_permission` (
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
) ENGINE=InnoDB AUTO_INCREMENT=75 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='权限表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_permission`
--

LOCK TABLES `sys_permission` WRITE;
/*!40000 ALTER TABLE `sys_permission` DISABLE KEYS */;
INSERT INTO `sys_permission` VALUES (1,'公司管理-查看','company:view',NULL,2,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(2,'公司管理-添加','company:add',NULL,2,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(3,'公司管理-编辑','company:edit',NULL,2,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(4,'公司管理-删除','company:delete',NULL,2,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(5,'门店管理-查看','store:view',NULL,3,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(6,'门店管理-添加','store:add',NULL,3,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(7,'门店管理-编辑','store:edit',NULL,3,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(8,'门店管理-删除','store:delete',NULL,3,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(9,'部门管理-查看','department:view',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(10,'部门管理-添加','department:add',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(11,'部门管理-编辑','department:edit',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(12,'部门管理-删除','department:delete',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(13,'职位管理-查看','position:view',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(14,'职位管理-添加','position:add',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(15,'职位管理-编辑','position:edit',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(16,'职位管理-删除','position:delete',NULL,4,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(17,'员工管理-查看','employee:view',NULL,5,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(18,'员工管理-添加','employee:add',NULL,5,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(19,'员工管理-编辑','employee:edit',NULL,5,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(20,'员工管理-删除','employee:delete',NULL,5,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(21,'角色管理-查看','role:view',NULL,6,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(22,'角色管理-添加','role:add',NULL,6,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(23,'角色管理-编辑','role:edit',NULL,6,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(24,'角色管理-删除','role:delete',NULL,6,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(25,'菜单管理-查看','menu:view',NULL,7,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(26,'菜单管理-添加','menu:add',NULL,7,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(27,'菜单管理-编辑','menu:edit',NULL,7,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(28,'菜单管理-删除','menu:delete',NULL,7,1,'2026-01-29 11:24:46','2026-01-31 13:38:11',2),(29,'菜单管理-权限分配','menu:permission','',7,1,'2026-01-31 13:49:23','2026-01-31 14:52:00',2),(30,'角色管理-权限分配','role:permission',NULL,6,1,'2026-01-31 14:51:41','2026-01-31 14:51:57',2),(31,'菜单管理-权限添加','menu:permission:add',NULL,7,1,'2026-01-31 14:56:13','2026-01-31 15:50:21',2),(32,'菜单管理-权限编辑','menu:permission:edit','',7,1,'2026-01-31 15:03:59','2026-01-31 15:03:59',2),(33,'菜单管理-权限删除','menu:permission:delete','',7,1,'2026-01-31 15:04:46','2026-01-31 15:04:46',2),(34,'超级管理员','role:issuper','',6,0,'2026-01-31 16:00:42','2026-01-31 16:07:38',2),(35,'部门管理-查看','store:department:view','部门查看',3,1,'2026-02-01 13:32:26','2026-02-01 13:34:43',2),(36,'部门管理-新增','store:department:add','',3,1,'2026-02-01 13:32:57','2026-02-01 13:34:39',2),(37,'部门管理-编辑','store:department:edit','',3,1,'2026-02-01 13:33:29','2026-02-01 13:34:34',2),(38,'部门管理-删除','store:department:delete','',3,1,'2026-02-01 13:34:28','2026-02-01 13:34:28',2),(39,'职位管理-查看','store:position:view','',3,1,'2026-02-01 13:42:53','2026-02-01 13:42:53',2),(40,'职位管理-新增','store:position:add','',3,1,'2026-02-01 13:43:22','2026-02-01 13:43:22',2),(41,'职位管理-编辑','store:position:edit','',3,1,'2026-02-01 13:43:39','2026-02-01 13:43:39',2),(42,'职位管理-删除','store:position:delete','',3,1,'2026-02-01 13:44:16','2026-02-01 13:44:16',2),(43,'客户信息-查看','customer:info:view','',14,1,'2026-02-02 09:02:37','2026-02-02 09:02:37',2),(44,'客户信息-新增','customer:info:add','',14,1,'2026-02-02 09:03:23','2026-02-02 09:04:10',2),(45,'客户信息-编辑','customer:info:edit','',14,1,'2026-02-02 09:03:43','2026-02-02 09:04:31',2),(46,'客户信息-删除','customer:info:delete','',14,1,'2026-02-02 09:04:49','2026-02-02 09:04:49',2),(47,'床位管理-查看','store:bedroom:view','',2,1,'2026-02-03 13:29:55','2026-02-03 13:29:55',2),(48,'床位管理-新增','store:bedroom:add','',2,1,'2026-02-03 13:30:13','2026-02-03 13:30:13',2),(49,'床位管理-编辑','store:bedroom:edit','',2,1,'2026-02-03 13:30:31','2026-02-03 13:30:31',2),(50,'床位管理-删除','store:bedroom:delete','',2,1,'2026-02-03 13:30:50','2026-02-03 13:30:50',2),(51,'充值卡-查看','card:recharge:view','',20,1,'2026-02-03 16:34:56','2026-02-12 14:33:16',2),(52,'充值卡-新增','card:recharge:add','',20,1,'2026-02-03 16:35:18','2026-02-12 14:33:22',2),(53,'充值卡-编辑','card:recharge:edit','',20,1,'2026-02-03 16:35:46','2026-02-12 14:33:26',2),(54,'充值卡-删除','card:recharge:delete','',20,1,'2026-02-03 16:36:08','2026-02-12 14:33:31',2),(55,'供应商-查看','project:supplier:view','',19,1,'2026-02-03 17:02:22','2026-02-06 08:53:51',2),(56,'供应商-新增','project:supplier:add','',19,1,'2026-02-03 17:02:42','2026-02-06 08:53:56',2),(57,'供应商-编辑','project:supplier:edit','',19,1,'2026-02-03 17:03:55','2026-02-06 08:54:01',2),(58,'供应商-删除','project:supplier:delete','',19,1,'2026-02-03 17:04:16','2026-02-06 08:54:06',2),(59,'项目分类-查看','project:category:view','',19,1,'2026-02-03 17:20:20','2026-02-03 17:20:20',2),(60,'项目分类-新增','project:category:add','',19,1,'2026-02-03 17:20:36','2026-02-03 17:20:36',2),(61,'项目分类-编辑','project:category:edit','',19,1,'2026-02-03 17:20:51','2026-02-03 17:20:51',2),(62,'项目分类-删除','project:category:delete','',19,1,'2026-02-03 17:21:06','2026-02-03 17:21:06',2),(63,'房间设置-查看','store:room:view','',3,1,'2026-02-05 02:29:56','2026-02-05 02:29:56',2),(64,'房间设置-新增','store:room:add','',3,1,'2026-02-05 02:30:08','2026-02-05 02:30:08',2),(65,'房间设置-编辑','store:room:edit','',3,1,'2026-02-05 02:30:30','2026-02-05 02:30:30',2),(66,'房间设置-删除','store:room:delete','',3,1,'2026-02-05 02:30:45','2026-02-05 02:30:45',2),(67,'项目-查看','project:project:view','',19,1,'2026-02-06 08:54:36','2026-02-06 08:54:36',2),(68,'项目-新增','project:project:add','',19,1,'2026-02-06 08:54:50','2026-02-06 08:54:50',2),(69,'项目-编辑','project:project:edit','',19,1,'2026-02-06 08:55:05','2026-02-06 08:55:05',2),(70,'项目-删除','project:project:delete','',19,1,'2026-02-06 08:55:18','2026-02-06 08:55:18',2),(71,'套餐卡-查看','card:package:view','',20,1,'2026-02-13 02:38:44','2026-02-13 02:38:44',2),(72,'套餐卡-新增','card:package:add','',20,1,'2026-02-13 02:39:03','2026-02-13 02:39:03',2),(73,'套餐卡-编辑','card:package:edit','',20,1,'2026-02-13 02:39:15','2026-02-13 02:39:15',2),(74,'套餐卡-删除','card:package:delete','',20,1,'2026-02-13 02:39:35','2026-02-13 02:39:35',2);
/*!40000 ALTER TABLE `sys_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_position`
--

DROP TABLE IF EXISTS `sys_position`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_position` (
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
) ENGINE=InnoDB AUTO_INCREMENT=67 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='职位表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_position`
--

LOCK TABLES `sys_position` WRITE;
/*!40000 ALTER TABLE `sys_position` DISABLE KEYS */;
INSERT INTO `sys_position` VALUES (1,'美容总经理',2,30,2,'公司最高管理者',1,'2026-01-28 22:57:25','2026-01-30 08:39:00',0),(2,'收银',3,19,2,'协助总经理管理公司',1,'2026-01-28 22:57:25','2026-01-30 08:40:02',0),(3,'美发店长',1,20,2,'管理部门日常事务',1,'2026-01-28 22:57:25','2026-01-30 08:38:46',0),(4,'美容店长',2,31,2,'管理团队日常工作',1,'2026-01-28 22:57:25','2026-01-30 08:39:13',0),(5,'专员',0,0,0,'执行具体工作任务',0,'2026-01-28 22:57:25','2026-02-02 15:41:23',1),(6,'助理',0,0,0,'协助上级完成工作',0,'2026-01-28 22:57:25','2026-02-02 15:41:26',1),(61,'董事长',3,10,2,NULL,1,'2026-01-30 08:19:59','2026-01-30 08:38:36',0),(62,'美容师',2,33,2,NULL,1,'2026-01-30 08:35:50','2026-01-30 08:38:53',0),(63,'顾问老师',2,32,2,NULL,1,'2026-01-30 08:39:49','2026-01-30 08:39:49',0),(64,'财务',3,12,2,NULL,1,'2026-01-30 08:40:38','2026-01-30 08:40:38',0),(65,'运营主管',3,13,2,NULL,1,'2026-01-30 08:41:11','2026-01-30 08:41:11',0),(66,'系统管理',4,0,1,NULL,1,'2026-01-31 05:41:35','2026-01-31 05:41:35',0);
/*!40000 ALTER TABLE `sys_position` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_role`
--

DROP TABLE IF EXISTS `sys_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_role` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_role`
--

LOCK TABLES `sys_role` WRITE;
/*!40000 ALTER TABLE `sys_role` DISABLE KEYS */;
INSERT INTO `sys_role` VALUES (1,'超级管理员',1,1,'拥有所有权限',1,'2026-01-29 11:24:46','2026-02-03 13:37:08'),(2,'管理员',0,2,'拥有大部分权限',1,'2026-01-29 11:24:46','2026-02-03 13:37:09'),(3,'普通用户',0,2,'拥有基础权限',1,'2026-01-29 11:24:46','2026-02-03 13:37:13');
/*!40000 ALTER TABLE `sys_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_role_menu`
--

DROP TABLE IF EXISTS `sys_role_menu`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_role_menu` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `menu_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu` (`role_id`,`menu_id`)
) ENGINE=InnoDB AUTO_INCREMENT=71 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色菜单关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_role_menu`
--

LOCK TABLES `sys_role_menu` WRITE;
/*!40000 ALTER TABLE `sys_role_menu` DISABLE KEYS */;
INSERT INTO `sys_role_menu` VALUES (1,1,1),(2,1,2),(18,1,3),(5,1,5),(6,1,6),(7,1,7),(34,1,12),(49,1,13),(50,1,14),(51,1,15),(52,1,16),(53,1,17),(63,1,18),(64,1,19),(65,1,20),(68,1,24),(69,1,25),(9,2,1),(10,2,2),(11,2,3),(13,2,5),(14,2,6),(19,2,7),(45,2,12),(35,3,1),(48,3,2),(37,3,3),(46,3,5),(47,3,6),(42,3,7),(44,3,12),(54,3,13),(55,3,14),(57,3,18),(58,3,19),(59,3,20),(66,3,24),(67,3,25),(70,3,26);
/*!40000 ALTER TABLE `sys_role_menu` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_role_permission`
--

DROP TABLE IF EXISTS `sys_role_permission`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_role_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`,`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=299 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色权限关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_role_permission`
--

LOCK TABLES `sys_role_permission` WRITE;
/*!40000 ALTER TABLE `sys_role_permission` DISABLE KEYS */;
INSERT INTO `sys_role_permission` VALUES (115,1,1),(116,1,2),(117,1,3),(118,1,4),(119,1,5),(121,1,6),(120,1,7),(122,1,8),(123,1,9),(124,1,10),(125,1,11),(126,1,12),(127,1,13),(128,1,14),(129,1,15),(130,1,16),(131,1,17),(132,1,18),(133,1,19),(134,1,20),(138,1,21),(137,1,22),(136,1,23),(135,1,24),(139,1,25),(140,1,26),(141,1,27),(142,1,28),(143,1,29),(144,1,30),(145,1,31),(146,1,32),(147,1,33),(203,1,35),(204,1,36),(218,1,37),(219,1,38),(220,1,39),(221,1,40),(222,1,41),(223,1,42),(243,1,43),(244,1,44),(245,1,45),(246,1,46),(263,1,47),(264,1,48),(265,1,49),(266,1,50),(275,1,51),(276,1,52),(277,1,53),(278,1,54),(267,1,55),(268,1,56),(269,1,57),(270,1,58),(271,1,59),(272,1,60),(273,1,61),(274,1,62),(289,1,63),(290,1,64),(287,1,65),(288,1,66),(291,1,67),(292,1,68),(293,1,69),(294,1,70),(190,2,21),(191,2,22),(192,2,23),(193,2,24),(195,2,25),(194,2,30),(208,3,1),(224,3,2),(225,3,3),(226,3,4),(214,3,5),(227,3,6),(228,3,7),(229,3,8),(188,3,9),(189,3,13),(217,3,17),(236,3,18),(237,3,19),(238,3,20),(199,3,21),(205,3,22),(198,3,23),(206,3,24),(202,3,25),(209,3,26),(210,3,27),(200,3,28),(211,3,29),(207,3,30),(212,3,31),(213,3,32),(201,3,33),(215,3,35),(230,3,36),(231,3,37),(232,3,38),(216,3,39),(235,3,40),(234,3,41),(233,3,42),(239,3,43),(240,3,44),(241,3,45),(242,3,46),(247,3,47),(248,3,48),(249,3,49),(250,3,50),(258,3,51),(257,3,52),(256,3,53),(255,3,54),(251,3,55),(252,3,56),(253,3,57),(254,3,58),(259,3,59),(260,3,60),(261,3,61),(262,3,62),(279,3,63),(280,3,64),(281,3,65),(282,3,66),(283,3,67),(284,3,68),(285,3,69),(286,3,70),(295,3,71),(296,3,72),(297,3,73),(298,3,74);
/*!40000 ALTER TABLE `sys_role_permission` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_store`
--

DROP TABLE IF EXISTS `sys_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_store` (
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_store`
--

LOCK TABLES `sys_store` WRITE;
/*!40000 ALTER TABLE `sys_store` DISABLE KEYS */;
INSERT INTO `sys_store` VALUES (1,'顺义店','021-11111111','上海市普陀区','综合',2,1,'2026-01-30 10:38:01','2026-01-30 10:40:26',0),(2,'肇嘉浜店','13800138000','上海市徐汇区肇嘉浜路201号','综合',2,1,'2026-01-30 10:42:21','2026-01-30 15:51:01',0),(3,'系统管理','13888888888','111111','综合',1,1,'2026-01-31 05:38:31','2026-01-31 05:38:31',0);
/*!40000 ALTER TABLE `sys_store` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_store_department`
--

DROP TABLE IF EXISTS `sys_store_department`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_store_department` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int(10) unsigned NOT NULL,
  `department_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_department` (`store_id`,`department_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='门店部门关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_store_department`
--

LOCK TABLES `sys_store_department` WRITE;
/*!40000 ALTER TABLE `sys_store_department` DISABLE KEYS */;
INSERT INTO `sys_store_department` VALUES (4,1,1),(5,1,2),(9,2,1),(10,2,2),(14,3,3);
/*!40000 ALTER TABLE `sys_store_department` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user`
--

DROP TABLE IF EXISTS `sys_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_user` (
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user`
--

LOCK TABLES `sys_user` WRITE;
/*!40000 ALTER TABLE `sys_user` DISABLE KEYS */;
INSERT INTO `sys_user` VALUES (1,'admin','$2y$10$S.T/5Z23QFHR5w47N/go1.XxQVCr.oXHRjTzl5FSpT2PkqYYyGE3u','','',1,'2026-01-29 11:24:46','2026-01-30 15:20:15'),(2,'test','$2y$10$X6fnPLs9N0rSL9ld3bAdTOUWF7LCRzQZ/OZR9B5uLESju4hpxGJOm','','',1,'2026-01-30 14:30:39','2026-02-02 15:02:28'),(3,'test1','$2y$10$WkNRuAi17Z8Use2gMVDpQeZ9B.gry7K4Ho4t1YpkWofCDvvPNaO62','','',1,'2026-01-30 14:34:38','2026-01-30 14:34:38'),(4,'test2','$2y$10$xsS.bdvysisnp4A/9D.h2Oa2e7E04kGDr5pSNUzYjSQUpC5ZmTJDi','','',1,'2026-01-30 16:46:31','2026-01-30 16:46:31'),(6,'meifa2','$2y$10$GD4MR.Si39ufWH45WtEr9O4kJuNdyPGkuJqYkt3psuqF63TtrF2Da','','',1,'2026-02-06 10:04:49','2026-02-06 10:04:49'),(7,'meifa1','$2y$10$qv.3TfY09OGZtpnRWIX5WeyU/OgQpzHWPYqYzeOhh3yrq1BPFtE5i','','',1,'2026-02-06 10:10:51','2026-02-06 10:10:51');
/*!40000 ALTER TABLE `sys_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user_employee`
--

DROP TABLE IF EXISTS `sys_user_employee`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_user_employee` (
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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='员工表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user_employee`
--

LOCK TABLES `sys_user_employee` WRITE;
/*!40000 ALTER TABLE `sys_user_employee` DISABLE KEYS */;
INSERT INTO `sys_user_employee` VALUES (1,'test',2,2,2,2,62,5,1,'2026-01-30 10:55:03','2026-02-06 09:54:24',0),(2,'超级管理员',1,1,3,4,66,0,0,'2026-01-30 10:55:06','2026-02-01 01:01:30',0),(4,'test22',3,2,1,2,62,0,1,'2026-01-30 14:34:38','2026-02-05 14:33:44',0),(5,'test的上级',4,2,2,2,63,0,1,'2026-01-30 16:46:31','2026-02-05 06:16:41',0),(6,'美发2',6,2,2,1,3,0,1,'2026-02-06 10:04:49','2026-02-06 10:10:00',0),(7,'美发1',7,2,2,1,3,6,1,'2026-02-06 10:10:51','2026-02-06 10:10:51',0);
/*!40000 ALTER TABLE `sys_user_employee` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user_profile`
--

DROP TABLE IF EXISTS `sys_user_profile`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_user_profile` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `birthday_solar` date DEFAULT NULL,
  `birthday_lunar` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_card` varchar(18) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_contact` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `emergency_phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entry_date` date DEFAULT NULL,
  `leave_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_sys_user_profile_user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user_profile`
--

LOCK TABLES `sys_user_profile` WRITE;
/*!40000 ALTER TABLE `sys_user_profile` DISABLE KEYS */;
INSERT INTO `sys_user_profile` VALUES (1,2,'','',NULL,'','','','','',NULL,NULL,'2026-01-30 14:32:20','2026-01-30 14:32:20'),(2,3,'','',NULL,'','','','','',NULL,NULL,'2026-01-30 14:34:38','2026-01-30 14:34:38'),(3,1,'','',NULL,'','','','','',NULL,NULL,'2026-01-30 15:20:15','2026-01-30 15:20:15'),(4,4,'','',NULL,'','','','','',NULL,NULL,'2026-01-30 16:46:31','2026-01-30 16:46:31'),(5,6,'','',NULL,'','','','','',NULL,NULL,'2026-02-06 10:04:49','2026-02-06 10:04:49'),(6,7,'','',NULL,'','','','','',NULL,NULL,'2026-02-06 10:10:51','2026-02-06 10:10:51');
/*!40000 ALTER TABLE `sys_user_profile` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user_role`
--

DROP TABLE IF EXISTS `sys_user_role`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`,`role_id`),
  KEY `idx_sys_user_role_user_id` (`user_id`),
  KEY `idx_sys_user_role_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户角色关联表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user_role`
--

LOCK TABLES `sys_user_role` WRITE;
/*!40000 ALTER TABLE `sys_user_role` DISABLE KEYS */;
INSERT INTO `sys_user_role` VALUES (21,1,1),(44,2,3),(30,3,2),(25,4,2),(45,6,3),(46,7,3);
/*!40000 ALTER TABLE `sys_user_role` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sys_user_store`
--

DROP TABLE IF EXISTS `sys_user_store`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sys_user_store` (
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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户门店权限表';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sys_user_store`
--

LOCK TABLES `sys_user_store` WRITE;
/*!40000 ALTER TABLE `sys_user_store` DISABLE KEYS */;
INSERT INTO `sys_user_store` VALUES (18,1,3),(61,2,1),(62,2,2),(33,3,1),(34,3,2),(24,4,1),(63,6,2),(64,7,2);
/*!40000 ALTER TABLE `sys_user_store` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-13 17:43:11
