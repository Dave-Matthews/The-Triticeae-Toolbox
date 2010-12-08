<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap.inc');
include($config['root_dir'] . 'curator_data/boot_test.php');

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();
include($config['root_dir'] . 'theme/admin_header.php');
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
connect_dev();  /* Connect with write-access. */
////////////////////////////////////////////////////////////////////////////////

/*
 * The following if statements handle the submission actions when the bottom 3 forms are filled out.
 */

if(isset($_POST['category'])) {		//add New Trait has been submitted

	if($_POST['short_name'] == "")
		$_POST['short_name'] = "ignore";

	if(validateForm($_POST)) {	//form has valid input

		if($_POST['short_name'] == "ignore") $_POST['short_name'] = "";

		/* Format Trait Name */
		$catname = getCategoryName($_POST['category']);
		$cline = explode(' ', $catname);
		$pname = $cline[0]."_".implode("_",explode(' ',$_POST['name']));

		/* Store in Database */
		$vals = array('phenotype_category_uid'=> $_POST['category'], 'unit_uid'=> $_POST['units'], 'phenotypes_name'=> $pname, 'short_name'=> $_POST['short_name'], 'description'=> $_POST['description']);
		$ret = add_array_attributes($vals, array("1", "1", "0", "0", "0"), "phenotypes", "phenotypes_name", $pname, "phenotype_uid");

		if($ret[0] > 0)
			echo "<p>". $pname . " has been added</p>";

		else
			$error = $pname . " already exists in the database.";

	}
	else {
		$error = "Please fill in all of the fields";
	}
}

if(isset($_POST['single_category'])) {		//add New Category has been submitted

	if(validateForm($_POST)) {	//form has valid input

		$ret = add_attribute("phenotype_category_name", $_POST['single_category'], "phenotype_category", "phenotype_category_uid");

		if($ret[0] > 0)
			echo "<p>". $_POST['single_category'] . " has been added</p>";

		else
			$error = $_POST['single_category'] . " already exists in the database.";
	}
	else {
		$error = "Please fill in all of the fields";
	}
}

if(isset($_POST['unit_name'])) {		//add new unit has been submitted

	if(validateForm($_POST)) {	//form has valid input

		$vals = array('unit_name'=>$_POST['unit_name'], 'unit_abbreviation'=>$_POST['unit_abbreviation'], 'unit_description'=>$_POST['unit_description']);
		$ret = add_array_attributes($vals, array("0", "0", "0"), "units", "unit_name", $_POST['unit_name'], "unit_uid");

		if($ret[0] > 0)
			echo "<p>". $_POST['unit_name'] . " has been added</p>";

		else
			$error = $_POST['unit_name'] . " already exists in the database.";

	}
	else {
		$error = "Please fill in all of the fields";
	}
}

?>
<div id="primaryContentContainer">
	<div id="primaryContent">
		<div class="box">

<?php

if($error != "") 	//is there an error?
	error(1, $error);


switch($_GET['add']) {

   case "single":
?>

<h2>Add a Single New Trait</h2>
<div class="boxContent">
<form action="<?php echo $config['base_url']; ?>login/traitAdd.php?add=single" method="post" enctype="multipart/form-data">

<p>Category:<br />
<select name="category">
	<?php echo showTableOptions("phenotype_category") ?>
</select></p>

<p>Units:<br />
<select name="units">
	<?php echo showTableOptions("units") ?>
</select></p>

<p>Name:<br />
<input type="text" name="name" /></p>

<p>Short Name:<br />
<input type="text" name="short_name" /></p>

<p>Description:<br />
<input type="text" name="description" /></p>

<p><input type="submit" value="Add" /></p>

</form>
</div>

   <?php break;
   case "category":
   ?>

<h2>Add a Trait Category</h2>
<div class="boxContent">
<form action="<?php echo $config['base_url']; ?>login/traitAdd.php?add=category" method="post" enctype="multipart/form-data">

<p>Category Name: <br />
<input type="text" name="single_category" /></p>
<p><input type="submit" value="Add" /></p>

</form>
</div>

   <?php break;
   case "unit":
   ?>

<h2>Add a New Unit</h2>
<div class="boxContent">
<form action="<?php echo $config['base_url']; ?>login/traitAdd.php?add=unit" method="post" enctype="multipart/form-data">

<p>Unit Name: <br />
<input type="text" name="unit_name"  /></p>
<p>Unit Abbreviation: <br />
<input type="text" name="unit_abbreviation"  /></p>
<p>Unit Description: <br />
<input type="text" name="unit_description"  /></p>

<p><input type="submit" value="Add" /></p>

</form>
</div>

   <?php break;
    default:
   ?>

<h2>Add Multiple New Traits</h2>
<div class="boxContent">

<p>Upload an <em>Excel</em> file with the format suggested by the <a href="<?php echo $config['base_url']; ?>downloadlite/index.php?THT_trait_template.xls"><em>THT Trait Template</em></a></p>

<form action="<?php echo $config['base_url']; ?>login/uploader.php?type=traits" method="post" enctype="multipart/form-data">

<p><input type="file" name="file" size="80%" /></p>
<p><input type="submit" value="Upload Trait File" /></p>

</form>
</div>
   <?php
   break;
}
?>

<p><a href="<?php echo $config['base_url']; ?>login/traitAdd.php">Add Multiple Traits</a><br />
<a href="<?php echo $config['base_url']; ?>login/traitAdd.php?add=single">Add a Single Trait</a><br />
<a href="<?php echo $config['base_url']; ?>login/traitAdd.php?add=category">Add a New Category</a><br />
<a href="<?php echo $config['base_url']; ?>login/traitAdd.php?add=unit">Add a New Unit</a><br />
</p>
		</div>

<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>
	</div>
</div>
</div>

<?php include($config['root_dir'] . '/theme/footer.php');?>