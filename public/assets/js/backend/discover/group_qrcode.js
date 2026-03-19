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
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'school_id', title: __('School_id'), operate: false, formatter: function (value, row) {
                            return row.school && row.school.name ? row.school.name : __('Platform_default');
                        }},
                        {field: 'popup_strategy', title: __('Display_timing'), searchList: {'always': __('Always'), 'daily': __('Daily'), 'interval': __('Interval')}, formatter: function (value, row) {
                            if (value === 'interval') {
                                return __('Popup_timing_interval_prefix') + (row.popup_interval || 1) + __('Popup_timing_interval_suffix');
                            }
                            var map = {
                                'always': __('Popup_timing_always'),
                                'daily': __('Popup_timing_daily')
                            };
                            return map[value] || value;
                        }},
                        {field: 'status', title: __('Status'), searchList: {'normal': __('Normal'), 'hidden': __('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'starttime', title: __('Starttime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
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
            syncPopupStrategy: function (form) {
                var strategy = form.find("input[name='row[popup_strategy]']:checked").val() || 'daily';
                var intervalGroup = form.find('.js-popup-interval-group');
                if (strategy === 'interval') {
                    intervalGroup.show();
                    intervalGroup.find('input').attr('data-rule', 'required');
                    return;
                }
                intervalGroup.hide();
                intervalGroup.find('input').removeAttr('data-rule');
            },
            bindevent: function () {
                var form = $('form[role=form]');
                Form.api.bindevent(form);
                Controller.api.syncPopupStrategy(form);
                form.on('change', "input[name='row[popup_strategy]']", function () {
                    Controller.api.syncPopupStrategy(form);
                });
            }
        }
    };

    return Controller;
});
