/*global $,Ajax,Element,window*/
//var php_self = document.location.href;
//var title = document.title;
var mmm = 10;
var mml = 10;
var mmaf = 5;
var clusters = 5;

function run_rscript(unq_file) {
  window.location ="cluster3d.php?clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
}

function run_rscript2(unq_file) {
  jQuery.ajax({
  type: "GET",
  url: "cluster3d.php",
  data: "clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml,
  success: function(data, textStatus) {
    jQuery("#step2").html(data);
  },
  error: function() {
        alert('Error in running cluster program');
      }
  });
}
function getCluster1() {
  var url = "";
  var formData = Object(); 
  //formData.lines  = document.getElementsByName("lines").value;
  formData.lines = lineStr;
  formData.url = document.getElementById("url").value;
  jQuery.ajax({
  type: "POST",
  url: "clusterapp.php",
  data: formData,
  success: function(data, textStatus) {
    jQuery("#step2").html(data);
  },
  error: function() {
        alert('Error in running cluster program');
      }
  });
}
