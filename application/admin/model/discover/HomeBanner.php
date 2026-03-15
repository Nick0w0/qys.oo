<?php

namespace app\admin\model\discover;

use think\Model;

class HomeBanner extends Model
{
    protected $name = 'home_banner';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;
    protected $append = [
        'status_text',
        'jump_type_text',
        'starttime_text',
        'endtime_text',
    ];

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            if ((int)$row['weigh'] === 0) {
                $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
            }
        });
    }

    public function getStatusList()
    {
        return ['normal' => __('Normal'), 'hidden' => __('Hidden')];
    }

    public function getJumpTypeList()
    {
        return [
            'none'     => __('Jump_none'),
            'path'     => __('Jump_path'),
            'discover' => __('Jump_discover'),
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: ($data['status'] ?? '');
        $list = $this->getStatusList();
        return $list[$value] ?? '';
    }

    public function getJumpTypeTextAttr($value, $data)
    {
        $value = $value ?: ($data['jump_type'] ?? '');
        $list = $this->getJumpTypeList();
        return $list[$value] ?? '';
    }

    public function getStarttimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['starttime'] ?? '');
        return $value ? (is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value) : '';
    }

    public function getEndtimeTextAttr($value, $data)
    {
        $value = $value ?: ($data['endtime'] ?? '');
        return $value ? (is_numeric($value) ? date('Y-m-d H:i:s', $value) : $value) : '';
    }

    protected function setStarttimeAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : (int)$value);
    }

    protected function setEndtimeAttr($value)
    {
        return $value === '' ? 0 : ($value && !is_numeric($value) ? strtotime($value) : (int)$value);
    }

    public function school()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\School', 'school_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}