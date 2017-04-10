<?php
/**
 * Display phenotype information for experiment
 *
 * PHP version 5.3
 *
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/display_phenotype.php
 *
 */

//A php script to dynamically read data related to a particular experiment from the database and to 
//display it in a nice table format. Utilizes the the tableclass Class by Manuel Lemos to display the 
//table.

// author Kartic Ramesh; drastically rewritten by Julie Dickerson, 2009 to make usable and use sessions

// 08/22/2012 DEM  Display multiple raw files, table rawfiles. 
// 08/17/2012 DEM  Display GRIN Accession instead of Line Synonym, for Jorge Dubcovsky.
// 03/25/2011 DEM  Oops, the Collaborator should be the one in table phenotype_experiment_info.
// 02/07/2011 DEM  Add CAPdata_program and Collaborator to the first table.
// 01/12/2011 JLee Add so experiment download data displays on separate page
// 01/12/2011 JLee Mod so mean and std.err values in experiment datafile do have signif digit applied to them   
// 10/07/2010 DEM Stop rounding-off values when exported via "Download Experiment Data".
// 9/30/2010 DEM Fixed comma-separated header line in tab-delimited "Download Experiment Data" output.
// 9/30/2010 DEM Add "Experiment" to display list. 
// 9/28/2010 J.Lee Add "Number of Entries" to display list
// 9/22/2010 DEM Output Source files loaded at the bottom of the page.
// 9/22/2010 DEM Output CAP Code for each germplasm line as column 2 of the data table.
// 8/19/2010 DEM Fixed scrolling table to work in IE too.
// 6/29/2010 J.Lee Fixed table display issue with MSIE7 and realign dataset download button
// 6/24/2010 J.Lee Merged with Julie's changes 
// 3/01/2010 J.Lee Handle missing Raw Data files 
// 2/18/2010 J.Lee Fix "Download Raw Data" button not showing with IE browser 

session_start();
require 'config.php';
require $config['root_dir'].'includes/bootstrap2.inc';
require $config['root_dir'].'theme/normal_header.php';
$delimiter = "\t";
$mysqli = connecti();

$trial_code=strip_tags($_GET['trial_code']);
// Display Header information about the experiment
$display_name=ucwords($trial_code); //used to display a beautiful name as the page header
        
// Restrict if private data.
if ($stmt = mysqli_prepare($mysqli, "SELECT data_public_flag FROM experiments WHERE trial_code = ?")) {
    mysqli_stmt_bind_param($stmt, "s", $trial_code);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $data_public_flag);
    if (mysqli_stmt_fetch($stmt)) {
        echo "<h1>Trial ".$display_name."</h1>";
    } else {
        mysqli_stmt_close($stmt);
        die("Error: trial not found\n");
    }
    mysqli_stmt_close($stmt);
} else {
    die("Error: bad sql statement\n");
}
if (($data_public_flag == 0) and
    (!authenticate(array(USER_TYPE_PARTICIPANT, USER_TYPE_CURATOR, USER_TYPE_ADMINISTRATOR)))) {
    echo "Results of this trial are restricted to project participants.";
} else {
    $sql="SELECT experiment_uid, experiment_set_uid, experiment_desc_name, experiment_year
          FROM experiments WHERE trial_code = ?";
    if ($stmt = mysqli_prepare($mysqli, $sql)) {
        mysqli_stmt_bind_param($stmt, "s", $trial_code);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $experiment_uid, $set_uid, $exptname, $year);
        if (!mysqli_stmt_fetch($stmt)) {
            mysqli_stmt_close($stmt);
            die("Error: trial not found\n");
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error: bad sql statement\n");
    }
    $datasets_exp_uid=$experiment_uid;
    if (!$experiment_uid) {
        die("Trial $trial_code not found.");
    }
    $query="SELECT * FROM phenotype_experiment_info WHERE experiment_uid='$experiment_uid'";
    $result_pei=mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $row_pei=mysqli_fetch_array($result_pei);

    // Get Experiment (experiment_set) too.
    if ($set_uid) {
        $exptset = mysql_grab("SELECT experiment_set_name from experiment_set where experiment_set_uid=$set_uid");
    }
    // Get CAPdata_program too.
    $query="SELECT data_program_name, collaborator_name 
	  from CAPdata_programs, experiments
	  where experiment_uid = $experiment_uid
	  and experiments.CAPdata_programs_uid = CAPdata_programs.CAPdata_programs_uid";
    $result_cdp=mysqli_query($mysqli, $query) or die(mysqli_error($mysqli));
    $row_cdp=mysqli_fetch_array($result_cdp);
    $dataprogram = $row_cdp['data_program_name'];

    echo "<table>";
    if ($exptset) {
        echo "<tr> <td>Experiment</td><td>".$exptset."</td></tr>";
    }
    echo "<tr> <td>Trial Year</td><td>$year</td></tr>";
    if ($exptname) {
        echo "<tr> <td>Description</td><td>$exptname</td></tr>";
    }
    echo "<tr> <td>Location (Latitude/Longitude)</td><td>".$row_pei['location']." ("
              .$row_pei['latitude']." / ".$row_pei['longitude'].")</td></tr>";
    echo "<tr> <td>Collaborator</td><td>".$row_pei['collaborator']."</td></tr>";
    echo "<tr> <td>Planting Date</td><td>".$row_pei['planting_date']."</td></tr>";
    echo "<tr> <td>Harvest Date</td><td>".$row_pei['harvest_date']."</td></tr>";
    echo "<tr> <td>Begin Weather Date</td><td>".$row_pei['begin_weather_date']."</td></tr>";
    echo "<tr> <td>Greenhouse?</td><td>".$row_pei['greenhouse_trial']."</td></tr>";
    echo "<tr> <td>Seeding Rate (plants/m<sup>2</sup>)</td><td>".$row_pei['seeding_rate']."</td></tr>";
    echo "<tr> <td>Experiment Design</td><td>".$row_pei['experiment_design']."</td></tr>";
    echo "<tr> <td>Plot Size (m<sup>2</sup>)</td><td>".$row_pei['plot_size']."</td></tr>";
    echo "<tr> <td>Harvest Area (m<sup>2</sup>)</td><td>".$row_pei['harvest_area']."</td></tr>";
    echo "<tr> <td>Irrigation</td><td>".$row_pei['irrigation']."</td></tr>";
    echo "<tr> <td>Number of Entries</td><td>".$row_pei['number_entries']."</td></tr>";
    echo "<tr> <td>Number of Replications</td><td>".$row_pei['number_replications']."</td></tr>";
    echo "<tr> <td>Comments</td><td>".$row_pei['other_remarks']."</td></tr>";
    echo "<tr> <td>Data Program</td><td>".$dataprogram."</td></tr>";
    echo "</table><p>";

    // get all line data for this experiment
    $sql="SELECT tht_base_uid, line_record_uid, check_line FROM tht_base WHERE experiment_uid='$experiment_uid'";
    $result_thtbase=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        
    while ($row_thtbase=mysqli_fetch_array($result_thtbase)) {
         $thtbase_uid[] = $row_thtbase['tht_base_uid'];
         $linerecord_uid[] = $row_thtbase['line_record_uid'];
         $check_line[] = $row_thtbase['check_line'];
         //echo $row_thtbase['tht_base_uid']."  ".$row_thtbase['line_record_uid']."  ".$row_thtbase['check_line']."<br>";
    }
    $num_lines = count($linerecord_uid);

    $num_phenotypes = 0;
    $sql="SELECT count(distinct(phenotype_uid)) from tht_base, phenotype_data
          WHERE tht_base.tht_base_uid = phenotype_data.tht_base_uid
          AND experiment_uid = $experiment_uid";
    $result_thtbase=mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row_thtbase=mysqli_fetch_array($result_thtbase)) {
        $num_phenotypes = $row_thtbase[0];
    }

    //echo $num_lines."<br>";
    $titles=array('Line Name'); //stores the titles for the display table with units
    $titles[]="GRIN Accession";//add CAP Code column to titles

    if ($num_phenotypes > 100) {
        echo "$num_lines lines<br>$num_phenotypes phenotypes measured<br>\n";
        echo "<font color=\"red\">Warning: Values will not be displayed if there are over 100 phenotypes measuered.</font><br>\n";
    } elseif (!empty($thtbase_uid)) {
        $thtbasestring = implode(",", $thtbase_uid);
        $sql1="SELECT DISTINCT p.phenotypes_name as name, p.phenotype_uid as uid, units.unit_name as unit, units.sigdigits_display as sigdig
                FROM phenotype_data as pd, phenotypes as p, units
                WHERE p.phenotype_uid = pd.phenotype_uid
                AND units.unit_uid = p.unit_uid
                AND pd.tht_base_uid IN ($thtbasestring)";
        //echo $sql1."<br>";
        $result1=mysqli_query($mysqli, $sql1) or die(mysqli_error($mysqli));
        $num_phenotypes = mysqli_num_rows($result1);

        //echo "$num_phenotypes Rows\n";
        while ($row1=mysqli_fetch_array($result1)) {
            $phenotype_data_name[]=$row1['name'];
            $phenotype_uid[]=$row1['uid'];
            $unit_sigdigits[]=$row1['sigdig'];
            $unit_name[]=$row1['unit'];
            $titles[]=ucwords($row1['name'])." (".strtolower($row1['unit']).")";
        }
        
        $titles[]="Check"; //add the check column to the display table
   
        $all_rows=array(); //2D array that will hold the values in table format to be displayed
        $sum_rows=array(); //summary statistics
        $all_rows_long=array(); // For the full unrounded values
        $single_row=array(); //1D array which will hold each row values in the table format to be displayed
        $single_row_long=array();
        
        /* $dir ='./downloads/temp/';				 */
        $dir ='/tmp/tht/';
        if (! file_exists('/tmp/tht')) {
             mkdir('/tmp/tht');
        }

        // Clean up old files, older than 1 day.
        system("find $dir -mtime +1 -name 'THT_Phenotypes_*.txt' -delete");

        $stringData = implode($delimiter, $titles);
         
        //---------------------------------------------------------------------------------------------------------------
        //Go through lines to create a data table for display
        for ($lr_i=0; $lr_i<$num_lines; $lr_i++) {
            $thtbaseuid=$thtbase_uid[$lr_i];
            $linerecorduid=$linerecord_uid[$lr_i];
            //echo $linerecorduid."  ".$thtbaseuid."<br>";
            
            $sql_lnruid="SELECT line_record_name FROM line_records WHERE line_record_uid='$linerecorduid'";
            $result_lnruid=mysqli_query($mysqli, $sql_lnruid) or die(mysqli_error($mysqli));
            $row_lnruid=mysqli_fetch_assoc($result_lnruid);
            $lnrname=$row_lnruid['line_record_name'];
            $single_row[0]=$lnrname;
            $single_row_long[0]=$lnrname;

/* Use GRIN accession instead of Synonym */
/* // get the CAP code */
/* $sql_cc="SELECT line_synonym_name */
/* FROM line_synonyms */
/* WHERE line_synonyms.line_record_uid = '$linerecorduid'"; */
/* 	    $result_cc=mysql_query($sql_cc) or die(mysql_error()); */
/* 	    $row_cc=mysql_fetch_assoc($result_cc); */
/* 	    $single_row[1]=$row_cc['line_synonym_name']; */
/* 	    $single_row_long[1]=$row_cc['line_synonym_name']; */
            $sql_gr="select barley_ref_number
             from barley_pedigree_catalog bc, barley_pedigree_catalog_ref bcr
             where barley_pedigree_catalog_name = 'GRIN'
             and bc.barley_pedigree_catalog_uid = bcr.barley_pedigree_catalog_uid
             and bcr.line_record_uid = '$linerecorduid'";
            $result_gr=mysqli_query($mysqli, $sql_gr) or die(mysqli_error($mysqli));
            $row_gr=mysqli_fetch_assoc($result_gr);
            $single_row[1]=$row_gr['barley_ref_number'];
            $single_row_long[1]=$row_gr['barley_ref_number'];

            for ($i=0; $i<$num_phenotypes; $i++) {
                $puid=$phenotype_uid[$i];
                $sigdig=$unit_sigdigits[$i];
                $sql_val="SELECT value FROM phenotype_data
                    WHERE tht_base_uid='$thtbaseuid'
                    AND phenotype_uid = '$puid'";
                //echo $sql_val."<br>";
                $result_val=mysqli_query($mysqli, $sql_val);
                if (mysqli_num_rows($result_val) > 0) {
                    $row_val=mysqli_fetch_assoc($result_val);
                    $val=$row_val['value'];
                    $val_long=$val;
                    if ($sigdig >= 0) {
                        $val = floatval($val);
                        $val=number_format($val, $sigdig);
                    }
                } else {
                    $val = "--";
                    $val_long = "--";
                }
                if (empty($val)) {
                    $val = "--";
                    $val_long = "--";
                }
                $single_row[$i+2]=$val;
                $single_row_long[$i+2]=$val_long;
            }
        //-----------------------------------------check line addition

            if ($check_line[$lr_i]=='yes') {
                $check=1;
            } else {
                $check=0;
            }
            //echo $check;
            $single_row[$num_phenotypes+2]=$check;
            $single_row_long[$num_phenotypes+2]=$check;
            //-----------------------------------------
            //var_dump($single_row_long);
            $stringData= implode($delimiter, $single_row_long);
            
            $all_rows[]=$single_row;
            $all_rows_long[]=$single_row_long;
        }
            //-----------------------------------------get statistics
        $mean_arr=array('Mean','');
        $se_arr=array('Standard Error','');
        // Unformatted mean and SE
        $unformat_mean_arr=array('Mean','');
        $unformat_se_arr=array('Standard Error','');
 
        $nr_arr=array('Number Replicates','');
        $prob_arr=array('Prob > F','');
            
        $fmean="Mean,";
        $fse="SE,";
        $fnr="Number Replicates,";
        $fprob="Prob gt F,";
         
        for ($i=0;$i<$num_phenotypes;$i++) {
            $puid=$phenotype_uid[$i];
            $sigdig=$unit_sigdigits[$i];
         
            $sql_mdata="SELECT mean_value,standard_error,number_replicates,prob_gt_F
                FROM phenotype_mean_data
                WHERE phenotype_uid='$puid'
                AND experiment_uid='$experiment_uid'";
            $res_mdata=mysqli_query($mysqli, $sql_mdata) or die(mysqli_error($mysqli));
            $row_mdata=mysqli_fetch_array($res_mdata);
            $mean=$row_mdata['mean_value'];
            $se=$row_mdata['standard_error'];
            $nr=$row_mdata['number_replicates'];
            $prob=$row_mdata['prob_gt_F'];
        
            if($mean!=0) {	
                $unformat_mean_arr[] = $mean;
                if ($sigdig>=0) $mean=number_format($mean,$sigdig);
                $mean_arr[] = $mean;
            } else {
                $unformat_mean_arr[] = "--";
                $mean_arr[]="--";
            }
            
            if($se!=0) {	
                $unformat_se_arr[] = $se;                
                if ($sigdig>=0) $se=number_format($se,$sigdig);
                $se_arr[] = $se;
            } else {	
                $se_arr[]="--";
                $unformat_se_arr[] = "--";
            }
            
            if($nr==0) {
                $nr="--";
            }
            $nr_arr[]=$nr;

# prob_gt_F is a varchar and can have nonnumeric values like "<.0001".  
            if($prob!="" && $prob!="NULL") {
#                $prob=number_format($prob,2);
                $prob_arr[]=$prob;
             } else {
                $prob_arr[]="--";
            }
        
        }
        
        $fmean= implode($delimiter, $mean_arr)."\n";
        $fse= implode($delimiter, $se_arr)."\n";
        $fnr= implode($delimiter, $nr_arr)."\n";
        $fprob= implode($delimiter, $prob_arr)."\n";

        $ufmean= implode($delimiter, $unformat_mean_arr)."\n";
        $ufse= implode($delimiter, $unformat_se_arr)."\n";
        
        $sum_rows[]=$mean_arr;
        $sum_rows[]=$se_arr;
        $sum_rows[]=$nr_arr;
        $sum_rows[]=$prob_arr;
        $all_rows_long[]=$mean_arr;
        $all_rows_long[]=$se_arr;
        $all_rows_long[]=$nr_arr;
        $all_rows_long[]=$prob_arr;
        
        //-----------------------------------------
        $total_rows=count($all_rows); //used to determine the number of rows to be displayed in the result page
?>
       
<!--Style sheet for better user interface-->
<style type="text/css">
	th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
	table {background: none; border-collapse: collapse}
	td {border: 1px solid #eee !important;}
	h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>

<script type="text/javascript">

function output_file(url) {
        window.open(url);
}
function output_file2(puid) {
    url = "download_phenotype.php?function=downloadMean&pi=" + puid;
    window.open(url);
}

function output_file_plot(puid) {
    url = "download_phenotype.php?function=downloadPlot&pi=" + puid;
    window.open(url);
}
</script>

<!-- Calculate the width of the table based on the number of columns. -->		
<?php $tablewidth = count($single_row) * 92 + 10;  ?>
			  
<div style="width: <?php echo $tablewidth; ?>px">
<table>
        <tr> 
		
        <?php
        for($i=0;$i<count($titles);$i++)
        {
        ?>
			<th><div style="width: 75px;">
			<?php echo $titles[$i]?>
			</div></th>
        <?php
        }
        ?>
        </tr>
</table>
</div>

<div style="padding: 0; width: <?php echo $tablewidth; ?>px; height: 400px; overflow: scroll; overflow-x: hidden; border: 1px solid #5b53a6; clear: both"> 
<table>
			<?php
				for ($i = 0; $i < count($all_rows); $i++)
				{
			?>
			<tr>
			<?php
				for ($j = 0; $j < count($single_row); $j++)
				{
			?>
			<!-- <td><div style="width: 75px; overflow-x: hidden;"> -->
			<td><div style="width: 75px; word-wrap: break-word">
			<?php echo $all_rows[$i][$j] ?>
			</div></td> 
			<?php
				}/* end of for j loop */
			?>
			</tr>
			<?php
			}/* end of for i loop */
			?>
</table>
</div>
<div style="padding: 0; width: <?php echo $tablewidth; ?>px; border: 1px solid #5b53a6; clear: both">
<table>
    <?php
    for ($i = 0; $i < 4; $i++) {
        echo "<tr>";
        for ($j = 0; $j < count($single_row); $j++) {
            echo "<td><div style=\"width: 75px; word-wrap: break-word\">";
            echo $sum_rows[$i][$j];
            echo "</div></td>";
        }
    }
    ?>
</table>
</div>			
        
<?php
}
    echo "<br>";
    echo "<form>";
    echo "<input type='button' value='Download Trial Data' onclick=\"javascript:output_file2('$experiment_uid');\" />";
    echo "</form>";

    $sql = "select experiment_uid from phenotype_plot_data where experiment_uid = $experiment_uid";
    $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if ($row = mysqli_fetch_row($res)) {
        echo "<form>";
        echo "<input type='button' value='Download Plot Data' onclick=\"javascript:output_file_plot('$experiment_uid');\" />";
        echo "</form>";
    }

$sourcesql="SELECT input_data_file_name FROM experiments WHERE trial_code='$trial_code'";
$sourceres=mysqli_query($mysqli, $sourcesql) or die(mysqli_error($mysqli));
$sourcerow=mysqli_fetch_array($sourceres);
$sources=$sourcerow['input_data_file_name'];
if ($sources)
  echo "<p><b>Means file:</b> $sources";

echo "<p><b>Raw data files:</b> ";
$rawsql="SELECT name, directory from rawfiles where experiment_uid = $experiment_uid";
$rawres=mysqli_query($mysqli, $rawsql) or die(mysqli_error($mysqli));
while ($rawrow = mysqli_fetch_assoc($rawres)) {
  $rawfilename=$rawrow['name'];
  $rawdir = $rawrow['directory'];
  if ($rawdir)
    $rawfilename=$rawrow['directory']."/".$rawfilename;
  $rawfile="raw/phenotype/".$rawfilename;
  echo "<a href=".$config['base_url'].$rawfile.">".$rawrow['name']."</a><br>";
}
if (empty($rawfilename))  echo "none<br>";

echo "<p><b>Field Book:</b> ";
$rawsql="SELECT experiment_uid from fieldbook where experiment_uid = $experiment_uid";
$rawres=mysqli_query($mysqli, $rawsql) or die(mysqli_error($mysqli));
if ($rawrow = mysqli_fetch_assoc($rawres)) {
  $fieldbook="display_fieldbook.php?function=display&uid=$experiment_uid";
  echo "<a href=".$config['base_url'].$fieldbook.">$trial_code</a><br>\n";
}
if (empty($fieldbook)) echo "none";  
$pheno_str = "";
$rawsql="SELECT distinct(phenotypes_name) from phenotype_plot_data, phenotypes where phenotype_plot_data.phenotype_uid = phenotypes.phenotype_uid and experiment_uid = $experiment_uid";
$rawres=mysqli_query($mysqli, $rawsql) or die(mysqli_error($mysqli));
while ($rawrow = mysqli_fetch_assoc($rawres)) {
    if ($pheno_str == "") {
        $pheno_str = $rawrow['phenotypes_name'];
    } else {
        $pheno_str = $pheno_str . ", " . $rawrow['phenotypes_name'];
    }
}
if ($pheno_str != "") {
    echo "<b>Display Numeric map:</b> <a href=".$config['base_url']."display_map_exp.php?uid=$experiment_uid>$pheno_str</a><br>\n";
    echo "<b>Display Heat map:</b> <a href=".$config['base_url']."display_heatmap_exp.php?uid=$experiment_uid>$pheno_str</a><br>\n";
}

$found = 0;
$sql="SELECT date_format(measure_date, '%m-%d-%Y'), date_format(start_time, '%H:%i'), spect_sys_uid, raw_file_name, measurement_uid from csr_measurement where experiment_uid = $experiment_uid order by measure_date";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($row = mysqli_fetch_array($res)) {
  if ($found == 0) {
    echo "<table><tr><td>Measured Date<td>CSR Annotation<td>CSR Data<td>Spectrometer<br>System<td>CSR Data\n";
    $found = 1;
  }
  $date = $row[0];
  $time = $row[1];
  $sys_uid = $row[2];
  $raw_file = $row[3];
  $measurement_uid = $row[4];
  $trial="display_csr_exp.php?function=display&uid=$measurement_uid";
  $tmp2 = $config['base_url'] . "raw/phenotype/" . $raw_file;
  echo "<tr><td>$date $time";
  echo "<td><a href=".$config['base_url'].$trial.">View</a>";
  echo "<td><a href=\"$tmp2\">Open File</a>";

  $sql="SELECT system_name from csr_system where system_uid = $sys_uid";
  $res2 = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if ($rawrow = mysqli_fetch_assoc($res2)) {
    $system_name = $rawrow["system_name"];
    $trial= $config['base_url'] . "display_csr_spe.php?function=display&uid=$sys_uid";
    echo "<td><a href=$trial>$system_name</a>";
  } else {
    echo "<td>missing";
  }
  $trial= $config['base_url'] . "curator_data/cal_index.php";
  echo "<td><a href=$trial>Calculate Index</a>";
}
echo "</table>";
}
  
    //-----------------------------------------------------------------------------------
    $footer_div = 1;
    require $config['root_dir'].'theme/footer.php';
    ?>
