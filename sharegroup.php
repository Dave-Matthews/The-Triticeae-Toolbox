<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();
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
echo "<h3><font color=blue>Current group members</font></h3>";

print "<form id=\"deselGroupForm\" action=\"".$_SERVER['PHP_SELF']."\" method=\"post\">";
print "<select name=\"deselGroup[]\" multiple=\"multiple\" style=\"height: 6em;width: 16em\">";
print "<option value=\"$user_uid\">Peter Bradbury</option>\n";
print "<option value=\"$user_uid\">Jean-Luc Jannink</option>\n";
print "<option value=\"$user_uid\">Mark Sorrells</option>\n";
print "<option value=\"$user_uid\">Dave Matthews</option>\n";
print "<option value=\"$user_uid\">Barack Obama</option>\n";
print "<option value=\"$user_uid\">Chiang Kai-Shek</option>\n";
print "</select>";
print "&nbsp;&nbsp;&nbsp;<input type=\"submit\" value=\"Remove highlighted members\" /></p>";
print "</form>";
?>

<p>
<form action = "">
  Add a member<br>
  <input type=text> <input type=submit value="Add"><br>
  User's T3 email address
  </form>
  <p>



</div></div></div>

<?php 
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); ?>
