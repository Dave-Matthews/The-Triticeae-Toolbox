11;rgb:ffff/fafa/f0f0<?php 
// Genotype data importer
// 08/09/2011 JLee  Add note that both files are required
// 04/11/2011 Jlee  Add zip file handling
// Written By: John Lee
//*********************************************

require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'] . 'theme/admin_header.php');
connect();
loginTest();

ob_start();
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

echo "<h2>Add Genotype Experiment Information </h2>"; 
?>
	
<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<form action="curator_data/genotype_data_check.php" method="post" enctype="multipart/form-data">

  <input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
  <p><table>
      <tr><td><strong>Line Translation File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />
      <tr><td><td><a href="curator_data/examples/LinesTrialCode_Sample.txt">Example Line Translation File</a></p>
      <tr><td><strong>Genotype Data File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />
      <tr><td><strong>Data File Format:</strong><td><input type="radio" name="data_format" value="1D"> 1D Example
	  <a href="curator_data/examples/genotypeData_T3.txt">Genotype Data File</a>
      <tr><td><td><input type="radio" name="data_format" value="2D" checked> 2D Example
	  <a href="curator_data/examples/TCAPbarley9K-sample.txt">Illumina_Genotype_template.txt</a>
      <tr><td><td><input type="radio" name="data_format" value="2D"> 2D Example
	  <a href="curator_data/examples/GBS_Genotype_template.txt">GBS_Genotype_template.txt</a> (ACTG) 
      <tr><td><td><input type="radio" name="data_format" value="2D"> 2D Example 
	  <a href="curator_data/examples/DArT_Genotype_template.txt">DArT_Genotype_template.txt</a> (Present = 1, Absent = 0, missing = "-")
</table>

<p><b>Note: Both files (Line Translation and Genotype Data) are required.</b>

<p>Data loading may take several hours to complete.  The results will be sent to the address below.
  <br><strong>Email address </strong> <input type="text" name="emailAddr" value="<?php echo $_SESSION['username'] ?>"  size="50%"/>
<p><input type="submit" value="Upload Line Translation and Genotype Data File" /></p>
</form>

<?php
$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
