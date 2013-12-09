<?php
/**
 * Download Gateway New
 * 
 * PHP version 5.3
 * Prototype version 1.5.0
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @version  GIT: 2
 * @link     http://triticeaetoolbox.org/wheat/curator_data/fieldbook_export.php
 * 
 */
require_once 'config.php';
require $config['root_dir'] . 'includes/bootstrap_curator.inc';
connect();
$mysqli = connecti();

new Tablet($_GET['function']);

/** Using a PHP class to implement the "Tablet" feature
 * 
 * @category PHP
 * @package  T3
 * @author   Clay Birkett <claybirkett@gmail.com>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/curator_data/fieldbook_export.php
 **/
class Tablet
{
    /**
     * Using the class's constructor to decide which action to perform
     *
     * @param string $function action to perform
     */
    public function __construct($function = null)
    {
        switch($function)
        {
            case 'save':
                    $this->save();
                    break;
            default:
                    $this->display();
                    break;
        }
    }
	
    function display()
    {
    	global $config;
    	global $mysqli;
		include($config['root_dir'] . 'theme/admin_header.php');
                ?>
		<h2>Tablet Tools</h2>
                These tools provide a interface to the Android Field Book program created by the
                <a href="http://www.wheatgenetics.org/bioinformatics/22-android-field-book.html">Poland Lab</a>.
                Before using these tools it is necessary to <a href="curator_data/input_experiments_upload_excel.php">import a field layout</a> into the database.
                In the Field Book program the range is the first level division and refers to the row of the field. 
                The second level division is the plot. All other coluns are considered Extra Information. The program moves through the field row by row for measurements.
                <h4>Create Fieldbook File (Import into tablet)</h4>
		1. Select a fieldbook containing your experiment.<br>
                2. Download and save the fieldbook file.<br>
                3. Connect your tablet to this computer and move this file into field_import folder of the SD card of the tablet.<br>
                4. Import this file into the Field Book App.<br>
                5. Define the set of traits to be measured in the Field Book App using the exact spelling as on the T3 database.<br>
                6. Record measurements using the tablet.<br><br>

		<div style="float: left">
		Fieldbook:
                <?php
		$sql = "select fieldbook_info_uid, experiment_uid, fieldbook_file_name from fieldbook_info";
		$sql = "select distinct(fieldbook.experiment_uid), trial_code from fieldbook, experiments where fieldbook.experiment_uid = experiments.experiment_uid";
		$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
		echo "<select id=\"experiment\" name=\"experiment\" onchange=\"javascript: update_expr()\">";
		echo "<option>select a fieldbook</option>\n";
		while ($row = mysqli_fetch_row($res)) {
			$uid = $row[0];
			$name = $row[1];
			echo "<option value=$uid>$name</option>\n";
		}
		?>
		</select>
		</div>
		<div id="export" style="float: left; text-align: center; width: 200px"></div>
		<div style="clear: both"><br><br>
		<h4>Create Trait Plot File (Exported from tablet)</h4>
                1. In the Field Book Program be careful to define you trait names exactly as named in the T3 database.<br>
		2. After completing data collection, export the data in table format.<br>
                3. Export as "Table Format" and select the first 4 columns for export.<br>
                4. Connect your tablet to this computer and move the file from the field_export folder.<br>
                5. Browse to this file and select Upload.<br>
		<p><form action="curator_data/input_tablet_plot_check.php" method="post" enctype="multipart/form-data">Plot file:
		<input type="hidden" id="plot" name="plot" value="-1" />
		<input id="file[]" type="file" name="file[]" size="50%" />
		<input type="submit" value="Upload" /><br>
		<a href="<?php echo $config['base_url']; ?>curator_data/examples/T3/PlotTabletTemplate.csv">Example Tablet file</a><br>
		</form></p>
		
		</div><br>
                Note: Other tablet devices can be used by opening the fieldbook download in a spreedsheet application and adding on columns for each trait as in the Example Plot file.
                </div>
		<?php
		include($config['root_dir'].'theme/footer.php');
		?>
		<script type="text/javascript" src="curator_data/tablet.js"></script>
		<?php 
    }
    
    function save()
    {
    	global $config;
    	global $mysqli;
    	$uid = $_GET['uid'];

        $sql = "select line_record_uid, line_record_name from line_records";
        $res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
        while ($row = mysqli_fetch_row($res)) {
                $line_uid= $row[0];
                $line_record_name = $row[1];
                $name_list[$line_uid] = $line_record_name;
        }

        $error = 0;
    	$unique_str = chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80)).chr(rand(65,80));
    	$filename = "import_" . $unique_str . ".csv";
    	if (! file_exists('/tmp/tht')) mkdir('/tmp/tht');
    	$output = fopen("/tmp/tht/$filename","w");
    	fwrite($output, "plot_id,range,plot,tray_row,name,replication,block\n");
    	$sql = "select plot_uid, plot, block, row_id, column_id, replication, check_id, line_uid from fieldbook where experiment_uid = $uid order by row_id, plot";
    	$res = mysqli_query($mysqli,$sql) or die(mysqli_error($mysqli) . "<br>$sql");
    	while ($row = mysqli_fetch_row($res)) {
    		$plot_id = $row[0];
    		$plot = $row[1];
    		$block = $row[2];
    		$row_id = $row[3];
    		$column_id = $row[4];
                $replication = $row[5];
                $check_id = $row[6];
                $line_uid = $row[7];
                $line_record_name = $name_list[$line_uid];
                if (!preg_match("/\d+/",$row_id)) {
                    $error = 1;
                }
                if (!preg_match("/\d+/",$column_id)) {
                    $error = 1;
                }
    		fwrite($output, "$plot_id,$row_id,$plot,$column_id,$line_record_name,$replication,$block\n");
    	}
    	fclose($output);
        if ($error == 0) {
    	echo "<form method=\"link\" action=\"/tmp/tht/$filename\">";
    	echo "<input type=submit value=\"Download\">";
    	echo "</form>";
        } else {
            echo "<font color=red>Error: Fieldbook did not contain required row and column location information</font>\n";
        }
    }
}
