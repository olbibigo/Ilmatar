/*global _, require*/

"use strict";

var activeTabId = null;

require(["jquery-ui"], function (Jq) {
    Jq("#tabs").tabs();
    var e = {
        target : Jq("li.ui-tabs-active").find('a')
    };
    for (var buttonName in buttonByTab) {
        for (var index in buttonByTab[buttonName]) {
            Jq('#' + buttonByTab[buttonName][index]).hide();
        }
    }
    Jq('.ui-tabs>ul>li>a').on("click", startProcess);
    startProcess(e);
});

function startProcess(e) {
    require(["jquery-ui"], function (Jq) {
        var newActiveTabId = Jq(e.target).attr('id');
        if (newActiveTabId === activeTabId) {
            return;
        }
        hideButton(buttonByTab[activeTabId]);
        showButton(buttonByTab[newActiveTabId]);
        activeTabId = newActiveTabId;
    });
}

function hideButton(hideId) {
    require(["jquery"], function (Jq) {
        for (var i in hideId) {
            Jq('#' + hideId[i]).hide();
        }
    });
}

function showButton(showId) {
    require(["jquery"], function (Jq) {
        for (var i in showId) {
            Jq('#' + showId[i]).show();
        }
    });
}