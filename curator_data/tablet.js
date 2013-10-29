/*global $,$A,Ajax*/

var php_self = document.location.href;
var exp_uid = "";
var title = document.title;

function update_expr() {
	var e = document.getElementById("experiment");
	exp_uid = e.options[e.selectedIndex].value;

	var url = php_self + "?function=save" + "&uid=" + exp_uid;
	tmp = new Ajax.Updater($('export'), url, {
        onComplete : function() {
            $('export').show();
            document.title = title;
        }
    });
}