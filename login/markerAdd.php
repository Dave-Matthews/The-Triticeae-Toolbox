<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include("../includes/bootstrap.inc");

connect();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);


if(isset($_POST['mapset_name'])) { //mapset form has been submitted

	if(validateForm($_POST)) {	//valid?
		$date = date("Y-m-d H:i:s");
		$_POST['published_on'] = $date;

		$res = add_array_attributes($_POST, array(0,0,0,0), "mapset", "mapset_name", $_POST['mapset_name'], "mapset_uid");

		if($res[0] < 1) {
			$err = $_POST['mapset_name'] . " already exists in the database";
		}
		else
			$success = "Successfully added " . $_POST['mapset_name'];
	}
	else {
		$err = "Please Fill in the Entire Form";
	}
}

////////////////////////////////////////////////////////////////////////////////
ob_start();
include("../theme/admin_header.php");
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

?>

<div id="primaryContentContainer">
	<div id="primaryContent">
  		<div class="box">
		<h2>Add New Markers</h2>
			<div class="boxContent">

<?php if($err == "")
	echo $success;
      else
	error(1,$err);
?>

<p><strong>Mapset:</strong> <select name="mapset" onchange="mapsetChange(this.value)">
		<option value="Select" selected="selected">Select</option>
		<?php showTableOptions("mapset"); ?>
		<option value="NewMapSet">New Mapset</option>
	</select></p>


<div id="NewMapsetForm" style="display:none;">

<form action="login/markerAdd.php" method="post" enctype="multipart/form-data">
<table class="tableclass1">
<tr>
	<td><strong>New Mapset Name:</strong></td>
	<td><input type="text" name="mapset_name"/></td>
</tr>
<tr>
	<td><strong>Species:</strong></td>
	<td><input type="text" name="species"/></td>
</tr>
<tr>
	<td><strong>Map Type:</strong></td>
	<td><input type="text" name="map_type"/></td>
</tr>
<tr>
	<td><strong>Map Unit:</strong></td>
	<td><input type="text" name="map_unit"/></td>
</tr>
<tr>
	<td></td>
	<td><button type="submit">Submit</button> <button type="reset">Clear</button></td>
</tr>
</table>
</form>

</div> <!-- end newMapsetForm -->

<div id="MapsetInfo">
</div>

<form action="login/uploader.php?type=marker" method="post" enctype="multipart/form-data">

<input type="hidden" id="mapsetID" name="MapsetID" value="-1" />
<p><strong>File:</strong> <input id="file" type="file" name="file" size="80%" disabled/></p>
<p><input type="submit" value="Upload Marker File" /></p>

</form>

			</div><!-- end boxContent -->

		</div> <!-- end Box -->


<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

	</div><!-- end primaryContent -->
</div>
</div>

<?php include("../theme/footer.php");?>
