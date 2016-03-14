<?php
/**
 * uploading it to main server
 *
 *  3/28/2011 JLee   Insert NULL instead 'NULL' into DB
 *  6/25/2010 J.Lee  Make the back page url relative and not hardwired to server
 *  9/29/2010 DEM    Use only single values of experiments.raw_data_file_name.
 *  12/14/2010 JLee  Change to use curator bootstrap
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


private function typeExperimentCheck()
	{
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
	
	function update_database(filepath, filename, username, rawdatafile)
	{
			
			
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




 $row = loadUser($_SESSION['username']);
	
	ini_set("memory_limit","24M");
	
	$username=$row['name'];
	
	$tmp_dir="uploads/tmpdir_".$username."_".rand();
	
	$raw_path= "../raw/phenotype/".$_FILES['file']['name'][1];
			    //	copy($_FILES['file']['tmp_name'][1], $raw_path);

	$filename=$_FILES['file']['tmp_name'][1];
			    if (copy($_FILES['file']['tmp_name'][1], $raw_path))
			      { echo "Raw file saved successfully.<br>"; }
			    else if ($_FILES['file']['tmp_name'][1] != "")
			      { echo "<font color=red size=+1><b>Raw file NOT saved, problem!</b></font><br>"; }
	
	umask(0);
	
	if(!file_exists($tmp_dir) || !is_dir($tmp_dir)) {
		mkdir($tmp_dir, 0777);
	}
	$target_path=$tmp_dir."/";
	if ($_FILES['file']['name'][0] == ""){
		error(1, "No File Uploaded");
		print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
	}
	else {
		
		$uploadfile=$_FILES['file']['name'][0];
		$rawdatafile = $_FILES['file']['name'][1];
		
		
	//	echo "uploaded file" .$uploadfile. "<br/>". "raw file" .$rawdatafile;
		
		
		$uftype=$_FILES['file']['type'][0];
		if (strpos($uploadfile, ".xls") === FALSE) {
			error(1, "Expecting an Excel file. <br> The type of the uploaded file is ".$uftype);
			print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
		}
		else {
			if(move_uploaded_file($_FILES['file']['tmp_name'][0], $target_path.$uploadfile)) 
			{
			
				$meansfile = $target_path.$uploadfile;
    		 //echo $meansfile."\n";

/* Read the annotation file */
	$reader = new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	if (strpos($meansfile,'.xls')>0)
	{
		$reader->read($meansfile);
	}else {
		$reader->read($meansfile . ".xls");
	}
	$means = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
	
		//echo "nrows ".$rows." ncols ".$cols."\n";
	// get first row of data and get phenotype names and their IDs, if phenotypes not in database,
// then send out an error
/**
 * Required Columns in the means file
 *
 * Tells the script which column is which. (Starting at 1)
 * This implies that the standard form MUST be used for data entry
 * 
 */
	$COL_CAPENTRYCODE=$COL_CAPENTRYNO=$COL_BREEDINGPROGRAM=$COL_CAPYEAR =$COL_CHECK =$COL_EXPTCODE = 0;
	$COL_TRIALCODE = $COL_TRIALYEAR = $COL_EXPTNAME = $COL_LOCATION = $COL_TRIALENTRYNO = $COL_CCRU = $COL_LINENAME = 0;
	
	for ($i = 1; $i <= $cols; $i++) {
		$teststr = str_replace(' ','',$means['cells'][1][$i]);
		
			//echo "teststr in process mean". $teststr . "<br/>";
		
		//echo "means 0 cells 1 i is set to ".$teststr."\n";
		if (stripos($teststr,'breeding')!==FALSE){
		  $COL_BREEDINGPROGRAM = $i;
		} elseif (stripos($teststr,'CAPyear')!==FALSE){
		  $COL_CAPYEAR = $i;
		} elseif (stripos($teststr,'check')!==FALSE){
		  $COL_CHECK = $i;
		} elseif (stripos($teststr,'exptcode')!==FALSE){
		  $COL_EXPTCODE = $i;
		} elseif (stripos($teststr,'trialcode')!==FALSE){
		  $COL_TRIALCODE = $i;
		} elseif(stripos($teststr,'code')!==FALSE) {
			$COL_CAPENTRYCODE = $i;
		}elseif (stripos($teststr,'trialyear')!==FALSE){
		  $COL_TRIALYEAR = $i;
		} elseif (stripos($teststr,'experiment')!==FALSE){
		  $COL_EXPTNAME = $i;
		} elseif (stripos($teststr,'location')!==FALSE){
		  $COL_LOCATION = $i;
		} elseif (stripos($teststr,'trialentry')!==FALSE){
		  $COL_TRIALENTRYNO = $i;
		} elseif (stripos($teststr,'ccru')!==FALSE){
		  $COL_CCRU = $i;
		} elseif (stripos($teststr,'No')!==FALSE){
		  $COL_CAPENTRYNO = $i;
		}elseif (stripos($teststr,'linename')!==FALSE){
		  $COL_LINENAME = $i;
		} 
	}
	//echo "Columns: linename ".$COL_LINENAME." CAPCode ".$COL_CAPENTRYCODE." BP ".$COL_BREEDINGPROGRAM.
		//	" CAPyear ".$COL_CAPYEAR." Check ".$COL_CHECK." Trial ".$COL_TRIALCODE." trial year ".$COL_TRIALYEAR."\n";
	// Check if a required col is missing
	if (($COL_LINENAME*$COL_CAPENTRYCODE*$COL_BREEDINGPROGRAM*$COL_CAPYEAR*$COL_CHECK*$COL_TRIALCODE*$COL_TRIALYEAR)==0) {
		echo "Missing Column: linename ".$COL_LINENAME." CAPCode ".$COL_CAPENTRYCODE." BP ".$COL_BREEDINGPROGRAM.
			" CAPyear ".$COL_CAPYEAR." Check ".$COL_CHECK." Trial ".$COL_TRIALCODE." trial year ".$COL_TRIALYEAR."\n";
		exit;
	}
	$offset = $COL_LINENAME + 1;//column where phenotype data starts


	
   $phenonames = array();
   $phenoids = array();
   
   //connect_dev();	/* connecting to development database */
	for ($i = $offset; $i <= $cols; $i++)
   {
      $teststr= addcslashes(trim($means['cells'][1][$i]),"\0..\37!@\177..\377");
      
		//	echo "the test string". $teststr."<br/>";      
      if (empty($teststr)){
         break; 
      } else {
         //break column title into pieces
         $teststr= str_replace('\\n',' ',$teststr);
		 $pheno= explode(' ',$teststr);
         $piece = count($pheno);
         $pheno_cur ='';
     //commented    print_r($pheno);
         
         //

         for ($j=0;$j<$piece;$j++){
            $eflg = 0;
            $pheno_cur .= " ".$pheno[$j];
          //  echo "pheno curretn is". $pheno_cur;
            $pheno_cur =trim($pheno_cur);
             $sql = "SELECT phenotype_uid as id,phenotypes_name as name, max_pheno_value as maxphen, min_pheno_value as minphen, datatype
						FROM phenotypes
						WHERE phenotypes_name = '$pheno_cur'";
            
          // commented     echo $pheno_cur." ".$sql."\n";
               
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (1 == mysql_num_rows($res))
            {
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
   
     // print_r($phenonames);
     //print_r($phenoids);
	 // print_r($pheno_max);
	 // print_r($pheno_min);
   

/*
 * Process the means file
 */
 
 
 /**
 * Returns $arg1 if it is set, else fatal error
 */
function ForceValue(& $arg1, $msg)
{
	if (isset($arg1))
	{
		return $arg1;
	}
	die($msg);
}



   $current = NULL;	// the current row
   $num_exp = 0;
   $experiment_uids[$num_exp] = -1;
   
   for($i = 2; $i <= $rows; $i++)
   {
      $current = $means['cells'][$i];
  
 		//	echo "current in process mean". $current . "<br/>";
		
      //check if line is empty, if yes then skip to the next line
      if (!empty($current)) {
        // print_r($current);
		
         // Get required columns
          $check =					ForceValue($current[$COL_CHECK], "Fatal Error: Missing checkvalue at row " . $i);
		  $breeding_program_name =	$current[$COL_BREEDINGPROGRAM];
		  
         $trial_code_new =			$current[$COL_TRIALCODE];
		 
		 if (($check<2)||($trial_code_new!=NULL)){
			$trial_code = $trial_code_new;
		 } 
		 //echo $trial_code." ".$trial_code_new."\n";
         $line_name =				ForceValue($current[$COL_LINENAME], "Fatal Error: Missing line name at row " . $i);
         $CAPentrycode =			$current[$COL_CAPENTRYCODE];
		 
        
		 if (($check > 0)||is_null($check)) {
			$CAPyear = $current[$COL_CAPYEAR];
		 } else {
			$CAPyear = ForceValue($current[$COL_CAPYEAR], "Fatal Error: Missing CAP year at row " . $i);
			$line_name = $CAPentrycode; // for caplines use CAPcode to ID line
		}
         
		 $year =			      $current[$COL_TRIALYEAR];
		 
		 if ($COL_TRIALENTRYNO>0) {
			$trial_entry_no =		trim($current[$COL_TRIALENTRYNO]);
		 } else {$trial_entry_no = NULL;}

   
         if (DEBUG>2) {
            echo $breeding_program_name." ".$trial_code." ".$line_name." ".$check." ".$pheno_num."\n";
         }
         
         
         /*
          * Figure out which experiment to use
          */
       
         $sql = "select experiment_uid as id from experiments where trial_code = '$trial_code'";
         $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
         if (1 == mysql_num_rows($res))
         {
            $experiment = mysql_fetch_assoc($res);
            $experiment_uid = $experiment['id'];
         } elseif (0 == mysql_num_rows($res)) {
            echo "Fatal Error: experiment '$trial_code' does not exist at row " . $i ."<br/><br/>";
						exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						
         } else {
            echo "Fatal Error: experiment '$trial_code' matches multiple experiments-must be unique " . $i ."<br/><br/>" ;
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
			 if (1 == mysql_num_rows($res))
            {
               $row = mysql_fetch_assoc($res);
               $BPcode_uid = $row['id'];
            }
            else
            {
				echo "Fatal Error: CAPbreeding program  does not exist at row " . $i . "<br/><br/>";
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
            if (1 == mysql_num_rows($res))
            {
               $row = mysql_fetch_assoc($res);
               $de_uid = $row['id'];
            }
            else
            {
               // set new dataset experiment code
               // get datasets_uid;
               $sql = "SELECT datasets_uid as id
                  FROM  datasets
                  WHERE CAPdata_programs_uid ='$BPcode_uid'
                  AND breeding_year = '$CAPyear'";
				$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       
                if (mysql_num_rows($res)<1)
               { // add in dataset
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
               } elseif (1 == mysql_num_rows($res))
               {
                  $row = mysql_fetch_assoc($res);
                  $ds_uid = $row['id'];
                  $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                           datasets_uid = '$ds_uid', updated_on=NOW(),
                           created_on = NOW()";
                  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                  $de_uid = mysql_insert_id();
               } else {
                  die ("Fatal Error: dataset '$ds_uid'  does not exist at row " . $i);
               }
               
            }
              if (DEBUG>1) {
                  echo "ds uid ".$ds_uid." de uid ".$de_uid."\n";
 
            }
            
         } // end if for datalines
      
      
      /*
       * Insert line into tht-base if check is 0 or 1
       */
         if ($check < 2)
         {
            $check_val ='no';
			if ($check ==1) {
				$check_val ='yes';
			}
			// check if tht_base_uid already exists for this line, check condition, and experiment
            $sql = "SELECT tht_base_uid FROM tht_base
                        WHERE line_record_uid='$line_record_uid' AND experiment_uid='$experiment_uid'
						AND check_line ='$check_val' limit 1";
                           
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");

            if (mysql_num_rows($res)==1)
            {
               $row = mysql_fetch_assoc($res);
               $tht_base_uid = $row['tht_base_uid'];
               $sql = "UPDATE tht_base
                     SET line_record_uid = '$line_record_uid',
                     experiment_uid = '$experiment_uid',";
               if ($check ==1){
                  $sql .= "check_line='yes', datasets_experiments_uid = NULL,
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
               if ($check ==1){
                  $sql .= "check_line='yes', datasets_experiments_uid = NULL,
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
			if (($check ==0)||($check ==1)) {
				for ($j=0;$j<$pheno_num;$j++) {
				   $pheno_uid =$phenoids[$j];
				   $phenotype_data =	$current[$offset+$j];
				    if (DEBUG>2) {echo $phenotype_data."\n";}
					  //put in check for SAS value for NULL
						// check datatype, if continuous or discrete it must be numeric
				   if ((!empty($phenotype_data))&($phenotype_data!=".")) {
						if ((($datatype[$j]=='continuous')||($datatype[$j]=='discrete'))&(!is_numeric($phenotype_data))) {
							echo "Error: Data not continuous: ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
						} 
						//CHeck if phenotype data is within the specified range given in the database.
						// fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15)
						if (abs($phenotype_data)<.00001){
							$phenotype_data=0;
						}
						elseif (($pheno_min[$j]!=$pheno_max[$j])&(($phenotype_data<$pheno_min[$j])||($phenotype_data>$pheno_max[$j]))){
								
								echo "Warning: Out of bounds line:trait:value ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
								//print_r($current);
								//exit;
						} elseif ($check ==0){
						// check if there is existing data for this experiment if yes then update
								$sql = "SELECT phenotype_data_uid FROM phenotype_data
										 WHERE phenotype_uid = '$phenoids[$j]'
											AND tht_base_uid = '$tht_base_uid'";
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
								if ( mysql_num_rows($res)>0)
								{
								   $sql = "UPDATE phenotype_data SET value = '$phenotype_data', updated_on=NOW()
											WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
								} else {
								   $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
										 tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
										 updated_on=NOW(), created_on = NOW()";
								}
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
								
						} elseif ($check ==1) {
							//Insert only as all checklines were deleted at the beginning. The problem
							//occurs when an experiment has multiple values for the same checklines (e.g., MN data)
							if (DEBUG>2) {echo "checkline data ".$phenotype_data."\n";}
							if (!empty($phenotype_data)) {
								$sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
									   tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
									   updated_on=NOW(), created_on = NOW()";
							  
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
							}
						}
				   }
						}
					}
	
		
         } elseif ($check=2)
         {
            /* deal with statistics */
            // read in stats
            // identify which statistic it is based on column 1
            	$col_lookup = array(
                     'trialmean' => 'mean_value','mean' => 'mean_value','se' => 'standard_error','lsd(.05)' => 'standard_error','replications' => 'number_replicates','prob>f' => 'prob_gt_F'
                      );
               $statname = str_replace(" ","",strtolower(trim($current[$COL_CAPENTRYCODE])));
               $fieldname = $col_lookup[$statname];
               if (!empty($fieldname)){
                  if (DEBUG>1) {echo $statname." mapped to ".$fieldname."\n";}
               
                  for ($j=0;$j<$pheno_num;$j++) {
                     $pheno_uid =$phenoids[$j];
                     $phenotype_data =	trim($current[$offset+$j]);
					 // insert NULL value if empty
					 if (empty($phenotype_data)) {
						$phenotype_data = "NULL";
					 } elseif (($fieldname =='mean_value')||($field_name == 'standard_error')||($fieldname =='number_replicates')){
						if (!is_numeric($phenotype_data)) {
							echo "Error:Value not numeric ".$fieldname.":". $phenoname[$j].":".$phenotype_data."\n";
							$phenotype_data = "NULL";
						}
					 }
					 
                     if (DEBUG>2) {echo $phenotype_data."\n";}
                     if (!empty($phenotype_data)) {
                        // check if there are existing statistics data for this experiment if yes then update
                        $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                 WHERE phenotype_uid = '$phenoids[$j]'
                                    AND experiment_uid = '$experiment_uid'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        if ( mysql_num_rows($res)>0)
                        {
                           $sql = "UPDATE phenotype_mean_data SET $fieldname = '$phenotype_data', updated_on=NOW()
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
      } // end skipping a line
   } // end for loop through file
   
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
        		for ($i = 1; $i <= $cols; $i++) {
								$teststr = str_replace(' ','',$means['cells'][1][$i]);
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
  for($i = 2; $i <= $rows; $i++)
   {
   ?>
   <tr>
   <?php
   $current_row = $means['cells'][$i];
   for($j=1; $j<=$cols; $j++)
   		{
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
  	} ?>
		</tbody>
			</table>
			
			
		<input type="Button" value="Accept" onclick="javascript: update_database('<?php echo $meansfile?>','<?php echo $uploadfile?>','<?php echo $username?>','<?php echo $rawdatafile ?>' )"/>
		<input type="Button" value="Cancel" onclick="history.go(-1); return;"/>
	<?php
  
    



}

				 
				 else {
    				error(1,"There was an error uploading the file, please try again!");
    				print "<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">";
							}
							
				
		}

	}
	

	
	} /* end of type_Experiment_Name function*/
	
	
	private function type_Database()
	{
	
	global $config;
		include($config['root_dir'] . 'theme/admin_header.php');

	
	//connect_dev();	/* connecting to development database */
	
	$meansfile = $_GET['expdata'];
	$filename = $_GET['file_name'];
	$username = $_GET['user_name'];
	$rawdatafile = $_GET['raw_data_file'];
	
	$reader = new Spreadsheet_Excel_Reader();
	$reader->setOutputEncoding('CP1251');
	if (strpos($meansfile,'.xls')>0)
	{
		$reader->read($meansfile);
	}else {
		$reader->read($meansfile . ".xls");
	}
	$means = $reader->sheets[0];
	$cols = $reader->sheets[0]['numCols'];
	$rows = $reader->sheets[0]['numRows'];
	
		//echo "nrows ".$rows." ncols ".$cols."\n";
	// get first row of data and get phenotype names and their IDs, if phenotypes not in database,
// then send out an error
/**
 * Required Columns in the means file
 *
 * Tells the script which column is which. (Starting at 1)
 * This implies that the standard form MUST be used for data entry
 * 
 */
	$COL_CAPENTRYCODE=$COL_CAPENTRYNO=$COL_BREEDINGPROGRAM=$COL_CAPYEAR =$COL_CHECK =$COL_EXPTCODE = 0;
	$COL_TRIALCODE = $COL_TRIALYEAR = $COL_EXPTNAME = $COL_LOCATION = $COL_TRIALENTRYNO = $COL_CCRU = $COL_LINENAME = 0;
	
	for ($i = 1; $i <= $cols; $i++) {
		$teststr = str_replace(' ','',$means['cells'][1][$i]);
		
			//echo "teststr in process mean". $teststr . "<br/>";
		
		//echo "means 0 cells 1 i is set to ".$teststr."\n";
		if (stripos($teststr,'breeding')!==FALSE){
		  $COL_BREEDINGPROGRAM = $i;
		} elseif (stripos($teststr,'CAPyear')!==FALSE){
		  $COL_CAPYEAR = $i;
		} elseif (stripos($teststr,'check')!==FALSE){
		  $COL_CHECK = $i;
		} elseif (stripos($teststr,'exptcode')!==FALSE){
		  $COL_EXPTCODE = $i;
		} elseif (stripos($teststr,'trialcode')!==FALSE){
		  $COL_TRIALCODE = $i;
		} elseif(stripos($teststr,'code')!==FALSE) {
			$COL_CAPENTRYCODE = $i;
		}elseif (stripos($teststr,'trialyear')!==FALSE){
		  $COL_TRIALYEAR = $i;
		} elseif (stripos($teststr,'experiment')!==FALSE){
		  $COL_EXPTNAME = $i;
		} elseif (stripos($teststr,'location')!==FALSE){
		  $COL_LOCATION = $i;
		} elseif (stripos($teststr,'trialentry')!==FALSE){
		  $COL_TRIALENTRYNO = $i;
		} elseif (stripos($teststr,'ccru')!==FALSE){
		  $COL_CCRU = $i;
		} elseif (stripos($teststr,'No')!==FALSE){
		  $COL_CAPENTRYNO = $i;
		}elseif (stripos($teststr,'linename')!==FALSE){
		  $COL_LINENAME = $i;
		} 
	}
	//echo "Columns: linename ".$COL_LINENAME." CAPCode ".$COL_CAPENTRYCODE." BP ".$COL_BREEDINGPROGRAM.
		//	" CAPyear ".$COL_CAPYEAR." Check ".$COL_CHECK." Trial ".$COL_TRIALCODE." trial year ".$COL_TRIALYEAR."\n";
	// Check if a required col is missing
	if (($COL_LINENAME*$COL_CAPENTRYCODE*$COL_BREEDINGPROGRAM*$COL_CAPYEAR*$COL_CHECK*$COL_TRIALCODE*$COL_TRIALYEAR)==0) {
		echo "Missing Column: linename ".$COL_LINENAME." CAPCode ".$COL_CAPENTRYCODE." BP ".$COL_BREEDINGPROGRAM.
			" CAPyear ".$COL_CAPYEAR." Check ".$COL_CHECK." Trial ".$COL_TRIALCODE." trial year ".$COL_TRIALYEAR."\n";
		exit;
	}
	$offset = $COL_LINENAME + 1;//column where phenotype data starts

//connect_dev();	/* connecting to development database */


   $phenonames = array();
   $phenoids = array();
	for ($i = $offset; $i <= $cols; $i++)
   {
      $teststr= addcslashes(trim($means['cells'][1][$i]),"\0..\37!@\177..\377");
      
		//	echo "the test string". $teststr."<br/>";      
      if (empty($teststr)){
         break; 
      } else {
         //break column title into pieces
         $teststr= str_replace('\\n',' ',$teststr);
		 $pheno= explode(' ',$teststr);
         $piece = count($pheno);
         $pheno_cur ='';
     //commented    print_r($pheno);
         
         //

         for ($j=0;$j<$piece;$j++){
            $eflg = 0;
            $pheno_cur .= " ".$pheno[$j];
          //  echo "pheno curretn is". $pheno_cur;
            $pheno_cur =trim($pheno_cur);
             $sql = "SELECT phenotype_uid as id,phenotypes_name as name, max_pheno_value as maxphen, min_pheno_value as minphen, datatype
						FROM phenotypes
						WHERE phenotypes_name = '$pheno_cur'";
            
          // commented     echo $pheno_cur." ".$sql."\n";
               
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
            if (1 == mysql_num_rows($res))
            {
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
   
     // print_r($phenonames);
     //print_r($phenoids);
	 // print_r($pheno_max);
	 // print_r($pheno_min);
   

/*
 * Process the means file
 */
 
 
 /**
 * Returns $arg1 if it is set, else fatal error
 */
function ForceValue(& $arg1, $msg)
{
	if (isset($arg1))
	{
		return $arg1;
	}
	die($msg);
}
 
 
 

   $current = NULL;	// the current row
   $num_exp = 0;
   $experiment_uids[$num_exp] = -1;
   
   for($i = 2; $i <= $rows; $i++)
   {
      $current = $means['cells'][$i];
  
 		//	echo "current in process mean". $current . "<br/>";
		
      //check if line is empty, if yes then skip to the next line
      if (!empty($current)) {
        // print_r($current);
		
         // Get required columns
          $check =					ForceValue($current[$COL_CHECK], "Fatal Error: Missing checkvalue at row " . $i);
		  $breeding_program_name =	$current[$COL_BREEDINGPROGRAM];
		  
         $trial_code_new =			$current[$COL_TRIALCODE];
		 
		 if (($check<2)||($trial_code_new!=NULL)){
			$trial_code = $trial_code_new;
		 } 
		 //echo $trial_code." ".$trial_code_new."\n";
         $line_name =				ForceValue($current[$COL_LINENAME], "Fatal Error: Missing line name at row " . $i);
         $CAPentrycode =			$current[$COL_CAPENTRYCODE];
		 
        
		 if (($check > 0)||is_null($check)) {
			$CAPyear = $current[$COL_CAPYEAR];
		 } else {
			$CAPyear = ForceValue($current[$COL_CAPYEAR], "Fatal Error: Missing CAP year at row " . $i);
			$line_name = $CAPentrycode; // for caplines use CAPcode to ID line
		}
         
		 $year =			      $current[$COL_TRIALYEAR];
		 
		 if ($COL_TRIALENTRYNO>0) {
			$trial_entry_no =		trim($current[$COL_TRIALENTRYNO]);
		 } else {$trial_entry_no = NULL;}

   
         if (DEBUG>2) {
            echo $breeding_program_name." ".$trial_code." ".$line_name." ".$check." ".$pheno_num."\n";
         }
         
         
         /*
          * Figure out which experiment to use
          */
       
         $sql = "select experiment_uid as id from experiments where trial_code = '$trial_code'";
         $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
         if (1 == mysql_num_rows($res))
         {
            $experiment = mysql_fetch_assoc($res);
            $experiment_uid = $experiment['id'];
         } elseif (0 == mysql_num_rows($res)) {
            echo "Fatal Error: experiment '$trial_code' does not exist at row " . $i ."<br/><br/>";
						exit("<input type=\"Button\" value=\"Return\" onClick=\"history.go(-1); return;\">");
						
         } else {
            echo "Fatal Error: experiment '$trial_code' matches multiple experiments-must be unique " . $i ."<br/><br/>" ;
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
			 if (1 == mysql_num_rows($res))
            {
               $row = mysql_fetch_assoc($res);
               $BPcode_uid = $row['id'];
            }
            else
            {
				echo "Fatal Error: CAPbreeding program  does not exist at row " . $i . "<br/><br/>";
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
            if (1 == mysql_num_rows($res))
            {
               $row = mysql_fetch_assoc($res);
               $de_uid = $row['id'];
            }
            else
            {
               // set new dataset experiment code
               // get datasets_uid;
               $sql = "SELECT datasets_uid as id
                  FROM  datasets
                  WHERE CAPdata_programs_uid ='$BPcode_uid'
                  AND breeding_year = '$CAPyear'";
				$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
       
                if (mysql_num_rows($res)<1)
               { // add in dataset
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
               } elseif (1 == mysql_num_rows($res))
               {
                  $row = mysql_fetch_assoc($res);
                  $ds_uid = $row['id'];
                  $sql = "INSERT INTO datasets_experiments SET experiment_uid='$experiment_uid',
                           datasets_uid = '$ds_uid', updated_on=NOW(),
                           created_on = NOW()";
                  $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                  $de_uid = mysql_insert_id();
               } else {
                  die ("Fatal Error: dataset '$ds_uid'  does not exist at row " . $i);
               }
               
            }
              if (DEBUG>1) {
                  echo "ds uid ".$ds_uid." de uid ".$de_uid."\n";
 
            }
            
         } // end if for datalines
      
      
      /*
       * Insert line into tht-base if check is 0 or 1
       */
         if ($check < 2)
         {
            $check_val ='no';
			if ($check ==1) {
				$check_val ='yes';
			}
			// check if tht_base_uid already exists for this line, check condition, and experiment
            $sql = "SELECT tht_base_uid FROM tht_base
                        WHERE line_record_uid='$line_record_uid' AND experiment_uid='$experiment_uid'
						AND check_line ='$check_val' limit 1";
                           
            $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");

            if (mysql_num_rows($res)==1)
            {
               $row = mysql_fetch_assoc($res);
               $tht_base_uid = $row['tht_base_uid'];
               $sql = "UPDATE tht_base
                     SET line_record_uid = '$line_record_uid',
                     experiment_uid = '$experiment_uid',";
               if ($check ==1){
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
               if ($check ==1){
                  $sql .= "check_line='yes', datasets_experiments_uid = NULL,
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
			if (($check ==0)||($check ==1)) {
				for ($j=0;$j<$pheno_num;$j++) {
				   $pheno_uid =$phenoids[$j];
				   $phenotype_data =	$current[$offset+$j];
				    if (DEBUG>2) {echo $phenotype_data."\n";}
					  //put in check for SAS value for NULL
						// check datatype, if continuous or discrete it must be numeric
				   if ((!empty($phenotype_data))&($phenotype_data!=".")) {
						if ((($datatype[$j]=='continuous')||($datatype[$j]=='discrete'))&(!is_numeric($phenotype_data))) {
							echo "Error: Data not continuous: ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
						} 
						//CHeck if phenotype data is within the specified range given in the database.
						// fix occasional excel problem with zeros coming up as very small negative numbers (E-12-E-15)
						if (abs($phenotype_data)<.00001){
							$phenotype_data=0;
						}
						elseif (($pheno_min[$j]!=$pheno_max[$j])&(($phenotype_data<$pheno_min[$j])||($phenotype_data>$pheno_max[$j]))){
								
								echo "Warning: Out of bounds line:trait:value ".$line_name.":".$phenonames[$j].$phenotype_data."\n";
								//print_r($current);
								//exit;
						} elseif ($check ==0){
						// check if there is existing data for this experiment if yes then update
								$sql = "SELECT phenotype_data_uid FROM phenotype_data
										 WHERE phenotype_uid = '$phenoids[$j]'
											AND tht_base_uid = '$tht_base_uid'";
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
								if ( mysql_num_rows($res)>0)
								{
								   $sql = "UPDATE phenotype_data SET value = '$phenotype_data', updated_on=NOW()
											WHERE tht_base_uid = '$tht_base_uid' AND phenotype_uid = '$phenoids[$j]'";
								} else {
								   $sql = "INSERT INTO phenotype_data SET phenotype_uid = '$phenoids[$j]',
										 tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
										 updated_on=NOW(), created_on = NOW()";
								}
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
								
						} elseif ($check ==1) {
							//Insert only as all checklines were deleted at the beginning. The problem
							//occurs when an experiment has multiple values for the same checklines (e.g., MN data)
							if (DEBUG>2) {echo "checkline data ".$phenotype_data."\n";}
							if (!empty($phenotype_data)) {
								$sql = "insert into phenotype_data set phenotype_uid = '$phenoids[$j]',
									   tht_base_uid = '$tht_base_uid', value = '$phenotype_data',
									   updated_on=NOW(), created_on = NOW()";
							  
								$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
							}
						}
				   }
						}
					}
	
		
         } elseif ($check=2)
         {
            /* deal with statistics */
            // read in stats
            // identify which statistic it is based on column 1
            	$col_lookup = array(
                     'trialmean' => 'mean_value','mean' => 'mean_value','se' => 'standard_error','lsd(.05)' => 'standard_error','replications' => 'number_replicates','prob>f' => 'prob_gt_F'
                      );
               $statname = str_replace(" ","",strtolower(trim($current[$COL_CAPENTRYCODE])));
               $fieldname = $col_lookup[$statname];
               if (!empty($fieldname)){
                  if (DEBUG>1) {echo $statname." mapped to ".$fieldname."\n";}
               
                  for ($j=0;$j<$pheno_num;$j++) {
                     $pheno_uid =$phenoids[$j];
                     $phenotype_data =	trim($current[$offset+$j]);
					 // insert NULL value if empty
					 if (empty($phenotype_data)) {
						$phenotype_data = "NULL";
					 } elseif (($fieldname =='mean_value')||($field_name == 'standard_error')||($fieldname =='number_replicates')){
						if (!is_numeric($phenotype_data)) {
							echo "Error:Value not numeric ".$fieldname.":". $phenoname[$j].":".$phenotype_data."\n";
							$phenotype_data = "NULL";
						}
					 }
					 
                     if (DEBUG>2) {echo $phenotype_data."\n";}
                     if (!empty($phenotype_data)) {
                        // check if there are existing statistics data for this experiment if yes then update
                        $sql = "SELECT phenotype_mean_data_uid FROM phenotype_mean_data
                                 WHERE phenotype_uid = '$phenoids[$j]'
                                    AND experiment_uid = '$experiment_uid'";
                        $res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
                        if ( mysql_num_rows($res)>0)
                        {
                           $sql = "UPDATE phenotype_mean_data SET $fieldname = '$phenotype_data', updated_on=NOW()
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
      } // end skipping a line
   } // end for loop through file
   
   // Update trait statistics
   $trait_stats = calcPhenoStats_mysql ($phenoids);
   //print_r ($trait_stats);
   ;
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
		if (mysql_num_rows($res)>0)
		{
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
		while ($row = mysql_fetch_array($res)){
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
			
			
			// Add rawdata name to the field for raw_data_file_name, if it was uploaded.
			if ($rawdatafile) {
			$sql = "UPDATE experiments SET raw_data_file_name = '$rawdatafile', updated_on=NOW()
                  WHERE experiment_uid = '$experiment_uids[$i]'";
			$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
			}
			if (DEBUG>2) echo $sql."\n";
		
   }
   //calling this function to calculate the statistics for phenotype data.
   
  // echo"statistics function call";
	 calcPhenoStats_mysql();

	 /* Some leftover debugging code?:
// testing recent data
$sql = "select input_data_file_name  from experiments where experiment_uid = '10'";
$res = mysql_query($sql) or die(mysql_error() . "<br>$sql");
//$row = mysql_fetch_array($res);
while ($row = mysql_fetch_array($res)){
				$data[]=$row['input_data_file_name'];
		}
//$data = explode(",",$row);
echo"input data files";
print_r($data);
	 */




// end of testing recent data 
	
	
   	echo " <b>The Means file was inserted/updated successfully </b>";
   	echo"<br/><br/>";
	?>
	<a href="./curator_data/input_experiments_upload.php"> Go Back To Main Page </a>
	<?php
   
	$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	
	}/* end of type_database function */
	
	

	
	

} /* end of class */

?>




























