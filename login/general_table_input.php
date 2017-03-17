<?php
// 06/03/2011 JLee  Change to use curator bootstrap

/* Logged in page initialization */
include("../includes/bootstrap_curator.inc");

$mysqli = connecti();
loginTest();

$row = loadUser($_SESSION['username']);

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
  		
<?php

/* generate a list of tables */

print "<div id='target_table_select'>";
print "<h3>Select a table from the list</h3>";
print "<form id='form_table_sel' method='post'>\n";
print "<p>";
print "<select id='table_sel' size=5>\n";
$result=mysqli_query($mysqli, "show tables") or die(mysqli_error($mysqli));
while ($row=mysqli_fetch_assoc($result)) {
	$selval=implode("",array_slice($row,0,1));
	print "<option value=\"$selval\">$selval</option>\n";
}
print "</select></p><p>";
print "<input type='submit' value='Select' onClick='DispTblInputFrm(); return false;'>";
print "<input type='button' value='Cancel' onClick=\"window.location='login/index.php'\">";
print "</p>";
print "</form>";
print "</div>";
print "<div id='table_input_form'>";
print "</div>";
?>

			</div> <!-- end boxContent -->
		</div>
	</div>
</div>



<?php include("../theme/footer.php");?>
