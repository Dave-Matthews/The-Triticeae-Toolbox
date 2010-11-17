<?php

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


new Maps($_GET['function']);

class Maps
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
							
					
			default:
				$this->typeMaps(); /* intial case*/
				break;
			
		}	
	}


private function typeMaps()
	{
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add/Upload Maps Information </h2>"; 
		
			
		$this->type_Map_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Map_Name()
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
		
		<form action="curator_data/input_maps_check.php" method="post" enctype="multipart/form-data">

	<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
	<p><strong>Map File:</strong> <input id="file" type="file" name="file" size="80%" /> &nbsp;&nbsp;&nbsp;   <a href="curator_data/examples/mapupload_example.txt">Example Map File</a></p>
	
	<table>
	<tr>
	<td>
	<p><strong>Map Set Name:</strong> </p>
	<p> <input type="textbox" name="mapset_name"/></p>
	</td>
	
	
	<td>	
	<p><strong>Map Set Prefix:</strong></p>
	<p> <input type="textbox" name="mapset_prefix"/></p>
	</td>
	
	<td>
	<p><strong>Species:</strong></p>
	<p><input type="textbox" name="species" value="Hordeum"/></p>
	</td>
	
	
	<td>
	<p><strong>Map Type:</strong></p>
	<p><input type="textbox" name="map_type" value="Genetic"/></p>
	</td>
	
	<td>	
	<p><strong>Map Unit:</strong></p>
	<p><input type="textbox" name="map_unit" value="cM"/></p>
	</td>
	
	</tr>
	</table>
	
	<p><strong>Comments:</strong> </p>
	<p><textarea name="comments" cols="40" rows="6" >PLEASE ENTER YOUR COMMENTS HERE </textarea></p>
	
	<p><input type="submit" value="Upload Maps File" /></p>
	
	

</form>
	
		
		
<?php
 
	} /* end of type_Pedigree_Name function*/
	
} /* end of class */

?>
