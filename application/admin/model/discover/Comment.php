<?php

namespace app\admin\model\discover;

use think\Model;


class Comment extends Model
{

    

    

    // 表名
    protected $name = 'discover_comment';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'statusdata_text'
    ];
    

    
    public function getStatusdataList()
    {
        return ['1' => __('Statusdata 1'), '2' => __('Statusdata 2')];
    }


    public function getStatusdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['statusdata']) ? $data['statusdata'] : '');
        $list = $this->getStatusdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




    public function discover()
    {
        return $this->belongsTo('app\admin\model\discover\Discover', 'discover_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }


    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}