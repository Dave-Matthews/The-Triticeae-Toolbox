<?php
// 20100629 JLee - make link to edit_line.php relative
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


new LineNames($_GET['function']);

class LineNames
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'type1experiments':
				$this->type1_experiments(); /* display experiments */
				break;
				
			case 'typeLineData':
				$this->type_Line_Data(); /* Handle Line Data */
				break;
			
			default:
				$this->typeLineName(); /* intial case*/
				break;
			
		}	
	}


private function typeLineName()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add New Lines </h2>"; 
		
			
		$this->type_Line_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Line_Name()
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
		
		<form action="<?php echo $config['root_dir'] ?>curator_data/input_line_names_check.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>File:</strong> <input id="file" type="file" name="file" /><br>
<a href="<?php echo $config['root_dir'] ?>curator_data/examples/T3/LineSubmissionForm_Wheat.xls">Example line input file</a></p>
	<p><input type="submit" value="Upload Line File" /></p>
	
	<a href="<?php echo $config['root_dir'] ?>login/edit_line.php"> Edit existing lines.</a>

</form>
	
		
		
<?php
 
	} /* end of type_GenoType_Display function*/
	
} /* end of class */

?>
