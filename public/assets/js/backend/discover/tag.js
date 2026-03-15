define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'discover/tag/index' + location.search,
                    add_url: 'discover/tag/add',
                    edit_url: 'discover/tag/edit',
                    del_url: 'discover/tag/del',
                    multi_url: 'discover/tag/multi',
                    import_url: 'discover/tag/import',
                    table: 'discover_tag',
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
                        {field: 'name', title: __('Name'), operate: 'LIKE'},
                        // {field: 'number', title: __('Number')},
                        {field: 'ishotdata', title: __('Ishotdata'), searchList: {"0":__('Ishotdata 0'),"1":__('Ishotdata 1'),"2":__('Ishotdata 2')}, formatter: Table.api.formatter.normal},
                        {field: 'typedata', title: __('Typedata'), searchList: {"0":__('Typedata 0'),"1":__('Typedata 1')}, formatter: Table.api.formatter.normal},
                        {field: 'auditdata', title: __('Auditdata'), searchList: {"0":__('Auditdata 0'),"1":__('Auditdata 1'),"2":__('Auditdata 2')}, formatter: Table.api.formatter.normal},
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