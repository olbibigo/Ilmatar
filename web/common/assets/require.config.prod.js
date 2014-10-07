var components = {
    paths: {
        "jqplot.dateAxisRenderer": "/common/assets/jqplot/plugins/jqplot.dateAxisRenderer.min",
        "jqplot.highlighter": "/common/assets/jqplot/plugins/jqplot.highlighter.min",
        "jqplot.cursor": "/common/assets/jqplot/plugins/jqplot.cursor.min"
    },
    "packages": [
        {
            "name": "jquery",
            "main": "jquery.2.1.1.min"
        },
        {
            "name": "jquery-ui",
            "main": "ui/minified/jquery-ui.min"
        },
        {
            "name": "tinymce",
            "main": "tinymce.4.0.16.min"
        },
        {
            "name": "jqgrid",
            "main": "js/minified/jquery.jqGrid.min"
        },
        {
            "name": "jqgrid/js/i18n",
            "main": "grid.locale-" + lang
        },
        {
            "name": "jquery-timepicker",
            "main": "jquery.ui.timepicker.1.4.3.min"
        },
        {
            "name": "jquery-timepicker/i18n",
            "main": "jquery-ui-timepicker-fr"
        },
        {
            "name": "noty",
            "main": "jquery.noty.2.2.2.min"
        },
        {
            "name": "jqplot",
            "main": "jquery.jqplot.1.0.8.min"
        },
        {
            "name": "strength",
            "main": "strength.min"
        }
    ],
    "shim": {
         "jqplot.dateAxisRenderer": {
            "deps": ["jqplot"],
            "exports": "jQuery"
        },
        "jqplot.highlighter": {
            "deps": ["jqplot"],
            "exports": "jQuery"
        },
        "jqplot.cursor": {
            "deps": ["jqplot"],
            "exports": "jQuery"
        },
        "jquery-ui": {
            "deps": ["jquery"],
            "exports": "jQuery"
        },
        "tinymce": {
            "exports": "tinymce"
        },     
        "jqplot": {
            "deps": ["jquery"],
            "exports": "jQuery"
        },
        "jqgrid": {
            "deps": ["jquery-ui", "jqgrid/js/i18n"],
            "exports": "jQuery"
        },
        "jqgrid/js/i18n": {
            "deps": ["jquery"]
        },
        "jquery-timepicker/i18n": {
            "deps": ["jquery-timepicker"],
            "exports": "jQuery"
        },        
        "jquery-timepicker": {
            "deps": ["jquery-ui"],
            "exports": "jQuery"
        },
        "noty": {
            "deps": ["jquery"],
            "exports": "jQuery"
        },
        "strength": {
            "deps": ["jquery"],
            "exports": "jQuery"
        }
    },
    "baseUrl": "/common/assets"
};
if (typeof require !== "undefined" && require.config) {
    require.config(components);
} else {
    var require = components;
}
if (typeof exports !== "undefined" && typeof module !== "undefined") {
    module.exports = components;
}