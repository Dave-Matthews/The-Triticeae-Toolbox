<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Cluster Lines by Genotype</h1>
  <div class="section">

<?php
$nclusters = $_GET['clusters'];
echo "<h3>Clusters: $nclusters</h3>";

// Line names to label in the legend
$linenames = $_GET['labels'];
if ($linenames != "") {
  if (strpos($linenames, ',') > 0 ) {
    $linenames = str_replace(", ",",", $linenames);	
    $lineList = explode(',',$linenames);
  } 
  elseif (preg_match("/\t/", $linenames)) {$lineList = explode("\t",$linenames);}
  else {$lineList = explode('\r\n',$linenames);}

  $labellines = "lineNames <- c(";
  for ($i=0; $i<count($lineList); $i++) {
    $labellines .= "\"$lineList[$i]\", ";
  }
  $labellines = trim($labellines, ", ");
  $labellines .= ")\n";
 }

$out = "png(\"".$config['root_dir']."downloads/temp/linecluster.png\", width=600, height=500)\n";
// Store the input parameters in file setupcluster.R.
$setup = fopen("R/temp/setupcluster.R", "w");
fwrite($setup, $out);
fwrite($setup, $labellines);
fwrite($setup, "nClust <- $nclusters\n");
fwrite($setup, "setwd(\"".$config['root_dir']."R\")\n");
fclose($setup);

// Remove the previous version of the image.  Necessary?
exec("rm ".$config['root_dir']."downloads/temp/linecluster.png");
// For debugging, use this to show the R output:
// echo "<pre>";
// system("cat R/temp/setupcluster.R R/VisualCluster.R | R --vanilla");
exec("cat R/temp/setupcluster.R R/VisualCluster.R | R --vanilla");

// IE will show the old cached image unless we make the name look different.
$date = date("Uu");
print "<img src=\"".$config['base_url']."downloads/temp/linecluster.png?d=$date\">";

?>

<P>Select which cluster of lines you want to use.
<form action="cluster_lines.php" method="GET">
  <select name="mycluster">
  <option value="1">1, black</option>
  <option value="2">2, red</option>
  <option value="3">3, green3</option>
  <option value="4">4, blue</option>
  <option value="5">5, cyan</option>
  <option value="6">6, magenta</option>
  <option value="7">7, yellow</option>
  <option value="8">8, gray</option>
  </select>
  <p><input type=submit value="Select">
  </form>

  </div></div></div>

  <?php 
  $footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>