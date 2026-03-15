define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'discover/favor/index' + location.search,
                    add_url: 'discover/favor/add',
                    edit_url: 'discover/favor/edit',
                    del_url: 'discover/favor/del',
                    multi_url: 'discover/favor/multi',
                    import_url: 'discover/favor/import',
                    table: 'discover_favor',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'typedata', title: __('Typedata'), searchList: {"1":__('Typedata 1'),"2":__('Typedata 2')}, formatter: Table.api.formatter.normal},
                        {field: 'discover_id', title: __('Discover_id')},
                        {field: 'discover.title', title: __('Discover.title'), operate: 'LIKE'},
                        // {field: 'comment_id', title: __('Comment_id')},
                        // {field: 'user_id', title: __('User_id')},
                         {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', autocomplete:false, formatter: Table.api.formatter.datetime},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});