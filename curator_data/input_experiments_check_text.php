<?php
/**
 * uploading it to main server
 *
 * 02/01/2011 JLee  Fix indentations and fatal error not presenting data
 * 02/01/2011 JLee  Fix problem with line with the value of 0
 * 12/14/2010 JLee  Change to use curator bootstrap
 */

require 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
require_once "../lib/Excel/reader.php"; // Microsoft Excel library

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new LineNames_Check($_GET['function']);

class LineNames_Check
{
    private $delimiter = "\t";
    // Using the class's constructor to decide which action to perform
	public function __construct($function = null)
	{	
		switch($function)
		{
			case 'typeDatabase':
				$this->type_Database(); /* update database */
				break;
						
			default:
				$this->typeExperimentCheck(); /* intial case*/
				break;
		}	
	}


    private function typeExperimentCheck() {
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2> New Means Data Validation</h2>"; 
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	
	private function type_Experiment_Name()
	{
	?>
	<script type="text/javascript">
	
	function update_database(filepath, filename, username, rawdatafile) {
					
		var url='<?php echo $_SERVER[PHP_SELF];?>?function=typeDatabase&expdata=' + filepath + '&file_name=' + filename + '&user_name=' + username + '&raw_data_file=' + rawdatafile;
	
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
			
<?php
	 // dem 3dec10: Must include these files again, don't know why.
	require 'config.php';

    $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	$username=$row['name'];
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
	
	$raw_path= "../raw/phenotype/".$_FILES['file']['name'][1];
	copy($_FILES['file']['tmp_name'][1], $raw_path);
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'][0] == "") {
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
		
		$uploadfile=$_FILES['file']['name'][0];
		$rawdatafile = $_FILES['file']['name'][1];
	
        //	echo "uploaded file" .$uploadfile. "<br/>". "raw file" .$rawdatafile;
		$uftype=$_FILES['file']['type'][0];
		if (strpos($uploadfile, ".txt") === FALSE) {
			error(1, "Expecting a text file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) {
			
				$meansfile = $target_path.$uploadfile;
                /* Read the means file */
				$handle = fopen($meansfile, "r");
                $header = fgetcsv($handle, 0, "\t");
        
                // Set up column indices; all columns are required
                $cap_entry_code_idx = implode(find("CAP Entry Code", $header),"");
                $cap_entry_no_idx =  implode(find("CAP Entry No.", $header),"");
                $bp_idx = implode(find("Breeding Program", $header),"");
                $cap_year_idx = implode(find("CAP Year", $header),"");
                $check_idx = implode(find("Check", $header),"");
                $expt_code_idx = implode(find("Expt Code", $header),"");
				$trial_code_idx = implode(find("Trial Code", $header),"");
				$trial_year_idx = implode(find("Trial Year", $header),"");
				$expt_short_idx = implode(find("Experiment", $header),"");
				$location_idx = implode(find("Location", $header),"");
				$trial_entry_no_idx = implode(find("Trial Entry No.", $header),"");
				$line_name_idx = implode(find("Line Name", $header),"");
				
				//echo "cap year".$cap_year_idx;
				//var_dump($header);
				
				// Check if a required col is missing
                if (($line_name_idx == "")||($cap_entry_code_idx == "")||($bp_idx == "")||($cap_year_idx = "")||($check_idx == "")||($trial_code_idx == "")||($trial_year_idx == "")) {
                    echo "Missing One of these required Columns. Please correct it and upload again: linename ".$line_name_idx." CAPCode ".$cap_entry_code_idx." BP ".$bp_idx.
                        " CAPyear ".$cap_year_idx." Check ".$check_idx." Trial ".$trial_code_idx." trial year ".$trial_year_idx."\n";
                    exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                //column where phenotype data starts
                $offset = $line_name_idx + 1;
                $phenonames = array();
                $phenoids = array();
                /* connecting to development database */
                // connect_dev();	
  
                for ($i = $offset; $i <= count($header); $i++) {
                    $teststr= addcslashes(trim($header[$i]),"\0..\37!@\177..\377");
                    //echo "the test string". $teststr."<br/>";      
                    if (strlen($teststr) == 0) {
                        break; 
                    } else {
                        //break column title into pieces
                        $teststr= str_replace('\\n',' ',$teststr);
                        $pheno= explode(' ',$teststr);
                        $piece = count($pheno);
                        $pheno_cur ='';
                        //commented    print_r($pheno);
                        for ($j=0;$j<$piece;$j++){
                            $eflg = 0;
                            $pheno_cur .= " ".$pheno[$j];
                            //echo "pheno curretn is". $pheno_cur."<br/>";
                            $pheno_cur =trim($pheno_cur);
                            $sql = "SELECT phenotype_uid as id,phenotypes_name as name, max_pheno_value as maxphen, min_pheno_value as minphen, datatype
                                FROM phenotypes
                                WHERE phenotypes_name = '$pheno_cur'";
                                // commented     echo $pheno_cur." ".$sql."\n";
               
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            if (1 == mysql_num_rows($res)) {
                                $row = mysql_fetch_assoc($res);
                                $datatypes = $row['datatype'];
                                $phenonames[] =  $row['name'];
                                $phenoids[] = $row['id'];//$phenotype_uid;
                                $pheno_max[] = $row['maxphen'];
                                $pheno_min[] = $row['minphen'];
                                $eflg = 1;
                                break;
                            }
                        }
                    }
                }
	
                if ($eflg==0) {
                    echo "Phenotype name ".$pheno_cur." does not exist ";
                    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                $pheno_num = count($phenoids);
                /*
                print_r($phenonames);
                print_r($phenoids);
                print_r($pheno_max);
                print_r($pheno_min);*/
                /**
                * Returns $arg1 if it is set, else fatal error
                */
                function ForceValue(& $arg1, $msg) {
                    if (isset($arg1)) 	{
                        return $arg1;
                    }
                    die($msg);
                }

                $current = NULL;	// the current row
                $num_exp = 0;
                $experiment_uids[$num_exp] = -1;
                $row_num = 0; // to keep track of the row number
                $cap_year_idx = implode(find("CAP Year", $header),"");
                /* this is where we deal with the data */
   
                while(($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
                    $row_num++;
                    $current = $data;
                    if(count($current)>1) {
                        //var_dump($current);
                        // Get required columns
                        $check =	ForceValue($current[$check_idx], "Fatal Error: Missing checkvalue at row " . $row_num);
                        $breeding_program_name =	$current[$bp_idx];
                        $trial_code_new =	$current[$trial_code_idx];
		 
                        if (($check<2)||($trial_code_new!=NULL)) {
                            $trial_code = $trial_code_new;
                        } 
                        //echo $trial_code." ".$trial_code_new."\n";
                        $line_name =	ForceValue($current[$line_name_idx], "Fatal Error: Missing line name at row " . $row_num);
                        $CAPentrycode = $current[$cap_entry_code_idx];
         
                        /* checking for mmismatch data*/
                        $sql = "select line_record_uid as id from line_synonyms where line_synonym_name  = '$CAPentrycode'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        $cap_line = mysql_fetch_assoc($res);
                        $cap_line_uid = $cap_line['id'];
            
                        $sql = "select line_record_uid as id from line_records where line_record_name  = '$line_name'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        $line = mysql_fetch_assoc($res);
                        $line_uid = $line['id'];
            
                        if (($cap_line_uid != $line_uid) && $check == 0) {
                            echo "Fatal Error: Data mismatch for line ".$line_name." at row " . $row_num ."<br/><br/>";
                            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                        }
                        /* end of checking for mismatch data */
        
                        if (($check > 0) || is_null($check)) {
                            $CAPyear = $current[$cap_year_idx];
                        } else {
                        /*
                            echo "chck index is". $check_idx . "<br/>";
                            echo"cap year index is" . $cap_year_idx . "<br/>";
                            echo "cap year:" . $current[4];*/
                            $CAPyear = ForceValue($current[$cap_year_idx], "Fatal Error: Missing CAP year at row " . $row_num);
                            $line_name = $CAPentrycode; // for caplines use CAPcode to ID line
                        }
                        $year =	$current[$trial_year_idx];
		 
                        if ($trial_entry_no_idx>0) {
                            $trial_entry_no =	 trim($current[$trial_entry_no_idx]);
                        } else {$trial_entry_no = NULL;}

                        if (DEBUG>2) {
                            echo $breeding_program_name." ".$trial_code." ".$line_name." ".$check." ".$pheno_num."\n";
                        }
                
                        /*
                        * Figure out which experiment to use
                        */
                        $sql = "select experiment_uid as id from experiments where trial_code = '$trial_code'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        if (1 == mysql_num_rows($res)) {
                            $experiment = mysql_fetch_assoc($res);
                            $experiment_uid = $experiment['id'];
                        } elseif (0 == mysql_num_rows($res)) {
                            echo "Fatal Error: experiment " . $trial_code. " does not exist at row " . $row_num . "-" .  $line_uid ."<br/><br/>";
                            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						} else {
                            echo "Fatal Error: experiment ".$trial_code." matches multiple experiments-must be unique " . $row_num ."<br/><br/>" ;
                            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                        } // end if
		 
                        //Store experiment_uids for this file
                        if (!in_array($experiment_uid,$experiment_uids)) {
                            $experiment_uids[$num_exp]=$experiment_uid;
                            $num_exp++;
                            // remove  checkline data for the phenotypes in this experiment from phenotype_data table, this will help deal with multiple
                            // copies of a check_line
                            // get tht-base_uids for checklines
                            // Only do this the first time through for an experiment
                            $pheno_uids = implode(",",$phenoids);
			
                            $sql = "SELECT tht_base_uid
                                FROM tht_base
                                WHERE check_line='yes' AND experiment_uid='$experiment_uid'";
                           
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            if (mysql_num_rows($res)>0) {
                                while ($row = mysql_fetch_array($res)){
                                    $tht_base_uids[]=$row['tht_base_uid'];
                                }
                                $tht_base_uids = implode(',',$tht_base_uids);
                                $sql = "DELETE FROM phenotype_data
                                    WHERE tht_base_uid in ($tht_base_uids)AND phenotype_uid IN ($pheno_uids)";
                                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                unset($tht_base_uids);
                            }
                        }
                        /*
                        * Figure out which line to use
                        */
                        if ($check != 2) {
                            $line_record_uid =	get_lineuid($line_name);
                            if (count($line_record_uid)>1) {
                                exit('more than one line record id for {$line_name}');
                            } elseif ($line_record_uid===FALSE){
                                exit("line {$line_name} not found in table, stop");
                            }
                            $line_record_uid=$line_record_uid[0];
                            if (DEBUG>1) {
                                echo "exp uid ".$experiment_uid." line uid ".$line_record_uid."\n";
                            }
                        }
                        /*
                        * Figure out which dataset to use if this is not a checkline
                        */
                        if ($check == 0) {
                            $sql = "SELECT CAPdata_programs_uid as id
                                FROM CAPdata_programs
                                WHERE data_program_code  = '$breeding_program_name'";
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            if (1 == mysql_num_rows($res)) {
                                $row = mysql_fetch_assoc($res);
                                $BPcode_uid = $row['id'];
                            } else {
                                echo "Fatal Error: CAPbreeding program  does not exist at row " . $row_num . "<br/><br/>";
                                exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                            }
                            $sql = "SELECT de.datasets_experiments_uid as id
                                FROM datasets_experiments AS de, datasets AS ds, CAPdata_programs AS cd
                                WHERE
                                    de.datasets_uid = ds.datasets_uid
                                AND ds.breeding_year = '$CAPyear'
                                AND ds.CAPdata_programs_uid ='$BPcode_uid'
                                AND experiment_uid = '$experiment_uid' limit 1";
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            if (1 == mysql_num_rows($res)) {
                                $row = mysql_fetch_assoc($res);
                                $de_uid = $row['id'];
                            }  else {
                             // set new dataset experiment code
                            // get datasets_uid;
                                $sql = "SELECT datasets_uid as id
                                    FROM  datasets
                                    WHERE CAPdata_programs_uid ='$BPcode_uid'
                                    AND breeding_year = '$CAPyear'";
                                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       
                                if (mysql_num_rows($res)<1)  {
                                 // add in dataset
                                    $row = mysql_fetch_assoc($res);
                                    $ds_name = $breeding_program_name.substr($CAPyear,-2);
                                    $sql = "INSERT INTO datasets SET CAPdata_programs_uid='$BPcode_uid',
                                        breeding_year = '$CAPyear', dataset_name = '$ds_name', updated_on=NOW(),
                                        created_on = NOW()";
                                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                    $ds_uid = mysql_insert_id();
                                    $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                                        datasets_uid = '$ds_uid', updated_on=NOW(),
                                        created_on = NOW()";
                                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                    $de_uid = mysql_insert_id();
                                } elseif (1 == mysql_num_rows($res)) {
                                    $row = mysql_fetch_assoc($res);
                                    $ds_uid = $row['id'];
                                    $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                                        datasets_uid = '$ds_uid', updated_on=NOW(),
                                        created_on = NOW()";
                                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                    $de_uid = mysql_insert_id();
                                } else {
                                    die ("Fatal Error: dataset ".$ds_uid." does not exist at row " . $row_num);
                                }
                            }
                            if (DEBUG>1) {
                                echo "ds uid ".$ds_uid." de uid ".$de_uid."\n";
                            }
                        } // end if for datalines
                        /*
                        * Insert line into tht-base if check is 0 or 1
                        */
                        if ($check < 2) {
                            $check_val ='no';
                            if ($check == 1) {
                                $check_val ='yes';
                            }
                            // check if tht_base_uid already exists for this line, check condition, and experiment
                            $sql = "SELECT tht_base_uid FROM tht_base
                                WHERE line_record_uid='$line_record_uid' AND experiment_uid='$experiment_uid'
                                AND check_line ='$check_val' limit 1";
                           
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");

                            if (mysql_num_rows($res)==1) {
                                $row = mysql_fetch_assoc($res);
                                $tht_base_uid = $row['tht_base_uid'];
                                $sql = "UPDATE tht_base
                                    SET line_record_uid = '$line_record_uid',
                                    experiment_uid = '$experiment_uid',";
                                if ($check == 1) {
                                    $sql .= "check_line='yes', datasets_experiments_uid=NULL,
                                        trial_code_number = NULL,";
                                } else {
                                    $sql .= "datasets_experiments_uid='$de_uid',
                                    trial_code_number = '$trial_entry_no',";
                                }
                                $sql .= "updated_on=NOW()
                                    WHERE tht_base_uid = '$tht_base_uid'";
                                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            } else {
                                $sql = "INSERT INTO tht_base
                                    SET line_record_uid = '$line_record_uid',
                                    experiment_uid = '$experiment_uid',";
                                if ($check == 1){
                                    $sql .= "check_line='yes', datasets_experiments_uid=NULL,
                                    trial_code_number = NULL,";
                                } else {
                                    $sql .= "datasets_experiments_uid='$de_uid',
                                        trial_code_number = '$trial_entry_no',";
                                }
                                $sql .= " updated_on=NOW(),created_on = NOW()";
                                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                $tht_base_uid = mysql_insert_id();
                                if (DEBUG>2) {
                                    echo "thtbase uid ".$tht_base_uid." line uid ".$line_record_uid."\n";
                                }
                            }
                         /*
                         * Enter phenotype values into the database for this particular line in this
                         * particular experiment. First check if data just needs to be updated, if no, then insert in
                         * new data.
                         */
                            // Get phenotypedata columns
                            if (($check == 0)||($check == 1)) {
                                for ($j=0;$j<$pheno_num;$j++) {
                                    $pheno_uid =$phenoids[$j];
                                    $phenotype_data =	$current[$offset+$j];
                                    if (DEBUG>2) {echo $phenotype_data."\n";}
                                    //put in check for SAS value for NULL
                                    // check datatype, if continuous or discrete it must be numeric
                                    if ((!is_null($phenotype_data)) && ($phenotype_data!=".")) {
                                        if ((($datatype[$j]=='continuous')||($datatype[$j]=='discrete'))&&(!is_numeric($phenotype_data))) {
                                            echo "Error: Data not continuous: ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
                                        } 
                                        //CHeck if phenotype data is within the specified range given in the database.
                                        // fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15)
                                        if (abs($phenotype_data) < .00001){
                                            $phenotype_data = '0';
                                        }
                                        if (($pheno_min[$j]!=$pheno_max[$j])&&(($phenotype_data<$pheno_min[$j])||($phenotype_data>$pheno_max[$j]))){
											echo "Warning: Out of bounds line:trait:value ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
                                            //print_r($current);
                                            //exit;
                                        } elseif ($check == 0){
                                        // check if there is existing data for this experiment if yes then update
                                            $sql = "SELECT phenotype_data_uid FROM phenotype_data
                                                WHERE phenotype_uid = '$phenoids[$j]'
                                                AND tht_base_uid = '$tht_base_uid'";
                                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                            if ( mysql_num_rows($res)>0) {
								   
                                                $sql = "UPDATE phenotype_data SET value = '$phenotype_data', updated_on=NOW()
                                                        WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
                                            } else {
                                                $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
                                                    tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
                                                    updated_on=NOW(), created_on = NOW()";
                                            }
                                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
										} elseif ($check == 1) {
                                        //Insert only as all checklines were deleted at the beginning. The problem
                                        //occurs when an experiment has multiple values for the same checklines (e.g., MN data)
                                            if (DEBUG>2) {echo "checkline data ".$phenotype_data."\n";}
                                        
                                            if (!is_null($phenotype_data)) {
                                                $sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
                                                    tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
                                                    updated_on=NOW(), created_on = NOW()";
                                                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                            }
                                        }
                                    }
                                }
                            }
                        } elseif ($check = 2) {
                        /* deal with statistics */
                        // read in stats
                        // identify which statistic it is based on column 1
                            $col_lookup = array(
                            'trialmean' => 'mean_value','mean' => 'mean_value','se' => 'standard_error','lsd(.05)' => 'standard_error','replications' => 'number_replicates','prob>f' => 'prob_gt_F' );
                            $statname = str_replace(" ","",strtolower(trim($current[$cap_entry_code_idx])));
                            $fieldname = $col_lookup[$statname];
                            if (!empty($fieldname)) {
                                if (DEBUG>1) {echo $statname." mapped to ".$fieldname."\n";}
               
                                for ($j=0;$j<$pheno_num;$j++) {
                                    $pheno_uid =$phenoids[$j];
                                    $phenotype_data =	trim($current[$offset+$j]);
                                    // insert NULL value if empty
                                    if (strlen($phenotype_data) == 0) {
                                        $phenotype_data = "NULL";
                                    } elseif (($fieldname =='mean_value')||($field_name == 'standard_error')||($fieldname =='number_replicates')){
                                        if (!is_numeric($phenotype_data)) {
                                            echo "Error:Value not numeric ".$fieldname.":". $phenoname[$j].":".$phenotype_data."\n";
                                            $phenotype_data = "NULL";
                                        }
                                    }
					 
                                    if (DEBUG>2) {echo $phenotype_data."\n";}
                                    if (!is_null($phenotype_data)) {
                                    // check if there are existing statistics data for this experiment if yes then update
                                        $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                            WHERE phenotype_uid = '$phenoids[$j]'
                                            AND experiment_uid = '$experiment_uid'";
                                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                        if ( mysql_num_rows($res)>0) {
					  if ($phenotype_data != "NULL") {
					    $phenotype_data = "'".$phenotype_data."'";
					  }
					  $sql = "UPDATE phenotype_mean_data SET $fieldname = $phenotype_data, updated_on=NOW()
                                                WHERE experiment_uid = '$experiment_uid' AND phenotype_uid = '$phenoids[$j]'";
                                        } else {
                                            $sql = "INSERT INTO phenotype_mean_data SET $fieldname = '$phenotype_data',
                                                experiment_uid = '$experiment_uid', phenotype_uid = '$phenoids[$j]',
                                                updated_on=NOW(), created_on = NOW()";
                                        }
                                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                    }
                                } 
                            }
                        } //end elseif for dealing with statistics
                    }/* end of if block checking for an empty line */
                } /* end of while loop */
  	?>
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
		
	<h3>We are reading following data from the uploaded Input Data File</h3>
		
	<table >
    <thead>
    <tr> 
    <?php
    		for ($i = 0; $i <= count($header); $i++) {
				$teststr = str_replace(' ','',$header[$i]);
				$newtext = wordwrap($teststr, 7, "\n", true);
    ?>
    <th ><?php echo $newtext?></th>
    <?php
      		}
    ?>
    </tr>
    </thead>
	
 <tbody style="padding: 0; height: 300px; width: 700px;  overflow: scroll;border: 1px solid #5b53a6;">
 <?php
    /* printing the values onto the page for user*/
   
	/* re opening the file since the pointer gets to the end */
            $handle = fopen($meansfile, "r");
            $skip_first = 0;
            while(($data_print = fgetcsv($handle, 0, "\t")) !== FALSE) {
?>
<tr>
<?php
                if($skip_first > 0) {
                    $current_row = $data_print;
                    for($j = 0; $j <= count($header); $j++) {
 ?>
 <td>
 <?php
                        $newtext = wordwrap($current_row[$j], 7, "\n", true);
                        echo  $newtext;
?>
</td>
<?php
                    }
?>
</tr>
<?php
                } /* end of if */
                $skip_first++;
            } /* end of while loop */ ?>
</tbody>
</table>
<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $meansfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $rawdatafile ?>' )"/>
<input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
<?php
            } else {
                error(1,"There was an error uploading the file, please try again!");
                print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
            }
        }
    }
} /* end of type_Experiment_Name function*/
	
	private function type_Database() {
		global $config;
		include($config['root_dir'] . 'theme/admin_header.php');
        //connect_dev();	/* connecting to development database */
	
        $meansfile = $_GET['expdata'];
        $filename = $_GET['file_name'];
        $username = $_GET['user_name'];
        $rawdatafile = $_GET['raw_data_file'];
		//echo "means file".$meansfile . "file name" . $filename . "username" . $username . "raw data file" .$rawdatafile;
        /* reading the file */
		/* Read the means file */
        $handle = fopen($meansfile, "r");
        $header = fgetcsv($handle, 0, "\t");
        
        // Set up column indices; all columns are required
        $cap_entry_code_idx = implode(find("CAP Entry Code", $header),"");
        $cap_entry_no_idx =  implode(find("CAP Entry No.", $header),"");
        $bp_idx = implode(find("Breeding Program", $header),"");
        $cap_year_idx = implode(find("CAP Year", $header),"");
        $check_idx = implode(find("Check", $header),"");
        $expt_code_idx = implode(find("Expt Code", $header),"");
		$trial_code_idx = implode(find("Trial Code", $header),"");
		$trial_year_idx = implode(find("Trial Year", $header),"");
		$expt_short_idx = implode(find("Experiment", $header),"");
		$location_idx = implode(find("Location", $header),"");
		$trial_entry_no_idx = implode(find("Trial Entry No.", $header),"");
		$line_name_idx = implode(find("Line Name", $header),"");
				
		//echo "cap year".$cap_year_idx;
		//var_dump($header);
		// Check if a required col is missing
        if (($line_name_idx == "")||($cap_entry_code_idx == "")||($bp_idx == "")||($cap_year_idx = "")||($check_idx == "")||($trial_code_idx == "")||($trial_year_idx == "")) {
            echo "Missing One of these required Columns. Please correct it and upload again: linename ".$line_name_idx." CAPCode ".$cap_entry_code_idx." BP ".$bp_idx.
                " CAPyear ".$cap_year_idx." Check ".$check_idx." Trial ".$trial_code_idx." trial year ".$trial_year_idx."\n";
            exit( "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
		//column where phenotype data starts
		$offset = $line_name_idx + 1;
        $phenonames = array();
        $phenoids = array();
   
        /* connecting to development database */
        //connect_dev();	
   
        for ($i = $offset; $i <= count($header); $i++) {
            $teststr= addcslashes(trim($header[$i]),"\0..\37!@\177..\377");
      		//echo "the test string". $teststr."<br/>";      
            if (strlen($teststr) == 0){
                break; 
            } else {
             //break column title into pieces
                $teststr= str_replace('\\n',' ',$teststr);
                $pheno= explode(' ',$teststr);
                $piece = count($pheno);
                $pheno_cur ='';
            //commented    print_r($pheno);
                for ($j=0;$j<$piece;$j++){
                    $eflg = 0;
                    $pheno_cur .= " ".$pheno[$j];
                    //echo "pheno curretn is". $pheno_cur."<br/>";
                    $pheno_cur =trim($pheno_cur);
                    $sql = "SELECT phenotype_uid as id,phenotypes_name as name, max_pheno_value as maxphen, min_pheno_value as minphen, datatype
						FROM phenotypes
						WHERE phenotypes_name = '$pheno_cur'";
                    // commented     echo $pheno_cur." ".$sql."\n";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                    if (1 == mysql_num_rows($res)) {
                        $row = mysql_fetch_assoc($res);
                        $datatypes = $row['datatype'];
                        $phenonames[] =  $row['name'];
                        $phenoids[] = $row['id'];//$phenotype_uid;
                        $pheno_max[] = $row['maxphen'];
                        $pheno_min[] = $row['minphen'];
                        $eflg = 1;
                        break;
                    }
                }
            }
        }
        if ($eflg==0) {
            echo "Phenotype name ".$pheno_cur." does not exist ";
            exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
        }
        $pheno_num = count($phenoids);
       /*
        print_r($phenonames);
        print_r($phenoids);
        print_r($pheno_max);
        print_r($pheno_min);*/
 /**
 * Returns $arg1 if it is set, else fatal error
 */
        function ForceValue(& $arg1, $msg) {
            if (isset($arg1)) {
                return $arg1;
            }
            die($msg);
        }
        $current = NULL;	// the current row
        $num_exp = 0;
        $experiment_uids[$num_exp] = -1;
        $row_num = 0; // to keep track of the row number
        $cap_year_idx = implode(find("CAP Year", $header),"");
        /* this is where we deal with the data */
   
        while(($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
            $row_num++;
            $current = $data;
            if(count($current)>1) {
            //var_dump($current);
  	        // Get required columns
                $check =	ForceValue($current[$check_idx], "Fatal Error: Missing checkvalue at row " . $row_num);
                $breeding_program_name =	$current[$bp_idx];
		        $trial_code_new =	$current[$trial_code_idx];
		 
            if (($check<2)||($trial_code_new!=NULL)){
                $trial_code = $trial_code_new;
            } 
            //echo $trial_code." ".$trial_code_new."\n";
            $line_name =	ForceValue($current[$line_name_idx], "Fatal Error: Missing line name at row " . $row_num);
            $CAPentrycode = $current[$cap_entry_code_idx];
         
            /* checking for mmismatch data*/
            $sql = "select line_record_uid as id from line_synonyms where line_synonym_name  = '$CAPentrycode'";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            $cap_line = mysql_fetch_assoc($res);
            $cap_line_uid = $cap_line['id'];
            
            $sql = "select line_record_uid as id from line_records where line_record_name  = '$line_name'";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            $line = mysql_fetch_assoc($res);
            $line_uid = $line['id'];
            
            if (($cap_line_uid != $line_uid) && $check == 0) {
                echo "Fatal Error: Data mismatch for line ".$line_name." at row " . $row_num ."<br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            }
         	 /* end of checking for mismatch data */
        
            if (($check > 0) || is_null($check)) {
                $CAPyear = $current[$cap_year_idx];
            } else {
                /*
                echo "chck index is". $check_idx . "<br/>";
                echo"cap year index is" . $cap_year_idx . "<br/>";
                echo "cap year:" . $current[4];*/
                $CAPyear = ForceValue($current[$cap_year_idx], "Fatal Error: Missing CAP year at row " . $row_num);
                $line_name = $CAPentrycode; // for caplines use CAPcode to ID line
            }
            $year =	$current[$trial_year_idx];
		 
            if ($trial_entry_no_idx>0) {
                $trial_entry_no =		trim($current[$trial_entry_no_idx]);
            } else {$trial_entry_no = NULL;}
            
            if (DEBUG>2) {
                echo $breeding_program_name." ".$trial_code." ".$line_name." ".$check." ".$pheno_num."\n";
            }
         /*
         * Figure out which experiment to use
         */
            $sql = "select experiment_uid as id from experiments where trial_code = '$trial_code'";
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (1 == mysql_num_rows($res)) {
                $experiment = mysql_fetch_assoc($res);
                $experiment_uid = $experiment['id'];
            } elseif (0 == mysql_num_rows($res)) {
                echo "Fatal Error: experiment " . $trial_code. " does not exist at row " . $row_num . "-" .  $line_uid ."<br/><br/>";
				exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
			} else {
                echo "Fatal Error: experiment ".$trial_code." matches multiple experiments-must be unique " . $row_num ."<br/><br/>" ;
                exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
            } // end if
            //Store experiment_uids for this file
            if (!in_array($experiment_uid,$experiment_uids)) {
                $experiment_uids[$num_exp]=$experiment_uid;
                $num_exp++;
                // remove  checkline data for the phenotypes in this experiment from phenotype_data table, this will help deal with multiple
                // copies of a check_line
                // get tht-base_uids for checklines
                // Only do this the first time through for an experiment
                $pheno_uids = implode(",",$phenoids);
			
                $sql = "SELECT tht_base_uid
                    FROM tht_base
                    WHERE check_line='yes' AND experiment_uid='$experiment_uid'";
                           
                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                if (mysql_num_rows($res)>0) {
                    while ($row = mysql_fetch_array($res)){
                        $tht_base_uids[]=$row['tht_base_uid'];
                    }
                    $tht_base_uids = implode(',',$tht_base_uids);
				
                    $sql = "DELETE FROM phenotype_data
						WHERE tht_base_uid in ($tht_base_uids) AND phenotype_uid IN ($pheno_uids)";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                    unset($tht_base_uids);
                }
            }
            /*
            * Figure out which line to use
            */
            if ($check !=2) {
                $line_record_uid =	get_lineuid($line_name);
                if (count($line_record_uid)>1) {
                    exit('more than one line record id for {$line_name}');
                } elseif ($line_record_uid===FALSE){
                    exit("line {$line_name} not found in table, stop");
                }
                $line_record_uid=$line_record_uid[0];
                if (DEBUG>1) {
                    echo "exp uid ".$experiment_uid." line uid ".$line_record_uid."\n";
                }
            }
         /*
          * Figure out which dataset to use if this is not a checkline
          */
            if ($check == 0) {
                $sql = "SELECT CAPdata_programs_uid as id
                    FROM CAPdata_programs
                    WHERE data_program_code  = '$breeding_program_name'";
                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                if (1 == mysql_num_rows($res)) {
                    $row = mysql_fetch_assoc($res);
                    $BPcode_uid = $row['id'];
                } else {
                    echo "Fatal Error: CAPbreeding program  does not exist at row " . $row_num . "<br/><br/>";
                    exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
                }
                $sql = "SELECT de.datasets_experiments_uid as id
                     FROM datasets_experiments AS de, datasets AS ds, CAPdata_programs AS cd
                     WHERE
                        de.datasets_uid = ds.datasets_uid
                    AND ds.breeding_year = '$CAPyear'
                    AND ds.CAPdata_programs_uid ='$BPcode_uid'
                    AND experiment_uid = '$experiment_uid' limit 1";
                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                if (1 == mysql_num_rows($res)) {
                    $row = mysql_fetch_assoc($res);
                    $de_uid = $row['id'];
                } else {
               // set new dataset experiment code
               // get datasets_uid;
                $sql = "SELECT datasets_uid as id
                    FROM  datasets
                    WHERE CAPdata_programs_uid ='$BPcode_uid'
                    AND breeding_year = '$CAPyear'";
				$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       
                if (mysql_num_rows($res)<1) { 
                    // add in dataset
                    $row = mysql_fetch_assoc($res);
                    $ds_name = $breeding_program_name.substr($CAPyear,-2);
                    $sql = "INSERT INTO datasets SET CAPdata_programs_uid='$BPcode_uid',
                        breeding_year = '$CAPyear', dataset_name = '$ds_name', updated_on=NOW(),
                        created_on = NOW()";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                    $ds_uid = mysql_insert_id();
                    $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                        datasets_uid = '$ds_uid', updated_on=NOW(),
                        created_on = NOW()";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                    $de_uid = mysql_insert_id();
                } elseif (1 == mysql_num_rows($res)) {
                    $row = mysql_fetch_assoc($res);
                    $ds_uid = $row['id'];
                    $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                        datasets_uid = '$ds_uid', updated_on=NOW(),
                        created_on = NOW()";
                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                    $de_uid = mysql_insert_id();
                } else {
                    die ("Fatal Error: dataset ".$ds_uid." does not exist at row " . $row_num);
                }
            }
            if (DEBUG>1) {
                echo "ds uid ".$ds_uid." de uid ".$de_uid."\n";
            }
        } // end if for datalines
        /*
        * Insert line into tht-base if check is 0 or 1
        */
        if ($check < 2) {
            $check_val ='no';
			if ($check ==1) {
				$check_val ='yes';
			}
			// check if tht_base_uid already exists for this line, check condition, and experiment
            $sql = "SELECT tht_base_uid FROM tht_base
                    WHERE line_record_uid='$line_record_uid' AND experiment_uid='$experiment_uid'
                    AND check_line ='$check_val' limit 1";
                           
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (mysql_num_rows($res)==1)  {
                $row = mysql_fetch_assoc($res);
                $tht_base_uid = $row['tht_base_uid'];
                $sql = "UPDATE tht_base
                    SET line_record_uid = '$line_record_uid',
                    experiment_uid = '$experiment_uid',";
                if ($check == 1){
                    $sql .= "check_line='yes', datasets_experiments_uid=NULL,
                        trial_code_number = NULL,";
                } else {
                    $sql .= "datasets_experiments_uid='$de_uid',
                        trial_code_number = '$trial_entry_no',";
                }
                $sql .= "updated_on=NOW()
                        WHERE tht_base_uid = '$tht_base_uid'";
                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            } else {
                $sql = "INSERT INTO tht_base
                    SET line_record_uid = '$line_record_uid',
                    experiment_uid = '$experiment_uid',";
                if ($check == 1){
                    $sql .= "check_line='yes', datasets_experiments_uid=NULL,
                        trial_code_number = NULL,";
                } else {
                    $sql .= "datasets_experiments_uid='$de_uid',
                        trial_code_number = '$trial_entry_no',";
                }
                $sql .= " updated_on=NOW(),created_on = NOW()";
                $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                $tht_base_uid = mysql_insert_id();
                if (DEBUG>2) {
                    echo "thtbase uid ".$tht_base_uid." line uid ".$line_record_uid."\n";}
                }
            /*
            * Enter phenotype values into the database for this particular line in this
            * particular experiment. First check if data just needs to be updated, if no, then insert in
            * new data.
            */
            // Get phenotypedata columns
                if (($check == 0) || ($check == 1)) {
                    for ($j=0;$j<$pheno_num;$j++) {
                        $pheno_uid =$phenoids[$j];
                        $phenotype_data =	$current[$offset+$j];
                        if (DEBUG>2) {echo $phenotype_data."\n";}
                        //put in check for SAS value for NULL
						// check datatype, if continuous or discrete it must be numeric
                        if ((!is_null($phenotype_data)) && ($phenotype_data!=".")) {
                            if ((($datatype[$j]=='continuous')||($datatype[$j]=='discrete'))&&(!is_numeric($phenotype_data))) {
                                echo "Error: Data not continuous: ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
                            } 
                            //CHeck if phenotype data is within the specified range given in the database.
                            // fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15)
                            if (abs($phenotype_data) < .00001) {
                                $phenotype_data = 0;
                            }
                            elseif (($pheno_min[$j]!=$pheno_max[$j])&&(($phenotype_data<$pheno_min[$j])||($phenotype_data>$pheno_max[$j]))){
								
								echo "Warning: Out of bounds line:trait:value ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
								//print_r($current);
								//exit;
                            } elseif ($check == 0) {
                                // check if there is existing data for this experiment if yes then update
								$sql = "SELECT phenotype_data_uid FROM phenotype_data
                                    WHERE phenotype_uid = '$phenoids[$j]'
									AND tht_base_uid = '$tht_base_uid'";
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
								if ( mysql_num_rows($res)>0) {
								   $sql = "UPDATE phenotype_data SET value = '$phenotype_data', updated_on=NOW()
										WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
								} else {
                                    $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
                                        tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
										updated_on=NOW(), created_on = NOW()";
								}
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            } elseif ($check == 1) {
							//Insert only as all checklines were deleted at the beginning. The problem
							//occurs when an experiment has multiple values for the same checklines (e.g., MN data)
                                if (DEBUG>2) {echo "checkline data ".$phenotype_data."\n";}
                                if (!is_null($phenotype_data)) {
                                    $sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
									   tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
									   updated_on=NOW(), created_on = NOW()";
							  
                                    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                                }
                            }
                        }
					}
				}
            } elseif ($check = 2) {
            /* deal with statistics */
            // read in stats
            // identify which statistic it is based on column 1
            	$col_lookup = array(
                    'trialmean' => 'mean_value','mean' => 'mean_value','se' => 'standard_error','lsd(.05)' => 'standard_error','replications' => 'number_replicates','prob>f' => 'prob_gt_F'
                    );
                $statname = str_replace(" ","",strtolower(trim($current[$cap_entry_code_idx])));
                $fieldname = $col_lookup[$statname];
                if (!empty($fieldname)){
                    if (DEBUG>1) {echo $statname." mapped to ".$fieldname."\n";}
                    for ($j=0;$j<$pheno_num;$j++) {
                        $pheno_uid =$phenoids[$j];
                        $phenotype_data =	trim($current[$offset+$j]);
                        // insert NULL value if empty
                        if (strlen($phenotype_data) == 0) {
                            $phenotype_data = "NULL";
                        } elseif (($fieldname =='mean_value')||($field_name == 'standard_error')||($fieldname =='number_replicates')){
                            if (!is_numeric($phenotype_data)) {
                                echo "Error:Value not numeric ".$fieldname.":". $phenoname[$j].":".$phenotype_data."\n";
                                $phenotype_data = "NULL";
                            }
                        }
					    if (DEBUG>2) {echo $phenotype_data."\n";}
                        if (!is_null($phenotype_data)) {
                            // check if there are existing statistics data for this experiment if yes then update
                            $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                WHERE phenotype_uid = '$phenoids[$j]'
                                AND experiment_uid = '$experiment_uid'";
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                            if ( mysql_num_rows($res)>0) {
			      if ($phenotype_data != "NULL") {
				$phenotype_data = "'".$phenotype_data."'";
			      }
                                $sql = "UPDATE phenotype_mean_data SET $fieldname = $phenotype_data, updated_on=NOW()
                                    WHERE experiment_uid = '$experiment_uid' AND phenotype_uid = '$phenoids[$j]'";
                            } else {
                                $sql = "INSERT INTO phenotype_mean_data SET $fieldname = '$phenotype_data',
                                    experiment_uid = '$experiment_uid', phenotype_uid = '$phenoids[$j]',
                                    updated_on=NOW(), created_on = NOW()";
                            }
                            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        }
                    } 
               }
            } //end elseif for dealing with statistics
    	}/* end of if block checking for an empty line */
    } /* end of while loop */
  	/* end of reading the file */
	// Update trait statistics
   $trait_stats = calcPhenoStats_mysql ($phenoids);
   //print_r ($trait_stats);
    for ($i = 0;$i<count($phenoids);$i++){
		//check if record there
		$max_val= $trait_stats[$i][max_val];
		$min_val= $trait_stats[$i][min_val];
		$mean_val= $trait_stats[$i][mean_val];
		$std_val= $trait_stats[$i][std_val];
		$sample_size= $trait_stats[$i][sample_size];
		$pheno_uid = $trait_stats[$i][phenotype_uid];
		
		$sql = "SELECT * FROM phenotype_descstat WHERE phenotype_uid = $pheno_uid";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		if (mysql_num_rows($res)>0) {
			$sql = "UPDATE phenotype_descstat SET mean_val = $mean_val,
				max_val = '$max_val', min_val = '$min_val',
				std_val = $std_val, sample_size = $sample_size,updated_on=NOW()
                WHERE phenotype_uid = '$pheno_uid'";
		} else {
            $sql = "INSERT INTO phenotype_descstat SET mean_val = $mean_val,
				max_val = $max_val, min_val = $min_val,
				std_val = $std_val, sample_size = $sample_size,
				phenotype_uid = $pheno_uid, updated_on=NOW(), created_on = NOW()
				";
        }
		//echo $sql."\n";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
   }
   // Go through experiments in this file to update string of measured traits in
   // the experiment and file name
   for ($i=0;$i<$num_exp;$i++){
   		unset($phenotypes);
		$sql = "SELECT p.phenotype_uid AS id, p.phenotypes_name AS name
				FROM phenotypes AS p, tht_base AS t, phenotype_data AS pd
				WHERE pd.tht_base_uid = t.tht_base_uid
				AND p.phenotype_uid = pd.phenotype_uid
				AND t.experiment_uid=$experiment_uids[$i]
				GROUP BY p.phenotype_uid";
		
        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
        while ($row = mysql_fetch_array($res)) {
			$phenotypes[]=$row['name'];
		}
		$phenotypes = implode(',',$phenotypes);
		$sql = "UPDATE experiments SET traits =('$phenotypes') WHERE experiment_uid=$experiment_uids[$i]";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
	
		// Add meansfile name to the field for meansfile name, append to existing list if different
		$sql = "SELECT input_data_file_name
				FROM experiments 
				WHERE experiment_uid = '$experiment_uids[$i]'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		$row = mysql_fetch_assoc($res);
		$meansfile = basename($meansfile);
		if ($row["input_data_file_name"]===NULL) {
			$infile = $meansfile;
		} else {
			$infile = $row["input_data_file_name"];
		}
		if (stripos($infile,$meansfile)===FALSE) {
				$infile .= ", ".$meansfile;
		}
		$sql = "UPDATE experiments SET input_data_file_name = '$infile', updated_on=NOW()
                WHERE experiment_uid = '$experiment_uids[$i]'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		// Add rawdata name to the field for raw_data_file_name , append to existing list if different
			
		$sql = "SELECT raw_data_file_name
				FROM experiments 
				WHERE experiment_uid = '$experiment_uids[$i]'";
		$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		$row = mysql_fetch_assoc($res);
		//$meansfile = basename($meansfile);
		$infile_raw = $rawdatafile;
        /* this part id not necessary as we want to replace the raw data file name and append to the existing raw data file name */	
	
/*		if ($row["raw_data_file_name"]===NULL) {
			$infile_raw = $rawdatafile;
		} else {
			$infile_raw = $row["raw_data_file_name"];
		}
		if (stripos($infile_raw,$rawdatafile)===FALSE) {
				$infile_raw .= ", ".$rawdatafile;
		}
	*/	
		
	/* this part id not necessary as we want to replace the raw data file name and append to the existing raw data file name */	
        if ($rawdatafile) {
            $sql = "UPDATE experiments SET raw_data_file_name = '$infile_raw', updated_on=NOW()
                WHERE experiment_uid = '$experiment_uids[$i]'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
		}
		if (DEBUG>2) echo $sql."\n";
   }
   //calling this function to calculate the statistics for phenotype data.
   // echo"statistics function call";
    calcPhenoStats_mysql($empty);
	
// testing recent data
    $sql = "select input_data_file_name  from experiments where experiment_uid = '10'";
    $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
    //$row = mysql_fetch_array($res);   
    while ($row = mysql_fetch_array($res)) {
		$data[]=$row['input_data_file_name'];
	}
	
    //$data = explode(",",$row);
    //echo"input data files";
    //print_r($data);
// end of testing recent data 
echo " <b>The Data is inserted/updated successfully </b>";
echo"<br/><br/>";
?>
<a href="<?php echo $config['base_url']; ?>curator_data/input_experiments_upload_text.php"> Go Back To Main Page </a>
<?php
        $footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
    }/* end of type_database function */
} /* end of class */
?>
