# INTERFACE_ENTRY_INDEX.md

配套背景文档：`PROJECT_CONTEXT.md`

本文只记录已经从仓库代码中核对过的入口、链路和高频修改文件，方便后续快速定位。

## 总入口

### HTTP / CLI

- `public/index.php`
  - 站点默认 Web 入口。
  - 检查安装锁后加载 ThinkPHP 启动文件。
- `public/GLIcHWTAnX.php`
  - 当前后台实际入口。
  - 会直接绑定 `admin` 模块。
- `think`
  - FastAdmin / ThinkPHP CLI 入口。
  - 常用命令注册在 `application/command.php`。

### UniApp

- `uniapp/main.js`
  - 挂载 `$api`、`$common`、`$db`、`$config`。
- `uniapp/pages.json`
  - 页面注册入口。
- `uniapp/config/config.js`
  - 前端站点地址、API 地址、CDN、Logo。
- `uniapp/config/api.js`
  - 所有前端接口封装与 token 头注入逻辑。

## 全局骨架文件

- `application/config.php`
  - 多模块开关、默认模块、禁止访问模块等。
- `application/database.php`
  - 数据库默认配置，实际值通常由 `.env` 覆盖。
- `application/route.php`
  - 全局路由骨架，当前内容较少。
- `application/tags.php`
  - 全局行为钩子注册。
- `application/command.php`
  - `php think` 命令注册列表。
- `application/extra/site.php`
  - 站点额外配置。
- `application/extra/upload.php`
  - 上传大小、类型、分片等配置。
- `application/extra/queue.php`
  - 队列配置。

## Discover 业务主链路

- `addons/discover/Discover.php`
  - 插件安装、卸载、启用、禁用入口。
- `addons/discover/config/menu.php`
  - 后台菜单定义来源。
- `addons/discover/config.php`
  - 插件业务配置；目前包含微信和腾讯地图相关项。
- `addons/discover/info.ini`
  - 插件元信息，版本为 `1.0.8`。
- `addons/discover/install.sql`
  - discover 业务表结构来源。

## API 控制器入口

### 通用 FastAdmin API

- `application/api/controller/Common.php`
  - 通用初始化、上传、验证码。
- `application/api/controller/User.php`
  - FastAdmin 默认用户中心相关 API。
- `application/api/controller/Token.php`
  - token 检查与刷新。
- `application/api/controller/Ems.php`
  - 邮件验证码相关。
- `application/api/controller/Sms.php`
  - 短信验证码相关。
- `application/api/controller/Validate.php`
  - 通用校验接口。
- `application/api/controller/Index.php`
  - 默认 API 首页。
- `application/api/controller/Demo.php`
  - 示例接口。

### Discover 自定义 API

- `application/api/controller/discover/Common.php`
  - 控制器入口：`index`、`type`、`detail`、`publish`、`locationData`、`showCommentLists`、`isFavor`、`getAccessToken`。
- `application/api/controller/discover/Base.php`
  - 真实业务实现集中地；包含：`indexData`、`attentionData`、`typeData`、`detailData`、`publishData`、`delData`、`doLike`、`doComment`、`doAttention`、`showMessageLists`、`doMessageRead`、`doLikeLists`、`doAttentionLists`、`doCommentLists`、`doMyDiscoverLists`、`userDataLists` 等。
- `application/api/controller/discover/User.php`
  - 自定义用户接口：`third`、`resetpwd`、`login`、`myGroup`、`register`、`index`、`logout`、`profile`、`refreshUser`、`changeMobile`、`sendSmsVerify`。
- `application/api/controller/discover/Ajax.php`
  - 辅助接口：`upload`、`lang`、`weigh`、`wipecache`、`category`、`area`、`icon`。
- `application/api/controller/discover/Basic.php`
  - 底层基类 / 辅助控制器，不是当前高频业务入口。

### 易混点

- 仓库里同时存在：
  - `application/api/controller/User.php`
  - `application/api/controller/discover/User.php`
- 修改登录、注册、资料时，先确认你改的是默认 FastAdmin API 还是 discover 自定义 API。
- `discover/Common/...` 路径下的不少接口，实际代码并不在 `Common.php`，而在其父类 `Base.php`。

## UniApp 页面与接口映射

### 已注册页面

- `pages/index/index`
- `pages/index/hot`
- `pages/index/detail`
- `pages/index/publish`
- `pages/plugin/index`
- `pages/user/index`
- `pages/user/message`
- `pages/user/attentions`
- `pages/user/myattentions`
- `pages/user/tag`
- `pages/user/login`
- `pages/user/register`
- `pages/user/bind`
- `pages/user/userInfo`

### 页面 -> API 调用

- `uniapp/pages/index/index.vue`
  - `locationAddress`
  - `typeData`
  - `indexData`
- `uniapp/pages/index/hot.vue`
  - `refreshUser`
  - `attentionData`
  - `dolikeData`
  - `doCommentData`
  - `doAttentionData`
- `uniapp/pages/index/detail.vue`
  - `doCommentListsData`
  - `detailData`
  - `dolikeData`
  - `doAttentionData`
  - `showCommentListsData`
  - `doCommentData`
- `uniapp/pages/index/publish.vue`
  - `refreshUser`
  - `typeData`
  - `publishData`
- `uniapp/pages/plugin/index.vue`
  - `locationAddress`
- `uniapp/pages/user/login.vue`
  - `login`
  - `third`
- `uniapp/pages/user/register.vue`
  - `sendSmsVerify`
  - `register`
  - `third`
- `uniapp/pages/user/bind.vue`
  - `third`
- `uniapp/pages/user/index.vue`
  - `refreshUser`
  - `doMyDiscoverListsData`
  - `logout`
  - `delData`
  - `userDataListsData`
- `uniapp/pages/user/message.vue`
  - `refreshUser`
  - `showMessageListsData`
  - `doMessageReadData`
- `uniapp/pages/user/myattentions.vue`
  - `doAttentionListsData`
  - `doLikeListsData`
- `uniapp/pages/user/userInfo.vue`
  - `refreshUser`
  - `profile`

### 存在但未注册到 `pages.json` 的页面

- `uniapp/pages/index/index2.vue`
- `uniapp/pages/plugin/indexes.vue`

## 后台 discover 管理链路

### 菜单来源

- `addons/discover/config/menu.php` 定义 discover 后台菜单。
- `addons/discover/Discover.php` 在安装插件时调用 `Menu::create()` 注册菜单。

### 后台 CRUD 文件族

每个 discover 资源基本遵循同一套文件分层：

- 控制器：`application/admin/controller/discover/*.php`
- 模型：`application/admin/model/discover/*.php`
- 校验：`application/admin/validate/discover/*.php`
- 视图：`application/admin/view/discover/*/*.html`
- 后台 JS：`public/assets/js/backend/discover/*.js`

### 已确认的资源

- `Discover`
- `Comment`
- `Favor`
- `Collect`
- `Attentions`
- `Log`
- `Tag`
- `Topic`

### 入口说明

- `application/admin/controller/discover/Index.php` 存在，但当前 `index()` 基本为空返回。
- 实际后台操作通常直接落到上面的各个 CRUD 控制器，而不是 `discover/Index`。

## 高频修改场景

### 1. 改 UniApp 接口域名 / CDN

优先看：

- `uniapp/config/config.js`
- `uniapp/config/api.js`

### 2. 加一个 discover 前台接口

通常要一起看：

- `application/api/controller/discover/Common.php`
- `application/api/controller/discover/Base.php`
- `application/api/controller/discover/User.php`
- `application/api/controller/discover/Ajax.php`
- `uniapp/config/api.js`
- 对应页面 `uniapp/pages/**/*.vue`

### 3. 改发布链路

通常涉及：

- `uniapp/pages/index/publish.vue`
- `uniapp/config/api.js`
- `application/api/controller/discover/Common.php`
- `application/api/controller/discover/Base.php`
- `application/api/controller/discover/Ajax.php`
- `application/extra/upload.php`
- `addons/discover/config.php`

### 4. 改 discover 后台列表 / 表单 / 搜索

通常涉及：

- `application/admin/controller/discover/<Resource>.php`
- `application/admin/model/discover/<Resource>.php`
- `application/admin/validate/discover/<Resource>.php`
- `application/admin/view/discover/<resource>/*.html`
- `public/assets/js/backend/discover/<resource>.js`

### 5. 改表结构

优先看：

- `addons/discover/install.sql`
- 相关后台模型 / API 逻辑 / UniApp 页面

注意：仓库内未看到 migration 体系，不要假设有自动迁移。

## 当前已发现的索引注意项

- `uniapp/config/api.js` 导出了 `bindphone -> discover/User/bind`，但当前后端未找到 `bind()` 方法实现。
- `uniapp/config/api.js` 导出了 `doCommentSubData`，而 `application/api/controller/discover/Base.php` 中只有被注释掉的 `doCommentSub()`。
- 如果后续联调时出现“前端有接口、后端 404/方法不存在”，优先回到本节检查映射是否失配。
