<?php

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
ini_set("auto_detect_line_endings", true);

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

new PedigreeCheck($_GET['function']);

class PedigreeCheck
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
                $this->typePedigreeCheck(); /* intial case*/
                break;
        }
    }

    private function typePedigreeCheck()
    {
        global $config;
        include $config['root_dir'] . 'theme/admin_header.php';

        echo "<h2> Enter/Update Pedigree Information: Validation</h2>";

	$this->type_Pedigree_Information();

	$footer_div = 1;
        include $config['root_dir'].'theme/footer.php';
    }
	
	
	private function type_Pedigree_Information()
	{
            global $mysqli;
	?>
	<script type="text/javascript">
	
	function update_database(filepath, filename, username)
	{
			
			
			var url='<?php echo $_SERVER['PHP_SELF'];?>?function=typeDatabase&pedigreedata=' + filepath + '&file_name=' + filename + '&user_name=' + username;
	
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
		
		<style type="text/css">
                   table.marker
                   {background: none; border-collapse: collapse}
                    th.marker
                    { background: #5b53a6; color: #fff; padding: 5px 0; border: 0; }
                    
                    td.marker
                    { padding: 5px 0; border: 0 !important; }
                </style>
		
		
		
<?php




  $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'] == ""){
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
		
		$uploadfile=$_FILES['file']['name'];
		$uftype=$_FILES['file']['type'];
		if (strpos($uploadfile, ".txt") === FALSE) {
			error(1, "Expecting a text file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'], $target_path.$uploadfile)) 
			{
			
			
			$datafile = $target_path.$uploadfile;
			
			 $handle = fopen($datafile, "r");
        $header = fgetcsv($handle, 0, "\t");
        
        // Set up column indices; all columns are required
        $capline_idx = implode(find("CAPLINE", $header),"");
        $line_idx = 1 * array_search("LINE", $header);;
        $par1_idx = implode(find("PARENT_1", $header),"");
        $par2_idx = implode(find("PARENT_2", $header),"");
        $con1_idx = implode(find("CONTRIB_1", $header),"");
        $con2_idx = implode(find("CONTRIB_2", $header),"");
        $self1_idx = implode(find("SELFING_1", $header),"");
        $self2_idx = implode(find("SELFING_2", $header),"");
        $pedstring_idx = implode(find("pedigree", $header),"");
        
	
	
				
			
	
	
	
    // Connect to the database
    		//connect_dev();	/* connecting to development database */
    		
    		
    		//  Step 2. Read in data, a line at a time  
        $run = 0;
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            if ($capline_idx!==FALSE) {$capline = trim($data[$capline_idx]);}
            $line = trim($data[$line_idx]);
            
            $line_data[] = $line;
            
            
          
            
            $par1 = trim($data[$par1_idx]);//parent 1
            
          
            
            
            $parent1_data[] = $par1;
            
            
            $par2 = trim($data[$par2_idx]);//parent 2
            $parent2_data[] = $par2;
            
            $con1 = trim($data[$con1_idx]);//parent 1 contribution
            
            //echo " the contribution parent1 for testing is ************". $con1."<br>";
            
            
            
            
            $con2 = trim($data[$con2_idx]);//parent 2 contribution
            //echo " the contribution parent1 for testing is ************". $con2."<br>";
            
            
            $self1 = trim($data[$self1_idx]);//parent 1 selfing
            
            
            $self2 = trim($data[$self2_idx]);//parent 2 selfing
            
            
            $pedstring = addslashes(trim($data[$pedstring_idx]));//pedigree string
            $pedstring_data[] = $pedstring;
            
              //echo " the pedigree for testing is ************". $pedstring . "<br>";
            
            //check for empty values and replace with defaults if needed
            $e1=strlen($par1);$e2=strlen($par2);
            $ec1=strlen($con1);$ec2=strlen($con2);
            $es1=strlen($self1);$es2=strlen($self2);
            //echo $le."blank".$e1."blank".$e2."\n";
            //set defaults if not given
            if ($ec1==0){
                $con1 = 0.5;
            }
            if ($ec2==0){
                $con2 = 0.5;
            }
            if ($es1==0){
                $self1 = "FN";
            }
            if ($es2==0){
                $self2 = "FN";
            }
            
            $contribution1_data[] = $con1;
            $contribution2_data[] = $con2;
            $selfing1_data[] = $self1;
            $selfing2_data[] = $self2;
            
            
            
            
            
            
           // echo $line." ".$par1." ".$par2." ".$con1." ".$con2." ".$self1." ".$self2."\n";
            
            /*  Step 3. Line name validation
    a. Check if line names (inbred, parent 1, parent 2) are
    in the line records table. */

            $line_uid =get_lineuid($line);
            if ($line_uid===FALSE) {
            
            			$line_insert_data[] = $line;
              //  echo " line ".$line." will be added to lines table\n";
            }else {
                    $line_uid = implode(",",$line_uid);
            }
            
            if (($e1!==0)AND($par1!=="TBD")AND($par1!==NA)) {
                $par1_uid =get_lineuid($par1);
                if ($par1_uid===FALSE) {
                
                		$parent1_insert_data[] = $par1;
                   // echo " Parent 1 ".$par1." will be added to lines table\n";
                }else {
                    $par1_uid = implode(",",$par1_uid);
                }
            }
            if (($e2!==0)AND($par2!=="TBD")AND($par2!==NA)) {
                $par2_uid =get_lineuid($par2);
                if ($par2_uid===FALSE) {
                
                		$parent2_insert_data[] = $par2;
                   // echo " Parent 2 ".$par2." will be added to lines table\n";
                }else {
                    $par2_uid = implode(",",$par2_uid);
                }
            }
           
            //echo $line_uid." ".$par1_uid." ".$par2_uid."\n"; 


/* 4. Check if information for this inbred/parent combo is already in table, Skip if an individual parent is blank
    If yes, then check if changed, if no change, do nothing
    If not in table, then add to pedigree relations table */
            if (($e1!==0)AND($par1!=="TBD")AND($par1!=="NA")AND($par1_uid!==FALSE)AND($line_uid!==FALSE)) {
                $sql_ped = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM pedigree_relations
                                WHERE line_record_uid=$line_uid AND parent_id =$par1_uid");
                $n_match = mysqli_fetch_assoc($sql_ped);
    
    
               if ($n_match['cnt']==1) {
                    //echo "Line ".$line." Parent 1 ".$par1." in pedigree_relations table\n";
                } elseif ($n_match['cnt']>1){
                
                				$run++;
                         echo "ERROR: multiple pedigrees for same parent-child set, parent:".$line_uid." ".$par1_uid."\n";
                }
            }
            
             // repeat for parent 2
            if (($e2!==0)AND($par2!=="TBD")AND($par2!=="NA")AND($par2_uid!==FALSE)AND($line_uid!==FALSE)) {
                $sql_ped = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM pedigree_relations
                               WHERE line_record_uid=$line_uid AND parent_id =$par2_uid");
               $n_match = mysqli_fetch_assoc($sql_ped);
               if ($n_match['cnt']==1) {
                   //echo "Line ".$line." Parent 2 ".$par2." in pedigree_relations table\n";
               }elseif ($n_match['cnt']>1){
               
               					$run++;
                        echo "ERROR: multiple pedigrees for same parent-child set, parent:".$line_uid." ".$par2_uid."\n";
               }
            }
            unset($line_uid);
            unset($par1_uid);
            unset($par2_uid);
        }
        
      //  echo "Number of lines are". count($line_data);
        //echo "Number of parents1 are". count($parent1_data);
         //echo "Number of parents2 are". count($parent2_data);
        // echo "Number of contribution1 are". count($contribution1_data);
        // echo "Number of contribution2 are". count($contribution2_data);
         
         
         
         
         
        ?>
        
    		
    	<h3>We are reading following data from the uploaded Input Data File</h3>
    	
    	
		
		<table >
	<tr>
	<th style="width: 150px;" class="marker">Line Name</th>
	<th style="width: 150px;" class="marker" >Parent1 </th>
	<th style="width: 150px;" class="marker" >Parent2 </th>
	<th style="width: 80px;" class="marker" >CONTRIB_1 </th>
	<th style="width: 80px;" class="marker" >CONTRIB_2 </th>
	<th style="width: 80px;" class="marker" >SELFING_1 </th>
	<th style="width: 80px;" class="marker" >SELFING_2 </th>
	<th style="width: 130px;" class="marker" >pedigree </th>
	
	</tr>
	</table>	
    		
   	<div id="test" style="padding: 0; height: 200px; width: 900px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php
				for ($i = 0; $i < max(count($contribution1_data),count($contribution2_data),count($line_data),count($parent1_data),count($parent2_data),count($selfing1_data),count($selfing2_data),count($pedstring_data)); $i++)
				{
			
			?>
			
			<tr>
			<td style="width: 150px;">
			<?php echo $line_data[$i]?>
			</td> 
			<td style="width: 150px;">
			<?php echo $parent1_data[$i] ?>
			</td>
			<td style="width: 150px;">
			<?php echo $parent2_data[$i] ?>
			</td> 
			<td style="width: 80px;">
			<?php echo $contribution1_data[$i] ?>
			</td> 
			<td style="width: 80px;">
			<?php echo $contribution2_data[$i] ?>
			</td> 
			<td style="width: 80px;">
			<?php echo $selfing1_data[$i] ?>
			</td> 
			<td style="width: 80px;">
			<?php echo $selfing2_data[$i] ?>
			</td> 
			<td style="width: 130px;">
			<?php echo $pedstring_data[$i] ?>
			</td> 
			</tr>
			
			<?php
				}/* end of for loop */
			?>
			
			</table>
			</div>		
    		
    		
    	
    	<h3>Following Information will be inserted/updated</h3>
 <table >
	<tr>
	<th style="width: 100px;" class="marker">Line Name</th>
	<th style="width: 125px;" class="marker" >Parent1 </th>
	<th style="width: 125px;" class="marker" >Parent2 </th>
	</tr>
	</table>
	
	
	<div id="test" style="padding: 0; height: 200px; width: 350px;  overflow: scroll;border: 1px solid #5b53a6;">
			<table>
			<?php
				for ($i = 0; $i < max(count($line_insert_data),count($parent1_insert_data),count($parent2_insert_data)); $i++)
				{
			
			?>
			
			<tr>
			<td style="width: 100px;">
			<?php echo $line_insert_data[$i] ?>
			</td> 
			<td style="width: 120px;">
			<?php echo $parent1_insert_data[$i] ?>
			</td>
			<td style="width: 120px;">
			<?php echo $parent2_insert_data[$i] ?>
			</td>
			</tr>
			<?php
				}/* end of for loop */
			?>
			
			</table>
			</div>
			
		<?php
			if($run!=0)
			{
				echo "<br><br>";
			echo " <b>Please fix these errors before you insert into/update the database </b><br/><br/>";
				
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
				}
				
			if($run == 0)
			
			{
				echo "<br><br>";
			?>
				<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $datafile?>','<?php echo $uploadfile?>','<?php echo $username?>' )"/>
    			<input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
			
			<?php
			
			}
		
		   			
   			}
				 
				 else {
    				error(1,"There was an error uploading the file, please try again!");
							}
							
				
		}
	
	}
	
	
	
	} /* end of type_Pedigree_Information function*/
	
	private function type_Database()
	{
	
	global $config;
        global $mysqli;
		include($config['root_dir'] . 'theme/admin_header.php');

	
	$datafile = $_GET['pedigreedata'];
	$filename_old = $_GET['file_name'];
	$filename = $filename_old.rand();
	
	$username = $_GET['user_name'];
	
	
/* This function does a quick add of a line to the line records table */        
        function addline($line,$pedstring){
            global $mysqli;
            $sql = "INSERT INTO line_records (line_record_name,pedigree_string,updated_on,created_on)
                    VALUES ('$line',";
            if (strlen($pedstring!==0)) {
                $sql = $sql."'$pedstring',";
            }
            $sql = $sql."NOW(), NOW())";
            $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            return $result;
        }	
	
	
	/* This function updates the line pedigree string in the line records table */        
	function updatelineped($line_uid,$pedstring){
            global $mysqli;
            $sql = "UPDATE line_records SET";

            if (strlen($pedstring!==0)) {
                $sql = $sql." pedigree_string='$pedstring', ";
            }
            $sql = $sql." updated_on=NOW()
                    WHERE line_record_uid=$line_uid";

            $result=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            return $result;
        }
	
	
	
	
	
	
	
	$handle = fopen($datafile, "r");
        $header = fgetcsv($handle, 0, "\t");
        
        // Set up column indices; all columns are required
        $capline_idx = implode(find("CAPLINE", $header),"");
        $line_idx = 1 * array_search("LINE_NAME", $header);;
        $par1_idx = implode(find("PARENT_1", $header),"");
        $par2_idx = implode(find("PARENT_2", $header),"");
        $con1_idx = implode(find("CONTRIB_1", $header),"");
        $con2_idx = implode(find("CONTRIB_2", $header),"");
        $self1_idx = implode(find("SELFING_1", $header),"");
        $self2_idx = implode(find("SELFING_2", $header),"");
        $pedstring_idx = implode(find("pedigree", $header),"");
        
	
    
    
  //  Step 2. Read in data, a line at a time  
       
        while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            if ($capline_idx!==FALSE) {$capline = trim($data[$capline_idx]);}
            $line = trim($data[$line_idx]);
            $par1 = trim($data[$par1_idx]);
            $par2 = trim($data[$par2_idx]);
            $con1 = trim($data[$con1_idx]);
            $con2 = trim($data[$con2_idx]);
            $self1 = trim($data[$self1_idx]);
            $self2 = trim($data[$self2_idx]);
            $pedstring = addslashes(trim($data[$pedstring_idx]));
            
            //check for empty values and replace with defaults if needed
            $e1=strlen($par1);$e2=strlen($par2);
            $ec1=strlen($con1);$ec2=strlen($con2);
            $es1=strlen($self1);$es2=strlen($self2);
            
            //set defaults if not given
            if ($ec1==0){
                $con1 = 0.5;
            }
            if ($ec2==0){
                $con2 = 0.5;
            }
            if ($es1==0){
                $self1 = "FN";
            }
            if ($es2==0){
                $self2 = "FN";
            }

        
/*  Step 3. Line name validation
    a. Check if line names (inbred, parent 1, parent 2) are
    in the line records table.
    b. If yes, then get the line_record_uids, also update pedigree strings
    c. If no, add this line and its pedigree string to the line record table.*/
            $line_uid =get_lineuid($line);
            if ($line_uid===FALSE) {
                $result = addline($line,$pedstring);
                $line_uid =get_lineuid($line);
            } else {
                updatelineped(implode(",",$line_uid),$pedstring); //add in pedigree string
            }
            $line_uid = implode(",",$line_uid);
            
            if (($e1!==0)AND($par1!=="TBD")AND($par1!==NA)) {
                $par1_uid =get_lineuid($par1);
                if ($par1_uid===FALSE) {
                    $result = addline($par1,"");
                    $par1_uid =get_lineuid($par1);
                }
                $par1_uid = implode(",",$par1_uid);
          }
            if (($e2!==0)AND($par2!=="TBD")AND($par2!==NA)) {
                $par2_uid =get_lineuid($par2);
                if ($par2_uid===FALSE) {
                    $result = addline($par2,"");
                    $par2_uid =get_lineuid($par2);
                }
                $par2_uid = implode(",",$par2_uid);
            }
           
           


/* 4. Check if information for this inbred/parent combo is already in table, Skip if either parent is blank
    If yes, then check if changed, if no change, do nothing
    If not in table, then add to pedigree relations table */
            if (($e1!==0)AND($par1!=="TBD")AND($par1!=="NA")) {
                $sql_ped = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM pedigree_relations
                                WHERE line_record_uid=$line_uid AND parent_id =$par1_uid");
                $n_match = mysqli_fetch_assoc($sql_ped);
    
               if ($n_match['cnt']==1) {
                   // echo "Line ".$line." Parent 1 ".$par1." is already in the table\n";
                    $sql_upaddped = ("UPDATE pedigree_relations
                                 SET contribution=$con1,selfing = '$self1',updated_on=NOW()
                                 WHERE line_record_uid=$line_uid AND parent_id =$par1_uid");
                    $result=mysqli_query($mysqli, $sql_upaddped) or die(mysqli_error($mysqli));
                } elseif ($n_match['cnt']==0) {
                    $sql_upaddped = ("INSERT pedigree_relations (line_record_uid,parent_id,contribution,selfing,updated_on,created_on)
                                 VALUES ($line_uid,$par1_uid,$con1,'$self1',NOW(),NOW())");
                    $result=mysqli_query($mysqli, $sql_upaddped) or die(mysqli_error($mysqli));
                } else{
                      //   echo "multiple pedigree for same parent-child set, parent:".$line_uid." ".$par1_uid."\n";
                }
            }
            
             // repeat for parent 2
            if (($e2!==0)AND($par2!=="TBD")AND($par2!=="NA")) {
                $sql_ped = mysqli_query($mysqli, "SELECT COUNT(*) AS cnt FROM pedigree_relations
                               WHERE line_record_uid=$line_uid AND parent_id =$par2_uid");
               $n_match = mysqli_fetch_assoc($sql_ped);
               if ($n_match['cnt']==1) {
                  // echo "Line ".$line." Parent 2 ".$par2." is already in the table\n";
                   $sql_upaddped = ("UPDATE pedigree_relations
                                SET contribution=$con2,selfing = '$self2',updated_on=NOW()
                                WHERE line_record_uid=$line_uid AND parent_id =$par2_uid");
                   $result=mysqli_query($mysqli, $sql_upaddped) or die(mysqli_error($mysqli));
               } elseif ($n_match['cnt']==0) {
                   $sql_upaddped = ("INSERT pedigree_relations (line_record_uid,parent_id,contribution,selfing,updated_on,created_on)
                                VALUES ($line_uid,$par2_uid,$con2,'$self2',NOW(),NOW())");
                   $result=mysqli_query($mysqli, $sql_upaddped) or die(mysqli_error($mysqli));
   
               }else{
                      //  echo "multiple pedigree for same parent-child set, parent:".$line_uid." ".$par2_uid."\n";
               }
            }
            unset($line_uid);
            unset($par1_uid);
            unset($par2_uid);
        }
        
     $sql = "INSERT INTO input_file_log (file_name,users_name)
										VALUES('$filename', '$username')";
					
					
	$ped_table=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));   
        
	echo " <b>The Data is inserted/updated successfully </b>";
	echo"<br/><br/>";
	?>
	<a href="./curator_data/input_pedigree_upload.php"> Go Back To Main Page </a>
	
	<?php
		$footer_div = 1;
    include $config['root_dir'].'theme/footer.php';
		
	} /* end of function type_database */
	
} /* end of class */

