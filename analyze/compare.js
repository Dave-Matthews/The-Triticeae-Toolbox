/*global alert,$,$$,$A,$H,Prototype,Ajax,Template,Element*/
var php_self = document.location.href;
var title = document.title;

var trial1 = "";
var trial2 = "";
var pheno = "";
var index = "Difference";
var ptype = "scatter";
var formula1 = "DI";
var formula2 = "(data$trial1 - data$trial2)";
var control = 1;

function disp_index(pheno_uid) {
    var off_switch = "off_switch" + pheno_uid;
    var on_switch = "on_switch" + pheno_uid;
    var content = "content" + pheno_uid;
    Element.hide(on_switch);
    Element.show(off_switch);
    Element.show(content);
}

function hide_index(pheno_uid) {
    var off_switch = "off_switch" + pheno_uid;
    var on_switch = "on_switch" + pheno_uid;
    var content = "content" + pheno_uid;
    Element.hide(off_switch);
    Element.show(on_switch);
    Element.hide(content);
}

function run_compare(unq_file) {
    var url = php_self + "?function=calculate" + "&unq=" + unq_file + "&index=" + index + "&formula=" + formula2 + "&type=" + ptype;
    var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function () {
            $('step3').show();
            document.title = title;
        }
    });
}

function run_compare2(unq_file) {
    var url = php_self + "?function=plotManyTrials" + "&unq=" + unq_file + "&index=" + index + "&formula=" + formula2 + "&type=" + ptype;
    var tmp = new Ajax.Updater($('step3'), url, {
        onComplete : function () {
            $('step3').show();
            document.title = title;
        }
    });
}

function twoTrials(frm) {
        if (frm.ptype[1].checked) {
            ptype = "line";
        } else {
            ptype = "scatter";
        }
	var unq_file = Date.now();
        var e= document.getElementById("trial1");
        trial1 = e.options[e.selectedIndex].value;
        e= document.getElementById("trial2");
        trial2 = e.options[e.selectedIndex].value;
        e= document.getElementById("pheno");
        pheno = e.options[e.selectedIndex].value;
	var url = php_self + "?function=download_session_v4&unq=" + unq_file + "&pheno=" + pheno + "&trial1=" + trial1 + "&trial2=" + trial2;
	var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
            run_compare(unq_file);
        }
    });
}

function manyTrials(frm) {
    ptype = "line";
    var unq_file = Date.now();
    var e= document.getElementById("pheno");
    pheno = e.options[e.selectedIndex].value;
    var url = php_self + "?function=download_session_v4&unq=" + unq_file + "&pheno=" + pheno;
        var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
            run_compare2(unq_file);
        }
    });
}

function update_t1() {
	var e= document.getElementById("trial1");
	trial1 = e.options[e.selectedIndex].value;
}

function update_t2() {
	var e= document.getElementById("trial2");
	trial2 = e.options[e.selectedIndex].value;
}

function update_ptype(frm) {
        if (frm.ptype[1].checked) {
            ptype = "line";
        } else {
            ptype = "scatter";
        }
}

function update_pheno(options) {
        pheno = "";
        $A(options).each(
            function(traits){
                if (traits.selected) {
                    pheno += (pheno === "" ? "" : ",") + traits.value;
                }
            });
        var url = php_self + "?function=status&pheno=" + pheno;
        var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
        });
}

function update_control(frm) {
	document.getElementById("step2").innerHTML = "";
	if (frm.control[0].checked) {
                if (formula1 == "DI") {
                        index = "Difference";
                        formula2 = "(data$trial1 - data$trial2)";
		} else if (formula1 == "PD") {
                        index = "Percent Difference";
			formula2 = "(data$trial1 - data$trial2)/((data$trial1 + data$trial2)/2)";
		} else if (formula1 == "STI") {
                        index = "Stress Tolerance Index";
			formula2 = "(data$trial1*data$trial2)/(mean(data$trial1, na.rm = TRUE)**2)";
		} else if (formula1 == "SSI") {
                        index = "Stress Susceptibility Index";
			formula2 = "(1 - (data$trial2/data$trial1))/(1 - (mean(data$trial2, na.rm = TRUE)/mean(data$trial1, na.rm = TRUE)))";
		} else if (formula1 == "GM") {
                        index = "Geometric Mean";
			formula2 = "sqrt(data$trial1*data$trial2)";
		} else {
                        index = "Difference";
                        formula2 = "(data$trial1 - data$trial2)";
                }
		control = 1;
	} else if (frm.control[1].checked) {
                if (formula1 == "DI") {
                        index = "Difference";
                        formula2 = "(data$trial2 - data$trial1)";
		} else if (formula1 == "PD") {
                        index = "Percent Difference";
			formula2 = "(data$trial2 - data$trial1)/((data$trial1 + data$trial2)/2)";
		} else if (formula1 == "STI") {
                        index = "Stress Tolerance Index";
			formula2 = "(data$trial1*data$trial2)/(mean(data$trial2, na.rm = TRUE)**2)";
		} else if (formula1 == "SSI") {
                        index = "Stress Susceptibility Index";
			formula2 = "(1 - (data$trial1/data$trial2))/(1 - (mean(data$trial1, na.rm = TRUE)/mean(data$trial2, na.rm = TRUE)))";
		} else if (formula1 == "GM") {
                        index = "Geometric Mean";
			formula2 = "sqrt(data$trial1*data$trial2)";
		} else {
                        index = "Difference";
                        formula2 = "(data$trial2 - data$trial1)";
                }
		control = 2;
	} else {
		formula2 = "sqrt(data$trial1*data$trial2)";
	}
	document.getElementById("formula2").value = formula2;
	formula2 = encodeURIComponent(formula2);
}

function update_f1() {
	  var e = document.getElementById("formula1");
	  formula1 = e.options[e.selectedIndex].value;
	  document.getElementById("step2").innerHTML = "";
	  document.getElementById("step3").innerHTML = "";
          if (formula1 == "DI") {
                  index = "Difference";
                  formula2 = "(data$trial1 - data$trial2)";
	  } else if (formula1 == "PD") {
                  index = "Percent Difference";
		  if (control == 1) {
		      formula2 = "(data$trial1 - data$trial2)/((data$trial1 + data$trial2)/2)";
		  } else if (control == 2) {
			  formula2 = "(data$trial2 - data$trial1)/((data$trial1 + data$trial2)/2)";
		  } else {
			  formula2 = "";
			  document.getElementById("step2").innerHTML = "<font color=red>Error: select which trial is Normal/Control</font>";
		  }
	  } else if (formula1 == "GM") {
                  index = "Geometric Mean";
		  formula2 = "sqrt(data$trial1*data$trial2)";
	  } else if (formula1 == "STI") {
                  index = "Stress Tolerance Index";
		  if (control == 1) {
		      formula2 = "(data$trial1*data$trial2)/(mean(data$trial1)**2)";
		  } else if (control == 2) {
			  formula2 = "(data$trial1*data$trial2)/(mean(data$trial2)**2)";
		  } else {
			  formula2 = "";
			  document.getElementById("step2").innerHTML = "<font color=red>Error: select which trial is Normal/Control</font>";
		  }
	  } else if (formula1 == "SSI") {
                  index = "Stress Susceptibility Index";
		  if (control == 1) {
			  formula2 = "(1 - (data$trial2/data$trial1))/(1 - (mean(data$trial2)/mean(data$trial1)))";
		  } else if (control == 2) {
			  formula2 = "(1 - (data$trial1/data$trial2))/(1 - (mean(data$trial1)/mean(data$trial2)))";
		  } else {
			  formula2 = "";
			  document.getElementById("step2").innerHTML = "<font color=red>Error: select which trial is Normal/Control</font>";
		  }
	  }
	  document.getElementById("formula2").value = formula2;
	  formula2 = encodeURIComponent(formula2);
}

function update_f2() {
	formula2 = document.getElementById("formula2").value;
	formula2 = encodeURIComponent(formula2);
}
