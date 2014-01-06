<?php
/**
 * Force download of file
 */
if (isset($_GET['file'])) {
  $file = $_GET['file'];
} else {
  die("Error: no file\n");
}
$pattern = '/([A-Za-z0-9]+\.[A-Za-z0-9]+)/';
if (preg_match($pattern, $file, $match)) {
} else {
  die("Error: bad file name\n");
}
$dir = "/tmp/tht/";
$filename = $dir.$file;
header("Content-disposition: attachment; filename=$file");
header("Content-type: application/csv");
readfile("$filename");
?>
