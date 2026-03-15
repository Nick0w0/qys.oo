<?php

namespace app\admin\validate\discover;

use think\Validate;

class School extends Validate
{
    protected $rule = [
        'name'       => 'require|max:100',
        'short_name' => 'max:50',
        'province'   => 'max:100',
        'city'       => 'max:100',
        'area'       => 'max:100',
        'address'    => 'max:255',
        'latitude'   => 'float',
        'longitude'  => 'float',
        'status'     => 'require|in:normal,hidden',
        'weigh'      => 'number',
    ];

    protected $scene = [
        'add'  => ['name', 'short_name', 'province', 'city', 'area', 'address', 'latitude', 'longitude', 'status', 'weigh'],
        'edit' => ['name', 'short_name', 'province', 'city', 'area', 'address', 'latitude', 'longitude', 'status', 'weigh'],
    ];
}
