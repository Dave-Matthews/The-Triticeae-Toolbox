var php_self = document.location.href;
var mmm = 10;
var mml = 10;
var displayAllFlag = "Y";
var unq_file = "";

function displayOut() {
    displayAllFlag = "Y";
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=displayOut&unq=" + unq_file,
        success: function (data, textStatus) {
            jQuery("#step7").html(data);
        },
        error: function () {
            alert("Error in display or results");
        }
    });
    Element.hide("spinner");
}

function use_session2() {
    var typeG = true;
    var typeGE = false;
    var notoselect = $("notoselect").getValue();
    var errorstat = document.getElementById("errorstat").value;
    Element.show("spinner");
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=download_session_v4" + "&typeG=" + typeG.checked + "&typeGE=" + typeGE.checked + "&err=" + errorstat + "&unq=" + unq_file + "&notoselect=" + notoselect + "&mmm=" + mmm,
        success: function (data, textStatus) {
            jQuery("#step6").html(data);
            displayOut();
        },
        error: function () {
            Element.hide("spinner");
            alert("Error analyzing outlier");
        }
    });
}

function use_session() {
    mmm = $("mmm").getValue();
    mml = $("mml").getValue();
    unq_file = Date.now();
    Element.show('spinner');
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=filter_lines" + "&mmm=" + mmm + "&mml=" + mml,
        success: function (data, textStatus) {
            jQuery("#step5").html(data);
            use_session2();
        },
        error: function () {
            alert("Error filtering data");
        }
    });
}

