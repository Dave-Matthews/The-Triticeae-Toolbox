<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
?>
<style type='text/css'>
  table td,th {padding: 3px; text-align: center;}
</style>

  <h1>Weather Data</h1>
  <div class="section">
<form>
  <p>Enter the Zip Code for the trial.
<input type=text name='zip' value='<?php echo $_GET[zip] ?>' size=5>
</form>

<?php 
$ourvariables = array('PRCP','TMAX', 'TMIN', 'TSUN');
/* "id":"PRCP","name":"Precipitation (tenths of mm)" */
/* "id":"TMAX","name":"Maximum temperature (tenths of degrees C)" */
/* "id":"TMIN","name":"Minimum temperature (tenths of degrees C)" */
/* "id":"TSUN","name":"Daily total sunshine (minutes)" */
$srvr = "http://www.ncdc.noaa.gov/cdo-web/api/v2/";
$cmd = "data?datasetid=GHCND"; // GHCND is "Daily Summaries".
$start = '2014-05-01';
$end = '2014-05-10';
$pagesize = 50;
$opts = "&startdate=$start&enddate=$end&limit=$pagesize";
if ($_GET['zip']) {
  $zip = $_GET['zip'];
  $item = "&locationid=ZIP:$zip";
  echo "Request:<br>$srvr$cmd$item$opts<p>";
  // First find how many result records there are.
  $noaa = fetch($srvr, $cmd, $item, $opts);
  $count = $noaa['metadata']['resultset']['count'];
  /* echo "Total records = $count<br>"; */
  /* print_h($noaa); */
  $pages = ceil($count / $pagesize);
  for ($p = 0; $p < $pages; $p++) {
    $offset = $p * $pagesize;
    $opts2 = $opts . "&offset=$offset";
    $noaa = fetch($srvr, $cmd, $item, $opts2);
    foreach ($noaa['results'] as $i) {
      $stn = substr($i['station'], 6);
      $dt = substr($i['date'], 0, 10);
      $dtype = $i['datatype'];
      $val = $i['value'];
      // Scale to nicer units.
      if ($dtype == 'PRCP')
	$val = $val / 100;
      if ($dtype == 'TMAX' OR $dtype == 'TMIN')
	$val = $val / 10;
      if ($dtype == 'TSUN')
	$val = $val / 60;
      $vals[$stn][$dt][$dtype] = $val;
    }
  }
  echo "PRCP = Precipitation (cm)<br>";
  echo "TMAX = Maximum temperature (degrees C)<br>";
  echo "TMIN = Minimum temperature (degrees C)<br>";
  echo "TSUN = Daily total sunshine (hours)<br>";
  // Get the coordinates of the station(s):
  $command = "stations";
  $item = "?locationid=ZIP:$zip";
  $coordsres = fetch($srvr, $command, $item);
  foreach ($coordsres['results'] as $r) {
    $stationname = substr($r['id'], 6);
    $lat[$stationname] = $r['latitude'];
    $lng[$stationname] = $r['longitude'];
  }
  // Print the table(s) of data.
  foreach (array_keys($vals) as $s) {
    echo "<h3>Station $s (Lat $lat[$s], Lng $lng[$s])</h3>";
    echo "<table><tr><th>Date";
    foreach ($ourvariables as $hdr)
      echo "<th>$hdr";
    foreach (array_keys($vals[$s]) as $d) {
    echo "<tr><td>$d";
      foreach ($ourvariables as $ourvar) {
	$cell = $vals[$s][$d][$ourvar];
	echo "<td>$cell";
      }
    }
    echo "</table>";
  }
  /* print_h($noaa); */
}


  echo "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

/* Functions */
// Make a REST request to $server using $command, for optional $item.
function fetch($server, $command, $item="", $options = "") {
  $rqst = $server . $command . $item . $options;
  /* echo "Request:<br>$rqst<br>"; */
  $curl = curl_init($rqst);
  $hdr = array('token:OCGctkWPcAVVJnkNWYEhiOKKyYTnrNEK');
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $hdr);
  $curl_response = curl_exec($curl);
  if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('Error occurred during curl exec. Additional info: ' . var_export($info));
  }
  curl_close($curl);
  return json_decode($curl_response, TRUE);
}

?>
