<?php

namespace app\admin\model\discover;

use think\Exception;
use think\Model;

class Review extends Model
{
    protected $name = 'discover_blocked_word';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;
    protected $append = [
        'status_text'
    ];

    protected static function init()
    {
        self::beforeWrite(function ($row) {
            $word = trim((string)$row->getAttr('word'));
            $remark = trim((string)$row->getAttr('remark'));
            $status = (string)$row->getAttr('status') === 'hidden' ? 'hidden' : 'normal';

            if ($word === '') {
                throw new Exception(__('Word can not be empty'));
            }

            $query = self::where('word', $word);
            $id = (int)$row->getAttr('id');
            if ($id > 0) {
                $query->where('id', '<>', $id);
            }
            if ($query->find()) {
                throw new Exception(__('Word already exist'));
            }

            $row->setAttr('word', $word);
            $row->setAttr('remark', $remark);
            $row->setAttr('status', $status);
        });

        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    public function getStatusList()
    {
        return ['normal' => __('Status normal'), 'hidden' => __('Status hidden')];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }
}