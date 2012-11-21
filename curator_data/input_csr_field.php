<?php
// uploading it to main server

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
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

		echo "<h2>Add CSR Field Book</h2>"; 
		
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Name()
	{
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
  <tr><td><strong>Field Book Name:</strong><td><input id="fieldbook" type="text" name="fieldbook" size="50%" />
  <tr><td><strong>Data File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td><a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/field_template.xlsx">Example Data File</a>
  </table>
  <p><input type="submit" value="Upload" /></p>
</form>

<a href=login/edit_csr_field.php>Edit Field Book Table</a>
		
<?php
	} /* end of type_Experiment_Name function*/
} /* end of class */

?>
