<?php 
// Genotype data importer

// 08/09/2011 JLee  Add note that both files are required
// 04/11/2011 Jlee  Add zip file handling
//
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
     	<p><table style="text-align:left;">
	<tr style="min-height: 0px"><td><strong>Line Translation File:</strong></td>
	<td><input id="file[]" type="file" name="file[]"><br>
        <tr style="min-height: 0px"><td><td>Two columns, tab-delimited, containing:<br>
Line Name: Sample ID names used for the experiment (can be found in the Sample Sheet files)<br>
Trial Code: Unique code for each site's data, as defined in the Genotype Annotation file<br>
<a href="curator_data/examples/LinesTrialCode_Sample.txt">Example Line Translation File</a></p>
 	<tr><td><strong>Genotype Data File:</strong><td><input id="file[]" type="file" name="file[]">
	    <br>May be compressed in .zip format.
        <tr><td style=vertical-align:text-top><strong>Data File Format:</strong>
	  <td><input type="radio" name="data_format" value="1D"> 1D <a href="curator_data/examples/genotypeData_T3.txt">Example Genotype Data file</a>
            <br><input type="radio" name="data_format" value="2D" checked> 2D <a href="curator_data/examples/TCAPbarley9K-sample.txt">Example Genotype Data file</a></p>
	</table>

    <p><b>Note: Both files (Line Translation and Genotype Data) are required.</b>

    <p>Data loading may take several hours to complete.  The results will be sent to the address below.
	<br><strong>Email address </strong> <input type="text" name="emailAddr" value="<?php echo $_SESSION['username'] ?>"  size="50%"/>
	<p><input type="submit" value="Upload Line Translation and Genotype Data File" /></p>
	</form>

<?php
 
	} 
	
} /* end of class */

?>
