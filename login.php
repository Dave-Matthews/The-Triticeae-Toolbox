<?php
/**
 * Login
 *
 * PHP version 5.3
 *
 * @author   Clay Birkett <clb343@cornell.edu>
 * @license  http://triticeaetoolbox.org/wheat/docs/LICENSE Berkeley-based
 * @link     http://triticeaetoolbox.org/wheat/login.php
 *
 * 12/14/2010 JLee  Change to use curator bootstrap
 *
 */


session_start();
session_regenerate_id();
$root = "//" . $_SERVER['HTTP_HOST'];
$root .= str_replace(basename($_SERVER['SCRIPT_NAME']), "", $_SERVER['SCRIPT_NAME']);
$config['base_url'] = "$root";
$root = preg_replace("/\/\/$/", "/", $root);
$config['root_dir'] = (dirname(__FILE__) . '/');
require 'includes/bootstrap_curator.inc';
require_once 'includes/email.inc';
require_once 'includes/aes.inc';
require_once 'theme/normal_header.php';
require_once 'securimage/securimage.php';
$mysqli = connecti();
?>
<h1>Login/Register</h1>
<div class="section">
<?php
/**
 * Return the registraion form fragment.
 *
 * @param string $msg         message to user
 * @param string $name        user name
 * @param string $email       user email
 * @param string $cemail      confirm user email
 * @param string $answer      response to do you have password
 * @param string $institution institution
 *
 * @return registration form
 */
function HTMLRegistrationForm($msg = "", $name = "", $email = "", $cemail = "", $answer = "no", $institution = "")
{
    // ensure that we go back to home..
    global $mysqli;
    $_SESSION['login_referer_override'] = '/';
    $c_no = "";
    $c_yes = "";
    $c_forgot="";
    $c_change="";
    if ($answer == "no") {
        $c_no = 'checked="checked"';
    }
    if ($answer == "yes") {
        $c_yes = 'checked="checked"';
    }
    $retval = "";
    if (!empty($msg)) {
        $retval .= "<div id='form_error'>$msg</div>\n";
    }
    $sql = "select institutions_name, email_domain from institutions";
    $result = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    $domainMap = array();
    $email_domain = preg_split('/@/', $email);
    $email_domain = $email_domain[1];
    while ($row = mysqli_fetch_assoc($result)) {
        $edomain = $row['email_domain'];
        $iname = $row['institutions_name'];
        if ($edomain) {
            array_push($domainMap, "'$edomain': '$iname'");
            if ($edomain == $email_domain) {
                $institution = $iname;
            }
        }
    }
    $domainMap = '{' . join(", ", $domainMap) . '}';
    $retval .= <<<HTML
<br />
<h2>Registration</h2>
<script type="text/javascript">
  function validatePassword(pw) {
    if (pw.length < 8) {
      alert("Please supply a password of at least 8 characters.");
      return false;
    }
    return true;
  }
  function guessInstitution(email) {
    var dm=$domainMap;
    return dm[email.preg_split('/@/')[1]] || '';
  }
</script>

<style type="text/css">
  table td {padding: 2px;}
</style>
<form action="{$_SERVER['SCRIPT_NAME']}" method="post"
      onsubmit="return validatePassword(document.getElementById('password').value);">
  <h3>Name</h3>
  &nbsp;&nbsp;<label for="name">My name is:</label>&nbsp;
  <input type="text" name="name" id="name" value="$name" /><br>
  &nbsp;&nbsp;Project participants <b>must</b> give a full name to be approved.
  <h3>Email address</h3>
  <table border="0" cellspacing="0" cellpadding="0"
	 style="border: none; background: none">
    <tr><td style="border: none; text-align: right;">
	<label for="email">My email address is:<label></td>
      <td style="border:none;">
	<input type="text" name="email" id="email" value="$email" onchange="document.getElementById('institution').value = guessInstitution(document.getElementById('email').value)" />
     </td></tr><tr><td style="border: none; text-align: right;">
	<label for="cemail">Type it again:</label></td>
      <td style="border: none;"><input type="text" name="cemail" id="cemail" value="$cemail" /></td></tr></table>
  <h3>Password</h3>
  <table border="0" cellspacing="0" cellpadding="0" style="border: none; background: none">
    <tr><td style="border: none; text-align: right;">
	<label for="password">I want my password to be:</label></td>
      <td style="border: none;">
	<input type="password" name="password" id="password" /> At least 8 characters but not special characters "!@#$".</td></tr>
    <tr><td style="border: none; text-align: right;">
	<label for="cpassword">Type it again:</label></td>
      <td style="border:none;">
	<input type="password" name="cpassword" id="cpassword" /></td></tr></table>
  <h3>Institution</h3>
	<table border="0" cellspacing="0" cellpadding="0" style="border: none; background: none"><tr>
	<td style="border: none; text-align: right;">
	<label for="institution">My institution is:<label></td>
	<td style="border:none;">
	<input type="text" name="institution" id="institution"
	       value="$institution" size="30" /> Required for project participants.
        </td></tr></table>
  <h3>Are you a project participant?</h3>
  <input $c_no type="radio" value="no" name="answer" id="answer_no" />
  <label for="answer_no">No</label>
  <br />
  <input $c_yes type="radio" value="yes" name="answer"
	 id="answer_yes" />
  <label for="answer_yes">Yes</label>
  <br />
  <table border="0" cellspacing=="0" cellpadding="0"
	 style="border: none; background: none">
    <tr><td><img id="captcha" src="./securimage/securimage_show.php"
		 alt="CAPTCHA image"><br>
	    <a href="#" onclick="document.getElementById('captcha').src = './securimage/securimage_show.php?' + Math.random();
				 return false;"></td>
      <td>CAPTCHA:
	<input type="text" name="captcha_code" size="10"
		 maxlength="6"></td></tr></table>
   </table>
  <br />
  <br />
  <input type="submit" name="submit_registration" value="Register" />
  </form>
HTML;
    return $retval;
}

/**
 * Return the login form fragment.
 *
 * @param string $msg message to user
 *
 * @return registration form form
 */
function HTMLLoginForm($msg = "")
{
    $email = "";
    if (isset($_GET['e']) && !empty($_GET['e'])) {
        $email = base64_decode($_GET['e']);
    }
    $c_no = "";
    $c_yes = "checked=\"checked\"";
    if (isset($_GET['a']) && !empty($_GET['a'])) {
        $c_no = "";
        $c_yes = "checked=\"checked\"";
    }

    $retval = "";
    if (!empty($msg)) {
        $retval .= "<div id='form_error'>$msg</div>";
    }
    global $config;
    $dir = explode("/", $config['root_dir']);
    // Pop twice.
    $crop = array_pop($dir);
    $crop = array_pop($dir);
    $retval .= <<<HTML
  <form action="{$_SERVER['SCRIPT_NAME']}" method="post">
  <h3>Why Register?</h3>
  <b>Participants</b>
  <ul>
    <li>have pre-release access to all phenotype and genotype data from the project.
 </ul>

  <b>All Registered Users</b>
  <ul>
    <li>can create private germplasm line panels. (<a href="http://malt.pw.usda.gov/t3/barley/curator_data/tutorial/T3_line_panels.pdf">Tutorial</a>)
    <li>can use the "Current Selections" they created during the previous session.
  </ul>

    <h3>What is your email address?</h3>
    My email address is:
    <input type="text" name="email" value="$email" />
    <h3>Do you have a password?</h3>
    <input id="answer_yes" $c_yes type="radio" name="answer" value="yes" />
    <label for="answer_yes">Yes, I have a password:</label>
    <input type="password" name="password" onfocus="$('answer_yes').checked = true"/>
    <br />
    <input id="answer_no" $c_no type="radio" name="answer" value="no" />
    <label for="answer_no">No, I am a new user.</label>
    <br />
    <input id="answer_forgot" $c_forgot type="radio" name="answer" value="forgot" />
    <label for="answer_forgot">I forgot my password.</label>
    <br />
    <input id="answer_change" $c_change type="radio" name="answer" value="change" />
    <label for="answer_change">I want to change my password.</label>
    <br />
    <br />
    <input type="submit" name="submit_login" value="Continue" />
   </form>
HTML;
    return $retval;
}

/**
 * Return the html fragment associated with successful login.
 *
 * @return null
 */
function HTMLLoginSuccess()
{
    // DEM jul2014: Don't return to the previous page.  It might be the "Access Denied"
    //   page which would be confusing.
    //$url = (isset($_SESSION['login_referer'])) ? $_SESSION['login_referer'] : 'index.php';
    global $config;
    $url = $config['base_url']."index.php";
    return <<< HTML
<p>You have been logged in. Welcome!
<p><input type='Button' value='Proceed' onClick='window.location.assign("$url")'>
<meta http-equiv="refresh" content="2;url=$url" />
HTML;
}

/**
 * Return the html fragment associated with successful registration.
 */
function HTMLRegistrationSuccess($name, $email)
{
    $_SESSION['login_referer_override'] = '/';
    $em = $email;
    $email = base64_encode($email);
    return <<< HTML
<p>Welcome, $name. You are being registered. An email has been sent to
$em describing how to confirm your registration.
<!--
Please wait while you are being redirected to login page
or click <a href="{$_SERVER['SCRIPT_NAME']}">here</a>.</p>
<br /><br /><br /><br /><br /><br /><br /><br /><br /><br /><br />
<meta http-equiv="refresh" content="2;url={$_SERVER['SCRIPT_NAME']}"/>
-->
HTML;
}

/**
 * Check if the given user/password pair belongs to a properly
 * registered user that can be logged in.
 */
function isUser($email, $pass)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql_pass = mysqli_real_escape_string($mysqli, $pass);
    $public_type_id = USER_TYPE_PUBLIC;
    $sql = "select * from users where users_name = SHA1('$sql_email') and
pass = SHA1('$sql_pass') and (abs(email_verified) > 0 or
user_types_uid=$public_type_id) limit 1";
    $query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    return mysqli_num_rows($query) > 0;
}

/**
 * Check if the given user/passrod pair belongs to a old account.
 */
function isOldUser($email, $pass)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql_pass = mysqli_real_escape_string($mysqli, $pass);
    $public_type_id = USER_TYPE_PUBLIC;
    $sql = "select * from users where users_name = '$sql_email' and
pass = MD5('$sql_pass') and (abs(email_verified) > 0 or
user_types_uid=$public_type_id) limit 1";
    $query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    return mysqli_num_rows($query) > 0;
}


/**
 * Check if the user+password confirmed his email.
 */
function isVerified($email)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql = "select email_verified from users where
    users_name = SHA1('$sql_email')";
    $r = mysqli_query($mysqli, $sql);
    $row = mysqli_fetch_assoc($r);
    if ($row) {
        return $row['email_verified'];
    }
    return false;
}

/**
 * See if the password is right for the user.
 */
function passIsRight($email, $pass)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql_pass = mysqli_real_escape_string($mysqli, $pass);
    $sql = "select pass=SHA1('$sql_pass') as passIsRight from users
    where users_name = SHA1('$sql_email')";
    $r = mysqli_query($mysqli, $sql);
    $row = mysqli_fetch_assoc($r);
    if ($row) {
        return $row['passIsRight'];
    }
    return false;
}

/**
 * See if the given email belongs to a registered user at all.
 */
function isRegistered($email)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql = "select * from users where users_name = SHA1('$sql_email')";
    $query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    return mysqli_num_rows($query) > 0;
}

/**
 * See if the given email belongs to a old account.
 */
function isOldRegistered($email)
{
    global $mysqli;
    $sql_email = mysqli_real_escape_string($mysqli, $email);
    $sql = "select * from users where users_name = '$sql_email'";
    $query = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    return mysqli_num_rows($query) > 0;
}

/**
 * Process the login attempt and return the appropriate html
 * fragment re that
 */
function HTMLProcessLogin()
{
    global $mysqli;
    $email = $_POST['email'];
    $password = $_POST['password'];
    $rv = '';
    if (!isRegistered($email)) {
        if (isOldRegistered($email)) {
            $rv = HTMLLoginForm("Address <b>'$email'</b> is an old account. Please select \"I forgot my password\" button to reset your password.");
        } else {
            $rv = HTMLLoginForm("Address <b>'$email'</b> has not registered in this T3 database.");
        }
    } elseif (!isVerified($_POST['email'])) {
        $rv = HTMLLoginForm("You cannot login until you confirm your email address, using the link was sent to you at the time of registration.
              <form action=\"{$_SERVER['SCRIPT_NAME']}\" method=\"post\">
              <input type=\"hidden\" name=\"email\" value=\"$email\">
              <input type=\"submit\" name=\"resend_registration\" value=\"Send Register\" />
              </form>");
    } else {
        if (isUser($email, $password)) {
            // Successful login
            $_SESSION['username'] = $email;
            $sql = "SELECT SHA1(\"$password\") AS password";
            $res = mysqli_query($mysqli, $sql) or die("SQL Error hashing password\n");
            if ($row = mysqli_fetch_assoc($res)) {
                $password = $row['password'];
                $_SESSION['password'] = $row['password'];
            } else {
                die("SQL Error hashing password\n");
            }
            // Store user_types_uid in $_SESSION.
            $sql = "select users_uid, user_types_uid, name from users where users_name = SHA1('$email')";
            $res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
            $row = mysqli_fetch_row($res);
            $_SESSION['userid'] = $row[0];
            $_SESSION['usertype'] = $row[1];
            $_SESSION['name'] = $row[2];
            $sql = "update users set lastaccess = now() where
            users_name = '$email'";
            mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) .
                                   "\n\n\n$sql.</pre>");
            // Retrieve stored selection of lines, markers and maps.
            $stored = retrieve_session_variables('selected_lines', $email);
            if (-1 != $stored) {
                $_SESSION['selected_lines'] = $stored;
            }
            $stored = retrieve_session_variables('clicked_buttons', $email);
            if (-1 != $stored) {
                $_SESSION['clicked_buttons'] = $stored;
            }
            $stored = retrieve_session_variables('mapids', $email);
            if (-1 != $stored) {
                $_SESSION['mapids'] = $stored;
            }
            $rv = HTMLLoginSuccess();
        } else {
            if (!passIsRight($_POST['email'], $_POST['password'])) {
                if (isOldUser($email, $password)) {
                    $rv = HTMLLoginForm("You hava an old account. Please select \"I forgot my password\" button to reset your password");
                } else {
                    $rv = HTMLLoginForm("You entered an incorrect e-mail/password combination. Please, try again.");
                }
            } else {
                $rv = HTMLLoginForm("Login failed for unknown reason.");
            }
        }
    }
    return $rv;
}

/**
 * Process registration attempt and return appropriate html fragment
 */
function HTMLProcessRegistration()
{
    if (isRegistered($_POST['email'])) {
        return HTMLLoginForm("That e-mail address already has an account associated with it. Please, try again.");
    } else {
        return HTMLRegistrationForm("", "", $_POST['email'], "", $_POST['answer'], $_POST['institution']);
    }
}

/**
 * Process forgotten password situation and return appropriate html
 * fragment.
 */
function HTMLProcessForgot()
{
    global $root;
    // ensure that we go back to home..
    $_SESSION['login_referer_override'] = '/';
    $email = $_POST['email'];
    if (isRegistered($email) || isOldRegistered($email)) {
        $key = setting('passresetkey');
        $urltoken = urlencode(AESEncryptCtr($email, $key, 128));
        send_email($email, "T3: Reset Your Password",
        "Hi,
Per your request, please visit the following URL to reset your password:
https:{$root}resetpass.php?token=$urltoken");
        return "An email has been sent to you with a link to reset your
password.";
    } else {
        return "<h3 style='color: red'>No such user, please register.</h3>";
    }
}

/**
 * Process password change situation and return appropriate html
 * fragment
 */
function HTMLProcessChange()
{
    global $mysqli;
    $_SESSION['login_referer_override'] = '/';
    $email = $_POST['txt_email'];
    $pass = $_POST['OldPass'];
    $rv = "";
    if (isset($email)) {
        if (isUser($email, $pass)) {
            if ($_POST['NewPass1'] == $_POST['NewPass2']) {
                $sql_email = mysqli_real_escape_string($mysqli, $email);
                $sql_pass = mysqli_real_escape_string($mysqli, $_POST['NewPass1']);
                $sql = "update users  set pass=SHA1('$sql_pass')
                where users_name=SHA1('$sql_email')";
                if (mysqli_query($mysqli, $sql)) {
                    $rv .= "<h3>Password successfully updated</h3>";
                } else {
                    $rv .= "<div id='form_error'>unexpected error while updating your password..</div>";
                }
            } else {
                $rv .= "<div id='form_error'>the two values you provided do not match..</div>";
            }
        } else {
            $rv .= "<div id='form_error'>username/password pair not recognized</div>";
        }
    } else {
        $rv .= <<<HTML
<form action="{$_SERVER['SCRIPT_NAME']}" method="post">
<input type="hidden" name="answer" value="change">
<input type="hidden" name="submit_login" value="">
Email ID: <input name= "txt_email" type="text" value="{$email}">
<br />Old Password: <input name = "OldPass" type="password">
<br /><br />
New Password: <input name="NewPass1" type="password"><br />
Retype New Password: <input name="NewPass2" type="password">
<br />
<input name="cmd_submit" type="submit" value="Submit">
</form>
HTML;
    }
    return $rv;
}

if (isset($_POST['submit_login'])) {
    if (isset($_POST['answer'])) {
        if ($_POST['answer'] == "no") {
            echo HTMLProcessRegistration();
        } elseif ($_POST['answer'] == "yes") {
            echo HTMLProcessLogin();
        } elseif ($_POST['answer'] == "forgot") {
            echo HTMLProcessForgot();
        } elseif ($_POST['answer'] == "change") {
            echo HTMLProcessChange();
        } else {
            echo HTMLLoginForm();
        }
    } else {
      echo HTMLLoginForm();
    }
 } elseif (isset($_POST['resend_registration'])) {
     $email = $_POST['email'];
     if (empty($email)) {
         $error = true;
         $error_msg .= "- You must provide your e-mail addresses.\n";
     } else {
         $safe_email = mysqli_real_escape_string($mysqli, $email);
         $safe_password = mysqli_real_escape_string($mysqli, $password);
         $safe_name = mysqli_real_escape_string($mysqli, $name);
         $sql = "SELECT SHA1('$safe_password') AS password";
         $res = mysqli_query($mysqli, $sql) or die("SQL Error hashing password\n");
         if ($row = mysqli_fetch_assoc($res)) {
             $hash_password = $row['password'];
         } else {
             die("SQL Error hashing password\n");
         }
     $sql = "SELECT SHA1('$safe_email') AS email";
     $res = mysqli_query($mysqli, $sql) or die("SQL Error hashing email\n");
     if ($row = mysqli_fetch_assoc($res)) {
         $hash_email = $row['email'];
     } else {
         die("SQL Error hashing email\n");
     }
     $key = setting('encryptionkey');
     $urltoken = urlencode(AESEncryptCtr($email, $key, 128));
     send_email($email, "T3 registration in progress",
"Thank you for requesting an account on T3.

To complete your registration, please confirm that you requested it 
by visiting the following URL:
https:{$root}fromemail.php?token=$urltoken

Your registration will be complete when you have performed this step.

Sincerely,
The Triticeae Toolbox Team
");
   }
   echo HTMLRegistrationSuccess($name, $email);
 } elseif (isset($_POST['submit_registration'])) {
   $name = $_POST['name'];
   $email = $_POST['email'];
   $cemail = $_POST['cemail'];
   $password = $_POST['password'];
   $cpassword = $_POST['cpassword'];
   $answer = $_POST['answer'];
   $institution = $_POST['institution'];

   $error = false;
   $error_msg = "";

   if (empty($name)) {
     $error = true;
     $error_msg .= "- You must provide your name.\n";
   }
   if (empty($email)) {
     $error = true;
     $error_msg .= "- You must provide your e-mail addresses.\n";
   }
   else {
     if (empty($cemail) || $email != $cemail) {
       $error = true;
       $error_msg .= "- The e-mail address you provided don't match.\n";
     }
   }	
   if (empty($password)) {
     $error = true;
     $error_msg .= "- You must provide a password.\n";
   }
   else {
     if (empty($cpassword) || $password != $cpassword) {
       $error = true;
       $error_msg .= "- The passwords you provided don't match.\n";
     }
   }
   $securimage = new Securimage();
   if (!$securimage->check($_POST['captcha_code'])) {
     $error = true;
     $error_msg .= "- Please enter the CAPTCHA code correctly.\n";
   }
   if (isRegistered($_POST['email'])) {
     $error = true;
     $error_msg .= "That e-mail address already has an account associated with it. Please, try again.";
   }

   if ($error) {
       echo HTMLRegistrationForm($error_msg, $name, $email, $cemail,
			       $answer, $institution);
   } else {
       $safe_email = mysqli_real_escape_string($mysqli, $email);
       $safe_password = mysqli_real_escape_string($mysqli, $password);
       $safe_name = mysqli_real_escape_string($mysqli, $name);
       $sql = "SELECT SHA1('$safe_password') AS password";
       $res = mysqli_query($mysqli, $sql) or die("SQL Error hashing password\n");
       if ($row = mysqli_fetch_assoc($res)) {
           $hash_password = $row['password'];
       } else {
           die("SQL Error hashing password\n");
       }
       $sql = "SELECT SHA1('$safe_email') AS email";
       $res = mysqli_query($mysqli, $sql) or die("SQL Error hashing email\n");
       if ($row = mysqli_fetch_assoc($res)) {
           $hash_email = $row['email'];
       } else {
           die("SQL Error hashing email\n");
       }
       $safe_institution = $institution ? "'" . mysqli_real_escape_string($mysqli, $institution) . "'" : 'NULL';
       $desired_usertype = ($answer == 'yes' ? USER_TYPE_PARTICIPANT :
			  USER_TYPE_PUBLIC);
     /* DEM jan2014 For Sandbox databases, make any registrant a Curator. */
     if (preg_match("/sandbox/", $_SERVER['SERVER_NAME'])) {
         $safe_usertype = USER_TYPE_CURATOR;
         $desired_usertype = USER_TYPE_CURATOR;
     } elseif (preg_match("/malt\.pw\.usda/" , $_SERVER['SERVER_NAME'])) {
         $safe_usertype = USER_TYPE_CURATOR;
         $desired_usertype = USER_TYPE_CURATOR;
     } else {
         $safe_usertype = USER_TYPE_PUBLIC;
     }
     $sql = "insert into users (user_types_uid, users_name, pass,
name, email, institution) values ($safe_usertype, '$hash_email',
'$hash_password', '$safe_name', '$hash_email',
$safe_institution)";
     mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) .
			      "\n\n\n$sql</pre>");
     $key = setting('encryptionkey');
     $urltoken = urlencode(AESEncryptCtr($email, $key, 128));
     send_email($email, "T3 registration in progress",
"Dear $name,

Thank you for requesting an account on T3.

To complete your registration, please confirm that you requested it 
by visiting the following URL:
https:{$root}fromemail.php?token=$urltoken

Your registration will be complete when you have performed this step.

Sincerely,
The Triticeae Toolbox Team
");

/* DEM jan2014 For Sandbox databases, don't require confirmation by tht_curator. */
     if ($desired_usertype == USER_TYPE_PARTICIPANT) {
       $capkey = setting('capencryptionkey');
       $capurltoken = urlencode(AESEncryptCtr($email, $capkey, 128));
       send_email(setting('capmail'),
		  "[T3] Validate Participant $email",
"Email: $email
Name: $name
Institution: $institution

Please use the following link to confirm or reject participant status
of this user:
https:{$root}fromcapemail.php?token=$capurltoken

A message has been sent to the user that he must confirm his email
address at
https:{$root}fromemail.php?token=$urltoken
");
     }

     echo HTMLRegistrationSuccess($name, $email);
   }
 }
 else {
   $referer = @(isset($_SESSION['login_referer_override'])) ?
     $_SESSION['login_referer_override'] : $_SERVER['HTTP_REFERER'];
   if (!empty($referer) &&
 	stripos($referer, $_SERVER['HTTP_HOST']) !== FALSE)
     $_SESSION['login_referer'] = $referer;
   unset($_SESSION['login_referer_override']);
   echo HTMLLoginForm();
 }

?>
</div>
<?php
$footer_div = 1;
require 'theme/footer.php';
