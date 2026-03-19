SET NAMES utf8mb4;

SET @now = UNIX_TIMESTAMP();

INSERT INTO fa_discover (
  title, description, coverimage, coverimages, tag_ids, top_ids, content,
  browse, createtime, updatetime, weigh, statusdata,
  province, city, area, address, latlng, adddata, iscommentdata,
  user_id, school_id, favorNum, commentNum,
  audit_status, audit_admin_id, audit_time, is_top, top_sort
)
SELECT
  CASE s.id
    WHEN 1 THEN '【演示】清远校区快递站排队提醒'
    WHEN 3 THEN '【演示】佛山大学晚间自习点位'
    WHEN 4 THEN '【演示】厦门大学食堂错峰建议'
    WHEN 5 THEN '【演示】福州大学雨天出门备忘'
    WHEN 6 THEN '【演示】福建师大晚课后回宿舍提醒'
    WHEN 7 THEN '【演示】集美大学操场散步路线'
    WHEN 8 THEN '【演示】华侨大学图书馆安静角落'
    WHEN 9 THEN '【演示】福建农林取快递时间建议'
    WHEN 10 THEN '【演示】闽江学院早八通勤提醒'
    WHEN 11 THEN '【演示】厦门理工打印店小提示'
    WHEN 12 THEN '【演示】泉州师院晚风散步点'
    WHEN 13 THEN '【演示】莆田学院食堂窗口观察'
    ELSE CONCAT('【演示】', s.short_name, '校园生活提醒')
  END AS title,
  CASE s.id
    WHEN 1 THEN '纯文字提醒帖，方便首页展示无图卡片。'
    WHEN 3 THEN '纯文字学习分享示例。'
    WHEN 4 THEN '纯文字校园生活帖示例。'
    WHEN 5 THEN '纯文字天气提醒示例。'
    WHEN 6 THEN '纯文字出行提醒示例。'
    WHEN 7 THEN '纯文字休闲分享示例。'
    WHEN 8 THEN '纯文字安静角落推荐示例。'
    WHEN 9 THEN '纯文字生活提醒示例。'
    WHEN 10 THEN '纯文字通勤提醒示例。'
    WHEN 11 THEN '纯文字实用信息示例。'
    WHEN 12 THEN '纯文字校园散步帖示例。'
    WHEN 13 THEN '纯文字食堂观察示例。'
    ELSE '纯文字帖子示例。'
  END AS description,
  '' AS coverimage,
  '' AS coverimages,
  CASE s.id
    WHEN 1 THEN '3'
    WHEN 3 THEN '3'
    WHEN 4 THEN '5'
    WHEN 5 THEN '5'
    WHEN 6 THEN '3'
    WHEN 7 THEN '5'
    WHEN 8 THEN '5'
    WHEN 9 THEN '3'
    WHEN 10 THEN '3'
    WHEN 11 THEN '3'
    WHEN 12 THEN '5'
    WHEN 13 THEN '5'
    ELSE '5'
  END AS tag_ids,
  '' AS top_ids,
  CASE s.id
    WHEN 1 THEN '下午四点半到六点快递站人会明显变多，着急拿件的话建议避开这一段。先看取件码是不是已经上架，再过去会省很多时间。'
    WHEN 3 THEN '如果晚上想找个安静点的位置复习，教学楼中间层通常比一楼安静一些。空调偏低，待久了记得带件薄外套。'
    WHEN 4 THEN '中午食堂高峰来得很快，稍微错后十几分钟，排队体感会好很多。赶时间的话可以直接挑出餐快的窗口。'
    WHEN 5 THEN '最近天气不太稳定，出门前最好顺手带把伞。教学楼到生活区这一段如果碰上大风，雨伞容易翻，雨衣反而更省心。'
    WHEN 6 THEN '晚课结束后人流会集中，回宿舍如果不赶时间，稍微晚几分钟再走会轻松很多。下雨天路面也会比较滑，鞋底抓地力差的话要注意。'
    WHEN 7 THEN '操场外圈傍晚风比较舒服，适合饭后慢走一会儿。要是只想安静透透气，靠树荫那一侧的人会少一些。'
    WHEN 8 THEN '图书馆靠里的区域整体更安静，适合长时间看书。手机如果要开震动，放桌面上会有声音，最好垫在本子上。'
    WHEN 9 THEN '取快递最好尽量避开晚饭前后，那个时段排队最久。箱子大的件建议直接借推车，不然从站点拎回宿舍会比较累。'
    WHEN 10 THEN '早八前这段时间人会比较集中，提前十分钟出门会稳很多。要是骑车去教学楼，记得留意常用停车点是不是已经停满。'
    WHEN 11 THEN '交材料前最好先确认打印店是不是在营业，临近截止时间经常排队。页数多的话提早半天去，会比现场临时处理从容很多。'
    WHEN 12 THEN '傍晚风大的时候，教学区往操场这条路走起来会很舒服。想安静待一会儿的话，可以避开主干道，旁边的小路人更少。'
    WHEN 13 THEN '食堂热门窗口中午确实排得快，如果不想等太久，早点去或者错峰会更舒服。想吃得清淡一点，边上的普通套餐窗口反而更稳。'
    ELSE CONCAT(s.short_name, '可以补一条纯文字生活提醒帖，方便无图流展示。')
  END AS content,
  24 + s.id * 3 AS browse,
  @now - s.id * 600 AS createtime,
  @now - s.id * 600 AS updatetime,
  130 - s.id AS weigh,
  '1' AS statusdata,
  s.province,
  s.city,
  s.area,
  s.address,
  CONCAT(CAST(s.latitude AS CHAR), ',', CAST(s.longitude AS CHAR)) AS latlng,
  '1' AS adddata,
  '1' AS iscommentdata,
  u.user_id,
  s.id AS school_id,
  CASE
    WHEN s.id IN (1, 4, 8, 11) THEN 1
    ELSE 0
  END AS favorNum,
  0 AS commentNum,
  'approved' AS audit_status,
  0 AS audit_admin_id,
  @now - s.id * 600 + 60 AS audit_time,
  '0' AS is_top,
  0 AS top_sort
FROM fa_school s
INNER JOIN (
  SELECT school_id, MIN(id) AS user_id
  FROM fa_user
  WHERE school_id > 0
  GROUP BY school_id
) u ON u.school_id = s.id
LEFT JOIN (
  SELECT school_id, COUNT(*) AS text_post_count
  FROM fa_discover
  WHERE IFNULL(coverimage, '') = ''
    AND (coverimages IS NULL OR coverimages = '' OR coverimages = '[]')
  GROUP BY school_id
) d ON d.school_id = s.id
WHERE COALESCE(d.text_post_count, 0) = 0;

UPDATE fa_discover_tag t
SET t.number = (
  SELECT COUNT(*)
  FROM fa_discover d
  WHERE FIND_IN_SET(t.id, d.tag_ids)
);
