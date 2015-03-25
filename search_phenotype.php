<?php session_start();
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();

//This is the main program for displaying a list of phenotype data.
$phenotypes_name=$_GET['pheno_name'];
$sql1="SELECT p.phenotypes_name, p.phenotype_uid, pc.phenotype_category_name, p.description,
	p.TO_number, p.alternate_name, u.unit_name, u.unit_description
	FROM phenotypes as p, units as u, phenotype_category as pc
	WHERE phenotypes_name='$phenotypes_name'
	AND u.unit_uid = p.unit_uid
	AND p.phenotype_category_uid = pc.phenotype_category_uid";
$res1=mysql_query($sql1) or die(mysql_error());
$row1=mysql_fetch_assoc($res1);
$trait=$row1['phenotypes_name'];
$display_name="Search Results for ".ucwords($trait); //used to display a beautiful name as the page header
$TOcode = $row1['TO_number'];
$puid = $row1['phenotype_uid'];
// Display Header information about the experiment
echo "<h1>".$display_name."</h1>";
echo "<table>";
        
echo "<tr> <td>Trait</td><td>".$trait."</td></tr>";
echo "<tr> <td>Category</td><td>".$row1['phenotype_category_name']."</td></tr>";
echo "<tr> <td>Units</td><td>".$row1['unit_name'];
if (!empty($row1['unit_description']))
  echo ": ".$row1['unit_description']."</td></tr>";
else 
  echo "</td></tr>";			
echo "<tr> <td>Description</td><td>".$row1['description']."</td></tr>";
if ( !empty($TOcode)) {
  $TOstr = "http://www.gramene.org/db/ontology/search?id=".$TOcode;
  echo "<tr> <td>Trait Ontology</td><td><a href='".$TOstr."'>".$TOcode."</a></td></tr>";
}
echo "</table>";
	// get selected experiments and verify that the user is authorized to see the experiment
	$sql2="select distinct  e.trial_code, e.experiment_year, e.experiment_uid
		FROM tht_base as tb, phenotype_data as pd, experiments as e
		WHERE pd.phenotype_uid='$puid'
			AND tb.tht_base_uid = pd.tht_base_uid
			AND e.experiment_uid = tb.experiment_uid";
	if (!authenticate(array(USER_TYPE_PARTICIPANT,
				USER_TYPE_CURATOR,
				USER_TYPE_ADMINISTRATOR)))
	  $sql2 .= " and e.data_public_flag > 0";
	$sql2 .= " order by e.experiment_year desc, e.trial_code asc";
	$res2=mysql_query($sql2) or die(mysql_error());
	
	$num_rows = mysql_num_rows($res2);
	
	if ($num_rows ==0){
		?>	<div class="section">
<p> There are no publicly available datasets for this trait in T3 at this time. 
 Registered users may see additional datasets after signing in.
		</div>
		<?php
	} else {

		?>
		<h3>Available Datasets for this Trait</h3>
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
		  $trial_code=$row_expuid['trial_code'];
		  $year=$row_expuid['experiment_year'];
		  $expuid=$row_expuid['experiment_uid'];
		  $traits=experimentListPhenotypes($expuid);
			echo( "<tr> <td>$year</td> <td><a href='display_phenotype.php?trial_code=$trial_code'>$trial_code</a> </td> <td>$traits</td> </tr>");
		
		}
		?>
			
			
			</tbody>
			</table>
		<?php
	}
	$footer_div = 1;
	include($config['root_dir'].'theme/footer.php'); ?>



