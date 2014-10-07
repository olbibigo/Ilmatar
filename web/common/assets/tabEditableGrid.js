/*global _, require, $, notify, aftersavefunc*/

"use strict";

function saveJqGridRow(gridId) {
    var selRow = $("#list" + gridId).jqGrid('getGridParam', 'selrow');
    if (null === selRow) {
        notify('warning', _("A row must be selected to perform this operation."));
    } else {
        $("#list" + gridId).jqGrid(
            'saveRow',
            selRow, {
            aftersavefunc : function (id, obj) {
                aftersavefunc(obj.responseJSON, id, gridId);
            },
                extraparam : {
                    oper : (selRow === "new_row") ? 'add' : 'edit'
                }
        });
    }
}

function aftersavefunc(response, rowId, gridId) {
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
    
    $("#list" + gridId).trigger('reloadGrid');
}

function addJqGridRow(gridId) {
    $("#list" + gridId).jqGrid('addRow', {
        rowID : "new_row",
        addRowParams : {
            oneditfunc : editfunc
        }
    });
}

function editfunc() {
    require(["jquery-timepicker/i18n"], function (Jq) {
        Jq("input[id$=_at]").attr("readonly", "readonly").datetimepicker({
            dateFormat : Jq.jgrid.formatter.date.newformat.replace('d', 'dd').replace('m', 'mm').replace('Y', 'yy'), //Datepicker format
            defaultDate : 0,
            minDate : null,
            timeFormat : 'HH:mm:ss'
        });
    });
}

//Manage grid in write mode
function loadEditableGrid(gridId) {
    require(["jqgrid"], function (Jq) {
        var lastSel = null;
        Jq("#list" + gridId).jqGrid({
            rowNum : -1,
            url : window['jqGridDataReadUrl' + gridId],
            datatype : "json",
            mtype : 'POST',
            colNames : window['jqGridColNames' + gridId],
            colModel : window['jqGridColModels' + gridId],
            caption : window['jqGridName' + gridId],
            altRows : true,
            height : "auto",
            altclass : "zebra",
            hidegrid : false,
            autowidth : true,
            gridview : true,
            rownumbers : true,
            rownumWidth : 40,
            //Editing related fields
            editurl : window['jqGridDataWriteUrl' + gridId],
            inlineData : {
                csrfToken : window['csrfToken' + gridId]
            },
            gridComplete : editfunc,
            onSelectRow : function (id) {
                if (id && (id !== lastSel)) {
                    Jq("#list" + gridId).jqGrid('restoreRow', lastSel);
                    lastSel = id;
                }
                Jq("#list" + gridId).jqGrid('editRow', id, {
                    keys : false,
                    oneditfunc : editfunc,
                    successfunc : function (obj) {
                        aftersavefunc(obj.responseJSON, id, gridId);
                    }
                });
            }
        });

        Jq(".btnAction").on("click", function () {
            eval(Jq(this).attr("data-action-data"));
        });
    });
}
