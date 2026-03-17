<?php

namespace app\admin\controller\discover;

use app\common\controller\Backend;
use think\Db;

class School extends Backend
{
    protected $model = null;
    protected $searchFields = 'id,name,short_name,city';
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->model = new \app\admin\model\discover\School;
        $this->view->assign('statusList', $this->model->getStatusList());
        $this->view->assign('hasSchoolThemeFields', $this->hasSchoolThemeFields());
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

    protected function hasSchoolThemeFields()
    {
        foreach (['logo', 'header_bg_image', 'theme_primary', 'theme_secondary', 'theme_text_color'] as $field) {
            if (!$this->hasColumn('school', $field)) {
                return false;
            }
        }
        return true;
    }

    protected function ensureSchema()
    {
        if (!$this->hasTable('school')) {
            $this->error(__('Please run phase1 sql first'));
        }
    }

    public function index()
    {
        if ($this->request->isAjax()) {
            $this->ensureSchema();
        }
        return parent::index();
    }

    public function add()
    {
        if ($this->request->isPost()) {
            $this->ensureSchema();
        }
        return parent::add();
    }

    public function edit($ids = null)
    {
        $this->ensureSchema();
        return parent::edit($ids);
    }

    public function del($ids = null)
    {
        $this->ensureSchema();
        return parent::del($ids);
    }

    public function multi($ids = null)
    {
        $this->ensureSchema();
        return parent::multi($ids);
    }

    public function import()
    {
        $this->ensureSchema();
        parent::import();
    }
}
