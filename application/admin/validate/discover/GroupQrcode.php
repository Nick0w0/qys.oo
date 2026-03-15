<?php

namespace app\admin\validate\discover;

use think\Validate;

class GroupQrcode extends Validate
{
    protected $rule = [
        'school_id'       => 'require|number|egt:0',
        'title'           => 'require|max:100',
        'description'     => 'max:255',
        'image'           => 'require|max:255',
        'popup_strategy'  => 'require|in:always,daily,interval',
        'popup_interval'  => 'require|number|egt:1',
        'status'          => 'require|in:normal,hidden',
        'starttime'       => 'number|egt:0',
        'endtime'         => 'number|egt:0',
        'weigh'           => 'number',
    ];

    protected $scene = [
        'add'  => ['school_id', 'title', 'description', 'image', 'popup_strategy', 'popup_interval', 'status', 'starttime', 'endtime', 'weigh'],
        'edit' => ['school_id', 'title', 'description', 'image', 'popup_strategy', 'popup_interval', 'status', 'starttime', 'endtime', 'weigh'],
    ];
}
