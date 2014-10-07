/*global _, require*/

"use strict";

var translations = [];

function bootstrapTrans($json) {
    translations = $json;
}

function _(label, tags) {
    var out = label,
    key;
    if (translations[label]) {
        out = translations[label];
    }
    if (tags) {
        for (key in tags) {
            out = out.replace(key, tags[key]);
        }
        return out;
    }
    return out;
}
