<?php

namespace app\admin\model\discover;

use think\Model;


class Log extends Model
{

    

    

    // 表名
    protected $name = 'discover_log';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'typedata_text',
        'readdata_text'
    ];
    

    
    public function getTypedataList()
    {
        return ['1' => __('Typedata 1'), '2' => __('Typedata 2'), '3' => __('Typedata 3'), '4' => __('Typedata 4'), '5' => __('Typedata 5'), '6' => __('Typedata 6')];
    }

    public function getReaddataList()
    {
        return ['0' => __('Readdata 0'), '1' => __('Readdata 1')];
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getReaddataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['readdata']) ? $data['readdata'] : '');
        $list = $this->getReaddataList();
        return isset($list[$value]) ? $list[$value] : '';
    }
      public function user()
    {
        return $this->belongsTo('app\admin\model\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);//提醒用户id
    }
      public function createuser()
    {
        return $this->belongsTo('app\admin\model\User', 'create_id', 'id', [], 'LEFT')->setEagerlyType(0);//创建人id
    }




}