/*global alert,$,$$,$A,$H,Prototype,Ajax,Template,Element,getXMLHttpRequest*/
var php_self = document.location.href;
var title = document.title;
var select_str = "";

function load_title(command) {
    var url = php_self + "?function=refreshtitle" + '&cmd=' + command;
    var tmp = new Ajax.Updater($('title'), url, {asynchronous:false}, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('title').show();
            document.title = title;
        }
    });
    url = "side_menu.php";
    tmp = new Ajax.Updater($('quicklinks'), url, {
    onComplete : function() {
      $('quicklinks').show();
      document.title = title;
    }
    });
}

function run_histo(unq_file) {
    var url = php_self + "?function=run_histo" + "&unq=" + unq_file;
    var tmp = new Ajax.Updater($('step3'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
}

function run_cluster(unq_file) {
    var url = php_self + "?function=run_cluster" + "&unq=" + unq_file;
    var tmp = new Ajax.Updater($('step3'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
}

function run_gwa(unq_file) {
    var url = php_self + "?function=run_gwa" + "&unq=" + unq_file;
    var tmp = new Ajax.Updater($('step4'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step4').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}

function run_rscript(unq_file) {
    document.getElementById('step5').innerHTML = "Running R script";
    var url = php_self + "?function=run_rscript" + "&unq=" + unq_file;
    var tmp = new Ajax.Updater($('step5'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step5').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}

function load_genomic_prediction(unq_file) {
    document.getElementById('step5').innerHTML = "";
    Element.show('spinner'); 
    document.getElementById('step3').innerHTML = "Creating Data Files";
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = php_self + "?function=download_session_v4" + "&unq=" + unq_file + '&mmm=' + mmm + '&mml=' + mml + '&mmaf=' + mmaf;
    var tmp = new Ajax.Updater($('step1'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step1').show();
            document.title = title;
            document.getElementById('step5').innerHTML = "Finished Data Files";
            run_histo(unq_file);
            run_rscript(unq_file);
        }
    });
}

// use this function to run GWA on training set 
function load_genomic_gwas(unq_file) {
    document.getElementById('step5').innerHTML = "";
    Element.show('spinner');
    document.getElementById('step3').innerHTML = "Creating Data Files";
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = php_self + "?function=download_session_v3" + "&unq=" + unq_file + '&mmm=' + mmm + '&mml=' + mml + '&mmaf=' + mmaf;
    var tmp = new Ajax.Updater($('step1'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step1').show();
            document.title = title;
            document.getElementById('step3').innerHTML = "Finished Data Files";
            run_histo(unq_file);
            run_gwa(unq_file);
        }
    });
}
