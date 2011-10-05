<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();

// Use the incoming value of $time instead of a new one.  Does it work?
if (isset($_GET['time'])) $time = $_GET['time'];
 else $time = date("U");

// If we entered the script having picked a cluster in cluster_show.php,
// load them into $_SESSION['selected_lines'].
if (isset($_GET['mycluster'])) {
  $mycluster = $_GET['mycluster'];
  $where_in = "";
  $clustertable = file("/tmp/tht/clustertable.txt".$time);
  unlink("/tmp/tht/clustertable.txt".$time);
  $clustertable = preg_replace("/\n/", "", $clustertable);
  // Remove first line, "x".
  array_shift($clustertable);
  for ($i=0;$i<count($clustertable);$i++) {
    for ($j=0;$j<count($mycluster);$j++) {
      $line = explode("\t", $clustertable[$i]);
      if ($line[1] == $mycluster[$j]) {
	// Build query for line_record_uids for these names.
	$where_in .= "'".$line[0]."',";
      }
    }
  }
  $where_in = trim($where_in, ",");
  $query = "select line_record_uid, line_record_name 
     from line_records where line_record_name in (".$where_in.")
     order by line_record_name";
  $result = mysql_query($query) or die(mysql_error());
  $_SESSION['selected_lines'] = array();
  while ($row = mysql_fetch_row($result)) {
    array_push($_SESSION['selected_lines'], $row[0]);
  }
}

// If only a few lines are selected, reduce the suggested number of clusters.
$clusters = 5;
if (isset($_SESSION['selected_lines'])) {
  $linecount = count($_SESSION['selected_lines']);
  $clusters = min($clusters, $linecount - 1);
 }

?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Cluster Lines by Genotype</h1>
  <div class="section">

  <p>  This R program will cluster the 
  <a href="<?php echo $config['base_url']; ?>pedigree/line_selection.php">
  <font color=blue>currently selected lines</font></a> according to their 
  alleles for all markers, using "pam" (Partitioning Around Medoids).  

  <p>The results will be displayed as a two-dimensional PCA plot with
  the clusters color-coded.  If you enter some line names below, the legend will indicate
  which cluster they fall into.  

  <p>When you have examined the results you can select the clusters you want to work with.

  <form action="cluster_show.php">
  <p>How many clusters should pam divide the lines into?  &nbsp;&nbsp;
  <input type=text name="clusters" value=<?php echo $clusters ?> size="1">  (Maximum 8.)
  <p>Lines to label in the legend:<br>
  <textarea name="labels" rows=4 cols=12></textarea>
  E.g. MERIT, FEG148-16, ND24205, VA07B-54
  <input type='hidden' name='time' value=<?php echo $time ?> >

  <div id='ajaxresult'></div>
  <script type="text/javascript">
        var req= getXMLHttpRequest();
 	var resp=document.getElementById('ajaxresult');
 	if(!req) {
	  alert("Browser not supporting Ajax");
	}
	resp.innerHTML = "<img src='./images/progress.gif' alt='Working...'><br>\
Retrieving all marker alleles for <b><?php echo $linecount ?><\/b> lines.<br>\
Retrieval rate is ca. one minute for 500 lines (1.5 million alleles).";
  	req.onreadystatechange = function(){
	  if(req.readyState === 4){
	    var button = "<p><input type='submit' value='Analyze'><\/form>";
	    resp.innerHTML= button + req.responseText;
	  }
  	};
	req.open("GET", "cluster_getalleles.php?time=<?php echo $time ?>", true);
  	req.send(null);
	</script>

<?php
echo "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

?>