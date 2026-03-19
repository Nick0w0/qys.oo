-- 2026-03-18 举报管理权限补齐

UPDATE `fa_auth_rule`
SET `status` = 'normal', `weigh` = 34
WHERE `name` = 'discover/report';

UPDATE `fa_auth_rule`
SET `status` = 'normal'
WHERE `name` IN ('discover/report/index', 'discover/report/del', 'discover/report/multi');

SET @rule_report = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report' LIMIT 1);
SET @rule_report_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/index' LIMIT 1);
SET @rule_report_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/del' LIMIT 1);
SET @rule_report_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/multi' LIMIT 1);

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report))
WHERE `name` IN ('学校管理员', '学校审核员')
  AND @rule_report IS NOT NULL
  AND FIND_IN_SET(@rule_report, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_index))
WHERE `name` IN ('学校管理员', '学校审核员')
  AND @rule_report_index IS NOT NULL
  AND FIND_IN_SET(@rule_report_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_del))
WHERE `name` IN ('学校管理员', '学校审核员')
  AND @rule_report_del IS NOT NULL
  AND FIND_IN_SET(@rule_report_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_multi))
WHERE `name` IN ('学校管理员', '学校审核员')
  AND @rule_report_multi IS NOT NULL
  AND FIND_IN_SET(@rule_report_multi, `rules`) = 0;
