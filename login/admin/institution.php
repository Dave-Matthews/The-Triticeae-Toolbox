<?php
include("../../includes/bootstrap.inc");

connect();

/* form has been submitted, now insert */
if(isset($_POST['name'])) {

	print_r($_POST);

	if(validateForm($_POST)) {
		$insert = "INSERT INTO institutions (institution_code, institutions_name, institute_acronym, institute_address, phone, email, created_on)
				VALUES('$_POST[code]', '$_POST[name]', '$_POST[acronym]', '$_POST[address]', '$_POST[phone]', '$_POST[email]', NOW());";

		mysql_query($insert) or die(mysql_error());
	
		echo "<strong>Success</strong>";
	}
	else {
		echo "<strong>Fill in all of the fields please</strong>";
	}

}

?>

<h1>Add an Institution</h1>
<form action="institution.php" method="post">

<p>Institution Code: <br />
<input type="text" name="code" /></p>

<p>Institution Name: <br />
<input type="text" name="name" /></p>

<p>Institution Acronym: <br />
<input type="text" name="acronym" /></p>

<p>Institution address: <br />
<input type="text" name="address" size="50" /></p>

<p>Institution Phone Number: <br />
<input type="text" name="phone" /></p>

<p>Institution E-Mail: <br />
<input type="text" name="email" /></p>

<p><input type="submit" value="Submit!" /> <input type="reset" value="Reset!" /></p>

</form>