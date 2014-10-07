/*global _, require, $, notify, aftersavefunc, createAndDisplayFlashMessage*/

"use strict";

function saveJqGridRow(gridId) {
    var selRow = $("#list" + gridId).jqGrid('getGridParam', 'selrow');
    if (null === selRow) {
        notify('warning', _("A row must be selected to perform this operation."));
    } else {
        $("#list" + gridId).jqGrid(
            'saveRow',
            selRow, {
            aftersavefunc : aftersavefunc,
            extraparam : {
                oper : 'edit'
            }
        });
    }
}

function aftersavefunc(response, rowId) {
    //Closes modal window if exist
    //Should work automatically when clicking but ... (jqGrid bug?)
    if ($("#delmodlist").length >= 1) {
        $(".ui-widget-overlay").first().remove();
        $("#delmodlist").remove();
    }

    //Sometimes arguments are permutted (jqGrid bug?)
    if (typeof response === 'string') {
        response = rowId;
    }
    if (response.responseJSON) {
        response = response.responseJSON;
    }
    processMessage(response, _("Object has been correctly processed (id: %id%).", {'%id%' : response.id}));
    
    $(".grid").trigger('reloadGrid');
}

//Manage grid in write mode
function loadGrid(gridId) {
    require(["jqgrid"], function (Jq) {
        var lastSel = null;
        Jq("#list" + gridId).jqGrid({
            loadonce : true,
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
            onSelectRow : function (id) {
                if (id && (id !== lastSel)) {
                    Jq("#list" + gridId).jqGrid('restoreRow', lastSel);
                    lastSel = id;
                }
                Jq("#list" + gridId).jqGrid('editRow', id, {
                    keys : false,
                    successfunc : function (obj) {
                        aftersavefunc(obj.responseJSON, id);
                    }
                });
            }
        });

        Jq(".btnAction").on("click", function () {
            eval(Jq(this).attr("data-action-data"));
        });
    });
}
