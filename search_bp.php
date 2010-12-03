<?php session_start();
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();

//This is the main program for displaying a list of experiments for a given program.

	$uid = $_GET['uid'];
	$trial_code=NULL;
	$sql="SELECT data_program_name, data_program_code,program_type FROM CAPdata_programs WHERE CAPdata_programs_uid='$uid'";
	$result_dpname=mysql_query($sql) or die(mysql_error());
	$row_dpname=mysql_fetch_array($result_dpname);
	$dpname=$row_dpname['data_program_name'];
	$dpcode=$row_dpname['data_program_code'];
	$dpname = $dpname." (".$dpcode.")";
	$dptype = $row_dpname['program_type'];
	echo "<h1> $dpname </h1>";

        if (($dptype =='breeding') || ($dptype =='mapping')) 
		$sql1="SELECT datasets_uid FROM datasets WHERE CAPdata_programs_uid='$uid'";
	else {
		$sql1="SELECT experiment_uid FROM experiments WHERE CAPdata_programs_uid='$uid'";
	}
	$res1=mysql_query($sql1) or die(mysql_error());
	$num_rows = mysql_num_rows($res1);
	
	if ($num_rows ==0){
			?>	<div class="section">
				<p> There are no available datasets for this CAP data provider in THT at this time.</p>
			</div>
			<?php
	} else {
	       if (($dptype =='breeding') || ($dptype =='mapping')) {
			// updated to allow for multiple datasets across years
			while ($row1=mysql_fetch_array($res1)){
				$datasets_uid[]=$row1['datasets_uid'];
				}
			$datasets_uid = implode(',',$datasets_uid);
			
			
			// get selected experiments and verify that the user is authorized to see the experiment
			$sql2="select e.experiment_uid, e.trial_code, e.experiment_year, et.experiment_type_name
			from datasets_experiments
			inner join experiments as e using (experiment_uid),experiment_types as et
			where datasets_uid IN ($datasets_uid)
				AND et.experiment_type_uid = e.experiment_type_uid";
			if (!authenticate(array(USER_TYPE_PARTICIPANT,
						USER_TYPE_CURATOR,
						USER_TYPE_ADMINISTRATOR)))
			  $sql2 .= " and data_public_flag > 0";
			$sql2 .= " order by e.experiment_year, e.trial_code asc";
		} else {
			$sql2="select e.experiment_uid, e.trial_code, e.experiment_year, et.experiment_type_name
				from experiments as e, experiment_types as et
				where CAPdata_programs_uid='$uid'
				AND et.experiment_type_uid = e.experiment_type_uid";
			if (!authenticate(array(USER_TYPE_PARTICIPANT,
						USER_TYPE_CURATOR,
						USER_TYPE_ADMINISTRATOR)))
			  $sql2 .= " and data_public_flag > 0";
			$sql2 .= " order by e.experiment_year, e.trial_code asc";
		}
		$res2=mysql_query($sql2) or die(mysql_error());
		
		$num_rows = mysql_num_rows($res2);
		if ($num_rows ==0){
			?>	<div class="section">
				<p> There are no publicly available datasets for this CAP data provider in THT at this time. Participants in the BarleyCAP project will need to login to see additional datasets.</p>
			</div>
			<?php
		} else {
?>
			<h3>Available Datasets</h3>
			<p>
				<table cellpadding="0" cellspacing="0">
				<tr>
					<th>Year</th>
					<th>Trial Name</th>
					<th>Traits</th>
					
				</tr>
				<?php
			while($row_expuid=mysql_fetch_array($res2))
			{
			  $expuid=$row_expuid['experiment_uid'];
			
			 /* $sql="SELECT e.trial_code, e.experiment_year, et.experiment_type_name
						FROM experiments as e, experiment_types as et
						WHERE e.experiment_uid = '$expuid'
						AND et.experiment_type_uid = e.experiment_type_uid
						ORDER BY e.experiment_year,e.trial_code ASC";
			  $result_trialcode=mysql_query($sql) or die(mysql_error());
			  $row_trialcode=mysql_fetch_array($result_trialcode);*/
			  $trial_code=$row_expuid['trial_code'];
			  $year=$row_expuid['experiment_year'];
			  $filename="data/".$trial_code.".xls";
			  $experiment_type = $row_expuid['experiment_type_name'];
			  $traits=experimentListPhenotypes($expuid);
			  if ($experiment_type=='phenotype') {
				echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
			  } elseif ($experiment_type=='genotype') {
				echo( "<tr> <td>$year</td> <td><a href='display_genotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
			  }
			
			}
			?>
				
				
				</tbody>
				</table>
			<?php
		}
	} 

	$footer_div = 1;
	include($config['root_dir'].'theme/footer.php'); ?>

?>

