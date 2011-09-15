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

		echo "<h2>Add/Delete Phenotype Experiment Data </h2>"; 
		
			
		$this->type_Annotation_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Annotation_Name()
	{
	?>
	
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>

	<ul>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_experiments_upload_excel.php"><strong><li>Upload an Excel (.xls) file</li></strong></a>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_experiments_upload_text.php"><p><strong><li>Upload a tab-delimited (.txt) file</li> </strong> </a>  
	<a href="<?php echo $config['base_url']; ?>curator_data/delete_experiment.php"><p><strong><li>Delete an existing experiment</li></strong></a>
	</ul>
<?
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
