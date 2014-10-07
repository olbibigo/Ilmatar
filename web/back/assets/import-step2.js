/*global _, require*/

"use strict";

require(["jquery-ui"], function (Jq) {
    Jq("#buttonPrevious").button({icons:{primary: "ui-icon-arrowthick-1-w"}});
    Jq("#buttonImport").button({icons:{primary: "ui-icon-arrowthick-1-e"}});
    Jq("#buttonRetry").button({icons:{primary: "ui-icon-arrowrefresh-1-s"}});
    Jq('#importStep2_path').on('change', function () {
        Jq('#import').hide();
    });
});
