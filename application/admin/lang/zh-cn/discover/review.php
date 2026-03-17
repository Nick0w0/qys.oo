<?php

return [
    'Title'                    => '屏蔽词管理',
    'Word'                     => '屏蔽词',
    'Word placeholder'         => '请输入单个屏蔽词，例如：诈骗',
    'Remark'                   => '备注',
    'Remark placeholder'       => '可选，记录用途或处理说明',
    'Status'                   => '状态',
    'Status normal'            => '启用',
    'Status hidden'            => '停用',
    'Tips'                     => '使用说明',
    'Tips content'             => '命中范围：发帖标题、描述、正文，以及评论/回复内容。建议每个词单独维护一条记录，便于搜索、停用、导入和审计。',
    'Word already exist'       => '该屏蔽词已存在，请勿重复添加',
    'Word can not be empty'    => '屏蔽词不能为空',
    'Please run blocked words sql' => '请先执行 sql/20260316_discover_blocked_words_config.sql 补丁',
    'Legacy migrated remark'   => '从旧屏蔽词配置自动迁移',
];
