<?php
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
connect();
loginTest();
ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
// If authenticated, proceed.
include($config['root_dir'].'theme/admin_header.php');
?>

<div id="primaryContentContainer">
  <div id="primaryContent">

<?php
  if (!isset($_GET) OR empty($_GET)) {
    //Initial entry to the script, nothing done yet. Pick a trial or experiment.
?>
    
  <!-- form to select a Trial: -->
  <h1>Delete a Phenotype Trial</h1>
  <div class="section">
    <form action = "curator_data/delete_experiment.php" method="get">
      Trial to delete
      <input type=text name=trialcode>
      <input type=submit value="Go">
    </form>
    <p>
      <select onchange="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?exptuid='+this.options[this.selectedIndex].value,'_top')">
	<option value=''>or choose from below...</option>
<?php
  $sql = "select trial_code, experiment_uid as uid 
	  from experiments where experiment_type_uid = 1
	  order by trial_code";
  $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
  while($row = mysql_fetch_assoc($r)) {
    $tc = $row['trial_code'];
    $uid = $row['uid'];
    echo "<option value='$uid'>$tc</option>\n";
  }
?>
  </select>
  </div>

  <!-- form to select a phenotype Experiment: -->
  <h1>Delete a Phenotype Experiment</h1>
  <div class='section'>
    <form action = "curator_data/delete_experiment.php" method="get">
      Experiment to delete
      <input type=text name=exptsetname>
      <input type=submit value="Go">
    </form>
    <p>
      <select onchange="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?exptsetuid='+this.options[this.selectedIndex].value,'_top')">
	<option value=''>or choose from below...</option>
<?php
  $sql = "select experiment_set_name as esname, experiment_set_uid as uid 
          from experiment_set
          order by experiment_set_name";
  $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
  while($row = mysql_fetch_assoc($r)) {
    $name = $row['esname'];
    $uid = $row['uid'];
    echo "<option value='$uid'>$name</option>\n";
  }
?>
  </select>
  </div>

  <!-- form to select a genotype Experiment: -->
  <h1>Delete a Genotype Experiment</h1>
  <div class='section'>
    <form action = "curator_data/delete_experiment.php" method="get">
      Experiment to delete
      <input type=text name=genoexptname>
      <input type=submit value="Go">
    </form>
    <p>
      <select onchange="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?genoexptuid='+this.options[this.selectedIndex].value,'_top')">
	<option value=''>or choose from below...</option>
<?php
  $sql = "select trial_code, experiment_uid as uid 
          from experiments where experiment_type_uid = 2 
          order by trial_code";
  $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
  while($row = mysql_fetch_assoc($r)) {
    $tc = $row['trial_code'];
    $uid = $row['uid'];
    echo "<option value='$uid'>$tc</option>\n";
  }
?>
  </select>
  </div>
<?php

  } // end of "if (!isset($_GET) OR empty($_GET))"

// Something has been selected. Execute it:
elseif ($_GET['doit'] == "trial") {
  //A trial has been selected and confirmed.
  delete_trial($_GET['exptuid']); // function defined below
  print "<p><input type='Button' value='Return' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\" style='font: bold 13px Arial'>";
}
elseif ($_GET['doit'] == "genoexpt") {
  //A genotyping experiment has been selected and confirmed.
  delete_genoexpt($_GET['exptuid']); // function defined below
  print "<p><input type='Button' value='Return' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\" style='font: bold 13px Arial'>";
}
elseif ($_GET['trialcode'] OR $_GET['exptuid']) {
   // A Trial has been selected. Display its contents and confirm.
   if (!empty($_GET['trialcode'])) {
     // submitted from the textbox form
     $tc = $_GET['trialcode'];
     $sql = "select experiment_uid from experiments where trial_code = '$tc'";
     $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
     $r2 = mysql_fetch_row($r);
     $exptuid = $r2[0];
     if (empty($exptuid))
       exit("Trial <b>'$tc'</b> not found.<p><input type='Button' value='Back' onClick='history.go(-1)'>");
   }
   elseif (!empty($_GET['exptuid'])) 
     // submitted from the pick'n'go dropdown menu
     $exptuid = $_GET['exptuid'];

   // Show the curator exactly what she's doing:
   $sql = "select trial_code, experiment_desc_name, input_data_file_name 
            from experiments where experiment_uid = $exptuid";
   $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
   $r2 = mysql_fetch_row($r);
   $trialcode = $r2[0];
   $exptdescnm = $r2[1];
   $filenm = $r2[2];
   echo "<p>Trial to delete: <b>$trialcode</b><br>";
   echo "Description: $exptdescnm<br>";
   echo "Loaded from file: $filenm<br>";
   //Also show number of traits, lines, data points (phenotype_data rows).
   $sql = "select * from phenotype_data where tht_base_uid in
           (select tht_base_uid from tht_base where experiment_uid = $exptuid)";
   $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
   $vals = mysql_num_rows($r);
   $sql = "select count(line_record_uid) from tht_base where experiment_uid = $exptuid";
   $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
   $r2 = mysql_fetch_row($r);
   $linecount = $r2[0];
   $sql = "select distinct phenotype_uid from phenotype_data where tht_base_uid in
           (select tht_base_uid from tht_base where experiment_uid = $exptuid)";
   $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
   $traitcount = mysql_num_rows($r);
   echo "<p><b>$vals</b> data points (phenotype values) for 
         <b>$traitcount</b> traits from <b>$linecount</b> lines will be deleted.<br>";
   print "<p><input type='Button' value='Yikes! No' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\" style='font: bold 13px Arial'>";
   ?>
   <input type='Button' value='Do it.' onClick="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?doit=trial&exptuid=<?php echo $exptuid ?>', '_top')">
   <p>To undelete you must reload the original files, including the experiment annotation file.

      <?php
      }

elseif ($_GET['exptsetname'] OR $_GET['exptsetuid']) {
  // An Experiment was selected.
  if ($_GET['exptsetname']) {
    $esname = $_GET['exptsetname'];
    $esuid = mysql_grab("select experiment_set_uid from experiment_set
                         where experiment_set_name = '$esname'");
    if (empty($esuid)) 
      exit("Experiment <b>'$esname'</b> not found.<p><input type='Button' value='Back' onClick='history.go(-1)'>");
  }
  else {
    $esuid = $_GET['exptsetuid'];
    $esname = mysql_grab("select experiment_set_name from experiment_set
                         where experiment_set_uid = '$esuid'");
  }
  $sql = "select trial_code from experiments where experiment_set_uid = $esuid";
  $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
  if (mysql_num_rows($r) == 0) {
    // No trials so okay to delete.
    $sql = "delete from experiment_set where experiment_set_uid = $esuid";
    mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
    echo "Experiment <b>$esname</b> has been deleted.<p>";
    echo "<input type='Button' value='Return' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\"' style='font: bold 13px Arial'>";
  }
  else {
    echo "Can't delete Experiment <b>$esname</b>. Please delete the Trials it contains first.<p>";
    echo "<b>Trials</b>:<br>";
    while ($tr = mysql_fetch_row($r))
      echo "$tr[0]<br>";
    echo "<br><input type='Button' value='Return' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\" style='font: bold 13px Arial'>";    
  }
}
elseif ($_GET['genoexptname'] OR $_GET['genoexptuid']) {
   // A genotyping experiment has been selected. Display its contents and confirm.
  if(!empty($_GET['genoexptname'])) {
    // submitted from the textbox form
     $tc = $_GET['genoexptname'];
     $sql = "select experiment_uid from experiments where trial_code = '$tc'";
     $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
     $r2 = mysql_fetch_row($r);
     $exptuid = $r2[0];
     if (empty($exptuid))
       exit("Genotyping Experiment <b>'$tc'</b> not found.<p><input type='Button' value='Back' onClick='history.go(-1)'>");
  }
  elseif (!empty($_GET['genoexptuid'])) 
    // submitted from the pick'n'go dropdown menu
    $exptuid = $_GET['genoexptuid'];

  // Show the curator what he's doing:
  $sql = "select trial_code, experiment_desc_name, experiment_short_name, platform_name, marker_type_name
	  from experiments, genotype_experiment_info
	  left join platform on genotype_experiment_info.platform_uid = platform.platform_uid
	  left join marker_types on genotype_experiment_info.marker_type_uid = marker_types.marker_type_uid
	  where genotype_experiment_info.experiment_uid = experiments.experiment_uid
	  and experiments.experiment_uid = $exptuid";
  $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
  $r2 = mysql_fetch_row($r);
  $trialcode = $r2[0];
  $exptdescnm = $r2[1];
  $exptshortnm = $r2[2];
  $platform = $r2[3];
  $markertype = $r2[4];
  echo "<p>Trial to delete: <b>$trialcode</b><br>";
  if (!empty($exptdescnm))
    echo "Description: $exptdescnm<br>";
  if (!empty($exptshortnm))
    echo "Short name: $exptshortnm<br>";
  if (!empty($platform))
    echo "Platform: $platform<br>";
  if (!empty($markertype))
    echo "Marker type: $markertype<br>";
   print "<p><input type='Button' value='Yikes! No' onClick=\"location.href='".$config['base_url']."curator_data/delete_experiment.php'\" style='font: bold 13px Arial'>";
   ?>
   <input type='Button' value='Do it.' onClick="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?doit=genoexpt&exptuid=<?php echo $exptuid ?>', '_top')">
   <p>To undelete you must reload the original files, including the experiment annotation file.

      <?php
}
else echo "Error, invalid _GET[] value. See script line ".__LINE__;

echo "</div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
// end

/* Local functions */

function delete_trial($uid) {
  $sql = "delete from phenotype_experiment_info where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from datasets_experiments where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from  phenotype_mean_data where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from phenotype_data where tht_base_uid in
          (select tht_base_uid from tht_base where experiment_uid = $uid)";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from tht_base where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from csr_measurement where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from experiments where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  echo "Trial deleted.";
  return;
}

function delete_genoexpt($uid) {
  // Order necessary: first delete alleles, then genotyping_data, then tht_base, then experiment.
  //$sql = "delete from alleles where genotyping_data_uid in
  //	   (select genotyping_data_uid from genotyping_data where tht_base_uid in
  //	     (select tht_base_uid from tht_base where experiment_uid = $uid) )";
  $sql = "select tht_base_uid from tht_base where experiment_uid = $uid";
  $res = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  while ($row = mysql_fetch_array($res)) {
      $tht_base_uid = $row['tht_base_uid'];
      echo "deleting alleles tht_base_uid = $tht_base_uid<br>\n";
      $sql = "select genotyping_data_uid from genotyping_data where tht_base_uid = $tht_base_uid";
      $res2 = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
      while ($row2 = mysql_fetch_array($res2)) {
          $genotyping_data_uid = $row2['genotyping_data_uid'];
          $sql = "delete from alleles where genotyping_data_uid = $genotyping_data_uid";
          $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
      }
      flush();
      ob_flush();
  }

  $sql = "select tht_base_uid from tht_base where experiment_uid = $uid";
  $res = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  while ($row = mysql_fetch_array($res)) {
      $tht_base_uid = $row['tht_base_uid'];
      echo "deleting genotype_data tht_base_uid = $tht_base_uid<br>\n";
      $sql = "delete from genotyping_data where tht_base_uid = $tht_base_uid"; 
      $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
      flush();
      ob_flush();
  }

  $sql = "delete from tht_base where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from genotype_experiment_info where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  $sql = "delete from experiments where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<p>Query was: $sql");
  echo "Genotyping experiment deleted.";
  return;
}

?>
