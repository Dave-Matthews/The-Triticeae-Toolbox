<?php
/**
 * Force download of file
 */
if (isset($_GET['file'])) {
  $file = $_GET['file'];
} else {
  die("Error: no file\n");
}
if (isset($_GET['unq'])) {
  $unq = $_GET['unq'];
} else {
  die("Error: no directory\n");
}
$dir = "/tmp/tht/$unq/";
$filename = $dir.$file;
if (file_exists($filename)) {
  header("Content-disposition: attachment; filename=$file");
  header("Content-type: application/csv");
  readfile("$filename");
} else {
  die("Error: file does not exists\n");
}
?>
