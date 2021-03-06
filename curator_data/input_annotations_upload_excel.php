<?php
// 12/14/2010 JLee  Change to use curator bootstrap

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

/* Add "(new <date>)" if newer than 30 days. */
function filelink($path, $label) {
  echo "<a href='curator_data/examples/$path'>$label</a>";
  if (time() - filemtime("examples/$path") < 2592000)
    echo " <font size= -2 color=red>(new ". date("dMY", filemtime("examples/$path")) . ")</font>";
}

new Annotations($_GET['function']);

class Annotations
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
							
					
			default:
				$this->typeAnnotations(); /* intial case*/
				break;
			
		}	
	}


private function typeAnnotations()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add Experiment Annotation Information </h2>"; 
		
			
		$this->type_Annotation_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Annotation_Name()
	{
	?>
	<script type="text/javascript">
	
	
	</script>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<form action="curator_data/input_annotations_check_excel.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>Annotation File:</strong> <input id="file[]" type="file" name="file[]" size="50%" /><br>
<?php filelink("T3/TrialSubmissionForm.xls", "Example template") ?>
	
	
	<p> <strong> Do You Want This Data To Be Public: </strong> <input type='radio' name='flag' value="1" checked/> Yes &nbsp;&nbsp; <input type='radio' name='flag' value="0"/> No
	<p><input type="submit" value="Upload Annotations File" /></p>
	
	

</form>
	
		
		
<?php
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
