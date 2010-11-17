<?php
require 'config.php';
header("Location: ".$config['base_url']."login.php");
// Code execution stops here.
// nothing below here matters anymore.
// - Gavin
die();

include("includes/bootstrap.inc");
connect();

$msg = "Wait a second...How'd I get here? Oh yea, I'm not allowed to see that page.";
$encode = urlencode($msg);
header("Location: http://www.ethanwilder.com/displaymsg.php?msg=$encode");
die();

/* Login form filled out */
if(isset($_POST['username'])) {

   if(validateForm($_POST)) {

   	if(checkForUser($_POST['username'])) { //username is available

	   	if(checkGoodPassword($_POST['password'])) { //password is acceptable

	    	   addUser($_POST['username'], $_POST['password'], $_POST['bcp'], $_POST['institute'], $_POST['name'], $_POST['email']);
		   $error = "You have successfully registered $_POST[username]. You can now <a href=\"login.php\">login</a> to THT";
		}
		else {
		   $error = "Your Password must be at least 6 characters and contain at least 1 non-alphanumeric character.";
		}
   	}
   	else {
	   $error = "Sorry but that username is already taken. Please try a different name.";
   	}
   }
   else {
      $error = "You must fill in all of the fields and provide a valid e-mail address. <br /> Example: name@domain.com";
   }
}

include("theme/header.php");

?>
<br />

   <div class="box">

	<h2>Registration</h2>

	<p class="error"><?php echo $error; ?></p>

	<form action="register.php" method="post">

  	<p><strong>Login Information:</strong></p>
	<p>Username: <input type="text" name="username" /></p>

	<p>Password: <input type="password" name="password" /></p>

  	<p><strong>General Information:</strong></p>
	<p>Your Name: <input type="text" name="name" /></p>

	<p>Your Email: <input type="text" name="email" /></p>

	<p>Institute: <select name="institute">
		<option value="Select">Select</option>
		<?php showTableOptions("institutions"); ?>
	</select></p>

	<p>Are you part of the Barley CAP Project?: <br />
	<input type="radio" name="bcp" value="100" /> Yes <br />
	<input type="radio" name="bcp" value="101" checked="checked" /> No <br /></p>


	<p><input type="submit" value="Register!" /> <input type="reset" value="Start Over" /></p>

	</form>

   </div>

   <center>
	<p>For general public access to THT, please visit <a href="http://hordeumtoolbox.org">hordeumtoolbox.org</a></p>
	<p><strong>Note:</strong> This site uses cookies and requires cookies to be enabled.</p>
   </center>



<?php include("theme/footer.php"); ?>