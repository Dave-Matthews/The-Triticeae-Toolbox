<?php
// monitor.php, dem 26apr2013
// Show MySQL process status.

require 'config.php';
include($config['root_dir'].'includes/bootstrap_curator.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
/* loginTest(); */
?>
<h1>Current Processes</h1>
<div class="section">
  <h3>MySQL</h3>
<?php 

print "<table><tr><th>Database<th>State<th>Status<th>Command";
$sql = "show processlist";
$res = mysql_query($sql);
while ($row = mysql_fetch_assoc($res)) 
  print "<tr><td>$row[db]<td>$row[Command]<td>$row[State]<td>$row[Info]";
print "</table>";
?>

</div>

<div class="section">
  <h3>PHP</h3>

<?php
echo "<pre><font size=2><table><td style='width:500px'>"; 
system("ps uww -C php");
echo "</td></table></font></pre>"; 

echo "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
