<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
?>
<style type="text/css">
  pre {font-size: 1.1em}
  #main h2 {color:#de5414; font-size: 1.4em; }
td {vertical-align: top}
</style>

<h1>T3 Breeding API Client: genetic distances</h1>

  <?php if (!$_REQUEST) {
?>
Enter a list of germplasm lines.  Data on their marker alleles will be 
fetched from the desired server, and their genetic distances from each
other will be displayed.
<p>
<table>
<tr><th>Germplasm names
<tr><td><textarea name="lines" cols="18" style="height: 22em;">
Steptoe
Morex
Robust
Barke
Harrington
Excel
Klages
Baronesse
Tradition
AC_Metcalfe
Flagship
Strider
Franklin
Newdale
Foster
Lacey
Stander
Legacy
Merit
Drummond
</textarea></table>
<script type="text/javascript" src="<?php echo $config['base_url'] ?>0.1/clusterapp.js"></script>
<input type="button" value="Submit" onclick="javascript: getCluster1();"/>

    <?php
    }
    if ($_REQUEST['lines']) {
      // "Submit" was clicked. Get the line IDs.
      $names = explode('\r\n', $_REQUEST['lines']);
      echo "Germplasm names : ". implode(', ', $names)."<br>";
      echo 'Fetching germplasm IDs...<br>';
      $server = $_REQUEST['url'];
      $cmd = "/germplasm/find?q=";
      foreach ($names as $nm) {
     	$id = fetch($server, $cmd, $nm);
     	$lineuids[$nm] = $id[0]['germplasmId'];
      }
      echo "<br>Germplasm IDs: <b>";
      echo implode(', ', $lineuids)."</b><br>";
      // Get the alleles.
      echo "<p>Fetching genotype data...<br>";
      $cmd = "/genotype/";
      $allmarkers = array();
      $z=0;
      foreach ($lineuids as $nm => $lid) {
        echo "get $lid<br>\n";
	$gtype = fetch($server, $cmd, $lid);
        if (isset($gtype['genotypes'][0]['data'])) {
	  $gtypes[$nm] = $gtype['genotypes'][0]['data'];
	  /* if ($z==0) */
	  /*   print_h($gtypes); */
	  /* $z++; */
	  $gtstring = substr(implode(', ', $gtypes[$nm]), 0, 100);
	  echo "<b>$gtstring...</b><br>";
	  $allmarkers = $allmarkers + array_keys($gtypes[$nm]);
        } else {
          echo "$lid no genotype data<br>\n";
        }
      }
      $markernamelist = implode(',', $allmarkers);
      echo "<br>Markers: <b>".substr($markernamelist, 0, 100)."...</b><br>";

      // Save to file mrkData.csv for R to analyze.
      // Make the filename unique to deal with concurrency.
      $time = date("U");
      if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
      $outfile = "/tmp/tht/mrkData.csv".$time;
      // Save the list of marker names.
      file_put_contents($outfile, $markernamelist."\n");
      // Translate allele AA to 1 etc. Bad treatment of missing data.
      $in = array('AA','BB','AB','--');
      $out = array('1','0','0.5','0.5');
      foreach ($gtypes as $nm => $markers) {
	$outrow = $nm.",";
	foreach ($allmarkers as $mrk) {
	  $allele = $gtypes[$nm][$mrk];
	  if (!$allele)
	    $allele = "--";
	  $outrow .= str_replace($in, $out, $allele) . ",";
	}
	file_put_contents($outfile, trim($outrow, ",")."\n", FILE_APPEND);
      }

?>
  <!-- <input type=button value="Cluster Analysis" onclick="javascript:get_alleles(<?php echo $time ?>)"> -->
  <input type=button value="Cluster Analysis" onclick="javascript:run_rscript(<?php echo $time ?>)">
  <div id='ajaxresult'></div>
  <script type="text/javascript">
 	var resp=document.getElementById('ajaxresult');
	resp.innerHTML = "<img id='spinner' src='<?php echo $config['base_url'] ?>0.1/progress.gif' alt='Working...' style='display:none;'>";
  </script>

<?php
    }

/* Functions */
// Make a REST request to $server using $command, for optional $item.
function fetch($server, $command, $item="") {
  $rqst = $server . $command . $item;
  echo "$rqst<br>";
  $curl = curl_init($rqst);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $curl_response = curl_exec($curl);
  if ($curl_response === false) {
    $info = curl_getinfo($curl);
    curl_close($curl);
    die('Error occurred during curl exec. Additional info: ' . var_export($info));
  }
  curl_close($curl);
  return json_decode($curl_response, TRUE);
}

// end of script


?>
