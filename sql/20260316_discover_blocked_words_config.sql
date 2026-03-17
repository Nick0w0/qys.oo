-- 2026-03-16 内容中心屏蔽词管理正式版

CREATE TABLE IF NOT EXISTS `fa_discover_blocked_word` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `word` varchar(100) NOT NULL DEFAULT '' COMMENT '屏蔽词',
  `remark` varchar(255) NOT NULL DEFAULT '' COMMENT '备注',
  `status` varchar(30) NOT NULL DEFAULT 'normal' COMMENT '状态',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '权重',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_word` (`word`),
  KEY `idx_status_weigh` (`status`,`weigh`,`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='内容中心屏蔽词';

INSERT INTO `fa_config` (`name`, `group`, `title`, `tip`, `type`, `visible`, `value`, `content`, `rule`, `extend`, `setting`)
SELECT 'discover_blocked_words', 'discover', 'Discover blocked words', '旧版屏蔽词配置兼容项，已迁移到内容中心屏蔽词管理页维护', 'text', '', '', '', '', '', ''
FROM DUAL
WHERE NOT EXISTS (
    SELECT 1 FROM `fa_config` WHERE `name` = 'discover_blocked_words'
);

UPDATE `fa_auth_rule`
SET `title` = '屏蔽词管理'
WHERE `name` = 'discover/review';

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/index', 'View', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/index');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/add', 'Add', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/add');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/edit', 'Edit', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/edit');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/del', 'Del', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/del');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/multi', 'Multi', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/multi');

INSERT INTO `fa_auth_rule` (`type`,`pid`,`name`,`title`,`icon`,`url`,`condition`,`remark`,`ismenu`,`menutype`,`extend`,`py`,`pinyin`,`createtime`,`updatetime`,`weigh`,`status`)
SELECT 'file', parent.`id`, 'discover/review/import', 'Import', 'fa fa-circle-o', '', '', '', 0, NULL, '', '', '', UNIX_TIMESTAMP(), UNIX_TIMESTAMP(), 0, 'normal'
FROM `fa_auth_rule` parent
WHERE parent.`name` = 'discover/review'
  AND NOT EXISTS (SELECT 1 FROM `fa_auth_rule` WHERE `name` = 'discover/review/import');
