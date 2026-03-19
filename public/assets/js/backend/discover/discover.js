define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            Table.api.init({
                extend: {
                    index_url: 'discover/discover/index' + location.search,
                    add_url: 'discover/discover/add',
                    edit_url: 'discover/discover/edit',
                    del_url: 'discover/discover/del',
                    multi_url: 'discover/discover/multi',
                    import_url: 'discover/discover/import',
                    table: 'discover'
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
                        {field: 'school.name', title: __('School.name'), operate: 'LIKE'},
                        {field: 'user.nickname', title: __('User.nickname'), operate: 'LIKE'},
                        {field: 'discovertag.name', title: __('Discovertag.name'), operate: 'LIKE', formatter: Table.api.formatter.flag},
                        {field: 'audit_status', title: __('Audit_status'), searchList: {'pending': __('Audit_status pending'), 'approved': __('Audit_status approved'), 'rejected': __('Audit_status rejected')}, formatter: Table.api.formatter.normal},
                        {field: 'is_top', title: __('Is_top'), searchList: {'0': __('Is_top 0'), '1': __('Is_top 1')}, formatter: Table.api.formatter.normal},
                        {field: 'statusdata', title: __('Statusdata'), searchList: {'1': __('Statusdata 1'), '2': __('Statusdata 2'), '3': __('Statusdata 3')}, formatter: Table.api.formatter.normal},
                        {field: 'city', title: __('City'), operate: 'LIKE'},
                        {field: 'address', title: __('Address'), operate: 'LIKE'},
                        {field: 'coverimage', title: __('Coverimage'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'coverimages', title: __('Coverimages'), operate: false, events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'createtime', title: __('Createtime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'updatetime', title: __('Updatetime'), operate: 'RANGE', addclass: 'datetimerange', autocomplete: false, formatter: Table.api.formatter.datetime},
                        {field: 'weigh', title: __('Weigh'), operate: false},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            buttons: [
                                {
                                    name: 'top',
                                    text: __('Top now'),
                                    title: __('Set top on'),
                                    icon: 'fa fa-arrow-up',
                                    classname: 'btn btn-success btn-xs btn-click',
                                    confirm: __('Confirm top post'),
                                    visible: function (row) {
                                        return String(row.is_top) !== '1';
                                    },
                                    click: function (options, row) {
                                        Backend.api.ajax({
                                            url: 'discover/discover/multi',
                                            data: {ids: row.id, params: 'is_top=1'}
                                        }, function () {
                                            $('#' + options.tableId).bootstrapTable('refresh');
                                        });
                                    }
                                },
                                {
                                    name: 'untop',
                                    text: __('Untop now'),
                                    title: __('Set top off'),
                                    icon: 'fa fa-arrow-down',
                                    classname: 'btn btn-warning btn-xs btn-click',
                                    confirm: __('Confirm untop post'),
                                    visible: function (row) {
                                        return String(row.is_top) === '1';
                                    },
                                    click: function (options, row) {
                                        Backend.api.ajax({
                                            url: 'discover/discover/multi',
                                            data: {ids: row.id, params: 'is_top=0'}
                                        }, function () {
                                            $('#' + options.tableId).bootstrapTable('refresh');
                                        });
                                    }
                                }
                            ],
                            formatter: Table.api.formatter.operate
                        }
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
