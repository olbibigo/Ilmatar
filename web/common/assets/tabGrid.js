/*global _, require*/

"use strict";

function loadGrid(gridId) {
    require(["jqgrid"], function (Jq) {
        var lastSel = null;
        Jq("#list" + gridId).jqGrid({
            loadonce : true,
            url : window['jqGridDataReadUrl' + gridId],
            datatype : "json",
            mtype : 'POST',
            colNames : window['jqGridColNames' + gridId],
            colModel : window['jqGridColModels' + gridId],
            caption : window['jqGridName' + gridId],
            height : "auto",
            hidegrid : false,
            autowidth : true,
            gridview : true,
            rownumbers : true,
            rownumWidth : 40,
        });
    });
}
