<?php
/**
 * Canopy Spectral Reflectance
*
* PHP version 5.3
* Prototype version 1.5.0
*
* @category PHP
* @package  T3
* @author   Clay Birkett <clb343@cornell.edu>
* @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
* @version  GIT: 2
* @link     http://triticeaetoolbox.org/wheat/curator_data/cal_index.php
*
*/

require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();
$mysqli = connecti();
//loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
//ob_start();
//authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_PARTICIPANT));
//ob_end_flush();


new Experiments($_GET['function']);

/** CSR phenotype experiment
 * 
 * @author claybirkett
 *
 */

class Experiments
{
	
	/**
	 * Using the class's constructor to decide which action to perform
	 * @param string $function action to perform
	 */
	public function __construct($function = null)
	{	
		switch($function)
		{
                    case 'display':
                                $this->typeDisplay();
                                break;							
					
		    default:
				$this->typeExperiments(); /* intial case*/
				break;
			
		}	
	}

/**
 * display the file that has been loaded
 */
private function typeDisplay() {
  global $config;
  include($config['root_dir'] . 'theme/admin_header.php');
  if (isset($_GET['uid'])) {
    $experiment_uid = $_GET['uid'];
  } else {
    die("Error - no experiment found<br>\n");
  }
  $sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
  $res = mysql_query($sql) or die (mysql_error());
  if ($row = mysql_fetch_assoc($res)) {
    $trial_code = $row["trial_code"];
  } else {
    die("Error - invalid uid $uid<br>\n");
  }

  //get line names
  $sql = "select line_record_uid, line_record_name from line_records";
  $res = mysql_query($sql) or die (mysql_error());
  while ($row = mysql_fetch_assoc($res)) {
    $uid = $row["line_record_uid"];
    $line_name = $row["line_record_name"];
    $line_list[$uid] = $line_name;
  } 

  $count = 0;
  $sql = "select * from fieldbook order by plot";
  $res = mysql_query($sql) or die (mysql_error());
  echo "<h2>Field Book for $trial_code</h2>\n";
  echo "<table>";
  echo "<tr><td>plot<td>line_name<td>row<td>column<td>entry<td>replication<td>block<td>subblock<td>treatment<td>block_tmt<td>subblock_tmt<td>check<td>Field_ID<td>note";
  while ($row = mysql_fetch_assoc($res)) {
    $expr = $row["experiment_uid"];
    $range = $row["range_id"];
    $plot = $row["plot"];
    $entry = $row["entry"];
    $line_uid = $row["line_uid"];
    $field_id = $row["field_id"];
    $note = $row["note"];
    $rep = $row["replication"];
    $block = $row["block"];
    $subblock = $row["subblock"];
    $row_id = $row["row_id"];
    $col_id = $row["column_id"];
    $treatment = $row["treatment"];
    $main_plot_tmt = $row["block_tmt"];
    $subblock_tmt = $row["subblock_tmt"];
    $check = $row["check_id"];
    echo "<tr><td>$plot<td>$line_list[$line_uid]<td>$row_id<td>$col_id<td>$entry<td>$rep<td>$block<td>$subblock<td>$treatment<td>$main_plot_tmt<td>$subblock_tmt<td>$check<td>$field_id<td>$note\n";
    $count++;
  }
  echo "</table>";
}

/**
 * wrapper to display header and footer for the input form
 */
private function typeExperiments()
	{
		global $config;
                global $mysqli;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Calculate Canopy Spectral Reflectance (CSR) Index</h2>"; 
                echo "The CSR indices are calculated from plot phenotype data and may be used to predict plant performance. Select ";	
                echo " a Trial and Index then click the Calculate button. The wavelength paramaters (W1, W2) and the formula ";
                echo "may be modified from their default values.<br><br>";
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
	/**
	 * display input form
	 */
	private function type_Experiment_Name()
	{
            global $config;
            global $mysqli;
	?>

<style type="text/css">
  th {background: #5B53A6 !important; color: white !important; border-left: 2px solid #5B53A6}
  table {background: none; border-collapse: collapse}
  td {border: 0px solid #eee !important;}
  h3 {border-left: 4px solid #5B53A6; padding-left: .5em;}
</style>
<script type="text/javascript" src="curator_data/csr.js"></script>

<!-- <p><strong>Note: </strong><font size="2px">Please load the corresponding
    <a href="<?php echo $config['base_url'] ?>curator_data/input_annotations_upload_excel.php">Phenotype 
      Experiment Annotations</a> file before uploading the results files. </font></p> -->

<form action="curator_data/cal_index_check.php" enctype="multipart/form-data">
  <table>
  <tr><td><strong>Trial:</strong><td>
  <select id="trial" name="trial" onchange="javascript: update_trial()">
<?php
/*echo "<option value=''>select a trial</option>\n";*/
$sql = "select trial_code, measurement_uid, date_format(measure_date,'%m-%d-%y') from experiments, csr_measurement where experiments.experiment_uid = csr_measurement.experiment_uid"; 
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
echo "<option>select a trial</option>\n";
while ($row = mysqli_fetch_row($res)) {
  $tc = $row[0];
  $mid = $row[1];
  $date = $row[2];
  $trial_list[$uid] = $tc;
  echo "<option value=\"$mid\">$tc $date</option>";
}
?>
</select>
  <tr><td><strong>Smoothing:</strong><td>
  <select id="smooth" name="smooth" onchange="javascript: update_smooth()">
  <option value="0">0</option>
  <option value="5">11</option>
  <option value="10">21</option>
  </select>
  <td id="smooth2">no smoothing
 
  <tr><td><strong>Index:</strong><td>
  <select id="formula1" name="formula1" onchange="javascript: update_f1()">
  <option value=''>Select a formula</option>
  <option value="SR">SR</option>
  <option value="NWI1">NWI 1</option>
  <option value="NWI3">NWI 3</option>
  <option value="NDVI">NDVI</option>
  <option value="NDVIR">NDVI Red</option>
  <option value="NDVIG">NDVI Green</option>
  <option value="OSAVI">OSAVI</option>
  <option value="TCARI">TCARI</option>
  </select>
  <td id="formdesc">
  <tr><td><strong>W1:</strong><td><input type="text" id="W1" name="W1" onchange="javascript: update_w1()">
  <tr><td><strong>W2:</strong><td><input type="text" id="W2" name="W2" onchange="javascript: update_w2()">
  <tr><td><strong>W3:</strong><td><input type="text" id="W3" name="W3" onchange="javascript: update_w3()">
  <tr><td><strong>Formula:</strong><td><input type="text" id="formula2" name="formula2" onchange="javascript: update_f2()">
  <tr><td><strong>plot CSR:</strong><td>
  <input type="radio" name="xrange" value="zoomout" onchange="javascript: update_zoom(this.form)">entire range
  <input type="radio" name="xrange" value="zoomin" checked onchange="javascript: update_zoom(this.form)">within (W1,W2,W3)
  </table>
  <p><input type="button" value="Calculate" onclick="javascript:cal_index()"/></p>
</form>

<!--a href=login/edit_csr_field.php>Edit Field Book Table</a><br-->
<div id="step1" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
</div>
<div id="step2" style="clear: both; float: left; margin-bottom: 1.5em; width: 100%">
<img alt="spinner" id="spinner" src="images/ajax-loader.gif" style="display:none;" />
</div>
<?php
	} /* end of type_Experiment_Name function*/
} /* end of class */

?>
