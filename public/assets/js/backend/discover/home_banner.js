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
                        {field: 'jump_value', title: __('Jump_value'), operate: 'LIKE', formatter: function (value, row) {
                            if (row.jump_type === 'none' || !value) {
                                return __('Jump_target_empty');
                            }
                            if (row.jump_type === 'discover') {
                                return __('Jump_target_discover_prefix') + value;
                            }
                            return value;
                        }},
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
            getDiscoverValue: function (form) {
                var discoverInput = form.find("#c-jump_value_discover");
                var hidden = discoverInput.closest('.sp_container').find('.sp_hidden');
                return $.trim(hidden.length ? hidden.val() : discoverInput.val());
            },
            syncJumpField: function (form) {
                var jumpType = form.find("input[name='row[jump_type]']:checked").val() || 'none';
                var hiddenInput = form.find("#c-jump_value");
                var pathInput = form.find("#c-jump_value_path");
                var discoverInput = form.find("#c-jump_value_discover");
                var noneBlock = form.find(".js-jump-target-none");
                var helpBlock = form.find(".js-jump-target-help");

                pathInput.hide().removeAttr('data-rule');
                discoverInput.hide().removeAttr('data-rule');
                noneBlock.hide();

                if (jumpType === 'path') {
                    pathInput.show().attr('data-rule', 'required');
                    hiddenInput.val($.trim(pathInput.val()));
                    helpBlock.text(__('Jump_path_help'));
                    return;
                }

                if (jumpType === 'discover') {
                    discoverInput.show().attr('data-rule', 'required');
                    hiddenInput.val(Controller.api.getDiscoverValue(form));
                    helpBlock.text(__('Jump_discover_help'));
                    return;
                }

                hiddenInput.val('');
                noneBlock.show();
                helpBlock.text(__('Jump_none_help'));
            },
            bindevent: function () {
                var form = $('form[role=form]');
                Form.api.bindevent(form);
                Controller.api.syncJumpField(form);

                form.on('change', "input[name='row[jump_type]']", function () {
                    Controller.api.syncJumpField(form);
                });

                form.on('input change', '#c-jump_value_path', function () {
                    if (form.find("input[name='row[jump_type]']:checked").val() === 'path') {
                        form.find('#c-jump_value').val($.trim($(this).val()));
                    }
                });

                form.on('change', '#c-jump_value_discover', function () {
                    if (form.find("input[name='row[jump_type]']:checked").val() === 'discover') {
                        form.find('#c-jump_value').val(Controller.api.getDiscoverValue(form));
                    }
                });

                form.on('change', '.sp_hidden', function () {
                    if (form.find("input[name='row[jump_type]']:checked").val() === 'discover') {
                        form.find('#c-jump_value').val(Controller.api.getDiscoverValue(form));
                    }
                });

                form.on('submit', function () {
                    Controller.api.syncJumpField(form);
                });
            }
        }
    };

    return Controller;
});
