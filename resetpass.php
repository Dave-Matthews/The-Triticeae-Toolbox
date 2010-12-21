<?php
// 12/14/2010 JLee  Change to use curator bootstrap

session_start();
$_SESSION['login_referer_override'] = '/';
require_once 'config.php';
require_once $config['root_dir'] . 'includes/bootstrap_curator.inc';
require_once $config['root_dir'] . 'includes/aes.inc';

if (!isset($_GET['token'])) {
  header('HTTP/1.0 404 Not Found');
  die('This script only handles password reset');
 }

connect();
$token = $_GET['token'];
$urltoken = urlencode($token);
$email = AESDecryptCtr($token, setting('passresetkey'), 128);
$sql_email = mysql_real_escape_string($email);
$sql = "select users_uid, name from users where users_name='$sql_email';";
$r = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n$sql");
if (!mysql_num_rows($r)) {
  header('HTTP/1.0 404 Not Found');
  die("Couldn't find your record in the database");
 }
$row = mysql_fetch_assoc($r);
extract($row);

require_once $config['root_dir'] . 'theme/normal_header.php';
?>
<h1>Password Reset</h1>
<?php
if (!isset($_POST['password']) ||
    ($_POST['password'] != $_POST['cpassword'])) {
  if ($_POST['password'] != $_POST['cpassword'])
    echo "<h3 style='color: red'>Two values you provided do not
match, please try again</h3>\n";
  echo <<<HTML
<p>Hi {$name}, to reset your password please type your new
  password in the input boxes below:<br />
  <form action="" method="post">
    <table>
      <tr><td>New password:</td>
	<td><input type="password" name="password" value="">
	    </input></td></tr>
      <tr><td>Retype new password:</td>
	<td><input type="password" name="cpassword" value="">
		    </input></td></tr>
    </table>
    <br />
    <input type="submit" value="Change My Password"></input>
</form></p>
HTML;
 }
 else {
   $sql_password = mysql_real_escape_string($_POST['password']);
   $sql = "update users set pass=MD5('$sql_password') where
users_uid=$users_uid;";
   mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n$sql");
   echo "<h3>Your password was updated.</h3>";
 }

$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
