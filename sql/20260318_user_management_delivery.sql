-- 2026-03-18 用户管理交付版菜单与权限调整

UPDATE `fa_auth_rule`
SET `title` = '管理员账号'
WHERE `name` = 'auth';

UPDATE `fa_auth_rule`
SET `title` = '后台账号'
WHERE `name` = 'auth/admin';

UPDATE `fa_auth_rule`
SET `ismenu` = 0,
    `title` = '用户权限'
WHERE `name` = 'user';

UPDATE `fa_auth_rule`
SET `pid` = 154,
    `title` = '用户管理',
    `weigh` = 90
WHERE `name` = 'user/user';

UPDATE `fa_auth_rule`
SET `weigh` = 100
WHERE `name` = 'discover/school';

UPDATE `fa_auth_group`
SET `rules` = '85,153,154,67,68,69,72,107,109,111,112,113'
WHERE `name` IN ('学校管理员', '学校审核员');
