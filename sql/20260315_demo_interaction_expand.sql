SET NAMES utf8mb4;
SET @now = UNIX_TIMESTAMP();

SET @u_qy = (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1);
SET @u_gz = (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1);
SET @u_fs = (SELECT id FROM fa_user WHERE mobile='13900001237' LIMIT 1);

SET @d_qy2 = (SELECT id FROM fa_discover WHERE title='【演示】清远校区食堂晚餐测评' LIMIT 1);
SET @d_qy3 = (SELECT id FROM fa_discover WHERE title='【演示】清远校区操场夜跑记录' LIMIT 1);
SET @d_gz2 = (SELECT id FROM fa_discover WHERE title='【演示】广州校区宿舍桌面改造' LIMIT 1);
SET @d_gz3 = (SELECT id FROM fa_discover WHERE title='【演示】广州校区奶茶轻测评' LIMIT 1);
SET @d_fs2 = (SELECT id FROM fa_discover WHERE title='【演示】佛山校区毕业季随手记' LIMIT 1);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_qy2,'【演示补充】广州学姐觉得晚饭这几家窗口都挺稳。',@u_gz,0,0,@now-2600,'1',0
FROM DUAL
WHERE @d_qy2 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_comment WHERE discover_id=@d_qy2 AND user_id=@u_gz AND comment_id=0 AND content='【演示补充】广州学姐觉得晚饭这几家窗口都挺稳。');
SET @cx1 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_qy2 AND user_id=@u_gz AND comment_id=0 AND content='【演示补充】广州学姐觉得晚饭这几家窗口都挺稳。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@cx1 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_qy3,'【演示补充】佛山同学表示夜跑时记得带水。',@u_fs,0,0,@now-2500,'1',0
FROM DUAL
WHERE @d_qy3 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_comment WHERE discover_id=@d_qy3 AND user_id=@u_fs AND comment_id=0 AND content='【演示补充】佛山同学表示夜跑时记得带水。');
SET @cx2 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_qy3 AND user_id=@u_fs AND comment_id=0 AND content='【演示补充】佛山同学表示夜跑时记得带水。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@cx2 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_gz2,'【演示补充】清远学长觉得桌面收纳思路很实用。',@u_qy,0,0,@now-2400,'1',0
FROM DUAL
WHERE @d_gz2 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_comment WHERE discover_id=@d_gz2 AND user_id=@u_qy AND comment_id=0 AND content='【演示补充】清远学长觉得桌面收纳思路很实用。');
SET @cx3 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_gz2 AND user_id=@u_qy AND comment_id=0 AND content='【演示补充】清远学长觉得桌面收纳思路很实用。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@cx3 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_gz3,'【演示补充】佛山同学提醒：自习前别空腹喝奶茶。',@u_fs,0,0,@now-2300,'1',0
FROM DUAL
WHERE @d_gz3 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_comment WHERE discover_id=@d_gz3 AND user_id=@u_fs AND comment_id=0 AND content='【演示补充】佛山同学提醒：自习前别空腹喝奶茶。');
SET @cx4 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_gz3 AND user_id=@u_fs AND comment_id=0 AND content='【演示补充】佛山同学提醒：自习前别空腹喝奶茶。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@cx4 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_fs2,'【演示补充】广州学姐觉得毕业照那几处机位都很稳。',@u_gz,0,0,@now-2200,'1',0
FROM DUAL
WHERE @d_fs2 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_comment WHERE discover_id=@d_fs2 AND user_id=@u_gz AND comment_id=0 AND content='【演示补充】广州学姐觉得毕业照那几处机位都很稳。');
SET @cx5 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_fs2 AND user_id=@u_gz AND comment_id=0 AND content='【演示补充】广州学姐觉得毕业照那几处机位都很稳。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@cx5 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_qy2,0,@u_fs,@now-2100,0 FROM DUAL
WHERE @d_qy2 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy2 AND user_id=@u_fs);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_qy3,0,@u_gz,@now-2000,0 FROM DUAL
WHERE @d_qy3 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy3 AND user_id=@u_gz);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_gz2,0,@u_fs,@now-1900,0 FROM DUAL
WHERE @d_gz2 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz2 AND user_id=@u_fs);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_gz3,0,@u_qy,@now-1800,0 FROM DUAL
WHERE @d_gz3 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz3 AND user_id=@u_qy);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_fs2,0,@u_qy,@now-1700,0 FROM DUAL
WHERE @d_fs2 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs2 AND user_id=@u_qy);

SET @fx1 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy2 AND user_id=@u_fs LIMIT 1);
SET @fx2 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy3 AND user_id=@u_gz LIMIT 1);
SET @fx3 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz2 AND user_id=@u_fs LIMIT 1);
SET @fx4 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz3 AND user_id=@u_qy LIMIT 1);
SET @fx5 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs2 AND user_id=@u_qy LIMIT 1);

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_qy,@u_gz,'评论了你的作品',CONCAT('{"discover_id":',@d_qy2,',"favor_id":"","attention_id":"","comment_id":',@cx1,'}'),'0',@now-2600,@now-2600,@d_qy2 FROM DUAL
WHERE @cx1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_qy2 AND remind=CONCAT('{"discover_id":',@d_qy2,',"favor_id":"","attention_id":"","comment_id":',@cx1,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_qy,@u_fs,'评论了你的作品',CONCAT('{"discover_id":',@d_qy3,',"favor_id":"","attention_id":"","comment_id":',@cx2,'}'),'0',@now-2500,@now-2500,@d_qy3 FROM DUAL
WHERE @cx2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_qy3 AND remind=CONCAT('{"discover_id":',@d_qy3,',"favor_id":"","attention_id":"","comment_id":',@cx2,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz,@u_qy,'评论了你的作品',CONCAT('{"discover_id":',@d_gz2,',"favor_id":"","attention_id":"","comment_id":',@cx3,'}'),'0',@now-2400,@now-2400,@d_gz2 FROM DUAL
WHERE @cx3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_gz2 AND remind=CONCAT('{"discover_id":',@d_gz2,',"favor_id":"","attention_id":"","comment_id":',@cx3,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz,@u_fs,'评论了你的作品',CONCAT('{"discover_id":',@d_gz3,',"favor_id":"","attention_id":"","comment_id":',@cx4,'}'),'0',@now-2300,@now-2300,@d_gz3 FROM DUAL
WHERE @cx4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_gz AND create_id=@u_fs AND discover_id=@d_gz3 AND remind=CONCAT('{"discover_id":',@d_gz3,',"favor_id":"","attention_id":"","comment_id":',@cx4,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_fs,@u_gz,'评论了你的作品',CONCAT('{"discover_id":',@d_fs2,',"favor_id":"","attention_id":"","comment_id":',@cx5,'}'),'0',@now-2200,@now-2200,@d_fs2 FROM DUAL
WHERE @cx5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_fs AND create_id=@u_gz AND discover_id=@d_fs2 AND remind=CONCAT('{"discover_id":',@d_fs2,',"favor_id":"","attention_id":"","comment_id":',@cx5,'}'));

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_qy,@u_fs,'赞了你的作品',CONCAT('{"discover_id":',@d_qy2,',"favor_id":',@fx1,',"attention_id":"","comment_id":""}'),'0',@now-2100,@now-2100,@d_qy2 FROM DUAL
WHERE @fx1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_qy2 AND remind=CONCAT('{"discover_id":',@d_qy2,',"favor_id":',@fx1,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_qy,@u_gz,'赞了你的作品',CONCAT('{"discover_id":',@d_qy3,',"favor_id":',@fx2,',"attention_id":"","comment_id":""}'),'0',@now-2000,@now-2000,@d_qy3 FROM DUAL
WHERE @fx2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_qy3 AND remind=CONCAT('{"discover_id":',@d_qy3,',"favor_id":',@fx2,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz,@u_fs,'赞了你的作品',CONCAT('{"discover_id":',@d_gz2,',"favor_id":',@fx3,',"attention_id":"","comment_id":""}'),'0',@now-1900,@now-1900,@d_gz2 FROM DUAL
WHERE @fx3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_gz AND create_id=@u_fs AND discover_id=@d_gz2 AND remind=CONCAT('{"discover_id":',@d_gz2,',"favor_id":',@fx3,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz,@u_qy,'赞了你的作品',CONCAT('{"discover_id":',@d_gz3,',"favor_id":',@fx4,',"attention_id":"","comment_id":""}'),'0',@now-1800,@now-1800,@d_gz3 FROM DUAL
WHERE @fx4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_gz3 AND remind=CONCAT('{"discover_id":',@d_gz3,',"favor_id":',@fx4,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_fs,@u_qy,'赞了你的作品',CONCAT('{"discover_id":',@d_fs2,',"favor_id":',@fx5,',"attention_id":"","comment_id":""}'),'0',@now-1700,@now-1700,@d_fs2 FROM DUAL
WHERE @fx5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_fs AND create_id=@u_qy AND discover_id=@d_fs2 AND remind=CONCAT('{"discover_id":',@d_fs2,',"favor_id":',@fx5,',"attention_id":"","comment_id":""}'));

UPDATE fa_discover d
SET d.favorNum = (SELECT COUNT(*) FROM fa_discover_favor f WHERE f.discover_id=d.id AND f.typedata='1'),
    d.commentNum = (SELECT COUNT(*) FROM fa_discover_comment c WHERE c.discover_id=d.id AND c.comment_id=0 AND c.statusdata='1'),
    d.updatetime = @now
WHERE d.title LIKE '【演示】%';