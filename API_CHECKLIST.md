# API_CHECKLIST.md

配套文档：`FEATURE_REQUIREMENTS.md`、`DEV_TASK_BREAKDOWN.md`、`DATA_MODEL_DRAFT.md`

本文用于列出后续开发需要新增或修改的接口清单，以及每类接口必须检查的权限与安全项。

## 一、通用接口原则

- 所有管理后台接口必须做服务端权限校验。
- 所有校级数据接口必须做学校归属校验。
- 所有新增写接口必须做输入校验、状态校验、日志记录。
- 不把外部同步凭据、群二维码原图地址、渠道密钥硬编码到仓库。

## 二、后台管理接口清单

### 1. 学校管理

建议新增：

- 学校列表
- 学校新增
- 学校编辑
- 学校启用/停用
- 学校删除（如允许）
- 学校搜索/选择器

必查项：

- 仅总账号可操作。
- 删除前校验是否已被管理员/帖子/广告引用。

### 2. 子管理账号管理

基于现有 `application/admin/controller/auth/Admin.php` 扩展：

- 子管理员创建
- 子管理员编辑
- 子管理员禁用/删除
- 子管理员重置密码
- 子管理员学校绑定写入

必查项：

- 只有总账号可新增、删除、改学校。
- 子管理员不能修改自己的学校绑定。
- 创建和修改动作都要写管理员日志。

### 3. discover 帖子管理

需要新增或改造：

- 帖子列表（按学校过滤）
- 帖子详情（按学校校验）
- 帖子审核通过
- 帖子审核驳回
- 帖子置顶
- 帖子取消置顶
- 帖子删除
- 批量操作

必查项：

- 子管理员只能操作自己学校帖子。
- 批量操作时逐条校验学校归属。
- 审核与置顶的状态流转要可追踪。

### 4. 首页广告活动管理

建议新增：

- 广告列表
- 广告新增
- 广告编辑
- 广告上下架
- 广告排序
- 广告学校范围配置

必查项：

- 校级广告只能作用于所属学校。
- 平台通用广告只能由总账号维护。
- 跳转参数做白名单校验。

### 5. 群二维码配置管理

建议新增：

- 群二维码列表
- 群二维码新增/编辑
- 展示策略配置
- 学校范围配置
- 平台默认群码配置

必查项：

- 图片上传走现有上传安全策略。
- 配置变更要可追踪。

## 三、小程序接口清单

### 1. 学校选择相关

建议新增：

- 根据定位推荐学校
- 学校搜索
- 获取当前学校详情
- 切换当前学校

必查项：

- 定位参数合法性校验。
- 拒绝定位时提供手动选择兜底。
- 切换学校后返回明确的当前学校信息。

### 2. 帖子发布与列表

需要改造：

- 发布帖子：必须带 `school_id` 或服务端可推导当前学校。
- 首页帖子列表：默认按当前学校过滤。
- 详情页：返回学校信息，便于前端展示。

必查项：

- 禁止前端提交任意学校 ID 绕过绑定策略。
- 如果用户学校为空，发布时给出明确错误。

### 3. 进群二维码弹窗

建议新增：

- 获取当前学校可用群二维码配置
- 记录弹窗曝光/关闭行为（可选）

必查项：

- 返回当前学校优先、平台默认回退的结果。
- 频率控制建议结合本地缓存与服务端配置共同实现。

### 4. 首页广告活动

建议新增：

- 获取首页广告列表
- 记录广告曝光/点击（可选）

必查项：

- 仅返回当前学校可见且在有效期内的广告。
- 跳转类型只允许白名单枚举值。

### 5. 发布同步

建议新增或内部触发：

- 同步任务触发接口/服务
- 同步日志查询接口（后台）
- 同步失败重试接口（后台）

必查项：

- 外部渠道失败时不应影响主发布事务是否成功，除非产品明确要求强一致。
- 失败响应中不泄露第三方密钥、token、签名串。

## 四、当前代码入口映射

### 后台账号管理

- `application/admin/controller/auth/Admin.php`
- `application/admin/model/Admin.php`
- `application/admin/model/AuthGroup.php`
- `application/admin/model/AuthRule.php`

### discover 后台管理

- `application/admin/controller/discover/Discover.php`
- `application/admin/model/discover/Discover.php`
- `application/admin/view/discover/discover/*.html`
- `public/assets/js/backend/discover/discover.js`

### discover API

- `application/api/controller/discover/Common.php`
- `application/api/controller/discover/Base.php`
- `application/api/controller/discover/User.php`
- `application/api/controller/discover/Ajax.php`
- `uniapp/config/api.js`

## 五、接口安全检查表

每次新增或修改接口时，至少检查：

- 是否有输入校验。
- 是否有登录校验。
- 是否有角色校验。
- 是否有学校归属校验。
- 是否有状态流转校验。
- 是否有操作日志。
- 是否避免信任前端传来的关键字段。
- 是否避免泄露密钥、token、内部错误堆栈。

## 六、当前已知的接口注意项

- 现有 discover 业务很多方法实际写在 `application/api/controller/discover/Base.php`，不是 `Common.php` 本体。
- `uniapp/config/api.js` 当前存在 `bindphone -> discover/User/bind` 映射，但后端未见 `bind()` 方法实现。
- `uniapp/config/api.js` 当前存在 `doCommentSubData` 导出，但后端对应方法是注释状态。
