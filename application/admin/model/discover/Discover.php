<?php

namespace app\admin\model\discover;

use think\Model;

class Discover extends Model
{
    protected $name = 'discover';
    protected $autoWriteTimestamp = 'int';
    protected $createTime = 'createtime';
    protected $updateTime = 'updatetime';
    protected $deleteTime = false;
    protected $append = [
        'statusdata_text',
        'adddata_text',
        'iscommentdata_text',
        'audit_status_text',
        'is_top_text'
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
        return ['1' => __('Statusdata 1'), '2' => __('Statusdata 2'), '3' => __('Statusdata 3')];
    }

    public function getAdddataList()
    {
        return ['1' => __('Adddata 1'), '2' => __('Adddata 2')];
    }

    public function getIscommentdataList()
    {
        return ['1' => __('Iscommentdata 1'), '2' => __('Iscommentdata 2')];
    }

    public function getAuditStatusList()
    {
        return ['pending' => __('Audit_status pending'), 'approved' => __('Audit_status approved'), 'rejected' => __('Audit_status rejected')];
    }

    public function getIsTopList()
    {
        return ['0' => __('Is_top 0'), '1' => __('Is_top 1')];
    }

    public function getStatusdataTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['statusdata']) ? $data['statusdata'] : '');
        $list = $this->getStatusdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getAdddataTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['adddata']) ? $data['adddata'] : '');
        $list = $this->getAdddataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getIscommentdataTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['iscommentdata']) ? $data['iscommentdata'] : '');
        $list = $this->getIscommentdataList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getAuditStatusTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['audit_status']) ? $data['audit_status'] : '');
        $list = $this->getAuditStatusList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function getIsTopTextAttr($value, $data)
    {
        $value = $value ?: (isset($data['is_top']) ? $data['is_top'] : '');
        $list = $this->getIsTopList();
        return isset($list[$value]) ? $list[$value] : '';
    }

    public function user()
    {
        return $this->belongsTo('app\\admin\\model\\User', 'user_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function discovertag()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\Tag', 'tag_ids', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function discovertopic()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\Topic', 'top_ids', 'id', [], 'LEFT')->setEagerlyType(0);
    }

    public function school()
    {
        return $this->belongsTo('app\\admin\\model\\discover\\School', 'school_id', 'id', [], 'LEFT')->setEagerlyType(0);
    }
}
