/*global $,Ajax*/

var php_self = document.location.href;
var title = document.title;
var trial = "";
var formula1 = "";
var formula2 = "";
var w1 = "";
var w2 = "";
var w3 = "";
var smooth = "";

function display(option) {
  var uid = option;
  var url = php_self + "?function=display&uid=" + uid;
  document.location = url;
}

function display2(option) {
  var uid = option;
  document.getElementById("step2").innerHTML = "";
  var url = php_self + "?function=display&uid=" + uid;
  var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}

function update_trial() {
  var e = document.getElementById("trial");
  trial = e.options[e.selectedIndex].value;
  var url = php_self + "?function=selTrial&trial=" + trial;
  /*var tmp = new Ajax.Updater($('step2'), url, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
  */
}

function update_w1() {
  w1 = document.getElementById("W1").value;
}

function update_w2() {
  w2 = document.getElementById("W2").value;
}

function update_f1() {
  var e = document.getElementById("formula1");
  formula1 = e.options[e.selectedIndex].value;
  formula1 = encodeURIComponent(formula1);
  if (formula1 == "NWI1") {
    w1 = 970;
    w2 = 900;
    formula2 = "(W1-W2)/(W1+W2)";
  } else if (formula1== "NWI3") {
    w1 = 970;
    w2 = 880;
    formula2 = "(W1-W2)/(W1+W2)";
  } else if (formula1 == "NDVI") {
    w1 = 900;
    w2 = 680;
    formula2 = "(W1-W2)/(W1+W2)";
  } else if (formula1 == "NDVIR") {
    w1 = 780;
    w2 = 670;
    formula2 = "(W1-W2)/(W1+W2)";
  } else if (formula1 == "NDVIG") {
    w1 = 780;
    w2 = 550;
    formula2 = "(W1-W2)/(W1+W2)";
  } else if (formula1 == "SR") {
    w1 = 900;
    w2 = 680;
    formula2 = "(W1-W2)";
  }
  document.getElementById("W1").value = w1;
  document.getElementById("W2").value = w2;
  document.getElementById("formula2").value = formula2;
   
}

function update_f2() {
  formula2 = document.getElementById("formula2").value;
  formula2 = encodeURIComponent(formula2);
}

function update_smooth() {
  var e = document.getElementById("smooth");
  smooth = e.options[e.selectedIndex].value;
}

function cal_index() {
  var param = 'trial=' + trial;
  param += '&W1=' + w1;
  param += '&W2=' + w2;
  param += '&formula1=' + formula1;
  param += '&formula2=' + formula2;
  param += '&smooth=' + smooth;
  document.getElementById("step2").innerHTML = "";
  var url = "curator_data/cal_index_check.php?trial=" + trial + "&W1=" + w1 + "&W2=" + w2 + "&formula1=" + formula1 + "&formula2=" + formula2;
  url = "curator_data/cal_index_check.php";
  /*var url = php_self + "?function=display&uid=" + uid;*/
  /*var tmp = new Ajax.Updater($('step2'), url, {*/
  var tmp = new Ajax.Updater($('step2'), url, {method: 'post', postBody: param}, {
        onComplete : function() {
            $('step2').show();
            document.title = title;
        }
    });
}
