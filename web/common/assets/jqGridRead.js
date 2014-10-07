"use strict";
//Manage grid in read only mode
function editfunc ()
{
    require(["jquery-timepicker/i18n"], function(Jq) {
        Jq("input[id*=date]").attr("readonly", "readonly").datetimepicker({
            dateFormat: Jq.jgrid.formatter.date.newformat.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yy'),//Datepicker format
            timeFormat: 'HH:mm:ss',
            //dateFormat: 'yy-mm-dd',
            defaultDate: 0,
            minDate: null
        });
        Jq("input[id$=_at]").attr("readonly", "readonly").datetimepicker({
            dateFormat: Jq.jgrid.formatter.date.newformat.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yy'),//Datepicker format
            defaultDate: 0,
            minDate: null,
            timeFormat: 'HH:mm:ss'
        });
    });
}

require(["jqgrid"], function(Jq) {
    Jq(jqGridListId).jqGrid({
        url: jqGridDataReadUrl,
        datatype: "json",
        mtype: 'POST',
        colNames: jqGridColNames,
        colModel: jqGridColModels,
        rowNum:25,
        rowList:[25,50,100],
        pager: '#pager',
        sortname: jqGridSortName,
        sortorder: jqGridSortOrder,
        viewrecords: true,
        caption: jqGridName,
        shrinkToFit: true,
        altRows: true,
        height: "auto",
        altclass : "zebra",
        hidegrid: false,
        autowidth: true,
        gridview: true,
        multiSort: true,
        rownumbers: true,
        rownumWidth: 40,
        sortable: true,
        postData: {filters : jqInitialFilter},
        search: (jqInitialFilter != '[]'),
        footerrow : jqGridUserDataOnFooter || ((typeof jqGridFooterData != 'undefined') && !Jq.isEmptyObject(jqGridFooterData)),
        ondblClickRow: function(rowid, iRow, iCol, e){
            Jq(".btnActionEdit").trigger("click");
        },
        userDataOnFooter: jqGridUserDataOnFooter,
        // datatype : "jsonstring",
        // datastr: jqGridData,
        // loadBeforeSend: function (xhr, settings) {
        // this.p.loadBeforeSend = null; //remove event handler
        // return false; // dont send load data request
        // },
        beforeProcessing:  function (xhr, status, error) {
            if (typeof xhr.error === 'undefined' || xhr.error === false) {
                return true;
            }
            notify('error', xhr.message);
            return false;
        },
        // beforeRequest: function () {
        // var i, l, rules, rule, $grid = $('#list'),

        // postData = $grid.jqGrid('getGridParam', 'postData'),
        // filters = $.parseJSON(postData.filters);

        // if (filters && typeof filters.rules !== 'undefined' && filters.rules.length > 0) {
        // rules = filters.rules;
        // for (i = 0; i < rules.length; i++) {
        // rule = rules[i];
        // if (rule.field === 'id') {
        // make modifications only for the 'contains' operation
        // rule.field = '2';
        // }
        // }
        // postData.filters = JSON.stringify(filters);
        // return true;
        // }
        // },
        gridComplete : editfunc
    }).jqGrid('filterToolbar', {
            searchOperators: true,
            searchOnEnter: true,
            stringResult: true
        }
    ).jqGrid(
        'navGrid',
        '#pager',
        {
            search: (jqInitialFilter != '[]'),
            edit: false,
            add: false,
            del: false,
            view:true,
            refresh: true
        },
        {},
        {},
        {},
        {multipleSearch:true}
    ).jqGrid (
        'navButtonAdd',
        '#pager',
        {
            caption: "",
            buttonicon: "ui-icon-calculator",
            title: _("Choose Columns"),
            onClickButton: function() {
                var width = jQuery(jqGridListId).width();
                Jq(jqGridListId).jqGrid(
                    'columnChooser',
                    {
                        done:function (perm) {
                            Jq(jqGridListId).jqGrid('setGridWidth', width + 18);
                            editfunc();
                        }
                    }
                );
            }
        }
    );

    //Group headers
    if ((typeof jqGridColGroups != 'undefined') && !Jq.isEmptyObject(jqGridColGroups)) {
        Jq(jqGridListId).jqGrid('setGroupHeaders', {
            useColSpanStyle: false,
            groupHeaders: jqGridColGroups
        });
    }

    Jq("#presets").on('change', function(event) {
        var filter = $(this).val();
        $(jqGridListId).setGridParam({
            search: (filter != '[]'),
            postData: {filters : filter}
        }).trigger("reloadGrid");
    });
});
