<?php

namespace app\admin\controller\discover;

use app\common\controller\Backend;
use app\common\model\Config as ConfigModel;
use think\Db;

/**
 * 内容中心-屏蔽词列表
 *
 * @icon fa fa-ban
 */
class Review extends Backend
{
    protected $model = null;
    protected $multiFields = 'status';
    protected static $schemaCache = [];

    public function _initialize()
    {
        parent::_initialize();
        $this->ensureBlockedWordSchema();
        $this->syncLegacyConfigWords();
        $this->model = new \app\admin\model\discover\Review;
        $this->view->assign('statusList', $this->model->getStatusList());
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

    protected function ensureBlockedWordSchema()
    {
        if (!$this->hasTable('discover_blocked_word')) {
            $this->error(__('Please run blocked words sql'));
        }
    }

    protected function normalizeBlockedWords($value)
    {
        $value = trim((string)$value);
        if ($value === '') {
            return [];
        }
        $items = preg_split('/[\r\n,，;；]+/u', $value);
        $words = [];
        foreach ($items as $item) {
            $item = trim((string)$item);
            if ($item !== '') {
                $words[$item] = $item;
            }
        }
        return array_values($words);
    }

    protected function syncLegacyConfigWords()
    {
        if (Db::name('discover_blocked_word')->count() > 0) {
            return;
        }
        $config = ConfigModel::getByName('discover_blocked_words');
        if (!$config || !isset($config['value'])) {
            return;
        }
        $words = $this->normalizeBlockedWords($config['value']);
        if (empty($words)) {
            return;
        }
        $time = time();
        $rows = [];
        $total = count($words);
        foreach ($words as $index => $word) {
            $rows[] = [
                'word'       => $word,
                'remark'     => __('Legacy migrated remark'),
                'status'     => 'normal',
                'weigh'      => $total - $index,
                'createtime' => $time,
                'updatetime' => $time,
            ];
        }
        if (!empty($rows)) {
            Db::name('discover_blocked_word')->insertAll($rows);
        }
    }

    public function import()
    {
        parent::import();
    }
}