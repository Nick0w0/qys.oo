<?php

namespace app\admin\controller\discover;

use app\admin\model\AdminSchool;
use app\admin\model\discover\School as SchoolModel;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class Discover extends Backend
{
    protected $model = null;
    protected $multiFields = 'audit_status,is_top';
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\discover\Discover;
        $this->view->assign('statusdataList', $this->model->getStatusdataList());
        $this->view->assign('adddataList', $this->model->getAdddataList());
        $this->view->assign('iscommentdataList', $this->model->getIscommentdataList());
        $this->view->assign('auditStatusList', $this->model->getAuditStatusList());
        $this->view->assign('isTopList', $this->model->getIsTopList());
        $this->view->assign('schooldata', $this->getSchoolData());
        $this->view->assign('isSuperAdmin', $this->auth->isSuperAdmin());
        $this->view->assign('schoolScopeReady', $this->hasSchoolScopeSchema());
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
        return $this->hasTable('school') && $this->hasTable('admin_school') && $this->hasColumn('discover', 'school_id');
    }

    protected function hasModerationSchema()
    {
        return $this->hasColumn('discover', 'audit_status') && $this->hasColumn('discover', 'is_top');
    }

    protected function ensureSchoolScopeSchema()
    {
        if (!$this->hasSchoolScopeSchema()) {
            $this->error(__('Please run phase1 sql first'));
        }
    }

    protected function getSchoolData()
    {
        if (!$this->hasTable('school')) {
            return [];
        }
        return SchoolModel::where('status', 'normal')->order('weigh desc,id desc')->column('name', 'id');
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

    protected function normalizeParams($params, $isCreate = false)
    {
        $params = $this->preExcludeFields($params);
        $this->ensureSchoolScopeSchema();

        if ($this->auth->isSuperAdmin()) {
            $params['school_id'] = isset($params['school_id']) ? (int)$params['school_id'] : 0;
            if ($params['school_id'] <= 0) {
                $this->error(__('Please select school'));
            }
        } else {
            unset($params['school_id']);
            $params['school_id'] = $this->getCurrentSchoolId(true);
        }

        if ($this->hasModerationSchema()) {
            if (!isset($params['audit_status']) || $params['audit_status'] === '') {
                $params['audit_status'] = $isCreate ? 'approved' : 'pending';
            }
            if (!isset($params['is_top']) || $params['is_top'] === '') {
                $params['is_top'] = '0';
            }

            if (isset($params['audit_status'])) {
                $params['audit_status'] = in_array($params['audit_status'], ['pending', 'approved', 'rejected'], true) ? $params['audit_status'] : 'pending';
                if ($params['audit_status'] === 'pending') {
                    $params['audit_admin_id'] = 0;
                    $params['audit_time'] = null;
                } else {
                    $params['audit_admin_id'] = $this->auth->id;
                    $params['audit_time'] = time();
                }
            }

            if (isset($params['is_top'])) {
                $params['is_top'] = ((string)$params['is_top'] === '1') ? '1' : '0';
                $params['top_sort'] = $params['is_top'] === '1' ? (isset($params['top_sort']) && is_numeric($params['top_sort']) ? (int)$params['top_sort'] : time()) : 0;
            }
        } else {
            unset($params['audit_status'], $params['audit_admin_id'], $params['audit_time'], $params['is_top'], $params['top_sort']);
        }

        return $params;
    }

    public function import()
    {
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('You have no permission'));
        }
        parent::import();
    }

    public function index()
    {
        $this->relationSearch = true;
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();

            $relations = ['user', 'discovertag', 'discovertopic'];
            if ($this->hasSchoolScopeSchema()) {
                $relations[] = 'school';
            }

            $query = $this->model->with($relations)->where($where);
            if (!$this->auth->isSuperAdmin() && $this->hasSchoolScopeSchema()) {
                $schoolId = $this->getCurrentSchoolId();
                $query->where('discover.school_id', $schoolId ?: -1);
            }

            $list = $query->order($sort, $order)->paginate($limit);
            foreach ($list as $row) {
                if ($row->getRelation('user')) {
                    $row->getRelation('user')->visible(['nickname']);
                }
                if ($row->getRelation('discovertag')) {
                    $row->getRelation('discovertag')->visible(['name']);
                }
                if ($row->getRelation('discovertopic')) {
                    $row->getRelation('discovertopic')->visible(['name']);
                }
                if ($this->hasSchoolScopeSchema() && $row->getRelation('school')) {
                    $row->getRelation('school')->visible(['name']);
                }
            }
            return json(['total' => $list->total(), 'rows' => $list->items()]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->normalizeParams($params, true);

        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.add' : $name) : $this->modelValidate;
                $this->model->validateFailException()->validate($validate);
            }
            $result = $this->model->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($result === false) {
            $this->error(__('No rows were inserted'));
        }
        $this->success();
    }

    public function edit($ids = null)
    {
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->assertSchoolAccess($row);
        if (!$this->auth->isSuperAdmin()) {
            $this->error(__('You have no permission'));
        }
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->normalizeParams($params, false);

        $result = false;
        Db::startTrans();
        try {
            if ($this->modelValidate) {
                $name = str_replace('\\model\\', '\\validate\\', get_class($this->model));
                $validate = is_bool($this->modelValidate) ? ($this->modelSceneValidate ? $name . '.edit' : $name) : $this->modelValidate;
                $row->validateFailException()->validate($validate);
            }
            $result = $row->allowField(true)->save($params);
            Db::commit();
        } catch (ValidateException|PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if (false === $result) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        $pk = $this->model->getPk();
        $list = $this->model->where($pk, 'in', $ids)->select();
        if (!$list) {
            $this->error(__('No Results were found'));
        }

        $count = 0;
        Db::startTrans();
        try {
            foreach ($list as $item) {
                $this->assertSchoolAccess($item);
                $count += $item->delete();
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were deleted'));
    }

    public function multi($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        if (false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }
        $this->ensureSchoolScopeSchema();

        parse_str($this->request->post('params'), $values);
        if (!$this->auth->isSuperAdmin()) {
            $allowed = array_flip(explode(',', $this->multiFields));
            $values = array_intersect_key($values, $allowed);
        }
        if (empty($values)) {
            $this->error(__('You have no permission'));
        }

        $count = 0;
        Db::startTrans();
        try {
            $list = $this->model->where($this->model->getPk(), 'in', $ids)->select();
            foreach ($list as $item) {
                $this->assertSchoolAccess($item);
                $payload = $values;
                if ($this->hasModerationSchema() && isset($payload['audit_status'])) {
                    $payload['audit_status'] = in_array($payload['audit_status'], ['pending', 'approved', 'rejected'], true) ? $payload['audit_status'] : $item['audit_status'];
                    if ($payload['audit_status'] === 'pending') {
                        $payload['audit_admin_id'] = 0;
                        $payload['audit_time'] = null;
                    } else {
                        $payload['audit_admin_id'] = $this->auth->id;
                        $payload['audit_time'] = time();
                    }
                }
                if ($this->hasModerationSchema() && isset($payload['is_top'])) {
                    $payload['is_top'] = ((string)$payload['is_top'] === '1') ? '1' : '0';
                    $payload['top_sort'] = $payload['is_top'] === '1' ? ($item['top_sort'] ?: time()) : 0;
                }
                $count += $item->allowField(true)->isUpdate(true)->save($payload);
            }
            Db::commit();
        } catch (PDOException|Exception $e) {
            Db::rollback();
            $this->error($e->getMessage());
        }
        if ($count) {
            $this->success();
        }
        $this->error(__('No rows were updated'));
    }
}
