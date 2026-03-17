-- 2026-03-17 内容中心屏蔽词补充种子

INSERT INTO `fa_discover_blocked_word` (`word`, `remark`, `status`, `weigh`, `createtime`, `updatetime`)
SELECT `seed`.`word`, `seed`.`remark`, 'normal', 0, UNIX_TIMESTAMP(), UNIX_TIMESTAMP()
FROM (
    SELECT 'vx' AS `word`, '默认补充：联系方式绕过' AS `remark`
    UNION ALL SELECT 'v信', '默认补充：联系方式绕过'
    UNION ALL SELECT '加微信', '默认补充：联系方式绕过'
    UNION ALL SELECT '加微', '默认补充：联系方式绕过'
    UNION ALL SELECT '加v', '默认补充：联系方式绕过'
    UNION ALL SELECT '微我', '默认补充：联系方式绕过'
    UNION ALL SELECT 'qq号', '默认补充：联系方式绕过'
    UNION ALL SELECT '加qq', '默认补充：联系方式绕过'
    UNION ALL SELECT '扣扣', '默认补充：联系方式绕过'
    UNION ALL SELECT '裸聊', '默认补充：涉黄风险'
    UNION ALL SELECT '约炮', '默认补充：涉黄风险'
    UNION ALL SELECT '招嫖', '默认补充：涉黄风险'
    UNION ALL SELECT '赌博', '默认补充：赌博风险'
    UNION ALL SELECT '博彩', '默认补充：赌博风险'
    UNION ALL SELECT '外围', '默认补充：赌博风险'
    UNION ALL SELECT '刷单', '默认补充：诈骗风险'
    UNION ALL SELECT '代开发票', '默认补充：违法交易'
    UNION ALL SELECT '毒品', '默认补充：违禁品风险'
    UNION ALL SELECT '冰毒', '默认补充：违禁品风险'
    UNION ALL SELECT 'K粉', '默认补充：违禁品风险'
) AS `seed`
WHERE NOT EXISTS (
    SELECT 1
    FROM `fa_discover_blocked_word` `existing`
    WHERE `existing`.`word` = `seed`.`word`
);
