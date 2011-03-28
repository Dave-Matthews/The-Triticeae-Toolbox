<?php
require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/normal_header.php');
connect();

/* We have connected */

$tables = array();
$i = 0;

$query = mysql_query("SHOW TABLES");
while($row = mysql_fetch_row($query)) {

  $tables[$i] = $row[0];
  $i++;

 }

for($i=0; $i<count($tables); $i++) {

  $query = mysql_query("DESCRIBE $tables[$i]");
  $fc = 1;


    echo "<strong>$tables[$i]</strong>: <a href='".$config['base_url']."dbtest/viewtable.php?table=$tables[$i]'>View Contents</a> <br />";
    // ---- <a href=\"deleteTable.php?table=$tables[$i]\">Delete Contents</a> <br />";
    echo "<table border=\"1\">\n<tr>\n\t";

    while($row = mysql_fetch_assoc($query)) {
      
    if($fc == 1) {

      foreach($row as $k=>$v) {
	echo "\n\t\t<td><strong>$k</strong></td>";
      }
    }

    echo "\n</tr>\n<tr>\n\t";

    foreach($row as $k=>$v) {
				
      echo "\n\t\t<td>$v</td>";

    }
    $fc++;
  }

  echo "\n</tr>\n</table>\n<br />";
 }

?>
