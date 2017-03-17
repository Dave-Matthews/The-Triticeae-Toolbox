<?php

require "includes/bootstrap.inc";
ob_start();
require "theme/admin_header.php";
ob_end_flush();
session_start();
/*if(isLoggedIn($_SESSION['username'], $_SESSION['password'])) {
	updateLastAccess($_SESSION['username'], $_SESSION['logintime']);
	
}*/

$_SESSION['username'] = null;
$_SESSION['password'] = null;
session_destroy();
?>
<!--<div id="primaryContentContainer">
<div id="primaryContent">
<div class="box">-->
<h1>Thank You</h1>
<div class="section">
<p>You have been logged out successfully. Please wait while you are being redirected</p>
<br/><br/><br/><br/><br/><br/><br/><br/><br/><br/><br/>
</div>
</div>

<meta http-equiv="refresh" content="2;url=index.php" />

<?php require "theme/footer.php";?>
