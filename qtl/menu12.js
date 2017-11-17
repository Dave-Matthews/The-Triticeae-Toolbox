/*global, alert, jQuery, Ajax, $ */

var php_self = document.location.href;
var title = document.title;
var phenotype_items_str = "";
var assembly = "";
var imput = "No";
var method = "single";
var groupBy = "marker";
var sortBy = "score";
var traits_remove = "";

function deselect() {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=refreshtitle&pi=" + traits_remove + "&cmd=clear",
      success: function(data, textStatus) {
        jQuery("#title").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error deleting phenotype');
      }
    });
}

//detail for marker
function detailM(uid) {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=detail&pi=" + phenotype_items_str + "&uid=" + uid + "&imput=" + imput + "&method=" + method,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

//detail for gene
function detailG(gene) {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=detail&pi=" + phenotype_items_str + "&gene=" + gene + "&imput=" + imput + "&method=" + method,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function display_qtl() {
    document.title = 'Loading GWAS results';
    assembly = $('input[name="assembly"]:checked').val();

    if (phenotype_items_str == "") {
        document.getElementById('step3').innerHTML = "Please select category and trait!";
    } else {
      $("#spinner").show();
      jQuery.ajax({
        type: "GET",
        url: php_self,
        data: "function=displayQTL&pi=" + phenotype_items_str + "&sortby=" + sortBy + "&group=" + groupBy + "&method=" + method + "&assembly=" + assembly,
        success: function(data, textStatus) {
          jQuery("#step3").html(data)
          document.title = title;
        },
        complete: function() {
          $("#spinner").hide();
        },
        error: function() {
          alert('Error finding QTL');
        }
      });
    }
}

function selectDb(parm) {
    document.title = 'Loading GWAS results';
    assembly = $('input[name="assembly"]:checked').val();

    $("#spinner").show();
    if (parm == "single") {
      method = "single";
    } else if (parm == "set") {
      method = "set";
    } else if (parm = "imput") {
      method = "imput";
    }
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str + "&sortby=" + sortBy + "&group=" + groupBy + "&method=" + method + "&assembly=" + assembly,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      complete: function() {
        $("#spinner").hide();
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function group(parm) {
    document.title = 'Loading GWAS results';
    assembly = $('input[name="assembly"]:checked').val();

    $("#spinner").show();
    groupBy = parm;
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str + "&sortby=" + sortBy + "&group=" + groupBy + "&imput=" + imput + "&assembly=" + assembly,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      complete: function() {
        $("#spinner").hide();
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function sort(parm) {
    document.title = 'Loading GWAS results';
    assembly = $('input[name="assembly"]:checked').val();

    $("#spinner").show();
    sortBy = parm;
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str + "&sortby=" + parm + "&group=" + groupBy + "&assembly=" + assembly,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      complete: function() {
        $("#spinner").hide();
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function update_phenotype_items() {
    phenotype_items_str = "";
    lines_str = "";
    $('#pheno_itm :selected').each(function(i, selected) {
        phenotype_items_str += (phenotype_items_str === "" ? "" : ",") + $(selected).val();
    });
    display_qtl();
    document.getElementById('step4').innerHTML = "";
}

function load_phenotypes2() {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=step2phenotype&pc=" + phenotype_categories_str,
      success: function(data, textStatus) {
        jQuery("#step2").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error in selecting phenotype categories');
      }
    });
}

function update_phenotype_categories(options) {
    phenotype_categories_str = "";
    phenotype_items_str = "";
    experiments_str = "";
    lines_str = "";
    select1_str = "Phenotypes";
    $('#pheno_cat :selected').each(function(i, selected) {
        phenotype_categories_str += (phenotype_categories_str === "" ? "" : ",") + $(selected).val();
    });
    load_phenotypes2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
}
