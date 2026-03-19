SET NAMES utf8mb4;
SET @now = UNIX_TIMESTAMP();

DELETE FROM fa_home_banner WHERE title LIKE '【演示】%';
DELETE FROM fa_school_group_qrcode WHERE title LIKE '【演示】%';
DELETE FROM fa_discover WHERE title LIKE '【演示】%';
DELETE FROM fa_user WHERE mobile IN ('13900001235','13900001236','13900001237');

UPDATE fa_school
SET name='清远校区',
    short_name='清远',
    province='广东省',
    city='清远市',
    area='清城区',
    address='清远市清城区演示路1号',
    latitude='23.682000',
    longitude='113.056000',
    status='normal',
    weigh=100,
    updatetime=@now
WHERE id=1;

INSERT INTO fa_school (id,name,short_name,province,city,area,address,latitude,longitude,status,weigh,createtime,updatetime)
VALUES
(2,'广州校区','广州','广东省','广州市','番禺区','广州市番禺区演示大道8号','23.043024','113.379321','normal',90,@now,@now),
(3,'佛山校区','佛山','广东省','佛山市','南海区','佛山市南海区演示路18号','23.028762','113.143319','normal',80,@now,@now)
ON DUPLICATE KEY UPDATE
name=VALUES(name),
short_name=VALUES(short_name),
province=VALUES(province),
city=VALUES(city),
area=VALUES(area),
address=VALUES(address),
latitude=VALUES(latitude),
longitude=VALUES(longitude),
status=VALUES(status),
weigh=VALUES(weigh),
updatetime=VALUES(updatetime);

INSERT INTO fa_user (
  group_id,school_id,school_confirm_time,school_locked,username,nickname,password,salt,email,mobile,avatar,
  level,gender,bio,money,score,successions,maxsuccessions,jointime,createtime,updatetime,token,status,verification
)
VALUES
(
  1,1,@now,'1','13900001235','清远学长',MD5(CONCAT(MD5('Demo@2026!35'),'dm35qA')),'dm35qA','', '13900001235','/assets/img/avatar.png',
  1,1,'清远校区日常分享','0.00',0,1,1,@now,@now,@now,'','normal',''
),
(
  1,2,@now,'1','13900001236','广州学姐',MD5(CONCAT(MD5('Demo@2026!36'),'dm36qB')),'dm36qB','', '13900001236','/assets/img/avatar.png',
  1,2,'广州校区活动情报员','0.00',0,1,1,@now,@now,@now,'','normal',''
),
(
  1,3,@now,'1','13900001237','佛山同学',MD5(CONCAT(MD5('Demo@2026!37'),'dm37qC')),'dm37qC','', '13900001237','/assets/img/avatar.png',
  1,1,'佛山校区生活记录','0.00',0,1,1,@now,@now,@now,'','normal',''
);

INSERT INTO fa_discover (
  title,description,coverimage,coverimages,tag_ids,top_ids,content,browse,createtime,updatetime,weigh,statusdata,
  province,city,area,address,latlng,adddata,iscommentdata,user_id,school_id,favorNum,commentNum,
  audit_status,audit_admin_id,audit_time,is_top,top_sort
)
VALUES
(
  '【演示】清远校区图书馆自习攻略',
  '新学期第一周自习位和插座分布整理好了。',
  '/assets/img/login-head.png',
  '/assets/img/login-head.png,/assets/img/logo.png',
  '3','',
  '图书馆二楼靠窗位置最安静，晚上七点后空位会明显变少。建议下午四点前到馆，顺手带一件薄外套，空调比较足。',
  126,@now-3600,@now-3600,300,'1',
  '广东省','清远市','清城区','清远校区图书馆','23.682000,113.056000','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1),1,32,8,
  'approved',0,@now-3500,'1',999
),
(
  '【演示】清远校区食堂晚餐测评',
  '三家窗口实测，人均 12-18 元。',
  '/assets/addons/wechat/images/mobile_index.png',
  '/assets/addons/wechat/images/mobile_index.png,/assets/img/logo.png',
  '1','',
  '一楼盖浇饭稳定，二楼砂锅出餐慢但味道最好。想省钱可以直接冲套餐窗口，晚上七点半后部分菜品会打折。',
  89,@now-7200,@now-7200,260,'1',
  '广东省','清远市','清城区','清远校区第一食堂','23.681300,113.055100','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1),1,24,6,
  'approved',0,@now-7100,'0',0
),
(
  '【演示】清远校区操场夜跑记录',
  '夜跑 5 公里，顺便拍了个短 Vlog。',
  '/assets/img/logo.png',
  '/assets/img/logo.png,/assets/img/login-head.png',
  '5','',
  '八点后的操场氛围很好，灯光也足，跑完还能顺路去超市买水。想坚持运动的话，找个固定搭子会更容易。',
  68,@now-10800,@now-10800,220,'1',
  '广东省','清远市','清城区','清远校区运动场','23.680800,113.057200','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1),1,18,4,
  'approved',0,@now-10700,'0',0
),
(
  '【演示】广州校区周末打卡路线',
  '地铁可达，适合半天放松。',
  '/assets/addons/wechat/images/mobile_index.png',
  '/assets/addons/wechat/images/mobile_index.png,/assets/img/login-head.png',
  '2','',
  '从学校出发先去江边，再去创意园，晚上回校刚好。预算 50 元左右就够，适合两三个人一起慢慢逛。',
  143,@now-14400,@now-14400,280,'1',
  '广东省','广州市','番禺区','广州校区北门集合','23.043024,113.379321','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1),2,35,10,
  'approved',0,@now-14300,'1',980
),
(
  '【演示】广州校区宿舍桌面改造',
  '百元以内把桌面整理得更顺手。',
  '/assets/img/login-head.png',
  '/assets/img/login-head.png,/assets/img/logo.png',
  '6','',
  '重点不是买很多收纳，而是先把每天高频使用的东西固定位置。建议先处理插线板和台灯，不然桌面永远看起来很乱。',
  77,@now-18000,@now-18000,210,'1',
  '广东省','广州市','番禺区','广州校区学生宿舍','23.042500,113.380210','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1),2,21,5,
  'approved',0,@now-17900,'0',0
),
(
  '【演示】广州校区奶茶轻测评',
  '教学楼周边三家饮品店口味记录。',
  '/assets/img/logo.png',
  '/assets/img/logo.png,/assets/addons/wechat/images/mobile_index.png',
  '1','',
  '推荐少糖少冰，晚高峰排队时间会明显增加。自习前买一杯还行，别空腹喝。',
  55,@now-21600,@now-21600,180,'1',
  '广东省','广州市','番禺区','广州校区商业街','23.041910,113.378620','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1),2,15,3,
  'approved',0,@now-21500,'0',0
),
(
  '【演示】佛山校区自习室座位观察',
  '上午和晚上座位使用差异挺明显。',
  '/assets/img/login-head.png',
  '/assets/img/login-head.png,/assets/addons/wechat/images/mobile_index.png',
  '3','',
  '早上八点到十点位置最好找，晚上七点半后靠插座区域基本坐满。想长时间学习，建议自己带个坐垫。',
  96,@now-25200,@now-25200,240,'1',
  '广东省','佛山市','南海区','佛山校区自习室','23.028762,113.143319','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001237' LIMIT 1),3,27,7,
  'approved',0,@now-25100,'1',970
),
(
  '【演示】佛山校区毕业季随手记',
  '最近校园里拍照的人越来越多了。',
  '/assets/addons/wechat/images/mobile_index.png',
  '/assets/addons/wechat/images/mobile_index.png,/assets/img/logo.png',
  '4','',
  '教学楼、操场和图书馆门口都很适合拍毕业照。赶在傍晚去，光线会柔和很多，也不会太热。',
  61,@now-28800,@now-28800,170,'1',
  '广东省','佛山市','南海区','佛山校区主教学楼','23.029110,113.144002','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001237' LIMIT 1),3,17,4,
  'approved',0,@now-28700,'0',0
),
(
  '【演示】清远校区快递站避坑提醒',
  '纯文字提醒帖示例，无配图。',
  '',
  '',
  '6','',
  '这两天快递站门口排队会比较久，尤其是下午四点半到六点。着急拿件的话建议错峰，或者先在小程序确认一下是不是已经上架，免得白跑一趟。',
  58,@now-32400,@now-32400,160,'1',
  '广东省','清远市','清城区','清远校区快递站','23.681900,113.055600','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001235' LIMIT 1),1,11,2,
  'approved',0,@now-32300,'0',0
),
(
  '【演示】广州校区自习室空调有点低',
  '纯文字经验分享示例。',
  '',
  '',
  '3','',
  '晚上在三楼自习室待久了会有点冷，建议带件薄外套。靠窗那排风口更明显，如果只是短时间学习，坐中间区域会舒服一点。',
  47,@now-36000,@now-36000,150,'1',
  '广东省','广州市','番禺区','广州校区公共教学楼','23.042880,113.379760','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1),2,9,1,
  'approved',0,@now-35900,'0',0
),
(
  '【演示】佛山校区周末安静角落推荐',
  '不带图的生活类帖子示例。',
  '',
  '',
  '2','',
  '如果只是想一个人安静坐会儿，图书馆侧门外的小花坛那边其实很舒服，下午人不多，风也比较大。带本书或者耳机，待半小时会很放松。',
  52,@now-39600,@now-39600,145,'1',
  '广东省','佛山市','南海区','佛山校区图书馆侧门','23.028960,113.143680','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001237' LIMIT 1),3,12,2,
  'approved',0,@now-39500,'0',0
),
(
  '【演示】广州校区二手转让小提醒',
  '无图二手帖示例。',
  '',
  '',
  '4','',
  '出闲置的时候，标题里最好直接写清楚物品名、成色和取货方式。没有图片也能发，但文字尽量说完整一点，别人判断起来会更快。',
  41,@now-43200,@now-43200,140,'1',
  '广东省','广州市','番禺区','广州校区生活区','23.042210,113.378950','1','1',
  (SELECT id FROM fa_user WHERE mobile='13900001236' LIMIT 1),2,8,1,
  'approved',0,@now-43100,'0',0
);

UPDATE fa_discover_tag t
SET t.number = (
  SELECT COUNT(*) FROM fa_discover d WHERE FIND_IN_SET(t.id, d.tag_ids)
)
WHERE t.id IN (1,2,3,4,5,6);

INSERT INTO fa_school_group_qrcode (
  school_id,title,description,image,popup_strategy,popup_interval,status,starttime,endtime,weigh,createtime,updatetime
)
VALUES
(0,'【演示】平台交流群','未绑定学校时显示的平台默认群码。','/assets/img/qrcode.png','daily',1,'normal',@now-86400,0,100,@now,@now),
(1,'【演示】清远校区新生群','清远校区同学可先扫码入群，后续公告会同步在群里发。','/assets/img/qrcode.png','daily',1,'normal',@now-86400,0,200,@now,@now),
(2,'【演示】广州校区活动群','广州校区活动、闲置和通知都可以先在群里同步。','/assets/img/qrcode.png','daily',1,'normal',@now-86400,0,190,@now,@now),
(3,'【演示】佛山校区交流群','佛山校区学习互助和活动通知演示群。','/assets/img/qrcode.png','daily',1,'normal',@now-86400,0,180,@now,@now);

SET @d_qy = COALESCE((SELECT CAST(id AS CHAR) FROM fa_discover WHERE title='【演示】清远校区图书馆自习攻略' LIMIT 1), '0');
SET @d_gz = COALESCE((SELECT CAST(id AS CHAR) FROM fa_discover WHERE title='【演示】广州校区周末打卡路线' LIMIT 1), '0');
SET @d_fs = COALESCE((SELECT CAST(id AS CHAR) FROM fa_discover WHERE title='【演示】佛山校区自习室座位观察' LIMIT 1), '0');

INSERT INTO fa_home_banner (
  school_id,title,image,jump_type,jump_value,status,starttime,endtime,weigh,clicks,views,createtime,updatetime
)
VALUES
(0,'【演示】先选学校，再看本校内容','/assets/addons/wechat/images/mobile_index.png','path','/pages/plugin/index','normal',@now-86400,0,300,0,0,@now,@now),
(0,'【演示】平台活动位展示','/assets/img/login-head.png','none','','normal',@now-86400,0,280,0,0,@now,@now),
(1,'【演示】清远校区热门帖子','/assets/img/logo.png','discover',@d_qy,'normal',@now-86400,0,260,0,0,@now,@now),
(2,'【演示】广州校区周末路线推荐','/assets/img/login-head.png','discover',@d_gz,'normal',@now-86400,0,250,0,0,@now,@now),
(3,'【演示】佛山校区自习室推荐','/assets/addons/wechat/images/mobile_index.png','discover',@d_fs,'normal',@now-86400,0,240,0,0,@now,@now);
