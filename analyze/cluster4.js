/*global $,Ajax,Element,window*/
var php_self = document.location.href;
var title = document.title;
var mmm = 10;
var mml = 10;
var mmaf = 5;
var clusters = 5;

function run_rscript(unq_file) {
  window.location ="analyze/cluster4d.php?clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
}

function get_alleles(unq_file) {
  mmm = $('mmm').getValue();
  mml = $('mml').getValue();
  mmaf = $('mmaf').getValue();
  clusters = $('clusters').getValue();
  Element.show('spinner');
  var url = "analyze/cluster_getallelesp.php?time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
  var tmp = new Ajax.Request(url, {
        onSuccess : function() {
            document.title = title;
            run_rscript(unq_file);
        },
        onFailure : function() {
            document.title = title;
            document.getElementById('primaryContent').innerHTML = "Failed to create data file";
        }
    });
}

function get_alleles2(unq_file) {
  var url = "analyze/cluster_getallelesp.php?time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
  var tmp = new Ajax.Request(url, {
        onSuccess : function() {
            document.title = title;
            run_rscript(unq_file);
        },
        onFailure : function() {
            document.title = title;
            document.getElementById('primaryContent').innerHTML = "Failed to create data file";
        }
    });
}

function run_status(unq_file) {
  window.location ="analyze/cluster4_status.php?clusters=" + clusters + "&time=" + unq_file + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
}

function recluster(unq_file) {
  mmm = $('mmm').getValue();
  mml = $('mml').getValue();
  mmaf = $('mmaf').getValue();
  var i = document.myForm.elements.length;
  var j = document.myForm.elements;
  var k = 0;
  var param = "function=recluster";
  for (k=0; k<i; k++) {
    if (document.myForm.elements[k].checked === true) {
      param += '&' + document.myForm.elements[k].name + '=' + document.myForm.elements[k].value;
    } else if (document.myForm.elements[k].name == 'time') {
      param += '&' + document.myForm.elements[k].name + '=' + document.myForm.elements[k].value;
    }
  }
  clusters = $('clusters').getValue();
  window.scrollTo(0,0);
  document.getElementById('primaryContent').innerHTML = "Creating data file";
  Element.show('spinner');
  var url = "cluster_lines4d.php";
  var tmp = new Ajax.Request(url, {
        method: 'post',
        postBody: param,
        onComplete : function() {
            document.title = title;
            get_alleles2(unq_file);
        }
    });
}

function filter_lines() {
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = "analyze/cluster_getalleles.php?mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml;
    var tmp = new Ajax.Updater($('filter'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            Element.hide('spinner');
            document.title = title;
        }
    });
}
