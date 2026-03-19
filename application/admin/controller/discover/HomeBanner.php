<?php

namespace app\admin\controller\discover;

use app\admin\model\AdminSchool;
use app\admin\model\discover\School as SchoolModel;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;
use think\exception\ValidateException;

class HomeBanner extends Backend
{
    protected $model = null;
    protected $multiFields = 'status';
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\discover\HomeBanner;
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->view->assign('jumpTypeList', $this->model->getJumpTypeList());
        $this->view->assign('bannerPageOptions', $this->getBannerPageOptions());
        $this->view->assign('schooldata', $this->getSchoolData());
        $this->view->assign('isSuperAdmin', $this->auth->isSuperAdmin());
        $this->view->assign('currentSchoolId', $this->getCurrentSchoolId());
        $this->view->assign('currentSchoolName', $this->getCurrentSchoolName());
    }

    protected function getBannerPageOptions($currentValue = '')
    {
        $options = [
            '/pages/index/index'      => '首页',
            '/pages/index/search'     => '搜索页',
            '/pages/index/hot'        => '关注页',
            '/pages/plugin/index'     => '选学校页',
            '/pages/user/message'     => '消息页',
            '/pages/user/myattentions'=> '我关注的作者',
            '/pages/user/index'       => '我的页面',
        ];
        $currentValue = trim((string)$currentValue);
        if ($currentValue !== '' && !isset($options[$currentValue])) {
            $options = [$currentValue => '当前已保存页面'] + $options;
        }
        return $options;
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
                $result = Db::query("SHOW COLUMNS FROM `" . addslashes($fullTable) . "` LIKE '" . addslashes($column) . "'");
                self::$schemaCache[$key] = !empty($result);
            }
        }
        return self::$schemaCache[$key];
    }

    protected function ensureSchema()
    {
        if (!$this->hasTable('home_banner') || !$this->hasTable('school') || !$this->hasTable('admin_school')) {
            $this->error(__('Please run phase4 sql first'));
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
                $this->error(__('Please run phase4 sql first'));
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

        $jumpTypeList = array_keys($this->model->getJumpTypeList());
        $params['jump_type'] = isset($params['jump_type']) && in_array($params['jump_type'], $jumpTypeList, true) ? $params['jump_type'] : 'none';
        $params['jump_value'] = isset($params['jump_value']) ? trim((string)$params['jump_value']) : '';
        if ($params['jump_type'] === 'discover') {
            $params['jump_value'] = (string)max(0, (int)$params['jump_value']);
            if ((int)$params['jump_value'] <= 0) {
                $this->error(__('Please input discover id'));
            }
        }
        if ($params['jump_type'] === 'path') {
            $pageOptions = $this->getBannerPageOptions($params['jump_value']);
            if ($params['jump_value'] === '' || !isset($pageOptions[$params['jump_value']])) {
                $this->error(__('Please input valid path'));
            }
        }
        if ($params['jump_type'] === 'none') {
            $params['jump_value'] = '';
        }
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
                $query->where('home_banner.school_id', $schoolId ?: -1);
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

    public function selectDiscover()
    {
        $this->request->filter(['trim', 'strip_tags', 'htmlspecialchars']);
        $this->ensureSchema();
        if (!$this->hasTable('discover')) {
            return json(['list' => [], 'totalRow' => 0]);
        }

        $word = (array)$this->request->request("q_word/a", []);
        $page = max(1, (int)$this->request->request("pageNumber", 1));
        $pageSize = max(1, min(20, (int)$this->request->request("pageSize", 10)));
        $primaryValue = $this->request->request("keyValue");
        $custom = (array)$this->request->request("custom/a", []);
        $schoolId = isset($custom['school_id']) ? (int)$custom['school_id'] : 0;

        if (!$this->auth->isSuperAdmin()) {
            $schoolId = $this->getCurrentSchoolId(true);
        }

        $fields = $this->getDiscoverSelectFields();

        if ($primaryValue !== null && $primaryValue !== '') {
            $ids = array_filter(array_unique(array_map('intval', explode(',', (string)$primaryValue))));
            if (empty($ids)) {
                return json(['list' => [], 'totalRow' => 0]);
            }
            $query = $this->buildDiscoverSelectQuery($schoolId);
            $query->where('id', 'in', $ids)->orderRaw("FIELD(id," . implode(',', $ids) . ")");
            $list = $query->field($fields)->select();
            return json(['list' => $this->formatDiscoverSelectRows($list), 'totalRow' => count($list)]);
        }

        $keyword = trim(implode(' ', array_filter($word)));
        $total = $this->buildDiscoverSelectQuery($schoolId, $keyword)->count();
        $list = $this->buildDiscoverSelectQuery($schoolId, $keyword)
            ->field($fields)
            ->order('id desc')
            ->page($page, $pageSize)
            ->select();
        return json(['list' => $this->formatDiscoverSelectRows($list), 'totalRow' => $total]);
    }

    protected function buildDiscoverSelectQuery($schoolId = 0, $keyword = '')
    {
        $query = Db::name('discover');

        if ($schoolId > 0 && $this->hasColumn('discover', 'school_id')) {
            $query->where('school_id', $schoolId);
        }
        if ($this->hasColumn('discover', 'audit_status')) {
            $query->where('audit_status', 'approved');
        }
        if ($keyword !== '') {
            $query->where(function ($subQuery) use ($keyword) {
                if (ctype_digit($keyword)) {
                    $subQuery->where('id', (int)$keyword)->whereOr('title', 'like', '%' . $keyword . '%');
                } else {
                    $subQuery->where('title', 'like', '%' . $keyword . '%');
                }
            });
        }

        return $query;
    }

    protected function getDiscoverSelectFields()
    {
        $fields = ['id', 'title'];
        if ($this->hasColumn('discover', 'school_id')) {
            $fields[] = 'school_id';
        }
        return implode(',', $fields);
    }

    protected function formatDiscoverSelectRows($rows)
    {
        $result = [];
        foreach ($rows as $item) {
            $row = is_array($item) ? $item : $item->toArray();
            $result[] = [
                'id'        => (int)$row['id'],
                'title'     => htmlspecialchars((string)$row['title'], ENT_QUOTES, 'UTF-8'),
                'school_id' => isset($row['school_id']) ? (int)$row['school_id'] : 0,
            ];
        }
        return $result;
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
            $this->view->assign('bannerPageOptions', $this->getBannerPageOptions($row['jump_type'] === 'path' ? $row['jump_value'] : ''));
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
}
