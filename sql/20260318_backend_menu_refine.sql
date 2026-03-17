-- 2026-03-18 后台菜单二次收敛
-- 目标结构：
-- 内容治理 / 用户管理 / 学校运营 / 运营中心 / 账号管理

-- 1) 内容中心改名为内容治理
UPDATE `fa_auth_rule`
SET `title` = '内容治理'
WHERE `name` = 'discover/content';

-- 2) 内容治理只保留核心治理项
UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` IN ('discover/tag', 'discover/topic');

UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` LIKE 'discover/tag/%'
   OR `name` LIKE 'discover/topic/%';

-- 3) 恢复轻量用户管理，只保留用户列表
UPDATE `fa_auth_rule`
SET `title` = '用户管理', `status` = 'normal'
WHERE `name` = 'user';

UPDATE `fa_auth_rule`
SET `title` = '用户列表', `status` = 'normal'
WHERE `name` = 'user/user';

UPDATE `fa_auth_rule`
SET `status` = 'normal'
WHERE `name` LIKE 'user/user/%';

UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` IN ('user/group', 'user/rule');

UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` LIKE 'user/group/%'
   OR `name` LIKE 'user/rule/%';
