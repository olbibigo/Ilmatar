/*global _, $, require, notify, jqGridRowIdUrlParam, processPostTabLoad, createAndDisplayFlashMessage, tinymce, editUserSettingsUrl, noty*/

"use strict";

/*
 * Reset form input
 */
function resetInput(id, parentId) {
    var elem = $('#' + id);
    elem.wrap('<form>').closest('form').get(0).reset();
    elem.unwrap();
    if (parentId) {
        $('#' + parentId).hide();
    }
}
/*
 * Genric download using AJAX request
 */
function ajaxDownload(url, data) {
    var iframe_doc,
        iframe_html,
        iframe = $('#download_iframe');

    if (iframe.length === 0) {
        iframe = $("<iframe id='download_iframe'" +
            " style='display: none' src='about:blank'></iframe>").appendTo("body");
    } else {
        iframe.empty();
    }
    iframe_doc = iframe[0].contentWindow || iframe[0].contentDocument;
    if (iframe_doc.document) {
        iframe_doc = iframe_doc.document;
    }
    iframe_html = "<html><head></head><body><form method='POST' action='" + url + "'>";
    if (data) {
        Object.keys(data).forEach(function (key) {
            iframe_html += "<input type='hidden' name='" + key + "' value='" + data[key] + "'>";
        });
    }
    iframe_html += "</form></body></html>";

    iframe_doc.open();
    iframe_doc.write(iframe_html);
    $(iframe_doc).find('form').submit();
}
/*
 * Generic ajax call and response processing
 */
function ajaxGetCall(url, callback) {
    $.get(url).done(function (data) {
        var fn = window[callback](data);
    });
}
function processMessage(response, message) {
    if (response.responseJSON) {
        response = response.responseJSON;
    }
    if ($.isPlainObject(response)) {
        if (true === response.error) {
            createAndDisplayFlashMessage('error', _(response.message));
        } else if (message) {
            if (message.substring(0, 9) === 'redirect:') {
                window.location.replace(message.substring(9));
            } else {
                createAndDisplayFlashMessage('success', message);
            }
        } else {
            createAndDisplayFlashMessage('success', _(response.message));
        }
    } else {
        createAndDisplayFlashMessage('error', _("Unexpected response from server."));
    }
}

/*
 * Preview all kind of document
 */
function previewDocument(path, mimeType) {
    $('#previewEmbed').attr('src', path).attr('type', mimeType);
    $("#previewContainer").dialog({
        autoOpen: false,
        width: 800,
        height: 600,
        modal: true,
        draggable: false,
        resizable: false
    });
    $("#previewContainer").dialog("open");    
}

//Manages button's hover event in main menu
require(["jquery-ui"], function(Jq){
    var $objLinkMenu = $("#menus.ui-widget-content ul > li > a");
    if (undefined !== $objLinkMenu) {
        $objLinkMenu.hover(
            function(){ Jq(this).addClass("ui-state-hover"); },
            function(){ Jq(this).removeClass("ui-state-hover"); }
        );
    }
});

//Manages button action
require(["jquery-ui", "tinymce"], function (Jq, tinymce) {
    Jq(".btnAction").on("click", function () {
        switch (Jq(this).attr("data-action-type")) {
            case "open-modal-window": //Opens a modal window
                Jq("#" + Jq(this).attr("data-action-data")).dialog("open");
                break;
            case "open-page": //Opens a new page
                var page = Jq(this).attr("data-action-data");
                if (typeof jqGridRowIdUrlParam !== "undefined") {
                    if (-1 !== page.indexOf(jqGridRowIdUrlParam)) {
                        var selRow = Jq("#list").jqGrid('getGridParam', 'selrow');
                        if (null === selRow) {
                            notify('warning', _("A row must be selected to perform this operation."));
                            return;
                        }
                        page = page.replace(
                            jqGridRowIdUrlParam,
                            Jq("#list").jqGrid('getCell', selRow, 'id'));
                        window.location.href = page;
                        return;
                    }
                }
                window.location.href = page;
                break;
            case "execute": //Executes a JS function
                eval(Jq(this).attr("data-action-data"));
                break;
            default:
            //Nothing
            break;
        }
    });
    //Tab management
    var tabs = Jq("#tabs").tabs({
        beforeLoad: function (event, ui) {
            ui.panel.html('<img alt="' + _("Loading...") + '" src="/common/img/spinner.gif" />');
        },
        load: function (event, ui) {
            if (typeof processPostTabLoad === 'function') {
                processPostTabLoad(ui.panel, ui.tab);
            }
        }
    });
    //Allows sortable tabs
    tabs.find(".ui-tabs-nav").sortable({
        axis: "x",
        stop: function () {
            tabs.tabs("refresh");
        }
    });
    //Displays TinyMCE
    tinymce.baseURL = "/common/assets/tinymce";
    tinymce.suffix = '.min';
    tinymce.init({
        theme: "modern",
        menubar: false,
        selector: "textarea.editmce",
        plugins: ["code"],
        toolbar: "undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link | code",
        entity_encoding: "raw"
    });
    //User Settings
    Jq('#logged').on('click',function () {
        Jq("#userSettingForm").dialog({
            autoOpen: false,
            width: 500,
            modal: true,
            draggable: false,
            resizable: false,
            buttons: []//No buttons
        });
        Jq("#userSettingForm").dialog("open");
    }).css('cursor', 'pointer');
    Jq('#landingPage').on('click',function () {
        Jq("#userSettingForm").dialog("close");
        Jq.ajax({
            dataType: "json",
            type: "POST",
            url: editUserSettingsUrl,
            data: {
                LANDING_PAGE: Jq(this).attr('data-landingPage')
            },
            success: processMessage
        });
    }).css('cursor', 'pointer');
    //Languages
    Jq('#LangSelectorHP').on('change', function () {
        window.location.replace('/?lg=' + Jq(this).val());
    });
    Jq('#langSelector, #themeSelector').on('change', function () {
        Jq("#userSettingForm").dialog("close");
        Jq.ajax({
            dataType: "json",
            type: "POST",
            url: editUserSettingsUrl,
            data: {
                LOCALE: Jq('#langSelector').val(),
                THEME: Jq('#themeSelector').val()
            },
            success: function (response) {
                processMessage(response, 'redirect:/');
            }
        });
    });
    //Checks if login required on ajax request
    Jq(document).ajaxComplete(function (event, xhr, settings) {
        if (xhr.status === 200 && xhr.responseText.search('<!DOCTYPE html>') !== -1) {
            window.location.reload();
        }
    });
});
//Default button action
function submitForm(oper) {
    if (tinymce !== "undefined") {
        tinymce.triggerSave();
    }
    require(["jquery"], function (Jq) {
        var input = Jq("<input>", {
            type: "hidden",
            name: "oper",
            value: oper
        });
        Jq("form").first().append(Jq(input));
        if ('del' === oper) {
            noty({ //See http://ned.im/noty/#options
                text: _("Can you confirm this action?"),
                type: 'confirm',
                layout: 'center',
                modal: true,
                buttons: [
                    {
                        text: _('Confirm'),
                        onClick: function (noty) {
                            Jq("form").first().submit();
                            return;
                        }
                    },
                    {
                        text: _('Cancel'),
                        onClick: function (noty) {
                            noty.close();
                        }
                    }
                ]
            });
        } else {
            Jq("form").first().submit();
        }
    });
}
/*
 * Allows grid content export
 */
function exportGrid(url, gridId) {
    //Displays the export form and launches the export command
    var gridId = typeof gridId !== 'undefined' ? gridId : "";
    require(["jquery-ui"], function (Jq) {
        Jq("#exportGridForm").dialog({
            autoOpen: false,
            width: 500,
            modal: true,
            draggable: false,
            resizable: false,
            buttons: [
                {
                    text: _('Export'),
                    click: function () {
                        var colModel = Jq("#list" + gridId).getGridParam('colModel'),
                            cols = [],
                            postData = Jq("#list" + gridId).jqGrid("getGridParam", "postData");

                        Jq.each(colModel, function (i) {
                            if (this.hidden === false) {
                                cols.push(this.name);
                            }
                        });

                        ajaxDownload(url, {
                            exportPerimeter: Jq('input[type=radio][name=exportPerimeter]:checked').val(),
                            exportFormat: Jq('input[type=radio][name=exportFormat]:checked').val(),
                            exportOrientation: Jq('input[type=radio][name=exportOrientation]:checked').val(),
                            exportNumberDateFormat: Jq('input[type=radio][name=exportNumberDateFormat]:checked').val(),
                            exportColumns: cols.join(";"),
                            _search: postData._search,
                            page: postData.page,
                            rows: postData.rows,
                            sidx: postData.sidx,
                            sord: postData.sord,
                            filters: (postData.filters !== 'undefined') ? postData.filters : null
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
        Jq("#exportGridForm").dialog("open");
    });
}

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
