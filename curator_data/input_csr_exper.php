<?php
// uploading it to main server

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Experiments($_GET['function']);

class Experiments
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
							
					
			default:
				$this->typeExperiments(); /* intial case*/
				break;
			
		}	
	}


private function typeExperiments()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add CSR Experiment Results</h2>"; 
                echo "Note: Please load the corresponding Phenotype Experiment, Spectrometer System, and Field Book files before uploading the CSR Experiment Results<br>";
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Name()
	{
          global $mysqli;
	?>

<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<!-- <p><strong>Note: </strong><font size="2px">Please load the corresponding
    <a href="<?php echo $config['base_url'] ?>curator_data/input_annotations_upload_excel.php">Phenotype 
      Experiment Annotations</a> file before uploading the results files. </font></p> -->
<br>
<form action="curator_data/input_csr_exper_check.php" method="post" enctype="multipart/form-data">
  <table>
  <tr><td><strong>Trial Name:</strong><td>
  <select name="exper_uid">
<?php
echo "<option>select a trial</option>\n";
$sql = "select trial_code, experiment_uid, experiment_year from experiments where experiment_type_uid = 1 order by experiment_year desc, trial_code";
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
while ($row = mysqli_fetch_assoc($res)) {
  $tc = $row['trial_code'];
  $uid = $row['experiment_uid'];
  $trial_list[$uid] = $tc;
  echo "<option value='$uid'>$tc</option>\n";
}
echo "</select>\n";
?>
  <tr><td><strong>CSR Annotation File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td>
  <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSRinT3_Sp1_Annotation.xlsx">CSR Annotation Template</a><td><font color=red>Updated 02/12/2013</font>
  <tr><td><strong>CSR Data File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td>
  <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSR_Data_template.txt">CSR Data Template (Jaz, USB2000)</a><br>
  <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/CSR_CropScan_template.txt">CSR Data Template (CropScan)</a>
  </table>
  <p><input type="submit" value="Upload" /></p>
</form>

<a href=login/edit_csr_trials.php>Edit CSR Trial Table</a><br><br>
Note: When CropScan data files is loaded it is translated into the format used by the Jaz device.<br>
The lines before the column header in the CropScan data file are not be saved.
		
<?php
	} /* end of type_Experiment_Name function*/
} /* end of class */

?>
