<?php

namespace addons\wechat\library\response;

use addons\wechat\library\Response;

class Ask implements Response
{

    public function available()
    {
        $info = get_addon_info('ask');
        return $info && $info['state'];
    }

    public function config()
    {
        $disabled = $this->available() ? '' : 'disabled="disabled"';
        return [
            'name'   => '知识付费问答社区',
            'config' => [
                [
                    'type'    => 'text',
                    'caption' => '问题ID',
                    'field'   => 'question_id',
                    'extend'  => 'class="form-control selectpage" data-source="ask/question/index" data-field="title" ' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'text',
                    'caption' => '文章ID',
                    'field'   => 'article_id',
                    'extend'  => 'class="form-control selectpage" data-source="ask/article/index" data-field="title"' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索问题',
                    'field'   => 'searchquestion',
                    'rule'    => '',
                    'extend'  => '',
                    'options' => [
                        '1' => '是',
                        '0' => '否',
                    ],
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索文章',
                    'field'   => 'searcharticle',
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
            return "请先在后台管理安装并启用《知识付费问答》插件";
        }

        if (isset($content['question_id']) && $content['question_id']) {
            $entry = \addons\ask\model\Question::get($content['question_id']);
        } elseif (isset($content['article_id']) && $content['article_id']) {
            $entry = \addons\ask\model\Article::get($content['article_id']);
        }

        if (!$entry && $content['searchquestion']) {
            $entry = \addons\ask\model\Question::where("title", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry && $content['searcharticle']) {
            $entry = \addons\ask\model\Article::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry) {
            return "未搜索到任何匹配信息";
        }
        return $entry;
    }
}