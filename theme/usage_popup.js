/*global jQuery, document */
"use strict";

jQuery(function () {
    var d = new Date();
    d.setTime(d.getTime() + (360 * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    jQuery("#dialog-confirm").dialog({
        dialogClass: "no-close",
        resizable: false,
        height: 250,
        width: 400,
        modal: true,
        buttons: {
            "Don't agree": function () {
                document.location.href = "../";
            },
            "Agree": function () {
                document.cookie = "T3terms=usage_policy_approved; " + expires;
                jQuery(this).dialog("close");
            }
        }
    });
});
