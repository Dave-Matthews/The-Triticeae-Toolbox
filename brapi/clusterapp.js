/*global $,Ajax,Element,window*/
var php_self = document.location.href;
var title = document.title;
var mmm = 10;
var mml = 10;
var mmaf = 5;
var clusters = 5;

function run_rscript(unq_file) {
  window.location ="brapi/cluster3d.php?clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
}

