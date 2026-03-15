# PROJECT_CONTEXT.md

配套索引文档：`INTERFACE_ENTRY_INDEX.md`

This file provides project context for coding agents working in this repository.

## 项目定位

- 仓库主体是 FastAdmin + ThinkPHP 多模块应用，同时带一套 `uniapp/` 移动端前端。
- 当前核心业务集中在插件 `addons/discover/`，插件版本为 `1.0.8`。
- Web 默认入口是 `public/index.php`。
- 后台隐藏入口是 `public/GLIcHWTAnX.php`，该文件直接绑定 `admin` 模块。
- 安装锁文件位于 `application/admin/command/Install/install.lock`。

## 目录速览

- `application/admin/`：后台管理模块。
- `application/api/`：API 模块；核心业务接口在 `application/api/controller/discover/`。
- `application/common/`：公共行为、公共库、模型基类等。
- `application/index/`：前台模块。
- `addons/discover/`：主要业务插件，含 `Discover.php`、`config.php`、`install.sql`。
- `uniapp/`：UniApp 移动端代码。
- `public/`：Web 可访问目录，静态资源在 `public/assets/`，上传文件在 `public/uploads/`。
- `application/extra/`：站点、上传、队列等额外配置。

## 已验证命令

```bash
composer install
npm install
npx grunt deploy
npx grunt
php think crud
php think menu
php think install
php think min
php think addon
php think api
```

- `.npmrc` 已配置腾讯镜像源。
- `Gruntfile.js` 中 `default` 任务会执行 `deploy`、前后台 JS 构建、前后台 CSS 构建。
- CLI 命令入口是根目录 `think`，命令清单定义在 `application/command.php`。

## 后端架构

- `application/config.php` 开启多模块：`app_multi_module = true`。
- 默认模块是 `index`。
- 禁止直接访问的模块包含 `common` 和 `admin`。
- `application/route.php` 目前基本是空白骨架，项目主要依赖控制器路径和默认路由解析。

## Discover API 约定

- 控制器目录：`application/api/controller/discover/`
  - `Base.php`：基础能力和大量 discover 业务方法实现。
  - `Basic.php`：较底层的基础控制器。
  - `Common.php`：首页、详情、发布、评论列表、定位等控制器入口。
  - `User.php`：登录、注册、第三方登录、资料、换绑手机等接口。
  - `Ajax.php`：上传等辅助接口。
- UniApp 侧通过 `uniapp/config/api.js` 组装 URL，常见格式为：
  - `baseApiUrl + 'discover/Common/index'`
  - `baseApiUrl + 'discover/User/login'`
- API 请求默认使用 `POST`。
- 登录态通过请求头 `token` 传递，不是 `Authorization`。
- 需要登录的接口列表维护在 `uniapp/config/api.js` 的 `methodsToken` 中。
- `application/api/controller/discover/Base.php` 已显式允许跨域请求头 `token`。
- 注意：很多以 `discover/Common/...` 暴露出来的接口，实际实现写在 `Base.php` 中，而不是 `Common.php` 本体里。

## 插件与数据表

- `addons/discover/install.sql` 创建 8 张核心业务表：
  - `discover`
  - `discover_attentions`
  - `discover_collect`
  - `discover_comment`
  - `discover_favor`
  - `discover_log`
  - `discover_tag`
  - `discover_topic`
- 仓库内未看到 migration 体系；插件 schema 主要通过 `install.sql` 管理。
- 插件安装/启停/卸载入口在 `addons/discover/Discover.php`。
- 后台菜单注册定义在 `addons/discover/config/menu.php`。

## UniApp 前端

- `uniapp/main.js` 将 `$api`、`$common`、`$db`、`$config` 挂到 `Vue.prototype`。
- `uniapp/config/config.js` 当前仍是 `example.com` 占位配置，联调前必须改成真实域名和 API 地址。
- 仓库中已确认存在并使用 `ColorUI`、`uv-waterfall`、`uv-parse`。
- 页面路由入口在 `uniapp/pages.json`。
- 配套页面/API/后台索引见 `INTERFACE_ENTRY_INDEX.md`。

## 关键配置

- `application/database.php`
  - 默认数据库类型是 `mysql`
  - 默认数据库名是 `fastadmin`
  - 默认表前缀是 `fa_`
  - 默认字符集是 `utf8mb4`
  - 实际连接参数以 `.env` 为准，不要把本地环境值写死到仓库级文档
- `application/extra/upload.php`
  - 默认上传入口是 `ajax/upload`
  - 默认最大上传大小是 `10mb`
  - 默认 `chunking = false`
- `addons/discover/config.php`
  - 管理微信和腾讯地图相关配置
  - 修改前先确认当前值是示例值、脱敏值还是实际运行值

## 操作注意

- Web 服务器根目录必须指向 `public/`。
- `runtime/` 与 `public/uploads/` 需要可写权限。
- PHP 版本要求 `>= 7.4`，扩展至少包含 `ext-json`、`ext-curl`、`ext-pdo`、`ext-bcmath`。
- 在 PowerShell 中查看中文文件时，优先使用 UTF-8 输出，避免把终端乱码误判成文件损坏。
- 本仓库同时存在两套 API 用户控制器：`application/api/controller/User.php` 与 `application/api/controller/discover/User.php`，修改登录注册相关逻辑时不要混淆。

## 文档边界

- 只记录已从仓库中验证过的结构、入口、命令和配置。
- 不把 `.env` 中的数据库名、账号、密钥等环境特定信息写入仓库级文档。
- 如果后续新增模块、入口或构建脚本，优先同步更新本文件与 `INTERFACE_ENTRY_INDEX.md`。
