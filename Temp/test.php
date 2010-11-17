<?php include("../theme/header.php");?>
<?php
	include("mysql_related_functions.inc");
	print "<pre>Testing page for creating Tables</pre>";
	$linkID=get_dbh('lab.bcb.iastate.edu','yhames04', 'gdcb07','sandbox_yhames04');
	$result=mysql_query("show tables");
	$num_rows=mysql_num_rows($result);
	for($i=0; $i<$num_rows; $i++) {
		$row=mysql_fetch_assoc($result);
		foreach ($row as $key=>$val) {
			print nl2br("$key -> $val\n");
		}
	}	
?>

<?php include("../theme/footer.php");?>