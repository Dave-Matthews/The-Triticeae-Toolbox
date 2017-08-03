/*global $,$A,Ajax,Element,window*/

var php_self = document.location.href;
var title = document.title;
var platform_str = "";
var expt_str = "";
var panel_str = "";

function update_platform(options)
{
    platform_str = "";
    $A(options).each(
        function (platform) {
            if (platform.selected) {
                platform_str += (platform_str === "" ? "" : ",") + platform.value;
            }
        }
    );
    var url = "includes/ajaxlib.php?func=DispExperiment&platform=" + platform_str;
    var tmp = new Ajax.Updater($('col2'), url, {
        onComplete : function () {
            $('col2').show();
            document.title = title;
        }
    });
}

function DispMarkerSet(options)
{
    panel_str = "";
    $A(options).each(
        function (markers) {
            if (markers.selected) {
                panel_str += (panel_str === "" ? "" : ",") + markers.value;
            }
        }
    );
    var url = "includes/ajaxlib.php?func=DispMarkerSet&set=" + panel_str;
    var tmp = new Ajax.Updater($('markerSet'), url, {
        onComplete : function () {
            $('MarkerSet').show();
            document.title = title;
        }
    });
}

function update_exper(options)
{
    expt_str = "";
    $A(options).each(
        function (expt) {
            if (expt.selected) {
                expt_str += (expt_str === "" ? "" : ",") + expt.value;
            }
        }
    );
}

function update_side()
{
    var url = "side_menu.php";
    var tmp = new Ajax.Updater($('quicklinks'), url, {
        onComplete : function () {
            $('quicklinks').show();
            document.title = title;
        }
    });
}

function select_exper(options)
{
    document.getElementById('current').innerHTML = "<img id=\"spinner\" src=\"images/ajax-loader.gif\"> Calculating which markers are in selected experiment(s)";
    var url = "includes/ajaxlib.php?func=SelcExperiment&experiment=" + expt_str;
    var tmp = new Ajax.Updater($('current'), url, {
        onComplete : function () {
            $('current').show();
            document.title = title;
            update_side();
        }
    });
}

function select_set(options)
{
    document.getElementById('current').innerHTML = "<img id=\"spinner\" src=\"images/ajax-loader.gif\"> Calculating which markers are in selected experiment(s)";
    var url = "includes/ajaxlib.php?func=SelcMarkerSet&set=" + panel_str;
    var tmp = new Ajax.Updater($('current'), url, {
        onComplete : function () {
            $('current').show();
            document.title = title;
            update_side();
        }
    });
}
