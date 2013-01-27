/*global $,Ajax*/

var php_self = document.location.href;
var title = document.title;
var map = 1;

function update_map(option) {
  map = option;
  document.getElementById("step2").innerHTML = "<input type=\"button\" value=\"Save\" onclick=\"javascript:save_map()\">";
}

function save_map() {
  document.getElementById("step2").innerHTML = "";
  var url = php_self + "?map=" + map + "&function=Save";
  var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
            document.getElementById("step2").innerHTML = "Saved map selection";
        }
    });
}

function load_markerDisplay(map) {
  document.getElementById("step1").innerHTML = "";
  var url = php_self + "?map=" + map + "&function=Markers";
  var tmp = new Ajax.Updater($('step1'), url, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
        }
    });
}
  
function load_markersInMap() {
  document.getElementById("step3").innerHTML = "";
  var url = php_self + "?function=Markers";
  var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
}
