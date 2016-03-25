/*global, alert, jQuery, Ajax, $*/

var php_self = document.location.href;

function load_title(command) {
    Element.show('spinner');
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=refreshtitle&lines=" + lines_str + "&exps=" + experiments_str + '&cmd=' + command + '&subset=' + subset,
      success: function(data, textStatus) {
        jQuery("#title").html(data);
        update_side();
      },
      error: function() {
          alert('Error in javascript');
      }
    });
}
