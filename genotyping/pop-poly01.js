var php_self = document.location.href;

function update_side()
{
    jQuery.ajax({
        url: "side_menu.php",
        done: function (data) {
            jQuery("#quicklinks").html(data);
        },
        error: function () {
            alert("Error updating side menu");
        }
    });
}

function select_chrom()
{
    "use strict";
    var chrom = document.getElementById("chrom").value;
    var start = document.getElementById("start").value;
    var stop = document.getElementById("stop").value;
    jQuery.ajax({
        url: php_self,
        data: "function=chrom&value=" + chrom + "&start=" + start + "&stop=" + stop,
        done: function (data) {
            jQuery("#step2").html(data);
        },
        error: function () {
            alert("Error in selecting map");
        }
    });
}

function save()
{
    jQuery.ajax({
        url: php_self,
        data: "function=save",
        done: function (data) {
            jQuery("#step2").html(data);
            update_side();
        },
        error: function () {
            alert("Error in selecting map");
        }
    });
}
