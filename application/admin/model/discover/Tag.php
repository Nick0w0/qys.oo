<?php

namespace app\admin\model\discover;

use think\Model;


class Tag extends Model
{

    

    

    // 表名
    protected $name = 'discover_tag';
    
    // 自动写入时间戳字段
    protected $autoWriteTimestamp = 'int';

    // 定义时间戳字段名
    protected $createTime = 'createtime';
    protected $updateTime = false;
    protected $deleteTime = false;

    // 追加属性
    protected $append = [
        'ishotdata_text',
        'typedata_text',
        'auditdata_text'
    ];
    

    protected static function init()
    {
        self::afterInsert(function ($row) {
            $pk = $row->getPk();
            $row->getQuery()->where($pk, $row[$pk])->update(['weigh' => $row[$pk]]);
        });
    }

    
    public function getIshotdataList()
    {
        return ['0' => __('Ishotdata 0'), '1' => __('Ishotdata 1'), '2' => __('Ishotdata 2')];
    }

    public function getTypedataList()
    {
        return ['0' => __('Typedata 0'), '1' => __('Typedata 1')];
    }

    public function getAuditdataList()
    {
        return ['0' => __('Auditdata 0'), '1' => __('Auditdata 1'), '2' => __('Auditdata 2')];
    }


    public function getIshotdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['ishotdata']) ? $data['ishotdata'] : '');
        $list = $this->getIshotdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getTypedataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['typedata']) ? $data['typedata'] : '');
        $list = $this->getTypedataList();
        return isset($list[$value]) ? $list[$value] : '';
    }


    public function getAuditdataTextAttr($value, $data)
    {
        $value = $value ? $value : (isset($data['auditdata']) ? $data['auditdata'] : '');
        $list = $this->getAuditdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }




}
