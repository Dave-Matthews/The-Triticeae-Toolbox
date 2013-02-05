/*global $,Ajax*/

var php_self = document.location.href;
var title = document.title;

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

