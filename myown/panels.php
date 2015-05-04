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
authenticate_redirect(array(USER_TYPE_PUBLIC,USER_TYPE_PARTICIPANT,USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
include($config['root_dir'].'theme/admin_header.php');
?>

<style type='text/css'>
  table td {vertical-align:top; text-align:left;}
</style>

<div id="primaryContentContainer">
  <div id="primaryContent">
  <div class="section">
  <h1>Create a Line Panel</h1>

  <?php
  // If we're re-entering the script with data, handle it.
  // 1. New panel from Create button:
  if ( isset($_POST['panel']) && $_POST['panel'] != "" ) {
    $panel = $_POST['panel'];
    $comment = $_POST['comment'];
    $sql="select linepanels_uid from linepanels where name = '$panel' and users_uid = $myid";
    $r = mysql_query($sql) or die(mysql_error(). "<br>Query was: $sql");
    if (mysql_num_rows($r) > 0)
      echo "<p><font color=red>Panel \"$panel\" already exists.</font>";
    else {
      if (count($_SESSION['selected_lines']) == 0) {
	echo "<p><font color=red>Please <a href='".$config['base_url']."pedigree/line_properties.php'>select some lines</a> first.</font>";
      }
      else {
	$lineids = implode(",", $_SESSION['selected_lines']);
	$sql = "insert into linepanels (name, users_uid, comment, line_ids) values ('$panel', $myid, '$comment', '$lineids')";
	$r = mysql_query($sql) or die(mysql_error(). "<br>Query was: $sql");
      }
    }
  }
// 2. "Deselect lines" button:
if (isset($_POST['deselLines'])) {
  $selected_lines = $_SESSION['selected_lines'];
  foreach ($_POST['deselLines'] as $line_uid) {
    if (($lineidx = array_search($line_uid, $selected_lines)) !== false) {
      array_splice($selected_lines, $lineidx,1);
    }
  }
  $_SESSION['selected_lines']=$selected_lines;
}
// 3. "Delete panels" button:
if (isset($_POST['deselPanel'])) {
  $remove = $_POST['deselPanel'];
  for ($i=0; $i < count($remove); $i++) {
    $sql = "delete from linepanels
         where linepanels_uid = $remove[$i]";
    $r = mysql_query($sql) or die(mysql_error(). "<br>Query was: $sql");
  }
}
// End of handling user input.

// Panel creation table:
print "<table><tr>";
// Fetch stored cookie if signed-in.
$username=$_SESSION['username'];
if ($username && !isset($_SESSION['selected_lines'])) {
  $stored = retrieve_session_variables('selected_lines', $username);
  if (-1 != $stored)
    $_SESSION['selected_lines'] = $stored;
 }
// Show nothing if empty.
$display = $_SESSION['selected_lines'] ? "":" style='display: none;'";
?>

<td><font color=blue><b>Currently selected lines</b></font>: 
<?php echo count($_SESSION['selected_lines']) ?>

<form id='deselLinesForm' action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post' <?php echo $display ?>>
<select name='deselLines[]' multiple='multiple' style='height: 13em;width: 16em'>
<?php
  if ($_SESSION['selected_lines']) {
    foreach ($_SESSION['selected_lines'] as $lineuid) {
      $result=mysql_query("select line_record_name from line_records where line_record_uid=$lineuid") 
      or die(mysql_error()."<br>invalid line uid: $lineuid<br>");
      while ($row=mysql_fetch_assoc($result)) {
	$selval=$row['line_record_name'];
	print "<option value='$lineuid'>$selval</option>\n";
      }
    }
  }
?>

</select>
<br><input type='submit' value='Deselect highlighted lines' /></p>
</form></td>
<td style='vertical-align:top; text-align:left;'>
<form action = '<?php echo $_SERVER['PHP_SELF'] ?>' method='post'>
Panel Name<br>
<input type=text name=panel><br>
Description<br>
<textarea rows=7 cols=30 name=comment></textarea><br>
<input type=submit value='Create'><br>
</form></td>
</tr></table>

<?php	
// Store the selected lines in the database.
if ($username)
  store_session_variables('selected_lines', $username);

// Show current list of panels, if any.
$r = mysql_query("select * from linepanels where users_uid = $myid") or die(mysql_error()."<br>Couldn't get users_uid.");
if (mysql_num_rows($r) > 0) {
  print "</div><div class='section'><h1>My Panels</h1>";
  print "<table><tr><td>";
  print "<form id='deselPanelForm' action='".$_SERVER['PHP_SELF']."' method='post'>";
  print "<select name='deselPanel[]' multiple='multiple' style='width: 16em' onclick='showcomment(this.value)'>";
  while ($row = mysql_fetch_array($r)) {
    if (empty($row['line_ids']))
      $count = 0;
    else {
      $panelid = $row['linepanels_uid'];
      $panelname = $row['name'];
      $panelids[] = $panelid;
      $panelnames[] = $panelname;
      $counts[] = count(explode(',', $row['line_ids']));
      $comments[] = $row['comment'];
    }
    print "<option value=$panelid>$panelname</option>";
  }
  ?>
  </select>
  <br><button type='submit' id='deletebutton' disabled>Delete</button>
  </form>
  <td>
  <table><thead><th>Panel</th><th>Lines</th><th>Description</th></thead>
  <?php
  for ($i=0; $i < count($panelnames); $i++) {
    print "<tr>";
    print "<td><a href ='".$config['base_url']."pedigree/line_properties.php?mypanel=$panelids[$i]'>$panelnames[$i]</a></td>";
    print "<td>$counts[$i]</td><td>$comments[$i]</td></tr>";
  }
  print "</td</tr></table></table>";
}

?>
<script type='text/javascript'>
    function showcomment(panelid) {
	document.getElementById('deletebutton').disabled = false;
	//alert('p = '+ panelid);
    }
</script>

<?php
print "</div></div></div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
