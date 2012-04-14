<?php 
session_start(); 
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();

echo "<h2>Available Datasets</h2><p>";
$year=$_GET['year'];
$sql_year = mysql_real_escape_string($year);
$sql="SELECT e.trial_code, e.experiment_year, e.traits, et.experiment_type_name
					FROM experiments as e, experiment_types as et
					WHERE e.experiment_year='$sql_year'
					AND et.experiment_type_uid = e.experiment_type_uid";
if (!authenticate(array(USER_TYPE_PARTICIPANT,
			USER_TYPE_CURATOR,
			USER_TYPE_ADMINISTRATOR)))
  $sql .= " AND data_public_flag > 0";
$sql .= " order by e.experiment_year,e.trial_code ASC";
$result_trialcode=mysql_query($sql) or die(mysql_error());
$num_rows = mysql_num_rows($result_trialcode);
if ($num_rows ==0){
?>
<div class="section">
  <p> There are no publicly available datasets for this year in T3 at this time. 
    Registered users may see additional datasets after signing in.
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

		while($row_trialcode=mysql_fetch_array($result_trialcode))
		{
			$trial_code=$row_trialcode['trial_code'];
			$year=$row_trialcode['experiment_year'];
			$traits=$row_trialcode['traits'];
			$filename="data/".$trial_code.".xls";
			$experiment_type = $row_trialcode['experiment_type_name'];
			if ($experiment_type=='phenotype') {
			  echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
			} elseif ($experiment_type=='genotype') {
			  echo( "<tr> <td>$year</td> <td><a href='display_genotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
			}
		}
}
echo "</table>";
$footer_div = 1;
include($config['root_dir'].'theme/footer.php'); ?>
</body>
</html>
