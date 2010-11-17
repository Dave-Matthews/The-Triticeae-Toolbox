<html>
<body>
<?php



/**

 * The letter l (lowercase L) and the number 1

 * have been removed, as they can be mistaken

 * for each other.

 */



function createRandomPassword() {



    $chars = "abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ023456789";

    srand((double)microtime()*1000000);

    $i = 0;

    $pass = '' ;



    while ($i <= 7) {

        $num = rand() % 33;

        $tmp = substr($chars, $num, 1);

        $pass = $pass . $tmp;

        $i++;

    }



    return $pass;



}



// Usage

if (isset($_GET['cmd_forgot_pass']))
{

$password = createRandomPassword();

echo "Your random password is: $password"; 

$mail_id=$_GET['txt'];
$message="Your new password is $password";
echo "<br />";
$pass=MD5('$password');
echo "Encrypted Password: ".$pass;
$from="no-reply";

//mail($mail_id, 'Password Reset', $message, "From: $from");
}
elseif (isset($_GET['cmd_change_pass']))
{
?>
Email ID:
<input name = "EmailID" type = "text" ></input><br>
Old Password:
<input name = "OldPass" type = "passwd"></input><br></br>
New Password:
<input name = "NewPass1" type = "passwd"></input><br></br>
Retype New Password:

<input name = "NewPass2" type = "passwd"></input><br></br>

<?php
}
?> 
</body>
</html>