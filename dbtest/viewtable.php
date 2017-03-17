<?php
//***********************************************************
//
// 3/28/2011 Jlee Fix to prevent crashing browser on large tables   
// 3/25/2011 JLee Fix to work in system 
//***********************************************************
require 'config.php';
/*
 * Logged in page initialization
 */
include($config['root_dir'] . 'includes/bootstrap_curator.inc');
include($config['root_dir'].'theme/normal_header.php');
$mysqli = connecti();
loginTest();

/* ************************************/

////////////////////////////////////////////////////////////////////////////////
ob_start();

authenticate_redirect(array(USER_TYPE_ADMINISTRATOR));
ob_end_flush();

$table = mysqli_real_escape_string($mysqli, $_GET['table']);
$capTable = strtoupper($table);
$start = $_GET['start'];
if ($start < 0) $start = 0;

$sql =  "SELECT count(*) as num FROM $table";
$query = mysqli_query($mysqli, $sql);
$rdata = mysqli_fetch_assoc($query);
$max = $rdata['num'];
$lStart = $max - 500;
$pStart = $start - 500;
$nStart = $start + 500;	

$sql = "SELECT * FROM $table limit ". $start . ', 500';
$query = mysqli_query($mysqli, $sql);

$fc = 1;

echo "<p><H3><strong>$capTable</strong>:</H3></p>";

echo "<div align='left' >";
if ($start != 0) {
	echo "<a href=\"dbtest/viewtable.php?table=$table&start=0 \"> [First 500] </a> ";
} 
if ($start+500 < $max) {
	echo "<a href=\"dbtest/viewtable.php?table=$table&start=$lStart \"> [Last 500] </a> ";
}
echo "</div>";
echo "<table border=\"1\">\n<tr>\n\t";

while($row = mysqli_fetch_assoc($query)) {

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
echo "\n</tr>\n</table>";

echo "<br>";
echo "<br>";

if ($fc >= 500)	 {
	echo "<div align='left' >";
	if ($pStart >= 0) {
		echo "<a href=\"dbtest/viewtable.php?table=$table&start=0 \"> [First 500] </a> ";
		echo "<td><td>";
		echo "<a href=\"dbtest/viewtable.php?table=$table&start=$pStart \"> [Previous 500] </a> ";
		echo "<td><td>";
	}
	if ( $lStart != $start) { 
		echo "<a href=\"dbtest/viewtable.php?table=$table&start=$nStart \"> [Next 500] </a> ";
		echo "<td><td>";	
		echo "<a href=\"dbtest/viewtable.php?table=$table&start=$lStart \"> [Last 500] </a> ";
	}
} elseif ($fc <= 500 && $start > 0) {
	echo "<a href=\"dbtest/viewtable.php?table=$table&start=0 \"> [First 500] </a> ";
	echo "<td><td>";	
	echo "<a href=\"dbtest/viewtable.php?table=$table&start=$pStart \"> [Previous 500] </a> ";
}  
echo "</div>";
?>
