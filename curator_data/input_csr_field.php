<?php
// uploading it to main server

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
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
                global $mysqli;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add CSR Field Book</h2>"; 
		
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Name()
	{
            global $config;
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

<form action="curator_data/input_csr_field_check.php" method="post" enctype="multipart/form-data">
  <table>
  <tr><td><strong>Trial Name:</strong><td>
  <select name="exper_uid">
<?php
echo "<option>select a trial</option>\n";
$sql = "select trial_code, experiment_uid, experiment_year from experiments where experiment_type_uid = 1 order by experiment_year desc";
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
while ($row = mysqli_fetch_assoc($res)) {
  $tc = $row['trial_code'];
  $uid = $row['experiment_uid'];
  $trial_list[$uid] = $tc;
  echo "<option value='$uid'>$tc</option>\n";
}
echo "</select>\n";
?>
  <tr><td><strong>Field Book File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td><a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/field_template.xlsx">Example Data File</a>
  </table>
  <p><input type="submit" value="Upload" /></p>
</form>

<!--a href=login/edit_csr_field.php>Edit Field Book Table</a><br-->
		
<?php

//list links to saved Excel Files
echo "<br>List of currently loaded Field Book files<br>\n";
echo "<table border=1>\n";
$sql = "select experiment_uid, fieldbook_file_name, created_on from csr_fieldbook_info";
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
while ($row = mysqli_fetch_assoc($res)) {
  $name = $row['experiment_uid'];
  $file = $row['fieldbook_file_name'];
  $date = $row['created_on'];
  $tmp = $config['base_url'] . $file;
  echo "<tr><td><a href=$tmp>$trial_list[$name]</a><td>$date";
}
echo "</table>";
  

	} /* end of type_Experiment_Name function*/
} /* end of class */

?>
