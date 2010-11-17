<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');

?>
<div id="primaryContentContainer">
	<div id="primaryContent">
	
<div class="section">
		
 <!-- Box Table B -->
 <p>
	<table cellpadding="0" cellspacing="0">
	<tr>
		<th>Search Type</th>
		<th>Example Search Term</th>
	</tr>
	
   <tr>
  <td>Search By Breeding Program <br>
  <form action="<?php echo $config['base_url']; ?>search_bp.php" method="get">
  <input type="hidden" name="table" value="CAPdata_programs">
  <select name="uid">
  <option value="Select">Select Breeding Program</option>
   <?php
  $sql = "select distinct data_program_name, data_program_code, CAPdata_programs_uid as uid
		  FROM CAPdata_programs
		  WHERE program_type = 'breeding'
		  order by data_program_name asc";
$r = mysql_query($sql) or die("<pre>" . mysql_error() . "\n$sql");
while($row = mysql_fetch_assoc($r)) {
  $progname = $row['data_program_name']." - ".$row['data_program_code'];
  $uid = $row['uid'];
  echo "<option value='$uid'>$progname</option>\n";
 }
?>
  </select>
  <input type="submit" name="breeding" value="Go >>" /></form></td>

  <td>Select a breeding program from the list to see all datasets containing data from program&#39;s lines.</td>
  </tr>
   
  <tr>
  <td>Search By Year <br>
  <form action="<?php echo $config['base_url']; ?>view_search_yr2.php" method="get">
  <input type="hidden" name="table" value="experiments">
  <select name="year">
  <option value="Select">Select Year</option>
  <?php
  $sql = "select distinct experiment_year from experiments
  order by experiment_year";
$r = mysql_query($sql) or die("<pre>" . mysql_error() . "\n$sql");
while($row = mysql_fetch_assoc($r)) {
  $year = $row['experiment_year'];
  echo "<option value='$year'>$year</option>\n";
 }
?>
</select>
<input type="submit" name="datasets" value="Go >>"></form></td>
<td>Select an experiment year to see all experiment data from that year.</td></tr>
    <tr>
  <td>Search By Phenotype <br>
  <form action="<?php echo $config['base_url']; ?>search_phenotype.php" method="get">
  <input type="hidden" name="table" value="experiments">
  <select name="pheno_name">
  <option value="Select">Select</option>
  <?php
  $sql = "select distinct phenotypes_name from phenotypes
  order by phenotypes_name";
$r = mysql_query($sql) or die("<pre>" . mysql_error() . "\n$sql");
while($row = mysql_fetch_assoc($r)) {
  $pheno_name = $row['phenotypes_name'];
  echo "<option value='$pheno_name'>$pheno_name</option>\n";
 }
?>
</select>
<input type="submit" name="datasets" value="Go >>"></form></td>
<td>Select phenotype to see information about the trait and all experiments that measure the trait.</td></tr>
  </tbody></table>
	</div>

	</div>
</div>
</div>
<?php
include($config['root_dir'].'theme/footer.php');
?>