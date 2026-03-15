CREATE TABLE IF NOT EXISTS `fa_school_group_qrcode` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '学校ID，0=平台默认',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `description` varchar(255) NOT NULL DEFAULT '' COMMENT '描述',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '二维码图片',
  `popup_strategy` enum('always','daily','interval') NOT NULL DEFAULT 'daily' COMMENT '弹窗策略',
  `popup_interval` int(10) unsigned NOT NULL DEFAULT '1' COMMENT '间隔天数',
  `status` enum('normal','hidden') NOT NULL DEFAULT 'normal' COMMENT '状态',
  `starttime` bigint(16) NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` bigint(16) NOT NULL DEFAULT '0' COMMENT '结束时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_school_status` (`school_id`,`status`),
  KEY `idx_time` (`starttime`,`endtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='学校群二维码配置';
