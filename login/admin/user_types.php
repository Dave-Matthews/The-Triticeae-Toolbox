<?php
include("../../includes/bootstrap.inc");
connect();

/* form has been submitted, now insert */
if(isset($_POST['description'])) {

	if(validateForm($_POST)) {
		$insert = "INSERT INTO user_types(user_type_name, description, created_on)
				VALUES('$_POST[name]','$_POST[description]', NOW());";

		mysql_query($insert) or die(mysql_error());
	
		echo "<strong>Success</strong>";
	}
	else {
		echo "<strong>Fill in all of the fields please</strong>";
	}

}

?>

<h1>Add a User Type</h1>
<form action="user_types.php" method="post">

<p>Name: <br />
<input type="text" name="name" /></p>

<p>Description: <br />
<input type="text" name="description" size="50" /></p>

<p><input type="submit" value="Submit!" /> <input type="reset" value="Reset!" /></p>

</form>