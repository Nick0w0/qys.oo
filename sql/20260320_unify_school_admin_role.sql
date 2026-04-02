-- 2026-03-20 统一子管理员角色为“学校管理员”
-- 目标：
-- 1. 所有绑定学校的子管理员统一挂到“学校管理员”组
-- 2. “学校管理员”组补齐内容治理权限（帖子 / 评论 / 举报）
-- 3. 屏蔽词管理仅保留给总管理员

SET @school_manager_group_id = (
    SELECT `id` FROM `fa_auth_group` WHERE `name` = '学校管理员' LIMIT 1
);

SET @rule_review = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/review' LIMIT 1);
SET @rule_review_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/review/index' LIMIT 1);
SET @rule_discover = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/discover' LIMIT 1);
SET @rule_discover_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/discover/index' LIMIT 1);
SET @rule_discover_edit = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/discover/edit' LIMIT 1);
SET @rule_discover_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/discover/del' LIMIT 1);
SET @rule_discover_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/discover/multi' LIMIT 1);
SET @rule_comment = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment' LIMIT 1);
SET @rule_comment_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/index' LIMIT 1);
SET @rule_comment_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/del' LIMIT 1);
SET @rule_comment_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/comment/multi' LIMIT 1);
SET @rule_report = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report' LIMIT 1);
SET @rule_report_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/index' LIMIT 1);
SET @rule_report_del = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/del' LIMIT 1);
SET @rule_report_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/report/multi' LIMIT 1);
SET @rule_schoolops = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/schoolops' LIMIT 1);
SET @rule_content = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover/content' LIMIT 1);
SET @rule_discover_root = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'discover' LIMIT 1);
SET @rule_user = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'user/user' LIMIT 1);
SET @rule_user_index = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'user/user/index' LIMIT 1);
SET @rule_user_edit = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'user/user/edit' LIMIT 1);
SET @rule_user_multi = (SELECT `id` FROM `fa_auth_rule` WHERE `name` = 'user/user/multi' LIMIT 1);

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover_root))
WHERE `id` = @school_manager_group_id
  AND @rule_discover_root IS NOT NULL
  AND FIND_IN_SET(@rule_discover_root, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_content))
WHERE `id` = @school_manager_group_id
  AND @rule_content IS NOT NULL
  AND FIND_IN_SET(@rule_content, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_schoolops))
WHERE `id` = @school_manager_group_id
  AND @rule_schoolops IS NOT NULL
  AND FIND_IN_SET(@rule_schoolops, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover))
WHERE `id` = @school_manager_group_id
  AND @rule_discover IS NOT NULL
  AND FIND_IN_SET(@rule_discover, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover_index))
WHERE `id` = @school_manager_group_id
  AND @rule_discover_index IS NOT NULL
  AND FIND_IN_SET(@rule_discover_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover_edit))
WHERE `id` = @school_manager_group_id
  AND @rule_discover_edit IS NOT NULL
  AND FIND_IN_SET(@rule_discover_edit, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover_del))
WHERE `id` = @school_manager_group_id
  AND @rule_discover_del IS NOT NULL
  AND FIND_IN_SET(@rule_discover_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_discover_multi))
WHERE `id` = @school_manager_group_id
  AND @rule_discover_multi IS NOT NULL
  AND FIND_IN_SET(@rule_discover_multi, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment))
WHERE `id` = @school_manager_group_id
  AND @rule_comment IS NOT NULL
  AND FIND_IN_SET(@rule_comment, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_index))
WHERE `id` = @school_manager_group_id
  AND @rule_comment_index IS NOT NULL
  AND FIND_IN_SET(@rule_comment_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_del))
WHERE `id` = @school_manager_group_id
  AND @rule_comment_del IS NOT NULL
  AND FIND_IN_SET(@rule_comment_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_comment_multi))
WHERE `id` = @school_manager_group_id
  AND @rule_comment_multi IS NOT NULL
  AND FIND_IN_SET(@rule_comment_multi, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report))
WHERE `id` = @school_manager_group_id
  AND @rule_report IS NOT NULL
  AND FIND_IN_SET(@rule_report, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_index))
WHERE `id` = @school_manager_group_id
  AND @rule_report_index IS NOT NULL
  AND FIND_IN_SET(@rule_report_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_del))
WHERE `id` = @school_manager_group_id
  AND @rule_report_del IS NOT NULL
  AND FIND_IN_SET(@rule_report_del, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_report_multi))
WHERE `id` = @school_manager_group_id
  AND @rule_report_multi IS NOT NULL
  AND FIND_IN_SET(@rule_report_multi, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_user))
WHERE `id` = @school_manager_group_id
  AND @rule_user IS NOT NULL
  AND FIND_IN_SET(@rule_user, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_user_index))
WHERE `id` = @school_manager_group_id
  AND @rule_user_index IS NOT NULL
  AND FIND_IN_SET(@rule_user_index, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_user_edit))
WHERE `id` = @school_manager_group_id
  AND @rule_user_edit IS NOT NULL
  AND FIND_IN_SET(@rule_user_edit, `rules`) = 0;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM CONCAT_WS(',', NULLIF(`rules`, ''), @rule_user_multi))
WHERE `id` = @school_manager_group_id
  AND @rule_user_multi IS NOT NULL
  AND FIND_IN_SET(@rule_user_multi, `rules`) = 0;

UPDATE `fa_auth_group_access` aga
INNER JOIN `fa_admin_school` ads
    ON ads.`admin_id` = aga.`uid`
   AND ads.`status` = 'normal'
SET aga.`group_id` = @school_manager_group_id
WHERE @school_manager_group_id IS NOT NULL
  AND aga.`group_id` <> @school_manager_group_id;

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `rules`, ','), CONCAT(',', @rule_review, ','), ','))
WHERE `id` = @school_manager_group_id
  AND @rule_review IS NOT NULL
  AND FIND_IN_SET(@rule_review, `rules`);

UPDATE `fa_auth_group`
SET `rules` = TRIM(BOTH ',' FROM REPLACE(CONCAT(',', `rules`, ','), CONCAT(',', @rule_review_index, ','), ','))
WHERE `id` = @school_manager_group_id
  AND @rule_review_index IS NOT NULL
  AND FIND_IN_SET(@rule_review_index, `rules`);
