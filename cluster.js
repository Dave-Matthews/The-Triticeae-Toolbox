/*global alert,$,Ajax,Element,window,getXMLHttpRequest*/
var php_self = document.location.href;
var title = document.title;
var mmm = 10;
var mml = 10;
var mmaf = 5;
var clusters = 5;

function filter_lines(time, linecount) {
    var mmm = $('mmm').getValue();
    var mml = $('mml').getValue();
    var mmaf = $('mmaf').getValue();
    var resp = document.getElementById('ajaxresult');
    resp.innerHTML = "<img id='spinner' src='./images/progress.gif' alt='Working...'><br>" +
            "Retrieving all marker alleles for <b>" + linecount + "<\/b> lines.<br>" +
            "Retrieval rate is ca. one minute for 500 lines (1.5 million alleles).";
    jQuery.ajax({
        type: "GET",
        url: "cluster_getalleles.php",
        data: "time=" + time + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml,
        success: function (data, textStatus) {
            var button = "<p><input type='submit' value='Analyze'><\/form>";
            resp.innerHTML = button + data;
        },
        error: function () {
            alert('Error in running cluster_getalleles.php');
        }
    });
}
