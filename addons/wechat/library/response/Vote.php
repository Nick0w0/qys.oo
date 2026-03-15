<?php

namespace addons\wechat\library\response;

use addons\wechat\library\Response;

class Vote implements Response
{

    public function available()
    {
        $info = get_addon_info('vote');
        return $info && $info['state'];
    }

    public function config()
    {
        $disabled = $this->available() ? '' : 'disabled="disabled"';
        return [
            'name'   => '在线投票系统',
            'config' => [
                [
                    'type'    => 'text',
                    'caption' => '投票主题ID',
                    'field'   => 'subject_id',
                    'extend'  => 'class="form-control selectpage" data-source="vote/subject/index" data-field="title" ' . $disabled,
                    'rule'    => '',
                    'options' => ''
                ],
                [
                    'type'    => 'text',
                    'caption' => '参赛人员ID',
                    'field'   => 'player_id',
                    'rule'    => '',
                    'extend'  => 'class="form-control selectpage" data-source="vote/player/index" data-field="nickname" ' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索主题',
                    'field'   => 'searchsubject',
                    'rule'    => '',
                    'extend'  => '',
                    'options' => [
                        '1' => '是',
                        '0' => '否',
                    ],
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索参赛人员',
                    'field'   => 'searchplayer',
                    'rule'    => '',
                    'extend'  => '',
                    'options' => [
                        '1' => '是',
                        '0' => '否',
                    ],
                ],
                [
                    'type'         => 'text',
                    'caption'      => '正则搜索匹配索引',
                    'field'        => 'searchregexindex',
                    'rule'         => '',
                    'defaultvalue' => '1',
                    'extend'       => '',
                    'options'      => [],
                ]
            ]
        ];
    }

    public function response($obj, $openid, $message, $content, $context, $matches = null, $keyword = '')
    {
        if (!$this->available()) {
            return "请先在后台管理安装并启用《在线投票系统》插件";
        }
        if (isset($content['subject_id']) && $content['subject_id']) {
            $entry = \addons\vote\model\Subject::all($content['subject_id']);
        } elseif (isset($content['player_id']) && $content['player_id']) {
            $entry = \addons\vote\model\Player::all($content['player_id']);
        }

        if (!$entry && $content['searchsubject']) {
            $entry = \addons\vote\model\Subject::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry && $content['searchplayer']) {
            $entry = \addons\vote\model\Player::where("nickname", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }

        if (!$entry) {
            return "未搜索到任何匹配信息";
        }
        return $entry;
    }
}