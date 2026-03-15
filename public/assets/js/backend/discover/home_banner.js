define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'discover/home_banner/index' + location.search,
                    add_url: 'discover/home_banner/add',
                    edit_url: 'discover/home_banner/edit',
                    del_url: 'discover/home_banner/del',
                    multi_url: 'discover/home_banner/multi',
                    table: 'home_banner'
                }
            });

            var table = $('#table');

            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'school_id', title: __('School_id'), operate: false, formatter: function (value, row) {
                            return row.school && row.school.name ? row.school.name : __('Platform_default');
                        }},
                        {field: 'jump_type', title: __('Jump_type'), searchList: {'none': __('Jump_none'), 'path': __('Jump_path'), 'discover': __('Jump_discover')}, formatter: function (value) {
                            var map = {'none': __('Jump_none'), 'path': __('Jump_path'), 'discover': __('Jump_discover')};
                            return map[value] || value;
                        }},
                        {field: 'jump_value', title: __('Jump_value'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {'normal': __('Normal'), 'hidden': __('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'starttime', title: __('Starttime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'views', title: __('Views'), operate: false},
                        {field: 'clicks', title: __('Clicks'), operate: false},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

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
                Form.api.bindevent($('form[role=form]'));
            }
        }
    };

    return Controller;
});