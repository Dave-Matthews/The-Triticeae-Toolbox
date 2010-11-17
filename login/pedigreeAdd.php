<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();

/*
 * Has the form been submitted?
 */

if(isset($_POST['parent'])) {	//the form has been submitted

	if($_POST['parent'] != "" && $_POST['child'] != "") {	//form is valid

		//get parent and line IDs, capture errors using output buffering
		ob_start();
		$parent = getPedigreeId($_POST['parent']);
		$line = getPedigreeId($_POST['child']);

		$error = ob_get_contents();
		ob_end_clean();

		if($error == "") {	//if there has been no error

			if(circularParentTest($_POST['child'], $_POST['parent'])) {	//parent won't be its own grandparent

				if(trim($_POST['contribution']) == "") 	//unsigned integer fix
					$_POST['contribution'] = 0;

				$vals = array("line_record_uid"=>$line,
					"parent_id"=>$parent,
					"relation"=>$_POST['relation'],
					"contribution"=>$_POST['contribution'],
					"selfing"=>$_POST['selfing'],
					"comments"=>$_POST['comments']);

				$res = add_array_attributes($vals, array(1,1,0,0,0,0), "pedigree_relations", "1", "0", "line_record_uid");

				if(mysql_error() != "")
					$error = error_string(mysql_error());

				if(mysql_errno() == 1062) {
					$error = error_string("That relationship already exists");
				}
				else {
					$success = TRUE;
				}
			}
			else
				$error = error_string("That pedigree doesn't make any sense.<br />" .$_POST['parent'] . " is a child of " . $_POST['child'] . ". It can't be its parent too");
		}

	}
	else {
		$error = error_string("Please enter which parent and child are related");
	}

}

?>
<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">
<?php

/*
 * Pedigree Add
 */
switch($_GET['add']) {

case "single":
?>

<h2>Add a Single Pedigree</h2>
<div class="boxContent">

<?php if($error != "" && isset($error)) echo $error; ?>
<?php if($success) echo "<p>Successfully added the pedigree between " . $_POST['parent'] . " and " . $_POST['child'] . "</p>"; ?>

<form action="login/pedigreeAdd.php?add=single" method="post" enctype="multipart/form-data">

<p><strong>Parent</strong>:<br />
<input type="text" name="parent" /></p>

<p><strong>Child</strong>:<br />
<input type="text" name="child" /></p>

<p><strong>Relation</strong>:<br />
<input type="text" name="relation" /></p>

<p><strong>Contribution</strong>:<br />
<input type="text" name="contribution" size="4"/></p>

<p><strong>Selfing</strong>:<br />
<input type="text" name="selfing" /></p>

<p><strong>Comments</strong>:<br />
<textarea name="comments" rows="5" cols="30"></textarea></p>

<p><input type="submit" value="Add Pedigree" /></p>


</form>
</div>
<?php break;

default:
?>

<h2>Add New Pedigree Data</h2>
<div class="boxContent">

<form action="login/uploader.php?type=pedigree" method="post" enctype="multipart/form-data">

<p><input type="file" name="file" size="80%" /></p>
<p><input type="submit" value="Upload Pedigree File" /></p>


</form>
</div>

<?php break;
}
?>


<p><a href="login/pedigreeAdd.php?add=single">Add a single pedigree</a><br />
<a href="login/pedigreeAdd.php">Add multiple pedigrees</a><br />
<a href="login/str2table.php">Add pedigrees via pedigree string</a></p>

<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

		</div>
	</div>
</div>
</div>
<?php include($config['root_dir'] . 'theme/footer.php'); ?>
