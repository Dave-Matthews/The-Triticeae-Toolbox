<?php 
// Genotype data importer

// 9/16/2011  JLee  Modify for T3

// Written By: John Lee
//*********************************************

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
loginTest();

/* ******************************* */

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new GenotypeData($_GET['function']);

class GenotypeData {
    
    private $delimiter = "\t";
    	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function) {
							
			default:
				$this->typeDataResults(); /* intial case*/
				break;
		}	
	}


    private function typeDataResults() 	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add Genotype Experiment Information </h2>"; 
		$this->type_genoData_Name();
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_genoData_Name() {
      
	?>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
	</style>
		
	<form action="curator_data/genotype_data_check.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>Line Translation File:</strong> <input id="file[]" type="file" name="file[]" size="80%" /> &nbsp;&nbsp;&nbsp; <a href="curator_data/examples/LinesTrialCode_Sample.txt">Example Line Translation File</a></p>
 	<p><strong>Genotype Data File:</strong> <input id="file[]" type="file" name="file[]" size="80%" /> &nbsp;&nbsp;&nbsp; <a href="curator_data/examples/genotypeData_T3.txt">Example Genotype Data File</a></p>
    <p><input type="submit" value="Upload Genotype Data File" /></p>
 
    <p><br> <br></p>
    <p><h4>Note: This process may take several hours to complete depending on size of the data file.  Please leave your email adress for us to contact you with the results.</h4></p>
	<p> <strong> Your Email Address: </strong> <input type="text" name="emailAddr" value="<?php echo $_SESSION['username'] ?>"  size="50%"/>
	</form>

<?php
 
	} 
	
} /* end of class */

?>
