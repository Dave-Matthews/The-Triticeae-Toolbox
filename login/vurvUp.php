<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();

/* ****************************** */
include("../theme/cheader.php");

$query = mysql_query("
	UPDATE line_records
	SET vurv_id = (
		SELECT vurv_num
		FROM barley_pedigree_catalog
		WHERE barley_pedigree_catalog.barley_pedigree_catalog_name = line_records.line_record_name
		)
	") or die(mysql_error());
?>

Successfully set the Vurv_id in line_record

<?php include($config['root_dir'] . '/theme/footer.php');?>
