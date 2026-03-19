<?php

namespace app\admin\model\discover;

use think\Model;

class Report extends Model
{
    protected $name = 'discover_report';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;
    protected $append = [
        'status_text'
    ];

    public function getStatusList()
    {
        return [
            'pending'  => __('Status pending'),
            'handled'  => __('Status handled'),
            'rejected' => __('Status rejected'),
        ];
    }

    public function getStatusTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['status']) ? $data['status'] : '');
        $list = $this->getStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function discover()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\Discover', 'discover_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function user()
    {
        return $this->belongsTo('app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function school()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\School', 'school_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
