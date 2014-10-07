/*global _, require, chartReadUrl, kpiCodeUrlParam*/

"use strict";

function loadChart(Jq, id, title, textColor) {
    return Jq.jqplot(
        id,
        chartReadUrl.replace(kpiCodeUrlParam, id) + Jq('.selectView[data-code=' + id + ']').val(),
        {
            title : {text:title, textColor: textColor},
            dataRenderer : function (url, plot, options) {
                var ret = null;
                Jq.ajax({
                    async : false,
                    url : url,
                    dataType : "json",
                    success : function (data) {
                        ret = data;
                    }
                });
                return ret;
            },
            axes : {
                xaxis : {
                    renderer : Jq.jqplot.DateAxisRenderer,
                    tickOptions: { textColor: textColor },
                    rendererOptions: { tickRenderer: Jq.jqplot.CanvasAxisTickRenderer}
                },
                yaxis : {
                    tickOptions: { textColor: textColor }
                }
            },
            highlighter : {
                show : true,
                sizeAdjust : 7.5
            },
            cursor:{
                show: true,
                zoom:true,
                showTooltip:false
            },
            seriesColors : ["#000"],
            grid : {background : '#F3F3F3'},
            series : [{
                    lineWidth : 1,
                    markerOptions : {
                        style : 'circle',
                        size : 4
                    },
                    rendererOptions : {smooth: true}
                }
            ]
        }
    );
}

require(["jqplot.dateAxisRenderer", "jqplot.cursor", "jqplot.highlighter"], function (Jq) {
    var plots = [];

    Jq(".buttonReset")
        .button({icons: {primary: "ui-icon-cancel"}})
        .on('click', function() {
            plots[Jq(this).attr('data-code')]['plot'].resetZoom();
        });
    Jq('.selectView').on("change", function (index) {//Daily by default
        var idx = Jq(this).attr('data-code');
        //Cleanup
        plots[idx]['plot'].destroy();
        Jq('#' + idx).html('<img src="/common/img/spinner.gif" alt="' + _("Loading...") + '" style="margin-top:150px"/>');
        //Loads new chart
        plots[idx]['plot'] = loadChart(Jq, idx, plots[idx]['title'], "#000");
    });
        
    var link = document.createElement("link");
    link.type = "text/css";
    link.rel = "stylesheet";
    link.href = '/common/assets/jqplot/jquery.jqplot.min.css';
    document.getElementsByTagName("head")[0].appendChild(link);

    Jq('.chartContainer').each(function (index) {
        var id    = Jq(this).attr('id'),
            title = Jq(this).attr('data-label');
        plots[id] = {plot: loadChart(Jq, id, title, Jq("h1").css("color")), title : title};
    });
});
