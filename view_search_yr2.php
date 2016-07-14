<?php 
session_start(); 
require 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
require $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();

echo "<h2>Available Datasets</h2><p>";
$year=$_GET['year'];

$sql="SELECT experiments.experiment_uid from csr_measurement, experiments
      WHERE experiments.experiment_uid = csr_measurement.experiment_uid
      AND experiment_year = ?";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $year);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $uid);
    while (mysqli_stmt_fetch($stmt)) {
        $csr_list[$uid] = 1;
    }
    mysqli_stmt_close($stmt);
}

$sql="SELECT e.trial_code, e.experiment_year, e.traits, et.experiment_type_name, e.experiment_uid
	FROM experiments as e, experiment_types as et
	WHERE e.experiment_year = ?
	AND et.experiment_type_uid = e.experiment_type_uid";
if (!authenticate(array(USER_TYPE_PARTICIPANT,
	USER_TYPE_CURATOR,
	USER_TYPE_ADMINISTRATOR))) {
    $sql .= " AND data_public_flag > 0";
}
$sql .= " order by e.experiment_year,e.trial_code ASC";
if ($stmt = mysqli_prepare($mysqli, $sql)) {
    mysqli_stmt_bind_param($stmt, "i", $year);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $trial_code, $year, $traits, $experiment_type, $uid) or die("Error in bind\n");
    mysqli_stmt_store_result($stmt);
    $num_rows = mysqli_stmt_num_rows($stmt);
    if ($num_rows == 0) {
      ?>
      <div class="section">
      <p> There are no publicly available datasets for this year in T3 at this time. 
      Registered users may see additional datasets after signing in.
      </div>
      <?php
    } else {
      ?>
      <table cellpadding="0" cellspacing="0">
      <tr>
      <th>Year</th>
      <th>Trial Name</th>
      <th>Traits</th>
      </tr>
      <?php

      while (mysqli_stmt_fetch($stmt)) {
	$filename="data/".$trial_code.".xls";
        if (isset($csr_list[$uid])) {
          $traits = "CSR Data<br>$traits";
        }
	if ($experiment_type=='phenotype') {
            echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
	} elseif ($experiment_type=='genotype') {
            echo( "<tr> <td>$year</td> <td><a href='display_genotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
	}
      }
      mysqli_stmt_close($stmt);
    }
}
echo "</table>";
$footer_div = 1;
require $config['root_dir'].'theme/footer.php'; ?>
</body>
</html>
