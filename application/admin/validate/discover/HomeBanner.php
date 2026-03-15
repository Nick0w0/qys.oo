<?php

namespace app\admin\validate\discover;

use think\Validate;

class HomeBanner extends Validate
{
    protected $rule = [
        'school_id'  => 'require|number|egt:0',
        'title'      => 'require|max:100',
        'image'      => 'require|max:255',
        'jump_type'  => 'require|in:none,path,discover',
        'jump_value' => 'max:255',
        'status'     => 'require|in:normal,hidden',
        'starttime'  => 'number|egt:0',
        'endtime'    => 'number|egt:0',
        'weigh'      => 'number',
    ];

    protected $scene = [
        'add'  => ['school_id', 'title', 'image', 'jump_type', 'jump_value', 'status', 'starttime', 'endtime', 'weigh'],
        'edit' => ['school_id', 'title', 'image', 'jump_type', 'jump_value', 'status', 'starttime', 'endtime', 'weigh'],
    ];
}