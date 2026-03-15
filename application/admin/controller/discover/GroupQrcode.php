<?php

namespace app\admin\controller\discover;

use app\admin\model\AdminSchool;
use app\admin\model\discover\School as SchoolModel;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class GroupQrcode extends Backend
{
    protected $model = null;
    protected $multiFields = 'status';
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\discover\GroupQrcode;
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->view->assign('popupStrategyList', $this->model->getPopupStrategyList());
        $this->view->assign('schooldata', $this->getSchoolData());
        $this->view->assign('isSuperAdmin', $this->auth->isSuperAdmin());
        $this->view->assign('currentSchoolId', $this->getCurrentSchoolId());
        $this->view->assign('currentSchoolName', $this->getCurrentSchoolName());
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

    protected function ensureSchema()
    {
        if (!$this->hasTable('school_group_qrcode') || !$this->hasTable('school') || !$this->hasTable('admin_school')) {
            $this->error(__('Please run phase3 sql first'));
        }
    }

    protected function getSchoolData()
    {
        if (!$this->hasTable('school')) {
            return [];
        }
        $data = SchoolModel::where('status', 'normal')->order('weigh desc,id desc')->column('name', 'id');
        if ($this->auth->isSuperAdmin()) {
            return [0 => __('Platform_default')] + $data;
        }
        $schoolId = $this->getCurrentSchoolId();
        if ($schoolId > 0 && isset($data[$schoolId])) {
            return [$schoolId => $data[$schoolId]];
        }
        return [];
    }

    protected function getCurrentSchoolId($required = false)
    {
        if ($this->auth->isSuperAdmin()) {
            return 0;
        }
        if (!$this->hasTable('admin_school')) {
            if ($required) {
                $this->error(__('Please run phase3 sql first'));
            }
            return 0;
        }
        $schoolId = (int)AdminSchool::where('admin_id', $this->auth->id)->where('status', 'normal')->value('school_id');
        if ($required && !$schoolId) {
            $this->error(__('Please bind school first'));
        }
        return $schoolId;
    }

    protected function getCurrentSchoolName()
    {
        $schoolId = $this->getCurrentSchoolId();
        if ($schoolId <= 0) {
            return $this->auth->isSuperAdmin() ? __('Platform_default') : '';
        }
        return (string)SchoolModel::where('id', $schoolId)->value('name');
    }

    protected function assertSchoolAccess($row)
    {
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->auth->isSuperAdmin()) {
            return;
        }
        $schoolId = $this->getCurrentSchoolId(true);
        if ((int)$row['school_id'] !== $schoolId) {
            $this->error(__('You have no permission'));
        }
    }

    protected function normalizeParams($params)
    {
        $params = $this->preExcludeFields($params);
        $this->ensureSchema();

        if ($this->auth->isSuperAdmin()) {
            $params['school_id'] = isset($params['school_id']) ? (int)$params['school_id'] : 0;
            if ($params['school_id'] < 0) {
                $this->error(__('Please select school'));
            }
            if ($params['school_id'] > 0 && !SchoolModel::where('id', $params['school_id'])->where('status', 'normal')->count()) {
                $this->error(__('Please select school'));
            }
        } else {
            $params['school_id'] = $this->getCurrentSchoolId(true);
        }

        $strategyList = array_keys($this->model->getPopupStrategyList());
        $params['popup_strategy'] = isset($params['popup_strategy']) && in_array($params['popup_strategy'], $strategyList, true) ? $params['popup_strategy'] : 'daily';
        $params['popup_interval'] = isset($params['popup_interval']) ? max(1, (int)$params['popup_interval']) : 1;
        $params['status'] = isset($params['status']) && isset($this->model->getStatusList()[$params['status']]) ? $params['status'] : 'normal';
        $params['starttime'] = empty($params['starttime']) ? 0 : $params['starttime'];
        $params['endtime'] = empty($params['endtime']) ? 0 : $params['endtime'];
        $params['weigh'] = isset($params['weigh']) ? (int)$params['weigh'] : 0;

        return $params;
    }

    public function index()
    {
        $this->relationSearch = true;
        $this->request->filter(['strip_tags', 'trim']);
        if ($this->request->isAjax()) {
            $this->ensureSchema();
            if ($this->request->request('keyField')) {
                return $this->selectpage();
            }
            list($where, $sort, $order, $offset, $limit) = $this->buildparams();
            $query = $this->model->with(['school'])->where($where);
            if (!$this->auth->isSuperAdmin()) {
                $schoolId = $this->getCurrentSchoolId();
                $query->where('school_group_qrcode.school_id', $schoolId ?: -1);
            }
            $list = $query->order($sort, $order)->paginate($limit);
            foreach ($list as $row) {
                if ($row->getRelation('school')) {
                    $row->getRelation('school')->visible(['name']);
                }
            }
            return json(['total' => $list->total(), 'rows' => $list->items()]);
        }
        return $this->view->fetch();
    }

    public function add()
    {
        $this->ensureSchema();
        if (false === $this->request->isPost()) {
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->normalizeParams($params);

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
        $this->ensureSchema();
        $row = $this->model->get($ids);
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        $this->assertSchoolAccess($row);
        if (false === $this->request->isPost()) {
            $this->view->assign('row', $row);
            return $this->view->fetch();
        }
        $params = $this->request->post('row/a');
        if (empty($params)) {
            $this->error(__('Parameter %s can not be empty', ''));
        }
        $params = $this->normalizeParams($params);

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
        if ($result === false) {
            $this->error(__('No rows were updated'));
        }
        $this->success();
    }

    public function del($ids = null)
    {
        if (false === $this->request->isPost()) {
            $this->error(__('Invalid parameters'));
        }
        $this->ensureSchema();
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
        $this->ensureSchema();
        $ids = $ids ?: $this->request->post('ids');
        if (empty($ids)) {
            $this->error(__('Parameter %s can not be empty', 'ids'));
        }
        if (false === $this->request->has('params')) {
            $this->error(__('No rows were updated'));
        }

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
                $payload = [];
                if (isset($values['status']) && isset($this->model->getStatusList()[$values['status']])) {
                    $payload['status'] = $values['status'];
                }
                if (!$payload) {
                    continue;
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
