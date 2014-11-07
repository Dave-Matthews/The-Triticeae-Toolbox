/*global jQuery,$,alert */
function getListStudies()
{
  var items = [];
  var apiUrlList = document.getElementById("url").value + "/study/list";
  items.push("API call = " + apiUrlList);
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrlList,
    success: function(data, textStatus) {
      items.push("<h3>List of studies</h3><table>");
      items.push("<tr>");
      jQuery.each( data[0], function( key, val ) {
         if (key == "studyId") {
           items.push("<td>");
         } else {
           items.push("<td>" + key);
         }
      });
      jQuery.each( data, function( i, j ) {
        items.push("<tr>");
        var studyId = data[i].studyId;
        jQuery.each( data[i], function( key, val ) {
          if (key == "studyId") {
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
  var apiUrl = document.getElementById("url").value + "/study/" + exp;
  items.push("API call = " + apiUrl)
  jQuery.ajax({
    type: "GET",
    dataType: "json",
    url: apiUrl,
    success: function(data, textStatus) {
      items.push("<h3>Study details</h3><table>");
      items.push("<tr>");
      jQuery.each( data, function( key, val ) {
          if (key == "design") {
            items.push("<tr><table>");
            jQuery.each( data.design[0], function( key2, val2 ) {
              items.push("<td>" + key2);
            });
            jQuery.each( data.design, function( i, j ) {
              items.push("<tr>");
              jQuery.each( data["design"][i], function ( key2, val2 ) {
                items.push("<td>" + val2);
              });
            });
          } else {
            items.push("<tr><td>" + key + "<td>" + val);
          }
      });
      items.push("</table>");
      var html = items.join("");
      jQuery("#step3").html(html);
    },
    error: function() {
        alert('Error in selecting experiment list');
      }
  });
}

function getListTraits()
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/traits/list";
  items.push("API call = " + apiUrl);
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
        alert('Error in selecting experiment list');
      }
  });
  document.getElementById("step3").innerHTML = "";
}

function get_detail_trait(exp)
{
  var items = [];
  var apiUrl = document.getElementById("url").value + "/traits/" + exp;
  items.push("API call = " + apiUrl);
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
