define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'discover/group_qrcode/index' + location.search,
                    add_url: 'discover/group_qrcode/add',
                    edit_url: 'discover/group_qrcode/edit',
                    del_url: 'discover/group_qrcode/del',
                    multi_url: 'discover/group_qrcode/multi',
                    table: 'school_group_qrcode'
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
                        {field: 'popup_strategy', title: __('Popup_strategy'), searchList: {'always': __('Always'), 'daily': __('Daily'), 'interval': __('Interval')}, formatter: function (value) {
                            var map = {'always': __('Always'), 'daily': __('Daily'), 'interval': __('Interval')};
                            return map[value] || value;
                        }},
                        {field: 'popup_interval', title: __('Popup_interval'), operate: false},
                        {field: 'status', title: __('Status'), searchList: {'normal': __('Normal'), 'hidden': __('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'starttime', title: __('Starttime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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
