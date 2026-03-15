<?php

namespace app\admin\model;

use think\Model;

class AdminSchool extends Model
{
    protected $name = 'admin_school';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    public function school()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\School', 'school_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
