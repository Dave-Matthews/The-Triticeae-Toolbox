<?php
/**
 * Import CSR fieldbook
 *
 * PHP version 5.3
 * Prototype version 1.5.0
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_csr_field.php
 *
 */

require 'config.php';
/*
 * Logged in page initialization
 */
require $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();


new Experiments($_GET['function']);

/** Using a PHP class to implement Field Book import
 *
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/input_csr_field.php
 **/

class Experiments
{
    /**
    * Using the class's constructor to decide which action to perform
    * @param string $function action to perform
    */
    public function __construct($function = null)
    {
        switch ($function) {
            case 'display':
                $this->typeDisplay();
                break;
            default:
                $this->typeExperiments(); /* intial case*/
                break;
        }
    }

/**
 * display database fieldbook value for a specified experiment
 */
private function typeDisplay() {
  global $config;
  global $mysqli;
  include($config['root_dir'] . 'theme/admin_header.php');
  if (isset($_GET['uid'])) {
    $experiment_uid = $_GET['uid'];
  } else {
    die("Error - no experiment found<br>\n");
  }
  $sql = "select trial_code from experiments where experiment_uid = $experiment_uid";
  $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli));
  if ($row = mysqli_fetch_assoc($res)) {
    $trial_code = $row["trial_code"];
  } else {
    die("Error - invalid uid $uid<br>\n");
  }

  //get line names
  $sql = "select line_record_uid, line_record_name from line_records";
  $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli));
  while ($row = mysqli_fetch_assoc($res)) {
    $uid = $row["line_record_uid"];
    $line_name = $row["line_record_name"];
    $line_list[$uid] = $line_name;
  } 

  $count = 0;
  $sql = "select * from fieldbook where experiment_uid = $experiment_uid order by plot";
  $res = mysqli_query($mysqli,$sql) or die (mysqli_error($mysqli));
  echo "<h2>Field Book for $trial_code</h2>\n";
  echo "<table>";
  echo "<tr><td>plot<td>line_name<td>row<td>column<td>entry<td>replication<td>block<td>subblock<td>treatment<td>block_tmt<td>subblock_tmt<td>check<td>Field_ID<td>note";
  while ($row = mysqli_fetch_assoc($res)) {
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
 * wrapper to load header and footer
 */
private function typeExperiments()
	{
		global $config;
                global $mysqli;
		include($config['root_dir'] . 'theme/admin_header.php');

		echo "<h2>Add CSR Field Book</h2>"; 
		
			
		$this->type_Experiment_Name();

		$footer_div = 1;
        include($config['root_dir'].'theme/footer.php');
	}
	
/**
 * form to load fieldbook data
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

<form action="curator_data/input_csr_field_check.php" method="post" enctype="multipart/form-data">
  <table>
  <!-- tr><td><strong>Trial Name:</strong><td-->
  <!-- select name="exper_uid"-->
<?php
//echo "<option>select a trial</option>\n";
$sql = "select trial_code, experiment_uid, experiment_year from experiments where experiment_type_uid = 1 order by experiment_year desc, trial_code";
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
while ($row = mysqli_fetch_assoc($res)) {
  $tc = $row['trial_code'];
  $uid = $row['experiment_uid'];
  $trial_list[$uid] = $tc;
  //echo "<option value='$uid'>$tc</option>\n";
}
//echo "</select>\n";
?>
  <tr><td><strong>Field Book File:</strong><td><input id="file[]" type="file" name="file[]" size="50%" /><td>
  <a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/fieldbook_template.xlsx">Field Book Template</a>
  <td><font color=red>Updated 06-24-2013</font>
  </table>
  <p><input type="submit" value="Upload" /></p>
</form>

<!--a href=login/edit_csr_field.php>Edit Field Book Table</a><br-->
<div id="step2">		
<?php

//list links to saved Excel Files
echo "<br>List of currently loaded Field Book files<br>\n";
echo "<table border=1>\n";
echo "<tr><td>experiment<td>Database<td>Input File<td>created on<td>updated on\n";
$sql = "select experiment_uid, fieldbook_file_name, created_on, updated_on from fieldbook_info where experiment_uid IN (select distinct experiment_uid from fieldbook)";
$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
while ($row = mysqli_fetch_assoc($res)) {
  $name = $row['experiment_uid'];
  $file = $row['fieldbook_file_name'];
  $date1 = $row['created_on'];
  $date2 = $row['updated_on'];
  $tmp1 = "curator_data/input_csr_field.php?function=display&name=$name";
  $tmp2 = $config['base_url'] . $file;
  echo "<tr><td>$trial_list[$name]<td><input type=button value=\"View Data\" onclick=\"javascript:display($name)\"><td><a href=$tmp2>Open</a><td>$date1<td>$date2";
}
echo "</table>";
echo "</div>";
  

	} /* end of type_Experiment_Name function*/
} /* end of class */

?>
