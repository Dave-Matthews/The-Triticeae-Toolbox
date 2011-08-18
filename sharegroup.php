<?php
require 'config.php';
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
connect();

loginTest();
if (loginTest2()) {
  $row = loadUser($_SESSION['username']);
  $myname = $row['users_name'];
  $myid = $row['users_uid'];
 }
//ob_start();
//authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR, USER_TYPE_PARTICIPANT));
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
//ob_end_flush();
include($config['root_dir'].'theme/admin_header.php');
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <h1>Sharing private data</h1>
  <div class="section">
  <p>Phenotype and genotype datasets you add to T3 are stamped
  <b>Public</b>, <b>CAP</b> or <b>Private</b>, as you choose.
  CAP data will become Public after X months by Triticeae CAP project policy.
  Private data will be visible only to the users you specify here.
  It is your responsibility to notify these users that some of the 
  data they see is private and to discuss any restrictions on their 
  use of it.

<?php

// If we're re-entering the script with data, handle it.
//print "<pre>"; print_r($_POST); print "</pre>";
  if ( isset($_POST['newshare']) && $_POST['newshare'] != "" ) {
    $newshare = $_POST['newshare'];
    $_POST['newshare'] = "";
    $sql="select users_uid from users where users_name = '$newshare'";
    $r = mysql_query($sql);
    if (mysql_num_rows($r) == 0)
      echo "<p><font color=red>\"$newshare\" not found.</font>";
    else {
      $row = mysql_fetch_row($r);
      $shareuid = $row[0];
      $sql = "insert into sharegroup (owner_users_uid, shareto_users_uid) values ($myid, $shareuid)";
      $r = mysql_query($sql) or die(mysql_error());
    }
  }  
if (isset($_POST['deselGroup'])) {
  $remove = $_POST['deselGroup'];
  for ($i=0; $i < count($remove); $i++) {
    $sql = "delete from sharegroup 
         where owner_users_uid = $myid 
         and shareto_users_uid = $remove[$i]";
    $r = mysql_query($sql) or die(mysql_error());
  }
 }
// End of handling user input.

echo "<h3><font color=blue>Current group members</font></h3>";

print "<form id=\"deselGroupForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
print "<select name=\"deselGroup[]\" multiple=\"multiple\" style=\"height: 6em;width: 16em\">";
$sql = "select u2.users_uid, u2.name 
   from users u, users u2, sharegroup 
   where u.users_uid = $myid
   and sharegroup.owner_users_uid = u.users_uid
   and u2.users_uid = sharegroup.shareto_users_uid";
$result = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_row($result)) {
  print "<option value=\"$row[0]\">$row[1]</option>\n";
 }
print "</select>";
print "&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Remove highlighted members\" /></p>";
print "</form>";

print "<p>";
print "<form action = \"".$_SERVER['PHP_SELF']."\" method=\"post\">";
print "Add a member<br>";
print "<input type=text name=newshare>";
print "<input type=submit value=\"Add\"><br>";
print "User's T3 email address";
print "</form>";
print "<p>";

print "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 

?>
