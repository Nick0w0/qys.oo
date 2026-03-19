<?php

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// [ 后台入口文件 ]
// 使用此文件可以达到隐藏admin模块的效果
// 为了你的安全，强烈不建议将此文件名修改成admin.php
// 定义应用目录
define('APP_PATH', __DIR__ . '/../application/');

if (empty($_SERVER['PATH_INFO']) && !empty($_SERVER['REQUEST_URI']) && !empty($_SERVER['SCRIPT_NAME'])) {
    $requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (is_string($requestUri) && strpos($requestUri, $_SERVER['SCRIPT_NAME']) === 0) {
        $_SERVER['PATH_INFO'] = substr($requestUri, strlen($_SERVER['SCRIPT_NAME']));
    }
}

// 判断是否安装
if (!is_file(APP_PATH . 'admin/command/Install/install.lock')) {
    header("location:./install.php");
    exit;
}

// 加载框架引导文件
require __DIR__ . '/../thinkphp/base.php';

// 绑定到admin模块
\think\Route::bind('admin');

// 关闭路由
\think\App::route(false);

// 设置根url
\think\Url::root('');

// 执行应用
\think\App::run()->send();
