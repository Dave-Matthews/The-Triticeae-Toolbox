<?php
/**
  12/14/2010 JLee  Change to use curator bootstrap
 */

session_start();
$_SESSION['login_referer_override'] = '/';
require_once 'config.php';
require_once $config['root_dir'].'includes/bootstrap_curator.inc';
require_once $config['root_dir'].'includes/aes.inc';

if (!isset($_GET['token'])) {
    die('This script only handles email confirmations.');
}

$mysqli = connecti();
$token = $_GET['token'];
$email = AESDecryptCtr($token, setting('encryptionkey'), 128);
$email = mysqli_real_escape_string($mysqli, $email);
/* $sql = "select users_uid, name from users where users_name = '$email' and email_verified=0;"; */
$sql = "select users_uid, name, email_verified from users where users_name = SHA1('$email')";
$r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n\n\n$sql");
if ($row = mysqli_fetch_assoc($r)) {
    $name = $row['name'];
    $uid = $row['users_uid'];
    $vrfy = $row['email_verified'];
} else {
    die("Couldn't find your record in the database.");
}

require_once $config['root_dir'].'theme/normal_header.php';
?>
<h1>Email Confirmation</h1>
<?php

if ($vrfy == 1) {
    echo "You have already verified yourself.  Thanks again.<p>";
} else {
    if (!isset($_GET['yes']) && !isset($_GET['no'])) {
        $htmltoken = htmlentities($token);
        echo <<< HTML
      <p> Hi {$name}, please confirm that you registered {$email}
    with The Triticeae Toolbox. <br /><br>
      <form action="">
      <input type="hidden" name="token"
      value="{$htmltoken}">
      <input type="submit" name="yes" value="Yes, I did"/>
      <input type="submit" name="no" value="No, I did not register"/>
      </form>
      </p>
HTML;
    } else {
        if (isset($_GET['yes'])) {
            $sql = "update users set email_verified=1 where users_uid=$uid";
        } else {
            $sql = "delete from users where users_uid=$uid;";
        }
        mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli) . "\n\n\n$sql");
        if (isset($_GET['yes'])) {
            echo "<h3>Your registration was confirmed.</h3>";
        } else {
            echo "We have removed the record. Sorry for bothering you.";
        }
    }
}

$footer_div = 1;
require $config['root_dir'].'theme/footer.php';
