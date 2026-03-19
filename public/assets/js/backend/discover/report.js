define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'discover/report/index' + location.search,
                    del_url: 'discover/report/del',
                    multi_url: 'discover/report/multi',
                    table: 'discover_report'
                }
            });

            var table = $('#table');
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'discover_id', title: __('Discover_id')},
                        {field: 'discover.title', title: __('Discover.title'), operate: 'LIKE'},
                        {field: 'reason', title: __('Reason'), operate: 'LIKE'},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'school.name', title: __('School.name'), operate: 'LIKE'},
                        {field: 'status', title: __('Status'), searchList: {'pending': __('Status pending'), 'handled': __('Status handled'), 'rejected': __('Status rejected')}, formatter: Table.api.formatter.normal},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'handled_time', title: __('Handled_time'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function (value, row, index) {
                            this.table.data('operate-edit', 0);
                            return Table.api.formatter.operate.call(this, value, row, index);
                        }}
                    ]
                ]
            });

            Table.api.bindevent(table);
        }
    };

    return Controller;
});
