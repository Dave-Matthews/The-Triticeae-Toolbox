/*global, alert, jQuery, Ajax, $ */

var php_self = document.location.href;
var title = document.title;
var map = 1;
$.noConflict(); //when prototype.js is removed then this is not necessary

function select_map() {
    jQuery( "#select-map" ).dialog( "open" );
}

jQuery(function() {
  jQuery( "#select-map" ).dialog({
      autoOpen: false,
      height: 450,
      width: 550,
      modal: false,
      buttons: {
        Close: function() {
          jQuery( this ).dialog( "close" );
          location.reload();
        }
      }
  });
});

function save_map() {
  var i;
  for (i=0; i<document.myForm.map.length; i++) {
      if (document.myForm.map[i].checked===true) {
          map = document.myForm.map[i].value;
      }
  }
  jQuery.ajax({
    type: "GET",
    url: "maps/select_map.php",
    data: "map=" + map + "&function=Save",
    success: function(data, textStatus) {
        jQuery("#select-map2").html(data);
    },
    error: function() {
        alert('Error in selecting design type');
      }
  });

}

