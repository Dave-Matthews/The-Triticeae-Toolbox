/*global $,Ajax*/

var php_self = document.location.href;
var title = document.title;

function load_markersInMap() {
  document.getElementById("step2").innerHTML = "";
  var url = php_self + "?function=Markers";
  var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}
