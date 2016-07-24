<?php

// 12/14/2010 JLee  Change to use curator bootstrap

require 'config.php';
/*
 * Logged in page initialization
 */
include $config['root_dir'] . 'includes/bootstrap_curator.inc';

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

/* ****************************** */
ob_start();
include $config['root_dir'] . 'theme/admin_header.php';
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

//connect_dev();  /* Connect with write-access. */

/*
 * Has it been submitted?
 */
if( ($id = array_search("Submit", $_POST)) != NULL) {
	foreach($_POST as $k=>$v)
		$_POST[$k] = addslashes($v);

	$ids = explode("-", $id);
	if(updateTable($_POST, "pedigree_relations", array("line_record_uid"=>$ids[1], "parent_id"=>$ids[2])) )
		echo "<p>Success</p>";
}


/*
 * Delete?
 */
if( ($id = array_search("Delete", $_POST)) != NULL) {
	$ids = explode("-", $id);
	if(deletePedigree($ids[1], $ids[2]))
		echo "<p>Success. Do you wish to <a href=\"".$config['base_url']."login/pedigreeAdd.php?add=single\">add a new pedigree</a>?</p>";
	else
		error(1, mysqli_error($mysqli));
}
?>

<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">

		<h2>Edit Pedigrees</h2>
			<div class="boxContent">
				<p><strong>Note:</strong> if you wish to change parents, delete the pedigree and then add a new pedigree.</p>

				<form action="<?php echo $config['base_url']; ?>login/edit_pedigree.php" method="get">
				<p><strong>Line Name</strong><br />
				<input type="text" name="line" value="<?php echo $_REQUEST['line']; ?>" /></p>
			</div>
		</div>

<p><input type="submit" value="Edit Pedigree" /></p>
</form>

<?php
if(isset($_REQUEST['line'])) {

	echo "<div class=\"box\">";

	//keeping the query string appended
	$self = $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'];

	editPedigree($_REQUEST['line'], $self);

	echo "<br /></div>";

}
?>

	</div>
</div>
</div>

<?php include $config['root_dir'] . 'theme/footer.php';?>
