/*global, alert, jQuery, $, Element */

var map = 1;
var markersInMap = null;
var php_self = document.location.href;
$.noConflict(); //when prototype.js is removed then this is not necessary

function save_map() {
  Element.hide('spinner');
  var i;
  for (i=0; i<document.myForm.map.length; i++) {
      if (document.myForm.map[i].checked===true) {
          map = document.myForm.map[i].value;
      }
  }
  jQuery.ajax({
    type: "GET",
    url: php_self,
    data: "map=" + map + "&function=Save",
    success: function(data, textStatus) {
        jQuery("#step3").html(data);
    },
    error: function() {
        alert('Error in selecting design type');
      }
  });
}

function load_markersInMap() {
  Element.show('spinner');
  document.getElementById("step4").innerHTML = "Calculating portion of markers in each map.";
  markersInMap = jQuery.ajax({
    type: "GET",
    url: php_self,
    data: "function=Markers",
    success: function(data, textStatus) {
        jQuery("#step4").html(data);
        Element.hide('spinner');
    }
  });
}
