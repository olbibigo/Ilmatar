/*global _, require, loadEntityUrl*/

"use strict";

require(["jquery-ui"], function (Jq) {
    Jq("#buttonNext").button({icons:{primary: "ui-icon-arrowthick-1-e"}});
    Jq('#importStep1_entities').on('change', function () {
        if (this.value !== '') {
            Jq.ajax({
                dataType : "html",
                type : "POST",
                url : loadEntityUrl,
                data : {
                    entity : this.value
                },
                success : function (data, textStatus, jqXHR) {
                    Jq('#entityField').html('').html(data);
                }
            });
        } else {
            Jq('#entityField').html('');
        }
    });
});
