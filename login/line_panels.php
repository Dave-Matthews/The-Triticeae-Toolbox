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
authenticate_redirect(array(USER_TYPE_ADMINISTRATOR, USER_TYPE_CURATOR));
include $config['root_dir'].'theme/admin_header.php';
?>

  <div class="section">
  <h1>Add a Line Panel</h1>

<?php
// If we're re-entering the script with data, handle it.

// Add a new panel.
$panel = $_POST[panel];
if (!empty($panel) AND $panel != "&lt;panel name&gt;") {
  $desc = $_POST['description'];
  if ($desc == "&lt;description&gt;")
    $desc = "";
  $sql="select linepanels_uid from linepanels where name = '$panel'";
  $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  if (mysqli_num_rows($r) > 0)
    echo "<p><font color=red>Panel \"$panel\" already exists.</font>";
  else {
    if (count($_SESSION['selected_lines']) < 2) 
      echo "<p><font color=red>Why would you want a panel that doesn't contain at least a few lines?</font>";
    else {
      $lineids = implode(",", $_SESSION['selected_lines']);
      $sql = "insert into linepanels (name, comment, line_ids) values ('$panel', '$desc', '$lineids')";
      $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
      echo "Panel \"$panel\" added.<p>";
    }
  }
}

// Delete lines from a panel.
if (isset($_POST['deselLines'])) {
  $selected_lines = $_SESSION['selected_lines'];
  foreach ($_POST['deselLines'] as $line_uid) {
    if (($lineidx = array_search($line_uid, $selected_lines)) !== false) {
      array_splice($selected_lines, $lineidx,1);
    }
  }
  $_SESSION['selected_lines']=$selected_lines;
}

// Delete a panel.
if (isset($_POST[delete])) {
  $remove = $_POST[panelist];
  $sql = "delete from linepanels where linepanels_uid = $remove";
  $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  $feedback = "Panel deleted.<p>";
 }

// Edit a panel description.
if (isset($_POST[update])) {
  $panelid = $_POST[panelist];
  $sql = "update linepanels set comment = '".$_POST[editdesc]."' where linepanels_uid = $panelid";
  $r = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
  $feedback =  "Description updated.<p>";
 }
// End of handling user input.

print "<table><tr><td style='vertical-align:top; text-align:left;'>";
print "<form action = \"".$_SERVER['PHP_SELF']."\" method=\"post\" id=pform>";
print "Add <font color=blue>current selection</font> as a panel.<br>";
print "<input type=text name=panel value='&lt;panel name&gt;'>";
print "<br><textarea name=description form=pform cols=30 rows=7>&lt;description&gt;</textarea>";
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
  $result=mysqli_query($mysqli, "select line_record_name from line_records where line_record_uid=$lineuid") or die("invalid line uid\n");
  while ($row=mysqli_fetch_assoc($result)) {
    $selval=$row['line_record_name'];
    print "<option value='$lineuid'>$selval</option>";
  }
}
print "</select>";
print "<br><input type=submit value='Deselect highlighted lines'>";
print "</form>";
print "</td></tr></table>";
	
// Store the selected lines in the database.
if ($username)
  store_session_variables('selected_lines', $username);

// Edit panel descriptions, or delete.
$r = mysqli_query($mysqli, "select * from linepanels") or die(mysqli_error($mysqli));
if (mysqli_num_rows($r) > 0) {
  print "</div><div class='section'><h1>Edit Panel Description</h1>";
  // If user has sent a command and we're refreshing the page, show confirmation.
  echo $feedback;
  print "<form id=editform method=post>";
  print "<table><tr><th>Name<th>Description";
  print "<tr><td><select id=panelist name=panelist style='width: 16em' onchange='pickpanel(this)'>";
  print "<option value=0>Which panel?...</option>";
  while ($row = mysqli_fetch_assoc($r)) {
    $count = count(explode(',', $row['line_ids']));
    $lpid = $row['linepanels_uid'];
    $lpname = $row['name'];
    print "<option value=$lpid>$lpname ($count)</option>\n";
  }
  print "</select>";
  print "<td><textarea name=editdesc id=editdesc cols=30 rows=7></textarea>";
  print "</table>";
  print "<input type=submit name=update value='Update'>";
  print "<br><input type=submit name=delete value='Delete panel'>";
  print "</form>";
}

print "</div>";
$footer_div=1;
include $config['root_dir'].'theme/footer.php'; 
?>

<script type=text/javascript>
    function pickpanel(picked) {
	// Output to textarea "Description".
        var resp = document.getElementById("editdesc");
        var req = getXMLHttpRequest();
        if(!req) 
            resp.innerHTML = "This function requires Ajax. Please report the problem.";
        var qs = "?func=dispDesc&panelid="+picked.value; 
        req.onreadystatechange = function() {
            if(req.readyState == 4) {
                resp.innerHTML = req.responseText;
                resp.style.display="block";
            }
        }
        req.open("GET", "login/ajaxfunctions.php"+qs, true);
        req.send(null);
    }
</script>


