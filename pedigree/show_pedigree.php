<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'].'includes/bootstrap.inc');
connect();


if(isset($_GET['line']) && ($_GET['line'] != "")) {
	$safe = mysql_real_escape_string($_GET['line']);

	//start output buffering, capture any errors here.
	ob_start();

	//if $number is FALSE then $safe was not a name, but a number
	if( ($number = getPedigreeId($safe)) === FALSE)
		$number = $safe;

	//end output buffering and clean out any errors.
	ob_end_clean();

	header("Location: ../view.php?table=line_records&uid=$number");


	/*	echo "<h2>View Line Record " . getAccessionName($number) . "</h2>";
	showRecord($number); */
}

else {

include($config['root_dir'].'theme/normal_header.php');

/*******************************/

?>
<h1>Browse Line Records</h1>
<div class="section">
<p>
<?php
	if(isset($_GET['start']))
		showLines($_SERVER['PHP_SELF'], $_GET['start']);
	else
		showLines($_SERVER['PHP_SELF']);
}
?>
</p>
</div>
</div>
<?php include($config['root_dir'].'theme/footer.php');?>
