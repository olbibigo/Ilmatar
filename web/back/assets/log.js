/*global _, require, $, rootDownloadUrl, jqGridRowIdUrlParam*/

"use strict";

function processPostTabLoad(panel, tab) {
    var path = tab.find('.ui-tabs-anchor').attr('href').split('/');
    $('.btnAction').attr(
        'data-action-data',
        rootDownloadUrl.replace(jqGridRowIdUrlParam, path[path.length - 1]));
}

require(["jquery-ui"], function (Jq) {
    Jq('#priorityFilter').on('change', function () {
        var filter = Jq(this).val(),
        idxActiveTab = Jq("#tabs").tabs("option", "active");
        Jq('.ui-tabs-anchor').each(function (index) {
            var url = Jq(this).attr('href'),
            newUrl = url.split('filter=');
            newUrl = newUrl[0] + 'filter=' + filter;
            Jq(this).attr('href', newUrl);
        });
        Jq("#tabs").tabs('load', idxActiveTab);
    });
});
