var php_self = document.location.href;
var mm = 10;
var mmaf = 5;
var mml = 10;
var displayAllFlag = "Y";
var unq_file = Date.now();

function clear_session() {
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=clearOutlier&unq=" + unq_file,
      success: function(data, textStatus) {
        jQuery("#step2").html(data);
        document.getElementById("step3").innerHTML = "<input type='button' value='Analyze' onclick=use_session()><br> <img alt='spinner' id='spinner' src='images/ajax-loader.gif' style='display:none;' />";
      },
      error: function() {
        alert('Error in saving outlier');
      }
    });
}

function save_outlier() {
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=saveOutlier&unq=" + unq_file,
      success: function(data, textStatus) {
        jQuery("#step2").html(data);
      },
      error: function() {
        alert('Error in saving outlier');
      }
    });
}

function displayOut() {
    displayAllFlag = "N";
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayOut&unq=" + unq_file,
      success: function(data, textStatus) {
        jQuery("#step4").html(data);
      },
      error: function() {
        alert('Error in display');
      }
    });
}

function displayAll() {
    displayAllFlag = "Y";
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=displayAll&unq=" + unq_file,
      success: function(data, textStatus) {
        jQuery("#step4").html(data);
      },
      error: function() {
        alert('Error in display');
      }
    });
}

function use_session() {
    var typeG = true;
    var typeGE = false;
    var thresh = $('thresh').getValue();
    Element.show('spinner');
    jQuery.ajax({
      type: "GET",
      url: php_self,
      data: "function=download_session_v4" + "&typeG=" + typeG.checked + "&typeGE=" + typeGE.checked + '&mm=' + mm + '&mmaf=' + mmaf + '&unq=' + unq_file + "&thresh=" + thresh,
      success: function(data, textStatus) {
        jQuery("#step4").html(data);
        if (displayAllFlag == "Y") {
          displayAll();
        } else {
          displayOut();
        }
      },
      error: function() {
        alert('Error in analizing outliert');
      }
    });
}
