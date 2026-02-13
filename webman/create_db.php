<?php

use Illuminate\Database\Capsule\Manager as Capsule;

require_once __DIR__ . '/vendor/autoload.php';

// 先创建数据库
$pdo = new PDO('mysql:host=127.0.0.1;port=3306', 'root', '123456');
$pdo->exec('CREATE DATABASE IF NOT EXISTS mydream CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci');

// 初始化数据库连接
$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => '127.0.0.1',
    'port'      => '3306',
    'database'  => 'mydream',
    'username'  => 'root',
    'password'  => '123456',
    'charset'   => 'utf8mb4',
    'collation' => 'utf8mb4_general_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

// 获取数据库连接
$connection = $capsule->getConnection();

// 创建表结构

// 用户表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_user` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '用户ID',
  `username` varchar(50) NOT NULL COMMENT '用户名',
  `password` varchar(255) NOT NULL COMMENT '密码',
  `nickname` varchar(50) NOT NULL COMMENT '昵称',
  `avatar` varchar(255) DEFAULT NULL COMMENT '头像',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户表'");

// 角色表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '角色ID',
  `name` varchar(50) NOT NULL COMMENT '角色名称',
  `description` varchar(255) DEFAULT NULL COMMENT '角色描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色表'");

// 菜单表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '菜单ID',
  `name` varchar(50) NOT NULL COMMENT '菜单名称',
  `path` varchar(100) NOT NULL COMMENT '菜单路径',
  `component` varchar(255) DEFAULT NULL COMMENT '组件路径',
  `redirect` varchar(100) DEFAULT NULL COMMENT '重定向路径',
  `parent_id` int unsigned DEFAULT 0 COMMENT '父菜单ID',
  `icon` varchar(50) DEFAULT NULL COMMENT '菜单图标',
  `menu_rank` int DEFAULT 0 COMMENT '菜单排序',
  `is_frame` tinyint NOT NULL DEFAULT 0 COMMENT '是否为外部链接',
  `frame_src` varchar(255) DEFAULT NULL COMMENT '外部链接地址',
  `show_link` tinyint NOT NULL DEFAULT 1 COMMENT '是否显示链接',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='菜单表'");

// 权限表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_permission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '权限ID',
  `name` varchar(50) NOT NULL COMMENT '权限名称',
  `code` varchar(50) NOT NULL COMMENT '权限编码',
  `description` varchar(255) DEFAULT NULL COMMENT '权限描述',
  `menu_id` int unsigned DEFAULT NULL COMMENT '关联菜单ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `menu_id` (`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='权限表'");

// 角色菜单关联表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_role_menu` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `role_id` int unsigned NOT NULL COMMENT '角色ID',
  `menu_id` int unsigned NOT NULL COMMENT '菜单ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_menu` (`role_id`,`menu_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色菜单关联表'");

// 角色权限关联表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_role_permission` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `role_id` int unsigned NOT NULL COMMENT '角色ID',
  `permission_id` int unsigned NOT NULL COMMENT '权限ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`,`permission_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='角色权限关联表'");

// 用户角色关联表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_user_role` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `user_id` int unsigned NOT NULL COMMENT '用户ID',
  `role_id` int unsigned NOT NULL COMMENT '角色ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`,`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='用户角色关联表'");

// 公司表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_company` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '公司ID',
  `name` varchar(100) NOT NULL COMMENT '公司名称',
  `boss` varchar(50) NOT NULL COMMENT '老板',
  `phone` varchar(20) NOT NULL COMMENT '电话',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `enterprise_type` enum('美容','美发','综合','养生') NOT NULL COMMENT '企业类型',
  `store_count` int NOT NULL DEFAULT 0 COMMENT '门店数量',
  `service_people` int NOT NULL DEFAULT 0 COMMENT '服务人',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='公司表'");

// 门店表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_store` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '门店ID',
  `name` varchar(100) NOT NULL COMMENT '门店名称',
  `phone` varchar(20) NOT NULL COMMENT '电话',
  `address` varchar(255) NOT NULL COMMENT '地址',
  `store_type` enum('美容','美发','综合','美容美甲','养生') NOT NULL COMMENT '门店类型',
  `company_id` int unsigned NOT NULL COMMENT '所属公司ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店表'");

// 部门表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_department` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '部门ID',
  `name` varchar(50) NOT NULL COMMENT '部门名称',
  `parent_id` int unsigned DEFAULT 0 COMMENT '上级部门ID',
  `company_id` int unsigned NOT NULL COMMENT '所属公司ID',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `parent_id` (`parent_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='部门表'");

// 职位表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_position` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '职位ID',
  `name` varchar(50) NOT NULL COMMENT '职位名称',
  `company_id` int unsigned DEFAULT NULL COMMENT '所属公司ID',
  `description` varchar(255) DEFAULT NULL COMMENT '职位描述',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='职位表'");

// 员工表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_employee` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '员工ID',
  `name` varchar(50) NOT NULL COMMENT '员工姓名',
  `company_id` int unsigned NOT NULL COMMENT '所属公司ID',
  `department_id` int unsigned NOT NULL COMMENT '所属部门ID',
  `superior_id` int unsigned DEFAULT NULL COMMENT '上级员工ID',
  `phone` varchar(20) DEFAULT NULL COMMENT '联系电话',
  `email` varchar(100) DEFAULT NULL COMMENT '电子邮箱',
  `status` tinyint NOT NULL DEFAULT 1 COMMENT '状态',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '创建时间',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `company_id` (`company_id`),
  KEY `department_id` (`department_id`),
  KEY `superior_id` (`superior_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工表'");

// 门店部门关联表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_store_department` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `store_id` int unsigned NOT NULL COMMENT '门店ID',
  `department_id` int unsigned NOT NULL COMMENT '部门ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_department` (`store_id`,`department_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='门店部门关联表'");

// 员工职位关联表
$connection->statement("CREATE TABLE IF NOT EXISTS `sys_employee_position` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT '关联ID',
  `employee_id` int unsigned NOT NULL COMMENT '员工ID',
  `position_id` int unsigned NOT NULL COMMENT '职位ID',
  PRIMARY KEY (`id`),
  UNIQUE KEY `employee_position` (`employee_id`,`position_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='员工职位关联表'");

// 员工角色关联表已移除，使用 sys_user_role 表代替

// 插入默认数据

// 清空表数据，确保重新创建所有关联关系
$connection->statement("TRUNCATE TABLE `sys_user_role`");
$connection->statement("TRUNCATE TABLE `sys_role_permission`");
$connection->statement("TRUNCATE TABLE `sys_role_menu`");
$connection->statement("TRUNCATE TABLE `sys_permission`");
$connection->statement("TRUNCATE TABLE `sys_menu`");
$connection->statement("TRUNCATE TABLE `sys_role`");
$connection->statement("TRUNCATE TABLE `sys_user`");

// 默认角色
$connection->statement("INSERT INTO `sys_role` (`name`, `description`) VALUES
('超级管理员', '拥有所有权限'),
('管理员', '拥有大部分权限'),
('普通用户', '拥有基础权限')");

// 默认菜单
$connection->statement("INSERT INTO `sys_menu` (`name`, `path`, `component`, `redirect`, `parent_id`, `icon`, `menu_rank`, `show_link`) VALUES
('企业管理', '/system/enterprise', NULL, '/system/enterprise/company', 0, 'OfficeBuilding', 1, 1),
('公司管理', '/system/enterprise/company', 'system/enterprise/company/index.vue', NULL, 1, NULL, 1, 1),
('门店管理', '/system/enterprise/store', 'system/enterprise/store/index.vue', NULL, 1, NULL, 2, 1),
('部门职位', '/system/enterprise/department-position', 'system/enterprise/department-position/index.vue', NULL, 1, NULL, 3, 1),
('员工管理', '/system/enterprise/employee', 'system/enterprise/employee/index.vue', NULL, 1, NULL, 4, 1),
('角色管理', '/system/enterprise/role', 'system/enterprise/role/index.vue', NULL, 1, NULL, 5, 1),
('菜单管理', '/system/menu', 'system/menu/index.vue', NULL, 0, 'Setting', 2, 1)");

// 默认权限
$connection->statement("INSERT INTO `sys_permission` (`name`, `code`, `menu_id`) VALUES
('公司管理-查看', 'company:view', 2),
('公司管理-添加', 'company:add', 2),
('公司管理-编辑', 'company:edit', 2),
('公司管理-删除', 'company:delete', 2),
('门店管理-查看', 'store:view', 3),
('门店管理-添加', 'store:add', 3),
('门店管理-编辑', 'store:edit', 3),
('门店管理-删除', 'store:delete', 3),
('部门管理-查看', 'department:view', 4),
('部门管理-添加', 'department:add', 4),
('部门管理-编辑', 'department:edit', 4),
('部门管理-删除', 'department:delete', 4),
('职位管理-查看', 'position:view', 4),
('职位管理-添加', 'position:add', 4),
('职位管理-编辑', 'position:edit', 4),
('职位管理-删除', 'position:delete', 4),
('员工管理-查看', 'employee:view', 5),
('员工管理-添加', 'employee:add', 5),
('员工管理-编辑', 'employee:edit', 5),
('员工管理-删除', 'employee:delete', 5),
('角色管理-查看', 'role:view', 6),
('角色管理-添加', 'role:add', 6),
('角色管理-编辑', 'role:edit', 6),
('角色管理-删除', 'role:delete', 6),
('菜单管理-查看', 'menu:view', 7),
('菜单管理-添加', 'menu:add', 7),
('菜单管理-编辑', 'menu:edit', 7),
('菜单管理-删除', 'menu:delete', 7)");

// 默认角色菜单关联
$connection->statement("INSERT INTO `sys_role_menu` (`role_id`, `menu_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6),
(3, 1)");

// 默认角色权限关联
$connection->statement("INSERT INTO `sys_role_permission` (`role_id`, `permission_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5), (1, 6), (1, 7), (1, 8),
(1, 9), (1, 10), (1, 11), (1, 12), (1, 13), (1, 14), (1, 15), (1, 16),
(1, 17), (1, 18), (1, 19), (1, 20), (1, 21), (1, 22), (1, 23), (1, 24),
(1, 25), (1, 26), (1, 27), (1, 28),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5), (2, 6), (2, 7), (2, 8),
(2, 9), (2, 10), (2, 11), (2, 12), (2, 13), (2, 14), (2, 15), (2, 16),
(2, 17), (2, 18), (2, 19), (2, 20)");

// 默认用户（密码：admin123）
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);
$connection->statement("INSERT INTO `sys_user` (`username`, `password`, `nickname`) VALUES
('admin', '$password_hash', '超级管理员')");

// 默认用户角色关联
$connection->statement("INSERT INTO `sys_user_role` (`user_id`, `role_id`) VALUES
(1, 1)");

// 默认公共职位
$connection->statement("INSERT INTO `sys_position` (`name`, `description`) VALUES
('总经理', '公司最高管理者'),
('副总经理', '协助总经理管理公司'),
('部门经理', '管理部门日常事务'),
('主管', '管理团队日常工作'),
('专员', '执行具体工作任务'),
('助理', '协助上级完成工作')");

echo "数据库表结构和默认数据创建成功！\n";