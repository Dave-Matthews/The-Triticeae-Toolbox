<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Pedigree($_GET['function']);

class Pedigree
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
							
					
			default:
				$this->typePedigree(); /* intial case*/
				break;
			
		}	
	}


private function typePedigree()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add Pedigree Information </h2>"; 
		
			
		$this->type_Pedigree_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Pedigree_Name()
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
		
		<form action="curator_data/input_pedigree_check.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>File:</strong> <input id="file" type="file" name="file" size="80%" /> &nbsp;&nbsp;&nbsp;   <a href="curator_data/examples/MT06lines_2008_05_15.txt">Example Pedigree Input File</a></p>
	<p><input type="submit" value="Upload Pedigree File" /></p>
	
	

</form>
	
		
		
<?php
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
