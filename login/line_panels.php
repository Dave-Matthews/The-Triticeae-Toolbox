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
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
include($config['root_dir'].'theme/admin_header.php');
?>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <div class="section">
  <h1>Add a Line Panel</h1>

<?php

// If we're re-entering the script with data, handle it.
//print "<pre>"; print_r($_POST); print "</pre>";
  if ( isset($_POST['panel']) && $_POST['panel'] != "" ) {
    $panel = $_POST['panel'];
    if ($panel == "&lt;panel name&gt;")
      echo "<p><font color=red>Please name the panel.</font>";
    else {
      $sql="select linepanels_uid from linepanels where name = '$panel'";
      $r = mysql_query($sql) or die(mysql_error());
      if (mysql_num_rows($r) > 0)
	echo "<p><font color=red>Panel \"$panel\" already exists.</font>";
      else {
	if (count($_SESSION['selected_lines']) < 2) {
	  echo "<p><font color=red>Why would you want a panel that doesn't contain at least a few lines?</font>";
	}
	else {
	  $lineids = implode(",", $_SESSION['selected_lines']);
	  $sql = "insert into linepanels (name, line_ids) values ('$panel', '$lineids')";
	  $r = mysql_query($sql) or die(mysql_error());
	}
      }
    }  
  }

if (isset($_POST['deselLines'])) {
  $selected_lines = $_SESSION['selected_lines'];
  foreach ($_POST['deselLines'] as $line_uid) {
    if (($lineidx = array_search($line_uid, $selected_lines)) !== false) {
      array_splice($selected_lines, $lineidx,1);
    }
  }
  $_SESSION['selected_lines']=$selected_lines;
}

if (isset($_POST['deselPanel'])) {
  $remove = $_POST['deselPanel'];
  for ($i=0; $i < count($remove); $i++) {
    $sql = "delete from linepanels
         where linepanels_uid = $remove[$i]";
    $r = mysql_query($sql) or die(mysql_error());
  }
 }
// End of handling user input.

print "<table><tr><td style='vertical-align:top; text-align:left;'>";
print "<form action = \"".$_SERVER['PHP_SELF']."\" method=\"post\">";
print "Add <font color=blue>current selection</font> as a panel.<br>";
print "<input type=text name=panel value='&lt;panel name&gt;'>";
print "<br><input type=submit value=\"Add\"><br>";
print "</form>";

$username=$_SESSION['username'];
if ($username && !isset($_SESSION['selected_lines'])) {
  $stored = retrieve_session_variables('selected_lines', $username);
  if (-1 != $stored)
    $_SESSION['selected_lines'] = $stored;
 }
$display = $_SESSION['selected_lines'] ? "":" style='display: none;'";

$selectedcount = count($_SESSION['selected_lines']);
print "</td><td style='vertical-align:top; text-align:left;'>";
echo "<font color=blue><b>Currently selected lines</b></font>: $selectedcount";

print "<form id=\"deselLinesForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\" $display>";
print "<select name=\"deselLines[]\" multiple=\"multiple\" style=\"height: 12em;width: 16em\">";
foreach ($_SESSION['selected_lines'] as $lineuid) {
  $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
  while ($row=mysql_fetch_assoc($result)) {
    $selval=$row['line_record_name'];
    print "<option value=\"$lineuid\">$selval</option>\n";
  }
}
print "</select>";
print "<br><input type=\"submit\" value=\"Deselect highlighted lines\" /></p>";
print "</form>";
print "</td></tr></table>";
	
// Store the selected lines in the database.
if ($username)
  store_session_variables('selected_lines', $username);

// Show current list of panels, if any.
$r = mysql_query("select * from linepanels") or die(mysql_error());
if (mysql_num_rows($r) > 0) {
  print "</div><div class='section'><h1>Delete Panels</h1>";
  print "<form id=\"deselPanelForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
  print "<select name=\"deselPanel[]\" multiple=\"multiple\" style=\"height: 12em;width: 16em\">";
  while ($row = mysql_fetch_row($r)) {
    if (empty($row[2]))
      $count = 0;
    else 
      $count = count(explode(',', $row[2]));
    print "<option value=\"$row[0]\">$row[1] ($count)</option>\n";
  }
  print "</select>";
  print "&nbsp;&nbsp;&nbsp;<br><input type=\"submit\" value=\"Delete highlighted panels\" /></p>";
  print "</form>";
}

print "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
