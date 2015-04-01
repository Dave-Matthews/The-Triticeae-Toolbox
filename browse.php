<?php
// browse.php, DEM apr2015
// Display the hits from Quick Search in a tidy pageable table.

require 'config.php';
include($config['root_dir'].'includes/bootstrap.inc');
include($config['root_dir'].'theme/admin_header.php');
connect();
$mysqli = connecti();

$table = $_GET['table'];
$tablelabel = beautifulTableName($table)."s";
$key = get_pkey($table);
$namecol = $_GET['namecol'];

$uidlist = $_GET['hits'];
// Alphabetize
$sql = "select $key, $namecol from $table where $key in ($uidlist) order by $namecol";
$res = mysqli_query($mysqli, $sql) or die(mysqli_error($mysqli));
while ($record = mysqli_fetch_row($res)) {
  $records[] = array($record[0], $record[1]);
};
$numrecords = count($records);

print "<div class='section'>";
print "<h1>$tablelabel</h1>";
// Print the hit names in 5 columns, top to bottom first.
// row, column and cell count from 0; page counts from 1.
$numcols = 5;
$numrows = 10;
$pagesize = $numcols * $numrows;
$page = 1;
if ($_GET['page']) {
  $page = $_GET['page'];
}
print "<table>";
for ($rw = 0; $rw < $numrows; $rw++) {
  print "<tr>";
  for ($cl = 0; $cl < $numcols; $cl++) {
    $cell = (($page - 1) * $pagesize) + ($cl * $numrows + $rw);
    if ($cell < $numrecords) {
      $uid = $records[$cell][0];
      $name = $records[$cell][1];
      print "<td><a href='view.php?table=$table&uid=$uid'>$name</a>";
    }
  }
}
print "</table>";

// Paging
if ($numrecords > $pagesize) {
  $numpages = ceil($numrecords / $pagesize);
  print "Page ";
?>
  <select onchange="window.open('browse.php?table=<?php echo $table ?>&page='+this.options[this.selectedIndex].value+'&namecol=<?php echo $namecol ?>&hits=<?php echo $uidlist ?>', '_self')">
<?php
  for ($p = 1; $p <= $numpages; $p++)
    if ($p == $page)
      print "<option value=$p selected>$p</option>";
    else
      print "<option value=$p>$p</option>";
  print "</select>";
  print " of <b>$numpages</b>";
  if ($numrecords == 900)
    print "<br>Only the first 900 records are shown.";
}

print "</div>";
$footer_div=1;
include($config['root_dir'].'theme/footer.php'); 
?>
