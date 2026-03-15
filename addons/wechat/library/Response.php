<?php

namespace addons\wechat\library;

use addons\signin\model\Signin;
use addons\third\model\Third;
use app\common\model\User;
use EasyWeChat\Factory;
use EasyWeChat\Kernel\Messages\News;
use EasyWeChat\Kernel\Messages\NewsItem;
use fast\Date;
use fast\Http;
use fast\Random;
use think\Session;
use think\Config;

/**
 * 微信服务类
 */
interface Response
{
    public function available();

    public function config();

    public function response($obj, $openid, $message, $content, $context, $matches = null, $keyword = '');
}
