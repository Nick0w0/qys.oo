-- 2026-03-16 第五阶段：学校主题头部配置

ALTER TABLE `fa_school`
  ADD COLUMN `logo` varchar(255) NOT NULL DEFAULT '' COMMENT '学校图标' AFTER `short_name`,
  ADD COLUMN `header_bg_image` varchar(255) NOT NULL DEFAULT '' COMMENT '头部背景图' AFTER `logo`,
  ADD COLUMN `theme_primary` varchar(20) NOT NULL DEFAULT '#6A5AF9' COMMENT '主题主色' AFTER `header_bg_image`,
  ADD COLUMN `theme_secondary` varchar(20) NOT NULL DEFAULT '#8F6BFF' COMMENT '主题辅色' AFTER `theme_primary`,
  ADD COLUMN `theme_text_color` varchar(20) NOT NULL DEFAULT '#FFFFFF' COMMENT '主题文字色' AFTER `theme_secondary`;