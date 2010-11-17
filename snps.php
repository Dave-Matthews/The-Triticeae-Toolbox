<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<Content-Type: text/plain>
<Content-Disposition: inline; filename=THT_SNPs.txt>
<title>SNPs_from_THT.txt</title>
</head>
<body bgcolor=white>

<?php

require_once('config.php');
include_once($config['root_dir'].'includes/bootstrap.inc');

connect();

echo "<h2>SNP Alleles</h2>"; 
echo "The table below is tab-delimited. Save this page as a .txt file and open it with Excel.<br>";
echo "<pre>";
echo "Marker	A allele	B allele	Sequence\n";

$sql = "select marker_name, A_allele, B_allele, sequence
from markers 
where A_allele is not null
order by marker_name";
$res = mysql_query($sql) or die(mysql_error());
while ($row = mysql_fetch_assoc($res)) {
  echo $row['marker_name']."	".$row['A_allele']."	".$row['B_allele']."	".$row['sequence']."\n";
 }

?>