SET NAMES utf8mb4;
SET @now = UNIX_TIMESTAMP();

SET @u_gz_old = (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1);
SET @u_gz_09 = (SELECT id FROM fa_user WHERE mobile='13200000000' LIMIT 1);
SET @u_gz_14 = (SELECT id FROM fa_user WHERE mobile='18800000004' LIMIT 1);
SET @u_gz_15 = (SELECT id FROM fa_user WHERE mobile='18800000005' LIMIT 1);

SET @d_gz_26 = (SELECT id FROM fa_discover WHERE id=26 AND user_id=@u_gz_09 LIMIT 1);
SET @d_gz_27 = (SELECT id FROM fa_discover WHERE id=27 AND user_id=@u_gz_14 LIMIT 1);

SET @c_gz_24 = (SELECT id FROM fa_discover_comment WHERE id=24 AND discover_id=@d_gz_26 AND user_id=@u_gz_old LIMIT 1);
SET @c_gz_25 = (SELECT id FROM fa_discover_comment WHERE id=25 AND discover_id=@d_gz_26 AND user_id=@u_gz_14 LIMIT 1);
SET @c_gz_27 = (SELECT id FROM fa_discover_comment WHERE id=27 AND discover_id=@d_gz_27 AND user_id=@u_gz_09 LIMIT 1);

SET @f_gz_27 = (SELECT id FROM fa_discover_favor WHERE id=27 AND typedata='1' AND discover_id=@d_gz_26 AND user_id=@u_gz_old LIMIT 1);
SET @f_gz_28 = (SELECT id FROM fa_discover_favor WHERE id=28 AND typedata='1' AND discover_id=@d_gz_26 AND user_id=@u_gz_14 LIMIT 1);
SET @f_gz_29 = (SELECT id FROM fa_discover_favor WHERE id=29 AND typedata='1' AND discover_id=@d_gz_26 AND user_id=@u_gz_15 LIMIT 1);
SET @f_gz_30 = (SELECT id FROM fa_discover_favor WHERE id=30 AND typedata='1' AND discover_id=@d_gz_27 AND user_id=@u_gz_old LIMIT 1);
SET @f_gz_31 = (SELECT id FROM fa_discover_favor WHERE id=31 AND typedata='1' AND discover_id=@d_gz_27 AND user_id=@u_gz_09 LIMIT 1);
SET @f_gz_32 = (SELECT id FROM fa_discover_favor WHERE id=32 AND typedata='1' AND discover_id=@d_gz_27 AND user_id=@u_gz_15 LIMIT 1);

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz_09,@u_gz_old,'评论了你的作品',CONCAT('{"discover_id":',@d_gz_26,',"favor_id":"","attention_id":"","comment_id":',@c_gz_24,'}'),'0',@now-900,@now-900,@d_gz_26
FROM DUAL
WHERE @c_gz_24 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='3' AND user_id=@u_gz_09 AND create_id=@u_gz_old AND discover_id=@d_gz_26
      AND remind=CONCAT('{"discover_id":',@d_gz_26,',"favor_id":"","attention_id":"","comment_id":',@c_gz_24,'}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz_09,@u_gz_14,'评论了你的作品',CONCAT('{"discover_id":',@d_gz_26,',"favor_id":"","attention_id":"","comment_id":',@c_gz_25,'}'),'0',@now-840,@now-840,@d_gz_26
FROM DUAL
WHERE @c_gz_25 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='3' AND user_id=@u_gz_09 AND create_id=@u_gz_14 AND discover_id=@d_gz_26
      AND remind=CONCAT('{"discover_id":',@d_gz_26,',"favor_id":"","attention_id":"","comment_id":',@c_gz_25,'}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_09,@u_gz_old,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_27,',"attention_id":"","comment_id":""}'),'0',@now-780,@now-780,@d_gz_26
FROM DUAL
WHERE @f_gz_27 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_09 AND create_id=@u_gz_old AND discover_id=@d_gz_26
      AND remind=CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_27,',"attention_id":"","comment_id":""}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_09,@u_gz_14,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_28,',"attention_id":"","comment_id":""}'),'0',@now-720,@now-720,@d_gz_26
FROM DUAL
WHERE @f_gz_28 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_09 AND create_id=@u_gz_14 AND discover_id=@d_gz_26
      AND remind=CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_28,',"attention_id":"","comment_id":""}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_09,@u_gz_15,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_29,',"attention_id":"","comment_id":""}'),'0',@now-660,@now-660,@d_gz_26
FROM DUAL
WHERE @f_gz_29 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_09 AND create_id=@u_gz_15 AND discover_id=@d_gz_26
      AND remind=CONCAT('{"discover_id":',@d_gz_26,',"favor_id":',@f_gz_29,',"attention_id":"","comment_id":""}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz_14,@u_gz_09,'评论了你的作品',CONCAT('{"discover_id":',@d_gz_27,',"favor_id":"","attention_id":"","comment_id":',@c_gz_27,'}'),'0',@now-600,@now-600,@d_gz_27
FROM DUAL
WHERE @c_gz_27 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='3' AND user_id=@u_gz_14 AND create_id=@u_gz_09 AND discover_id=@d_gz_27
      AND remind=CONCAT('{"discover_id":',@d_gz_27,',"favor_id":"","attention_id":"","comment_id":',@c_gz_27,'}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_14,@u_gz_old,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_30,',"attention_id":"","comment_id":""}'),'0',@now-540,@now-540,@d_gz_27
FROM DUAL
WHERE @f_gz_30 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_14 AND create_id=@u_gz_old AND discover_id=@d_gz_27
      AND remind=CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_30,',"attention_id":"","comment_id":""}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_14,@u_gz_09,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_31,',"attention_id":"","comment_id":""}'),'0',@now-480,@now-480,@d_gz_27
FROM DUAL
WHERE @f_gz_31 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_14 AND create_id=@u_gz_09 AND discover_id=@d_gz_27
      AND remind=CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_31,',"attention_id":"","comment_id":""}')
  );

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz_14,@u_gz_15,'赞了你的作品',CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_32,',"attention_id":"","comment_id":""}'),'0',@now-420,@now-420,@d_gz_27
FROM DUAL
WHERE @f_gz_32 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_log
    WHERE typedata='1' AND user_id=@u_gz_14 AND create_id=@u_gz_15 AND discover_id=@d_gz_27
      AND remind=CONCAT('{"discover_id":',@d_gz_27,',"favor_id":',@f_gz_32,',"attention_id":"","comment_id":""}')
  );
