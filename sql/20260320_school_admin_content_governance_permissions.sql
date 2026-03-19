-- 2026-03-20 学校管理员补齐内容治理权限
-- 目标：学校管理员与学校审核员一致，具备帖子 / 评论 / 举报治理入口

SET @rule_comment = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment' LIMIT 1);
SET @rule_comment_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/index' LIMIT 1);
SET @rule_comment_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/del' LIMIT 1);
SET @rule_comment_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/multi' LIMIT 1);

SET @rule_report = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report' LIMIT 1);
SET @rule_report_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/index' LIMIT 1);
SET @rule_report_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/del' LIMIT 1);
SET @rule_report_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/multi' LIMIT 1);

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment))
WHERE `name` = '学校管理员'
  AND @rule_comment IS NOT NULL
  AND FIND_IN_SET(@rule_comment, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_index))
WHERE `name` = '学校管理员'
  AND @rule_comment_index IS NOT NULL
  AND FIND_IN_SET(@rule_comment_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_del))
WHERE `name` = '学校管理员'
  AND @rule_comment_del IS NOT NULL
  AND FIND_IN_SET(@rule_comment_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_multi))
WHERE `name` = '学校管理员'
  AND @rule_comment_multi IS NOT NULL
  AND FIND_IN_SET(@rule_comment_multi, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report))
WHERE `name` = '学校管理员'
  AND @rule_report IS NOT NULL
  AND FIND_IN_SET(@rule_report, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_index))
WHERE `name` = '学校管理员'
  AND @rule_report_index IS NOT NULL
  AND FIND_IN_SET(@rule_report_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_del))
WHERE `name` = '学校管理员'
  AND @rule_report_del IS NOT NULL
  AND FIND_IN_SET(@rule_report_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_multi))
WHERE `name` = '学校管理员'
  AND @rule_report_multi IS NOT NULL
  AND FIND_IN_SET(@rule_report_multi, `rules`) = 0;
