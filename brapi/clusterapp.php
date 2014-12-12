<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
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
<form method=post>
<table>
<tr><th>Germplasm names<th>Server
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
  </textarea>
<td><input type=text size=50 value="http://malt.pw.usda.gov/t3/wheatplus/brapi/0.1" name="server"><p>
<i>Example:</i> 
http://cassava-test.sgn.cornell.edu/brapi/0.1<br> 
<i>for germplasm:</i><br>
95NA-00063<br>
TMEB1981<br>
BA95070<br>
BAHKYEHEMAA<br>
BANGWEULU<br>
CB-10(80411)<br>
CH92108<br>
DOKUNBAHKYE<br>
ESAMBAHKYE<br>
TMEB1776<br>
IITA-TMS-IBA000017<br>
IITA-TMS-IBA000028<br>
IITA-TMS-IBA000049<br>
IITA-TMS-IBA000061<br>
IITA-TMS-IBA000070<br>
IITA-TMS-IBA000093<br>
IITA-TMS-IBA000101<br>
IITA-TMS-IBA000107<br>
IITA-TMS-IBA000203<br>
</table>
<input type="submit" value="Submit">
</form>

    <?php
    }
    if ($_REQUEST['lines']) {
      // "Submit" was clicked. Get the line IDs.
      $names = explode('\r\n', $_REQUEST['lines']);
      echo "Germplasm names : ". implode(', ', $names)."<br>";
      echo 'Fetching germplasm IDs...<br>';
      $server = $_REQUEST['server'];
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
	$gtype = fetch($server, $cmd, $lid);
	$gtypes[$nm] = $gtype['genotypes'][0]['data'];
	/* if ($z==0) */
	/*   print_h($gtypes); */
	/* $z++; */
	$gtstring = substr(implode(', ', $gtypes[$nm]), 0, 100);
	echo "<b>$gtstring...</b><br>";
	$allmarkers = $allmarkers + array_keys($gtypes[$nm]);
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
  <script type="text/javascript" src="<?php echo $config['base_url'] ?>brapi/clusterapp.js"></script>
  <!-- <input type=button value="Cluster Analysis" onclick="javascript:get_alleles(<?php echo $time ?>)"> -->
  <input type=button value="Cluster Analysis" onclick="javascript:run_rscript(<?php echo $time ?>)">
  <div id='ajaxresult'></div>
  <script type="text/javascript">
 	var resp=document.getElementById('ajaxresult');
	resp.innerHTML = "<img id='spinner' src='<?php echo $config[base_url] ?>images/progress.gif' alt='Working...' style='display:none;'>";
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

$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
// end of script


?>
