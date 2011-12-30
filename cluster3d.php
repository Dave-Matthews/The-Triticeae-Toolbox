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

// Store the input parameters in file setupclust3d.txt.
if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
$setup = fopen("/tmp/tht/setupclust3d.txt".$time, "w");
fwrite($setup, $labellines);
fwrite($setup, "nClust <- $nclusters\n");
fwrite($setup, "setwd(\"/tmp/tht/\")\n");
fwrite($setup, "mrkDataFile <-c('mrkData.csv".$time."')\n");
fwrite($setup, "clustInfoFile<-c('clustInfo.txt".$time."')\n");
fwrite($setup, "clustertableFile <-c('clustertable.txt".$time."')\n");
fwrite($setup, "clust3dCoords<-c('clust3dCoords.csv".$time."')\n");
fclose($setup);

//   For debugging, use this to show the R output:
//   (Regardless, R error messages will be in the Apache error.log.)
//echo "<pre>"; system("cat /tmp/tht/setupclust3d.txt$time R/Clust3D.R | R --vanilla");
exec("cat /tmp/tht/setupclust3d.txt$time R/Clust3D.R | R --vanilla");

/*
 * Show the graphic.
 */
?>
    <script type="text/javascript" src="X3DOM/x3dom-full.js"></script>
    <link rel="stylesheet" type="text/css" href="X3DOM/x3dom.css" />
    <!-- Box for line names to appear in -->
    <style type="text/css">
      #myoutput {
      position: absolute;                                                          
      float: left;                                                                 
      z-index: 1;                                                                  
      top: 5px;                                                                    
      left: 5px;                                                                   
      width: 10em;                                                                 
      height: 2em;                                                                 
      border: none;
      background-color: white;                                                   
      text-align: left;
      font-size: 18px;
      }
    </style>

    <x3d xmlns="http://www.x3dom.org/x3dom" showStat="false" showLog="true" showProgress="true" x="0px" y="0px" width="500px" height="500px">

      <div id="myoutput"></div>
      <scene>
	<viewpoint position='0 0 10' orientation="0 40 40 0" fieldOfView="0.785398"></viewpoint>
<?php
// Define the colors for the plotting symbols.
$color = array('','black','red','limegreen','blue','cyan','magenta','orange','#ffff00');
for ($i=1; $i <= count($color); $i++) {
  echo "<appearance DEF='_$i'>";
  echo "<material diffuseColor='$color[$i]' specularColor='.2 .2 .2' transparency='0.3'></material>";
  echo "</appearance>";
}

$coords = file("/tmp/tht/clust3dCoords.csv".$time);
$coords = preg_replace("/\n/", "", $coords);
// Get the ranges of the PCA values.
for ($i=0; $i<count($coords); $i++) {
  $coords[$i] = explode("\t", $coords[$i]);
  $xvals[] = $coords[$i][2];
  $yvals[] = $coords[$i][3];
  $zvals[] = $coords[$i][4];
}
$xrange = max($xvals) - min($xvals);
$yrange = max($yvals) - min($yvals);
$zrange = max($zvals) - min($zvals);

for ($i=0; $i<count($coords); $i++) {
  $name = str_replace("\"", "", $coords[$i][0]);
  $clusternumber = $coords[$i][1];
  $x = 5 * $coords[$i][2] / $xrange;
  $y = 5 * $coords[$i][3] / $yrange;
  $z = 5 * $coords[$i][4] / $zrange;
  echo "
     <transform translation='$x $y $z'>
       <shape DEF='$name'>
	 <appearance USE='_$clusternumber'>
	 </appearance>
<!--	 <text string='$name'><fontstyle family='Helvetica' size='12'></fontstyle></text> -->
	 <sphere radius='0.1' 	
  	         onmouseover=\"document.getElementById('myoutput').innerHTML = '$name'\" 
	         onclick=\"alert('Line name: $name')\">
	 </sphere>
       </shape>
     </transform>
     ";
  }
?>
      </scene>
    </x3d>
</div>

  <div style="
	      position: absolute;
	      left: 765px;
	      top: 520px;
	      width: 180px;
	      ">
    <b>r</b>: Reset.<br>
    <b>Doubleclick</b>: Re-center rotation.<br>
    <a href="http://x3dom.org/docs/dev/navigation.html" target="_blank">Other commands...</a>
    <br><br><p style="font-size: 8pt">
      <b>Browsers:</b><br>
      <b>Firefox</b> and <b>Chrome</b> work well.<br>
      <b>Internet Explorer</b> requires <a href="http://get.adobe.com/flashplayer/">Flash Player 11</a> (new version).<br>
      <b>Mac Safari</b>: Set "Enable WebGL" in the Develop menu.<br>
    <p style="font-size: 8pt">
	Graphics from <a href="http://www.x3dom.org">www.x3dom.org</a>
  </div>

<div style="clear:both">
<br><br>

<style type="text/css">
  table th {text-align: center;}
  table td {text-align: center;}
</style>

<?php
/*
 * Show table of cluster members.
 */

$clustInfo = file("/tmp/tht/clustInfo.txt".$time);
//unlink("/tmp/tht/clustInfo.txt".$time);
$clustInfo = preg_replace("/\n/", "", $clustInfo);
sort($clustInfo);

for ($i=0; $i<count($clustInfo); $i++) {
  $clustInfo[$i] = explode(", ", $clustInfo[$i]);
  $clustsize[$clustInfo[$i][0]] = $clustInfo[$i][2];
  $clustlist[$clustInfo[$i][0]] .= $clustInfo[$i][1].", ";
 }

// Modify yellow a bit to show up better in text.
$color = array('black','red','green','blue','cyan','magenta','orange','#cccc00');
print "<form action='cluster_lines3d.php' method='POST'>";
print "<table width=200 style='background-image: none; font-weight: bold;'>";
print "<thead><tr><th>&nbsp;</th><th>Cluster</th><th>Lines</th></tr></thead>";
for ($i=1; $i<count($clustsize)+1; $i++) {
  $total = $total + $clustsize[$i];
  print "<tr style='color:".$color[$i-1]."';'>";
  print "<td><input type='checkbox' name='mycluster[]' value=$i></td>";
  print "<td>$i</td>";
  print "<td>$clustsize[$i]</td>";
  print "</tr>";
 }
print "<tr><td>Total:</td><td>$total</td></tr>";
print "</table>";
print "<p>Select the clusters you want to use. ";
print "<input type = 'hidden' name = 'time' value = $time>";
print "<input type=submit value='Re-cluster'>";
print "</form>";

print "<p><hr><p>";
print "<h3>Cluster contents</h3>";
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
  print "<td style='text-align: left'>".trim($contents[$i],', ')."</td>";
  print "</tr>";
 }
print "</table>";

// Clean up old files, older than 1 day.
system("find /tmp/tht -mtime +1 -name 'clustertable.txt*' -delete");
system("find /tmp/tht -mtime +1 -name 'mrkData.csv*' -delete");

print "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
