# BACKEND_MENU_HIDDEN_INDEX.md

本文记录当前后台被隐藏的菜单，方便后续需要恢复时快速找回。

更新时间：2026-03-18

关联脚本：

- `sql/20260318_backend_menu_simplify.sql`
- `sql/20260318_backend_menu_refine.sql`
- `sql/20260318_review_menu_cleanup.sql`

## 一、当前保留的后台结构

当前后台按项目需求，保留为下面这套结构：

- `内容治理`
  - `评论管理`
  - `帖子管理`
  - `屏蔽词管理`
- `用户管理`
  - `用户列表`
- `学校运营`
  - `学校管理`
- `运营中心`
  - `群二维码`
  - `首页广告位`
- `账号管理`
  - `子账号管理`

## 二、当前隐藏的一级菜单

以下一级菜单已隐藏：

- `dashboard` → `控制台`
- `general` → `常规管理`
- `addon` → `插件管理`
- `third` → `第三方登录管理`
- `wechat` → `微信管理`
- `discover` → `发现种草`

说明：

- `发现种草` 不是彻底不用，而是已拆成独立一级菜单：
  - `内容治理`
  - `学校运营`
  - `运营中心`
- `user` 已恢复，但不是恢复整套老会员后台，而是收敛成轻量 `用户管理`

## 三、当前隐藏的二级/权限菜单

### 1. 账号体系里被隐藏的菜单

- `auth/adminlog` → 管理员日志
- `auth/group` → 角色组
- `auth/rule` → 菜单规则

说明：

- 现在只保留 `auth` 和 `auth/admin`
- 即只保留：
  - `账号管理`
  - `子账号管理`

### 2. 控制台下隐藏的权限节点

- `dashboard/index`
- `dashboard/add`
- `dashboard/edit`
- `dashboard/del`
- `dashboard/multi`

### 3. 常规管理下隐藏的菜单与权限节点

- `general/config`
- `general/attachment`
- `general/profile`
- 以及它们所有子节点：
  - `general/config/*`
  - `general/attachment/*`
  - `general/profile/*`

### 4. 插件管理下隐藏的菜单与权限节点

- `addon/index`
- `addon/add`
- `addon/edit`
- `addon/del`
- `addon/downloaded`
- `addon/state`
- `addon/config`
- `addon/refresh`
- `addon/multi`

### 5. 用户体系里当前隐藏的菜单

- `user/group` → 用户分组
- `user/rule` → 用户规则
- 以及它们所有子节点：
  - `user/group/*`
  - `user/rule/*`

说明：

- 当前没有恢复旧的整套会员后台
- 只恢复了一个轻量入口：
  - `user` → `用户管理`
  - `user/user` → `用户列表`

### 6. 第三方登录管理下隐藏的菜单与权限节点

- `third/index`
- `third/del`

### 7. 微信管理下隐藏的菜单与权限节点

- `wechat/autoreply`
- `wechat/config`
- `wechat/menu`
- `wechat/response`
- 以及它们所有子节点：
  - `wechat/autoreply/*`
  - `wechat/config/*`
  - `wechat/menu/*`
  - `wechat/response/*`

### 8. 内容治理里当前隐藏的菜单

- `discover/tag` → 标签管理
- `discover/topic` → 话题管理
- 以及它们所有子节点：
  - `discover/tag/*`
  - `discover/topic/*`

## 四、为什么隐藏这些菜单

当前需求明确聚焦于：

- 子账号只能管理本校帖子审核 / 置顶 / 删除
- 用户按地理位置选择学校
- 后台需要能查用户
- 首页广告位
- 进群二维码
- 总账号维护子管理员
- 后续预留发布同步

因此，这一版后台故意做了收敛：

- 去掉和当前交付无关的通用系统菜单
- 去掉容易误操作的权限规则菜单
- 去掉和当前业务链路无关的微信/第三方后台入口
- 把内容治理、用户、学校运营拆成独立主线

## 五、子管理员权限当前规则

当前这两个权限组都已经收紧为只保留帖子治理主链路：

- `学校管理员`
- `学校审核员`

当前规则为：

- `85` → `discover`
- `153` → `discover/content`
- `107` → `discover/discover`
- `109` → `discover/discover/index`
- `111` → `discover/discover/edit`
- `112` → `discover/discover/del`
- `113` → `discover/discover/multi`

说明：

- `edit` 用于帖子审核与置顶
- `del` 用于删除
- `multi` 用于批量审核/批量置顶/批量删除
- 子管理员当前仍不包含用户管理权限

## 六、后续如何恢复隐藏菜单

### 方案 A：恢复单个菜单

例如恢复 `控制台`：

```sql
UPDATE fa_auth_rule SET status = 'normal' WHERE name = 'dashboard';
UPDATE fa_auth_rule SET status = 'normal' WHERE name LIKE 'dashboard/%';
```

例如恢复 `微信管理`：

```sql
UPDATE fa_auth_rule SET status = 'normal' WHERE name = 'wechat';
UPDATE fa_auth_rule SET status = 'normal' WHERE name LIKE 'wechat/%';
```

例如恢复 `插件管理`：

```sql
UPDATE fa_auth_rule SET status = 'normal' WHERE name = 'addon';
UPDATE fa_auth_rule SET status = 'normal' WHERE name LIKE 'addon/%';
```

### 方案 B：恢复发现种草父菜单

如果后续又想把 `内容治理 / 学校运营 / 运营中心` 收回 `发现种草` 下面：

```sql
UPDATE fa_auth_rule SET status = 'normal' WHERE name = 'discover';
UPDATE fa_auth_rule SET pid = 85, weigh = 0 WHERE name = 'discover/content';
UPDATE fa_auth_rule SET pid = 85, weigh = 0 WHERE name = 'discover/schoolops';
UPDATE fa_auth_rule SET pid = 85, weigh = 0 WHERE name = 'discover/operation';
```

### 方案 C：恢复权限管理全套菜单

```sql
UPDATE fa_auth_rule
SET status = 'normal'
WHERE name IN ('auth/adminlog', 'auth/group', 'auth/rule');

UPDATE fa_auth_rule
SET status = 'normal'
WHERE name LIKE 'auth/adminlog/%'
   OR name LIKE 'auth/group/%'
   OR name LIKE 'auth/rule/%';
```

### 方案 D：恢复标签 / 话题管理

```sql
UPDATE fa_auth_rule
SET status = 'normal'
WHERE name IN ('discover/tag', 'discover/topic');

UPDATE fa_auth_rule
SET status = 'normal'
WHERE name LIKE 'discover/tag/%'
   OR name LIKE 'discover/topic/%';
```

### 方案 E：恢复完整会员体系

```sql
UPDATE fa_auth_rule
SET status = 'normal'
WHERE name IN ('user/group', 'user/rule');

UPDATE fa_auth_rule
SET status = 'normal'
WHERE name LIKE 'user/group/%'
   OR name LIKE 'user/rule/%';
```

## 七、恢复后要做什么

不管恢复哪一类菜单，恢复后都建议做两件事：

### 1. 清菜单缓存

重点是运行缓存里的菜单树缓存。

### 2. 退出后台重新登录

因为当前登录态可能还带着旧菜单树。

## 八、建议

后续如果再加后台功能，优先加到这几块里，不要再堆回大杂烩结构：

- `内容治理`
- `用户管理`
- `学校运营`
- `运营中心`

只有当某一类功能足够独立、且长期会持续扩展时，才新增新的一级菜单。
