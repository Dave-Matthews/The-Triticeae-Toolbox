<?php
// 12/14/2010 JLee  Change to use curator bootstrap


session_start();
$_SESSION['login_referer_override'] = '/';
require_once 'config.php';
require_once($config['root_dir'].'includes/bootstrap_curator.inc');
require_once($config['root_dir'].'includes/aes.inc');

if (!isset($_GET['token'])) {
  header('HTTP/1.0 404 Not Found');
  die('This script only handles email confirmations.');
 }

connect();
$token = $_GET['token'];
$email = AESDecryptCtr($token, setting('encryptionkey'), 128);
$email = mysql_real_escape_string($email);
$sql = "select users_uid, name from users where
users_name = '$email' and email_verified=0;";
$r = mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n$sql");
if (!mysql_num_rows($r)) {
  header('HTTP/1.0 404 Not Found');
  die("Couldn't find your record in the database.");
 }

$row = mysql_fetch_assoc($r);
$name = $row['name'];
$uid = $row['users_uid'];

require_once $config['root_dir'].'theme/normal_header.php';
?>
<h1>Email Confirmation</h1>
<?php
if (!isset($_GET['yes']) && !isset($_GET['no'])) {
  $htmltoken = htmlentities($token);
  echo <<< HTML
    <p> Hi {$name}, please confirm that you registered {$email}
  with the Hordeum Toolbox? <br />
    <form action="">
      <input type="hidden" name="token"
	     value="{$htmltoken}"></input>
    <input type="submit" name="yes" value="Yes, I did"/>
    <input type="submit" name="no" value="No, I did not register"/>
    </form>
    </p>
HTML;
 }
 else {
   if (isset($_GET['yes']))
     $sql = "update users set email_verified=1 where users_uid=$uid";
   else 
     $sql = "delete from users where users_uid=$uid;";
   mysql_query($sql) or die("<pre>" . mysql_error() . "\n\n\n$sql");
   if (isset($_GET['yes']))
     echo "<h3>Your registration was confirmed.</h3>";
   else 
     echo "<h3>We removed the record, sorry for bothering.</h3>";
 }

$footer_div = 1;
include($config['root_dir'].'theme/footer.php');
?>
