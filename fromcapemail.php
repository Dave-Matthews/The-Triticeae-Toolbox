<?php
// 12/14/2010 JLee  Change to use curator bootstrap

require_once 'config.php';
require_once $config['root_dir'] . 'includes/bootstrap_curator.inc';
require_once $config['root_dir'] . 'includes/aes.inc';
require_once $config['root_dir'].'theme/normal_header.php';

if (!isset($_GET['token'])) {
    die("The token must be included in the URL.");
}

$mysqli = connecti();
$token = $_GET['token'];
$email = AESDecryptCtr($token, setting('capencryptionkey'), 128);

$sql_email = mysqli_real_escape_string($mysqli, $email);

$user_type_participant = USER_TYPE_PARTICIPANT;
//$sql = "select users_uid, name, institution from users where users_name='$sql_email' and user_types_uid<>$user_type_participant";
//$sql = "select users_uid, name, institution from users where users_name='$sql_email'";
$sql = "select users_uid, name, institution from users where users_name= SHA1('$sql_email')";
$r = mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli));
if (!mysqli_num_rows($r)) {
    die("Couldn't find a record for user \"$email\" in the database.");
}
$row = mysqli_fetch_assoc($r);
extract($row);
$html_name = htmlspecialchars($name, ENT_QUOTES);
$html_email = htmlspecialchars($email, ENT_QUOTES);
$html_institution = htmlspecialchars($institution, ENT_QUOTES);
$html_token = htmlspecialchars($token, ENT_QUOTES);

$usertype = mysql_grab("select user_types_uid from users where users_name='$sql_email'");

echo "<h1>CAP Participant Confirmation</h1>";

if ($usertype === USER_TYPE_PARTICIPANT) {
    echo "'$html_name' is already a CAP Participant and needs no further confirmation.<p>";
    $confirmed = true;
}

if (!($confirmed) && !isset($_GET['yes']) && !isset($_GET['no'])) {
    echo <<<HTML
<p>Please confirm the following CAP participant:<br />
  <table>
    <tr><td>Name</td><td>$html_name</td></tr>
    <tr><td>Email</td><td>$html_email</td></tr>
    <tr><td>Institution</td><td>$html_institution</td></tr></table>
    <br />
    <form action="">
      <input type="hidden" name="token" value="{$html_token}"></input>
      <input type="submit" name="yes"
	     value="Yes, this user is a CAP participant"></input>
      <input type="submit" name="no"
	     value="No, this user is not a CAP participant"></input>
    </form>
</p>
HTML;
} else {
    if (isset($_GET['yes'])) {
        $sql = "update users set user_types_uid=$user_type_participant
where users_uid=$users_uid";
        mysqli_query($mysqli, $sql) or die("<pre>" . mysqli_error($mysqli));
        echo "<h3>User was marked as CAP participant</h3>";
    } else {
        echo "<h3>User was NOT marked as CAP participant</h3>";
    }
}
$footer_div = 1;
include $config['root_dir'].'theme/footer.php';
