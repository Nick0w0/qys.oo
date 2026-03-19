-- 2026-03-18 第六阶段：帖子举报能力

CREATE TABLE IF NOT EXISTS `fa_discover_report` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `discover_id` int(10) NOT NULL DEFAULT '0' COMMENT '动态ID',
  `discover_user_id` int(10) NOT NULL DEFAULT '0' COMMENT '动态作者ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '举报人ID',
  `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属学校',
  `reason` varchar(100) NOT NULL DEFAULT '' COMMENT '举报原因',
  `status` enum('pending','handled','rejected') NOT NULL DEFAULT 'pending' COMMENT '处理状态',
  `handled_admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '处理管理员ID',
  `handled_time` bigint(16) DEFAULT NULL COMMENT '处理时间',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_user_discover` (`user_id`,`discover_id`),
  KEY `idx_discover_status` (`discover_id`,`status`),
  KEY `idx_school_status` (`school_id`,`status`),
  KEY `idx_user_create` (`user_id`,`createtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='帖子举报';

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'menu', parent.`id`, 'discover/report', '举报管理', 'fa fa-exclamation-triangle', 'discover/report', '', '', 1, 'addtabs', '', 'jbgl', 'jubiaoguanli', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 35, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/content'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/report');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/report/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/report'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/report/index');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/report/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/report'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/report/del');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/report/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/report'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/report/multi');
