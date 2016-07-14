<?php 

/**
 *  DEM dec2014: Show all Trials from the chosen Experiment (experiments from the chosen experiment_set).
 */

session_start(); 
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();

$exptuid = $_GET['expt'];
$exptname = mysql_grab("select experiment_set_name from experiment_set where experiment_set_uid = $exptuid");
echo "<h2>Trials from Experiment $exptname</h2><p>";

$sql="SELECT experiments.experiment_uid from csr_measurement, experiments
      WHERE experiments.experiment_uid = csr_measurement.experiment_uid
      AND experiment_set_uid='$exptuid'";
$result_trialcode=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row_trialcode=mysqli_fetch_array($result_trialcode)) {
  $uid = $row_trialcode['experiment_uid'];
  $csr_list[$uid] = 1;
}

$sql="SELECT e.trial_code, e.experiment_year, e.traits, et.experiment_type_name, e.experiment_uid
		FROM experiments as e, experiment_types as et
		WHERE e.experiment_set_uid='$exptuid'
		AND et.experiment_type_uid = e.experiment_type_uid";
if (!authenticate(array(USER_TYPE_PARTICIPANT,
			USER_TYPE_CURATOR,
			USER_TYPE_ADMINISTRATOR)))
  $sql .= " AND data_public_flag > 0";
$sql .= " order by e.experiment_year desc,e.trial_code ASC";
$result_trialcode=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$num_rows = mysqli_num_rows($result_trialcode);
if ($num_rows ==0){
?>
<div class="section">
  <p> There are no publicly available trials for this Experiment at this time. 
    Registered users may see additional data after signing in.
</div>
<?php
} 
else {
?>
<table cellpadding="0" cellspacing="0">
  <tr>
    <th>Year</th>
    <th>Trial Name</th>
    <th>Traits</th>
  </tr>
<?php

    while($row_trialcode=mysqli_fetch_array($result_trialcode))
      {
        $uid=$row_trialcode['experiment_uid'];
	$trial_code=$row_trialcode['trial_code'];
	$year=$row_trialcode['experiment_year'];
	$traits=$row_trialcode['traits'];
	$filename="data/".$trial_code.".xls";
	$experiment_type = $row_trialcode['experiment_type_name'];
        if (isset($csr_list[$uid])) {
          $traits = "CSR Data<br>$traits";
        }
	if ($experiment_type=='phenotype') {
	  echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
	} elseif ($experiment_type=='genotype') {
	  echo( "<tr> <td>$year</td> <td><a href='display_genotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
	}
      }
}
echo "</table>";
$footer_div = 1;
require $config['root_dir'].'theme/footer.php'; ?>
</body>
</html>
