<?php
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/admin_header.php';
?>

<div id="primaryContentContainer">
  <img id='spinner' src='./images/progress.gif' alt='Working...' style='display:none;'>
  <div id="primaryContent">
  <h1>Cluster Lines 3D, hclust</h1>
  <script type="text/javascript" src="cluster4.js"></script>
  <div class="section">

<?php
$nclusters = $_GET['clusters'];
$estimate = $_GET['anal'];

// Timestamp for names of temporary files.
$time = $_GET['time'];
$min_maf = $_GET['mmaf'];
$max_missing = $_GET['mmm'];
$max_miss_line = $_GET['mml'];
$querytime = $_SESSION['timmer'];

// Check the results of filtering before running R script
$count = count($_SESSION['filtered_markers']);
if ($count > 0) {
//   For debugging, use this to show the R output:
//   (Regardless, R error messages will be in the Apache error.log.)
//echo "<pre>"; system("cat /tmp/tht/setupclust3d.txt$time R/Clust3D.R | R --vanilla 2>&1");

$estimate = count($_SESSION['filtered_markers']) * count($_SESSION['filtered_lines']);
$estimate = round($estimate/400000,0);
}

/*
 * Show the graphic.
 */
if (!file_exists("/tmp/tht/clust3dCoords.csv".$time)) {
  echo "Estimated analysis time is $estimate minutes.<br>";
  ?>
  <font color=red>Select the "Check Results" button to retrieve results.<br>
    <input type="button" value="Check Results" onclick="javascript: run_status('<?php echo $time; ?>');"/>
    </font>
  <?php
  $h = fopen("/tmp/tht/cluster3d.txt".$time,"r");
  while ($line=fgets($h)) {
    echo "$line<br>\n";
  }
  fclose($h);
  die();
} else {
  $filetime = filemtime("/tmp/tht/clust3dCoords.csv".$time); 
  $elapsed = date("U", $filetime) - $time;
}
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

if (file_exists("/tmp/tht/clust3dCoords.csv".$time)) {
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

  <div style="position: absolute; left: 765px; top: 520px; width: 180px;">
    <b>r</b>: Reset.<br>
    <b>Doubleclick</b>: Re-center rotation.<br>
    <a href="http://x3dom.org/docs-old/navigation.html" target="_blank">Other commands...</a>
    <br><br><p style="font-size: 8pt">
      <b>Browsers:</b><br>
      <b>Firefox</b> and <b>Chrome</b> work well.<br>
      <b>Internet Explorer</b> requires <a href="http://www.google.com/chromeframe/eula.html?quickenable=true">Chrome Frame</a> plug-in.<br>
      <b>Mac Safari</b>: Set "Enable WebGL" in the 
      <span onclick = "alert('To get Safari to show the Develop menu, go to Preferences.../Advanced. \n\'Show Develop menu\' is at the bottom of the dialog box.')" style = "text-decoration: underline">
	Develop menu.</span><br>
    <p style="font-size: 8pt">
	Graphics from <a href="http://www.x3dom.org">x3dom.org</a>
  </div>

<div style="clear:both">
<!-- For testing only: Show elapsed times. -->
Query time = <?php echo $querytime ?> s<br>
Analysis time = <?php echo $elapsed ?> s<br>
<style type="text/css">
  table th {text-align: center;}
  table td {text-align: center;}
</style>

<?php
/* Show table of cluster members.  */
$clustInfo = file("/tmp/tht/clustInfo.txt".$time);
$clustInfo = preg_replace("/\n/", "", $clustInfo);
sort($clustInfo);

for ($i=0; $i<count($clustInfo); $i++) {
  $clustInfo[$i] = explode(", ", $clustInfo[$i]);
  $clustsize[$clustInfo[$i][0]] = $clustInfo[$i][2];
  $clustlist[$clustInfo[$i][0]] .= $clustInfo[$i][1].", ";
 }
$clustertable = file("/tmp/tht/clustertable.txt".$time);
$clustertable = preg_replace("/\n/", "", $clustertable);
// Remove the first row, "x".
array_shift($clustertable);
for ($i=0; $i<count($clustertable); $i++) {
  $row = explode("\t", $clustertable[$i]);
  $contents[$row[1]] .= $row[0].", ";
}
// Modify yellow a bit to show up better in text.
$color = array('black','red','green','blue','cyan','magenta','orange','#cccc00');

print "<form action='cluster_lines4d.php' method='POST' name='myForm'>";
print "<table width=700 style='background-image: none; font-weight: bold;'>";
print "<thead><tr><th>&nbsp;</th><th>Cluster</th><th>Count</th><th>Lines</th></tr></thead>";
for ($i=1; $i<count($clustsize)+1; $i++) {
  $total = $total + $clustsize[$i];
  print "<tr style='color:".$color[$i-1]."';'>";
  print "<td><input type='checkbox' name='mycluster[]' value=$i></td>";
  print "<td>$i</td>";
  print "<td>$clustsize[$i]</td>";
  print "<td style='text-align: left'>".trim($contents[$i],', ')."</td>";
  print "</tr>";
 }
print "<tr><td></td><td>Total:</td><td>$total</td></tr>";
}
?>
</table>
<p>
    How many clusters? <input type=text id='clusters' name="clusters" value=<?php echo $nclusters ?> size="1"><br>
    &nbsp;&nbsp;&nbsp;&nbsp;
    <p>Minimum MAF &ge; <input type="text" name="mmaf" id="mmaf" size="2" value="<?php echo ($min_maf) ?>" />%
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove markers missing &gt; <input type="text" name="mmm" id="mmm" size="2" value="<?php echo ($max_missing) ?>" />% of data
        &nbsp;&nbsp;&nbsp;&nbsp;
        Remove lines missing &gt; <input type="text" name="mml" id="mml" size="2" value="<?php echo ($max_miss_line) ?>" />% of data
<br>
<?php
echo "<table>";
$count = count($_SESSION['filtered_markers']);
echo "<tr><td>markers<td>$count\n";
$count = count($_SESSION['filtered_lines']);
echo "<tr><td>lines<td>$count\n";
echo "</table>";
print "<p>Select the clusters you want to use. ";
print "<input type = 'hidden' name = 'time' value = $time>";
print "<input type=button value='Re-cluster' onclick='javascript:recluster($time)'>";
print "</form>";

// Clean up old files, older than 1 day.
system("find /tmp/tht -mtime +1 -name 'clustertable.txt*' -delete");
system("find /tmp/tht -mtime +1 -name 'mrkData.csv*' -delete");

print "</div></div></div>";
$footer_div=1;
require $config['root_dir'].'theme/footer.php';
?>
