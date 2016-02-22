<?php 
// dem oct2014: For a breeding program, find trials containing lines from that program.

session_start();
require 'config.php';
include $config['root_dir'].'includes/bootstrap.inc';
include $config['root_dir'].'theme/normal_header.php';
$mysqli = connecti();

//This is the main program for displaying a list of experiments for a given program.

$uid = intval($_GET['uid']);
$trial_code=NULL;
$sql="SELECT data_program_name, data_program_code,program_type FROM CAPdata_programs WHERE CAPdata_programs_uid='$uid'";
$result_dpname=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
$row_dpname=mysqli_fetch_array($result_dpname);
$dpname=$row_dpname['data_program_name'];
$dpcode=$row_dpname['data_program_code'];
$dpname = $dpname." (".$dpcode.")";
$dptype = $row_dpname['program_type'];
echo "<h1> $dpname </h1>";

if (($dptype =='breeding') || ($dptype =='mapping')) 
  $sql1 = "SELECT DISTINCT e.experiment_uid
	   FROM CAPdata_programs cp, experiments e, tht_base tb, line_records lr
	   WHERE tb.line_record_uid = lr.line_record_uid
	   AND lr.breeding_program_code = cp.data_program_code
	   AND tb.experiment_uid = e.experiment_uid
	   AND cp.CAPdata_programs_uid = $uid";
else 
  $sql1="SELECT experiment_uid FROM experiments WHERE CAPdata_programs_uid='$uid'";
$res1=mysqli_query($mysqli, $sql1) or die(mysqli_error($mysqli));
$num_rows = mysqli_num_rows($res1);
if ($num_rows == 0) {
  echo "<div class='section'>";
  echo "<p>There are no data from this Program in the database.</div>";
} 
else {
  if (($dptype =='breeding') || ($dptype =='mapping')) {
    while ($row1=mysqli_fetch_array($res1))
      $exptuids[] = $row1['experiment_uid'];
    $exptlist = implode(',', $exptuids);
    // get selected experiments and verify that the user is authorized to see the experiment         
    $sql2="select experiment_uid, trial_code, experiment_year, et.experiment_type_name  
	      from experiments e, experiment_types et
	      where experiment_uid in ($exptlist)
	      and et.experiment_type_uid = e.experiment_type_uid" ;
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
			    USER_TYPE_CURATOR,
			    USER_TYPE_ADMINISTRATOR)))
      $sql2 .= " and data_public_flag > 0";
    $sql2 .= " order by experiment_year desc, trial_code asc";
  } 
  else {
    // Program Type is not breeding or mapping.
    $sql2="select e.experiment_uid, e.trial_code, e.experiment_year, et.experiment_type_name
	    from experiments as e, experiment_types as et
	    where CAPdata_programs_uid='$uid'
	    AND et.experiment_type_uid = e.experiment_type_uid";
    if (!authenticate(array(USER_TYPE_PARTICIPANT,
			    USER_TYPE_CURATOR,
			    USER_TYPE_ADMINISTRATOR)))
      $sql2 .= " and data_public_flag > 0";
    $sql2 .= " order by e.experiment_year desc, e.trial_code asc";
  }
  $res2=mysqli_query($mysqli, $sql2) or die(mysqli_error($mysqli));
  $num_rows = mysqli_num_rows($res2);
  if ($num_rows == 0) {
    echo "<div class='section'>";
    echo "<p>There are no public data from this Program in the database. Project participants must be logged in to see any private datasets.</div>";

  } 
  else {
    // Show the trials available.
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
      while($row_expuid=mysqli_fetch_array($res2)) {
	$expuid=$row_expuid['experiment_uid'];
	$trial_code=$row_expuid['trial_code'];
	$year=$row_expuid['experiment_year'];
	$experiment_type = $row_expuid['experiment_type_name'];
	$traits = experimentListPhenotypes($expuid);
	if ($experiment_type=='phenotype') 
	  echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
	elseif ($experiment_type=='genotype') 
	  echo( "<tr> <td>$year</td> <td><a href='display_genotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
      }
    echo "</tbody></table>";
  }
} 
$footer_div = 1;
include $config['root_dir'].'theme/footer.php';
