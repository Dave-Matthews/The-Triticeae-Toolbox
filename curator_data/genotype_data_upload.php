<?php
/**
 * Genotype data importer
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/genotype_data_upload.php
 *
 */
// 08/09/2011 JLee  Add note that both files are required
// 04/11/2011 Jlee  Add zip file handling
//
// Written By: John Lee

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

/* ******************************* */

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new GenotypeData($_GET['function']);

class GenotypeData
{
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
	include $config['root_dir'] . 'theme/admin_header.php';

	echo "<h2>Add Genotype Experiment Information </h2>"; 
	$this->type_genoData_Name();
	$footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
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
     	<p><table>
	<tr><td><strong>Line Translation File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />
	<tr><td><td><a href="curator_data/examples/LinesTrialCode_Sample.txt">Example Line Translation File</a></p>
 	<tr><td><strong>Genotype Data File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />Size limit 100M
        <tr><td><strong>Data File Format:</strong><td><input type="radio" name="data_format" value="1D"> 1D Example
<a href="curator_data/examples/genotypeData_T3.txt">Genotype Data File</a>
                          <tr><td><td><input type="radio" name="data_format" value="2D" checked> 2D Example
<a href="curator_data/examples/TCAPbarley9K-sample.txt">Illumina_Genotype_template.txt</a>
                          <tr><td><td><input type="radio" name="data_format" value="2D"> 2D Example
<a href="curator_data/examples/GBS_Genotype_template.txt">GBS_Genotype_template.txt</a> (ACTG, N = missing, H = heterozygous) 
                          <tr><td><td><input type="radio" name="data_format" value="2D"> 2D Example 
<a href="curator_data/examples/DArT_Genotype_template.txt">DArT_Genotype_template.txt</a> (1 = Present, 0 = Absent, "-" =  missing)</p>
	</table>
    <p><input type="submit" name="submitButton" value="Upload Line Translation and Genotype Data File" /></p>
    <p><input type="submit" name="submitButton" value="Verify Files" /> Check if lines and markers exist in database</p>
 
    <p></p>
    <p>Note:<br>
    1. Both files (line translation and genotype data) are required. Line names should be identical between both files.<br>
    2. Due to size of the Genotype Data File, it can be compressed with a "zip" application before submitting it.<br>
    3. Genotype files with over 100K makers should be imported via the command line as described in <a href="curator_data/gbs_import_instructions.php" target="_new">GBS import instruction</a>.<br>
    4. This upload process may take several hours to complete depending on size of the data file.  Please leave your email address for us to contact you with the results.</p>
	<p> <strong> Your Email Address: </strong> <input type="text" name="emailAddr" value="<?php echo $_SESSION['username'] ?>"  size="50%"/>
	</form>

<?php
 
	} 
	
} /* end of class */

?>
