<?php
include("../../includes/bootstrap.inc");

connect();

/* form has been submitted, now insert */
if(isset($_POST['name'])) {

	if(validateForm($_POST)) {
		$insert = "INSERT INTO collecting_sites (breeding_programs_uid, collecting_sites_name, growing_conditions, plot_size, longitude, latitude, planting_date, harvest_date, created_on)
				VALUES('$_POST[breeding_program]', '$_POST[name]', '$_POST[growing]', '$_POST[plot_size]', '$_POST[longitude]', '$_POST[latitude]', '$_POST[planting], '$_POST[harvest]', now());";

		mysql_query($insert) or die(mysql_error());
	
		echo "<strong>Success</strong>";
	}
	else {
		echo "<strong>Fill in all of the fields please</strong>";
	}

}

?>

<h1>Add a Collecting Site</h1>
<form action="collecting_sites.php" method="post">

<p>Breeding Program: <br />
<select name="breeding_program">
	<option value="Select" selected="selected">Select</option>
	<?php showBreedingProgramOptions(); ?>
</select></p>

<p>Collecting Site Name: <br />
<input type="text" name="name" /></p>

<p>Growing Conditions (environment): <br />
<input type="text" name="growing" /></p>

<p>Plot Size: <br />
<input type="text" name="plot_size" size="50" /></p>

<p>Longitude: <br />
<input type="text" name="longitude" /></p>

<p>Latitude: <br />
<input type="text" name="latitude" /></p>

<p>Planting Date: <br />
<input type="text" name="planting" /></p>

<p>Harvest Date: <br />
<input type="text" name="harvest" /></p>

<p><input type="submit" value="Submit!" /> <input type="reset" value="Reset!" /></p>

</form>
