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
    <h1>Delete a phenotype experiment</h1>
    <div class="section">

  <?php
  if (!isset($_GET) OR empty($_GET)) {
    //Initial entry to the script, nothing done yet. Pick a trial_code.
    ?>

  <form action = "curator_data/delete_experiment.php" method="get">
    Experiment to delete
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
    echo "</select></td>";
  }
elseif ($_GET['doit'] == "yes") {
  //A trial has been selected and confirmed.
  delete_experiment($_GET['exptuid']); // function defined below
}
 else {
   //A trial has been selected. Confirm.
   if (!empty($_GET['trialcode'])) {
     // submitted from the textbox form
     $tc = $_GET['trialcode'];
     $sql = "select experiment_uid from experiments where trial_code = '$tc'";
     $r = mysql_query($sql) or die("<pre>" . mysql_error() . "<br>$sql");
     $r2 = mysql_fetch_row($r);
     $exptuid = $r2[0];
     if (empty($exptuid))
       exit("Experiment <b>'$tc'</b> not found.<p><input type='Button' value='Back' onClick='history.go(-1)'>");
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
   echo "<p>Experiment to delete: <b>$trialcode</b><br>";
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
   print "<p><input type='Button' value='Yikes! No' onClick='history.go(-1)' style='font: bold 13px Arial'>";
   ?>
   <input type='Button' value='Do it.' onClick="window.open('<?php echo $config['base_url']; ?>curator_data/delete_experiment.php?doit=yes&exptuid=<?php echo $exptuid ?>', '_top')">
   <p>To undelete you must reload the original files, including the experiment annotation file.

      <?php
      }

echo "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
// end

function delete_experiment($uid) {
  //$sql = "delete from phenotype_experiment_info where experiment_uid = $uid";
  $sql = "delete from phenotype_experiment_info where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  $sql = "delete from datasets_experiments where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  $sql = "delete from  phenotype_mean_data where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  $sql = "delete from phenotype_data where tht_base_uid in
          (select tht_base_uid from tht_base where experiment_uid = $uid)";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  $sql = "delete from tht_base where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  $sql = "delete from experiments where experiment_uid = $uid";
  $r = mysql_query($sql) or die(mysql_error() . "<br>Query was: $sql");
  echo "Experiment deleted.";
  return;
}

?>
