<?php
/**
/ 4/26/2011 J.Lee  Redirect output file to Temp folder and
/		   Address possible concurrency issue, hardwire map data
/                  MSIE about box not adjustable, comments not updated when
/                  selecting mapset, etc.
*/

require_once 'config.php';
require $config['root_dir'].'includes/bootstrap.inc';
$mysqli = connecti();

new Map_FlapJack($_GET['function']);

class Map_FlapJack
{
    
    private $delimiter = "\t";
    public $mapsetHash = array();

    // Using the class's constructor to decide which action to perform
    public function __construct($function = null) {	

    switch($function)
    {
			
        case 'typeMapOut':
                $this->type_output();
				break;
                
			default:
				$this->typeMapSet(); /* intial case*/
				break;
			
		}	
	}

   private function type_output() {
        global $mysqli;
//		include($config['root_dir'] . 'theme/admin_header.php');

    	$mapsetID = $_GET["msid"];
    	
    	if (empty ($mapsetID)) {
    	    die("Please select a map first!"); 
    	}

        if (is_dir("/tmp/tht") == false) {
            mkdir ("/tmp/tht",0777,true);
            chmod('/tmp/tht', 0777);
        }

        //$myFile = '/tmp/tht/tht_FlapJack_Map'.chr(rand(65,80)).chr(rand(65,80)).chr(rand(64,80)).'.txt';
        //$fh = fopen($myFile, 'w') or die("can't open file");
        //fwrite($fh, $mapsetID);
        //fwrite($fh, "\n");
        $tab = "\t";
        $sql_map = "SELECT map_name FROM map where mapset_uid = " . $mapsetID;
        $res_map = mysqli_query($mysqli, $sql_map) or die("Error: Can't locate map name - " . mysqli_error($mysqli));
	
        while ($row_map = mysqli_fetch_assoc($res_map)) {
	
 	       	$sql = "SELECT mkr.marker_name, mk.start_position,  mk.chromosome  
                    FROM map m, markers_in_maps mk, markers mkr 
                    where  map_name='".$row_map['map_name']."' and m.map_uid = mk.map_uid 
                    AND mk.marker_uid = mkr.marker_uid ORDER BY mk.start_position";
			$res = mysqli_query($mysqli, $sql) or die("Error: map data retrieval - " . mysqli_error($mysqli));
		    echo "<pre>";
            while ($row = mysqli_fetch_assoc($res)) {
                if (empty($row['marker_name'])) continue;
            	$stringData = $row['marker_name'].$tab.$row['chromosome'].$tab.$row['start_position'].PHP_EOL;
                //fwrite($fh, $stringData);
                echo $stringData;
            } /* end of marker details while loop */
            echo "</pre>";
        } /* end of map name while loop */
  	
        //fclose($fh);
		// JLee force url context change
        //header('Cache-Control:');
		//header('Pragma: public');
        //header("Content-Transfer-Encoding: binary"); 
        //header("Content-length: ".filesize($myFile)); 
		//header('Content-type: text/plain');
        //header('Content-Disposition: attachment; filename="' . basename($myFile) . '"');		
        //header('Pragma: no-cache');
		//header('Expires: 0');
        //ob_clean();
        //flush();
        //readfile($myFile);
    }

    private function typeMapSet() {
	global $config;
        global $mysqli;
        global $mapsetHash;
        
        include $config['root_dir'].'theme/normal_header.php';
        $sql = "SELECT comments, mapset_uid FROM mapset";

        $res = mysqli_query($mysqli, $sql) or die("Error: Unable to create comment hash table - ". mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
            $mapset_uid = $row['mapset_uid'];
            $mapsetHash[$mapset_uid] = $row['comments'];
        }

	echo "<h2>Map Sets Details</h2>"; 
	$this->type_MapSet_Display();

	$footer_div = 1;
        include $config['root_dir'].'theme/footer.php'; 
}
	
	
	private function type_MapSet_Display() 	{
        global $mysqli;
        global $mapsetHash;
        
?>
<script type="text/javascript">
	
        function load_flapjack() {
			
	var url="<?php echo $_SERVER[PHP_SELF];?>?function=typeFlapJack";
        url = url.replace(/\/\/\/+/g, '/');
			// Opens the url in the same window
        window.open(url, "_self");
        }
	  
        function display_comments(comvalue) {
			
            var commentLookup = <?php echo json_encode($mapsetHash); ?>;
			var comment_str = commentLookup[comvalue];
 
			my_window= window.open ("",  "mywindow1","status=1,width=450,height=150,resizable=yes",true);
            //my_window.document.write(comvalue) ;
            //my_window.document.write('<br>');
			my_window.document.write(comment_str);
            my_window.document.close();
            if (window.focus) {my_window.focus();}
        
		}
        
        function create_output(mapsetID) {
		
           //alert (" I was here");
            var url="<?php echo $_SERVER[PHP_SELF];?>?function=typeMapOut" + "&msid=" + mapsetID;
            url = url.replace(/\/\/\/+/g, '/');
			// Opens the url in the same window
            window.open(url, "_self");
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
		<select name="msid" id="msid" size="5" style="height: 12em;" onclick="javascript:display_comments(this.value)">
	<?php
		
        // Select Mapset Name for the drop down menu
        $sql = "SELECT mapset_name, mapset_uid FROM mapset ORDER BY mapset_name DESC";

        $res = mysqli_query($mysqli, $sql) or die("Error: Unable to get mapset names and uids ". mysqli_error($mysqli));
        while ($row = mysqli_fetch_assoc($res)) {
            if (empty($row['mapset_name'])) continue;
	?>
			<option value="<?php echo $row['mapset_uid']; ?>"><?php echo $row['mapset_name']; ?></option>
	<?php
		}
        echo "</select>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td>	";
?>
<input type="Button" value="Download" onclick="create_output(document.getElementById('msid').value)" />
<?php 
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</div>";
    }	

 } /* end of class */
