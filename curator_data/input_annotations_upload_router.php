<?
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
	
	<ul>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_text.php"><li>Upload a Tab Delimited(.txt) File (THT)</li></a><br>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_excel.php"><li><strong>Upload an Excel (.xls) File (T3)</strong></li></a><br>
        <a href="<?php echo $config['base_url']; ?>curator_data/input_annotations_upload_excel_tht.php"><li>Upload an Excel (.xls) File (THT)</li></a> 
	</ul>
	
	

	
		
		
<?
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
