<?php
include("../../includes/bootstrap.inc");
connect();

/* form has been submitted, now insert */
if(isset($_POST['name'])) {

	if(validateForm($_POST)) {
		$insert = "INSERT INTO breeding_programs(institutions_uid, breeding_programs_name, description, created_on)
				VALUES('$_POST[institution]', '$_POST[name]', '$_POST[description]', now());";

		mysql_query($insert) or die(mysql_error());
	
		echo "<strong>Success</strong>";
	}
	else {
		echo "<strong>Fill in all of the fields please</strong>";
	}

}

?>

<h1>Add a Breeding Program</h1>
<form action="breeding_programs.php" method="post">

<p>Breeding Program Institution: <br />
<select name="institution">
	<option value="Select" selected="selected">Select</option>
	<?php showInstituteOptions(); ?>
</select></p>

<p>Breeding Program Name: <br />
<input type="text" name="name" /></p>

<p>Breeding Program Description: <br />
<input type="text" name="description" /></p>


<p><input type="submit" value="Submit!" /> <input type="reset" value="Reset!" /></p>

</form>
