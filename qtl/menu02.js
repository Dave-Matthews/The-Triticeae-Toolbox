/*global, alert, jQuery, Ajax, $ */

var php_self = document.location.href;
var title = document.title;
var phenotype_items_str = "";
var groupBy = "";

function detail(uid) {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=detail&pi=" + phenotype_items_str + "&uid=" + uid,
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
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function group(parm) {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str + "&group=" + parm,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function sort(parm) {
    document.title = 'Loading Step1...';
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayQTL&pi=" + phenotype_items_str + "&sortby=" + parm,
      success: function(data, textStatus) {
        jQuery("#step3").html(data)
        document.title = title;
      },
      error: function() {
        alert('Error finding QTL');
      }
    });
}

function update_phenotype_items(options) {
    phenotype_items_str = "";
    lines_str = "";
    $A(options).each(
            function(phenotype_items) {
                        if (phenotype_items.selected) {
                            phenotype_items_str += (phenotype_items_str === "" ? ""
                                    : ",") + phenotype_items.value;
                        }
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
    $A(options).each(
            function(phenotype_categories) {
            if (phenotype_categories.selected) {
                phenotype_categories_str += (phenotype_categories_str === "" ? "" : ",") + phenotype_categories.value;
            }
        });
    load_phenotypes2();
    document.getElementById('step3').innerHTML = "";
    document.getElementById('step4').innerHTML = "";
}
