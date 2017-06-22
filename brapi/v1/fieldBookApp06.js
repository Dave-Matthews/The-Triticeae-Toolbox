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
                    success: function (data, textStatus) {
                        jQuery("#step2").html(data);
                    },
                    error: function () {
                        alert('Error in running clusterapp.php');
                    }
                });
            }
            jQuery("#step2").html("");
        }
    });
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
        success: function (data, textStatus) {
            items.push("<h3>Marker profiles</h3><table><tr>");
            jQuery.each( data[0], function (key, val) {
                items.push("<td>" + key);
            });
            jQuery.each( data, function (key, val) {
                items.push("<tr>");
                jQuery.each( val, function (key2, val2) {
                    items.push("<td>" + val2);
                });
            });
            items.push("</table>");
            var html = items.join("");
            jQuery("#step2").html(html);
        },
        error: function () {
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

function getListCalls()
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/calls";
  if (document.getElementById("YesDebug").checked === true) {
      items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>List of calls</h3><table>");
      jQuery.each( data, function( key, val ) {
        if (key == "metadata") {
          items.push("Metadata<br>");
        } else if (key == "result") {
          jQuery.each( val, function ( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              items.push("Data<tr><table border=1><tr>");
              jQuery.each( val2[0], function ( key3, val3 ) {
                items.push("<td>" + key3);
              });
            }
          });
        }
      });
      jQuery.each( data, function( key, val ) {
        if (key == "metadata") {
        } else if (key  == "result") {
          jQuery.each( val, function( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              jQuery.each( val2, function (key3, val3) {
                if (typeof val3 == "object") {
                  items.push("<tr>");
                  jQuery.each( val3, function(key4, val4) {
                      items.push("<td>" + val4);
                  });
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
    error: function () {
        alert('Error in selecting experiment list');
      }
  });
  document.getElementById("step3").innerHTML = "";
}

function getListStudies()
{
  var items = [];
  var studyId = "";
  var apiUrlList = document.getElementById("url").value + "/studies-search";
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
      jQuery.each( data, function( key, val ) {
        if (key == "metadata") {
          items.push("Metadata<br>");
        } else if (key == "result") {
          jQuery.each( val, function ( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              items.push("Data<tr><table border=1><tr>");
              jQuery.each( val2[0], function ( key3, val3 ) { 
                if (key3 == "studyDbId") {
                  items.push("<td><td>");
                } else {
                  items.push("<td>" + key3);
                }
              });
            }
          });
        }
      });
      jQuery.each( data, function( key, val ) {
        if (key == "metadata") {
        } else if (key == "result") {
          jQuery.each( val, function( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              jQuery.each( val2, function (key3, val3) {
                if (typeof val3 == "object") {
                  items.push("<tr>");
                  jQuery.each( val3, function(key4, val4) {
                    if (key4 == "studyDbId") {
                      studyId = val4;
                      items.push("<td><button onclick=\"get_detail(" + studyId + ")\">details</button>");
                      items.push("<td><button onclick=\"select_study(" + studyId + ")\">select study</button>");
                    } else {
                      items.push("<td>" + val4);
                    }
                  });
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
  var apiUrl = document.getElementById("url").value + "/studies/" + exp;

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
          if (key == "metadata") {
            items.push("Metadata<br>");
          } else if (key == "result") {
              items.push("Data<br><table><tr>");
              jQuery.each( val , function ( key2, val2) {
                if ((key2 == "location") && (typeof val2 == "object")) {
                  items.push("<tr><td>" + key2 + "<td>" + val2[1]);
                } else {
                  items.push("<tr><td>" + key2 + "<td>" + val2);
                }
              });
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
  var studyId;
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
          jQuery.each( val, function( key2, val2 ) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              jQuery.each( val2, function (key3, val3) {
                if (typeof val3 == "object") {
                jQuery.each( val3, function(key4, val4) {
                if (key4 == "traitDbId") {
                  studyId = val4;
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
  var apiUrl = document.getElementById("url").value + "/maps";
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>List of maps</h3>");
      items.push("<tr>");
      jQuery.each( data, function(key, val) {
        if (key == "metadata") {
          items.push("Metadata<br>");
        } else if (key == "result") {
          jQuery.each( val, function(key2, val2) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              items.push("Data<br><table><tr>");
              jQuery.each(val2[0], function(key3, val3) {
                items.push("<td>" + key3);
              });
            } 
          });
        }
      });
      jQuery.each( data, function(key, val) {
        if (key == "metadata") {
        } else if (key == "result") {
          jQuery.each( val, function(key2, val2) {
            if ((key2 == "data") && (typeof val2 == "object")) {
              jQuery.each(val2, function(key3, val3) {
                if (typeof val3 == "object") {
                  items.push("<tr>");
                  jQuery.each(val3, function(key4, val4) {
                    if (key4 == "mapId") {
                      var mapId = val4;
                      items.push("<td><button onclick=\"get_detail_map(" + mapId + ")\">details</button>");
                    } else if (key4 == "comments") {
                      items.push("<td>" + val4);
                    } else {
                      items.push("<td nowrap>" + val4);
                    }
                  });
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
      items.push("<h3>Map details</h3>");
      jQuery.each( data, function(key, val) {
        if (key == "metadata") {
          items.push("Metadata<br>");
        } else if (key == "result") {
          items.push(key + "<table><tr>");
          jQuery.each( val, function(key2, val2) {
            if (typeof val2 == "object") {
            } else {
              items.push("<td>" + key2);
            }
          });
        }
      });
      jQuery.each(data, function(key, val) {
        if (key == "metadata") {
        } else if (key == "result") {
          var lineNum = 1;
          items.push("<tr>");
          jQuery.each( val, function(key2, val2) {
            if (typeof val2 == "object") {
              items.push("</table>" + key2 + "<table><tr>");
              jQuery.each(val2, function(key3, val3) {
                jQuery.each(val3, function(key4, val4) {
                  items.push("<td>" + key4);
                });
              });
              jQuery.each(val2, function(key3, val3) {
                if (typeof val3 == "object") {
                  items.push("<tr>");
                  jQuery.each(val3, function(key4, val4) {
                    items.push("<td>" + val4);
                  });
                }
              });
            } else {
              items.push("<td>" + val2);
            }
          });
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
