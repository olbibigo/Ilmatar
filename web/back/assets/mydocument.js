/*global _, require, notify, ajaxDownload, downloadUrl, deleteUrl, jqGridListId, error,createAndDisplayFlashMessage, processMessage, progressUrl, resetInput*/

"use strict";

function downloadAll(url) {
    require(["jquery-ui"], function (Jq) {
        if (0 == Jq(jqGridListId).jqGrid('getGridParam', 'records')) {
            notify("warning", _("No document to download."));
        } else {
            ajaxDownload(url);
        }
    });
}
function processDeleteResponse(data) {
    require(["jquery-ui"], function (Jq) {
        Jq(jqGridListId).trigger("reloadGrid");
        processMessage(data);
    });
}
function pbUpdate(data) {
    require(["jquery-ui"], function (Jq) {
        Jq("#progressbar").progressbar("value", data.percent);
    });
}

function pbFinish() {
    require(["jquery-ui"], function (Jq) {
        Jq("#progressbar").progressbar("value", 100);
    });
}
function progressInit() {
    require(["jquery-ui"], function (Jq) {
        Jq('<iframe></iframe>').css(
            { position: "absolute", left: "-100px", top: "-100px", width: "1px", height: "1px" }
        ).attr('src', progressUrl + '/' + Jq("#file_id").val()).attr('id', 'progress').appendTo(Jq("#progressdiv"));
        Jq("#progressdiv").append('<div id="progressbar" style="position: relative;"><div class="progresslabel" style="position: absolute;left: 50%;top: 4px;font-weight: bold;text-shadow: 1px 1px 0 #fff;">' + _('Loading...') + '</div></div>');
        Jq("#progressbar").progressbar({
            value: false,
            change: function () {
                Jq(".progresslabel").text(Jq("#progressbar").progressbar("value") + "%");
            },
            complete: function () {
                Jq(".progresslabel").text("Complete!");
            }
        });
    });
}

require(["jquery-ui"], function (Jq) {
    Jq('#uploadForm').children('div').each(function (index, elem) {
        if (index > 0) {
            Jq(elem).hide();
        }
    });
    var Jqx = Jq;
    Jq('.buttonRemove')
        .button({
            icons: {primary: 'ui-icon-trash'},
            text: false
        })
        .click(function () {
            var targetId = Jqx(this).attr('data-target');
            var elem = Jq('#form_' + targetId);
            elem.wrap('<form>').closest('form').get(0).reset();
            elem.unwrap();
            Jqx('#' + targetId).hide();
            if (1 === Jqx("#uploadForm > div:hidden").length) {
                Jq(this).show();
            }
        });
    Jq('#addFileButton')
        .button({icons: {primary: 'ui-icon-circle-plus'}})
        .click(function () {
            if (1 === Jq("#uploadForm > div:hidden").length) {
                Jq(this).hide();
            }
            Jq("#uploadForm > div:hidden:first").show();
        });
    Jq("#uploadFormContainer").dialog({
        autoOpen: false,
        minWidth: 700,
        modal: true,
        draggable: false,
        resizable: false,
        buttons: [
            {
                text: _('Send'),
                click: function () {
                    var files = {},
                        errors = {};
                    Jq('#uploadForm input[type="file"]').each(function (index, elem) {
                        var v = Jq(elem).val();
                        if (v !== '') {
                            if (files[v]) {
                                errors[v] = 1;
                            } else {
                                files[v] = 1;
                            }
                        }
                    });
                    if (!Jq.isEmptyObject(errors)) {
                        Jq('#validateTips').text(_('Files with different name are expected.')).addClass("ui-state-error");
                    } else if (!Jq.isEmptyObject(files)) {
                        Jq('#uploadForm').submit();
                    } else {
                        Jq('#validateTips').text(_('At least one file to upload is expected.')).addClass("ui-state-error");
                    }
                }
            }
        ],
        close: function() {
            //Reset
            Jq("#uploadForm > div").each(function(index) {
                Jq(this).wrap('<form>').closest('form').get(0).reset();
                Jq(this).unwrap();
                Jq(this).hide();
            });
            Jq("#uploadForm > div:first").show();
            Jq("#addFileButton").show();
        },
        open: function() {
            Jq('.ui-dialog-buttonpane').find('button:first-child').button({
                icons: {primary: 'ui-icon-arrowthickstop-1-s'}
            });
        }
    });
    Jq("#downloadFormContainer").dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        draggable: false,
        resizable: false,
        buttons: [
            {
                text: _('Download'),
                click: function () {
                    ajaxDownload(downloadUrl + '/' + Jq("#documentId").val() + '/' + Jq("#version").val());
                    Jq("#downloadFormContainer").dialog("close");
                }
            }
        ],
        open: function() {
            Jq('.ui-dialog-buttonpane').find('button').button({
                icons: {primary: 'ui-icon-arrowthickstop-1-s'}
            });
        }
    });
    Jq("#deleteConfirmContainer").dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        draggable: false,
        resizable: false,
        buttons: [
            {
                text: _('Delete'),
                click: function () {
                    Jq.get(deleteUrl + '/' + Jq("#documentId").val() + '?mode=all').done(
                        function (data) {
                            Jq(jqGridListId).trigger("reloadGrid");
                            Jq("#deleteConfirmContainer").dialog("close");
                            processMessage(data);
                        }
                    );
                }
            },
            {
                text: _('Cancel'),
                click: function () {
                    Jq("#deleteConfirmContainer").dialog("close");
                }
            }
        ],
        open: function() {
            Jq('.ui-dialog-buttonpane').find('button:first-child').button({
                icons: {primary: 'ui-icon-trash'}
            });
        }
    });
    Jq("#deleteContainer").dialog({
        autoOpen: false,
        width: 500,
        modal: true,
        draggable: false,
        resizable: false,
        buttons: [
            {
                text: _('Delete all versions'),
                click: function () {
                    Jq.get(deleteUrl + '/' + Jq("#documentId").val() + '?mode=all').done(
                        function (data) {
                            Jq(jqGridListId).trigger("reloadGrid");
                            Jq("#deleteContainer").dialog("close");
                            processMessage(data);
                        }
                    );
                }
            },
            {
                text: _('Delete only the latest version'),
                click: function () {
                    Jq.get(deleteUrl + '/' + Jq("#documentId").val() + '?mode=latest').done(
                        function (data) {
                            Jq(jqGridListId).trigger("reloadGrid");
                            Jq("#deleteContainer").dialog("close");
                            processMessage(data);
                        }
                    );
                }
            }
        ],
        open: function() {
            Jq('.ui-dialog-buttonpane').find('button').button({
                icons: {primary: 'ui-icon-trash'}
            });
        }
    });

    Jq("#uploadForm").submit(function (e) {
        var formObj = Jq(this),
            formURL = formObj.attr("action");

        e.preventDefault();
        var formData = new FormData(this);
        Jq.ajax({
            url: formURL,
            type: 'POST',
            data: formData,
            mimeType: "multipart/form-data",
            contentType: false,
            cache: false,
            processData: false,

            success: function (data, textStatus, jqXHR) {
                Jq(jqGridListId).trigger("reloadGrid");
                Jq("#uploadFormContainer").dialog("close");
                Jq("#progressdiv").empty();
                Jq('#uploadForm').children('div').each(function (index, elem) {
                    resetInput(elem.id);
                    if (index > 0) {
                        Jq(elem).hide();
                    }
                });
                Jq('#validateTips').text('').removeClass("ui-state-error");
                processMessage(JSON.parse(data));
            },
            error: function (jqXHR, textStatus, errorThrown) {
            }
        });
        //progressInit();
    });
});