<?php

namespace addons\wechat\library\response;

use addons\wechat\library\Response;

class Blog implements Response
{

    public function available()
    {
        $info = get_addon_info('blog');
        return $info && $info['state'];
    }

    public function config()
    {
        $disabled = $this->available() ? '' : 'disabled="disabled"';
        return [
            'name'   => '简单博客',
            'config' => [
                [
                    'type'    => 'text',
                    'caption' => '博客日志ID',
                    'field'   => 'post_id',
                    'rule'    => '',
                    'extend'  => 'class="form-control selectpage" data-source="blog/post/index" data-field="title" ' . $disabled,
                    'options' => '',
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索日志',
                    'field'   => 'searchpost',
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
            return "请先在后台管理安装并启用《简单博客》插件";
        }
        $entry = \addons\blog\model\Post::get($content['post_id']);
        if ($entry) {
            $entry['image'] = $entry['thumb'];
        }
        if (!$entry && $content['searchpost']) {
            $entry = \addons\blog\model\Post::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry) {
            return "未搜索到任何匹配信息$keyword" . json_encode($matches);
        }
        return $entry;
    }
}