/*global $,Ajax,Element,window*/
var php_self = document.location.href;
var title = document.title;
var mmm = 10;
var mml = 10;
var mmaf = 5;
var clusters = 5;

function run_rscript(unq_file) {
  window.location ="cluster4d.php?clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
}

function get_alleles(unq_file) {
  mmm = $('mmm').getValue();
  mml = $('mml').getValue();
  mmaf = $('mmaf').getValue();
  clusters = $('clusters').getValue();
  var url = "cluster_getallelesp.php?time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
  var tmp = new Ajax.Request(url, {
        onComplete : function() {
            document.title = title;
            run_rscript(unq_file);
        }
    });
}

function get_alleles2(unq_file) {
  var url = "cluster_getallelesp.php?time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
  var tmp = new Ajax.Request(url, {
        onComplete : function() {
            document.title = title;
            run_rscript(unq_file);
        }
    });
}

function recluster(unq_file) {
  mmm = $('mmm').getValue();
  mml = $('mml').getValue();
  mmaf = $('mmaf').getValue();
  clusters = $('clusters').getValue();
  window.scrollTo(0,0);
  document.getElementById('primaryContent').innerHTML = "Creating data file";
  Element.show('spinner');
  var url = "cluster_lines4d.php";
  var tmp = new Ajax.Request(url, {
        onComplete : function() {
            document.title = title;
            get_alleles2(unq_file);
        }
    });
}

