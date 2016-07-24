/*global alert,$,$$,$A,$H,Prototype,Ajax,Template,Element,getXMLHttpRequest*/
var php_self = document.location.href;
var title = document.title;
var select_str = "";
var fixed1 = "trial";
var fixed2 = "0";
var p3d = "";
var method = "";
var analysis_count = "";
var unq_file = "";

function load_title(command) {
    var url = php_self + "?function=refreshtitle" + '&cmd=' + command;
    var tmp = new Ajax.Updater($('title'), url, {asynchronous: false}, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('title').show();
            document.title = title;
        }
    });
    url = "side_menu.php";
    tmp = new Ajax.Updater($('quicklinks'), url, {
        onComplete : function () {
            $('quicklinks').show();
            document.title = title;
        }
    });
}

function run_histo(unq_file, pheno) {
    var url = php_self + "?function=run_histo" + "&unq=" + unq_file + "&pheno=" + pheno;
    new Ajax.Updater($('step3'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step3').show();
            document.title = title;
        }
    });
}

function run_cluster(unq_file) {
    var url = php_self + "?function=run_cluster" + "&unq=" + unq_file;
    new Ajax.Updater($('step3'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step3').show();
            document.title = title;
        }
    });
}

function run_status(unq_file) {
    var url = "";
    if (method === "gwas") {
        url = php_self + "?function=gwas_status&unq=" + unq_file;
    } else {
        url = php_self + "?function=pred_status&unq=" + unq_file;
    }
    var tmp = new Ajax.Updater($('step5'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step5').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}

function run_gwa(unq_file) {
    var url = "";
    var radio = document.getElementsByName('P3D');
    if (radio[1].checked) {
      p3d = "FALSE";
    } else {
      p3d = "TRUE";
    }
    if (analysis_count > 300) {
        url = php_self + "?function=run_gwa2" + "&unq=" + unq_file + "&fixed1=" + fixed1 + "&fixed2=" + fixed2 + "&p3d=" + p3d;
    } else {
        url = php_self + "?function=run_gwa" + "&unq=" + unq_file + "&fixed1=" + fixed1 + "&fixed2=" + fixed2 + "&p3d=" + p3d;
    }
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "Running R script";
    var tmp = new Ajax.Updater($('step5'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step5').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}

function run_rscript(unq_file) {
    var url = "";
    if (analysis_count > 3000) {
        url = php_self + "?function=run_rscript2" + "&unq=" + unq_file;
    } else {
        url = php_self + "?function=run_rscript" + "&unq=" + unq_file;
    }
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
    document.getElementById('step5').innerHTML = "Running R script";
    var tmp = new Ajax.Updater($('step5'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step5').show();
            document.title = title;
            Element.hide('spinner');
        }
    });
}

function filter_lines() {
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = php_self + "?function=filter_lines" + '&mmm=' + mmm + '&mml=' + mml + '&maf=' + mmaf;
    var tmp = new Ajax.Updater($('filter'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('filter').show();
            document.title = title;
        }
    });
}

function load_genomic_prediction(count) {
    var unq_file = Date.now();
    method = "pred";
    analysis_count = count;
    document.getElementById('step5').innerHTML = "";
    //Element.show('spinner');
    document.getElementById('step3').innerHTML = "Creating Data Files";
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = php_self + "?function=filter_lines" + '&mmm=' + mmm + '&mml=' + mml + '&maf=' + mmaf;
    var tmp = new Ajax.Updater($('filter'), url, {asynchronous: false}, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('filter').show();
            document.title = title;
        }
    });
    url = php_self + "?function=download_session_v4" + "&unq=" + unq_file + '&mmm=' + mmm + '&mml=' + mml + '&mmaf=' + mmaf + "&fixed1=" + fixed1;
    tmp = new Ajax.Updater($('step1'), url, {
        onCreate: function () { Element.show('spinner'); },
        onComplete : function () {
            $('step1').show();
            document.title = title;
            document.getElementById('step5').innerHTML = "Finished Data Files";
            run_histo(unq_file);
            run_rscript(unq_file);
        }
    });
}

function load_histogram(pheno) {
    var unq_file = Date.now();
    document.getElementById('step5').innerHTML = "";
    Element.show('spinner');
    document.getElementById('step3').innerHTML = "Creating Data Files";
    var url = php_self + "?function=download_session_v4" + "&unq=" + unq_file + "&pheno=" + pheno;
    var tmp = new Ajax.Updater($('step3'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('step3').show();
            document.title = title;
            run_histo(unq_file, pheno);
            Element.hide('spinner');
        }
    });
}

function load_genomic_gwas2() {
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    Element.show('spinner');
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=download_session_v3" + "&unq=" + unq_file + '&mmm=' + mmm + '&mml=' + mml + '&mmaf=' + mmaf,
      success: function(data, textStatus) {
        jQuery("#step1").html(data);
        document.getElementById('step4').innerHTML = "Finished Data Files";
        run_histo(unq_file);
        run_gwa(unq_file);
      },
      error: function() {
        Element.hide('spinner');
        alert('Error in running GWAS');
      }
    });
}

// use this function to run GWA on training set 
function load_genomic_gwas(count) {
    unq_file = Date.now();
    method = "gwas";
    analysis_count = count;
    document.getElementById('step5').innerHTML = "";
    Element.show('spinner');
    document.getElementById('step4').innerHTML = "Creating Data Files";
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var url = php_self + "?function=filter_lines" + '&mmm=' + mmm + '&mml=' + mml + '&maf=' + mmaf;
    var tmp = new Ajax.Updater($('filter'), url, {
        onCreate: function() { Element.show('spinner'); },
        onComplete : function() {
            $('filter').show();
            document.title = title;
            load_genomic_gwas2();
        }
    });
}

// use this function to save trial fixed effects options
function update_fixed(option) {
  fixed2 = option;
}

function filterDesc(min_maf, max_missing, max_miss_line) {
  alert("1. Marker allele frequency is calculated for the selected lines.\n2. Markers are removed that have MAF less than " +  min_maf + "% or are missing in more than " + max_missing + "% of the lines.\n3. Lines are removed if they are missing more than " + max_miss_line + "% of the marker data.\nAfter changing the default settings for the filter, select Analyze to use the new paramaters");
}

function linesRemoved(lineRemovedName) {
  alert("Lines Removed\n" + lineRemovedName);
}

