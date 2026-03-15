<?php

namespace app\admin\model\discover;

use think\Model;


class Attentions extends Model
{

    

    

    // 表名
    protected $name = 'discover_attentions';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [

    ];
    

    







    public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
    public function userm()
    {
        return $this->belongsTo('app\admin\model\User', 'attention_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function discover()
    {
        return $this->belongsTo('app\admin\model\discover\Discover', 'discover_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}