/*global _, require*/

"use strict";

//Manages button action
function genVersion(version) {
    require(["jquery-ui"], function (Jq) {
        var verselect = Jq('#version');
        var v = JSON.parse(version);
        var options = "";
        verselect.html('');
        for (var key in v) {
            options = "<option value='" + v[key] + "'>" + v[key] + "</option>" + options;
        }
        verselect.append(options);
        verselect.val(v[v.length - 1]);
    });
}