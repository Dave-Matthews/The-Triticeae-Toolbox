<?php
require 'config.php';
/*
 * Logged in page initialization
 */

include("../includes/bootstrap.inc");
connect();
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

<h2>Store a string based pedigree notation into the database</h2>
			<div class="boxContent">

<?php

	if( ($_POST['str'] !=  "") && ($_POST['line'] !=  "")) {

		$old_lines = getNumEntries("line_record");
		$old_pedi = getNumEntries("pedigree_relations");

		$return = pediStr2SQL($_POST['line'], $_POST['str']);

		$new_lines = getNumEntries("line_record");
		$new_pedi = getNumEntries("pedigree_relations");

		if($return !== FALSE) {
			echo "<p>Successfully added " .($new_pedi - $old_pedi). " new pedigrees and "
				.($new_lines - $old_lines)." new line records</p>";
			echo "<p>There were $return duplicates</p>";
		}
	}
?>

<form action="login/str2table.php" method="post">
<p><strong>Line</strong><br />
<input type="text" name="line" value="<?php echo $_POST['line']; ?>" /></p>

<p><strong>String based pedigree</strong><br />
<input type="text" name="str" value="<?php echo $_POST['str']; ?>" size="50" /></p>

<p><input type="submit" value="Store" /></p>
</form>

<p><a href="login/pedigreeAdd.php?add=single">Add a single pedigree</a><br />
<a href="login/pedigreeAdd.php">Add multiple pedigrees</a><br />
<a href="login/str2table.php">Add pedigrees via pedigree string</a></p>

			</div>
		</div>

<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

	</div>
</div>
</div>

<?php include("../theme/footer.php");?>
