<?php

namespace app\admin\controller\auth;

use app\admin\model\AdminSchool;
use app\admin\model\AuthGroup;
use app\admin\model\AuthGroupAccess;
use app\admin\model\AuthRule;
use app\admin\model\discover\School;
use app\common\controller\Backend;
use fast\Random;
use fast\Tree;
use think\Db;
use think\Validate;

class Admin extends Backend
{
    protected $model = null;
    protected $selectpageFields = 'id,username,nickname,avatar';
    protected $searchFields = 'id,username,nickname';
    protected $childrenGroupIds = [];
    protected $childrenAdminIds = [];
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = model('Admin');

        $this->childrenAdminIds = $this->auth->getChildrenAdminIds($this->auth->isSuperAdmin());
        $this->childrenGroupIds = $this->auth->getChildrenGroupIds($this->auth->isSuperAdmin());

        $groupList = collection(AuthGroup::where('id', 'in', $this->childrenGroupIds)->select())->toArray();
        Tree::instance()->init($groupList);
        $groupdata = [];
        if ($this->auth->isSuperAdmin()) {
            $result = Tree::instance()->getTreeList(Tree::instance()->getTreeArray(0));
            foreach ($result as $item) {
                $groupdata[$item['id']] = $item['name'];
            }
        } else {
            $result = [];
            $groups = $this->auth->getGroups();
            foreach ($groups as $group) {
                $childlist = Tree::instance()->getTreeList(Tree::instance()->getTreeArray($group['id']));
                $temp = [];
                foreach ($childlist as $item) {
                    $temp[$item['id']] = $item['name'];
                }
                $result[__($group['name'])] = $temp;
            }
            $groupdata = $result;
        }

        $this->view->assign('groupdata', $groupdata);
        $this->view->assign('schooldata', $this->getSchoolData());
        $this->view->assign('roletemplatedata', $this->getRoleTemplateOptions());
        $this->assignconfig('admin', ['id' => $this->auth->id, 'isSuperAdmin' => $this->auth->isSuperAdmin()]);
        if ($this->auth->isSuperAdmin()) {
            $this->syncRoleTemplateGroups();
        }
    }

    protected function hasTable($table)
    {
        $key = 'table:' . $table;
        if (!array_key_exists($key, self::$schemaCache)) {
            $fullTable = config('database.prefix') . $table;
            $result = Db::query("SHOW TABLES LIKE '" . addslashes($fullTable) . "'");
            self::$schemaCache[$key] = !empty($result);
        }
        return self::$schemaCache[$key];
    }

    protected function hasSchoolSchema()
    {
        return $this->hasTable('school') && $this->hasTable('admin_school');
    }

    protected function getSchoolData()
    {
        if (!$this->hasTable('school')) {
            return [];
        }
        return School::order('weigh desc,id desc')->column('name', 'id');
    }

    protected function getAdminSchoolMap(array $adminIds)
    {
        if (!$adminIds || !$this->hasTable('admin_school')) {
            return [];
        }
        return AdminSchool::where('admin_id', 'in', $adminIds)
            ->where('status', 'normal')
            ->column('school_id', 'admin_id');
    }

    protected function bindSchool($adminId, $schoolId)
    {
        if (!$this->hasSchoolSchema()) {
            return;
        }
        AdminSchool::where('admin_id', $adminId)->delete();
        if ($schoolId > 0) {
            AdminSchool::create([
                'admin_id'    => $adminId,
                'school_id'   => $schoolId,
                'status'      => 'normal',
                'is_primary'  => '1',
                'created_by'  => $this->auth->id,
                'updated_by'  => $this->auth->id,
                'createtime'  => time(),
                'updatetime'  => time(),
            ]);
        }
    }

    protected function validateSchoolId($schoolId, $adminId = 0)
    {
        if (!$this->hasSchoolSchema()) {
            exception(__('Please run phase1 sql first'));
        }
        $schoolId = (int)$schoolId;
        if ($schoolId <= 0) {
            if ($adminId && $this->auth->isSuperAdmin() && (int)$adminId === (int)$this->auth->id) {
                return 0;
            }
            exception(__('Please select school'));
        }
        if (!School::where('id', $schoolId)->count()) {
            exception(__('No Results were found'));
        }
        return $schoolId;
    }

    protected function requireSuperAdminManage()
    {
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('Only super admin can manage sub admins'));
        }
    }

    protected function getRoleTemplateDefinitions()
    {
        return [
            'school_manager' => [
                'label'      => __('Role template school manager'),
                'desc'       => __('Role template school manager desc'),
                'group_name' => __('Role group school manager'),
                'rules'      => [
                    'discover',
                    'discover/content',
                    'discover/discover',
                    'discover/discover/index',
                    'discover/discover/edit',
                    'discover/discover/del',
                    'discover/discover/multi',
                ],
            ],
        ];
    }

    protected function getRoleTemplateOptions($includeLegacy = false)
    {
        $options = [];
        foreach ($this->getRoleTemplateDefinitions() as $key => $config) {
            $options[$key] = $config['label'];
        }
        if ($includeLegacy) {
            $options['legacy_custom'] = __('Role template legacy custom');
        }
        return $options;
    }

    protected function getTemplateParentGroupId()
    {
        $groupIds = $this->auth->getGroupIds();
        if (!empty($groupIds)) {
            return (int)reset($groupIds);
        }
        $superGroupId = (int)AuthGroup::where('rules', '*')->order('id asc')->value('id');
        return $superGroupId > 0 ? $superGroupId : 1;
    }

    protected function getRuleIdsByNames(array $ruleNames)
    {
        if (empty($ruleNames)) {
            return [];
        }
        return AuthRule::where('name', 'in', $ruleNames)->column('id', 'name');
    }

    protected function getExistingAdminGroupIds($adminId)
    {
        if (!$adminId) {
            return [];
        }
        return AuthGroupAccess::where('uid', (int)$adminId)->column('group_id');
    }

    protected function ensureRoleTemplateGroup($templateKey)
    {
        $definitions = $this->getRoleTemplateDefinitions();
        if (!isset($definitions[$templateKey])) {
            exception(__('Please select role template'));
        }
        $definition = $definitions[$templateKey];
        $ruleIdMap = $this->getRuleIdsByNames($definition['rules']);
        $ruleIds = [];
        foreach ($definition['rules'] as $ruleName) {
            if (isset($ruleIdMap[$ruleName])) {
                $ruleIds[] = $ruleIdMap[$ruleName];
            }
        }
        $saveData = [
            'pid'    => $this->getTemplateParentGroupId(),
            'name'   => $definition['group_name'],
            'rules'  => implode(',', $ruleIds),
            'status' => 'normal',
        ];
        $group = AuthGroup::get(['name' => $definition['group_name']]);
        if ($group) {
            $group->allowField(true)->save($saveData);
            return $group;
        }
        return AuthGroup::create($saveData);
    }

    protected function syncRoleTemplateGroups()
    {
        foreach (array_keys($this->getRoleTemplateDefinitions()) as $templateKey) {
            $this->ensureRoleTemplateGroup($templateKey);
        }
    }

    protected function detectRoleTemplateByGroupIds(array $groupIds, $adminId = 0)
    {
        if ($adminId && (int)$adminId === (int)$this->auth->id && $this->auth->isSuperAdmin()) {
            return 'platform_owner';
        }
        $normalized = array_map('intval', $groupIds);
        sort($normalized);
        $legacyReviewerGroup = AuthGroup::get(['name' => __('Role group school reviewer')]);
        if ($legacyReviewerGroup && in_array((int)$legacyReviewerGroup['id'], $normalized, true) && count($normalized) === 1) {
            return 'school_manager';
        }
        foreach ($this->getRoleTemplateDefinitions() as $templateKey => $definition) {
            $group = AuthGroup::get(['name' => $definition['group_name']]);
            if ($group && in_array((int)$group['id'], $normalized, true) && count($normalized) === 1) {
                return $templateKey;
            }
        }
        return empty($normalized) ? '' : 'legacy_custom';
    }

    protected function getRoleTemplateText($templateKey)
    {
        if ($templateKey === 'platform_owner') {
            return __('Role template platform owner');
        }
        $definitions = $this->getRoleTemplateDefinitions();
        if (isset($definitions[$templateKey])) {
            return $definitions[$templateKey]['label'];
        }
        return __('Role template legacy custom');
    }

    protected function resolveAdminGroupIds($params, $adminId = 0)
    {
        $templateKey = isset($params['role_template']) ? trim((string)$params['role_template']) : '';
        if ($templateKey === 'legacy_custom' && $adminId) {
            $groupIds = $this->getExistingAdminGroupIds($adminId);
            $groupIds = array_intersect($this->childrenGroupIds, array_map('intval', $groupIds));
            if (!$groupIds) {
                exception(__('Please select role template'));
            }
            return $groupIds;
        }
        if ($templateKey === '') {
            exception(__('Please select role template'));
        }
        $group = $this->ensureRoleTemplateGroup($templateKey);
        return [(int)$group['id']];
    }

    public function index()
    {
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            $childrenGroupIds = $this->childrenGroupIds;
            $groupName = AuthGroup::where('id', 'in', $childrenGroupIds)->column('id,name');
            $authGroupList = AuthGroupAccess::where('group_id', 'in', $childrenGroupIds)
                ->field('uid,group_id')
                ->select();

            $adminGroupName = [];
            foreach ($authGroupList as $item) {
                if (isset($groupName[$item['group_id']])) {
                    $adminGroupName[$item['uid']][$item['group_id']] = $groupName[$item['group_id']];
                }
            }
            $groups = $this->auth->getGroups();
            foreach ($groups as $group) {
                $adminGroupName[$this->auth->id][$group['id']] = $group['name'];
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $list = $this->model
                ->where($where)
                ->where('id', 'in', $this->childrenAdminIds)
                ->field(['password', 'salt', 'token'], true)
                ->order($sort, $order)
                ->paginate($limit);

            $adminIds = [];
            foreach ($list as $item) {
                $adminIds[] = $item['id'];
            }
            $schoolMap = $this->getAdminSchoolMap($adminIds);
            $schoolNames = [];
            if ($schoolMap && $this->hasTable('school')) {
                $schoolNames = School::where('id', 'in', array_values($schoolMap))->column('name', 'id');
            }

            foreach ($list as $k => &$item) {
                $groups = isset($adminGroupName[$item['id']]) ? $adminGroupName[$item['id']] : [];
                $item['groups'] = implode(',', array_keys($groups));
                $item['groups_text'] = implode(',', array_values($groups));
                $item['role_template'] = $this->detectRoleTemplateByGroupIds(array_keys($groups), $item['id']);
                $item['role_template_text'] = $this->getRoleTemplateText($item['role_template']);
                $item['school_id'] = isset($schoolMap[$item['id']]) ? (int)$schoolMap[$item['id']] : 0;
                $item['school_name'] = $item['school_id'] && isset($schoolNames[$item['school_id']]) ? $schoolNames[$item['school_id']] : '-';
            }
            unset($item);

            return json(['total' => $list->total(), 'rows' => $list->items()]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        $this->requireSuperAdminManage();
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    $schoolId = isset($params['school_id']) ? $this->validateSchoolId($params['school_id']) : 0;
                    $group = $this->resolveAdminGroupIds($params);
                    unset($params['role_template']);
                    unset($params['school_id']);

                    if (!Validate::is($params['password'], '\\S{6,30}')) {
                        exception(__('Please input correct password'));
                    }
                    $params['salt'] = Random::alnum();
                    $params['password'] = $this->auth->getEncryptPassword($params['password'], $params['salt']);
                    $params['avatar'] = '/assets/img/avatar.png';
                    $result = $this->model->validate('Admin.add')->save($params);
                    if ($result === false) {
                        exception($this->model->getError());
                    }

                    $dataset = [];
                    foreach ($group as $value) {
                        $dataset[] = ['uid' => $this->model->id, 'group_id' => $value];
                    }
                    model('AuthGroupAccess')->saveAll($dataset);
                    $this->bindSchool($this->model->id, $schoolId);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        return $this->view->fetch();
    }

    public function edit($ids = null)
    {
        $this->requireSuperAdminManage();
        $row = $this->model->get(['id' => $ids]);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if (!in_array($row->id, $this->childrenAdminIds)) {
            $this->error(__('You have no permission'));
        }
        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post('row/a');
            if ($params) {
                Db::startTrans();
                try {
                    $schoolId = isset($params['school_id']) ? $this->validateSchoolId($params['school_id'], $row->id) : 0;
                    $group = $this->resolveAdminGroupIds($params, $row->id);
                    unset($params['role_template']);
                    unset($params['school_id']);

                    if (!empty($params['password'])) {
                        if (!Validate::is($params['password'], '\\S{6,30}')) {
                            exception(__('Please input correct password'));
                        }
                        $params['salt'] = Random::alnum();
                        $params['password'] = $this->auth->getEncryptPassword($params['password'], $params['salt']);
                    } else {
                        unset($params['password'], $params['salt']);
                    }

                    $adminValidate = \think\Loader::validate('Admin');
                    $adminValidate->rule([
                        'username' => 'require|regex:\\w{3,30}|unique:admin,username,' . $row->id,
                        'email'    => 'require|email|unique:admin,email,' . $row->id,
                        'mobile'   => 'regex:1[3-9]\\d{9}|unique:admin,mobile,' . $row->id,
                        'password' => 'regex:\\S{32}',
                    ]);
                    $result = $row->validate('Admin.edit')->save($params);
                    if ($result === false) {
                        exception($row->getError());
                    }

                    model('AuthGroupAccess')->where('uid', $row->id)->delete();

                    $dataset = [];
                    foreach ($group as $value) {
                        $dataset[] = ['uid' => $row->id, 'group_id' => $value];
                    }
                    model('AuthGroupAccess')->saveAll($dataset);
                    $this->bindSchool($row->id, $schoolId);
                    Db::commit();
                } catch (\Exception $e) {
                    Db::rollback();
                    $this->error($e->getMessage());
                }
                $this->success();
            }
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $grouplist = $this->auth->getGroups($row['id']);
        $groupids = [];
        foreach ($grouplist as $group) {
            $groupids[] = $group['id'];
        }
        $row['school_id'] = $this->hasTable('admin_school') ? (int)AdminSchool::where('admin_id', $row['id'])->where('status', 'normal')->value('school_id') : 0;
        $row['role_template'] = $this->detectRoleTemplateByGroupIds($groupids, $row['id']);
        $this->view->assign('row', $row);
        $this->view->assign('groupids', $groupids);
        $this->view->assign('roletemplatedata', $this->getRoleTemplateOptions($row['role_template'] === 'legacy_custom'));
        return $this->view->fetch();
    }

    public function del($ids = '')
    {
        $this->requireSuperAdminManage();
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if ($ids) {
            $ids = array_intersect($this->childrenAdminIds, array_filter(explode(',', $ids)));
            $childrenGroupIds = $this->childrenGroupIds;
            $adminList = $this->model->where('id', 'in', $ids)->where('id', 'in', function ($query) use ($childrenGroupIds) {
                $query->name('auth_group_access')->where('group_id', 'in', $childrenGroupIds)->field('uid');
            })->select();
            if ($adminList) {
                $deleteIds = [];
                foreach ($adminList as $item) {
                    $deleteIds[] = $item->id;
                }
                $deleteIds = array_values(array_diff($deleteIds, [$this->auth->id]));
                if ($deleteIds) {
                    Db::startTrans();
                    try {
                        $this->model->destroy($deleteIds);
                        model('AuthGroupAccess')->where('uid', 'in', $deleteIds)->delete();
                        if ($this->hasTable('admin_school')) {
                            AdminSchool::where('admin_id', 'in', $deleteIds)->delete();
                        }
                        Db::commit();
                    } catch (\Exception $e) {
                        Db::rollback();
                        $this->error($e->getMessage());
                    }
                    $this->success();
                }
                $this->error(__('No rows were deleted'));
            }
        }
        $this->error(__('You have no permission'));
    }

    public function multi($ids = '')
    {
        $this->error();
    }

    public function selectpage()
    {
        $this->dataLimit = 'auth';
        $this->dataLimitField = 'id';
        return parent::selectpage();
    }
}
