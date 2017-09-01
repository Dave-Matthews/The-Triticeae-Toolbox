var php_self = document.location.href;

function update_side2()
{
    var url = "side_menu.php";
    var tmp = new Ajax.Updater($('quicklinks'), url, {
        onComplete : function () {
            $('quicklinks').show();
            document.title = title;
        }
    });
}

function update_side()
{
    jQuery.ajax({
      type: "GET",
      url: "side_menu.php",
      data: "",
      success: function (data, textStatus) {
        jQuery("#quicklinks").html(data);
      },
      error: function () {
        alert("Error in selecting map");
      }
    });
}

function select_chrom() {
    "use strict";
    var chrom = document.getElementById("chrom").value;
    var start = document.getElementById("start").value;
    var stop = document.getElementById("stop").value;
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=chrom&value=" + chrom + "&start=" + start + "&stop=" + stop,
        success: function (data, textStatus) {
            jQuery("#step2").html(data);
        },
        error: function () {
            alert("Error in selecting map");
        }
    });
}

function save() {
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=save",
        success: function (data, textStatus) {
            jQuery("#step2").html(data);
            update_side();
        },
        error: function () {
            alert("Error in selecting map");
        }
    });
}
