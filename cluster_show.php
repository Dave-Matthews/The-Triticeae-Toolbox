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
//echo "<h3>Clusters: $nclusters</h3>";

// Timestamp for names of temporary files.
$time = $_GET['time'];

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
 else $labellines = "lineNames <-c('')\n";

/**** Changing the location of temporary files:
// Store the input parameters in file setupcluster.R.
$setup = fopen("downloads/temp/setupcluster.R".$time, "w");
$png = "png(\"".$config['root_dir']."downloads/temp/linecluster.png\", width=600, height=500)\n";
fwrite($setup, $png);
fwrite($setup, $labellines);
fwrite($setup, "nClust <- $nclusters\n");
fwrite($setup, "setwd(\"".$config['root_dir']."downloads\")\n");
fwrite($setup, "mrkDataFile <-c('temp/mrkData.csv".$time."')\n");
fwrite($setup, "clustInfoFile<-c('temp/clustInfo.txt".$time."')\n");
fwrite($setup, "clustertableFile <-c('temp/clustertable.txt".$time."')\n");
fclose($setup);

// Remove previous image.  Otherwise if R fails the user gets previous image.
unlink($config['root_dir']."downloads/temp/linecluster.png");

//   For debugging, use this to show the R output:
//   (Regardless, R error messages will be in the Apache error.log.)
//echo "<pre>"; system("cat downloads/temp/setupcluster.R$time R/VisualCluster.R | R --vanilla");
exec("cat downloads/temp/setupcluster.R$time R/VisualCluster.R | R --vanilla");

// IE will show the old cached image unless we make the name look different.
$date = date("U");
print "<img src=\"".$config['base_url']."downloads/temp/linecluster.png?d=$date\">";

$clustInfo = file($config['root_dir']."downloads/temp/clustInfo.txt".$time);
unlink($config['root_dir']."downloads/temp/clustInfo.txt".$time);
$clustInfo = preg_replace("/\n/", "", $clustInfo);
sort($clustInfo);
****/

// Store the input parameters in file setupcluster.R.
if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
$setup = fopen("/tmp/tht/setupcluster.R".$time, "w");
$png = "png(\"/tmp/tht/linecluster.png\", width=600, height=500)\n";
fwrite($setup, $png);
fwrite($setup, $labellines);
fwrite($setup, "nClust <- $nclusters\n");
/* fwrite($setup, "setwd(\"".$config['root_dir']."downloads\")\n"); */
/* fwrite($setup, "mrkDataFile <-c('temp/mrkData.csv".$time."')\n"); */
/* fwrite($setup, "clustInfoFile<-c('temp/clustInfo.txt".$time."')\n"); */
/* fwrite($setup, "clustertableFile <-c('temp/clustertable.txt".$time."')\n"); */
fwrite($setup, "setwd(\"/tmp/tht/\")\n");
fwrite($setup, "mrkDataFile <-c('mrkData.csv".$time."')\n");
fwrite($setup, "clustInfoFile<-c('clustInfo.txt".$time."')\n");
fwrite($setup, "clustertableFile <-c('clustertable.txt".$time."')\n");
fclose($setup);

// Remove previous image.  Otherwise if R fails the user gets previous image.
unlink("/tmp/tht/linecluster.png");

//   For debugging, use this to show the R output:
//   (Regardless, R error messages will be in the Apache error.log.)
//echo "<pre>"; system("cat /tmp/tht/setupcluster.R$time R/VisualCluster.R | R --vanilla");
exec("cat /tmp/tht/setupcluster.R$time R/VisualCluster.R | R --vanilla");

// IE will show the old cached image unless we make the name look different.
$date = date("U");
/* print "<img src=\"".$config['base_url']."downloads/temp/linecluster.png?d=$date\">"; */
print "<img src=\"/tmp/tht/linecluster.png?d=$date\">";

$clustInfo = file("/tmp/tht/clustInfo.txt".$time);
unlink("/tmp/tht/clustInfo.txt".$time);
$clustInfo = preg_replace("/\n/", "", $clustInfo);
sort($clustInfo);

for ($i=0; $i<count($clustInfo); $i++) {
  $clustInfo[$i] = explode(", ", $clustInfo[$i]);
  $clustsize[$clustInfo[$i][0]] = $clustInfo[$i][2];
  $clustlist[$clustInfo[$i][0]] .= $clustInfo[$i][1].", ";
 }

$color = array("black","red","limegreen","blue","cyan","magenta","#dddd00","gray");
print "<table width=300 style='background-image: none; font-weight: bold'>";
print "<thead><tr><th>Cluster</th><th>Labeled lines</th><th>Lines</th></tr></thead>";
for ($i=1; $i<count($clustsize)+1; $i++) {
  $total = $total + $clustsize[$i];
  print "<tr style='color:".$color[$i-1]."';'>";
  print "<td>$i</td>";
  print "<td>".trim($clustlist[$i],', ')."</td>";
  print "<td>$clustsize[$i]</td>";
  print "</tr>";
 }
print "<tr><td>Total:</td><td></td><td>$total</td></tr>";
print "</table>";


print "<P>Select the clusters you want to use.";
print "<form action='cluster_lines.php' method='GET'>";
print "<select name='mycluster[]' multiple size=$nclusters>";
for ($i=0; $i<$nclusters; $i++) {
  $j=$i+1;
  print "<option value=$j>$j</option>";
 }
print "</select>";
print "<input type = 'hidden' name = 'time' value = $time>";
print "<p><input type=submit value='Select'>";
print "</form>";

print "<p><hr><p>";
print "<h3>Full cluster contents</h3>";
/* $clustertable = file("downloads/temp/clustertable.txt".$time); */
$clustertable = file("/tmp/tht/clustertable.txt".$time);
$clustertable = preg_replace("/\n/", "", $clustertable);
// Remove the first row, "x".
array_shift($clustertable);
for ($i=0; $i<count($clustertable); $i++) {
  $row = explode("\t", $clustertable[$i]);
  $contents[$row[1]] .= $row[0].", ";
}
print "<table width=500 style='background-image: none; font-weight: bold'>";
print "<thead><tr><th>Cluster</th><th>Lines</th></tr></thead>";
for ($i=1; $i<count($contents)+1; $i++) {
  print "<tr style='color:".$color[$i-1]."';'>";
  print "<td>$i</td>";
  print "<td>".trim($contents[$i],', ')."</td>";
  print "</tr>";
 }
print "</table>";

print "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>