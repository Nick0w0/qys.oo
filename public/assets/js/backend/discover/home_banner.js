define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var pageOptionMap = {
        '/pages/index/index': '首页',
        '/pages/index/search': '搜索页',
        '/pages/index/hot': '关注页',
        '/pages/plugin/index': '选学校页',
        '/pages/user/message': '消息页',
        '/pages/user/myattentions': '我关注的作者',
        '/pages/user/index': '我的页面'
    };

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
                        {field: 'title', title: __('Title'), operate: 'LIKE'},
                        {field: 'school_id', title: __('School_id'), operate: false, formatter: function (value, row) {
                            return row.school && row.school.name ? row.school.name : __('Platform_default');
                        }},
                        {field: 'jump_type', title: __('Click_action'), searchList: {'none': __('Jump_none'), 'path': __('Jump_path'), 'discover': __('Jump_discover')}, formatter: function (value) {
                            var map = {'none': __('Jump_none'), 'path': __('Jump_path'), 'discover': __('Jump_discover')};
                            return map[value] || value;
                        }},
                        {field: 'jump_value', title: __('Click_target'), operate: 'LIKE', formatter: function (value, row) {
                            if (row.jump_type === 'none' || !value) {
                                return __('Jump_target_empty');
                            }
                            if (row.jump_type === 'discover') {
                                return __('Jump_target_discover_prefix') + value;
                            }
                            return pageOptionMap[value] || value;
                        }},
                        {field: 'status', title: __('Status'), searchList: {'normal': __('Normal'), 'hidden': __('Hidden')}, formatter: Table.api.formatter.status},
                        {field: 'starttime', title: __('Starttime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'endtime', title: __('Endtime'), operate: 'RANGE', addclass: 'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'views', title: __('Views'), operate: false},
                        {field: 'clicks', title: __('Clicks'), operate: false},
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
            getCurrentSchoolId: function (form) {
                return $.trim(form.find('#c-school_id').val() || form.find("input[name='row[school_id]']").val() || '0');
            },
            getDiscoverWrap: function (form) {
                return form.find('#c-jump_value_discover_wrap');
            },
            getDiscoverKeywordInput: function (form) {
                return form.find('#c-jump_value_discover_keyword');
            },
            getDiscoverMenu: function (form) {
                return form.find('#c-jump_value_discover_menu');
            },
            buildDiscoverRequestData: function (form, keyword, extra) {
                var data = {
                    pageNumber: 1,
                    pageSize: 8,
                    andOr: 'OR',
                    showField: 'title',
                    keyField: 'id',
                    custom: {
                        school_id: Controller.api.getCurrentSchoolId(form)
                    }
                };
                if (keyword !== '') {
                    data.q_word = [keyword];
                    data.searchField = ['title', 'id'];
                } else {
                    data.q_word = [''];
                    data.searchField = ['title', 'id'];
                }
                if (extra) {
                    $.extend(true, data, extra);
                }
                return data;
            },
            fetchDiscoverList: function (form, keyword, extra, callback) {
                $.ajax({
                    url: 'discover/home_banner/selectDiscover',
                    type: 'POST',
                    dataType: 'json',
                    data: Controller.api.buildDiscoverRequestData(form, keyword, extra),
                    success: function (ret) {
                        callback(ret && $.isArray(ret.list) ? ret.list : []);
                    },
                    error: function () {
                        callback([]);
                    }
                });
            },
            renderDiscoverMenu: function (form, list) {
                var menu = Controller.api.getDiscoverMenu(form);
                if (!menu.length) {
                    return;
                }
                if (!list.length) {
                    menu.html('<div class="banner-discover-empty">未找到匹配帖子</div>').show();
                    return;
                }
                var html = $.map(list, function (item) {
                    var title = item && item.title ? item.title : '';
                    var id = item && item.id ? item.id : '';
                    return '<div class="banner-discover-item" data-id="' + id + '" data-title="' + $('<div/>').text(title).html() + '">#' + id + ' ' + $('<div/>').text(title).html() + '</div>';
                }).join('');
                menu.html(html).show();
            },
            hideDiscoverMenu: function (form) {
                Controller.api.getDiscoverMenu(form).hide().empty();
            },
            setDiscoverSelection: function (form, item) {
                var hiddenInput = form.find('#c-jump_value');
                var keywordInput = Controller.api.getDiscoverKeywordInput(form);
                var wrap = Controller.api.getDiscoverWrap(form);
                var id = item && item.id ? String(item.id) : '';
                var title = item && item.title ? String(item.title) : '';
                hiddenInput.val(id);
                keywordInput.val(id ? ('#' + id + ' ' + title) : '');
                wrap.data('selectedDiscoverId', id);
                wrap.data('selectedDiscoverText', id ? ('#' + id + ' ' + title) : '');
                Controller.api.hideDiscoverMenu(form);
            },
            clearDiscoverSelection: function (form, keepText) {
                var hiddenInput = form.find('#c-jump_value');
                var keywordInput = Controller.api.getDiscoverKeywordInput(form);
                var wrap = Controller.api.getDiscoverWrap(form);
                hiddenInput.val('');
                if (!keepText) {
                    keywordInput.val('');
                }
                wrap.removeData('selectedDiscoverId');
                wrap.removeData('selectedDiscoverText');
            },
            syncDiscoverSelectionState: function (form) {
                var wrap = Controller.api.getDiscoverWrap(form);
                var keywordInput = Controller.api.getDiscoverKeywordInput(form);
                var selectedText = wrap.data('selectedDiscoverText') || '';
                if ($.trim(keywordInput.val()) !== $.trim(selectedText)) {
                    Controller.api.clearDiscoverSelection(form, true);
                }
            },
            initDiscoverSelection: function (form) {
                var wrap = Controller.api.getDiscoverWrap(form);
                var hiddenValue = $.trim(form.find('#c-jump_value').val());
                var initialId = $.trim(wrap.data('initialId') || hiddenValue);
                if (!initialId) {
                    return;
                }
                Controller.api.fetchDiscoverList(form, '', {keyValue: initialId}, function (list) {
                    if (list.length) {
                        Controller.api.setDiscoverSelection(form, list[0]);
                    }
                });
            },
            buildDiscoverParams: function (form) {
                return {
                    custom: {
                        school_id: Controller.api.getCurrentSchoolId(form)
                    }
                };
            },
            syncJumpField: function (form) {
                var jumpType = form.find("input[name='row[jump_type]']:checked").val() || 'none';
                var hiddenInput = form.find("#c-jump_value");
                var pathInput = form.find("#c-jump_value_path");
                var discoverWrap = Controller.api.getDiscoverWrap(form);
                var noneBlock = form.find(".js-jump-target-none");
                var helpBlock = form.find(".js-jump-target-help");

                pathInput.hide().removeAttr('data-rule');
                discoverWrap.hide();
                noneBlock.hide();

                if (jumpType === 'path') {
                    pathInput.show().attr('data-rule', 'required');
                    hiddenInput.val($.trim(pathInput.val()));
                    helpBlock.text(__('Jump_path_help'));
                    return;
                }

                if (jumpType === 'discover') {
                    discoverWrap.show();
                    helpBlock.text(__('Jump_discover_help'));
                    return;
                }

                hiddenInput.val('');
                Controller.api.hideDiscoverMenu(form);
                noneBlock.show();
                helpBlock.text(__('Jump_none_help'));
            },
            bindevent: function () {
                var form = $('form[role=form]');
                Form.api.bindevent(form);
                Controller.api.initDiscoverSelection(form);
                Controller.api.syncJumpField(form);

                form.on('change', "input[name='row[jump_type]']", function () {
                    Controller.api.syncJumpField(form);
                });

                form.on('change', '#c-jump_value_path', function () {
                    if (form.find("input[name='row[jump_type]']:checked").val() === 'path') {
                        form.find('#c-jump_value').val($.trim($(this).val()));
                    }
                });

                form.on('focus input', '#c-jump_value_discover_keyword', function () {
                    if (form.find("input[name='row[jump_type]']:checked").val() !== 'discover') {
                        return;
                    }
                    Controller.api.syncDiscoverSelectionState(form);
                    Controller.api.fetchDiscoverList(form, $.trim($(this).val()), null, function (list) {
                        Controller.api.renderDiscoverMenu(form, list);
                    });
                });

                form.on('click', '.banner-discover-item', function () {
                    Controller.api.setDiscoverSelection(form, {
                        id: $(this).data('id'),
                        title: $(this).data('title')
                    });
                });

                form.on('change', '#c-school_id', function () {
                    Controller.api.clearDiscoverSelection(form);
                    if (form.find("input[name='row[jump_type]']:checked").val() === 'discover') {
                        Controller.api.syncJumpField(form);
                    }
                });

                $(document).off('mousedown.homeBannerDiscover').on('mousedown.homeBannerDiscover', function (e) {
                    var target = $(e.target);
                    if (!target.closest('#c-jump_value_discover_wrap').length) {
                        Controller.api.hideDiscoverMenu(form);
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
