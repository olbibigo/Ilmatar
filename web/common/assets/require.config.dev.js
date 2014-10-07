var components = {
    paths: {
        "jqplot.dateAxisRenderer": "/common/assets/jqplot/plugins/jqplot.dateAxisRenderer",
        "jqplot.highlighter": "/common/assets/jqplot/plugins/jqplot.highlighter",
        "jqplot.cursor": "/common/assets/jqplot/plugins/jqplot.cursor"
    },
    "packages": [
        {
            "name": "jquery",
            "main": "jquery.2.1.1"
        },
        {
            "name": "jquery-ui",
            "main": "ui/jquery-ui"
        },
        {
            "name": "tinymce",
            "main": "tinymce.4.0.16.min"
        },
        {
            "name": "jqgrid",
            "main": "js/jquery.jqGrid"
        },
        {
            "name": "jqgrid/js/i18n",
            "main": "grid.locale-" + lang
        },
        {
            "name": "jquery-timepicker",
            "main": "jquery.ui.timepicker.1.4.3"
        },
        {
            "name": "jquery-timepicker/i18n",
            "main": "jquery-ui-timepicker-fr"
        },
        {
            "name": "noty",
            "main": "jquery.noty.2.2.2"
        },
        {
            "name": "jqplot",
            "main": "jquery.jqplot.1.0.8"
        },
        {
            "name": "strength",
            "main": "strength"
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
            "deps": ["jquery"],
            "exports": "jQuery"
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