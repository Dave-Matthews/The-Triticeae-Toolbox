<?php 

require_once('config.php');
include($config['root_dir'].'includes/bootstrap.inc');
connect();


new Map_FlapJack($_GET['function']);

class Map_FlapJack
{
    
    private $delimiter = "\t";
    
	
	// Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			
			case 'typeFlapJack':
				$this->type_Flap_Jack(); /* Handle Flap Jack Compatible download */
				break;
			
			default:
				$this->typeMapSet(); /* intial case*/
				break;
			
		}	
	}


private function typeMapSet()
	{
		global $config;
		include($config['root_dir'].'theme/normal_header.php');

		echo "<h2>Map Sets Details</h2>"; 
		
			
		$this->type_MapSet_Display();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_MapSet_Display()
	{
?>
<script type="text/javascript">
	
	function load_flapjack()
			{
			
			var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeFlapJack';
	
			// Opens the url in the same window
	   	window.open(url, "_self");
	  }
	  
	  function display_comments(comvalue)
		{
			
			comment_str = comvalue;
			
			my_window= window.open ("",  "mywindow1","status=1,width=450,height=150");
			my_window.document.write(comment_str);
			if (window.focus) {my_window.focus()}

		}
	
	
</script>
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<div style=" float: left; margin-bottom: 1.5em;">
		<table>
				<tr>
				
					<th>MapSet Name</th>
										
				</tr>
				
				<tr>
				
					<td>
						<select name="mapsetnames" size="5" style="height: 12em;" onchange="javascript: display_comments(this.value)">
				<?php
		
		
		// Select Mapset Name for the drop down menu
		$sql = "SELECT mapset_name, comments FROM mapset ORDER BY mapset_name DESC";

		$res = mysql_query($sql) or die(mysql_error());
		while ($row = mysql_fetch_assoc($res))
		{
			?>
				<option value="<?php echo $row['comments'] ?>"><?php echo $row['mapset_name'] ?></option>
			<?php
		}
		?>
						</select>
					</td>
					</tr>
					
			
	
	<?php 
	
	
	
	$myFile = "tht_FlapJack_Map.txt";
	$fh = fopen($myFile, 'w') or die("can't open file");
	$delimiter ="\t";
	$sql_map = "SELECT map_name FROM map  where mapset_uid IN (1,9)";
	$res_map = mysql_query($sql_map) or die(mysql_error());
	
	while ($row_map = mysql_fetch_assoc($res_map)) {
	
		$sql = "SELECT mkr.marker_name, mk.start_position,  mk.chromosome  FROM map m, markers_in_maps mk, markers mkr where map_name='".$row_map['map_name']."' and m.map_uid = mk.map_uid AND mk.marker_uid = mkr.marker_uid ORDER BY mk.start_position";
		$res = mysql_query($sql) or die(mysql_error());
		
		while ($row = mysql_fetch_assoc($res)) {
			
					$stringData = $row['marker_name'].$delimiter.$row['chromosome'].$delimiter.$row['start_position']."\n";
					fwrite($fh, $stringData);
		} /* end of marker details while loop */
	
			
	} /* end of map name while loop */
	
	?>
	<tr>
			<td>	
				
		
		<form action='tht_FlapJack_Map.txt'>
<input type='submit' value='Download'/>
</form>
		
		</td>
		</tr>
		</table>
					</div>

<?php 
	fclose($fh);
	
	} /* end of type_MapSet_Display function */

} /* end of class */

?>
