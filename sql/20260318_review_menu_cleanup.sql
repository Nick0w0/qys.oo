-- 2026-03-18 屏蔽词菜单与列表展示收尾

UPDATE `fa_auth_rule`
SET `title` = '屏蔽词管理'
WHERE `name` = 'discover/review';
