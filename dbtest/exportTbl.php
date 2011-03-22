<?php

	ini_set("memory_limit","32M");
	include("../includes/bootstrap.inc");
	connect();
	/* Command to Backup the Database */

	//why all the options? don't ask... stupid windows server.
        $passthru = "mysqldump --skip-opt --add-drop-table --add-locks --create-options --disable-keys --extended-insert --quick -h $server -u $username -p$password $database";

	/* process export table*/
	if (isset($_POST['table_sel'])) {
		$tblname=$_POST['table_sel'];
		$count=$_POST['count'];
		$limit = "";
		if ($count=100) $limit = "limit $count";
		//$backup = "Table name ".$_POST['table_sel']."\n";
		$result=mysql_query("select * from $tblname $limit");
		$count=0;
		ob_start();
		while($row=mysql_fetch_assoc($result)) {
			$rkeys=array_keys($row);
			if ($count==0) {
				$count++;
				for($i=0; $i<count($rkeys); $i++) {
					print mysql_escape_string($rkeys[$i]);
					if ($i!=count($rkeys)-1) print "\t"; 
				}
				print "\n";
			}
			for($i=0; $i<count($rkeys); $i++) {
				print mysql_escape_string($row[$rkeys[$i]]);
				if ($i!=count($rkeys)-1) print "\t"; 
			}
			print "\n";
		}
		$backup.=ob_get_contents();
		ob_end_clean();
		$date = date("m-d-Y-H:i");
// 		$name = "$tblname-$date.txt";
		$name = "$tblname.txt";
		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=$name");
		header("Pragma: no-cache");
		header("Expires: 0");

		echo $backup;
	}
	
?>