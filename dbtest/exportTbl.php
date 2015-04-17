<?php

	include("../includes/bootstrap.inc");
	$mysqli = connecti();

	/* process export table*/
	if (isset($_POST['table_sel'])) {
		$tblname=$_POST['table_sel'];
		$count=$_POST['count'];
		$limit = "";
		if ($count == "100") $limit = "limit $count";
		//$backup = "Table name ".$_POST['table_sel']."\n";
		$result=mysql_query("select * from $tblname $limit");
		$count=0;
		ob_start();
		while($row=mysqli_fetch_assoc($mysqli, $result)) {
			$rkeys=array_keys($row);
			if ($count==0) {
				$count++;
				for($i=0; $i<count($rkeys); $i++) {
					print mysqli_escape_string($mysqli, $rkeys[$i]);
					if ($i!=count($rkeys)-1) print "\t"; 
				}
				print "\n";
			}
			for($i=0; $i<count($rkeys); $i++) {
				print mysqli_escape_string($mysqli, $row[$rkeys[$i]]);
				if ($i!=count($rkeys)-1) print "\t"; 
			}
			print "\n";
		}
		$backup.=ob_get_contents();
		ob_end_clean();
		$date = date("m-d-Y-H:i");
		$name = "$tblname.txt";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$name");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $backup;
	}
	
?>
