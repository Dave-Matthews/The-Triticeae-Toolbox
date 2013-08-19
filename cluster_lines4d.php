<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();

// Use the incoming value of $time instead of a new one.  Does it work?
if (isset($_POST['time'])) $time = $_POST['time'];
 else $time = date("U");

// If we entered the script having picked a cluster in cluster3d.php,
// load them into $_SESSION['selected_lines'].
if (isset($_POST['mycluster'])) {
  $mycluster = $_POST['mycluster'];
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
  $result = mysql_query($query) or die(mysql_error()."<br>Query was:<br>".$query);
  $_SESSION['selected_lines'] = array();
  while ($row = mysql_fetch_row($result)) {
    array_push($_SESSION['selected_lines'], $row[0]);
  }
}

// If only a few lines are selected, reduce the suggested number of clusters.
$clusters = 5;
if (isset($_POST['clusters'])) 
  $clusters = $_POST['clusters'];
if (isset($_SESSION['selected_lines'])) {
  $linecount = count($_SESSION['selected_lines']);
  $clusters = min($clusters, $linecount - 1);
 }

?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Cluster Lines 3D, hclust()</h1>
  <div class="section">

  <p>The 
  <font color=blue>Currently selected lines and traits</font> will be clustered according to their 
  distance computed from markers and trait values, using the R procedure <b>hclust()</b> (Hierarchical cluster analysis on a set of dissimilarities).  
  The clusters will be displayed in three dimensions calculated by <b>Singular
  Value Decomposition</b>, R procedure <b>svd()</b>. This method of clustering typically requires at least 25 lines for the execution to complete.<p>
  When you have examined the results you can select the clusters you want to use
  as your new <font color=blue>Currently selected lines</font>.

<?php
$selectedcount = count($_SESSION['selected_lines']);
echo "<h3><font color=blue>Currently selected lines</font>: $selectedcount</h3>";
if (!isset ($_SESSION['selected_lines']) || (count($_SESSION['selected_lines']) == 0) ) {
  // No lines selected so prompt to get some.
  echo "<a href=".$config['base_url']."pedigree/line_properties.php>Select lines</a> or ";
  echo "<a href=".$config['base_url']."downloads/select_all.php>lines and trait</a>. ";
  echo "(Patience required for more than a few hundred lines.)";
}
else {
  print "<textarea rows = 9>";
  foreach ($_SESSION['selected_lines'] as $lineuid) {
    $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
    while ($row=mysql_fetch_assoc($result)) {
      $selval=$row['line_record_name'];
      print "$selval\n";
    }
  }
  print "</textarea>";
?>
  <script type="text/javascript" src="cluster4.js"></script>
  <p>How many clusters?&nbsp;
  <input type=text id='clusters' name="clusters" value=<?php echo $clusters ?> size="1">  (Maximum 8.)<br>
  <?php
        $min_maf = 5;
        $max_missing = 10;
        $max_miss_line = 10;
        ?>
        <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
  <br><br>
  <input type=button value="Cluster Analysis" onclick="javascript:get_alleles(<?php echo ($time) ?>)">
  <div id='ajaxresult'></div>
  <script type="text/javascript">
 	var resp=document.getElementById('ajaxresult');
	resp.innerHTML = "<img id='spinner' src='./images/progress.gif' alt='Working...' style='display:none;'>";
  </script>
<?php
	    } // end of if('selected_lines' exist)

echo "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

?>
