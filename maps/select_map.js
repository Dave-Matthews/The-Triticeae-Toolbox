/*global $,Ajax,Element*/

var php_self = document.location.href;
var title = document.title;
var map = 1;

function update_map(option) {
  map = option;
  document.getElementById("step2").innerHTML = "<input type=\"button\" value=\"Save\" onclick=\"javascript:save_map()\">";
}

function save_map() {
  var i;
  for (i=0; i<document.myForm.map.length; i++) {
      if (document.myForm.map[i].checked===true) {
          map = document.myForm.map[i].value;
      }
  }
  document.getElementById("step1").innerHTML = "";
  var url = php_self + "?map=" + map + "&function=Save";
  var tmp = new Ajax.Updater($('step1'), url, {
        onComplete : function() {
            $('step1').show();
            document.title = title;
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
  document.getElementById("step3").innerHTML = "Calculating portion of markers in each map.";
  var url = php_self + "?function=Markers";
  var tmp = new Ajax.Updater($('step3'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step3').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}
