<?php

namespace addons\wechat\library\response;

use addons\wechat\library\Response;

class Cms implements Response
{

    public function available()
    {
        $info = get_addon_info('cms');
        return $info && $info['state'];
    }

    public function config()
    {
        $disabled = $this->available() ? '' : 'disabled="disabled"';
        return [
            'name'   => 'CMS内容管理系统',
            'config' => [
                [
                    'type'    => 'text',
                    'caption' => '文章ID',
                    'field'   => 'archives_id',
                    'rule'    => '',
                    'extend'  => 'class="form-control selectpage" data-source="cms/archives/index" data-field="title" ' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'text',
                    'caption' => '单页ID',
                    'field'   => 'page_id',
                    'rule'    => '',
                    'extend'  => 'class="form-control selectpage" data-source="cms/page/index" data-field="title" ' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'text',
                    'caption' => '专题ID',
                    'field'   => 'special_id',
                    'rule'    => '',
                    'extend'  => 'class="form-control selectpage" data-source="cms/special/index" data-field="title" ' . $disabled,
                    'options' => ''
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索文章',
                    'field'   => 'searcharchives',
                    'rule'    => '',
                    'extend'  => '',
                    'options' => [
                        '1' => '是',
                        '0' => '否',
                    ],
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索单页',
                    'field'   => 'searchpage',
                    'rule'    => '',
                    'extend'  => '',
                    'options' => [
                        '1' => '是',
                        '0' => '否',
                    ],
                ],
                [
                    'type'    => 'radio',
                    'caption' => '开启搜索专题',
                    'field'   => 'searchspecial',
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
            return "请先在后台管理安装并启用《CMS内容管理系统》插件";
        }
        $entry = null;
        if (isset($content['archives_id']) && $content['archives_id']) {
            $entry = \addons\cms\model\Archives::get($content['archives_id']);
        } elseif (isset($content['page_id']) && $content['page_id']) {
            $entry = \addons\cms\model\Page::get($content['page_id']);
        } elseif (isset($content['special_id']) && $content['special_id']) {
            $entry = \addons\cms\model\Special::get($content['special_id']);
        }
        if (!$entry && $content['searcharchives']) {
            $entry = \addons\cms\model\Archives::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry && $content['searchpage']) {
            $entry = \addons\cms\model\Page::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry && $content['searchspecial']) {
            $entry = \addons\cms\model\Special::where("title|description", 'like', "%{$keyword}%")->where('status', 'normal')->find();
        }
        if (!$entry) {
            return "未搜索到任何匹配信息";
        }
        return $entry;
    }
}