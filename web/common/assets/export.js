/*global _, require*/

"use strict";

function changeStatusExportOrientation(Jq, exportFormatValue) {
    if ("PDF" === exportFormatValue) {
        Jq('.pageOrientation.hide').removeClass("hide");
    } else {
        Jq('.pageOrientation:not(.hide)').addClass("hide");
    }
}

// Activate/hide the choice of export orientation according to the value of export format
require(["jquery"], function (Jq) {
    changeStatusExportOrientation(Jq, Jq("input[type=radio][name=exportFormat]").val());

    Jq("input[type=radio][name=exportFormat]").change(function () {
        changeStatusExportOrientation(Jq, Jq(this).val());
    });
});
