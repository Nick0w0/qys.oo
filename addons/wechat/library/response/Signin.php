<?php

namespace addons\wechat\library\response;

use addons\signin\library\Service;
use addons\third\model\Third;
use addons\wechat\library\Response;
use app\common\model\User;
use fast\Date;

class Signin implements Response
{

    public function available()
    {
        $info = get_addon_info('signin');
        return $info && $info['state'];
    }

    public function config()
    {
        $disabled = $this->available() ? '' : 'disabled="disabled"';
        return [
            'name'   => '会员签到送积分',
            'config' => []
        ];
    }

    public function response($obj, $openid, $message, $content, $context, $matches = null, $keyword = '')
    {
        $signinInfo = get_addon_info('signin');
        if (!$this->available()) {
            return "请先在后台管理安装并启用《会员签到》插件";
        }

        $thirdInfo = get_addon_info('third');
        if (!$thirdInfo || !$thirdInfo['state']) {
            return "请先在后台管理安装并启用《第三方登录》插件";
        }
        $user = $this->getUserByOpenid($openid);
        if (!$user) {
            return "请先在会员中心绑定微信登录，<a href='" . addon_url('third/index/connect', [':platform' => 'wechat'], true, true) . "'>点击这里绑定</a>";
        }

        if (version_compare($signinInfo['version'], '1.0.5', '<')) {
            return "请升级《会员签到》插件到最新版本";
        }

        $result = Service::dosign($user->id);
        if (isset($result['errmsg'])) {
            return $result['errmsg'];
        } else {
            return '签到成功!连续签到' . $result['successions'] . '天!获得' . $result['score'] . '积分';
        }
    }

    /**
     * 根据Openid获取用户信息
     * @param string $openid 微信OpenID
     * @return User|null
     */
    public function getUserByOpenid($openid)
    {
        $third = Third::where('platform', 'wechat')->where('openid', $openid)->find();
        if ($third && $third->user_id) {
            return User::get($third->user_id);
        }
        return null;
    }
}