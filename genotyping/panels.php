<?php
require 'config.php';
include $config['root_dir'] . 'includes/bootstrap_curator.inc';
$mysqli = connecti();
loginTest();
if (loginTest2()) {
    $row = loadUser($_SESSION['username']);
    $myname = $row['users_name'];
    $myid = $row['users_uid'];
}
authenticate_redirect(array(USER_TYPE_PUBLIC,USER_TYPE_PARTICIPANT,USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
include $config['root_dir'].'theme/admin_header.php';
?>

<style type='text/css'>
  table td {vertical-align:top; text-align:left;}
</style>

<div id="primaryContentContainer">
<div id="primaryContent">
<div class="section">
<h1>Create a Marker Panel</h1>

<?php
// If we're re-entering the script with data, handle it.
// 1. New panel from Create button:
if (isset($_POST['panel']) && $_POST['panel'] != "") {
    $panel = $_POST['panel'];
    $comment = $_POST['comment'];
    $sql="select markerpanels_uid from markerpanels where name = '$panel' and users_uid = $myid";
    $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
    if (mysqli_num_rows($r) > 0) {
        echo "<p><font color=red>Panel \"$panel\" already exists.</font>";
    } else {
        if (count($_SESSION['clicked_buttons']) == 0) {
	    echo "<p><font color=red>Please <a href='".$config['base_url']."genotyping/marker_selection.php'>select some markers</a> first.</font>";
        } else {
	    $markerids = implode(",", $_SESSION['clicked_buttons']);
	    $sql = "insert into markerpanels (name, users_uid, comment, marker_ids) values ('$panel', $myid, '$comment', '$markerids')";
	    $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
        }
    }
}
// 2. "Deselect markers" button:
if (isset($_POST['deselLines'])) {
  $selected_markers = $_SESSION['clicked_buttons'];
  foreach ($_POST['deselLines'] as $marker_uid) {
    if (($markeridx = array_search($marker_uid, $selected_markers)) !== false) {
      array_splice($selected_markers, $markeridx,1);
    }
  }
  $_SESSION['clicked_buttons']=$selected_markers;
}
// 3. "Delete panels" button:
if (isset($_POST['deselPanel'])) {
  $remove = $_POST['deselPanel'];
  for ($i=0; $i < count($remove); $i++) {
    $sql = "delete from markerpanels
         where markerpanels_uid = $remove[$i]";
    $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  }
}
// End of handling user input.

// Panel creation table:
print "<table><tr>";
// Fetch stored cookie if signed-in.
$username=$_SESSION['username'];
if ($username && !isset($_SESSION['clicked_buttons'])) {
  $stored = retrieve_session_variables('selected_markers', $username);
  if (is_array($stored))
    $_SESSION['clicked_buttons'] = $stored;
 }
// Show nothing if empty.
$display = $_SESSION['clicked_buttons'] ? "":" style='display: none;'";
?>

<td><font color=blue><b>Currently selected markers</b></font>: 
<?php echo count($_SESSION['clicked_buttons']);
 ?>

<form id='deselLinesForm' action='<?php echo $_SERVER['PHP_SELF'] ?>' method='post' <?php echo $display ?>>
<select name='deselLines[]' multiple='multiple' style='height: 13em;width: 16em'>
<?php
if (isset($_SESSION['clicked_buttons'])) {
foreach ($_SESSION['clicked_buttons'] as $markeruid) {
  $result=mysqli_query($mysqli, "select marker_name from markers where marker_uid=$markeruid") 
  or die("invalid marker uid\n");
  while ($row=mysqli_fetch_assoc($result)) {
    $selval=$row['marker_name'];
    print "<option value='$markeruid'>$selval</option>\n";
  }
}
}
?>

</select>
<br><input type='submit' value='Deselect highlighted markers' /></p>
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
// Store the selected markers in the database.
if ($username)
  store_session_variables('selected_markers', $username);

// Show current list of panels, if any.
$r = mysqli_query($mysqli, "select * from markerpanels where users_uid = $myid") or die(mysqli_error($mysqli));
if (mysqli_num_rows($r) > 0) {
  print "</div><div class='section'><h1>My Panels</h1>";
  print "<table><tr><td>";
  print "<form id='deselPanelForm' action='".$_SERVER['PHP_SELF']."' method='post'>";
  print "<select name='deselPanel[]' multiple='multiple' style='width: 16em' onclick='showcomment(this.value)'>";
  while ($row = mysqli_fetch_array($r)) {
    if (empty($row['marker_ids']))
      $count = 0;
    else {
      $panelid = $row['markerpanels_uid'];
      $panelname = $row['name'];
      $panelids[] = $panelid;
      $panelnames[] = $panelname;
      $counts[] = count(explode(',', $row['marker_ids']));
      $comments[] = $row['comment'];
    }
    print "<option value=$panelid>$panelname</option>";
  }
  ?>
  </select>
  <br><button type='submit' id='deletebutton' disabled>Delete</button>
  </form>
  <td>
  <table><thead><th>Panel</th><th>Markers</th><th>Description</th></thead>
  <?php
  for ($i=0; $i < count($panelnames); $i++) {
    print "<tr>";
    print "<td><a href ='".$config['base_url']."genotyping/marker_selection.php?mypanel=$panelids[$i]'>$panelnames[$i]</a></td>";
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
