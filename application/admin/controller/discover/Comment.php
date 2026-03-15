<?php

namespace app\admin\controller\discover;

use app\admin\model\AdminSchool;
use app\common\controller\Backend;
use think\Db;
use think\Exception;
use think\exception\PDOException;

class Comment extends Backend
{
    protected $model = null;
    protected static $schemaCache = [];
    protected $multiFields = 'statusdata';

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\discover\Comment;
        $this->view->assign('statusdataList', $this->model->getStatusdataList());
        $this->view->assign('isSuperAdmin', $this->auth->isSuperAdmin());
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
        return $this->hasTable('admin_school') && $this->hasColumn('discover', 'school_id');
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

    protected function assertCommentAccess($row)
    {
        if (!$row) {
            $this->error(__('No Results were found'));
        }
        if ($this->auth->isSuperAdmin() || !$this->hasSchoolScopeSchema()) {
            return;
        }
        $schoolId = $this->getCurrentSchoolId(true);
        $discoverSchoolId = (int)Db::name('discover')->where('id', $row['discover_id'])->value('school_id');
        if ($discoverSchoolId !== $schoolId) {
            $this->error(__('You have no permission'));
        }
    }

    public function import()
    {
        $this->error(__('You have no permission'));
    }

    public function add()
    {
        $this->error(__('You have no permission'));
    }

    public function edit($ids = null)
    {
        $this->error(__('You have no permission'));
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

            $query = $this->model
                ->with(['discover', 'user'])
                ->where($where);

            if (!$this->auth->isSuperAdmin() && $this->hasSchoolScopeSchema()) {
                $schoolId = $this->getCurrentSchoolId();
                $query->where('discover.school_id', $schoolId ?: -1);
            }

            $list = $query->order($sort, $order)->paginate($limit);
            foreach ($list as $row) {
                if ($row->getRelation('discover')) {
                    $row->getRelation('discover')->visible(['title']);
                }
                if ($row->getRelation('user')) {
                    $row->getRelation('user')->visible(['id', 'nickname']);
                }
            }

            return json(['total' => $list->total(), 'rows' => $list->items()]);
        }
        return $this->view->fetch();
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
                $this->assertCommentAccess($item);
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
                $this->assertCommentAccess($item);
                $count += $item->allowField(true)->isUpdate(true)->save($values);
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
