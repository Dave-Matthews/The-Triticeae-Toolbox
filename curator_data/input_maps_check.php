<?php

/** 12/14/2010 JLee  Change to use curator bootstrap **/

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
set_time_limit(3000);
ini_set('memory_limit', '4G');

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new MapsCheck($_GET['function']);

class MapsCheck
{
    
    private $delimiter = "\t";
    
    // Using the class's constructor to decide which action to perform
    public function __construct($function = null)
    {
        switch ($function) {
            case 'typeDatabase':
                $this->type_Database(); /* update database */
                break;

            case 'typeLineData':
                $this->type_Line_Data(); /* Handle Line Data */
                break;

            default:
                $this->typeMapsCheck(); /* intial case*/
                break;

        }
    }

    private function typeMapsCheck()
    {
        global $config;
        include($config['root_dir'] . 'theme/admin_header.php');

        echo "<h2> Enter/Update Maps Information: Validation</h2>";

        $this->type_Maps();

        $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
    }
	
    private function type_Maps()
    {
	?>
        <script type="text/javascript" src="curator_data/input_maps.js"></script>
	<style type="text/css">
			th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
			table {background: none; border-collapse: collapse}
			td {border: 0px solid #eee !important;}
			h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
		</style>
		
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		
		
<?php


        global $mysqli;
        $row = loadUser($_SESSION['username']);

	$username = $row['name'];
        $username = preg_replace('/\s+/', '', $username);
	
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'] == ""){
		error(1, "No File Uploaded");
		exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
	}
	else {
	
	  $mapset_name = $_POST['mapset_name'];	
	  $mapset_prefix = $_POST['mapset_prefix'];	
	  $comments = $_POST['comments'];	
	  $species = $_POST['species'];
	  $map_type = $_POST['map_type'];
	  $map_unit = $_POST['map_unit'];
	  
	  //echo "comments". $comments;
	  
	  //echo "mapset name". $mapset_name;
		$uploadfile=$_FILES['file']['name'];
		$uftype=$_FILES['file']['type'];
		if (strpos($uploadfile, ".txt") === FALSE) {
			error(1, "Expecting a text file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
		
		
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) 
			{


    			/* start reading the file */
    			
    			
    		$mapfile = $target_path.$uploadfile;
				
			//	echo " map file name". $mapfile;
					
    			/* Part 1: read in header data*/
        $handledata = fopen($mapfile, "r");
        $data = fgetcsv($handledata, 0, "\t");
        $header = $data;	// read first line
        
          
			// echo "testing the marker value". $test."<br/>";
        
        $m_idx = 1.0 * array_search("Marker", $header);	// numeric typecasting
        $c_idx = 1.0 * array_search("Chrom", $header);
        $arm_idx = 1.0 * array_search("Arm", $header);
        $start_idx = 1.0 * array_search("Start_pos", $header);
        $end_idx = 1.0 * array_search("End_pos", $header);
         $bin_idx = 1.0 * array_search("Bin", $header);
         
         $m_c_idx = $m_idx + $c_idx;
         $m_Start_idx = $m_idx + $start_idx;
         
         if(($m_c_idx == 0) || ($m_Start_idx == 0))
         {
         	echo "One or more of the required columns (Marker, Chrom,Start_pos) are  missing in the input data file. Please fix them and try again."."<br/>";
         	exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
         }
 
        //echo $m_idx.$c_idx.$arm_idx.$start_idx.$end_idx.$bin_idx."\n";
        
        
        
       // echo "mapset name". $mapset_name."<br/>";
    			
    		// get map ID
        $sql = "SELECT mapset_uid FROM mapset WHERE mapset_name = '$mapset_name'";
        $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        $rdata = mysqli_fetch_assoc($res);
        $mapset_uid=$rdata['mapset_uid'];
				
			//	echo "checking for the map id". $mapset_uid. "<br/>";
				
				
				
				
				
				
				// Read in all data and store in arrays, If a new mapset, then initialize all maps"
        
				$row = -1;
				
        while (($data = fgetcsv($handledata, 0, "\t")) !== FALSE) {
                $num = count($data);		// number of fields
                $row++;				// number of lines
                if ($row>50) {
                    break;
                }
                //print_r($data);
                $marker[] .= trim($data[$m_idx]);
                $chrom[] .= trim($data[$c_idx]);
                //print_r($marker);
                
                $start_pos[] .=trim($data[$start_idx]);
                if (empty($end_idx)){$end_pos[] = NULL;
			} else {$end_pos[] .=trim($data[$end_idx]);}
		if (empty($arm_idx)){$arm[] = NULL;
			} else {$arm[] .= trim($data[$arm_idx]);}
		if (empty($bin_idx)){$bin[] = NULL;
			} else {$bin[] .= trim($data[$bin_idx]);
}
		//set default values
                if (empty($chrom[$row]))
                    $chrom[$row] = "UNK";
                if (empty($arm[$row]))
                    $arm[$row] = NULL;
                if (empty($start_pos[$row]))
                    $start_pos[$row] = 0;
                if (empty($end_pos[$row]))
                    $end_pos[$row]= $start_pos[$row];
                if (empty($bin[$row]))
                    $bin[$row] = NULL;
		//echo $marker[$row].' '.$chrom[$row].' '.$arm[$row],"\n";    
	}
	fclose($handledata);
	?>
	<h3>Here is a sample of the first 50 lines of data from the uploaded Input Data File</h3>
				
				
			<table>
        <thead>
        <tr> 
				<th style="width: 100px;"> Marker </th>
				<th style="width: 100px;"> Chrom </th>
				<th style="width: 100px;"> Arm </th>
				<th style="width: 100px;"> Start Pos </th>
				<th style="width: 100px;"> End Pos </th>
				<th style="width: 100px;"> Bin </th>
				</tr>
				</thead>
				<tbody style="padding: 0; height: 300px; overflow: scroll;border: 1px solid #5b53a6;">
				
				<?php
				for ($i = 0; $i < $row; $i++)
				{
			
			?>
			
			<tr>
			<td style="width: 100px;"> 
			<?php echo $marker[$i] ?>
			</td>
			<td style="width: 100px;"> 
			<?php echo $chrom[$i] ?>
			</td>
			<td style="width: 100px;"> 
			<?php echo $arm[$i] ?>
			</td>
			<td style="width: 100px;"> 
			<?php echo $start_pos[$i] ?>
			</td>
			<td style="width: 100px;"> 
			<?php echo $end_pos[$i] ?>
			</td>
			<td style="width: 100px;"> 
			<?php echo $bin[$i] ?>
			</td>
			</tr>
			
		<?php
			} /* end of for loop */
		?>
		</tbody>
		</table>
						
		<?php		
				
				
				
				if (empty($mapset_uid))
				{
					echo "<br/> <br/>" ."Mapset  name&nbsp;&nbsp;" ."<b>" . $mapset_name . "</b>" . "&nbsp;&nbsp; does not exist and will be created."."<br/><br/>";
				}
				
				else
				{
					echo "<br/> <br/>" ."Mapset &nbsp;&nbsp;" ."<b>" . $mapset_name . "</b>" . " &nbsp;&nbsp;already exists and will be updated."."<br/><br/>";
				}
				?>
				
		<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $mapfile?>','<?php echo $mapset_name?>','<?php echo $mapset_prefix?>','<?php echo $comments?>','<?php echo $species?>','<?php echo $map_type?>','<?php echo $map_unit?>')"/>
    <input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
				
			<?php  				
		     
   			}
				 
				 else {
    				error(1,"There was an error uploading the file, please try again!");
    				print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
							}
			
		}
	
	}

	} /* end of type_GenoType_Display function*/
	
    private function type_Database()
    {
        global $mysqli;
        global $config;
	include $config['root_dir'] . 'theme/admin_header.php';
	
	$mapfilename = $_GET['file_name'];
	$mapset_name = $_GET['mapset_name'];
	$mapset_prefix = $_GET['mapset_prefix'];
	$comments = $_GET['comments'];
	$species = $_GET['species'];
	$map_type = $_GET['map_type'];
	$map_unit = $_GET['map_unit'];
	
        $handledata = fopen($mapfilename, "r");
        if ($handledata) {
            $data = fgetcsv($handledata, 0, "\t");
        } else {
            die("Can't open file $mapfilename\n");
        }

        $header = $data;	// read first line
        $m_idx = 1.0 * array_search("Marker", $header);	// numeric typecasting
        $c_idx = 1.0 * array_search("Chrom", $header);
        $arm_idx = 1.0 * array_search("Arm", $header);
        $start_idx = 1.0 * array_search("Start_pos", $header);
        $end_idx = 1.0 * array_search("End_pos", $header);
        $bin_idx = 1.0 * array_search("Bin", $header);
 
    //  echo $m_idx.$c_idx.$arm_idx.$start_idx.$end_idx.$bin_idx."\n";
        
        // get map ID
	$sql = "SELECT mapset_uid FROM mapset WHERE mapset_name = '$mapset_name'";
	$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
	$rdata = mysqli_fetch_assoc($res);
	$mapset_uid=$rdata['mapset_uid'];
//	echo $sql.'UID '.$mapset_uid."\n";
	
	
	
	// Make new mapset if it one does not exist yet
	 if (empty($mapset_uid)) {
		$new_map = "TRUE";
		$sql = "INSERT INTO mapset (mapset_name, species, map_type, map_unit, comments, updated_on, created_on)
			VALUE ('$mapset_name','$species','$map_type','$map_unit','$comments', NOW(),NOW())";
		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		$sql = "SELECT mapset_uid FROM mapset WHERE mapset_name = '$mapset_name'";

		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		$rdata = mysqli_fetch_assoc($res);
		$mapset_uid=$rdata['mapset_uid'];
	 } else {
		$new_map ="FALSE";
		$sql = "UPDATE mapset SET updated_on=NOW()";// update date
		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));

		// get maps in the mapset
		$sql = "SELECT map_uid, map_name FROM map WHERE mapset_uid = $mapset_uid";
		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		$cnt = 0;
		while ($row = mysqli_fetch_array($res)){
                        $map_uid[$row['map_name']] = $row['map_uid'];
			$map_name[] =$row['map_name'];			
			echo $map_uid[$cnt]."".$map_name[$cnt]."\n";
			$cnt++;
		}
	 }
	


/* reading data from the file */

       
			  // Read in all data and store in arrays, If a new mapset, then initialize all maps"
        $row = -1;
        while (($data = fgetcsv($handledata, 0, "\t")) !== FALSE) {
                $num = count($data);		// number of fields
                $row++;				// number of lines
                //print_r($data);
                $marker[] .= trim($data[$m_idx]);
                $chrom[] .= trim($data[$c_idx]);
                $start_pos[] .=trim($data[$start_idx]);
                if (empty($end_idx)){$end_pos[] = NULL;
			} else {$end_pos[] .=trim($data[$end_idx]);}
		if (empty($arm_idx)){$arm[] = NULL;
			} else {$arm[] .= trim($data[$arm_idx]);}
		if (empty($bin_idx)){$bin[] = NULL;
			} else {$bin[] .= trim($data[$bin_idx]);
}
		//set default values
                if (empty($chrom[$row]))
                    $chrom[$row] = "UNK";
                if (empty($arm[$row]))
                    $arm[$row] = NULL;
                if (empty($start_pos[$row]))
                    $start_pos[$row] = 0;
                if (empty($end_pos[$row]))
                    $end_pos[$row]= $start_pos[$row];
                if (empty($bin[$row]))
                    $bin[$row] = NULL;
		//echo $marker[$row].' '.$chrom[$row].' '.$arm[$row],"\n";    
	}
	fclose($handledata);

/* end of reading data from the file */


/* handling new mapset */

	// If new mapset, then find all the chromosome names to make a newmapset
	// then find min and max position
	
	if ($new_map == 'TRUE') {		
		$map_name = array_unique($chrom);
		echo "size map".sizeof($map_name)."\n";
		//print_r($map_name);
		//print_r($marker);
		foreach ($map_name as $cstr) {
		//	echo "into loop".$cstr." i'm in first loop"."\n";
			$chrom_vals = array_keys(find($cstr, $chrom));
			//print_r($chrom_vals);
			$minval = 99999;
			$maxval = -1;
			for ($cnt=0;$cnt<count($chrom_vals);$cnt++){
				$indval = $chrom_vals[$cnt];
				if ($start_pos[$indval]>$maxval){
					$maxval = $start_pos[$chrom_vals[$cnt]];
				}
				if ($start_pos[$indval]<$minval){
					$minval = $start_pos[$chrom_vals[$cnt]];
				}
			}
		//	echo "i'm in second loop". count($start_pos)."into loop".$cstr." ".count($chrom_vals)." ".$minval." ".$maxval. "end of second loop"."\n";
			$mapnametmp = $mapset_prefix."_".$cstr;
			$sql = "INSERT INTO map (mapset_uid, map_name, map_start, map_end, updated_on, created_on)
				VALUES ($mapset_uid, '$mapnametmp', $minval,$maxval, NOW(),NOW())";
			$res = mysqli_query($mysqli, $sql);
			if (!$res) { //catch it and go on if duplicate key message comes up for the first time through a new map
				$message  = 'Invalid query: ' . mysqli_error($myslqi) . "i have an error"."\n";
				$message .= 'Whole query: ' . $query. "\n";
				if (strpos($query,"uplicate")) {echo $message;
				} else {die($message);}
			}
                        $map_chr_new[$cstr] = $mapnametmp;
		}	
		
		/* map uid's exist only for the existing mapsets so for new ones we need to read it from the map table after we create */
	        echo "List of map entries<br>\n";	
		$sql = "SELECT map_uid, map_name FROM map WHERE mapset_uid = $mapset_uid";
		$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
		while ($row = mysqli_fetch_array($res)){
			$map_uid[$row['map_name']] = $row['map_uid'];
			$map_name_new[] =$row['map_name'];			
			echo $row['map_uid'] ." ".$row['map_name']."<br>\n";
		}
		
	} else {
            $map_name = array_unique($chrom);
            foreach ($map_name as $cstr) {
                $mapnametmp = $mapset_prefix."_".$cstr;
                $map_chr[$cstr] = $mapnametmp;
             }
        }
		

/* end of handling new mapset */

/* begin inserting main data */


        /* Begin main insertion loop. Data is already read out of file.*/
        $sql_fk="SET FOREIGN_KEY_CHECKS = 0";
	$result_fk=mysqli_query($mysqli, $sql_fk) or die(mysqli_error($mysqli));


	for ($cnt=0;$cnt<count($marker);$cnt++){  

	  /* Check if marker is a synonym. If not found, then check name. */
	  $sql ="SELECT ms.marker_uid FROM  marker_synonyms AS ms WHERE ms.value='$marker[$cnt]'";
	  $res = mysqli_query($mysqli, $sql) or die("Database Error: Marker synonym lookup - ". mysqli_error($mysqli)."<br>$sql");
	  $rdata = mysqli_fetch_assoc($res);
	  $marker_uid=$rdata['marker_uid'];
	  if (empty($marker_uid)) {
	    $sql = "SELECT m.marker_uid FROM  markers AS m WHERE m.marker_name ='$marker[$cnt]'";
	    $res = mysqli_query($mysqli, $sql) or die("Database Error: Marker lookup - ". mysqli_error($mysqli)."<br>$sql");
	    if (mysqli_num_rows($res) < 1) {
	      echo "<b>Error</b>: marker <b>\"$marker[$cnt]\"</b> not found.<p>";
	      exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">");	  
	    } 
	    else {
	      $rdata = mysqli_fetch_assoc($res);
	      $marker_uid=$rdata['marker_uid'];
	    }
	  }
		
		// Find map_uid for marker using the chromosome name
															 
		if ($new_map == 'TRUE')
		{
                    $tmp = $chrom[$cnt];
                    $name = $map_chr_new[$tmp]; 
		}

		if ($new_map == 'FALSE')
		{
                    $tmp = $chrom[$cnt];
                    $name = $map_chr[$tmp];
                }

                if (isset($map_uid[$name])) {
                    $mmap_uid = $map_uid[$name];
                } else {
                    echo "Error $name<br>\n";
		}
                
				
		// store in markers_in_maps
                // If this mapset, marker combination exists already, then update only
                //see if marker_uid in the current mapset
                
    if(!empty($map_uid))
    {
		$map_string = implode(",",$map_uid); /* map uid's exist only for existing mapsets */
		//echo "map string". $map_string . "<br/>";
		//echo"marker uid" . $marker_uid . "<br/>";
		
		}
		if(empty($map_uid))
		{
			//echo"i'm in empty";
			$map_string = "";
		}
		
		$sql = "SELECT mim.markers_in_maps_uid as mimu, count(mim.markers_in_maps_uid) as cntm, mim.map_uid,
			mim.marker_uid
			FROM markers_in_maps as mim
			WHERE mim.marker_uid=$marker_uid AND mim.map_uid IN ($map_string)
			GROUP BY (markers_in_maps_uid)";
			
                $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
                $rdata = mysqli_fetch_assoc($res);
                $mim_uid=$rdata['mimu'];
                
		if (empty($mim_uid)) {
		
		 //if not in a current map then insert a new record
                    //echo "in insert";

		  if (empty($mmap_uid)) {
		    echo "No Map Set Prefix entered.<br>";
                    echo "new_map = $new_map<br>\n";
                    echo "map_idx = $map_idx<br>\n";
		    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-2); return;\">");	  
		  }
                  
                    $sql_beg = "INSERT INTO markers_in_maps (marker_uid,map_uid, start_position, end_position,chromosome,";
                    $sql_mid = "updated_on,created_on) VALUES ($marker_uid,$mmap_uid,$start_pos[$cnt],$end_pos[$cnt],'$chrom[$cnt]',";
                    $sql_end = "NOW(),NOW())";
                    if ($arm[$cnt]!== NULL){
			$sql_beg .= "arm,";
			$sql_mid .= "'$arm[$cnt]',";
		    }
		    if ($bin[$cnt]!== NULL){
			$sql_beg .= "bin_name,";
			$sql_mid .= "'$bin[$cnt]',";
		  	}
			$sql = $sql_beg.$sql_mid.$sql_end;
                } else { 
                        $sql_beg = "UPDATE markers_in_maps SET map_uid =$mmap_uid,
                        start_position=$start_pos[$cnt], end_position=$end_pos[$cnt],chromosome='$chrom[$cnt]',";
                        $sql_end = "updated_on=NOW() WHERE markers_in_maps_uid=$mim_uid";
                        if ($arm[$cnt]!== NULL){
				$sql_beg .= "arm='$arm[$cnt]',";
                        }
                        if ($bin[$cnt]!== NULL) {
                            $sql_beg .= "bin_name='$bin[$cnt]',";
                        }
                        $sql = $sql_beg.$sql_end;
                    }
             mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli) . "<br>Command was:<br><pre>$sql</pre>");    
	}
	
	/* end inserting main data */

$sql_fk="SET FOREIGN_KEY_CHECKS = 1";
	$result_fk=mysqli_query($mysqli, $sql_fk) or die(mysqli_error($mysqli));


 echo " <b>The Data is inserted/updated successfully </b>";
	echo"<br/><br/>";
	?>
	<a href="./curator_data/input_map_upload.php"> Go Back To Main Page </a>
	<?php
	
		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	
	} /* end of function type_database */


/**
     * Takes a needle and haystack (just like in_array()) and does a wildcard search on it's values.
     *
     * @param    string        $string        Needle to find
     * @param    array        $array        Haystack to look through
     * @result    array                    Returns the elements that the $string was found in
     */
    function find ($string, $array = array ())
    {   
        foreach ($array as $key => $value) {
            unset ($array[$key]);
            if (strpos($value, $string) !== false) {
                $array[$key] = $key;
            }
        }       
        return $array;
    } 

} /* end of class */

