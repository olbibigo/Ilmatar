/*global _, $, require, jqGridDataReadUrl, jqGridColNames, jqGridColModels, jqGridSortName, jqGridSortOrder, jqGridName, jqInitialFilter, jqGridUserDataOnFooter, jqGridFooterData, jqGridColGroups, notify, aftersavefunc, editfunc, csrfToken, createAndDisplayFlashMessage, jqGridDataWriteUrl*/

"use strict";

function saveJqGridRow() {
    var selRow = $(jqGridListId).jqGrid('getGridParam', 'selrow');
    if (null === selRow) {
        notify('warning', _("A row must be selected to perform this operation."));
    } else {
        $(jqGridListId).jqGrid(
            'saveRow',
            selRow,
            {
                aftersavefunc : aftersavefunc,
                extraparam : {
                    oper : (selRow === "new_row") ? 'add' : 'edit'
                }
            }
        );
    }
}

function addJqGridRow() {
    $(jqGridListId).jqGrid('addRow', {
        rowID : "new_row",
        addRowParams : {
            oneditfunc : editfunc
        }
    });
}

function deleteJqGridRow(fieldsToCheck) {
    var selRow = $(jqGridListId).jqGrid('getGridParam', 'selrow');
    if (null === selRow) {
        notify('warning', _("A row must be selected to perform this operation."));
    } else {
        var isBroken = false;
        $.each(fieldsToCheck, function (idx, key) {
            var value = $(jqGridListId).jqGrid('getCell', selRow, key),
                isCounter = (key.substring(0, 3) === 'nb_');
            if ((isCounter && (parseInt(value, 10) !== 0)) || (!isCounter && (value.length !== 0))) {
                var cm = $(jqGridListId).jqGrid("getGridParam", "colModel");
                $.each(cm, function (idx2, col) {
                    if (col.name === key) {
                        var cn = $(jqGridListId).jqGrid("getGridParam", "colNames");
                        notify('warning', _("This operation is not possible on this entity: '%field%' is not empty.", {'%field%' : cn[idx2]}));
                    }
                });
                isBroken = true;
                return false;
            }
        });
        if (isBroken) {
            return;
        }
        $(jqGridListId).jqGrid(
            'delGridRow',
            selRow,
            {
                modal : true,
                width : 500,
                top : Math.floor($(window).height() / 2) - 50,
                left : Math.floor($(window).width() / 2) - 250,
                delData : {
                    csrfToken : csrfToken
                },
                reloadAfterSubmit : false,
                afterSubmit : aftersavefunc,
                afterShowForm : function (formid) {
                    //Changes opacity
                    $(".ui-widget-overlay").css('opacity', 0.8);
                }
            }
        );
    }
}

function aftersavefunc(response, rowId) {
    //Closes modal window if exist
    //Should work automatically when clicking but ... (jqGrid bug?)
    $(".ui-widget-overlay").remove();
    $("#delmodlist").remove();

    //Sometimes arguments are permutted (jqGrid bug?)
    if (typeof response === 'string') {
        response = rowId;
    }
    if (response.responseJSON) {
        response = response.responseJSON;
    }
    processMessage(response, _("Object has been correctly processed (id: %id%).", {'%id%' : response.id}));

    $(jqGridListId).trigger('reloadGrid');
}

function editfunc() {
    require(["jquery-timepicker/i18n"], function (Jq) {
        Jq("input[id$=_date]").attr("readonly", "readonly").datepicker({
            dateFormat : Jq.jgrid.formatter.date.newformat.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yy'), //Datepicker format
            defaultDate : 0,
            minDate : null
        });
        Jq("input[id$=_at]").attr("readonly", "readonly").datetimepicker({
            dateFormat : Jq.jgrid.formatter.date.newformat.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yy'), //Datepicker format
            defaultDate : 0,
            minDate : null,
            timeFormat : 'HH:mm:ss'
        });
    });
}
//Manage grid in write mode
require(["jqgrid"], function (Jq) {
    var lastSel = null;
    Jq(jqGridListId).jqGrid({
        url : jqGridDataReadUrl,
        datatype : "json",
        mtype : 'POST',
        colNames : jqGridColNames,
        colModel : jqGridColModels,
        rowNum : 25,
        rowList : [25, 50, 100],
        pager : '#pager',
        sortname : jqGridSortName,
        sortorder : jqGridSortOrder,
        viewrecords : true,
        caption : jqGridName,
        altRows : true,
        height : 500,
        altclass : "zebra",
        hidegrid : false,
        autowidth : true,
        gridview : true,
        multiSort : true,
        rownumbers : true,
        rownumWidth : 40,
        sortable : true,
        //Editing related fields
        editurl : jqGridDataWriteUrl,
        inlineData : {
            csrfToken : csrfToken
        },
        postData : {
            filters : jqInitialFilter
        },
        search : (jqInitialFilter !== '[]'),
        onSelectRow : function (id) {
            if (id && (id !== lastSel)) {
                Jq(jqGridListId).jqGrid('restoreRow', lastSel);
                lastSel = id;
            }
            Jq(jqGridListId).jqGrid('editRow', id, {
                keys : false,
                oneditfunc : editfunc,
                successfunc : function (obj) {
                    aftersavefunc(obj.responseJSON, id);
                }
            });
        },
        footerrow : jqGridUserDataOnFooter || ((jqGridFooterData !== 'undefined') && !Jq.isEmptyObject(jqGridFooterData)),
        userDataOnFooter : jqGridUserDataOnFooter,
        gridComplete : editfunc,
        rowattr : function (rd) {
            //Read only row management
            var isEditable = true;
            if (rd.is_readonly !== undefined) {
                var cm = Jq(jqGridListId).jqGrid("getGridParam", "colModel");
                Jq.each(cm, function (idx, col) {
                    if (col.name === 'is_readonly') {
                        var yes = col.editoptions.value.split(':');
                        if (rd.is_readonly === yes[0]) {
                            isEditable = false;
                        }
                        return;
                    }
                });
                if (!isEditable) {
                    return {
                        "class" : "not-editable-row"
                    };
                }
            }
        }
    }).jqGrid('filterToolbar', {
        searchOperators : true,
        searchOnEnter : true,
        stringResult : true
    }).jqGrid(
        'navGrid',
        '#pager',
        {
            search : (jqInitialFilter !== '[]'),
            edit : false,
            add : false,
            del : false,
            view : true,
            refresh : true
        },
        {},
        {},
        {},
        {
            multipleSearch : true
        }
    ).jqGrid(
        'navButtonAdd',
        '#pager',
        {
            caption : "",
            buttonicon : "ui-icon-calculator",
            title : _("Choose Columns"),
            onClickButton : function () {
                var width = Jq(jqGridListId).width();
                Jq(jqGridListId).jqGrid(
                    'columnChooser',
                    {
                        done : function (perm) {
                            Jq(jqGridListId).jqGrid('setGridWidth', width + 18);
                            editfunc();
                        }
                    }
                );
            }
        }
    ).jqGrid(
        'inlineNav',
        '#pager',
        {
            add : false,
            edit : false,
            cancel : false,
            save : false
        }
    );

    //Group headers
    if ((jqGridColGroups !== 'undefined') && !Jq.isEmptyObject(jqGridColGroups)) {
        Jq(jqGridListId).jqGrid('setGroupHeaders', {
            useColSpanStyle : false,
            groupHeaders : jqGridColGroups
        });
    }

    Jq("#presets").on('change', function (event) {
        var filter = Jq(this).val();
        Jq(jqGridListId).setGridParam({
            search : (filter !== '[]'),
            postData : {
                filters : filter
            }
        }).trigger("reloadGrid");

    });
});