<?php 
// Marker data importer

// Author: JLee 

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Markers($_GET['function']);

class Markers {
    
    private $delimiter = "\t";
    	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function) {
							
			default:
				$this->typeMarkers(); /* intial case*/
				break;
		}	
	}


    private function typeMarkers() 	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add/Edit Marker Information </h2>"; 
		$this->type_Marker_Data();
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Marker_Data() {
	?>
	<script type="text/javascript">
	</script>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
                        td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
	</style>
		
	<form action="curator_data/markers_upload_check.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
        <table>
	<tr><td><strong>Marker Annotation File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />
        <tr><td><td><a href="curator_data/examples/Marker_import_sample4.txt">Example Marker Annotation File</a>
	<tr><td> <H4> &nbsp;&nbsp;&nbsp;&nbsp;Or </H4>
 	<tr><td><strong>SNP Sequence File:</strong><td><input id="file[]" type="file" name="file[]" size="80%" />
        <tr><td><td><a href="curator_data/examples/SNP_assay.txt">Illumina Manifest (opa) Format</a>
 , <a href="curator_data/examples/Marker_import_sample5.txt">Illumina Manifest (Infinium) Format</a>
 , <a href="curator_data/examples/Generic_SNP.txt">Generic Format(csv)</a> <font color=red> Updated 02/04/2014</font>
 , or <a href="curator_data/examples/DArT.csv">DArT Format(csv)</a>
        </table><br>
	<p><input type="submit" value="Upload Marker Import Files" /></p>
    </form>
	<br>
	<br>
	<p>
        </style>
        NOTE:<br>
        1. Use Illumina format for files with AB base calls. Use Generic format for ACTG base calls. Use A_allele = 1 and B_allele = 0 for DArT markers.<br>
        2. For Illumina and DArT format the marker names should already be defined by importing the marker annotation file.<br>
        3. For Generic format you can skip the annotation file and a new database entry will be created by the sequence file import.<br>
	</p>
<?php
 
	} /* end of function*/
	
} /* end of class */

?>
