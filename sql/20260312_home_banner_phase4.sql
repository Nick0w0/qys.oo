CREATE TABLE IF NOT EXISTS `fa_home_banner` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `school_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '所属学校:0=平台默认',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '广告标题',
  `image` varchar(255) NOT NULL DEFAULT '' COMMENT '广告图片',
  `jump_type` varchar(20) NOT NULL DEFAULT 'none' COMMENT '跳转类型:none/path/discover',
  `jump_value` varchar(255) NOT NULL DEFAULT '' COMMENT '跳转值',
  `status` varchar(20) NOT NULL DEFAULT 'normal' COMMENT '状态',
  `starttime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '开始时间',
  `endtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '结束时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `clicks` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '点击量',
  `views` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '曝光量',
  `createtime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `updatetime` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_school_status` (`school_id`,`status`),
  KEY `idx_time_status` (`status`,`starttime`,`endtime`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='首页广告位';