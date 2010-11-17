<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
connect();

include($config['root_dir'] . 'theme/admin_header.php');
/*******************************/
?>

<div id="primaryContentContainer">
	<div id="primaryContent">

	<h2>Show markers</h2>

<?php

if(isset($_GET['line']) && ($_GET['line'] != "")) {

	//start output buffering, capture any errors here.
	ob_start();

	//if $number is FALSE then the parameter was not a name, but a number
	if( ($number = getPedigreeId($_GET['line'])) === FALSE)
		$number = $_GET['line'];

	//end output buffering and clean out any errors.
	ob_end_clean();

	echo "<br /><h3>". getAccessionName($number) ."</h3>";

	if(isset($_GET['sortby']) && isset($_GET['sorttype'])) {
		$orderby = $_GET['sortby'] . " " . $_GET['sorttype'];
		showMarkerForLine($number, $orderby);
	}
	else
		showMarkerForLine($number);

	echo "<br />";
}
?>

<div class="boxContent">
<p>Example: harrington</p>

<form action="<?php echo $config['base_url']; ?>genotyping/show.php" method="get">
<p><strong>Line: </strong><br />
<input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" /></p>

<p><input type="submit" value="Get Data" /></p>
</form>


			</div>
	</div>
</div>
</div>


<?php include($config['root_dir'] . 'theme/footer.php'); ?>
