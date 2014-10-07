/*global _, $, require, ajax_download, exportUrl, executeUrl, createAndDisplayFlashMessage*/

"use strict";

function exportGrid() {
    //Displays the export form and launches the export command
    require(["jquery-ui"], function (Jq) {
        Jq("#exportGridForm").dialog({
            autoOpen : false,
            width : 500,
            modal : true,
            draggable : false,
            resizable : false,
            buttons : [{
                    text : _('Export'),
                    click : function () {
                        ajaxDownload(
                            exportUrl, {
                            exportFormat : Jq('input[type=radio][name=exportFormat]:checked').val()
                        });
                        Jq("#exportGridForm").dialog("close");
                    }
                }
            ],
            focus: function() {
                Jq('.ui-dialog-buttonpane').find('button:first-child').button({
                    icons: {primary: 'ui-icon-gear'}
                });
            }
        });
        $("#exportGridForm").dialog("open");
    });
}

function executeQuery() {
    //Executes a query in Ajax and create dynamically the JqGrid containing results
    require(["jqgrid"], function (Jq) {
        Jq.getJSON(executeUrl, function (data) {
            var jqGridColNames = [],
            jqGridColModels = [],
            k;
            if (data[0] !== 'undefined') {
                for (k in data[0]) {
                    jqGridColNames.push(k);
                    jqGridColModels.push({
                        name : k,
                        sortable : false
                    });
                }
            }

            Jq("#list").empty().jqGrid({
                datatype : "local",
                colNames : jqGridColNames,
                colModel : jqGridColModels,
                viewrecords : true,
                caption : _("Query results"),
                shrinkToFit : true,
                altRows : true,
                height : "auto",
                altclass : "zebra",
                hidegrid : false,
                autowidth : true,
                gridview : true,
                rownumbers : true,
                rownumWidth : 40,
                sortable : true
            });
            Jq.each(data, function (index, value) {
                Jq("#list").jqGrid('addRowData', index + 1, value);
            });

            createAndDisplayFlashMessage('success', _('Query executed and results displayed successfully.'));
        });
    });
}

require(["jquery-ui"], function (Jq) {
    Jq("#query_visibility").buttonset();
    Jq("#query_visibility .ui-button").each(function(){$(this).width(200);});
    
    Jq('#query_is_exported').on("change", function () {
        var isDisabled = !Jq(this).prop('checked');
        Jq('#query_export_format, #query_mail_list, #query_mail_repeats, #query_mail_offset_selector').prop('disabled', isDisabled);
    });
    
    Jq('#query_mail_repeats').on("change", function () {
        var value = Jq(this).val(),
            days = ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday", "Sunday"],
            options = '',
            i;
        
        switch (value) {
            case '1': //every week
                for (i = 0; i <= 6; i=i+1) {
                    options += '<option value="' + i + '">' + _(days[i]) + '</option>';
                }
                break;
            case '2': //every month
                for (i = 1; i <= 31; i=i+1) {
                    options += '<option value="' + i + '">' + (i < 10 ? '0' : '') + i + '</option>';
                }
                break;
            default: //everyday
                //nothing
                break;
        }
        Jq('#query_mail_offset_selector').empty().append(options);
    });
    Jq('#query_mail_offset_selector').on("change", function () {
        Jq('#query_mail_offset').val(Jq(this).val());
    });
    Jq('#query_is_exported').trigger("change");
    Jq('#query_mail_repeats').trigger("change");
    Jq('#query_mail_offset_selector').val(Jq('#query_mail_offset').val());
});
