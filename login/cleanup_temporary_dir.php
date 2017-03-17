<?php
require 'config.php';
/*
 * Logged in page initialization
 */
include("../includes/bootstrap.inc");

$mysqli = connecti();
loginTest();

/* ******************************* */
$row = loadUser($_SESSION['username']);

////////////////////////////////////////////////////////////////////////////////
ob_start();
include("../theme/admin_header.php");
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR));
ob_end_flush();
////////////////////////////////////////////////////////////////////////////////

?>

<div id="primaryContentContainer">
<div id="primaryContent">
<div class="box">

<?php
	print "<pre> Cleaning up \n";
	$handle = opendir("uploads");
	while (FALSE !== ($item = readdir($handle))) {
			if(preg_match('/^tmpdir/', strtolower($item)) && is_dir("uploads/".$item)) {
				$tmpdirname="uploads/".$item;
				print $tmpdirname."\n";
				clean_up_temporary($tmpdirname);
			}
	}
	print "</pre>";
	print "<a href='login'>Done</a>";

?>
<p><?php echo $row['name']; ?> you last accessed the system on <?php echo $row['lastaccess']; ?></p>

</div>
</div>
</div>
</div>
<?php include("../theme/footer.php");?>
