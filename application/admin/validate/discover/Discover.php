<?php

namespace app\admin\validate\discover;

use think\Validate;

class Discover extends Validate
{
    protected $rule = [
        'title'        => 'require|max:100',
        'school_id'    => 'require|number|gt:0',
        'statusdata'   => 'require|in:1,2,3',
        'audit_status' => 'require|in:pending,approved,rejected',
        'is_top'       => 'require|in:0,1',
        'top_sort'     => 'number',
    ];

    protected $scene = [
        'add'  => ['title', 'school_id', 'statusdata', 'audit_status', 'is_top', 'top_sort'],
        'edit' => ['title', 'school_id', 'statusdata', 'audit_status', 'is_top', 'top_sort'],
    ];
}
