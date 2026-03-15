-- 2026-03-12 第二阶段：用户单学校绑定与锁定
ALTER TABLE `fa_user`
  ADD COLUMN `school_id` int(10) NOT NULL DEFAULT '0' COMMENT '所属学校' AFTER `group_id`,
  ADD COLUMN `school_confirm_time` bigint(16) DEFAULT NULL COMMENT '学校确认时间' AFTER `school_id`,
  ADD COLUMN `school_locked` enum('0','1') NOT NULL DEFAULT '0' COMMENT '学校是否锁定:0=未锁定,1=已锁定' AFTER `school_confirm_time`;

ALTER TABLE `fa_user`
  ADD KEY `idx_school_user` (`school_id`,`id`);
