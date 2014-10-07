/*global _, require, setReadStatus, tabIndex, changeReadStatusUrl*/

"use strict";

require(["jquery-ui"], function (Jq) {
    Jq(".accordion").accordion({
        collapsible : true,
        heightStyle : "content",
        create : function (event, ui) {
            setReadStatus(ui.header, ui.panel.find("input[type='hidden']"), Jq);
        },
        activate : function (event, ui) {
            setReadStatus(ui.newHeader, ui.newPanel.find("input[type='hidden']"), Jq);
        }
    });

    if (tabIndex !== 0) {
        Jq("#tabs").tabs("option", "active", tabIndex);
    }

    Jq('#buttonSend')
        .button({icons:{primary: "ui-icon-extlink"}})
        .on('click', function (e) {
            e.preventDefault();
            Jq('#composeMailForm').submit();
        });

    function setReadStatus(header, ids, Jq) {
        if (ids && (ids.length > 0) && (ids.eq(1).attr('value').length > 0)) {
            Jq.ajax({
                dataType : "json",
                type : "POST",
                url : changeReadStatusUrl,
                data : {
                    msgId : ids.eq(0).attr('value')
                },
                success : function () {
                    //nothing
                }
            });
            ids.eq(1).attr('value', '');
            header.find('.unread').remove();
            var nbUnreadMails = parseInt(Jq('#nbUnreadMails').text(), 10);
            if (nbUnreadMails > 1) {
                nbUnreadMails -= 1;
                Jq('#nbUnreadMails').html(nbUnreadMails);
            } else {
                Jq('#nbUnreadMails').remove();
            }
        }
    }
});
