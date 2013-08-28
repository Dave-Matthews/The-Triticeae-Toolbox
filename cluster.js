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
    var req= getXMLHttpRequest();
    var resp=document.getElementById('ajaxresult');
    if(!req) {
       alert("Browser not supporting Ajax");
    }
    resp.innerHTML = "<img id='spinner' src='./images/progress.gif' alt='Working...'><br>" +
                     "Retrieving all marker alleles for <b>" + linecount + "<\/b> lines.<br>" +
                     "Retrieval rate is ca. one minute for 500 lines (1.5 million alleles).";
    req.onreadystatechange = function(){
          if(req.readyState === 4){
            var button = "<p><input type='submit' value='Analyze'><\/form>";
            resp.innerHTML= button + req.responseText;
          }
        };
        req.open("GET", "cluster_getalleles.php?time=" + time + "&mmaf=" + mmaf + "&mmm=" + mmm + "&mml=" + mml, true);
        req.send(null);
}
