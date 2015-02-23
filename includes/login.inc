<?php

/*
 * Functions for the Login System
 */



/*
 * Add a default adminster account.
 */
function addAdmin() {
	addUser("Admin", "hj00k", "0", "Iowa State University", "Admin", "NoEmail");
}


/*
 * Checks to see if the username is already in the database 
 *
 * @return true if the username IS NOT already being used, false if it is.
 */
function checkForUser($username) {
    global $mysqli;
	$query = mysqli_query($mysqli, "SELECT pass FROM users WHERE users_name = '$username'");

	if(mysqli_num_rows($query) > 0)
		return FALSE;
	else
		return TRUE;
}

/*
 * Checks to see if the password is good or not
 *
 * Note: Good means the password is more than 5 characters and contains at least 1 number
 *
 * @return true if the password is good : false if not
 */
function checkGoodPassword($password) {

	$length = strlen($password) > 7;	    //length greater than 5?
	$number = preg_match("/[0-9]/", $password); //find any number?

	if($length && $number)
		return true;
	else
		return false;
}

/*
 * Add a user to the table.
 *
 * @return true if the query is successful
 *
 * Note: if the username is unique then it will not be able to add another user with the same username.
 *	 this will be the main reason this function returns false.
 */
function addUser($username, $password, $type, $institute, $name, $email) { //add user to table
    global $mysqli;
    	$password = encrypt($password);

	$str = "INSERT INTO users (users_name, user_types_uid, institutions_uid, pass, name, email, lastaccess, created_on) 
			VALUES('$username', '$type', '$institute', '$password', '$name', '$email', NOW(), NOW())";

    	$query = mysqli_query($mysqli, $str) or die(mysqli_error($mysqli));
	if(!$query) 
		return false;
	else
		return true;
}

/*
 * Remove a user to the table.
 *
 * @return true if the query is successful
 */
function removeUser($username) { //remove user from table
    global $mysqli;
    	$user = $username;
    	$query = mysqli_query($mysqli, "DELETE FROM users WHERE users_name = '$user' LIMIT 1");
	if(!$query) 
		return false;
	else
		return true;
}

/*
 * Changes a User's password in the database if the user does exist. 
 * 
 * Note: This function validates the username and password. and returns false if 
 *	 the old password given doesn't match the actual old password
 * 
 * @param $username - the username who's password is going to be changed
 * @param $newpass - the new password to use. 
 * @param $oldpass - the old password to ensure this action is legitimate.
 * @return true if the change was successful
 */
function changePass($username, $newpass, $oldpass) {
    global $mysqli;
	if(login($username, $oldpass)) {

		$password = encrypt($newpass);
		$updateString = "UPDATE users SET pass='$password' WHERE users_name='$username' LIMIT 1";
		$query = mysqli_query($mysqli, $updateString);

		if(!$query)
			return false;
		else
			return true;
	}

	return false;

}

/*
 * function used to test the username and password
 *
 * @return the encrypted password on successful match - false on failure
 */
function login($user, $password) { //attempt to login false if invalid true if correct
    global $mysqli;
	$auth = FALSE;
	$result = mysqli_query($mysqli, "SELECT pass FROM users WHERE users_name='$user'");
	$pass = mysqli_fetch_row($result);
	if (encrypt($password) === $pass[0]) {
    	   $auth = $pass[0];
	}
	return $auth;
}


/*
 * This function is used to test whether or not the user is already logged in.
 * The login function returns the encrypted password which is then stored in a cookie.
 * That password is what's given here to reconfirm that the user is logged in. 
 *
 * @return true on successful match - false on failure
 */
function isLoggedIn($user, $hashpass) { 
    global $mysqli;
	$auth = FALSE;
	if($hashpass != "") { 
           $result = mysqli_query($mysqli, "SELECT pass FROM users WHERE users_name = SHA1('$user')") or die(mysqli_error($mysqli));
   	   $pass = mysqli_fetch_row($result);

	   if ($hashpass === $pass[0]) {
    	      $auth = TRUE;
	   }
	}
	return $auth;
}


/*
 * Encryption function using sha1
 */
function encrypt($string) {
	//return sha1($string);
	return md5($string);
}

?>
