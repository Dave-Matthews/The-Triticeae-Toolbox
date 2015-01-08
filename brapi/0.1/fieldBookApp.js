/*global jQuery,$,alert,window */

//window.onload = function() {
//  $('a').click(function() {
//    jQuery("#step2").html("tab selected");
//  });
//};

var lineAry = [];

window.onload = function() {
$("#tabs").tabs({
    activate: function (event, ui) {
        var active = $('#tabs').tabs('option', 'active');
        if (active == "2") {
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

function getListStudies()
{
  var items = [];
  var studyId = "";
  var apiUrlList = document.getElementById("url").value + "/study/list";
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
function get_detail(exp)
{
  var items = [];
  lineAry = [];
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
      items.push("<input type=\"button\" value=\"Select these lines\"");
      items.push("<tr>");
      jQuery.each( data, function( key, val ) {
          if (key == "design") {
            items.push("<tr><table>");
            //save column names
            jQuery.each( data.design[0], function( key2, val2 ) {
              items.push("<td>" + key2);
            });
            //save field layout
            jQuery.each( data.design, function( i, j ) {
              items.push("<tr>");
              jQuery.each( data["design"][i], function ( key2, val2 ) {
                items.push("<td>" + val2);
                if (key2 == "lineRecordName") {
                  lineAry.push(val2);
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
  var apiUrl = document.getElementById("url").value + "/traits/list";
  if (document.getElementById("YesDebug").checked === true) {
    items.push("API call = " + apiUrl);
  }
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>List of studies</h3><table>");
      items.push("<tr>");
      jQuery.each( data[0], function( key, val ) {
         if (key == "uid") {
           items.push("<td>");
         } else {
           items.push("<td>" + key);
         }
      });
      jQuery.each( data, function( i, j ) {
        items.push("<tr>");
        var studyId = data[i].uid;
        jQuery.each( data[i], function( key, val ) {
          if (key == "uid") {
            items.push("<td><button onclick=\"get_detail_trait(" + studyId + ")\">details</button>");
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
