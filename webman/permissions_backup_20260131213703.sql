-- 权限相关表备份 2026-01-31 21:37:03

-- 表结构: sys_permission
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='权限表';

-- 表数据: sys_permission
INSERT INTO sys_permission (id, name, code, description, menu_id, status, created_at, updated_at, type) VALUES
(1, ''公司管理-查看'', ''btn:/system/enterprise/company:view'', NULL, 2, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(2, ''公司管理-添加'', ''btn:/system/enterprise/company:add'', NULL, 2, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(3, ''公司管理-编辑'', ''btn:/system/enterprise/company:edit'', NULL, 2, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(4, ''公司管理-删除'', ''btn:/system/enterprise/company:delete'', NULL, 2, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(5, ''门店管理-查看'', ''btn:/system/enterprise/store:view'', NULL, 3, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(6, ''门店管理-添加'', ''btn:/system/enterprise/store:add'', NULL, 3, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(7, ''门店管理-编辑'', ''btn:/system/enterprise/store:edit'', NULL, 3, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:49'', 2),
(8, ''门店管理-删除'', ''btn:/system/enterprise/store:delete'', NULL, 3, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:33:50'', 2),
(9, ''部门管理-查看'', ''btn:/system/enterprise/dept-position/department:view'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(10, ''部门管理-添加'', ''btn:/system/enterprise/dept-position/department:add'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(11, ''部门管理-编辑'', ''btn:/system/enterprise/dept-position/department:edit'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(12, ''部门管理-删除'', ''btn:/system/enterprise/dept-position/department:delete'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(13, ''职位管理-查看'', ''btn:/system/enterprise/dept-position/position:view'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(14, ''职位管理-添加'', ''btn:/system/enterprise/dept-position/position:add'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(15, ''职位管理-编辑'', ''btn:/system/enterprise/dept-position/position:edit'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(16, ''职位管理-删除'', ''btn:/system/enterprise/dept-position/position:delete'', NULL, 4, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:46:18'', 2),
(17, ''员工管理-查看'', ''btn:/system/enterprise/employee:view'', NULL, 5, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(18, ''员工管理-添加'', ''btn:/system/enterprise/employee:add'', NULL, 5, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(19, ''员工管理-编辑'', ''btn:/system/enterprise/employee:edit'', NULL, 5, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(20, ''员工管理-删除'', ''btn:/system/enterprise/employee:delete'', NULL, 5, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(21, ''角色管理-查看'', ''btn:/system/enterprise/role:view'', NULL, 6, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(22, ''角色管理-添加'', ''btn:/system/enterprise/role:add'', NULL, 6, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(23, ''角色管理-编辑'', ''btn:/system/enterprise/role:edit'', NULL, 6, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(24, ''角色管理-删除'', ''btn:/system/enterprise/role:delete'', NULL, 6, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(25, ''菜单管理-查看'', ''btn:/system/menu:view'', NULL, 7, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(26, ''菜单管理-添加'', ''btn:/system/menu:add'', NULL, 7, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(27, ''菜单管理-编辑'', ''btn:/system/menu:edit'', NULL, 7, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2),
(28, ''菜单管理-删除'', ''btn:/system/menu:delete'', NULL, 7, 1, ''2026-01-29 19:24:46'', ''2026-01-31 14:37:25'', 2);

-- 表结构: sys_role_permission
CREATE TABLE `sys_role_permission` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int(10) unsigned NOT NULL,
  `permission_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `role_permission` (`role_id`,`permission_id`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='角色权限关联表';

-- 表数据: sys_role_permission
INSERT INTO sys_role_permission (id, role_id, permission_id) VALUES
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
(114, 3, 1);

-- 表结构: sys_user_role
CREATE TABLE `sys_user_role` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_role` (`user_id`,`role_id`),
  KEY `idx_sys_user_role_user_id` (`user_id`),
  KEY `idx_sys_user_role_role_id` (`role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci COMMENT='用户角色关联表';

-- 表数据: sys_user_role
INSERT INTO sys_user_role (id, user_id, role_id) VALUES
(10, 1, 1),
(11, 1, 2),
(12, 1, 3),
(13, 2, 3),
(8, 3, 1),
(9, 4, 2);

