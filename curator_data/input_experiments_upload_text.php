<?php
// uploading it to main server
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


new Experiments($_GET['function']);

class Experiments
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
							
					
			default:
				$this->typeExperiments(); /* intial case*/
				break;
			
		}	
	}


private function typeExperiments()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add Experiment Information </h2>"; 
		
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Name()
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
		
		<p><strong>Note: </strong> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <font size="2px">Please upload the relevant annotation file before uploading Means Data File. </font></p>
		<form action="<?php echo $config['base_url']; ?>curator_data/input_experiments_check_text.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>Means Data File:</strong> <input id="file[]" type="file" name="file[]" size="80%" /> &nbsp;&nbsp;&nbsp;   <a href="<?php echo $config['base_url']; ?>curator_data/examples/Expt_test.txt">Example Means Input File</a></p>
	<p><strong>Raw Data File:</strong> <input id="file[]" type="file" name="file[]" size="80%" /> &nbsp;&nbsp;&nbsp;   <a href="<?php echo $config['base_url']; ?>curator_data/examples/NDQ06Dormancy_raw_2008_08_27.xls">Example Raw Data File</a></p>
	<p><input type="submit" value="Upload Data File" /></p>

</form>
	
		
		
<?php
 
	} /* end of type_Experiment_Name function*/
	
} /* end of class */

?>
