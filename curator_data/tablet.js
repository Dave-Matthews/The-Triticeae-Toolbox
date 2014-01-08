/*global $,$A,Ajax*/

var php_self = document.location.href;
var phenotype_categories_str = "";
var phenotype_items_str = "";
var experiments_str = "";
var exp_uid = "";
var title = document.title;

function update_expr() {
	var e = document.getElementById("experiment");
	exp_uid = e.options[e.selectedIndex].value;

	var url = php_self + "?function=save" + "&uid=" + exp_uid;
	var tmp = new Ajax.Updater($('export'), url, {
        onComplete : function() {
            $('export').show();
            document.title = title;
        }
    });
}

function load_phenotypes() {
    $('step2').hide();
    var url = php_self + "?function=step2phenotype&pc=" + phenotype_categories_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}

function update_phenotype_categories(options) {
    phenotype_categories_str = "";
    phenotype_items_str = "";
    experiments_str = "";
    $A(options).each(
                function(phenotype_categories) {
            if (phenotype_categories.selected) {
                phenotype_categories_str += (phenotype_categories_str === "" ? "" : ",") + phenotype_categories.value;
            }
        });
    load_phenotypes();
    document.getElementById('step3').innerHTML = "";
}

function load_phenotypes3() {
    $('step3').hide();
    var url = php_self + "?function=step3phenotype&pi=" + phenotype_items_str;
    document.title = 'Loading Step1...';
    var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function() {
            $('step3').show();
            document.title = title;
        }
    });
}

function update_phenotype_items(options) {
    phenotype_items_str = "";
    $A(options)
            .each(
                    function(phenotype_items) {
                        if (phenotype_items.selected) {
                            phenotype_items_str += (phenotype_items_str === "" ? ""
                                    : ",") + phenotype_items.value;
                        }
                    });
    load_phenotypes3();
    document.getElementById('step4').innerHTML = "";
}


