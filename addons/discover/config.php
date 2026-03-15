<?php

return [
    [
        'name' => 'wechat',
        'title' => '微信',
        'type' => 'array',
        'content' => [
            'app_id' => '',
            'app_secret' => '',
            'callback' => '',
            'scope' => 'snsapi_base',
        ],
        'value' => [
            'app_id' => 'wx551*****7fcf',
            'app_secret' => 'bd2da***d7',
            'scope' => 'snsapi_userinfo',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
    [
        'name' => 'tmapkey',
        'title' => '腾讯地图key',
        'type' => 'array',
        'content' => [
            'key' => '',
        ],
        'value' => [
            'key' => '',
        ],
        'rule' => 'required',
        'msg' => '',
        'tip' => '',
        'ok' => '',
        'extend' => '',
    ],
];
