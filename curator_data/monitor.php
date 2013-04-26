<?php
// monitor.php, dem 26apr2013
// Show MySQL process status.

require 'config.php';
include($config['root_dir'].'includes/bootstrap_curator.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
/* loginTest(); */
?>
  <h1>Current MySQL Processes</h1>
  <div class="section">
<?php 

$sql = "show processlist";
$res1 = mysql_query($sql);
$rowcount = mysql_num_rows($res1);
while ($row = mysql_fetch_assoc($res1)) {
  $db = $row[db];
  $usr = $row[User];
}
echo "Database <b>$db</b>, user <b>$usr</b><p>";
echo "Processes: <b>$rowcount</b><p>";
print "<table><tr><th>State<th>Status<th>Command";

$res = mysql_query($sql);
while ($row = mysql_fetch_assoc($res)) 
  print "<tr><td>$row[Command]<td>$row[State]<td>$row[Info]";

print "</table>";
echo "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
