<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

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

		echo "<h2>Add/Edit Pedigree Information </h2>"; 
		
			
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
	
		<table>
		<tr>
		<td>
	<a href="<?php echo $config['base_url']; ?>login/edit_pedigree.php"><p><strong><li>Edit Pedigree</li> </strong></p> </a>  
	</td>
	<td colspan="2">
	</td>
	<td>
	<a href="<?php echo $config['base_url']; ?>curator_data/input_pedigree_upload.php"><p><strong><li>Add Pedigree</li></strong></p></a>
	</td>
	</tr>
	</table>
	
<?php
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
