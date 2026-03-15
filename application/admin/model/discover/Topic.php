<?php

namespace app\admin\model\discover;

use think\Model;


class Topic extends Model
{

    

    

    // 表名
    protected $name = 'discover_topic';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'statusdata_text',
        'ishotdata_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getStatusdataList()
    {
        return ['1' => __('Statusdata 1'), '2' => __('Statusdata 2')];
    }

    public function getIshotdataList()
    {
        return ['0' => __('Ishotdata 0'), '1' => __('Ishotdata 1')];
    }


    public function getStatusdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['statusdata']) ? $data['statusdata'] : '');
        $list = $this->getStatusdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getIshotdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ishotdata']) ? $data['ishotdata'] : '');
        $list = $this->getIshotdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
