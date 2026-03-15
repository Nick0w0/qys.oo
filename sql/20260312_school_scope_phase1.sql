-- 2026-03-12 第一阶段：学校/子管理员学校绑定/帖子学校权限

CREATE TABLE IF NOT EXISTS `fa_school` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '学校名称',
  `short_name` varchar(50) NOT NULL DEFAULT '' COMMENT '学校简称',
  `province` varchar(100) NOT NULL DEFAULT '' COMMENT '省份',
  `city` varchar(100) NOT NULL DEFAULT '' COMMENT '城市',
  `area` varchar(100) NOT NULL DEFAULT '' COMMENT '区域',
  `address` varchar(255) NOT NULL DEFAULT '' COMMENT '详细地址',
  `latitude` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '纬度',
  `longitude` decimal(10,6) NOT NULL DEFAULT '0.000000' COMMENT '经度',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_city_status` (`city`,`status`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学校';

CREATE TABLE IF NOT EXISTS `fa_admin_school` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '管理员ID',
  `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '学校ID',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `is_primary` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否主绑定',
  `created_by` int(10) NOT NULL DEFAULT '0' COMMENT '创建人',
  `updated_by` int(10) NOT NULL DEFAULT '0' COMMENT '更新人',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`) USING BTREE,
  UNIQUE KEY `uniq_admin_school` (`admin_id`),
  KEY `idx_school_admin` (`school_id`,`admin_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='管理员学校绑定';

ALTER TABLE `fa_discover`
  ADD COLUMN `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属学校' AFTER `user_id`,
  ADD COLUMN `audit_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT '审核状态' AFTER `commentNum`,
  ADD COLUMN `audit_admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '审核管理员' AFTER `audit_status`,
  ADD COLUMN `audit_time` bigint(16) DEFAULT NULL COMMENT '审核时间' AFTER `audit_admin_id`,
  ADD COLUMN `is_top` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否置顶' AFTER `audit_time`,
  ADD COLUMN `top_sort` int(10) NOT NULL DEFAULT '0' COMMENT '置顶排序' AFTER `is_top`;

ALTER TABLE `fa_discover`
  ADD KEY `idx_school_audit_create` (`school_id`,`audit_status`,`createtime`),
  ADD KEY `idx_school_top` (`school_id`,`is_top`,`top_sort`);
