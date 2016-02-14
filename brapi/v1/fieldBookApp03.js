/*global jQuery,$,alert,window */

var lineStr = "";
var expStr = "";
var markerprofileStr = "";

window.onload = function () {
$("#tabs").tabs({
        activate: function (event, ui) {
            var active = $('#tabs').tabs('option', 'active');
            if (active === "2") {
            jQuery.ajax({
              url: "clusterapp.php",
              success: function(data, textStatus) {
                  jQuery("#step2").html(data);
              },
              error: function() {
                  alert('Error in running clusterapp.php');
              }
            });
        }
        jQuery("#step2").html("");
    }
}

);
};

function updateUrl()
{
  var apiUrlList = document.getElementById("url2").value;
  document.getElementById("url").value = apiUrlList; 
}

function getMarkerprofiles()
{
  var items = [];
  if (expStr != "") {
    var apiUrl = document.getElementById("url").value + "/markerprofiles?extract=" + expStr;
  } else if (lineStr != "") {
    var apiUrl = document.getElementById("url").value + "/markerprofiles?germplasm=" + lineStr;
  } else {
    alert('Error: Select a genotype study or germplasm');
    return;
  } 
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Marker profiles</h3><table><tr>");
      jQuery.each( data[0], function( key, val ) {
        items.push("<td>" + key);
      });
      jQuery.each( data, function( key, val ) {
        items.push("<tr>");
        jQuery.each( val, function( key2, val2 ) {
          items.push("<td>" + val2);
        });
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        alert('Error in selecting markerprofiles');
      }
  });
}

function getAlleleMatrix()
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/allelematrix?" + markerprofileStr;
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Marker profiles</h3><table><tr>");
      jQuery.each( data, function( key, val ) {
        items.push("<tr>");
        if (key == "metadata") {
          items.push("<td>metadata");
        } else if (key == "markerprofileIds") {
          items.push("<tr><td>markerprofileIds<tr>");
          items.push("<td>" + val);
        } else if (key == "scores") {
          items.push("<tr><td>scores<tr><td>");
          jQuery.each( val, function( key2, val2 ) {
            items.push("<tr><td>" + key2 + " " + val2);
            jQuery.each( val2, function( key3, val3 ) {
              items.push("<td>" + val3);
            });
          });
        }
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
      error: function() {
        alert('Error in selecting experiment list');
      }
  });
}


function getListStudies()
{
  var items = [];
  var studyId = "";
  var apiUrlList = document.getElementById("url").value + "/study";
  if (document.getElementById("YesDebug").checked === true) {
      items.push("API call = " + apiUrlList);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrlList,
    success: function(data, textStatus) {
      items.push("<h3>List of studies</h3><table>");
      items.push("<tr>");
      jQuery.each( data[0], function( key, val ) {
         if (key == "studyId") {
           items.push("<td>" + key);
         } else {
           items.push("<td>" + key);
         }
      });
      jQuery.each( data, function( i, j ) {
        items.push("<tr>");
        if (data[i].studyId) {
          studyId = data[i].studyId;
        } else if (data[i].id) { /*not standard*/
          studyId = data[i].id;
        } 
        jQuery.each( data[i], function( key, val ) {
          if (key == "studyId") {
            items.push("<td><button onclick=\"get_detail(" + studyId + ")\">details</button>");
            items.push("<button onclick=\"select_study(" + studyId + ")\">select study</button>");
          } else if (key == "id") {  /*not standard*/
            items.push("<td><button onclick=\"get_detail(" + studyId + ")\">details</button>");
          } else {
            items.push("<td>" + val);
          }
        });
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        alert('Error in selecting experiment list');
      }
  });
  document.getElementById("step3").innerHTML = "";
}

function select_study(exp)
{
  expStr = exp;
}

function select_germplasm(line)
{
  lineStr = line;
}

function get_detail(exp)
{
  var items = [];
  var count = 1;
  lineStr = "";
  markerprofileStr = "";
  var apiUrl = document.getElementById("url").value + "/study/" + exp;

  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Study details</h3><table>");
      items.push("<tr>");
      jQuery.each( data, function( key, val ) {
          if (key == "design") {
            items.push("</table><br><table>");
            //save column names
            jQuery.each( data.design[0], function( key2, val2 ) {
              items.push("<td>" + key2);
            });
            //save field layout
            jQuery.each( data.design, function( i, j ) {
              items.push("<tr>");
              jQuery.each( data["design"][i], function ( key2, val2 ) {
                items.push("<td>" + val2);
                if (key2 == "germplasmId") {
                  items.push("<button onclick=\"select_germplasm(" + val2 + ")\">select germplasm</button>");
                  h = "markerprofileId" + count + "=" + val2 + "_" + exp;
                  count++;
                  if (markerprofileStr === "") {
                    markerprofileStr = h;
                  } else {
                    markerprofileStr += "&" + h;
                  }
                }
              });
            });
          } else {
            items.push("<tr><td>" + key + "<td>" + val);
          }
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        var html = apiUrl + '<br>Error: study details API not implemented';
        jQuery("#step2").html(html);
      }
  });
}

function getListTraits()
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/traits";
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>List of traits</h3>");
      jQuery.each( data, function( key, val ) {
        if (key == "metadata") {
          items.push("Metadata<br>");
        } else if (key == "result") {
          jQuery.each( val , function ( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              items.push("Data<br><table><tr>");
              jQuery.each( val2[0] , function ( key3, val3) {
                if (key3 == "traitDbId") {
                  items.push("<td>");
                } else {
                  items.push("<td>" + key3);
                }
              });
            }
           });
        }
      });
      jQuery.each( data, function( key, val ) {
        items.push("<tr>");
        if (key == "metadata") {
        } else if (key  == "result") {
          var studyId = data[key].traitDbId;
          jQuery.each( val, function( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              jQuery.each( val2, function (key3, val3) {
                if (typeof val3 == "object") {
                jQuery.each( val3, function(key4, val4) {
                if (key4 == "traitDbId") {
                  items.push("<tr><td><button onclick=\"get_detail_trait(" + studyId + ")\">details</button>");
                } else if (key3 =  "observationVariables") {
                  items.push("<td>" + val4);
                } else {
                  items.push("<td>" + val4);
                }
                });
                } else {
                  items.push("Error: " + val3);
                }
              });
            }
          });
        }
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        var html = apiUrl + '<br>Error: traits API not implemented on this server';
        jQuery("#step2").html(html);
      }
  });
  document.getElementById("step3").innerHTML = "";
}

function getListMaps()
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/maps/list";
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>List of maps</h3><table>");
      items.push("<tr>");
      jQuery.each( data[0], function( key, val ) {
         if (key == "mapId") {
           items.push("<th>");
         } else {
           items.push("<th>" + key);
         }
      });
      jQuery.each( data, function( i, j ) {
        items.push("<tr>");
        var mapId = data[i].mapId;
        jQuery.each( data[i], function( key, val ) {
          if (key == "mapId") {
            items.push("<td><button onclick=\"get_detail_map(" + mapId + ")\">details</button>");
          } else {
            items.push("<td nowrap>" + val);
          }
        });
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        var html = apiUrl + '<br>Error: traits API not implemented on this server';
        jQuery("#step2").html(html);
      }
  });
  document.getElementById("step3").innerHTML = ""; 
}

function get_detail_trait(exp)
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/traits/" + exp;
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Trait details</h3><table>");
      jQuery.each( data, function( key, val ) {
        items.push("<tr><td>" + key + "<td>" + val);
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        alert('Error in selecting experiment list');
      }
  });
}

function get_detail_map(uid)
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/maps/" + uid;
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Map details</h3><table>");
      jQuery.each( data, function( key, val ) {
        items.push("<tr>");
        if (key == "entries") {
          jQuery.each( val, function( key2, val2 ) {
            items.push("<tr>");
            jQuery.each ( val2, function( key3, val3 ) {
              items.push("<td>" + val3);
            });
          });
        } else {
          items.push("<td>" + key + "<td>" + val);
        }
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step2").html(html);
    },
    error: function() {
        alert('Error in selecting map');
      }
  });
}
