<?php
$root = "http://".$_SERVER['HTTP_HOST'];
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']),"",$_SERVER['SCRIPT_NAME']);
$config['base_url'] = "$root";
$config['root_dir'] = dirname(__FILE__).'/';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');

?>
	<h1>
		Login/Register
	</h1>
	<div class="section">
<?php
/**
 *
 */
function HTMLRegistrationForm($msg="", $name="", $email="", $cemail="", $answer="no")
{
	$c_no = "";
	$c_yes = "";
	$c_forgot="";
	if ($answer == "no")
	{
		$c_no = 'checked="checked"';
	}
	if ($answer == "yes")
	{
		$c_yes = 'checked="checked"';
	}
	$retval = "";
	if (!empty($msg))
	{
		$retval .= <<< HTML
		<div id="form_error">
			$msg
		</div>
HTML;
	}
	$retval .= <<< HTML
		<br />
		<h2>
			Registration
		</h2>
		<form action="{$_SERVER['PHP_SELF']}" method="post">
			<h3>
				What is your name?
			</h3>
			<label for="name">My name is:</lable>&nbsp;<input type="text" name="name" id="name" value="$name" />
			<h3>
				What is your e-mail address?
			</h3>
			<table border="0" cellspacing="0" cellpadding="0" style="border: none; background: none">
				<tr>
					<td style="border: none; text-align: right;">
						<label for="email">
							My e-mail address is:
						<label>
					</td>
					<td style="border:none;">
						<input type="text" name="email" id="email" value="$email" />
					</td>
				</tr>
				<tr>
					<td style="border: none; text-align: right;">
						<label for="cemail">
							Type it again:
						</label>
					</td>
					<td style="border: none;">
						<input type="text" name="cemail" id="cemail" value="$cemail" />
					</td>
				</tr>
			</table>
			<h3>
				What do you want your password to be?
			</h3>
			<table border="0" cellspacing="0" cellpadding="0" style="border: none; background: none">
				<tr>
					<td style="border: none; text-align: right;">
						<label for="password">
							I want my password to be:
						</label>
					</td>
					<td style="border: none;">
						<input type="password" name="password" id="password" />
					</td>
				</tr>
				<tr>
					<td style="border: none; text-align: right;">
						<label for="cpassword">
							Type it again:
						</label>
					</td><td style="border:none;">
						<input type="password" name="cpassword" id="cpassword" />
					</td>
				</tr>
			</table>
			<h3>
				Are you a Barley CAP participant?
			</h3>
			<input $c_no type="radio" value="no" name="answer" id="answer_no" />
			<label for="answer_no">
				No
			</label>
			<br />
			<input $c_yes type="radio" value="yes" name="answer" id="answer_yes" />
			<label for="answer_yes">
				Yes
			</label>
			<br />
			<br/>
			<input type="submit" name="submit_registration" value="Register" />
		</form>
HTML;
	return $retval;
}

/**
 *
 */
function HTMLLoginForm($msg = "")
{
	$email = "";
	if (isset($_GET['e']) && !empty($_GET['e']))
		$email = base64_decode($_GET['e']);
	$c_no = "checked=\"checked\"";
	$c_yes = "";
	if (isset($_GET['a']) && !empty($_GET['a'])){
		$c_no = "";
		$c_yes = "checked=\"checked\"";
	}

	$retval = "";
	if (!empty($msg))
	{
		$retval .= <<< HTML
		<div id="form_error">
			$msg
		</div>
HTML;
	}
	$retval .= <<< HTML
		<form action="{$_SERVER['PHP_SELF']}" method="post">
			<h3>
				What is your e-mail address?
			</h3>
			My e-mail address is: <input type="text" name="email" value="$email" />
			<h3>
				Do you have a password?
			</h3>
			<input id="answer_no" $c_no type="radio" name="answer" value="no" />
			<label for="answer_no">
				No, I am a new user.
			</label>
			<br />
			<input id="answer_yes" $c_yes type="radio" name="answer" value="yes" />
			<label for="answer_yes">
				Yes, I have a password:
			</label>
			<input type="password" name="password" onfocus="$('answer_yes').checked = true"/>
			<br />
			<input id="answer_forgot" $c_forgot type="radio" name="answer" value="forgot" />
			<label for="answer_forgot">
				I forgot my password
			</label>
			<br />
			<input id="answer_change" $c_change type="radio" name="answer" value="change" />
			<label for="answer_change">
				I want to change my Password.
			</label>
			<br />
			<br />
			<input type="submit" name="submit_login" value="Continue" />
		</form>
HTML;
	return $retval;
}

/**
 *
 */
function HTMLLoginSuccess()
{
	$url = (isset($_SESSION['login_referer'])) ? $_SESSION['login_referer'] : 'index.php';
	return <<< HTML
		<p>
			You have been logged in. Please wait while you are being redirected.
		</p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<meta http-equiv="refresh" content="2;url=$url" />
HTML;
}

/**
 *
 */
function HTMLRegistrationSuccess($name, $email)
{
	$email = base64_encode($email);
	return <<< HTML
		<p>
			Welcom, $name. You have been registered. Please wait while you are being redirected.
		</p>
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<br />
		<meta http-equiv="refresh" content="2;url=login.php?e=$email&a=1" />
HTML;
}

/**
 *
 */
function isUser($email, $pass)
{
	$sql = "SELECT * FROM users WHERE users_name = '$email' AND pass = MD5('$pass') LIMIT 1";
	$query = mysql_query($sql) or die("<pre>".mysql_error()."\n\n\n".$sql."</pre>");
	return mysql_num_rows($query) > 0;
}

/**
 *
 */
function isUser2($email)
{
	$sql = "SELECT * FROM users WHERE users_name = '$email' LIMIT 1";
	$query = mysql_query($sql) or die("<pre>".mysql_error()."\n\n\n".$sql."</pre>");
	return mysql_num_rows($query) > 0;
}

if (isset($_POST['submit_login']))
{
	connect();
	if (isset($_POST['answer']))
	{
		if ($_POST['answer'] == "no")
		{
			if (isUser2($_POST['email']))
			{
				echo HTMLLoginForm("That e-mail address already has an account associated with it. Please, try again.");
			}
			else
			{
				echo HTMLRegistrationForm("", "", $_POST['email']);
			}
		}
		else if ($_POST['answer'] == "yes")
		{
			$email = $_POST['email'];
			$password = $_POST['password'];

			if (isUser($email, $password))
			{
				$_SESSION['username'] = $email;
				$_SESSION['password'] = md5($password);

				$sql = "UPDATE users SET lastaccess = NOW() WHERE users_name = '$email'";
				mysql_query($sql) or die("<pre>".mysql_error()."\n\n\n".$sql."</pre>");

				echo HTMLLoginSuccess();
			}
			else
			{
				echo HTMLLoginForm("You entered an incorrect e-mail/password combination. Please, try again.");
			}
		}
		else if ($_POST['answer'] == "forgot")
		{
			$email = $_POST['email'];
			?>
			Email ID: <input name= "txt" type="text" value=""> <br>
			<input name="cmd_submit" type="submit" value="Submit"></input>
			
			<?php
		}
		else
		{
			echo HTMLLoginForm();
		}
	}
	else
	{
		echo HTMLLoginForm();
	}
}
else if (isset($_POST['submit_registration']))
{
	$name = $_POST['name'];
	$email = $_POST['email'];
	$cemail = $_POST['cemail'];
	$password = $_POST['password'];
	$cpassword = $_POST['cpassword'];
	$answer = $_POST['answer'];

	$error = false;
	$error_msg = "";

	if (empty($name)){
		$error = true;
		$error_msg .= "- You must provide your name.\n";
	}
	if (empty($email)){
		$error = true;
		$error_msg .= "- You must provide your e-mail addresses.\n";
	}
	else
	{
		if (empty($cemail) || $email != $cemail){
			$error = true;
			$error_msg .= "- The e-mail address you provided don't match.\n";
		}
	}
	if (empty($password)){
		$error = true;
		$error_msg .= "- You must provide a password.\n";
	}
	else
	{
		if (empty($cpassword) || $password != $cpassword){
			$error = true;
			$error_msg .= "- The passwords you provided don't match.\n";
		}
	}

	if ($error)
	{
		echo HTMLRegistrationForm($error_msg, $name, $email, $cemail, $answer);
	}
	else
	{
		if ($answer == "yes") {

			$sql = "INSERT INTO
						users (user_types_uid, users_name, pass, name, email)
					VALUES ('103', '$email', MD5('$password'), '$name', '$email')";

		}
		else
		{
			$sql = "INSERT INTO
						users (user_types_uid, users_name, pass, name, email)
					VALUES ('102', '$email', MD5('$password'), '$name', '$email')";
		}
		mysql_query($sql) or die("<pre>".mysql_error()."\n\n\n".$sql."</pre>");

		echo HTMLRegistrationSuccess($name, $email);
	}
}
else
{
	$referer = @(isset($_SESSION['login_referer_override'])) ? $_SESSION['login_referer_override'] : $_SERVER['HTTP_REFERER'];
	if (! empty($referer) && stripos($referer, $_SERVER['HTTP_HOST']) !== FALSE){
		$_SESSION['login_referer'] = $referer;
	}
	unset($_SESSION['login_referer_override']);
	echo HTMLLoginForm();
}

?>
	</div>
<?php
$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
