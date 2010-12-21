<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
//include($config['root_dir'] . 'curator_data/boot_test.php');


/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');

connect();
//loginTest();


/* ******************************* */
$row = loadUser($_SESSION['username']);

/* ****************************** */

////////////////////////////////////////////////////////////////////////////////
ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

connect_dev();  /* Connect with write-access. */

/*
 * Session variable stores duplicate records, do we wish to edit duplicates?
 */
if(isset($_SESSION['DupTraitRecords'])) {
	sort($_SESSION['DupTraitRecords']);
	$drds = $_SESSION['DupTraitRecords'];
}

if(count($drds) == 0) {
	session_unregister("DupTraitRecords");
	unset($drds);
}

/*
 * Has an update been submitted?
 */
if( ($id = array_search("Update", $_POST)) != NULL) {
	foreach($_POST as $k=>$v)
		$_POST[$k] = addslashes($v);

	updateTable($_POST, "phenotypes", array("phenotype_uid"=>$id));
}

if(isset($_REQUEST['search']) && $_REQUEST['search'] != "") {

	$tablesToSearch = array("phenotypes");

	$found = array();
	$words = explode(" ", $_REQUEST['search']);

	foreach($words as $q) {
		$found = array_merge($found, desperateTermSearch($tablesToSearch, $q));
	}

	$drds = array();

	if(count($found) > 0) {		//if we found results..

		for($i=0; $i<count($found); $i++) {

			$parts = explode("@@", $found[$i]);
			array_push($drds, $parts[2]);
		}

	}

}

$start = 0;
if(isset($_GET['start'])) {
	$start = $_GET['start'];
}

?>


<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">

		<h2>Search within Traits</h2>

			<div class="boxContent">
			<form action="<?php echo $config['base_url']; ?>login/edit_traits.php" method="post">
			<p><input type="text" name="search" size="50" /> <input type="submit" value="Go >>" /></p>
			</form>
			</div>
		</div>
<?php

	// attaching the query string to the callback URL.
	$self = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

	if(isset($drds) && count($drds) > 0) {
		$self .= isset($_GET['search']) ? "" : "search=". $_REQUEST['search'];
		editSelectPhenotypes($drds, $self, $start);
	}

	else if(!isset($drds))
		editAllPhenotypes($self, $start);

	else
		echo "<p>Search returned no results</p>";
?>
	</div>
</div>
</div>

<?php include($config['root_dir'] . 'theme/footer.php');?>
