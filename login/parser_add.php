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
			<div class="boxContent">


<?php
if (isset($_POST['resetdata']) && $_POST['resetdata']==1) {
	unset($_SESSION['user_data_file']);
}
if (isset($_SESSION['user_data_file'])) {
	print "<h3>The data file is stored at ".$_SESSION['user_data_file']."</h3>";

	print <<<_RESET
	<form action="login/parser_add.php" method="post">
	<p><input type="submit" value="Reset data file"></p>
	<input type="hidden" name="resetdata" value=1>
	</form>
_RESET;

	print <<<_PARSER
<h3>Now, upload the parser definition file </h3>
<form action="login/uploader.php?type=user_def" method="post" enctype="multipart/form-data">

<p><input type="file" name="file" size="80%" ></p>
<p><input type="submit" value="Upload File" ></p>
</form>
_PARSER;
}
else {
	print <<<_DEFAULT
<h3>First upload the data file in Excel format</h3>
<form action="login/uploader.php?type=user_data" method="post" enctype="multipart/form-data">
<p><input type="file" name="file" size="80%" ></p>
<p><input type="submit" value="Upload File" ></p>
</form>
_DEFAULT;
}

?>
			</div>
		</div>
	</div>
</div>
</div>
<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

<?php include("../theme/footer.php");?>