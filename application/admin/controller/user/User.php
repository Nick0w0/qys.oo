<?php

namespace app\admin\controller\user;

use app\admin\model\AdminSchool;
use app\admin\model\discover\School;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\Validate;
use think\exception\PDOException;

/**
 * 用户管理
 *
 * @icon fa fa-user
 */
class User extends Backend
{
    protected $relationSearch = false;
    protected $searchFields = 'id,username,nickname,mobile';
    protected $multiFields = 'status';
    protected static $schemaCache = [];

    /**
     * @var \app\admin\model\User
     */
    protected $model = null;

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\User;
        $schoolData = $this->getSchoolData();
        $this->view->assign('schooldata', $schoolData);
        $this->view->assign('isSuperAdmin', $this->auth->isSuperAdmin());
        $this->assignconfig('schooldata', $schoolData);
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

    protected function hasColumn($table, $column)
    {
        $key = 'column:' . $table . ':' . $column;
        if (!array_key_exists($key, self::$schemaCache)) {
            if (!$this->hasTable($table)) {
                self::$schemaCache[$key] = false;
            } else {
                $fullTable = config('database.prefix') . $table;
                $result = Db::query("SHOW COLUMNS FROM `{$fullTable}` LIKE '" . addslashes($column) . "'");
                self::$schemaCache[$key] = !empty($result);
            }
        }
        return self::$schemaCache[$key];
    }

    protected function hasSchoolScopeSchema()
    {
        return $this->hasTable('school') && $this->hasTable('admin_school') && $this->hasColumn('user', 'school_id');
    }

    protected function canEditUserSchool()
    {
        return $this->auth->isSuperAdmin() && $this->hasTable('school') && $this->hasColumn('user', 'school_id');
    }

    protected function canEditUserMobile()
    {
        return $this->auth->isSuperAdmin() && $this->hasColumn('user', 'mobile');
    }

    protected function normalizeSchoolId($schoolId)
    {
        $schoolId = (int)$schoolId;
        if ($schoolId <= 0) {
            return 0;
        }
        if (!School::where('id', $schoolId)->where('status', 'normal')->find()) {
            $this->error(__('No Results were found'));
        }
        return $schoolId;
    }

    protected function getSchoolData()
    {
        if (!$this->hasTable('school')) {
            return [];
        }
        return School::where('status', 'normal')->order('weigh desc,id desc')->column('name', 'id');
    }

    protected function getCurrentSchoolId($required = false)
    {
        if ($this->auth->isSuperAdmin()) {
            return 0;
        }
        if (!$this->hasTable('admin_school')) {
            if ($required) {
                $this->error(__('Please run phase1 sql first'));
            }
            return 0;
        }
        $schoolId = (int)AdminSchool::where('admin_id', $this->auth->id)->where('status', 'normal')->value('school_id');
        if ($required && !$schoolId) {
            $this->error(__('Please bind school first'));
        }
        return $schoolId;
    }

    protected function assertSchoolAccess($row)
    {
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->auth->isSuperAdmin() || !$this->hasSchoolScopeSchema()) {
            return;
        }
        $schoolId = $this->getCurrentSchoolId(true);
        if ((int)$row['school_id'] !== $schoolId) {
            $this->error(__('You have no permission'));
        }
    }

    protected function getSummaryRows($table, array $userIds, $aggregateSql)
    {
        if (empty($userIds) || !$this->hasTable($table)) {
            return [];
        }
        return Db::name($table)
            ->field($aggregateSql)
            ->where('user_id', 'in', $userIds)
            ->group('user_id')
            ->select();
    }

    protected function getUserSummaryMap(array $userIds)
    {
        $summary = [
            'schoolNames'       => [],
            'discoverCounts'    => [],
            'commentCounts'     => [],
            'lastDiscoverTimes' => [],
        ];
        if (empty($userIds)) {
            return $summary;
        }

        $schoolIds = Db::name('user')->where('id', 'in', $userIds)->column('school_id', 'id');
        $schoolIdList = array_values(array_filter(array_unique(array_map('intval', $schoolIds))));
        if (!empty($schoolIdList) && $this->hasTable('school')) {
            $summary['schoolNames'] = Db::name('school')->where('id', 'in', $schoolIdList)->column('name', 'id');
        }

        foreach ($this->getSummaryRows('discover', $userIds, 'user_id,COUNT(*) AS total,MAX(createtime) AS last_time') as $row) {
            $summary['discoverCounts'][(int)$row['user_id']] = (int)$row['total'];
            $summary['lastDiscoverTimes'][(int)$row['user_id']] = !empty($row['last_time']) ? (int)$row['last_time'] : 0;
        }

        foreach ($this->getSummaryRows('discover_comment', $userIds, 'user_id,COUNT(*) AS total') as $row) {
            $summary['commentCounts'][(int)$row['user_id']] = (int)$row['total'];
        }

        $summary['userSchoolIds'] = $schoolIds;
        return $summary;
    }

    protected function formatAvatar($row)
    {
        $avatar = isset($row['avatar']) ? (string)$row['avatar'] : '';
        $nickname = isset($row['nickname']) ? (string)$row['nickname'] : '';
        return $avatar ? cdnurl($avatar, true) : letter_avatar($nickname);
    }

    protected function decorateRows($rows)
    {
        $userIds = [];
        foreach ($rows as $row) {
            $userIds[] = (int)$row['id'];
        }
        $summary = $this->getUserSummaryMap($userIds);
        foreach ($rows as $row) {
            $userId = (int)$row['id'];
            $schoolId = isset($summary['userSchoolIds'][$userId]) ? (int)$summary['userSchoolIds'][$userId] : (isset($row['school_id']) ? (int)$row['school_id'] : 0);
            $row['school_name'] = $schoolId && isset($summary['schoolNames'][$schoolId]) ? $summary['schoolNames'][$schoolId] : '-';
            $row['discover_count'] = isset($summary['discoverCounts'][$userId]) ? (int)$summary['discoverCounts'][$userId] : 0;
            $row['comment_count'] = isset($summary['commentCounts'][$userId]) ? (int)$summary['commentCounts'][$userId] : 0;
            $row['last_discover_time'] = isset($summary['lastDiscoverTimes'][$userId]) ? (int)$summary['lastDiscoverTimes'][$userId] : 0;
            $row['avatar'] = $this->formatAvatar($row);
            $row->hidden(['password', 'salt', 'token']);
        }
    }

    protected function getScopedListQuery($where)
    {
        $query = $this->model->where($where);
        if (!$this->auth->isSuperAdmin() && $this->hasSchoolScopeSchema()) {
            $query->where('school_id', $this->getCurrentSchoolId(true));
        }
        return $query;
    }

    /**
     * 查看
     */
    public function index()
    {
        //设置过滤方法
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            //如果发送的来源是Selectpage，则转发到Selectpage
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $list = $this->getScopedListQuery($where)
                ->order($sort, $order)
                ->paginate($limit);
            $this->decorateRows($list);
            $result = array("total" => $list->total(), "rows" => $list->items());

            return json($result);
        }
        return $this->view->fetch();
    }

    /**
     * 添加
     */
    public function add()
    {
        $this->error(__('You have no permission'));
    }

    /**
     * 编辑
     */
    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->assertSchoolAccess($row);

        if ($this->request->isPost()) {
            $this->token();
            $params = $this->request->post('row/a');
            $status = isset($params['status']) ? (string)$params['status'] : '';
            if (!in_array($status, ['normal', 'hidden'], true)) {
                $this->error(__('Parameter %s can not be empty', 'status'));
            }

            $saveData = ['status' => $status];
            $oldSchoolId = (int)$row['school_id'];
            $newSchoolId = $oldSchoolId;
            $oldMobile = isset($row['mobile']) ? (string)$row['mobile'] : '';
            $newMobile = $oldMobile;

            if (array_key_exists('school_id', $params)) {
                if (!$this->canEditUserSchool()) {
                    $this->error(__('You have no permission'));
                }
                $newSchoolId = $this->normalizeSchoolId($params['school_id']);
                $saveData['school_id'] = $newSchoolId;
            }

            if (array_key_exists('mobile', $params)) {
                if (!$this->canEditUserMobile()) {
                    $this->error(__('You have no permission'));
                }
                $newMobile = trim((string)$params['mobile']);
                if ($newMobile === '') {
                    $this->error(__('Parameter %s can not be empty', 'mobile'));
                }
                if (!Validate::regex($newMobile, "^1\\d{10}$")) {
                    $this->error(__('Mobile is incorrect'));
                }
                $exists = $this->model
                    ->where('mobile', $newMobile)
                    ->where('id', '<>', (int)$row['id'])
                    ->find();
                if ($exists) {
                    $this->error(__('Mobile already exists'));
                }
                $saveData['mobile'] = $newMobile;
            }

            Db::startTrans();
            try {
                $row->save($saveData);

                if ($this->canEditUserSchool() && $newSchoolId !== $oldSchoolId) {
                    if ($this->hasTable('discover') && $this->hasColumn('discover', 'school_id')) {
                        Db::name('discover')
                            ->where('user_id', (int)$row['id'])
                            ->update(['school_id' => $newSchoolId]);
                    }
                    if ($this->hasTable('discover_report') && $this->hasColumn('discover_report', 'school_id')) {
                        Db::name('discover_report')
                            ->where('discover_user_id', (int)$row['id'])
                            ->update(['school_id' => $newSchoolId]);
                    }
                }
                if ($this->canEditUserMobile() && isset($saveData['mobile']) && $newMobile !== $oldMobile && $this->hasColumn('user', 'verification')) {
                    $verification = json_decode((string)$row['verification'], true);
                    $verification = is_array($verification) ? $verification : [];
                    $verification['mobile'] = 0;
                    $row->save(['verification' => json_encode($verification, JSON_UNESCAPED_UNICODE)]);
                }
                Db::commit();
            } catch (PDOException|Exception $e) {
                Db::rollback();
                $this->error($e->getMessage());
            }

            $this->success();
        }

        $this->decorateRows([$row]);
        $row['createtime_text'] = !empty($row['createtime']) ? date('Y-m-d H:i:s', (int)$row['createtime']) : '-';
        $row['last_discover_time_text'] = !empty($row['last_discover_time']) ? date('Y-m-d H:i:s', (int)$row['last_discover_time']) : '-';
        $row['avatar_url'] = $row['avatar'];
        $this->view->assign('row', $row);
        $this->view->assign('canEditUserSchool', $this->canEditUserSchool());
        $this->view->assign('canEditUserMobile', $this->canEditUserMobile());
        return $this->view->fetch();
    }

    /**
     * 删除
     */
    public function del($ids = "")
    {
        $this->error(__('You have no permission'));
    }

    public function multi($ids = null)
    {
        if (!$this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids) || false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }
        parse_str($this->request->post('params'), $values);
        $status = isset($values['status']) ? (string)$values['status'] : '';
        if (!in_array($status, ['normal', 'hidden'], true)) {
            $this->error(__('You have no permission'));
        }

        $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
        if (!$list) {
            $this->error(__('No Results were found'));
        }

        $count = 0;
        foreach ($list as $row) {
            $this->assertSchoolAccess($row);
            $count += $row->save(['status' => $status]);
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }

    public function selectpage()
    {
        return parent::selectpage();
    }

}
