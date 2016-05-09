/*global, alert, jQuery, $ */

var map = 1;
var markersInMap = null;
var php_self = document.location.href;

function save_map() {
    var i = 0;
    for (i = 0; i < document.myForm.map.length; i++) {
        if (document.myForm.map[i].checked === true) {
            map = document.myForm.map[i].value;
        }
    }
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "map=" + map + "&function=Save",
        success: function (data, textStatus) {
            jQuery("#step3").html(data);
        },
        error: function () {
            alert('Error in saving map selection');
        }
    });
}

function displayMap() {
    jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "map=" + map + "&function=Display",
        success: function (data, textStatus) {
            jQuery("#step3").html(data);
        },
        error: function () {
            alert('Error in saving map selection');
        }
    });
}

function load_one_marker(mapset) {
  var url = "";
  var spn = "spinner" + mapset;
  document.getElementById(spn).style.display = "inline";
  markersInMap = jQuery.ajax({
    type: "GET",
    url: php_self,
    data: "function=Markers&mapset=" + mapset,
    success: function(data, textStatus) {
        url = "#" + mapset;
        jQuery(url).html(data);
    },
    error: function() {
        Element.hide('spinner' + mapset);
        alert('Error in calculating markers in map');
      }
  });
}

function load_markersInMap(mapset_list) {
  var i = 0;
  var url = "";
  document.getElementById("step4").innerHTML = "Calculating portion of markers in each map.";
  for (i = 0; i < arguments.length; i++) {
    load_one_marker(arguments[i]);
  }
  document.getElementById("step4").innerHTML = "";
}
