<?php
/**
 * Show 2D cluster
 *
 * PHP version 5.3
 *
 * dem 19apr13, added imagemap with line names.
 */
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
?>
<!-- imagemap code -->
<script src='js/interactiveMaps.js'></script>
<script src='js/imageMaps.js'></script>
<link rel=stylesheet type='text/css' href='R/iPlot.css'>
<?php
echo "<h1>Cluster Lines by Genotype</h1>";
echo "<div class=section>";

$nclusters = intval($_GET['clusters']);

// Timestamp for names of temporary files.
$time = intval($_GET['time']);

// Line names to label in the legend
$linenames = $_GET['labels'];
if ($linenames != "") {
    if (strpos($linenames, ',') > 0) {
        $linenames = str_replace(", ", ",", $linenames);
        $lineList = explode(',', $linenames);
    } elseif (preg_match("/\t/", $linenames)) {
        $lineList = explode("\t", $linenames);
    } else {
        $lineList = explode('\r\n', $linenames);
    }
    $labellines = "lineNames <- c(";
    for ($i=0; $i<count($lineList); $i++) {
        $labellines .= "\"$lineList[$i]\", ";
    }
    $labellines = trim($labellines, ", ");
    $labellines .= ")\n";
} else {
    $labellines = "lineNames <-c('')\n";
}

$count = count($_SESSION['filtered_markers']);
if ($count == 0) {
    echo "<font color=red>Error: No markers selected<br>";
    echo "Reselect markers with less filtering</font>";
    echo "<p><input type='Button' value='Back' onClick='history.go(-1)'>";
} else {
    // Store the input parameters in file setupcluster.R.
    if (! file_exists('/tmp/tht')) {
        mkdir('/tmp/tht');
    }
    $setup = fopen("/tmp/tht/setupcluster.R".$time, "w");
    fwrite($setup, $labellines);
    fwrite($setup, "nClust <- $nclusters\n");
    fwrite($setup, "setwd(\"/tmp/tht/\")\n");
    fwrite($setup, "mrkDataFile <-c('mrkData.csv".$time."')\n");
    fwrite($setup, "clustInfoFile<-c('clustInfo.txt".$time."')\n");
    fwrite($setup, "clustertableFile <-c('clustertable.txt".$time."')\n");
    fclose($setup);

    // Remove previous image.  Otherwise if R fails the user gets previous image.
    unlink("/tmp/tht/linecluster.png");

    //   For debugging, use this to show the R output:
    //   (Regardless, R error messages will be in the Apache error.log.)
    //echo "<pre>"; system("cat /tmp/tht/setupcluster.R$time R/iPlot.R R/VisualCluster.R | R --vanilla 2>&1");
    exec("cat /tmp/tht/setupcluster.R$time ../R/iPlot.R ../R/VisualCluster.R | R --vanilla");

    // Read in the HTML file with the <img src> png and the <map> coordinates.
    include '/tmp/tht/linecluster.html';

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
print "<thead><tr><th>Cluster</th><th>Sample member</th><th>Lines</th></tr></thead>";
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
}

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
if (file_exists("/tmp/tht/clustertable.txt".$time)) {
    $clustertable = file("/tmp/tht/clustertable.txt".$time);
    $clustertable = preg_replace("/\n/", "", $clustertable);
    // Remove the first row, "x".
    array_shift($clustertable);
    for ($i=0; $i<count($clustertable); $i++) {
      $row = explode("\t", $clustertable[$i]);
      $contents[$row[1]] .= $row[0].", ";
    }
} else {
    echo "Error: cluster file not found\n";
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

// Clean up old files, older than 1 day.
system("find /tmp/tht -mtime +1 -name 'clustertable.txt*' -delete");
system("find /tmp/tht -mtime +1 -name 'mrkData.csv*' -delete");

print "</div>";
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
