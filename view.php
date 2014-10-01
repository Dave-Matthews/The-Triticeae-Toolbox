<?php 
include("includes/bootstrap.inc");
connect();
include("theme/normal_header.php");

// Will take both uids and names
// DEM sep2014: But treat them differently!

$table = $_REQUEST['table'];
$pkey = get_pkey($table);
$name = get_unique_name($table);		   
$prettified = beautifulTableName($table, 0);
$id = $_REQUEST['uid'];
$nm = $_REQUEST['name'];
if ($id) {
  // Argument is a record uid.
  $sql = "SELECT * FROM $table WHERE $pkey = $id";
  $record = mysql_query($sql) or die(mysql_error()."<br>Query was<br>".$sql);
  if(@mysql_num_rows($record) > 0) {
    $row = mysql_fetch_assoc($record);
    $n = $row[$name];
    echo "<h1>$prettified $n</h1>";
    echo "<div class=boxContent>";
    $func = "show_" . $table;
    // Is there a custom function for that table in includes/general.inc
    // or includes/pedigree.inc?  examples:
    //  line_records = includes/pedigree.inc/show_line_records()
    //  markers = includes/general.inc/show_markers()
    //  breeding_programs = includes/general.inc/show_breeding_programs()
    if(function_exists($func))
      call_user_func($func, $row[$pkey]);
    else {
      // Default to raw table dump using includes/general.inc:show_general().
      show_general($table, $row[$pkey]);
    }
    echo "</div>";
  } 
  else 
    error(1, "No Record Found"); 
}
elseif ($nm) {
  // Argument is a record name.
  $sql = "SELECT * FROM $table WHERE $name = '$nm'";
  $record = mysql_query($sql) or die(mysql_error()."<br>Query was<br>".$sql);
  if(@mysql_num_rows($record) > 0) {
    $row = mysql_fetch_assoc($record);
    $n = $row[$name];
    echo "<h1>$prettified $n</h1>";
    echo "<div class=boxContent>";
    $func = "show_" . $table;
    if(function_exists($func))
      call_user_func($func, $row[$pkey]);
    else 
      show_general($table, $row[$pkey]);
    echo "</div>";
  } 
  else 
    error(1, "No Record Found"); 
}

echo "</div>";
include("theme/footer.php");
?>
