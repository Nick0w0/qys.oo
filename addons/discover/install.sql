CREATE TABLE IF NOT EXISTS `__PREFIX__school` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='学校';

CREATE TABLE IF NOT EXISTS `__PREFIX__admin_school` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='管理员学校绑定';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `title` varchar(100) NOT NULL DEFAULT '' COMMENT '标题',
  `description` text COMMENT '描述',
  `coverimage` varchar(1000) DEFAULT '' COMMENT '主图',
  `coverimages` varchar(2000) DEFAULT '' COMMENT '图集',
  `tag_ids` varchar(100) DEFAULT '' COMMENT '关联标签',
  `top_ids` varchar(100) DEFAULT '' COMMENT '关联话题',
  `content` text COMMENT '内容',
  `browse` int(10) DEFAULT '0' COMMENT '浏览量',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `weigh` int(10) DEFAULT '0' COMMENT '排序',
  `statusdata` enum('1','2','3') DEFAULT '1' COMMENT '状态:1=正常,2=隐藏,3=系统下架',
  `province` varchar(100) DEFAULT '' COMMENT '所属省',
  `city` varchar(200) DEFAULT '' COMMENT '所属市',
  `area` varchar(50) DEFAULT '' COMMENT '所属区',
  `address` varchar(255) DEFAULT '' COMMENT '具体地址',
  `latlng` varchar(100) DEFAULT '' COMMENT '经纬度',
  `adddata` enum('1','2') DEFAULT '1' COMMENT '是否显示地址:1=开启,2=关闭',
  `iscommentdata` enum('1','2') DEFAULT '1' COMMENT '是否开启评论:1=开启,2=关闭',
  `user_id` int(10) DEFAULT '0' COMMENT '发布人',
  `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属学校',
  `favorNum` int(10) DEFAULT '0' COMMENT '点赞数量',
  `commentNum` int(10) DEFAULT '0' COMMENT '评论数',
  `audit_status` enum('pending','approved','rejected') NOT NULL DEFAULT 'pending' COMMENT '审核状态',
  `audit_admin_id` int(10) NOT NULL DEFAULT '0' COMMENT '审核管理员',
  `audit_time` bigint(16) DEFAULT NULL COMMENT '审核时间',
  `is_top` enum('0','1') NOT NULL DEFAULT '0' COMMENT '是否置顶',
  `top_sort` int(10) NOT NULL DEFAULT '0' COMMENT '置顶排序',
  PRIMARY KEY (`id`) USING BTREE,
  KEY `idx_school_audit_create` (`school_id`,`audit_status`,`createtime`),
  KEY `idx_school_top` (`school_id`,`is_top`,`top_sort`)
) ENGINE=InnoDB AUTO_INCREMENT=263 DEFAULT CHARSET=utf8 COMMENT='发现-动态';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_attentions` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '关注者id',
  `attention_id` int(10) NOT NULL DEFAULT '0' COMMENT '被关注者id',
  `discover_id` int(10) NOT NULL DEFAULT '0' COMMENT '通过作品关联id',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=230 DEFAULT CHARSET=utf8 COMMENT='发现-关注';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_collect` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `discover_id` int(10) NOT NULL DEFAULT '0' COMMENT '作品id',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '用户id',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='发现-收藏';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_comment` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `discover_id` int(10) NOT NULL DEFAULT '0' COMMENT '关联动态ID',
  `content` varchar(255) NOT NULL DEFAULT '' COMMENT '内容',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '第一评论人id',
  `comment_id` int(10) NOT NULL DEFAULT '0' COMMENT '评论id',
  `reply_id` int(10) NOT NULL DEFAULT '0' COMMENT '回复人id',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `statusdata` enum('1','2') NOT NULL DEFAULT '1' COMMENT '状态:1=显示,2=不显示',
  `parent_id` int(10) DEFAULT NULL COMMENT '最顶层的评论id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=666 DEFAULT CHARSET=utf8 COMMENT='发现-评论';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_favor` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `typedata` enum('1','2') NOT NULL DEFAULT '1' COMMENT '类型:1=作品点赞,2=评论点赞',
  `discover_id` int(10) NOT NULL DEFAULT '0' COMMENT '作品id',
  `comment_id` int(10) NOT NULL DEFAULT '0' COMMENT '评论id',
  `user_id` int(10) NOT NULL DEFAULT '0' COMMENT '点赞人id',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=480 DEFAULT CHARSET=utf8 COMMENT='发现-点赞';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_log` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `typedata` enum('1','2','3','4','5','6') NOT NULL DEFAULT '1' COMMENT '消息类型:1=作品被点赞,2=评论被点赞,3=作品被评论,4=被关注,5=评论被回复,6=备用',
  `user_id` int(10) DEFAULT '0' COMMENT '提醒用户',
  `create_id` int(10) DEFAULT NULL COMMENT '创建人Id',
  `content` varchar(100) DEFAULT NULL COMMENT '提醒内容',
  `remind` text COMMENT '提醒id（关注，点赞，评论，json类型）',
  `readdata` enum('0','1') DEFAULT '0' COMMENT '是否已读:0=未读,1=已读',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `updatetime` bigint(16) DEFAULT NULL COMMENT '更新时间',
  `discover_id` int(10) DEFAULT NULL COMMENT '动态id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1384 DEFAULT CHARSET=utf8 COMMENT='发现-消息';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_tag` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(20) NOT NULL DEFAULT '' COMMENT '标签名称',
  `number` int(10) NOT NULL DEFAULT '0' COMMENT '标签使用次数',
  `ishotdata` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '是否热门标签:0=普通,1=热门,2=禁用',
  `typedata` enum('0','1') NOT NULL DEFAULT '0' COMMENT '添加类型:0=系统添加,1=用户添加',
  `auditdata` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '审核状态:0=通过（系统添加自动通过）,1=审核中,2=审核失败',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='发现-标签';

CREATE TABLE IF NOT EXISTS `__PREFIX__discover_topic` (
  `id` int(10) NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `name` varchar(100) NOT NULL DEFAULT '' COMMENT '话题名称',
  `statusdata` enum('1','2') NOT NULL DEFAULT '1' COMMENT '话题状态:1=使用中,2=禁用',
  `createtime` bigint(16) DEFAULT NULL COMMENT '创建时间',
  `weigh` int(10) NOT NULL DEFAULT '0' COMMENT '排序',
  `ishotdata` enum('0','1') NOT NULL DEFAULT '1' COMMENT '是否热门:0=普通,1=热门',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='发现-话题';

INSERT INTO `__PREFIX__discover_tag` (`id`, `name`, `number`, `ishotdata`, `typedata`, `auditdata`, `createtime`, `weigh`) VALUES
(1, '美食 ','0','1 ','0','0','1623986220','1'),(2, '旅行','0','1 ','0','0','1623986220','2'),(3, '生活','0','1 ','0','0','1623986220','3'),(4, '情感','0','1 ','0','0','1623986220','4'),(5, 'Vlog','0','1 ','0','0','1623986220','5'),(6, '潮范儿','0','1 ','0','0','1623986220','6');
