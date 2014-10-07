/*global _, require, $, noty*/

"use strict";

//Global functions
//Displays a simple notification
function notify(type, message, buttons) {
    noty({ //See http://ned.im/noty/#options
        text : message,
        type : type,
        layout : 'center',
        dismissQueue : true,
        timeout : false,
        modal : true,
        closeWith : ['click'],
        buttons : buttons
    });
}
//Displays all flash messages found into the DOM
function displayFlashMessages($) {
    //mapping between Monolog types and Noty types
    var types = {
        "emergency" : "error",
        "alert" : "error",
        "critical" : "error",
        "error" : "error",
        "warning" : "warning",
        "notice" : "information",
        "info" : "information",
        "success" : "success",
        "debug" : "alert"
    };

    $.each(types, function (MType, Ntype) {
        $('.flash-' + MType).each(function (index) {
            if ($(this).text().length > 0) {
                notify(Ntype, $(this).text());
            }
        }).remove();
    });
}
//Create a flash message
function createAndDisplayFlashMessage(type, message) {
    $('body').append("<div class='flash flash-" + type + "'>" + message + "</div>");
    displayFlashMessages($);
}
//Displays notifications
require(["noty", "jquery-ui"], function (Jq) {
    //helpers for autocomplete
    function split(val) {
        return val.split(/,\s*/);
    }
    function extractLast(term) {
        return split(term).pop();
    }

    //Displays current flash messages
    displayFlashMessages(Jq);
    //Displays tooltips
    Jq("[title]").tooltip({
        track : true
    });
    //Displays autocomplete
    var autocompleteCache = {};
    Jq(".autocomplete").each(function () {
        var that = Jq(this);
        // don't navigate away from the field on tab when selecting an item
        that.bind("keydown", function (event) {
            if (event.keyCode === Jq.ui.keyCode.TAB && that.data("ui-autocomplete").menu.active) {
                event.preventDefault();
            }
        }).autocomplete({
            source : function (request, response) {
                var target = that.attr('data-autocompletion-url'),
                term = extractLast(request.term);
                if (autocompleteCache.hasOwnProperty(target)) {
                    if (autocompleteCache[target].hasOwnProperty(term)) {
                        response(autocompleteCache[target][term]);
                        return;
                    }
                } else {
                    autocompleteCache[target] = {};
                }
                Jq.post(
                    that.attr('data-autocompletion-url'), {
                    term : extractLast(request.term)
                },
                    function (data, status, xhr) {
                    autocompleteCache[target][term] = data;
                    response(data);
                },
                    'json');
            },
            search : function () {
                // custom minLength
                var term = extractLast(this.value);
                if (term.length < 2) {
                    return false;
                }
            },
            focus : function () {
                // prevent value inserted on focus
                return false;
            },
            select : function (event, ui) {
                var terms = split(this.value);
                // remove the current input
                terms.pop();
                // add the selected item
                terms.push(ui.item.value);
                // add placeholder to get the comma-and-space at the end
                terms.push("");
                this.value = terms.join(", ");
                return false;
            }
        });
    });
});
