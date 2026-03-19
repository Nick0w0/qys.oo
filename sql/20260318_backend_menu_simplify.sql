-- 2026-03-18 后台菜单精简

-- 1) 只保留当前项目真正需要的后台主菜单
UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` IN (
  'dashboard',
  'general',
  'addon',
  'user',
  'third',
  'wechat',
  'auth/adminlog',
  'auth/group',
  'auth/rule'
);

UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` LIKE 'dashboard/%'
   OR `name` LIKE 'general/%'
   OR `name` LIKE 'addon/%'
   OR `name` LIKE 'user/%'
   OR `name` LIKE 'third/%'
   OR `name` LIKE 'wechat/%'
   OR `name` LIKE 'auth/adminlog/%'
   OR `name` LIKE 'auth/group/%'
   OR `name` LIKE 'auth/rule/%';

-- 2) 权限管理收敛为账号管理
UPDATE `fa_auth_rule`
SET `title` = '账号管理'
WHERE `name` = 'auth';

UPDATE `fa_auth_rule`
SET `title` = '子账号管理'
WHERE `name` = 'auth/admin';

-- 3) 子管理员权限组统一收紧到“只能管理本校帖子”
UPDATE `fa_auth_group`
SET `rules` = '85,153,100,102,105,106,202,203,204,205,107,109,111,112,113'
WHERE `name` IN ('学校管理员', '学校审核员');

-- 4) 拆分发现种草为独立一级菜单
UPDATE `fa_auth_rule`
SET `pid` = 0, `weigh` = 98
WHERE `name` = 'discover/content';

UPDATE `fa_auth_rule`
SET `pid` = 0, `weigh` = 97
WHERE `name` = 'discover/schoolops';

UPDATE `fa_auth_rule`
SET `pid` = 0, `weigh` = 96
WHERE `name` = 'discover/operation';

UPDATE `fa_auth_rule`
SET `status` = 'hidden'
WHERE `name` = 'discover';

-- 5) 内容治理二级菜单补充更直观的图标
UPDATE `fa_auth_rule`
SET `icon` = 'fa fa-comments-o'
WHERE `name` = 'discover/comment';

UPDATE `fa_auth_rule`
SET `icon` = 'fa fa-file-text-o'
WHERE `name` = 'discover/discover';
