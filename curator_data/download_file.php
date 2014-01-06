<?php
/**
 * Force download of file
 */
if (isset($_GET['file'])) {
  $file = $_GET['file'];
} else {
  die("Error: no file\n");
}
$dir = "/tmp/tht/";
$filename = $dir.$file;
header("Content-disposition: attachment; filename=$file");
header("Content-type: application/csv");
readfile("$filename");
?>
