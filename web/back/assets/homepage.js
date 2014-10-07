/*global _, require, forgottenActionUrl, createAndDisplayFlashMessage, Recaptcha*/

"use strict";

//Manage login form
require(["jquery-ui"], function (Jq) {
    //Submits form with enter key
    Jq("input").on('keypress', function (event) {
        if (event.which === 13) {
            if ('hide' !== Jq("#loginButtonContainer").attr('class')) {
                Jq("#loginForm").submit();
            }
        }
    }).on('input', function () {
        //Displays button only if both fields are not empty
        if (Jq('#_username').val().length > 0 && Jq('#_password').val().length > 0) {
            Jq("#loginButtonContainer").removeClass("hide");
        } else {
            Jq("#loginButtonContainer").addClass("hide");
        }
    });
    Jq("#buttonLogin")
        .button({icons:{primary: "ui-icon-unlocked"}})
        .on('click', function () {
            Jq("#loginForm").submit();
        });

    //Deals with password autocomplete by browser
    Jq(function () {
        setInterval(function () {
            Jq("input").trigger("input");
        }, 250);
    });

    //Manages forgotten password
    Jq("#forgottenPassword").on('click', function () {
        Jq("#forgottenPasswordForm").dialog("open");
    });
    var Jqx = Jq;
    Jq("#forgottenPasswordForm").dialog({
        autoOpen : false,
        width : 500,
        modal : true,
        draggable : false,
        resizable : false,
        buttons : [{
                text : _('Send'),
                click : function () {
                    var pattern = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i,
                    challenge = Jq('#recaptcha_challenge_field'),
                    response = Jq('#recaptcha_response_field');

                    if (pattern.test(Jq('#_email').val()) && (((challenge.length > 0) && (challenge.val().length > 0)) || (challenge.length === 0))) {
                        //Send in ajax
                        Jq.post(
                            forgottenActionUrl, {
                            email : Jq('#_email').val(),
                            recaptcha_challenge_field : challenge.length > 0 ? challenge.val() : null,
                            recaptcha_response_field : response.length > 0 ? response.val() : null
                        },
                            function (response) {
                            if (response.error) {
                                Jq('#validateTips').text(response.message).addClass("ui-state-highlight");
                            } else {
                                Jq("#forgottenPasswordForm").dialog("close");
                                createAndDisplayFlashMessage('success', response.message);
                            }
                        });
                    } else {
                        Jq('#validateTips').text(_('An email and a captcha are expected.')).addClass("ui-state-error");
                    }
                }
            }
        ],
        open : function (event, ui) {
            //Refreshs recaptcha
            if (Recaptcha !== 'undefined') {
                Recaptcha.reload();
            }
            Jqx('.ui-dialog-buttonpane').find('button:first-child').button({
                icons: {primary: 'ui-icon-arrowreturnthick-1-e'}
            });
        }
    });
});
