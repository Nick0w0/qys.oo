# DATA_MODEL_DRAFT.md

配套文档：`FEATURE_REQUIREMENTS.md`、`DEV_TASK_BREAKDOWN.md`

本文是当前需求的一版数据模型草案，重点服务于“一账号一学校，技术上可扩展”的实现方案。

## 一、现状判断

### 已存在结构

- 后台管理员使用 FastAdmin 现有管理员体系：`Admin`、`AuthGroup`、`AuthRule`。
- discover 主表当前已有位置相关字段：`province`、`city`、`area`、`address`、`latlng`。
- discover 主表当前没有明确学校归属字段。
- discover 主表当前没有独立的审核状态与置顶状态字段。

### 设计原则

- 一期业务限制：一个子管理账号只能绑定一个学校。
- 技术扩展原则：底层仍使用关系表设计，后续可扩展到多学校。
- 权限控制原则：学校范围必须由服务端判断，不依赖前端传参可信。

## 二、建议新增数据表

### 1. `fa_school`

建议字段：

- `id`
- `name`
- `short_name`
- `province`
- `city`
- `area`
- `address`
- `latitude`
- `longitude`
- `status`（启用/禁用）
- `weigh`
- `createtime`
- `updatetime`

建议索引：

- `idx_city_status`
- `idx_name`
- `idx_lat_lng`（如果后续做邻近查询，可视数据库能力调整）

### 2. `fa_admin_school`

用途：管理员与学校关系表。

建议字段：

- `id`
- `admin_id`
- `school_id`
- `is_primary`（一期固定为 1）
- `status`
- `createtime`
- `updatetime`
- `created_by`
- `updated_by`

建议约束：

- 一期增加唯一约束，确保一个管理员只有一个有效学校绑定。
- 后续如开放多学校，可取消唯一约束，仅保留状态控制。

建议索引：

- `uniq_admin_active_school`（一期）
- `idx_school_admin`

### 3. `fa_discover_sync_log`

用途：记录帖子同步到群聊/平台的执行结果。

建议字段：

- `id`
- `discover_id`
- `school_id`
- `channel_type`
- `channel_target`
- `request_payload`
- `response_payload`
- `sync_status`
- `error_message`
- `retry_count`
- `createtime`
- `updatetime`

### 4. `fa_school_group_qrcode`

用途：按学校配置进群二维码。

建议字段：

- `id`
- `school_id`（平台默认群码可允许为空或 0）
- `title`
- `description`
- `image`
- `popup_strategy`
- `popup_interval`
- `status`
- `starttime`
- `endtime`
- `weigh`
- `createtime`
- `updatetime`

### 5. `fa_home_banner`

用途：首页广告/活动位。

建议字段：

- `id`
- `school_id`（平台通用活动可允许为空或 0）
- `title`
- `image`
- `jump_type`
- `jump_value`
- `status`
- `starttime`
- `endtime`
- `weigh`
- `clicks`
- `views`
- `createtime`
- `updatetime`

## 三、建议调整现有表

### 1. `fa_discover`

建议新增字段：

- `school_id`：帖子所属学校
- `audit_status`：待审核/已通过/已拒绝
- `audit_admin_id`：最后审核人
- `audit_time`：审核时间
- `audit_remark`：驳回原因或审核备注
- `is_top`：是否置顶
- `top_sort`：置顶排序
- `top_expire_time`：置顶失效时间，可选
- `sync_status`：最近一次同步状态，可选

不建议做法：

- 不建议继续把审核语义塞进 `statusdata`。
- 不建议把学校信息只写在 `city/province/area` 文本字段里代替 `school_id`。

建议索引：

- `idx_school_audit_create`（`school_id`, `audit_status`, `createtime`）
- `idx_school_top`（`school_id`, `is_top`, `top_sort`）
- `idx_user_school`（`user_id`, `school_id`）

### 2. `fa_user` 或用户扩展表

如果普通用户需要长期绑定当前学校，建议二选一：

产品规则已经明确：每个用户只能绑定一个学校，普通用户绑定后不可自助修改，如需修改只能由后台人工处理。

- 直接在用户表新增 `school_id`、`school_confirm_time`、`school_locked`
- 或新增用户学校关系表/用户配置表

一期若只需要单学校，直接加 `school_id` 会更快；同时建议增加“已确认绑定/是否锁定”的状态字段，支撑前端不可自助修改和后台人工改绑日志。
如果后续存在跨校身份，再拆关系表。

## 四、权限与数据边界设计

### 管理员边界

- 总账号：admin 超级管理员，不受学校范围限制。
- 子管理员：必须通过 `fa_admin_school` 查出所属学校后再附加查询条件。
- 所有 discover 写操作都要二次校验目标记录的 `school_id` 是否等于当前管理员学校。

### 推荐的权限校验顺序

1. 先判断是否超级管理员。
2. 若不是，查询当前管理员绑定学校。
3. 对目标帖子/广告/群码记录读取 `school_id`。
4. 两者不一致则直接拒绝。
5. 记录日志。

### 安全注意点

- 不信任前端传来的 `school_id`。
- 搜索接口、批量接口、导出接口都必须补学校条件。
- 管理员学校绑定的新增/变更只能由总账号执行。

## 五、迁移建议

### 一期最低改动路径

- 新建 `fa_school`
- 新建 `fa_admin_school`
- 给 `fa_discover` 增加 `school_id`、`audit_status`、`is_top`
- 新建 `fa_school_group_qrcode`
- 新建 `fa_home_banner`
- 如同步需要持久化，再新建 `fa_discover_sync_log`

### 数据回填建议

- 老帖子先按城市/人工规则映射学校，无法确定的进入“待分配学校”状态。
- 老管理员若需转为子管理员，必须先绑定学校后再启用校级权限。

## 六、当前推荐结论

- 产品规则：一账号一学校。
- 技术实现：管理员-学校关系表。
- 原因：一期权限边界最清晰，同时避免后续从单字段迁移到关系表的二次改造成本。
