SET NAMES utf8mb4;
SET @now = UNIX_TIMESTAMP();

SET @u_qy = (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1);
SET @u_gz = (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1);
SET @u_fs = (SELECT id FROM fa_user WHERE mobile='13900001237' LIMIT 1);

SET @d_qy1 = (SELECT id FROM fa_discover WHERE title='【演示】清远校区图书馆自习攻略' LIMIT 1);
SET @d_qy2 = (SELECT id FROM fa_discover WHERE title='【演示】清远校区食堂晚餐测评' LIMIT 1);
SET @d_gz1 = (SELECT id FROM fa_discover WHERE title='【演示】广州校区周末打卡路线' LIMIT 1);
SET @d_gz2 = (SELECT id FROM fa_discover WHERE title='【演示】广州校区宿舍桌面改造' LIMIT 1);
SET @d_fs1 = (SELECT id FROM fa_discover WHERE title='【演示】佛山校区自习室座位观察' LIMIT 1);
SET @d_fs2 = (SELECT id FROM fa_discover WHERE title='【演示】佛山校区毕业季随手记' LIMIT 1);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_qy1,'【演示互动】广州学姐来串门，图书馆靠窗位确实不错。',@u_gz,0,0,@now-5400,'1',0
FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_qy1 AND user_id=@u_gz AND comment_id=0
      AND content='【演示互动】广州学姐来串门，图书馆靠窗位确实不错。'
  );
SET @c1 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_qy1 AND user_id=@u_gz AND comment_id=0 AND content='【演示互动】广州学姐来串门，图书馆靠窗位确实不错。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c1 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_qy1,'【演示互动】佛山同学补充：中午人少的时候环境最好。',@u_fs,0,0,@now-5200,'1',0
FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_qy1 AND user_id=@u_fs AND comment_id=0
      AND content='【演示互动】佛山同学补充：中午人少的时候环境最好。'
  );
SET @c2 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_qy1 AND user_id=@u_fs AND comment_id=0 AND content='【演示互动】佛山同学补充：中午人少的时候环境最好。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c2 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_gz1,'【演示互动】清远学长也想周末去这条路线打卡。',@u_qy,0,0,@now-5000,'1',0
FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_gz1 AND user_id=@u_qy AND comment_id=0
      AND content='【演示互动】清远学长也想周末去这条路线打卡。'
  );
SET @c3 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_gz1 AND user_id=@u_qy AND comment_id=0 AND content='【演示互动】清远学长也想周末去这条路线打卡。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c3 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_gz1,'【演示互动】佛山同学觉得傍晚江边那一段最适合拍照。',@u_fs,0,0,@now-4800,'1',0
FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_gz1 AND user_id=@u_fs AND comment_id=0
      AND content='【演示互动】佛山同学觉得傍晚江边那一段最适合拍照。'
  );
SET @c4 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_gz1 AND user_id=@u_fs AND comment_id=0 AND content='【演示互动】佛山同学觉得傍晚江边那一段最适合拍照。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c4 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_fs1,'【演示互动】清远学长觉得这个自习室很适合备考周使用。',@u_qy,0,0,@now-4600,'1',0
FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_fs1 AND user_id=@u_qy AND comment_id=0
      AND content='【演示互动】清远学长觉得这个自习室很适合备考周使用。'
  );
SET @c5 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_fs1 AND user_id=@u_qy AND comment_id=0 AND content='【演示互动】清远学长觉得这个自习室很适合备考周使用。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c5 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_fs1,'【演示互动】广州学姐留言：插座位如果再多一点就更好了。',@u_gz,0,0,@now-4400,'1',0
FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_fs1 AND user_id=@u_gz AND comment_id=0
      AND content='【演示互动】广州学姐留言：插座位如果再多一点就更好了。'
  );
SET @c6 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_fs1 AND user_id=@u_gz AND comment_id=0 AND content='【演示互动】广州学姐留言：插座位如果再多一点就更好了。' LIMIT 1);
UPDATE fa_discover_comment SET parent_id=id WHERE id=@c6 AND (parent_id IS NULL OR parent_id=0);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_qy1,'【演示互动】清远学长回复：晚上去的话要早点占位。',@u_qy,@c1,@u_gz,@now-5100,'1',@c1
FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_qy IS NOT NULL AND @c1 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_qy1 AND user_id=@u_qy AND comment_id=@c1
      AND content='【演示互动】清远学长回复：晚上去的话要早点占位。'
  );
SET @r1 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_qy1 AND user_id=@u_qy AND comment_id=@c1 AND content='【演示互动】清远学长回复：晚上去的话要早点占位。' LIMIT 1);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_gz1,'【演示互动】广州学姐回复：这条路线下午四点出发最合适。',@u_gz,@c3,@u_qy,@now-4700,'1',@c3
FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_gz IS NOT NULL AND @c3 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_gz1 AND user_id=@u_gz AND comment_id=@c3
      AND content='【演示互动】广州学姐回复：这条路线下午四点出发最合适。'
  );
SET @r2 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_gz1 AND user_id=@u_gz AND comment_id=@c3 AND content='【演示互动】广州学姐回复：这条路线下午四点出发最合适。' LIMIT 1);

INSERT INTO fa_discover_comment (discover_id,content,user_id,comment_id,reply_id,createtime,statusdata,parent_id)
SELECT @d_fs1,'【演示互动】佛山同学回复：考前周我会提前半小时去。',@u_fs,@c5,@u_qy,@now-4300,'1',@c5
FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_fs IS NOT NULL AND @c5 IS NOT NULL
  AND NOT EXISTS (
    SELECT 1 FROM fa_discover_comment
    WHERE discover_id=@d_fs1 AND user_id=@u_fs AND comment_id=@c5
      AND content='【演示互动】佛山同学回复：考前周我会提前半小时去。'
  );
SET @r3 = (SELECT id FROM fa_discover_comment WHERE discover_id=@d_fs1 AND user_id=@u_fs AND comment_id=@c5 AND content='【演示互动】佛山同学回复：考前周我会提前半小时去。' LIMIT 1);

INSERT INTO fa_discover_attentions (user_id,attention_id,discover_id,createtime)
SELECT @u_qy,@u_gz,@d_gz1,@now-4200 FROM DUAL
WHERE @u_qy IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_attentions WHERE user_id=@u_qy AND attention_id=@u_gz);
INSERT INTO fa_discover_attentions (user_id,attention_id,discover_id,createtime)
SELECT @u_gz,@u_qy,@d_qy1,@now-4100 FROM DUAL
WHERE @u_qy IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_attentions WHERE user_id=@u_gz AND attention_id=@u_qy);
INSERT INTO fa_discover_attentions (user_id,attention_id,discover_id,createtime)
SELECT @u_fs,@u_qy,@d_qy2,@now-4000 FROM DUAL
WHERE @u_fs IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_attentions WHERE user_id=@u_fs AND attention_id=@u_qy);
INSERT INTO fa_discover_attentions (user_id,attention_id,discover_id,createtime)
SELECT @u_qy,@u_fs,@d_fs1,@now-3900 FROM DUAL
WHERE @u_qy IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_attentions WHERE user_id=@u_qy AND attention_id=@u_fs);

SET @a1 = (SELECT id FROM fa_discover_attentions WHERE user_id=@u_qy AND attention_id=@u_gz LIMIT 1);
SET @a2 = (SELECT id FROM fa_discover_attentions WHERE user_id=@u_gz AND attention_id=@u_qy LIMIT 1);
SET @a3 = (SELECT id FROM fa_discover_attentions WHERE user_id=@u_fs AND attention_id=@u_qy LIMIT 1);
SET @a4 = (SELECT id FROM fa_discover_attentions WHERE user_id=@u_qy AND attention_id=@u_fs LIMIT 1);

INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_gz1,@u_qy,@now-3800 FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_gz1 AND user_id=@u_qy);
INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_fs1,@u_qy,@now-3780 FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_fs1 AND user_id=@u_qy);
INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_qy1,@u_gz,@now-3760 FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_qy1 AND user_id=@u_gz);
INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_fs1,@u_gz,@now-3740 FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_fs1 AND user_id=@u_gz);
INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_qy1,@u_fs,@now-3720 FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_qy1 AND user_id=@u_fs);
INSERT INTO fa_discover_collect (discover_id,user_id,createtime)
SELECT @d_gz1,@u_fs,@now-3700 FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_collect WHERE discover_id=@d_gz1 AND user_id=@u_fs);

INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_qy1,0,@u_gz,@now-3600,0 FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy1 AND user_id=@u_gz);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_qy1,0,@u_fs,@now-3500,0 FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy1 AND user_id=@u_fs);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_gz1,0,@u_qy,@now-3400,0 FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz1 AND user_id=@u_qy);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_gz1,0,@u_fs,@now-3300,0 FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz1 AND user_id=@u_fs);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_fs1,0,@u_qy,@now-3200,0 FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs1 AND user_id=@u_qy);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '1',@d_fs1,0,@u_gz,@now-3100,0 FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs1 AND user_id=@u_gz);

SET @fp1 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy1 AND user_id=@u_gz LIMIT 1);
SET @fp2 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_qy1 AND user_id=@u_fs LIMIT 1);
SET @fp3 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz1 AND user_id=@u_qy LIMIT 1);
SET @fp4 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_gz1 AND user_id=@u_fs LIMIT 1);
SET @fp5 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs1 AND user_id=@u_qy LIMIT 1);
SET @fp6 = (SELECT id FROM fa_discover_favor WHERE typedata='1' AND discover_id=@d_fs1 AND user_id=@u_gz LIMIT 1);

INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '2',@d_qy1,@c1,@u_fs,@now-3000,0 FROM DUAL
WHERE @d_qy1 IS NOT NULL AND @c1 IS NOT NULL AND @u_fs IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c1 AND user_id=@u_fs);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '2',@d_gz1,@c4,@u_qy,@now-2900,0 FROM DUAL
WHERE @d_gz1 IS NOT NULL AND @c4 IS NOT NULL AND @u_qy IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c4 AND user_id=@u_qy);
INSERT INTO fa_discover_favor (typedata,discover_id,comment_id,user_id,createtime,weigh)
SELECT '2',@d_fs1,@c5,@u_gz,@now-2800,0 FROM DUAL
WHERE @d_fs1 IS NOT NULL AND @c5 IS NOT NULL AND @u_gz IS NOT NULL
  AND NOT EXISTS (SELECT 1 FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c5 AND user_id=@u_gz);

SET @fc1 = (SELECT id FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c1 AND user_id=@u_fs LIMIT 1);
SET @fc2 = (SELECT id FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c4 AND user_id=@u_qy LIMIT 1);
SET @fc3 = (SELECT id FROM fa_discover_favor WHERE typedata='2' AND comment_id=@c5 AND user_id=@u_gz LIMIT 1);

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_qy,@u_gz,'赞了你的作品',CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fp1,',"attention_id":"","comment_id":""}'),'0',@now-3600,@now-3600,@d_qy1 FROM DUAL
WHERE @fp1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fp1,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_qy,@u_fs,'赞了你的作品',CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fp2,',"attention_id":"","comment_id":""}'),'0',@now-3500,@now-3500,@d_qy1 FROM DUAL
WHERE @fp2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fp2,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz,@u_qy,'赞了你的作品',CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fp3,',"attention_id":"","comment_id":""}'),'0',@now-3400,@now-3400,@d_gz1 FROM DUAL
WHERE @fp3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fp3,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_gz,@u_fs,'赞了你的作品',CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fp4,',"attention_id":"","comment_id":""}'),'0',@now-3300,@now-3300,@d_gz1 FROM DUAL
WHERE @fp4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_gz AND create_id=@u_fs AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fp4,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_fs,@u_qy,'赞了你的作品',CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fp5,',"attention_id":"","comment_id":""}'),'0',@now-3200,@now-3200,@d_fs1 FROM DUAL
WHERE @fp5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_fs AND create_id=@u_qy AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fp5,',"attention_id":"","comment_id":""}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '1',@u_fs,@u_gz,'赞了你的作品',CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fp6,',"attention_id":"","comment_id":""}'),'0',@now-3100,@now-3100,@d_fs1 FROM DUAL
WHERE @fp6 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='1' AND user_id=@u_fs AND create_id=@u_gz AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fp6,',"attention_id":"","comment_id":""}'));

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '2',@u_gz,@u_fs,'赞了你的评论',CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fc1,',"attention_id":"","comment_id":',@c1,'}'),'0',@now-3000,@now-3000,@d_qy1 FROM DUAL
WHERE @fc1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='2' AND user_id=@u_gz AND create_id=@u_fs AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":',@fc1,',"attention_id":"","comment_id":',@c1,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '2',@u_fs,@u_qy,'赞了你的评论',CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fc2,',"attention_id":"","comment_id":',@c4,'}'),'0',@now-2900,@now-2900,@d_gz1 FROM DUAL
WHERE @fc2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='2' AND user_id=@u_fs AND create_id=@u_qy AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":',@fc2,',"attention_id":"","comment_id":',@c4,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '2',@u_qy,@u_gz,'赞了你的评论',CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fc3,',"attention_id":"","comment_id":',@c5,'}'),'0',@now-2800,@now-2800,@d_fs1 FROM DUAL
WHERE @fc3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='2' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":',@fc3,',"attention_id":"","comment_id":',@c5,'}'));

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_qy,@u_gz,'评论了你的作品',CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c1,'}'),'0',@now-5400,@now-5400,@d_qy1 FROM DUAL
WHERE @c1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c1,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_qy,@u_fs,'评论了你的作品',CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c2,'}'),'0',@now-5200,@now-5200,@d_qy1 FROM DUAL
WHERE @c2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c2,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz,@u_qy,'评论了你的作品',CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c3,'}'),'0',@now-5000,@now-5000,@d_gz1 FROM DUAL
WHERE @c3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c3,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_gz,@u_fs,'评论了你的作品',CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c4,'}'),'0',@now-4800,@now-4800,@d_gz1 FROM DUAL
WHERE @c4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_gz AND create_id=@u_fs AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c4,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_fs,@u_qy,'评论了你的作品',CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c5,'}'),'0',@now-4600,@now-4600,@d_fs1 FROM DUAL
WHERE @c5 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_fs AND create_id=@u_qy AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c5,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '3',@u_fs,@u_gz,'评论了你的作品',CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c6,'}'),'0',@now-4400,@now-4400,@d_fs1 FROM DUAL
WHERE @c6 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='3' AND user_id=@u_fs AND create_id=@u_gz AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c6,'}'));

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '5',@u_gz,@u_qy,'回复了你的评论',CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c1,'}'),'0',@now-5100,@now-5100,@d_qy1 FROM DUAL
WHERE @r1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='5' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":"","attention_id":"","comment_id":',@c1,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '5',@u_qy,@u_gz,'回复了你的评论',CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c3,'}'),'0',@now-4700,@now-4700,@d_gz1 FROM DUAL
WHERE @r2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='5' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":"","attention_id":"","comment_id":',@c3,'}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '5',@u_qy,@u_fs,'回复了你的评论',CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c5,'}'),'0',@now-4300,@now-4300,@d_fs1 FROM DUAL
WHERE @r3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='5' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":"","attention_id":"","comment_id":',@c5,'}'));

INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '4',@u_gz,@u_qy,'关注了你',CONCAT('{"discover_id":',@d_gz1,',"favor_id":0,"attention_id":',@a1,',"comment_id":0}'),'0',@now-4200,@now-4200,@d_gz1 FROM DUAL
WHERE @a1 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='4' AND user_id=@u_gz AND create_id=@u_qy AND discover_id=@d_gz1 AND remind=CONCAT('{"discover_id":',@d_gz1,',"favor_id":0,"attention_id":',@a1,',"comment_id":0}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '4',@u_qy,@u_gz,'关注了你',CONCAT('{"discover_id":',@d_qy1,',"favor_id":0,"attention_id":',@a2,',"comment_id":0}'),'0',@now-4100,@now-4100,@d_qy1 FROM DUAL
WHERE @a2 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='4' AND user_id=@u_qy AND create_id=@u_gz AND discover_id=@d_qy1 AND remind=CONCAT('{"discover_id":',@d_qy1,',"favor_id":0,"attention_id":',@a2,',"comment_id":0}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '4',@u_qy,@u_fs,'关注了你',CONCAT('{"discover_id":',@d_qy2,',"favor_id":0,"attention_id":',@a3,',"comment_id":0}'),'0',@now-4000,@now-4000,@d_qy2 FROM DUAL
WHERE @a3 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='4' AND user_id=@u_qy AND create_id=@u_fs AND discover_id=@d_qy2 AND remind=CONCAT('{"discover_id":',@d_qy2,',"favor_id":0,"attention_id":',@a3,',"comment_id":0}'));
INSERT INTO fa_discover_log (typedata,user_id,create_id,content,remind,readdata,createtime,updatetime,discover_id)
SELECT '4',@u_fs,@u_qy,'关注了你',CONCAT('{"discover_id":',@d_fs1,',"favor_id":0,"attention_id":',@a4,',"comment_id":0}'),'0',@now-3900,@now-3900,@d_fs1 FROM DUAL
WHERE @a4 IS NOT NULL AND NOT EXISTS (SELECT 1 FROM fa_discover_log WHERE typedata='4' AND user_id=@u_fs AND create_id=@u_qy AND discover_id=@d_fs1 AND remind=CONCAT('{"discover_id":',@d_fs1,',"favor_id":0,"attention_id":',@a4,',"comment_id":0}'));

UPDATE fa_discover d
SET d.favorNum = (
      SELECT COUNT(*) FROM fa_discover_favor f
      WHERE f.discover_id=d.id AND f.typedata='1'
    ),
    d.commentNum = (
      SELECT COUNT(*) FROM fa_discover_comment c
      WHERE c.discover_id=d.id AND c.comment_id=0 AND c.statusdata='1'
    ),
    d.updatetime = @now
WHERE d.id IN (@d_qy1,@d_qy2,@d_gz1,@d_gz2,@d_fs1,@d_fs2);