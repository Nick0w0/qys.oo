SET NAMES utf8mb4;

-- 2026-03-18
-- 根据现有互动数据，回填缺失的通知日志
-- 可重复执行；已存在的同类通知不会重复插入

-- 1) 作品被点赞
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT
  '1' AS typedata,
  D.user_id AS user_id,
  F.user_id AS create_id,
  '赞了你的作品' AS content,
  CONCAT('{"discover_id":',D.id,',"favor_id":',F.id,',"attention_id":"","comment_id":""}') AS remind,
  '0' AS readdata,
  COALESCE(F.createtime, UNIX_TIMESTAMP()) AS createtime,
  COALESCE(F.createtime, UNIX_TIMESTAMP()) AS updatetime,
  D.id AS discover_id
FROM fa_discover_favor F
INNER JOIN fa_discover D ON D.id = F.discover_id
WHERE F.typedata = '1'
  AND D.user_id > 0
  AND F.user_id > 0
  AND D.user_id <> F.user_id
  AND NOT EXISTS (
    SELECT 1
    FROM fa_discover_log L
    WHERE L.typedata = '1'
      AND L.user_id = D.user_id
      AND L.create_id = F.user_id
      AND L.discover_id = D.id
      AND L.remind = CONCAT('{"discover_id":',D.id,',"favor_id":',F.id,',"attention_id":"","comment_id":""}')
  );

-- 2) 评论被点赞
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT
  '2' AS typedata,
  C.user_id AS user_id,
  F.user_id AS create_id,
  '赞了你的评论' AS content,
  CONCAT('{"discover_id":',C.discover_id,',"favor_id":',F.id,',"attention_id":"","comment_id":',C.id,'}') AS remind,
  '0' AS readdata,
  COALESCE(F.createtime, UNIX_TIMESTAMP()) AS createtime,
  COALESCE(F.createtime, UNIX_TIMESTAMP()) AS updatetime,
  C.discover_id AS discover_id
FROM fa_discover_favor F
INNER JOIN fa_discover_comment C ON C.id = F.comment_id
WHERE F.typedata = '2'
  AND C.user_id > 0
  AND F.user_id > 0
  AND C.user_id <> F.user_id
  AND NOT EXISTS (
    SELECT 1
    FROM fa_discover_log L
    WHERE L.typedata = '2'
      AND L.user_id = C.user_id
      AND L.create_id = F.user_id
      AND L.discover_id = C.discover_id
      AND L.remind = CONCAT('{"discover_id":',C.discover_id,',"favor_id":',F.id,',"attention_id":"","comment_id":',C.id,'}')
  );

-- 3) 作品被评论
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT
  '3' AS typedata,
  D.user_id AS user_id,
  C.user_id AS create_id,
  '评论了你的作品' AS content,
  CONCAT('{"discover_id":',D.id,',"favor_id":"","attention_id":"","comment_id":',C.id,'}') AS remind,
  '0' AS readdata,
  COALESCE(C.createtime, UNIX_TIMESTAMP()) AS createtime,
  COALESCE(C.createtime, UNIX_TIMESTAMP()) AS updatetime,
  D.id AS discover_id
FROM fa_discover_comment C
INNER JOIN fa_discover D ON D.id = C.discover_id
WHERE C.comment_id = 0
  AND D.user_id > 0
  AND C.user_id > 0
  AND D.user_id <> C.user_id
  AND NOT EXISTS (
    SELECT 1
    FROM fa_discover_log L
    WHERE L.typedata = '3'
      AND L.user_id = D.user_id
      AND L.create_id = C.user_id
      AND L.discover_id = D.id
      AND L.remind = CONCAT('{"discover_id":',D.id,',"favor_id":"","attention_id":"","comment_id":',C.id,'}')
  );

-- 4) 评论被回复
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT
  '5' AS typedata,
  P.user_id AS user_id,
  R.user_id AS create_id,
  '回复了你的评论' AS content,
  CONCAT('{"discover_id":',R.discover_id,',"favor_id":"","attention_id":"","comment_id":',P.id,'}') AS remind,
  '0' AS readdata,
  COALESCE(R.createtime, UNIX_TIMESTAMP()) AS createtime,
  COALESCE(R.createtime, UNIX_TIMESTAMP()) AS updatetime,
  R.discover_id AS discover_id
FROM fa_discover_comment R
INNER JOIN fa_discover_comment P ON P.id = R.comment_id
WHERE R.comment_id > 0
  AND P.user_id > 0
  AND R.user_id > 0
  AND P.user_id <> R.user_id
  AND NOT EXISTS (
    SELECT 1
    FROM fa_discover_log L
    WHERE L.typedata = '5'
      AND L.user_id = P.user_id
      AND L.create_id = R.user_id
      AND L.discover_id = R.discover_id
      AND L.remind = CONCAT('{"discover_id":',R.discover_id,',"favor_id":"","attention_id":"","comment_id":',P.id,'}')
  );

-- 5) 被关注
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT
  '4' AS typedata,
  A.attention_id AS user_id,
  A.user_id AS create_id,
  '关注了你' AS content,
  CONCAT('{"discover_id":',A.discover_id,',"favor_id":0,"attention_id":',A.id,',"comment_id":0}') AS remind,
  '0' AS readdata,
  COALESCE(A.createtime, UNIX_TIMESTAMP()) AS createtime,
  COALESCE(A.createtime, UNIX_TIMESTAMP()) AS updatetime,
  A.discover_id AS discover_id
FROM fa_discover_attentions A
WHERE A.attention_id > 0
  AND A.user_id > 0
  AND A.attention_id <> A.user_id
  AND NOT EXISTS (
    SELECT 1
    FROM fa_discover_log L
    WHERE L.typedata = '4'
      AND L.user_id = A.attention_id
      AND L.create_id = A.user_id
      AND L.discover_id = A.discover_id
      AND L.remind = CONCAT('{"discover_id":',A.discover_id,',"favor_id":0,"attention_id":',A.id,',"comment_id":0}')
  );
